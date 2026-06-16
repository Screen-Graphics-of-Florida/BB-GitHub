%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Attachment                                                  *
*********************************************************************
%}
  @DTW_REPLACE(attachVarKey, "/", "+", attachVarKey)
  @DTW_REPLACE(attachVarKey, " ", "+", attachVarKey)
  <a href="$(homeURL)$(phpPath)Attachment.PHP$(altVarBase)&amp;attachFolder=@dtw_rurlescseq(attachFolder)&amp;attachForDesc=@dtw_rurlescseq(attachForDesc)&amp;attachVarKey=@dtw_rurlescseq(attachVarKey)&amp;userProfile=@dtw_rurlescseq(userProfile)&amp;attachPrg1=@dtw_rurlescseq(attachPrg1)&amp;attachPrg2=@dtw_rurlescseq(attachPrg2)&amp;attachPrg3=@dtw_rurlescseq(attachPrg3)&amp;attachPrg4=@dtw_rurlescseq(attachPrg4)&amp;attachPrg5=@dtw_rurlescseq(attachPrg5)" onclick="$(selectionWinVar)">$(attachImageLrg)</a>