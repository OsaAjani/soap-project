<?php
	/**
	 * Ce controller gère la page qui sert à retourne le script permettant de passer des valeurs de php à js
	 */
	class phptojs extends Controller
	{
		/**
		 * Cette fonction retourne le template qui fait la conversion
		 * @return void;
		 */	
		public function byDefault()
		{
			header('Content-Type: application/javascript');
			return $this->render('phptojs/default');
		}

	}
