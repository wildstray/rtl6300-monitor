var ltegeo = 'ltegeo.php';
var cpegeo = 'cpegeo.php';
var arccache = 'arccache.php';
var cpePoint = [45.0705, 7.6868];

$(document).ready(function () {

    var map = L.map('map');
    var defaultBaseMap = L.tileLayer.provider('OpenStreetMap.Mapnik')
    defaultBaseMap.addTo(map)
    var baseMaps = {
      'OpenStreetMap':  defaultBaseMap,
      'OpenTopoMap':  L.tileLayer.provider('OpenTopoMap'),
      'Esri WorldStreetMap':  L.tileLayer.provider('Esri.WorldStreetMap'),
      'Esri WorldTopoMap':  L.tileLayer.provider('Esri.WorldTopoMap'),
      'Esri WorldImagery':  L.tileLayer.provider('Esri.WorldImagery')
    };
    L.control.layers(baseMaps).addTo(map);
    var elevationOptions = {
            theme: "lightblue-theme",
            position: "bottomleft",
            height: 200,
            summary: "inline",
            downloadLink: false,
            time: false,
            legend: false,
            waypoints: false,
            closeBtn: false,
            hotline: false,
            almostOver: false,
            distanceMarkers: false,
        };
    var controlElevation = L.control.elevation(elevationOptions).addTo(map);
    var group = L.featureGroup().addTo(map);
    var markerArray = [];

    $.ajax({ 
        type: "GET",
        dataType: "json",
        url: cpegeo,
        success: function(data){
            cpePoint = data;
            var cpeIcon = L.IconMaterial.icon({
                icon: 'home',
                markerColor: 'rgba(255,6,6,0.5)',
            })
            var cpeMarker = L.marker(cpePoint, {icon: cpeIcon}).addTo(map).bindPopup('Home<br/>'+cpePoint);
            markerArray.push(cpeMarker);
//            drawMarkers();
            drawCell();
            setInterval(function() {
                drawCell();
            }, refresh);
        }
    });

    function getDistance(origin, destination) {
        var lon1 = origin[1]*Math.PI/180,
            lat1 = origin[0]*Math.PI/180,
            lon2 = destination[1]*Math.PI/180,
            lat2 = destination[0]*Math.PI/180;

        var a = Math.pow(Math.sin((lat2 - lat1)/ 2), 2) + Math.cos(lat1) * Math.cos(lat2) * Math.pow(Math.sin((lon2 - lon1)/2), 2);
        var c = 2 * Math.asin(Math.sqrt(a));
        var EARTH_RADIUS = 6371;
        return c * EARTH_RADIUS * 1000;
    }

    function drawMarkers() {
        group.clearLayers();
        markerArray.forEach(function(marker) {
            group.addLayer(marker);
        });
//        if (markerArray.length > 1) {
//            group.addLayer(L.polygon(markerArray.map(marker => [marker.getLatLng().lat, marker.getLatLng().lng])));
//        }
        map.fitBounds(group.getBounds());
    }

    function drawCell() {
        $.ajax({ 
            type: "GET",
            dataType: "json",
            url: lteurl+'lte/cellular_info',
            success: function(data){
                var mcc = data.Result.mcc;
                var mnc = data.Result.mnc;
                var tac = data.Result.tac;
                var enb = data.Result.enb;
                var type = data.Result.type;
                if (type == "5G_NSA" || type == "5G_SA") {
                    var btsIcon = L.IconMaterial.icon({
                        icon: '5g',
                        markerColor: 'rgba(6,6,255,0.5)',
                    });
                } else {
                    var btsIcon = L.IconMaterial.icon({
                        icon: '4g_mobiledata',
                        markerColor: 'rgba(6,255,6,0.5)',
                    });
                }
                $.ajax({ 
                    type: "GET",
                    dataType: "json",
                    url: ltegeo+'?mcc='+mcc+'&mnc='+mnc+'&enb='+enb,
                    success: function(data){
                        btsPoint = [data.location.coordinates[1],data.location.coordinates[0]];
                        var distance = getDistance(cpePoint, btsPoint).toFixed(2);
                        btsMarker = L.marker(btsPoint, {icon: btsIcon}).addTo(map).bindPopup('BTS ' + type + ' ' + enb + '<br>distance: ' + distance + 'm')
                        markerArray.push(btsMarker);
                        drawMarkers();
                        markerArray.pop();
                        drawProfile([[cpePoint[1],cpePoint[0]], [btsPoint[1],btsPoint[0]]]);
                    }
                });
            }
        });
    }

    function drawProfile(path) {
        var arcgis=arccache+'?https://elevation.arcgis.com/arcgis/rest/services/Tools/ElevationSync/GPServer/Profile/execute';
        var dataPoints = [];
        var ilf= {fields: [{name: "OID", type: "esriFieldTypeObjectID", alias: "OID"}], 
            geometryType:"esriGeometryPolyline",
            features: [{geometry:{paths:[path],spatialReference:{wkid:4326}},attributes:{OID:1}}],sr:{wkid:4326}};
        var data = {f:"geojson", "env:outSR":4326, InputLineFeatures: JSON.stringify(ilf), ProfileIDField: "OID", DEMResolution: "FINEST", MaximumSampleDistance: 10, MaximumSampleDistanceUnits:"Meters", returnZ: true, returnM: true};
        $.ajax({ 
            type: "POST",
            dataType: "json",
            data: data,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            url: encodeURI(arcgis).replace(/[!'()*\{\}]/g, function(c) {
                return '%' + c.charCodeAt(0).toString(16);
            }).replace(/%25/g, '%'),
            success: function(data){
                var geojson = {"name":"elevation.geojson","type":"FeatureCollection","features":[{"type":"Feature","geometry":{},"properties":null}]};
                geojson.features[0].geometry = {"type":"LineString","coordinates":data.results[0].value.features[0].geometry.coordinates};
                controlElevation.clear();
                controlElevation.load(JSON.stringify(geojson));
            }
        });
    };

});
