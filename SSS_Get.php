<?php 
/** 
	V4 By sebcbien, 09/2017
	Thread here:
	https://www.domotique-fibaro.fr/topic/11097-yapuss-passerelle-universelle-surveillance-station/
	Thanks to all open sources examples grabbed all along the web who made this script possible

	examples of use:
 http://xxxxxx/get_snapshots/getV4.php?list=json - réponds avec le json de toutes les caméras
 http://xxxxxx/get_snapshots/getV4.php?list=camera - affiche la liste de toutes les caméras, infos screenshots etc
 http://xxxxxx/get_snapshots/getV4.php?camera=19&stream=1 - retourne le snapshot de la caméra N° 19, stream N°1
 0: Live stream | 1: Recording stream | 2: Mobile stream  - valeur par défaut: 0 
 http://xxxxxx/get_mjpeg/getV1.php?cam=14&format=mjpeg  - retourne le flux mjpeg pour la caméra 14
 */

// Configuration 
$user = "xxxxxxx";  // Synology username with rights to Surveillance station 
$pass = "xxxxxxx";  // Password of the user entered above 
$ip = "192.168.xxxxxx";  // IP-Adress of your Synology-NAS 
$port = "5000";  // default port of Surveillance Station 
$http = "http"; // Change to https if you use a secure connection 
$cameraID = $_GET['camera'];
$cameraStream = $_GET["stream"];
$list = $_GET["list"];
$vCamera = 7; //Version API SYNO.SurveillanceStation.Camera
$vAuth = ""; // 2; with 2, no images displayed, too fast logout problem ?  //Version de l' SYNO.API.Auth a utiliser

if ($cameraStream == NULL) { 
    $cameraStream = "0"; 
} 

//Get SYNO.API.Auth Path (recommended by Synology for further update)
	$json = file_get_contents($http.'://'.$ip.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.API.Auth');
	$obj = json_decode($json);
	$AuthPath = $obj->data->{'SYNO.API.Auth'}->path;
//echo $AuthPath;

// Authenticate with Synology Surveillance Station WebAPI and get our SID 
	$json = file_get_contents($http.'://'.$ip.':'.$port.'/webapi/'.$AuthPath.'?api=SYNO.API.Auth&method=Login&version=6&account='.$user.'&passwd='.$pass.'&session=SurveillanceStation&format=sid'); 
	$obj = json_decode($json); 

//Check if auth ok
if($obj->success != "true"){
	echo "error";
	exit();
}else{

//authentification successful
$sid = $obj->data->sid;
//echo '<p>'.$sid.'</p>';
//echo '<p>'.$obj.'</p>';

//Get SYNO.SurveillanceStation.Camera path (recommended by Synology for further update)
        $json = file_get_contents($http.'://'.$ip.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.SurveillanceStation.Camera');
        $obj = json_decode($json);
        $CamPath = $obj->data->{'SYNO.SurveillanceStation.Camera'}->path;
//print $CamPath;

// Get Snapshot
if ($cameraID != NULL) { 

// Setting the correct header so the PHP file will be recognised as a JPEG file 
	header('Content-Type: image/jpeg'); 
// Read the contents of the snapshot and output it directly without putting it in memory first 
	readfile($http.'://'.$ip.':'.$port.'/webapi/'.$CamPath.'?camStm='.$cameraStream.'&version='.$vCamera.'&cameraId='.$cameraID.'&api=SYNO.SurveillanceStation.Camera&preview=true&method=GetSnapshot&_sid='.$sid); 
}

//print $list;
//get Camera List
if ($list == "json") {
	echo "<p>Json camera list viewer</p>";
	echo "<p><a href=https://codebeautify.org/jsonviewer>https://codebeautify.org/jsonviewer</a></p>";
	https://codebeautify.org/jsonviewer

//list of known cams 
	$json = file_get_contents($http.'://'.$ip.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version='.$vCamera.'&method=List&_sid='.$sid);
	$obj = json_decode($json);
	echo $json;
}

if ($list == "camera") {
	echo "<p>camera list</p>";

//list of known cams 
	$json = file_get_contents($http.'://'.$ip.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version='.$vCamera.'&method=List&_sid='.$sid);
	$obj = json_decode($json);
foreach($obj->data->cameras as $cam){
	$id_cam = $cam->id;
	$nomCam = $cam->detailInfo->camName;
	$vendor = $cam->vendor;
	$model = $cam->model;
	echo "<p>-----------------------------------------------------------------------------------------</p>";
	echo "<p>Cam <b>". $nomCam ." (" . $id_cam . ")</b> detected</p>";
	echo "<p>Vendor <b>". $vendor ." Model:(" . $model . ")</b></p>";
	echo "<p> Snapshot on Stream Live: <a href=http://".$ip."/get_snapshots/getV4.php?camera=".$id_cam."&stream=0>http://".$ip."/get_snapshots/getV4.php?camera=".$id_cam."&stream=0</a></p>";
	echo "<p> Snapshot on Stream Recording on Syno: <a href=http://".$ip."/get_snapshots/getV4.php?camera=".$id_cam."&stream=1>http://".$ip."/get_snapshots/getV4.php?camera=".$id_cam."&stream=1</a></p>";
	echo "<p> Snapshot on Stream Mobile: <a href=http://".$ip."/get_snapshots/getV4.php?camera=".$id_cam."&stream=2>http://".$ip."/get_snapshots/getV4.php?camera=".$id_cam."&stream=2</a></p>";
	echo "<p> Stream MJPEG: <a href=http://".$ip."/get_mjpeg/getV1.php?cam=".$id_cam."&format=mjpeg>http://".$ip."/get_mjpeg/getV1.php?cam=".$id_cam."&format=mjpeg</a></p>";
	// http://diskstation412/get_mjpeg/getV1.php?cam=19&format=mjpeg
	//check if cam is connected
	if(!$cam->status) {
		//check if cam is activated and not recording
		if($cam->enabled && !$cam->recStatus) {
	//showing a snapshot of the camera
			echo "<img src='".$http."://".$ip.":".$port."/webapi/".$CamPath."?api=SYNO.SurveillanceStation.Camera&version=7&method=GetSnapshot&preview=true&camStm=1&cameraId=".$id_cam."&_sid=".$sid."' alt='image JPG' width='480' height='360'>";
		}
		else{
			echo "<p>Cam " . $id_cam . " RECORDING .... Should be skipped ?</p>";
			echo "<img src='".$http."://".$ip.":".$port."/webapi/".$CamPath."?api=SYNO.SurveillanceStation.Camera&version=7&method=GetSnapshot&preview=true&camStm=1&cameraId=".$id_cam."&_sid=".$sid."' alt='image JPG' width='480' height='360'>";
		}
	}
	else{
		echo "<p>Cam " . $id_cam . " deconnected</p>";
	}
}
}

//Get SYNO.API.Auth Path (recommended by Synology for further update)
	$json = file_get_contents($http.'://'.$ip.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.API.Auth');
	$obj = json_decode($json);
	$AuthPath = $obj->data->{'SYNO.API.Auth'}->path;

//Logout and destroying SID
	$json = file_get_contents($http."://".$ip.":".$port."/webapi/".$AuthPath."?api=SYNO.API.Auth&method=Logout&version=".$vAuth."&session=SurveillanceStation&_sid=".$sid);
	$obj = json_decode($json);
	}
?> 