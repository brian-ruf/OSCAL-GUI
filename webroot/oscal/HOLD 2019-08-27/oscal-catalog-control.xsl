<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  version="1.0"
  xmlns:xhtml="http://www.w3.org/1999/xhtml"
  xmlns:oscal="http://csrc.nist.gov/ns/oscal/1.0"
  exclude-result-prefixes="oscal xhtml"
  >
  
  <xsl:output method="html" />

  <xsl:template match="/">
    <xsl:apply-templates select="//oscal:control[@id='*id*'] | //oscal:subcontrol[@id='*id*']" />
  </xsl:template>
    
  <xsl:template match="//oscal:control[@id='*id*'] | //oscal:subcontrol[@id='*id*']">
      
    <div class='control-statement' style='font-weight:bold; font-size: 1.6em; background-color: black; color:white; width:auto; '>
      <xsl:value-of select="ancestor::oscal:group/oscal:title" />
    </div>

    <xsl:if test="local-name(.)='control'">
      <div class='control-statement' style='font-weight:bold; font-size: 1.2em; background-color: #c0c0c0; color:black; width:auto; '>
        <xsl:value-of select="oscal:prop[@class='label']" />
        <xsl:text>&#160;</xsl:text>
        <xsl:value-of select="oscal:title" />
      </div>
    </xsl:if>

    <xsl:if test="local-name(.)='subcontrol'">
      <div class='control-statement' style='font-weight:bold; font-size: 1.4em; background-color: #c0c0c0; color:black; width:auto; '>
        <xsl:value-of select="../oscal:prop[@class='label']"/>
        <xsl:text>&#160;</xsl:text>
        <xsl:value-of select="../oscal:title" />
      </div>
      
      <div class='control-statement' style='margin: 4px; font-weight:bold; font-size: 1.4em; background-color: white; color:black; width:auto; '>
        <xsl:value-of select="oscal:prop[@class='label']"/>
      <xsl:text>&#160;</xsl:text>
      <xsl:value-of select="oscal:title" />
      </div>
    </xsl:if>
    
    <div class='control-statement' style='margin: 4px; font-weight:normal; font-size: 1.2em; background-color: white; color:black; width:auto; '>
      <xsl:for-each select="oscal:part[@class='statement']/oscal:p">
        <p><xsl:value-of select="self::node()" /></p>
      </xsl:for-each>
      
      <xsl:for-each select="oscal:part[@class='statement']/oscal:part[@class='item']">
        <table class='control-statement'>
          
          <td>
            <xsl:text>&#160;&#160;</xsl:text>
            <xsl:value-of select="oscal:prop[@class='label']" />
            <xsl:text>&#160;&#160;</xsl:text>
          </td>
          <td>
            <xsl:for-each select="./oscal:p">
              <xsl:value-of select="self::node()" /><br />
            </xsl:for-each>

            <xsl:for-each select="./oscal:part[@class='item']">
              <table class='control-statement'>
                
                <td>
                  <xsl:text>&#160;&#160;</xsl:text>
                  <xsl:value-of select="oscal:prop[@class='label']" />
                  <xsl:text>&#160;&#160;</xsl:text>
                </td>
                <td>
                  <xsl:for-each select="./oscal:p">
                    <xsl:value-of select="self::node()" /><br />
                  </xsl:for-each>
                </td>
              </table>
            </xsl:for-each>
          </td>
        </table>
      </xsl:for-each>
    </div>
  </xsl:template>
  
  

</xsl:stylesheet>