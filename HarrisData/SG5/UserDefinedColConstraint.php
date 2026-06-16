<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$table        = (isset($_GET['table']))        ? $_GET['table']       : "";
$column       = (isset($_GET['column']))       ? $_GET['column']      : "";
$colDesc      = (isset($_GET['colDesc']))      ? $_GET['colDesc']     : "";

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "User-Defined Column Constraint";
$scriptName     = "UserDefinedColConstraint.php";
$scriptVarBase   = "{$genericVarBase}&amp;table=" . urlencode($table) . "&amp;column=" . urlencode($column);
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$popUpWin        = "Y";

if ($tag == "Edit_Data") {
	require 'stmtSQLClear.php';
	$stmtSQL .= " Delete From SYUDCC Where CCFILN='$table' and CCFLDN='$column'";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
	
	$values = null;
	foreach ($_POST as $key => $value) {
		$key4 = substr($key, 0, 4);
		if (trim($value) != "") {
			$cust = 0;
			$cont = 0;
			$type = "";
			$pcls = "";
			if ($key4 == "cust") {$cust = $value;}
			elseif ($key4 == "cont") {$cont = $value;}
			elseif ($key4 == "type") {$type = $value;}
			elseif ($key4 == "pcls") {$pcls = $value;}
			if (isset($values)) {$values.=", ";}
			if ($cust > 0 || $cont > 0 || $type != "" || $pcls != "") {
				$values.="('" . $table . "', '" . $column . "', '" . $cust . "', '" . $cont . "', '" . $type . "', '" . $pcls . "')";
			}
		}
	}

	require 'stmtSQLClear.php';
	$stmtSQL .= " Insert Into SYUDCC (CCFILN,CCFLDN,CCCUST,CCCONT,CCORTY,CCPCLS) Values $values ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

	print "\n <script TYPE=\"text/javascript\">";
	print "\n opener.location.href=opener.location.href";
	print "\n opener.focus();";
	print "\n window.close();";
	print "\n </script>";
	exit();
}

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'RetValueAjax.php';
	require_once 'AJAXRequest.js';
	require_once 'CheckEnterChg.php';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	require_once 'NoFormValidate.php';
?>
	function deleteElement(id){ 
	  var el = document.getElementById(id);
	  el.parentNode.removeChild(el);
	  return false;
	}

    var count = "1";
    function addRow(tableName,tableID,form,delimg) {
    var cust=document.Chg.elements[0].value;
    if (tableName == 'OEOUHD') {
	    var cont=document.Chg.elements[1].value;
	    var type=document.Chg.elements[2].value;
	    if (cust == "" && cont == "" && type == "") {return;}
 	    var type = type.toUpperCase();
    } else if (tableName == 'OEOUDT') {
        var pcls=document.Chg.elements[1].value;
        if (cust == "" && pcls == "") {return;}
        var pcls = pcls.toUpperCase();
    } else if (tableName == 'POOUMS') {
        var type=document.Chg.elements[1].value;
        if (cust == "" && type == "") {return;}
        var type = type.toUpperCase();
    } else {
        var type=document.Chg.elements[1].value;
    	var pcls=document.Chg.elements[2].value;
 	    if (cust == "" && type == "" && pcls == "") {return;}
        var type = type.toUpperCase();
 	    var pcls = pcls.toUpperCase();
    }
      for(i=3; i<document.Chg.elements.length; i++){
        if (document.Chg.elements[i].value > 0 && document.Chg.elements[i].name.substring(0,4) == "cust" && document.Chg.elements[i].value == cust) {alert('Customer Number ' + cust + ' already exists'); return;}
        if (tableName == 'OEOUHD' || tableName == 'POOUMS' || tableName == 'POOUMD') {
            if (tableName == 'OEOUHD') {
	            if (document.Chg.elements[i].value > 0 && document.Chg.elements[i].name.substring(0,4) == "cont" && document.Chg.elements[i].value == cont) {alert('Contact Number ' + cont + ' already exists'); return;}
            }
	        if (document.Chg.elements[i].value != '' && document.Chg.elements[i].name.substring(0,4) == "type" && document.Chg.elements[i].value == type) {alert('Order Type ' + type + ' already exists'); return;}
        } else {
	        if (document.Chg.elements[i].value != '' && document.Chg.elements[i].name.substring(0,4) == "pcls" && document.Chg.elements[i].value == pcls) {alert('Product Class ' + pcls + ' already exists'); return;}
        }
      }     
      
 	  var CNAM="";
      if (cust>0) {
          if (tableName == 'OEOUHD' || tableName == 'OEOUDT') {
              var selWhere ="CMCUST='"+cust+"' ";
              var selTable ="HDCUST ";
              var selColumn="coalesce(CMCNA1,' ') as CMCNA1 ";
              var retVal="";
              var CNAM=RetValueAjax(selWhere,selTable, selColumn, retVal);
              if (CNAM == "") {alert('Customer Number ' + cust + ' not found'); return;}
          } else {
              var selWhere ="VMVEND='"+cust+"' ";
              var selTable ="HDVEND ";
              var selColumn="coalesce(VMVNA1,' ') as CMCNA1 ";
              var retVal="";
              var CNAM=RetValueAjax(selWhere,selTable, selColumn, retVal);
              if (CNAM == "") {alert('Vendor Number ' + cust + ' not found'); return;}
          }
      }
      if (tableName == 'OEOUHD' || tableName == 'POOUMS') {
	 	  var CTDS="";
	      if (cont>0) {
              if (tableName == 'OEOUHD') {
                  var selWhere ="CRCONT='"+cont+"' ";
                  var selTable ="CRCNTM ";
                  var selColumn="coalesce((CRLNAM||', '||CRFNAM),' ') as CRLNAM ";
              }
		      var retVal=""; 
		      var CTDS=RetValueAjax(selWhere,selTable, selColumn, retVal); 
		      if (CTDS == "") {alert('Contact Number ' + cont + ' not found'); return;}
	      }
	 	  var OTDS="";
	      if (type!= "") {
              if (tableName == 'OEOUHD') {
		          var selWhere ="OTAPID='OE' and OTOTCD='"+type+"' ";
              } else {
                  var selWhere ="OTAPID='PO' and OTOTCD='"+type+"' ";
              }
		      var selTable ="HDOTYP "; 
		      var selColumn="coalesce(OTDESC,' ') as OTDESC "; 
		      var retVal=""; 
		      var OTDS=RetValueAjax(selWhere,selTable, selColumn, retVal); 
		      if (OTDS == "") {alert('Order Type ' + type + ' not found'); return;}
	      }
      } else {
          if (tableName == 'POOUMD' || tableName == 'POOUMS') {
                var OTDS="";
                if (type!= "") {
                    var selWhere ="OTAPID='PO' and OTOTCD='"+type+"' ";
                    var selTable ="HDOTYP ";
                    var selColumn="coalesce(OTDESC,' ') as OTDESC ";
                    var retVal="";
                    var OTDS=RetValueAjax(selWhere,selTable, selColumn, retVal);
                    if (OTDS == "") {alert('Order Type ' + type + ' not found'); return;}
                }
          }
	 	  var PCDS="";
	      if (pcls!= "") {
		      var selWhere ="PCPCLS='"+pcls+"' "; 
		      var selTable ="HDPCLS "; 
		      var selColumn="coalesce(PCPCDS,' ') as PCPCDS "; 
		      var retVal=""; 
		      var PCDS=RetValueAjax(selWhere,selTable, selColumn, retVal); 
		      if (PCDS == "") {alert('Product Class ' + pcls + ' not found'); return;}
	      }
      }
      var tbody = document.getElementById(tableID).getElementsByTagName("TBODY")[0];
	  var table = document.getElementById(tableID);
 
      var rowCount = table.rows.length;      // create row
      rowCount = parseInt(rowCount) + 1;
      var row = document.createElement("TR");
      row.setAttribute('id',entry);
      if (rowCount%2 == 0) {
        row.setAttribute('class','evenrow');
        row.setAttribute('className','evenrow');  // For IE
      } else {
        row.setAttribute('class','oddrow');
        row.setAttribute('className','oddrow');  // For IE
      }
    
      var td1 = document.createElement("TD")
      td1.setAttribute('class','colicon');
      td1.setAttribute('className','colicon');  // For IE
      var img = document.createElement('IMG');
      img.setAttribute('src', delimg);
      img.setAttribute('title', 'Remove row');
      img.onclick = function(){delRow(row);}
      td1.appendChild(img);
    
      var td2 = document.createElement("TD")
      td2.setAttribute('class','colnmbr');
      td2.setAttribute('className','colnmbr');  // For IE
      td2.innerHTML = "<input type=hidden name=cust"+rowCount+" value='"+cust+"'>"+cust;
    
      var td3 = document.createElement("TD")
      td3.setAttribute('class','colalph');
      td3.setAttribute('className','colalph');  // For IE
      td3.setAttribute('id',"dsc"+cust.value);
      td3.innerHTML = "<input type=hidden id=csnm"+cust+" name=dsc"+rowCount+" value=''>"+CNAM;

      if (tableName == 'OEOUHD' || tableName == 'POOUMS') {
          if (tableName == 'OEOUHD') {
              var td4 = document.createElement("TD")
              td4.setAttribute('class','colnmbr');
              td4.setAttribute('className','colnmbr');  // For IE
              td4.innerHTML = "<input type=hidden name=cont"+rowCount+" value='"+cont+"'>"+cont;

              var td5 = document.createElement("TD")
              td5.setAttribute('class','colalph');
              td5.setAttribute('className','colalph');  // For IE
              td5.setAttribute('id',"dsc"+cont.value);
              td5.innerHTML = "<input type=hidden id=ctnm"+cont+" name=dsc"+rowCount+" value=''>"+CTDS;
	      }

	      var td6 = document.createElement("TD")
	      td6.setAttribute('class','colcode');
	      td6.setAttribute('className','colcode');  // For IE
	      td6.innerHTML = "<input type=hidden name=type"+rowCount+" value='"+type+"'>"+type;
	    
	      var td7 = document.createElement("TD")
	      td7.setAttribute('class','colalph');
	      td7.setAttribute('className','colalph');  // For IE
	      td7.setAttribute('id',"dsc"+type.value);
	      td7.innerHTML = "<input type=hidden id=tpds"+type+" name=dsc"+rowCount+" value=''>"+OTDS;
	  } else {
          if (tableName == 'POOUMD') {
            var td6 = document.createElement("TD")
            td6.setAttribute('class','colcode');
            td6.setAttribute('className','colcode');  // For IE
            td6.innerHTML = "<input type=hidden name=type"+rowCount+" value='"+type+"'>"+type;

            var td7 = document.createElement("TD")
            td7.setAttribute('class','colalph');
            td7.setAttribute('className','colalph');  // For IE
            td7.setAttribute('id',"dsc"+type.value);
            td7.innerHTML = "<input type=hidden id=tpds"+type+" name=dsc"+rowCount+" value=''>"+OTDS;
          }

	      var td8 = document.createElement("TD")
	      td8.setAttribute('class','colalph');
	      td8.setAttribute('className','colalph');  // For IE
	      td8.innerHTML = "<input type=hidden name=pcls"+rowCount+" value='"+pcls+"'>"+pcls;
	    
	      var td9 = document.createElement("TD")
	      td9.setAttribute('class','colalph');
	      td9.setAttribute('className','colalph');  // For IE
	      td9.setAttribute('id',"dsc"+pcls.value);
	      td9.innerHTML = "<input type=hidden id=pcds"+pcls+" name=dsc"+rowCount+" value=''>"+PCDS;
	  }   
      // append data to row
      row.appendChild(td1);
      row.appendChild(td2);
      row.appendChild(td3);
      if (tableName == 'OEOUHD' || tableName == 'POOUMS') {
          if (tableName == 'OEOUHD') {
	        row.appendChild(td4);
	        row.appendChild(td5);
          }
	      row.appendChild(td6);
	      row.appendChild(td7);
      } else {
          if (tableName == 'POOUMD') {
              row.appendChild(td6);
              row.appendChild(td7);
          }
	      row.appendChild(td8);
	      row.appendChild(td9);
      }
      // append row to table
      tbody.appendChild(row);
     
      document.Chg.addCust.value="";
      document.getElementById('custName').innerHTML = '';
      if (tableName == 'OEOUHD' || tableName == 'POOUMS') {
          if (tableName == 'OEOUHD') {
	        document.Chg.addCont.value="";
	        document.getElementById('contName').innerHTML = '';
          }
	      document.Chg.addType.value="";
	      document.getElementById('typeDesc').innerHTML = '';
      } else {
          if (tableName == 'POOUMD') {
            document.Chg.addType.value="";
            document.getElementById('typeDesc').innerHTML = '';
          }
	      document.Chg.addProd.value="";
	      document.getElementById('prodClassDesc').innerHTML = '';
      }
      setTimeout(function () { document.Chg.addCust.focus() }, 50);
    }
  
function delRow(row){row.parentNode.removeChild(row);}


</script>
<?php

require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr>";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

require 'stmtSQLClear.php';
$stmtSQL .=  " Select * ";
$fileSQL .=  " SYUDCC ";
$selectSQL =  " CCFILN='$table' and CCFLDN='$column' ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By CCID";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
print "\n <tr><td><h1>$page_title</h1></td>";
print "\n     <td class=\"toolbar\">";
print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
print "\n <a href=\"javascript:window.close()\">$cancelImageMed</a>";

$medIcon= "Y";
require 'HelpPage.php';
print "\n </td></tr></table>";

print "<table $contentTable>";
$tableDesc = ($table == 'OEOUHD' || $table == 'OEOUDT') ? 'Customer' : 'Purchase';
Format_Header("Table", $tableDesc . " Order", $table);
Format_Header("Column", $colDesc, $column);
print "\n </table>";

print $hrTagAttr;

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\" onSubmit=\"return false;\">";

$custVendDesc = ($table == 'OEOUHD' || $table == 'OEOUDT') ? 'Customer' : 'Vendor';
print "\n <table $contentTable id=\"entry\">
		            <tr><th class=\"colhdr\">$optionHeading</th>
		                <th class=\"colhdr\">$custVendDesc<br>Number</th>
		                <th class=\"colhdr\">Name</th>";
if ($table == "OEOUHD" || $table == "POOUMS") {
    if ($table == "OEOUHD") {
        print "<th class=\"colhdr\">Contact<br>Number</th>
               <th class=\"colhdr\">Name</th>";
    }
    print "<th class=\"colhdr\">Order<br>Type</th>
           <th class=\"colhdr\">Description</th>
           </tr>";
} else {
    if ($table == "POOUMD") {
        print "<th class=\"colhdr\">Order<br>Type</th>
           <th class=\"colhdr\">Description</th>";
    }
	print "<th class=\"colhdr\">Product<br>Class</th>
           <th class=\"colhdr\">Description</th>
           </tr>";
}
print "\n <tr><td class=\"colicon\"><a href=\"javascript:addRow('{$table}','entry',this,'{$homeURL}{$imagePath}smDelete.gif')\">$acceptImageMed</a></td>";
if ($table == "POOUMS" || $table == "POOUMD") {
    print "\n     <td class=\"inputalph\" nowrap><input type=\"text\" name=\"addCust\" id=\"addCust\" value=\"\" size=\"5\" maxlength=\"7\"><a href=\"{$homeURL}{$phpPath}VendorSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=addCust&amp;fldDesc=custName\" onclick=\"$searchWinVar\"> $searchImage </a></td><td><span class=\"dspdesc\" id=\"custName\"></span></td>";
} else {
    print "\n     <td class=\"inputalph\" nowrap><input type=\"text\" name=\"addCust\" id=\"addCust\" value=\"\" size=\"5\" maxlength=\"7\"><a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=addCust&amp;fldDesc=custName\" onclick=\"$searchWinVar\"> $searchImage </a></td><td><span class=\"dspdesc\" id=\"custName\"></span></td>";
}
if ($table == "OEOUHD" || $table == "POOUMS") {
    if ($table == "OEOUHD") {
        print "\n <td class=\"inputalph\" nowrap><input type=\"text\" name=\"addCont\" id=\"addCont\" value=\"\" size=\"5\" maxlength=\"7\"><a href=\"{$homeURL}{$cGIPath}CustomerContactSearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=addCont&amp;fldDesc=contName\" onclick=\"$searchWinVar\"> $searchImage </a></td><td><span class=\"dspdesc\" id=\"contName\"></span></td>  ";
	    print "\n <td class=\"inputalph\" nowrap><input type=\"text\" name=\"addType\" id=\"addType\" value=\"\" size=\"5\" maxlength=\"1\"><a href=\"{$homeURL}{$cGIPath}OrderTypeSearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=addType&amp;fldDesc=typeDesc&amp;appID=OE\" onclick=\"$searchWinVar\"> $searchImage </a></td><td><span class=\"dspdesc\" id=\"typeDesc\"></span></td></tr>  ";
    } else {
        print "\n <td class=\"inputalph\" nowrap><input type=\"text\" name=\"addType\" id=\"addType\" value=\"\" size=\"5\" maxlength=\"1\"><a href=\"{$homeURL}{$cGIPath}OrderTypeSearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=addType&amp;fldDesc=typeDesc&amp;appID=PO\" onclick=\"$searchWinVar\"> $searchImage </a></td><td><span class=\"dspdesc\" id=\"typeDesc\"></span></td></tr>  ";
    }
} else {
    if ($table == "POOUMD") {
        print "\n <td class=\"inputalph\" nowrap><input type=\"text\" name=\"addType\" id=\"addType\" value=\"\" size=\"5\" maxlength=\"1\"><a href=\"{$homeURL}{$cGIPath}OrderTypeSearch.d2w/ENTRY{$altVarBase}&amp;docName=Chg&amp;fldName=addType&amp;fldDesc=typeDesc&amp;appID=PO\" onclick=\"$searchWinVar\"> $searchImage </a></td><td><span class=\"dspdesc\" id=\"typeDesc\"></span></td>  ";
    }
	print "\n <td class=\"inputalph\" nowrap><input type=\"text\" name=\"addProd\" id=\"addProd\" value=\"\" size=\"5\" maxlength=\"4\"><a href=\"{$homeURL}{$phpPath}ProdClassSearch.php{$scriptVarBase}&amp;docName=Chg&amp;fldName=addProd&amp;fldDesc=prodClassDesc\" onclick=\"$searchWinVar\"> $searchImage </a></td><td><span class=\"dspdesc\" id=\"prodClassDesc\"></span></td></tr>  ";
}
$rowCount = 0;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	require  'SetRowClass.php';
	$custName = "";
	$contName = "";
	$typeDesc = "";
	$prodClassDesc = "";
	if ($row[CCCUST]>0)   {
        if ($table == "OEOUHD" || $table == 'OEOUDT') {
            $custName = RetValue("CMCUST=$row[CCCUST]", "HDCUST", "CMCNA1");
        } else {
            $custName = RetValue("VMVEND=$row[CCCUST]", "HDVEND", "VMCNA1");
        }
	} else {$row[CCCUST] = "";}
	if ($table == "OEOUHD" || $table == "POOUMS") {
        if ($table == "OEOUHD") {
            if ($row[CCCONT] > 0) {
                $contName = RetValue("CRCONT=$row[CCCONT]", "CRCNTM", "coalesce((CRLNAM||', '||CRFNAM),' ') as CRLNAM");
            } else {
                $row[CCCONT] = "";
            }
            if (trim($row[CCORTY]) != "") {
                $typeDesc = RetValue("OTAPID='OE' and OTOTCD='$row[CCORTY]'", "HDOTYP", "OTDESC");
            }
        } else {
            $row[CCCONT] = "";
            if (trim($row[CCORTY]) != "") {
                $typeDesc = RetValue("OTAPID='PO' and OTOTCD='$row[CCORTY]'", "HDOTYP", "OTDESC");
            }
        }
	} else {
        if ($table == "POOUMD" && trim($row[CCORTY]) != "") {
            $typeDesc = RetValue("OTAPID='PO' and OTOTCD='$row[CCORTY]'", "HDOTYP", "OTDESC");
        }
		if (trim($row[CCPCLS])!= "") {$prodClassDesc = RetValue("PCPCLS='$row[CCPCLS]'", "HDPCLS", "PCPCDS");}
	}
	print "\n <tr class=\"$rowClass\" id=\"entry[$rowCount]\">
				      <td class=\"colicon\"><a href=\"\" onclick=\"return deleteElement('entry[$rowCount]');\">$deleteImageSml</a></td>
					  <td class=\"colnmbr\"><input type=\"hidden\" name=\"cust$rowCount\" value=\"$row[CCCUST]\">$row[CCCUST]</td>
					  <td class=\"colalph\">$custName</td>";
	if ($table == "OEOUHD" || $table == "POOUMS") {
        if ($table == "OEOUHD") {
            print "<td class=\"colnmbr\"><input type=\"hidden\" name=\"cont$rowCount\" value=\"$row[CCCONT]\">$row[CCCONT]</td>
		       <td class=\"colalph\">$contName</td>";
        }
		print "<td class=\"colcode\"><input type=\"hidden\" name=\"type$rowCount\" value=\"$row[CCORTY]\">$row[CCORTY]</td>
		       <td class=\"colalph\">$typeDesc</td></tr>";
	} else {
        if ($table == "POOUMD") {
            print "<td class=\"colcode\"><input type=\"hidden\" name=\"type$rowCount\" value=\"$row[CCORTY]\">$row[CCORTY]</td>
		       <td class=\"colalph\">$typeDesc</td>";
        }
		print "<td class=\"colalph\"><input type=\"hidden\" name=\"pcls$rowCount\" value=\"$row[CCPCLS]\">$row[CCPCLS]</td>
		       <td class=\"colalph\">$prodClassDesc</td></tr>";
	}
	
	$startRow ++;
	$rowCount ++;
}

print "\n </table>";
print "\n <script TYPE=\"text/javascript\">";
print "\n document.Chg.addCust.focus();";
print "\n </script>";

print "\n </form>";
print $hrTagAttr;
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "</body> </html>";
}

?>