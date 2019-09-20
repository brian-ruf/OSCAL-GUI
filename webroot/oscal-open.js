/*
filedrag.js - HTML5 File Drag & Drop
Featured on SitePoint.com
Developed by Craig Buckler (@craigbuckler) of OptimalWorks.net
Modified by Brian Ruf of the FedRAMP PMO and NIST OSCAL Team
*/
(function() {

	// getElementById
	function $id(id) {
		return document.getElementById(id);
	}


	// output information
	function Output(msg) {
		var m = $id("messages");
		m.innerHTML = msg + m.innerHTML;
	}


	// file drag hover
	function FileDragHover(e) {
		e.stopPropagation();
		e.preventDefault();
		e.target.className = (e.type == "dragover" ? "hover" : "");
	}


	// file selection
	function FileSelectHandler(e) {

		// cancel event and hover styling
		FileDragHover(e);

		// fetch FileList object
		var files = e.target.files || e.dataTransfer.files;

		// process all File objects
		for (var i = 0, f; f = files[i]; i++) {
			ParseFile(f);
			UploadFile(f);
		}

	}

	// output file information
	function ParseFile(file) {
		var m = $id("messages");
		m.innerHTML = ""; 

		Output(
			"<p>File information: <strong>" + file.name +
			"</strong> type: <strong>" + file.type +
			"</strong> size: <strong>" + file.size +
			"</strong> bytes</p>"
		);

		var doc = ""

		// display text
//		if (file.type.indexOf("text/xml") == 0) {
		if (file.type == "text/xml" || file.type == "text/json" || file.type == "application/json") {
			var reader = new FileReader();
			reader.onload = function(e) {
				Output(
					"<p><strong>" + file.name + ":</strong></p><pre>" +
					e.target.result.replace(/</g, "&lt;").replace(/>/g, "&gt;") +
					"</pre>"
				);
			}
			doc = reader.readAsText(file);
		}

			if (file.type.indexOf("text/json") == 0) {
			var reader = new FileReader();
			reader.onload = function(e) {
				Output(
					"<p><strong>" + file.name + ":</strong></p><pre>" +
					e.target.result.replace(/</g, "&lt;").replace(/>/g, "&gt;") +
					"</pre>"
				);
			}
			doc = reader.readAsText(file);
		}
	}

	// upload Allowed files
	function UploadFile(file) {
		var m = $id("messages");

		var xhr = new XMLHttpRequest();
		if (xhr.upload && (file.type == "text/xml" || file.type == "text/json" || file.type == "application/json") && file.size <= $id("MAX_FILE_SIZE").value) {

			// create progress bar
			var o = $id("progress");
			var progress = o.appendChild(document.createElement("p"));
			progress.appendChild(document.createTextNode("upload " + file.name));


			// progress bar
			xhr.upload.addEventListener("progress", function(e) {
				var pc = parseInt(100 - (e.loaded / e.total * 100));
				progress.style.backgroundPosition = pc + "% 0";
			}, false);

			// check file received or failed 
			xhr.onreadystatechange = function(e) {
				if (xhr.readyState == 4) {
					if (xhr.status == 200) {
						progress.className = "success";

						window.open("oscal.php?mode=verify", "_self");
					} else {
						progress.className = "failure";
					}
				}
			};

			// start upload
			xhr.open("POST", $id("upload").action, true);
			xhr.setRequestHeader("X-FILENAME", file.name);
			xhr.send(file);

		} else {
			m.innerHTML = m.innerHTML + "<p style='color: red; font-weight; bold;'>NOT UPLOADED!</p>";
			if (file.type != "text/xml") {
				m.innerHTML = m.innerHTML + "<p>File must have an XML extension.</p>"; 
				}
				
			if (file.size > $id("MAX_FILE_SIZE").value) {
				m.innerHTML = m.innerHTML + "<p style='color: red; font-weight; bold;'>File is too large.</p>"; 
				m.innerHTML = m.innerHTML + "Size: " + file.size + "<br/>";
				m.innerHTML = m.innerHTML + "Max : " + $id("MAX_FILE_SIZE").value + "<br/>";
			}
		}
	}

	// initialize
	function Init() {

		var fileselect = $id("fileselect"),
			filedrag = $id("filedrag"),
			submitbutton = $id("submitbutton");

		// file select
		fileselect.addEventListener("change", FileSelectHandler, false);

		// is XHR2 available?
		var xhr = new XMLHttpRequest();
		if (xhr.upload) {

			// file drop
			filedrag.addEventListener("dragover", FileDragHover, false);
			filedrag.addEventListener("dragleave", FileDragHover, false);
			filedrag.addEventListener("drop", FileSelectHandler, false);
			filedrag.style.display = "block";

			// remove submit button
			submitbutton.style.display = "none";
			continuebutton.style.display = "none";
		}
	}

	// call initialization file
	if (window.File && window.FileList && window.FileReader) {
		Init();
	}

})();
