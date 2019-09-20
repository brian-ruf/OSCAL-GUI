<?php  
require_once('oscal-begin.php');

?>
<!DOCTYPE html>
<html>
<head lang="en">
	<title id='pagetitle'>OSCAL: <?= $page_title ?></title>
	<link rel="stylesheet" type="text/css" media="all" href="default.css"/>
    <link rel="stylesheet" href="jodit.min.css">
	<script src='oscal-zones.js'></script>
    <script src="jodit.min.js"></script>
</head>

<body onload="<?= $script ?>">
<?php include 'header.php'; ?>


<div style='text-align: center; width: <?= SCREEN_WIDTH ?>; align: center; margin: 0 auto; '>

<table class='zone' style='width: 100%'>
	<tr>
		<td colspan='2'><div id='zone-one' class='zone'><?= $zone_init['zone-one'] ?></div></td>
	</tr>
	<tr>
		<td style='width: 50%'><div id='zone-one-left' class='zone'><?= $zone_init['zone-one-left'] ?></div></td>
		<td style='width: 50%'><div id='zone-one-right' class='zone'><?= $zone_init['zone-one-right'] ?></div></td>
	</tr>
	<tr>
		<td colspan='2'><div id='zone-two' class='zone'><?= $zone_init['zone-two'] ?></div></td>
		<!-- style='background-image: url(./img/spinning_rings_fr_red.gif); height: 400px; width: 400px;' -->
	</tr>
	<tr>
		<td style='width: 50%'><div id='zone-two-left' class='zone'><?= $zone_init['zone-two-left'] ?></div></td>
		<td style='width: 50%'><div id='zone-two-right' class='zone'><?= $zone_init['zone-two-right'] ?></div></td>
	</tr>
	<tr>
		<td colspan='2'><div id='zone-three' class='zone'><?= $zone_init['zone-three'] ?></div></td>
	</tr>
	<tr>
		<td style='width: 50%'><div id='zone-three-left' class='zone'><?= $zone_init['zone-three-left'] ?></div></td>
		<td style='width: 50%'><div id='zone-three-right' class='zone'><?= $zone_init['zone-three-right'] ?></div></td>
	</tr>
	<?php if (SHOW_DEBUG) : ?>
	<tr>
		<td colspan='2'>
			<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
			<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
			<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
			<div id='debug-title' class='zone'>
				<hr />
				<b>DEBUG MESSAGES</b>&nbsp;
				[<span id='debug-menu' onclick="toggle_visibility('debug')">&nbsp;TOGGLE VISIBILITY&nbsp;</span>]
				&nbsp;&nbsp;To disable DEBUG MESSAGES, set <b>SHOW_DEBUG</b> to <b>false</b> in <b>oscal-config.php</b>
			</div></td>
	</tr>
	<tr>
		<td colspan='2'><div style='width:100%; overflow:auto;'><pre id='debug' class='debug'></pre><div></td>
	</tr>
	<?php endif ?>
</table>
	
	<img src='./img/spinning_rings_fr_red.gif' style='display:none'>
	
<?php include 'footer.php'; ?>
</body>
</html>
