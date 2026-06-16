%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Set check boxes for H/R report selection                    *
*********************************************************************
%}
  %if (errFound != "" || scheduleJobSwitch == "Y")
     %if (V_SLRPSQ == "02")
         @dtw_assign(seqAlpha, "")
         @dtw_assign(seqPrEmpl, "CHECKED")
         @dtw_assign(seqHrEmpl, "")
     %elif (V_SLRPSQ == "03")
         @dtw_assign(seqAlpha, "")
         @dtw_assign(seqPrEmpl, "")
         @dtw_assign(seqHrEmpl, "CHECKED")
     %else
         @dtw_assign(seqAlpha, "CHECKED")
         @dtw_assign(seqPrEmpl, "")
         @dtw_assign(seqHrEmpl, "")
     %endif
     %if (V_SLACTV == "1")
         @dtw_assign(checkedActiveEmpl, "CHECKED")
     %else
         @dtw_assign(checkedActiveEmpl, "")
     %endif
     %if (V_SLTERM == "1")
         @dtw_assign(checkedTermEmpl, "CHECKED")
     %else
         @dtw_assign(checkedTermEmpl, "")
     %endif
     %if (V_SLACMP == "ALL")
         @dtw_assign(checkedCompany, "CHECKED")
     %else
         @dtw_assign(checkedCompany, "")
     %endif
     %if (V_SLAFAC == "ALL")
         @dtw_assign(checkedFacility, "CHECKED")
     %else
         @dtw_assign(checkedFacility, "")
     %endif
     %if (V_SLALOC == "ALL")
         @dtw_assign(checkedLocation, "CHECKED")
     %else
         @dtw_assign(checkedLocation, "")
     %endif
     %if (V_SLADPT == "ALL")
         @dtw_assign(checkedDepartment, "CHECKED")
     %else
         @dtw_assign(checkedDepartment, "")
     %endif
     %if (V_SLAPRE == "ALL")
         @dtw_assign(checkedPrEmpl, "CHECKED")
     %else
         @dtw_assign(checkedPrEmpl, "")
     %endif
     %if (V_SLAHRE == "ALL")
         @dtw_assign(checkedHrEmpl, "CHECKED")
     %else
         @dtw_assign(checkedHrEmpl, "")
     %endif
 %else
      @dtw_assign(seqAlpha, "CHECKED")
      @dtw_assign(seqPrEmpl, "")
      @dtw_assign(seqHrEmpl, "")
      @dtw_assign(checkedActiveEmpl, "CHECKED")
      @dtw_assign(checkedTermEmpl, "CHECKED")
      @dtw_assign(checkedCompany, "CHECKED")
      @dtw_assign(checkedFacility, "CHECKED")
      @dtw_assign(checkedLocation, "CHECKED")
      @dtw_assign(checkedDepartment, "CHECKED")
      @dtw_assign(checkedPrEmpl, "CHECKED")
      @dtw_assign(checkedHrEmpl, "CHECKED")
 %endif

 @SetTextOvr(Err_SLRPSQ)
 <tr><td class="dsphdr"><span $(textOvr)>Select Row Sequence</span></td>
     <td class="inputnmbr"><input name="emplSequence" type="radio" VALUE='01' $(seqAlpha)>Last Name, First Name</td>
 </tr>
 @DspErrMsg(Err_SLRPSQ)

 <tr><td>&nbsp;</td>
     <td class="inputnmbr"><input name="emplSequence" type="radio" VALUE='02' $(seqPrEmpl)>P/R Employee Number</td>
 </tr>

 <tr><td>&nbsp;</td>
     <td class="inputnmbr"><input name="emplSequence" type="radio" VALUE='03' $(seqHrEmpl)>H/R Employee Number</td>
 </tr>

 @SetTextOvr(Err_SLACTV)
 <tr><td class="dsphdr"><span $(textOvr)>Select Employees</span></td>
     <td class="inputnmbr"><input name="activeEmpl" type="checkbox" VALUE='1' $(checkedActiveEmpl)>Include Active Employees</td>
 </tr>
 @DspErrMsg(Err_SLACTV)

 <tr><td>&nbsp;</td>
     <td class="inputnmbr"><input name="terminatedEmpl" type="checkbox" VALUE='1' $(checkedTermEmpl)>Include Terminated Employees</td>
 </tr>
