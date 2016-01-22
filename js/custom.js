/**
 * Cette fonction permet de mettre à jour en ajax le bloc des camions qui roulent
 */
function ajaxRunningTrucks ()
{
	jQuery.get(HTTP_PWD + 'gestion/runningTrucks').success(function (data) {
		jQuery('body').find('#runningTrucksBlock').replaceWith(data);
	});
}

/**
 * Cette fonction permet de mettre à jour en ajax le bloc des camions qui sont en panne
 */
function ajaxBrokenTrucks ()
{
	jQuery.get(HTTP_PWD + 'gestion/brokenTrucks').success(function (data) {
		jQuery('body').find('#brokenTrucksBlock').replaceWith(data);
	});
}

/**
 * Cette fonction permet de mettre à jour en ajax le bloc des interventions en cours
 */
function ajaxCurrentInterventions ()
{
	jQuery.get(HTTP_PWD + 'gestion/currentInterventions').success(function (data) {
		jQuery('body').find('#currentInterventionsBlock').replaceWith(data);
	});
}

/**
 * Cette fonction permet d'appeler toutes les requetes ajax de rechargement des block
 */
function reloadAjaxBlocks ()
{
	ajaxRunningTrucks();
	ajaxBrokenTrucks();
	ajaxCurrentInterventions();
}

/**
 * Cette fonction initie toutes les requetes ajax
 */
function instanciateAjaxBlocks ()
{
	reloadAjaxBlocksInterval = setInterval(reloadAjaxBlocks, 15000);
}

/**
 * Cette fonction permet le clone d'un object
 */
function naiveShallowCopy( original )  
{
	var clone = {} ;

	var key ;

	for ( key in original )
	{
			clone[ key ] = original[ key ] ;
	}

	return clone ;
}
