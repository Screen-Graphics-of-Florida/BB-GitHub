%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Set Text Overrides For Employee Selection                   *
*********************************************************************
%}

  @SetTextOvr(Err_SLFCMP)
  %if (textOvr == "") @SetTextOvr(Err_SLTCMP) %endif
  %if (textOvr == "") @SetTextOvr(Err_SLACMP) %endif
  <tr><td class="dsphdr"><span $(textOvr)>Company Number</span></td>
      <td class="inputnmbr"><input name="fromCompany" value="$(V_SLFCMP)" type="text" size="5" maxlength="2"> <a href="$(homeURL)$(phpPath)HRCoFacSearch.php$(altVarBase)&amp;docName=Chg&amp;fldCo=fromCompany&amp;fldFac=fromFacility&amp;fldDesc=coFacDesc" onclick="$(searchWinVar)"> $(searchImage) </a> <input disabled name="coFacDesc" value="$(fieldDesc)"  type="hidden" size="30" maxlength="30"></td>
      <td class="inputnmbr"><input name="toCompany"   value="$(V_SLTCMP)" type="text" size="5" maxlength="2"> <a href="$(homeURL)$(phpPath)HRCoFacSearch.php$(altVarBase)&amp;docName=Chg&amp;fldCo=toCompany&amp;fldFac=toFacility&amp;fldDesc=coFacDesc" onclick="$(searchWinVar)"> $(searchImage) </a> <input disabled name="coFacDesc" value="$(fieldDesc)"  type="hidden" size="30" maxlength="30"></td>
      <td class="inputalph"><input name="allCompany"  value='ALL' $(checkedCompany) type="checkbox" onClick="if (this.checked) this.form.fromCompany.value='', this.form.toCompany.value='';"></td>
  </tr>
  @DspErrMsg(Err_SLFCMP)
  @DspErrMsg(Err_SLTCMP)
  @DspErrMsg(Err_SLACMP)

  @SetTextOvr(Err_SLFFAC)
  %if (textOvr == "") @SetTextOvr(Err_SLTFAC) %endif
  %if (textOvr == "") @SetTextOvr(Err_SLAFAC) %endif
  <tr><td class="dsphdr"><span $(textOvr)>Facility Number</span></td>

      <td class="inputnmbr"><input name="fromFacility" value="$(V_SLFFAC)" type="text" size="5" maxlength="4"> <a href="$(homeURL)$(phpPath)HRCoFacSearch.php$(altVarBase)&amp;docName=Chg&amp;fldCo=fromCompany&amp;fldFac=fromFacility&amp;fldDesc=coFacDesc" onclick="$(searchWinVar)"> $(searchImage) </a> <input disabled name="coFacDesc" value="$(fieldDesc)"  type="hidden" size="30" maxlength="30"></td>
      <td class="inputnmbr"><input name="toFacility"   value="$(V_SLTFAC)" type="text" size="5" maxlength="4"> <a href="$(homeURL)$(phpPath)HRCoFacSearch.php$(altVarBase)&amp;docName=Chg&amp;fldCo=toCompany&amp;fldFac=toFacility&amp;fldDesc=coFacDesc" onclick="$(searchWinVar)"> $(searchImage) </a> <input disabled name="coFacDesc" value="$(fieldDesc)"  type="hidden" size="30" maxlength="30"></td>
      <td class="inputalph"><input name="allFacility"  value='ALL' $(checkedFacility) type="checkbox" onClick="if (this.checked) this.form.fromFacility.value='', this.form.toFacility.value='';"></td>
  </tr>  			
  @DspErrMsg(Err_SLFFAC)
  @DspErrMsg(Err_SLTFAC)
  @DspErrMsg(Err_SLAFAC)

  @SetTextOvr(Err_SLFLOC)
  %if (textOvr == "") @SetTextOvr(Err_SLTLOC) %endif
  %if (textOvr == "") @SetTextOvr(Err_SLALOC) %endif
  <tr><td class="dsphdr"><span $(textOvr)>Location</span></td>
      <td class="inputalph"><input name="fromLocation" value="$(V_SLFLOC)"type="text" size="5" maxlength="4"> <a href="$(homeURL)$(phpPath)HRCodesSearch.php$(altVarBase)&amp;docName=Chg&amp;fldType=O&amp;fldName=fromLocation&amp;fldDesc=locCodeDesc2" onclick="$(searchWinVar)"> $(searchImage) </a> <input disabled name="locCodeDesc2" value="$(codeDesc)"  type="hidden" size="20" maxlength="20"></td>
      <td class="inputalph"><input name="toLocation"   value="$(V_SLTLOC)" type="text" size="5" maxlength="4"> <a href="$(homeURL)$(phpPath)HRCodesSearch.php$(altVarBase)&amp;docName=Chg&amp;fldType=O&amp;fldName=toLocation&amp;fldDesc=locCodeDesc2" onclick="$(searchWinVar)"> $(searchImage) </a> <input disabled name="locCodeDesc2" value="$(codeDesc)"  type="hidden" size="20" maxlength="20"></td>
      <td class="inputalph"><input name="allLocation"  value='ALL' $(checkedLocation) type="checkbox" onClick="if (this.checked) this.form.fromLocation.value='', this.form.toLocation.value='';"></td>
  </tr>
  @DspErrMsg(Err_SLFLOC)
  @DspErrMsg(Err_SLTLOC)
  @DspErrMsg(Err_SLALOC)

  @SetTextOvr(Err_SLFDPT)
  %if (textOvr == "") @SetTextOvr(Err_SLTDPT) %endif
  %if (textOvr == "") @SetTextOvr(Err_SLADPT) %endif
  <tr><td class="dsphdr"><span $(textOvr)>Department</span></td>
      <td class="inputalph"><input name="fromDepartment" value="$(V_SLFDPT)" type="text" size="5" maxlength="5"> <a href="$(homeURL)$(phpPath)DepartmentSearch.php$(altVarBase)&amp;docName=Chg&amp;fldName=fromDepartment&amp;fldDesc=fromDeptDesc" onclick="$(searchWinVar)"> $(searchImage) </a> <input  name="fromDeptDesc" value="$(fromDeptDesc)"  type="hidden" size="20" maxlength="20"></td>
      <td class="inputalph"><input name="toDepartment"   value="$(V_SLTDPT)" type="text" size="5" maxlength="5"> <a href="$(homeURL)$(phpPath)DepartmentSearch.php$(altVarBase)&amp;docName=Chg&amp;fldName=toDepartment&amp;fldDesc=toDeptDesc" onclick="$(searchWinVar)"> $(searchImage) </a> <input  name="toDeptDesc" value="$(toDeptDesc)"  type="hidden" size="20" maxlength="20"></td>
      <td class="inputalph"><input name="allDepartment"  value='ALL' $(checkedDepartment) type="checkbox" onClick="if (this.checked) this.form.fromDepartment.value='', this.form.toDepartment.value='';"></td>
  </tr>
  @DspErrMsg(Err_SLFDPT)
  @DspErrMsg(Err_SLTDPT)
  @DspErrMsg(Err_SLADPT)

  @SetTextOvr(Err_SLFPRE)
  %if (textOvr == "") @SetTextOvr(Err_SLTPRE) %endif
  %if (textOvr == "") @SetTextOvr(Err_SLAPRE) %endif
  <tr><td class="dsphdr"><span $(textOvr)>P/R Employee Number</span></td>
      <td class="inputalph"><input name="fromPrEmpl" value="$(V_SLFPRE)" type="text" size="5" maxlength="5"> <a href="$(homeURL)$(phpPath)EmployeeSearch.php$(altVarBase)&amp;docName=Chg&amp;fromD2w=@dtw_rurlescseq(d2wName)&amp;fldCo=prCompany&amp;fldFacl=prFacility&amp;fldEmpl=fromPrEmpl&amp;fldHrCo=hrCompany&amp;fldHREmpl=hrEmpl"onclick="$(searchWinVar)">$(searchImage)</a></td>
      <td class="inputalph"><input name="toPrEmpl"   value="$(V_SLTPRE)" type="text" size="5" maxlength="5"> <a href="$(homeURL)$(phpPath)EmployeeSearch.php$(altVarBase)&amp;docName=Chg&amp;fromD2w=@dtw_rurlescseq(d2wName)&amp;fldCo=prCompany&amp;fldFacl=prFacility&amp;fldEmpl=toPrEmpl&amp;fldHrCo=hrCompany&amp;fldHREmpl=hrEmpl"onclick="$(searchWinVar)">$(searchImage)</a></td>
      <td class="inputalph"><input name="allPrEmpl"  value='ALL' $(checkedPrEmpl) type="checkbox" onClick="if (this.checked) this.form.fromPrEmpl.value='', this.form.toPrEmpl.value='';"></td>
      <td class="inputalph"><input name="prCompany" type="hidden"><input name="prFacility" type="hidden"><input name="hrCompany" type="hidden"><input name="hrEmpl" type="hidden"></td>
  </tr>
  @DspErrMsg(Err_SLFPRE)
  @DspErrMsg(Err_SLTPRE)
  @DspErrMsg(Err_SLAPRE)

  @SetTextOvr(Err_SLHPRE)
  %if (textOvr == "") @SetTextOvr(Err_SLTHRE) %endif
  %if (textOvr == "") @SetTextOvr(Err_SLAHRE) %endif
  <tr><td class="dsphdr"><span $(textOvr)>H/R Employee Number</span></td>
      <td class="inputalph"><input name="fromHrEmpl" value="$(V_SLFHRE)" type="text" size="5" maxlength="9"> <a href="$(homeURL)$(phpPath)EmployeeSearch.php$(altVarBase)&amp;docName=Chg&amp;fromD2w=@dtw_rurlescseq(d2wName)&amp;enterOrFocus=fromHrEmpl&amp;fldCompany=prCompany&amp;fldFacl=prFacility&amp;fldHRCo=hrCompany&amp;fldHREmpl=fromHrEmpl"onclick="$(searchWinVar)">$(searchImage)</a> </td>
      <td class="inputalph"><input name="toHrEmpl"   value="$(V_SLTHRE)" type="text" size="5" maxlength="9"> <a href="$(homeURL)$(phpPath)EmployeeSearch.php$(altVarBase)&amp;docName=Chg&amp;fromD2w=@dtw_rurlescseq(d2wName)&amp;enterOrFocus=toHrEmpl&amp;fldCompany=prCompany&amp;fldFacl=prFacility&amp;fldHRCo=hrCompany&amp;fldHREmpl=toHrEmpl"onclick="$(searchWinVar)">$(searchImage)</a></td>
      <td class="inputalph"><input name="allHrEmpl"  value='ALL' $(checkedHrEmpl) type="checkbox" onClick="if (this.checked) this.form.fromHrEmpl.value='', this.form.toHrEmpl.value='';"></td>
      <td class="inputalph"><input name="prCompany" type="hidden"><input name="prFacility" type="hidden"><input name="hrCompany" type="hidden"><input name="prEmpl" type="hidden"></td>
  </tr>
  @DspErrMsg(Err_SLFHRE)
  @DspErrMsg(Err_SLTHRE)
  @DspErrMsg(Err_SLAHRE)