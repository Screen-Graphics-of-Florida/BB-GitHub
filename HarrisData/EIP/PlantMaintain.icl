%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Plant Maintenance Stored Procedures                          *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 autoExplodePOTable        = %table
 completedDefaultTable     = %table
 haltErrorTable            = %table
 hoursWorkedTable          = %table
 fixedOverheadTable        = %table
 kanbanDocumentTable       = %table
 kanbanPOTable             = %table
 laborRateTable            = %table
 piecesReworkTable         = %table
 stdMilTable               = %table
 timeEntryTable            = %table
 forecastDateTable         = %table
 forecastSpreadersTable    = %table
 defaultDateAnchorTable    = %table
 updateMfgOrderTable       = %table 
%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) AutoExplodePO_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	autoExplodePOTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) CompletedDefault_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	completedDefaultTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) HaltError_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	haltErrorTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) HoursWorked_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	hoursWorkedTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) AutoExplodePO_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	autoExplodePOTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) FixedOverhead_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	fixedOverheadTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) KanbanDocument_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	kanbanDocumentTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) KanbanPO_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	kanbanPOTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) LaborRateUsage_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	laborRateTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) PiecesRework_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	piecesReworkTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) StdMilitary_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	stdMilTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) TimeEntryFormat_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	timeEntryTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) ForecastDateFormat_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	forecastDateTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) ForecastSpreaders_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	forecastSpreadersTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) DefaultDateAnchor_Query
				(IN 	CHAR(64) profileHandle,
			            CHAR(2)  dataBaseID,
			            CHAR(10) type,
				OUT  	defaultDateAnchorTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) UpdateMfgOrder_Query
				(IN 	CHAR(64) profileHandle,
			            CHAR(2)  dataBaseID,
			            CHAR(10) type,
				OUT  	updateMfgOrderTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
