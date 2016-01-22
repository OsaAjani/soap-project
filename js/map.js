var map;
var INTERVAL = 10000;
map = new google.maps.Map(document.getElementById('map'), {
	center: {lat: 46.768196, lng: 2.4326639},
	zoom: 7,
	scrollwheel: false
});

var markerStore = {};
var missingMarkers;
function getMarkers()
{
	$.get(HTTP_PWD + 'gestion/getTrucksLastPositions', {}, function(res,resp) {
		missingMarkers = naiveShallowCopy(markerStore);
		for(var i=0, len=res.length; i<len; i++) {
			//marker already exist, move it
			if (markerStore.hasOwnProperty(res[i].matriculation))
			{
				var newPosition = new google.maps.LatLng(res[i].latitude, res[i].longitude);
				markerStore[res[i].matriculation].setPosition(newPosition);
				delete missingMarkers[res[i].matriculation];
			}
			else //marker not exist, create it
			{
				var marker = new google.maps.Marker({
					position: new google.maps.LatLng(res[i].latitude,res[i].longitude),
					title:res[i].matriculation,
					map:map
				});
				markerStore[res[i].matriculation] = marker;
			}
		}
		
		for (var property in missingMarkers)
		{
			if (missingMarkers.hasOwnProperty(property))
			{
				markerStore[property].setMap(null);
				delete markerStore[property];
			}
		}

		window.setTimeout(getMarkers, INTERVAL);
	}, "json");
}

getMarkers();
