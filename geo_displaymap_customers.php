<?php


$Title = _('Geocoded Customers Report');

include ('includes/session.php');
include ('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

$SQL="SELECT * FROM geocode_param WHERE 1";
$ErrMsg = _('An error occurred in retrieving the currency information');
$Result = DB_query($SQL, $ErrMsg);
$MyRow = DB_fetch_array($Result);

$Api_Key = $MyRow['geocode_key'];
$Center_Long = $MyRow['center_long'];
$Center_Lat = $MyRow['center_lat'];
$Map_Height = $MyRow['map_height'];
$Map_Width = $MyRow['map_width'];
$Map_Host = $MyRow['map_host'];

?>

<style>
    html, body, #map-canvas {
        height: 100%;
        width: 100%;
        margin: 0px;
        padding: 0px
    }
</style>

<?php
echo '<script src="https://' . $Map_Host . '/maps/api/js?key=' . $Api_Key . '&sensor=false"';
echo ' type="text/javascript"></script>';?>
<script type="text/javascript">
//<![CDATA[


var customIcons = {
4: {
icon: '//labs.google.com/ridefinder/images/mm_20_blue.png'
},
5: {
icon: '//labs.google.com/ridefinder/images/mm_20_red.png'
}
};

function load() {
var map = new google.maps.Map(document.getElementById("map"), {
<?php echo 'center:new google.maps.LatLng(' . $Center_Lat . ', ' . $Center_Long . '),'; ?>
zoom: 4,
mapTypeId: 'roadmap'
});
var infoWindow = new google.maps.InfoWindow;

// Change this depending on the name of your PHP file
downloadUrl("geocode_genxml_customers.php", function(data) {
var xml = data.responseXML;
var markers = xml.documentElement.getElementsByTagName("marker");
for (var i = 0; i < markers.length; i++) {
var name = markers[i].getAttribute("name");
var address = markers[i].getAttribute("address");
var type = markers[i].getAttribute("type");
var point = new google.maps.LatLng(
parseFloat(markers[i].getAttribute("lat")),
parseFloat(markers[i].getAttribute("lng")));
var html = "<b>" + name + "</b> <br/>" + address;
var icon = '//labs.google.com/ridefinder/images/mm_20_blue.png' || {};
var marker = new google.maps.Marker({
map: map,
position: point,
icon: icon.icon
});
bindInfoWindow(marker, map, infoWindow, html);
}
});
}


function bindInfoWindow(marker, map, infoWindow, html) {
google.maps.event.addListener(marker, 'click', function() {
infoWindow.setContent(html);
infoWindow.open(map, marker);
});
}

function downloadUrl(url, callback) {
var request = window.ActiveXObject ?
new ActiveXObject('Microsoft.XMLHTTP') :
new XMLHttpRequest;

request.onreadystatechange = function() {
if (request.readyState == 4) {
request.onreadystatechange = doNothing;
callback(request, request.status);
}
};

request.open('GET', url, true);
request.send(null);
}

function doNothing() {}

//]]>

</script>
</head>

<body onload="load()" onunload="GUnload()">
    <p>
    <?php echo '<div class="centre" id="map" style="width: ' . $Map_Width . 'px; height: ' . $Map_Height . 'px"></div>'; ?>
    </p>
</body>
<?php
echo '<div class="centre"><a href="' . $RootPath . '/GeocodeSetup.php">' . _('Go to Geocode Setup') . '</a></div></p>';
include ('includes/footer.php');
?>
</html>