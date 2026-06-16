<html xsl:version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

         <head>
               <META http-equiv="Content-Type" content="text/html" />
         </head>
         <body>
               <table border="1">
                               <xsl:for-each select="customerlist/customer">
                                       <xsl:sort select="name"/>
                                       <tr><td><xsl:value-of select="name"/></td></tr>
                                       <tr><td><xsl:value-of select="company"/></td></tr>
                                       <tr><td><xsl:value-of select="address1"/></td></tr>
                                       <tr><td><xsl:value-of select="address2"/></td></tr>
                                       <tr><td><xsl:value-of select="address3"/></td></tr>
                                       <tr><td><xsl:value-of select="city"/></td></tr>
                                       <tr><td><xsl:value-of select="state"/></td></tr>
                                       <tr><td><xsl:value-of select="zip"/></td></tr>

                                </xsl:for-each>
               </table>
         </body>
       </html>


