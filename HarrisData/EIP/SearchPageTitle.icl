%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Search Page Title                                           *
*********************************************************************
%}
    @dtw_pos("Search", "$(page_title)", posSearch)
    %if (posSearch != "0")
        @dtw_assign(searchTitle, "$(page_title)")
    %else
        @dtw_assign(searchTitle, "$(page_title) Search")
    %endif
    <table $(contentTable)>
	       <colgroup>
	           <col width="89%">
	           <col width="6%">
        <tr><td><h1>$(searchTitle)</h1></td>
            <td class="toolbar">
              <a href="javascript:document.Search.updateSearch.value='N'; check(document.Search)">$(goSearchImageLrg)</a>
              %INCLUDE "HelpPage.icl"
            </td>
        </tr>
    </table>
