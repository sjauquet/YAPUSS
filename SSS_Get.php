<?php 
//session_start();
/*
# Version History :
```
V6   by sebcbien, 18/10/2017
V6.1 by Jojo (19/10/2017):	server IP adress generated automatically
V6.2 by sebcbien:			added ptz placeholder
V6.3 by Jojo (19/10/2017):	remove hardcoding of file name & location
V6.4 by Jojo (20/10/2017):	links are opened in a new tab
V7   by Sebcbien (21/10/2017):	Added enable/disable action
								Changed file Name (SSS_Get.php)
V8   by Jojo (25/10/2017):	add start/stop recording action
				& global review for alignments, comments, ...
V9   by Jojo (20/11/2017):	takes screenshots and send them per e-mail as attachement
					  & actions for all cameras
					  & update menu
V10   by seb (22/11/2017)  :	Added PTZ function, small bug fixes & rearrange code for speed optimisation
V10.1 by seb (25/11/2017)  :	correction bug actions.
v10.2 by Jojo (25/11/2017) :	correction bug list PTZ ids & code optimization (use function)
v11   by Jojo (23/12/2017) :	get configuration from external file
v11.1 by Jojo (22/06/2018) :	add TimeStamps for display in logs
v12   by Jojo (14/09/2018) :	add possibility to personnalize subject of the e-amil
v13   by seb (16/09/2018) :	add elapsed time counter for debug purposes
				solved bug ini file not parsed when YAPUSS script is not in the root folder
v14   by seb (18/09/2018) :	added method to re-use the SID between API calls
				added method to clear SID (action=ClearSID)
v15   by seb (19/09/2018) :	added method to write all available snapshots to disk (list=AllSnapshots)
				resolved bug about snapshot quality not working (see more info in .ini file)
				Cosmetic Work
v16   by seb (21/09/2018) :	keep the same SID for all sessions. Script is now WAYYYY Faster and les load on Surveillance Station
V16.1 by seb (22/09/2018) : 	added auto creation of SessionFile.txt if not present
				check if getsnapshot failed, and then reset SID t oget a new one
				cleaned up the code
ToDo:
 - accept array of cameras form url arguments
 - find a quicker test to check if api access is ok (retreiving json of cameras takes 0,5 second)
 - Clean up code with faster check with PTZ at the beginning
 - Force refresh of snapshot on demo page
```
# Installation instructions :
```php
Install php 7.0 on the Web server.
Save this file with extension .php (example : SSS_Get.php)
In the same folder, create the .ini file with the SAME name (except the extension) as this scirpt file (example : SSS_Get.ini)

If you use Synology to send mails. you need to configure the notification in the control panel.
I share with you some strange behaviors.
	1) When using GMAIL, even if the test mail notification was ok, this php mail was not send. => Solution is to re-do the autentication for Gmail in the notification Panel of the Synology. But after few hours / days it does not work anymore ...
	2) So I tried Yahoo! as SMTP provider, but the delivery of mails tooks a long time (several seconds/minutes)
	3) I use now the SMTP of my mail provider : this is as fast as whith Gmail.

```
# Thread here :
```php
https://www.domotique-fibaro.fr/topic/11097-yapuss-passerelle-universelle-surveillance-station/
Thanks to all open sources examples grabbed all along the web and specially filliboy who made this script possible.

```
# Some Examples :
```php
- Main functions: Get Snapshot:
http://xxxxxx/SSS_Get.php?stream_type=jpeg&camera=19&snapQual=0  - will returns a snapshot of camera Nr 19, High Quality
	Select Snapshot quality: snapQual: 0: High Quality | 0: High Quality |2: Low Quality (if available) default is set in .ini: profileType
http://xxxxxx/SSS_Get.php?action=saveSnapshot&camera=19&snapQual=0  - will write on the server disk, (where the SSS_Get.php is stored) a snapshot of camera Nr 19, High Quality
	Select Snapshot quality: snapQual: 0: High Quality | 0: High Quality |2: Low Quality (if available) default is set in .ini: profileType

- Main functions: Get Mjpeg:
http://xxxxxx/SSS_Get.php?stream_type=mjpeg&camera=19          - Returns a mjpeg stream of camera 19

- Main function: Generate and write all available snapshots to disk (High Quality)
http://xxxxxx/SSS_Get.php?action=AllSnapshots&snapQual=0
- Main function: Generate and write all available snapshots to disk (High Quality) and return one snapshot of a selected camera:
		Typical use: ask this urls with one display . It will then act as scheduler. Then grab the writen image on disk with the other displays.
http://xxxxxx/SSS_Get.php?action=AllSnapshots&snapQual=0&camera=1 
	Medium Quality:
http://xxxxxx/SSS_Get.php?action=AllSnapshots&snapQual=1&camera=2.

Send one image (of camera 6 in this example) to client who requested to write all snapshots to disk (SSS_Get.php?action=AllSnapshots&snapQual=0&camera=6)

Debug:
http://xxxxxx/SSS_Get.php?action=?action=ResetSID                      - Will force regenerate a new sid. Should not be needed, an SID stays untill a reboot of the synology

Help function:
http://xxxxxx/SSS_Get.php                                      - Returns the list of all cameras with a snapshot, status, urls etc.
http://xxxxxx/SSS_Get.php?list=json                            - Returns a json with all cameras
http://xxxxxx/SSS_Get.php?list=camera                          - Returns the list of all cameras with a snapshot, status, urls etc.

Other functions:
- Enable/Disable Camera
http://xxxxxx/SSS_Get.php?action=enable&camera=14              - enable camera 14
http://xxxxxx/SSS_Get.php?action=enable&camera=0               - enable ALL cameras
http://xxxxxx/SSS_Get.php?action=enable                        - enable ALL cameras
http://xxxxxx/SSS_Get.php?action=disable&camera=12             - disable camera 12
http://xxxxxx/SSS_Get.php?action=disable&camera=0              - disable ALL cameras
http://xxxxxx/SSS_Get.php?action=disable                       - disable ALL cameras
- Start/Stop recording
http://xxxxxx/SSS_Get.php?action=start&camera=14               - start recording camera 14 (the camera is enabeled if disabeled)
http://xxxxxx/SSS_Get.php?action=start&camera=0                - start recording ALL cameras (the desabled cameras are enabeled)
http://xxxxxx/SSS_Get.php?action=start                         - start recording ALL cameras (the desabled cameras are enabeled)
http://xxxxxx/SSS_Get.php?action=stop&camera=14                - stop recording camera 14
http://xxxxxx/SSS_Get.php?action=stop&camera=0                 - stop recording ALL cameras
http://xxxxxx/SSS_Get.php?action=stop                          - stop recording ALL cameras
- Send Snapshot by Email
http://xxxxxx/SSS_Get.php?action=mail&camera=14                - send per mail snapshot of camera 14
http://xxxxxx/SSS_Get.php?action=mail&camera=0                 - send per mail snapshot of ALL cameras
http://xxxxxx/SSS_Get.php?action=mail                          - send per mail snapshot of ALL cameras
http://xxxxxx/SSS_Get.php?action=mail&subject=non default      - send per mail snapshot of ALL cameras with the non default subject on the mail
- PTZ function
http://xxxxxx/SSS_Get.php?ptz=5&camera=19                      - moves camera to PTZ position id 5
for action=start & action=mail, adding the parameter '&enable=1' enable the disabled camera before the action.
```
*/

// from .ini file (.ini file mut have the same name as the running script)
$ini_array = parse_ini_file(substr(basename($_SERVER['SCRIPT_NAME']).PHP_EOL, 0, -4)."ini");
$user = $ini_array[user];
$pass = $ini_array[pass];
$ip_ss = $ini_array[ip_ss];
$port = $ini_array[port];
$http = $ini_array[http];
$vCamera = $ini_array[vCamera];
$vAuth = $ini_array[vAuth];
$ptzCapTest = $ini_array[ptzCapTest];
$profileType = $ini_array[profileType];
$debug = $ini_array[debug];
// e-mail configuration
$from_mail = $ini_array[from_mail];
$from_name = $ini_array[from_name];
$reply_mail = $ini_array[reply_mail];
$reply_name = $ini_array[reply_name];
$to_mail = $ini_array[to_mail];
$to_name = $ini_array[to_name];
$subject = $ini_array[subject];
$body = $ini_array[body];
// end from .ini file

// auto configuration
$ip = $_SERVER['SERVER_ADDR']; 					// IP-Adress of your Web server hosting this script
$file = $_SERVER['PHP_SELF'];  					// path & file name of this running php script
$dirname = pathinfo($file, PATHINFO_DIRNAME);
//$SessionFile = getcwd().DIRECTORY_SEPARATOR."SessionFile.txt";
$SessionFile = "SessionFile.txt";
$SessionSave = (object)array();

// URL parameters
$stream_type = $_GET['stream_type'];
$cameraID = $_GET['camera'];
$cameraStream = $_GET["stream"]; // obsolete
$profileTypeGet = $_GET["snapQual"];
$cameraPtz = $_GET["ptz"];
$action = $_GET["action"];
$enable = $_GET["enable"];
$list = $_GET["list"];
if ($_GET["subject"] != NULL) {
	$subject = $_GET["subject"];
}

if ($action == "ResetSID") {
	SessionSave("","","");
	if ($debug) {echo "Echo after ResetSID: Path: ".$CamPath." SID: ".$sid." Auth: ".$AuthPath;}
	}

SessionRead();

if ($debug) {echo "<br>BEFORE EXIT: path: ".$CamPath." sid: ".$sid." auth: ".$AuthPath;}
if ($debug) {echo "-------------------------------------------------------------------------------------------------------------<br>DEBUG ENABLED, TURN OFF BY SETTING VAR debug TO FALSE IN THE CODE<br>      !!!!REMODE DEBUG WHEN CODE IS IN PRODUCTION !!!!<br>-------------------------------------------------------------------------------------------------------------<br>";}

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
		$message .= $body;
		$message .= $passage_ligne;
}
// Default values
if ($profileTypeGet != NULL ) {
	$profileType = $profileTypeGet;
}
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
//if Snapshot, disable debug
if ($cameraID != NULL && $stream_type == "jpeg" && $cameraPtz == NULL && $action == NULL) {
	$debug = false;
	}

SessionRead();

if ($debug) {echo time_elapsed("End of initialisation :");}
// Authenticate with Synology Surveillance Station WebAPI and get our SID 
	//Check if session ID if working. if not, getting a new one.
	//The check API "cameralist" returns a huge json and uses a lot of time, try to find a new way to reducte with another API check?
if($sid != "") {
	if ($debug) {echo("Var session existantes:<br> CamPath: ".$CamPath."<br> AuthPath: ".$AuthPath."<br> SID: ".$sid."<br>");}
	//TESTER l'accès avec camera list json
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version='.$vCamera.'&method=List&_sid='.$sid);
	$obj = json_decode($json);
		//Check if auth pas ok 
		if($obj->success != "true"){
			SessionSave("","",""); // reset session file and exit
			exit();
		}
	} else { //Get a new SID ------------------------------------------------------------------
	//Get SYNO.API.Auth Path (recommended by Synology for further update)
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.API.Auth');
	$obj = json_decode($json);
	$AuthPath = $obj->data->{'SYNO.API.Auth'}->path;
	if ($debug) {echo time_elapsed("Received Auth Path :");}
	//Get SYNO.SurveillanceStation.Camera path (recommended by Synology for further update)
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.SurveillanceStation.Camera');
	$obj = json_decode($json);
	$CamPath = $obj->data->{'SYNO.SurveillanceStation.Camera'}->path;
	if ($debug) {echo time_elapsed("Received Camera Path :");}
	//Get SID
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$AuthPath.'?api=SYNO.API.Auth&method=Login&version=6&account='.$user.'&passwd='.$pass.'&session=SurveillanceStation&format=sid'); 
	$obj = json_decode($json); 
	//Check if auth ok 
	if($obj->success != "true"){
		if ($debug) {echo "<br>";}
		if ($debug) {echo time_elapsed("Error not a valid SID ! ");}
			SessionSave("","","");
		exit();
	} else {
		//authentification successful
		$sid = $obj->data->sid;
		$sid = $obj->data->sid;
		SessionSave($CamPath,$sid,$AuthPath);
		SessionRead();
		if ($debug) {echo "New SID received. storing in session var: ".$sid."<br>";}
		if ($debug) {echo time_elapsed("Received new SID : ");}
	}
}
if ($debug) {echo time_elapsed("All Session Variables :");}
if ($debug) {echo("<br> CamPath: ".$CamPath."<br> AuthPath: ".$AuthPath."<br> SID: ".$sid."<br>");}
//Re-Check if auth OK ----------------------------------------------------------------------------
if($sid == ""){
	echo "Error: Sid Not SET. Clearing file session and Exiting";
	SessionSave("","","");
	// on ferme la page qui vient d'être générée
	exit();
}
//OK, SID Exists
	//Get Snapshot -------------------------------------------------------------------------------
	if ($cameraID != NULL && $stream_type == "jpeg" && $cameraPtz == NULL && $action == NULL) {
		$id_cam = $cameraID;
		// Read the contents of the snapshot and output it directly without putting it in memory first
		//$sid = "kubkjh";
		ob_start();
		$length = readfile($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?&version=9&id='.$id_cam.'&api=SYNO.SurveillanceStation.Camera&method="GetSnapshot"&profileType='.$profileType.'&_sid='.$sid);
		// remove the lengt from the output
		$content = ob_get_clean();
		//Check if length is not too low, else check if cam enabled, regenerate a new SID
		if ($length <=50 ) {
			echo "File length: ".$length."<BR>";
			if ($debug) {echo time_elapsed("Error, NO SID : ");}
			echo "File empty, probable SID problem, Checking if camera enabled<br>";
			$obj = ListCams($http, $ip_ss, $port, $CamPath, $vCamera, $sid);
			//$cam = $obj->data->cameras->$cameraID
				foreach($obj->data->cameras as $cam){
					$id_cam2 = $cam->id;
					if ($id_cam2 == $cameraID) {
						$nomCam = $cam->name;
						$status = $cam->status;
						//camStatus 0 … 4 Indicating the camera status • 0: Normal • 1: Disconnected • 2: Disabled • 3: Deleted • 4: Others
						if ($status == 0 && $cam->enabled) {
							echo "Camera ".$nomCam." IS enabled but not returning an image regenerating a new one<br><hr>";
							echo "Returned value: ".$content."<br>";
							echo "You may check if this images can be served by surveillance station.<br> Example: stream 3 (low quality) is not always available<br>";
							SessionSave("","","");
							} else {
							echo "camera status: ".$status."<br> if camera status <> 0, no image is ok.<br>";
							echo "0: Normal <br> 1: Disconnected <br> 2: Disabled <br> 3: Deleted <br> 4: Others";
							}
					}
				}
			} else {
			// Setting the correct header so the PHP file will be recognised as a JPEG file 
			header('Content-Type: image/jpeg'); 
			echo $content;
			}
		}
	// Save Snapshot -----------------------------------------------------------------------------
	if ($cameraID != NULL && $action == "saveSnapshot" && $cameraPtz == NULL) { 
		$id_cam = $cameraID;
		// Setting the correct header so the PHP file will be recognised as a JPEG file 
		$SnapshotUrl = $http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?&version=9&id='.$id_cam.'&api=SYNO.SurveillanceStation.Camera&method="GetSnapshot"&profileType='.$profileType.'&_sid='.$sid;
		$FileName = "Snapshot-Cam-".$id_cam.".jpg";
		$ch = curl_init($SnapshotUrl);
		$fp = fopen($FileName, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		echo "success";
		exit();
	}
	// Get Camera List ---------------------------------------------------------------------------
	if ($list == "json") {
		echo "Json camera list viewer. Copy the Json below and paste it in this viewer:<br>";
		echo "<a href=https://codebeautify.org/jsonviewer>https://codebeautify.org/jsonviewer</a><br>";
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version='.$vCamera.'&method=List&_sid='.$sid);
		// echo $list.'<br>';
		//echo $http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version='.$vCamera.'&method=List&_sid='.$sid;
		echo $json;
		exit();
	}
	// Write all available snapshots to disk CameraName-x.jpg x = camera id ----------------------
	if ($action == "AllSnapshots") {
		//list & status of known cams 
		$obj = ListCams($http, $ip_ss, $port, $CamPath, $vCamera, $sid);
		$nbrCam = $obj->data->total;
		if ($nbrCam == 0) {
			echo "No camera defined";
			exit();
		}
		//list of known cams 
		foreach($obj->data->cameras as $cam){
			$id_cam = $cam->id;
			$nomCam = $cam->name;
			//echo "-----------------------------------------------------------------------------------------<br>";
			//echo "Cam <b>".$nomCam." (".$id_cam.")<br> ";
			//check if cam is connected
			if (!$cam->status) {
				//check if cam is activated and recording
				if($cam->enabled) {
					//Write Image to Disk
					//API 2.8 query for snapshot with working quality : http://192.168.1.1:5000/webapi/entry.cgi?version=9&id=18&api="SYNO.SurveillanceStation.Camera"&method="GetSnapshot"&profileType=0
					$SnapshotUrl = $http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?&version=9&id='.$id_cam.'&api=SYNO.SurveillanceStation.Camera&method="GetSnapshot"&profileType='.$profileType.'&_sid='.$sid;
					$FileName = "Snapshot-Cam-".$id_cam.".jpg";
					$ch = curl_init($SnapshotUrl);
					//usleep(250000); // Wait 0.25 second
					//flock($fp, LOCK_EX); // acquire an exclusive lock
					//ftruncate($fp, 0);   // truncate file
					$fp = fopen($FileName, 'wb');
					curl_setopt($ch, CURLOPT_FILE, $fp);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_exec($ch);
					curl_close($ch);
					fclose($fp);
					//flock($fp, LOCK_UN);// release the lock
					//Display image
					//echo "<img src='".$FileName."' alt='image JPG' width='480'><br>";
				}
			}
			//echo $FileName." Saved to disk<br>";
			//echo time_elapsed("Elapsed Processing time: ");
		}
		//Send one image (of camera 6 in this example) to client who requested to write all snapshots to disk (SSS_Get.php?action=AllSnapshots&snapQual=0&camera=6)
		if ($cameraID != 0){
		ob_clean();
		header('Content-Type: image/jpeg'); 
		// Read the contents of the snapshot and output it directly without putting it in memory first 
		readfile($http.'://'.$ip_ss.$dirname.'/Snapshot-Cam-'.$cameraID.'.jpg');
		} else {echo "done";
		}
		exit();
	}
	// create full information page --------------------------------------------------------------
	if ($list == "camera") {
		//Get SYNO.SurveillanceStation.Ptz path (recommended by Synology for further update)
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.SurveillanceStation.PTZ');
		$obj = json_decode($json);
		$PtzPath = $obj->data->{'SYNO.SurveillanceStation.PTZ'}->path;

		//list & status of known cams 
		$obj = ListCams($http, $ip_ss, $port, $CamPath, $vCamera, $sid);
		$nbrCam = $obj->data->total;
		echo "<u>Informations:</u><hr><br>";
		if ($nbrCam == 0) {
			echo "No camera defined";
			exit();
		} else {
			echo "Camera count: <b>".$nbrCam."</b><br>";
		}
		echo "Actual <b>SID</b>: ".$sid."<br>Force SID Renew ? <a href=http://".$ip.$file."?action=ResetSID target='_blank'>http://".$ip.$file."?action=ResetSID </a><br>";
		echo "<hr><u>Global Functions:</u><hr><br>";
		echo "<b>Generate and write all available snapshots to disk (High Quality): </b><br><a href=http://".$ip.$file."?action=AllSnapshots&snapQual=0 target='_blank'>http://".$ip.$file."?action=AllSnapshots&snapQual=0 </a><br>";
		echo "<b>Generate and write all available snapshots to disk (High Quality) and grap snapshot of CAM 1: </b><br><a href=http://".$ip.$file."?action=AllSnapshots&snapQual=0&camera=1 target='_blank'>http://".$ip.$file."?action=AllSnapshots&snapQual=0&camera=1 </a><br>";
		echo "<b>Generate and write all available snapshots to disk (Medium Quality) and grap snapshot of CAM 2:</b><br><a href=http://".$ip.$file."?action=AllSnapshots&snapQual=1&camera=2 target='_blank'>http://".$ip.$file."?action=AllSnapshots&snapQual=1&camera=2 </a><br>";
		echo "Gets All Cameras JSON: <a href=http://".$ip.$file."?list=json target='_blank'>http://".$ip.$file."?list=json </a><br>";
		echo "Enable ALL cameras: <a href=http://".$ip.$file."?action=enable target='_blank'>http://".$ip.$file."?action=enable </a><br>";
		echo "Disable ALL cameras: <a href=http://".$ip.$file."?action=disable target='_blank'>http://".$ip.$file."?action=disable </a><br>";
		echo "Start recording ALL camera: <a href=http://".$ip.$file."?action=start target='_blank'>http://".$ip.$file."?action=start </a><br>";
		echo "Stop recording ALL cameras: <a href=http://".$ip.$file."?action=stop target='_blank'>http://".$ip.$file."?action=stop </a><br>";
		echo "Send screenshots per e-mail for ALL cameras: <a href=http://".$ip.$file."?action=mail target='_blank'>http://".$ip.$file."?action=mail </a><br>";
		echo "<hr><u>Actions for each individual cameras:</u><hr><br>";
		//list of known cams 
		foreach($obj->data->cameras as $cam){
			$id_cam = $cam->id;
			$nomCam = $cam->name;
			$vendor = $cam->vendor;
			$model = $cam->model;
			$ptzCap = $cam->ptzCap;
			echo "Camera Name: <b>".$nomCam." (".$id_cam.") ";
			if ($cam->enabled) {
				echo "Is Enabled</b> --> <a href=http://".$ip.$file."?action=disable&camera=".$id_cam." target='_blank'>Disable ?</a><br>";
			} else {
				echo "Is disabled</b> --> <a href=http://".$ip.$file."?action=enable&camera=".$id_cam." target='_blank'><b>Enable ?</b></a><br>";
			}
			echo "Vendor : <b>".$vendor."</b> - Model : <b>".$model."</b><br>";
			
			if ($ptzCap > $ptzCapTest) {
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
					echo "<b>Download snapshot.</b> Quality: <a href=http://".$ip.$file."?stream_type=jpeg&camera=".$id_cam."&snapQual=0 target='_blank'>High Quality</a> - <a href=http://".$ip.$file."?stream_type=jpeg&camera=".$id_cam."&snapQual=1 target='_blank'>Medium Quality</a> - <a href=http://".$ip.$file."?stream_type=jpeg&camera=".$id_cam."&snapQual=2 target='_blank'>LowQuality</a><br>";
					echo "<b>Save Snapshot to server disk.</b> Quality: <a href=http://".$ip.$file."?action=saveSnapshot&camera=".$id_cam."&snapQual=0 target='_blank'>High Quality</a> - <a href=http://".$ip.$file."?action=saveSnapshot&camera=".$id_cam."&snapQual=1 target='_blank'>Medium Quality</a> - <a href=http://".$ip.$file."?action=saveSnapshot&camera=".$id_cam."&snapQual=2 target='_blank'>LowQuality</a><br>";
					echo "URL for saved Snapshot on Server Disk: <a href=http://".$ip.$dirname."/Snapshot-Cam-".$id_cam.".jpg target='_blank'>http://".$ip.$dirname."/Snapshot-Cam-".$id_cam.".jpg</a><br>";
			
			
					echo "<b>Stream MJPEG: </b> <a href=http://".$ip.$file."?stream_type=mjpeg&camera=".$id_cam." target='_blank'>MJPEG</a><br>";
					//check if cam is recording
					echo 'Recording status : ';
					if ($cam->recStatus) {
						echo "<b>recording...</b> --> <a href=http://".$ip.$file."?action=stop&camera=".$id_cam." target='_blank'>stop ?</a><br>";
					} else {
						echo "<b>NOT recording...</b> --> <a href=http://".$ip.$file."?action=start&camera=".$id_cam." target='_blank'>start ?</a><br>";
					}
					echo "Send screenshot per e-mail ? : <a href=http://".$ip.$file."?action=mail&camera=".$id_cam." target='_blank'>http://".$ip.$file."?action=mail&camera=".$id_cam."</a><br>";
					echo "Press CTRL-F5 to refresh Snapshots<br>";
					echo "<img src='".$http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?&version=9&id='.$id_cam.'&api=SYNO.SurveillanceStation.Camera&method="GetSnapshot"&profileType='.$profileType.'&_sid='.$sid."' alt='image JPG' width='480'><br>";
				}
			}
			echo time_elapsed("Elapsed Processing time: ");
			echo "<hr>";
		}
		exit();
	}
	// Move camera to PTZ position ---------------------------------------------------------------
	if ($cameraPtz != NULL) {
	//Get SYNO.SurveillanceStation.Ptz path (recommended by Synology for further update)
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.SurveillanceStation.PTZ');
		$obj = json_decode($json);
		$PtzPath = $obj->data->{'SYNO.SurveillanceStation.PTZ'}->path;
		//echo $PtzPath.'<br>';
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$PtzPath.'?api=SYNO.SurveillanceStation.PTZ&method=GoPreset&version=1&cameraId='.$cameraID.'&presetId='.$cameraPtz.'&_sid='.$sid);
		echo $json;
		// on ferme la page qui vient d'être génrée
		echo "<script>window.close();</script>";
		exit();
	}
	// ACTIONS: Enable - Disable - START - STOP - Mail (Prepare) ---------------------------------
	if ($action != NULL) {
		//list & status of known cams 
		$obj = ListCams($http, $ip_ss, $port, $CamPath, $vCamera, $sid);
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
				echo time_elapsed("Elapsed Processing time: ");
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
				echo time_elapsed("Elapsed Processing time: ");
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
				echo time_elapsed("Elapsed Processing time: ");
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
				echo time_elapsed("Elapsed Processing time: ");
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
	// SEND MAIL ---------------------------------------------------------------------------------
	if ($action == "mail") {
		// Fin du mail et Envoi
		$message .= "--".$boundary."--";
		mail($mail_to, $subject, $message, $entete);
		echo "<br><b>Sujet : </b>" . $subject . "<br>";
		echo "<b>Mail envoyé. </b><br>";
	}
	// Get MJPEG ---------------------------------------------------------------------------------
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

// Functions
	// Read Session File -------------------------------------------------------------------------
function SessionRead() {
	global $SessionFile, $CamPath, $sid, $AuthPath;
	//Read the $SessionFile file to get the properties
	if (file_exists($SessionFile)) {
	$objData = file_get_contents($SessionFile);
	$SessionSave = unserialize($objData);
		if (!empty($SessionSave)) {
			$CamPath = $SessionSave->CamPath;
			$sid = $SessionSave->sid;
			$AuthPath = $SessionSave->AuthPath;
		}
	}
}
	// Write Session File to disk and create if not present --------------------------------------
function SessionSave($FCamPath, $Fsid, $FAuthPath) {
	global $SessionFile;
	if (file_exists($SessionFile)) {
		//Write Variables to text file $SessionFile
		$SessionSave->CamPath = $FCamPath;
		$SessionSave->sid = $Fsid;
		$SessionSave->AuthPath = $FAuthPath;
		$objData = serialize($SessionSave);
		if (is_writable($SessionFile)) {
			$fp = fopen($SessionFile, "w"); 
			fwrite($fp, $objData); 
			fclose($fp);
		}
	} else {
		touch($SessionFile);
		//Write Variables to text file $SessionFile
		$SessionSave->CamPath = $FCamPath;
		$SessionSave->sid = $Fsid;
		$SessionSave->AuthPath = $FAuthPath;
		$objData = serialize($SessionSave);
		if (is_writable($SessionFile)) {
			$fp = fopen($SessionFile, "w"); 
			fwrite($fp, $objData); 
			fclose($fp);
		}
	}
}
	//list & status of known cams ----------------------------------------------------------------
function ListCams($http, $ip_ss, $port, $CamPath, $vCamera, $sid) {
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version='.$vCamera.'&method=List&_sid='.$sid);
	//echo $json;
	$obj = json_decode($json);
	return $obj;
}
	// Time Stamp by JOJO (obsolete ?) -----------------------------------------------------------
function TimeStamp() {
	//Display TimeStamp in log
	echo date('d\/m\/Y H\:i\:s').' <br> ';
}
	// To trace php execution time ---------------------------------------------------------------
function time_elapsed($affich) {
	
	$executionTime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
return "$affich "." $executionTime"." Seconds <br>";
}
?>
