<?php
$qsOpt .= "\n <option value=\"IVAINV|null|Invoice|N|\" title=\"Invoice\" SELECTED>Invoice";
$qsOpt .= "\n <option value=\"Coalesce(PEAMT,0)|null|Payment Amount|N|\" title=\"Payment Amount\">Payment Amount";
if ($paymentType=="C" || $paymentType=="U" || $paymentType=="Y") {$qsOpt .= "\n <option value=\"Coalesce(PEDAMT,Case When $DscAmtSQL When ABS($InvBalSQL) < ABS($DscBalSQL) Then $InvBalSQL Else $DscBalSQL End,0)|null|Discount|N|\" title=\"Discount\">Discount";}
$qsOpt .= "\n <option value=\"$InvBalSQL|null|Invoice Balance|N|\" title=\"Invoice Balance\">Invoice Balance";
$qsOpt .= "\n <option value=\"$NetBalSQL|null|Net Balance|N|\" title=\"Net Balance\">Net Balance";
$qsOpt .= "\n <option value=\"IVFRT|null|Freight|N|\" title=\"Freight\">Freight";
$qsOpt .= "\n <option value=\"IVSTAX|null|Tax|N|\" title=\"Tax\">Tax";
$qsOpt .= "\n <option value=\"IVSPC|null|Special Charge|N|\" title=\"Special Charge\">Special Charge";
if ($fromType=="P") {$qsOpt .= "\n <option value=\"IVBLTO|null|Bill-To|N|\" title=\"Bill-To\">Bill-To";}
$qsOpt .= "\n <option value=\"coalesce(aa.CMCNA1U, ' ')|null|Bill-To Name|A|U\" title=\"Bill-To Name\">Bill-To Name";
$qsOpt .= "\n <option value=\"IVTRMS|null|Terms|A|U\" title=\"Terms\">Terms";
$qsOpt .= "\n <option value=\"coalesce(Upper(TMCTDS), ' ')|null|Terms Description|A|U\" title=\"Terms Description\">Terms Description";
$qsOpt .= "\n <option value=\"IVDUED|DATE|Due Date|I|\" title=\"Due Date\">Due Date";
$qsOpt .= "\n <option value=\"IVIVDT|DATE|Invoice Date|D|\" title=\"Invoice Date\">Invoice Date";
$qsOpt .= "\n <option value=\"IVARPO|null|Reference Number|A|U\" title=\"Reference Number\">Reference Number";
$qsOpt .= "\n <option value=\"IVORD|null|Order Number|N|\" title=\"Order Number\">Order Number";
$qsOpt .= "\n <option value=\"IVORDT|DATE|Order Date|D|\" title=\"Order Date\">Order Date";
$qsOpt .= "\n <option value=\"IVORLN|null|Line Number|N|\" title=\"Line Number\">Line Number";
$qsOpt .= "\n <option value=\"IVPLT|null|Plant|N|\" title=\"Plant\">Plant";
$qsOpt .= "\n <option value=\"coalesce(Upper(PLNAME), ' ')|null|Plant Name|A|U\" title=\"Plant Name\">Plant Name";
$qsOpt .= "\n <option value=\"IVMORD|null|Mfg Order|A|U\" title=\"Mfg Order\">Mfg Order";
$qsOpt .= "\n <option value=\"IVIVAM|null|Invoice Amount|N|\" title=\"Invoice Amount\">Invoice Amount";
$qsOpt .= "\n <option value=\"IVLOC|null|Location|N|\" title=\"Location\">Location";
$qsOpt .= "\n <option value=\"coalesce(Upper(LOLNA1), ' ')|null|Location Name|A|U\" title=\"Location Name\">Location Name";
$qsOpt .= "\n <option value=\"IVSLSM|null|Salesman|N|\" title=\"Salesman\">Salesman";
$qsOpt .= "\n <option value=\"coalesce(Upper(SMSNA1), ' ')|null|Salesman Name|A|U\" title=\"Salesman Name\">Salesman Name";
$qsOpt .= "\n <option value=\"IVCUST|null|Ship-To|N|\" title=\"Ship-To\">Ship-To";
$qsOpt .= "\n <option value=\"coalesce(bb.CMCNA1U, ' ')|null|Ship-To Name|A|U\" title=\"Ship-To Name\">Ship-To Name";
$qsOpt .= "\n <option value=\"IVPSDT|DATE|Last Posted Date|D|\" title=\"Last Posted Date\">Last Posted Date";
$qsOpt .= "\n <option value=\"IVSBCD|null|Created By Payment Code|A|U\" title=\"Created By Payment Code\">Created By Payment Code";
$qsOpt .= "\n <option value=\"coalesce(PSDESCU, ' ')|null|Created By Payment Code Description|A|U\" title=\"Created By Payment Code Description\">Created By Payment Code Description";
$qsOpt .= "\n <option value=\"IVIVCD|null|Invoice Code|A|U\" title=\"Invoice Code\">Invoice Code";
?>
