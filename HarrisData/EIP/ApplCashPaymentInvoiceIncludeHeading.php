<?php
	if ($columnDisplay['IVFRT']=="Y") {
		$returnValue=OrderBy_Sort("IVFRT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Freight\"   title=\"Sequence By Freight\">{$sortPoint}Freight</a></th>";
	}
	if ($columnDisplay['IVSTAX']=="Y") {
		$returnValue=OrderBy_Sort("IVSTAX"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Tax\"       title=\"Sequence By Tax\">{$sortPoint}Tax</a></th>";
	}
	if ($columnDisplay['IVSPC']=="Y") {
		$returnValue=OrderBy_Sort("IVSPC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SpcChg\"       title=\"Sequence By Special Charge\">{$sortPoint}Special Charge</a></th>";
	}
	if ($columnDisplay['IVBLTO']=="Y" && $fromType=="P") {
		$returnValue=OrderBy_Sort("IVBLTO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BillTo\"       title=\"Sequence By Bill-To\">{$sortPoint}Bill-To</a></th>";
	}
	if ($columnDisplay['BLTO_CMCNA1']=="Y" && $fromType=="P") {
		$returnValue=OrderBy_Sort("BLTO_CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=BillToName\"       title=\"Sequence By Bill-To Name\">{$sortPoint}Bill-To Name</a></th>";
	}
	if ($columnDisplay['IVTRMS']=="Y") {
		$returnValue=OrderBy_Sort("IVTRMS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TermsCode\"       title=\"Sequence By Terms\">{$sortPoint}Terms</a></th>";
	}
	if ($columnDisplay['TMCTDS']=="Y") {
		$returnValue=OrderBy_Sort("TMCTDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=TermsDescr\"       title=\"Sequence By Terms Description\">{$sortPoint}Terms Description</a></th>";
	}
	if ($columnDisplay['IVDUED']=="Y") {
		$returnValue=OrderBy_Sort("IVDUED"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=DueDate\"       title=\"Sequence By Due Date\">{$sortPoint}Due Date</a></th>";
	}
	if ($columnDisplay['IVIVDT']=="Y") {
		$returnValue=OrderBy_Sort("IVIVDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvoiceDate\"       title=\"Sequence By Invoice Date\">{$sortPoint}Invoice Date</a></th>";
	}
	if ($columnDisplay['IVARPO']=="Y") {
		$returnValue=OrderBy_Sort("IVARPO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PONumber\"       title=\"Sequence By Reference Number\">{$sortPoint}Reference Number</a></th>";
	}
	if ($columnDisplay['IVORD']=="Y") {
		$returnValue=OrderBy_Sort("IVORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OEOrder\"       title=\"Sequence By Order Number\">{$sortPoint}Order Number</a></th>";
	}
	if ($columnDisplay['IVORDT']=="Y") {
		$returnValue=OrderBy_Sort("IVORDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OEDate\"       title=\"Sequence By Order Date\">{$sortPoint}Order Date</a></th>";
	}
	if ($columnDisplay['IVORLN']=="Y") {
		$returnValue=OrderBy_Sort("IVORLN"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=OELine\"       title=\"Sequence By Line Number\">{$sortPoint}Line Number</a></th>";
	}
	if ($columnDisplay['IVPLT']=="Y") {
		$returnValue=OrderBy_Sort("IVPLT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Plant\"       title=\"Sequence By Plant\">{$sortPoint}Plant</a></th>";
	}
	if ($columnDisplay['PLNAME']=="Y") {
		$returnValue=OrderBy_Sort("PLNAMEU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=PlantName\"       title=\"Sequence By Plant Name\">{$sortPoint}Plant Name</a></th>";
	}
	if ($columnDisplay['IVMORD']=="Y") {
		$returnValue=OrderBy_Sort("IVMORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=MfgOrder\"       title=\"Sequence By Mfg Order\">{$sortPoint}Mfg Order</a></th>";
	}
	if ($columnDisplay['IVIVAM']=="Y") {
		$returnValue=OrderBy_Sort("IVIVAM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=InvoiceAmount\"       title=\"Sequence By Invoice Amount\">{$sortPoint}Invoice Amount</a></th>";
	}
	if ($columnDisplay['IVLOC']=="Y") {
		$returnValue=OrderBy_Sort("IVLOC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Location\"       title=\"Sequence By Loc\">{$sortPoint}Loc</a></th>";
	}
	if ($columnDisplay['LOLNA1']=="Y") {
		$returnValue=OrderBy_Sort("LOLNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LocationName\"       title=\"Sequence By Location Name\">{$sortPoint}Location Name</a></th>";
	}
	if ($columnDisplay['IVSLSM']=="Y") {
		$returnValue=OrderBy_Sort("IVSLSM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=Salesman\"       title=\"Sequence By Slsm\">{$sortPoint}Slsm</a></th>";
	}
	if ($columnDisplay['SMSNA1']=="Y") {
		$returnValue=OrderBy_Sort("SMSNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SalesmanName\"       title=\"Sequence By Salesman Name\">{$sortPoint}Salesman Name</a></th>";
	}
	if ($columnDisplay['IVCUST']=="Y") {
		$returnValue=OrderBy_Sort("IVCUST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ShipTo\"       title=\"Sequence By Ship-To\">{$sortPoint}Ship-To</a></th>";
	}
	if ($columnDisplay['SHTO_CMCNA1']=="Y") {
		$returnValue=OrderBy_Sort("SHTO_CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=ShipToName\"       title=\"Sequence By Ship-To Name\">{$sortPoint}Ship-To Name</a></th>";
	}
	if ($columnDisplay['IVPSDT']=="Y") {
		$returnValue=OrderBy_Sort("IVPSDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=LastPosted\"       title=\"Sequence By Last Posted\">{$sortPoint}Last Posted</a></th>";
	}
	if ($columnDisplay['IVSBCD']=="Y") {
		$returnValue=OrderBy_Sort("IVSBCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SubCode\"       title=\"Sequence By Created By Payment Code\">{$sortPoint}Created By Payment Code</a></th>";
	}
	if ($columnDisplay['PSDESC']=="Y") {
		$returnValue=OrderBy_Sort("PSDESCU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
		print "\n <th class=\"colhdr$sortVar\"><a href=\"{$baseURL}&amp;tag=ORDERBY&amp;sequence=SubCodeDesc\"       title=\"Sequence By Created By Payment Code Description\">{$sortPoint}Created By Payment Code Description</a></th>";
	}

?>
