%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Event Code                                                  *
*********************************************************************
%}
  @dtw_tb_rows(eventCodeTable, eventMaxRows)

  %if (eventMaxRows != "0")
      <select name="url" onChange="gotosite(this.options[this.selectedIndex].value)">
      <option disabled>Schedule/Record Event:

      %if (evtsec_01 == "Y")
          @dtw_assign(ux, "1")
          %while(ux <= eventMaxRows){
              @dtw_tb_getv(eventCodeTable, ux, @dtw_tb_rQuerycolnonj(eventCodeTable, "ECEVNT"), V_ECEVNT)
              @dtw_tb_getv(eventCodeTable, ux, @dtw_tb_rQuerycolnonj(eventCodeTable, "ECDESC"), V_ECDESC)
              %if (fileName == "CRCEVU")
                  <option value="$(homeURL)$(cGIPath)customerContactEventMaintain.d2w/EVENT$(genericVarBase)&amp;contactNumber=@dtw_rurlescseq(contactNumber)&amp;fromContactNumber=@dtw_rurlescseq(contactNumber)&amp;customerNumber=@dtw_rurlescseq(customerNumber)&amp;customerName=@dtw_rurlescseq(customerName)&amp;eventCode=@dtw_rurlescseq(V_ECEVNT)&amp;eventSequence=0&amp;origSequence=@dtw_rurlescseq(origSequence)&amp;fromD2w=@dtw_rurlescseq(d2wName)&amp;maintenanceCode=A">&nbsp; &nbsp; $(V_ECDESC)
              %elif (fileName == "SRCEVU")
                  <option value="$(homeURL)$(cGIPath)supplierContactEventMaintain.d2w/EVENT$(genericVarBase)&amp;contactNumber=@dtw_rurlescseq(contactNumber)&amp;fromContactNumber=@dtw_rurlescseq(contactNumber)&amp;vendorNumber=@dtw_rurlescseq(vendorNumber)&amp;vendorName=@dtw_rurlescseq(vendorName)&amp;eventCode=@dtw_rurlescseq(V_ECEVNT)&amp;eventSequence=0&amp;origSequence=@dtw_rurlescseq(origSequence)&amp;fromD2w=@dtw_rurlescseq(d2wName)&amp;maintenanceCode=A">&nbsp; &nbsp; $(V_ECDESC)
              %endif
              @dtw_add(ux, "1", ux)
          %}
      %endif

      </select>
  %endif