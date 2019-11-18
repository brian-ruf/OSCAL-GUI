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
$project_id = "";
$metaschema_map = array();

// 
$mode = $_GET["mode"];
Logging("CONTENT MODE: " . $mode);
switch($mode) {

	case 'open':
		OpenProject($_GET["project"]);
		break;
	case 'catalog':
		HandleCatalog($_GET["project"]);
		break;
	case 'profile':
		HandleProfile($_GET["project"]);
		break;
//	case 'ssp':
//		HandleSSPControls($_GET["project"]);
//		break;
//	case 'sspchar':
//		HandleSSPSystem($_GET["project"]);
//		break;
//	case 'metadata':
//		HandleMetadata($_GET["project"]);
//		break;
	case 'imports':
		HandleImports($_GET["project"]);
		break;
	case 'resources':
		HandleResources($_GET["project"]);
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
function OpenProject($project_dir) {
global $project_file, $project_id;

	if (file_exists(PROJECT_LOCATION . $project_dir)) {
		Logging("PROJECT EXISTS");
		ClearAllZones();
		$project_id = $project_dir;
		$project_folder = PROJECT_LOCATION . $project_dir . "/";
		$project_file = $project_dir . "/" . FindOSCALFileInDir(PROJECT_LOCATION . $project_dir . "/");

		$type = GetOSCALType(PROJECT_LOCATION . $project_file, $project_id);
		$rootname = GetOSCALRoot(PROJECT_LOCATION . $project_file, $project_id);

		$metadata = GetBasicMetadata(PROJECT_LOCATION . $project_file, $project_id);

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
		ZoneAdjust("text-align: center; height:1000px; max-height:1000px;", "zone-two-left");
		ZoneAdjust("text-align: center; height:1000px; max-height:1000px;", "zone-two-right");

		// Menu Here Based On Type
		switch($rootname) {
			case 'catalog':
				// Catalog Menu
				ZoneOutput("<span style='font-size: 16px; font-weight: bold;'>Catalog Menu</span>", 'zone-two-left');
//				$form_metadata = "<iframe align='center' width='100%' height='auto' src='oscal-forms.php?form=catalog-metadata&project=" . urlencode($project_dir) . "' frameborder='no' scrolling='yes' name='catalog-metadata' id='catalog-metadata'></iframe>";
				$buttons = array(
//						["text" => "Document Properties", "img" => "./img/book.png",       "action" => "window.open(\"oscal-forms.php?form=metadata-basic&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Roles",               "img" => "./img/book.png",       "action" => "window.open(\"oscal-forms.php?form=metadata-role&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Organizations",       "img" => "./img/book.png",       "action" => "window.open(\"oscal-forms.php?form=metadata-party-org&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Individuals",         "img" => "./img/book.png",       "action" => "window.open(\"oscal-forms.php?form=metadata-party-person&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Manage Controls",     "img" => "./img/paragraph.png",  "action" => "window.open(\"oscal-forms.php?form=catalog-controls&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Manage Resources",    "img" => "./img/infinity.png",   "action" => "window.open(\"oscal-forms.php?form=catalog-resources&project=" . urlencode($project_id) . "\", \"_self\")"],
						["text" => "Create JSON",         "img" => "./img/wheel.png",      "action" => "zoneManagement(\"oscal-create-json.php?project=" . urlencode($project_id) . "\", \"_self\")"],
						["text" => "Go Back",             "img" => "./img/arrow-left.png", "action" => "window.open(\"oscal.php?mode=continue\", \"_self\")"]
						);
				ZoneOutputAppend(MakeMenu($buttons), 'zone-two-left');
				ZoneOutputAppend(DisplayFiles($metadata), 'zone-two-right');
				break;
			case 'profile':
				ZoneOutput("<span style='font-size: 16px; font-weight: bold;'>Profile Menu</span>", 'zone-two-left');
				$buttons = array(
//						["text" => "Document Properties",      "img" => "./img/book.png",       "action" => "window.open(\"oscal-forms.php?form=metadata-basic&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Roles",      "img" => "./img/book.png",       "action" => "window.open(\"oscal-forms.php?form=metadata-role&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Organizations",      "img" => "./img/book.png",       "action" => "window.open(\"oscal-forms.php?form=metadata-party-org&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Individuals",      "img" => "./img/book.png",       "action" => "window.open(\"oscal-forms.php?form=metadata-party-person&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Link Catalogs",        "img" => "./img/parts.png",      "action" => "window.open(\"oscal-forms.php?form=profile-imports&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Manage Organizations", "img" => "./img/people.png",     "action" => "window.open(\"oscal-forms.php?form=profile-party&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Manage Controls",      "img" => "./img/paragraph.png",  "action" => "window.open(\"oscal-forms.php?form=profile-controls&project=" . urlencode($project_id) . "\", \"_self\")"],
						["text" => "Resolve Profile",      "img" => "./img/measure.png",    "action" => "zoneManagement(\"oscal-profile-resolution.php?project=" . urlencode($project_id) . "\", \"_self\")"],
						["text" => "Create JSON",          "img" => "./img/wheel.png",      "action" => "zoneManagement(\"oscal-create-json.php?project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Manage Resources",     "img" => "./img/infinity.png",   "action" => "window.open(\"oscal-forms.php?form=catalog-resources&project=" . urlencode($project_id) . "\", \"_self\")"],
						["text" => "Go Back",              "img" => "./img/arrow-left.png", "action" => "window.open(\"oscal.php?mode=continue\", \"_self\")"]
						);
				ZoneOutputAppend(MakeMenu($buttons), 'zone-two-left');
				ZoneOutputAppend(DisplayFiles($metadata), 'zone-two-right');
				// Profile Menu
				break;
			case 'system-security-plan':
				ZoneOutput("<span style='font-size: 16px; font-weight: bold;'>System Security Plan (SSP) Menu</span>", 'zone-two-left');
				$buttons = array(
//						["text" => "Manage Metadata",        "img" => "./img/book.png",       "action" => "zoneManagement(\"oscal-content.php?mode=metadata-basic&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Manage Imports",         "img" => "./img/parts.png",      "action" => "zoneManagement(\"oscal-content.php?mode=imports&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "System Characteristics", "img" => "./img/screen.png",     "action" => "zoneManagement(\"oscal-content.php?mode=sspchar&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Control Implementation", "img" => "./img/lock.png",       "action" => "zoneManagement(\"oscal-content.php?mode=ssp&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Manage Resources",       "img" => "./img/infinity.png",   "action" => "zoneManagement(\"oscal-content.php?mode=resources&project=" . urlencode($project_id) . "\", \"_self\")"]
						["text" => "Control Information Summary (CIS)",    "img" => "./img/eye.png",        "action" => "zoneManagement(\"oscal-content.php?mode=cis&project=" . urlencode($project_id) . "\", \"_self\")"],
						["text" => "Customer Responsibility Matrix (CRM)", "img" => "./img/eye.png",        "action" => "zoneManagement(\"oscal-content.php?mode=crm&project=" . urlencode($project_id) . "\", \"_self\")"],
						["text" => "Create JSON",                          "img" => "./img/wheel.png",      "action" => "zoneManagement(\"oscal-create-json.php?project=" . urlencode($project_id) . "\", \"_self\")"],
						["text" => "Go Back",                              "img" => "./img/arrow-left.png", "action" => "window.open(\"oscal.php?mode=continue\", \"_self\")"]
						);
				ZoneOutputAppend(MakeMenu($buttons), 'zone-two-left');
				ZoneOutputAppend(DisplayFiles($metadata), 'zone-two-right');
				// SSP Menu
				break;
			case 'component-definition':
				ZoneOutput("<span style='font-size: 16px; font-weight: bold;'>System Security Plan (SSP) Menu</span>", 'zone-two-left');
				$buttons = array(
//						["text" => "Manage Metadata",        "img" => "./img/book.png",       "action" => "zoneManagement(\"oscal-content.php?mode=metadata-basic&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Manage Imports",         "img" => "./img/parts.png",      "action" => "zoneManagement(\"oscal-content.php?mode=imports&project=" . urlencode($project_id) . "\", \"_self\")"],
//						["text" => "Manage Resources",       "img" => "./img/infinity.png",   "action" => "zoneManagement(\"oscal-content.php?mode=resources&project=" . urlencode($project_id) . "\", \"_self\")"]
						["text" => "Create JSON",            "img" => "./img/wheel.png",      "action" => "zoneManagement(\"oscal-create-json.php?project=" . urlencode($project_id) . "\", \"_self\")"],
						["text" => "Go Back",                "img" => "./img/arrow-left.png", "action" => "window.open(\"oscal.php?mode=continue\", \"_self\")"]
						);
				ZoneOutputAppend(MakeMenu($buttons), 'zone-two-left');
				ZoneOutputAppend(DisplayFiles($metadata), 'zone-two-right');
				// SSP Menu
				break;
			default:
				// Unrecognized rootname
				break;
			}		
	} else {
		Logging ("INVALID FILE LOCATION: <br />" . PROJECT_LOCATION . $project_dir);
	}
}

// ============================================================================
function DisplayFiles($metadata) {
	global $project_file, $project_id;
	$ret_val = "";
	$file_label = "";



	$file_label .= "<span style='font-weight:bold;'>" . $metadata['title'] . "</span>";
	if ($metadata['label'] !== "" ) {
		$file_label .= "<br />"  . $metadata['label'];
	} else {
		$file_label .= "<br />[NO DOCUMENT LABEL DEFINED]";
	}
	if ($metadata['version'] !== "" ) {
		$file_label .= "<br />VERSION " . $metadata['version'] ;
	} else {
		$file_label .= "<br />VERSION NOT DEFINED";
	}

	if ($metadata['date'] !== "" ) {
		$pub_date = DateTime::createFromFormat(DATE_TIME_STORE_FORMAT, $metadata['date'])->format(DATE_PRESENT_FORMAT);
		$file_label .= "<br />CITED PUBLICATION DATE<br />" . $pub_date;
	} else {
		$file_label .= "<br />PUBLICATION DATE NOT CITED";
	}

	if ($metadata['last-modified'] !== "" ) {
		$pub_date = DateTime::createFromFormat(DATE_TIME_STORE_FORMAT, $metadata['last-modified'])->format(DATE_TIME_PRESENT_FORMAT); // "F j\, Y H:m:s"
		$file_label .= "<br />LAST MODIFIED DATE<br />" . $pub_date;
	} else {
		$file_label .= "<br />LAST MODIFIED DATE NOT CITED";
	}

	$ret_val .= "<table class='filelist' style='width:100%'><tr><td>";
	$ret_val .= $file_label;
	$ret_val .= "</td></tr></table>";


	$project_dir = $project_id . "/";
	$file_pattern = "*.[xX][mM][lL]";
	
	$file_label = "CURRENT FILE";
	$date_label = "Last Edited";
	$files = glob(PROJECT_LOCATION . $project_dir . $file_pattern);
	$current_file_timestamp = filemtime($files[0]);
	$ret_val .= MakeDownloadButtons($files, $project_dir, $file_pattern, $file_label, $date_label);


	// =================

	$project_dir = $project_id . "/__originial/";
	$file_pattern = "*.[xX][mM][lL]";
	$file_label = "ORIGINAL FILE";
	$date_label = "Uploaded";
	$files = glob(PROJECT_LOCATION . $project_dir . $file_pattern);
	if (count($files) === 1 && $current_file_timestamp !== filemtime($files[0])) {
		$ret_val .= MakeDownloadButtons($files, $project_dir, $file_pattern, $file_label, $date_label);
	} 

	$project_dir = $project_id . "/__other_formats/";
	$file_pattern = "*.*";
	$file_label = "OTHER FORMATS";
	$date_label = "Reflects content as of";
	$files = glob(PROJECT_LOCATION . $project_dir . $file_pattern);
	if (count($files) > 0) {
		$ret_val .= MakeDownloadButtons($files, $project_dir, $file_pattern, $file_label, $date_label);
	}

	$project_dir = $project_id . "/__output/";
	$file_pattern = "*.*";
	$file_label = "GENERATED FILES";
	$date_label = "Generated";
	$files = glob(PROJECT_LOCATION . $project_dir . $file_pattern);
	if (count($files) > 0) {
		$ret_val .= MakeDownloadButtons($files, $project_dir, $file_pattern, $file_label, $date_label);
	}

	$project_dir = $project_id . "/__resources/";
	$file_pattern = "*.*";
	$file_label = "ATTACHED FILES";
	$date_label = "";
	$files = glob(PROJECT_LOCATION . $project_dir . $file_pattern);
	if (count($files) > 0) {
		$ret_val .= MakeDownloadButtons($files, $project_dir, $file_pattern, $file_label, $date_label);
	}

	return $ret_val;
}

// ============================================================================
function HandleCatalogMetadata($project_file) {
global $project_id;

	// $project_file is still encoded, from above, so urlencode not needed here.
	$form_metadata = "<iframe align='center' width='100%' height='800px' src='oscal-forms.php?form=catalog-metadata&file=" . $project_file . "' frameborder='no' scrolling='yes' name='catalog-metadata' id='form-iframe'></iframe>";
	ZoneAdjust('text-align: left; height: auto; max-height: none;', 'zone-two');
	ZoneOutput($form_metadata);
	ZoneCommand("refreshiFrame");

/*
	if (file_exists(OSCAL_FORM_FILES . "custom-catalog-metadata.php")) {
		$form_file = OSCAL_FORM_FILES . "custom-catalog-metadata.php";
	} else {
		$form_file = OSCAL_FORM_FILES . "generated-catalog-metadata.php";
	}
	$input = file_get_contents($form_file);

	ZoneAdjust("text-align: left; max-height: 800px;", "zone-two");
	ZoneOutput("<span style='font-size: 16px; font-weight: bold;'>Metadata</span>");
	ZoneOutputAppend($input, 'zone-two');
	ZoneCommand("refreshTextAreas");
*/

}

// ============================================================================
function HandleForm($project_file, $form_name) {
global $project_id;

	// $project_file is still encoded, from above, so urlencode not needed here.
	$form = "<iframe align='center' width='100%' height='800px' src='oscal-forms.php?form=" . $form_name . "&file=" . $project_file . "' frameborder='no' scrolling='yes' name='catalog-metadata' id='form-iframe'></iframe>";
	ZoneAdjust('text-align: left; height: auto; max-height: none;', 'zone-two');
	ZoneOutput($form);
	ZoneCommand("refreshiFrame");

}


// ============================================================================
function HandleResources($project_dir) {
	ZoneOutput("<span style='font-size: 16px; font-weight: bold;'>Resources</span>");

}

// ============================================================================
function HandleCatalog($project_dir) {
	ZoneOutput("<span style='font-size: 16px; font-weight: bold;'>Catalog Controls</span>");
	
}

// ============================================================================
function HandleProfile($project_dir) {
	
}

// ============================================================================
function HandleImplementation($project_dir) {
	
}

// ============================================================================


// ============================================================================
/* NOTES
Every element is a mini-form
Each mini-form submits to the server immediately without repainting the screen
(CHECK drag-and-drop upload setup for hints on submitting forms witihout changing pages)
(Reserch an onchange or addEventListener javascript method or similar)

CHANGES: For each field change the server receives it:
	1. Reads the entire tree from disk_free_space
	2. Makes the change
	3. Saves the entire tree to dsk
ADDITIONS: Same appraoch
DELETIONS: Same approach
RE-ORDERING: Same approach, except #2 is the swap


define-assembly (only one)
FORM

define-assembly group-as
FORMS

define-field (only one)

define-field group-as

<input title="This is the text of the tooltip" onchange="javascript()">
<textarea placeholder="This will display text that disappears when the user starts typing."></textarea>


<body onbeforeunload="return myFunction()">

*[local-name() = 'A' and not(descendant::*[local-name() = 'B'])]

*/
// ============================================================================
// ============================================================================


// ============================================================================

	
?>
