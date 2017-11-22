<?php 
/*
    V6 By sebcbien, 18/10/2017
    V6.1 by Jojo (19/10/2017) 	: server IP adress generated automatically
	V6.2 by sebcbien 			: added ptz placeholder
	V6.3 by Jojo (19/10/2017) 	: remove hardcoding of file name & location
	V6.4 by Jojo (20/10/2017) 	: links are opened in a new tab
	V7 by Sebcbien (21/10/2017) : Added enable/disable action
	                              Changed file Name (SSS_Get.php)
	V8 by Jojo (25/10/2017) 	: add start/stop recording action
							  	  & global review for alignments, comments, ...
	V9 by Jojo (20/11/2017) 	: takes screenshots and send them per e-mail as attachement
							      & actions for all cameras
							      & update menu
	V10 by sebcbien (22/11/2017): Added PTZ function, small bug fixes
								  & rearrange code for speed optimisation

	ToDo:
	 - accept array of cameras form url arguments
	 - PTZ action
	 - create external configuration file

	Istallation instructions :
	==========================
 	install php 7.0 on the Web server.
 	If you use Synology to send mails. you need to configure the notification in the control panel.
 	I share with you some strange behaviors.
 		1) When using GMAIL, even if the test mail notification was ok, this php mail was not send. => Solution is to re-do the autentication for Gmail in the notification Panel of the Synology. But after few hours / days it does not work anymore ...
 		2) So I tried Yahoo! as SMTP provider, but the delivery of mails tooks a long time (several seconds/minutes)
		3) I use now the SMTP of my mail provider : this is as fast as whith Gmail.

    Thread here:
    ============
    https://www.domotique-fibaro.fr/topic/11097-yapuss-passerelle-universelle-surveillance-station/
    Thanks to all open sources examples grabbed all along the web and specially filliboy who made this script possible.
 
 	Exemples:
 	==========
 http://xxxxxx/SSS_Get.php                                      - sans argument, réponds avec la liste de toutes les caméras
 http://xxxxxx/SSS_Get.php?list=json                            - répond avec le json de toutes les caméras
 http://xxxxxx/SSS_Get.php?list=camera                          - affiche la liste de toutes les caméras, infos screenshots etc
 http://xxxxxx/SSS_Get.php?stream_type=jpeg&camera=19&stream=1  - retourne le snapshot de la caméra N° 19, stream N°1
       0: Live stream | 1: Recording stream | 2: Mobile stream        - valeur par défaut: 0 
 http://xxxxxx/SSS_Get.php?action=enable&camera=14              - enable camera 14
 http://xxxxxx/SSS_Get.php?action=enable&camera=0               - enable ALL cameras
 http://xxxxxx/SSS_Get.php?action=enable                        - enable ALL cameras
 http://xxxxxx/SSS_Get.php?action=disable&camera=12             - disable camera 12
 http://xxxxxx/SSS_Get.php?action=disable&camera=0              - disable ALL cameras
 http://xxxxxx/SSS_Get.php?action=disable                       - disable ALL cameras
 http://xxxxxx/SSS_Get.php?action=start&camera=14               - start recording camera 14 (the camera is enabeled if disabeled)
 http://xxxxxx/SSS_Get.php?action=start&camera=0                - start recording ALL cameras (the desabled cameras are enabeled)
 http://xxxxxx/SSS_Get.php?action=start                         - start recording ALL cameras (the desabled cameras are enabeled)
 http://xxxxxx/SSS_Get.php?action=stop&camera=14                - stop recording camera 14
 http://xxxxxx/SSS_Get.php?action=stop&camera=0                 - stop recording ALL cameras
 http://xxxxxx/SSS_Get.php?action=stop                          - stop recording ALL cameras
 http://xxxxxx/SSS_Get.php?action=mail&camera=14                - send per mail screenshot of camera 14
 http://xxxxxx/SSS_Get.php?action=mail&camera=0                 - send per mail screenshot of ALL cameras
 http://xxxxxx/SSS_Get.php?action=mail                          - send per mail screenshot of ALL cameras
 http://xxxxxx/SSS_Get.php?ptz=5&camera=19                      - moves camera to PTZ position id 5
 http://xxxxxx/SSS_Get.php?stream_type=mjpeg&camera=19          - retourne le flux mjpeg pour la caméra 19
 for action=start & action=mail, adding the parameter '&enable=1' enable the disabled camera before the action.
*/

// Enter from this line your configuration
	// Surveillance Station Configuration 
		$user = "xxxxxx";  							// Synology username with rights to Surveillance station 
		$pass = "xxxxxx";  							// Password of the user entered above 
		$ip_ss = "192.168.xxx.xxx";  						// IP-Adress of Synology Surveillance Station
		$port = "5000";  								// default port of Surveillance Station 
		$http = "http"; 								// Change to https if you use a secure connection
		$vCamera = 7; 									// Version API SYNO.SurveillanceStation.Camera
		$vAuth = ""; 									// Version de l' SYNO.API.Auth a utiliser
														// 2; with 2, no images displayed, too fast logout problem ? 
	// e-mail configuration
		$from_mail = "xxxxxx"; 							// e-mail expediteur
		$from_name = "xxxxxx"; 							// nom expéditeur
		$reply_mail = "xxxxxx";							// e-mail réponse
		$reply_name = "xxxxxx"; 						// nom réponse
		$to_mail = "vxxxxxx";  							// e-mail destinataire
		$to_name = "xxxxxx"; 							// nom destinataire
		$subject = "Snapshot caméra";					// objet du mail
// Last line of your configuration. DO not change bellow.

// auto configuration
	$ip = $_SERVER['SERVER_ADDR']; 					// IP-Adress of your Web server hosting this script
	$file = $_SERVER['PHP_SELF'];  					// path & file name of this running php script

// URL parameters
$stream_type = $_GET['stream_type'];
$cameraID = $_GET['camera'];
$cameraStream = $_GET["stream"];
$cameraPtz = $_GET["ptz"];
$action = $_GET["action"];
$enable = $_GET["enable"];
$list = $_GET["list"];

// e-mail preparation
if ($action == "mail") {
	$mail_to = "\"$to_name\"<".$to_mail.">";
	$typepiecejointe = "image/jpeg";
	// Passage à la ligne : On filtre les serveurs qui rencontrent des bogues.		
		if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $mail_to)) { 
			$passage_ligne = "\r\n";
		} else {
			$passage_ligne = "\n";
		}
	//Génération du séparateur
		$boundary = md5(uniqid(time()));
	// Entête mail
		$entete = "From: \"$from_name\"<".$from_mail.">".$passage_ligne;
		$entete .= "Reply-to: \"$from_name\"<".$from_mail.">".$passage_ligne;
		$entete .= "MIME-Version: 1.0 ".$passage_ligne;
		$entete .= "Content-Type: multipart/mixed; boundary=\"$boundary\" ".$passage_ligne;
		$entete .= $passage_ligne;
	// Message : entête
		$message  = "--".$boundary.$passage_ligne;
		$message .= "Content-Type: text/html; charset=\"ISO-8859-1\" ".$passage_ligne;
		$message .= "Content-Transfer-Encoding: 8bit".$passage_ligne;
		$message .= $passage_ligne;
	// Message : texte
		$message .= "Bonjour,<br>Veuillez trouver ci-joint les screenshots demandés.";
		$message .= $passage_ligne;
}

// Default values
if ($cameraStream == NULL && $stream_type == NULL && $cameraID == NULL && $cameraPtz == NULL && $action == NULL && $list == NULL) { 
    $list = "camera"; 
} 

if ($cameraStream == NULL) { 
    $cameraStream = "0"; 
} 

if ($stream_type == NULL) { 
    $stream_type = "jpeg"; 
} 

if ($cameraID == NULL) {
	$cameraID = 0;
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
} else {
	//authentification successful
	$sid = $obj->data->sid;
	// echo $sid.'<br>';
	// echo $obj.'<br>';

	//Get SYNO.SurveillanceStation.Camera path (recommended by Synology for further update)
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.SurveillanceStation.Camera');
	$obj = json_decode($json);
	$CamPath = $obj->data->{'SYNO.SurveillanceStation.Camera'}->path;
	//echo $CamPath.'<br>';

	// Get Snapshot
	if ($cameraID != NULL && $stream_type == "jpeg" && $cameraPtz == NULL && $action == NULL) { 
		// Setting the correct header so the PHP file will be recognised as a JPEG file 
		header('Content-Type: image/jpeg'); 
		// Read the contents of the snapshot and output it directly without putting it in memory first 
		readfile($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?camStm='.$cameraStream.'&version='.$vCamera.'&cameraId='.$cameraID.'&api=SYNO.SurveillanceStation.Camera&preview=true&method=GetSnapshot&_sid='.$sid); 
		exit();
	}

	// Get Camera List
	if ($list == "json") {
		echo "Json camera list viewer. Copy the Json below and paste it in this viewer:<br>";
		echo "<a href=https://codebeautify.org/jsonviewer>https://codebeautify.org/jsonviewer</a><br>";
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version='.$vCamera.'&method=List&_sid='.$sid);
		// echo $list.'<br>';
		echo $json;
		exit();
	}

	if ($list == "camera") {
	//Get SYNO.SurveillanceStation.Ptz path (recommended by Synology for further update)
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.SurveillanceStation.PTZ');
	$obj = json_decode($json);
	$PtzPath = $obj->data->{'SYNO.SurveillanceStation.PTZ'}->path;
	
	//list & status of known cams 
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version='.$vCamera.'&method=List&_sid='.$sid);
	$obj = json_decode($json);
	$nbrCam = $obj->data->total;
		if ($nbrCam == 0) {
			echo "No camera defined";
			exit();
		} elseif ($nnrCam == 1) {
			echo "Camera list : <b>".$nbrCam."</b> camera<br>";
		} else {
			echo "Camera list : <b>".$nbrCam."</b> cameras<br>";
		}
		echo "Enable ALL cameras: <a href=http://".$ip.$file."?action=enable target='_blank'>http://".$ip.$file."?action=enable </a><br>";                   
		echo "Disable ALL cameras: <a href=http://".$ip.$file."?action=disable target='_blank'>http://".$ip.$file."?action=disable </a><br>";                   
		echo "Start recording ALL camera: <a href=http://".$ip.$file."?action=start target='_blank'>http://".$ip.$file."?action=start </a><br>";                   
		echo "Stop recording ALL cameras: <a href=http://".$ip.$file."?action=stop target='_blank'>http://".$ip.$file."?action=stop </a><br>";                   
		echo "Send screenshots per e-mail for ALL cameras: <a href=http://".$ip.$file."?action=mail target='_blank'>http://".$ip.$file."?action=mail </a><br>";
		//list of known cams 
		foreach($obj->data->cameras as $cam){
			$id_cam = $cam->id;
			$nomCam = $cam->detailInfo->camName;
			$vendor = $cam->vendor;
			$model = $cam->model;
			$ptzCap = $cam->ptzCap;

			echo "-----------------------------------------------------------------------------------------<br>";
			echo "Cam <b>".$nomCam." (".$id_cam.") ";
			if ($cam->enabled) {
				echo "enabled</b> --> <a href=http://".$ip.$file."?action=disable&camera=".$id_cam." target='_blank'>disable ?</a><br>";
			} else {
				echo "disabled</b> --> <a href=http://".$ip.$file."?action=enable&camera=".$id_cam." target='_blank'>enable ?</a><br>";				
			}
			echo "Vendor <b>".$vendor." Model:(".$model.")</b><br>";
			
			if ($ptzCap > 263) {
				echo "List of PTZ presets: <br>";
				$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$PtzPath.'?api=SYNO.SurveillanceStation.PTZ&method=ListPreset&version=1&cameraId='.$id_cam.'&_sid='.$sid);
				$listPtz = json_decode($json);
				foreach($listPtz->data->presets as $ptzId){
					echo "id: ".$ptzId->id." Name: ".$ptzId->name."  --  URL: "."<a href=http://".$ip.$file."?ptz=".$ptzId->id."&camera=".$id_cam." target='_blank'>http://".$ip.$file."?ptz=".$ptzId->id."&camera=".$id_cam."</a><br>";
					}
			}
			
			//check if cam is connected
			if (!$cam->status) {
				//check if cam is activated and recording
				if($cam->enabled) {
					echo "Display <b>snapshot</b> on Stream : <a href=http://".$ip.$file."?stream_type=jpeg&camera=".$id_cam."&stream=0 target='_blank'>Live</a> - <a href=http://".$ip.$file."?stream_type=jpeg&camera=".$id_cam."&stream=1 target='_blank'>Recording on Syno</a> - <a href=http://".$ip.$file."?stream_type=jpeg&camera=".$id_cam."&stream=2 target='_blank'>Mobile</a> -- Display <b>stream</b> : <a href=http://".$ip.$file."?stream_type=mjpeg&camera=".$id_cam." target='_blank'>MJPEG</a><br>";
					//check if cam is recording
					echo 'Recording status : ';
					if ($cam->recStatus) {
						echo "<b>recording...</b> --> <a href=http://".$ip.$file."?action=stop&camera=".$id_cam." target='_blank'>stop ?</a><br>";
					} else {
						echo "<b>NOT recording...</b> --> <a href=http://".$ip.$file."?action=start&camera=".$id_cam." target='_blank'>start ?</a><br>";
					}
					echo "Send screenshot per e-mail ? : <a href=http://".$ip.$file."?action=mail&camera=".$id_cam." target='_blank'>http://".$ip.$file."?action=mail&camera=".$id_cam."</a><br>";
					//showing a snapshot of the camera
					echo "<img src='".$http."://".$ip_ss.":".$port."/webapi/".$CamPath."?api=SYNO.SurveillanceStation.Camera&version=".$vCamera."&method=GetSnapshot&preview=true&camStm=1&cameraId=".$id_cam."&_sid=".$sid."' alt='image JPG' width='480' height='360'><br>";
				}
			}
		}
		exit();
	}

	if ($cameraPtz != NULL) {
	//Get SYNO.SurveillanceStation.Ptz path (recommended by Synology for further update)
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.SurveillanceStation.PTZ');
		$obj = json_decode($json);
		$PtzPath = $obj->data->{'SYNO.SurveillanceStation.PTZ'}->path;
		//echo $PtzPath.'<br>';
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$PtzPath.'?api=SYNO.SurveillanceStation.PTZ&method=GoPreset&version=1&cameraId='.$cameraID.'&presetId='.$cameraPtz.'&_sid='.$sid);
		echo $json;
	exit();
	}

	if ($action != NULL) {
		foreach($obj->data->cameras as $cam){
			$id_cam = $cam->id;
			$nomCam = $cam->detailInfo->camName;
			$enabled_cam = $cam->enabled;
			// check if ALL Cameras
			if ($cameraID == 0) {
				$camID = $id_cam;
			} else {
				$camID = $cameraID;
			}
			if ($action == "disable" && $id_cam == $camID) {
				//if cam already Disabled
				if(!$enabled_cam) {
				// if(!$cam->enabled) {
					echo 'Camera SS id : '.$id_cam.'<br>';
					echo 'Camera SS Name : '.$nomCam.'<br>';
					echo 'Status : Camera already Disabled <br>';
					echo "-----------------------------------------------------------------------------------------<br>";
				} else {
				//Deactivate cam
				$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&method=Disable&version='.$vCamera.'&cameraIds='.$camID.'&_sid='.$sid);
				echo 'Camera SS id : '.$id_cam.'<br>';
				echo 'Camera SS Name : '.$nomCam.'<br>';
				echo 'Status : Camera Disabled <br>';
				echo "-----------------------------------------------------------------------------------------<br>";
				}
			} else if ($action == "enable" && $id_cam == $camID) {
				//if cam already Enabled
				if($enabled_cam) {
					echo 'Camera SS id : '.$id_cam.'<br>';
					echo 'Camera SS Name : '.$nomCam.'<br>';
					echo 'Status : Camera already Enabled <br>';
					echo "-----------------------------------------------------------------------------------------<br>";
				} else {
				//Activate cam
				$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&method=Enable&version='.$vCamera.'&cameraIds='.$camID.'&_sid='.$sid);
				echo 'Camera SS id : '.$id_cam.'<br>';
				echo 'Camera SS Name : '.$nomCam.'<br>';
				echo 'Status : Camera Enabled <br>';
				echo "-----------------------------------------------------------------------------------------<br>";
				}
			} else if (($action == "start" or $action == "stop") && $id_cam == $camID) {
				echo 'Camera SS id : '.$id_cam.'<br>';
				echo 'Camera SS Name : '.$nomCam.'<br>';
				//if cam Disabled
				if (!$enabled_cam and $action == "start" and $enable == 1) {
					echo 'Status : Camera is Disabled => Enabeling <br>';
					$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&method=Enable&version='.$vCamera.'&cameraIds='.$camID.'&_sid='.$sid);
					sleep (5);  // wait 5 sec to finalyse the enabeling process
					$enabled_cam = true;
					echo 'Status : Camera Enabled <br>';
				}
				if ($enabled_cam) {
					// start or stop recording cam
					$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.ExternalRecording&method=Record&version=1&action='.$action.'&cameraId='.$camID.'&_sid='.$sid);
					echo 'Status : '.$action.' recording <br>';
				} else {
					echo 'Status : Camera is Disabled => no recording action <br>';
				}
				echo "-----------------------------------------------------------------------------------------<br>";

			} else if ($action == "mail" && $id_cam == $camID) {
				//if cam Disabled
				if (!$enabled_cam and $enable == 1) {
					echo 'Status : Camera '.$id_cam.' - '.$nomCam.' is Disabled => Enabeling <br>';
					$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&method=Enable&version='.$vCamera.'&cameraIds='.$camID.'&_sid='.$sid);
					sleep (5);  // wait 5 sec to finalyse the enabeling process
					$enabled_cam = true;
					echo 'Status : Camera '.$id_cam.' - '.$nomCam.' Enabled <br>';
				}
				if ($enabled_cam) {
					$file_displayName = "Camera_".$nomCam."_".strftime("%Y%m%d_%H%M%S");
					$url = $http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?camStm='.$cameraStream.'&version='.$vCamera.'&cameraId='.$camID.'&api=SYNO.SurveillanceStation.Camera&method=GetSnapshot&_sid='.$sid; 
					$data = chunk_split (base64_encode(file_get_contents($url)));
					$message .= "--".$boundary.$passage_ligne;
					$message .= "Content-Type: $typepiecejointe; name=\"$file_displayName\" ".$passage_ligne;
					$message .= "Content-Transfer-Encoding: base64 ".$passage_ligne;
					$message .= "Content-Disposition: attachment; filename=\"$file_displayName\" ".$passage_ligne;
					$message .= $passage_ligne;
					$message .= $data.$passage_ligne;
					$message .= $passage_ligne;
					echo "mail préparé pour caméraID: ".$id_cam."<br>";
				}
			}
		}
    	// on ferme la page qui vient d'être génrée
    	echo "<script>window.close();</script>";
	}

	if ($action == "mail") {
		// Fin du mail et Envoi
		$message .= "--".$boundary."--";
		mail($mail_to, $subject, $message, $entete);
		echo "<br><b>Mail envoyé. </b><br>";
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

}

?>
