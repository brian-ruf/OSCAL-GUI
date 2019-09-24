<?php 
include_once("oscal-objects.php");
if (isset($_SESSION['myFORM'])) {
	$oscal_catalog = $_SESSION['myFORM'];
	echo "<br />from session variable<br />";
} else {
	$oscal_catalog = New OSCAL("D:\GitRepos\OSCAL-GUI\webroot\projects\proj-2019-03-27--15-31-10-000000\NIST_SP-800-53_rev4_catalog.xml");
	echo "<br />setting<br />";
}

include_once("oscal-begin.php");
include_once("oscal-functions.php");
include_once("oscal-formfunctions.php");
// session_start();
$page_title = "FORM TEST";

?>
<html>
<head lang="en">
	<title id='pagetitle'>OSCAL: <?= $page_title ?></title>
	<link rel="stylesheet" type="text/css" media="all" href="default.css"/>
    <link rel="stylesheet" href="jodit.min.css">
	<script src='oscal-zones.js'></script>
    <script src="jodit.min.js"></script>
</head>
<body>

<?php
echo $oscal_catalog->GetTitle();
$metadata = $oscal_catalog->Query("//metadata");
echo "<br />";
echo var_dump($oscal_catalog->xpath);
?>

<?php
	$input_map = array();
	
	foreach($_POST as $key => $form_field) {
		
		
		if ($form_field['input'] == $form_field['original']) {
			echo "No change detected for " . fieldname2xapth($key) . "<br />";
		} else {
			echo "New value for " .  fieldname2xapth($key) . ": " . $form_field['input'] . "<br />"; 
		}
	}
?>

<form action='zFormTest.php' method='post'>

<?php  

$xpath = "//control[@id='ac-1']";

// echo MakeField($xpath);

?>

<input name="//control{@id='123'}[input]" value='test1' type='text'>
<input name="//control{@id='123'}[original]" value='test1' type='hidden'>
<br />

<input name="//control/@id[input]" value='test2' type='text'>
<input name="//control/@id[original]" value='test2' type='hidden'>
<br />

<input name="//test3[input]" value='test3' type='text'>
<input name="//test3[original]" value='test3' type='hidden'>
<br />

<input type="submit" value="Submit">
<br />

</form>

</body>

<div>
<pre>
<?php echo $oscal_catalog->messages->GetDebug(); ?>
</pre>
</div>

<?php

$_SESSION['myFORM'] = $oscal_catalog;

function xapth2fieldname($field_name) {
	$curly = array('{', '}');
	$straight = array ('[', ']');
	return str_replace($straight, $curly, $field_name);
}

function fieldname2xapth($field_name) {
	$curly = array('{', '}');
	$straight = array ('[', ']');
	return str_replace($curly, $straight, $field_name);
}


?>
