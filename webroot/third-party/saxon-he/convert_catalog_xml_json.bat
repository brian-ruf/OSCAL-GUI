REM exec("xmllint --noout ~/desktop/test.xml 2>&1", $retArr, $retVal);


REM CATALOF XML2JSON
exec("java -jar saxon9h3.jar -s:%1 -o:%2 -xsl:./oscal/oscal_profile_xml-to-json-converter.xsl json-indent=yes 2>&1", $retArr, $retVal);
