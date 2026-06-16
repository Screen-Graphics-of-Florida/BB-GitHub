%{******************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc.*
* All rights reserved. This work contains trade secrets.           *
*                  				                                    *
*  Job: Pricing Detail                                             *
********************************************************************
%}

  @dtw_assign(edtVar, "")
  @Concat_Field("@@fx@@", "DEFN")
  @Concat_Field("@@pmlv", pricingLevel)
  @dtw_concat(edtVar, "}{", edtVar)
  @Rtv_Pricing_Definition(profileHandle, edtVar)
  @Decat_Field("@@cn@@")   @dtw_assign(contract, fieldValue)
  @Decat_Field("@@ll@@")   @dtw_assign(listLess, fieldValue)
  @Decat_Field("@@cp@@")   @dtw_assign(costPlus, fieldValue)
  @Decat_Field("@@dl@@")   @dtw_assign(dollarAmt, fieldValue)
  @Decat_Field("@@up@@")   @dtw_assign(usePercent, fieldValue)
  @Decat_Field("@@bp@@")   @dtw_assign(bracketQty, fieldValue)
  @Decat_Field("@@ba@@")   @dtw_assign(bracketAmt, fieldValue)
  @Decat_Field("@@comm")   @dtw_assign(commission, fieldValue)
  @dtw_Assign(SAV_MAX_ROWS,  RPT_MAX_ROWS)
  @dtw_Assign(RPT_MAX_ROWS,  "999")
  @dtw_Assign(SAV_START_ROW, START_ROW_NUM)
  @dtw_Assign(START_ROW_NUM, "1")
  @Rtv_Pricing_Categories(profileHandle, pricingLevel, mdCol)
  @dtw_Assign(RPT_MAX_ROWS,  SAV_MAX_ROWS)
  @dtw_Assign(START_ROW_NUM, SAV_START_ROW)
