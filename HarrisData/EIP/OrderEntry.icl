%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Order Entry Stored Procedures                                *
**********************************************************************
%}
%INCLUDE "EdtVar.icl"

%DEFINE {

 oe_sec_01          = ""
 oe_sec_02          = ""
 oe_sec_03          = ""
 oe_sec_04          = ""
 oe_sec_05          = ""
 oe_sec_06          = ""
 oe_sec_07          = ""
 oe_sec_08          = ""
 oe_sec_09          = ""
 oe_sec_10          = ""
 oe_sec_11          = ""
 oe_sec_12          = ""
 oe_sec_13          = ""
 oe_sec_14          = ""
 oe_sec_15          = ""
 totalType          = ""
 H1ORD#             = ""
 H1TURN             = ""
 turnaround         = ""
 H1MNCD             = ""
 orderStatus        = ""
 orderBusy          = ""
 H1RQDT             = ""
 numberOfItems      = ""
 optionItemCnt      = ""
 hdrTrlCmtCnt       = ""
 emailFaxCnt        = ""
 fixedPrcOptCnt     = ""
 orderTotal         = ""
 weightTotal        = ""
 H1SHTO             = ""
 H1MOA              = ""
 H1MOAD             = ""
 shipToName         = ""
 shipToAdrOne       = ""
 shipToAdrTwo       = ""
 shipToAdrThree     = ""
 shipToCity         = ""
 shipToState        = ""
 shipToZip          = ""
 shipToWhs          = ""
 H1DSHP             = ""
 dropShipName       = ""
 dropShipAdrOne     = ""
 dropShipAdrTwo     = ""
 dropShipAdrThree   = ""
 dropShipCity       = ""
 dropShipState      = ""
 dropShipZip        = ""
 dropShip           = ""
 H1BLTO             = ""
 billToName         = ""
 billToAdrOne       = ""
 billToAdrTwo       = ""
 billToAdrThree     = ""
 billToCity         = ""
 billToState        = ""
 billToZip          = ""
 H1CONT             = ""
 contactName        = ""
 H1ORTY             = ""
 H1CURT             = ""
 orderTypeDesc      = ""
 flag_01            = ""
 flag_02            = ""
 flag_03            = ""
 flag_04            = ""
 flag_05            = ""
 flag_06            = ""
 flag_07            = ""
 flag_08            = ""
 flag_09            = ""
 flag_10            = ""
 flag_11            = ""
 flag_12            = ""
 flag_13            = ""
 flag_14            = ""
 flag_15            = ""
 flag_16            = ""
 flag_17            = ""
 flag_18            = ""
 flag_19            = ""
 flag_20            = ""
 orderDate          = ""
 stateTax           = ""
 countyTax          = ""
 cityTax            = ""
 local1Tax          = ""
 local2Tax          = ""
 local3Tax          = ""
 freightChg         = ""
 specialChg         = ""
 payCnt             = ""
 userCustAlpha1     = ""
 userCustAlpha2     = ""
 userCustAlpha3     = ""
 userCustAlpha4     = ""
 userCustAlpha5     = ""
 userDateDesc1      = ""
 userDateDesc2      = ""
 userDateDesc3      = ""
 userAlphaDesc1     = ""
 userAlphaDesc2     = ""
 userAlphaDesc3     = ""
 userAlphaDesc4     = ""
 userAlphaDesc5     = ""
 userAlphaDesc6     = ""
 userAlphaDesc7     = ""
 userAlphaDesc8     = ""
 userAlphaDesc9     = ""
 userAlphaDesc0     = ""
 userNumDesc1       = ""
 userNumDesc2       = ""
 userNumDesc3       = ""
 userNumDesc4       = ""
 userNumDesc5       = ""
 userDtlAlphaDesc1  = ""
 userDtlAlphaDesc2  = ""
 userDtlAlphaDesc3  = ""
 userDtlAlphaDesc4  = ""
 userDtlAlphaDesc5  = ""
 userDtlAlphaDesc6  = ""
 userDtlAlphaDesc7  = ""
 userDtlAlphaDesc8  = ""
 userDtlAlphaDesc9  = ""
 userDtlAlphaDesc0  = ""
 userDtlNumDesc1    = ""
 userDtlNumDesc2    = ""
 userDtlNumDesc3    = ""
 userDtlNumDesc4    = ""
 userDtlNumDesc5    = ""
 allowTaxOverride   = ""
 useProdGroup       = ""
 allowPOGeneration  = ""
 genPOType          = ""
 dspLinkedPOTab     = ""
 errLinkedPOTab     = ""
 autoAssignPO       = ""
 refNbrReq          = ""
 refNumber          = ""
 allowOverShip      = ""
 overShipPct        = ""
 storeNbrReq        = ""
 shippedNotInv      = ""
 custItemFlag       = ""
 vendCustItemsFound = ""
 multShipTo         = ""
 multShipVia        = ""
 hdrError           = ""
 lineError          = ""
 errPaymentTab      = ""
 allowPaymentTab    = ""
 creditCardReq      = ""
 checkCCL           = ""
 pastDueInv         = ""
 openCount          = ""
 shipCount          = ""
 firstOrder         = ""
 lastOrder          = ""
 prevOrder          = ""
 nextOrder          = ""
 firstTurnaround    = ""
 lastTurnaround     = ""
 prevTurnaround     = ""
 nextTurnaround     = ""
 prevNextSeq        = ""
 orderReviewReq     = ""
 contractOrder      = ""
 conStartDate       = ""
 conStartErr        = ""
 conEndDate         = ""
 conEndErr          = ""
 userDefinedTab     = ""
 userDefError       = ""
 userDtlError       = ""
 exitPointItem      = ""
 exitInfoMsg        = ""
 exitErrorMsg       = ""

 blanketLineTable   = %table
 entryCodeTable     = %table
 commentTable       = %table
 userDetailTable    = %table
 userHeaderTable    = %table
%}

 %{ Table Routine Function Calls %}

%FUNCTION(DTW_SQL) Blanket_Line_Query
				(IN  	CHAR(64) profileHandle,
				      char(2)  dataBaseID,
				      DEC(8,0) orderControlNumber,
				      DEC(8,0) orderNumber,
				      DEC(3,0) lineNumber,
				 OUT   blanketLineTable)
				{call $(pgmLibrary)hoebwp_w
%}

%FUNCTION(DTW_SQL) EntryCode_Query
				(IN 	CHAR(64) profileHandle,
			         char(2)  dataBaseID,
					   char(10) type,
				OUT  	entryCodeTable)
				{call $(pgmLibrary)hsyfvp_w
%}

%FUNCTION(DTW_directcall) Process_Order (INOUT CHAR(64)     profileHandle,
				      		                CHAR(2)      dataBaseID,
				      		                DECIMAL(8,0) orderControlNumber,
				      		                DECIMAL(8,0) orderNumber,
				      		                char(1)      maintenanceCode)

{%EXEC {HOEPRO_W.PGM %}
%}

%FUNCTION(DTW_directcall) Update_Misc_Charges (INOUT CHAR(64)  profileHandle,
				      		                      CHAR(2)   dataBaseID,
				      	 	                      DEC(8,0)  orderControlNumber,
				      	 	                      DEC(9,2)  freightCharge,
				      		                      DEC(9,2)  specialCharge,
				      		                      CHAR(1)   updateTotal)
						
{%EXEC {HOEUMC_W.PGM %}
%}

%FUNCTION(DTW_directcall) Remove_Line (INOUT	CHAR(64)     profileHandle,
				      	               	CHAR(2)      dataBaseID,
				      	               	DECIMAL(8,0) orderControlNumber,
				      		               DECIMAL(3,0) lineNumber)

{%EXEC {HOERML_W.PGM %}
%}

%FUNCTION(DTW_directcall) Assign_OrderNumber (INOUT DECIMAL(8,0) orderControlNumber,
				      		                     DECIMAL(8,0) newOrder)

{%EXEC {HOEAON_W.PGM %}
%}

%FUNCTION(DTW_directcall) Assign_OrderControl (INOUT CHAR(64)     profileHandle,
				      		                      CHAR(2)      dataBaseID,
				      		                      CHAR(1)      orderType,
				      		                      DECIMAL(7,0) customerNumber,
				      		                      DECIMAL(7,0) contactNumber,
				      		                      DECIMAL(8,0) orderControlNumber,
				      		                      DECIMAL(8,0) orderNumber,
				      		                      DECIMAL(9,0) turnaround,
				      		                      CHAR(1)      maintCode,
				      		                      CHAR(1)      orderBusy,
                                                     CHAR(7)      erroMsg)
{%EXEC {HOEOTU_W.PGM %}
%}

%FUNCTION(dtw_directcall) Order_Control (INOUT CHAR(64)     profileHandle,
				                            CHAR(2)      dataBaseID,
				                            DECIMAL(8,0) orderControlNumber,
				                            CHAR(1)      maintCode,
				                            CHAR(1)      dropShip,
				      	 	                CHAR(32000)  edtVar)
{%EXEC {HOECTO_W.PGM %}
%}

%FUNCTION(DTW_directcall) Calc_Cost_Price (INOUT CHAR(64)     profileHandle,
				      	                     CHAR(2)      dataBaseID,
				      		                  DECIMAL(8,0) orderControlNumber,
				      		                  DECIMAL(3,0) lineNumber,
				      		                  CHAR(1)      costPrice)

{%EXEC {HOEDCP_W.PGM %}
%}

%FUNCTION(DTW_directcall) Update_Credit_Card  (INOUT CHAR(32000) edtVar)
{%EXEC {HOEUCC_W.PGM %}
%}

%FUNCTION(DTW_directcall) Update_Credit_Card_C3  (INOUT CHAR(32000) edtVar)
{%EXEC {HOEUC3_W.PGM %}
%}

%FUNCTION(dtw_directcall) Add_To_Order (INOUT CHAR(64)    profileHandle,
				                           CHAR(2)     dataBaseID,
                                              CHAR(1)     haveKit,
                                              CHAR(32000) edtVar)                             	

{%EXEC {HOEATO_W.PGM %}
%}

%FUNCTION(DTW_directcall) Ret_Order_Info (INOUT CHAR(32000) edtVar)

{%EXEC {HOEROI_W.PGM %}
%}

%FUNCTION(DTW_directcall) Update_Drop_Ship (INOUT CHAR(64)     profileHandle,
				      	                      CHAR(2)      dataBaseID,
				      		                   DECIMAL(8,0) orderControlNumber,
				      		                   DECIMAL(7,0) dropShipNumber)

{%EXEC {HOEUDS_W.PGM %}
%}

%FUNCTION(DTW_SQL) Get_Default_Comments(INOUT CHAR(15)     itemNumber,
				               DECIMAL(7,0) shipTo,	
				               CHAR(3)      documentType,
				               CHAR(1)      headerTrailer,
				               CHAR(1)      type,
		                         OUT                commentTable)
{call $(pgmLibrary)HHDCIC_P
%}

%FUNCTION(DTW_SQL) Order_Comments_Query(IN    CHAR(3)      orderDocType,
				                           DECIMAL(8,0) orderControlNumber,	
				      	                  DECIMAL(3,0) lineNumber,
				                     OUT   commentTable)

{call $(pgmLibrary)HOECWP_W
%}

%FUNCTION(DTW_SQL) Open_History_Comments(INOUT CHAR(64)     profileHandle,
				                            CHAR(2)      dataBaseID,
				                            DECIMAL(8,0) orderNumber,	
					                         DECIMAL(3,0) orderSequence,
				      	                   DECIMAL(3,0) lineNumber,
				      	                   DECIMAL(3,0) releaseNumber,
				      	                   CHAR(3)      orderDocType,
				      	                   CHAR(1)      type,
				                      OUT                commentTable)

{call $(pgmLibrary)HOEOCP_W
%}

%FUNCTION(DTW_SQL) Kit_Options_Query(IN    CHAR(64)     profileHandle,
			                           CHAR(2)      dataBaseID,
				                        DECIMAL(8,0) orderControlNumber,	
				                        DECIMAL(8,0) orderNumber,	
				      	               DECIMAL(3,0) lineNumber,
				                  OUT   optionTable)

{call $(pgmLibrary)HOEKWP_W
%}

%FUNCTION(DTW_SQL) Feat_Option_Query(IN  DECIMAL(8,0) orderControlNumber,	
				                      DECIMAL(8,0) orderNumber,	
				      	             DECIMAL(3,0) lineNumber,
				                  OUT optionTable)

{call $(pgmLibrary)HHDFOP_P
%}

%FUNCTION(dtw_directcall) Features_Options_Update (INOUT DEC(8,0)    orderControlNumber,
                                                         DEC(3,0)    lineNumber,
                                                         CHAR(1)     updSmartPart,
                                                         CHAR(1)     useVendorCatalog,
			                                         CHAR(1000)  smartPart)
  {%EXEC {HHDFOP_U.PGM %}
%}

%FUNCTION(DTW_SQL) User_Header_Query(IN  CHAR(10)     userProfile,
				                      CHAR(1)      H1ORTY,
				                  OUT              userHeaderTable)

  {call $(pgmLibrary)HOEUHR_W
%}

%FUNCTION(DTW_SQL) User_Detail_Query(IN  CHAR(10)     userProfile,
				                      CHAR(1)      H1ORTY,
				                      CHAR(1)      nonStock,	
				                  OUT              userHeaderTable)

  {call $(pgmLibrary)HOEUDR_W
%}

%FUNCTION(dtw_directcall) Update_OEPOWK(INOUT DECIMAL(8,0) orderControlNumber,
				      	                  DECIMAL(3,0) lineNumber)
{%EXEC {HOEPOW_W.PGM %}
%}

%FUNCTION(DTW_directcall) Regenerate_Fixed_Price_Options (IN CHAR(32000) edtVar)

{%EXEC {HHDFPO.PGM %}
%}

%FUNCTION(dtw_directcall) Exit_Point_OEITEMADD (INOUT DECIMAL(8,0) orderControlNumber,
				      	              CHAR(15)     itemNumber,
				      	              CHAR(75)     itemError)
{%EXEC {HOEITX_W.PGM %}
%}


%FUNCTION(DTW_directcall) Check_Privileges (INOUT CHAR(32000) edtVar)

{%EXEC {HSYPVU_W.PGM %}
%}

