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
		global $db;

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
		global $db;

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
		global $db;

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
		global $db;
		$retourOk = json_encode(['success' => true]);
		$retourKo = json_encode(['success' => false]);

		if (!$db->getFromTableWhere('path', ['id' => $pathId]))
		{
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
		global $db;
		if ($key != '7Jl2ESGU5wQZeVzD9ZkuQA26VBYorI9J')
		{
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

			$text = 'L\'intervention N°' . $intervention['id'] . ' a bien été validée. Quand vous aurez réparé la panne, envoyez le message suivant à ce numéro : "intervention:' . $intervention['id'] . ':finish"';
			$internalSms = new internalSms();
			$internalSms->sendSmsToNumber($text, $_POST['send_by']);
		}
		else if ($smsText[2] == 'finish')
		{
			$date = new DateTime();
			$date = $date->format('Y-m-d H:i:s');
			$intervention['end_date'] = $date;
			$intervention['status'] = internalConstants::$interventionStatus['END'];

			$paths = $db->updateTableWhere('path', ['status' => internalConstants::$pathStatus['RUN']], ['id' => $intervention['path_id']]);

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
}
