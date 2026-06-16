%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Quick Link End Loop                                          *
**********************************************************************
%}
      %endif

      %if (quicklinkSelected != "" && quicklinkSelected != "viewAll" && quicklinkSelected != "allRows" && quicklinkSelected != "defaultRows")
          %if (quicklinkSelected != "useDefault" && quicklinkSelected != "saveDefault")
              @dtw_assign(x, "0")
          %endif
          @dtw_assign(quicklinkSelected, "")
          @dtw_assign(quicklinkSavSeq, quicklinkSelSeq)
          @dtw_assign(quicklinkSelSeq, "")
      %endif
      @dtw_add(x, "1", x)
  %}