%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: XML Formating Macros Remove Special Characters              *
*********************************************************************
%}

%MACRO_FUNCTION XMLTag (INOUT strX,
                        INOUT label) {
  @dtw_replace(strX, "&", "&amp;", strX)
  @dtw_replace(strX, "<", "&lt;", strX)
  @dtw_replace(strX, ">", "&gt;", strX)
  @dtw_replace(strX, "'", "&apos;", strX)
  @XMLTagFormat(strX, label)
%}


