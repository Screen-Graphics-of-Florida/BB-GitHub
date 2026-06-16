%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Apply Edit Code To Number                                   *
*********************************************************************
%}
%MACRO_FUNCTION Format_Nbr (IN    CHAR(50) inNumber,
                            OUT   CHAR(50) outNumber,
                            IN    CHAR(2)  decimals,
                            IN    CHAR(1)  editCode,
                            IN    CHAR(1)  roundNbr,
                            IN    CHAR(1)  beforeChar,
			            IN    CHAR(1)  afterChar)
  {
      %if (roundNbr == "Y")
          @dtw_assign(inNumber, @dtw_rformat($(inNumber), "21", decimals, "0", "", @dtw_radd("21",decimals)))
      %endif
      @dtw_assign(inNumber,@dtw_rstrip(inNumber))
      @dtw_assign(outNumber, "")
      @dtw_pos("-", inNumber, negPos)
      @dtw_pos(".", inNumber, perPos)
      @dtw_length(inNumber, length)

      %if (decimals>"0")
          %if (perPos=="0")
              @dtw_concat(inNumber, ".", inNumber)
              @dtw_assign(nbrX, "1")
              %while(nbrX <= decimals) {
                  @dtw_concat(inNumber, "0", inNumber)
                  @dtw_add(nbrX, "1", nbrX)
              %}
          %else
              @dtw_assign(nbrX, @dtw_rsubtract(length, perPos))
              %while(nbrX == "0") {
                  @dtw_concat(inNumber, "0", inNumber)
                  @dtw_subtract(nbrX, "1", nbrX)
              %}
          %endif
          @dtw_pos(".", inNumber, perPos)
          @dtw_length(inNumber, length)
      %endif

      %if (perPos=="0")
          @dtw_add(length, "1", perPos)
      %endif

      @dtw_assign(zero, "")
      %if (decimals=="0" || editCode=="Z")
          @dtw_assign(dec, "")
      %else
          @dtw_assign(dec, $(decimalChar))
      %endif

      %if (editCode=="2" || editCode=="4" || editCode=="B" || editCode=="D" || editCode=="K" || editCode=="M" || editCode=="Z")
           @dtw_assign(nbrX, "1")
           @dtw_assign(zero, "Y")
           %while(nbrX <= length && zero=="Y") {
               %if (@dtw_rsubstr(inNumber, nbrX, "1") > "0")
                   @dtw_assign(zero, "")
               %endif
               @dtw_add(nbrX, "1", nbrX)
           %}
      %endif

      %if (zero!="Y")
          %if (afterChar!="")
              @dtw_concat("$(afterChar)", outNumber, outNumber)
          %endif

          %if (negPos>"0")
              @dtw_assign(intPos, @dtw_radd(negPos, "1"))
          %else
              @dtw_assign(intPos, "1")
          %endif
          %if (editCode=="J" || editCode=="K" || editCode=="L" || editCode=="M")
              %if (negPos>"0" && creditCodeOvr=="Y")
                  @dtw_concat(")", outNumber, outNumber)
              %elif (negPos>"0")
                  @dtw_concat("-", outNumber, outNumber)
              %else
                  @dtw_concat("&nbsp;", outNumber, outNumber)
              %endif
          %elif (editCode=="A" || editCode=="B" || editCode=="C" || editCode=="D")
              %if (negPos>"0" && creditCodeOvr=="Y")
                  @dtw_concat(")", outNumber, outNumber)
              %elif (negPos>"0")
                  @dtw_concat("CR", outNumber, outNumber)
              %elif (creditCodeOvr=="Y")
                  @dtw_concat("&nbsp;", outNumber, outNumber)
              %else
                  @dtw_concat("&nbsp; &nbsp; &nbsp;", outNumber, outNumber)
              %endif
          %endif

          %if (perPos>"0")
              @dtw_concat("$(dec)@dtw_rsubstr(inNumber, @dtw_radd(perPos, "1"), "$(decimals)")", outNumber, outNumber)
          %endif
          @dtw_assign(numInt, @dtw_rsubtract(perPos, intPos))
          %if (negPos>"0")
              @dtw_add(numInt, "1", numInt)
          %endif

          @dtw_assign(intCnt, "0")
          %while(numInt > negPos){
              @dtw_add(intCnt, "1", intCnt)
              %if (intCnt=="4" && (editCode=="1" || editCode=="2" || editCode=="A" || editCode=="B" || editCode=="J" || editCode=="K"))
                  @dtw_concat($(thousandChar), outNumber, outNumber)
                  @dtw_assign(intCnt, "1")
              %endif
              @dtw_concat("@dtw_rsubstr(inNumber, "$(numInt)", "1")", outNumber, outNumber)
              @dtw_subtract(numInt, "1", numInt)
          %}

          %if (negPos>"0" && creditCodeOvr=="Y" && editCode!="1" && editCode!="2" && editCode!="3" && editCode!="4")
              @dtw_concat("(", outNumber, outNumber)
          %endif

          %if (beforeChar!="")
              @dtw_concat("$(beforeChar)", outNumber, outNumber)
          %endif
      %endif

  %}