<!DOCTYPE html>
<html lang="en">
<?php
include 'oscal-config.php';
include 'oscal-functions.php';

$title = "CIS Workbook";

$status = false;

if (empty($_GET['file'])) {
	$oscalfile='';
} else {		
	$oscalfile="./uploads/" . urldecode($_GET['file']);
	$status = true;
}

if ($status) {
	
	$oscal = new DOMDocument();         // Creates a memory object to handle the catalog XML
	$oscal->preserveWhiteSpace = false; // Preserving white space is an option in the XML spec. Make sure this aligns with OSCAL's recommendation.
	if (! $oscal->load("$oscalfile") === false) { // Load the file and only proceed if there were no errors.

		$xsl = new DOMDocument;
		if (! $xsl->load('./datafiles/FedRAMP-CIS.xsl') === false) { // Load the file and only proceed if there were no errors.
			$proc = new XSLTProcessor();
			$proc->importStyleSheet($xsl);

		} else{
			$status = false;
		}
		
		 

	} else {
		// Failed to open file. Show error.
		$status = false;
	}
} else {
	Echo "Error getting file name.";
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
</head>
<body>
<?php include 'header.php'; ?>
<br/><br/><br/>


<div style='text-align: center; width: 1000px; align: center; margin: 0 auto; '>


<!-- VISIBLE CONTENT GOES HERE -->
<?php 

if ($status) { 
//	if (! $xsl->load('./datafiles/FedRAMP-CIS.xsl') === false) { // Load the file and only proceed if there were no errors.
	echo $proc->transformToXML($oscal);

} else { 
	echo "Error. Could not continue.";

} 
?>

</div>
	
<?php include 'footer.php'; ?>
</body>
</html>