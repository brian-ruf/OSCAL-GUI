<?php  
// ============================================================================
// CODE GENERATION: Everything in this php file generates source code.
//      It is PHP that generates PHP, HTML 5, and JavaScript.
//      It uses the metaschema array created in oscal-schema-map.php to  
//      generae php modules that will later create forms from the XML content.
//
//      This is important, as the form creator is unable to "know" the state
//      of any given XML file at the time this is run.
//      

session_start();
session_write_close();
ignore_user_abort(true);

require_once('oscal-begin.php');
require_once("oscal-zones.php");
require_once('oscal-functions.php');
require_once('oscal-formfunctions.php');

define("formType", "post");
define("tagID", "**id**");
define("tagMultiple", "**Multiple**");
define("tagValue", "");
define("EMPTY_MESSAGE", "** EMPTY **");

// Initialize Variables
$status = false;
$metaschema_map = array();

ZoneAdjust("max-height: none; ");


// 
$type = $_GET["type"];
switch($type) {

	case 'common':
		CreateCommonForms();
		break;
	case 'catalog':
		// CreateCatalogForms();
		ZoneAdjust("font-size: 1.7em; color: red;", "zone-one");
		ZoneOutput("CATALOG FORMS NOT YET AVAILABLE!", 'zone-one');
		break;
	case 'profile':
		// CreateProfileForms();
		ZoneAdjust("font-size: 1.7em; color: red;", "zone-one");
		ZoneOutput("PROFILE FORMS NOT YET AVAILABLE!", 'zone-one');
		break;
	case 'ssp':
		// CreateSSPForms();
		ZoneAdjust("font-size: 1.7em; color: red;", "zone-one");
		ZoneOutput("SSP FORMS NOT YET AVAILABLE!", 'zone-one');
		break;
	case 'component':
		// CreateComponentForms();
		ZoneAdjust("font-size: 1.7em; color: red;", "zone-one");
		ZoneOutput("COMPONENT FORMS NOT YET AVAILABLE!", 'zone-one');
		break;
	default:
		// Unexpected type
		$title = "INVALID TYPE: " . $_GET["type"];
		ZoneAdjust("font-size: 0.7em; color: red;", "header-additional");
		ZoneOutput($title, 'header-additional');
		break;
	}
	
	ZoneOutputAppend(MakeBackButton("./oscal-tools-advanced.php"));
	
ZoneClose();
// End Main

// ============================================================================
// Forms for the Common OSCAL constructs (Metadata, back-matter, etc.) can 
// be generated from any of the NIST OSCAL schema files. The Catalog schema
// is used here, because it was the fist and is typically the most up-to-date.
// ============================================================================
function CreateCommonForms() {
	$file_output = "";
	$messages = "";
	$root = 'catalog';
//	$metamap_array_name = "$" . "oscal_common";
	$oscal_catalog = array();

	
	ZoneOutput("<span style='font-size: 16px; font-weight: bold;'>Common</span><br />" , 'zone-one'); // .	"<span style='color:red;'>This may take several minutes. Please be patient.</span><br />"
	ZoneOutputAppend("Creating Basic Metadata forms.<br />", 'zone-two');

//	ZoneOutputAppend($messages, 'zone-two');

	// Get the Catalog Schema Map
	$metaschema = GetSchemaMap("catalog");

//	$element = "metadata";
	$metaschema_branch = $metaschema["model"]["metadata"];
	$exclude = array("prop", "link", "role", "party");
	$form_name = "metadata-basic";
	$form_title = "Document Properties";
	$form_result  = CreateForm($metaschema_branch, $exclude, $form_name, $form_title);
	
//	$element = "party/person";
	$metaschema_branch = $metaschema["model"]["metadata"]["model"]["party"];
	$exclude = array("org");
	$form_name = "metadata-party-person";
	$form_title = "Individuals";
	$form_result  = CreateForm($metaschema_branch, $exclude, $form_name, $form_title);

//	$element = "party/org";
	$metaschema_branch = $metaschema["model"]["metadata"]["model"]["party"];
	$exclude = array("person");
	$form_name = "metadata-party-org";
	$form_title = "Organizations";
	$form_result  = CreateForm($metaschema_branch, $exclude, $form_name, $form_title);

//	$element = "role";
	$metaschema_branch = $metaschema["model"]["metadata"]["model"]["role"];
	$exclude = array();
	$form_name = "metadata-role";
	$form_title = "Roles";
	$form_result  = CreateForm($metaschema_branch, $exclude, $form_name, $form_title);

/*
		CreateMetadataForm();
		CreateRolesForm();
		CreateParitesForm();
		CreateAttachmentForm();
*/
}

// ============================================================================
// Takes the complete metamap for an OSCAL root, and reduces it to just the 
//      portion for which a form should be generated. 
// 
function MetamapScope($metaschema_branch, $exclude=array()){
	$ret_val = array();
	
	foreach ($metaschema_branch as $branch_key => $branch_element) {
		if ($branch_key == "model") {
			$ret_val["model"] = array();
			foreach ($metaschema_branch["model"] as $child_key => $child) {
//				ZoneOutputAppend("<pre>" . json_encode($ret_val, JSON_PRETTY_PRINT) . "</pre>");
				if (! in_array($child["name"], $exclude)) {
					$ret_val["model"][$child_key] = MetamapScope($child, $exclude);
				}
			}
		} else {
			$ret_val[$branch_key] = $branch_element;
		}
	}
	Logging("<pre>" . json_encode($ret_val, JSON_PRETTY_PRINT) . "</pre>");

	return $ret_val;
}
// ============================================================================
// ============================================================================
function CreateForm($metaschema_branch, $exclude=array(), $form_name, $form_title) {
global $metaschema_map;
$output = "";
$status = false;

	ZoneOutputAppend("<br /><hr /><br />\nGENERATING: <span style='font-weight: bold;'>" . $form_title . "</span><br />\n", "zone-two");
	Logging("GENERATING: " . $form_title);
	
	$metaschema_branch = MetamapScope($metaschema_branch, $exclude);
	
	if (! $form_name == "") {
		$output = MetaMap2Function($metaschema_branch, $form_name);
		$output_file = OSCAL_FORM_FILES . "generated-" . $form_name . ".php";
		$status = PublishForm($output_file, $output);
	}

	return $status;
}

// ============================================================================
function CreateCatalogForms() {
	$file_output = "";
	$root = 'catalog';
	$metamap_array_name = "$" . "oscal_catalog";
	$oscal_catalog = array();

	ZoneOutput("<span style='font-size: 16px; font-weight: bold;'>" . ucwords($root) . "</span><br />" .
		"<span style='color:red;'>This may take several minutes. Please be patient.</span><br />", 'zone-one');
	ZoneOutput("Creating " . $root . " forms.<br />", 'zone-two');

	// Get the Catalog Schema Map
	$metaschema = getSchemaMap("catalog");

	// Compile whole metaschema tree and save to JSON file
	$element = "catalog";
	$exclude = array("prop", "resource", "hlink", "role", "party", "extra-meta");
	$form_name = "";
	$oscal_catalog_full = CreateForm($metaschema, $element, $exclude, $form_name, $metamap_array_name );
	$file_output = json_encode($oscal_catalog_full, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
	if (file_put_contents(OSCAL_LOCAL_FILES . "oscal-catalog-metamap.json", $file_output) === false) {
		Logging("ERROR WRITING FILE");
	}
		
	// Compile porton of metaschema tree, and create form (metadata)
	$element = "metadata";
	$exclude = array("prop", "resource", "hlink", "role", "party", "extra-meta");
	$form_name = $root . "-" . $element;
	$oscal_catalog [$element] = CreateForm($metaschema, $element, $exclude, $form_name, $metamap_array_name );
	
	// Compile porton of metaschema tree, and create form (party)
	$element = "party";
	$exclude = array("address");
	$form_name = $root . "-" . $element;
	$oscal_catalog [$element] = CreateForm($metaschema, $element, $exclude, $form_name, $metamap_array_name );
	
	// Compile porton of metaschema tree, and create form (role)
	$element = "role";
	$exclude = array();
	$form_name = $root . "-" . $element;
	$oscal_catalog [$element] = CreateForm($metaschema, $element, $exclude, $form_name, $metamap_array_name );
	
	// Compile porton of metaschema tree, and create form (address)
	$element = "address";
	$exclude = array();
	$form_name = $root . "-" . $element;
	$oscal_catalog [$element] = CreateForm($metaschema, $element, $exclude, $form_name, $metamap_array_name );
	
	// Compile porton of metaschema tree, and create form (param)
	$element = "param";
	$exclude = array();
	$form_name = $root . "-" . $element;
	$oscal_catalog[$element] = CreateForm($metaschema, $element, $exclude, $form_name, $metamap_array_name );

	// Compile porton of metaschema tree, and create form (subcontrol)
	$element = "subcontrol";
	$exclude = array("ref-list", "part");
//					['element' => "part", 'attribute' => "class", 'value' => "objective"],
//					['element' => "part", 'attribute' => "class", 'value' => "assessment"] );
	$form_name = $root . "-" . $element;
	$oscal_catalog[$element] = CreateForm($metaschema, $element, $exclude, $form_name, $metamap_array_name );

	// Compile porton of metaschema tree, and create form (part)
	$element = "part";
	$exclude = array(); // NOTE: part self-references. Code now detects that and won't loop.
	$form_name = $root . "-" . $element;
	$oscal_catalog[$element] = CreateForm($metaschema, $element, $exclude, $form_name, $metamap_array_name );
	
	// Compile porton of metaschema tree, and create form (ref-list)
	$element = "ref-list";
	$exclude = array(); // ref-list self-references. Code now detects that and won't loop.
	$form_name = $root . "-" . $element;
	$oscal_catalog[$element] = CreateForm($metaschema, $element, $exclude, $form_name, $metamap_array_name );
	
	ZoneOutputAppend("<span style='color:red; font-weight:bold;'>DONE!</span><br />", 'zone-one');

}

// ============================================================================
function CreateProfileForms() {

/*

NOT READY
NEEDS WORK
TBD for this Milestone Release

*/

	$file_output = "";
	$root = 'profile';
	$metamap_array_name = "$" . "oscal_profile";
	$oscal_catalog = array();

	ZoneOutput("<span style='font-size: 16px; font-weight: bold;'>" . ucwords($root) . "</span><br />" .
		"<span style='color:red;'>This may take several minutes. Please be patient.</span><br />", 'zone-one');
	ZoneOutput("Creating " . $root . " forms.<br />", 'zone-two');

	// Get the collapsed Profile Metaschema
	$metaschema = OSCAL_LOCAL_FILES . "composed-oscal-" . $root . "-metaschema.xml";

	// Compile whole metaschema tree and save to JSON file
	$element = "profile";
	$exclude = array("prop", "resource", "hlink", "role", "party", "extra-meta");
	$form_name = "";
	$oscal_catalog_full = CreateForm($metaschema, $element, $exclude, $form_name, $metamap_array_name );
	$file_output = json_encode($oscal_catalog_full, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
	if (file_put_contents(OSCAL_LOCAL_FILES . "oscal-profile-metamap.json", $file_output) === false) {
		Logging("ERROR WRITING FILE");
	}
	
/*
	
	$root = 'profile';
	ZoneOutput("<span style='font-size: 16px; font-weight: bold;'>" . ucwords($root) . "</span>", 'zone-one');
	ZoneOutput("Creating " . $root . " forms.<br />", 'zone-two');

	$metaschema = OSCAL_LOCAL_FILES . "composed-oscal-" . $root . "-metaschema.xml";

	$element = "metadata";
	$exclude = array("prop", "resource", "hlink", "role", "party", "extra-meta");
	$form_name = $root . "-" . $element;
	CreateForm($metaschema, $element, $exclude, $form_name );
*/

	
}

// ============================================================================
function CreateSSPForms() {

	$root = 'implementation';
	ZoneOutput("<span style='font-size: 16px; font-weight: bold;'>" . ucwords($root) . "</span>", 'zone-one');

	ZoneOutput("Coming soon!<br />", 'zone-two');
	
}

// ============================================================================
function CreateComponentForms() {

	$root = 'implementation';
	ZoneOutput("<span style='font-size: 16px; font-weight: bold;'>" . ucwords($root) . "</span>", 'zone-one');

	ZoneOutput("Coming soon!<br />", 'zone-two');
	
}

// ============================================================================
// May be dead code
//
function StopHere($parent_name, $stop_at_list, $child_name) {
	$ret_val = false;
	
	if ($parent_name == $child_name) {  // If the assembly lists itself as a child, stop.
		$ret_val = true;
	} else {
		foreach ($stop_at_list as $stop_at) { 
			if ($stop_at == $child_name) {     // If the child is in the stop list, stop.
				$ret_val = true;
				break;
			}
		}
	}
	
	return $ret_val;
}

// ============================================================================
function PublishForm($out_file, $output) {
	$status = false;
	
	$myfile = fopen($out_file, "w") or die("Unable to open file!");
	if ($myfile !== false) {
		$result = fwrite($myfile, $output);
		if ($result !== false) {
			$status = true;
		}
		fclose($myfile);
	}
return $status;
	
}

// ============================================================================
// ============================================================================
// CODE GENERATION: 
// This generates the php code that establishes a function with a name based 
//      on the highest-level element handled by the form.
//
// This is called from ____ in oscal-formcreator.php.
// This returns the generated function as php code within a string variable.
// The calling function saves the generated code as a php file, suitable for
//      use as an include file.
//
// These include files are used by oscal-forms.php, which calls the generated
//      function to present the XML data in browser using HTML 5 form functions.
// NOTE: oscal-forms.php has separate code to handle the processing of form
//      submission. 
//      
// MetaMap2Function specifically generates php code that can later create:
//      - the HTML 5 document structure start/end tags
//                       (html, head, body, and basic structure)
//      - the form start/end tags
//      - debugging output div zone, if SHOW_DEBUG set to true in oscal-config.php
//      
// The rest of the content is generated by MetaMap2Form (below)
//
function MetaMap2Function($definition, $form_name=""){
	$indent = 0;
	$output = "";
	
if ($form_name == "") {
	$form_name = "form-" . $definition["name"];
}
	
$output .= "\n<" . "?php";

$date = new DateTime('NOW');

$output .= AddComment("GENERATED " . $date->format('Y-m-d -- H-i-s'), 'php', false);


$output .= NLandTabs($indent). "function " . str_replace("-", "_", $form_name) . "_form($" . "oscal, $" . "project_id, $" . "form_only=false) {";
$indent += 1;
$output .= NLandTabs($indent). "global $" . "logging;";
$output .= NLandTabs($indent). "$" . "html = '';";
$output .= NLandTabs($indent). "$" . "metadata = QueryList($" . "oscal, OSCAL_METADATA);";
$output .= NLandTabs($indent). "$" . "project_file = GetProjectDetail($" . "project_id, 'file-with-path');";

$output .= BuildXMLQueries($definition, $indent); // Generates the XPATH calls to the content

$output .= NLandTabs($indent). "$" . "html .= \"<form id='form-" . $form_name . "' method='post' action='./oscal-forms.php?mode=save&form=" . $form_name . "&project={" . "$" . "project_id}'>\";";

/* $output .= NLandTabs($indent). "$" . "html .= \"<input ";
$output .= " id='" . $form_name . "___definition'";
$output .= " name='" . $form_name . "___definition'";
$output .= " type='hidden'";
$output .= " value='" . encodeURIComponent(json_encode($definition)) . "'";
$output .= " />\";";
$output .= NLandTabs(0). "/" . "/ ". json_encode($definition) . "'";
*/

$output .= MetaMap2Form($definition, $indent);  // Generates the actual <input> fields and supporting code

// Generate the code that makes the form buttons
$output .= NLandTabs($indent++). "$" . "buttons = array(";
$output .= NLandTabs($indent). "[\"text\" => \"Go Back\", \"img\" => \"./img/arrow-left.png\", \"action\" => \"goBack('./oscal.php?mode=open&project={" . "$" . "project_id}')\"],";
// $output .= NLandTabs($indent). "[\"text\" => \"Reset\", \"img\" => \"./img/close.png\", \"action\" => \"clearForm('form-" . $form_name . "')\"],";
$output .= NLandTabs($indent). "[\"text\" => \"Commit Changes\", \"img\" => \"./img/edit.png\", \"action\" => \"submitForm('form-" . $form_name . "')\"]";
$output .= NLandTabs(--$indent). ");";

$output .= "\n$" . "html .= MakeMenu($" . "buttons, false, true);";
$output .= "\n$" . "html .= \"</form>\n\";";

$output .= NLandTabs($indent). "return $"."html;\n}\n";
$output .= AddComment("BASED ON THE FOLLOWING STRUCTURE:\n" . json_encode($definition, JSON_PRETTY_PRINT), 'php', false);
$output .= "\n?>\n\n";


// ZoneOutputAppend($form);
	
return $output;	
}

// ============================================================================
// ============================================================================
function MetaMap2Form($definition, $indent=0, $path="", $first_time=true){
	$output="";
	$indent +=1;

	if ($path == "") {
		$path = "//". $definition["name"];
	} else {
		$path .= "/". $definition["name"];
	}
	$output .= NLandTabs($indent). "// MetaMap2Form Generated Content - START (" . $path . ")";
	$field_var_name = field_var_name($path);
	$cntr_name = $field_var_name . "__cntr";

		// Generate PHP code to loop through 1 or more instances of a field or assembly
		$output .= NLandTabs($indent). $cntr_name . " = 0;";
		$output .= NLandTabs($indent). $cntr_name . "_max = count(" . $field_var_name . ");";
		// LOOP START
		$output .= NLandTabs($indent) . "foreach (" . $field_var_name . " as " . $field_var_name . "_item)  { ";
		$indent += 1;
		$output .= NLandTabs($indent). $cntr_name . " += 1;";
		if ($first_time) {
			$output .= NLandTabs($indent). "$" . "html .= \"<table class='form' style='width=100%;'>\";";
		}

		$output .= MakeFormRow ($definition, $indent, $field_var_name . "_item", $cntr_name, $path); // ********

		if ($first_time) {
			$output .= NLandTabs($indent). "$" . "html .= \"</table>\";";
		}

		$indent -=1;
		$output .= NLandTabs($indent). "} "; // END LOOP

	$output .= NLandTabs($indent). "// MetaMap2Form Generated Content - END";

	return $output;
}



// ============================================================================
// ============================================================================

	
?>
