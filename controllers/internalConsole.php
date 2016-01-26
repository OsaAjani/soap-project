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
			global $logger;
			global $db;

			$logger->log('info', 'Script check static truck');
			$pathsInProgress = $db->getFromTableWhere('path', ['status' => internalConstants::$pathStatus['RUN']]);

			if (!count($pathsInProgress))
			{
				$logger->log('info', 'no path in progess');
				return false;
			}
			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			$channel->queue_declare('check_truck', false, true, false, false);

			foreach ($pathsInProgress as $path) {

				$data = array(
					'path_id' 		=> $path['id'],
					'sms_static'		=> $path['sms_static'],
					'truck'			=> $path['truck_id'],
					'driver'		=> $path['driver_id']
				);
				$logger->log('debug','call worker with path : ' . json_encode($data));
				$msg = new AMQPMessage(json_encode($data),
		        	array('delivery_mode' => 2) # make message persistent
		        );
				$channel->basic_publish($msg, '', 'check_truck');
			}
			
			$channel->close();
			$connection->close();
			
		}

		/**
		 * Cette fonction permet d'appeler la vérification des interventions des reparateurs
		 */
		public function checkInterventionResponse () 
		{
			global $logger;
			global $db;

			$logger->log('info', 'Script check intervention response');
			
			if (!$interventions = $db->getFromTableWhere('intervention', ['status' => internalConstants::$interventionStatus['WAIT']]))
			{
				return false;
			}

			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();
			$channel->queue_declare('check_intervention', false, true, false, false);

			foreach ($interventions as $intervention) {
				$msg = new AMQPMessage(json_encode($intervention), ['delivery_mode' => 2]);
				$channel->basic_publish($msg, '', 'check_intervention');
			}

			$channel->close();
			$connection->close();
		}
	}
