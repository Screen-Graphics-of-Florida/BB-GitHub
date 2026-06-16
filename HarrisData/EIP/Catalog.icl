%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Catalog                                                      *
**********************************************************************
%}

%FUNCTION(dtw_directcall) Catalog_Selection(INOUT CHAR(64) profileHandle,
                                            INOUT CHAR(2)  dataBaseID,
                                            INOUT CHAR(1)  maintCode,
                                            INOUT CHAR(15) catalog,
                                            INOUT DEC(7,0) recordSequence,
                                            INOUT DEC(7,2) orderSequence,
                                            INOUT CHAR(50) selectDesc,
                                            INOUT CHAR(500) newPage,
                                            INOUT CHAR(500) selectGroupBy,
                                            INOUT CHAR(3000) selectCriteria)
  {%EXEC {HOECTS_W.PGM %}
%}

%FUNCTION(dtw_directcall) Check_Exclusion (INOUT CHAR(64)  profileHandle,
                                                 CHAR(2)   dataBaseID,
                                                 CHAR(15)  catalog,
                                                 DEC(7,0) recordSequence,
                                                 CHAR(500) varField,
                                                 CHAR(7)   checked)
  {%EXEC {HOECXC_W.PGM %}
%}

%FUNCTION(dtw_directcall) Update_Exclusion (INOUT CHAR(64)  profileHandle,
                                                  CHAR(2)   dataBaseID,
                                                  CHAR(15)  catalog,
                                                  DEC(7,0) recordSequence,
                                                  CHAR(32000) edtVar)
  {%EXEC {HOECXU_W.PGM %}
%}