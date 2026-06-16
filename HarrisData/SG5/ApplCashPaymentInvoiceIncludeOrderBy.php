<?php
if ($sequence == "Freight")           {$orby = array(array("IVFRT" ,"A","Freight"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "Tax")           {$orby = array(array("IVSTAX" ,"A","Tax"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "SpcChg")        {$orby = array(array("IVSPC" ,"A","Special Charge"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "BillTo")        {$orby = array(array("IVBLTO" ,"A","Bill-To"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "BillToName")    {$orby = array(array("BLTO_CMCNA1U" ,"A","Bill-To Name"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "TermsCode")     {$orby = array(array("IVTRMS" ,"A","Terms"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "TermsDescr")    {$orby = array(array("TMCTDSU" ,"A","Terms Description"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "DueDate")       {$orby = array(array("IVDUED" ,"A","Due Date"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "InvoiceDate")   {$orby = array(array("IVIVDT" ,"A","Invoice Date"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "PONumber")      {$orby = array(array("IVARPO" ,"A","Reference Number"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "OEOrder")       {$orby = array(array("IVORD" ,"A","Order Number"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "OEDate")        {$orby = array(array("IVORDT" ,"A","Order Date"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "OELine")        {$orby = array(array("IVORLN" ,"A","Line Number"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "Plant")         {$orby = array(array("IVPLT" ,"A","Plant"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "PlantName")     {$orby = array(array("PLNAMEU" ,"A","Plant Name"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "MfgOrder")      {$orby = array(array("IVMORD" ,"A","Mfg Order"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "InvoiceAmount") {$orby = array(array("IVIVAM" ,"A","Invoice Amount"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "Location")      {$orby = array(array("IVLOC" ,"A","Loc"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "LocationName")  {$orby = array(array("LOLNA1U" ,"A","Location Name"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "Salesman")      {$orby = array(array("IVSLSM" ,"A","Slsm"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "SalesmanName")  {$orby = array(array("SMSNA1U" ,"A","Salesman Name"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "ShipTo")        {$orby = array(array("IVCUST" ,"A","Ship-To"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "ShipToName")    {$orby = array(array("SHTO_CMCNA1U" ,"A","Ship-To Name"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "LastPosted")    {$orby = array(array("IVPSDT" ,"A","Last Posted"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "SubCode")       {$orby = array(array("IVSBCD" ,"A","Created By Payment Code"),array("IVAINV" ,"A","Invoice"));}
elseif ($sequence == "SubCodeDesc")   {$orby = array(array("PSDESCU" ,"A","Created By Payment Code Description"),array("IVAINV" ,"A","Invoice"));}
?>
