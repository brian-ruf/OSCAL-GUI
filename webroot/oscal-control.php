<!DOCTYPE html>
<html lang="en">
<?php

// STILL NEED TO HANDLE:
// <param> sub-elements: constraint, desc, value, link, part
// Within param


include 'oscal-config.php';
include 'oscal-functions.php';
error_reporting(E_ALL | E_STRICT);

define("ParamToken", "!PARAMETER!");
$title = "OSCAL Control";
$errormessage = "";
$status = false;
$xsltfile = './datafiles/oscal-catalog-control.xsl';

if (! empty($_GET['file'])) {
	$oscalfile="./uploads/" . urldecode($_GET['file']);
	$status = true;
} else {		
	$status = false;
	$oscalfile='';
	$errormessage = "Missing file name.";
}

if (! empty($_GET['id'])) {
	$controlid=urldecode($_GET['id']);
	$status = true;
} else {		
	$status = false;
	$controlid='';
	$errormessage = "Missing control ID.";
}

if (! empty($_GET['paramtype'])) {
	// valid paramtypes:
	//    original: as it appears in the catalog
	//    profile: as constrained or assigned by profiles
	//    values: as assigned within an SSP
	$paramtype=urldecode($_GET['paramtype']);
} else {		
	$paramtype="original";
}

if ($status) {
	
	$oscal = new DOMDocument();         // Creates a memory object to handle the catalog XML
	$oscal->preserveWhiteSpace = false; // Preserving white space is an option in the XML spec. Make sure this aligns with OSCAL's recommendation.
	if (! $oscal->load("$oscalfile") === false) { // Load the file and only proceed if there were no errors.

		$xsl = new DOMDocument;
		if (! $xsl->load($xsltfile) === false) { // Load the file and only proceed if there were no errors.
			$temp = $xsl->saveXML($xsl);
			$temp = str_replace("*id*", $controlid, $temp);
			$xsl2 = new DOMDocument;
			$xsl2->loadXML($temp);

			$ns = $oscal->documentElement->namespaceURI;
			if($ns) {
				$xpath = new DomXPath($oscal);
				if ($xpath->registerNamespace("oscal", $ns)) {

					
					// Find all <insert> elements and replace them with the parameter tolken.
					// This essentially converts that tag to a string so it can passed, processed, 
					// and displayed with the rest of the string that surrounds it.
				
					$query = "//oscal:insert";
					$inserts_found = "";
					foreach ($xpath->query($query) as $insert) {
						$param_id = $insert->getAttribute("param-id");
						
						$insert->parentNode->replaceChild($oscal->createTextNode("[" . ParamToken . ":" . $param_id . "]"), $insert);
					}

					// Read all the parameters into an array. 
					// [ param_id_1 => [ param text, null ], param_id_2 => [ param_text, [Choices]] ]  
					// NOTE: param_id_1 is an example of an ASSIGNMENT parameter
					// NOTE: param_id_2 is an example of a SELECT parameter
					$query = "//oscal:param";
					$parameter_list = ParamList2Array($xpath->query($query));
					
					$query = "//oscal:control[@id='" . $controlid . "'] | //oscal:subcontrol[@id='" . $controlid . "']";
					$control_branch = $xpath->query($query);

//					$dom = new DOMDocument('1.0', 'utf-8');
//					$root = $dom->create_element("group");
//					$family = $root;
//					$cat_control = $dom->createElement('test', 'This is the root element!');					
//					$temp2 = $oscal->saveXML($control_branch);
				
				} else {
					$errormessage .= "Could not register namespace.<br/>";
					$status = false;
				};
			} else {
				$errormessage .= "Could not find namespace declaration.<br/>";
				$status = false;
			} ;
			
			$proc = new XSLTProcessor();
			$proc->importStyleSheet($xsl2);

			$tmpcntr = 0;
			$output = $proc->transformToXML($oscal);
			if (!$output === false) {
				// Call function
				$param_views = array( 
								['Blank', "[&nbsp;PARAMETER&nbsp;]", "color: grey; font-style:italic; "], 
								['Catalog' , $parameter_list , "font-style:italic; font-weight: bold;"]
								);
				$output = GenerateViews($output, $param_views);
			} else {
				$output = "[Processing Error]";
				$errormessage .= "Could not transform. Problem applying XSLT to XML.<br/>";
				
				$status = false;
			}

		} else{
			$errormessage .= "Could not open XSLT file.<br/>";
			$status = false;
		}

	} else {
		$errormessage .= "Could not open XML file.<br/>";
		$status = false;
	}
} else {
	$errormessage .= "File name not present.<br/>";
	$status = false;
}

?>
<head>
    <title id='pagetitle'><?php echo($title); ?></title>
	<link rel="stylesheet" href="default.css">
    <style type="text/css">
		body { font-family: sans-serif }
		table.summary { display: inline-block }

		td, th { border: thin solid black;text-align: center }
		th { background-color: rgb(17, 46, 81); color: white }
		th.subhead { background-color: rgb(4, 107, 153) }
		td.rowhead { font-weight: bold }
		.flagged { background-color: #FFCCFF }
		.on { background-color: #FFD700 }
	</style>
	<script>
		function changeView(param_type) {
			views = [ <?php echo GenerateViewScript($param_views) ; ?> ];

		  for(var i =0, il = views.length;i<il;i++){
			 document.getElementById(views[i]).style.display = "none";
			 document.getElementById(views[i] + '-menu').style.fontWeight = "normal";
		  }
		  document.getElementById(param_type).style.display = "block";
		  document.getElementById(param_type + '-menu').style.fontWeight = "bold";
		}

	</script>
</head>
<body onload="CollapsibleLists.apply();">
<header style=' position: fixed; left: 0; top: 0; width: 100%; background-color: rgb(17, 46, 81);'>

<!-- Move this div statement and function call to 'AdditionalHeader' function. -->
<div style='font-size: 12px; text-align: right;'>
<?php echo GenerateViewMenu($param_views); ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</div>
</header><br /><br />

<div style='text-align: left; width: 800px; align: center; margin: 0 auto; '>


<!-- VISIBLE CONTENT GOES HERE -->
<?php 
// echo var_dump($proc);
// echo "<br/><br/>";

if ($status) { 
	echo $output;

} else { 
	echo $errormessage;
} 
?>
</div>

<br />
<div class='debug' style='text-align: left;  align: center; margin: 0 auto; background-color: #c0c0c0; color: grey;'>
<br />DEBUG MESSAGES: <br />
<?php  echo $errormessage; ?><br /><br /><hr />
BRANCH: <?php  echo var_dump($control_branch); ?><br />

<?php

// foreach ($control_branch as $item) {
 //   echo $item->nodeValue . "<br />\n";
// }


?>
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
</div>
	
<footer class='site-footer' style=' position: fixed; left: 0; bottom: 0; width: 100%; background-color: rgb(17, 46, 81);'>
<div style='text-align: right; width: 800px; align: right; margin: 0 auto; '>
<table><tr><td class="button" onclick="window.close()">CLOSE</td></tr></table>
</div>
</footer>
</body>
</html>


<?php
function GenerateViewScript(&$views) {

	$output = "";
	$cntr = 0;
	
	foreach ($views as $view_spec) {
		$cntr += 1;
		if ($cntr > 1) {
			$output .= ", ";
		}
		$output .=  chr(34) . "view-" . $view_spec[0] . chr(34);
	}

	return $output;
}



function GenerateViewMenu(&$views) {

	$output = "PARAMETER DISPLAY: ";
	$cntr = 0;
	
	foreach ($views as $view_spec) {
		$cntr += 1;
		if ($cntr == 1) {
			$output .= "<span id ='view-" . $view_spec[0] . "-menu' onclick= " . chr(34) . "changeView('view-" . $view_spec[0] . "')" . chr(34) . " style='font-weight: bold; cursor: pointer; font-size: 0.8em;'>$view_spec[0]</span>";
		} else {
			$output .= "&nbsp;|&nbsp;";
			$output .= "<span id ='view-" . $view_spec[0] . "-menu' onclick= " . chr(34) . "changeView('view-" . $view_spec[0] . "')" . chr(34) . " style='font-weight: normal; cursor: pointer; font-size: 0.8em;'>$view_spec[0]</span>";
		}
	}

	return $output;
}


function GenerateViews($raw, &$views) {

	$output = "";
	$cntr = 0;
	
	foreach ($views as $view_spec) {
		$cntr += 1;
		if ($cntr == 1) {
			$output .= "<div id='view-" . $view_spec[0] . "' style='display:block'>";
		} else {
			$output .= "<div id='view-" . $view_spec[0] . "' style='display:none'>";
		}
		
		$output .= ReplaceParms($raw, $view_spec[1], $view_spec[2]);
//		$output .= "<br /><br />VIEW: " . $view_spec[0] . "<br />";
		$output .= "</div>";
	}

	return $output;
}

// 
function ReplaceParms($output, &$parameter_list, $style) {
	$default = "[&nbsp;PARAMETER&nbsp;]";

	$param_start = strpos($output, "[" . ParamToken . "");
	while (! $param_start === false) {
//		$tmpcntr += 1;
//		if ($tmpcntr > 10) break;
		$param_end = strpos($output, "]", $param_start);
		if ($param_end > $param_start) {
			$param_id = substr($output, $param_start+13, $param_end - $param_start -13);
			$goodstuff = "";
			$goodstuff .= "<span style='" . $style . "'>";
			if (is_array($parameter_list)) {
				if (array_key_exists($param_id, $parameter_list)) {
					$goodstuff .= $parameter_list[$param_id][0];
				} else {
					$goodstuff .= $default;
				}
			} elseif (is_string($parameter_list)) {
				$goodstuff .= $parameter_list;
			} else {
				$goodstuff .= $default;
			}
			$goodstuff .= "</span>";
			$output = str_replace("[" . ParamToken . ":" . $param_id . "]", $goodstuff, $output);
		} else {
			break;
		}
	$param_start = strpos($output, "[" . ParamToken . "");
	}
	return $output;
}


// Finds all <param> in catalog and converts them to an array: 
//    $parameter_list = [ [ param-id, display_text, array_of_choices ], 
//                        [ param-id, display_text, array_of_choices ], 
//                        [ param-id, display_text, array_of_choices ]  ]
//    
// NOTE: array_of_choices only populated for select statements, and is null otherwise. 
//       It is intended for creating pull-down lists, radio buttons, and similar user input constructs.
//
// EXAMPLE:
// 		Converts this:
//          <param id="ac-4.3_prm_1">
//             <label>organization-defined policies</label>
//          </param>
//
//      TO this:
//   		$parameter_list = [[ 'ac-4.3_prm_1', '[ASSIGNMENT: organization-defined policies]', null ], [other param], [other param]]
//
// EXAMPLE:
// 		Converts this:
//          <param id="ac-4.4_prm_1">
//             <select how-many="one or more">
//                <choice>Option A</choice>
//                <choice>Option B</choice>
//                <choice>Option C</choice>
//			</select>
//          </param>
//
//      TO this:
//   		$parameter_list = [[ 'ac-4.4_prm_1', '[SELECT: Option A | Option B | Option C', ['Option A', 'Option B', 'Option C'] ], [other param], [other param]]
//
function ParamList2Array($nodeList) 
{ 
global $errormessage;

    $parameter_list = false; 
	foreach ($nodeList as $param) {  // loop through each parameter element found

		foreach ($param->childNodes as $item) { // loop through the child elements in the param element looking for 'select' and 'label' elements

			$choices = array();
			switch($item->nodeName) {
				case 'select':
					$choices_str = "SELECT: ";
					$cntr = 0;
					if ($item->hasAttribute('how-many')) {
						$choices_str .= "(" . $item->getAttribute('how-many') . ") ";
					}
					foreach ($item->childNodes as $choice) {  // within a 'select' element, we must loop through the 'choice' elements
						if ($cntr == 0) {
							$choices_str .= $choice->nodeValue;
						} else {
							$choices_str .= " | " . $choice->nodeValue;
						}
						$cntr += 1;
						array_push($choices, $choice->nodeValue);
					}
					$parameter_list[$param->getAttribute("id")] = ["[" . $choices_str . "]", $choices]; 
					break;
				case 'label':
					$parameter_list[$param->getAttribute("id")] = ["[ASSIGNMENT: " . $param->nodeValue . "]", null]; 
					break;
				default:
					break;
				}
		}
	} 

    return $parameter_list; 
} 
?>