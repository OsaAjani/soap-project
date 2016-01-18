<?php
	/**
	 * Classe qui permet d'envoyer des SMS
	 */
	class internalSms extends Controller
	{

		const adminEmail = '';
		const adminPassword = '';
		const url = 'http://ajanibx.noip.me/RaspiSMS/smsAPI/';

		/**
		 * Cette classe permet d'envoyer un SMS à un numéro
		 * @param string $text : Le texte du SMS à envoyer
		 * @param string $number : Le numéro auquel envoyer le SMS
		 * @return boolean : Vrai si l'envoi à réussi, faux sinon
		 */
		public function sendSmsToNumber($text, $number)
		{
			//On ouvre une ressource CURL pour récupérer une page
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $this->url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			
			curl_setopt($curl, CURLOPT_POSTFIELDS, array(
				'email' => $this->adminEmail,
				'password' => $this->adminPassword,
				'numbers' => $number,
				'text' => $text,
			));


			//On recupère la page
			$json = curl_exec($curl);
		
			curl_close($curl); //On ferme CURL

			$json = json_decode($json);

			return !(boolean) $json->error;
		}
	}
