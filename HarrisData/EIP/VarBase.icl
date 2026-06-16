%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Variable Base Includes                                      *
*********************************************************************
%}

%Define {	
  genericVarBase    = "?baseVar=@dtw_rurlescseq(baseVar)&amp;portal=@dtw_rurlescseq(portal)&amp;eID=@dtw_rurlescseq(eID)"
  altVarBase        = "?baseVar=@dtw_rurlescseq(altBaseVar)&amp;portal=@dtw_rurlescseq(portal)&amp;eID=@dtw_rurlescseq(eID)"
  searchVarBase     = "&amp;orderBy=@dtw_rurlescseq(orderBy)&amp;wildCardSearch=@dtw_rurlescseq(wildCardSearch)"
  orderByVarBase    = "&amp;orderBy=@dtw_rurlescseq(orderBy)&amp;orderByDisplay=@dtw_rurlescseq(orderByDisplay)"
  wildCardVarBase   = "&amp;wildCardSearch=@dtw_rurlescseq(wildCardSearch)&amp;wildCardDisplay=@dtw_rurlescseq(wildCardDisplay)"
  glDDVarBase       = "&amp;ddReport=@dtw_rurlescseq(ddReport)&amp;ddDescr=@dtw_rurlescseq(ddDescr)&amp;ddCompany=@dtw_rurlescseq(ddCompany)&amp;ddFacility=@dtw_rurlescseq(ddFacility)"
  employeeVarBase   = "&amp;prCompany=@dtw_rurlescseq(prCompany)&amp;prFacility=@dtw_rurlescseq(prFacility)&amp;prEmployee=@dtw_rurlescseq(prEmployee)&amp;hrCompany=@dtw_rurlescseq(hrCompany)&amp;hrEmployee=@dtw_rurlescseq(hrEmployee)"
  hrCoFacVarBase    = "&amp;prCompany=@dtw_rurlescseq(prCompany)&amp;prFacility=@dtw_rurlescseq(prFacility)&amp;hrCompany=@dtw_rurlescseq(hrCompany)"
%}