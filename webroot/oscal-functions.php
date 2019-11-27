<?php
// OSCAL Functions
//
// These are common OSCAL functions, needed in two or more scripts

// SECTIONS:
// ==  Xpath Manipulation Functions
// ==  Xpath Query Functions
// ==  Compiled Schema Functions
// ==  XML manipulation functions
// ==  OSCAL-specific functions
// ==  JSON Conversion (JSON to/from XML) Functions
// ==  XML Error Handling
// ==  Other XML Functions
// ==  UI Generation Functions
// ==  Form Helper Functions
// ==  Miscellaneous Helper Functions

require_once('oscal-config.php');

// Enable user error handling
libxml_disable_entity_loader(false);
// libxml_use_internal_errors(false);
libxml_use_internal_errors(true);

$oscal_objects = array();
$oscal_schema_map = array();
$oscal_flat_schema_map = array();
$messages = "";

// ============================================================================
// ==  Xpath Manipulation Functions
// ============================================================================

// ----------------------------------------------------------------------------
// removes attributes from the xpath statement
function StripAttributes($xpath) {

	$ret_val = "";
	$start = 0;

	$open_bracket = strpos($xpath, "[");
	if ($open_bracket === false) {
		$ret_val = $xpath;
	} else {
		while ($open_bracket !== false) {
			$close_bracket = strpos($xpath, "]", $open_bracket + 1);
			if ($close_bracket !== false) {
				$ret_val .= substr($xpath, $start, $open_bracket - $start);
				$start = $close_bracket + 1;
				$open_bracket = strpos($xpath, "[", $start);
			} else {
				$open_bracket = strpos($xpath, "[", $start +1);
			}
		}
		$ret_val .= substr($xpath, $close_bracket +1, strlen($xpath) - $close_bracket);
	}
	Messages ("STRIPED XPATH: " . $ret_val);

return $ret_val;
}

// ----------------------------------------------------------------------------
// Returns the full xpath to $element (DOMNode)
// ----------------------------------------------------------------------------
function GetFullXpath($element){
	$ret_val = "/" . $element->nodeName;
	
	while ($element->parentNode !== null and $element->parentNode->nodeName !== "#document"){
		$ret_val = "/" . $element->parentNode->nodeName . $ret_val; 
		$element = $element->parentNode;
		
	}
	
	$ret_val = "/" . $ret_val;
//	Messages("FULL PATH: " . $ret_val);
	return $ret_val;
}

// ----------------------------------------------------------------------------
function AddNamespace2xpath($query, $ns) {
	$temp = "";
	$q_len = strlen($query);
	$prev_char = "";

	for($i=0; $i < $q_len ; $i++) {
		$cur_char = substr($query, $i, 1);
		if ($prev_char === '/') {
			if (ctype_alpha($cur_char)) {
				$temp .= $ns . ":";
			};
		}
		$temp .= $cur_char;
		$prev_char = $cur_char;
	}
	
	return $temp;
}

// ----------------------------------------------------------------------------
// This is called by GetValidationErrorDetails, and just removes the namespace
// from error messages to make them more human-friendly.
// This is especially important when trying to understand a list of
// non-compliant findings after validating an XML document against a schema.
function strip_namespace($message) {
	$retval = $message;

	$bracket_start = strpos($retval, "{http:");
	if ( $bracket_start === false) {
		$bracket_start = strpos($retval, "{urn:");
		}
		
	while (! $bracket_start === false) {
		$bracket_end = strpos($retval, "}", $bracket_start);
		if ($bracket_end > 0) {
			$temp = $retval;
			$retval = substr($temp, 0, $bracket_start) . substr($temp, $bracket_end + 1, strlen($temp));
		}
		$bracket_start = strpos($retval, "{http:");
		if ( $bracket_start === false) {
			$bracket_start = strpos($retval, "{urn:");
			}
	}
	return($retval);
}

// ============================================================================
// ==  Xpath Query Functions
// ============================================================================

// ----------------------------------------------------------------------------
function QueryOneItem(&$oscal_objects, $query) {
$logging = "** QueryOneItem (" . $query . "):<br />";

	if ($oscal_objects["namespace"]==="") {
		// Do nothing
	} else {
		$query = AddNamespace2xpath($query, $oscal_objects["namespace"]);
	}

	$logging .= "ONE-ITEM XPATH: " . $query;
	$result = $oscal_objects["XPATH"]->query($query);

	if ($result !== false) {
		if ($result->length > 0) {
			$logging .= " FOUND: " . $result->length;
			$ret_val = trim($result->item(0)->nodeValue);
		} else {
			$logging .= " FOUND: -0-";
			$ret_val = false;
		}
	} else {
		$ret_val = false;
		$logging .= "ERROR: Invalid Xpath Query!";
	}
	
//	Messages($logging);
 	return $ret_val;
}

// ----------------------------------------------------------------------------
function QueryList(&$oscal_objects, $query) {
	$logging = "** QueryList (" . $query . "):<br />";
	$ret_val = false;

	if ($oscal_objects["namespace"]==="") {
		// Do nothing
	} else {
		$query = AddNamespace2xpath($query, $oscal_objects["namespace"]);
	}

	$logging .= " LIST XPATH: " . $query;
	$result = $oscal_objects["XPATH"]->query($query);
//	$logging .= " = (TYPE: " . gettype($result) . ")";
	
	if ($result !== false) {
		if ($result->length > 0) {
			$logging .= " FOUND: " . $result->length;
			$ret_val = $result->item(0);
		} else {
			$logging .= " FOUND: -0-";
			$ret_val = false;
		}
	} else {
		$logging .= "ERROR: Invalid Xpath Query!";
		$ret_val = false;
	}

//	Messages($logging);
 	return $ret_val;
}

// ----------------------------------------------------------------------------
function QueryListResult(&$oscal_objects, $query) {
	$logging = "** QueryListResult (" . $query . "):<br />";
	$ret_val = false;

	if ( $oscal_objects["namespace"]==="") {
		$query = AddNamespace2xpath($query, "*");
	} else {
		$query = AddNamespace2xpath($query, $oscal_objects["namespace"]);
	}
	
	$logging .= " LIST XPATH: " . $query;
	$result = $oscal_objects["XPATH"]->query($query);
//	$logging .= " = (TYPE: " . gettype($result) . ")";

	if ($result !== false) {
		if ($result->length > 0) {
			$logging .= " FOUND: " . $result->length;
			$ret_val = $result;
		} else {
			$logging .= " FOUND: -0-";
			$ret_val = false;
		}
	} else {
		$logging .= "ERROR: Invalid Xpath Query!";
		$ret_val = false;
	}

//	Messages($logging);
 	return $ret_val;
}

// ----------------------------------------------------------------------------
// Possible Dead Code - Consider Removing
function QueryOneItemArray(&$oscal_objects, $query, $flags=array()) {
	$logging = "** QueryOneItemArray (" . $query . "):<br />";
	$ret_val = array();
	$flag_values=array();

	if ($oscal_objects["namespace"]==="") {
		// Do nothing
	} else {
		$query = AddNamespace2xpath($query, $oscal_objects["namespace"]);
	}
	$logging .= "ONE-ITEM XPATH: " . $query;
	$result = $oscal_objects["XPATH"]->query($query);

	if ($result !== false) {
		if ($result->length > 0) {
			$ret_val['value'] = trim($result->item(0)->nodeValue);
		} else {
			$ret_val['value'] = false;
		}
		if (count($flags)>0) {
			foreach ($flags as $flag) {
				if ($result->item(0)->hasAttribute($flag)) {  // ********
					$flag_values[$flag] = htmlspecialchars($result->item(0)->getAttribute($flag)) ;
				} else {
					$flag_values[$flag] = "";
				}
			}
		} 
		$ret_val['attributes'] = $flag_values;
	} else {
		$logging .= "ERROR: Invalid Xpath Query!";
		$ret_val = array();
	}

//	Messages($logging);
 	return $ret_val;
}

// ----------------------------------------------------------------------------
// Performs an XPATH query and generates an array
//
// Array format is as follows for no result (no elements found that match the XPATH query):
//    $rev_val[0][ 'value' => null, 'flags' => Array('Attribute Name' => 'Attribute Value')] 
//
// Array format is as follows for one element found:
//    $rev_val[0][ 'value' => 'Element Value', 'flags' => Array('Attribute Name' => 'Attribute Value')] 
//
// Array format is as follows two or more elements found:
//    $rev_val[0][ 'value' => 'Element Value', 'flags' => Array('Attribute Name' => 'Attribute Value')] 
//    $rev_val[1][ 'value' => 'Element Value', 'flags' => Array('Attribute Name' => 'Attribute Value')] 
//       . . .
//    $rev_val[n][ 'value' => 'Element Value', 'flags' => Array('Attribute Name' => 'Attribute Value')] 
function QueryListArray(&$oscal_objects, $query, $flags=array()) {
	$logging = "** QueryListArray (" . $query . "):<br />";
	$ret_val = array();
	$flag_values=array();

	if ($oscal_objects["namespace"]==="") {
		// Do nothing
	} else {
		$query = AddNamespace2xpath($query, $oscal_objects["namespace"]);
	}

	$logging .= " LIST XPATH: " . $query;
	$result = $oscal_objects["XPATH"]->query($query);
//	$logging .= " TYPE: " . gettype($result);
//	$logging .= " Length: " . $result->length;
	
	if ($result !== false) {
		if ($result->length == 0) {
			if (count($flags)>0) {
				foreach ($flags as $flag) {
						$flag_values[$flag] = "";
				}
			} 
			array_push($ret_val, array('value' => null, 'flags'=> $flag_values));
			
		} else {
			foreach ($result as $item) {
				if (count($flags)>0) {
					foreach ($flags as $flag) {
						if ($item->hasAttribute($flag)) {
							$flag_values[$flag] = htmlspecialchars($item->getAttribute($flag));
						} else {
							$flag_values[$flag] = "";
						}
					}
				} 
				array_push($ret_val, array('value' => htmlspecialchars($item->nodeValue), 'flags'=> $flag_values));
			}
		}
	} else {
		$logging .= "ERROR: Invalid Xpath Query!";
		$ret_val = array();
	}
	
//	Messages($logging);
 	return $ret_val;
}

// ============================================================================
// ==  Compiled Schema Functions
// ============================================================================

// ----------------------------------------------------------------------------
// Returns the flat schema map as an associative array.
// Returns false on failure.
//     If it does not have the array in memory, or if true is passed for 
//         $refresh, it first reads the appropriate JSON file and converts 
//         it to an associative array for the specified OSCAL root 
//         (catalog, profile, etc.)
function GetFlatSchemaMap($root, $refresh=false) {
	global $oscal_flat_schema_map;
	$ret_val = false;

	if (isset($_SESSION["OSCAL-ROOTS"]) && !empty($_SESSION["OSCAL-ROOTS"]) ) {
		$oscal_roots = $_SESSION["OSCAL-ROOTS"];
		if (isset($oscal_roots[$root]["schema-flat-map"]) && !$refresh) {
			$ret_val = $oscal_roots[$root]["schema-flat-map"];
		}
	}

	if ($ret_val === false) {

		if (strpos($root, "/") !== false) {
			$root = parse_url($root, PHP_URL_HOST);
		}

		if (strpos($root, ":") !== false) {
			$root = StripNameSpace($root);
		}

		Messages("LOADING FLAT SCHEMA MAP: " . $root);

		if ($refresh || ! isset($oscal_flat_schema_map[$root])) {
			$file_input = file_get_contents(OSCAL_LOCAL_FILES . "oscal_" . $root . "_flat_schemamap.json");
			if ( $file_input !== false) {
				$oscal_flat_schema_map[$root] = json_decode($file_input, true);
				$ret_val = $oscal_flat_schema_map[$root];
			} else {
				Messages("ERROR READING FILE");
			}
		} else {
			$ret_val = $oscal_flat_schema_map[$root];
		}
	}
	
	return $ret_val;
}

// ----------------------------------------------------------------------------
// Returns the nested schema map as an associative array.
// Returns false on failure.
//     If it does not have the array in memory, or if true is passed for 
//         $refresh, it first reads the appropriate JSON file and converts 
//         it to an associative array for the specified OSCAL root 
//         (catalog, profile, etc.)
function GetSchemaMap($root, $refresh=false) {
	global $oscal_schema_map;
	$ret_val = false;

	if (isset($_SESSION["OSCAL-ROOTS"]) && !empty($_SESSION["OSCAL-ROOTS"]) ) {
		$oscal_roots = $_SESSION["OSCAL-ROOTS"];
		if (isset($oscal_roots[$root]["schema-map"]) && !$refresh) {
			$ret_val = $oscal_roots[$root]["schema-map"];
		}
	}

	if ($ret_val === false) {
		
		if (strpos($root, "/") !== false) {
			$root = parse_url($root, PHP_URL_HOST);
		}

		if (strpos($root, ":") !== false) {
			$root = StripNameSpace($root);
		}

		Messages("LOADING SCHEMA MAP: " . $root);

			if ($refresh || ! isset($oscal_schema_map[$root])) {
				$file_input = file_get_contents(OSCAL_LOCAL_FILES . "oscal_" . $root . "_schemamap.json");
				if ( $file_input !== false) {
					$oscal_schema_map[$root] = json_decode($file_input, true);
					$ret_val = $oscal_schema_map[$root];
				} else {
					Messages("ERROR READING FILE");
				}
			} else {
				$ret_val = $oscal_schema_map[$root];
			}
	}

	return $ret_val;
}

// Expands the $oscal_roots array to include a local file name
//      derived from the web_source path.
// This simplifies changes for the application user when NIST
//     changes the published file names. Users just have to change
//     the web_source name, and the rest of the program will
//     begin using the new name locally.
//
// Also reads in pre-compiled $schema_map and $schema_map_flat files

function Enhance_OSCAL_roots($oscal_roots) {
	foreach ($oscal_roots as $root_key => $root_item ) {

		// ... and through the ["files"] nested array.
		foreach ($oscal_roots[$root_key]["files"] as $file_key => $file_item ) {
			// capture the base file name without the full URI
			$oscal_roots[$root_key]["files"][$file_key]["local_file"] = 
				basename($oscal_roots[$root_key]["files"][$file_key]["web_source"]);
		}

		$oscal_roots[$root_key]["schema-map"] = GetSchemaMap($root_key);
		$oscal_roots[$root_key]["schema-flat-map"] = GetFlatSchemaMap($root_key);
		
	}
	return $oscal_roots;
}


// ============================================================================
// ==  XML manipulation functions
// ============================================================================

// ----------------------------------------------------------------------------
// Sample code - not yet integrated into this tool
// From: https://stackoverflow.com/questions/20192956/how-to-interchange-position-of-two-nodes-in-xml-file-using-php
function SwapNodes($oscal_file, $oscalproject="") {
	
	$oscal_objects = OpenOSCALfile($oscal_file, $oscalproject);

// find the nodes
$nodeOne = $xpath->evaluate('//scene[@name="one"]')->item(0);
$nodeTwo = $xpath->evaluate('//scene[@name="two"]')->item(0);

if (!$nodeOne->isSameNode($nodeTwo)) {

  // remember parent and position of the second node
  $parent = $nodeTwo->parentNode;
  $target = $nodeTwo->nextSibling;

  // move the second node
  $nodeOne->parentNode->insertBefore($nodeTwo, $nodeOne->nextSibling);

  // move the first node
  $parent->insertBefore($nodeOne, $target);
}
header('Content-type: text/xml');
echo $dom->saveXml();
	
	return $ret_val;
}

// ----------------------------------------------------------------------------
function StripNameSpace($value_string) {
	$ret_val = "";
	
	$colon_pos = strpos($value_string, ":");
	if ($colon_pos == false) {
			$ret_val = $value_string;
	} else {
		$ret_val = substr($value_string, ($colon_pos - strlen($value_string) + 1));
	}
		
	return $ret_val;
}

// ----------------------------------------------------------------------------
// Removes blank lines from the XML file. Useful after making edits.
function ReformatXML($xml) {

$outXML = $xml->saveXML(); 
$xml = new DOMDocument(); 
$xml->preserveWhiteSpace = false; 
$xml->formatOutput = true; 
$xml->loadXML($outXML); 
// $outXML = $xml->saveXML();
	
return $xml;
}

// ----------------------------------------------------------------------------
// Removes anything that satisfies the xpath query ($query)
function RemoveChildren(&$oscal_objects, $query) {
$messages = "";

	if ($oscal_objects["namespace"]==="") {
		// Do nothing
	} else {
		$query = AddNamespace2xpath($query, $oscal_objects["namespace"]);
	}

	foreach ($oscal_objects['XPATH']->evaluate($query) as $node) {
	  $node->parentNode->removeChild($node);
	}

}

// ============================================================================
// ==  OSCAL-specific functions
// ============================================================================

// ----------------------------------------------------------------------------
function GetBasicMetadata($oscal_file, $oscalproject="") {
	$ret_val = array();
		
	$oscal_objects = OpenOSCALfile($oscal_file, $oscalproject);
	if ($oscal_objects['status']) {
		$ret_val['status'] = true;
		$ret_val['file'] = $oscal_file;

		// Get title
		$result = QueryOneItem($oscal_objects, OSCAL_METADATA_TITLE);
		if ($result === false || $result == "") {
			$ret_val['title'] = "[NO TITLE]";
		} else {
			$ret_val['title'] = $result;
		}

		// Get document formal publication date
		$result = QueryOneItem($oscal_objects, OSCAL_METADATA_DATE);
		if ($result === false || $result == "") {
			$ret_val['date'] = "";
		} else {
			$ret_val['date'] = $result;
		}

		// Get document last modified date
		$result = QueryOneItem($oscal_objects, OSCAL_METADATA_LAST_MODIFIED);
		if ($result === false || $result == "") {
			$ret_val['last-modified'] = "";
		} else {
			$ret_val['last-modified'] = $result;
		}

		// Get document version
		$result = QueryOneItem($oscal_objects, OSCAL_METADATA_VERSION);
		if ($result === false || $result == "") {
			$ret_val['version'] = "";
		} else {
			$ret_val['version'] = $result;
		}

		// Get markings
		$result = QueryOneItem($oscal_objects, OSCAL_METADATA_SENSITIVITY_LABEL);
		if ($result === false || $result == "") {
			$ret_val['label'] = "";
		} else {
			$ret_val['label'] = $result;
		}
		
	} else {
		$ret_val['status'] = false;
		$ret_val['title'] = "[ERROR: UNABLE TO OPEN]";
	}

	return $ret_val;
}


// ----------------------------------------------------------------------------
function GetTitle($oscal_file, $oscalproject="") {
		
	$oscal_objects = OpenOSCALfile($oscal_file, $oscalproject);
	if ($oscal_objects['status']) {
		$ret_val = QueryOneItem($oscal_objects, OSCAL_METADATA_TITLE);

		if ($ret_val === false || $ret_val == "") {
			$ret_val = "[NO TITLE]";
		} 
	} else {
		$ret_val = "[ERROR: UNABLE TO OPEN]";
	}

	return $ret_val;
}

// ----------------------------------------------------------------------------
function GetOSCALType($oscal_file, $oscalproject="") {

	$oscal_objects = OpenOSCALfile($oscal_file, $oscalproject);

	$ret_val = $oscal_objects["type"];

	if ($ret_val === false || $ret_val == "") {
		$ret_val = "[NON-OSCAL ROOT ELEMENT]";
	} 

	return $ret_val;
}

// ----------------------------------------------------------------------------
function GetOSCALRoot($oscal_file, $oscalproject="") {

	$oscal_objects = OpenOSCALfile($oscal_file, $oscalproject);

	$ret_val = $oscal_objects["root"];

	if ($ret_val === false || $ret_val == "") {
		$ret_val = false;
	} 

	return $ret_val;
}

// ----------------------------------------------------------------------------
function CreateOSCALfile($oscalroot, $oscal_id="") {
//	global $oscal_objects; 
    global $oscal_roots; // from oscal-config.php
	$staut = false;

	$messages = "";
	$ns_alias = "oscal";
	$ns = "http://csrc.nist.gov/ns/oscal/1.0";
	$date = new DateTime('NOW');
	$oscal_id .= "-" . $date->format('Y-m-d--H-i-s');

	$new_oscal = "<?xml version='1.0' encoding='UTF-8'?>
	<" . $oscalroot . " xmlns='" . $ns . "'
			 id='" . com_create_guid() . "'>
			 <metadata />
	</" . $oscalroot . ">
	";

	$oscal_new = array();
	$oscal_new['DOM'] = new domDocument;
	$oscal_new['DOM']->preserveWhiteSpace = false; 
	$oscal_new['DOM']->loadXML($new_oscal);

	$oscal_new['status'] = true;
	$oscal_new['project'] = $oscal_id;
	$oscal_new['file'] = "";
	$oscal_new['root'] = $oscalroot;
	$oscal_new['DOM']->formatOutput = true;		

	$oscal_new['XPATH'] = new domXPath($oscal_new['DOM']);
	if ($oscal_new['XPATH']->registerNamespace($ns_alias, $ns)) {
		$oscal_new['namespace'] = $ns_alias;
		$status = true;
	} else {
		$messages .= "Failed to register namespace: " . $ns;
		$status = false;
	}

	if ($status) {
		return $oscal_new;	
	} else {
		return false;
	}

}

// ----------------------------------------------------------------------------
// Accepts:
//   $oscal_obj: the DOMdocument object for the OSCAL XML file (array of objects)
//   $xpath_to_parent: should resolve to a single location in the XML file,
//                     which represents the parent of the insertion pointer
//   $data: a DOMnode object to be inserted.
//
// This determines the correct place to insert the child based on OSCAL's
//       required sequence of elements as specified in the appropriate
//       NIST-provided OSCAL XSD schema file.
// RETURNS:
//     boolean true if successful.
//     string with error messages if unsuccessful.
function InsertOSCALdata($oscal_obj, $xpath_to_parent, $data){
	$messages = "";
	$ret_val = false;


//	Messages ("-- INSERTING: " . $data->nodeName);
	// Use xpath to get parent object
	$parent_obj = QueryListResult($oscal_obj, $xpath_to_parent);
	if ($parent_obj !== false) {
		// The xpath may have been relative, so determine the full xpath  
		$full_path_to_parent = GetFullXpath($parent_obj->item(0));
		if ($full_path_to_parent !== false) {
			// Using the full xpath, get the flat schema array for this OSCAL layer
			$flat_schema_map = GetFlatSchemaMap($full_path_to_parent);
			if ($flat_schema_map !== false) {
				// reverse the sequence of the array so we loop through from the bottom of the list to the top
				$child_element_count = count($flat_schema_map[$full_path_to_parent]["model"]);
				$child_element_index = $child_element_count;
				// Loop through the array from the bottom up, until we 
				// find the element name of the child to insert.
				// Build an xpath query for each child we find below our $data element in the list
				$found_element = false;
				$xpath_to_parents_remaining_children = "";
				while ($child_element_index){
//					Messages ("   -- CHECKING: " . $flat_schema_map[$full_path_to_parent]["model"][$child_element_index-1]);
					if ($data->nodeName == $flat_schema_map[$full_path_to_parent]["model"][$child_element_index-1] ) {
						$found_element = true;
						break;
					} else {
						if (strlen($xpath_to_parents_remaining_children) > 0) {
							$xpath_to_parents_remaining_children = " | " . $xpath_to_parents_remaining_children;
						}
						$xpath_to_parents_remaining_children = $xpath_to_parent . "/" . $flat_schema_map[$full_path_to_parent]["model"][$child_element_index-1] . $xpath_to_parents_remaining_children;
					}
					$child_element_index--;
				}
				if ($found_element) {
					$import_obj= $oscal_obj['DOM']->importNode($data, true);
					// In the specified parameter, find the FIRST element that must appear AFTER the element being inserted.
					// 		If none exists, use appendChild
					//      Otherwise, store in $param_next, and use insertBefore($parameter_new, $param_next)
					$remaining_children_object = QueryListResult($oscal_obj, $xpath_to_parents_remaining_children);
					if ($remaining_children_object !== false) {
						$parent_obj->item(0)->insertBefore($import_obj, $remaining_children_object->item(0));
					} else {
						$parent_obj->item(0)->appendChild($import_obj);
					}
					if ( ! CheckForXMLerrors() ) {
						$messages .= "<br />   !! " . ("ADDED " . $data->nodeName . " TO " . $xpath_to_parent);
						$ret_val = true;
					} else {
						$messages .= "<br />   !! " . ("ERROR: Unable to insert/append data. Unable to continue (". $xpath_to_parent . ")");
					}
				} else {
					
				}
			} else {
				// ERROR: Unable to get flat schema map. Unable to continue.
				$messages .= "<br />   !! " . ("ERROR: Unable to get flat schema map. Unable to continue (". $xpath_to_parent . ")");
			}
		} else {
			// ERROR: Unable to get full path to parent. Unable to continue.
			$messages .= "<br />   !! " . ("ERROR: Unable to get full path to parent. Unable to continue (". $xpath_to_parent . ")");
		}
	} else {
		// ERROR: Invalid path to parent. Unable to continue.
		$messages .= "<br />   !! " . ("ERROR: Invalid path to parent. Unable to continue (". $xpath_to_parent . ")");
	}

//	Messages($messages);
	if (! $ret_val) {
		$ret_val = $messages;
	}
	return $ret_val;
}

// ----------------------------------------------------------------------------
// Opens an XML file, registers the namespace, and sets up XPATH
// PARAMETER:
// $oscalfile: path to OSCAL file (either full or relative to web server root)
// $oscalproject: (optional) the project ID (folder) - if passed, this will 
//					retain the XML and XPATH objects so subsequent calls to
//					open this project, will use the version in memory.
// $refresh: (Optional) Only relevant if $oscalproject is passed. This triggers
//					a fresh read of the file, and overrites the version in memory.
//
// RETURNS:
// If successful  : Array (Status=true,  Result_Information, DOMDocument_object, 
//                  XPATH_object, Namespace_Alias, Root_Element_Name, 
//                  Validation_File, Processing_Function)
// If unsuccessful: Array (Status=false, Result_Information) 
//                  If there is a problem opening the file, loading it as XML, 
//                  or registering the namespace.
function OpenOSCALfile($oscalfile, $oscalproject="", $refresh=false) {

global $oscal_objects; 
global $oscal_roots; // from oscal-config.php

$status = false;
$messages = "";

	if (strlen($oscalproject) > 0) {
		if (array_key_exists($oscalproject, $oscal_objects) && ! $refresh) {
			$ret_val = $oscal_objects[$oscalproject];
			$status = true;
			$messages .= "REUSING oscal_object for " . $oscalproject;
		}
	}

	if ( ! $status ) {
		$ns_alias = "oscal";

		// Creates a memory object to handle the XML file
		$oscal = new DOMDocument();
		
		// Preserving white space is an option in the XML spec. 
		// NOTE: Make sure this aligns with OSCAL's recommendation.
		$oscal->preserveWhiteSpace = true; 
		
		// Load the file and only proceed if there were no errors.
		if ($oscal->load($oscalfile) !== false) { 

			// This just will let us see properly indented XML if we display it
			$oscal->formatOutput = true;		

			// OSCAL XML documents must have a namespace declared
			$ns = $oscal->documentElement->namespaceURI;
			if($ns) {
				$messages .= " NAMESPACE: " . $ns;
				$xpath = new DomXPath($oscal);
				// For XPATH to work, the namespace must be registered, but an
				// alias may be used. We use "oscal" as the alias for all OSCAL
				// files. At this time, there is no need for separate namespaces.
				if ($xpath->registerNamespace($ns_alias, $ns)) {
					$status = true;
					$messages .= " -- Registered Successfully<br />";
					
					// This exposes the root element name, which we need below.
					$oscal_root_element = $oscal->documentElement;
					$messages .= "ROOT ELEMENT: " . $oscal_root_element->nodeName . "<br />";
//					echo "<br />ROOT ELEMENT: " . $oscal_root_element->nodeName . "<br />";
					// We search $oscal_roots (defined in oscal-config.php) for valid 
					// OSCAL root element names. If we find a match, we capture the:
					//   -- appropriate schema file for validation
					//   -- human-firendly name of the OSCAL file type
					// Other elements in the array may be removed, but are captured for now
					
					if ( isset($oscal_roots[$oscal_root_element->nodeName]) ) {
						Messages("ROOT FOUND: " . $oscal_root_element->nodeName);
						$Valid_OSCAL_Root = true;
						$type = $oscal_roots[$oscal_root_element->nodeName]["title"];
						$xmlschema = OSCAL_LOCAL_FILES . $oscal_roots[$oscal_root_element->nodeName]["files"]["schema"]["local_file"];
						Messages("ROOT FOUND: " . $oscal_roots[$oscal_root_element->nodeName]["files"]["schema"]["local_file"]);
						$metaschema = "";
						$messages .= "RECOGNIZED: " . $type;
					} else {
						$Valid_OSCAL_Root = false;
					}
					// If the root element was not found in the list of valid
					//      OSCAL elements set variables appropriately.
					if ( ! $Valid_OSCAL_Root) {
						$type = "[NOT AN OSCAL FILE]";
						$xmlschema = "";
						$metaschema = "";
					}

				} else {
					$messages .= "Failed to register namespace: " . $ns;
				}
			} else {
				$messages .= "Namespace element missing from root (Missing @xmlns attribute).";
			} 

		} else {
//			$xml_errors = "UNABLE TO OPEN: " . $oscalfile . "<br />";
			$xml_errors = GatherXMLerrors(); 
			$messages .= $xml_errors;
			$status = false;
		}

		Messages($messages);
		
		// If everything went well, populate an array with return values.
		// Otherwise, populate an array with error information.
		if ($status) {
			$ret_val = array("status" => true, "message" => $messages, 
								"project" => $oscalproject, 
								"file" => $oscalfile,
								"type" => $type,
								"DOM" => $oscal, "XPATH" => $xpath, "namespace" => $ns_alias, 
								"root" => $oscal_root_element->nodeName, 
								"schema" => $xmlschema, "metaschema" => $metaschema);
			if (strlen($oscalproject)>0) {
				$oscal_objects[$oscalproject] = $ret_val;
				Messages("SETTING " . $oscalproject);
			}
		} else {
			$messages = "<br /><span style='font-weight:bold; color:red;'>UNABLE TO OPEN:</span> " . $oscalfile . "<br />" . $messages;
			$ret_val = array("status" => false, "message" => $messages);
			if ( function_exists('ZoneOutputAppend') ) {
				ZoneOutputAppend($messages, 'zone-three');
			}
		}
	}
	
return $ret_val;
}

// ----------------------------------------------------------------------------
function SaveOSCALfile($oscal){
	
	SetLastModified($oscal['DOM']);
	$status = $oscal['DOM']->save($oscal['file']);
	if ($status) {
		Messages(" ---- FILE SAVED! ---- ");
		
		$oscal = OpenOSCALfile($oscal['file'], $oscal['project'], true);
		
	} else {
		Messages(" !!!! ERROR SAVING FILE! !!!! ");
	}
	return $status;
}


// ----------------------------------------------------------------------------
function SetLastModified($oscal){
	
	$last_modified = date(DATE_TIME_STORE_FORMAT);
	$basename = basename(OSCAL_METADATA_LAST_MODIFIED);

	if (is_array($oscal)) { 
		$last_modified_object = QueryListResult($oscal, OSCAL_METADATA_LAST_MODIFIED);
		
		if ($last_modified_object === false) { // last-modified doesn't exist (non-typical)
			$last_modified_object = $oscal['DOM']->createElement(basename(OSCAL_METADATA_LAST_MODIFIED), $last_modified);
			$ret_val = InsertOSCALdata($oscal['DOM'], OSCAL_METADATA_LAST_MODIFIED . "/..", $last_modified_object);
		} else {
			$last_modified_object->item(0)->nodeValue = $last_modified;
		}
	} else {  // assume DOM
		$metadata_object = $oscal->getElementsByTagName('metadata');
		if ($metadata_object->length > 0) {
			$last_modified_object = $metadata_object->item(0)->getElementsByTagName($basename);
			if ($last_modified_object->length == 0) {  // last-modified doesn't exist (non-typical)
				$last_modified_object = $oscal->createElement($basename, $last_modified);
				$ret_val = InsertOSCALdata($oscal, OSCAL_METADATA_LAST_MODIFIED . "/..", $last_modified_object);
			} else {
				$last_modified_object->item(0)->nodeValue = $last_modified;
			}
		} else {
			Logging("No metadata!");
		}
	}
}

// ----------------------------------------------------------------------------
// $oscal is either a DOM object, or an array with a DOM object associated with 
//    the ['DOM'] element in the array.
// $title is the title to replace or append.
// $append if true, the value of $title is added to any existng title
//         if false, the value of $title replaces any existing title
//         if no title exists, the value of $title will become the new title.
// 
function SetTitle($oscal, $title, $append=false){
	
	$basename = basename(OSCAL_METADATA_TITLE);

	if (is_array($oscal)) { 
		$title_object = QueryListResult($oscal, OSCAL_METADATA_TITLE);
		
		if ($title_object === false) { // title doesn't exist (non-typical)
			$title_object = $oscal['DOM']->createElement(basename(OSCAL_METADATA_TITLE), $title);
			$ret_val = InsertOSCALdata($oscal['DOM'], OSCAL_METADATA_TITLE . "/..", $title_object);
		} else {
			if ($append) {
				$title_object->item(0)->nodeValue .= $title;
			} else {
				$title_object->item(0)->nodeValue = $title;
			}
		}
	} else {  // assume DOM
		$metadata_object = $oscal->getElementsByTagName('metadata');
		if ($metadata_object->length > 0) {
			$title_object = $metadata_object->item(0)->getElementsByTagName($basename);
			if ($title_object->length == 0) {   // title doesn't exist (non-typical)
				$title_object = $oscal->createElement($basename, $title);
				$ret_val = InsertOSCALdata($oscal, OSCAL_METADATA_LAST_MODIFIED . "/..", $title_object);
			} else {
				if ($append) {
					$title_object->item(0)->nodeValue .= $title;
				} else {
					$title_object->item(0)->nodeValue = $title;
				}
			}
		} else {
			Logging("No metadata!");
		}
	}
}

// ----------------------------------------------------------------------------
// Checks the syntax of the file against the schema
// PARAMETERS
// $oscal_objects is a DOMDocument object
//
// RETURNS an array regardless of success or failure.
// Success: Array contains status=> true
// Failure: Array contains  status=> false, and message=>[string] (containing any error messages)
function ValidateFile(&$oscal_objects) {
$status = false;
$ret_val = false;
$messages = "";

	$messages .= "SCHEMA: " . $oscal_objects["schema"] ;
	$messages .= " -- TYPE: " . $oscal_objects["type"];
	if ( ! $oscal_objects["schema"] == "") {
		
		if ($oscal_objects["DOM"]->schemaValidate($oscal_objects["schema"])) {
			$messages .= " -- VALID!";
			$ret_val = array("status" => true);
		} else {
			$ret_val = array("status" => false, "message" => GatherXMLerrors());
			$messages .= "\n" . $ret_val['message'] ."\n";
		}				
	} else {
		$ret_val = array("status" => false, "message" => "Unable to validate! Schema validation file unknown.");
		$messages .=  "\n" . $ret_val['message'] . "\n";
	}
	
Messages($messages);

return $ret_val;
}

// ----------------------------------------------------------------------------
// This function reads an array of files, and downloads local copies from the web
// $oscal_files is defined in oscal-config.php
//      It contains an array of files to update, and the web URI to the original
function UpdateOSCALValidationFiles() {
	global $oscal_roots; 
	global $oscal_additional_files; 
	$status = true;

	$ret_val = "";
	
	if (file_exists(OSCAL_LOCAL_FILES)) {
		// Loop through each entry in the $oscal_roots array, ...
		foreach ($oscal_roots as $root_key => $root_item) {
			// ... and through the ["files"] nexted array.
			$ret_val .= "<br /><span style='font-weight: bold; font-size: 2em;'>" . $oscal_roots[$root_key]["title"] . "</span><br />";
			foreach ($oscal_roots[$root_key]["files"] as $file_key => $file_item ) {
				$file_headers = @get_headers($file_item["web_source"]);
				if( ! strpos($file_headers[0], "200") === false ) {
					$result = DownloadOSCALFile($file_item["web_source"], OSCAL_LOCAL_FILES . $file_item["local_file"]);
					$ret_val .= $result['messages'];
					if ($result["status"] === true && $file_key == "schema") {
						$ret_val .= "<br />" . ProcessSchema($root_key);
					}
				} else {
					$status = false;
					$ret_val .= "<span style='color:red; font-size: 2em;'>Unable to download: </span><br />";
					$ret_val .= "<span style='color: red; font-weight:bold;'>";
					$ret_val .= "<a href='" . $file_item["web_source"] . "' download='" . $file_item["local_file"] . "' target='_new'>" . $file_item["web_source"] . "</a><br />";
					$ret_val .= "</span>";
					$ret_val .= "Check <b>web_source</b> element in <b>oscal_roots</b> array defined in <b>oscal-config.php</b><br />";
				}
			}
			$ret_val .= "<hr />";
		}
		foreach ($oscal_additional_files as $file ) {
			$file_headers = @get_headers($file["web_source"]);
			if( ! strpos($file_headers[0], "200") === false ) {
				$file_name = basename($file);
				$result = DownloadOSCALFile($file, OSCAL_LOCAL_FILES . $file_name);
				$ret_val .= $result['messages'];
			} else {
				$status = false;
				$ret_val .= "<span style='color:red; font-size: 2em;'>Unable to download: </span><br />";
				$ret_val .= "<span style='color: red; font-weight:bold;'>";
				$ret_val .= "<a href='" . $file . "' download='" . $file_name . "' target='_new'>" . $file . "</a><br />";
				$ret_val .= "</span>";
				$ret_val .= "Check <b>web_source</b> element in <b>oscal_additional_files</b> array defined in <b>oscal-config.php</b><br />";
			}
		}

		if (! $status ) {
			$messages = "";
			$messages .= "";
			$messages .= "<p style='color:red; font-size:1.5em;'>ONE OR MORE FILES DID NOT DOWNLOAD CORRECTLY!</p>";
			$messages .= "<p>Check the link(s) below, to ensure each is valid.</p>";
			$messages .= "<p>If a link is not valid, correct it in the oscal-config.php file.</p>";
			$messages .= "<p>This requires the openssl extension to be enabled in the php.ini file if the URLS use SSL, as with the GitHub URLs.</p>";
			$messages .= "<p>If links are valid and openssl is enabled, a host firewall may be preventing";
			$messages .= " php.exe from downloading files.</p>";
			$messages .= "<p>WORKAROUND: You can download the files manually and put them here:</p>";
			$messages .= "<p style='font-weight:bold; font-size:1.2em;'>" . OSCAL_LOCAL_FILES . "</p>";
			$messages .= "<p>After a manual download, you must compile the schema files via MAIN MENU \ \"Tools & Maintenance\" \ \"Compile Schema Files\".</p>";
			$messages .= "<br /><br />";
			$ret_val = $messages . $ret_val;
		}

	} else {
		$ret_val .= "<span style='color:red; font-size: 2em;'>Invalid local directory: </span><br />";
		$ret_val .= "<span style='color: red; font-weight:bold;'>";
		$ret_val .= OSCAL_LOCAL_FILES . "<br />";
		$ret_val .= "</span>";
		$ret_val .= "Check OSCAL_LOCAL_FILES in oscal-config.php.<br />";
		$ret_val .= "Attempted on ". date("Y-m-d   h:i:sa") . " " . date_default_timezone_get() . "<br/ >";
	}
	
	return $ret_val;
}

// ----------------------------------------------------------------------------
function FindOSCALFileInDir($dir) {
	$xml_file_found = false;
	$dh = opendir($dir);
	$cntr = 0;
	while (($file = readdir($dh)) !== false) {
		if (filetype($dir . "/" . $file) == 'file') {
			if (strtolower(right_str($file, 4)) == '.xml') {
				$xml_file_found = true; 
				break;
			}
		}
		$cntr += 1;
		if ($cntr > 100) break;
	}
	closedir($dh);

	if ($xml_file_found) {
		$ret_val = $file;
//		Messages("FOUND FILE: " . $file);
	} else {
		Messages("NO OSCAL FILE FOUND IN: " . $dir);
		$ret_val = false;
	}
	return $ret_val;
}

// ----------------------------------------------------------------------------
// Perform the download
function DownloadOSCALFile($oscalfile, $newfile) {
	$ret_val = array();
	$ret_val['status'] = false;
	$ret_val['messages'] = "";
	
	try {
		$status = copy($oscalfile, $newfile);
	} catch (Exception $e) {
		$status = false;
		$err_msg = $e->getMessage();
	}

	if ( $status ) {
	$ret_val['status'] = true;
		$ret_val['messages'] .= "<br /><span style='color: green; font-weight:bold;'>";
		$ret_val['messages'] .= basename($newfile) . " updated successfully ";
		$ret_val['messages'] .= "</span>";
		$ret_val['messages'] .= date("Y-m-d   h:i:sa") . " " . date_default_timezone_get() . "<br/ >";
	}else{
	$ret_val['status'] = false;
		$ret_val['messages'] .= "Error downloading " . $oscalfile["local_file"] . " from <br />";
		$ret_val['messages'] .= "<a href='" . $oscalfile["web_source"] . "' target='_blank'>". $oscalfile["web_source"] . "</a><br />";
		$ret_val['messages'] .= "Ensure the URL is valid in oscal_files array, web_source in oscal-config.php<br />";
	}
	
	return $ret_val;
}

// ============================================================================
// ==  JSON Conversion (JSON to/from XML) Functions
// ============================================================================

// ----------------------------------------------------------------------------
function CreateJSON($file_from, $file_to) {
	global $oscal_roots;
	$rootname = GetOSCALRoot($file_from);

//	$file_from = FriendlyPath($file_from);
//	$file_to = FriendlyPath($file_to);
	$converter = $oscal_roots[$rootname]["files"]["xml2json"]["local_file"];
	$flags = "json-indent=yes";
	$ExOutput = array();

	$result = "";
	$ExString = 'java -jar ' . SAXON_HE . ' -s:"' . $file_from . '" -o:"' . $file_to . '" -xsl:"' . OSCAL_LOCAL_FILES . $converter . '" ' . $flags . '  2>&1';
	Messages($ExString);
	exec($ExString, $ExOutput, $result);
	
	Messages("XML to JSON RESULT FOR " . $file_to . ": " . $result);
		
	return $ExOutput;
}

// ----------------------------------------------------------------------------
function GetOSCAJSONLRoot($json_file) {
$ret_val = false;

		$file_input = file_get_contents($json_file);
		if ( $file_input !== false) {
			$json_file_array = json_decode($file_input, false);
			$ret_val = key($json_file_array);
			Messages("JSON ROOT: " . $ret_val);
		} else {
			Messages("ERROR READING FILE");
		}
return $ret_val;
}

// ----------------------------------------------------------------------------
function OSCAL_JSON2XML($file_from, $file_to) {
	global $oscal_roots;
	$rootname = GetOSCAJSONLRoot($file_from);
	Messages("JSON ROOT: " . $rootname);
	$file_from = MakeURI($file_from);
	Messages("FROM: " . $file_from);
//	$file_to = FriendlyPath($file_to);
	Messages("TO: " . $file_to);
	$converter = $oscal_roots[$rootname]["files"]["json2xml"]["local_file"];
	Messages("CONVERTER: " . $converter);
	$flags = "-it";
	$ExOutput = array();

	$result = "";
	$ExString = 'java -jar ' . SAXON_HE . ' -o:"' . $file_to . '" -xsl:"' . OSCAL_LOCAL_FILES . $converter . '" '  . $flags . ' json-file="' . $file_from . '"   2>&1';
	Messages($ExString);
	exec($ExString, $ExOutput, $result);

//	exec('java -jar ' . SAXON_HE . ' -o:"' . $file_to . '" -xsl:"' . OSCAL_LOCAL_FILES . $converter . '" '  . $flags . ' json-file"' . $file_from . '" 2>&1', $ExOutput, $result);
	
	Messages("JSON to XML RESULT FOR " . $file_to . ": " . $result);
		
	return $ExOutput;
}

// ============================================================================
// ==  XML Error Handling
// ============================================================================

// ----------------------------------------------------------------------------
function CheckForXMLerrors() {
    $errors = libxml_get_errors();
	$ret_val = false;
	if (count($errors) > 0) {
		$ret_val = true;
	}
//    libxml_clear_errors();
	
	return $ret_val;

}

// ----------------------------------------------------------------------------
// Manages the list of all generated XML errors.
// This is mostly used after validating an XML file against a schema, which 
// could generate multiple non-compliant findings.
function GatherXMLerrors() {
    $errors = libxml_get_errors();
	$ret_val = "";
	$error_cnt = count($errors);
	if ($error_cnt == 1) {
		$ret_val .= "<span style='color:red; font-weight: bold; font-size: 1.2em'>" . $error_cnt . " error found";
	} else {
		$ret_val .= "<span style='color:red; font-weight: bold; font-size: 1.2em'>" . $error_cnt . " errors found";
	}
	$ret_val .= "</span><br />\n";
	
    foreach ($errors as $error) {
        $ret_val .= GetValidationErrorDetails($error);
    }
    libxml_clear_errors();
	
	return $ret_val;
}

// ----------------------------------------------------------------------------
// This is called by GatherXMLerrors, and expands the details on any one
// specific error.
function GetValidationErrorDetails($error)
{
    $return = "<br />\n";

//    if ($error->file) {
//        $return .= "Error in <b>$error->file</b> <br/>\n";
//    }
    $return .= "<b>Line $error->line</b> <br/>\n";
	
    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "<b>Warning $error->code</b>: \n";
            break;
        case LIBXML_ERR_ERROR:
            $return .= "<b>Error $error->code</b>: \n";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "<b>Fatal Error $error->code</b>: \n";
            break;
    }

	$return .= str_replace(". ", ". \n", strip_namespace(trim($error->message)));
    $return .= "<br />\n";

    return $return;
}


// ============================================================================
// ==  Other XML Functions
// ============================================================================

// ----------------------------------------------------------------------------
function joinXML(&$parent, $child, $tag = null)
{

	$node = $child->documentElement;
	
	$node = $parent->importNode($node, true);

	if ($tag !== null) {
		$tag = $DOMParent->getElementsByTagName($tag)->item(0);
		$tag->appendChild($node);
	} else {
		$DOMParent->documentElement->appendChild($node);
	}


	/*
	$DOMChild = new DOMDocument;
	$DOMChild->loadXML($child);
	$node = $DOMChild->documentElement;
	
	$DOMParent = new DOMDocument;
	$DOMParent->formatOutput = true;
	$DOMParent->loadXML($parent);

	$node = $DOMParent->importNode($node, true);

	if ($tag !== null) {
		$tag = $DOMParent->getElementsByTagName($tag)->item(0);
		$tag->appendChild($node);
	} else {
		$DOMParent->documentElement->appendChild($node);
	}

	return $DOMParent->saveXML();
	*/
}


// ----------------------------------------------------------------------------
// This extracsts the reference to a schema file embeded within an XML file's
// headers. 
function ExtractSchemaFile($schema_declaration) {
	$retval = "";
	$filepos = stripos($schema_declaration, "file:") + 5;
	
	if ($filepos == 0) {
		$retval = trim($schema_declaration);
	} else {
		$retval = trim(substr($schema_declaration, $filepos, strlen($schema_declaration) - $filepos));
	}
	return $retval;
}


// ============================================================================
// ==  UI Generation Functions
// ============================================================================

// ----------------------------------------------------------------------------

function MakeBackButton($url="", $img="/img/arrow-left.png") {
	$output = "";
	
	if ($url == "") {
		$action = "window.history.back()";
	} else {
		$action = "window.open(\"" . $url . "\", \"_self\")";
	}
	
	$buttons = array(
//		["text" => "&lAarr; BACK", "img" => $img, "action" => "window.open(\"" . $url . "\", \"_self\")"]
		["text" => "Go Back", "img" => $img, "action" => $action]
		);

	$output = MakeMenu($buttons);

	return $output;
}

// ----------------------------------------------------------------------------

function MakeMenu($buttons, $add_goback=false, $horizontal=false) {
	$output = "";
	
	$output .= "<table class='menu'>";
	if ($horizontal) {
		$output .= "<tr>";
	}
	foreach ($buttons as $button) {
		if ($horizontal) {
			$output .= "<td class='button' onclick='" . str_replace("'", "\"", $button["action"]) . "'>";
		} else {  // vertical 
			$output .= "<tr><td class='button' onclick='" . str_replace("'", "\"", $button["action"]) . "'>";
		}
		$output .= "<img class='buttonicon' src='" . $button["img"] . "' />";   // ' width='40' height='40' 
		$output .= "&nbsp;&nbsp;";
		$output .= $button["text"];
		$output .= "&nbsp;";
		if ($horizontal) {
			$output .= "</td>";
		} else {  // vertical 
			$output .= "</td></tr>";
		}
	}
	if ($add_goback) {
		if ($horizontal) {
			$output .= "<td class='button' onclick='goBack()'>";
		} else {  // vertical 
			$output .= "<tr><td class='button' onclick='window.history.back()'>";
		}
		$output .= "<img class='buttonicon' src='./img/arrow-left.png' />";   // ' width='40' height='40' 
		$output .= "&nbsp;&nbsp;";
		$output .= "Go Back";
		$output .= "&nbsp;";
		if ($horizontal) {
			$output .= "</tr>";
		} else {  // vertical 
			$output .= "</td></tr>";
		}
	}
	if ($horizontal) {
		$output .= "</tr>";
	}
	$output .= "</table>";

	return $output;
}
 
// ----------------------------------------------------------------------------

function RemoveWhiteSpace($str, $comment="") {
	
	if (is_string($str)) {
		$str = trim($str);
		$str = str_replace([ chr(13), chr(10), chr(9)], ["", "", ""], $str);
		$str_len = 0;
		
		// keep collapsing two spaces to one
		// For a long string of spaces, this needs to happen multiple times.
		while (! strpos($str, '  ') === false) {
			$str = str_replace("  ", " ", $str);
			$str_len = strlen($str);
		}
//		Messages("White Space Removed: " . $comment);
	} else {
		$str = "";
		Messages("Unable to remove white space. Not a string: " . $comment);
	}
    return $str;
}
 
// ----------------------------------------------------------------------------
// For elments with a cardinality greater than 1, adds the tool buttons to
//     add, remove, and change the sequence of the elements
// 
function GenerateToolsMenu($multiple, $cntr, $cntr_max, $empty=false) {
$output = "";
$indent = 1;
			
	// This is the tools menu
	// $output .= NLandTabs($indent). "<img onclick=\"clearField(this)\" src='./img/tools-clear.png' title='Clear contents' style='cursor: pointer;' width='" . ICON_SIZE . "' height='" . ICON_SIZE . "'>";

	if ($multiple) {

//		$output .= NLandTabs($indent). "<img src='./img/tools-spacer.png' />";

		// Move up (or blank if first item in the list)
		if ($cntr  == 1) {
			$output .= NLandTabs($indent). "<img src='./img/tools-blank.png' width='" . ICON_SIZE . "' height='" . ICON_SIZE . "' />";
		} else {
			$output .= NLandTabs($indent). "<img onclick=\"moveUp(this)\" src='./img/tools-up.png' title='Move up' style='cursor: pointer;' width='" . ICON_SIZE . "' height='" . ICON_SIZE . "' />";
		}

//		$output .= NLandTabs($indent). "<img src='./img/tools-spacer.png' />";

		// Move down (or blank if last item in the list)
		if ($cntr  == $cntr_max) {
			$output .= NLandTabs($indent). "<img src='./img/tools-blank.png' width='" . ICON_SIZE . "' height='" . ICON_SIZE . "' />";
		} else {
			$output .= NLandTabs($indent). "<img onclick=\"moveDown(this)\" src='./img/tools-down.png' title='Move down' style='cursor: pointer;' width='" . ICON_SIZE . "' height='" . ICON_SIZE . "' />";
		}

//		$output .= NLandTabs($indent). "<img src='./img/tools-spacer.png' />\n";

		// Delete and Add (or blank and blank if no entries exist in the OSCAL file for the field)
		if ($empty) {
			$output .= NLandTabs($indent). "<img src='./img/tools-blank.png' width='" . ICON_SIZE . "' height='" . ICON_SIZE . "' />";
//			$output .= NLandTabs($indent). "<img src='./img/tools-spacer.png' />\n";
			$output .= NLandTabs($indent). "<img src='./img/tools-blank.png' width='" . ICON_SIZE . "' height='" . ICON_SIZE . "' />";
		} else {
			$output .= NLandTabs($indent). "<img onclick=\"remove(this)\" src='./img/tools-trash.png' title='Remove' style='cursor: pointer;' width='" . ICON_SIZE . "' height='" . ICON_SIZE . "' />";
//			$output .= NLandTabs($indent). "<img src='./img/tools-spacer.png' />\n";
			$output .= NLandTabs($indent). "<img onclick=\"addBelow(this)\" src='./img/tools-plus.png' title='Add another beneath this' style='cursor: pointer;' width='" . ICON_SIZE . "' height='" . ICON_SIZE . "' />";
		}
		
		// Add (the only choice that is always available)
	}
	// This is the end of the tools menu

return $output;	
	
}

// ----------------------------------------------------------------------------
// This identifies the local path to a an OSCAL file based on the project ID
// Valid values for $item_ref are:
// 		"title" => The value set in the /metadata/title element of the file
// 		"file" => The file name of the OSCAL file (without path)
// 		"date_orig" => The date/time the file was first added to this application
// 		"date_last_mod" => The date/time the file was last modified by this app
//		"dir" => the directory name containing the this project (without full path)
// 		"file-with-path" => Full path and file name

function GetProjectDetail($project_id, $item_ref){
	$ret_val = false;
	
	if (! isset($_SESSION["project_list"]) ) {
		GatherProjectList();
	}
	$file_project_list = $_SESSION["project_list"];
	if ( is_array($file_project_list[$project_id]) ) {
		$ret_val = $file_project_list[$project_id][$item_ref];
	} else {
		$ret_val = false;
		Messages("PROJECT ID NOT FOUND! (" . $project_id . ")");
	}

	Messages("PROJECT DETAIL for " . $project_id . " (" . $item_ref . "): " . $ret_val);
	return $ret_val;
}

// ----------------------------------------------------------------------------
function AddToProjectList($project_id) {
$project_list = array();
$status = false;
$storage_location = PROJECT_LOCATION . "proj-*";

	$project_list = GatherProjectList();

	$dir = PROJECT_LOCATION . $project_id;
	if (false !== ($file = FindOSCALFileInDir($dir))) {
		$project_id = basename($dir); // For now, we are using the base dir as the project ID.
		$base_dir = basename($dir);
		$dir .= "/";
		$orig_date = filemtime($dir . "__original/" . $file);
		if ($orig_date === false) {
			$orig_date = filemtime($dir . "__original/" . str_ireplace(".xml", ".json", $file));
			if ($orig_date === false) {
				$orig_date = "[NOT AVAILABLE]";
			}
		} 
		$project_list[$project_id] = array(
				"title" => GetTitle($dir . $file),
				"file" => $file,
				"date_orig" => $orig_date,
				"date_last_mod" => filemtime($dir . $file),
				"dir" => $base_dir,
				"file-with-path" => $dir . $file
				);
	}
	$_SESSION["project_list"] = $project_list;
	
}

// ----------------------------------------------------------------------------
function GatherProjectList($refresh=false) {
	$project_list = array();
	$status = false;

	if (isset($_SESSION["project_list"]) && !empty($_SESSION["project_list"]) && !$refresh) {
		$project_list = $_SESSION["project_list"];
	} else {

		$storage_location = PROJECT_LOCATION . "proj-*";
		$dirs = glob($storage_location, GLOB_ONLYDIR);
		if (count($dirs) > 0) {
			foreach ($dirs as $dir) {
				if (false !== ($file = FindOSCALFileInDir($dir))) {
					$project_id = basename($dir); // For now, we are using the base dir as the project ID.
					$base_dir = basename($dir);
					$dir .= "/";
					$orig_date = filemtime($dir . "__original/" . $file);
					if ($orig_date === false) {
						$orig_date = filemtime($dir . "__original/" . str_ireplace(".xml", ".json", $file));
						if ($orig_date === false) {
							$orig_date = "[NOT AVAILABLE]";
						}
					} 
					$project_list[$project_id] = array(
							"title" => GetTitle($dir . $file),
							"file" => $file,
							"date_orig" => $orig_date,
							"date_last_mod" => filemtime($dir . $file),
							"dir" => $base_dir,
							"file-with-path" => $dir . $file
							);
				}
			}
		}
		$_SESSION["project_list"] = $project_list;
	}
	return $project_list;
}

// ----------------------------------------------------------------------------
function MakeDownloadButtons(&$files, $project_dir, $file_pattern, $file_label, $date_label, $message="") {
	$ret_val = "";

	$ret_val .= "<table width='100%' class='fileinfo'>";
	$ret_val .= "<tr><th colspan='2'>" . $file_label . "</th></tr>";
//	resolved-profile_catalog.xml
	foreach ($files as $file) {
		$base_file_name = basename($file);
		$ret_val .= "<tr>";
		$ret_val .= "<td class='button' width='30%'>";

		$ret_val .= "<a class='buttonlink' href='" . $project_dir . $base_file_name . "' download='" . $base_file_name . "'>";
		$ret_val .= "<table width='100%' class='button'><tr><td class='button'>";
		$ret_val .= "<img class='buttonicon' src='/img/download2.png' />&nbsp;DOWNLOAD";
		$ret_val .= "</td></tr></table>";
		$ret_val .= "</a>"; 

		$ret_val .= "</td>";
		$ret_val .= "<td> <span style='font-weight:bold;'>" . $base_file_name . "</span>";
		$ret_val .= "<br /><span style='color:red;'>" . $date_label . ":</span><br />" . date(DATE_TIME_PRESENT_FORMAT, filemtime($file)) ;
		$ret_val .= "</td></tr>";

		if ($message !== "") {
			$ret_val .= "<tr><td colspan='2' style='color:red; font-weight: bold;'>";
			$ret_val .= $message;
			$ret_val .= "</td></tr>";
		}

//		$ret_val .= "</tr>";
	}
	$ret_val .= "</table>";
	$ret_val .= "<br /><br />";
	
	return $ret_val;
}

// ============================================================================
// ==  Miscellaneous Helper Functions
// ============================================================================

// ----------------------------------------------------------------------------
// Creates a PHP or HTML style Comment on a new line
// 	$text contains the comment
// 	$type is either 'php' (default), or 'html'
// 	$add_php_tag indicates whether the '<?php' open/close tags need to be 
//          added. This is ignored if $type is not 'php'
function AddComment($text, $type='php', $add_php_tag=true) {
	$ret_val = "";
	$comment_wrap_length = 80;
	
	if ( $type == 'php') {
		$ret_val .= "\n/*\n";
		$ret_val .= wordwrap($text, $comment_wrap_length);
		$ret_val .= "\n*/\n";
	} else {
		$ret_val .= "\n<!-- \n";
		$ret_val .= wordwrap($text, $comment_wrap_length);
		$ret_val .= "\n -->\n";
	}
	
	if ( $type == 'php' && $add_php_tag) {
		$ret_val = "<?php\n" . $ret_val . "\n?>";
	}

return $ret_val;	
}

// ----------------------------------------------------------------------------
// !!!!! Likely needs work. !!!!!!
// This converts a path to a URI. 
function MakeURI($path) {

// ************ 
//	if (RunningOnWindows()) {
		$bad  = array( " ",  "\\");
		$good = array("%20", "/");
//	} else { // Assume Linux or linux-friendly (like Mac OS)
//		$bad  = array(" ");
//		$good = array("%20" );
//	}
	
	$ret_val = str_replace($bad, $good, $path);
	if (substr($ret_val, 0, 2) == "//") {
		$ret_val = "file:" . $ret_val;
	} else {
		if (substr($path, 1, 1) == ":") {
			$ret_val = "file:///" . $ret_val;
		} else {
			$ret_val = "file://" . $ret_val;
		}
	}
	
	return $ret_val;
}

// ----------------------------------------------------------------------------
// May be unnecessary
// Returns true if the underlying server is Windows.
// Returns false otherwise.
function RunningOnWindows() {
	//By default, we assume that PHP is NOT running on windows.
	$isWindows = false;
	 
	// If the first three characters PHP_OS are equal to "WIN",
	// then PHP is running on a Windows operating system.
	if(strcasecmp(substr(PHP_OS, 0, 3), 'WIN') === 0){
		$isWindows = true;
	}
	return $isWindows;
}

// ----------------------------------------------------------------------------
function NLandTabs($indent=0) {

$ret_val = "\n" . str_repeat("\t", $indent) ;
	
return $ret_val;
}

// ----------------------------------------------------------------------------
function DisplayDescription($description, $name = "") {
$ret_val = "";

	if (strlen($description) > 0) {
		$ret_val = wordwrap($description, WRAP_LENGTH);
	} else {
		$ret_val = "[ " . $name . ": No description available ]";
	}
	
return $ret_val;
}

// ----------------------------------------------------------------------------
function check_file ($file){
	$status = false;
	
    if ( !preg_match('/\/\//', $file) ) {
        if ( file_exists($file) ){
            $status = true;
        } else {
			Logging("NOT FOUND LOCALLY: " . $file);
		}
    }

    else {
		if ( ! (!$file || !is_string($file) || ! preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $file))) {
			$ch = curl_init($file);
			if ($ch !== false) {
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt($ch, CURLOPT_NOBODY, true);
				
				// !! PHP's cURL implementation cheks the certificate of an https
				// !! connection; however, it does not "know" the proper root 
				// !! certificates for GitHub, and fails to complete the connection.
				// 
				// !! The following two lines can override SSL validity checking.
				// !! They are currently configured to allow the connection as long
				// !! the actual host name matches the host name in the certificate;
				// !! however, the root CA signature is not verified because cURL 
				// !! does not recognize it "out of the box". 
				//
				// !! For better connection integrity, install the proper 
				// !! certificatesin the php.ini file as described at the link
				// !! below, and set CURLOPT_SSL_VERIFYPEER to true below.
				//
				// Based on information found here:
				//     https://thisinterestsme.com/php-curl-ssl-certificate-error/
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 0 = don't check, 1 = depreciated; 2 = check certificate common name and ensure it matches host
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // true = verify certificate

				curl_exec($ch);
				$code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

				if($code == 200){
					$status = true;
				}else{
					$status = false;
					Logging("NOT FOUND REMOTELY: " . $file);
					Logging("ERRORS: " . curl_error($ch));
				}
				curl_close($ch);
			} else {
				Logging("CURL ERROR OPENING: " . $file);
			}
		} else {
			Logging("INVALID URL: " . $file);
		}
    }
    return $status;
}

// ----------------------------------------------------------------------------
// Simulates the javascript encodeURIComponent for PHP
function encodeURIComponent($str) {
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($str), $revert);
}

// ----------------------------------------------------------------------------
// Simulates the javascript decodeURIComponent for PHP
function decodeURIComponent($str) {

    $revert = array('!'=>'%21', '*'=>'%2A', "'"=>'%27', '('=>'%28', ')'=>'%29');
	return rawurldecode(strtr($str, $revert)); 
}

// ----------------------------------------------------------------------------

function left_str($str, $length) {
     return substr($str, 0, $length);
}
 
// ----------------------------------------------------------------------------
function right_str($str, $length) {
     return substr($str, -$length);
}

// ----------------------------------------------------------------------------
function recurse_array($values){
    $content = '';
    if( is_array($values) ){
        foreach($values as $key => $value){
            if( is_array($value) ){
                $content.="$key<br />".recurse_array($value);
            }else{
                $content.=$key . " = " . (string)$value . "<br />";
            }

        }
    }
    return $content;
}

function Messages($content){
	global $messages;

	if (function_exists('Logging')) {
		Logging($messages);
		$messages = "";
	} else {
		$messages .= "\n-- " . $content . "<br />";	
	}
	
}

// ----------------------------------------------------------------------------
// Generates a globally unique identifeer (v4 UUID)
// The function exists when running PHP on Windows, but must be created 
//      when running PHP on other platforms.
if (!function_exists('com_create_guid')) {
  function com_create_guid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
  }
}
// ============================================================================

// ----------------------------------------------------------------------------

	


?>
