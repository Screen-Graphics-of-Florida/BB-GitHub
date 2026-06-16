%{
**********************************************************************
*  Copr 1979 2002 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: H/R Company/Facility Stored Procedures                       *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 accountDefaultTable        = %table
 codeDefaultTable           = %table
 classDefaultTable          = %table
 dateDefaultTable           = %table
 deptDefaultTable           = %table
 detailSummaryTable         = %table
 entryFormTable             = %table
 hoursFormatTable           = %table
 jobDefaultTable            = %table
 overflowAdviceTable        = %table
 payCodeTable               = %table
 processStatusTable         = %table
 rateDefaultTable           = %table
 reportOptionTable          = %table
 shiftDefaultTable          = %table
 statementCodeTable         = %table
%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) account_Default_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	accountDefaultTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) code_Default_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	codeDefaultTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) class_Default_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	classDefaultTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) date_Default_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	dateDefaultTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) dept_Default_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	deptDefaultTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) detail_Summary_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	detailSummaryTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) entry_Form_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	entryFormTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) hours_Format_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	hoursFormatTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) job_Default_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	jobDefaultTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) overflow_Advice_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	overflowAdviceTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) pay_Code_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   dec(2,0) company,
					   dec(4,0) facility,
				OUT  	payCodeTable)
			  {call $(pgmLibrary)hhrpys_w
%}				

%FUNCTION(DTW_SQL) process_Status_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	processStatusTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) rate_Default_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	rateDefaultTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) report_Option_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	reportOptionTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) shift_Default_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	shiftDefaultTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) statement_Code_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	statementCodeTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				