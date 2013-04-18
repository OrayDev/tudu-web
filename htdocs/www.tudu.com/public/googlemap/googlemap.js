var mapWidth = 512;
var mapHeight = 320;
var mapType;
var center_lat = 0;
var center_lng = 0;
var marker_lat = 0;
var marker_lng = 0;
var setZoom = 4;
var arrMapname = {'地图':'map','混合地图':'hybrid','卫星':'satellite','地形':'terrain'};

function initMap(zoom){setTimeout(function(){_initMap(zoom);},1);}
function _initMap(zoom)
{
	zoom = zoom ? zoom : 14;
	var address = $('#address').val();
	var map = new GMap2($('#mapArea')[0]);
	var geocoder = new GClientGeocoder();
	geocoder.getLatLng(address,function (point){      
				if (!point)alert(address + " 地址错误");
				else
				{       
					map.setCenter(point, zoom);
					map.addControl(new GScaleControl());

					map.enableDoubleClickZoom();
					map.enableScrollWheelZoom();
					map.setUIToDefault();

					var marker = new GMarker(point, {draggable: true});
					GEvent.addListener(marker, "dragend", function() {
						marker_lat = marker.getLatLng().lat(); 
						marker_lng = marker.getLatLng().lng(); 
						});
					map.addOverlay(marker);
				}
			});

	GEvent.addListener(map, "maptypechanged", function() {mapType = map.getCurrentMapType().getName();});
	GEvent.addListener(map, "moveend", function(){
		center_lat = map.getCenter().lat();
		center_lng = map.getCenter().lng();
		setZoom = map.getZoom();
	});
}
function pasteMap()
{
	if (marker_lat == 0) marker_lat = center_lat;
	if (marker_lng == 0) marker_lng = center_lng;
	callback("http://maps.google.com/maps/api/staticmap?center=" + center_lat + ',' + center_lng + "&zoom=" + setZoom + "&size=" + mapWidth + 'x' + mapHeight + "&maptype=" + arrMapname[mapType] + "&markers=" + marker_lat + ',' + marker_lng + "&sensor=false");
}
function pageInit()
{
	$('#address').keypress(function(ev){if(ev.which==13)initMap(10);});
	$('#mapsearch').click(function(){initMap(10);});
	$('#addMap').click(pasteMap);
}
$(pageInit);