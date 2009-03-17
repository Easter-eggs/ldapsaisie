<?xml version='1.0'?> 
<xsl:stylesheet  
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:fo="http://www.w3.org/1999/XSL/Format"
    version="1.0"> 

<xsl:import href="/usr/share/xml/docbook/stylesheet/nwalsh/htmlhelp/htmlhelp.xsl"/>
<xsl:param name="chunk.section.depth" select="4"/>
<xsl:param name="chunk.first.sections" select="1"/>

<xsl:param name="html.stylesheet" select="'../../../styles/LS.css'"/>

<xsl:param name="admon.graphics" select="1"/>
<xsl:param name="admon.graphics.path">../../../images/</xsl:param>
<xsl:param name="admon.graphics.extension">.png</xsl:param>

<xsl:param name="section.autolabel" select="1"></xsl:param>
<xsl:param name="toc.section.depth">5</xsl:param>

</xsl:stylesheet>  
