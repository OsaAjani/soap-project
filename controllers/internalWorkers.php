<?php
	use PhpAmqpLib\Connection\AMQPStreamConnection;
	use PhpAmqpLib\Message\AMQPMessage;

	class internalWorkers extends Controller
	{
		public function _status ()
		{
			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			$channel->queue_declare('change_status', false, true, false, false);

			$callback = function($msg){
				global $db;
				$params = json_decode($msg->body, true);

				if ($params['status'] == 3)
				{
					//SELECT repairer
					$message = '';
					$headers = array();
					$body = array(
						'email' => internalConstants::$urlApiRaspiSms,
						'password' => internalConstants::$urlApiRaspiSms,
						'numbers' => $repairer['number'],
						'text' => $message
					);

					Unirest\Request::post(internalConstants::$urlApiRaspiSms, $headers, $body);
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

		public function _position ()
		{
			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			$channel->queue_declare('post_position', false, true, false, false);

			$callback = function($msg){
				global $db;
				$position = json_decode($msg->body, true);
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

		public function _checkTruck () 
		{
			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			$channel->queue_declare('check_truck', false, true, false, false);

			$callback = function($msg){
				global $db;
				$path = json_decode($msg->body, true);
				$now = new \DateTime();
				$now->sub(new \DateInterval('PT5M'));
				$positions = $db->getFromTableWhere('position', ['>at' => $now->format('Y-m-d H:i:s')]); //AJOUTER CONDITION SUR PATH
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
					$message = '';
					$headers = array();
					$body = array(
						'email' => internalConstants::$urlApiRaspiSms,
						'password' => internalConstants::$urlApiRaspiSms,
						'numbers' => $driver[0]['phone'],
						'text' => $message
					);

					Unirest\Request::post(internalConstants::$urlApiRaspiSms, $headers, $body);
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
	}
