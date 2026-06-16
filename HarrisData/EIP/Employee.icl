%{
**********************************************************************
*  Copr 1979 2002 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: H/R Employee Tables                                          *
**********************************************************************
%}

%DEFINE {

 %{ Table Routine Variables %}

 autoAssignTable            = %table
 balMethodTable             = %table
 deductionMethodTable       = %table
 directDepositTable         = %table
 employmentTypeTable        = %table
 eeoTable                   = %table
 eicTable                   = %table
 ethnicIdTable              = %table
 exemptTable                = %table
 federalWhTable             = %table
 fitExemptTable             = %table
 genderTable                = %table
 hourunitTable              = %table
 maritalStsTable            = %table
 overtimeMethodTable        = %table
 payFreqTable               = %table
 payTypeTable               = %table
 reclaimResourceTable       = %table
 statutoryEmplTable         = %table
 salaryCodeTable            = %table
 sitExemptTable             = %table
 sitWhCatTable              = %table
 stdDedTable                = %table
 taxCreditTable             = %table
 timeTable                  = %table
 yearTypeTable              = %table

%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) autoAssign_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	autoAssignTable)
			  {call $(pgmLibrary)hsyfvp_w
%}

%FUNCTION(DTW_SQL) balMethod_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	balMethodTable)
			  {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) deductionMethod_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	deductionMethodTable)
			  {call $(pgmLibrary)hsyfvp_w
%}

%FUNCTION(DTW_SQL) directDeposit_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	directDepositTable)
			  {call $(pgmLibrary)hsyfvp_w
%}

%FUNCTION(DTW_SQL) employmentType_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	employmentTypeTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
%FUNCTION(DTW_SQL) eeo_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	eeoTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) eic_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	eicTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) ethnic_Id_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	ethnicIdTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) exempt_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	exemptTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) federal_Wh_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	federalWhTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) fitExempt_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	fitExemptTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) gender_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	genderTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) hourunit_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	hourunitTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) marital_Sts_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	maritalStsTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) overtime_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	overtimeMethodTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) payroll_Freq_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	payFreqTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) payType_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	payTypeTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) reclaimResource_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	reclaimResourceTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) statutory_Empl_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	statutoryEmplTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) salaryCode_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	salaryCodeTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) sitExempt_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	sitExemptTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) sitWhCat_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	sitWhCatTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) stdDed_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	stdDedTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) taxCredit_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	taxCreditTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) time_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	timeTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				

%FUNCTION(DTW_SQL) yearType_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	yearTypeTable)
			  {call $(pgmLibrary)hsyfvp_w
%}				
