%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Assign Page Value                                           *
*********************************************************************
%}
          <script TYPE="text/javascript">
              %INCLUDE "ShowHideSelCriteria.icl"
          </script>

  %if (sql_Record_Count > RPT_MAX_ROWS)  %{Assign Paging Values %}

      @DTW_ASSIGN(totalPages, @DTW_RINTDIV(sql_Record_Count, RPT_MAX_ROWS))
      %if (@DTW_RDIVREM(sql_Record_Count, RPT_MAX_ROWS) > "0")
          @DTW_ADD(totalPages, "1", totalPages)
      %endif
  %else
      @DTW_ASSIGN(totalPages, "1")
  %endif

  @dtw_assign(page, @dtw_radd(@dtw_rdivide(@dtw_rsubtract(START_ROW_NUM, "1"), RPT_MAX_ROWS), "1"))
  @dtw_add(START_ROW_NUM, RPT_MAX_ROWS, rowIndexNext)

  <div class="page">Page:
      %if (sql_Record_Count > RPT_MAX_ROWS && pageSelectList == "Y")
          @dtw_format(@dtw_rdivide(sql_Record_Count, RPT_MAX_ROWS), "5", "1", loop)
          @dtw_assign(cnt, "1")
          <select class="page" name="goToPage" id="goToPage" onChange="goToPage(this.options[this.selectedIndex].value)">
              <option value="$(homeURL)$(cGIPath)$(d2wName)/INPUT$(nextPrevVar)&amp;START_ROW_NUM=1&amp;RPT_MAX_ROWS=$(RPT_MAX_ROWS)">1
              %while(@dtw_rformat(cnt, "5", "1") < loop){
                  @dtw_assign(pageValue, @dtw_radd(@dtw_rmultiply(cnt, RPT_MAX_ROWS), "1"))
                  @dtw_add(cnt, "1", cnt)
                  %if (cnt == page)
                      <option value="$(homeURL)$(cGIPath)$(d2wName)/INPUT$(nextPrevVar)&amp;START_ROW_NUM=$(pageValue)&amp;RPT_MAX_ROWS=$(RPT_MAX_ROWS)" SELECTED>$(cnt)
                  %else
                      <option value="$(homeURL)$(cGIPath)$(d2wName)/INPUT$(nextPrevVar)&amp;START_ROW_NUM=$(pageValue)&amp;RPT_MAX_ROWS=$(RPT_MAX_ROWS)">$(cnt)
                  %endif
              %}
          </select>
      %else
          $(page)
      %endif
      of $(totalPages)
      %if (nextPrevPos != "2" && nextPrevVar != "")
          %if (START_ROW_NUM > RPT_MAX_ROWS)
              <a href="input$(nextPrevVar)&amp;START_ROW_NUM=@DTW_RSUBTRACT(START_ROW_NUM, RPT_MAX_ROWS)&amp;RPT_MAX_ROWS=$(RPT_MAX_ROWS)">$(previousImage)</a>
          %else
              %if (sql_Record_Count > RPT_MAX_ROWS)
                  $(nextPrevBlank)
              %endif
          %endif

          %if (sql_Record_Count >= rowIndexNext)
              <a href="input$(nextPrevVar)&amp;START_ROW_NUM=$(rowIndexNext)&amp;RPT_MAX_ROWS=$(RPT_MAX_ROWS)">$(nextImage)</a>
          %else
              %if (sql_Record_Count > RPT_MAX_ROWS)
                  $(nextPrevBlank)
              %endif
          %endif
      %endif

      @Retrieve_WebReg(webRegCurFile, profileHandle, d2wName, w_wildCardSearch, w_orderBy, orderByDisplay, wildCardDisplay)
      %if (wildCardDisplay != "")
          <a href="javascript:void+0" onMouseOver="showSel('selData')" onMouseOut="hideSel('selData')">$(wildView)</a>
      %endif
      %if (wildCardDefault != "N")
          <a href="$(homeURL)$(cGIPath)$(d2wName)/$(wildDftVar)&amp;defaultView=Y">$(wildDftImage)</a>
          <a href="$(homeURL)$(cGIPath)$(d2wName)/$(wildDftVar)&amp;wildCardDisplay=@dtw_rurlescseq(wildCardDisplay)&amp;defaultSet=Y">$(wildSetImage)</a>
      %endif
      %if (advanceSearch != "N")
          <a href="$(homeURL)$(cGIPath)$(d2wName)/MASTERSEARCH$(d2wVarBase)$(orderByVarBase)&amp;defaultSearch=Y$(advSrchVar)">$(wildChgImage)</a>
      %endif
      <a href="$(homeURL)$(cGIPath)$(d2wName)/WILDCARD$(d2wVarBase)$(orderByVarBase)&amp;wildCardSearch=$(advSrchVar)">$(wildClearImage)</a>

      %INCLUDE "ViewCheckBox.icl"
      %INCLUDE "ViewRadioButton.icl"
  </div>
  <div id="selData" class="searchHover"><table $(quickSearchTable)><tr><td class="colhdr">Search Criteria</td><td class="colhdr">Sequence By</td></tr> <tr><td valign="top" class="colalph">$(wildCardDisplay)</td><td valign="top" class="colalph">$(orderByDisplay)</td></tr></table></div>
