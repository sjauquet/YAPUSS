# YAPUSS
YAPUSS - Passerelle "Universelle" Surveillance Station

More info available here:

https://www.domotique-fibaro.fr/topic/11097-yapuss-passerelle-universelle-surveillance-station/ (French forum)

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
	 - start/stop manual recording
	 - PTZ action
	 
    Thread here:
    https://www.domotique-fibaro.fr/topic/11097-yapuss-passerelle-universelle-surveillance-station/
    Thanks to all open sources examples grabbed all along the web and specially filliboy who made this script possible.

    examples:

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
