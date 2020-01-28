<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    version="1.0"
    xmlns:o="http://csrc.nist.gov/ns/oscal/1.0"
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:meta="m:meta"
    exclude-result-prefixes="o">

    <meta:metadata>
        <meta:title>FedRAMP Control Information Summary</meta:title>
        <title-short>FedRAMP CIS</title-short>
        <description><p>This generates the FedRAMP Control Information Summary.</p></description>
        <author>Brian J. Ruf, CISSP, CCSP, PMP</author>
        <last-modified>2019-11-12Z</last-modified>
        <oscal-required root='system-security-plan'/>
        <function type='display'/>
    </meta:metadata>

    <xsl:output indent="yes"
        method="html"
        omit-xml-declaration="yes" />

    <xsl:variable name="fedramp-values">fedramp_values.xml</xsl:variable>
    <xsl:variable name="vMetadata" select="document('')/*/meta:metadata"/>
    <xsl:variable name="baseline-level" select="/*/o:system-characteristics/o:security-sensitivity-level"/>
    <xsl:variable name="baseline-file" select="document($fedramp-values)/*/o:baselines/o:file[@id=$baseline-level]/@href"/>
    <xsl:variable name="csp-name" select="/*/o:metadata/o:party[@id='party-csp']/o:org/o:org-name" />
    <xsl:variable name="csp-short-name" select="/*/o:metadata/o:party[@id='party-csp']/o:org/o:short-name" />
    <xsl:variable name="sensitivty-level" select="document($fedramp-values)/*/o:security-sensitivity-level/o:value[@id=$baseline-level]/@label" />
    <xsl:variable name="system-name" select="/*/o:system-characteristics/o:system-name" />

    <xsl:template match="/">
        <html>
            <head>
                <xsl:if test="boolean($csp-short-name)">
                    <title><xsl:value-of select="$csp-short-name"/>FedRAMP CIS</title>
                </xsl:if>
                <xsl:if test="not(boolean($csp-short-name))">
                    <title><xsl:value-of select="$csp-name"/>FedRAMP CIS</title>
                </xsl:if>
                <link rel="stylesheet" type="text/css" media="all" href="fedramp.css"/>
            </head>
            <body>
                <header>
                    <div style='width:100%; color: red;'><xsl:value-of select="$csp-name"/></div>
                    <div style='width:100%; font-size: 0.7em; color: red;'>
                            (<xsl:value-of select="/*/o:system-characteristics/o:system-id[@identifier-type='https://fedramp.gov']"/>) 
                        <xsl:value-of select="/*/o:system-characteristics/o:system-name"/>
                        [ <xsl:value-of select="$sensitivty-level"/> ]
                        <!--[ <xsl:apply-templates select="/*/system-characteristics/security-sensitivity-level" /> ]-->
                    </div>
                </header>
                <h2><xsl:value-of select="$sensitivty-level"/>&#160;<xsl:value-of select="$vMetadata/meta:title"/></h2>
                
                <xsl:call-template name="build-compliance-table"/>
                

                <br/><br/><br/><br/><br/><br/>
                <footer id='footer-area' class='site-footer' style=' position: fixed; left: 0; bottom: 0; width: 100%; background-color: black;'>
                    
                    <div style='text-align: center; align: center; margin: 0 auto; font-size: 20px;'>
                        <xsl:apply-templates select="/*/o:metadata/o:prop[@name='marking'][@ns='fedramp']" />
                    </div>
                </footer>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="o:metadata/o:title">
        <h2>
          <xsl:apply-templates/>
        </h2>
    </xsl:template>

    <xsl:template match="o:security-sensitivity-level">
        -<xsl:value-of select="document($fedramp-values)/*/o:security-sensitivity-level/o:value[@id=current()]/@label"/>-
    </xsl:template>

    <xsl:template name="build-compliance-table">

        <div>
            <xsl:text>MISSING: </xsl:text><xsl:value-of select="count(document($baseline-file)//o:control[not(@id = $controls/@control-id)])" />
            <xsl:text>&#160;&#160;</xsl:text>
            <xsl:text>PROVIDED: </xsl:text><xsl:value-of select="count(document($baseline-file)//o:control[@id = $controls/@control-id])" />
        </div>
        
        <div style="margin: 0 auto;">
            <table class="review" style="margin: 0 auto; text-align:center;">
            <tr class="thead">
                <th class="thead" rowspan="2">Control ID</th>
                <th class="thead" colspan="{ count(document($fedramp-values)/*/o:implementation-status/o:value) }">Implementation Status</th>
                <th class="thead" colspan="{ count(document($fedramp-values)/*/o:control-origination/o:value) }">Control Origination</th>
            </tr>
            <tr>
                <xsl:for-each select="document($fedramp-values)/*/o:implementation-status/o:value/@label-short">
                    <th class="thead" style="font-size:0.7em">  <!--class="subhead"-->
                       <xsl:value-of select="."/>
                    </th>
                </xsl:for-each>
                <xsl:for-each select="document($fedramp-values)/*/o:control-origination/o:value/@label-short">
                    <th class="thead" style="font-size:0.7em">
                        <xsl:value-of select="."/>
                    </th>
                </xsl:for-each>
            </tr>
            
            <xsl:variable name="control-implementation" select="/*/o:control-implementation"/>
            <xsl:for-each select="document($baseline-file)//o:control/o:prop[@name='label']">
                <xsl:variable name="control-id" select="./../@id"/>
                <tr>
                    <td><xsl:value-of select="."/></td>
                    <xsl:for-each select="document($fedramp-values)/*/o:implementation-status/o:value">
                        <td>
                            <xsl:if test="./@id=$control-implementation/o:implemented-requirement[@control-id=$control-id]/o:prop[@name='implementation-status']" >
                                <xsl:if test="./@id='planned'" >
                                    <span>
                                        <xsl:attribute name="title">
                                            <xsl:value-of select="$control-implementation/o:implemented-requirement[@control-id=$control-id]/o:prop[@name='planned-completion-date']" />
                                            <xsl:text>
</xsl:text>
                                            <xsl:value-of select="$control-implementation/o:implemented-requirement[@control-id=$control-id]/o:annotation[@name='planned']/o:remarks" />
                                        </xsl:attribute>
                                        <xsl:text>X</xsl:text>
                                    </span>
                                </xsl:if>
                                <xsl:if test="not(./@id='planned')" >
                                    <xsl:text>X</xsl:text>
                                </xsl:if>
                            </xsl:if>
                        </td>
                    </xsl:for-each>
                    <xsl:for-each select="document($fedramp-values)/*/o:control-origination/o:value">
                        <td>
                            <xsl:if test="./@id=$control-implementation/o:implemented-requirement[@control-id=$control-id]/o:prop[@name='control-origination']" >
                                <xsl:text>X</xsl:text>
                            </xsl:if>
                        </td>
                    </xsl:for-each>
                </tr>
            </xsl:for-each>
            
            <!--<xsl:apply-templates select="document($baseline-file)//control/prop[@name='label'][not(@ns)]" mode="list-control"/>-->

        </table>
        </div>
    </xsl:template>
    
    <xsl:variable name="controls" select="/*/o:control-implementation/o:implemented-requirement"/>
    


</xsl:stylesheet>