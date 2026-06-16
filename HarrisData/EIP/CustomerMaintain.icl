%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Customer Maintenance Stored Procedures                       *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 invoiceMethodTable      = %table
 printDetSumTable        = %table

%}

 %{ Table Routine Function Calls %}

			
%FUNCTION(DTW_SQL) Invoice_Method_Query
				(IN  	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	invoiceMethodTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) Print_DetSum_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	printDetSumTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				