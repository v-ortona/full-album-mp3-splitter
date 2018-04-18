<?php
	//Splits full mp3 into tracks (put into folder)
	require_once("YouTubeTracklist.php");
	require_once("phpmp3/phpmp3.php");

	//YOUTUBE API SETUP
	$DEVELOPER_KEY = 'API_KEY_GOES_HERE';
	
	$client = new Google_Client();
	$client->setDeveloperKey($DEVELOPER_KEY);
	
	//Define an object that will be used to make all API requests
	$youtube = new Google_Service_YouTube($client);
	

	// Snippet code for videos.list
	function videosListById($service, $part, $params) {
	    $params = array_filter($params);
	    $response = $service->videos->listVideos(
		$part,
		$params
	    );

		return $response;
	}

	//get parameters from command line
	$url = $argv[1]; //YouTube url (first arg is program name)
	$file = $argv[2]; //the full album mp3 file

	//parse url for video ID
	$videoParams = parse_url($url, PHP_URL_QUERY);
	parse_str($videoParams, $output);
	$videoID = $output['v'];
	

	//Make API request
	$response = videosListById($youtube,
	    'snippet', //part parameters
	    array('id' => $videoID));	//the video ID
	

	$tracks = new YouTubeTracklist($response);
	$desc = $tracks->getDesc();
	$tracks->trackList = $tracks->parse($desc);
	
	print_r($tracks->trackList);
	echo "Is the following tracklist correct? Y/n\n";
	$handle = fopen("php://stdin","r");
	$line = fgets($handle);
	
	//Correct tracklist
	if(trim($line) == 'Y' || trim($line) == 'y') {
		$tracks->convertToSec();
	
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
		echo "The conversion has terminated\n";
	} else {
		echo "Error: A correct tracklist could not be found\n";
	}
	
	fclose($handle);
	

	
	
	
