%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*                                                                    *
*  Job: Software Update Stored Procedures                            *
**********************************************************************
%}

%FUNCTION(dtw_directcall)  RtvObjDesc(IN    CHAR(10) user,
                                      IN    CHAR(7)  type,
                                      INOUT CHAR(50) fieldDesc)
  { %EXEC {CSUROD_W.PGM %}
  %}

%DEFINE {

 %{ Table Routine Variables %}

 productionFileOperTable   = %table
 runStatusTable   = %table
 runOnceTable   = %table
 typeOfProcessTable   = %table
 ileTypeTable   = %table
 actGrpTable   = %table

%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) Production_File_Oper_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	productionFileOperTable)
					{call $(pgmLibrary)hsyfvp_w
%}

%FUNCTION(DTW_SQL) Run_Status_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	runStatusTable)
					{call $(pgmLibrary)hsyfvp_w
%}

%FUNCTION(DTW_SQL) Run_Once_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	runOnceTable)
					{call $(pgmLibrary)hsyfvp_w
%}

%FUNCTION(DTW_SQL) Type_Of_Process_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	typeOfProcessTable)
					{call $(pgmLibrary)hsyfvp_w
%}

%FUNCTION(DTW_SQL) ILE_Type_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	ileTypeTable)
					{call $(pgmLibrary)hsyfvp_w
%}

%FUNCTION(DTW_SQL) Act_Grp_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	actGrpTable)
					{call $(pgmLibrary)hsyfvp_w
%}