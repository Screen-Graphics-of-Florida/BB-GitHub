%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Item Maintenance Stored Procedures                           *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 kitTable                  = %table
 lotTable                  = %table
 upcBarTable               = %table
 qualityTable              = %table
 partTypeTable             = %table

%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) Kit_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	kitTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) Lot_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	lotTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) UPCBar_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	upcBarTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) Quality_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	qualityTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) PartType_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	partTypeTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
