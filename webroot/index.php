<?php  
require_once('oscal-begin.php');
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
<!--
<tr><td class="button" onclick="window.open('./oscal.php?mode=new', '_self')" style="text-align: left">
	<img style="vertical-align:middle" src="img/document.png" width="40" height="40" />&nbsp;&nbsp;Start New Project&nbsp;
</td></tr>
-->

<tr><td class="button" onclick="window.open('./oscal.php?mode=continue', '_self')" style="text-align: left">
	<img style="vertical-align:middle" src="img/folder.png" width="40" height="40">&nbsp;&nbsp;Open OSCAL File&nbsp;
</td></tr>

<tr><td class="button" onclick="window.open('./oscal-tools.php', '_self')" style="text-align: left">
	<img style="vertical-align:middle" src="img/gear.png" width="40" height="40">&nbsp;&nbsp;Tools &amp; Maintenance&nbsp;
</td></tr>

<tr><td class="button" onclick="window.open('./oscal-about.php', '_self')" style="text-align: left">
	<img style="vertical-align:middle" src="img/information.png" width="40" height="40">&nbsp;&nbsp;About&nbsp;
</td></tr>
</table>

<br/><br/><br/>

</div>

<br><br><br>

<?php include 'footer.php'; ?>
</body>
</html>


