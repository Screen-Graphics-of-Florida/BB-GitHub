%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: This macro holds base stored procedure functions             *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}


 hrCodeTableA         = %table
 hrCodeTableB         = %table
 hrCodeTableC         = %table
 hrCodeTableD         = %table
 hrCodeTableE         = %table
 hrCodeTableF         = %table
 hrCodeTableG         = %table
 hrCodeTableH         = %table
 hrCodeTableI         = %table
 hrCodeTableJ         = %table
 hrCodeTableK         = %table
 hrCodeTableL         = %table
 hrCodeTableM         = %table
 hrCodeTableN         = %table
 hrCodeTableO         = %table
 hrCodeTableP         = %table
 hrCodeTableQ         = %table
 hrCodeTableR         = %table
 hrCodeTableS         = %table
 hrCodeTableT         = %table
 hrCodeTableU         = %table
 hrCodeTableV         = %table
 hrCodeTableW         = %table
 hrCodeTableX         = %table
 hrCodeTableY         = %table
 hrCodeTable1         = %table
 hrCodeTable2         = %table
 hrCodeTable3         = %table
 hrCodeTable4         = %table

 hrCodeTableD2        = %table
 hrCodeTableJ2        = %table
 hrCodeTableM2        = %table
 hrCodeTableO2        = %table
 hrCodeTableQ2        = %table
 hrCodeTableS2        = %table
 hrCodeTableY2        = %table
%}

 %{ Control Table Calls %}
 %FUNCTION(DTW_SQL) hrCode_QueryA
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeA,
				 OUT  hrCodeTableA)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryB
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeB,
				 OUT  hrCodeTableB)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryC
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeC,
				 OUT  hrCodeTableC)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryD
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeD,
				 OUT  hrCodeTableD)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryE
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeE,
				 OUT  hrCodeTableE)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryF
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeF,
				 OUT  hrCodeTableF)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryG
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeG,
				 OUT  hrCodeTableG)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryH
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeH,
				 OUT  hrCodeTableH)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryI
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeI,
				 OUT  hrCodeTableI)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryJ
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeJ,
				 OUT  hrCodeTableJ)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryK
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeK,
				 OUT  hrCodeTableK)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryL
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeL,
				 OUT  hrCodeTableL)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryM
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeM,
				 OUT  hrCodeTableM)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryN
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeN,
				 OUT  hrCodeTableN)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryO
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeO,
				 OUT  hrCodeTableO)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryP
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeP,
				 OUT  hrCodeTableP)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryQ
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeQ,
				 OUT  hrCodeTableQ)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryR
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeR,
				 OUT  hrCodeTableR)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryS
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeS,
				 OUT  hrCodeTableS)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryT
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeT,
				 OUT  hrCodeTableT)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryU
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeU,
				 OUT  hrCodeTableU)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryV
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeV,
				 OUT  hrCodeTableV)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryW
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeW,
				 OUT  hrCodeTableW)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryX
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeX,
				 OUT  hrCodeTableX)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryY
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeY,
				 OUT  hrCodeTableY)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_Query1
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeType1,
				 OUT  hrCodeTable1)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_Query2
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeType2,
				 OUT  hrCodeTable2)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_Query3
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeType3,
				 OUT  hrCodeTable3)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_Query4
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeType4,
				 OUT  hrCodeTable4)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryD2
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeD,
				 OUT  hrCodeTableD2)
				{call $(pgmLibrary)hpecds_w
%}

 %FUNCTION(DTW_SQL) hrCode_QueryJ2
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeJ,
				 OUT  hrCodeTableJ2)
				{call $(pgmLibrary)hpecds_w
%}
 %FUNCTION(DTW_SQL) hrCode_QueryM2
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeM,
				 OUT  hrCodeTableM2)
				{call $(pgmLibrary)hpecds_w
%}
 %FUNCTION(DTW_SQL) hrCode_QueryO2
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeO,
				 OUT  hrCodeTableO2)
				{call $(pgmLibrary)hpecds_w
%}
 %FUNCTION(DTW_SQL) hrCode_QueryO2
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeO,
				 OUT  hrCodeTableO2)
				{call $(pgmLibrary)hpecds_w
%}
 %FUNCTION(DTW_SQL) hrCode_QueryQ2
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeQ,
				 OUT  hrCodeTableQ2)
				{call $(pgmLibrary)hpecds_w
%}
 %FUNCTION(DTW_SQL) hrCode_QueryS2
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeS,
				 OUT  hrCodeTableS2)
				{call $(pgmLibrary)hpecds_w
%}
 %FUNCTION(DTW_SQL) hrCode_QueryY2
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  codeTypeY,
				 OUT  hrCodeTableY2)
				{call $(pgmLibrary)hpecds_w
%}