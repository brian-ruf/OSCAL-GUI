<?php  
session_start();
session_write_close();
ignore_user_abort(true);

require_once('oscal-begin.php');
require_once("oscal-zones.php");
require_once('oscal-functions.php');

// NOTE: **** ADD OPTION IN oscal-begin.php TO OPEN BLANK 
//            FILES: CATALOG AND PROFILE FOR NOW. 
//                   SSP AND OTHERS LATER.

// Initialize Variables
$status = false;
$project_folder = "";
$project_file = "";
$project_list = array();

// If the location for project files does not exist, create it.
if (file_exists(PROJECT_LOCATION)) {
	// nothing to do. continue.
	// Logging("Project repository location found.");
} else {
	// need to create it
	Logging("Missing project repository:<br />&nbsp;&nbsp;&nbsp;" . PROJECT_LOCATION);
	if (mkdir(PROJECT_LOCATION, 0755, true)) {
		Logging("Successfully created project repository:<br />&nbsp;&nbsp;&nbsp;" . PROJECT_LOCATION);
	} else {
		Logging("Problem creating project repository:<br />&nbsp;&nbsp;&nbsp;" . PROJECT_LOCATION);
	}
}


$mode = $_GET["mode"];
Logging("PROJECT MODE: " . $mode);

switch($mode) {
	case 'verify':
		NewProject();
		break;
	case 'continue':
		PresentProjectList();
		break;
	case 'manage':
		$title = "Manage Project";
		break;
	case 'delete':
		DeleteConfirmation($_GET["project"]);
		break;
	case 'deleteconfirmed':
		DeleteProject($_GET["project"]);
		GatherProjectList(refresh);
		PresentProjectList();
		break;
	case 'export':
		break;
	case 'open':
//		OpenProject($_GET["project"]);
		break;
	default:
		// Unexpected mode
		$title = "INVALID MODE: " . $_GET["mode"];
		ZoneAdjust("font-size: 0.7em; color: red;", "header-additional");
		ZoneOutput($title, 'header-additional');
		break;
	}
	
ZoneClose();
// End Main


// ============================================================================
function DeleteConfirmation($project_dir) {

	$project_list = $_SESSION["project_list"];
	
	$output .= $project_list[$project_dir]["title"];

	$output .= "<br /><span style='color: red;'>";
	$output .= "This will permanently remove the entire project.<br />";
	$output .= "<table><tr>";

	$url = "oscal-project.php?mode=deleteconfirmed&project=" . urlencode(basename($project_dir)) ;
	$output .= "<td onclick='zoneManagement(\"" . $url. "\")' class='button' style='height: 25px;'>REMOVE</td>";
	$output .= "<td>&nbsp;</td>";

	$url = "oscal-project.php?mode=continue";
	$output .= "<td onclick='zoneManagement(\"" . $url. "\")' class='button' style='height: 25px;'>Cancel</td>";
	$output .= "</tr></table>";
	$output .= "</span>";
	
	ZoneOutput($output, $project_dir);
//	PROJECT_LOCATION . $project_dir
	
}

// ============================================================================
// Deletes a project - this includes all local copies of attachments
// and imported files contained wthin that project. 
// This function recursively traverses the project folder until all
// files and sub-folders are removed.
function DeleteProject($project_dir) {
global $project_folder, $project_file;
	
	if (strlen($project_dir) > 5 && strtolower( left_str($project_dir, 5)) == 'proj-' && ! right_str($project_dir,1) == '*' )  {
		if (file_exists(PROJECT_LOCATION . $project_dir)) {
			rrmdir(PROJECT_LOCATION . $project_dir);
		}
	}
}

function rrmdir($path) {
     // Open the source directory to read in files
        $i = new DirectoryIterator($path);
        foreach($i as $f) {
            if($f->isFile()) {
                unlink($f->getRealPath());
            } else if(!$f->isDot() && $f->isDir()) {
                rrmdir($f->getRealPath());
            }
        }
        rmdir($path);
}



// ============================================================================
function PresentProjectList() {
global $project_folder, $project_file;
$output = "";
// $date_format = "F d Y H:i:s";
// $table_format = "border: 1px solid black; padding: 5px; spacing: 5px; border-bottom: 1px solid #ddd;";
$table_format = "border: 0; padding: 5px; spacing: 5px; border-bottom: 1px solid #ddd;";

	if (! isset($_SESSIONp["project_list"])) {
		GatherProjectList();
	}
	$project_list = $_SESSION["project_list"];

	ZoneAdjust("max-height: 800px;");
	$title = "Continue Working";
	ZoneAdjust("font-size: 0.7em; color: red;", "header-additional");
	ZoneOutput($title, "header-additional");

	
	$buttons = array(
			["text" => "Go Back",             "img" => "./img/arrow-left.png", "action" => "window.open(\"./index.php\", \"_self\")"],
			["text" => "Open New OSCAL File", "img" => "./img/document.png",   "action" => "window.open(\"./oscal.php?mode=new\", \"_self\")"]
			);
	$output .= MakeMenu($buttons, false, true);
	
	$output .= "<span style='font-weight: bold; font-size: 1.2em; color:blue;'>" . count($project_list) . " projects found.</span><br /><br />";
	ZoneOutput($output);

	SortProjects($project_list, "date_last_mod");

	$output = "<table class='fileinfo' >";   //  style='".$table_format."'
	$output .= "<tr><th style='width: 30%;'>Title</th><th style='width: 20%;'>File Name</th>";
	$output .= "<th style='width: 20%;'>Created</th><th style='width: 20%;'>Modified</th>";
	$output .= "<th style='width: 10%;'>TOOLS</th>";
	foreach ($project_list as $project) {
		$url = "oscal.php?mode=open&project=" . urlencode(basename($project["dir"])) ;
		$output .= "<tr><td id='" . basename($project["dir"]) . "'>";
		
		$output .= "<table width='100%' class='filelistbutton' onclick='window.open(\"" . $url. "\", \"_self\")'><tr>";
		$output .= "<td style='width:35px;'>" ;
		$output .= "<img style='vertical-align:middle; height:30px; width:30px;' src='img/folder.png'>&nbsp;";
		$output .= "</td><td  >" ;
		$output .= $project["title"] ;

		$output .= "</td>";
		$output .= "</tr></table>";

		$output .= "</td>";
		$output .= "<td style='".$table_format."'>" ;
		$output .= $project["file"];
		$output .= "</td>";
		$output .= "<td style='".$table_format."'>" . date(DATE_TIME_PRESENT_FORMAT, $project["date_orig"]) . "</td>";
		$output .= "<td style='".$table_format."'>" . date(DATE_TIME_PRESENT_FORMAT,$project["date_last_mod"]) . "</td>";
		$output .= "<td style='".$table_format." text-align: center;'>" ;
		$url = "oscal-project.php?mode=delete&project=" . urlencode(basename($project["dir"])) ;
		$output .= "<img class='hover' onclick='zoneManagement(\"" . $url. "\")' style='vertical-align:middle' src='img/tools-trash.png' width='20' height='20'>";
		$output .= "&nbsp;&nbsp;";
		$url = "./" . basename(PROJECT_LOCATION) . "/" . $project["dir"] . "/" . $project["file"] ;
		$output .= "<a href='" . $url . "' download='" . $project["file"] . "' target='_new'><img style='vertical-align:middle' src='img/download.png' width='20' height='20'></a>";
		$output .= "</td>";

		$output .= "</tr>";
	}
	$output .= "</table>";

	ZoneOutputAppend($output);
}

// ============================================================================
function SortProjects(&$project_list, $sort_field){
	
	uasort($project_list, function($a, $b) {
//    return $b[$sort_field] - $a[$sort_field];
    return $b['date_last_mod'] - $a['date_last_mod'];
		});
	
}

// ============================================================================
function NewProject(){
global $project_folder, $project_file;
$status = true;

	$title = "Start New Project";
	ZoneAdjust("display: none;", "zone-two");
	ZoneAdjust("font-size: 0.7em; color: red;", "header-additional");
	ZoneOutput($title, "header-additional");

	
	if (isset($_SESSION["project_folder"])) {
		$project_folder = $_SESSION["project_folder"];
	} else {
		$project_folder = "";
	}

	if (isset($_SESSION["project_file"])) {
		$project_file = $_SESSION["project_file"];
	} else {
		$project_file = "";
	}

	Logging("LOADNG: " . $project_folder . $project_file);
	ZoneOutput("<span style='font-size: 1.2em; color:blue; font-weight:bold;'>Checking: " . $project_file . "</span><br /><br />", 'zone-one');

	$file_type = substr($project_file, -4);
	
	if ($file_type == "json") {
		// move json file from project root to __other_formats
		// call routine to convert JSON to XML
		// set status to true if both of the above are successful
		Logging($project_folder . $project_file);
		$status = rename($project_folder . $project_file, $project_folder . "__other_formats/" . $project_file);
		if ($status) {
			$file_from = $project_folder . "__other_formats/" . $project_file;
			$out_file_name = str_ireplace(".json", ".xml", $project_file);
			$file_to = $project_folder . basename($out_file_name, ".");
			$ret_val = OSCAL_JSON2XML($file_from, $file_to);
			$status =  ($ret_val !== false);
			if ($status) {
				$project_file = $out_file_name;
				if ( ! file_exists($project_folder . $project_file)) {
					$status = false;
					$message = "";
					$message .= "<span style='font-size: 1.2em; color:red; font-weight:bold;'>ERROR CONVERTING JSON FILE TO XML</span><br />";
					$message .= "<pre>";
					foreach ($ret_val as $item) {
						$message .= $item;
					}
					$message .= "</pre>";
					ZoneOutputAppend( $message, 'zone-one');
					ZoneAdjust("display: block;", "zone-two");
				}
			} else {
				Logging("JSON2XML Failed!");
			}
		} else {
			$message = "";
			$message .= "<span style='font-size: 1.2em; color:red; font-weight:bold;'>COULD NOT IMPORT JSON FILE</span><br />";
			$message .= $output['message'];
			$message .= "<br /><span style='font-size: 1.2em; color:red; font-weight:bold;'>Please ensure the JSON file is OSCAL complaint and try again.</span><br />";
			$message .= "<br />";
			ZoneOutputAppend( $message, 'zone-one');
			ZoneAdjust("display: block;", "zone-two");
		}
	}
	
	if ($status) {
		$oscal_objects = OpenOSCALfile($project_folder . $project_file, "", true);
		if ( $oscal_objects["status"] == true) {
			$output = ValidateFile($oscal_objects);
			if ( $output["status"] == true) {
				ZoneOutputAppend( "<br /><span style='font-size: 1.2em; color:green; font-weight:bold;'>File is valid!</span><br />" , 'zone-one');
				$status = true;

				$project_id = urlencode(basename($project_folder));
				AddToProjectList($project_id);
				$url = "oscal.php?mode=open&project=" .  $project_id;
				NewFunction($url);
				
			} else {
				$message = "";
				$message .= "<span style='font-size: 1.2em; color:red; font-weight:bold;'>FILE VALIDATION PROBLEM</span><br />";
				$message .= $output['message'];
				$message .= "<br /><span style='font-size: 1.2em; color:red; font-weight:bold;'>Please correct the file and try again.</span><br />";
				$message .= "<br />";
				ZoneOutputAppend( $message, 'zone-one');
				ZoneAdjust("display: block;", "zone-two");
				
			}
		} else {
			$message = "";
			$message .= "<span style='font-size: 1.2em; color:red; font-weight:bold;'>FILE LOAD PROBLEM</span><br />";
			$message .= "<br />";
			$message .= $oscal_objects['message'];
			$message .= "<br />";
			$message .= "<br /><span style='font-size: 1.2em; color:red; font-weight:bold;'>Please correct the file and try again.</span><br />";
			$message .= "<br />";
			ZoneOutputAppend( $message, 'zone-one');
			ZoneAdjust("display: block;", "zone-two");
		}
	} 
		
	// If there was a problem with the file, we remove it, but keep the project 
	//    open, allowing the user to fix and re-upload the file into the same 
	//    project directory. 
	if ( ! $status ) {
		unlink($project_folder . $project_file );
		unlink($project_folder . "__original/" . $project_file );
		unlink($project_folder . "__other_formats/" . $project_file );
	}
}

// ============================================================================
	
?>
