<?php
	/**
	 * Cette page gère les appels de la console
	 */
	use PhpAmqpLib\Connection\AMQPStreamConnection;
	use PhpAmqpLib\Message\AMQPMessage;


	class internalConsole extends Controller
	{
		public function checkStaticTruck () 
		{
			global $db;
			
			$pathsInProgress = $db->getFromTableWhere('path', ['status' => internalConstants::$pathStatus['RUN']]);
			
			if (count($pathsInProgress))
			{
				$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
				$channel = $connection->channel();

				foreach ($pathsInProgress as $path) {
					$channel->queue_declare('check_truck', false, true, false, false);
					$data = array(
						'path_id' 	=> $path['id'],
						'truck'		=> $path['truck_id']
					);
					
					$msg = new AMQPMessage(json_encode($data),
			        	array('delivery_mode' => 2) # make message persistent
			        );
						$channel->basic_publish($msg, '', 'check_truck');
				}

				$channel->close();
				$connection->close();
			}	
		}
	}
