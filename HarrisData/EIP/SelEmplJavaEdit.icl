%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Java Script Edit For Employee Selection Fields              *
*********************************************************************
%}

   if (

       editNum(document.Chg.fromCompany, 2, 0) &&
       editNum(document.Chg.toCompany, 2, 0) &&
       editNum(document.Chg.fromFacility, 4, 0) &&
       editNum(document.Chg.toFacility, 4, 0) &&
       editNum(document.Chg.fromPrEmpl, 5, 0) &&
       editNum(document.Chg.toPrEmpl, 5, 0) &&
       editNum(document.Chg.fromHrEmpl, 9, 0) &&
       editNum(document.Chg.toHrEmpl, 9, 0) &&
       editFromToAll(document.Chg.fromCompany, document.Chg.toCompany, document.Chg.allCompany,2) &&
       editFromToAll(document.Chg.fromFacility, document.Chg.toFacility, document.Chg.allFacility,4) &&
       editFromToAll(document.Chg.fromLocation, document.Chg.toLocation, document.Chg.allLocation,"A") &&
       editFromToAll(document.Chg.fromDepartment, document.Chg.toDepartment, document.Chg.allDepartment,"A") &&
       editFromToAll(document.Chg.fromPrEmpl, document.Chg.toPrEmpl, document.Chg.allPrEmpl,5) &&
       editFromToAll(document.Chg.fromHrEmpl, document.Chg.toHrEmpl, document.Chg.allHrEmpl,9))