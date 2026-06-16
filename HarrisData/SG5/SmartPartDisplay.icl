%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Assign Page Value                                           *
*********************************************************************
%}
      %if (smartPartDspSize == "")
          @dtw_assign(smartPartDspSize, "60")
      %endif
      @dtw_length(V_SPPART, len)
      @dtw_multiply(smartPartDspSize, "6", dspPX)
      %if (len > smartPartDspSize)
          @BrowserType(browserCode)
          %if (browserCode == "IE")
              @dtw_assign(pxSize, "15")
          %else
              @dtw_assign(pxSize, "2")
          %endif
          <div style="width: $(dspPX)px; overflow-x: auto; overflow-y: hidden; padding-bottom:$(pxSize)px;">$(V_SPPART)</div>
      %else
          $(V_SPPART)
      %endif
