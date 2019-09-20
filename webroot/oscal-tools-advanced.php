<?php  
require_once('oscal-begin.php');
require_once('oscal-schema-map.php');
$page_title = "OSCAL Menu";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?= $page_title ?></title>
	<link rel="stylesheet" type="text/css" media="all" href="default.css"/>

</head>

<body>
<?php include 'header.php'; ?>


<div style='text-align: center; width: <?= SCREEN_WIDTH ?>; align: center; margin: 0 auto; '>
<span style='color: red; font-weight: bold;'>EXPERIMENTAL -- EXPERIMENTAL<br />FOR DEVELOPERS ONLY</span>
<br />
<div style='text-align:left;'>
<p>These buttons generate PHP code from the OSCAL schema files.
The generated code can perform some editing of OSCAL content.
They are generic forms and intended to get an applicaton started.</p>
<p>While some of the generated forms could be used stand-alone, they are intended to be customized 
and/or merged in with a more robust user interface.</p>
<p>The forms are created in the /forms subdirectory under the root of this application, and are named:<br />
<span class='fixed-width' style='width:25%;'>generated-[<span style='font-style: italic; font-weight:bold;'>form-name</span>].php</span></p>

<p>Customized versions of forms should also reside in the /forms subdirectory and be named:<br />
<span class='fixed-width' style='width:25%;'>custom-[<span style='font-style: italic; font-weight:bold;'>form-name</span>].php</span></p>

<p>Within the application, oscal-forms.php is designed to first look for and use a custom version of a form.
If no custom version of a form is found, it will use the generated version.</p>
</div>

<table style="border: none; margin: 0px auto; border-collapse:separate; vertical-align:middle; padding: 5px;  border-spacing: 10px; ">
<!-- tr><th style="text-align: center; font-weight: bold; ">Menu</td></tr -->

<tr><td class="button" onclick="window.open('./oscal.php?mode=makeforms&type=common', '_self')" 
		style="text-align: left" title='Includes metadata, attachments, and other constructs common to all OSCAL files.'>
	<img style="vertical-align:middle" src="img/measure.png" width="40" height="40">&nbsp;&nbsp;Generate Common Forms&nbsp;
</td></tr>

<tr><td class="button" onclick="window.open('./oscal.php?mode=makeforms&type=catalog', '_self')" style="text-align: left">
	<img style="vertical-align:middle" src="img/measure.png" width="40" height="40">&nbsp;&nbsp;Generate Catalog Forms&nbsp;
</td></tr>

<tr><td class="button" onclick="window.open('./oscal.php?mode=makeforms&type=profile', '_self')" style="text-align: left">
	<img style="vertical-align:middle" src="img/measure.png" width="40" height="40">&nbsp;&nbsp;Generate Profile Forms&nbsp;
</td></tr>

<tr><td class="button" onclick="window.open('./oscal.php?mode=makeforms&type=ssp', '_self')" style="text-align: left">
	<img style="vertical-align:middle" src="img/measure.png" width="40" height="40">&nbsp;&nbsp;Generate SSP Forms&nbsp;
</td></tr>

<tr><td class="button" onclick="window.open('./oscal.php?mode=makeforms&type=component', '_self')" style="text-align: left">
	<img style="vertical-align:middle" src="img/measure.png" width="40" height="40">&nbsp;&nbsp;Generate Component Forms&nbsp;
</td></tr>

</table>

<?php  echo MakeBackButton("./oscal-tools.php");  ?>

<br/><br/><br/>

</div>

<br><br><br>

<?php include 'footer.php'; ?>
</body>
</html>


