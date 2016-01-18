<?php
	class internalConstants extends Controller
	{
		/**
		 * Liste des status d'un path
		 */
		public static $pathStatus = array(
			'WAIT' => 0,
			'RUN' => 1,
			'BREAK' => 2,
			'DOWN' => 3,
			'FIX' => 4,
			'END' => 5
		);
	}
