<?php
/**
 * page gestion
 */
class gestion extends Controller
{
	/**
	 * Page de gestion par défaut
	 */	
	public function byDefault()
	{
		return $this->render("gestion/default");
	}

	/**
	 * Cette fonction retourne les camion qui roulent pour la premiere fenetre de l'admin
	 */
	public function runningTrucks ()
	{
		global $logger;
		global $db;
		$logger->log('info', 'Access Back Office, get all running trucks');

		//On recupère les trajets qui roule ou son en pause
		$paths = $db->getRunOrBreakPaths();
		
		//On recupère les chauffeur et les camion liés
		foreach ($paths as $key => $path)
		{
			$path['truck'] = $db->getFromTableWhere('truck', ['id' => $path['truck_id']])[0];
			$path['driver'] = $db->getFromTableWhere('driver', ['id' => $path['driver_id']])[0];
			$position = $db->getFromTableWhere('position', ['path_id' => $path['id']], $order_by = 'at', $desc = true, $limit = 1);
			$path['position'] = $position ? $position[0] : false;
			$paths[$key] = $path;
		}

		return $this->render('gestion/runningTrucks', array(
			'paths' => $paths,
		));
	}

	/**
	 * Cette fonction retourne les véhicules en problèmes pour la seconde fenetre de l'admin
	 */
	public function brokenTrucks ()
	{
		global $logger;
		global $db;
		$logger->log('info', 'Access Back Office, get all broken trucks');

		//On recupere les trajets en panne ou qui son en cours de réparation
		$paths = $db->getFromTableWhere('path', ['status' => internalConstants::$pathStatus['DOWN']]);

		//On recupère les chauffeur et les camion liés
		foreach ($paths as $key => $path)
		{
			//Si quelqun est déjà en attente d'une réponse pour une intervention, on annule
			if ($db->getFromTableWhere('intervention', ['path_id' => $path['id'], 'status' => internalConstants::$interventionStatus['WAIT']]))
			{
				unset($paths[$key]);
				continue;
			}
			if ($db->getFromTableWhere('intervention', ['path_id' => $path['id'], 'status' => internalConstants::$interventionStatus['RUN']]))
			{
				unset($paths[$key]);
				continue;
			}

			$path['truck'] = $db->getFromTableWhere('truck', ['id' => $path['truck_id']])[0];
			$path['driver'] = $db->getFromTableWhere('driver', ['id' => $path['driver_id']])[0];
			$paths[$key] = $path;
		}

		//On recupère tous les reparateurs disponibles
		$repairers = $db->getAvailablesRepairers();

		return $this->render('gestion/brokenTrucks', array(
			'paths' => $paths,
			'repairers' => $repairers,
		));
	}

	/**
	 * Cette fonction retourne les interventions en cours
	 */
	public function currentInterventions ()
	{
		global $logger;
		global $db;
		$logger->log('info', 'Access Back Office, get all current interventions');

		//On recupère les interventions en cours
		$interventions = $db->getWaitOrRunInterventions();
		

		//On recupère les chemins liés
		foreach ($interventions as $key => $intervention)
		{
			$intervention['path'] = $db->getFromTableWhere('path', ['id' => $intervention['path_id']])[0];
			$intervention['repairer'] = $db->getFromTableWhere('repairer', ['id' => $intervention['repairer_id']])[0];

			$intervention['path']['truck'] = $db->getFromTableWhere('truck', ['id' => $intervention['path']['truck_id']])[0];
			$intervention['path']['driver'] = $db->getFromTableWhere('driver', ['id' => $intervention['path']['driver_id']])[0];
			$position = $db->getFromTableWhere('position', ['path_id' => $intervention['path']['id']], $order_by = 'at', $desc = true, $limit = 1);
			$intervention['path']['position'] = $position ? $position[0] : false;
			$interventions[$key] = $intervention;
		}

		return $this->render('gestion/currentInterventions', array(
			'interventions' => $interventions
		));
	}

	/**
	 * Cette fonction permet de demander une intervention pour un employé
	 * @param int $pathId : L'id du path sur lequel il faut intervenir
	 * @param int $repairerId : L'id du réparateur à contacter
	 */
	public function askForIntervention ($pathId, $repairerId)
	{
		global $logger;
		global $db;
		$logger->log('info', 'Access Back Office, ask for intervention for path id : ' . $pathId . ' and repairer id : ' . $repairerId);

		$retourOk = json_encode(['success' => true]);
		$retourKo = json_encode(['success' => false]);

		if (!$db->getFromTableWhere('path', ['id' => $pathId]))
		{
			$logger->log('warning', 'No path with id ' . $pathId . ' find in database');
			echo $retourKo;
			return false;
		}

		$availablesRepairers = $db->getAvailablesRepairers();
		$isAvailable = false;

		foreach ($availablesRepairers as $availablesRepairer)
		{
			if ($availablesRepairer['id'] == $repairerId)
			{
				$repairer = $availablesRepairer;
				$isAvailable = true;
				break;
			}
		}

		if (!$isAvailable)
		{
			$logger->log('warning', 'repairer with id : ' . $repairerId . ' isn\'t available');
			echo $retourKo;
			return false;
		}

		$intervention = array(
			'path_id' => $pathId,
			'repairer_id' => $repairerId,
			'status' => internalConstants::$interventionStatus['WAIT'],
		);

		if (!$db->insertIntoTable('intervention', $intervention))
		{
			$logger->log('error', 'can\'t save intevention : ' . json_encode($intervention));
			echo $retourKo;
			return false;
		}

		$interventionId = $db->lastId();

		//On envoie un SMS au réparateur
		$internalSms = new internalSms();
		
		$paths = $db->getFromTableWhere('path', ['id' => $pathId]);
		$path = $paths[0];
		$path['truck'] = $db->getFromTableWhere('truck', ['id' => $path['truck_id']])[0];
		$path['position'] = $db->getFromTableWhere('position', ['path_id' => $path['id']], $order_by = 'at', $desc = true, $limit = 1)[0];

		$text = "Une intervention sur le camion N°" . $path['truck']['matriculation'] . " vous attend à l'adresse suivante : " . internalTools::getAdressFromLatitudeAndLongitude ($path['position']['latitude'], $path['position']['longitude']) . "\n" .
			"Pour valider l'intervention, renvoyez le SMS suivant à ce numéro : 'intervention:" . $interventionId . ":ok'\n" .
			"\n" .
			"Si vous n'avez pas répondu d'ici 5 minutes l'intervention sera annulée.";	
		
		$logger->log('info', 'Send SMS to repairer id : ' . $repairer['id'] . ' (' . $repairer['phone'] . ') with message : ' . $text);	
		$internalSms = new internalSms();
		$internalSms->sendSmsToNumber($text, $repairer['phone']);

		echo $retourOk;
		return true;
	}

	/**
	 * Cette fonction permet de valider une intervention par SMS pour un repairer
	 * @param string $key : La clef du webhook
	 */
	public function webhookValidateIntervention ($key)
	{
		global $logger;
		global $db;

		if ($key != '7Jl2ESGU5wQZeVzD9ZkuQA26VBYorI9J')
		{
			$logger->log('error', 'Webhook validate intervention invalid key : ' . $key);
			return false;
		}

		if (!isset($_POST['content'], $_POST['send_by']))
		{
			return false;
		}

		$smsText = explode(':', str_replace('\n', '', trim($_POST['content'])));

		if (count($smsText) < 3)
		{
			return false;
		}

		if ($smsText[0] != 'intervention')
		{
			return false;
		}

		if (!$interventions = $db->getFromTableWhere('intervention', ['id' => $smsText[1]]))
		{
			return false;
		}
		$intervention = $interventions[0];

		if ($intervention['status'] != internalConstants::$interventionStatus['WAIT'] && $intervention['status'] != internalConstants::$interventionStatus['RUN'])
		{
			return false;
		}

		if ($smsText[2] == 'ok')
		{
			$intervention['status'] = internalConstants::$interventionStatus['RUN'];
			$db->updateTableWhere('path', ['status' => internalConstants::$pathStatus['FIX']], ['id' => $intervention['path_id']]);


			$text = 'L\'intervention N°' . $intervention['id'] . ' a bien été validée. Quand vous aurez réparé la panne, envoyez le message suivant à ce numéro : "intervention:' . $intervention['id'] . ':finish"';
			$internalSms = new internalSms();
			$internalSms->sendSmsToNumber($text, $_POST['send_by']);

			if ($paths = $db->getFromTableWhere('path', ['id' => $intervention['path_id']]))
			{
				$path = $paths[0];
				$driver = $db->getFromTableWhere('driver', ['id' => $path['driver_id']])[0];
				$textDriver = 'Un reparateur viens de vous prendre en charge, il arrivera sous peu.';
				$internalSms->sendSmsToNumber($textDriver, $driver['phone']);
			}
		}
		else if ($smsText[2] == 'finish')
		{
			$date = new DateTime();
			$date = $date->format('Y-m-d H:i:s');
			$intervention['end_date'] = $date;
			$intervention['status'] = internalConstants::$interventionStatus['END'];

			$text = 'L\'intervention N°' . $intervention['id'] . ' a bien été terminée. Merci.';
			$internalSms = new internalSms();
			$internalSms->sendSmsToNumber($text, $_POST['send_by']);
		}
		else
		{
			return false;
		}

		$db->updateTableWhere('intervention', $intervention, ['id' => $intervention['id']]);
		return true;
	}

	/**
	 * Cette fonction retourne la dernière position de chaque camion qui roule et son nom
	 */
	public function getTrucksLastPositions ()
	{
		global $db;

		$return = [];

		//On recupère les trajets qui roule ou son en pause
		$paths = $db->getRunOrBreakPaths();

		//On recupère les camion liés
		foreach ($paths as $key => $path)
		{
			$truck = $db->getFromTableWhere('truck', ['id' => $path['truck_id']])[0];
			if (!$position = $db->getFromTableWhere('position', ['path_id' => $path['id']], $order_by = 'at', $desc = true, $limit = 1))
			{
				continue;
			}
			$position = $position[0];
			
			$return[] = ['matriculation' => $truck['matriculation'], 'latitude' => $position['latitude'], 'longitude' => $position['longitude']];
		}

		echo json_encode($return);
	}
}
