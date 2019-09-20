<!DOCTYPE html>
<html lang="en">
<?php
define("SCHEMA_ROOT_NAME", "//xs:annotation/xs:appinfo/m:root");
define("SCHEMA_VERSION", "//xs:schema/@version");
// include 'uploadfiles.php';
require_once('oscal-begin.php');
require_once('oscal-functions.php');
require_once('oscal-schema-map.php');
$title = "Update OSCAL Files";
global $oscal_roots;

?>
<head>
    <title id='pagetitle'><?php echo($title); ?></title>
	<link rel="stylesheet" href="default.css">

</head>
<body>
<?php include 'header.php'; ?>


<div style='text-align: center; width: 1000px; align: center; margin: 0 auto; '>
<br/>


<span style='color: red; font-size: 2em; font-style: italic;'>Update Schema Files</span>
<br/><br/>
<span style='color: black; font-size: 1em; font-family: monospace; font-style: normal; font-weight:bold;'>

<?php 
if (array_key_exists('mode', $_GET)) {
	if ($_GET["mode"] == "compile") {
		foreach ($oscal_roots as $root_key => $root_item) {
			echo "" . $root_key;
			echo ProcessSchema($root_key);
			echo "<br /><hr />";	
		}
	} else {
		echo UpdateOSCALValidationFiles(); 
	}

} else {
	echo UpdateOSCALValidationFiles(); 
}

echo MakeBackButton("./oscal-tools.php"); // , "img/gear.png")

?>
<br /><br />

<?php if (SHOW_DEBUG) {  ?>
<hr />
<div class='debug'>
<br />
Files downloaded here: <br/><br />
<span style='color: black; font-size: 1em; font-family: monospace; font-style: normal; font-weight:bold;'>
<?php echo OSCAL_LOCAL_FILES; ?><br /><br />

</span>

<br/><br/>
</div>
<?php } ?>

<br/>

</span>

</div>
	
<?php include 'footer.php'; ?>
</body>
</html>