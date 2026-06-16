%{
**********************************************************************
*  Copr 1979 2002 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Employee URL Parameters                                      *
**********************************************************************
%}
  @dtw_replace(workURL, "@@prCompany", "@dtw_rurlescseq(prCompany)", "1", "a", workURL)
  @dtw_replace(workURL, "@@prFacility", "@dtw_rurlescseq(prFacility)", "1", "a", workURL)
  @dtw_replace(workURL, "@@prEmployee", "@dtw_rurlescseq(prEmployee)", "1", "a", workURL)
  @dtw_replace(workURL, "@@hrCompany", "@dtw_rurlescseq(hrCompany)", "1", "a", workURL)
  @dtw_replace(workURL, "@@hrEmployee", "@dtw_rurlescseq(hrEmployee)", "1", "a", workURL)
  @dtw_replace(workURL, "@@backHome", "@dtw_rurlescseq(backHome)", "1", "a", workURL)