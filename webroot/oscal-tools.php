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

<br/><br/><br/>

<div style='text-align: center; width: <?= SCREEN_WIDTH ?>; align: center; margin: 0 auto; '>

<br/>

<table style="border: none; margin: 0px auto; border-collapse:separate; vertical-align:middle; padding: 5px;  border-spacing: 10px; ">
<!-- tr><th style="text-align: center; font-weight: bold; ">Menu</td></tr -->

<tr><td class="button" onclick="window.open('./oscal-getlatest.php?mode=download', '_self')" style="text-align: left">
	<img style="vertical-align:middle" src="img/cloud-download.png" width="40" height="40">&nbsp;&nbsp;Update OSCAL Files&nbsp;
</td></tr>

<tr><td class="button" onclick="window.open('./oscal-getlatest.php?mode=compile', '_self')" style="text-align: left">
	<img style="vertical-align:middle" src="img/wheel.png" width="40" height="40">&nbsp;&nbsp;Compile Schema Files&nbsp;
</td></tr>

<?php if (SHOW_ADVANCED) {  ?>
<tr><td class="button" onclick="window.open('./oscal-tools-advanced.php', '_self')" style="text-align: left">
	<img style="vertical-align:middle" src="img/spinning_rings_fr_red.gif" width="40" height="40">&nbsp;&nbsp;Advanced Settings&nbsp;
</td></tr>
<?php } ?>

<?php echo MakeBackButton("./index.php"); ?>

</table>

<br/><br/><br/>

</div>

<br><br><br>

<?php include 'footer.php'; ?>
</body>
</html>


