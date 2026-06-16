%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Search Button Include                                       *
*********************************************************************
%}
      <fieldset class="legendBody">
          <legend  class="legendTitle">Refine Search Criteria</legend>
                          <table $(contentTable)>
      <colgroup>
	         <col width="89%">
	         <col width="6%">
      <tr><td class="searchCriteria">
              <input type="hidden" name="updateSearch" value="Y">
              %if (wildCardDisplay != "")
                  Add To Search:
                  <input type="radio" name="andOr" value="and" CHECKED>And
                  <input type="radio" name="andOr" value="or">Or &nbsp;
              %endif
          </td>
          <td class="toolbar">
              <a href="$(homeURL)$(cGIPath)$(d2wName)/$(wildDftVar)&amp;defaultView=Y&amp;returnToSearch=Y">$(wildDftLrg)</a>
              <a href="javascript:document.Search.updateSearch.value='Y'; check(document.Search)">$(addToImage)</a>
          </td>
      </tr>
  </table>
  <table $(contentTable)>
  <tr>
      <th class="dsphdr">&nbsp;</th>
      <th class="dsphdr">Operand</th>
      %if (fromToSearch=="Y")
          <th class="dsphdr">From</th>
          <th class="dsphdr">To</th>
      %else
          <th class="dsphdr">Search Data</th>
      %endif
  </tr>