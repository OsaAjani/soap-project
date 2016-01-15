<?php
/**
 * API Rest
 */
class api extends ApiController
{
	public function putStatus ($idPath, $status)
	{
		return $this->autoHttpCode();
	}

	public function postPostion ($idPath, $latitude, $longitude, $at)
	{
		return $this->autoHttpCode();
	}
}
