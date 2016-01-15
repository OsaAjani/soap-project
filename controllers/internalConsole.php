<?php
	/**
	 * Cette page gère les appels de la console
	 */
	class internalConsole extends Controller
	{
		public function checkRunTruck ()
		{
			public function checkStaticTruck () 
			{
				//Select tout les truck en cours
				global $db;
				
				$pathsInProgress = $db->getFromTableWhere('path', ['status' => internalConstants::$pathStatus['RUN']])
				$truckRun = array();

				foreach ($pathsInProgress as $path) {
					$truckRun[] = $path['']
				}
			}
		}
	}
