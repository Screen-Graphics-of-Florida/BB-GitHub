%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Salesman Maintenance Stored Procedures                       *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 commissionTypeTable   = %table

%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) Commission_Type_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	commissionTypeTable)
					{call $(pgmLibrary)hsyfvp_w
%}				