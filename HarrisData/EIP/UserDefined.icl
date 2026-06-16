%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: User-Defined Table Include                                  *
*********************************************************************
%}
  @dtw_tb_getv(userDefinedTable, ux, @dtw_tb_rQuerycolnonj(userDefinedTable, "UFFLDN"), V_UFFLDN)
  @dtw_tb_getv(userDefinedTable, ux, @dtw_tb_rQuerycolnonj(userDefinedTable, "UFDESC"), V_UFDESC)
  @dtw_tb_getv(userDefinedTable, ux, @dtw_tb_rQuerycolnonj(userDefinedTable, "UFTYPE"), V_UFTYPE)
  @dtw_tb_getv(userDefinedTable, ux, @dtw_tb_rQuerycolnonj(userDefinedTable, "UFFFMT"), V_UFFFMT)
  @dtw_tb_getv(userDefinedTable, ux, @dtw_tb_rQuerycolnonj(userDefinedTable, "UFSIZE"), V_UFSIZE)
  @dtw_tb_getv(userDefinedTable, ux, @dtw_tb_rQuerycolnonj(userDefinedTable, "UFDECM"), V_UFDECM)
  @dtw_tb_getv(userDefinedTable, ux, @dtw_tb_rQuerycolnonj(userDefinedTable, "UFVALU"), V_UFVALU)
  @dtw_tb_getv(userDefinedTable, ux, @dtw_tb_rQuerycolnonj(userDefinedTable, "UFBOXS"), V_UFBOXS)
  @dtw_tb_getv(userDefinedTable, ux, @dtw_tb_rQuerycolnonj(userDefinedTable, "UFREQF"), V_UFREQF)
  @dtw_tb_getv(userDefinedTable, ux, @dtw_tb_rQuerycolnonj(userDefinedTable, "UFVLDV"), V_UFVLDV)