%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Error Message Handler                                       *
*********************************************************************
%}

%MESSAGE {
    SQLSTATE : 02000 : { %} : Continue
    +default : { %} : Continue
    -default : { @ErrorMsg() %} : Exit
  %}

%{-----------------------------------------------------------------%}
%{ Display No Row Found Message                                    %}
%{-----------------------------------------------------------------%}
%macro_function NoRecord () {
    <table  bgcolor="black" border="0">
<br><br>	
        <tr bgcolor="#0066CC">
            <td colspan="3">
                <span STYLE="color: white">No Data Found For Selection Criteria</span>
            </td>
        </tr>
	</table>
%}
		
%macro_function MakeErrorMsgTR (rowCaption, rowLabel, rowValue)
 {
    <tr bgcolor="ivory">
        <td>$(rowCaption)</td>
        <td>$(rowLabel)</td>
        <td>$(rowValue)</td>
    </tr>
%}

%{-----------------------------------------------------------------%}
%{ Make an environment variable table row                          %}
%{-----------------------------------------------------------------%}
%macro_function MakeEnvVarTR (rowCaption, rowValue)
 {
    <tr bgcolor="ivory">
        <td colspan="2">$(rowCaption)</td>
        <td>$(rowValue)</td>
    </tr>
%}

%{-----------------------------------------------------------------%}
%{ Output an HTML message page                                     %}
%{-----------------------------------------------------------------%}
%macro_function ErrorMsg () {
	<br>
	<br>
	
    <!--------------------------------------------------------------
        Begin table for message display
    --------------------------------------------------------------->	
    <table  bgcolor="navy" border="0">
	
        <tr bgcolor="silver">
            <td colspan="3">
                <span STYLE="color: navy">Error when processing Net.Data macro $(DTW_MACRO_FILENAME)</span>
            </td>
        </tr>
		
        <tr bgcolor="mintcream" align="center">
            <td><b>Description</b></td>
            <td><b>Net.Data Variable</b></td>				
            <td><b>Value</b></td>
        </tr>

        <!-------------------------------------------------------------
            Display Net.Data built-in variables
        -------------------------------------------------------------->		
        @MakeErrorMsgTR("Return Code",
                        "RETURN_CODE",
                        $(RETURN_CODE))

        @MakeErrorMsgTR("SQL State",
                        "SQL_STATE",
                        $(SQL_STATE))
						
        @MakeErrorMsgTR("Default message",
                        "DTW_DEFAULT_MESSAGE",
                        $(DTW_DEFAULT_MESSAGE))

        @MakeErrorMsgTR("Current filename",
                        "DTW_CURRENT_FILENAME",
                        $(DTW_CURRENT_FILENAME))

        @MakeErrorMsgTR("Current last modified",
                        "DTW_CURRENT_LAST_MODIFIED",
                        $(DTW_CURRENT_LAST_MODIFIED))

        @MakeErrorMsgTR("Macro filename",
                        "DTW_MACRO_FILENAME",
                        $(DTW_MACRO_FILENAME))

        @MakeErrorMsgTR("Macro last modified",
                        "DTW_MACRO_LAST_MODIFIED",
                        $(DTW_MACRO_LAST_MODIFIED))

        @MakeErrorMsgTR("Macro processor path",
                        "DTW_MP_PATH",
                        $(DTW_MP_PATH))

        @MakeErrorMsgTR("Macro processor version",
                        "DTW_MP_VERSION",
                        $(DTW_MP_VERSION))

        <!-------------------------------------------------------------
            Display HTTP server environment variables
        -------------------------------------------------------------->		
        <tr bgcolor="mintcream" align="left">
            <td colspan="3"><b>Environment Variables</b></td>
        </tr>

        @MakeEnvVarTR("AUTH_TYPE",
                      @dtw_rgetenv("AUTH_TYPE"))						

        @MakeEnvVarTR("CGI_ASCII_CCSID",
                      @dtw_rgetenv("CGI_ASCII_CCSID"))						

        @MakeEnvVarTR("CGI_MODE",
                      @dtw_rgetenv("CGI_MODE"))						

        @MakeEnvVarTR("CGI_EBCDIC_CCSID",
                      @dtw_rgetenv("CGI_EBCDIC_CCSID"))						

        @MakeEnvVarTR("CONTENT_LENGTH",
                      @dtw_rgetenv("CONTENT_LENGTH"))						

        @MakeEnvVarTR("CONTENT_TYPE",
                      @dtw_rgetenv("CONTENT_TYPE"))						

        @MakeEnvVarTR("GATEWAY_INTERFACE",
                      @dtw_rgetenv("GATEWAY_INTERFACE"))						

        @MakeEnvVarTR("HTTP_ACCEPT",
                      @dtw_rgetenv("HTTP_ACCEPT"))						

        @MakeEnvVarTR("HTTP_USER_AGENT",
                      @dtw_rgetenv("HTTP_USER_AGENT"))						

        @MakeEnvVarTR("IBM_CCSID_VALUE",
                      @dtw_rgetenv("IBM_CCSID_VALUE"))						

        @MakeEnvVarTR("PATH_TRANSLATED",
                      @dtw_rgetenv("PATH_TRANSLATED"))						

        @MakeEnvVarTR("PATH_INFO",
                      @dtw_rgetenv("PATH_INFO"))						

        @MakeEnvVarTR("QUERY_STRING",
                      @dtw_rgetenv("QUERY_STRING"))						

        @MakeEnvVarTR("REMOTE_ADDR",
                      @dtw_rgetenv("REMOTE_ADDR"))						

        @MakeEnvVarTR("REMOTE_HOST",
                      @dtw_rgetenv("REMOTE_HOST"))						

        @MakeEnvVarTR("REMOTE_IDENT",
                      @dtw_rgetenv("REMOTE_IDENT"))						

        @MakeEnvVarTR("REMOTE_USER",
                      @dtw_rgetenv("REMOTE_USER"))						

        @MakeEnvVarTR("REQUEST_METHOD",
                      @dtw_rgetenv("REQUEST_METHOD"))						

        @MakeEnvVarTR("SCRIPT_NAME",
                      @dtw_rgetenv("SCRIPT_NAME"))						

        @MakeEnvVarTR("SERVER_NAME",
                      @dtw_rgetenv("SERVER_NAME"))						

        @MakeEnvVarTR("SERVER_PROTOCOL",
                      @dtw_rgetenv("SERVER_PROTOCOL"))						

        @MakeEnvVarTR("SERVER_SOFTWARE",
                      @dtw_rgetenv("SERVER_SOFTWARE"))						

        <!-------------------------------------------------------------
            Display SSL environment variables
        -------------------------------------------------------------->		
        <tr bgcolor="mintcream" align="left">
            <td colspan="3"><b>SSL Environment Variables</b></td>
        </tr>

        @MakeEnvVarTR("HTTPS",
                      @dtw_rgetenv("HTTPS"))

        @MakeEnvVarTR("HTTPS_CLIENT_CERT",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT"))						

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_COUNTRY",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_COUNTRY"))						

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_COMMON_NAME",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_COMMON_NAME"))						

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_ISSUER_COMMON_NAME",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_ISSUER_COMMON_NAME"))

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_ISSUER_COUNTRY",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_ISSUER_COUNTRY"))

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_ISSUER_LOCALITY",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_ISSUER_LOCALITY"))

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_ISSUER_STATE_OR_PROVINCE",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_ISSUER_STATE_OR_PROVINCE"))

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_ISSUER_ORG_UNIT",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_ISSUER_ORG_UNIT"))

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_ISSUER_ORGANIZATION",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_ISSUER_ORGANIZATION"))

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_LEN",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_LEN"))

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_LOCALITY",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_LOCALITY"))

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_SERIAL_NUM",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_SERIAL_NUM"))

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_ORG_UNIT",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_ORG_UNIT"))

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_ORGANIZATION",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_ORGANIZATION"))

        @MakeEnvVarTR("HTTPS_CLIENT_CERT_STATE_OR_PROVINCE",
                      @dtw_rgetenv("HTTPS_CLIENT_CERT_STATE_OR_PROVINCE"))

        @MakeEnvVarTR("HTTPS_KEYSIZE",
                      @dtw_rgetenv("HTTPS_KEYSIZE"))

        @MakeEnvVarTR("HTTPS_PORT",
                      @dtw_rgetenv("HTTPS_PORT"))

	</table>
%}
