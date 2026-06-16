%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Item/Plant Maintenance Stored Procedures                     *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 partTypeTable             = %table
 backflushTable            = %table
 orderPolicyTable          = %table
 leadTimeTable             = %table
 mrpCalcCodeTable          = %table
 scheduleForecastTable     = %table
 scheduleDemandTable       = %table
 kanbanFlowTable           = %table
 ecnComponentTable         = %table
 abcClassTable             = %table
 abcOverrideTable          = %table

%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) PartType_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	partTypeTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) Backflush_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	backflushTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) OrderPolicy_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	orderPolicyTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) LeadTime_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	leadTimeTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) MRPCalcCode_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	mrpCalcCodeTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) ScheduleForecast_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	scheduleForecastTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) ScheduleDemand_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	scheduleDemandTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) KanbanFlow_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	kanbanFlowTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) ECNComonent_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	ecnComponentTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) ABCClass_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	abcClassTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) ABCOverride_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(10) type,
				OUT  	abcOverrideTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				