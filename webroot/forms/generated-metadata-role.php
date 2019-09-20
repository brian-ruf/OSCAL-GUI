
<?php
/*
GENERATED 2019-09-05 -- 19-16-15
*/

function metadata_role_form($oscal, $project_id, $form_only=false) {
	global $logging;
	$html = '';
	$metadata = QueryList($oscal, OSCAL_METADATA);
	$project_file = GetProjectDetail($project_id, 'file-with-path');
	
// === XPATH QUERIES ===
	$oscal_role_flags = array('id');  // All possible attributes this element might have set.
	$oscal_role = QueryListArray($oscal, '//role', $oscal_role_flags);

		$oscal_role_title_flags = array();  // No attributes for this element.
		$oscal_role_title = QueryListArray($oscal, '//role/title', $oscal_role_title_flags);

		$oscal_role_short_name_flags = array();  // No attributes for this element.
		$oscal_role_short_name = QueryListArray($oscal, '//role/short-name', $oscal_role_short_name_flags);

		$oscal_role_desc_flags = array();  // No attributes for this element.
		$oscal_role_desc = QueryListArray($oscal, '//role/desc', $oscal_role_desc_flags);

	$html .= "<form id='form-metadata-role' method='post' action='./oscal-forms.php?mode=save&form=metadata-role&project={$project_id}'>";
		// MetaMap2Form Generated Content - START (//role)
		$oscal_role__cntr = 0;
		$oscal_role__cntr_max = count($oscal_role);
		foreach ($oscal_role as $oscal_role_item)  { 
			$oscal_role__cntr += 1;
			$html .= "<table class='form' style='width=100%;'>";
				$html .= <<<HTML

				<!-- Form Row - START (role) -->
				<tr>
					<td colspan='2' style='width: auto; text-align: left; vertical-align: top;'>
						<span title='FIELD: role -- DATATYPE: none' style='font-weight: bold;'>Role</span>
HTML;

					$html .= "<br /><span title='FLAG: id -- TYPE: ID'>Role: Identifier</span>&nbsp;";
						$html .= "
							<input  id='oscal_role_item__" . $oscal_role__cntr . "__id' name='role[$oscal_role__cntr][\"id\"]' type='text' style='width: 300px;' class='attribute' value='{$oscal_role_item['flags']['id']}' placeholder='** EMPTY **' title='Unique identifier of the containing object' />";

					$html .= "<table class='form' style='width=100%;'>";
					
							// MetaMap2Form Generated Content - START (//role/title)
							$oscal_role_title__cntr = 0;
							$oscal_role_title__cntr_max = count($oscal_role_title);
							foreach ($oscal_role_title as $oscal_role_title_item)  { 
								$oscal_role_title__cntr += 1;
									$html .= <<<HTML

									<!-- Form Row - START (title) -->
									<tr>
										<td style='width: auto; text-align: right; vertical-align: top;'>
											<span title='FIELD: title -- DATATYPE: mixed' style=''>Title</span>
										</td>
HTML;

										$html .= "<td style='width: auto; text-align: left; vertical-align: top;'>";
											$html .= "
											
												<div title='A title for display and navigation' >
													<input  id='mixed-oscal_role_title_item__" . $oscal_role_title__cntr . "' name='role/title[$oscal_role_title__cntr][0]' class='mixed_editing' style='width: 500px; height: 15px;' title='A title for display and navigation' placeholder='** EMPTY **' type='text' value='{$oscal_role_title_item["value"]}' />
													<script>
														oscal_oscal_role_title_item__" . $oscal_role_title__cntr . " = RichText('mixed', '#mixed-oscal_role_title_item__" . $oscal_role_title__cntr . "');
													</script>
												</div>";

										$html .= GenerateToolsMenu(false, $oscal_role_title__cntr, $oscal_role_title__cntr_max, ($oscal_role_title_item['value'] === null )) ;
										$html .= "</td>";
										$html .= <<<HTML
									</tr>
									<!-- Form Row - END (title) -->
HTML;

							} 
							// MetaMap2Form Generated Content - END
							// MetaMap2Form Generated Content - START (//role/short-name)
							$oscal_role_short_name__cntr = 0;
							$oscal_role_short_name__cntr_max = count($oscal_role_short_name);
							foreach ($oscal_role_short_name as $oscal_role_short_name_item)  { 
								$oscal_role_short_name__cntr += 1;
									$html .= <<<HTML

									<!-- Form Row - START (short-name) -->
									<tr>
										<td style='width: auto; text-align: right; vertical-align: top;'>
											<span title='FIELD: short-name -- DATATYPE: string' style=''>Short-name</span>
										</td>
HTML;

										$html .= "<td style='width: auto; text-align: left; vertical-align: top;'>";
											$html .= "
												<input  id='oscal_role_short_name_item__" . $oscal_role_short_name__cntr . "' name='role/short-name[$oscal_role_short_name__cntr][0]' type='text' style='width: 500px;' value='{$oscal_role_short_name_item["value"]}' placeholder='** EMPTY **' title='A common name, short name or acronym' />";

										$html .= GenerateToolsMenu(false, $oscal_role_short_name__cntr, $oscal_role_short_name__cntr_max, ($oscal_role_short_name_item['value'] === null )) ;
										$html .= "</td>";
										$html .= <<<HTML
									</tr>
									<!-- Form Row - END (short-name) -->
HTML;

							} 
							// MetaMap2Form Generated Content - END
							// MetaMap2Form Generated Content - START (//role/desc)
							$oscal_role_desc__cntr = 0;
							$oscal_role_desc__cntr_max = count($oscal_role_desc);
							foreach ($oscal_role_desc as $oscal_role_desc_item)  { 
								$oscal_role_desc__cntr += 1;
									$html .= <<<HTML

									<!-- Form Row - START (desc) -->
									<tr>
										<td style='width: auto; text-align: right; vertical-align: top;'>
											<span title='FIELD: desc -- DATATYPE: string' style=''>Description</span>
										</td>
HTML;

										$html .= "<td style='width: auto; text-align: left; vertical-align: top;'>";
											$html .= "
												<input  id='oscal_role_desc_item__" . $oscal_role_desc__cntr . "' name='role/desc[$oscal_role_desc__cntr][0]' type='text' style='width: 500px;' value='{$oscal_role_desc_item["value"]}' placeholder='** EMPTY **' title='A short textual description' />";

										$html .= GenerateToolsMenu(false, $oscal_role_desc__cntr, $oscal_role_desc__cntr_max, ($oscal_role_desc_item['value'] === null )) ;
										$html .= "</td>";
										$html .= <<<HTML
									</tr>
									<!-- Form Row - END (desc) -->
HTML;

							} 
							// MetaMap2Form Generated Content - END
					$html .= "</table>";
					$html .= "<br />";
					$html .= GenerateToolsMenu(true, $oscal_role__cntr, $oscal_role__cntr_max, ($oscal_role_item['value'] === null )) ;
					$html .= "</td>";
					$html .= <<<HTML
				</tr>
				<!-- Form Row - END (role) -->
HTML;

			$html .= "</table>";
		} 
		// MetaMap2Form Generated Content - END
	$buttons = array(
		["text" => "Go Back", "img" => "./img/arrow-left.png", "action" => "goBack('./oscal.php?mode=open&project={$project_id}')"],
		["text" => "Commit Changes", "img" => "./img/edit.png", "action" => "submitForm('form-metadata-role')"]
	);
$html .= MakeMenu($buttons, false, true);
$html .= "</form>
";
	return $html;
}

/*
BASED ON THE FOLLOWING STRUCTURE:
{
    "name": "role",
    "path": "\/\/catalog\/metadata\/role",
    "position": 7,
    "required": false,
    "multiple": true,
    "holds_data": false,
    "datatype": "none",
    "flags": [
        {
            "name": "id",
            "required": true,
            "datatype": "ID",
            "formal-name": "Identifier",
            "description": "Unique identifier of the containing object"
        }
    ],
    "content": null,
    "formal-name": "Role",
    "description": "Defining a role to be assigned to a party or agent",
    "model": {
        "title": {
            "name": "title",
            "path": "\/\/catalog\/metadata\/role\/title",
            "position": 0,
            "required": false,
            "multiple": false,
            "holds_data": true,
            "datatype": "mixed",
            "flags": [],
            "content": null,
            "formal-name": "Title",
            "description": "A title for display and navigation"
        },
        "short-name": {
            "name": "short-name",
            "path": "\/\/catalog\/metadata\/role\/short-name",
            "position": 1,
            "required": false,
            "multiple": false,
            "holds_data": true,
            "datatype": "string",
            "flags": [],
            "content": null,
            "formal-name": "short-name",
            "description": "A common name, short name or acronym"
        },
        "desc": {
            "name": "desc",
            "path": "\/\/catalog\/metadata\/role\/desc",
            "position": 2,
            "required": false,
            "multiple": false,
            "holds_data": true,
            "datatype": "string",
            "flags": [],
            "content": null,
            "formal-name": "Description",
            "description": "A short textual description"
        }
    }
}
*/

?>

