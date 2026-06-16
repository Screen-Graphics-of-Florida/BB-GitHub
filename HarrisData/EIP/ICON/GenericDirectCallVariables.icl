%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: This macro holds generic direct call functions               *
**********************************************************************
%}

%{ Direct Call Function Blocks %}
%Define {
  uv_Sql         = ""
  userViewVar    = ""
  uv_FieldName   = ""
  uv_FieldValue  = ""
%}

%{ Retrieve File Library %}
%FUNCTION(dtw_directcall) RtvFileLib (INOUT CHAR(10) e_fileName,
                                            CHAR(10) e_library)
  {%EXEC {CSYLIB_W.PGM %}
  %}

%{ Set Library List %}
%FUNCTION(dtw_directcall) setLibl (INOUT CHAR(64) profileHandle,
			                         CHAR(2)  dataBaseID,
			                         CHAR(75) error,
			                         CHAR(10) userProfile)
  {%EXEC {HSYENA_W.PGM %}
  %}

%{ Validate User Profile %}
%FUNCTION(dtw_directcall) Validate_User_Profile(INOUT CHAR(10) userProfile,
						                             CHAR(10) password,
				                                   CHAR(2)  dataBaseID,
                                                      CHAR(64) profileHandle,
			                                      CHAR(32) HTTP_AS_AUTH_PROFILETKN)
  {%EXEC {HSYHND_W.PGM%}
  %}

%{ Retrieve User Role %}
%FUNCTION(dtw_directcall) retrieveUserRole (IN    CHAR(64) profileHandle,
			                                  CHAR(2)  dataBaseID,
			                                  CHAR(10) userProfile,
                                            INOUT DEC(7,0) userCustomer,
                                            INOUT DEC(7,0) userVendor,
                                            INOUT DEC(3,0) userSalesman,
                                            INOUT CHAR(30) userBadge,
                                            INOUT CHAR(15) userCatalog,
                                            INOUT CHAR(256) newsLink,
                                            INOUT CHAR(256) profileName)
  { %EXEC {HSYROL_W.PGM %}
  %}

%{ Delete User Handle %}
%FUNCTION(dtw_directcall) deleteUserHandle (INOUT CHAR(64) profileHandle,
			                                  CHAR(2)  dataBaseID)
  {
      %EXEC {HSYDHN_W.PGM %}
  %}

%{ Retrieve Field Description %}
%FUNCTION(dtw_directcall)  RtvFldDesc(IN    CHAR(32000) selectRecord,
			                            CHAR(1000) fileName,
			                            CHAR(500)  fieldName,
                                      INOUT CHAR(3000) fieldDesc)
  { %EXEC {HSYRFD_W.PGM %}
  %}

%{ Retrieve Field Description %}
%FUNCTION(dtw_directcall)  RtvWFExcptExist(IN    CHAR(64) profileHandle,
			                                 CHAR(10) programName,
			                                 CHAR(10) vldPgmName,
			                                 CHAR(1)  vldCheck,
                                           INOUT CHAR(1)  vldExcptExist)
  { %EXEC {HWFEXC_W.PGM %}
  %}

%{ Session Date Formated %}
%FUNCTION(dtw_directcall) SessionDate (INOUT CHAR(64) profileHandle,
			                             CHAR(2)  dataBaseID,
                                             CHAR(50) sessionDateFormat)
  {
      %EXEC {SSYDTE_W.PGM %}
  %}

%{ Session Date Formated %}
%FUNCTION(dtw_directcall) SystemDate (INOUT DEC(7,0) systemDate)
  {
      %EXEC {HSYSDT_W.PGM %}
  %}

%{ Date Reformat Program %}
%FUNCTION(dtw_directcall) Reformat_Date(INOUT CHAR(7) CYMDDate,
                                        IN CHAR(10) fromFormat,
                                        IN CHAR(10) toFormat)
  {
      %EXEC {CSYDTC_W.PGM %}
  %}

%{ Date Reformat Program %}
%FUNCTION(dtw_directcall) Reformat_Date_4(INOUT CHAR(8)  CYMDDate,
                                          IN    CHAR(10) fromFormat,
                                          IN    CHAR(10) toFormat)
  {
      %EXEC {CSYDT4_W.PGM %}
  %}

%{ ISO Date Reformat Program %}
%FUNCTION(dtw_directcall) Reformat_Date_ISO (INOUT CHAR(10) ISODate,
                                             IN    CHAR(10) fromFormat,
                                             IN    CHAR(10) toFormat)
  {
      %EXEC {CSYDTI_W.PGM %}
  %}

%{ Calculate Years Between Two Dates %}
%FUNCTION(dtw_directcall) Calc_Years(IN    CHAR(64) profileHandle,
			                     IN    CHAR(2)  dataBaseID,
                                     IN    CHAR(7)  fromDate,
                                     IN    CHAR(7)  toDate,
                                     INOUT DEC(5,0) years)
  {
      %EXEC {HSYCYB_W.PGM %}
  %}

%{ Calculate New ISO Date %}
%FUNCTION(dtw_directcall) Calc_ISO_Date (IN    CHAR(64) profileHandle,
			                         IN    CHAR(2)  dataBaseID,
                                         INOUT CHAR(10) dateISO,
                                         IN    CHAR(1)  addSub,
                                         IN    CHAR(1)  mdyCode,
                                         IN    DEC(3,0) incr)
  {
      %EXEC {HSYISO_W.PGM %}
  %}

%{ Period Reformat Program %}
%FUNCTION(dtw_directcall) Reformat_Period(INOUT CHAR(7) CYPPeriod,
                                          IN    CHAR(10) fromFormat)
  {
      %EXEC {CSYPDC_W.PGM %}
  %}

%{ Convert Period To 4 digit Year %}
%FUNCTION(dtw_directcall) Period_CYP_YYYY (INOUT CHAR(10) dateIn)
  {
      %EXEC {HSYPD4_W.PGM %}
  %}

%{ Get Release Version %}
%FUNCTION(dtw_directcall) Release_Version (IN CHAR(64) profileHandle,
			                           IN CHAR(2)  dataBaseID,
                                           IN CHAR(2)  apid,
                                           INOUT DEC(5,1) release,
                                           INOUT DEC(3,0) libLev)
  {  %EXEC {HSYAPP_W.PGM %}
  %}

%{ Retrieve Error Message %}
%FUNCTION(dtw_directcall) Ret_Error_Msg   (IN CHAR(64) profileHandle,
			                           IN CHAR(2)  dataBaseID,
                                           IN CHAR(7)  errorNumber,
                                           INOUT CHAR(67) errorMessage)
  {  %EXEC {HSYERR_W.PGM %}
  %}

%{ Get Environment Overrides %}
%FUNCTION(dtw_directcall) Env_Overrides (IN    CHAR(64) profileHandle,
			                               CHAR(2)  dataBaseID,
                                               CHAR(2)  apid,
                                               CHAR(10) envProgram,
                                               CHAR(10) envPrinter,
                                         INOUT CHAR(10) envJobName,
                                               CHAR(10) envJobDescription,
                                               CHAR(10) envJobQueue,
                                               CHAR(75) envError)
  {  %EXEC {HSYSBS_W.PGM %}
  %}

%{ Asset User View %}
%FUNCTION(DTW_directcall)  AssetUserView(INOUT CHAR(64)  profileHandle,
		                                  CHAR(2)   dataBaseID,
		                                  CHAR(1)   userPass,
		                                  DEC(3,0)  UVSite,
		                                  DEC(12,0) UVAsset)
  { %EXEC {HFAASP_W.PGM %}
  %}
%{ Employee User View %}
%FUNCTION(DTW_directcall)  EmployeeUserView(INOUT CHAR(64)  profileHandle,
			                                  CHAR(2)   dataBaseID,
			                                  CHAR(2)   applicationID,
			                                  CHAR(1)   userPass,
			                                  DEC(2,0)  UVCo,
			                                  DEC(4,0)  UVFac,
			                                  DEC(5,0)  UVPREmpl,
			                                  DEC(2,0)  UVHRCo,
			                                  DEC(9,0)  UVHREmpl)
  { %EXEC {HHREMP_W.PGM %}
  %}

%{ Bank User View %}
%FUNCTION(DTW_directcall) BankUserView(INOUT CHAR(64)  profileHandle,
                                             CHAR(2)   dataBaseID,
                                             CHAR(1)   userPass,
                                             DEC(3,0)  bankNumber)
  { %EXEC {HHDBANK_W.PGM %}
  %}

%{ Payroll Bank User View %}
%FUNCTION(DTW_directcall) PrBankUserView(INOUT CHAR(64)  profileHandle,
                                             CHAR(2)   dataBaseID,
                                             CHAR(2)   applicationID,
                                             CHAR(1)   userPass,
                                             DEC(3,0)  bankNumber)
  { %EXEC {HPRBANK_W.PGM %}
  %}

%FUNCTION(DTW_directcall) CoFacUserView(INOUT CHAR(64)  profileHandle,
                                              CHAR(2)   dataBaseID,
                                              CHAR(1)   userPass,
                                              DEC(2,0)  companyNumber,
                                              DEC(4,0)  facilityNumber)
  { %EXEC {HHDCFAC_W.PGM %}
  %}

%{ Customer User View %}
%FUNCTION(DTW_directcall)  CustomerUserView(INOUT CHAR(64)  profileHandle,
			                                  CHAR(2)   dataBaseID,
			                                  CHAR(1)   userPass,
			                                  DEC(7,0)  customerNumber,
			                            IN    CHAR(1)   testOvrFields)
  { %EXEC {HHDCUST_W.PGM %}
  %}

%FUNCTION(DTW_directcall) HrCoFacUserView(INOUT CHAR(64)  profileHandle,
                                              CHAR(2)   dataBaseID,
                                              CHAR(1)   userPass,
                                              DEC(2,0)  companyNumber,
                                              DEC(4,0)  facilityNumber)
  { %EXEC {HHRCOFC_W.PGM %}
  %}

%{ Item User View %}
%FUNCTION(DTW_directcall)  ItemUserView(INOUT CHAR(64)  profileHandle,
                                              CHAR(2)   dataBaseID,
                                              CHAR(1)   userPass,
                                              CHAR(15)  itemNumber)
  { %EXEC {HHDIMST_W.PGM %}
  %}

%{ Item Plant User View %}
%FUNCTION(DTW_directcall)  ItemPlantUserView(INOUT CHAR(64)  profileHandle,
                                                   CHAR(2)   dataBaseID,
                                                   CHAR(1)   userPass,
                                                   DEC(3,0)  plantNumber,
                                                   CHAR(15)  itemNumber)
  { %EXEC {HHDIPLT_W.PGM %}
  %}

%{ Item Warehouse User View %}
%FUNCTION(DTW_directcall)  ItemWarehouseUserView(INOUT CHAR(64)  profileHandle,
                                                       CHAR(2)   dataBaseID,
                                                       CHAR(1)   userPass,
                                                       DEC(3,0)  warehouseNumber,
                                                       CHAR(15)  itemNumber)
  { %EXEC {HHDIWHS_W.PGM %}
  %}

%{ Kanban User View %}
%FUNCTION(DTW_directcall)  KanbanUserView(INOUT CHAR(64)  profileHandle,
                                                CHAR(2)   dataBaseID,
                                                CHAR(1)   userPass,
                                                DEC(3,0)  plantNumber,
                                                CHAR(15)  itemNumber)
  { %EXEC {HHDKBMS_W.PGM %}
  %}

%{ Location User View %}
%FUNCTION(DTW_directcall)  LocationUserView(INOUT CHAR(64)  profileHandle,
			                                  CHAR(2)   dataBaseID,
			                                  CHAR(1)   userPass,
			                                  DEC(3,0)  locationNumber)
  { %EXEC {HHDLCTN_W.PGM %}
  %}

%{ Lot User View %}
%FUNCTION(DTW_directcall)  LotUserView(INOUT CHAR(64)  profileHandle,
                                             CHAR(2)   dataBaseID,
                                             CHAR(1)   userPass,
                                             DEC(3,0)  warehouseNumber,
                                             CHAR(15)  itemNumber)
  { %EXEC {HHDLOT_W.PGM %}
  %}

%{ Plant User View %}
%FUNCTION(DTW_directcall)  PlantUserView(INOUT CHAR(64)  profileHandle,
                                               CHAR(2)   dataBaseID,
                                               CHAR(1)   userPass,
                                               DEC(3,0)  plantNumber)
  { %EXEC {HHDPLNT_W.PGM %}
  %}

%{ Vendor User View %}
%FUNCTION(DTW_directcall)  VendorUserView(INOUT CHAR(64)  profileHandle,
			                                CHAR(2)   dataBaseID,
			                                CHAR(1)   userPass,
			                                DEC(7,0)  vendorNumber)
  { %EXEC {HHDVEND_W.PGM %}
  %}

%{ Warehouse User View %}
%FUNCTION(DTW_directcall) WarehouseUserView(INOUT CHAR(64)  profileHandle,
                                                  CHAR(2)   dataBaseID,
                                                  CHAR(1)   userPass,
                                                  DEC(3,0)  warehouseNumber)
  { %EXEC {HHDWHSM_W.PGM %}
  %}

%{ Work Center User View %}
%FUNCTION(DTW_directcall)  WorkCenterUserView(INOUT CHAR(64)  profileHandle,
                                                    CHAR(2)   dataBaseID,
                                                    CHAR(1)   userPass,
                                                    DEC(3,0)  plantNumber,
                                                    CHAR(5)   department,
                                                    CHAR(5)   workCenter)
  { %EXEC {HHDMWCM_W.PGM %}
  %}

%{ Salesman User View %}
%FUNCTION(DTW_directcall)  SalesmanUserView(INOUT CHAR(64)  profileHandle,
			                                  CHAR(2)   dataBaseID,
			                                  CHAR(1)   userPass,
			                                  DEC(3,0)  salesmanNumber)
  { %EXEC {HHDSLSM_W.PGM %}
  %}

%{ Work Flow Task Request User View %}
%FUNCTION(DTW_directcall)  WFWorkItemUserView(INOUT CHAR(64)    profileHandle,
                                                    CHAR(2)     dataBaseID,
                                                    CHAR(1)     userPass,
                                                    CHAR(32000) edtVar)
{ %EXEC {HWFUSV_W.PGM %}
%}

%{ User View SQL %}
%FUNCTION(DTW_directcall)  User_View(INOUT CHAR(64)    profileHandle,
			                           CHAR(2)     dataBaseID,
                                           CHAR(32000) userViewVar,
                                           CHAR(32000) uv_Sql)
  { %EXEC {HSYCU3_W.PGM %}
  %}

%{ Fill Available To Promise Work File %}
%FUNCTION(DTW_directcall) Fill_ATP_Work_File (INOUT CHAR(64)     profileHandle,
                                                    CHAR(2)      dataBaseID,
                                                    DECIMAL(3,0) plantNumber,
                                                    CHAR(15)     itemNumber)

{%EXEC {HMS230_W.PGM %}
%}

%{ Retrieve Order History Sequence Number For An Invoice %}
%FUNCTION(dtw_directcall) retHistorySeq (IN    CHAR(64) profileHandle,
			                               CHAR(2)  dataBaseID,
			                               DEC(7,0) invoiceNumber,
                                         INOUT DEC(3,0) sequence)
{ %EXEC {HOERHS_W.PGM %}
%}

%{ Check For Existence Of Customer Invoice %}
%FUNCTION(dtw_directcall) Check_Invoice (IN    CHAR(64) profileHandle,
			                               CHAR(2)  dataBaseID,
			                               DEC(7,0) invoiceNumber,
			                               DEC(7,0) customerNumber,
                                         INOUT CHAR(1)  invoiceFound)
{ %EXEC {HOECIV_W.PGM %}
%}

%{ Retrieve Quantity Available For Item/Whs %}
%FUNCTION(dtw_directcall) Get_Qty_Avail (IN    CHAR(64)  profileHandle,
				                            CHAR(2)   dataBaseID,
                                               CHAR(15)  itemNumber,
                                               DEC(3,0)  wareHouse,
                                         INOUT DEC(13,4) qtyAvailable)
{%EXEC {HHDRAV_W.PGM %}
%}

%{ Retrieve Unit Costs %}
%FUNCTION(dtw_directcall) Rtv_Unit_Cost (IN    DEC(3,0)  plantNumber,
                                               CHAR(15)  itemNumber,
                                               DEC(3,0)  wareHouse,
                                               CHAR(15)  lotNumber,
                                         INOUT DEC(13,5) totalCost,
                                               DEC(13,5) cat1Cost,
                                               DEC(13,5) cat2Cost,
                                               DEC(13,5) cat3Cost,
                                               DEC(13,5) cat4Cost,
                                               DEC(13,5) cat5Cost)
{%EXEC {HHDRUC_W.PGM %}
%}

%{ Retrieve Customer Price For An Item %}
%FUNCTION(dtw_directcall) Cust_Unit_Price (IN  CHAR(64)  profileHandle,
				                            CHAR(2)   dataBaseID,
                                               DEC(7,0)  vendCustNumber,
                                               CHAR(15)  itemNumber,
                                         INOUT DEC(13,5) unitPrice,
                                               DEC(3,0)  wareHouse,
                                               DEC(9,4)  piecesPerPricing)
{%EXEC {HOEPRC_W.PGM %}
%}

%{ Retrieve Vendor Price For An Item %}
%FUNCTION(dtw_directcall) Vend_Unit_Price (IN  CHAR(64)  profileHandle,
				                            CHAR(2)   dataBaseID,
                                               DEC(7,0)  vendcustNumber,
                                               CHAR(15)  itemNumber,
                                               DEC(3,0)  wareHouse,
                                         INOUT DEC(13,5) unitPrice)
{%EXEC {HPOPRC_W.PGM %}
%}

%{ Retrieve Order Type User Defined Field Descriptions %}
%FUNCTION(dtw_directcall) Order_Type_UDF(INOUT CHAR(64) profileHandle,
                                        	    CHAR(2)  dataBaseID,
                                        	    CHAR(1)  orderType,
						                      CHAR(23) dateOneDescripiton,
						                      CHAR(23) dateTwoDescripiton,
						                      CHAR(23) dateThreeDescripiton,
						                      CHAR(23) uDFOneDescripiton,
						                      CHAR(23) uDFTwoDescripiton,
						                      CHAR(23) uDFThreeDescripiton,
						                      CHAR(23) uDFFourDescripiton,
						                      CHAR(23) uDFFiveDescripiton)
  {
      %EXEC {HHDOUP_W.PGM %}
  %}

%{ Check Vendor/Customer Item Comments %}
%FUNCTION(dtw_directcall) Check_Item_Comments (IN    CHAR(64)  profileHandle,
				                                  CHAR(2)   dataBaseID,
                                                     CHAR(15)  itemNumber,
                                                     CHAR(1)   vcf,
                                                     DEC(7,0)  customerNumber,
                                                     CHAR(3)   documentType,
                                                     CHAR(1)   headerTrailer,
                                               INOUT CHAR(1)   itemComments)
  {%EXEC {HHDCIC_W.PGM %}
  %}

%{ Customer A/R Balance %}
%FUNCTION(dtw_directcall) CustArBalance(INOUT CHAR(64)  profileHandle,
                                              CHAR(2)   dataBaseID,
                                              DEC(7,0)  customerNumber,
                                              CHAR(1)   arBalanceType,
                                              CHAR(3)   arBalanceCurrency,
                                              DEC(11,2) arBalanceAmount)
  {
      %EXEC {HHDARB_W.PGM %}
  %}

%{ Customer A/R Aging %}
%FUNCTION(dtw_directcall) LoadARAging(INOUT CHAR(64)  profileHandle,
                                            DEC(7,0)  customerNumber)
  {
      %EXEC {HARAGI_W.PGM %}
  %}

%FUNCTION(dtw_directcall) RtvCustCont(IN    CHAR(64) profileHandle,
                                            CHAR(2)  dataBaseID,
                                            DEC(7,0) contactNumber,
                                      OUT   CHAR(30) firstName,
                                            CHAR(30) lastName,
                                            CHAR(4)  salutation,
                                            CHAR(30) companyName)
  {
      %EXEC {HCRRCN_W.PGM %}
  %}

%FUNCTION(dtw_directcall) RtvSuppCont(IN    CHAR(64) profileHandle,
                                            CHAR(2)  dataBaseID,
                                            DEC(7,0) contactNumber,
                                      OUT   CHAR(30) firstName,
                                            CHAR(30) lastName,
                                            CHAR(4)  salutation,
                                            CHAR(30) companyName)
  {
      %EXEC {HSRRCN_W.PGM %}
  %}

%{ Program Option Security %}
%FUNCTION(dtw_directcall) pgmOptSecurity(INOUT CHAR(64) profileHandle,
                                               CHAR(2)  dataBaseID,
                                               CHAR(10) programName,
                                               CHAR(1)  sec_01,
                                               CHAR(1)  sec_02,
                                               CHAR(1)  sec_03,
                                               CHAR(1)  sec_04,
                                               CHAR(1)  sec_05,
                                               CHAR(1)  sec_06,
                                               CHAR(1)  sec_07,
                                               CHAR(1)  sec_08,
                                               CHAR(1)  sec_09,
                                               CHAR(1)  sec_10,
                                               CHAR(1)  sec_11,
                                               CHAR(1)  sec_12,
                                               CHAR(1)  sec_13,
                                               CHAR(1)  sec_14,
                                               CHAR(1)  sec_15)
  {
      %EXEC {HSYPGM_W.PGM %}
  %}

%{ Return Plant Number From Warehouse Supply %}
%FUNCTION(dtw_directcall) RetPltNbr(INOUT CHAR(64) profileHandle,
                                          CHAR(2)  dataBaseID,
                                          CHAR(10) subroutine,
                                          DEC(3,0) warehouseNumber,
                                          CHAR(15) itemNumber,
                                          CHAR(3)  returnPlant)
  {
      %EXEC {SHDRWS_W.PGM %}
  %}
%{ Return Warehouse Number From Warehouse Supply %}
%FUNCTION(dtw_directcall) RetWhsNbr(INOUT CHAR(64) profileHandle,
                                          CHAR(2)  dataBaseID,
                                          CHAR(10) subroutine,
                                          DEC(3,0) plantNumber,
                                          CHAR(15) itemNumber,
                                          CHAR(3)  returnWarehouse)
  {
      %EXEC {SHDRWS_W.PGM %}
  %}

  %{ Employee User View %}
%FUNCTION(DTW_directcall)  RetEmpNam(IN    DEC(2,0)  PRComp,
			                           DEC(4,0)  PRFACL,
			                           DEC(5,0)  PREmpl,
			                           DEC(2,0)  HRCo,
			                           DEC(9,0)  HREmpl,
			                     OUT   CHAR(18)  lastName,
			                           CHAR(18)  firstName,
			                           CHAR(1)   middleInitial,
			                           CHAR(23)  reportName,
			                           CHAR(4)   termCode)
  { %EXEC {HHRREN_W.PGM %}
  %}
%FUNCTION(DTW_directcall)  RetHrCompNam(INOUT CHAR(64)  profileHandle,
			                                  CHAR(2)   dataBaseID,
			                                  DEC(2,0)  HRCo,
			                                  DEC(4,0)  HRFacl,
			                                  CHAR(30)  coFacName)
  { %EXEC {HHRCFN_W.PGM %}
  %}

%FUNCTION(DTW_directcall)  RetDepNam(INOUT CHAR(64)  profileHandle,
			                                  CHAR(2)   dataBaseID,
			                                  DEC(7,0)  HRSDNO,
			                                  CHAR(30)  depName)
  { %EXEC {HHRSDN_W.PGM %}
  %}
%FUNCTION(dtw_directcall)  RetCalEvents(INOUT CHAR(64)    profileHandle,
			                              CHAR(2)     dataBaseID,
			                              CHAR(10)    loadProgram,
			                              CHAR(4)     year,
                                              CHAR(2)     month,
                                              CHAR(1)     week,
                                              CHAR(10)    startDate,
                                              CHAR(10)    endDate,
                                              CHAR(1)     reload,
                                              CHAR(1)     pastDue,
                                              CHAR(32000) searchVar,
                                              CHAR(500)   searchDsp)
  { %EXEC {HSYCLE_W.PGM %}
  %}
%FUNCTION(dtw_directcall)  RetDayName(IN    CHAR(64) profileHandle,
			                            CHAR(2)  dataBaseID,
			                            CHAR(10) dateIn,
                                      INOUT CHAR(9)  dayName)
  { %EXEC {HSYRDD_W.PGM %}
  %}
%FUNCTION(DTW_directcall)  RetPayCodeDesc(INOUT CHAR(64)  profileHandle,
			                                  CHAR(2)   dataBaseID,
			                                  DEC(2,0)  PRComp,
			                                  DEC(4,0)  PRFacl,
			                                  CHAR(3)   PRCode,
			                                  CHAR(20)  prCodeDesc)
  { %EXEC {HHRPCD_W.PGM %}
  %}

%FUNCTION(DTW_directcall)  RetTotDed(INOUT CHAR(64)  profileHandle,
			                           CHAR(2)   dataBaseID,
			                           DEC(11,0) CkRef,
			                           DEC(9,2)  totDed)
  { %EXEC {HPRTVD_W.PGM %}
  %}

%FUNCTION(DTW_directcall)  RetHoursUnits(INOUT CHAR(64)  profileHandle,
			                               CHAR(2)   dataBaseID,
			                               DEC(11,0) CkRef,
			                               CHAR(1)   Cksumt,
			                               DEC(9,2)  CkHours,
			                               DEC(9,2)  CkUnits,
			                               DEC(9,2)  CkWkHr)
  { %EXEC {HPRHRU_W.PGM %}
  %}

%FUNCTION(dtw_directcall) SQL_Update (IN  CHAR(32000) stmtSQL,
			                      OUT CHAR(5)     status)
  {%EXEC {HSYSQL_U.PGM %}
  %}

%MACRO_FUNCTION Default_Zero (INOUT CHAR(10) inputField)
  {
      %if (inputField == "")
          @dtw_assign(inputField, "0")
      %endif
  %}

%MACRO_FUNCTION Concat_UserView (IN CHAR(6)     uv_FieldName,
			                    CHAR(32000) uv_FieldValue)
{
  %if (userViewVar == "")
      @dtw_concat(userViewVar, "$(uv_FieldName)$(uv_FieldValue)", userViewVar)
  %else
      @dtw_concat(userViewVar, "}{$(uv_FieldName)$(uv_FieldValue)", userViewVar)
  %endif
%}

%{ Retrieve Default Plant Number %}
%MACRO_FUNCTION RtvDftPlant (INOUT CHAR(3)  dftPltNumber,
                             INOUT CHAR(30) dftPltName)
{
  @RtvFldDesc("USUSER='$(userProfile)'", "SYUSER", "char(USDPLT)", dftPltNumber)
  %if (dftPltNumber<="0")
      @RtvFldDesc("HDDPLT<>0", "HDCTRL", "char(HDDPLT)", dftPltNumber)
  %endif
  @RtvFldDesc("PLPLNT=$(dftPltNumber) ", "HDPLNT", "PLNAME", dftPltName)
%}

%{ Retrieve In Process Flag %}
%FUNCTION(dtw_directcall) In_Process   (IN CHAR(64) profileHandle,
			                           IN CHAR(2)  dataBaseID,
                                           INOUT CHAR(1) InProc)
  {  %EXEC {HPRCCF_W.PGM %}
%}

%{ Retrieve Y/N Description %}
%MACRO_FUNCTION RtvYNDesc (IN  CHAR(1) ynValue,
                           OUT CHAR(3) ynDesc)
{
  @dtw_assign(ynDesc, "")
  %if (ynValue == "Y")
      @dtw_assign(ynDesc, "Yes")
  %elseif (ynValue == "N")
      @dtw_assign(ynDesc, "No")
  %endif
%}

%FUNCTION(dtw_directcall) Retrieve_AcctJrnl_Data(INOUT CHAR(64)  profileHandle,
                                                 INOUT CHAR(2)   dataBaseID,
                                                 INOUT DEC(2,0)  coNumber,
                                                 INOUT DEC(4,0)  facNumber,
                                                 INOUT DEC(4,0)  accountNumber,
                                                 INOUT DEC(4,0)  subAccount,
                                                 INOUT DEC(5,0)  fromPer,
                                                 INOUT DEC(5,0)  toPer,
                                                 INOUT CHAR(1)   incUnposted,
                                                 INOUT CHAR(30)  acctName,
                                                 INOUT CHAR(30)  coFacName,
                                                 INOUT CHAR(13)  balanceIncome,
                                                 INOUT CHAR(8)   currencyUnit,
                                                 INOUT CHAR(3)   currencyType,
                                                 INOUT DEC(15,2) beginBal)
{%EXEC {HGLDDA_W.PGM %}
%}
