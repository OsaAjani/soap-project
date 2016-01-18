<?php
	/**
	 * Cette classe contient l'ensemble des requetes sur la base
	 */
	class DataBase extends Model
	{
		/**
		 * Cette classe récupère les trucks en fonction du status de leur path
		 * @param int $status : Le status du path
		 * @return array : Un tableau des camion récupérés
		 */
		public function getTrucksFromPathStatus ($status)
		{
			$query = '
				SELECT truck.id AS id, truck.matriculation AS matriculation
				FROM path
				JOIN truck
				ON (path.truck_id = truck.id)
				AND path.status = :status
			';

			$params = ['status' => $status];

			return $this->runQuery($query, $params);
		}

		/**
		 * Cette méthode retourne les repairers disponibles
		 * @return array : un tableau des repairers ko
		 */
		public function getNotAvailablesRepairers()
		{
			$query = '
				SELECT repairer.id AS id, repairer.name AS name, repairer.phone AS phone
				FROM intervention
				JOIN repairer
				ON (intervention.repairer_id = repairer.id)
				AND (intervention.status = 1 OR intervention.status = 0)
			';

			return $this->runQuery($query);
		}

		/**
		 * Cette méthode retourne les repairers disponibles
		 * @return array : un tableau des repairers ok
		 */
		public function getAvailablesRepairers()
		{
			$repairers = $this->getFromTableWhere('repairer');
			$notAvailablesRepairers = $this->getNotAvailablesRepairers();

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
			
			return $repairers;
		}

		/**
		 * Cette méthode retourne les path qui roulent ou sont en pause
		 * @return array : un tableau des paths
		 */
		public function getRunOrBreakPaths()
		{
			$query = '
				SELECT *
				FROM path
				WHERE status = 1
				OR status = 2
			';

			return $this->runQuery($query);
		}

		/**
		 * Cette méthode retourne les path qui son cassé ou en réparation
		 * @return array : un tableau des paths
		 */
		public function getDownOrFixPaths()
		{
			$query = '
				SELECT *
				FROM path
				WHERE status = 3
				OR status = 5
			';

			return $this->runQuery($query);
		}

		/**
		 * Cette méthode retourne les intervention en cours ou en attente
		 * @return array : un tableau des paths
		 */
		public function getWaitOrRunInterventions()
		{
			$query = '
				SELECT *
				FROM intervention
				WHERE status = 0
				OR status = 1
			';

			return $this->runQuery($query);
		}
	}
