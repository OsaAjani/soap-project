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
				global $db;
				$params = json_decode($msg->body, true);

				if ($params['status'] == 5)
				{
					$now = new \DateTime();
					$db->updateTableWhere('path', ['end_date' => $now->format('Y-m-d H:i:s')], ['id' => $params['path_id']]);
				}
				
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
				global $db;
				$position = json_decode($msg->body, true);
				var_dump($position);
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
				global $db;
				$path = json_decode($msg->body, true);
				$limit = new \DateTime();
				$limit->sub(new \DateInterval('PT5M'));
				$positions = $db->getFromTableWhere('position', ['>at' => $limit->format('Y-m-d H:i:s'), 'path_id' => $path['id']]);
				$static = true;
				$previousLongitude = $positions[0]['longitude'];
				$previousLatitude = $positions[0]['latitude'];
				foreach ($positions as $position) 
				{
					if ($position['longitude'] != $previousLongitude || $position['latitude'] != $previousLatitude)
					{
						$static = false;
						break;
					}
				}

				if ($static == true)
				{
					$driver = $db->getFromTableWhere('driver', ['id' => $path['driver']]);
					$message = 'Nous avons détecté une immobilité de votre véhicule depuis plus de 5 minutes, Merci d\'informer le status de votre trajet via l\'application';
					$sms = new internalSms();
					$sms->send($driver[0]['phone'], $message);
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
				global $db;
				$intervention = json_decode($msg->body, true);

				$limit = new DateTime();
				$limit->sub(new DateInterval('PT5M'));
				
				$interventionStartDate = new DateTime($intervention['start_date']);

				//Si ça fait plus de 5 minutes qu'on à lancé la demande d'intervention
				if ($limit < $interventionStartDate)
				{
					$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
					return true;
				}

				//On va passé l'intervention en annulée
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
