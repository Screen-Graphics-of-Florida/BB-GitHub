%{
**********************************************************************
*  Copr 1979 2002 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*                                                                    *
*  Job: Human Resources Transactions Stored Procedures               *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 hrCodeTableJ2              = %table
 hrCodeTableM2              = %table
 hrCodeTableQ2              = %table
 hrCodeTableS2              = %table
 hrCodeTableY2              = %table
 payrollFreqTable           = %table
 payrollFreqTable2          = %table
 payrollFreqTable3          = %table
 payrollFreqTable4          = %table
 payrollTypeTable           = %table
 payrollTypeTable2          = %table
 transTypeTable             = %table

%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) Payroll_Freq_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	payrollFreqTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) Payroll_Freq_Query2
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	payrollFreqTable2)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) Payroll_Freq_Query3
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	payrollFreqTable3)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) Payroll_Freq_Query4
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	payrollFreqTable4)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) Payroll_Type_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	payrollTypeTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) Payroll_Type_Query2
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	payrollTypeTable2)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) TransType_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
				OUT  	transTypeTable)
			  {call $(pgmLibrary)hpetcs_w
%}				
