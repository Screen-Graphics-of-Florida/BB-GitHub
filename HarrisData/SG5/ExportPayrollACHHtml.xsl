<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions">
	<xsl:output method="html" version="4.0" encoding="iso-8859-1" indent="yes"/>
	
	<xsl:template match="payroll_ach">
		<html>
		<head>
		<xsl:variable name="style_sheet"><xsl:value-of select="//html_stylesheet"/></xsl:variable>
		<link rel="stylesheet" type="text/css" href="{$style_sheet}"></link>
		</head>
		<body>
	    <h2>ACH Transmission</h2>
        <br />
	    
		<xsl:apply-templates/>
		</body>
		</html>
	</xsl:template>
	
	<xsl:template match="html_stylesheet">
	</xsl:template>
	
	<xsl:template match="bank">
		<table class="contenttable" border="0" cellpadding="0" cellspacing="0" summary="contenttable">
		<tr><td class="dsphdr">File</td>
			<td class="dspalph"><xsl:value-of select="ach_transmission_file"/></td>
		</tr>
		<tr><td class="dsphdr">Bank</td>
			<td class="dspnmbr"><xsl:value-of select="bank_number"/></td>
			<td class="dspalph"><xsl:value-of select="bank_name"/></td>
		</tr>
		<tr><td class="dsphdr">Routing Number</td>
			<td class="dspalph"><xsl:value-of select="bank_routing_transit"/><xsl:value-of select="bank_routing_aba"/><xsl:value-of select="bank_routing_check"/></td>
		</tr>

		</table>
		
		<xsl:for-each select="batch">
		    <p></p>
			<table class="contenttable" border="0" cellpadding="0" cellspacing="0" summary="contenttable">
			<tr><td class="dsphdr">Batch Number</td>
				<td class="dspnmbr"><xsl:value-of select="batch_number"/></td>
			</tr>
			<tr><td class="dsphdr">Account Number</td>
				<td class="dspalph"><xsl:value-of select="employer_bank_account"/></td>
			</tr>
			<tr><td class="dsphdr">Federal EIN</td>
				<td class="dspnmbr"><xsl:value-of select="fed_ein"/></td>
			</tr>
			<tr><td class="dsphdr">Check Date</td>
				<td class="dspdate"><xsl:value-of select="check_date"/></td>
			</tr>
			<tr><td class="dsphdr">Period Date</td>
				<td class="dspdate"><xsl:value-of select="pay_period_end_date"/></td>
			</tr>
			</table>
			
			<xsl:for-each select="payee">
			    <p></p>
				<table class="contenttable" border="0" cellpadding="0" cellspacing="0" summary="contenttable">
					<tr>
						<th class="colhdr">Employee</th>
						<th class="colhdr">Employee Name</th>
						<th class="colhdr">Deposit Amount</th>
						<th class="colhdr">Account Type</th>
						<th class="colhdr">Account Number</th>
						<th class="colhdr">Pre-Note</th>
						<th class="colhdr">Trace Number</th>
					</tr>
			
				<xsl:for-each select="payee_detail">
					<tr>
					<td class="colnmbr"><xsl:value-of select="payee_number"/></td>
					<td class="colalph"><xsl:value-of select="payee_name"/></td>
					<td class="colnmbr">
					<xsl:choose>
						<xsl:when test="payee_prenote='Y'"> </xsl:when>
						<xsl:otherwise>                     <xsl:value-of select="format-number(payee_amount,'.00')"/></xsl:otherwise>
					</xsl:choose>
					</td>
					<td class="colcode"><xsl:value-of select="payee_account_type"/></td>
					<td class="colalph"><xsl:value-of select="payee_account"/></td>
					<td class="colcode"><xsl:value-of select="payee_prenote"/></td>
 				        <td class="colnmbr"><xsl:value-of select="seq"/></td>
					</tr>
				</xsl:for-each>
				
				<tr>
				<td class="colalph"></td>
				<td class="colalph">Batch Total</td>
				<td class="colnmbr"><xsl:value-of select="format-number(sum(payee_detail[payee_prenote='N']/payee_amount),'.00')"/></td>
				</tr>
				</table>
			</xsl:for-each>
		</xsl:for-each>

	    <p></p>
		<table class="contenttable" border="0" cellpadding="0" cellspacing="0" summary="contenttable">
			<tr>
			    <th class="colhdr"> </th>
				<th class="colhdr">Dollars</th>
				<th class="colhdr">Transactions</th>
			</tr>
		<tr><td class="dsphdr">Total Pre-Notes</td>
			<td class="colalph"></td>
			<td class="colnmbr"><xsl:value-of select="count(//payee_detail[payee_prenote='Y']/payee_amount)"/></td>
		</tr>
		<tr><td class="dsphdr">Total Direct Deposits</td>
			<td class="colnmbr"><xsl:value-of select="format-number(sum(//payee_detail[payee_prenote='N']/payee_amount),'.00')"/></td>
			<td class="colnmbr"><xsl:value-of select="count(//payee_detail[payee_prenote='N']/payee_amount)"/></td>
		</tr>
		<tr><td class="dsphdr">Final Totals</td>
			<td class="colnmbr"><xsl:value-of select="format-number(sum(//payee_detail/payee_amount),'.00')"/></td>
			<td class="colnmbr"><xsl:value-of select="count(//payee_detail/payee_amount)"/></td>
		</tr>
		</table>
	</xsl:template>
</xsl:stylesheet>
