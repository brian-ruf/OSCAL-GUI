<?php
session_start();
require_once('oscal-config.php');
require_once('oscal-functions.php');

// Sets the application's timezone based on the TIMEZONE
//     variable set in oalca-config.php
date_default_timezone_set(TIMEZONE);

$zone_init = array('zone-one' => '', 'zone-one-left' => '', 'zone-one-right' => '', 'zone-two' => '', 'zone-two-left' => '', 'zone-two-right' => '', 'zone-three' => '', 'zone-three-left' => '', 'zone-three-right' => '');
$script = "";


// $script is inserted into the javascript onload call.
// Must use single quotes inside the $script string, not 
// double-quotes. i.e.  "oscal-project?mode='new'"
if (array_key_exists('mode', $_GET)) {
switch($_GET["mode"]) {
	case 'verify':
		$zone_init['zone-two'] = FormNew("upload");
		$script = "zoneManagement('oscal-project.php?mode=verify')";
		$page_title = "New Project";
		break;
	case 'new':
		$zone_init['zone-one'] = MakeBackButton('./oscal.php?mode=continue');
		$zone_init['zone-two'] = FormNew("upload");
		unset($_SESSION["project_folder"]);
		unset($_SESSION["project_file"]);
		$page_title = "New Project";
		break;
	case 'continue':
		$script = "zoneManagement('oscal-project.php?mode=continue')";
		$page_title = "Open Project";
		break;
	case 'makeforms':
		$script = "zoneManagement('oscal-formcreator.php?type=" . $_GET["type"] . "')";
		$page_title = "Make Forms";
		break;
	case 'open':
		$script = "zoneManagement('oscal-content.php?mode=open&project=" . $_GET["project"] . "')";
		$page_title = "Opening Project ...";
		break;
/*
	case 'manage-catalog-metadata':
//		$zone_init['zone-two'] = FormNew("catalog-metadata");
		$zone_init['zone-two'] = "<iframe align='center' width='100%' height='auto' src='oscal-forms.php?form=catalog-metadata&project=" . $_GET["project"] . "' frameborder='no' scrolling='yes' name='catalog-metadata' id='catalog-metadata'></iframe>";
		$script = "ZoneAdjust('text-align: left; height: 800px; max-height: none;', 'zone-two');";
//		$script = "zoneManagement('oscal-content.php?form=manage-catalog-metadata&project=" . $_GET["project"] . "')";
		$form = "";
		$page_title = "Catalog Metadata";
		break;
*/
	default:
		// Unexpected mode
		$page_title = "INVALID";
		$script = "window.open('./index.php', '_self')";
		break;
	}
} else {
	$script = "window.open('./index.php', '_self')";
}

// Creates the drag and drop form
function FormNew($form_name) {
	$content = "";

	if (file_exists(OSCAL_FORM_FILES . "custom-" . $form_name . ".php")) {
		$form_file = OSCAL_FORM_FILES . "custom-" . $form_name . ".php";
	} else {
		$form_file = OSCAL_FORM_FILES . "generated-" . $form_name . ".php";
	}
	$content .= file_get_contents($form_file);
 
return $content;
	
	
}

?>
