%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Display/Reset Of WilCard Selection Criteria                 *
*********************************************************************
%}

  %if (wildCardDisplay != "")
      <fieldset class="legendBody">
          <legend  class="legendTitle">Current Search Criteria</legend>
          <table $(contentTable)>
              <colgroup>
                  <col width="99%">
                  <col width="1%">
              <tr><td class="toolbar"><td>&nbsp;</td><td><a href="$(wildCardResetURL)">$(wildClearLrg)</a></td></tr>
              <tr><td class="searchcriteria">$(wildCardDisplay)</td></tr>
          </table>
      </fieldset>
  %endif