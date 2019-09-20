<header id='header-area' style=' position: fixed; left: 0; top: 0; width: 100%; background-color: rgb(17, 46, 81);'>

<div id='header-additional' style='width:100%;'></div>
<?php
	// after everything is converted to use 'header-additional', this php code should be removed.
	if (function_exists("AdditionalHeader") ) {
		AdditionalHeader($xpath);
//		echo ("<br/><br/><br/><br/>");
	} 
	?>

</header>
<br/><br/>
