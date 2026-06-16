%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: User View Error                                             *
*********************************************************************
%}
      @deleteUserHandle(profileHandle, dataBaseID)
      <link rel=stylesheet type="text/css" href="$(homeURL)$(homePath)$(casStyleSheet)">
      <div class="accessError">$(accessErrorDesc)</div>
      <meta http-equiv="refresh" content="$(accessErrorTime); URL=$(signonURL)">