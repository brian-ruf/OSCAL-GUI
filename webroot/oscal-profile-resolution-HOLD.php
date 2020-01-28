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
$output = "";

$project_id = $_GET["project"];
$project_file = GetProjectDetail($project_id, 'file-with-path');
$base_dir = basename(dirname($project_file));

ClearAllZones();
ZoneAdjust("font-size: 1.5em; max-height:none; font-weight: bold; color: red;", "zone-one");
ZoneAdjust("display:none;", "zone-two-left");
ZoneAdjust("display:none;", "zone-two-right");
ZoneAdjust("display:none;", "zone-two");
// ZoneAdjust("text-align: center; max-height:800px;", "zone-two");
ZoneAdjust("background: color url('./img/spinning_rings_fr_red.gif');", "zone-three");

$resolved = ResolveProfile($project_id);
if ($resolved !== false) {
	$output_file_name = str_ireplace("_profile", "-resolved-profile", basename($project_file, ".xml")) . "_catalog.xml";
	if ($resolved->save(PROJECT_LOCATION . $base_dir . "/__output/" . $output_file_name) != false) {
		ZoneOutputAppend("<br />DONE!", "zone-one");
		
		$project_dir = $project_id . "/__output/";
		$file_pattern = $output_file_name;
		$file_label = "DOWNLOAD RESOLVED PROFILE";
		$date_label = "Resolved on";
		$files = glob(PROJECT_LOCATION . $base_dir . "/__output/*.*");
		if (count($files) > 0) {
			$output = MakeDownloadButtons($files, PROJECT_LOCATION_RELATIVE . $project_dir, $file_pattern, $file_label, $date_label);
		}

		$output .= MakeBackButton("oscal.php?mode=open&project=" . $project_id );
		ZoneOutputAppend($output, "zone-one");
//		ZoneOutputAppend("<br /><a href='./projects/" . $base_dir . "/__output/" . $output_file_name . "' download='" . $output_file_name . "'>DOWNLOAD RESOLVED PROFILE</a>", "zone-one");
	}
} else {
	$output .= "<br />ERROR PROCESSING PROFILE!";
	$output .= MakeBackButton("oscal.php?mode=open&project=" . $project_id );
	ZoneOutputAppend($output, "zone-one");
}

ZoneClose();
// End Main
// ============================================================================

// ----------------------------------------------------------------------------
function ResolveProfile($project_id) {
$messages = "";
	$project_file = GetProjectDetail($project_id, 'file-with-path');
	$base_dir = basename(dirname($project_file));
	$import_file_cntr = 0;
	$import_control_cntr = 0;
	$status = false;
	$ret_val = false;

	$messages .= "FULL PATH/FILE NAME: " . $project_file;
	// First check to see if the project location exists
	if (file_exists($project_file)) {  
		$messages .= "<br />-- " .  "<br />PROJECT EXISTS - RESOLVING";

		$rootname = GetOSCALRoot($project_file);
		if ($rootname == "profile") {
			$oscal = OpenOSCALfile($project_file, $base_dir);
			$title = GetProjectDetail($project_id, 'title');
			ZoneOutput($title, 'header-additional');
			$metadata = GetBasicMetadata($project_file,  $base_dir);

			if ($metadata['status']) {
				$title_output = $metadata['title'];
				$title_hover_output = "";

				if ($metadata['version'] !== "" ) {
					$title_hover_output .= "VERSION: " . $metadata['version'];
				}
				
				if ($metadata['last-modified'] !== "" ) {
					if (strlen($title_hover_output) > 1) {
						$title_hover_output .= " -- ";
					}
					$title_hover_output .= "LAST MODIFIED: " . $metadata['last-modified'];
				}

				if ($metadata['label'] !== "" ) {
					$title_output .= "<br /><span style='background-color: red; color: white; font-weight: bold;'>" . $metadata['label'] . "</span>";
				}
				
				ZoneOutput("<span style='color: red; font-weight: bold; font-size:1.2em' title='" . $title_hover_output . "'>" . $title_output . "</span>", 'header-additional');
			} else {
				ZoneOutput($metadata['title'], 'header-additional');
			}
	
			ZoneOutput("RESOLVING PROFILE ...", 'zone-one');
			ZoneCommand('working_notice');

			$merge_combine = CheckMergeCombine($oscal, $base_dir);
			$merge_as_is = CheckMergeAs_Is($oscal, $base_dir);
			$merge_custom = CheckMergeCustom($oscal, $base_dir);

			// Create new catalog to reflect resolved profile
			$cat_new = CreateOSCALfile("catalog", "resolved-profile");
			if ($cat_new !== false) {  // As long as CreateOSCALfile didn't return false, we can continue

				// Copy Metadata from profile and modify as necessary
				$cat_metadata_query = "//metadata";
				RemoveChildren($cat_new, $cat_metadata_query); 
				$cat_metadata = QueryList($oscal, $cat_metadata_query);
				$cat_new_metadata = $cat_new['DOM']->importNode($cat_metadata, true);
				$cat_new['DOM']->documentElement->appendChild($cat_new_metadata);

				foreach ($oscal["DOM"]->getElementsByTagName("import") as $import) {
					$import_file_cntr += 1;
//					ZoneOutputAppend("<br />IMPORTING FILE #" . $import_file_cntr, 'zone-two');
					$found = false;
					$messages .= "<br />-- " .  "<br />IMPORTING: " . $import->getAttribute("href");
					// CURRENT: 
					// Look to see if file was previously imported into the project.
					// If not, try to retrieve it via the path.
						// FUTURE ****:
						// If neither of the above works, ask the user to specify location.
						//    If the user-specified locaiton works, ask the user if
						//    the profile should be updated with the new location.
					$import_base_file_name = basename($import->getAttribute("href"));
//					$messages .= "<br /> BASE: " . $import_base_file_name;
//					Logging("<br /> BASE: " . $import_base_file_name);
					if (file_exists(PROJECT_LOCATION . $base_dir . "/__import/" . $import_base_file_name)) {
						$messages .= "<br />-- " .  "<br />   FOUND LOCALLY";
						$found = true;
						$import_file = PROJECT_LOCATION . $base_dir . "/__import/" . $import_base_file_name;
					} elseif (check_file($import->getAttribute("href"))) {
						$messages .= "<br />-- " .  "<br />   FOUND AT PATH SPECIFIED";
						$found = true;
						$result = DownloadOSCALFile($import->getAttribute("href"), PROJECT_LOCATION . $base_dir . "/__import/" . $import_base_file_name);
						if ($result['status']) {
							$messages .= "<br />-- " .  "<br />   LOCAL COPY SAVED";
							$import_file = PROJECT_LOCATION . $base_dir . "/__import/" . $import_base_file_name;
							$found = true;
						} else {
							$messages .= "<br />-- " .  "<br />   ERROR COPYING LOCALLY.";
//							$import_file = $import->getAttribute("href");
							$found = false;
						}
					}

					// CURRENT: 
					// Process <include> 
					//    Import node for each call (handle presence/absence of @with-control, @with-subcontrol)
					// Process <exclude> by removing any specified items
					// Detect <match> and report as not yet supported
						// FUTURE ****:
						//    Handle <match> within <include> and <exclude>
					if ($found) {
						$messages .= "<br />-- " .  "<br />   IMPORTING.";
						$cat_get = OpenOSCALfile($import_file);
						foreach ($import->childNodes as $import_item) {
							switch($import_item->nodeName) {
								case 'include':
									if ($import_item->getElementsByTagName('all')->length == 1) {
										// import all groups, controls, and sub-controls that are children to root, and do not process further
										// ****
										break;
									}								
									foreach ($import_item->childNodes as $include_item) {
										$import_control_cntr += 1;
//										ZoneOutput("IMPORTING FILE #" . $import_file_cntr, 'zone-one');
//										ZoneOutputAppend("<br />IMPORTING CONTROL #" . $import_control_cntr, 'zone-one');

										if ($include_item->nodeName == 'call') {
											$messages .= ImportControl($include_item, $cat_get, $cat_new, $merge_combine, $merge_as_is);
										} elseif ($include_item->nodeName == 'match') {
											// FUTURE: Not yet supported
											$messages .= "<br />-- " .  "<br />   ** &lt;match%gt; FOUND - NOT YET SUPPORTED.";
										}
									}
									break;
								case 'exclude':
									$messages .= "<br />-- " .  "<br />   ** &lt;exclude%gt; FOUND - NOT YET SUPPORTED.";
									break;
								default:
									break;
							}
						}
						$status = true;
					} else {
						$messages .= "<br />-- " .  "<br />IMPORT FAILED - UNABLE TO CONTINUE!";
						ZoneOutputAppend("<br />UNABLE TO CONTINUE<br />&nbsp;&nbsp;&nbsp;COULD NOT FIND:<br />&nbsp;&nbsp;&nbsp;" . $import->getAttribute("href"), 'zone-one');
					}
				}
			} else {
				$messages .= "<br />-- " .  "<br />UNABLE TO INITIALIZE CATALOG FOR PROFILE RESOLUTION.";
				ZoneOutputAppend("<br />UNABLE TO INITIALIZE CATALOG FOR PROFILE RESOLUTION.", 'zone-one');
			}
		} else {
			$messages .= "<br />-- " .  "<br />NOT AN OSCAL PROFILE -- TYPE: " . $rootname;
			ZoneOutputAppend("<br />NOT AN OSCAL PROFILE", 'zone-one');
		}
	} else {
		ZoneOutputAppend("<br />ERROR OPENING PROFILE", 'zone-one');
	}

	if ($status) {
		$status = ModifyParameters($cat_new, $oscal);
	}	

	if ($status) {
		$status = ModifyControls($cat_new, $oscal);
	}

	if ($status) {

		// CLEANUP
		
		
			/*  NO LONGER NEED TO DO THIS, BUT CODE MAY BE NEEDED FOR SIMILAR CLEANUP, SO LEAVING AS A FORM OF NOTES
			// Remove assessment objective and assessment actions from control (make this an option later)
			ZoneOutputAppend("REMOVING ASSESSMENT OBJECTIVES, TEST CASES, AND GUIDANCE LINKS", 'zone-one');
			$removexpath = "//part[@name='objective'] | //part[@name='assessment'] | //part[@name='guidance']/link";
			RemoveChildren($cat_new, $removexpath);
			*/
		
		// Remove blank lines
		SetLastModified($cat_new);
		SetTitle($cat_new, " [RESOLVED]", true);

		$cat_new['DOM'] = ReformatXML($cat_new['DOM']);
		
		ZoneCommand('working_notice_stop');
		ZoneAdjust("max-height:600px; box-shadow: 10px 10px 5px grey; border: 1px solid #8a4419; border-style: inset;", 'zone-three');
		ZoneOutput("<pre style='width:100%; max-width:100%;'>" . UseEscapeCodes($cat_new['DOM']->saveXML()) . "</pre>", 'zone-three');
		
		$ret_val = $cat_new['DOM'];
		
	} else {
		ZoneCommand('working_notice_stop');
		ZoneOutput("ERROR RESOLVING PROFILE!", 'zone-three');
		$ret_val = false;
	}
	Logging ($messages);
	return $ret_val;
}

// ----------------------------------------------------------------------------
function ModifyControls(&$cat_new, &$profile) {
	global $messages;
	$status = false;
	$ret_val = true;
	$control_level = "";

	$query_alter = "//modify/alter";
	$profile_alter_list = QueryListResult($profile, $query_alter);
	foreach ($profile_alter_list as $alter_node) { // cycles through each <alter> tag
	
		if ($alter_node->hasAttribute('control-id')) {
			$control_id = $alter_node->getAttribute('control-id');
			$control_alter_query = "//modify/alter[@control-id='" . $control_id . "']";
			$control_query = "//control[@id='" . $control_id . "']";
			$control_level="control";
			$status = true;
		} elseif ($alter_node->hasAttribute('subcontrol-id')) {
			$control_id = $alter_node->getAttribute('subcontrol-id');
			$control_alter_query = "//modify/alter[@subcontrol-id='" . $control_id . "']";
			$control_query = "//subcontrol[@id='" . $control_id . "']";
			$control_level="subcontrol";
			$status = true;
		} else {
			$status = false;
			ZoneOutputAppend("<br />INVALID PROFILE SYNTAX: //modify/alter must have either @control-id attribute. Please correct the OSCAL file and try again.", 'zone-two');
			$messages .= "<br />-- " .  ("//modify/alter must have either an @control-id attribute.");
		}
		
		if ($status) {
//			ZoneOutput("UPDATING CONTROL: " . $control_id, 'zone-one');
			$control_remove_items = QueryListResult($profile, $control_alter_query . "/remove");
			if ($control_remove_items !== false) {
				foreach($control_remove_items as $profile_alter_item) { // cycles through each tag under <alter>
					// ****** NEED TO TEST 
				
					// build xpath statements for each possible attribute
					$remove_xpath = "//control[@id='" . $control_id . "']//*";
					if ($profile_alter_item->hasAttribute('id-ref')) {
						$remove_xpath .= "[@id='" . $profile_alter_item->getAttribute('id-ref') . "']";
					}
					if ($profile_alter_item->hasAttribute('name-ref')) {
						$remove_xpath .= "[@name='" . $profile_alter_item->getAttribute('name-ref') . "']";
					}
					if ($profile_alter_item->hasAttribute('class-ref')) {
						$remove_xpath .= "[@class='" . $profile_alter_item->getAttribute('class-ref') . "']";
					}

					RemoveChildren($cat_new, $remove_xpath);
				}
			} else {
				// Do nothing. No remove items found.
			}			
						
			$control_add_commands = QueryListResult($profile, $control_alter_query . "/add");
			if ($control_add_commands !== false) {
				foreach($control_add_commands as $control_add_command) { // cycles through each tag under <add>
					
					// If an @id-ref is specified, it must reference an @id 
					//    that is a decendant to the control specified in the 
					//    alter statement.
					// To ensure this, we append this constraint to the query 
					//    that identifies the control itself, which will only
					//    find the @id among the descendants of the control.
					if ($control_add_command->hasAttribute('id-ref')) {
						$control_add_query = $control_query . "/*[@id='" . $control_add_command->getAttribute('id-ref') . "']";
					} else {
						$control_add_query = $control_query;
					}
					
					$control = QueryListResult($cat_new, $control_add_query);
					if ($control !== false) {
						if ($control_add_command->hasAttribute('position')) {
							$position_of_add = $control_add_command->getAttribute('position');
//							$messages .= "<br />-- " .  ("/modify/add");
						} else {
							$position_of_add = "after";
						}
						foreach($control_add_command->childNodes as $item_to_add) { // cycles through each tag under <add>
							if ($item_to_add->nodeName != '#text') { // Need to ignore 
//								$messages .= "<br />-- " .  ("ADDING TO CONTROL " . $control_id . ": " . $item_to_add->nodeName );
								$insert_ok = InsertOSCALdata($cat_new, $control_add_query, $item_to_add);
								if ( ! $insert_ok === true) {
									$messages .= "<br />-- " .  ("ERROR INSERTING CONTROL ADDITIONS AT: " . $control_add_query);
									$messages .= $insert_ok;
								}
							}
						}
					} else {
						ZoneOutputAppend("<br />CONTROL NOT FOUND IN RESOLVED CATALOG (2): " . $control_add_query , 'zone-two');
						$messages .= "<br />-- " .  ("CONTROL NOT FOUND IN RESOLVED CATALOG (2): " . $control_add_query);
					}
				}
			} else {
				// Do nothing. No add items found.
			}
		}
	}
	return $ret_val;
}

// ----------------------------------------------------------------------------
function ModifyParameters(&$cat_new, &$profile) {
	global $messages;
	$status = false;
	
	$query_parameter_set = "//modify/set";
	$profile_set_list = QueryListResult($profile, $query_parameter_set);
	foreach ($profile_set_list as $param_set) { // cycles through each <set> tag
		$param_id = $param_set->getAttribute('param-id');
		$parameter_query = "//param[@id='" . $param_id . "']";
		$parameter = QueryListResult($cat_new, $parameter_query);
		if ($parameter !== false) {
//			ZoneOutput("UPDATING PARAMETER: " . $param_id, 'zone-one');
			foreach($param_set->childNodes as $param_item) { // cycles through each tag under <set>
				if ($param_item->nodeName !== '#text') {
					$insert_ok = InsertOSCALdata($cat_new, $parameter_query, $param_item);
					if ( ! $insert_ok) {
						$messages .= "<br />-- " .  ("ERROR INSERTING PARAMETER DATA AT: " . $parameter_query);
					}
				}
			}
		} else {
			ZoneOutputAppend("<br />PARAMETER NOT FOUND IN RESOLVED PROFILE: " . $param_id, 'zone-two');
			$messages .= "<br />-- " .  ("PARAMETER NOT FOUND IN RESOLVED PROFILE: " . $param_id);
		}

	}
	$status = true;
	
	return $status;
}

// ----------------------------------------------------------------------------
// CURRENT: Imports controls with the following constraints
//    - For top level controls, the <as-is>, @with-child-controls is honored
//    - Will only import child controls if the parent control is already present
//            in the destination catalog. In other words, the call for the 
//            parent control, must appear before the call for the child control.
function ImportControl($include_item, &$cat_get, &$cat_new, $merge_combine, $merge_as_is) {
$valid = false;
$with_subcontrols = false;
$with_control = false;
$messages = "";
$continue = false;

	if ($include_item->hasAttribute('control-id')) {
		$valid = true;
		$type = "control-id";
		if ($include_item->hasAttribute('with-child-controls')) {
			$with_subcontrols = ($include_item->getAttribute('with-child-controls') == 'yes');
		} else {
			$with_subcontrols = false;
		}
		if ($include_item->hasAttribute('with-parent-control')) {
			$with_control = ($include_item->getAttribute('with-parent-control') == 'yes');
		} else {
			$with_control = false;
		}
	} else {
		$valid = false;
		$messages .= "<br />-- " .  "<br />WARNING: Control ID or Sub-Control ID not specified in call statement.";
	}

	if ($valid) {
		$id = $include_item->getAttribute($type);
		if ($id !== "") {
	//		$messages .= ("ID: " . $id);
			$cat_query = "//control[@id='". $id . "']";
			$cat_control_obj = QueryList($cat_get, $cat_query); // $cat_get('XPATH')->query($cat_query);
			if ($cat_control_obj !== false) {
	//			$messages .= "<br />-- " .  "<br />   FOUND: " . $id;
				
				if ($merge_as_is) {   // <merge><as-is> means to honor groups and sequencing
									// When sequence syntax is added, must update this section.
					
					// For the identified control in the source catalog,
					//    identify the ancestors all the way up to the top
					//    of the tree (//catalog).
/*					
					$cat_parent = $cat_control_obj->parentNode;
					$cat_ancestor_list = array();
					while ($cat_parent->nodeName !== "catalog") {
						$cat_parent_title_obj = $cat_parent->getElementsByTagName("title");
						if ($cat_parent_title_obj->length > 0) {
							
						}
						$cat_ancestor_list[] = array('nodeName' => $cat_parent->nodeName, 'id' => $cat_parent->getAttribute("id"), )
						$cat_parent = $cat_parent->parentNode;
					}
*/					
					// Starting at root, check for each existence of each 
					//    ancestor and add to the new file if necessary.
					if ($cat_control_obj->parentNode->nodeName == "group") {
						$messages .= ($id . " UNDER GROUP\n");
						$group_id = $cat_control_obj->parentNode->getAttribute("id");
/*
						if (strlen($group_id) > 0 ) {
							$cat_group_query = "//group[@id='". $group_id . "']";
							$cat_group = QueryList($cat_new, $cat_group_query);
						} else {
							$cat_group = $cat_control_obj->parentNode;
						}
*/
						$cat_group = $cat_control_obj->parentNode;
						if ($cat_group != false) {
//								$messages .= "<br />-- " .  "<br />FOUND GROUP IN DESTINATION (" . $group_id . ")";
							// group found in destination. Ready to add control.
							$cat_control = $cat_new['DOM']->importNode($cat_control_obj, true);
							$cat_group->appendChild($cat_control);
							$continue = true;
						} else {
							// group not found in destination. Create it first.
							
							// Importing group elements with attributes (typically @class and @id), but no child nodes
//								$messages .= "<br />-- " .  "<br />ADDING GROUP (" . $group_id . ") IN NEW CATALOG";
							$cat_group = $cat_new['DOM']->importNode($cat_control_obj->parentNode, false);
							$cat_new['DOM']->documentElement->appendChild($cat_group);
							
							// Getting Group Title, by finding the nodeValue of the group's title child node.
							$cat_group_new = QueryList($cat_new, $cat_group_query);

							$cat_group_title_query = "//group[@id='". $group_id . "']/title";
							$group_title = QueryOneItem($cat_get, $cat_group_title_query);
							$group_title_obj = $cat_new['DOM']->createElement('title', $group_title);
							$cat_group_new->appendChild($group_title_obj);

							$cat_control = $cat_new['DOM']->importNode($cat_control_obj, true);
							$cat_group->appendChild($cat_control);
							$continue = true;
						}
					} elseif ($cat_control_obj->parentNode->nodeName == "control") { // This is a nested control
						// Get the ID of the parent control (control->parentNode->getAttribute('id'))
						// Search $cat_new for existing control
						// If not found:
							// Check rest of profile to see if it is imported later (this should not happen)
							// if not @with-parent-control, place at root
						// else found
							// place under referenced control
						$messages .= ($id . " SUB CONTROL\n");
						$control_id = $cat_control_obj->parentNode->getAttribute("id"); // ID of parent
						if (strlen($control_id) > 0 ) {                                 // we should find 
							$cat_control_query = "//control[@id='". $control_id . "']";
							$cat_parent_control = QueryList($cat_new, $cat_control_query);  // look for parent in $cat_new
							if ($cat_parent_control != false) {
								$messages .= "-- " .  "FOUND CONTROL IN DESTINATION (" . $control_id . ")\n";
								// control found in destination. Ready to add subcontrol.
								$cat_control = $cat_new['DOM']->importNode($cat_control_obj, true);
								$cat_parent_control->appendChild($cat_control);
								$continue = true;
							} else {
								// control not found in destination. 
								$messages .= "!! " .  "<br />CONTROL (" . $control_id . ") NOT PRESENT IN NEW CATALOG FOR SUBCONTROL (" . $id . ").\n";
								ZoneOutputAppend("<br />ERROR: &lt;control id='" . $control_id . "'&gt; must be called in profile before calling subcontrol id='" . $id . "'");
							}							
						} else {
							$messages .= "<br />-- " .  "<br />ERROR: Source catalog nont compliant. The subcontrol (" . $id . ") must have a parent control.";
							ZoneOutputAppend("<br />ERROR: Source catalog nont compliant. The subcontrol (" . $id . ") must have a parent control. The subcontrol was not imported.");
						}
					} else {  // parent is either a group, nor a control. Insert under root at end.
						$cat_control = $cat_new['DOM']->importNode($cat_control_obj, true);
						$cat_new->documentElement->appendChild($cat_control);
						$messages .= ("AT ROOT: " . $id);
					}
				} else {   // NO <merge><as-is>, so just insert at end
					$cat_control = $cat_new['DOM']->importNode($cat_control_obj, true);
					$cat_new->documentElement->appendChild($cat_control);
				}
				
				if ($continue && ! $with_subcontrols) {  // Control without @with-subcontrols attribute, so need to remove subcontrols.
					$removexpath = "//control[@id='" . $cat_control->getAttribute('id') . "']/control";
					RemoveChildren($cat_new, $removexpath);
				}
			// if ($merge_combine == "")    // 'use-first', 'merge', 'keep'
			} else {
				$messages .= "<br />-- " .  "<br />   XPATH RETURNED NO RESULT for " . $cat_query;
			}
		} else {
			$messages .= "<br />-- " .  "<br />   WARNING: CALL STATEMENT IS MISSING " . $type;
		}
	}
	return $messages;
}

// ----------------------------------------------------------------------------
// if //merge/combine exists, look at @method
// Valid values: "use-first" (default), "merge", "keep"
function CheckMergeCombine(&$oscal, $project) {
	global $messages;
	$ret_val = "use-first";
	
	$combine_val = QueryList($oscal, "//merge/combine");
	if ($combine_val != false) {
		$ret_val = $combine_val->getAttribute("method");
		$messages .= "<br />-- " .  ("COMBINE: " . $ret_val);
	} else {
		$messages .= "<br />-- " .  ("COMBINE NOT FOUND. USING DEFAULT: " . $ret_val);
	}
	return $ret_val;
}

// ----------------------------------------------------------------------------
// if //merge/as-is exists, return true
// else, return false
function CheckMergeAs_Is(&$oscal, $project) {
	global $messages;
	$ret_val = false;
	
	$ret_val = QueryList($oscal, "//merge/as-is");
	if ($ret_val) {
		$messages .= "<br />-- " .  ("AS-IS: PRESENT");
	} else {
		$messages .= "<br />-- " .  ("AS-IS: NOT PRESENT");
	}
	return $ret_val;
	
}

// ----------------------------------------------------------------------------
// if //merge/as-is exists, return true
// else, return false
function CheckMergeCustom(&$oscal, $project) {
	global $messages;
	$ret_val = false;
	
	$ret_val = QueryList($oscal, "//merge/custom");
	if ($ret_val) {
		$messages .= "<br />-- " .  ("CUSTOM: PRESENT");
		ZoneOutputPrepend("WARNING: Custom merge is specified in the profile, but is not yet supported. (/&lt;merge&gt;/&lt;custom&gt;)<br />", "zone-one");
	} 
	return $ret_val;
	
}

/*
ORIGINAL PLAN: (Actual implementation varied slightly)
1. Open the profile
2. Check for merge/combine an merge/as-is
3. Create empty catalog
4. Cycle through top-level elements
	a. metadata
		1. copy to new catalog
		2. Add profile resolution date/time
	b. import [href]
		2. cycle through 
			a. href: find file, or ask for new pointer
			b. include:
				1. all: 
					a. take all groups and controls under catalog root [//group and //control] 
					b. copy each into new catalog
					c. check @with-subcontrols attribute and exclude subcontrols if explicitly "no" 
				2. call: 
					a. control:
						1. find the control
						2. Check the parent of the control in the profile for group.
					b. subcontrol:
						
			c. exclude:
				
	c. merge: process
		(combine and as-is addressed above)
		1. custom: filters the import universe
			a. create a second blank catalog
	d. modify: process
		1. set[param-id, class | depends-on]  (For parameters)
			- label
			- usage
			- constraint
			- value | select
			- link
			- part
		2. alter [control-id | subcontrol-id]
			b. remove (class-ref | id-ref | item-name)
			c. add [position="before | after | starting | ending"]
				- title
				- param
				- property_existslink
				- part

	e. back-matter: ignore for now
4. Finalize catalog:
	a. copy metadata element and children from profile
	b. add metadata to indicate time and date of profile resolution
	c. add metadata to indicate the tool used
	
CURRENT:
- Import from catalogs only (no importing from other profiles)
- //back-matter content does not get imported, even if linked from a called control
- All merge is as-is (as if //merge/as-is is present) whether specified or not
- All merge is keep-both for now regarless of //merge/combine value (or presence)

FUTURE:
- Import from profiles as well as catalogs
- //back-matter content gets imported when linked from a called control 
- //merge/as-is missing honored (currently all merge is as-is whether missing or not)
- //merge/combine properly honored

CLEANUP:
- Review any <link> elements that get imported, and import the corrisponding resource
	distinct-values(//link[@rel='reference']/@href)  
	NOTE: distinct-values is xpath 3.0. Might have to drop that and 
	      use code to de-dupe, such as array_unique($array, SORT_STRING);

*/

?>
