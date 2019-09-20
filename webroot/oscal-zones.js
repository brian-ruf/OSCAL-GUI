// ============================================================================
// Runs client-size. Kicks-off the server-side (backgroun) script, identified
// by the url parameter, then listens for server-sent events from that script.
//
// All server-sent events are generated sent using the functions in
// oscal-zones.php, which is a required incude file within the server-side script.
// ============================================================================
	function zoneManagement(url) {

		var source = new EventSource(url); // . $parameterspassalong; ?>');

		source.onmessage = function(event){

			var statusdata;
			var i;
			var headerHeight;
			var iFrame;
			var prose_editing;
			var textareas;
			var how_many;
			var temp_var;
			
			statusdata = JSON.parse(event.data);
			
			switch(statusdata.code) {
			  case "end": // The server-side script must call this when it is done, or the browser will continuously refresh the page
				source.close(); 
				break;
			  case "content-replace":
				document.getElementById(statusdata.zone).innerHTML = statusdata.content; 
				break;
			  case "content-prepend":
				document.getElementById(statusdata.zone).innerHTML = statusdata.content + document.getElementById(statusdata.zone).innerHTML; 
				break;
			  case "content-append":
				document.getElementById(statusdata.zone).innerHTML = document.getElementById(statusdata.zone).innerHTML + statusdata.content; 
				break;
			  case "adjust":
				document.getElementById(statusdata.zone).style = statusdata.adjust;
				break;
			  case "url":
				window.open(statusdata.url, '_self');
				break;
			  case "command":
				switch(statusdata.command) {
				  case "refreshiFrame":
					// resizeIFrameToFitContent( 'form-iframe' );
					iFrame = document.getElementById( 'form-iframe' );
					iFrame.height = window.innerHeight 
									- document.getElementById('header-area').offsetHeight 
									- document.getElementById('footer-area').offsetHeight;
					break;
				  case "working_notice":
					splashScreen();
					break;
				  case "working_notice_stop":
					splashScreenstop();
					break;
				  case "working_notice_end":
					div = document.getElementById('zone-two');
					div.style.background = "";
					break;
				  case "refreshTextAreas":
						prose_editing = [null];
						textareas = document.getElementsByClassName("mixed_editing");
						how_many = textareas.length;

						for (i = 0; i < how_many; i+=1) { 

							temp_var = new Jodit('#mixed-' + textareas[i].name  , {
							  'toolbar': false,
							  'height': 35,
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
							  'buttons': '|,bold,italic,underline,superscript,subscript,,fontsize,cut,eraser,copyformat,|,symbol,source'
							});
							prose_editing.push(temp_var);
						} 

						textareas = document.getElementsByClassName("prose_editing");
						how_many = textareas.length;

						for (i = 0; i < how_many; i+=1) { 
							temp_var = new Jodit('#textarea-' + textareas[i].name  , {
							  'height': 140,
							  'minHeight': 140,
							  'maxWidth': 600,
							  'toolbarAdaptive': false,
							  'defaultActionOnPaste': 'insert_clear_html',
							  'buttons': '|,bold,italic,underline,superscript,subscript,|,ul,ol,table,|,paragraph,,hr,image,link,|,undo,redo,\\n,cut,eraser,copyformat,symbol,|,source,fullsize,selectall,print,about'
							});
							prose_editing.push(temp_var);
						} 
					break;
				  default:
					// code block
				}
				break;
			  default:
				// code block
			}
			
	/*
			// command message: "refreshTextAreas" - Enables Jodit for each
			// textarea with "prose_editing" in its class attribute and
			// each input with "mixed_editing" in its class attribute. 
			// Jodit allows GUI-based formatting of (prose) and (mixed) data,
			// rather than burdening the user with HTML tags.
			// This is usually called from the server-side script after it
			// sends content that includes form fields.
			// NOTE: These calls limit the formatting functions available 
			// based on the OSCAL specification for what is allowed in 
			// prose or mixed datatypes.
			if ( statusdata.command == "refreshTextAreas") {
				prose_editing = [null];
				textareas = document.getElementsByClassName("mixed_editing");
				how_many = textareas.length;

				for (i = 0; i < how_many; i+=1) { 

					temp_var = new Jodit('#mixed-' + textareas[i].name  , {
					  'toolbar': false,
					  'height': 35,
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
					  'buttons': '|,bold,italic,underline,superscript,subscript,,fontsize,cut,eraser,copyformat,|,symbol,source'
					});
					prose_editing.push(temp_var);
				} 

				textareas = document.getElementsByClassName("prose_editing");
				how_many = textareas.length;

				for (i = 0; i < how_many; i+=1) { 
					temp_var = new Jodit('#textarea-' + textareas[i].name  , {
					  'height': 140,
					  'minHeight': 140,
					  'maxWidth': 600,
					  'toolbarAdaptive': false,
					  'defaultActionOnPaste': 'insert_clear_html',
					  'buttons': '|,bold,italic,underline,superscript,subscript,|,ul,ol,table,|,paragraph,,hr,image,link,|,undo,redo,\\n,cut,eraser,copyformat,symbol,|,source,fullsize,selectall,print,about'
					});
					prose_editing.push(temp_var);
				} 
			} 
		*/

		/*
			// command message: "refreshiFrame" attempts to resize height of the 
			// iFrame sometimes appearing in zone-two, to the visible height within
			// the window.
			// NOTE: iFrame resizing may not be working correctly at this time.
			if ( statusdata.command == "refreshiFrame") {
//				resizeIFrameToFitContent( 'form-iframe' );
				iFrame = document.getElementById( 'form-iframe' );
				iFrame.height = window.innerHeight 
								- document.getElementById('header-area').offsetHeight 
								- document.getElementById('footer-area').offsetHeight;

			}
		*/
			

			// ================================================================
			// The commands below are run every pass through the loop, even 
			// if no server-sent messages are received. 

			// The fixed header area can grow and shrink depending on window 
			// width and file title length.
			// This ensures the body content always starts just beneath the 
			// header area, which prevents body content from being hidden 
			// behind the header. 
//			headerHeight = document.getElementById('header-area').offsetHeight;
//			headerHeight = headerHeight + 5;
//			document.getElementById('header-padding').style = 'height: ' + headerHeight + 'px;';

		};
	}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================
	function toggle_visibility(id) {
	   var e = document.getElementById(id);
	   if(e.style.display == 'none') {
		  e.style.display = 'block';
	   } else {
		  e.style.display = 'none';
	   }
	}

// ============================================================================
// Test code. Expand or remove.
	function UpdateField(id) {   
		alert("value changed");
	}

// ============================================================================
// Not working. Fix or remove.
	function ClearField(fieldobject) {
		fieldobject.value=""; // alert("value changed");
	}

// ============================================================================
// Unclear if this is working. Needs attention.
	function resizeIFrameToFitContent( FrameName ) {
		var iFrame = document.getElementById( FrameName );
		iFrame.height = window.innerHeight 
						- document.getElementById('header-area').offsetHeight 
						- document.getElementById('footer-area').offsetHeight;
/*		window.addEventListener('DOMContentLoaded', function(e) {

			var iframes = document.querySelectorAll("iframe");
			for( var i = 0; i < iframes.length; i++) {
				resizeIFrameToFitContent( iframes[i] );
			}
		} );  */
	}


// ============================================================================
function splashScreen() {
    var div = document.getElementById('zone-three');
//    div.appendChild(document.createTextNode("Working ..."));
//    div.style.position = "fixed";
//    div.style.width = "100%";
//    div.style.height = "100%";
//    div.style.left = "0";
//    div.style.top = "0";
    div.style.zIndex = "1000";
    div.style.background = "white url('img/spinning_rings_fr_red.gif') no-repeat center";
    div.style.fontSize = "x-large";
    div.style.textAlign = "center";
    div.style.lineHeight = "3em";
//    div.style.opacity = "0.75";
//    div.style.filter = "alpha(opacity=75)"; // fix ie
//    document.body.appendChild(div);
    return true;
}

function splashScreenstop() {
    var div = document.getElementById('zone-three');
//    div.appendChild(document.createTextNode(""));
//    div.style.zIndex = "1000";
    div.style.background = "white";
    div.style.backgroundImage = "none";
//    div.style.opacity = "0.75";
//    div.style.filter = "alpha(opacity=75)"; // fix ie
//    document.body.appendChild(div);
    return true;
}


// ============================================================================
// stub function - used to avoid syntax errors on place-holder menu items
// where the functionality of those items has not yet been developed.
// Should be unnecessary in production.
	function doNothing() {
		}
		