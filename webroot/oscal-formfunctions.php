<?php  
// ============================================================================
function PopulateFormFields($oscal, $xapth, $exclude=false) {
	$ret_val = false;
	
	$base_xpath = StripAttributes($xpath);
	$flat_schema_map = GetFlatSchemaMap($oscal['root']);
	$data_set = QueryListResult($oscal, $xpath);
		
	if (isset($flat_schema_map[$base_xpath])) {
		$data_type = $flat_schema_map[$base_xpath]['datatype'];
		if ($flat_schema_map[$base_xpath]['multiple']) {

		} else {
			// single (0 or 1 required)
		}
		
		
	} else {
		Logging("NOT FOUND IN FLAT SCHEMA (" . $oscal['root'] . "): " . $base_xpath);
		$ret_val = false;
	}

	return $ret_val;
}

// ============================================================================
function BuildXMLQueries($definition, $indent, $path="" ){
	$output = "";
	if ($path == "") {
		$output .= NLandTabs($indent). "\n// === XPATH QUERIES ===";
		$path = "//". $definition["name"];
	} else {
		$path .= "/". $definition["name"];
	}
	$field_var_name = field_var_name($path);
	
    if( is_array($definition) ){
		if ( array_key_exists("flags", $definition) ) {
			if (count($definition['flags']) > 0) {
				$output .= NLandTabs($indent). $field_var_name . "_flags = array(";
				$first_flag = true;
				foreach($definition["flags"] as $flag){
					if ($first_flag) {
						$first_flag = false;
					} else {
						$output .= ", ";
					}
					$output .= "'" . $flag['name'] . "'"; // BuildXML_AttributeQueries($flag);
				}
				$output .= ");  // All possible attributes this element might have set.";
			} else {
				$output .= NLandTabs($indent). $field_var_name . "_flags = array();  // No attributes for this element.";
			}
		} else {
			$output .= NLandTabs($indent). $field_var_name . "_flags = array();";
		}

		$output .= NLandTabs($indent). $field_var_name . " = QueryListArray($" . "oscal, '" . $path . "', " . $field_var_name . "_flags);\n";

		if( array_key_exists("model", $definition) ){
			$indent += 1;
			foreach($definition["model"] as $model){
				$output .= BuildXMLQueries($model, $indent, $path);
			}
			$indent -= 1;
		}
    }

	return $output;
}

// ============================================================================

// ============================================================================
// Within the recursion individual fields are listed  - table one row per field
// Each row will contain three columns:
//    -- Col 1: Field Label
//    -- Col 2: Field Data (input, text area, etc.)
//            Where appropriate, controls are also added to Col 2 (up/down/add/del)
function MakeFormRow($definition, $indent=0, $field_var_name="", $cntr_name="", $path="") {
	$indent +=1;
	$output = "";
	$id_var = str_replace("$", "", $field_var_name) . "__\" . " . $cntr_name . " . \"" ;
	$name_var = str_replace("//", "", $path) . "[" . $cntr_name . "]";

	$output .= NLandTabs($indent). "$"."html .= <<<HTML\n"; // restart the heredoc string
	$output .= NLandTabs($indent). "<!-- Form Row - START (" . $definition['name'] . ") -->";

	$output .= NLandTabs($indent). "<tr>";
	$indent += 1;
	// Col 1: Field Label
		if( array_key_exists("model", $definition) ){
			$output .= NLandTabs($indent). "<td colspan='2' style='width: auto; text-align: left; vertical-align: top;'>";
			$title_style = "font-weight: bold;";
		} else {
			$output .= NLandTabs($indent). "<td style='width: auto; text-align: right; vertical-align: top;'>";
			$title_style = "";
		}
			
		$indent +=1;
			$output .= NLandTabs($indent). "<span title='FIELD: " . $definition["name"] . " -- DATATYPE: " . $definition["datatype"] . "' style='" . $title_style . "'>";

			if ($definition["name"] == "id") {
				$output .= strtoupper($definition["formal-name"]);
			} else {
				$output .= ucwords($definition["formal-name"]);
			}
			$output .= "</span>";

			if ($definition["required"]) {
				$output .= "<span style='color:red;'>*</span>\n";
			}
	$indent -=1;
//	$output .= "\n" . str_repeat("\t", $indent);
	if( ! array_key_exists("model", $definition) ){
		$output .= NLandTabs($indent). "</td>";
	}
	$output .= "\nHTML;\n";

	
	// Col 2: Field Data (input, text area, etc.)

		if( array_key_exists("model", $definition) ){
			// Handle flags here (for elements with sub-elements and attributes, but no data)
			$output .= HandleFlags($definition, $field_var_name, $id_var, $name_var, $indent);

			$output .= NLandTabs($indent). "$" . "html .= \"<table class='form' style='width=100%;'>\";";
			$output .= NLandTabs($indent);
			foreach($definition["model"] as $model){

				$output .= MetaMap2Form($model, $indent+1, $path, false);
			}
			$output .= NLandTabs($indent). "$" . "html .= \"</table>\";";
			$output .= NLandTabs($indent). "$" . "html .= \"<br />\";";
		} else {
			$output .= NLandTabs($indent). "$" . "html .= \"<td style='width: auto; text-align: left; vertical-align: top;'>\";";
			$value_var = $field_var_name . "[\"value\"]"; // ******
			$output .= MakeInput ($definition, $value_var, $indent, false, $id_var, $name_var . "[0]");

			// Handle flags here (for elements with data)
			$output .= HandleFlags($definition, $field_var_name, $id_var, $name_var, $indent);
		}

		$output .= NLandTabs($indent). "$" . "html .= GenerateToolsMenu(" . ($definition["multiple"] ? "true" : "false") . ", " . $cntr_name . ", " . $cntr_name . "_max, (" . $field_var_name . "['value'] === null )) ;";
		$output .= NLandTabs($indent). "$" . "html .= \"</td>\";";
		$output .= NLandTabs($indent). "$" . "html .= <<<HTML";

	$indent -=1;
	$output .= NLandTabs($indent). "</tr>";
			
	$output .= NLandTabs($indent). "<!-- Form Row - END (" . $definition['name'] . ") -->";
	$output .= "\nHTML;\n";
	
	return $output;
}

// ============================================================================
function HandleFlags($definition, $field_var_name, $id_var, $name_var, $indent){
	$output = "";
	
	if ( array_key_exists("flags", $definition) ) {
		foreach ($definition["flags"] as $flag) {
			$output .= NLandTabs($indent). "$" . "html .= \"<br /><span title='FLAG: " . $flag["name"] . " -- TYPE: " . $flag["datatype"] . "'>";
			$output .= ucwords($definition['formal-name']) . ": " . ucwords($flag["formal-name"]) . "</span>&nbsp;\";"; // &rAarr;
			$value_var = $field_var_name . "['flags']['" . $flag["name"] . "']" ;  
			$id_var .= "__" . $flag["name"] ;
			$output .= MakeInput ($flag, $value_var, $indent, true, $id_var, $name_var . "[\\\"" . $flag["name"] . "\\\"]");
		}
	}
	
	return $output;
}

// ============================================================================
function MakeInput ($definition, $value_var="", $indent=0, $is_flag=false, $id_var, $name_var) {
$indent +=1;
$output = "";
	
	$output .= NLandTabs($indent). "$" . "html .= \"";
	
	$output .= NLandTabs($indent);
	switch($definition["datatype"]) {
		case 'empty':
			// will never have data
			break;
		case 'boolean':
			$output .= BooleanArea($definition, $value_var, $indent, $id_var, $name_var);
			break;
		case 'prose':
			$output .= ProseTextArea($definition, $value_var, $indent, $id_var, $name_var);
			break;
		case 'mixed':
			$output .= MixedArea($definition, $value_var, $indent, $id_var, $name_var);
			break;
		case 'integer':
		case 'nonNegativeInteger':
		case 'positiveInteger':
		case 'dateTime-with-timezone':
		case 'date-with-timezone':
		case 'email':
		case 'hostname':
		case 'ip-v4-address':
		case 'ip-v6-address':
		case 'uri':
		case 'uri-reference':
		case 'base64':
		case 'string':
		default:   // This is not a mistake. String is also the default.
//			$output .= " (" . $definition["datatype"] .")";
			$output .= InputField($definition, $value_var, $indent, $is_flag, $id_var, $name_var);
			break;
	}
	
//	$output .= MakeFieldDataInputs($definition, $value_var, $indent, $id_var, $name_var);
	
	$output .= "\";\n";
	

// ZoneOutputAppend($output . "<br />");

return $output;	
	
}

// ============================================================================
// Not used. Consider removing.
function MakeFieldDataInputs($definition, $value_var="", $indent=0, $id_var, $name_var) {
$output = "";
$indent +=1;
$description = DisplayDescription($definition["description"], $definition["name"]);

	$output .= NLandTabs($indent). "<input ";
	$output .= " id='" . $id_var . "_type'";
	$output .= " name='" . $name_var . "_type'";
	$output .= " type='hidden'";
	$output .= " value='" . $definition["datatype"] . "'";
	$output .= " />";

/*	
	$output .= NLandTabs($indent). "<input ";
	$output .= " id='" . $id_var . "_position'";
	$output .= " name='" . $name_var . "_position'";
	$output .= " type='hidden'";
	$output .= " value='" . $definition["position"] . "'";
	$output .= " />";
	
	if (false) { // ******
		$output .= NLandTabs($indent). "<input ";
		$output .= " id='" . $id_var . "_id'";
		$output .= " name='" . $name_var . "_id'";
		$output .= " type='hidden'";
		$output .= " value='{" . $value_var . "_id}'";
		$output .= " />";
	}
*/

return $output;	
}

// ============================================================================
function field_var_name($text) {

$find    = array("-", "//", "/", "*");
$replace = array("_", "_", "_", "_");

$ret_val = "$" . "oscal" . str_replace($find, $replace, $text);
return $ret_val;
}

// ============================================================================
function InputField($definition, $value_var="", $indent=0, $is_flag, $id_var, $name_var) {
$output = "";
$indent +=1;
$description = DisplayDescription($definition["description"], $definition["name"]);

	$output .= "	<input ";
	$output .= " id='" . $id_var . "'";
	$output .= " name='" . $name_var . "'";
	switch($definition["datatype"]) {
		case 'numeric':
			$output .= " type='number'";
			$output .= " style='width: 50px;'";
			break;
		case 'date':
			$output .= " type='date'";
			$output .= " style='width: 100px;'";
			break;
		case 'string':
		default:   // This is not a mistake. String is also the default.
			$output .= " type='text'";
			if ($is_flag) {
				$output .= " style='width: 300px;'";
			} else {
				$output .= " style='width: 500px;'";
			}
			break;
	}

	if ($is_flag) {
		$output .= " class='attribute'";
	}
	$output .= " value='{" . $value_var . "}'";
	$output .= " placeholder='" . EMPTY_MESSAGE . "'";
	$output .= " title='" . $description . "'";
	$output .= " />";
	
return $output;	
}


// ============================================================================
function BooleanArea($definition, $value_var="", $indent=0, $id_var, $name_var) {
$output = "";
$indent +=1;
$description = DisplayDescription($definition["description"], $definition["name"]);

	$output .= NLandTabs($indent). "<input ";
	$output .= " type='radio'";
	$output .= " name='" . $name_var .  "'";

	$output .= " id='" . $id_var.  "_yes'";
	$output .= " value='true'";
	$output .= " title='" . $description . "'";
	$output .= " />&nbsp;True";
	$output .= "&nbsp;&nbsp;";
	
	$output .= "<input ";
	$output .= " type='radio'";
	$output .= " name='" . $name_var .  "'";

	$output .= " id='" . $id_var .  "_no'";
	$output .= " value='false'";
	$output .= " title='" . $description . "'";
	$output .= " />&nbsp;False";
	$output .= "&nbsp;&nbsp;";
	
	$output .= "<input ";
	$output .= " type='radio'";
	$output .= " name='" . $name_var .  "'";

	$output .= " id='" . $id_var .  "_unselected'";
	$output .= " value=''";
	$output .= " title='" . $description . "'";
	$output .= " />&nbsp;No Answer";

return $output;	
}

// ============================================================================
function MixedArea($definition, $value_var="", $indent=0, $id_var, $name_var) {
// $output = "\n";
$indent +=1;
$description = DisplayDescription($definition["description"], $definition["name"]);

	$output .= NLandTabs($indent). "<div";
	$output .= " title='" . $description . "'";
	$output .= " >";
	$indent +=1;
	$output .= NLandTabs($indent). "<input ";
	$output .= " id='mixed-" . $id_var .  "'";
	$output .= " name='" . $name_var .  "'";
	$output .= " class='mixed_editing'";
	$output .= " style='width: 500px; height: 15px;'";
	$output .= " title='" . $description . "'";
//	$output .= " placeholder='" . $description . "'";
	$output .= " placeholder='" . EMPTY_MESSAGE . "'";
	$output .= " type='text'";
	$output .= " value='{" . $value_var . "}'";
	
	$output .= " />";

	$output .= NLandTabs($indent). "<script>";
		$indent +=1;
		$output .= NLandTabs($indent). "oscal_" . $id_var .  " = RichText('mixed', '#mixed-". $id_var ."');";
	$indent -=1;
	$output .= NLandTabs($indent). "</script>";

	$indent -=1;
	$output .= NLandTabs($indent). "</div>";

return $output;	
}

// ============================================================================
function ProseTextArea($definition, $value_var="", $indent=0, $id_var, $name_var) {
$output = "";
$indent +=1;
$description = DisplayDescription($definition["description"], $definition["name"]);

	$output .= NLandTabs($indent). "<div ";
	$output .= " title='" . $description . "'";
	$output .= " >";
		$indent +=1;
		$output .= NLandTabs($indent). "<textarea ";
		$output .= " id='textarea-" . $id_var . "'";
		$output .= " name='" . $name_var . "'";
		$output .= " class='prose_editing'";
		$output .= " style='width: 500px; height: 50px;'";
		$output .= " title='" . $description . "'";
		$output .= " placeholder='" . EMPTY_MESSAGE . "'>";
//		$output .= " placeholder='" . $description . "'>";
		$output .= "{" . $value_var . "}" ;
		$output .= "</textarea>";

		$output .= NLandTabs($indent). "<script>";
			$indent +=1;
			$output .= NLandTabs($indent). "oscal_" . $id_var .  " = RichText('prose', '#textarea-". $id_var ."');";
		$indent -=1;
		$output .= NLandTabs($indent). "</script>";
	$indent -=1;
	$output .= NLandTabs($indent). "</div>";
	
return $output;	
}

// ============================================================================
// ============================================================================
	
?>
