%{
**********************************************************************
*  Copr 1979 2006 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: General Ledger Account Include                               *
**********************************************************************
%}
  @dtw_assign(maintainVar, "$(genericVarBase)&amp;GLCompany=@dtw_rurlescseq(GLCompany)&amp;GLFacility=@dtw_rurlescseq(GLFacility)&amp;GLAcct=@dtw_rurlescseq(GLAcct)&amp;GLSub=@dtw_rurlescseq(GLSub)")

  <table $(contentTable)>
	     <colgroup>
	         <col width="85%">
	         <col width="10%">
      <tr><td><h1>$(page_title)</h1></td>
          <td class="toolbar">
              <a href="$(homeURL)$(cGIPath)GlCoFacAcct.d2w/REPORT$(d2wVarBase)" title="Return To General Ledger Account">$(portalHome)</a>
              %INCLUDE "HelpPage.icl"
          </td>
          </tr>
  </table>
  <table $(contentTable)>
      <tr>
          @RtvFldDesc("OBCO=$(GLCompany) and OBFAC=$(GLFacility) and OBACCT=$(GLAcct) and OBSUB=$(GLSub) ", "GLTROB", "Char(ifnull(Min(OBYEAR),0))", firstYear)
          @RtvFldDesc("OBCO=$(GLCompany) and OBFAC=$(GLFacility) and OBACCT=$(GLAcct) and OBSUB=$(GLSub) ", "GLTROB", "Char(ifnull(Max(OBYEAR),0))", lastYear)
          @RtvFldDesc("OBCO=$(GLCompany) and OBFAC=$(GLFacility) and OBACCT=$(GLAcct) and OBSUB=$(GLSub) and OBYEAR<$(GLYear) ", "GLTROB", "Char(ifnull(Max(OBYEAR),0))", prevYear)
          @RtvFldDesc("OBCO=$(GLCompany) and OBFAC=$(GLFacility) and OBACCT=$(GLAcct) and OBSUB=$(GLSub) and OBYEAR>$(GLYear) ", "GLTROB", "Char(ifnull(Min(OBYEAR),0))", nextYear)
          <td class="hdrtitl">Year:</td>
          <td><h2>&nbsp;
              @dtw_assign(F_GLYear, GLYear)  @YearFromCYY(F_GLYear)
              %if (firstYear > "0" && firstYear!=GLYear)
                  @dtw_assign(F_firstYear, firstYear)  @YearFromCYY(F_firstYear)
                  @dtw_assign(previousImageBegTitle, "View First Year $(F_firstYear)")
                  <a href="$(homeURL)$(cGIPath)GLCoFacAcctSelect.d2w/ENTRY$(maintainVar)&amp;GLYear=@dtw_rurlescseq(firstYear)&amp;GLPlan=@dtw_rurlescseq(GLPlan)">$(previousImageBegSml)</a>
              %endif

              %if (prevYear > "0")
                  @dtw_assign(F_prevYear, prevYear)  @YearFromCYY(F_prevYear)
                  @dtw_assign(previousImageTitle, "View Previous Year $(F_prevYear)")
                  <a href="$(homeURL)$(cGIPath)GLCoFacAcctSelect.d2w/ENTRY$(maintainVar)&amp;GLYear=@dtw_rurlescseq(prevYear)&amp;GLPlan=@dtw_rurlescseq(GLPlan)">$(previousImageSml)</a>
              %endif
              $(F_GLYear)
              %if (nextYear > "0")
                  @dtw_assign(F_nextYear, nextYear)  @YearFromCYY(F_nextYear)
                  @dtw_assign(nextImageTitle, "View Next Year $(F_nextYear)")
                  <a href="$(homeURL)$(cGIPath)GLCoFacAcctSelect.d2w/ENTRY$(maintainVar)&amp;GLYear=@dtw_rurlescseq(nextYear)&amp;GLPlan=@dtw_rurlescseq(GLPlan)">$(nextImageSml)</a>
              %endif
              %if (lastYear > "0" && lastYear!=GLYear)
                  @dtw_assign(F_lastYear, lastYear)  @YearFromCYY(F_lastYear)
                  @dtw_assign(nextImageEndTitle, "View Last Year $(F_lastYear)")
                  <a href="$(homeURL)$(cGIPath)GLCoFacAcctSelect.d2w/ENTRY$(maintainVar)&amp;GLYear=@dtw_rurlescseq(lastYear)&amp;GLPlan=@dtw_rurlescseq(GLPlan)">$(nextImageEndSml)</a>
              %endif
          </h2></td>
      </tr>

      <tr>
          @RtvFldDesc("BDCO=$(GLCompany) and BDFAC=$(GLFacility) and BDACCT=$(GLAcct) and BDSUB=$(GLSub) ", "GLBGDT", "Char(ifnull(Min(BDNMBR),0))", firstPlan)
          @RtvFldDesc("BDCO=$(GLCompany) and BDFAC=$(GLFacility) and BDACCT=$(GLAcct) and BDSUB=$(GLSub) ", "GLBGDT", "Char(ifnull(Max(BDNMBR),0))", lastPlan)
          @RtvFldDesc("BDCO=$(GLCompany) and BDFAC=$(GLFacility) and BDACCT=$(GLAcct) and BDSUB=$(GLSub) and BDNMBR<$(GLPlan) ", "GLBGDT", "Char(ifnull(Max(BDNMBR),0))", prevPlan)
          @RtvFldDesc("BDCO=$(GLCompany) and BDFAC=$(GLFacility) and BDACCT=$(GLAcct) and BDSUB=$(GLSub) and BDNMBR>$(GLPlan) ", "GLBGDT", "Char(ifnull(Min(BDNMBR),0))", nextPlan)
          <td class="hdrtitl">Budget Plan:</td>
          <td><h2>&nbsp;
              @RtvFldDesc("BGNMBR=$(GLPlan) ", "GLBGHD", "BGDESC", V_BGDESC)
              @Format_Code(GLPlan, F_GLPlan)
              %if (firstPlan > "0" && firstPlan!=GLPlan)
                  @dtw_assign(previousImageBegTitle, "View First Plan $(firstPlan)")
                  <a href="$(homeURL)$(cGIPath)GLCoFacAcctSelect.d2w/ENTRY$(maintainVar)&amp;GLYear=@dtw_rurlescseq(GLYear)&amp;GLPlan=@dtw_rurlescseq(firstPlan)">$(previousImageBegSml)</a>
              %endif
              %if (prevPlan > "0")
                  @dtw_assign(previousImageTitle, "View Previous Plan $(prevPlan)")
                  <a href="$(homeURL)$(cGIPath)GLCoFacAcctSelect.d2w/ENTRY$(maintainVar)&amp;GLYear=@dtw_rurlescseq(GLYear)&amp;GLPlan=@dtw_rurlescseq(prevPlan)">$(previousImageSml)</a>
              %endif
              $(V_BGDESC) $(F_GLPlan)
              %if (nextPlan > "0")
                  @dtw_assign(nextImageTitle, "View Next Plan $(nextPlan)")
                  <a href="$(homeURL)$(cGIPath)GLCoFacAcctSelect.d2w/ENTRY$(maintainVar)&amp;GLYear=@dtw_rurlescseq(GLYear)&amp;GLPlan=@dtw_rurlescseq(nextPlan)">$(nextImageSml)</a>
              %endif
              %if (lastPlan > "0" && lastPlan!=GLPlan)
                  @dtw_assign(nextImageEndTitle, "View Last Plan $(lastPlan)")
                  <a href="$(homeURL)$(cGIPath)GLCoFacAcctSelect.d2w/ENTRY$(maintainVar)&amp;GLYear=@dtw_rurlescseq(GLYear)&amp;GLPlan=@dtw_rurlescseq(lastPlan)">$(nextImageEndSml)</a>
              %endif
           </h2></td>
      </tr>
  </table>

	<div id="main">
    <div id="contents">
