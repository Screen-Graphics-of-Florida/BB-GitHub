%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: User Defined Number                                         *
*********************************************************************
%}
%MACRO_FUNCTION Build_User_Number (IN  CHAR(1800) userNumber,
			                       DEC(5,0)   wholeSize,
			                       DEC(2,0)   decimalSize,
			                   OUT CHAR(1800) outNumber)
  {
      @dtw_assign(leadZero, "")
      @dtw_assign(decZero, "")
      @dtw_assign(outNumber, userNumber)

      @dtw_pos(".", outNumber, decPos)

      %if (decimalSize > "0")
          @dtw_pos(".", outNumber, decPos)
          %if (decPos == "0")
              @dtw_insert(".", outNumber, @dtw_rlength(outNumber), outNumber)
              @dtw_pos(".", outNumber, decPos)
          %endif
          @dtw_assign(decZero, @dtw_rsubtract(decimalSize, @dtw_rsubtract(@dtw_rlength(outNumber), decPos)))
          @dtw_assign(lx, "1")
          %while(lx <= decZero) {
              @dtw_insert("0", outNumber, @dtw_rlength(outNumber), outNumber)
                  @dtw_add(lx, "1", lx)
          %}
      %else
          @dtw_assign(decPos, @dtw_radd(@dtw_rlength(outNumber), "1"))
      %endif

      @dtw_assign(leadZero, @dtw_rsubtract(wholeSize, @dtw_rsubtract(decPos, "1")))
      @dtw_assign(lx, "1")
      %while(lx <= leadZero) {
          @dtw_insert("0", outNumber, outNumber)
          @dtw_add(lx, "1", lx)
      %}
  %}