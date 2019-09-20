<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  version="1.0"
  xmlns:xhtml="http://www.w3.org/1999/xhtml"
  xmlns:oscal="http://csrc.nist.gov/ns/oscal/1.0"
  exclude-result-prefixes="oscal xhtml"
  >

  <xsl:output method="html" />
  
  <xsl:template match="/">
      
    <span style="font-size:1.5em; font-weight:bold; color:red;"><xsl:value-of select="descendant::oscal:metadata/oscal:title"/></span>
    <br /><br />
    
    <xsl:for-each select="//oscal:group">
      <xsl:if test="descendant::oscal:control">
        <div>
        <div style='font-weight:bold; font-size: 1.2em; background-color: black; color:white; width:900px; '>
          <xsl:text>&#160;</xsl:text>
          <xsl:value-of select="oscal:title"/>
          <xsl:text>&#160;</xsl:text>
        </div>
        <xsl:text>&#160;&#160;(</xsl:text>
        <span id="prompt-{@id}" onclick="toggle_visibility('{@id}')" style="cursor: pointer; font-size: 1em; color:blue; font-style: italic;">Show</span>
        <xsl:text>&#160;</xsl:text>
        <xsl:value-of select="count(oscal:control)"/>
        Controls) 
        <xsl:text>&#160;</xsl:text>
        <span id="add-{@id}" onclick="addcontrol('{@id}')" style="cursor: pointer; font-size: 0.8em; color:blue; font-style: italic;">[Add Control]</span>
        </div>
        <div id="div-{@id}" style="max-height: 0; transition: all 2s ease; overflow:hidden;"> <!-- hidden="true"  -->
          
          <xsl:for-each select="descendant::oscal:control">
            <xsl:text>&#160;&#160;</xsl:text>
            <xsl:if test="descendant::oscal:subcontrol">
              <div>
              <div id="add-{@id}" onclick="show_control('{@id}')" style="cursor: pointer; font-size: 1em; color:black; background-color: #c0c0c0; font-style: normal; font-weight: bold; width:900px;">
                <xsl:value-of select="oscal:prop[@class='label']"/>
                <xsl:text>&#160;</xsl:text>
                <xsl:value-of select="oscal:title"/>
              </div>
              <xsl:text>&#160;&#160;(</xsl:text>
              <span id="prompt-{@id}" onclick="toggle_visibility('{@id}')" style="cursor: pointer; font-size: 1em; color:blue; font-style: italic;">Show</span>
              <xsl:text>&#160;</xsl:text>
              <xsl:value-of select="count(oscal:subcontrol)"/>
              Sub-Controls) <xsl:text>&#160;</xsl:text>
              <xsl:text>&#160;</xsl:text>
              <span id="add-{@id}" onclick="addcontrol('{@id}')" style="cursor: pointer; font-size: 0.8em; color:blue; font-style: italic;">[Add Sub-Control]</span>
              </div>
              
              <div id="div-{@id}" style="max-height: 0px; transition: all 2s ease; overflow:hidden;"> <!-- hidden="true"  -->

                <xsl:for-each select="descendant::oscal:subcontrol">
                  <xsl:text>&#160;&#160;&#160;&#160;</xsl:text>
                  <span id="add-{@id}" onclick="show_control('{@id}')" style="cursor: pointer; font-size: 1em; color:blue; font-style: normal;">
                    <xsl:value-of select="oscal:prop[@class='label']"/>
                    <xsl:text>&#160;</xsl:text>
                    <xsl:value-of select="oscal:title"/>
                  </span>
                  <br/>
                </xsl:for-each>
              </div>
            </xsl:if>

            <xsl:if test="not(descendant::oscal:subcontrol)">
              <div>
              <div id="add-{@id}" onclick="show_control('{@id}')" style="cursor: pointer; font-size: 1em; color:black; background-color: #c0c0c0; font-style: normal; font-weight: normal; width:900px;">
                
                <xsl:value-of select="oscal:prop[@class='label']"/>
                <xsl:text>&#160;</xsl:text>
                <xsl:value-of select="oscal:title"/>
              </div>
              <xsl:text>&#160;&#160;(No Sub-Controls)&#160;&#160;</xsl:text>
              <span id="add-{@id}" onclick="addcontrol('{@id}')" style="cursor: pointer; font-size: 0.8em; color:blue; font-style: italic;">[Add Sub-Control]</span>
              </div>
            </xsl:if>
          </xsl:for-each>

        </div>
      </xsl:if>

      <xsl:if test="not(descendant::oscal:control)">
        <span style='font-weight:normal; font-size: 1.2em;'><xsl:value-of select="oscal:title"/></span>
        <xsl:text>&#160;&#160;(No Controls)&#160;&#160;</xsl:text>
        <span id="add-{@id}" onclick="addcontrol('{@id}')" style="cursor: pointer; font-size: 0.8em; color:blue; font-style: italic;">[Add Control]</span>
        <br />
      </xsl:if>
      <br />
    </xsl:for-each>
  </xsl:template>
  
</xsl:stylesheet>