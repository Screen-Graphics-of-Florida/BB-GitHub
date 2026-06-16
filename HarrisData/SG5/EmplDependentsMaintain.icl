%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Employee Spouse/Dependents Stored Procedures                 *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 classificationTable        = %table
 genderTable                = %table
 provinceTable              = %table
 relationshipTable          = %table

%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) Classification_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	classificationTable)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) Gender_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	genderTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) Province_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	provinceTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
				
%FUNCTION(DTW_SQL) Relationship_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	relationshipTable)
			  {call $(pgmLibrary)hsyfvp_w
%}
