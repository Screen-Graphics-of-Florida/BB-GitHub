%{
*********************************************************************
* Copr 1979 2023 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                *
*  Job: Sales Forecast Inquiry Options                              *
*********************************************************************
%}
      <tr>
          <td class="toolbar">
              <div id="options" class="moreInfoPos">
                  <table $(quickSearchTable)>
                      <colgroup>
                          <col width="80%">
                          <col width="15%">
                      <tr><td><h1>Sales Forecast Options</h1></td>
                          <td class="toolbar">
                              <a href="javascript:check(document.Search)">$(acceptImageMed)</a>
                              <a href="javascript:void+0" onClick="hideSel('options')">$(closeImageMed)</a>
                          </td>
                      </tr>
                  </table>
                  $(searchhrTagAttr)

                  <form class="formClass" METHOD=POST NAME="Search" onSubmit="return validate(document.Search)" ACTION="$(homeURL)$(cGIPath)$(d2wName)/LOAD$(d2wVarBaseO)">
                      <table $(quickSearchTable)>
                          %if (periodFormat == "01")
                                  @dtw_assign(weekly, "CHECKED")
                                  @dtw_assign(monthly, "")
                                  @dtw_assign(quarterly, "")
                          %elif (periodFormat == "02")
                                  @dtw_assign(weekly, "")
                                  @dtw_assign(monthly, "CHECKED")
                                  @dtw_assign(quarterly, "")
                          %elif (periodFormat == "03")
                                  @dtw_assign(weekly, "")
                                  @dtw_assign(monthly, "")
                                  @dtw_assign(quarterly, "CHECKED")
                          %endif

                          %if (quantityFormat == "01")
                                  @dtw_assign(units, "CHECKED")
                                  @dtw_assign(currentlist, "")
                                  @dtw_assign(standardcost, "")
                                  @dtw_assign(currentcost, "")
                                  @dtw_assign(futurecost, "")
                                  @dtw_assign(averagecost, "")
                          %elif (quantityFormat == "02")
                                  @dtw_assign(units, "")
                                  @dtw_assign(currentlist, "CHECKED")
                                  @dtw_assign(standardcost, "")
                                  @dtw_assign(currentcost, "")
                                  @dtw_assign(futurecost, "")
                                  @dtw_assign(averagecost, "")
                          %elif (quantityFormat == "03")
                                  @dtw_assign(units, "")
                                  @dtw_assign(currentlist, "")
                                  @dtw_assign(standardcost, "CHECKED")
                                  @dtw_assign(currentcost, "")
                                  @dtw_assign(futurecost, "")
                                  @dtw_assign(averagecost, "")
                          %elif (quantityFormat == "04")
                                  @dtw_assign(units, "")
                                  @dtw_assign(currentlist, "")
                                  @dtw_assign(standardcost, "")
                                  @dtw_assign(currentcost, "CHECKED")
                                  @dtw_assign(futurecost, "")
                                  @dtw_assign(averagecost, "")
                          %elif (quantityFormat == "05")
                                  @dtw_assign(units, "")
                                  @dtw_assign(currentlist, "")
                                  @dtw_assign(standardcost, "")
                                  @dtw_assign(currentcost, "")
                                  @dtw_assign(futurecost, "CHECKED")
                                  @dtw_assign(averagecost, "")
                           %elif (quantityFormat == "06")
                                  @dtw_assign(units, "")
                                  @dtw_assign(currentlist, "")
                                  @dtw_assign(standardcost, "")
                                  @dtw_assign(currentcost, "")
                                  @dtw_assign(futurecost, "")
                                  @dtw_assign(averagecost, "CHECKED")
                          %endif

                          <tr><td class="dsphdr">Select Period Format</td>
                              <td class="inputnmbr"><input name="periodFormat" type="radio" VALUE='01' $(weekly)>Weekly</td>
                          </tr>
                          <tr><td>&nbsp;</td>
                              <td class="inputnmbr"><input name="periodFormat" type="radio" VALUE='02' $(monthly)>Monthly</td>
                          </tr>
                          <tr><td>&nbsp;</td>
                              <td class="inputnmbr"><input name="periodFormat" type="radio" VALUE='03' $(quarterly)>Quarterly</td>
                          </tr>

                          <tr><td>&nbsp;</td></tr>

                          <tr><td class="dsphdr">Select Display Format</td>
                              <td class="inputnmbr"><input name="quantityFormat" type="radio" VALUE='01' $(units)>Units</td>
                          </tr>
                          <tr><td>&nbsp;</td>
                              <td class="inputnmbr"><input name="quantityFormat" type="radio" VALUE='02' $(currentlist)>Current List</td>
                          </tr>
                          <tr><td>&nbsp;</td>
                              <td class="inputnmbr"><input name="quantityFormat" type="radio" VALUE='03' $(standardcost)>Standard Cost</td>
                          </tr>
                          <tr><td>&nbsp;</td>
                              <td class="inputnmbr"><input name="quantityFormat" type="radio" VALUE='04' $(currentcost)>Current Cost</td>
                          </tr>
                          <tr><td>&nbsp;</td>
                              <td class="inputnmbr"><input name="quantityFormat" type="radio" VALUE='05' $(futurecost)>Future Cost</td>
                          </tr>
                          <tr><td>&nbsp;</td>
                              <td class="inputnmbr"><input name="quantityFormat" type="radio" VALUE='06' $(averagecost)>Average Cost</td>
                          </tr>
                      </table>
                  </form>
              </div>
          </td>
      </tr>
