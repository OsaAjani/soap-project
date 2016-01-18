<?php
/**
 * page qui retourne l'ajax pour l'admin
 */
class ajax extends Controller
{
	/**
	 * Cette fonction retourne les camion en fonction du statut de leur trajet au format json
	 * @param int $status : Le status du trajet
	 * @return string : Les camions sous forme de json
	 */
	public function truck_by_path_status($status)
	{
		global $db;

		$trucks = $db->getTrucksFromPathStatus($status);

		echo json_encode($trucks);
	}

	/**
	 * Cette fonction retourne les paths en fonction de leur statut
	 * @param int $status : Le statut du trajet
	 * @return string : Les paths sous forme de json
	 */
	public function path_by_status($status)
	{
		global $db;
		$paths = $db->getFromTableWhere('path', ['status' => $status]);
		echo json_encode($paths);
	}

	/**
	 * Cette fonction retourne la dernère adresse d'un path
	 * @param int $pathId : L'id du path
	 * @return mixed : La dernière adresse du path sous forme d'un numéro de rue ou faux si le camion n'existe pas
	 */
	public function path_last_address ($pathId)
	{
		global $db;

		if (!$position = $db->getFromTableWhere('position', ['path_id' => $pathId], $order_by = 'at', $desc = true, $limit = 1))
		{
			return false;
		}

		$position = $position[0];

		$address = internalTools::getAdressFromLatitudeAndLongitude($position['latitude'], $position['longitude']);
	}

	/**
	 * Cette fonction retourne la liste des repairer disponibles
	 * @return string : la liste des repairers en json
	 */
	public function repairer_availables()
	{
		global $db;

		$repairers = $db->getFromTableWhere('repairer');
		$notAvailablesRepairers = $db->getNotAvailablesRepairers();

		foreach ($repairers as $key => $repairer)
		{
			foreach ($notAvailablesRepairers as $key2 => $notAvailablesRepairer)
			{
				if ($notAvailablesRepairer['id'] == $repairer['id'])
				{
					unset($repairers[$key]);
					continue 2;
				}
			}
		}

		echo json_encode($repairers);
	}
}
