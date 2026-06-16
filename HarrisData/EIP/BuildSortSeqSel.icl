%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Build Sort Sequence Selection                               *
*********************************************************************
%}

%MACRO_FUNCTION Build_Sort_Select (IN CHAR(20) sortName,
                                      CHAR(2)  sortValue)
{
  <td align="center">
      <select name="$(sortName)">
          <option value="0">
          @dtw_assign(cnt, "1")
          %while(cnt <= sortSeqMax){
              %if (cnt == sortValue)
                  <option value="$(cnt)" SELECTED>$(cnt)
              %else
                  <option value="$(cnt)">$(cnt)
              %endif
              @dtw_add(cnt, "1", cnt)
          %}
      </select>
  </td>
%}