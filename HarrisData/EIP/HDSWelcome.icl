%{*******************************************************************
* Copr 1979 2001 An Unpublished Work By Harrris Business Group, Inc.*
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Welcome Page                                                *
*********************************************************************
%}

  @SessionDate(profileHandle, dataBaseID, sessionDateFormat)

  <h1>Welcome, $(profileName), to the $(title)</h1>

  $(hrTagAttr) 	

  <div style="padding: 1ex 1ex 1ex 1ex;">
    Today is <span style="font-weight: bold;">$(sessionDateFormat)</span>
  </div>

  <div style="padding: 1ex 1ex 1ex 1ex;">
    Welcome to your personalized information portal!
    This portal is your gateway to information in your HarrisData system.
    On the left, you'll find links to information you are authorized to access throughout the system.
    Just point, click, and explore!
  </div>

  <div style="padding: 1ex 1ex 1ex 1ex;">
    The look and feel of the HarrisData EIP can be easily customized using simple web techniques.
    Contact <a href="http://www.harrisdata.com/cusHotline.htm" title="HarrisData Online">HarrisData Support</a> for more details.
  </div>

  $(hrTagAttr)

  %INCLUDE "Copyright.icl"
