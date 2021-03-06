<?php
	/*
		Ce fichier défini les constantes du MVC
	*/

	//On définit si on est en prod ou non
	define('ENV_PRODUCTION', true);

	//On définit les chemins
        define('PWD', '/var/www/soap-project/'); //On défini le chemin de base du site
	define('HTTP_PWD', (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost') . '/soap-project/'); //On défini l'adresse url du site

	define('PWD_IMG', PWD . 'img/'); //Chemin dossier des images
	define('HTTP_PWD_IMG', HTTP_PWD . 'img/'); //URL dossier des images

	define('PWD_CSS', PWD . 'css/'); //Chemin dossier des css
	define('HTTP_PWD_CSS', HTTP_PWD . 'css/'); //URL dossier des css

	define('PWD_JS', PWD . 'js/'); //Chemin dossier des js
	define('HTTP_PWD_JS', HTTP_PWD . 'js/'); //URL dossier des js

	define('PWD_CONTROLLER', PWD . 'controllers/'); //Dossier des controllers
	define('PWD_MODEL', PWD . 'model/'); //Dossier des models
	define('PWD_TEMPLATES', PWD . 'templates/'); //Dossier des templates
	define('PWD_MODULES', PWD . 'modules/'); //Dossier des modules
	define('PWD_CACHE', PWD . 'cache/'); //Dossier du cache


	//On défini les controlleurs et methodes par défaut
	define('DEFAULT_CONTROLLER', 'index'); //Nom controller appelé par défaut
	define('DEFAULT_METHOD', 'byDefault'); //Nom méthode appelée par défaut
	define('DEFAULT_BEFORE', 'before'); //Nom méthode before par défaut


	//Réglages du cache
	define('ACTIVATING_CACHE', true); //On desactive le cache

	//Réglages des identifiants de base de données
	define('DATABASE_HOST', 'localhost'); //Hote de la bdd
	define('DATABASE_NAME', 'soap-project'); //Nom de la bdd
	define('DATABASE_USER', 'root'); //Utilisateur de la bdd
	define('DATABASE_PASSWORD', ''); //Password de l'utilisateur

	//Réglages divers
	define('WEBSITE_TITLE', 'RestRoad'); //Le titre du site
	define('WEBSITE_DESCRIPTION', ''); //Description du site
	define('WEBSITE_KEYWORDS', ''); //Mots clefs du site
	define('WEBSITE_AUTHOR', 'GroupeGroupe'); //Auteur du site


	//On va inclure les constantes de tous les modules (dans l'ordre du dossier)
	foreach (scandir(PWD . 'mvc/modules-constants/') as $filename)
	{
		if ($filename == '.' || $filename == '..')
		{
			continue;
		}

		require_once(PWD . 'mvc/modules-constants/' . $filename);
	}
