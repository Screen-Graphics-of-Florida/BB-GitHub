%{******************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc.*
* All rights reserved. This work contains trade secrets.           *
*                  				                                    *
*  Job: Pricing Detail Functions                                   *
********************************************************************
%}
%DEFINE {

 %{ Table Routine Variables %}

  mdCol                   = %table

%}

%FUNCTION(dtw_directcall) Rtv_Pricing_Definition (INOUT CHAR(64)    profileHandle,
                                                  INOUT CHAR(32000) edtVar)
{%EXEC {HOEPSM_W.PGM %}
%}

%FUNCTION(dtw_sql) Rtv_Pricing_Categories (IN CHAR(64)    profileHandle,
                                              DEC(2,0)    pricingLevel,
                                              OUT         mdCol)
{call $(pgmLibrary)HOEPSM_P
%}
