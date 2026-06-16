%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: User-Defined Column Maintenance Stored Procedures            *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 userDefFldTable         = %table

%}

 %{ Table Routine Function Calls %}

			
%FUNCTION(DTW_SQL) UserDef_FldType_Query
				(IN  	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	userDefFldTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				