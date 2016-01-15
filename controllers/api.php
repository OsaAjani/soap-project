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
		global $db;

		if(!in_array($status, internalConstants::$pathStatus)|| !$db->getFromTableWhere('path', ['id' => $idPath]))
		{
			return $this->autoHttpCode(false);
		}

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
		global $db;

		if(!$db->getFromTableWhere('path', ['id' => $idPath]))
		{
			return $this->autoHttpCode(false);
		}

		$timestamp = new \DateTime();
		$timestamp->setTimestamp($at);

		$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
		$channel = $connection->channel();

		$channel->queue_declare('post_position', false, true, false, false);
		$data = array(
			'path_id' 	=> $idPath,
			'latitude' 	=> $latitude,
			'longitude' => $longitude,
			'at' 		=> $timestamp->format("Y-m-d G:i:s")
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
		global $db;

		if(!$path = $db->getFromTableWhere('path', ['id' => $idPath]))
		{
			return $this->autoHttpCode(false);
		}

		$path = $path[0];
		
		$driver = $db->getFromTableWhere('driver', ['id' => $path["driver_id"]]);
		$truck = $db->getFromTableWhere('truck', ['id' => $path["truck_id"]]);
		
		$path["driver"] = $driver[0];
		$path["truck"] = $truck[0];
		
		unset($path["driver_id"]);
		unset($path["truck_id"]);

		return $this->json($path)->autoHttpCode();
	}

	public function _workerStatus ()
	{
		$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
		$channel = $connection->channel();

		$channel->queue_declare('change_status', false, true, false, false);

		$callback = function($msg){
			global $db;
			$params = json_decode($msg->body, true);
			$db->updateTableWhere('path', ['status' => $params["status"]], ['id' => $params["path_id"]]);
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

	public function _workerPosition ()
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

}
