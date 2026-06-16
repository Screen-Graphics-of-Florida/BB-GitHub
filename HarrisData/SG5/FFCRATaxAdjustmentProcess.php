<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'StoredProcedureVariablesInclude.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "FFCRA Adjust Employer Social Security";
$scriptName = "FFCRATaxAdjustmentProcess.php";
$scriptVarBase = "{$genericVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$process = (isset($_GET['process'])) ? $_GET['process'] : null;

require_once($docType);
print "\n
<html> <head> ";
require_once($headInclude);
print "\n <script TYPE=\"text/javascript\">  ";
require_once 'Menu.js';
print "\n </script> ";
require_once($genericHead);
print "\n    </head> ";
print "\n    <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "FFCRATaxAdjust";
if ($formatToPrint == "") {
    require_once 'MenuDisplay.php';
}
print "\n <td class=\"content\">";
print "\n <table $contentTable>";
print "\n <colgroup><col width=\"75%\"><col width=\"20%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

print "\n <td class=\"toolbar\">";
print "\n   <a href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;process=Y\">$sbmSchdImage</a> ";
$designIcon = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgHelp.gif\" title=\"View FFCRA Payroll Tax Credit Documentation. User ID available from HarrisData Marketing is required.\" alt=\"Help\">";
print "<a href=\"https://hdcommunity.atlassian.net/wiki/spaces/AOH/pages/312147969/FFCRA+Payroll+Tax+Credit\" target=\"_blank\">{$designIcon}</a>";
print "\n </td> ";

print "\n </tr> ";
print "\n </table> ";
if (trim(FFCRA_PayCodes) == '') {
    print "<tr class=\"error\"><td class=\"confMsg\">Qualified Health Plans (FFCRA_PayCodes) have not been defined in<br> Configuration->Control Panel Plus->EIP Configuration->BaseFormat</td></tr>";
    print "\n </table> ";
    exit();
}
require_once 'ConfMessageDisplay.php';

print $hrTagAttr;
require_once 'Copyright.php';

$arr = explode(', ', $FFCRA_PayCodes);
$FFCRA_PayCodes = "'" . implode("', '", $arr) . "'";

if (!is_null($process)) {
    updateWages();
    updateTaxes();
    $confMessage = "Confirm Processing of Employer Social Security Adjustments";
    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']) . " \"> ";
    exit();
}

function updateWages()
{
    global $i5Connect, $FFCRA_PayCodes;

    $stmtSQL = "Update PRCKHS hist set
                EHUD1W = (
                  Select EHUD1W from (
                        Select
                            chk.EHPREF,
                            case
                                when q.YTD_EHTOAS <= 137700.00 then max(-q.YTD_EHTOAS, - q.YTD_EXEMPT)  
                                when q.YTD_EHTOAS - q.YTD_EXEMPT >= 137700.00 then 0                    
                                else q.YTD_EXEMPT - (q.YTD_EHTOAS - 137700.00)                          
                                end as EHUD1W                                                           
                        from PRCKHS chk
                            join (
                                Select c.EHPREF,
                                    (Select sum(EHROA) from PRCKHS chk where EHCKTY > 1 and EHRCCD <> 'V'
                                        and chk.EHUD1W = 0 and c.EHCOMP = chk.EHCOMP and c.EHFACL = chk.EHFACL and c.EHEMPL = chk.EHEMPL and year(F_MAKEDATE(chk.EHCKDT)) = 2020)
                                    as YTD_EHROA,
                                    (Select sum(EHTOAS) + sum(EHUD1N) from PRCKHS chk where EHCKTY > 1 and EHRCCD <> 'V'
                                        and chk.EHUD1W = 0 and c.EHCOMP = chk.EHCOMP and c.EHFACL = chk.EHFACL and c.EHEMPL = chk.EHEMPL and year(F_MAKEDATE(chk.EHCKDT)) = 2020)
                                    as YTD_EHTOAS,
                                    (Select sum(SHEARN) from PRCKHS chk join PRDTHS tran on (chk.EHPREF = tran.SHPREF)
                                        where EHCKTY > 1 and EHRCCD <> 'V'
                                        and chk.EHUD1W = 0 and c.EHCOMP = chk.EHCOMP and c.EHFACL = chk.EHFACL and c.EHEMPL = chk.EHEMPL and year(F_MAKEDATE(chk.EHCKDT)) = 2020 
                                        and SHCODE in (" . $FFCRA_PayCodes . ") )
                                    as YTD_EXEMPT
                                from PRCKHS c
                                    join (Select EHCOMP, EHFACL, EHEMPL, max(EHPREF) as EHPREF from PRCKHS 
                                          where EHCKTY = 3 and EHRCCD = 'H'
                                          and EHPREF in (Select SHPREF From PRDTHS Where SHCODE in (" . $FFCRA_PayCodes . ")) 
                                          group by EHCOMP, EHFACL, EHEMPL) k on (k.EHPREF = c.EHPREF)
                                where c.EHCKTY = 3 and c.EHRCCD = 'H' and year(F_MAKEDATE(c.EHCKDT)) = 2020 
                                ) q on (q.EHPREF = chk.EHPREF)
                        where chk.EHPREF in (Select SHPREF From PRDTHS Where SHCODE in (" . $FFCRA_PayCodes . "))                                       
                            and ( case
                                when q.YTD_EHTOAS <= 137700.00 then max(-q.YTD_EHTOAS, - q.YTD_EXEMPT) 
                                when q.YTD_EHTOAS - q.YTD_EXEMPT >= 137700.00 then 0                    
                                else q.YTD_EXEMPT - (q.YTD_EHTOAS - 137700.00)                          
                                end  ) <> 0
                ) q1
                where q1.EHPREF = hist.EHPREF
            )
            where hist.EHPREF in (
                Select
                    chk.EHPREF
                from PRCKHS chk
                    join (
                        Select c.EHPREF,
                            (Select sum(EHROA) from PRCKHS chk where EHCKTY > 1 and EHRCCD <> 'V'
                                and chk.EHUD1W = 0 and c.EHCOMP = chk.EHCOMP and c.EHFACL = chk.EHFACL and c.EHEMPL = chk.EHEMPL and year(F_MAKEDATE(chk.EHCKDT)) = 2020)
                            as YTD_EHROA,
                            (Select sum(EHTOAS) + sum(EHUD1N) from PRCKHS chk where EHCKTY > 1 and EHRCCD <> 'V'
                                and chk.EHUD1W = 0 and c.EHCOMP = chk.EHCOMP and c.EHFACL = chk.EHFACL and c.EHEMPL = chk.EHEMPL and year(F_MAKEDATE(chk.EHCKDT)) = 2020)
                            as YTD_EHTOAS,
                            (Select sum(SHEARN) from PRCKHS chk join PRDTHS tran on (chk.EHPREF = tran.SHPREF)
                                where EHCKTY > 1 and EHRCCD <> 'V'
                                and chk.EHUD1W = 0 and c.EHCOMP = chk.EHCOMP and c.EHFACL = chk.EHFACL and c.EHEMPL = chk.EHEMPL and year(F_MAKEDATE(chk.EHCKDT)) = 2020 
                                and SHCODE in (" . $FFCRA_PayCodes . ") )
                            as YTD_EXEMPT
                        from PRCKHS c
                            join (Select EHCOMP, EHFACL, EHEMPL, max(EHPREF) as EHPREF from PRCKHS 
                                  where EHCKTY = 3 and EHRCCD = 'H' 
                                  and EHPREF in (Select SHPREF From PRDTHS Where SHCODE in (" . $FFCRA_PayCodes . ")) 
                                  group by EHCOMP, EHFACL, EHEMPL) k on (k.EHPREF = c.EHPREF)
                        where c.EHCKTY = 3 and c.EHRCCD = 'H' and year(F_MAKEDATE(c.EHCKDT)) = 2020 
                        ) q on (q.EHPREF = chk.EHPREF)
                where chk.EHPREF in (Select SHPREF From PRDTHS Where SHCODE in (" . $FFCRA_PayCodes . "))                                        
                    and ( case
                        when q.YTD_EHTOAS <= 137700.00 then max(-q.YTD_EHTOAS, - q.YTD_EXEMPT)  
                        when q.YTD_EHTOAS - q.YTD_EXEMPT >= 137700.00 then 0                    
                        else q.YTD_EXEMPT - (q.YTD_EHTOAS - 137700.00)                          
                        end ) <> 0
            )";
    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

}

function updateTaxes()
{
    global $i5Connect, $FFCRA_PayCodes;

    $stmtSQL = "Update PRCKHS hist set
            EHUD1N = (
            Select EHUD1N from (
                    Select
                          chk.EHPREF, min(max(0,round( (q.YTD_EHTOAS - q.YTD_EXEMPT) * 0.062,2)), 8537.40) - q.YTD_EHROA as EHUD1N             
                    from PRCKHS chk
                        join (
                            Select c.EHPREF,
                                (Select sum(EHROA) from PRCKHS chk where EHCKTY > 1 and EHRCCD <> 'V'
                                    and chk.EHUD1N = 0 and c.EHCOMP = chk.EHCOMP and c.EHFACL = chk.EHFACL and c.EHEMPL = chk.EHEMPL and year(F_MAKEDATE(chk.EHCKDT)) = 2020)
                                    as YTD_EHROA,
                                (Select sum(EHTOAS) + sum(EHUD1N) from PRCKHS chk where EHCKTY > 1 and EHRCCD <> 'V'
                                    and chk.EHUD1N = 0 and c.EHCOMP = chk.EHCOMP and c.EHFACL = chk.EHFACL and c.EHEMPL = chk.EHEMPL and year(F_MAKEDATE(chk.EHCKDT)) = 2020)
                                    as YTD_EHTOAS,
                                (Select sum(SHEARN) from PRCKHS chk join PRDTHS tran on (chk.EHPREF = tran.SHPREF)
                                    where EHCKTY > 1 and EHRCCD <> 'V'
                                    and chk.EHUD1N = 0 and c.EHCOMP = chk.EHCOMP and c.EHFACL = chk.EHFACL and c.EHEMPL = chk.EHEMPL and year(F_MAKEDATE(chk.EHCKDT)) = 2020 and SHCODE in (" . $FFCRA_PayCodes . ") )
                            as YTD_EXEMPT
                            from PRCKHS c
                                join (Select EHCOMP, EHFACL, EHEMPL, max(EHPREF) as EHPREF from PRCKHS 
                                where EHCKTY = 3 and EHRCCD = 'H' 
                                and EHPREF in (Select SHPREF From PRDTHS Where SHCODE in (" . $FFCRA_PayCodes . ")) 
                                group by EHCOMP, EHFACL, EHEMPL) k on (k.EHPREF = c.EHPREF)
                            where c.EHCKTY = 3 and c.EHRCCD = 'H' and year(F_MAKEDATE(c.EHCKDT)) = 2020 
                            ) q on (q.EHPREF = chk.EHPREF)
                    where chk.EHPREF in (Select SHPREF From PRDTHS Where SHCODE in (" . $FFCRA_PayCodes . "))                                        
                        and ( min(max(0,round( (q.YTD_EHTOAS - q.YTD_EXEMPT) * 0.062,2)), 8537.40) - q.YTD_EHROA ) <> 0
                ) q2
                where q2.EHPREF = hist.EHPREF
            )
            
            where hist.EHPREF in (
                            Select
                                chk.EHPREF
                            from PRCKHS chk
                                join (
                                    Select c.EHPREF,
                                        (Select sum(EHROA) from PRCKHS chk where EHCKTY > 1 and EHRCCD <> 'V'
                                            and chk.EHUD1N = 0 and c.EHCOMP = chk.EHCOMP and c.EHFACL = chk.EHFACL and c.EHEMPL = chk.EHEMPL and year(F_MAKEDATE(chk.EHCKDT)) = 2020)
                                            as YTD_EHROA,
                                        (Select sum(EHTOAS) + sum(EHUD1N) from PRCKHS chk where EHCKTY > 1 and EHRCCD <> 'V'
                                            and chk.EHUD1N = 0 and c.EHCOMP = chk.EHCOMP and c.EHFACL = chk.EHFACL and c.EHEMPL = chk.EHEMPL and year(F_MAKEDATE(chk.EHCKDT)) = 2020)
                                            as YTD_EHTOAS,
                                        (Select sum(SHEARN) from PRCKHS chk
                                                        join PRDTHS tran on (chk.EHPREF = tran.SHPREF)
                                            where EHCKTY > 1 and EHRCCD <> 'V'
                                            and chk.EHUD1N = 0 and c.EHCOMP = chk.EHCOMP and c.EHFACL = chk.EHFACL and c.EHEMPL = chk.EHEMPL and year(F_MAKEDATE(chk.EHCKDT)) = 2020 
                                            and SHCODE in (" . $FFCRA_PayCodes . ") )
                                    as YTD_EXEMPT
                                    from PRCKHS c
                                        join (Select EHCOMP, EHFACL, EHEMPL, max(EHPREF) as EHPREF from PRCKHS 
                                        where EHCKTY = 3 and EHRCCD = 'H'
                                        and EHPREF in (Select SHPREF From PRDTHS Where SHCODE in (" . $FFCRA_PayCodes . "))  
                                        group by EHCOMP, EHFACL, EHEMPL) k on (k.EHPREF = c.EHPREF)
                                    where c.EHCKTY = 3 and c.EHRCCD = 'H' and year(F_MAKEDATE(c.EHCKDT)) = 2020 
                                    ) q on (q.EHPREF = chk.EHPREF)
                            where chk.EHPREF in (Select SHPREF From PRDTHS Where SHCODE in (" . $FFCRA_PayCodes . "))                                        
                                and ( min(max(0,round( (q.YTD_EHTOAS - q.YTD_EXEMPT) * 0.062,2)), 8537.40) - q.YTD_EHROA ) <> 0
            )";
    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
}

?>
