# YAPUSS - Yet Another Passerelle(bridge) Universelle Surveillance Station
## Version History :
```txt YAPUSS - Yet Another Passerelle(bridge) Universelle Surveillance Station
## Version History :
```txt
V6   by sebcbien (18/10/17)
V6.1 by Jojo (19/10/2017)	:	server IP adress generated automatically
V6.2 by Sebcbien:				added ptz placeholder
V6.3 by Jojo (19/10/2017)	:	remove hardcoding of file name & location
V6.4 by Jojo (20/10/2017)	:	links are opened in a new tab
V7   by Sebcbien (21/10/17)	:	Added enable/disable action
								Changed file Name (SSS_Get.php)
V8   by Jojo (25/10/2017)	:	add start/stop recording action
								& global review for alignments, comments, ...
V9   by Jojo (20/11/2017)	:	takes screenshots and send them per e-mail as attachement
								& actions for all cameras
								& update menu
V10   by seb (22/11/2017)	:	Added PTZ function, small bug fixes & rearrange code for speed optimisation
V10.1 by seb (25/11/2017)	:	correction bug actions.
v10.2 by Jojo (25/11/2017)	:	correction bug list PTZ ids & code optimization (use function)
v11   by Jojo (23/12/2017)	:	get configuration from external file
v11.1 by Jojo (22/06/2018)	:	add TimeStamps for display in logs
v12   by Jojo (14/09/2018)	:	add possibility to personnalize subject of the e-amil
v13   by seb (16/09/2018)	:	add elapsed time counter for debug purposes
								& solve bug ini file not parsed when YAPUSS script is not in the root folder
v14   by seb (18/09/2018)	:	add method to re-use the SID between API calls
								& add method to reset SID (action=ResetSID)
v15   by seb (19/09/2018)	:	add method to write all available snapshots to disk (list=AllSnapshots)
								& resolve bug about snapshot quality not working (see more info in .ini file)
								& Cosmetic Work
v16   by seb (21/09/2018)	:	keep the same SID for all sessions. Script is now WAYYYY Faster and les load on Surveillance Station
V16.1 by seb (22/09/2018)	:	add auto creation of SessionFile.txt if not present
								& WRITE Permission IS NEEDED by the web server to write session file.
								& CURL must be enabled in php option
								& check if getsnapshot failed, and then reset SID to get a new one
								& cleaned up the code
v16.2 by Jojo (23/09/2018)	:	rename SessionFile.txt to SSS_Get.session
v16.3 by Seb (23/09/2018)	:	clear bug not showing snapshots when in debug mode. cleaning the code.
v17.0b by Jojo (23/09/2018)	:	work in progress to add save snapshots with history
v17.1b by Seb (24/09/2018)	:	Add status images sent to client when no image is available on SS
v18 by Jojo (04/10/2018)	:	review/optimize code
								& review/standadize actions
								& review documentation
								& add parameter debug=1
								& add autorefresh (via .ini file ( v >= 3.0) or via parameter)
v18.1 by Jojo (21/02/2019)	:	correct bug with PTZ

# ToDo:
 - accept array of cameras form url arguments
 - find a quicker test to check if api access is ok (retreiving json of cameras takes 0,5 second)
 - Clean up code with faster check with PTZ at the beginning
 - Force refresh of snapshot on demo page
```
## Requirements
```txt
PHP 7.0, altrough a previous version may work for some functionalities
cURL extension (optional, for downloading snapshots and saving them to the web server)
WRITE Permission IS NEEDED by the web server to write session file.
User defined in the .ini file must be director of the cameras.

```
## Installation instructions :
```txt
Install php 7.0 on the Web server.
Save this file with extension .php (example : SSS_Get.php)
In the same folder, create the .ini file with the SAME name (except the extension) as this scirpt file (example : SSS_Get.ini)

If you use Synology to send mails. you need to configure the notification in the control panel.
I share with you some strange behaviors.
	1) When using GMAIL, even if the test mail notification was ok, this php mail was not send. => Solution is to re-do the autentication for Gmail in the notification Panel of the Synology. But after few hours / days it does not work anymore ...
	2) So I tried Yahoo! as SMTP provider, but the delivery of mails tooks a long time (several seconds/minutes)
	3) I use now the SMTP of my mail provider : this is as fast as whith Gmail.

```
## Thread here :
```txt
https://www.domotique-fibaro.fr/topic/11097-yapuss-passerelle-universelle-surveillance-station/
Thanks to all open sources examples grabbed all along the web and specially filliboy who made this script possible.

```
## Syntax :
```txt
	- Snapshot quality: snapQual=0: High Quality | 1: Medium Quality | 2: Low Quality (if available) default is set in .ini: profileType

	- action=
		enable 		- enable camera
		disable 	- disable camera
		start 		- start recodring camera
		stop 		- stop recording camera
		snapshot 	- save camera snapchot (Snapshot-Cam-#.jpg in the script running folder)
		archive 	- save camera snapshot to /Snapshots sub-folder with timestamp (Snapshot_#_<cam name>_yyyymmdd_hhmmss.jpg)
		mail 		- send per e-mail a camera snapshot
		ResetSID 	- force regeneration of a new sid. Should not be needed, an SID stays untill a reboot of the synology

	  if camera=# provided : restrict the action to the specified cameera
	  if camera=0|All or no camera specified : perform action for All cameras

	  for action=start & action=mail, adding the parameter '&enable=1' enable the disabled camera before the action.

	- display=# : Send one image (of camera #) to the client & display

	- Other functions :
		Get Snapshot :	http://xxxxxx/SSS_Get.php?stream=jpeg&camera=#&snapQual=q   - returns snapshot of camera #, Quality q
		Get Mjpeg :		http://xxxxxx/SSS_Get.php?stream=mjpeg&camera=#             - returns mjpeg stream of camera #
		Debug :			http://xxxxxx/SSS_Get.php?debug=1							- run the script in debug mode
		Refresh :		http://xxxxxx/SSS_Get.php?refresh=#							- refresh de home page every # sec/9999 to stop

```
## Some Examples :
```txt
	http://xxxxxx/SSS_Get.php?action=snapshot&camera=19&snapQual=0 
		Save snapshot of camera Nr 19 on disk (High Quality)
	http://xxxxxx/SSS_Get.php?action=snapshot&snapQual=0
		Save all available snapshots to disk (High Quality)
	http://xxxxxx/SSS_Get.php?action=snapshot&snapQual=0&display=1 
		Save all available snapshots to disk (High Quality) and return one snapshot of camera Nr 1.
		(Typical use: ask this urls with one display . It will then act as scheduler. Then grab the writen image on disk with the other displays).
	http://xxxxxx/SSS_Get.php?action=snapshot&snapQual=1&display=2.
		Save all available snapshots to disk (Medium Quality) and return one snapshot of camera Nr 2.
	http://xxxxxx/SSS_Get.php?action=enable&camera=14              - enable camera 14
	http://xxxxxx/SSS_Get.php?action=enable&camera=0               - enable ALL cameras
	http://xxxxxx/SSS_Get.php?action=enable&camera=0All            - enable ALL cameras
	http://xxxxxx/SSS_Get.php?action=enable                        - enable ALL cameras
	http://xxxxxx/SSS_Get.php?action=mail&camera=14                - send per mail snapshot of camera 14
	http://xxxxxx/SSS_Get.php?action=mail&subject=non default      - send per mail snapshot of ALL cameras with the non default subject on the mail

Help function:
	http://xxxxxx/SSS_Get.php                                      - Returns the list of all cameras with a snapshot, status, urls etc.
	http://xxxxxx/SSS_Get.php?list=json                            - Returns a json with all cameras
	http://xxxxxx/SSS_Get.php?list=camera                          - Returns the list of all cameras with a snapshot, status, urls etc.

PTZ function
	http://xxxxxx/SSS_Get.php?ptz=5&camera=19                      - moves camera Nr 19 to PTZ position id 5
```
## License
This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/.
