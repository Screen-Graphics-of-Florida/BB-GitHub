%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Stock Location Maintenance Stored Procedures                 *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 priorityTable     = %table

%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) Priority_Rating_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	priorityTable)
			  {call $(pgmLibrary)hsyfvp_w
%}