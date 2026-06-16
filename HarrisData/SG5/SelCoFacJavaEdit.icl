%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Java Script Edit For H/R Selection Fields                   *
*********************************************************************
%}

   if (editNum(document.Chg.fromCompany, 2, 0) &&
       editNum(document.Chg.toCompany, 2, 0) &&
       editNum(document.Chg.fromFacility, 4, 0) &&
       editNum(document.Chg.toFacility, 4, 0) &&
       editFromToAll(document.Chg.fromCompany, document.Chg.toCompany, document.Chg.allCompany,2) &&
       editFromToAll(document.Chg.fromFacility, document.Chg.toFacility, document.Chg.allFacility,4) &&
   return true;