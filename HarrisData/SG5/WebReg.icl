%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Web Registry Table Save/Retrieve                            *
*********************************************************************
%}

  %if (defaultSet == "Y")
      @Retrieve_WebReg(webRegCurFile, profileHandle, d2wName, wildCardSearch, orderBy, orderByDisplay, wildCardDisplay)
      @Save_WebReg(userProfile, "", d2wName, wildCardSearch, orderBy, orderByDisplay, wildCardDisplay)
  %endif

  %if (defaultView == "Y")
      @Delete_WebReg(webRegCurFile, profileHandle, d2wName)
  %else
      @Retrieve_WebReg(webRegCurFile, profileHandle, d2wName, wildCardSearch, orderBy, orderByDisplay, wildCardDisplay)
  %endif

  %if (orderBy == "")
      @Retrieve_WebReg(userProfile, "", d2wName, wildCardSearch, orderBy, orderByDisplay, wildCardDisplay)
      %if (orderBy != "")
          @Save_WebReg(webRegCurFile, profileHandle, d2wName, wildCardSearch, orderBy, orderByDisplay, wildCardDisplay)
      %endif
  %endif