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

 schFreqTable	      = %table
 schDaysTable	      = %table

 ARPYRUTable          = %table
 benefitCodeTable     = %table
 benefitFunctionTable = %table
 benefitTypeTable     = %table
 carrierCodeTable     = %table
 coFacDeductionTable  = %table
 coFacTable     	   = %table
 custContactEventTable= %table
 custContactUserTable = %table
 custUserDefinedTable = %table
 dataCollectTable	   = %table
 dayOfWeekTable  	   = %table
 deptTable            = %table
 designPhaseTable     = %table
 enrollEmplTable      = %table
 enrollDepTable       = %table
 eventTable           = %table
 eventCodeTable       = %table
 hrCodeTable          = %table
 injuryTypeTable      = %table
 localtaxtable        = %table
 jobClassTable        = %table
 kanbanDocumentTable  = %table
 kanbanPolicyTable    = %table
 monthsTable          = %table
 nameFormatTable      = %table
 orderTypeTable       = %table
 orderDetailUserDefinedTable= %table
 orderDetailWorkUserDefinedTable= %table
 orderUserDefinedTable= %table
 orderWorkUserDefinedTable= %table
 planCodeTable        = %table
 prCountryCodeTable   = %table
 salutationTable	   = %table
 suiTable             = %table
 summarytotalTable	   = %table
 supervisorTable      = %table
 suppContactEventTable= %table
 userDefinedTable     = %table
 suppContactUserTable = %table
 wfChoiceParmTable    = %table

%}

 %{ Schedule Job Calls %}

%FUNCTION(DTW_SQL) schFreq_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	schFreqTable)
			       {call $(pgmLibrary)hsyfvp_w
%}

%FUNCTION(DTW_SQL) schDays_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	schDaysTable)
			       {call $(pgmLibrary)hsyfvp_w
%}


 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) Benefit_Function_Query
				(IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				 OUT   benefitFunctionTable)
				{call $(pgmLibrary)hhrbfs_w
%}			
%FUNCTION(DTW_SQL) Benefit_Type_Query
				(IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				 OUT   benefitTypeTable)
				{call $(pgmLibrary)hhrbts_w
%}			
%FUNCTION(DTW_SQL) BenefitCode_Query
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) companyNumber,
                         dec(4,0)  facilityNumber,
				      char(4)  benefitGroup,
				 OUT  benefitCodeTable)
				{call $(pgmLibrary)hpebcs_w
%}
%FUNCTION(DTW_SQL) Carrier_Code_Query
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				 OUT  carrierCodeTable)
				{call $(pgmLibrary)hpecis_w
%}
%FUNCTION(DTW_SQL) CoFac_Deduction_Query
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) companyNumber,
                         dec(4,0) facilityNumber,
				 OUT  coFacDeductionTable)
				{call $(pgmLibrary)hhrcds_w
%}
%FUNCTION(DTW_SQL) CustContactEvent_Query
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				 	   dec(7,0) contactNumber,
				 	   dec(7,0) eventSequence,
				 OUT  custContactEventTable)
				{call $(pgmLibrary)hcrcue_p
%}
%FUNCTION(DTW_SQL) CustContactUser_Query
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				 	   dec(7,0) contactNumber,
				 OUT  custContactUserTable)
				{call $(pgmLibrary)hcrcud_p
%}
%FUNCTION(DTW_SQL) CustUserDefined_Query
			    (IN   dec(7,0) customerNumber,
				 OUT  custUserDefinedTable)
				{call $(pgmLibrary)HHDCUP_P
%}
%FUNCTION(DTW_SQL) DataCollect_Query
			   (IN   CHAR(64) profileHandle,
			         char(2)  dataBaseID,
				OUT   dataCollectTable)
			  {call $(pgmLibrary)hetdcp_w
%}
%FUNCTION(DTW_SQL) Months_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	monthsTable)
			       {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) DayOfWeek_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	dayOfWeekTable)
			       {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) Department_Query
			   (IN   CHAR(64) profileHandle,
			         char(2)  dataBaseID,
				OUT   deptTable)
			  {call $(pgmLibrary)hprdpt_W
%}
%FUNCTION(DTW_SQL) DesignPhase_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	designPhaseTable)
			       {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) EnrollEmpl_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	enrollEmplTable)
			       {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) EnrollDep_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	enrollDepTable)
			       {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) Event_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
				      char(10) startDate,
			         char(10) endDate,
				OUT  	eventTable)
			  {call $(pgmLibrary)HSYCLE_P
%}
%FUNCTION(DTW_SQL) EventCode_Query
				(IN  CHAR(64) profileHandle,
			        char(2)  dataBaseID,
			        char(10) fileName,
				OUT  eventCodeTable)
			  {call $(pgmLibrary)hsyevp_w
%}				
%FUNCTION(DTW_SQL) HrCoFac_Query
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				 OUT  coFacTable)
				{call $(pgmLibrary)hhrcfp_w
%}
 %FUNCTION(DTW_SQL) hrCode_Query
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
                         dec(2,0) hrCompany,
                         char(1)  hrCodeType,
				 OUT  hrCodeTable)
				{call $(pgmLibrary)hpecds_w
%}
%FUNCTION(DTW_SQL) InjuryType_Query
				(IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				      dec(7)  injIllDate,
				OUT   injuryTypeTable)
			  {call $(pgmLibrary)hpeits_w
%}
%FUNCTION(DTW_SQL) JobClass_Query
				(IN  CHAR(64) profileHandle,
				     char(2)  dataBaseID,
				OUT  JobClassTable)
			  {call $(pgmLibrary)hprjbs_w
%}
%FUNCTION(DTW_SQL) KanbanDocument_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	kanbanDocumentTable)
			       {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) KanbanPolicy_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	kanbanPolicyTable)
			       {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) LocalTax_Query
				(IN  CHAR(64) profileHandle,
				     char(2)  dataBaseID,
                        DEC(3,0) cyr,
				OUT  localtaxtable)
			  {call $(pgmLibrary)hprltx_w
%}
%FUNCTION(DTW_SQL) Months_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	monthsTable)
			       {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) NameFormat_Query
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				 OUT  nameFormatTable)
				{call $(pgmLibrary)hhrnfs_w
%}
%FUNCTION(DTW_SQL) OrderType_Query
                   (IN	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
			         char(2)  applicationID,
				 OUT  orderTypeTable)
	                  {call $(pgmLibrary)hhdotp_w
%}
%FUNCTION(DTW_SQL) OrderDetailUDF_Query
                  (IN CHAR(10) fileName,
			          dec(8,0) orderControlNumber,
			          dec(3,0) lineNumber,
				 OUT  orderDetailUserDefinedTable)
				{call $(pgmLibrary)HOEUDU_P
%}
%FUNCTION(DTW_SQL) OrderDetailWorkUDF_Query
			    (IN   dec(8,0) orderControlNumber,
			          dec(3,0) lineNumber,
				 OUT  orderDetailWorkUserDefinedTable)
				{call $(pgmLibrary)HOEUDW_P
%}
%FUNCTION(DTW_SQL) OrderUserDefined_Query
                  (IN CHAR(10) fileName,
			          dec(8,0) orderControlNumber,
				 OUT  orderUserDefinedTable)
				{call $(pgmLibrary)HOEUDF_P
%}
%FUNCTION(DTW_SQL) OrderWorkUserDefined_Query
			    (IN   dec(8,0) orderControlNumber,
				 OUT  orderWorkUserDefinedTable)
				{call $(pgmLibrary)HOEUHW_P
%}
%FUNCTION(DTW_SQL) PlanCode_Query
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				 OUT  planCodeTable)
				{call $(pgmLibrary)hpepls_w
%}
%FUNCTION(DTW_SQL) PrCountryCode_Query
				(IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				 OUT   prCountryCodeTable)
			   {call $(pgmLibrary)hhrctp_w
%}			
%FUNCTION(DTW_SQL) Salutation_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	salutationTable)
			       {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) SUI_Query
				(IN  CHAR(64) profileHandle,
				     char(2)  dataBaseID,
                        DEC(2,0) co,
                        DEC(4,0) fac,
				OUT  suiTable)
			  {call $(pgmLibrary)hprsui_w
%}
%FUNCTION(DTW_SQL) SummaryTotal_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	summarytotalTable)
			       {call $(pgmLibrary)hsyfvp_w
%}
%FUNCTION(DTW_SQL) Supervisor_Query
				(IN  CHAR(64) profileHandle,
				     char(2)  dataBaseID,
				     char(1)  eventCode,
				OUT  supervisorTable)
			  {call $(pgmLibrary)hettms_w
%}
%FUNCTION(DTW_SQL) SuppContactEvent_Query
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				 	   dec(7,0) contactNumber,
				 	   dec(7,0) eventSequence,
				 OUT  suppContactEventTable)
				{call $(pgmLibrary)hsrcue_p
%}
%FUNCTION(DTW_SQL) UserDefined_Query
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				 	   CHAR(10) fileName,
                         char(10) eventCode,
				 OUT  userDefinedTable)
				{call $(pgmLibrary)hsyudf_p
%}
%FUNCTION(DTW_SQL) SupplierContactUser_Query
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				 	   dec(7,0) contactNumber,
				 OUT  suppContactUserTable)
				{call $(pgmLibrary)hsrcud_p
%}
%FUNCTION(DTW_SQL) WFChoiceParm_Query
			   (IN   CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				 	   CHAR(30) wfChoice,
				 OUT  wfChoiceParmTable)
				{call $(pgmLibrary)hwfchf_p
%}
%FUNCTION(DTW_SQL) MS230A_Query
				(IN  CHAR(64) profileHandle,
			        CHAR(2)  dataBaseID,
					  DEC(3,0) plantNumber,
					  CHAR(15) itemNumber,
				OUT  headerTable)
			  {call $(pgmLibrary)HMS230_P
%}				