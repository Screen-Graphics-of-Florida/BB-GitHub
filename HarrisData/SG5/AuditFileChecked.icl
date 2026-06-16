%{
**********************************************************************
*  Copr 1979 2006 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Audit Table Checked SQL Statements                           *
**********************************************************************
%}


                      %if ((inclCommon == "Y") && (inclHR == "Y") && (inclPayroll == "Y"))
                          @dtw_concat(selectSQL, " and (AUFILE='HREMPL' or AUFILE='PEEMPL' or AUFILE='PREMPL')", selectSQL)

                      %elseif ((inclCommon == "Y") && (inclHR == "Y") && (inclPayroll != "Y"))
                          @dtw_concat(selectSQL, " and (AUFILE='HREMPL' or AUFILE='PEEMPL')", selectSQL)

                      %elseif ((inclCommon == "Y") && (inclHR != "Y") && (inclPayroll == "Y"))
                          @dtw_concat(selectSQL, " and (AUFILE='HREMPL' or AUFILE='PREMPL')", selectSQL)

                      %elseif ((inclCommon == "Y") && (inclHR != "Y") && (inclPayroll != "Y"))
                          @dtw_concat(selectSQL, " and (AUFILE='HREMPL')", selectSQL)

                      %elseif ((inclCommon != "Y") && (inclHR == "Y") && (inclPayroll == "Y"))
                          @dtw_concat(selectSQL, " and (AUFILE='PEEMPL' or AUFILE='PREMPL')", selectSQL)

                      %elseif ((inclCommon != "Y") && (inclHR == "Y") && (inclPayroll != "Y"))
                          @dtw_concat(selectSQL, " and (AUFILE='PEEMPL')", selectSQL)

                      %elseif ((inclCommon != "Y") && (inclHR != "Y") && (inclPayroll == "Y"))
                          @dtw_concat(selectSQL, " and (AUFILE='PREMPL')", selectSQL)

                      %elseif ((inclCommon != "Y") && (inclHR != "Y") && (inclPayroll != "Y"))
                          @dtw_concat(selectSQL, " and (AUFILE=' ')", selectSQL)
                      %endif