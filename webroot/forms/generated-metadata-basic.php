
<?php
/*
GENERATED 2019-09-05 -- 19-16-15
*/

function metadata_basic_form($oscal, $project_id, $form_only=false) {
	global $logging;
	$html = '';
	$metadata = QueryList($oscal, OSCAL_METADATA);
	$project_file = GetProjectDetail($project_id, 'file-with-path');
	
// === XPATH QUERIES ===
	$oscal_metadata_flags = array();  // No attributes for this element.
	$oscal_metadata = QueryListArray($oscal, '//metadata', $oscal_metadata_flags);

		$oscal_metadata_title_flags = array();  // No attributes for this element.
		$oscal_metadata_title = QueryListArray($oscal, '//metadata/title', $oscal_metadata_title_flags);

		$oscal_metadata_last_modified_date_flags = array();  // No attributes for this element.
		$oscal_metadata_last_modified_date = QueryListArray($oscal, '//metadata/last-modified-date', $oscal_metadata_last_modified_date_flags);

		$oscal_metadata_version_flags = array();  // No attributes for this element.
		$oscal_metadata_version = QueryListArray($oscal, '//metadata/version', $oscal_metadata_version_flags);

		$oscal_metadata_oscal_version_flags = array();  // No attributes for this element.
		$oscal_metadata_oscal_version = QueryListArray($oscal, '//metadata/oscal-version', $oscal_metadata_oscal_version_flags);

		$oscal_metadata_doc_id_flags = array('type');  // All possible attributes this element might have set.
		$oscal_metadata_doc_id = QueryListArray($oscal, '//metadata/doc-id', $oscal_metadata_doc_id_flags);

		$oscal_metadata_notes_flags = array('type');  // All possible attributes this element might have set.
		$oscal_metadata_notes = QueryListArray($oscal, '//metadata/notes', $oscal_metadata_notes_flags);

	$html .= "<form id='form-metadata-basic' method='post' action='./oscal-forms.php?mode=save&form=metadata-basic&project={$project_id}'>";
		// MetaMap2Form Generated Content - START (//metadata)
		$oscal_metadata__cntr = 0;
		$oscal_metadata__cntr_max = count($oscal_metadata);
		foreach ($oscal_metadata as $oscal_metadata_item)  { 
			$oscal_metadata__cntr += 1;
			$html .= "<table class='form' style='width=100%;'>";
				$html .= <<<HTML

				<!-- Form Row - START (metadata) -->
				<tr>
					<td colspan='2' style='width: auto; text-align: left; vertical-align: top;'>
						<span title='FIELD: metadata -- DATATYPE: none' style='font-weight: bold;'>Publication Metadata</span><span style='color:red;'>*</span>

HTML;

					$html .= "<table class='form' style='width=100%;'>";
					
							// MetaMap2Form Generated Content - START (//metadata/title)
							$oscal_metadata_title__cntr = 0;
							$oscal_metadata_title__cntr_max = count($oscal_metadata_title);
							foreach ($oscal_metadata_title as $oscal_metadata_title_item)  { 
								$oscal_metadata_title__cntr += 1;
									$html .= <<<HTML

									<!-- Form Row - START (title) -->
									<tr>
										<td style='width: auto; text-align: right; vertical-align: top;'>
											<span title='FIELD: title -- DATATYPE: mixed' style=''>Title</span><span style='color:red;'>*</span>

										</td>
HTML;

										$html .= "<td style='width: auto; text-align: left; vertical-align: top;'>";
											$html .= "
											
												<div title='A title for display and navigation' >
													<input  id='mixed-oscal_metadata_title_item__" . $oscal_metadata_title__cntr . "' name='metadata/title[$oscal_metadata_title__cntr][0]' class='mixed_editing' style='width: 500px; height: 15px;' title='A title for display and navigation' placeholder='** EMPTY **' type='text' value='{$oscal_metadata_title_item["value"]}' />
													<script>
														oscal_oscal_metadata_title_item__" . $oscal_metadata_title__cntr . " = RichText('mixed', '#mixed-oscal_metadata_title_item__" . $oscal_metadata_title__cntr . "');
													</script>
												</div>";

										$html .= GenerateToolsMenu(false, $oscal_metadata_title__cntr, $oscal_metadata_title__cntr_max, ($oscal_metadata_title_item['value'] === null )) ;
										$html .= "</td>";
										$html .= <<<HTML
									</tr>
									<!-- Form Row - END (title) -->
HTML;

							} 
							// MetaMap2Form Generated Content - END
							// MetaMap2Form Generated Content - START (//metadata/last-modified-date)
							$oscal_metadata_last_modified_date__cntr = 0;
							$oscal_metadata_last_modified_date__cntr_max = count($oscal_metadata_last_modified_date);
							foreach ($oscal_metadata_last_modified_date as $oscal_metadata_last_modified_date_item)  { 
								$oscal_metadata_last_modified_date__cntr += 1;
									$html .= <<<HTML

									<!-- Form Row - START (last-modified-date) -->
									<tr>
										<td style='width: auto; text-align: right; vertical-align: top;'>
											<span title='FIELD: last-modified-date -- DATATYPE: dateTime-with-timezone' style=''>Last Modified Date</span>
										</td>
HTML;

										$html .= "<td style='width: auto; text-align: left; vertical-align: top;'>";
											$html .= "
												<input  id='oscal_metadata_last_modified_date_item__" . $oscal_metadata_last_modified_date__cntr . "' name='metadata/last-modified-date[$oscal_metadata_last_modified_date__cntr][0]' type='text' style='width: 500px;' value='{$oscal_metadata_last_modified_date_item["value"]}' placeholder='** EMPTY **' title='Date of last modification.' />";

										$html .= GenerateToolsMenu(false, $oscal_metadata_last_modified_date__cntr, $oscal_metadata_last_modified_date__cntr_max, ($oscal_metadata_last_modified_date_item['value'] === null )) ;
										$html .= "</td>";
										$html .= <<<HTML
									</tr>
									<!-- Form Row - END (last-modified-date) -->
HTML;

							} 
							// MetaMap2Form Generated Content - END
							// MetaMap2Form Generated Content - START (//metadata/version)
							$oscal_metadata_version__cntr = 0;
							$oscal_metadata_version__cntr_max = count($oscal_metadata_version);
							foreach ($oscal_metadata_version as $oscal_metadata_version_item)  { 
								$oscal_metadata_version__cntr += 1;
									$html .= <<<HTML

									<!-- Form Row - START (version) -->
									<tr>
										<td style='width: auto; text-align: right; vertical-align: top;'>
											<span title='FIELD: version -- DATATYPE: string' style=''>Document Version</span>
										</td>
HTML;

										$html .= "<td style='width: auto; text-align: left; vertical-align: top;'>";
											$html .= "
												<input  id='oscal_metadata_version_item__" . $oscal_metadata_version__cntr . "' name='metadata/version[$oscal_metadata_version__cntr][0]' type='text' style='width: 500px;' value='{$oscal_metadata_version_item["value"]}' placeholder='** EMPTY **' title='The version of the document content.' />";

										$html .= GenerateToolsMenu(false, $oscal_metadata_version__cntr, $oscal_metadata_version__cntr_max, ($oscal_metadata_version_item['value'] === null )) ;
										$html .= "</td>";
										$html .= <<<HTML
									</tr>
									<!-- Form Row - END (version) -->
HTML;

							} 
							// MetaMap2Form Generated Content - END
							// MetaMap2Form Generated Content - START (//metadata/oscal-version)
							$oscal_metadata_oscal_version__cntr = 0;
							$oscal_metadata_oscal_version__cntr_max = count($oscal_metadata_oscal_version);
							foreach ($oscal_metadata_oscal_version as $oscal_metadata_oscal_version_item)  { 
								$oscal_metadata_oscal_version__cntr += 1;
									$html .= <<<HTML

									<!-- Form Row - START (oscal-version) -->
									<tr>
										<td style='width: auto; text-align: right; vertical-align: top;'>
											<span title='FIELD: oscal-version -- DATATYPE: string' style=''>OSCAL Version</span><span style='color:red;'>*</span>

										</td>
HTML;

										$html .= "<td style='width: auto; text-align: left; vertical-align: top;'>";
											$html .= "
												<input  id='oscal_metadata_oscal_version_item__" . $oscal_metadata_oscal_version__cntr . "' name='metadata/oscal-version[$oscal_metadata_oscal_version__cntr][0]' type='text' style='width: 500px;' value='{$oscal_metadata_oscal_version_item["value"]}' placeholder='** EMPTY **' title='OSCAL model version.' />";

										$html .= GenerateToolsMenu(false, $oscal_metadata_oscal_version__cntr, $oscal_metadata_oscal_version__cntr_max, ($oscal_metadata_oscal_version_item['value'] === null )) ;
										$html .= "</td>";
										$html .= <<<HTML
									</tr>
									<!-- Form Row - END (oscal-version) -->
HTML;

							} 
							// MetaMap2Form Generated Content - END
							// MetaMap2Form Generated Content - START (//metadata/doc-id)
							$oscal_metadata_doc_id__cntr = 0;
							$oscal_metadata_doc_id__cntr_max = count($oscal_metadata_doc_id);
							foreach ($oscal_metadata_doc_id as $oscal_metadata_doc_id_item)  { 
								$oscal_metadata_doc_id__cntr += 1;
									$html .= <<<HTML

									<!-- Form Row - START (doc-id) -->
									<tr>
										<td style='width: auto; text-align: right; vertical-align: top;'>
											<span title='FIELD: doc-id -- DATATYPE: string' style=''>Document Identifier</span>
										</td>
HTML;

										$html .= "<td style='width: auto; text-align: left; vertical-align: top;'>";
											$html .= "
												<input  id='oscal_metadata_doc_id_item__" . $oscal_metadata_doc_id__cntr . "' name='metadata/doc-id[$oscal_metadata_doc_id__cntr][0]' type='text' style='width: 500px;' value='{$oscal_metadata_doc_id_item["value"]}' placeholder='** EMPTY **' title='A document identifier qualified by an identifier type.' />";

										$html .= "<br /><span title='FLAG: type -- TYPE: string'>Document Identifier: Type</span>&nbsp;";
											$html .= "
												<input  id='oscal_metadata_doc_id_item__" . $oscal_metadata_doc_id__cntr . "__type' name='metadata/doc-id[$oscal_metadata_doc_id__cntr][\"type\"]' type='text' style='width: 300px;' class='attribute' value='{$oscal_metadata_doc_id_item['flags']['type']}' placeholder='** EMPTY **' title='Indicating the type of identifier, address, email or other data item.' />";

										$html .= GenerateToolsMenu(true, $oscal_metadata_doc_id__cntr, $oscal_metadata_doc_id__cntr_max, ($oscal_metadata_doc_id_item['value'] === null )) ;
										$html .= "</td>";
										$html .= <<<HTML
									</tr>
									<!-- Form Row - END (doc-id) -->
HTML;

							} 
							// MetaMap2Form Generated Content - END
							// MetaMap2Form Generated Content - START (//metadata/notes)
							$oscal_metadata_notes__cntr = 0;
							$oscal_metadata_notes__cntr_max = count($oscal_metadata_notes);
							foreach ($oscal_metadata_notes as $oscal_metadata_notes_item)  { 
								$oscal_metadata_notes__cntr += 1;
									$html .= <<<HTML

									<!-- Form Row - START (notes) -->
									<tr>
										<td style='width: auto; text-align: right; vertical-align: top;'>
											<span title='FIELD: notes -- DATATYPE: prose' style=''>Notes</span>
										</td>
HTML;

										$html .= "<td style='width: auto; text-align: left; vertical-align: top;'>";
											$html .= "
											
												<div  title='Any notes with further information' >
													<textarea  id='textarea-oscal_metadata_notes_item__" . $oscal_metadata_notes__cntr . "' name='metadata/notes[$oscal_metadata_notes__cntr][0]' class='prose_editing' style='width: 500px; height: 50px;' title='Any notes with further information' placeholder='** EMPTY **'>{$oscal_metadata_notes_item["value"]}</textarea>
													<script>
														oscal_oscal_metadata_notes_item__" . $oscal_metadata_notes__cntr . " = RichText('prose', '#textarea-oscal_metadata_notes_item__" . $oscal_metadata_notes__cntr . "');
													</script>
												</div>";

										$html .= "<br /><span title='FLAG: type -- TYPE: string'>Notes: Type</span>&nbsp;";
											$html .= "
												<input  id='oscal_metadata_notes_item__" . $oscal_metadata_notes__cntr . "__type' name='metadata/notes[$oscal_metadata_notes__cntr][\"type\"]' type='text' style='width: 300px;' class='attribute' value='{$oscal_metadata_notes_item['flags']['type']}' placeholder='** EMPTY **' title='Indicating the type of identifier, address, email or other data item.' />";

										$html .= GenerateToolsMenu(false, $oscal_metadata_notes__cntr, $oscal_metadata_notes__cntr_max, ($oscal_metadata_notes_item['value'] === null )) ;
										$html .= "</td>";
										$html .= <<<HTML
									</tr>
									<!-- Form Row - END (notes) -->
HTML;

							} 
							// MetaMap2Form Generated Content - END
					$html .= "</table>";
					$html .= "<br />";
					$html .= GenerateToolsMenu(false, $oscal_metadata__cntr, $oscal_metadata__cntr_max, ($oscal_metadata_item['value'] === null )) ;
					$html .= "</td>";
					$html .= <<<HTML
				</tr>
				<!-- Form Row - END (metadata) -->
HTML;

			$html .= "</table>";
		} 
		// MetaMap2Form Generated Content - END
	$buttons = array(
		["text" => "Go Back", "img" => "./img/arrow-left.png", "action" => "goBack('./oscal.php?mode=open&project={$project_id}')"],
		["text" => "Commit Changes", "img" => "./img/edit.png", "action" => "submitForm('form-metadata-basic')"]
	);
$html .= MakeMenu($buttons, false, true);
$html .= "</form>
";
	return $html;
}

/*
BASED ON THE FOLLOWING STRUCTURE:
{
    "name": "metadata",
    "path": "\/\/catalog\/metadata",
    "position": 0,
    "required": true,
    "multiple": false,
    "holds_data": false,
    "datatype": "none",
    "flags": [],
    "content": null,
    "formal-name": "Publication metadata",
    "description": "Provides information about the publication and availability
of the containing document.",
    "model": {
        "title": {
            "name": "title",
            "path": "\/\/catalog\/metadata\/title",
            "position": 0,
            "required": true,
            "multiple": false,
            "holds_data": true,
            "datatype": "mixed",
            "flags": [],
            "content": null,
            "formal-name": "Title",
            "description": "A title for display and navigation"
        },
        "last-modified-date": {
            "name": "last-modified-date",
            "path": "\/\/catalog\/metadata\/last-modified-date",
            "position": 1,
            "required": false,
            "multiple": false,
            "holds_data": true,
            "datatype": "dateTime-with-timezone",
            "flags": [],
            "content": null,
            "formal-name": "Last modified date",
            "description": "Date of last modification."
        },
        "version": {
            "name": "version",
            "path": "\/\/catalog\/metadata\/version",
            "position": 2,
            "required": false,
            "multiple": false,
            "holds_data": true,
            "datatype": "string",
            "flags": [],
            "content": null,
            "formal-name": "Document version",
            "description": "The version of the document content."
        },
        "oscal-version": {
            "name": "oscal-version",
            "path": "\/\/catalog\/metadata\/oscal-version",
            "position": 3,
            "required": true,
            "multiple": false,
            "holds_data": true,
            "datatype": "string",
            "flags": [],
            "content": null,
            "formal-name": "OSCAL version",
            "description": "OSCAL model version."
        },
        "doc-id": {
            "name": "doc-id",
            "path": "\/\/catalog\/metadata\/doc-id",
            "position": 4,
            "required": false,
            "multiple": true,
            "holds_data": true,
            "datatype": "string",
            "flags": [
                {
                    "name": "type",
                    "required": true,
                    "datatype": "string",
                    "formal-name": "Type",
                    "description": "Indicating the type of identifier, address,
email or other data item."
                }
            ],
            "content": null,
            "formal-name": "Document Identifier",
            "description": "A document identifier qualified by an identifier
type."
        },
        "notes": {
            "name": "notes",
            "path": "\/\/catalog\/metadata\/notes",
            "position": 9,
            "required": false,
            "multiple": false,
            "holds_data": true,
            "datatype": "prose",
            "flags": [
                {
                    "name": "type",
                    "required": false,
                    "datatype": "string",
                    "formal-name": "Type",
                    "description": "Indicating the type of identifier, address,
email or other data item."
                }
            ],
            "content": null,
            "formal-name": "Notes",
            "description": "Any notes with further information"
        }
    }
}
*/

?>

