%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Display/Reset Of WildCard Selection Criteria                *
*********************************************************************
%}

  %if (formatToPrint != "" && hideSelectCriteria != "Y")
      @Retrieve_WebReg(webRegCurFile, profileHandle, d2wName, w_wildCardSearch, w_noorderBy, orderByDisplay, wildCardDisplay)
      %if (wildCardDisplay != "")
          <fieldset class="legendTitle">
              <legend class="searchcriteria">Search Criteria <a href="$(homeURL)$(cGIPath)$(d2wName)/$(wildDftVar)&amp;formatToPrint=Y&amp;hideSelectCriteria=Y">$(closeSelImage)</a></legend>
                  <table $(contentTable)>
                      <tr><td class="searchcriteria">$(wildCardDisplay) </td></tr>
                      <tr><td class="searchcriteria">Sequence by $(orderByDisplay)</td></tr>
                  </table>
          </fieldset>
      %endif
  %endif