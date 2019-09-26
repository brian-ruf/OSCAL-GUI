<?php
// OSCAL Objects
// This is a work-in-progress: Converting common OSCAL functions into
//    PHP classes for a more object-oriented approach.

// Classes defined:
// ==  OSCAL Class
// ==  Messages Class

require_once('oscal-config.php');

// ============================================================================
// ==  OSCAL Class Definition (Experimental)
// ============================================================================
// When instantiating this class, one argument must be passed.
//    This class will first check to see if the argument is a recognized OSCAL
//       root file. If not, it will check to see if it is a recognized project
//       ID. If not, it will check to see if it is a valid XML file name.
// 
// -- If the argument is the name of a recognized OSCAL root element, this class
//       will create and return a new OSCAL object with an "empty" OSCAL file.
// -- (FUTURE) If the arugment is a recognized project ID, this class will open the XML 
//       file associated with that ID.
// -- If the argument is the name of of a valid XML file, this class will 
//       attempt to open the XML file.
// -- If none of the above are valid, the class will return null.
//
// NOTE: An "empty" OSCAL file minimally contains the root element, metadata, 
//       backmatter, and place-holders for certain required elements, such as 
//       //metadata/title and //metadata/oscal-version, and assigns the 
//       OSCAL name spece anda unique UUID to the @id attrbute at the root.
class OSCAL {
	// Member variables 
	var $project_id;
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
			$this->recognized = true;
			$this->type = $oscal_roots[$this->root_element]["title"];
			$this->schema_file = OSCAL_LOCAL_FILES . $oscal_roots[$this->root_element]["files"]["schema"]["local_file"];
			$this->messages->Messages("RECOGNIZED OSCAL TYPE: " . $this->type);
			$this->root_element = $spec;
			// ******** NOT COMPLETE
			// if valid, create new OSCAL file
		} else {
			$this->recognized = false;
			$this->status = false;
		}
		
		// Check for valid project ID
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

		// If everything went well, populate an array with return values.
		// Otherwise, populate an array with error information.
		if (! $this->status) {

			$this->messages->PrependMessages("<span style='font-weight:bold; color:red;'>UNABLE TO OPEN:</span> " . $this->oscalfile);
			if ( function_exists('ZoneOutputAppend') ) {
				ZoneOutputAppend($messages, 'zone-three');
			}
		}
		return $this->status;
	}

	// ----------------------------------------------------------------------------
	public function Save($file_name=""){
		
		if ($file_name == "") {
			$file_name = $this->file_name;
		}
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
	public function Create($oscalroot) {
		global $oscal_roots; // from oscal-config.php
		$this->status = false;

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
			$title = $this->Query("//metadata/title");
			if ($title === false) {
				$this->title = "[NO TITLE]";
			} else {
				$this->title = $title->item(0)->nodeValue;
			}
		}
		
		return $this->title;
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

