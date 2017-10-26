<?php 
/** 
    V6 By sebcbien, 18/10/2017
    V6.1 by Jojo (19/10/2017) : server IP adress generated automatically
	V6.2 by sebcbien added ptz placeholder
	V6.3 by Jojo (19/10/2017) : remove hardcoding of file name & location
	V6.4 by Jojo (20/10/2017) : links are opened in a new tab
	V7 by Sebcbien (21/10/2017) : Added enable/disable action
	                              Changed file Name (SSS_Get.php)
	V8 by Jojo (25/10/2017) : add start/stop recording action
				& global review for alignments, comments, ...

	ToDo:
	 - accept array of cameras form url arguments
	 - PTZ action
	 
    Thread here:
    https://www.domotique-fibaro.fr/topic/11097-yapuss-passerelle-universelle-surveillance-station/
    Thanks to all open sources examples grabbed all along the web and specially filliboy who made this script possible.
    exemples:
 http://xxxxxx/SSS_Get.php - sans argument, réponds avec la liste de toutes les caméras
 http://xxxxxx/SSS_Get.php?list=json - réponds avec le json de toutes les caméras
 http://xxxxxx/SSS_Get.php?list=camera - affiche la liste de toutes les caméras, infos screenshots etc
 http://xxxxxx/SSS_Get.php?stream_type=jpeg&camera=19&stream=1 - retourne le snapshot de la caméra N° 19, stream N°1
 0: Live stream | 1: Recording stream | 2: Mobile stream  - valeur par défaut: 0 
 http://xxxxxx/SSS_Get.php?action=enable&camera=14 - enable camera 14
 http://xxxxxx/SSS_Get.php?action=disable&camera=12 - disable camera 12
 http://xxxxxx/SSS_Get.php?action=start&camera=14 - start recording camera 14 (the camera is enabeled if disabeled)
 http://xxxxxx/SSS_Get.php?action=stop&camera=14 - stop recording camera 14 (the camera is enabeled if disabeled)
 http://xxxxxx/SSS_Get.php?stream_type=mjpeg&camera=19 - retourne le flux mjpeg pour la caméra 19
 */

// Configuration 
$user = "xxxxxxxx";  // Synology username with rights to Surveillance station 
$pass = "xxxxxxxx";  // Password of the user entered above 
$ip_ss = "192.168.xxx.xxx";  // IP-Adress of Synology Surveillance Station
$ip = $_SERVER['SERVER_ADDR']; // IP-Adress of your Web server hosting this script
$file = $_SERVER['PHP_SELF'];  // path& file name of this running php script
$port = "5000";  // default port of Surveillance Station 
$http = "http"; // Change to https if you use a secure connection
$vCamera = 7; //Version API SYNO.SurveillanceStation.Camera
$vAuth = ""; // 2; with 2, no images displayed, too fast logout problem ?  //Version de l' SYNO.API.Auth a utiliser

// URL parameters
$stream_type = $_GET['stream_type'];
$cameraID = $_GET['camera'];
$cameraStream = $_GET["stream"];
$cameraPtz = $_GET["ptz"];
$action = $_GET["action"];
$list = $_GET["list"];

if ($cameraStream == NULL && $stream_type == NULL && $cameraID == NULL && $cameraPtz == NULL && $action == NULL) { 
    $list = "camera"; 
} 

if ($cameraStream == NULL) { 
    $cameraStream = "0"; 
} 

if ($stream_type == NULL) { 
    $stream_type = "jpeg"; 
} 
//Get SYNO.API.Auth Path (recommended by Synology for further update)
$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.API.Auth');
$obj = json_decode($json);
$AuthPath = $obj->data->{'SYNO.API.Auth'}->path;
//echo $AuthPath;

// Authenticate with Synology Surveillance Station WebAPI and get our SID 
$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$AuthPath.'?api=SYNO.API.Auth&method=Login&version=6&account='.$user.'&passwd='.$pass.'&session=SurveillanceStation&format=sid'); 
$obj = json_decode($json); 

//Check if auth ok
if($obj->success != "true"){
	echo "error";
	exit();
}else{

	//authentification successful
	$sid = $obj->data->sid;
	// echo $sid.'<br>';
	// echo $obj.'<br>';

	//Get SYNO.SurveillanceStation.Camera path (recommended by Synology for further update)
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.SurveillanceStation.Camera');
	$obj = json_decode($json);
	$CamPath = $obj->data->{'SYNO.SurveillanceStation.Camera'}->path;
	// echo $CamPath.'<br>';

	// Get Snapshot
	if ($cameraID != NULL && $stream_type == "jpeg" && $cameraPtz == NULL && $action == NULL) { 
		// Setting the correct header so the PHP file will be recognised as a JPEG file 
		header('Content-Type: image/jpeg'); 
		// Read the contents of the snapshot and output it directly without putting it in memory first 
		readfile($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?camStm='.$cameraStream.'&version='.$vCamera.'&cameraId='.$cameraID.'&api=SYNO.SurveillanceStation.Camera&preview=true&method=GetSnapshot&_sid='.$sid); 
		exit();
	}

	// echo $list.'<br>';
	// Get Camera List
	if ($list == "json") {
		echo "Json camera list viewer<br>";
		echo "<a href=https://codebeautify.org/jsonviewer>https://codebeautify.org/jsonviewer</a><br>";
		https://codebeautify.org/jsonviewer

		//list of known cams 
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version='.$vCamera.'&method=List&_sid='.$sid);
		$obj = json_decode($json);
		echo $json;
		exit();
	}

	if ($list == "camera") {
		echo "Camera list <br>";

		//list of known cams 
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version='.$vCamera.'&method=List&_sid='.$sid);
		$obj = json_decode($json);
		foreach($obj->data->cameras as $cam){
			$id_cam = $cam->id;
			$nomCam = $cam->detailInfo->camName;
			$vendor = $cam->vendor;
			$model = $cam->model;
			echo "-----------------------------------------------------------------------------------------<br>";
			echo "Cam <b>". $nomCam ." (" . $id_cam . ")</b> detected<br>";
			echo "Vendor <b>". $vendor ." Model:(" . $model . ")</b><br>";
			echo "Snapshot on Stream Live: <a href=http://".$ip.$file."?stream_type=jpeg&camera=".$id_cam."&stream=0 target='_blank'>http://".$ip.$file."?stream_type=jpeg&camera=".$id_cam."&stream=0</a><br>";
			echo "Snapshot on Stream Recording on Syno: <a href=http://".$ip.$file."?stream_type=jpeg&camera=".$id_cam."&stream=1 target='_blank'>http://".$ip.$file."?stream_type=jpeg&camera=".$id_cam."&stream=1</a><br>";
			echo "Snapshot on Stream Mobile: <a href=http://".$ip.$file."?stream_type=jpeg&camera=".$id_cam."&stream=2 target='_blank'>http://".$ip.$file."?stream_type=jpeg&camera=".$id_cam."&stream=2</a><br>";
			echo "Stream MJPEG: <a href=http://".$ip.$file."?stream_type=mjpeg&camera=".$id_cam." target='_blank'>http://".$ip.$file."?stream_type=mjpeg&camera=".$id_cam."</a><br>";
			// http://diskstation412/get_mjpeg/getV1.php?cam=19&format=mjpeg
			//check if cam is connected
			if(!$cam->status) {
				//check if cam is activated and not recording
				if($cam->enabled && !$cam->recStatus) {
					//showing a snapshot of the camera
					echo "<img src='".$http."://".$ip_ss.":".$port."/webapi/".$CamPath."?api=SYNO.SurveillanceStation.Camera&version=7&method=GetSnapshot&preview=true&camStm=1&cameraId=".$id_cam."&_sid=".$sid."' alt='image JPG' width='480' height='360'>";
				}
				else{
					echo "Cam " . $id_cam . " RECORDING .... Should be skipped ?<br>";
					echo "<img src='".$http."://".$ip_ss.":".$port."/webapi/".$CamPath."?api=SYNO.SurveillanceStation.Camera&version=7&method=GetSnapshot&preview=true&camStm=1&cameraId=".$id_cam."&_sid=".$sid."' alt='image JPG' width='480' height='360'>";
				}
			}else{
				echo "Cam " . $id_cam . " deconnected<br>";
			}
		}
		exit();
	}

	if ($cameraPtz != NULL) {
		echo "Camera PTZ argument: ".$cameraPtz."  --  Camera id: ".$cameraID;
		exit();
	}

	if ($action != NULL) {
		//list of known cams 
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version=3&method=List&_sid='.$sid);
		$obj = json_decode($json);

		foreach($obj->data->cameras as $cam){
			$id_cam = $cam->id;
			$nomCam = $cam->detailInfo->camName;
			//check if cam is activated or not
			if($action == "disable" && $id_cam == $cameraID) {
				//if cam already Disabled
				if(!$cam->enabled) {
					echo 'Camera SS id : '.$id_cam.'<br>';
					echo 'Camera SS Name : '.$nomCam.'<br>';
					echo 'Status : Camera already Disabled';
					exit();
				}
				//Deactivate cam
				$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&method=Disable&version=3&cameraIds='.$cameraID.'&_sid='.$sid);
				echo 'Camera SS id : '.$id_cam.'<br>';
				echo 'Camera SS Name : '.$nomCam.'<br>';
				echo 'Status : Camera Disabled';
				exit();
			}else if($action == "enable" && $id_cam == $cameraID) {
				//if cam already Enabled
				if($cam->enabled) {
					echo 'Camera SS id : '.$id_cam.'<br>';
					echo 'Camera SS Name : '.$nomCam.'<br>';
					echo 'Status : Camera already Enabled';
					exit();
				}
				//Activate cam
				$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&method=Enable&version=3&cameraIds='.$cameraID.'&_sid='.$sid);
				echo 'Camera SS id : '.$id_cam.'<br>';
				echo 'Camera SS Name : '.$nomCam.'<br>';
				echo 'Status : Camera Enabled';
				exit();
			}else if(($action == "start" or $action == "stop") && $id_cam == $cameraID) {
				echo 'Camera SS id : '.$id_cam.'<br>';
				echo 'Camera SS Name : '.$nomCam.'<br>';
				//if cam Disabled
				if(!$cam->enabled) {
					echo 'Status : Camera is Disabled => Enabeling <br>';
					$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&method=Enable&version=3&cameraIds='.$cameraID.'&_sid='.$sid);
					sleep (15);  // wait 15 sec to finalyse the enabeling process
					echo 'Status : Camera Enabled <br>';
				}
				// start or stop recording cam
				$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.ExternalRecording&method=Record&version=1&action='.$action.'&cameraId='.$cameraID.'&_sid='.$sid);
				echo 'Status : '.$action.' recording';
				exit();
			}
		}
	}

	// Get MJPEG
	if ($cameraID != NULL && $stream_type == "mjpeg") {
		$link_stream = 'http://' . $ip_ss . ':' . $port . '/webapi/SurveillanceStation/videoStreaming.cgi?api=SYNO.SurveillanceStation.VideoStream&version=1&method=Stream&cameraId=' . $cameraID . '&format=mjpeg&_sid=' . $sid;

		set_time_limit ( 60 ); 

		$r = ""; 
		$i = 0; 
		$boundary = "\n--myboundary"; 
		$new_boundary = "newboundary"; 

		$f = fopen ( $link_stream, "r" ); 

		if (! $f) { 
			// **** cannot open 
			echo "error <br>"; 
		} else { 
			// **** URL OK 
			header ( "Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0" ); 
			header ( "Cache-Control: private" ); 
			header ( "Pragma: no-cache" ); 
			header ( "Expires: -1" ); 
			header ( "Content-type: multipart/x-mixed-replace;boundary={$new_boundary}" ); 

			while ( true ) { 

				while ( substr_count ( $r, "Content-Length:" ) != 2 ) { 
					$r .= fread ( $f, 32 ); 
				} 

				$pattern = "/Content-Length\:\s([0-9]+)\s\n(.+)/i"; 
				preg_match ( $pattern, $r, $matches, PREG_OFFSET_CAPTURE ); 
				$start = $matches [2] [1]; 
				$len = $matches [1] [0]; 
				$end = strpos ( $r, $boundary, $start ) - 1; 
				$frame = substr ( "$r", $start + 2, $len ); 

				print "--{$new_boundary}\n"; 
				print "Content-type: image/jpeg\n"; 
				print "Content-Length: ${len}\n\n"; 
				print $frame; 
				usleep ( 40 * 1000 ); 
				$r = substr ( "$r", $start + 2 + $len ); 
			} 
		} 
		fclose ( $f ); 
	}
/* needed ???
	//Get SYNO.API.Auth Path (recommended by Synology for further update)
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.API.Auth');
		$obj = json_decode($json);
		$AuthPath = $obj->data->{'SYNO.API.Auth'}->path;

	//Logout and destroying SID
		$json = file_get_contents($http."://".$ip_ss.":".$port."/webapi/".$AuthPath."?api=SYNO.API.Auth&method=Logout&version=".$vAuth."&session=SurveillanceStation&_sid=".$sid);
		$obj = json_decode($json);
*/
}

?>
