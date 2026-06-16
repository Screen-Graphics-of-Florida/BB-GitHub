%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: HDS Sign On Page                                            *
*********************************************************************
%}

  %INCLUDE "$(docType)"
  <html>
	     <head>
          <link href="$(homeURL)$(imagePath)partners/eip.ico" rel="SHORTCUT ICON">
	         %INCLUDE "$(headInclude)"
          %INCLUDE "banner.icl"
	
	         <!-- Script for loading rollover IBM e-business mark -->
	         <script language="JavaScript">
		 var browser = navigator.appName;
		 var version = parseInt(navigator.appVersion);
		 var clientRolloverCapable=false;
		 var loaded=false;
		 var imagePath="$(homeURL)$(imagePath)partners/";
		 //detect browser
		 if ((browser =="Netscape" && version >= 3)||
		     (browser.indexOf("Microsoft")!=-1&& version>= 4)) {
		  clientRolloverCapable=true;
		 }
		 function loadImgs() {
		         if (clientRolloverCapable)
		 {
		 //preload rollover images
		 addOffsetImage(imagePath+"ebim_sc_st2.gif");
		 loaded = 1;
		        }
		 }
		 function addOffsetImage(imageSrc)
		 {
		 var offsetImage=new Image();
	    	 offsetImage.src=imageSrc;
		 }	
		 function switchImage(imageName,imageSrc)
		 {
		 if(clientRolloverCapable){
		        self.document.images[imageName].src=imageSrc;
		 }
		 }
		 var browser = navigator.appName;
		 var version = parseInt(navigator.appVersion);
              function getBrowser() {
		 if (browser.indexOf("Microsoft")!=-1 && version>= 4)
               {document.signon.browserCode.value="IE"}
              else
		  {document.signon.browserCode.value="XX"}
		 }
		 //-->
	         </script>

	     </head>

	     <body bgcolor="White" text="Black" link="Red" vlink="#0066CC"
	         onLoad="hdMenu.construct(); loadImgs()" leftmargin=0 topmargin=0 marginheight="0" marginwidth="0">

          <script language="JavaScript1.2" src="$(homeURL)$(imagePath)partners/coolmenus.js"></script>
          <script language="JavaScript1.2" src="$(homeURL)$(imagePath)partners/hdNav.js"></script>

	         <!-- Banner: Top of page rapid navigation bar -->
	         <table width="100%" border="0" cellspacing="0" cellpadding="3">
 	 <tr bgcolor="#0066CC"></tr>

              <tr valign="top">
                  <td width="152">
			      <table width="100%" border="0" cellpadding="10" cellspacing="0">
			          <tr><td>
				               &nbsp;<br><br><br><br><br><br><br><br><br><br>
			          </td></tr>
			          <tr><td>
					            <a href="http://www.ibm.com/e-business/casestudies" target="_blank"
					 	             onMouseover="switchImage('ebim_sc_st',imagePath+'ebim_sc_st2.gif');window.status ='e-business'; return true;"
						             onMouseout="switchImage('ebim_sc_st',imagePath+'ebim_sc_st1.gif');window.status =''; ">
						             <img name="ebim_sc_st" src="$(homeURL)$(imagePath)partners/ebim_sc_st1.gif" width="107" height="42" border="0">
					            </a>
					
			          </td></tr>
			          <tr><td>
				               <hr color="#0066CC" width="80%" size="1">
				               <a href="http://www.ibm.com/e-business/casestudies" target="_blank">
					                <div class="legal">Click for IBM Mark meaning and disclaimers.</div>
				               </a>
				               <br>
				               <div class="legal">IBM and e-business Mark are TM's of IBM Corp.</div>
				               <br>
			          </td></tr>
			      </table>
		     </td>
                  <td>
                      <table border="0" cellpadding="0" cellspacing="0" width="100%">
                          <tr><td nowrap><div class="head">$(page_title)</div></td></tr>
                          <tr><td><hr align=left size="1" noshade width="95%" color="#0066CC"></td></tr>
			          <tr><td>
                                 <div class="keytext">
    						         Welcome to the <b class="mainemph">HarrisData Enterprise Information Portal</b>!
    						         <br><br>
    						         If you know your user id and password, please sign in now.

    	  		                  <form NAME="signon" METHOD=POST ACTION="$(homeURL)$(cGIPath)signon.d2w/Edit_Password?browserCode=@dtw_rurlescseq(browserCode)" onSubmit="getBrowser()";>

    	                                     <table border=0>
        	                             <tr>
    						                     <td class="dsphdr">User:</td>
    					                        <td class="inputalph"><input type="text" class="input" name="userProfile" size="10" maxlength="10"></td>
    					                    </tr>
            	                             <tr>
    						                     <td class="dsphdr">Password:</td>
    						                     <td class="inputalph"><input type="password" class="input" name="password" size="10" maxlength="10"></td>
    					                    </tr>
    					                    <tr>
    						                     <td>&nbsp;</td>
                                                 <td><input TYPE=SUBMIT VALUE=" Sign On "></td>
                                                 <td><input type="hidden" name="browserCode"></td>
    					                    </tr>
                  	                      </table>
    	   		                  </form>
	          		                  </div>

                                  <!-- 	<div class="powerby" text color="blue"> Powered By:<img src="$(homeURL)$(imagePath)$(logo)"></a> eBiz</div> <p>
                                  -->              	
				           </td>
                          </tr>
			
                          <tr><td><hr align=left size="1" noshade width="95%" color="#0066CC"</td></tr>

			          <tr>
			  	          <td>
					           <div class="subhead">
						            <a href="HTTP://www.harrisdata.com/cusZone.htm">About the HarrisData EIP</a>
					           </div>
					           <div class="keytext">
						            The <b class="mainemph">HarrisData Enterprise Information Portal</b> ("EIP") offers members of your community secure, browser-based access to information in the HarrisData system.
						            Customers, salespeople, suppliers, and employees can get personalized, secured access to the information they need through the HarrisData EIP.
						            <br><br>
						            <b class="mainemph">Want to try it out?</b> <a href="HTTP://www.harrisdata.com/contact.htm">Contact us</a> for a guided tour of this revolutionary technology!
						            <br><br>
						            <a href="HTTP://www.harrisdata.com/proEIP.htm">Click here</a> to learn more about the HarrisData EIP.						
						            <br><br>
					           </div>
				          </td>
			          </tr>
			  			
                          <tr><td><hr align=left size="1" noshade width="95%" color="#0066CC"></td></tr>
		
			          <tr>
			  	           <td>
					            <div class="subhead">
						             <a href="HTTP://www.harrisdata.com/aboHome.htm">About HarrisData</a>
					            </div>
					            <div class="keytext">
              	  		                HarrisData offers Business Application Software to mid-sized organizations utilizing <b>IBM <img src="$(homeURL)$(imagePath)/ibmeserverlogo.gif" width="53" height="10" alt="" border="0"> iSeries</b> and <b>AS/400</b> computers.
						             <br><br>
						             <a href="HTTP://www.harrisdata.com/aboHome.htm">Click here</a> to learn more about HarrisData.						
						             <br><br>
					            </div>
				           </td>
			          </tr>
			  			
                          <tr><td><hr align=left size="1" noshade width="95%" color="#0066CC"></td></tr>
			
                          <tr>
		                 <td>
		                     <!-- Quick Navigation Menu for bottom of each page -->
  <table width="490"><tr><td>
     <!-- Quick Navigation Menu for bottom of each page -->
     <div style="font-family: Verdana, Arial, sans-serif; font-size: 8pt; text-align: center;">
       	<a href="http://www.harrisdata.com/aboHome.htm" style="color: #0066CC; text-decoration: none;"> About</a>
       	|
	<a href="http://www.harrisdata.com/proHome.htm" style="color: #0066CC; text-decoration: none;"> Products</a>
	|
 	<a href="http://www.harrisdata.com/serHome.htm" style="color: #0066CC; text-decoration: none;"> Services</a>
	|
 	<a href="http://www.harrisdata.com/parHome.htm" style="color: #0066CC; text-decoration: none;"> Partners</a>
	|
	<a href="http://www.harrisdata.com/cusHome.htm" style="color: #0066CC; text-decoration: none;"> Customers</a>
	|
 	<a href="http://www.harrisdata.com/supHome.htm" style="color: #0066CC; text-decoration: none;"> Supplies</a>
	<br>
	<a href="http://www.harrisdata.com/Home.htm" style="color: #0066CC; text-decoration: none;"> Home</a>
	|
	<a href="http://www.harrisdata.com/SiteMap.htm" style="color: #0066CC; text-decoration: none;"> Site Map</a>
	|
	<a href="http://www.harrisdata.com/Privacy.htm" style="color: #0066CC; text-decoration: none;"> Privacy Policy</a>
	|
	<a href="http://www.harrisdata.com/Contact.htm" style="color: #0066CC; text-decoration: none;"> Contact Us</a>
	<br>
    </div>
    <div style="font-family: Verdana, Arial, sans-serif; font-size: 8pt; text-align: center;">
	<br><br>
	&copy; Copyright 2001 HarrisData. All rights reserved.
	<br><br>
	IBM is a registered trademark of IBM Corporation.<br>
	All <a href="http://www.harrisdata.com/Trademark.htm" target="_blank">trademarks</a> on this site are properties of their respective owners.
	<br><br>
	<br><br>
    </div>
  </td></tr></table>
		                 </td>
                          </tr>
                      </table>
                  </td>
              </tr>
	         </table>
      </body>
  </html>