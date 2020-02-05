<?php
// OSCAL Objects
// This is a work-in-progress: Converting common OSCAL functions into
//    PHP classes for a more object-oriented approach.

// Classes defined:
// ==  OSCAL Class
// ==  Messages Class

require_once('oscal-config.php');

/*
if (isset($_SESSION["OSCAL-OBJECTS"]) && !empty($_SESSION["OSCAL-OBJECTS"]) ) {
	$oscal_objs = $_SESSION["OSCAL-OBJECTS"];
} else {
	$oscal_objs = array();
}
*/


// ============================================================================
// ==  OSCAL Class Definition (EXPERIMENTAL)
// ============================================================================
// When instantiating this class, one argument must be passed ($spec).
//    $spec may be one of the following:
//       -- If $spec is a recognized OSCAL root element, this class will create
//          and a new OSCAL object with an "empty" OSCAL file.
//       -- (FUTURE) If $spec is a recognized project ID, this class will
//          open the XML file associated with the project ID. (NOT IMPLEMENTED)
//       -- If $spec is the name of of a valid XML file, this class will 
//          attempt to open the XML file.
//
// RETURNS: 
//    if $spec is valid, returns an OSCAL object with status = true.
//    if $spec is not valid, returns an OSCAL object with status = false.
//
// ORDER OF PROCESSING:
//    This class will first check to see if the argument is a recognized 
//       OSCAL root element name. 
//       If not, it will check to see if it is a recognized project ID (FUTURE).
//       If not, it will check to see if it is a valid XML file name.
// 
//
// NOTE: An "empty" OSCAL file minimally contains the root element, metadata, 
//       backmatter, and place-holders for certain required elements, such as 
//       //metadata/title and //metadata/oscal-version. It also assigns the 
//       OSCAL name spece anda unique UUID to the @id attrbute at the root.
class OSCAL {
	// Member variables 
	var $project_id;
	var $processing_id;
	var $file_name; // includes full path to file
	var $file_base_name;
	var $title;
	var $dom;
	var $xpath;
	var $namespace_explicit;
	var $namespace_alias;
	var $root_element;
	var $recognized;   // Is the root element a recognized OSCAL root?
	var $schema_file;
	var $schema_map;
	var $schema_flat_map;
	var $type;
	var $messages;  // messages for the user, and debugging messages
	var $status;

	// Initialize - 
	//     If $create is true, $spec specifies an OSCAL root type, 
	//        such as "catalog", "profile", etc.
	//     If $create is false (default) or missing, $spec 
	//        specifies a file name to be opened.
	//     If $spec is missing or invalid, $this->status is false.
	function __construct($spec, $create=false) {
		$this->project_id = "";
		$this->processing_id = 'uuid-' . com_create_guid();
		$this->file_name = ""; // includes full path to file
		$this->file_base_name = "";
		$this->title = "";
		$this->namespace_explicit = "";
		$this->namespace_alias = "oscal";
		$this->recognized = false;   // Is the root element a recognized OSCAL root?
		$this->schema_file = "";
		$this->schema_map = array();
		$this->schema_flat_map = array();
		$this->messages = new Messages();
		$this->status = true;
		$this->type = "";
		$this->recognized = false;

		// check $spec for valid OSCAL root type
		if ( isset($oscal_roots[$spec]) ) {
			$this->messages->Messages("RECOGNIZED OSCAL TYPE: " . $spec);
			$this->recognized = true;
			$this->Create($spec);
		} else {
			$this->recognized = false;
			$this->status = false;
			$this->messages->Messages("NOT A RECOGNIZED OSCAL TYPE: " . $spec);
		}
		
		// Check for valid project ID [FUTURE]
		if (! $this->recognized && false){
			// ******** Need to create
			// Get file name from ID
			// perform Open
			// NOTE: The intention is to design code such that the 
			//       created object is tied to the ID, so in theory
			//       this may not be needed; however, it may be
			//       necessary to open the file based on project ID,
			//       such as if the sesson has expired, and the session
			//       variable containing list of porjects is lost.
		}

		// Attempt to open the file.
		if (! $this->recognized) {
			if (isset($spec) ) {
				$this->status = $this->Open($spec);
			} else {
				$this->status = false;
			}
		}
	}

	// Initialize - if file name is passed, open it
	public function Open($oscalfile, $refresh=false) {

		global $oscal_roots; // from oscal-config.php

		$this->status = false;
		$this->namespace_explicit = "oscal";

		// Creates a memory object to handle the XML file
		$this->dom = new DOMDocument();
		
		// Preserving white space is an option in the XML spec. 
		// NOTE: Make sure this aligns with OSCAL's recommendation.
		$this->dom->preserveWhiteSpace = true; 
		
		// Load the file and only proceed if there were no errors.
		if ($this->dom->load($oscalfile) !== false) { 

			// This just will let us see properly indented XML if we display it
			$this->dom->formatOutput = true;		

			// OSCAL XML documents must have a namespace declared
			$ns = $this->dom->documentElement->namespaceURI;
			if($ns) {
				$this->messages->Debug(" NAMESPACE: " . $ns);
				$this->xpath = new DomXPath($this->dom);
				// For XPATH to work, the namespace must be registered, but an
				// alias may be used. We use "oscal" as the alias for all OSCAL
				// files. At this time, there is no need for separate namespaces.
				if ($this->xpath->registerNamespace($this->namespace_explicit, $ns)) {
					$this->status = true;
					$this->messages->Debug(" -- Registered Successfully");
					
					// This exposes the root element name, which we need below.
					$this->root_element = $this->dom->documentElement->nodeName;
					$this->messages->Debug("ROOT ELEMENT: " . $this->root_element);
					// We search $oscal_roots (defined in oscal-config.php) for valid 
					// OSCAL root element names. If we find a match, we capture the:
					//   -- appropriate schema file for validation
					//   -- human-firendly name of the OSCAL file type
					// Other elements in the array may be removed, but are captured for now
					
					if ( isset($oscal_roots[$this->root_element]) ) {
						$this->recognized = true;
						$this->type = $oscal_roots[$this->root_element]["title"];
						$this->schema_file = OSCAL_LOCAL_FILES . $oscal_roots[$this->root_element]["files"]["schema"]["local_file"];
						$this->messages->Messages("RECOGNIZED OSCAL TYPE: " . $this->type);
					} else {
						$this->recognized = false;
					}
					// If the root element was not found in the list of valid
					//      OSCAL elements set variables appropriately.
					if ( ! $this->recognized) {
						$this->type = "[NOT AN OSCAL FILE]";
						$this->schema_file = "";
					}

				} else {
					$this->messages->Messages("Failed to register namespace: " . $ns);
				}
			} else {
				$this->messages->Messages("Namespace element missing from root (Missing @xmlns attribute).");
			} 
		} else {
			$xml_errors = GatherXMLerrors(); 
			$this->messages->Messages($xml_errors);
			$this->status = false;
		}

		// If problems, raise error messages.
		if (! $this->status) {
			$messages = "<span style='font-weight:bold; color:red;'>UNABLE TO OPEN:</span> " . $oscalfile;
			$this->messages->PrependMessages($messages);
			if ( function_exists('ZoneOutputAppend') ) {
				ZoneOutputAppend($messages, 'zone-three');
			}
		}
		return $this->status;
	}

	// ----------------------------------------------------------------------------
	public function Transform($xslt_file_name){
//		var $ret_val;
//		var $status = false;
		$ret_val = false;;
		$status = false;
		
		if ($file_name !== "") {
			$status = true;
		} else { 
			$ret_val = "*** NO XSLT FILE SPECIFIED ***";
			$this->messages->Debug($ret_val);
		}

		if ( $status && file_exists($file_name)) {
		} else { 
			$ret_val = "*** XSLT FILE NOT FOUND (" . $file_name . ") ***";
			$this->messages->Debug(" ---- FILE SAVED! ---- ");
			$this->messages->Debug($ret_val);
		}

		if ($status) {

			$proc = new XSLTProcessor();
			$proc->importStyleSheet($xslt_file_name);

			$tmpcntr = 0;
			$ret_val = $proc->transformToXML($this->dom);
			if ( $ret_val === false) {
				$ret_val = "Could not transform. Problem applying XSLT to XML.<br/>";
				$this->messages->Debug($ret_val);
				$status = false;
			}
		}

		return $ret_val;
	}


	// ----------------------------------------------------------------------------
	public function Save($file_name=""){
		
		if ($file_name == "") {
			$file_name = $this->file_name;
		}
		$this->SetLastModified();
		$this->status = $this->dom->Save($file_name);
		if ($this->status) {
			$this->messages->Debug(" ---- FILE SAVED! ---- ");
			$this->status = $this->Open($file_name, true);
			if ( $this->status) {
				$this->messages->Logging("Saved and Reopened: ". $file_name);
			} else {
				$this->messages->PrependMessages(" **** ERROR RELOADING FILE AFTER SAVE! **** ");
			}
		} else {
			$this->messages->PrependMessages(" **** ERROR SAVING FILE! **** ");
		}

		return $this->status;
	}

	// ----------------------------------------------------------------------------
	public function SetLastModified(){
		
		$last_modified = date(DATE_TIME_STORE_FORMAT);
		$last_modified_object = $this->Query(OSCAL_METADATA_LAST_MODIFIED);
		
		if ($last_modified_object === false) { // last-modified doesn't exist (non-typical)
			$last_modified_object = $this->dom->createElement(basename(OSCAL_METADATA_LAST_MODIFIED), $last_modified);
//			$ret_val = InsertOSCALdata($this->dom, OSCAL_METADATA_LAST_MODIFIED . "/..", $last_modified_object);
			$ret_val = $this->Insert(OSCAL_METADATA_LAST_MODIFIED . "/..", $last_modified_object);
		} else {
			$last_modified_object->item(0)->nodeValue = $last_modified;
		}
	}

	// ----------------------------------------------------------------------------
	// Accepts:
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
	function Insert($xpath_to_parent, $data){
		$messages = "";
		$ret_val = false;


	//	Messages ("-- INSERTING: " . $data->nodeName);
		// Use xpath to get parent object
		$parent_obj = QueryListResult($this->dom, $xpath_to_parent);
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
						$import_obj= $this->dom->importNode($data, true);
						// In the specified parameter, find the FIRST element that must appear AFTER the element being inserted.
						// 		If none exists, use appendChild
						//      Otherwise, store in $param_next, and use insertBefore($parameter_new, $param_next)
						$remaining_children_object = $this->Query($xpath_to_parents_remaining_children);
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
	public function Create($oscalroot) {
		global $oscal_roots; // from oscal-config.php
		$this->status = false;


		$this->recognized = true;
		$this->type = $oscal_roots[$spec]["title"];
		$this->schema_file = OSCAL_LOCAL_FILES . $oscal_roots[$oscalroot]["files"]["schema"]["local_file"];
		$this->messages->Messages("CREATING XML FILE IN MEMORY: " . $this->type);
		$this->root_element = $oscalroot;
		$this->title = $oscal_roots[$oscalroot]["title"];
		$this->namespace_explicit = "";
		$this->schema_map = array();
		$this->schema_flat_map = array();
		$this->recognized = false;



		$this->namespace_alias = "oscal";
		$date = new DateTime('NOW');
		$oscal_id = com_create_guid();// "-" . $date->format('Y-m-d--H-i-s');

		$new_oscal = "<?xml version='1.0' encoding='UTF-8'?>
		<" . $oscalroot . " xmlns='" . OSCAL_NS . "'
				 id='" . $oscal_id . "'>
				 <metadata />
		</" . $oscalroot . ">
		";

		$this->dom = new domDocument;
		$this->dom->preserveWhiteSpace = false; 
		$this->dom->loadXML($new_oscal);

		$this->status = true;
		$this->filename = "";
		$this->file_base_name = "";
		$this->project_id = "";
		$this->root_element = $oscalroot;
		$this->dom->formatOutput = true;		

		$this->xpath = new domXPath($this->dom);
		if ($this->xpath->registerNamespace($this->namespace_alias, OSCAL_NS)) {
			$this->status = true;
		} else {
			$this->messages->PrependMessages("Failed to register namespace: " . $ns);
			$this->status = false;
		}
		return $this->status;
	}
	
	// ----------------------------------------------------------------------------
	// Runs an xpath query against the OSCAL object, and returns as follows:
	//     - if one or more results are found, returns a DOMNodeList
	//        (See https://www.php.net/manual/en/domxpath.query.php )
	//     - if no results are found, returns false.
	public function Query($query) {
		$this->messages->Debug("** Query (" . $query . "):");
		$ret_val = false;

		if ($this->namespace_explicit==="") {
			// Do nothing
		} else {
			$query = AddNamespace2xpath($query, $this->namespace_alias);
		}

		$this->messages->Debug(" XPATH: " . $query);
		$result = $this->xpath->query($query);
		$this->messages->Debug(" = (TYPE: " . gettype($result) . ")");
		
		if ($result !== false) {
			if ($result->length > 0) {
				$this->messages->Debug(" FOUND: " . $result->length);
				$ret_val = $result;
			} else {
				$this->messages->Debug(" FOUND: -0-");
				$ret_val = false;
			}
		} else {
			$this->messages->Debug("ERROR: Invalid Xpath Query!");
			$ret_val = false;
		}

		return $ret_val;
	}	
	
	// ----------------------------------------------------------------------------
	public function GetTitle($refresh=false){
		if ($refresh || $this->title == "") {
			$title = $this->Query(OSCAL_METADATA_TITLE);
			if ($title === false) {
				$this->title = "[NO TITLE]";
			} else {
				$this->title = $title->item(0)->nodeValue;
			}
		}
		
		return $this->title;
	}
	
	// ----------------------------------------------------------------------------
	public function GetBasicMetadata($refresh=false){
		$ret_val = array();
		$ret_val['status'] = true;
		$ret_val['file'] = "";

		// Get title
		$result = $this->Query(OSCAL_METADATA_TITLE);
		if ($result === false || $result->item(0)->nodeValue == "") {
			$ret_val['title'] = "[NO TITLE]";
		} else {
			$ret_val['title'] = $result->item(0)->nodeValue;
		}

		// Get document formal publication date
		$result = $this->Query(OSCAL_METADATA_DATE);
		if ($result === false || $result->item(0)->nodeValue == "") {
			$ret_val['date'] = "";
		} else {
			$ret_val['date'] = $result->item(0)->nodeValue;
		}

		// Get document last modified date
		$result = $this->Query(OSCAL_METADATA_LAST_MODIFIED);
		if ($result === false || $result->item(0)->nodeValue == "") {
			$ret_val['last-modified'] = "";
		} else {
			$ret_val['last-modified'] = $result->item(0)->nodeValue;
		}

		// Get document version
		$result = $this->Query(OSCAL_METADATA_VERSION);
		if ($result === false || $result->item(0)->nodeValue == "") {
			$ret_val['version'] = "";
		} else {
			$ret_val['version'] = $result->item(0)->nodeValue;
		}

		// Get markings
		$result = $this->Query(OSCAL_METADATA_SENSITIVITY_LABEL);
		if ($result === false || $result->item(0)->nodeValue == "") {
			$ret_val['label'] = "";
		} else {
			$ret_val['label'] = $result->item(0)->nodeValue;
		}

		return $ret_val;
	}
}

// A class to collect messages during a processing event, where 
//    the messages are intended for presentation to the user
//    at the end of processing.
class Messages {
	var $messages;
	var $debug;
	var $line_break;
	
	static $debug_line_break = "\n";

	// Initialize 
	function __construct($line_break_seq="<br />") {
		$this->messages = "";
		$this->debug = "";
		$this->line_break = $line_break_seq;
	}

	// Acumulate messages intended for presentation to the user.
	// Save each of those user messages to debug as well.
	public function Messages($text) {
		$this->messages .= $text . $this->line_break;
		$this->debug .= $text . self::$debug_line_break;
	}

	// Acumulate messages intended for presentation to the user, 
	//    however, insert this message at the top so the user 
	//    sees it first.
	// Also, save each of those user messages to debug.
	public function PrependMessages($text) {
		$this->messages = $text . $this->line_break . $this->messages;
		$this->Debug($text);
	}

	// Accumulate debug messages
	public function Debug($text) {
		$this->debug .= $text . self::$debug_line_break;
	}

	// Returns all accumulated user messages.
	// If $flush is true, this will also clear all user messages.
	// By default, messages continue to accumulate.
	public function GetUserMessages($flush=false) {
		$ret_val = $this->messages;
		if ($flush === true) {
			FlushUserMessages();
		}
		return $ret_val;
	}

	// Clears all user messages.
	public function FlushMessages() {
			$this->messages = "";
	}

	// Returns all accumulated debug messages.
	// Debug messages continue to accumulate.
	public function GetDebug() {
		return $this->debug;
	}
}

if (isset($_SESSION['OSCAL-objects'])) {
	$oscal_objects = $_SESSION['OSCAL-objects'];
} else {
	$oscal_objects = array();
}

