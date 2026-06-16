%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Set Schema List                                             *
*********************************************************************
%}

      @dtw_assign(userProfile, @dtw_ruppercase(REMOTE_USER))
	  @RtvFldDesc("HNHAND='$(eID)'", "SYHAND", "HNUSER", handUser)
	  %if (userProfile != handUser)
	      @dtw_assign(eID, "")
	  %endif
	  

  @setLibl(profileHandle, dataBaseID, userProfile, authHandle, eID, HTTP_AS_AUTH_PROFILETKN, activeRole, tokenError)
