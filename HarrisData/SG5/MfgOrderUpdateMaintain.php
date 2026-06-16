<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
if ($tag == "EDIT_DATA") {
    $maintenanceCode = 'E';
    $edtVar = $_GET['edtVar'];
    $returnValue = Maintain_Edit_Handle("HSI215_W", $profileHandle, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
    $edtVar = $returnValue['edtVar'];
    $errVar = $returnValue['errVar'];
    $wrnVar = $returnValue['wrnVar'];
    print "|E|$edtVar|E|$errVar|E|$wrnVar|E|";
} else {
    require_once 'EditRoutines.php';
    require_once 'EdtVar.php';
    require_once 'Menu.php';
    require_once 'NewWindowVariables.php';
    require_once "InventoryControl$dataBaseID.php";
    require_once "SystemControl$dataBaseID.php";
    require_once 'VarBase.php';
    $selRow = (isset($_POST['selRow'])) ? $_POST['selRow'] : 0;
    $page_title = "Manufacturing Order Update";
    $scriptName = "MfgOrderUpdateMaintain.php";
    $scriptVarBase = "{$genericVarBase}";
    $baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
    $rtvSelection = " ";
    $selScheduleJob = " ";
    $saveSelection = " ";
    $submitCallProgram = "CSI215_W";
    $submitEnvProgram = "BROWSER";
    $submitEnvPrinter = " ";
    $submitSchedule = "N";
    $applicationID = "SI";
    $selTYP = array();
    $selPLT = array();
    $selORD = array();
    $thisRow = 0;
    $programName = "HSI215";
    require_once 'ProgSecurityTestInclude.php';
    if ($pgmOptAuth == "F") {
        require_once 'ProgSecurityError.php';
        exit;
    }
    $HSI215_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);  // Program Option Security
}
if ($tag == "FIRST_LOAD") {
    $tag = "GET_NEXT";
    $backHome = $_GET['backHome'];
    $numRows = $_POST['displayedRows'];
    for ($i = 0; $i < $numRows; $i++) {
        $selTYP[$i] = $_POST['UPD' . $i];
        $selPLT[$i] = $_POST['PLT' . $i];
        $selORD[$i] = $_POST['ORD' . $i];
    }
} else {
    $backHome = $_POST['backHome'];
    $numRows  = $_POST['numRows'];
    $selTYP   = $_POST['selTYPS'];
    $selPLT   = $_POST['selPLTS'];
    $selORD   = $_POST['selORDS'];
}
if ($tag == "UPDATE_DATA") {
    $uType = strtoupper($selTYP[$selRow]);
    $selDORS = (isset($_POST['selDORS'])) ? strtoupper($_POST['selDORS']) : 'S';
    $transDate = (isset($_POST['transDate'])) ? $_POST['transDate'] : date('mdy');
    $uPlant = $selPLT[$selRow];
    $uOrder = $selORD[$selRow];
    $maintenanceCode = 'C';
    $edtVar   = " ";
    Concat_Field("@@uptp", $uType);
    Concat_Field("@@srow", $selRow);
    Concat_Field("@@dors", $selDORS);
    Concat_Field("@@date", $transDate);
    Concat_Field("@@dbid", $dataBaseID);
    Concat_Field("@@plt@", $uPlant);
    Concat_Field("@@mord", $uOrder);
    $edtVar .= "}{";
    $returnValue = Maintain_Edit_Handle("HSI215_W", $profileHandle, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
    $errFound = $returnValue['errFound'];
    $edtVar = $returnValue['edtVar'];
    $errVar = $returnValue['errVar'];
    $wrnVar = $returnValue['wrnVar'];
    if ($errFound == "N") {
        $Wrn_LWPCT = Decat_Field("@@lpct", $wrnVar);
        $Wrn_MWPCT = Decat_Field("@@mpct", $wrnVar);
        if ((($Wrn_LWPCT != "") && ($HSI215_OPT['sec_07'] == "N")) || (($Wrn_MWPCT != "") && ($HSI215_OPT['sec_08'] == "N"))) {
            $uType = ($uType == "B") ? "T" : "";
        }
    }	
    if (($errFound == "N") && ($uType != "")) {
        $maintenanceCode = 'U';
        $edtVar= "";
        Concat_Field("@@rqst", "CALL $submitCallProgram PARM('$profileHandle' '$maintenanceCode' '$uType' '$dataBaseID' '$uPlant' '$uOrder' '$selDORS' '$transDate')");
        Concat_Field("@@pgid", $submitEnvProgram);
        Concat_Field("@@prtf", $submitEnvPrinter);
        Concat_Field("@@pref", $submitApplPrefix);
        Concat_Field("@@apid", $applicationID);
        require 'ScheduleJobConcat.php';   // Schedule Entries Values
        $edtVar .= "}{";
        $pgmCall = array(
            array("Name"=>"profileHandle",  "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
            array("Name"=>"dataBaseID",     "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
            array("Name"=>"submitSchedule", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
            array("Name"=>"errFound",       "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
            array("Name"=>"edtVar",         "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
            array("Name"=>"errVar",         "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

        $pgm = i5_program_prepare("HSYSSS_W", $pgmCall);
        if (!$pgm) {die("<br>Validate_Data (HSYSSS_W) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}
        $tStamp = date("Y-m-d-H.i.s.u");
        $tseq = 0;
        $terr = 'N';
        if (($uType == "T") || ($uType == "B")) {
            $ttype = 'FT';
            $insSQL = "Insert into HDMOTCQ ";
            $insSQL .= " (O8PLT, O8ORD, O8SEQN, O8MTCD, O8ERR, O8TSUS, O8TSTP) ";
            $insSQL .= " VALUES ({$uPlant},'{$uOrder}',{$tseq},'{$ttype}','{$terr}','{$userProfile}','{$tStamp}')";
            $status = db2_exec($i5Connect->getConnection (), $insSQL);
            if (!$status) {die("<br>Error in SQL statement 1.");}
        }
        if (($uType == "C") || ($uType == "B")) {
            $ttype = 'CL';
            $insSQL = "Insert into HDMOTCQ ";
            $insSQL .= " (O8PLT, O8ORD, O8SEQN, O8MTCD, O8ERR, O8TSUS, O8TSTP) ";
            $insSQL .= " VALUES ({$uPlant},'{$uOrder}',{$tseq},'{$ttype}','{$terr}','{$userProfile}','{$tStamp}')";
            $status = db2_exec($i5Connect->getConnection(), $insSQL);
            if (!$status) {die("<br>Error in SQL statement 2.");}
        }
	    $parmIn = array(
            "profileHandle"  =>$profileHandle,
            "dataBaseID"     =>$dataBaseID,
            "submitSchedule" =>$submitSchedule,
            "errFound"       =>$errFound,
            "edtVar"         =>$edtVar,
            "errVar"         =>$errVar);

        $parmOut = array(
            "profileHandle"  =>"profileHandle",
            "dataBaseID"     =>"dataBaseID",
            "submitSchedule" =>"submitSchedule",
            "errFound"       =>"errFound",
            "edtVar"         =>"edtVar",
            "errVar"         =>"errVar");

        $ret = i5_program_call($pgm, $parmIn, $parmOut);
        if (function_exists('i5_output')) extract(i5_output());
        if (!$ret) {
            $errMsg = "<br>Validate_Data (HSYSSS_W) call errno=".i5_errno()." msg=".i5_errormsg();
            $stmtSQL = "Delete from HDMOTCQ Where (O8PLT,O8ORD)=({$uPlant},'{$uOrder}') ";
            $sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
            die($errMsg);
        }
    }		
    if ($errFound != "Y") {
        $selRow++;
        $tag = "GET_NEXT";
    } else {
        $tag = "REPORT";
    }
}
if ($tag == "GET_NEXT") {
     for ($thisRow=$selRow; $thisRow<$numRows; $thisRow++) {
         $uType = $selTYP[$thisRow];
         if ($uType != "") {
             $selDORS = $CIFEDS='' ? 'S' : $CIFEDS;
             $transDate = date('mdy');
             $uPlant = $selPLT[$thisRow];
             $uOrder = $selORD[$thisRow];
            break;
         }
    }
    $selRow=$thisRow;
    $errFound = "N";
    if ($selRow == $numRows) {
        print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$backHome}\">";
        exit;
    }
    $tag = "REPORT";
    if ((($HSI215_OPT['sec_02'] == "N") && ($uType == "T" || $uType == "B")) || (($HSI215_OPT['sec_03'] == "N") && ($uType == "C" || $uType == "B"))) {
        require_once 'ProgSecurityError.php';
        exit;
    }
}
if ($tag == "REPORT") {
    require_once($docType);
    print "\n <html> <head>";
    require_once($headInclude);
    $formName = "Chg";
    print "\n <script TYPE=\"text/javascript\">";
    require_once 'Menu.js';
    require_once 'CheckSel.js';
    require_once 'CalendarInclude.php';
    require_once 'CheckEnterSearch.php';
    require_once 'NoFormValidate.php';
    require_once 'SaveCurrentURL.php';
    require_once 'ShowHideSelCriteria.php';
    print "\n </script> \n";
    require_once($genericHead);
    print "\n </head>";

    print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
    require_once 'Banner.php';        // Harrisdata Header
    print "\n <table $baseTable>";
    print "\n <tr valign=\"top\">";
    $pageID = "MFGORDERUPDATEMAINTAIN";
    require_once 'MenuDisplay.php';   // Sidebar Menu
    print "\n <td class=\"content\">";
    print "\n <script TYPE=\"text/javascript\">";
    require_once 'AJAXRequest.js';
    require_once 'CheckSel.js';
    require_once 'CheckEnterSearch.php';
    require_once 'NoFormValidate.php';
    require_once 'DateEdit.php';
    require_once 'SaveCurrentURL.php';
    require_once 'ShowHideSelCriteria.php';
    print "\n function dateSearch(transDate){ ";
    print "\n     calWindow('transDate');";
    print "\n     window.addEventListener('focus', daySearch);";
    print "\n }";
    print "\n function daySearch(event){ ";
    print "\n     var stepA = document.getElementById('transDate').value;";
    print "\n     var stepB = stepA.substring(0,2) + '-' + stepA.substring(2,4) +'-20' + stepA.substring(4);";
    print "\n     var stepC = Date.parse(stepB);";
    print "\n     var newDate = new Date(stepC);";
    print "\n     var dayNum  = newDate.getDay();";
    print "\n     var dayString = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][dayNum];";
    print "\n     document.getElementsByName(\"transDay\")[0].value = dayString;";
    print "\n     window.removeEventListener('focus', daySearch);";
    print "\n }";
    print "\n function validate(chgForm) {";
    print "\n     if (chgForm.transDate.value ==\"\") {";
    print "\n         alert(\"$reqFieldError\"); ";
    print "\n         return false; ";
    print "\n     } ";
    print "\n     if (editdate(chgForm.transDate)) { ";
    print "\n         return true;";
    print "\n     } ";
    print "\n } ";
    print "\n function gotoNext() {";
    print "\n     var nextTag = \"GET_NEXT\";";
    print "\n     var next =  document.getElementById('selRow');";
    print "\n     next.value = parseInt(next.value) + 1;";
    print "\n     var url = \"" . $homeURL . $phpPath . "MfgOrderUpdateMaintain.php" . $scriptVarBase . "&tag=\" + escape(nextTag);"; 
    print "\n     document.getElementById('Chg').action = url;";
    print "\n     document.getElementById('Chg').submit();";
    print "\n } ";
    print "\n </script> \n";

    switch ($uType) {
        case "T":
            $page_title = 'Manufacturing Order Final Tag';
            break;
        case "C":
            $page_title = 'Manufacturing Order Close';
            break;
        case "B":
            $page_title = 'Manufacturing Order Final Tag and Close';
    }
    print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
    print "\n <tr><td><h1>$page_title</h1></td>";
    print "\n <td class=\"toolbar\">";
    print "\n &nbsp;<a href=\"javascript:check(document.Chg)\">$selectAcceptImage</a>";
    print "\n &nbsp;<a href=\"javascript:gotoNext()\">$cancelImageMed</a>";
    print "\n &nbsp;<a href=\"{$backHome}\" title=\"Back Home\">$portalHomeMed</a>";
    print "</td>";
    print "\n </tr></table>";

    $uv_PlantName = "OHPLT";
    require 'UserView.php';
    require 'stmtSQLClear.php';
    $stmtSQL .= " Select OHPLT, OHORD, OHPN, OHOTYP, OHCDDT, OHCSDT, OHCQTY, OHRORD, OHORD#, OHORL#, OHBLN#, OHPRIC, O2UNIT, MWPCT, LWPCT ";
    $fileSQL .= " HDMOHMV02 ";
    $selectSQL .= " OHPLT=$uPlant and OHORD='$uOrder' ";
    require 'stmtSQLSelect.php';
    require 'stmtSQLEnd.php';
    require 'stmtSQLTotalRows.php';

    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $row = db2_fetch_assoc($sqlResult);

    $focusField = "transDate";
    if ($errFound == "N") {
        $maintenanceCode = 'C';
        $edtVar   = " ";
        Concat_Field("@@uptp", $uType);
        Concat_Field("@@srow", $selRow);
        Concat_Field("@@dors", $selDORS);
        Concat_Field("@@date", $transDate);
        Concat_Field("@@dbid", $dataBaseID);
        Concat_Field("@@plt@", $uPlant);
        Concat_Field("@@mord", $uOrder);
        $edtVar .= "}{";
        $returnValue = Maintain_Edit_Handle("HSI215_W", $profileHandle, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
        $errFound = $returnValue['errFound'];
        $edtVar = $returnValue['edtVar'];
        $errVar = $returnValue['errVar'];
        $wrnVar = $returnValue['wrnVar'];
    }	
    $focusField = "";
    $errVar = ErrVarErr($profileHandle, $errVar);
    $Err_selDORS = DecatErr_Field("@@dors", "selDORS");
    $Err_transDate = DecatErr_Field("@@date", "transDate");
    $Err_OHPLT = DecatErr_Field("@@plt@", "plantNumber");
    $Err_OHORD = DecatErr_Field("@@mord", "mfgOrder");
    $Err_OHPN = DecatErr_Field("@@item", "itemNumber");
    $Err_OHOTYP = DecatErr_Field("@@otyp", "orderType");
    $Err_OHCDDT = DecatErr_Field("@@cddt", "dueDate");
    $Err_OHCSDT = DecatErr_Field("@@csdt", "startDate");
    $Err_OHCQTY = DecatErr_Field("@@cqty", "orderQty");
    $Err_OHRORD = DecatErr_Field("@@rord", "refOrder");
    $Err_OHSORD = DecatErr_Field("@@sord", "salesOrder");
    $Err_OHSLNE = DecatErr_Field("@@slne", "salesLine");
    $Err_OHSRLS = DecatErr_Field("@@srls", "salesRls");
    $Err_OHPRIC = DecatErr_Field("@@pric", "unitPrc");
    $Err_O2UNIT = DecatErr_Field("@@unit", "unitCost");
    $Err_MWPCT = DecatErr_Field("@@mpct", "mVariance");
    $Err_LWPCT = DecatErr_Field("@@lpct", "lVariance");
    $edtVar    = EdtVarErr($profileHandle, $edtVar);
    $selRow    = Decat_Field("@@srow", $edtVar);
    $selDORS   = Decat_Field("@@dors", $edtVar);
    $transDate = Decat_Field("@@date", $edtVar);
    $Wrn_MWPCT = Decat_Field("@@mpct", $wrnVar);
    $Wrn_LWPCT = Decat_Field("@@lpct", $wrnVar);
 
    print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" id=\"Chg\" ACTION=\"{$baseURL}&amp;tag=UPDATE_DATA\">";
    print "\n <table $contentTable> <tr>";
    $textOvr=SetTextOvr($Err_OHPLT);
    $textOvr=SetTextOvr($Err_OHORD);
    $textOvr=SetTextOvr($Err_OHPN);
    $plantDesc = RetValue("PLPLNT={$row['OHPLT']}", "HDPLNT", "PLNAME");
    Format_Header_URL("Plant", $plantDesc, $row['OHPLT'], "");
    Format_Header_URL("Manufacturing Order", $row['OHORD'], "", "");
    $itemDesc = RetValue("IMITEM='$row[OHPN]'", "HDIMST", "IMIMDS");
    Format_Header_URL("Item", $row['OHPN'], $itemDesc, "");
    DspErrMsg($Err_OHPLT);
    DspErrMsg($Err_OHORD);
    DspErrMsg($Err_OHPN);
    print "\n </tr></table> ";
    require_once 'ConfMessageDisplay.php';
    print $hrTagAttr;

    print "\n <table $contentTable><tr>";
    print "\n <td><input name=\"selRow\" id=\"selRow\" type=\"hidden\" value=\"$selRow\"></td>";
    print "\n <td><input name=\"numRows\" id=\"numRows\" type=\"hidden\" value=\"$numRows\"></td>";
    print "\n <td><input name=\"backHome\" type=\"hidden\" value=\"$backHome\"> </td>";
    for ($pos = 0; $pos < $numRows; $pos++) {
        print "\n <td><input name=\"selTYPS[]\" id=\"selTYPS\" type=\"hidden\" value=\"$selTYP[$pos]\"> </td>";
        print "\n <td><input name=\"selPLTS[]\" id=\"selPLTS\" type=\"hidden\" value=\"$selPLT[$pos]\"> </td>";
        print "\n <td><input name=\"selORDS[]\" id=\"selORDS\" type=\"hidden\" value=\"$selORD[$pos]\"> </td>";
    }
    if ($uType != "T") {
        Build_Fld_Entry("Print Detail or Summary","selDORS","inputalph","DORS","DORS",$selDORS,$Err_selDORS,"1","1","Y","","");
    }
    $textOvr = SetTextOvr($Err_transDate);
    $transDay = trim(date('l', strtotime($transDate)));
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Date</span></td>";
    print "\n <td class=\"inputdate\"><input type=\"text\" name=\"transDate\" id=\"transDate\" value=\"$transDate\" size=\"6\" maxlength=\"6\">";
    print "\n     <a href=\"javascript:dateSearch('transDate');\"> $reqFieldChar $calendarImage </a> </td>";
    print "\n <td class=\"dspalph\"><input type=\"text\" readonly=\"true\" style=\"border: none\" name=\"transDay\" value=\"$transDay\" > </td> ";
    print "\n </tr> ";
    DspErrMsg($Err_transDate);

    $textOvr = SetTextOvr($Err_OHOTYP);
    Build_DspFld("Order Type", $row['OHOTYP'], "", "A");
    DspErrMsg($Err_OHOTYP);

    $textOvr = SetTextOvr($Err_OHCDDT);
    $fieldDesc = trim(date('l', strtotime($row['OHCDDT'])));
    $W_DDate = Format_Date_ISO($row['OHCDDT'], "D");
    Build_DspFld("Due Date", $W_DDate, "$fieldDesc", "D");
    DspErrMsg($Err_OHCDDT);

    $textOvr = SetTextOvr($Err_OHCSDT);
    $fieldDesc = trim(date('l', strtotime($row['OHCSDT'])));
    $W_SDate = Format_Date_ISO($row['OHCSDT'], "D");
    Build_DspFld("Start Date", $W_SDate, "$fieldDesc", "D");
    DspErrMsg($Err_OHCSDT);

    $textOvr = SetTextOvr($Err_OHCQTY);
    Build_DspFld("Order Quantity", $row['OHCQTY'], "", "N");
    DspErrMsg($Err_OHCQTY);

    $textOvr = SetTextOvr($Err_OHRORD);
    Build_DspFld("Reference Order", $row['OHRORD'], "", "A");
    DspErrMsg($Err_OHRORD);

    $textOvr = SetTextOvr($Err_OHSORD);
    Build_DspFld("Sales Order", $row['OHORD#'], "", "N");
    DspErrMsg($Err_OHSORD);

    $textOvr = SetTextOvr($Err_OHSLNE);
    Build_DspFld("Sales Line", $row['OHORL#'], "", "N");
    DspErrMsg($Err_OHSLNE);

    $textOvr = SetTextOvr($Err_OHSRLS);
    Build_DspFld("Sales Release", $row['OHBLN#'], "", "N");
    DspErrMsg($Err_OHSRLS);

    $textOvr = SetTextOvr($Err_OHPRIC);
    Build_DspFld("Unit Price", $row['OHPRIC'], "", "N");
    DspErrMsg($Err_OHPRIC);

    $textOvr = SetTextOvr($Err_O2UNIT);
    Build_DspFld("Current Unit Cost", $row['O2UNIT'], "", "N");
    DspErrMsg($Err_O2UNIT);

    $textOvr = ($Err_MWPCT == '') ? SetTextOvr($Wrn_MWPCT) : SetTextOvr($Err_MWPCT);
    $F_MWPCT = number_format($row['MWPCT'],3);
    Build_DspFld("Material Variance", $F_MWPCT, "", "N");
    if ($Err_MWPCT != "") {
        DspErrMsg($Err_MWPCT);
    } else {
        DspErrMsg($Wrn_MWPCT);
    }

    $textOvr = ($Err_LWPCT == '') ? SetTextOvr($Wrn_LWPCT) : SetTextOvr($Err_LWPCT);
    $F_LWPCT = number_format($row['LWPCT'],3);
    Build_DspFld("Labor Variance", $F_LWPCT, "", "N");
    if ($Err_LWPCT != "") {
        DspErrMsg($Err_LWPCT);
    } else {
        DspErrMsg($Wrn_LWPCT);
    }
    print "\n </tr>";
    print "\n </table> ";
    print "\n </form> \n\n";
    print $hrTagAttr;
    require_once 'Copyright.php';
    print "\n </td> </tr> </table>";
    require_once 'Trailer.php';
    print "\n </body> </html>";
}
?>