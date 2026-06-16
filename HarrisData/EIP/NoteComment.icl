%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Note/Comment Stored Procedures                               *
**********************************************************************
%}

%{ Get Alternate Routing Notes %}

%FUNCTION(dtw_directcall) Get_Alternate_Notes(INOUT CHAR(64)     profileHandle,
                                                    CHAR(2)      dataBaseID,
                                                    DEC(3,0)     plantNumber,
                                                    CHAR(15)     itemNumber,
                                                    CHAR(5)      configurationType,
                                                    CHAR(5)      configurationID,
                                                    DEC(3,0)     sequenceNumber,
                                                    CHAR(3)      documentType,
                                                    CHAR(32000)  comment)

  {%EXEC {HPDARN_P.PGM %}
  %}

%{ Get Feature Routing Notes %}

%FUNCTION(dtw_directcall) Get_Feature_Notes(INOUT CHAR(64)     profileHandle,
                                                  CHAR(2)      dataBaseID,
                                                  DEC(3,0)     plantNumber,
                                                  CHAR (15)    feature,
                                                  CHAR (15)    featuredItem,
                                                  DEC(3,0)     sequenceNumber,
                                                  CHAR(3)      documentType,
                                                  CHAR(32000)  comment)

  {%EXEC {HPDFRN_P.PGM %}
  %}

%{ Get Option Routing Notes %}

%FUNCTION(dtw_directcall) Get_Option_Notes(INOUT CHAR(64)     profileHandle,
                                                 CHAR(2)      dataBaseID,
                                                 DEC(3,0)     plantNumber,
                                                 CHAR (15)    option,
                                                 CHAR (15)    feature,
                                                 DEC(3,0)     sequenceNumber,
                                                 CHAR(3)      documentType,
                                                 CHAR(32000)  comment)

  {%EXEC {HPDFRN_P.PGM %}
  %}

%{ Get Routing Notes %}

%FUNCTION(dtw_directcall) Get_Routing_Notes(INOUT CHAR(64)     profileHandle,
                                                  CHAR(2)      dataBaseID,
                                                  DEC(3,0)     plantNumber,
                                                  CHAR (15)    itemNumber,
                                                  DEC(3,0)     sequenceNumber,
                                                  CHAR(3)      documentType,
                                                  CHAR(32000)  comment)

  {%EXEC {HPDRTN_P.PGM %}
  %}

%{ Get Labor In Process Notes %}

%FUNCTION(dtw_directcall) Get_Labor_Notes(INOUT CHAR(64)     profileHandle,
                                                CHAR(2)      dataBaseID,
                                                DEC(3,0)     plantNumber,
                                                CHAR (9)     mfgOrder,
                                                DEC(3,0)     sequenceNumber,
                                                CHAR(3)      documentType,
                                                CHAR(32000)  comment)

  {%EXEC {HSILPN_P.PGM %}
  %}

%{ Get Kanban Notes %}

%FUNCTION(DTW_directcall) Get_Kanban_Comments(INOUT CHAR(64)     profileHandle,
				                                 CHAR(2)      dataBaseID,
                                                    DEC(3,0)     plantNumber,
				      		                     CHAR (15)    itemNumber,
				      		                     CHAR(3)      documentType,
				                                 char(32000)  comment)
  {%EXEC {HSIKBN_P.PGM %}
  %}
