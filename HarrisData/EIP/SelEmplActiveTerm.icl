%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Employee Report Selection, Require Active/Terminated        *
*********************************************************************
%}

  if (document.Chg.activeEmpl.checked==false && document.Chg.terminatedEmpl.checked==false)
      {alert("Selection required for Select Employees");
       return false;}