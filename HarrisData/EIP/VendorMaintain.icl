%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Vendor Maintenance Stored Procedures                         *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 federalIDTable            = %table
 vendorStatusTable         = %table

%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) FederalID_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	federalIDTable)
			       {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) VendorStatus_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	vendorStatusTable)
			       {call $(pgmLibrary)hsyfvp_w
%}				
