%{*******************************************************************
* Copr 1979 2001 An Unpublished Work By Harrris Business Group, Inc.*
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Anonymous Welcome Page                                      *
*********************************************************************
%}

  @SessionDate(profileHandle, dataBaseID, sessionDateFormat)

  <table border="0" cellpadding="0" cellspacing="0" width="490">
      <tr>
        <td>
          <h1>Welcome to the $(title)</h1>
          $(hrTagAttr)
          <p>
            <b>$(sessionDateFormat)</b>
          </p>
          <p>
            Welcome to the HarrisData Portal!
            This portal is the gateway to information in the HarrisData system.
            On the left, you'll find links to information throughout the system.
            Just point, click, and explore!
          </p>
          <p>
            The look and feel of the HarrisData EIP can be easily customized using simple web techniques.
            Contact <a href="http://www.harrisdata.com" title="HarrisData Online">HarrisData Support</a> for more details.
          </p>
          $(hrTagAttr)
         <div class="copr">$(copyright)</div>
        </td>
      </tr>
  </table>