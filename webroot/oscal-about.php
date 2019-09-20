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

<br />

<p>This is open source software, developed by <a href="https://noblis.org" target="_new">Noblis</a> 
under a joint effort between the <a href="https://github.com/usnistgov/OSCAL" target="_new">NIST OSCAL Team</a>, 
and the <a href="https://www.fedramp.gov" target="_new">FedRAMP PMO</a>.<br />
<strong>You are free to use, duplicate, and modify this software.</strong>
</p>

<p>Please visit <a href="https://github.com/brianrufgsa/OSCAL-GUI" target="_new">https://github.com/brianrufgsa/OSCAL-GUI</a> 
for more information about this tool.</p>

<p>This tool will work with later versions of most major browsers, except Internet Explorer and Edge, 
which do not support server-side execution (SSE) via javascript/eventSource. Google Chrome or Mozilla Firefox is recommended.</p>

<p>This enables creation and modification of OSCAL content.<br />
While not actively supported, please direct questions to <a href="mailto:brian.ruf@noblis.org">brian.ruf@noblis.org</a>.</p>

<br />
<p>This tool uses the Open Source WYSIWYG editor, <a href="https://xdsoft.net/jodit/" target="_new">Jodit</a> for editing of fields that allow formatting.<br />
Formatting is limited to that which is allowed by OSCAL to ensure lossless translation between XML and JSON.
</p>

<p>This tool uses the Java version of <a href="https://saxonica.com/download/java.xml" target="_new">Saxon-HE (Home Edition)</a> Version 9.9, which is used only to process the 
XSLT 3.0 files used to convert between OSCAL formats (XML to/from JSON and YAML).</p>


<br/><br/><br/>

</div>

<br><br><br>

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
	<td colspan='2'><div style='width:100%; overflow:auto;'>
<pre id='debug' class='debug'>

</pre><div></td>
</tr>
<tr><td><a href="./zdebug.php" target='_new'>Session and Server Variables</a> (Takes Time to Generate)</td></tr>
<?php endif ?>

<?php include 'footer.php'; ?>
</body>
</html>


