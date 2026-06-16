%{
**********************************************************************
*  Copr 1979 2002 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Human Resources Transactions Stored Procedures               *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 planHolderTable            = %table

%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) PlanHolder_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	transTypeTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				