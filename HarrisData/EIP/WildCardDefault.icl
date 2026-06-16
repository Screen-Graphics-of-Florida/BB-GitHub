%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Display/Reset Of WilCard Selection Criteria                 *
*********************************************************************
%}

  @Retrieve_WebReg(webRegCurFile, profileHandle, d2wName, w_wildCardSearch, w_orderBy, orderByDisplay, wildCardDisplay)
  %if (wildCardDisplay != "")
      <td class="page"><a href="$(homeURL)$(cGIPath)WildCardDisplay.d2w/DISPLAY$(genericVarBase)$(orderByVarBase)$(wildCardVarBase)&amp;userProfile=@dtw_rurlescseq(userProfile)&amp;fromd2wName=@dtw_rurlescseq(d2wName)&amp;fromPageTitle=@dtw_rurlescseq(page_title)" onclick="$(wildCardWinVar)">$(wildViewImage)</a></td>
  %endif
  <td class="page"><a href="$(homeURL)$(cGIPath)$(d2wName)/$(wildDftVar)&amp;defaultView=Y">$(wildDftImage)</a></td>
  <td class="page"><a href="$(homeURL)$(cGIPath)$(d2wName)/$(wildDftVar)&amp;wildCardDisplay=@dtw_rurlescseq(wildCardDisplay)&amp;defaultSet=Y">$(wildSetImage)</a></td>
  %if (advanceSearch != "N")
      <td class="page"><a href="$(homeURL)$(cGIPath)$(d2wName)/MASTERSEARCH$(d2wVarBase)$(orderByVarBase)&amp;defaultSearch=Y$(advSrchVar)">$(wildChgImage)</a></td>
  %endif
  <td class="page"><a href="$(homeURL)$(cGIPath)$(d2wName)/WILDCARD$(d2wVarBase)$(orderByVarBase)&amp;wildCardSearch=$(advSrchVar)">$(wildClearImage)</a></td>
