%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  	                                              *
*  Job: Edit Routines                                               *
*********************************************************************
%}
%Define {
  returnFldDesc  = ""
%}

%INCLUDE "MonthDesc.icl"

%MACRO_FUNCTION EditPhoneNumber (INOUT phone) {
  %if (phoneNbrFormat == "3")
      @dtw_assign(phoneSep, ".")
  %else
      @dtw_assign(phoneSep, "-")
  %endif

  %if (@dtw_rlength(phone) == "11" && phoneNbrFormat == "1")
      @dtw_insert(" ", phone, "1", "1", phone)
      @dtw_insert("(", phone, "2", "1", phone)
      @dtw_insert(")", phone, "6", "1", phone)
      @dtw_insert(" ", phone, "7", "1", phone)
      @dtw_insert("$(phoneSep)", phone, "11", "1", phone)

  %elseif (@dtw_rlength(phone) == "11")
      @dtw_insert("$(phoneSep)", phone, "1", "1", phone)
      @dtw_insert("$(phoneSep)", phone, "5", "1", phone)
      @dtw_insert("$(phoneSep)", phone, "9", "1", phone)

  %elseif (@dtw_rlength(phone) == "10" && phoneNbrFormat == "1")
      @dtw_insert("(", phone, "0", "1", phone)
      @dtw_insert(")", phone, "4", "1", phone)
      @dtw_insert(" ", phone, "5", "1", phone)
      @dtw_insert("$(phoneSep)", phone, "9", "1", phone)

  %elseif (@dtw_rlength(phone) == "10")
      @dtw_insert("$(phoneSep)", phone, "3", "1", phone)
      @dtw_insert("$(phoneSep)", phone, "7", "1", phone)

  %elseif (@dtw_rlength(phone) == "7")
      @dtw_insert("$(phoneSep)", phone, "3", "1", phone)
  %endif
%}

%MACRO_FUNCTION Format_Header (IN  CHAR(100) titleIn,
                               IN  CHAR(100) descIn,
                               IN  CHAR(100) dataIn)
{
  @dtw_assign(F_dataIn, "")
  %if (dataIn != "")
    @Format_Code(dataIn, F_dataIn)
  %endif
  @dtw_assign(hdrOut, "<tr>")
  %if (titleIn != "")
      @dtw_concat(hdrOut, "<td class=""hdrtitl"">$(titleIn):</td>", hdrOut)
  %endif
  @dtw_concat(hdrOut, "<td class=""hdrdata"">$(descIn) &nbsp; $(F_dataIn)</td></tr>", hdrOut)
  $(hdrOut)
%}

%MACRO_FUNCTION Format_Header_URL (IN  CHAR(100)  titleIn,
                                   IN  CHAR(100)  descIn,
                                   IN  CHAR(100)  dataIn,
                                   IN  CHAR(1000) urlIn)
{
  @dtw_assign(F_dataIn, "")
  %if (dataIn != "")
    @Format_Code(dataIn, F_dataIn)
  %endif
  @dtw_assign(hdrOut, "<tr>")
  %if (titleIn != "")
      @dtw_concat(hdrOut, "<td class=""hdrtitl"">$(titleIn):</td>", hdrOut)
  %endif
  %if (urlIn != "")
      @dtw_concat(hdrOut, "<td class=""hdrdata""><a href=""$(urlIn)"" title=""View $(titleIn)"">$(descIn) &nbsp; $(F_dataIn)</a></td></tr>", hdrOut)
  %else
      @dtw_concat(hdrOut, "<td class=""hdrdata"">$(descIn) &nbsp; $(F_dataIn)</td></tr>", hdrOut)
  %endif
  $(hdrOut)
%}

%MACRO_FUNCTION Format_Code (IN  CHAR(100) CodeIn,
                             OUT CHAR(100) CodeOut)
{
  @dtw_assign(CodeOut, "")
  %if (codeDisplay == "Y" && CodeIn != "")
      @dtw_concat("$(CodeOut)$(codeDspLeft)$(CodeIn)", "$(codeDspRight)", CodeOut)
  %endif
%}

%MACRO_FUNCTION Format_Quote (IN  CHAR(100) fieldIn,
                              OUT CHAR(100) fieldOut)
{
  @dtw_assign(fieldOut, fieldIn)
  @dtw_replace(fieldOut, "'", "&acute;", "1", "a", fieldOut)
  @dtw_replace(fieldOut, """", "&quot;", "1", "a", fieldOut)
%}

%MACRO_FUNCTION Format_Confirm_Desc (IN  CHAR(100) confirmDesc1,
                                     IN  CHAR(100) confirmData1,
                                     IN  CHAR(100) confirmDesc2,
                                     IN  CHAR(100) confirmData2,
                                     IN  CHAR(100) confirmDesc3,
                                     IN  CHAR(100) confirmData3,
                                     OUT CHAR(600) F_confirmDesc)
{
  @Format_Code(@DTW_rSTRIP(confirmData1), F_confirmData1)
  @dtw_assign(F_confirmDesc, "$(confirmDesc1) $(F_confirmData1)")
  %if (confirmDesc2 != "")
      @Format_Code(@DTW_rSTRIP(confirmData2), F_confirmData2)
      @dtw_concat(F_confirmDesc, "\r$(confirmDesc2) $(F_confirmData2)", F_confirmDesc)
  %endif
  %if (confirmDesc3 != "")
      @Format_Code(@DTW_rSTRIP(confirmData3), F_confirmData3)
      @dtw_concat(F_confirmDesc, "\r$(confirmDesc3) $(F_confirmData3)", F_confirmDesc)
  %endif
  @dtw_replace(F_confirmDesc, "'", "&acute;", "1", "a", F_confirmDesc)
  @dtw_replace(F_confirmDesc, """", "&quot;", "1", "a", F_confirmDesc)
%}

%MACRO_FUNCTION Format_ConfMsg_Desc (IN  CHAR(1)   maintenanceCode,
                                     IN  CHAR(100) confirmDesc1,
                                     IN  CHAR(100) confirmData1,
                                     IN  CHAR(100) confirmDesc2,
                                     IN  CHAR(100) confirmData2,
                                     IN  CHAR(100) confirmDesc3,
                                     IN  CHAR(100) confirmData3,
                                     OUT CHAR(600) F_confMsgDesc)
{
  %if (maintenanceCode == "D")
      @DTW_ASSIGN(F_confMsgDesc, "Confirm Delete Of ")
  %elseif (maintenanceCode == "C")
      @DTW_ASSIGN(F_confMsgDesc, "Confirm Update Of ")
  %elseif (maintenanceCode == "M")
      @DTW_ASSIGN(F_confMsgDesc, "Confirm Move To ")
  %elseif (maintenanceCode == "A")
      @DTW_ASSIGN(F_confMsgDesc, "Confirm Add Of ")
  %elseif (maintenanceCode == "R")
      @DTW_ASSIGN(F_confMsgDesc, "Confirm Release Of ")
  %elseif (maintenanceCode == "T")
      @DTW_ASSIGN(F_confMsgDesc, "Confirm Transfer Of ")
  %elseif (maintenanceCode == "X")
      @DTW_ASSIGN(F_confMsgDesc, "Confirm Cancel Of ")
  %elseif (maintenanceCode == "Z")
      @DTW_ASSIGN(F_confMsgDesc, "Confirm Copy Of ")
  %elseif (maintenanceCode == "E")
      @DTW_ASSIGN(F_confMsgDesc, "Errors Found During Update Of ")
  %endif

  @Format_Code(@DTW_rSTRIP(confirmData1), F_confirmData1)
  @dtw_concat(F_confMsgDesc, "$(confirmDesc1) $(F_confirmData1)", F_confMsgDesc)
  %if (confirmDesc2 != "")
      @Format_Code(@DTW_rSTRIP(confirmData2), F_confirmData2)
      @dtw_concat(F_confMsgDesc, " $(confirmDesc2) $(F_confirmData2)", F_confMsgDesc)
  %endif
  %if (confirmDesc3 != "")
      @Format_Code(@DTW_rSTRIP(confirmData3), F_confirmData3)
      @dtw_concat(F_confMsgDesc, " $(confirmDesc3) $(F_confirmData3)", F_confMsgDesc)
  %endif
  @dtw_replace(F_confMsgDesc, "'", "&acute;", "1", "a", F_confMsgDesc)
  @dtw_replace(F_confMsgDesc, """", "&quot;", "1", "a", F_confMsgDesc)
%}

%MACRO_FUNCTION Format_Acct (IN  CHAR(4)  account,
                             IN  CHAR(4)  subAccount,
                             IN  CHAR(1)  getDesc,
                             OUT CHAR(50) F_acctSub)
{
  @dtw_assign(returnFldDesc, "")
  @dtw_assign(F_acctSub, "")
  %if (account > "0")
      %while(@dtw_rlength(subAccount) != "4") {@dtw_insert("0", subAccount, subAccount)%}
      @dtw_assign(F_acctSub, "$(account)-$(subAccount)")

      %if (getDesc == "Y")
          @RtvFldDesc("CHACCT=$(account) and CHSUB=$(subAccount) ", "HDCHRT", "CHCHDS", acctDesc)
          @dtw_concat(F_acctSub, "&nbsp; $(acctDesc)", F_acctSub)
      %elseif (getDesc == "F")
          @RtvFldDesc("CHACCT=$(account) and CHSUB=$(subAccount) ", "HDCHRT", "CHCHDS", returnFldDesc)
          @Format_Code(F_acctSub, F_acctSub)
      %endif
  %endif
%}

%MACRO_FUNCTION Format_CoFac (IN  CHAR(4)  company,
                              IN  CHAR(4)  facility,
                              IN  CHAR(1)  getDesc,
                              OUT CHAR(50) F_coFac)
{
  @dtw_assign(returnFldDesc, "")
  @dtw_assign(F_coFac, "")
  %if (company > "0")
      %while(@dtw_rlength(facility) != "4") {@dtw_insert("0", facility, facility)%}
      %if (@dtw_rlength(company) != "2" && getDesc != "F")
          @dtw_assign(F_coFac, "$(company)/$(facility)")
      %else
          @dtw_assign(F_coFac, "$(company)/$(facility)")
      %endif

      %if (getDesc == "Y")
          @RtvFldDesc("CFCO#=$(company) and CFFAC#=$(facility) ", "HDCFAC", "CFCFNM", coFacName)
          @dtw_concat(F_coFac, "&nbsp; $(coFacName)", F_coFac)
      %elseif (getDesc == "F")
          @RtvFldDesc("CFCO#=$(company) and CFFAC#=$(facility) ", "HDCFAC", "CFCFNM", returnFldDesc)
          @Format_Code(F_coFac, F_coFac)
      %endif
  %endif
%}

%MACRO_FUNCTION Format_HRCoFac (IN  CHAR(4)  company,
                                IN  CHAR(4)  facility,
                                IN  CHAR(1)  getDesc,
                                OUT CHAR(50) F_coFac)
{
  @dtw_assign(returnFldDesc, "")
  @dtw_assign(F_coFac, "")
  %if (company > "0")
      %while(@dtw_rlength(facility) != "4") {@dtw_insert("0", facility, facility)%}
      @dtw_assign(F_coFac, "$(company)/$(facility)")

      %if (getDesc == "Y")
          @RtvFldDesc("CFCOMP=$(company) and CFFACL=$(facility) ", "HRCOFC", "CFNAME", coFacName)
          @dtw_concat(F_coFac, "&nbsp; $(coFacName)", F_coFac)
      %elseif (getDesc == "F")
          @RtvFldDesc("CFCOMP=$(company) and CFFACL=$(facility) ", "HRCOFC", "CFNAME", returnFldDesc)
          @Format_Code(F_coFac, F_coFac)
      %endif
  %endif
%}

%MACRO_FUNCTION Ret_Format_EmplName(IN  DEC(2,0)  PRComp,
			                        DEC(4,0)  PRFACL,
    			                     DEC(5,0)  PREmpl,
    			                     DEC(2,0)  HRCo,
    			                     DEC(9,0)  HREmpl,
                                        CHAR(1)   termHD,
                                    OUT CHAR(100) F_EmpName)
{
  @dtw_assign(F_EmpName, "")
  @RetEmpNam(PRComp, PRFACL, PREmpl, HRCo, HREmpl, lastName, firstName, middleInitial, reportName, termCode)

  %if (firstName != "" || lastName != "")
      @Format_EmplName(firstName, lastName, middleInitial, reportName, termCode, termHD, F_EmpName)
  %endif
%}

%MACRO_FUNCTION Format_EmplName (IN  CHAR(30)  firstName,
                                 IN  CHAR(30)  lastName,
                                 IN  CHAR(1)   middleInitial,
                                 IN  CHAR(30)  reportName,
                                 IN  CHAR(1)   termCode,
                                 IN  CHAR(1)   termHD,
                                 OUT CHAR(100) F_EmpName)
{
  @dtw_assign(F_EmpName, "")
  %if (firstName != "" || lastName != "")
      %if (HRNameFormat == "0" && reportName != "")
          @dtw_assign(F_EmpName, "$(reportName)")
      %elseif (HRNameFormat == "0")
          @dtw_assign(F_EmpName, "$(firstName) $(middleInitial) $(lastName)")
      %elseif (HRNameFormat == "1")
          @dtw_assign(F_EmpName, "$(lastName). @dtw_rsubstr(firstName, "1", "1")")
      %elseif (HRNameFormat == "2")
          @dtw_assign(F_EmpName, "$(lastName), $(firstName) $(middleInitial)")
      %elseif (HRNameFormat == "3")
          @dtw_assign(F_EmpName, "$(lastName) $(firstName)")
      %elseif (HRNameFormat == "4")
          @dtw_assign(F_EmpName, "$(lastName) @dtw_rsubstr(firstName, "1", "1") $(middleInitial)")
      %elseif (HRNameFormat == "5")
          @dtw_assign(F_EmpName, "$(firstName) $(middleInitial) $(lastName)")
      %elseif (HRNameFormat == "6")
          @dtw_assign(F_EmpName, "@dtw_rsubstr(firstName, "1", "1") $(middleInitial) $(lastName)")
      %endif
      %if (termCode != "" && termHD == "H")
          @dtw_concat(F_EmpName, "&nbsp; $(termEmplHeader)", F_EmpName)
      %elseif (termCode != "" && termHD == "D")
          @dtw_concat(F_EmpName, "&nbsp; $(termEmplDetail)", F_EmpName)
      %endif
  %endif
%}

%MACRO_FUNCTION Rtv_Error_Desc (IN  CHAR(10) errorNumber,
                                OUT CHAR(67) errorDesc)
{
  @dtw_assign(errorDesc, "")
  @RtvFldDesc("ERER#='$(errorNumber)'", "SYCERR", "ERERDS", errorDesc)
  %if (errorDesc == "")
      @RtvFldDesc("ERER#='$(errorNumber)'", "HDERROR", "ERERDS", errorDesc)
  %endif
%}

%MACRO_FUNCTION Format_Date (IN  CHAR(7)  dateIn,
                             IN  CHAR(2)  dateInFormat,
                             OUT CHAR(20) F_dateIn)
{
  @dtw_assign(F_dateIn, "")
  @dtw_strip(dateIn, dateIn)
  %if (dateIn > "0")
      %if (dateInFormat == "H")
          @dtw_assign(dateFormat, dateFormatHdr)
      %elseif (dateInFormat == "D")
          @dtw_assign(dateFormat, dateFormatDtl)
      %else
          @dtw_assign(dateFormat, dateInFormat)
      %endif

      %while(@dtw_rlength(dateIn) != "8") {@dtw_insert("0", dateIn, dateIn)%}
      @dtw_assign(fromFormat, "*CYMD")
      @dtw_assign(toFormat, "*MDYY")
      @Reformat_Date_4(dateIn, fromFormat, toFormat)
      @dtw_assign(mm, "@dtw_rsubstr(dateIn, "1", "2")")
      @dtw_assign(dd, "@dtw_rsubstr(dateIn, "3", "2")")
      @dtw_assign(yy, "@dtw_rsubstr(dateIn, "7", "2")")
      @dtw_assign(yyyy, "@dtw_rsubstr(dateIn, "5", "4")")

      %if (dateFormat == "1")
          @dtw_assign(F_dateIn, "$(mm)$(dateEdit)$(dd)$(dateEdit)$(yy)")
      %elseif (dateFormat == "2")
          @dtw_assign(F_dateIn, "$(mm)$(dateEdit)$(dd)$(dateEdit)$(yyyy)")
      %elseif (dateFormat == "3")
          @dtw_assign(F_dateIn, "$(yy)$(dateEdit)$(mm)$(dateEdit)$(dd)")
      %elseif (dateFormat == "4")
          @dtw_assign(F_dateIn, "$(yyyy)$(dateEdit)$(mm)$(dateEdit)$(dd)")
      %elseif (dateFormat == "5")
          @dtw_assign(F_dateIn, "$(dd)$(dateEdit)$(mm)$(dateEdit)$(yy)")
      %elseif (dateFormat == "6")
          @dtw_assign(F_dateIn, "$(dd)$(dateEdit)$(mm)$(dateEdit)$(yyyy)")
      %elseif (dateFormat == "7")
          @Get_Month_Desc(mm, mmDesc)
          @dtw_assign(F_dateIn, "$(mmDesc) $(dd),$(yyyy)")
      %elseif (dateFormat == "8")
          @Get_Month_Desc(mm, mmDesc)
          @dtw_assign(F_dateIn, "$(dd) $(mmDesc) $(yyyy)")
      %elseif (dateFormat == "9")
          @Get_Month_Full_Desc(mm, mmDesc)
          @dtw_assign(F_dateIn, "$(mmDesc) $(dd),$(yyyy)")
      %elseif (dateFormat == "10")
          @Get_Month_Full_Desc(mm, mmDesc)
          @dtw_assign(F_dateIn, "$(dd) $(mmDesc) $(yyyy)")
      %endif
  %endif
%}

%MACRO_FUNCTION Format_Date_ISO (IN  CHAR(10) dateIn,
                                 IN  CHAR(2)  dateInFormat,
                                 OUT CHAR(20) F_dateIn)
{
  @dtw_assign(F_dateIn, "")
  @dtw_strip(dateIn, dateIn)
  %if (dateIn != "0001-01-01" && dateIn != "")
      @Date_FromISO_ToCYMD(dateIn, dateIn)
      @Format_Date(dateIn, dateInFormat, F_dateIn)
  %endif
%}

%MACRO_FUNCTION Field_Checked (IN  CHAR(100) fieldValue,
                                   CHAR(100) checkValue,
                               OUT CHAR(7)   fieldCheck)
{
  %if (fieldValue == checkValue)
      @dtw_assign(fieldCheck, "CHECKED")
  %else
      @dtw_assign(fieldCheck, "")
  %endif
%}

%MACRO_FUNCTION Build_Date_Range (IN  CHAR(2) yearYY,
                                  OUT CHAR(7) startCMD,
                                      CHAR(7) endCMD)
{
    %if ( @dtw_rlength(yearYY) == "0")
        @dtw_assign(yearYY, "00")
    %elseif ( @dtw_rlength(yearYY) == "1")
        @dtw_concat("0", yearYY, yearYY)
    %endif
    @dtw_assign(startMDY, "0101")
    @dtw_concat(startMDY, yearYY, startCMD)
    @DateMDYCYMD(startCMD)
    @dtw_assign(endMDY, "1231")
    @dtw_concat(endMDY, yearYY, endCMD)
    @DateMDYCYMD(endCMD)
%}				

%MACRO_FUNCTION DateBirthFromCYMD (INOUT date) {
  %if (date > "0")
      %while(@dtw_rlength(date) != "7") {@dtw_insert("0", date, date)%}
      @dtw_assign(c, "@dtw_rsubstr(date, "1", "1")")
      @dtw_assign(yy, "@dtw_rsubstr(date, "2", "2")")
      %if (c == "0" && yy <= "39")
          @dtw_assign(date, "1@dtw_rsubstr(date, "2", "6")")
      %endif
      @dtw_assign(fromFormat, "*CYMD")
      @dtw_assign(toFormat, "*SYSVAL")
      @Reformat_Date(date, fromFormat, toFormat)
  %endif
%}

%MACRO_FUNCTION DateInputFromCYMD (INOUT date) {
  %if (date > "0")
      %while(@dtw_rlength(date) != "7") {@dtw_insert("0", date, date)%}
      @dtw_assign(fromFormat, "*CYMD")
      @dtw_assign(toFormat, "*SYSVAL")
      @Reformat_Date( date, fromFormat, toFormat)
  %endif
%}

%MACRO_FUNCTION DateTodayCYMD (INOUT date) {
  @dtw_DATE("U", date)
  @dtw_REPLACE(date, "/", "", date)
  %if (date > "0")
      %while(@dtw_rlength(date) != "7") {@dtw_insert("0", date, date)%}
      @dtw_assign(fromFormat, "*MDY")
      @dtw_assign(toFormat, "*CYMD")
      @Reformat_Date( date, fromFormat, toFormat)
  %endif
%}

%MACRO_FUNCTION DateFromCYMD (INOUT date) {
  %if (date > "0")
      %while(@dtw_rlength(date) != "7") {@dtw_insert("0", date, date)%}
      @dtw_assign(fromFormat, "*CYMD")
      @dtw_assign(toFormat, "*SYSVAL")
      @Reformat_Date( date, fromFormat, toFormat)
      @dtw_insert(dateEdit,  date, "2", "1",  date)
      @dtw_insert(dateEdit,  date, "5", "1",  date)
  %endif
%}

%MACRO_FUNCTION DateToCYMD (INOUT date) {
  %if (date > "0")
     %while(@dtw_rlength(date) != "7") {@dtw_insert("0", date, date)%}
     @dtw_assign(fromFormat, "*SYSVAL")
     @dtw_assign(toFormat, "*CYMD")
     @Reformat_Date(date, fromFormat, toFormat)
  %endif
%}

%MACRO_FUNCTION DateMDYCYMD (INOUT date) {
  %if (date > "0")
     %while(@dtw_rlength(date) != "7") {@dtw_insert("0", date, date)%}
     @dtw_assign(fromFormat, "*MDY")
     @dtw_assign(toFormat, "*CYMD")
     @Reformat_Date(date, fromFormat, toFormat)
  %endif
%}

%MACRO_FUNCTION Date_No_Slash (IN    CHAR(10) dateIn,
                               INOUT CHAR(10) dateOut)
{
 %if (@dtw_rpos("/", $(dateIn)) > "0")
     @dtw_assign(dateOut, "$(dateIn)")
     @dtw_delstr(dateOut, "3", "1", dateOut)
     @dtw_delstr(dateOut, "5", "1", dateOut)
 %else
     @dtw_assign(dateOut, "")
 %endif
%}

%MACRO_FUNCTION DateFromISO  (IN    CHAR(10) dateIn,
                              INOUT CHAR(10) dateOut)
{
 %if (dateIn != "0001-01-01")
     @dtw_assign(dateOut, "@dtw_rsubstr(dateIn, "6", "2")@dtw_rsubstr(dateIn, "9", "2")@dtw_rsubstr(dateIn, "3", "2")")
     @dtw_insert(dateEdit,  dateOut, "2", "1",  dateOut)
     @dtw_insert(dateEdit,  dateOut, "5", "1",  dateOut)
 %else
     @dtw_assign(dateOut, "")
 %endif
%}

%MACRO_FUNCTION Date_FromISO_ToCYMD (IN  CHAR(10) dateIn,
                                     OUT CHAR(7)  dateOut)
{
  @dtw_assign(dateOut, "")
  %if (dateIn > "0001-01-01")
      @dtw_assign(mm, "@dtw_rsubstr(dateIn, "6", "2")")
      @dtw_assign(dd, "@dtw_rsubstr(dateIn, "9", "2")")
      @dtw_assign(yy, "@dtw_rsubstr(dateIn, "3", "2")")
      %if (@dtw_rsubstr(dateIn, "1", "1") == "1")
          @dtw_assign(c, "0")
      %else
          @dtw_assign(c, "1")
      %endif
      @dtw_assign(dateOut, "$(c)$(yy)$(mm)$(dd)")
  %endif
%}

%MACRO_FUNCTION Date_CYMD_ISO (IN  CHAR(7)  dateIn,
                               OUT CHAR(10) dateOut)
{
  @dtw_assign(dateOut, "")
  %while(@dtw_rlength(dateIn) != "7") {@dtw_insert("0", dateIn, dateIn)%}
  @dtw_assign(workDate, dateIn)
  @dtw_assign(fromFormat, "*CYMD")
  @dtw_assign(toFormat, "*MDY")
  @Reformat_Date(workDate, fromFormat, toFormat)
  @Date_MDY_ISO(workDate, dateOut)
%}

%MACRO_FUNCTION Date_ISO_MDY (IN    CHAR(10) dateIn,
                              INOUT CHAR(10) dateOut)
{
 %if (dateIn != "0001-01-01" && dateIn != "")
     @dtw_assign(dateOut, "@dtw_rsubstr(dateIn, "6", "2")@dtw_rsubstr(dateIn, "9", "2")@dtw_rsubstr(dateIn, "3", "2")")
 %else
     @dtw_assign(dateOut, "")
 %endif
%}

%MACRO_FUNCTION Date_MDY_ISO (IN    CHAR(10) dateIn,
                              INOUT CHAR(10) dateOut)
{
 %if (dateIn == "0")
     @dtw_assign(dateOut, "0001-01-01")
 %else
     %while(@dtw_rlength(dateIn) != "7") {@dtw_insert("0", dateIn, dateIn)%}
     @dtw_assign(CYMDDate, dateIn)
     @dtw_assign(fromFormat, "*MDY")
     @dtw_assign(toFormat, "*CYMD")
     @Reformat_Date(CYMDDate, fromFormat, toFormat)

     %if (@dtw_rsubstr(CYMDDate, "1", "1") == "1")
         @dtw_assign(year, "20@dtw_rsubstr(CYMDDate, "2", "2")")
     %else
         @dtw_assign(year, "19@dtw_rsubstr(CYMDDate, "2", "2")")
     %endif

     @dtw_assign(dateOut, "$(year)-@dtw_rsubstr(CYMDDate, "4", "2")-@dtw_rsubstr(CYMDDate, "6", "2")")
 %endif
%}

%MACRO_FUNCTION PeriodInputFromCYP (INOUT period) {
  %if (period > "0")
      %while(@dtw_rlength(period) != "5") {@dtw_insert("0", period, period)%}
      @dtw_assign(fromFormat, "*YYPP")
      @Reformat_Period(period, fromFormat)
      @dtw_substr(period, "2", "4", period)
  %endif
%}

%MACRO_FUNCTION PeriodFromCYP (INOUT period) {
  %if (period > "0")
      %while(@dtw_rlength(period) != "5") {@dtw_insert("0", period, period)%}
      @dtw_assign(fromFormat, "*YYPP")
      @Reformat_Period(period, fromFormat)
      @dtw_insert(dateEdit, period, "3", "1", period)
      @dtw_substr(period, "2", "5", period)
  %endif
%}

%MACRO_FUNCTION PeriodToCYP (INOUT period) {
  %if (period > "0")
      %while(@dtw_rlength(period) != "5") {@dtw_insert("0", period, period)%}
      @dtw_assign(fromFormat, "*PPYY")
      @Reformat_Period(period, fromFormat)
  %endif
%}

%MACRO_FUNCTION EditHrsMin (INOUT time) {
  %while(@dtw_rlength(time) != "4") {@dtw_insert("0", time, time)%}
  @dtw_insert(":",  time, "2", "1",  time)
%}

%MACRO_FUNCTION EditHrsMinSec (INOUT time) {
  %while(@dtw_rlength(time) != "6") {@dtw_insert("0", time, time)%}
  @dtw_insert(":",  time, "2", "1",  time)
  @dtw_insert(":",  time, "5", "1",  time)
%}

%MACRO_FUNCTION EditHrsMinNoSec (INOUT time)
  {
       %if (time != "" && time != "&nbsp;")
         @dtw_strip(time, time)
         @dtw_pad("L", time, @dtw_rsubtract("6", @dtw_rlength(time)), "0", time)
         @dtw_substr(time, "1", "4", "0", time)
         @dtw_insert(":",  time, "2", "1",  time)
       %endif
%}

%MACRO_FUNCTION TimeStamp_CYMD (IN    CHAR(26) timestampIn,
                                INOUT CHAR(10) dateOut)
{
 %if (timestampIn == "0001-01-01-00.00.00.000000" || timestampIn == "9999-12-31-24.00.00.000000")
     @dtw_assign(dateOut, "")
 %else
     @dtw_assign(CYMDDate, "0@dtw_rsubstr(timestampIn, "6", "2")@dtw_rsubstr(timestampIn, "9", "2")@dtw_rsubstr(timestampIn, "3", "2")")
     @dtw_assign(fromFormat, "*MDY")
     @dtw_assign(toFormat, "*CYMD")
     @Reformat_Date(CYMDDate, fromFormat, toFormat)
     @dtw_assign(dateOut, CYMDDate)
 %endif
%}

%MACRO_FUNCTION TimeStamp_TIME (IN    CHAR(26) timestampIn,
                                INOUT CHAR(6)  timeOut)
{
 %if (timestampIn == "0001-01-01-00.00.00.000000" || timestampIn == "9999-12-31-24.00.00.000000")
     @dtw_assign(timeOut, "")
 %else
     @dtw_assign(timeOut, @dtw_rsubstr(timestampIn, "12", "8"))
     @dtw_assign(timeOut, @dtw_rReplace(timeOut, ".", ""))
 %endif
%}
%MACRO_FUNCTION TimeStamp_TIME_HM (IN    CHAR(26) timestampIn,
                                   INOUT CHAR(4)  timeOut)
{
 %if (timestampIn == "0001-01-01-00.00.00.000000" || timestampIn == "9999-12-31-24.00.00.000000")
     @dtw_assign(timeOut, "")
 %else
     @dtw_assign(timeOut, @dtw_rsubstr(timestampIn, "12", "5"))
     @dtw_assign(timeOut, @dtw_rReplace(timeOut, ".", ""))
     %if (timeOut == "0000")
         @dtw_assign(timeOut, "")
     %endif
 %endif
%}
%MACRO_FUNCTION CvtEEO (IN    DEC(5,3)  EEOIn,
                        INOUT CHAR(4)   EEOOut)
{
  %if ("@dtw_rsubstr(EEOIn, "3", "1")" == "0")
      @dtw_assign(EEOOut, "@dtw_rsubstr(EEOIn, "1", "1")")
  %else
      %if ("@dtw_rsubstr(EEOIn, "4", "1")" == "0")
         @dtw_assign(EEOOut, "@dtw_rsubstr(EEOIn, "1", "3")")
      %else
         @dtw_assign(EEOOut, "@dtw_rsubstr(EEOIn, "1", "1")")
      %endif
  %endif



%}