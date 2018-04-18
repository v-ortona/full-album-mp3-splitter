<?php

require_once 'vendor/autoload.php';

use Sunra\PhpSimple\HtmlDomParser;

//Finds album track list from a Google_Service_YouTube_VideoListResponse Object
class YouTubeTracklist
{

	public $trackList;	//array("songname" => "starttime", ...)
	public $desc;		//video description

	private $response;	//Google_Service_YouTube_VideoListResponse Object
	

	/*
	
	*/
	public function __construct($response)
	{
		//$this->html = HtmlDomParser::file_get_html($url);
		$this->response = $response;
		$this->desc = $response['items']['0']['snippet']['description'];
	}


	public function getDesc()
	{
		return $this->desc;
	}

	
	//tries to parse text and returns tracklist array
	public function parse($text)
	{
		$trackList;
		$textArray = explode("\n", $text);	//each array entry is a whole line in text so it's easier to parse for titles
		
		foreach($textArray as $line) {
			preg_match("/\d{1,2}:\d{2}:\d{2}|\d{1,2}:\d{2}/", $line, $time);
			if(!empty($time)) { //if time in format d:dd, dd:dd was found
				$title = trim(preg_filter("/\d{1,2}:\d{2}:\d{2}|\d{1,2}:\d{2}/", '', $line)); //title is everything else on line but trim whitespace
				$trackList[$title] = $time[0];
			}
		}
		
		return $trackList;
	}
	
	
	//converts tracklist times in array from hh:mm:ss to seconds
	public function convertToSec()
	{
		$newlist;
		foreach($this->trackList as $title => $time) {
			$numSec = 0;
			$times = explode(":", $time);
			$units = 1;
			for($i = count($times)-1; $i >= 0; $i--) {
				$numSec += $times[$i] * $units;
				$units *= 60;
			}
			$newlist[$title] = $numSec;
		}
		$this->trackList = $newlist;
	}
	
	//TODO Option to try to get track list from comment section
	//TODO Strip numbers from title eg) 2. Song 2


}
