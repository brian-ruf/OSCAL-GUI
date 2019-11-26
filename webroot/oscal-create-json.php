<?php

session_start();
session_write_close();
ignore_user_abort(true);

require_once('oscal-begin.php');
require_once("oscal-zones.php");
require_once('oscal-functions.php');

// Initialize Variables
$status = false;
$project_file = "";
$project_id = $_GET["project"];;
$ret_val = "";
$messages="";

$project_file = $project_id . "/" . FindOSCALFileInDir(PROJECT_LOCATION . $project_id . "/");;
$base_dir = basename(dirname($project_file));

ClearAllZones();
ZoneAdjust("font-size: 1.5em; max-height:80px; font-weight: bold; color: red;", "zone-one");
ZoneAdjust("text-align: center; max-height:800px;", "zone-two");
ZoneAdjust("background: color url('./img/spinning_rings_fr_red.gif');", "zone-three");

	if (file_exists(PROJECT_LOCATION . $project_file)) {  
		Logging("OPENING FILE");
		$oscal = OpenOSCALfile(PROJECT_LOCATION . $project_file, $base_dir);
		Logging("GETTING METADATA");

		$metadata = GetBasicMetadata(PROJECT_LOCATION . $project_file,  $base_dir);

		if ($metadata['status']) {
			
			$title_output = $metadata['title'];
			$title_hover_output = "";

			if ($metadata['version'] !== "" ) {
				$title_hover_output .= "VERSION: " . $metadata['version'];
			}
			if ($metadata['date'] !== "" ) {
				if (strlen($title_hover_output) > 1) {
					$title_hover_output .= " -- ";
				}
				$title_hover_output .= "DATE: " . $metadata['date'];
			}

			if ($metadata['label'] !== "" ) {
				$title_output .= "<br /><span style='background-color: red; color: white; font-weight: bold;'>" . $metadata['label'] . "</span>";
			}
			
			ZoneOutput("<span style='color: red; font-weight: bold; font-size:1.2em' title='" . $title_hover_output . "'>" . $title_output . "</span>", 'header-additional');
		} else {
			ZoneOutput($metadata['title'], 'header-additional');
		}

		ZoneOutput("CONVERTING TO JSON ...", 'zone-one');
		ZoneCommand('working_notice');

		$input_file_name = PROJECT_LOCATION . $project_file;
		$output_file_name = PROJECT_LOCATION . $base_dir . "/__other_formats/" . basename($project_file, ".xml") . ".json";
		
		Logging("CONVERTING FILE TO JSON");
		Logging($output_file_name);
		$result = CreateJSON($input_file_name, $output_file_name);

		ZoneCommand('working_notice_stop');
		ZoneAdjust("max-height:600px; box-shadow: 10px 10px 5px grey; border: 1px solid #8a4419; border-style: inset;", 'zone-two');
		
		ZoneOutputAppend("<br />DONE!", "zone-one");
//		ZoneOutputAppend("<br /><a href='./projects/" . $base_dir . "/__other_formats/" . basename($output_file_name) . "' download='" . basename($output_file_name) . "'>DOWNLOAD JSON FILE</a>", "zone-one");
		
	} else {
		ZoneOutputAppend("<br />ERROR OPENING FILE", 'zone-one');
	}
	
	$project_dir = $project_id . "/__other_formats/";
	$file_pattern = "*.[jJ][sS][oO][nN]";
	$file_label = "DOWNLOAD JSON FILE";
	$date_label = "Created";
	$files = glob(PROJECT_LOCATION . $project_dir . $file_pattern);
	if (count($files) > 0) {
		$ret_val .= MakeDownloadButtons($files, PROJECT_LOCATION . $project_dir, $file_pattern, $file_label, $date_label);
	}

	$ret_val .= MakeBackButton("oscal.php?mode=open&project=" . $project_id );

	if (count($result) > 0 ) {
		$ret_val .= "<pre>";
		foreach($result as $item) {
			$ret_val .= $item;
		}
		$ret_val .= "</pre>";
	}

	ZoneOutputAppend($ret_val, "zone-two");

Logging ($messages);
ZoneClose();
// End Main

