<?php
/**
 * API Rest
 */
	use PhpAmqpLib\Connection\AMQPStreamConnection;
	use PhpAmqpLib\Message\AMQPMessage;

	class api extends ApiController
	{
		public function putStatus ($idPath, $status)
		{
			global $logger;
			global $db;

			if(!in_array($status, internalConstants::$pathStatus)|| !$db->getFromTableWhere('path', ['id' => $idPath]))
			{
				$logger->log('warning', 'Request API PUT Status - No path with id ' . $idPath . ' find in database OR status ' . $status . ' doesn\'t exist');
				return $this->autoHttpCode(false);
			}

			$logger->log('info', 'Request API PUT Status with path id ' . $idPath . ' and new status : ' . array_search($status, internalConstants::$pathStatus));

			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			$channel->queue_declare('change_status', false, true, false, false);
			$data = array(
				'path_id' => $idPath,
				'status' => $status
			);
			
			$msg = new AMQPMessage(json_encode($data),
	        	array('delivery_mode' => 2) # make message persistent
	        );

			$channel->basic_publish($msg, '', 'change_status');

			$channel->close();
			$connection->close();

			return $this->autoHttpCode();
		}

		public function postPosition ($idPath, $latitude, $longitude, $at) //$at au Format timestamp
		{
			global $logger;
			global $db;

			if(!$db->getFromTableWhere('path', ['id' => $idPath]))
			{
				$logger->log('warning', 'Request API POST Position - No path with id ' . $idPath . ' find in database');
				return $this->autoHttpCode(false);
			}

			$timestamp = new \DateTime();
			$timestamp->setTimestamp($at);

			$logger->log('info', 'Request API PUT Status with path id : ' . $idPath . ', latitude : ' . $latitude . ', longitude : ' . $longitude . ' at ' . $timestamp->format('Y-m-d G:i:s'));

			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			$channel->queue_declare('post_position', false, true, false, false);
			$data = array(
				'path_id' 	=> $idPath,
				'latitude' 	=> $latitude,
				'longitude' => $longitude,
				'at' 		=> $timestamp->format('Y-m-d G:i:s')
			);
			
			$msg = new AMQPMessage(json_encode($data),
	        	array('delivery_mode' => 2) # make message persistent
	        );

			$channel->basic_publish($msg, '', 'post_position');

			$channel->close();
			$connection->close();

			return $this->autoHttpCode();
		}

		public function getPath ($idPath)
		{
			global $logger;
			global $db;
			
			if(!$path = $db->getFromTableWhere('path', ['id' => $idPath]))
			{
				$logger->log('warning', 'Request API GET Path - No path with id ' . $idPath . ' find in database');
				return $this->autoHttpCode(false);
			}

			$logger->log('info', 'Request API GET Path with id ' . $idPath);

			$path = $path[0];
			
			$driver = $db->getFromTableWhere('driver', ['id' => $path['driver_id']]);
			$truck = $db->getFromTableWhere('truck', ['id' => $path['truck_id']]);
			
			$path['driver'] = $driver[0];
			$path['truck'] = $truck[0];
			
			unset($path['driver_id']);
			unset($path['truck_id']);

			return $this->json($path)->autoHttpCode();
		}
	}
