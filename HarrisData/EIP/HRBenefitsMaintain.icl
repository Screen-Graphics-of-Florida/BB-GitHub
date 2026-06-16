%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*                                                                    *
*  Job: H/R Benefits Stored Procedures                               *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 paymentFrequencyTable      = %table
 coreTable                  = %table
 continuationTable          = %table
 conversionTable            = %table

%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) payment_Frequency_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	paymentFrequencyTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) core_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	coreTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) continuation_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	continuationTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) conversion_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	conversionTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				