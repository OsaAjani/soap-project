var map;
var INTERVAL = 10000;
map = new google.maps.Map(document.getElementById('map'), {
	center: {lat: 46.768196, lng: 2.4326639},
	zoom: 7
});

var markerStore = {};
function getMarkers()
{
	$.get(HTTP_PWD + 'gestion/getTrucksLastPositions', {}, function(res,resp) {
		for(var i=0, len=res.length; i<len; i++) {

			//marker already exist, move it
			if (markerStore.hasOwnProperty(res[i].matriculation))
			{
				var newPosition = new google.maps.LatLng(res[i].latitude, res[i].longitude);
				markerStore[res[i].matriculation].setPosition(newPosition);
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
		
		window.setTimeout(getMarkers, INTERVAL);
	}, "json");
}

getMarkers();
