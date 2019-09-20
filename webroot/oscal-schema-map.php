<?php  
require_once('oscal-functions.php');
require_once('oscal-config.php');
$messages = "";
$logging = "";
$this_flat_map = array();

// ============================================================================
// Compile whole schema tree and save to JSON file
// ============================================================================
function ProcessSchema($root) {
global $messages;	
global $logging;	
global $this_flat_map;
global $oscal_roots;
$file_output = "";
$messages = "";

//	$messages .= "<br /><span style='font-size: 16px; font-weight: bold;'>EVALUATING: " . ucwords($root) . " schema</span><br />" .
//		"<span style='color:red;'>This may tak several minutes. Please be patient.</span><br />";

	// The local schema file (assumes the appropriate version of the schema file is available)
	$schema_file = OSCAL_LOCAL_FILES . $oscal_roots[$root]["files"]["schema"]["local_file"];

	$oscal_schema = OpenOSCALfile($schema_file, $root);
	
//	$messages .= "<br />\n";
	$messages .= "<span style='font-weight: bold;'>Compiling " . $root . " schema.</span><br />\n";

	$schema_map = RecurseSchema($oscal_schema, $root);           // Generates nested array
	$schema_map_flat = RecurseSchemaFlat($oscal_schema, $root);  // Generates flat array indexed by xpath

	$file_output = json_encode($schema_map, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
	if (file_put_contents(OSCAL_LOCAL_FILES . "oscal_" . $root . "_schemamap.json", $file_output) === false) {
			$messages .= ("ERROR WRITING FILE");
	}

	$file_output = json_encode($this_flat_map, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
	if (file_put_contents(OSCAL_LOCAL_FILES . "oscal_" . $root . "_flat_schemamap.json", $file_output) === false) {
			$messages .= ("ERROR WRITING FILE");
	}

	$messages .= "<span style='color:red; font-weight:bold;'>DONE!</span><br />";

return $messages;
}

// ============================================================================
// Counts the number of times an element appears in an xpath.
// This does NOT compensate for name space.  
//       If $xpath includes namespace, $element_name must as well.
//            EXAMPLE: $xpath = "//xs:element/xs:complexType/xs:sequence/xs:choice/xs:element"
//                           $element_name = "xs:element"  // returns 2
//                           $element_name = "element"      // returns 0
//       If $xpath does NOT include namespace, $element_name must not either.
//            EXAMPLE: $xapth = "//catalog/group/control/part/part/part"
//                           $element_name = "part"           // returns 3
//                           $element_name = "oscal:part"   // returns 0
// ============================================================================
function in_path_count($xpath, $element_name) {
	$count = 0;
	$start_pos = 0;
	$found_pos = 0;

//	echo "<br />!! PATH COUNT: " . $xpath ;
	
	while ($found_pos !== false) {
			$found_pos = strpos($xpath, "/" . $element_name, $start_pos);
			if ($found_pos !== false) {
				$count +=1;
				$start_pos = $found_pos + 1;
			}
	}
	
//	echo "<br />                          -- " . $element_name . " FOUND: " . $count;
return $count;	
	
}
// ============================================================================
// From element level:
//   - formal-name
//   - description
//   - children (complexType)
//   - attributes
//   - datatype (for string, boolean, and default)
//	 - xpath
// From one level up:
//   - minOccurs (required)
//   - maxOccurs (multiple)
//   - 
// ============================================================================
// Finds the definition (define-assembly or define-field) or the element
// identified by the $element_name parameter, then recurses down the 
// schema and builds an array containg all of the definition information.
// The sequence is maintaned, as OSCAL is senstive to sequencing of elements.
// The resulting array is structured as follows:
// $this_map: ( [name] => element_name,
//              [multiple] => true/false,   (boolean - is more than one element allowed?)
//              [required] => true/false,   (boolean - is this a required element?)
//              [datatype] => '',           (the specified data type of the element)
//              [holds_data] => true/false, (boolean - can the element hold data?)
//              [formal-name] => '',        (The human-friendly name)
//              [description] => '',        (A description of the element's intended content)
//              [flags] => array( [
//                                 [name] => '', [required] => boolean, [datatype] => '',  [formal-name] => '', [description] => ''] ,
//                                 [ ], ... []
//                                ] ) ,
//              [model] => array( [ ** entire structure (this_map) repeats as needed ** ], 
//              [content] => '' or null     (Place holder for the actual content of the element)           
//              )
//
function RecurseSchema(&$schema_object, $element_name, $required=true, $multiple=false, $rel_position=0, $path="/") {
global $messages;	
$this_map = array();
$too_many_levels = false;

	if (in_path_count($path, $element_name) >= MAX_LEVELS) {
//		$messages .= "<br />MAXIMUM LEVELS EXCEEDED: " . $element_name . " is more than " . MAX_LEVELS . " deep.";
		$too_many_levels = true;
	}

//	echo "<br />PROCESSING: " . $path . "/" . $element_name;
	//	$messages .= "<br />PROCESSING: " . $element_name;
	$this_map["name"] = $element_name;
	$this_map["path"] = $path . "/" . $element_name;
	$this_map["position"] = $rel_position; 
	$this_map["required"] = $required;
	$this_map["multiple"] = $multiple;
	$this_map["holds_data"] = false;
	$this_map["datatype"] = '';
	$this_map["flags"] = array();
	$this_map["content"] = null;
	
	// Look for the element identified by $element_name
	$query = "//xs:schema/xs:element[@name='" . $element_name . "']"; 
	$assembly = $schema_object["XPATH"]->query($query);
	if ($assembly->length > 0) { // If $element_name found, continue.

		// If the formal name is speified, get it. Otherwise, use the element name.
		$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:annotation/xs:appinfo/m:formal-name"; 
		$data_obj = $schema_object["XPATH"]->query($query);
		if ($data_obj !== false) {
			$this_map["formal-name"] = $data_obj->item(0)->nodeValue;
		} else {
			
			$this_map["formal-name"] = $element_name; 
		}

		// If the description is speified, get it. Otherwise, set to empty string.
		$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:annotation/xs:appinfo/m:description"; 
		$data_obj = $schema_object["XPATH"]->query($query);
		if ($data_obj !== false) {
			$this_map["description"] = RemoveWhiteSpace($data_obj->item(0)->nodeValue);
		} else {
			$this_map["description"] = "[ Description Not Available ]"; 
		}

		// Determine if the element can hold data. If so, determine the data type.
		// If the datatype is specified, capture it. Otherwise default to "string" type.
		if ($assembly->item(0)->hasAttribute("type")) {
			$this_map["datatype"] = StripNameSpace($assembly->item(0)->getAttribute("type"));
			$this_map["holds_data"] = true;
		} else {
			// If complexType exists, we need to dig deeper.
			$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:complexType"; 
			$mixed_obj = $schema_object["XPATH"]->query($query);
			if ($mixed_obj->length > 0) {
				if ($mixed_obj->item(0)->hasAttribute("mixed")) { // 
					if ($mixed_obj->item(0)->getAttribute("mixed") == true) {
						$this_map["holds_data"] = true;
						$this_map["datatype"] = "string";
					} else {
						$this_map["holds_data"] = false;
						$this_map["datatype"] = "none";					}
				} else {
					$this_map["holds_data"] = false; 
					$this_map["datatype"] = "none";	
				}
			} else { // If there is no @type attribute, and no complexType schema, assume string.
				$this_map["holds_data"] = true;
				$this_map["datatype"] = "string";
			}

			// Check to see if this element is of type PROSE
			$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:complexType/xs:sequence/xs:group/@ref"; 
			$group_obj = $schema_object["XPATH"]->query($query);
			if ($group_obj->length > 0) {
				if (stripos($group_obj->item(0)->nodeValue, "PROSE") > 0 ) {
					$this_map["holds_data"] = true;
					$this_map["datatype"] = "prose";
				} else {
					$messages .= "<br />WARNING: Unexpected value (" . $group_obj->item(0)->nodeValue . ") for @ref in " . $query;
				}
			}
			// Check to see if this element is of type mixed content
			$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:complexType/xs:group/@ref"; 
			$group_obj = $schema_object["XPATH"]->query($query);
			if ($group_obj->length > 0) {
				if (stripos($group_obj->item(0)->nodeValue, "everything-inline") > 0 ) {
					$this_map["holds_data"] = true;
					$this_map["datatype"] = "mixed";
				} else {
					$messages .= "<br />WARNING: Unexpected value (" . $group_obj->item(0)->nodeValue . ") for @ref in " . $query;
				}
			}
		}

		// If the element has children, process them.
		$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:complexType/xs:sequence/xs:element | //xs:schema/xs:element[@name='" . $element_name . "']/xs:complexType/xs:sequence/xs:choice"; 
		$children_obj = $schema_object["XPATH"]->query($query);
		$child_position = 0;
		$child_sequence_position = 0;
		
		foreach($children_obj as $child) {
			$element_name_temp = StripNameSpace($child->getAttribute('ref'));
			if ($child->nodeName == "xs:element") {
				$this_map['model'][$element_name_temp]= ProcessChildElement($child, $schema_object, $child_sequence_position, $this_map["path"] , $too_many_levels);

			} elseif ($child->nodeName == "xs:choice") {
				foreach($child->childNodes as $choice_child) {
					if ($choice_child->nodeName != "#text") {
						$this_map['model'][$child_position]= ProcessChildElement($choice_child, $schema_object, $child_sequence_position, $this_map["path"], $too_many_levels );
						$child_position += 1;
					} else {
						// "<br />SKIPPING #text";
					}
				}
			}
			$child_position += 1;
			$child_sequence_position += 1;
		}
		
		// FLAGS
		$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:complexType/xs:attribute"; 
		$attrib_obj = $schema_object["XPATH"]->query($query);
		foreach($attrib_obj as $attrib) {
			if ($attrib->hasAttribute('name')) {
				$attrib_array = array();
				$attrib_array['name'] = $attrib->getAttribute('name');
				if ($attrib->hasAttribute('use') && $attrib->getAttribute('use') == 'required') {
					$attrib_array['required'] = true;
				} else {
					$attrib_array['required'] = false;
				}
				if ($attrib->hasAttribute('type') ) {
					$attrib_array['datatype'] = StripNameSpace($attrib->getAttribute('type'));
				} else {
					$attrib_array['datatype'] = 'string';
				}

				$attrib_annotation_obj = $schema_object["XPATH"]->query($query . "[@name='" . $attrib->getAttribute('name') . "']/xs:annotation/xs:appinfo/m:formal-name");
				if ($attrib_annotation_obj !== false ) {
					$attrib_array['formal-name'] = $attrib_annotation_obj->item(0)->nodeValue;
				} else {
					$attrib_array['formal-name'] = $attrib->getAttribute('name');
				}
				$attrib_array['description'] = ""; 

				$attrib_annotation_obj = $schema_object["XPATH"]->query($query . "[@name='" . $attrib->getAttribute('name') . "']/xs:annotation/xs:appinfo/m:description");
				if ($attrib_annotation_obj !== false ) {
					$attrib_array['description'] = $attrib_annotation_obj->item(0)->nodeValue;
				} else {
					$attrib_array['description'] = '[ Description not available ]';
				}
				array_push($this_map["flags"], $attrib_array);
			} else {
				$messages .= "<br />WARNING: Attribute with no @name attribute at " . $query;
			}
		}
	} else {
		$messages .= "<br />ERROR : " . $element_name . " NOT FOUND!";
	}

	
return $this_map;	
}

// ============================================================================
function RecurseSchemaFlat(&$schema_object, $element_name, $required=true, $multiple=false, $rel_position=0, $path="/") {
global $messages;	
global $this_flat_map;
$too_many_levels = false;

	if (in_path_count($path, $element_name) > MAX_LEVELS) {
//		$messages .= "<br />MAXIMUM LEVELS EXCEEDED: " . $element_name . " is more than " . MAX_LEVELS . " deep.";
		$too_many_levels = true;
	}

	$this_path = $path . "/" . $element_name;

//	echo "<br />PROCESSING: " . $path . "/" . $element_name;
	//	$messages .= "<br />PROCESSING: " . $element_name;
	$this_flat_map[$this_path]["name"] = $element_name;
	$this_flat_map[$this_path]["path"] = $path . "/" . $element_name;
	$this_flat_map[$this_path]["position"] = $rel_position; 
	$this_flat_map[$this_path]["required"] = $required;
	$this_flat_map[$this_path]["multiple"] = $multiple;
	$this_flat_map[$this_path]["holds_data"] = false;
	$this_flat_map[$this_path]["datatype"] = '';
	$this_flat_map[$this_path]["flags"] = array();
	
	// Look for the element identified by $element_name
	$query = "//xs:schema/xs:element[@name='" . $element_name . "']"; 
	$assembly = $schema_object["XPATH"]->query($query);
	if ($assembly->length > 0) { // If $element_name found, continue.

		// If the formal name is speified, get it. Otherwise, use the element name.
		$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:annotation/xs:appinfo/m:formal-name"; 
		$data_obj = $schema_object["XPATH"]->query($query);
		if ($data_obj !== false) {
			$this_flat_map[$this_path]["formal-name"] = $data_obj->item(0)->nodeValue;
		} else {
			
			$this_flat_map[$this_path]["formal-name"] = $element_name; 
		}

		// If the description is speified, get it. Otherwise, set to empty string.
		$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:annotation/xs:appinfo/m:description"; 
		$data_obj = $schema_object["XPATH"]->query($query);
		if ($data_obj !== false) {
			$this_flat_map[$this_path]["description"] = RemoveWhiteSpace($data_obj->item(0)->nodeValue);
		} else {
			$this_flat_map[$this_path]["description"] = "[ Description Not Available ]"; 
		}

		// Determine if the element can hold data. If so, determine the data type.
		// If the datatype is specified, capture it. Otherwise default to "string" type.
		if ($assembly->item(0)->hasAttribute("type")) {
			$this_flat_map[$this_path]["datatype"] = StripNameSpace($assembly->item(0)->getAttribute("type"));
			$this_flat_map[$this_path]["holds_data"] = true;
		} else {
			// If complexType exists, we need to dig deeper.
			$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:complexType"; 
			$mixed_obj = $schema_object["XPATH"]->query($query);
			if ($mixed_obj->length > 0) {
				if ($mixed_obj->item(0)->hasAttribute("mixed")) { // 
					if ($mixed_obj->item(0)->getAttribute("mixed") == true) {
						$this_flat_map[$this_path]["holds_data"] = true;
						$this_flat_map[$this_path]["datatype"] = "string";
					} else {
						$this_flat_map[$this_path]["holds_data"] = false;
						$this_flat_map[$this_path]["datatype"] = "none";					}
				} else {
					$this_flat_map[$this_path]["holds_data"] = false; 
					$this_flat_map[$this_path]["datatype"] = "none";	
				}
			} else { // If there is no @type attribute, and no complexType schema, assume string.
				$this_flat_map[$this_path]["holds_data"] = true;
				$this_flat_map[$this_path]["datatype"] = "string";
			}

			// Check to see if this element is of type PROSE
			$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:complexType/xs:sequence/xs:group/@ref"; 
			$group_obj = $schema_object["XPATH"]->query($query);
			if ($group_obj->length > 0) {
				if (stripos($group_obj->item(0)->nodeValue, "PROSE") > 0 ) {
					$this_flat_map[$this_path]["holds_data"] = true;
					$this_flat_map[$this_path]["datatype"] = "prose";
				} else {
					$messages .= "<br />WARNING: Unexpected value (" . $group_obj->item(0)->nodeValue . ") for @ref in " . $query;
				}
			}
			// Check to see if this element is of type mixed content
			$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:complexType/xs:group/@ref"; 
			$group_obj = $schema_object["XPATH"]->query($query);
			if ($group_obj->length > 0) {
				if (stripos($group_obj->item(0)->nodeValue, "everything-inline") > 0 ) {
					$this_flat_map[$this_path]["holds_data"] = true;
					$this_flat_map[$this_path]["datatype"] = "mixed";
				} else {
					$messages .= "<br />WARNING: Unexpected value (" . $group_obj->item(0)->nodeValue . ") for @ref in " . $query;
				}
			}
		}

		// If the element has children, process them.
		$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:complexType/xs:sequence/xs:element | //xs:schema/xs:element[@name='" . $element_name . "']/xs:complexType/xs:sequence/xs:choice"; 
		$children_obj = $schema_object["XPATH"]->query($query);
		$child_position = 0;
		$child_sequence_position = 0;
		if ($children_obj->length > 0) {
			$this_flat_map[$this_path]['model'] = array();
		}
		
		foreach($children_obj as $child) {
			
			if ($child->nodeName == "xs:element") {
				if ($child->hasAttribute('ref')) {
					$element_name = StripNameSpace($child->getAttribute('ref'));
					// $this_flat_map[$this_path]['model'][$child_position]= $element_name;
					array_push($this_flat_map[$this_path]['model'], $element_name);
					ProcessChildElementFlat($child, $schema_object, $child_sequence_position, $this_path , $too_many_levels);
				} else {
					// error is raised in ProcessChildElementFlat. No need to raise it here too.
				}

			} elseif ($child->nodeName == "xs:choice") {
				foreach($child->childNodes as $choice_child) {
					if ($choice_child->nodeName != "#text") {
						if ($choice_child->hasAttribute('ref')) {
							$element_name = StripNameSpace($choice_child->getAttribute('ref'));
							// $this_flat_map[$this_path]['model'][$child_position]= $element_name;
							array_push($this_flat_map[$this_path]['model'], $element_name);
							ProcessChildElementFlat($choice_child, $schema_object, $child_sequence_position, $this_path , $too_many_levels);
							$child_position += 1;
						} else {
							// error raised in ProcessChildElementFlat. Not necessary here too.
						}
					} else {
						// "<br />SKIPPING #text";
					}
				}
			}
			$child_position += 1;
			$child_sequence_position += 1;
		}
		
		// FLAGS
		$query = "//xs:schema/xs:element[@name='" . $element_name . "']/xs:complexType/xs:attribute"; 
		$attrib_obj = $schema_object["XPATH"]->query($query);
		foreach($attrib_obj as $attrib) {
			if ($attrib->hasAttribute('name')) {
				$attrib_array = array();
				$attrib_array['name'] = $attrib->getAttribute('name');
				if ($attrib->hasAttribute('use') && $attrib->getAttribute('use') == 'required') {
					$attrib_array['required'] = true;
				} else {
					$attrib_array['required'] = false;
				}
				if ($attrib->hasAttribute('type') ) {
					$attrib_array['datatype'] = StripNameSpace($attrib->getAttribute('type'));
				} else {
					$attrib_array['datatype'] = 'string';
				}

				$attrib_annotation_obj = $schema_object["XPATH"]->query($query . "[@name='" . $attrib->getAttribute('name') . "']/xs:annotation/xs:appinfo/m:formal-name");
				if ($attrib_annotation_obj !== false ) {
					$attrib_array['formal-name'] = $attrib_annotation_obj->item(0)->nodeValue;
				} else {
					$attrib_array['formal-name'] = $attrib->getAttribute('name');
				}
				$attrib_array['description'] = ""; 

				$attrib_annotation_obj = $schema_object["XPATH"]->query($query . "[@name='" . $attrib->getAttribute('name') . "']/xs:annotation/xs:appinfo/m:description");
				if ($attrib_annotation_obj !== false ) {
					$attrib_array['description'] = $attrib_annotation_obj->item(0)->nodeValue;
				} else {
					$attrib_array['description'] = '[ Description not available ]';
				}
				array_push($this_flat_map[$this_path]["flags"], $attrib_array);
			} else {
				$messages .= "<br />WARNING: Attribute with no @name attribute at " . $query;
			}
		}
	} else {
		$messages .= "<br />ERROR : " . $element_name . " NOT FOUND!";
	}

	
// return $this_map;	
}


// ============================================================================
function ProcessChildElementFlat($child, &$schema_object, $rel_position, $path, $too_many_levels) {
global $messages;

	if ($child->hasAttribute('ref')) {
		$element_name = StripNameSpace($child->getAttribute('ref'));
		// If minOccurs attribute exists and ="1", then $required = true. Otherwise false.
		$required = false;
		if ($child->hasAttribute('minOccurs')) {
			if ($child->getAttribute('minOccurs') == "1") {
				$required = true;
			}
		}

		// If maxOccurs attribute exists and ="1", then $multiple = false. Otherwise true.
		$multiple = true;
		if ($child->hasAttribute('maxOccurs')) {
			if ($child->getAttribute('maxOccurs') == "1") {
				$multiple = false;
			}
		}
		
		if ( ! $too_many_levels) {
			RecurseSchemaFlat($schema_object, $element_name, $required, $multiple, $rel_position, $path);
//		} else {
//			$this_map = null;
		}
	} else {
		$messages .= "<br />***MISSING @ref ATTRIBUTE. COULD NOT PROCESS ELEMENT.";
//		$this_map = null;
	}
//	return $this_map;
}


// ============================================================================
function ProcessChildElement($child, &$schema_object, $rel_position, $path, $too_many_levels) {
global $messages;

	if ($child->hasAttribute('ref')) {
		$element_name = StripNameSpace($child->getAttribute('ref'));
		// If minOccurs attribute exists and ="1", then $required = true. Otherwise false.
		$required = false;
		if ($child->hasAttribute('minOccurs')) {
			if ($child->getAttribute('minOccurs') == "1") {
				$required = true;
			}
		}

		// If maxOccurs attribute exists and ="1", then $multiple = false. Otherwise true.
		$multiple = true;
		if ($child->hasAttribute('maxOccurs')) {
			if ($child->getAttribute('maxOccurs') == "1") {
				$multiple = false;
			}
		}
		
		if ( ! $too_many_levels) {
			$this_map = RecurseSchema($schema_object, $element_name, $required, $multiple, $rel_position, $path);
		} else {
			$this_map = null;
		}
	} else {
		$messages .= "<br />***MISSING @ref ATTRIBUTE. COULD NOT PROCESS ELEMENT.";
		$this_map = null;
	}
	return $this_map;
}


// ============================================================================
function Logging($text) {
global $logging;	

$logging .= "<br />-- " . $text;

}
// ============================================================================
// ============================================================================

	
?>
