<?php
  $genericVarBase    = (string) "?baseVar=" . urlencode($baseVar) . "&amp;portal=" . urlencode($portal) . "&amp;eID=" . urlencode($eID);
  $altVarBase        = (string) "?baseVar=" . urlencode($altBaseVar) . "&amp;portal=" . urlencode($portal) . "&amp;eID=" . urlencode($eID);
  $searchVarBase     = (string) "&amp;orderBy=" . urlencode($orderBy) . "&amp;wildCardSearch=" . urlencode($wildCardSearch);
  $orderByVarBase    = (string) "&amp;orderBy=" . urlencode($orderBy) . "&amp;orderByDisplay=" . urlencode($orderByDisplay);
  $wildCardVarBase   = (string) "&amp;wildCardSearch=" . urlencode($wildCardSearch) . "&amp;wildCardDisplay=" . urlencode($wildCardDisplay);
  $glDDVarBase       = (string) "&amp;ddReport=" . urlencode($_GET['ddReport']) . "&amp;ddDescr=" . urlencode($_GET['ddDescr']) . "&amp;ddCompany=" . urlencode($_GET['ddCompany']) . "&amp;ddFacility=" . urlencode($_GET['ddFacility']);
  $employeeVarBase   = (string) "&amp;prCompany=" . urlencode($prCompany) . "&amp;prFacility=" . urlencode($prFacility) . "&amp;prEmployee=" . urlencode($prEmployee) . "&amp;hrCompany=" . urlencode($hrCompany) . "&amp;hrEmployee=" . urlencode($hrEmployee);
  $hrCoFacVarBase    = (string) "&amp;prCompany=" . urlencode($prCompany) . "&amp;prFacility=" . urlencode($prFacility) . "&amp;hrCompany=" . urlencode($hrCompany);
  $hdListVarBase     = (string) "&amp;tblID=" . urlencode($tblID) . "&amp;pagID=" . urlencode($pagID);
?>