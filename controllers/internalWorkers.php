<?php
	use PhpAmqpLib\Connection\AMQPStreamConnection;
	use PhpAmqpLib\Message\AMQPMessage;

	class internalWorkers extends Controller
	{
		public function status ()
		{
			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			$channel->queue_declare('change_status', false, true, false, false);

			$callback = function($msg){
				global $logger;
				global $db;
				$params = json_decode($msg->body, true);
				$path = $db->getFromTableWhere('path', ['id' => $params['path_id']]);
				if ($params['status'] == 1)
				{
					if ($path[0]['status'] == 0)
					{
						$logger->log('info', 'Worker - The path with id : ' . $path[0]['id'] . ' start');
						$now = new \DateTime();
						$db->updateTableWhere('path', ['start_date' => $now->format('Y-m-d H:i:s')], ['id' => $params['path_id']]);
					}
				}
				else if ($params['status'] == 5)
				{
					$logger->log('info', 'Worker - The path with id : ' . $path[0]['id'] . ' is finished');
					$now = new \DateTime();
					$db->updateTableWhere('path', ['end_date' => $now->format('Y-m-d H:i:s')], ['id' => $params['path_id']]);
				}
				$logger->log('info', 'Worker - The path with id : ' . $path[0]['id'] . ' has new status : ' . array_search($params['status'], internalConstants::$pathStatus));
				$db->updateTableWhere('path', ['status' => $params['status']], ['id' => $params['path_id']]);
	   			$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
			};

			$channel->basic_qos(null, 1, null);
			$channel->basic_consume('change_status', '', false, false, false, false, $callback);

			while(count($channel->callbacks)) {
	    		$channel->wait();
			}

			$channel->close();
			$connection->close();
		}

		public function position ()
		{
			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			$channel->queue_declare('post_position', false, true, false, false);
			$callback = function($msg){
				global $logger;
				global $db;
				$position = json_decode($msg->body, true);
				$logger->log('info', 'Worker - insert new geolocation : ' . json_encode($position));
				$db->insertIntoTable('position', $position);
	   			$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
			};

			$channel->basic_qos(null, 1, null);
			$channel->basic_consume('post_position', '', false, false, false, false, $callback);

			while(count($channel->callbacks)) {
	    		$channel->wait();
			}

			$channel->close();
			$connection->close();
		}

		public function checkTruck () 
		{
			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			$channel->queue_declare('check_truck', false, true, false, false);

			$callback = function($msg){
				global $logger;
				global $db;
				$path = json_decode($msg->body, true);
				$limit = new \DateTime();
				$limit->sub(new \DateInterval('PT5M'));
				$positions = $db->getFromTableWhere('position', ['>at' => $limit->format('Y-m-d H:i:s'), 'path_id' => $path['path_id']]);
				if (count($positions) < 2)
				{
					return true;
				}
				$static = true;
				$previousLongitude = $positions[0]['longitude'];
				$previousLatitude = $positions[0]['latitude'];
				foreach ($positions as $position) 
				{
					if ($position['longitude'] != $previousLongitude || $position['latitude'] != $previousLatitude)
					{
						if ($path['sms_static'])
						{
							$db->updateTableWhere('path', ['sms_static' => false], ['id' => $path['path_id']]);
						}
						$logger->log('info', 'Worker - Truck for the path with id : ' . $path['id'] . 'isn\'t static');
						$static = false;
						break;
					}
				}
				
				if ($static == true && !$path['sms_static'])
				{
					$db->updateTableWhere('path', ['sms_static' => true], ['id' => $path['path_id']]);
					$logger->log('info', 'Worker - Truck for the path with id : ' . $path['path_id'] . 'is static');
					$driver = $db->getFromTableWhere('driver', ['id' => $path['driver']]);
					$message = 'Nous avons détecté une immobilité de votre véhicule depuis plus de 5 minutes, Merci d\'informer le status de votre trajet via l\'application';
					$logger->log('info', 'Worker - Send SMS to driver with id ' . $driver[0]['id'] . ' (' . $driver[0]['phone'] . ') with message : ' . $message);
					$sms = new internalSms();
					$sms->sendSmsToNumber($message,$driver[0]['phone']);
				}

	   			$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
			};

			$channel->basic_qos(null, 1, null);
			$channel->basic_consume('check_truck', '', false, false, false, false, $callback);

			while(count($channel->callbacks)) {
	    		$channel->wait();
			}

			$channel->close();
			$connection->close();
		}

		/**
		 * Cette fonction prend une intervention qui n'a pas reçue de réponse et vérifie si elle doit être annulée
		 */
		public function checkIntervention ()
		{
			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			$channel->queue_declare('check_intervention', false, true, false, false);

			$callback = function($msg) {
				global $logger;
				global $db;

				$logger->log('info', 'Worker - Check intervention');
				$intervention = json_decode($msg->body, true);

				$limit = new DateTime();
				$limit->sub(new DateInterval('PT5M'));
				
				$interventionStartDate = new DateTime($intervention['start_date']);

				//Si ça fait moins de 5 minutes qu'on à lancé la demande d'intervention
				if ($limit < $interventionStartDate)
				{
					$logger->log('info', 'Worker - ask intervention for less than 5 minutes for intervention with id : ' . $intervention['id']);
					$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
					return true;
				}

				//On va passé l'intervention en annulée
				$logger->log('info', 'Worker - intervention with id . ' . $intervention['id'] . ' is refused');
				$intervention['status'] = internalConstants::$interventionStatus['REFUSED'];
				$intervention['end_date'] = new DateTime();
				$intervention['end_date'] = $intervention['end_date']->format('Y-m-d H:i:s');
				$db->updateTableWhere('intervention', $intervention, ['id' => $intervention['id']]);
	   			$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
			};

			$channel->basic_qos(null, 1, null);
			$channel->basic_consume('check_intervention', '', false, false, false, false, $callback);

			while(count($channel->callbacks)) {
				$channel->wait();
			}

			$channel->close();
			$connection->close();
		}
	}
