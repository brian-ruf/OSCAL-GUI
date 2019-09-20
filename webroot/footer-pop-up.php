<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />

<footer id='footer-area' class='site-footer' style=' position: fixed; left: 0; bottom: 0; width: 100%; background-color: black;'>

<div id='footer-additional' style='width:100%;'></div>
<?php
	// after everything is converted to use 'footer-additional', this php code should be removed.
	if (function_exists("AdditionalFooter") ) {
		AdditionalFooter($xpath);
	} 
?>

	<div style='text-align: center; align: center; margin: 0 auto;'>

		<table style='border: 0;margin:0; width:100%;  background-color: black;'>

		<tr>
			<td style='width; 48%;  text-align:left`;'>
				&nbsp;
				<table><tr><td class="button" onclick="window.close()">CLOSE</td></tr></table>
			</td>
			<td style='width: 2%;'>&nbsp;</td>
			<td style='width: 48%; text-align:right;'>
				<img src='./img/NIST.png' style='width:50px;height:15px;' alt="National Institute of Standards and Technology Logo" />&nbsp;
				<img src='./img/FedRAMP logo_Option 2_no_tagline.png' style='width:15px;height:15px;' alt="Federal Risk and Authorizaton Management Program (FedRAMP) Logo" />
				&nbsp;
			</td>
			<td style='width: 2%;'>&nbsp;</td>
		</tr>
		</table>
	</div>
</footer>