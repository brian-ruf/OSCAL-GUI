<?php
require_once("oscal-functions.php");
/*  ======================================  */ 
/*  ===      DEPLOYMENT SPECIFIC       ===  */ 
/*  === VARIABLES THAT MAY BE MODIFIED ===  */ 
/*  ======================================  */ 

// List of supported timezones found here: http://php.net/manual/en/timezones.php
define("TIMEZONE", "America/New_York");

// Location of files being worked by users. 
// In a production web server, this should be outside the 
//     web site's root directory to better protect the files;
//     however, the application does not yet support this.
// IMPORTANT: Must include trailing slash
define("PROJECT_LOCATION_RELATIVE", "/projects/"); 
define("PROJECT_LOCATION", $_SERVER['DOCUMENT_ROOT'] . PROJECT_LOCATION_RELATIVE); 

// Root Certificate Authorities (CA)
// If you wish to have the application verifiy certificates for external
//    datastores, such as for the NIST-provided OSCAL resources on GitHub,
//    you must provide a file with valid root CAs.
// If ROOT_CA_PEM_LOCATION is defined, and the .pem file exists, this 
//    application will attempt to use it when downloading files.
// ADVANCED: If you require an organizational certificate to be recognized, 
//    you can simply add the PEM-formatted certificate to an existing
//    PEM file.
// IMPORTANT: This application triggers Java .jar files for advanced 
//    execution, such as for converting between XML and JSON. 
//    Java may require its own root CA configuration.
define("ROOT_CA_PEM_LOCATION", $_SERVER['DOCUMENT_ROOT'] . "/third-party/root-certificates/cacert.pem"); 

// Should be set to false for normal use.
// When set to true, some scripts will add a grey box at 
//     the end of the page with debugging onformation.
define("SHOW_DEBUG", true);

// Should be set to false for normal use.
// When set to true, some scripts will add a grey box at 
//     the end of the page with debugging onformation.
define("SHOW_ADVANCED", true);

// Location of Java runtime on the host system. 
//     Use an explicit path for security and stability; however, 
//     this will work with just the Java executable name
//     if the executable is in the environment/shell path.
define("JAVA_VM", "java.exe");

// Location of Saxon HE JAR File. 
//     Use an explicit path for security and stability; however, 
//     this will work with a relative path from the web server root
define("SAXON_HE", "./third-party/saxon-he/saxon9he.jar");

// DATE/TIME FORMATS
// For date/time formats see: https://www.php.net/manual/en/datetime.createfromformat.php
// Format for presenting date and time to users 
define ("DATE_TIME_PRESENT_FORMAT", "l F j\, Y g:i:s A \(T\)");
// Format for storing date and time in an OSCAL file
// IMPORTANT: This must align with the OSCAL specification.
define ("DATE_TIME_STORE_FORMAT", "Y-m-d\TH:i:s.uP");

// DATE FORMATS
// For date/time formats see: https://www.php.net/manual/en/datetime.createfromformat.php
// Format for presenting date and time to users 
define ("DATE_PRESENT_FORMAT", "l F j\, Y");
// Format for storing date and time in an OSCAL file
// IMPORTANT: This must align with the OSCAL specification.
define ("DATE_STORE_FORMAT", "Y-m-dP");


/*  =====================================================  */ 
/*  ===  USE CAUTION WHEN MODIFIYING BELOW THIS LINE  ===  */ 
/*  =====================================================  */ 

// Location of OSCAL XSD, XSLT, and Metaschema files.
// In a shared system, put this outside the web site root directory
// IMPORTANT: Must include trailing slash
define("OSCAL_LOCAL_FILES", $_SERVER['DOCUMENT_ROOT'] . "/oscal/");  

// Location of forms generated from Metaschema files.
// In a shared system, put this outside the web site root directory
// IMPORTANT: Must include trailing slash
define("OSCAL_FORM_FILES", $_SERVER['DOCUMENT_ROOT'] . "/forms/");  

// This array contains the list of valid OSCAL root elements as well as:
//     - an appropriate title for each root element type; and
//     - Links to the NIST-published schema and conversion files for each root.
//
// When a user slects "Tools & Maintence", "Update OSCAL Files", each "web_source"
//     is downloaded, and local copies cached for use.
// IMPORTANT: At this time, ONLY the local copy of these files is used for processing.
// 
// When one of the "root" elements is detected, it is validated using 
//     a local copy of the specified "schema".
// When an OSCAL file needs to be converted between formats, it is converted using
//     a local copy of the converter specified here.
// 
// Every schema and converter file identified by a "web_source" in 
//    this array will be downloaded when the user selects "Update OSCAL Files" 
//    from the "Tools & Maintenance" menu. 
//    NOTE: Use $oscal_additional_files (below) if you need "Update OSCAL Files"
//          to download additional files beyond the schema and converters.
//
// NOTE: Must call 'global $oscal_roots;' to expose this variable within functions
if (isset($_SESSION["OSCAL-ROOTS"]) && !empty($_SESSION["OSCAL-ROOTS"]) ) {
	$oscal_roots = $_SESSION["OSCAL-ROOTS"];
} else {
	$oscal_roots = array(
		"catalog" => [
			"title" => "OSCAL Catalog", 
			"namespace" => "http://csrc.nist.gov/ns/oscal/1.0", 
			"files" => [
				"schema"   =>   ["web_source" => "https://raw.githubusercontent.com/usnistgov/OSCAL/master/xml/schema/oscal_catalog_schema.xsd"], 
				"json2xml" =>   ["web_source" => "https://raw.githubusercontent.com/usnistgov/OSCAL/master/xml/convert/oscal_catalog_json-to-xml-converter.xsl"],
				"xml2json" =>   ["web_source" => "https://raw.githubusercontent.com/usnistgov/OSCAL/master/json/convert/oscal_catalog_xml-to-json-converter.xsl"]
			] ],
		"profile" => [
			"title" => "OSCAL Profile", 
			"namespace" => "http://csrc.nist.gov/ns/oscal/1.0", 
			"files" => [
				"schema" =>     ["web_source" => "https://raw.githubusercontent.com/usnistgov/OSCAL/master/xml/schema/oscal_profile_schema.xsd"], 
				"json2xml" =>   ["web_source" => "https://raw.githubusercontent.com/usnistgov/OSCAL/master/xml/convert/oscal_profile_json-to-xml-converter.xsl"],
				"xml2json" =>   ["web_source" => "https://raw.githubusercontent.com/usnistgov/OSCAL/master/json/convert/oscal_profile_xml-to-json-converter.xsl"]
			] ],
		"system-security-plan" => [
			"title" => "OSCAL System Security Plan (SSP)", 
			"namespace" => "http://csrc.nist.gov/ns/oscal/1.0", 
			"files" => [
				"schema" =>     ["web_source" => "https://raw.githubusercontent.com/usnistgov/OSCAL/master/xml/schema/oscal_ssp_schema.xsd"], 
				"json2xml" =>   ["web_source" => "https://raw.githubusercontent.com/usnistgov/OSCAL/master/xml/convert/oscal_ssp_json-to-xml-converter.xsl"],
				"xml2json" =>   ["web_source" => "https://raw.githubusercontent.com/usnistgov/OSCAL/master/json/convert/oscal_ssp_xml-to-json-converter.xsl"]
			] ],
		"component-definition" => [
			"title" => "OSCAL Component", 
			"namespace" => "http://csrc.nist.gov/ns/oscal/1.0", 
			"files" => [
				"schema" =>     ["web_source" => "https://raw.githubusercontent.com/usnistgov/OSCAL/master/xml/schema/oscal_component_schema.xsd"], 
				"json2xml" =>   ["web_source" => "https://raw.githubusercontent.com/usnistgov/OSCAL/master/xml/convert/oscal_component_json-to-xml-converter.xsl"],
				"xml2json" =>   ["web_source" => "https://raw.githubusercontent.com/usnistgov/OSCAL/master/json/convert/oscal_component_xml-to-json-converter.xsl"],
			] ] );
	$oscal_roots = Enhance_OSCAL_roots($oscal_roots);
	$_SESSION["OSCAL-ROOTS"] = $oscal_roots;
}


// Every entry in $oscal_additional_files will be downloaded when the user 
//      selects "Update OSCAL Files" from the main menu. 
// Enter the full web URL for "web_source" (just as you would type it into a browser).
// Only enter the filename for "local_file" with no path.
//     It will be stored in the location specified in "OSCAL_LOCAL_FILES"
// Be sure to only specify "web_source" URLs that would result in downloading the raw data.
//     This is especially important for GitHub-hosted files.
// NOTE: Must call 'global $oscal_additional_files;' to expose this within functions
// NOTE: Only use this for files, not listed in $oscal_roots
$oscal_additional_files = array(
//	 "web_source"	=> "",
	);



// For OSCAL elements that can contain a child of itself, such as part, 
//     this determines how deep this program will iterate when recursing.
// If any element appears more than MAX_LEVELS in a full XPATH from root,
//      it will not be handled by the code.
// EXAMPLE for MAX_LEVELS set to 4: 
// 		  HANDLED: <control><part><part><part><part><p>something</p></part></part></part></part></control>
//    NOT HANDLED: <control><part><part><part><part><part><p>something</p></part></part></part></part></part></control>
define("MAX_LEVELS", 4);  // The maximum levels an element (such part) can nest


/* ========================================================================= */
/* === DO NOT MODIFY BELOW THIS POINT === DO NOT MODIFY BELOW THIS POINT === */
/* === DO NOT MODIFY BELOW THIS POINT === DO NOT MODIFY BELOW THIS POINT === */
/* ===                UNINTENDED RESULTS ARE LIKELY                      === */
/* ========================================================================= */
define("SCREEN_WIDTH", "1000px");
define("POPUP_WIDTH", "800px");
define("WRAP_LENGTH", 80);
define("ICON_SIZE", 15);

// STANDARD XPATH QUERIES: All queries are xpath 1.0 unless noted.

// Standard OSCAL XPATH Queries (Metadata & Back-Matter)
define("OSCAL_METADATA", "//metadata");
define("OSCAL_METADATA_TITLE", "//metadata/title");
define("OSCAL_METADATA_VERSION", "//metadata/version");
define("OSCAL_METADATA_DATE", "//metadata/published");
define("OSCAL_METADATA_LAST_MODIFIED", "//metadata/last-modified");
define("OSCAL_METADATA_SENSITIVITY_LABEL", "//metadata/prop[@name='marking'][@ns='fedramp']");


// OSCAL Catalog-Specific XPATH Queries


// OSCAL Profile-Specific XPATH Queries


// OSCAL SSP-Specific XPATH Queries

?>