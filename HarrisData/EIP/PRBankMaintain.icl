%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Payroll Bank Maintenance Stored Procedures                   *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 sortSequenceTable          = %table
 payRateOptionTable         = %table
 dirDepMethodTable          = %table
 immedOriginTable           = %table
 preNoteTable               = %table
 fillRecordsTable           = %table
 nameOptionTable            = %table

%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) Sort_Sequence_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	sortSequenceTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
				
%FUNCTION(DTW_SQL) PayRate_Option_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	payRateOptionTable)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) DirDep_Method_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	dirDepMothodTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) Immediate_Origin_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	immedOriginTable)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) PreNote_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	preNoteTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) Fill_Records_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	fillRecordsTable)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) Name_Option_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	nameOptionTable)
			  {call $(pgmLibrary)hsyfvp_w
%}