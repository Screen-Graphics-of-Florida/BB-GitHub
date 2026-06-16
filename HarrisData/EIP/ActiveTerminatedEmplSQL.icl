%{
**********************************************************************
*  Copr 1979 2002 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Active and terminated employee selection for SQL             *
**********************************************************************
%}
  %if ((inclActive == "Y") && (inclTerminated != "Y"))
      @dtw_concat(selectSQL, "  and EMTRCD = ' '", selectSQL)
  %elif ((inclActive != "Y") && (inclTerminated == "Y"))
      @dtw_concat(selectSQL, "  and EMTRCD <> ' '", selectSQL)
  %elif ((inclActive != "Y") && (inclTerminated != "Y"))
      @dtw_concat(selectSQL, "  and EMTRCD = ' ' and EMTRCD <> ' '", selectSQL)
  %endif