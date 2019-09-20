// ============================================================================
	function toggle_visibility(id) {
	   var e = document.getElementById(id);
	   if(e.style.display == 'none')
		  e.style.display = 'block';
	   else
		  e.style.display = 'none';
	}

// ----------------------------------------------------------------------------
	function updateField(fieldobject) {
		alert(fieldobject.name);
	}

// ----------------------------------------------------------------------------
	function ParentRedirect(url) {
		alert("Redirect Attempt: " + url);
		window.parent.postMessage("data: {'url': url}");
	}

// ============================================================================
	// Adapted from: https://stackoverflow.com/questions/8862006/swapping-rows-using-a-javascript
	function moveRowUp(obj) {
		var row = obj.parentNode.parentNode,
			sibling = row.previousElementSibling,
			parent = row.parentNode;
		parent.insertBefore(row, sibling);
	}		

	function moveRowDn(obj) {
		var row = obj.parentNode.parentNode,
			sibling = row.nextElementSibling,
			parent = row.parentNode;
		parent.insertBefore(sibling, row);
	}		


// ============================================================================
	function moveUp(fieldobject) {
		alert("moveUp: " + fieldobject.name);
	}

	function moveDown(fieldobject) {
		alert("moveDown: " + fieldobject.name);
	}

	function remove(fieldobject) {
		alert("Delete: " + fieldobject.name);
	}

	function addBelow(fieldobject) {
		alert("Add New Item Below: " + fieldobject.name);
	}

// ----------------------------------------------------------------------------
// Not working at the moment
	function clearField(fieldobject) {
		alert(fieldobject.id)
		fieldobject.value = "" // alert("value changed");
		fieldobject.reset()
	}

// ----------------------------------------------------------------------------
	function clearForm(formName) {
		if (confirm("EDITS WILL BE LOST!\nClick OK to lose your changes and revert to the loaded form data.\nPress cancel to keep your edits.\n(To save your edits, click Cancel here, then click Commit Changes.)")) {
			document.forms[formName].reset();
		}
	}

// ----------------------------------------------------------------------------
	function submitForm(formName) {
		if (confirm("This will overwrite the file with your current edits.\nClick OK to save your changes.\nPress cancel to abort the overwrite.\n(Your draft edits will remain on the screen.)")) {
			document.forms[formName].submit();
		}
	}

function showElements(oForm) {
  str = "Form Elements of form " + oForm.name + ": \n"
  for (i = 0; i < oForm.length; i++) 
      str += oForm.elements[i].name + "\n"
  alert(str)
}

// ----------------------------------------------------------------------------
	function goBack(url) {
		if (confirm("EDITS WILL BE LOST!\nClick OK to lose your changes and return to the menu.\nPress cancel to stay here.\n(To save your edits, click Cancel here, then click Commit Changes.)")) {
			window.open(url, '_self');
		}
	}

// ============================================================================

	function ZoneAppend(content, zone){
		parent.document.getElementById(zone).innerHTML = parent.document.getElementById(zone).innerHTML + content; 
	}

	function ZonePrepend(content, zone){
		parent.document.getElementById(zone).innerHTML = content + parent.document.getElementById(zone).innerHTML; 
	}

	function ZoneReplace(content, zone){
		parent.document.getElementById(zone).innerHTML = content; 
	}

// ----------------------------------------------------------------------------
// Thes buttons and/ or plugins should be configured to match the formatting 
//      described here as closely as possible:
//	https://pages.nist.gov/OSCAL/docs/schemas/datatypes/#markup-line
//  TODO: Need to check on underline
	function RichText(field_type, rich_id) {

		if(field_type == 'mixed') {
			var rich_field = new Jodit(rich_id  , {
			    'iframe': true,
				'toolbar': false,
				'height': 50,
				'minHeight': 35,
				'maxWidth': 600,
				'enter': '',
				'addNewLine': false,
				'resizer': false,
				'toolbarAdaptive': false,
				'showCharsCounter': false,
				'showWordsCounter': false,
				'showXPathInStatusbar': false,
				'disablePlugins': 'iframe,fullsize,enter,color,font,indent,imageProcessor,imageProperties,justify,link,media,resizer,table,tableKeyboardNavigation,search,DragAndDropElement,DragAndDrop',
				'buttons': '|,bold,italic,superscript,subscript,,fontsize,cut,eraser,copyformat,|,symbol,source'
			});
		} else {
			var rich_field = new Jodit(rich_id  , {
			  'iframe': true,
			  'height': 140,
			  'minHeight': 140,
			  'maxWidth': 600,
			  'toolbarAdaptive': false,
			  'defaultActionOnPaste': 'insert_clear_html',
			  'disablePlugins': 'color,font',
			  'buttons': '|,bold,italic,superscript,subscript,|,ul,ol,table,|,paragraph,,hr,image,link,|,undo,redo,cut,eraser,copyformat,symbol,|,source,fullsize,selectall,print,about'
			});
		}

		return rich_field;
	}

	
// ----------------------------------------------------------------------------
	function doNothing() {
	}

	