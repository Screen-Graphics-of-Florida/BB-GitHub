%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: XML Formating Macros                                        *
*********************************************************************
%}

%include "browserType.icl"

%MACRO_FUNCTION XMLString (INOUT strX) {
  @dtw_replace(strX, "&", "&amp;", strX)
  @dtw_replace(strX, "<", "&lt;", strX)
  @dtw_replace(strX, ">", "&gt;", strX)
  @dtw_replace(strX, "'", "&apos;", strX)
  @dtw_replace(strX, "@", "&#64;", strX)
%}

%MACRO_FUNCTION XMLTag (IN  dBField,
                        IN  label)
{
  @XMLString(dBField)
  @DTW_ASSIGN(text, @DTW_rCONCAT("<", @DTW_rCONCAT(label, @DTW_rCONCAT(">", @DTW_rCONCAT(dBField, @DTW_rCONCAT("</", @DTW_rCONCAT(label, ">")))))))
  $(text)
%}

%MACRO_FUNCTION XMLBeginTag (IN label)
{
  @DTW_ASSIGN(text,  @DTW_rCONCAT("<", @DTW_rCONCAT(label, ">")))
  $(text)
%}

%MACRO_FUNCTION XMLEndTag (IN label)
{
  @DTW_ASSIGN(text, @DTW_rCONCAT("</", @DTW_rCONCAT(label, ">")))
  $(text)
%}

%MACRO_FUNCTION XMLIDTag (IN  dBField,
                          IN  label)
{
  @XMLString(dBField)
  @DTW_ASSIGN(text, @DTW_rCONCAT("<", @DTW_rCONCAT(label, @DTW_rCONCAT("=",  @DTW_rCONCAT("'", @DTW_rCONCAT(dBField, "'>"))))))
  $(text)
%}

%MACRO_FUNCTION XMLInit () {

  @BrowserType(browser)

  %if (browser == "FF")

      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="en" lang="en" xml?ns="http://www.w3.org/1999/xhtml">
      @dtw_assign(cRLF, @dtw_rhextochar("0D25"))
      Content-type: text/xml$(CRLF)$(CRLF)
      Content-encoding: ebcdic $(CRLF)$(CRLF)
      <?xml version="1.0"?>
  %else
       <?xml version="1.0"?>
  %endif
%}
