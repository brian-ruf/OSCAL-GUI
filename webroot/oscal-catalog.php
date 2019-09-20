<!DOCTYPE html>
<html lang="en">
<?php
include 'oscal-config.php';
include 'oscal-functions.php';
error_reporting(E_ALL | E_STRICT);

$title = "OSCAL Catalog";
$errormessage = "";
$status = false;
$xsltfile = './datafiles/oscal-catalog-list.xsl';

if (! empty($_GET['file'])) {
	$oscalfile="./uploads/" . urldecode($_GET['file']);
	$status = true;
} else {		
	$status = false;
	$oscalfile='';
	$errormessage = "Missing file name.";
}

if ($status) {
	
	$oscal = new DOMDocument();         // Creates a memory object to handle the catalog XML
	$oscal->preserveWhiteSpace = false; // Preserving white space is an option in the XML spec. Make sure this aligns with OSCAL's recommendation.
	if (! $oscal->load("$oscalfile") === false) { // Load the file and only proceed if there were no errors.

		$xsl = new DOMDocument;
		if (! $xsl->load($xsltfile) === false) { // Load the file and only proceed if there were no errors.
			$proc = new XSLTProcessor();
			$proc->importStyleSheet($xsl);
			
			$output = $proc->transformToXML($oscal);
			if ($output === false) {
				$output = "[Processing Error]";
				$errormessage .= "Could not transform. Problem applying XSLT to XML.<br/>";
				$status = false;
			}

		} else{
			$errormessage .= "Could not open XSLT file.<br/>";
			$status = false;
		}

	} else {
		$errormessage .= "Could not open XML file.<br/>";
		$status = false;
	}
} else {
	$errormessage .= "File name not present.<br/>";
	$status = false;
}

?>
<head>
    <title id='pagetitle'><?php echo($title); ?></title>
	<link rel="stylesheet" href="default.css">
    <style type="text/css">
		body { font-family: sans-serif }
		table.summary { display: inline-block }

		td, th { border: thin solid black;text-align: center }
		th { background-color: rgb(17, 46, 81); color: white }
		th.subhead { background-color: rgb(4, 107, 153) }
		td.rowhead { font-weight: bold }
		.flagged { background-color: #FFCCFF }
		.on { background-color: #FFD700 }
		
	</style>
	<script>
		function toggle_visibility(id) {
		   var e = document.getElementById('div-' + id);
		   var p = document.getElementById('prompt-' + id);
		   if(p.innerHTML == 'Hide') {
			  e.setAttribute("style", "max-height: 0px; transition: all 1s ease;  overflow:hidden;"); 
		      p.innerHTML = 'Show';
		   } else {
			  e.setAttribute("style", "max-height: 400px; transition: all 1s ease; overflow: auto;"); 
		      p.innerHTML = 'Hide';
		   }
		}

		
		function addcontrol(id) {
			
		}
		
		function show_control(id) {
			window.open("./oscal-control.php?id=" + id + "&file=<?php echo $_GET['file'] ?>", 'popup','width=800,height=800');
		}

		
	</script>
</head>
<body onload="CollapsibleLists.apply();">
<?php include 'header.php'; ?>
<br/><br/><br/>

<div style='text-align: left; width: 1000px; align: center; margin: 0 auto; '>


<!-- VISIBLE CONTENT GOES HERE -->
<?php 

if ($status) { 
	echo $output;

} else { 
	echo $errormessage;
} 
?>

</div>
	
<?php include 'footer.php'; ?>
<script src="CollapsibleLists.js"></script>
</body>
</html>