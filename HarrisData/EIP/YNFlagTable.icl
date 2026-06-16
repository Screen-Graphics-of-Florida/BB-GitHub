%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Yes/No Selection Lists                                       *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 yn01Table                  = %table
 yn02Table                  = %table
 yn03Table                  = %table
 yn04Table                  = %table
 yn05Table                  = %table
 yn06Table                  = %table
 yn07Table                  = %table
 yn08Table                  = %table
 yn09Table                  = %table
 yn10Table                  = %table
 yn11Table                  = %table
 yn12Table                  = %table
 yn13Table                  = %table
 yn14Table                  = %table
 yn15Table                  = %table
 yn16Table                  = %table
 yn17Table                  = %table
 yn18Table                  = %table
 yn19Table                  = %table
 yn20Table                  = %table

%}

 %{ Yes,No Table Routine Function Calls %}

%FUNCTION(DTW_SQL) yn01_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn01Table)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) yn02_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn02Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn03_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn03Table)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) yn04_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn04Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn05_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn05Table)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) yn06_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn06Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn07_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn07Table)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) yn08_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn08Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
 %FUNCTION(DTW_SQL) yn09_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn09Table)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) yn10_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn10Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn11_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn11Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn12_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn12Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn13_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn13Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn14_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn14Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn15_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn15Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn16_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn16Table)
                   {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn15_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn15Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn17_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn17Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn18_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn18Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn19_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn19Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) yn20_Table_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yn20Table)
			  {call $(pgmLibrary)hsyfvp_w
%}
