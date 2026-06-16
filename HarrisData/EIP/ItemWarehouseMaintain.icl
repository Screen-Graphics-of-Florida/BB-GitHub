%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Item Maintenance Stored Procedures                           *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 abcClassTable             = %table
 abcOverrideTable          = %table
 roundDecimalTable         = %table

%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) ABCClass_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	abcClassTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) ABCOverride_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	abcOverrideTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) RoundDecimal_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	roundDecimalTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
