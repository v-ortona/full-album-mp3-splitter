<?php
	//Splits full mp3 into tracks (put into folder)

	require_once("YouTubeTracklist.php");
	require_once("phpmp3/phpmp3.php");
	
	$url = ""; //YouTube url
	$file = ""; //the full album mp3 file
	
	
	$tracks = new YouTubeTracklist($url);
	$desc = $tracks->getDesc();
	$tracks->trackList = $tracks->parse($desc);
	$tracks->convertToSec();
	print_r($tracks->trackList);
	
	
	
	$path_parts = pathinfo($file);
	if(strcmp($path_parts['extension'], "mp3") != 0) {
		exit("File linked is not MP3 format");
	}
	
	$dirname = "".$path_parts['dirname']."/".$path_parts['filename'];
	mkdir($dirname); //make folder with orignal mp3 name
	
	$phpmp3 = new PHPMP3($file);
	$phpmp3->setFileInfoExact();
	
	$rev = array_reverse($tracks->trackList); //easier to work with backwards
	//last song first
	
	//TODO set ID3 metadata
	$endtime = $phpmp3->time;
	foreach($rev as $title => $starttime) {
		$song = $phpmp3->extract($starttime, $endtime-$starttime);
		$song->save("$dirname/$title.mp3");
		$endtime = $starttime;
	}
	
	
	
