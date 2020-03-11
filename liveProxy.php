<?php 

/*

Icecast / Shoutcast MP3 Radio Stream 

Shoutcast V1 (http://shoutcast-server-ip:port/) 
Shoutcast V2 (http://shoutcast-server-ip:port/streamname) 
Icecast V2 (http://icecast-server-ip:port/streamname)

Type: Audio
Codec: MPEG Audio layer 1/2 (mpga)
Channels: Stereo
Sample rate: 44100 Hz
Bitrate: 128 kb/s

*/

header('Content-Type: audio/mpeg');

$media = "";

if (isset($_GET['media'])) {
	$media = $_GET['media'];
} 

if ("test" == $media) {
    $server = "mediaserv33.live-streams.nl";
    $port   = "8042";
    $mount  = "live";
} else {
    $server = "80.60.61.114";
    $port   = "8000";
    $mount  = "high";
}


// HTTP Radio Stream URL with Mount Point
$url = "http://".$server.":".$port."/".$mount;

// Open Radio Stream URL
// Make Sure Radio Stream [Port] must be open / allow in this script hosting server firewall 
$f=fopen($url,'r');

// Read chunks maximum number of bytes to read
if(!$f) exit;
while(!feof($f)) {
	echo fread($f,128);  
	flush();
}
fclose($f);

?>