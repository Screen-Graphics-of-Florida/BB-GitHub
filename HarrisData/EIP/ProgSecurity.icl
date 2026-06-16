%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Program Security Usage Inquiry                              *
*********************************************************************
%}

%MACRO_FUNCTION RtvUserViewFields (IN    PSUVFN,
                                   OUT   userViewFieldDesc)
{
  @dtw_assign(userViewFieldDesc, "")
  %if (@dtw_rpos("XPCO#", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Company Number <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPFAC#", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Facility Number <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPTIN#", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "A/P Payer TIN <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPBNK#", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Financial Bank <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPACCT", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Account <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPBUYR", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Buyer/Analyst <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPVTYP", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Vendor Type <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPVEND", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Vendor Number <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPSLSM", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Salesman Number <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPCCLS", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Customer Class Code <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPCRGN", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Customer Region <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPBLLC", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Billing Location Number <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPCUST", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Customer Number <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPAYR", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Payer <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPEIN", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "P/R Federal EIN <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPECO", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "H/R Company Number <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPELC", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "H/R Location Code <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPBANK", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Payroll Bank <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPDEPT", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Home Department <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPAYT", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Pay Type <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPAYD", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Salary Control <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPREM", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "P/R Employee Number <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPEEM", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "H/R Employee Number <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPSCHD", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Schedule <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPGRP", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Group Number <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPCODE", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Data Collection Code <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPLCOD", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Labor Code <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPINCD", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Indirect/Downtime Code <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPLT", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Plant Number <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPMFDP", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Department <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPWC", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Work Center <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPITC", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Plant Inventory Type <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPTYP", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Plant Part Type <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPGRP", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Product Group <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPCLAS", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Part Class Code <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPCLS", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Product Class <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPWHS", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Warehouse Number <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPITC", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Product Inventory Type <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPPTY", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Product Part Type <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPCYCL", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Count Group <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPSTKR", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Stockroom <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPAILE", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Aisle <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPSLOC", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Stock Location <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPOOCD", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Order Entry Order Type <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPOCD", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Purchasing Order Type <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPTTYP", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Transaction Type <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPSITE", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Site <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPPROP", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Property Type <br>", userViewFieldDesc)
  %endif
  %if (@dtw_rpos("XPFMCD", PSUVFN) > "0")
      @dtw_concat(userViewFieldDesc, "Family Code <br>", userViewFieldDesc)
  %endif
%}

%MACRO_FUNCTION Build_UserView (IN CHAR(10)  fldName,
			                   CHAR(100) fldDesc,
			                   CHAR(100) selData)
{
  @dtw_assign(selData, @dtw_rstrip(selData))
  %if (selData == "on")
      %if (wildCardTemp == "")
          %if (wildCardSearch == "")
              @dtw_assign(wildCardSearch, "and ( (")
          %else
              @dtw_replace(wildCardSearch, ")", " ", @dtw_rlastpos(")", wildCardSearch), wildCardSearch)
              @dtw_concat(wildCardSearch, " or (", wildCardSearch)
          %endif
      %endif

      %if (wildCardTemp != "")
          @dtw_concat(wildCardTemp, " and ", wildCardTemp)
      %endif
      %if (userViewDisplay != "")
          @dtw_concat(userViewDisplay, ", ", userViewDisplay)
      %endif

      @dtw_concat(userViewDisplay, "$(fldDesc) ", userViewDisplay)
      @dtw_concat(wildCardTemp, " trim(PSUVFN) LIKE '%$(fldName)%'", wildCardTemp)
  %endif
%}
