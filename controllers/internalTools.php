<?php
	class internalTools extends Controller
	{
		/**
		 * Replace accented characters with non accented
		 *
		 * @param $str
		 * @return mixed
		 * @link http://myshadowself.com/coding/php-function-to-convert-accented-characters-to-their-non-accented-equivalant/
		 */
		public static function removeAccents($str) {
			$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
			$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
			return str_replace($a, $b, $str);
		}

		/**
		 * Cette fonction vérifie une date
		 * @param string $date : La date a valider
		 * @param string $format : Le format de la date
		 * @return boolean : Vrai si la date et valide, faux sinon
		 */
		public static function validateDate($date, $format)
		{
			$objectDate = DateTime::createFromFormat($format, $date);
			return ($objectDate && $objectDate->format($format) == $date);
		}

		/**
		 * Cette fonction retourne un mot de passe généré aléatoirement
		 * @param int $length : Taille du mot de passe à générer
		 * @return string : Le mot de passe aléatoire
		 */
		public static function generatePassword($length)
		{
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-@()?.:!%*$&/';
			$password = '';
			$chars_length = mb_strlen($chars) - 1;
			$i = 0;
			while ($i < $length)
			{
				$i ++;
				$password .= $chars[rand(0, $chars_length)];
			}
			return $password;	
		}

		/**
		 * Cette fonction vérifie si un argument csrf a été fourni et est valide
		 * @param string $csrf : argument optionel, qui est la chaine csrf à vérifier. Si non fournie, la fonction cherchera à utiliser $_GET['csrf'] ou $_POST['csrf'].
		 * @return boolean : True si le csrf est valide. False sinon.
		 */
		public static function verifyCSRF($csrf = '')
		{
			if (!$csrf)
			{
				$csrf = isset($_GET['csrf']) ? $_GET['csrf'] : $csrf;
				$csrf = isset($_POST['csrf']) ? $_POST['csrf'] : $csrf;
			}

			if ($csrf == $_SESSION['csrf'])
			{
				return true;
			}
			
			return false;
		}

		/**
		 * Cette fonction converti un mesure de données vers une autre
		 * @param int $size : La mesure à convertir
		 * @param string $from : L'unité d'origine
		 * @param string $to : L'unité de sortie
		 * @return mixed : Si réussi le résultat de la conversion. Si raté, false
		 */
		public static function convertSize($size, $from, $to)
		{
			//On défini le tableau des unités
			$units = array(
				'o'  => 1,
				'ko' => 2,
				'mo' => 3,
				'go' => 4,
				'to' => 5,
			);

			//Si au moins une des unités fournies n'existe pas
			if (!(array_key_exists($from, $units) && array_key_exists($to, $units)))
			{
				return false;
			}

			//On calcul le nombre de tour à faire
			$i = $units[$from] - $units[$to];
			while ($i != 0)
			{
				if ($i > 0) //Si on doit passer par exemple de ko à o
				{
					$size = $size * 1024;
					$i--;
				}
				else //Si on doit passer par exemple de o a ko
				{
					$size = $size / 1024;
					$i++;
				}
			}
			
			return $size;	
		}

		/**
		 * Cette fonction génère des données de pagination
		 * @param int $nbResults : Nombre de résultat récupérés
		 * @param int $nbByPage : Le nombre de resultats à afficher par page
		 * @param int $page : Le numéro de page courant
		 * @param string $urlFixe : La partie fixe de l'url
		 * @return array : Un tableau de pagination comme voulu par le template pagination
		 */
		public static function generatePagination($nbResults, $nbByPage, $page, $urlFixe)
		{
			$nbPage = ceil($nbResults / $nbByPage);

			$pagination = array('current' => $page);
	
			if ($page > 1)
			{
				$pagination['first'] = $urlFixe;
			}

			if ($page < $nbPage)
			{
				$pagination['last'] = $urlFixe . $nbPage;
			}

			//On crée la pagination des pages avant et après celle en cours
			$pagination['before'] = array();
			$pagination['after'] = array();
			$i = 0;
			while ($i < 3)
			{
				$i ++;
				$pageBeforeNb = $page - $i;
				$pageAfterNb = $page + $i;

				if ($pageBeforeNb >= 1)
				{
					$pagination['before'][] = array(
						'url' => $urlFixe . $pageBeforeNb,
						'nb' => $pageBeforeNb,
					);
				}

				if ($pageAfterNb <= $nbPage)
				{
					$pagination['after'][] = array(
						'url' => $urlFixe . $pageAfterNb,
						'nb' => $pageAfterNb,
					);
				}
			}

			//On remet la pagination before dans le bon ordre pour l'affichage
			$pagination['before'] = array_reverse($pagination['before']);

			return $pagination;
		}

		/**
		 * Cette fonction retourne une adresse depuis une Latitude et une Longitude (on exploite openstreetmap)
		 * @param float $latitude : la latitude
		 * @param floaat $longitude : la longitude
		 * @return string : L'adresse sous forme de chaine
		 */
		public static function getAdressFromLatitudeAndLongitude ($latitude, $longitude)
		{
			$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . rawurlencode($latitude) . ',' . rawurlencode($longitude) . '&key=AIzaSyD93B70eg2gattBfd6QrSSL3aFQds01zbg';

			//On ouvre une ressource CURL pour récupérer une page
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
			//On recupère la page
			$json = curl_exec($curl);
			curl_close($curl); //On ferme CURL
			$fullAddress = json_decode($json, true);

			return (isset($fullAddress['results'][0]['formatted_address']) ? $fullAddress['results'][0]['formatted_address'] : 'Inconnue (Latitude : ' . $latitude . ' Longitude : ' . $longitude . ')');
		}

		/**
		 * Cette fonction retourne l'icone pour le statut d'un path
		 * @param int $pathStatus : Le status du path
		 * @return string : La chaine qui contiendra l'icone
		 */
		public static function getIconForPathStatus ($pathStatus)
		{
			switch (true)
			{
				case $pathStatus == internalConstants::$pathStatus['WAIT'] :
					return '<span class="fa fa-home"></span>';		
					break;
				case $pathStatus == internalConstants::$pathStatus['RUN'] :
					return '<span class="fa fa-road"></span>';		
					break;
				case $pathStatus == internalConstants::$pathStatus['BREAK'] :
					return '<span class="fa fa-cutlery"></span>';		
					break;
				case $pathStatus == internalConstants::$pathStatus['DOWN'] :
					return '<span class="fa fa-chain-broken"></span>';		
					break;
				case $pathStatus == internalConstants::$pathStatus['FIX'] :
					return '<span class="fa fa-wrench"></span>';		
					break;
				case $pathStatus == internalConstants::$pathStatus['END'] :
					return '<span class="fa fa-flag-checkered"></span>';		
					break;
			}
		}

		/**
		 * Cette fonction retourne l'icone pour le statut d'une intervention
		 * @param int $interventionStatus : Le status de l'intervention
		 * @return string : La chaine qui contiendra l'icone
		 */
		public static function getIconForInterventionStatus ($interventionStatus)
		{
			switch (true)
			{
				case $interventionStatus == internalConstants::$interventionStatus['WAIT'] :
					return '<span class="fa fa-clock-o"></span>';		
					break;
				case $interventionStatus == internalConstants::$interventionStatus['RUN'] :
					return '<span class="fa fa-wrench"></span>';		
					break;
				case $interventionStatus == internalConstants::$interventionStatus['END'] :
					return '<span class="fa fa-check"></span>';		
					break;
				case $interventionStatus == internalConstants::$interventionStatus['REFUSED'] :
					return '<span class="fa fa-times"></span>';		
					break;
			}
		}
	}
