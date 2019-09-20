<?php  
session_start();
session_write_close();
ignore_user_abort(true);

require_once('oscal-config.php');
require_once('oscal-functions.php');
require_once('oscal-formfunctions.php');

// Initialize Variables
$logging = "";
$content = "";
$output = "";
$status = false;
$form_base = $_GET["form"];
$project_id = urldecode($_GET["project"]);
$project_file = GetProjectDetail($project_id, 'file-with-path');

$oscal_doc_title = "";

if (file_exists($project_file)) {
	$base_dir = basename(dirname($project_file));
	$oscal = OpenOSCALfile($project_file, $project_id);

	$oscal_doc_title = QueryOneItem($oscal, OSCAL_METADATA_TITLE);
	if ($oscal_doc_title === false || $oscal_doc_title == "") {
		$oscal_doc_title = "[NO TITLE]";
	} 

	// Get appropriate form
	if (file_exists(OSCAL_FORM_FILES . "custom-" . $form_base . ".php")) {
		$form_name = OSCAL_FORM_FILES . "custom-" . $form_base . ".php";
	} elseif (file_exists(OSCAL_FORM_FILES . "generated-" . $form_base . ".php")) {
		$form_name = OSCAL_FORM_FILES . "generated-" . $form_base . ".php";
	} else {
		$form_name = "";
	}
	Logging("FORM FILE: " . $form_name);
	
	if ($form_name !== "") {
		if (isset($_GET["mode"]) && $_GET["mode"] == 'save') {
			$output .= SaveFormDataGeneric($oscal);
		}
		Logging("Getting form: " . $form_name);
		require_once($form_name);
		
		$function_name = str_replace("-", "_", $form_base) . "_form";
		if ( function_exists($function_name) ) {
			$output .= $function_name($oscal, $project_id);
		} else {
			switch($form_base) {
//				case 'form-name-1':
//					$output .= form_name_1_form($oscal, $project_id);
//					break;
				default:
					$output = "FORM NAME NOT RECOGNIZED";
					$output .= MakeBackButton();
					Logging("FORM NAME NOT RECOGNIZED: " . $form_name);
					break;
			}
		}

	} else {
		$output = "FORM NOT FOUND";
		$output .= MakeBackButton();
		Logging("FORM NOT FOUND: " . $form_name);

	}

	$oscal_doc_title = QueryOneItem($oscal, OSCAL_METADATA_TITLE); // GetProjectDetail($project_id, 'title', true);
	if ($oscal_doc_title === false || $oscal_doc_title == "") {
		$oscal_doc_title = "[NO TITLE]";
	} 

} else {
	$output = "<div>ERROR: FILE NOT FOUND!</div><div>PROJECT_LOCATION./" . $file . "</div>";
	$output .= MakeBackButton();
}

?>
<!DOCTYPE html>
<html>
<html lang="en">
	<title id='pagetitle'>OSCAL: <?= strip_tags($oscal_doc_title) ?></title>
	<link rel="stylesheet" type="text/css" media="all" href="default.css"/>
    <link rel="stylesheet" href="jodit.min.css">
	<script src='oscal-forms.js'></script>
    <script src="jodit.min.js"></script>
</head>

<!-- body onload="< ?= $script ?>" -->
<body>
<?php include 'header.php'; ?>
<section style="background-color:white;">

<div id='before-form' style='text-align: center; width: <?= SCREEN_WIDTH ?>; align: center; margin: 0 auto; '></div>

<div id='main-form' style='text-align: center; width: <?= SCREEN_WIDTH ?>; align: center; margin: 0 auto; '><?= $output ?></div>

<div id='after-form' style='text-align: center; width: <?= SCREEN_WIDTH ?>; align: center; margin: 0 auto; '></div>

</section>

<?php if (SHOW_DEBUG) : ?>
<section style="background-color:white;">
<table class='zone' style='width: 100%'>
	<tr>
		<td colspan='2'>
			<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
			<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
			<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
			<div id='debug-title' class='zone'>
				<hr />
				<b>DEBUG MESSAGES</b>&nbsp;
				[<span id='debug-menu' onclick="toggle_visibility('debug')">&nbsp;TOGGLE VISIBILITY&nbsp;</span>]
				&nbsp;&nbsp;To disable DEBUG MESSAGES, set <b>SHOW_DEBUG</b> to <b>false</b> in <b>oscal-config.php</b>
			</div></td>
	</tr>
	<tr>
		<td colspan='2'><div id='debug' class='debug'><?= $logging ?></div></td>
	</tr>
</table>
</section>
<?php endif ?>
	
<?php include 'footer.php'; ?>
</body>
</html>

<?php

// ============================================================================
function NoFileFound($file) {



return $output;
}


// ============================================================================
function Logging($content){
global $logging;

$logging .= "\n-- " . $content . "<br />";
	
}

// ============================================================================
function SaveFormDataGeneric($oscal) {
	$changes_detected = false;
	$definition = array();
	global $logging;
	

	/* ********
	Start with spec in array
	Use array to traverse DOMdcument 
		Load ORIGINAL data from DOMdocument object into array
		Save node object pointer (and parent - grandparent will be saved within parent)
		Grow array as needed
		Mark ORIGINAL array nodes as not populated where no data in DOMdocument
		
	Use array to traverse Form
		Load FORM data into array
		Grow array as needed
		Mark FORM array nodes as not populated where no data in form
		Populate changed flag as false
		Compare FORM to ORINGAL for each node, and mark as changed flag as true where differences found
		
	Make changes
		To DOMdocument object
		For changes, make change
		For inserts, use array to identify order
		For deletions,

	*/
	
	foreach($_POST as $key => $form_field) {
		Logging("PROCESSING: <strong>" . $key . "</strong>");
		$element_name = basename($key);
		$xpath = "//" . $key;
		
		foreach ($form_field as $sub_key => $form_sub_field) {
			Logging("PROCESSING: <strong>" . $sub_key . "</strong>");
			
			// In the loop below, the first array item should alwyas have
			//    a single element ($field_key =='0').
			// Any additional items in the array are an attributes (flags).
			// For these additional items, the named key is the attribute name. 
			foreach ($form_sub_field as $field_key => $item) {
				if ($field_key == '0') {
					Logging(" FIELD:");
				} else {
					Logging(" (@" . $field_key . "):");
				}
				$xml_friendly = utf8_encode(html_entity_decode($item, null, "UTF-8"));

				Logging("<textarea rows='2' cols='80'>" . $xml_friendly . "</textarea>");
			}
		}
	}

	/*
	// Cycle through each form field (skip the ___definition field)
	foreach($_POST as $key => $form_field) {
		if (strpos($key, "___definition") === false) {
			$logging .= "PROCESSING: <strong>" . $key . "</strong><br />";
			
			// $xml_friendly_data = utf8_encode(html_entity_decode($dirty_data, null, "UTF-8"))
			

			// Try to reload the original from the XML file
			$xpath = "//" . $key;

			$branch = QueryListResult($oscal, $xpath);
			if ( $branch !== false) { // if the original XML data is found
				if ($branch->length == count($form_field)) {
					$cntr = 1;
					foreach ($branch as $node) {
						// 1. Process the fields
						if (strcmp($node->nodeValue, $form_field[$cntr][0]) === 0) {
							Logging("No change in data. Do not bother to update field.");
						} else {
							Logging("Data does not match. Update field.");
							$changes_detected = true;
							// Modify XML
							$node->nodeValue = Clean_up($form_field[$cntr][0]);
						}
							
						// 2. Process any flags
						ProcessFlags($form_field, $cntr, $node, $changes_detected);
						$cntr +=1;
					}
				} else {
					Logging ("The number of elements in the form do not match the number of elements in the XML file for //" . $key);
				}
			} else {  // if the original XML data is NOT found
				if (strlen($form_field[1][0]) > 0) {
					// No data in the file, but data in the form. Need to add new node
					Logging ("No data in XML file for //" . $key . ", but data in form. Adding.");
					$element_name = basename($key);
					$parent_xpath = "//" . substr($key, 0, strlen($key) - strlen($element_name) -1);
					
					Logging("NEW: <strong>" . $element_name . "</strong> with " . $parent_xpath . " parent.");
					
					$newdoc = new DOMDocument;
					$newdoc->formatOutput = true;
					$parent_node = QueryList($oscal, $parent_xpath);  // Make sure parent node exists
					if (! $parent_node === false) { // Parent node found
						Logging("Parent found!");
						//	$newdoc->loadXML("<root><" . $element_name . ">" . $form_field[1][0] . "</" . $element_name . "></root>");
						//	echo htmlspecialchars($newdoc->saveXML());
						//	$parent_node = joinXML($parent_node, $newdoc);
						//	$node = $newdoc->documentElement;
						//	$node = $parent_node->importNode($node, true);
						$newnode = $oscal['DOM']->createElement($element_name); // , $form_field[1][0]);
						$newnode = $parent_node->appendChild($newnode);
						$newnode->nodeValue = $form_field[1][0];
						$changes_detected = true;

						// ***** FINISH INSERT HERE
					} else { // Parent node NOT found. Need to add both this and the parent to the grandparent.
						Logging("Parent node NOT found!" );
						$parent_element = basename($parent_xpath);
						$grandparent_xpath = "//" . substr($parent_xpath, 0, strlen($parent_xpath) - strlen($parent_element) -1);
						$grandparent_node = QueryListResult($oscal, $grandparent_xpath);  // Make sure parent node exists
						if (! $grandparent_node === false) { // Grandparent node found
							Logging("Grandparent node found!");
							$newdoc->loadXML("<root><" . $parent_element . "><" . $element_name . ">" . $form_field[1][0] . "</" . $element_name . "></" . $parent_element . "></root>");
							// ***** FINISH INSERT HERE
						} else {
							Logging("Grandparent node NOT found!");
						}
					}

					// Import the node, and all its children, to the document
//					$node = $oscal['DOM']->importNode($new_node, true);
					// And then append it to the "<root>" node
//					$newdoc->documentElement->appendChild($node);				
					//	ProcessFlags($form_field, $cntr, $node, $changes_detected);
					//	$changes_detected = true;
				}
//			} else {
				// OK to ignore
//				Logging ("No data in XML file for //" . $key . ", and no data in form. Ignoring.");
			}
		}
	}
	*/
	
	if ($changes_detected) {
		// Write to file
		SaveOSCALfile($oscal);
		echo ("<span style='color:red; font-weight:bold;'>UPDATES SAVED!</span><br />");
	} else {
		echo ("<span style='color:red; font-weight:bold;'>NO CHANGES DETECTED! (FILE NOT UPDATED)</span><br />");
	}
	
//	echo var_dump($_POST);
}
// ============================================================================
// 
function Clean_up($form_field_data, $form_field_type='text') {
	$ret_val = "";
	$ret_val = trim($form_field_data);

//	$find_this         = array("<p>", "</p>", "<br>", "&lt;p&gt;", "&lt;br&gt;", "&lt;/p&gt;");
//	$replace_with_this = array("",    "",     "",     "",          "",           "");
//	$ret_val = str_replace($find_this, $replace_with_this, $ret_val);
	
	if ($form_field_type == 'text') {
		if (right_str($ret_val, 11) == '<p><br></p>') {
			$ret_val = left_str($ret_val, strlen($ret_val) - 11);
		}
		if (right_str($ret_val, 29) == "&lt;p&gt;&lt;br&gt;&lt;/p&gt;") {
			$ret_val = left_str($ret_val, strlen($ret_val) - 29);
		}
	}
	
	return $ret_val;
}


// ============================================================================
function ProcessFlags($form_field, $cntr, &$node, &$changes_detected) {
	
	$flag_cntr = 1;
	$flag_count = count($form_field[$cntr]) -1;
	foreach ($form_field[$cntr] as $flag_key => $flag_value) {
		if ($flag_cntr > 1) { // Essentially skip the first, because that's the field, not a flag
			if ($node->hasAttribute($flag_key)) {
				// compare and update if different
				if ($node->getAttribute($flag_key) == $flag_value) {
					// No change. Ignore
				} else {
					$changes_detected = true;
					$node->setAttribute($flag_key, $flag_value);
				}
			} else {
				if (strlen($flag_value) > 0 ) {
					$changes_detected = true;
					$node->setAttribute($flag_key, $flag_value);
				}
			}
		}
		$flag_cntr += 1;
	}
}

// ============================================================================
// Possible dead code - consider removing.
function ManageMetadata($oscal, $html){
	global $logging;

$metadata = QueryList($oscal, OSCAL_METADATA);
$before_form = $html['XPATH']->query("//div[@id='before-form']")[0];
$form_shell = $html['XPATH']->query("//div[@id='form-main']")[0];
$after_form = $html['XPATH']->query("//div[@id='after-form']")[0];
$debug_div = $html['XPATH']->query("//div[@id='debug']")[0];

// . . . . . . . .

// If a custom form exists, use it. Otherwise, use the one created by the
//          form generator.
if (file_exists(OSCAL_FORM_FILES . "custom-catalog-metadata.php")) {
	require_once(OSCAL_FORM_FILES . "custom-catalog-metadata.php");
//	$form_file = OSCAL_FORM_FILES . "custom-catalog-metadata.php";
} else {
	require_once(OSCAL_FORM_FILES . "generated-catalog-metadata.php");
//	$form_file = OSCAL_FORM_FILES . "generated-catalog-metadata.php";
}


}

/*
$oscal_metadata = json_decode(file_get_contents(OSCAL_LOCAL_FILES . "oscal-metadata-metamap.json"), true);
// $input = file_get_contents($form_file);
$input = MetaMap2Form($oscal_metadata['metadata'], $metadata);

$newnode = $html['HTML']->createDocumentFragment();
$newnode->appendXML($input);
$before_form->appendChild($newnode);


$newnode = $html['HTML']->createDocumentFragment();
$newnode->appendXML($logging);
$debug_div->appendChild($newnode);

$html['HTML']->preserveWhiteSpace = false;
$html['HTML']->formatOutput = true;
echo $html['HTML']->saveHTML();
*/
	

/*
$d = new DOMDocument();
$d->loadHTML($yourWellFormedHTMLString);

$xpathsearch = new DOMXPath($d);
$nodes = $xpathsearch->query('//div[contains(@class,'main')]');  

foreach($nodes as $node) {
    $newnode = $d->createDocumentFragment();
    $newnode->appendXML($yourCodeYouWantToFillIn);
    $node->appendChild($newnode);
}
*/	

// ============================================================================
// ============================================================================
// ============================================================================

	
?>
