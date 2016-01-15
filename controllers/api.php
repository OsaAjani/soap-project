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
		$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
		$channel = $connection->channel();

		$channel->queue_declare('change_status', false, true, false, false);
		//send to Queue info change status

		$channel->close();
		$connection->close();
		return $this->autoHttpCode(true);
	}

	public function postPostion ($idPath, $latitude, $longitude, $at)
	{
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

}
