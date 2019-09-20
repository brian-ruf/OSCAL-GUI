<?php if (! isset($oscal_doc_title)) $oscal_doc_title = ""; ?>
<header id='header-area' >
<a href='/index.php'><img style="vertical-align:middle" src="img/home.png" width="40" height="40"></a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<a href='https://pages.nist.gov/OSCAL/' target='_blank' style='text-decoration:none; color:white;'>
Open Security Controls Assessment Language (OSCAL)</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<div id='header-additional' style='width:100%; font-size: 0.7em; color: red;'><?= $oscal_doc_title ?></div>
<?php
	// after everything is converted to use 'header-additional', this php code should be removed.
	if (function_exists("AdditionalHeader") ) {
		AdditionalHeader($xpath);
	} 
	?>

</header>
<!-- div id='header-padding' style='height: 100px;'></div>
<script>
	headerHeight = document.getElementById('header-area').offsetHeight;
	headerHeight = headerHeight + 5;
	document.getElementById('header-padding').style = 'height: ' + headerHeight + 'px;';

</script-->

