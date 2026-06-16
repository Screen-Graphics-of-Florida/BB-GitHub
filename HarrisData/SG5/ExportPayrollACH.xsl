<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/TR/xpath-functions/">
	<xsl:output method="text" encoding="utf-8" media-type="text/plain"/>
	<xsl:strip-space elements="*"/>
	<xsl:template name="Space">
		<xsl:param name="count"/>
		<xsl:if test="$count">
			<xsl:value-of select="' '"/>
			<xsl:call-template name="Space">
				<xsl:with-param name="count" select="$count - 1"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="Filler">
		<xsl:param name="filler_count"/>
		<xsl:if test="$filler_count">
			<xsl:text>9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999</xsl:text>
			<xsl:text>&#xD;&#xA;</xsl:text>
			<xsl:call-template name="Filler">
				<xsl:with-param name="filler_count" select="$filler_count - 1"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="payroll_ach">
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="html_stylesheet">
	</xsl:template>
	<xsl:template match="bank">

		<xsl:variable name="size_file_id" select="1"/>
		<xsl:variable name="size_destination_name" select="23"/>
		<xsl:variable name="size_origin_name" select="23"/>
		<xsl:variable name="size_routing_transit" select="4"/>
		<xsl:variable name="size_routing_aba" select="4"/>
		<xsl:variable name="size_routing_check" select="1"/>
		<xsl:text>1</xsl:text>
		<xsl:text>01</xsl:text>
		<xsl:text> </xsl:text>
		<xsl:value-of select="substring(bank_routing_transit,1,$size_routing_transit)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_transit - string-length(substring(bank_routing_transit,1,$size_routing_transit))"/></xsl:call-template>
		<xsl:value-of select="substring(bank_routing_aba,1,$size_routing_aba)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_aba - string-length(substring(bank_routing_aba,1,$size_routing_aba))"/></xsl:call-template>
		<xsl:value-of select="substring(bank_routing_check,1,$size_routing_check)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_check - string-length(substring(bank_routing_check,1,$size_routing_check))"/></xsl:call-template>
		<xsl:value-of select="substring(file_modifier,1,$size_file_id)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_file_id - string-length(substring(file_modifier,1,$size_file_id))"/></xsl:call-template>
		<xsl:choose>
			<xsl:when test="immed_origin_value='I'"> 
				<xsl:value-of select="substring(bank_routing_transit,1,$size_routing_transit)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_transit - string-length(substring(bank_routing_transit,1,$size_routing_transit))"/></xsl:call-template>
				<xsl:value-of select="substring(bank_routing_aba,1,$size_routing_aba)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_aba - string-length(substring(bank_routing_aba,1,$size_routing_aba))"/></xsl:call-template>
				<xsl:value-of select="substring(bank_routing_check,1,$size_routing_check)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_check - string-length(substring(bank_routing_check,1,$size_routing_check))"/></xsl:call-template>
			</xsl:when>
			<xsl:otherwise>  
				<xsl:value-of select="format-number(fed_ein,'000000000')"/>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:value-of select="substring(process_date,3,2)"/>
		<xsl:value-of select="substring(process_date,6,2)"/>
		<xsl:value-of select="substring(process_date,9,2)"/>
		<xsl:value-of select="substring(process_time,1,2)"/>
		<xsl:value-of select="substring(process_time,4,2)"/>
		<xsl:value-of select="substring(file_modifier_override,1,$size_file_id)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_file_id - string-length(substring(file_modifier_override,1,$size_file_id))"/></xsl:call-template>
		<xsl:text>094</xsl:text>
		<xsl:text>10</xsl:text>
		<xsl:text>1</xsl:text>
		<xsl:value-of select="substring(bank_name,1,$size_destination_name)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_destination_name - string-length(substring(bank_name,1,$size_destination_name))"/></xsl:call-template>
		<xsl:choose>
			<xsl:when test="employer_name_option='1'"> 
				<xsl:value-of select="substring(tax_default_employer_name,1,$size_origin_name)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_origin_name - string-length(substring(tax_default_employer_name,1,$size_origin_name))"/></xsl:call-template>
			</xsl:when>
			<xsl:otherwise>  
				<xsl:value-of select="substring(cofac_name_upper,1,$size_origin_name)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_origin_name - string-length(substring(cofac_name_upper,1,$size_origin_name))"/></xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:text>00000001</xsl:text>
		<xsl:text>&#xD;&#xA;</xsl:text>
		
		<xsl:for-each select="batch">
			<xsl:variable name="size_company_name" select="36"/>
			<xsl:text>5</xsl:text>
			<xsl:text>200</xsl:text>
			<xsl:choose>
				<xsl:when test="parent::*/employer_name_option='1'"> 
					<xsl:value-of select="substring(tax_default_employer_name,1,$size_company_name)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_company_name - string-length(substring(tax_default_employer_name,1,$size_company_name))"/></xsl:call-template>
				</xsl:when>
				<xsl:otherwise>  
					<xsl:value-of select="substring(cofac_name_upper,1,$size_company_name)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_company_name - string-length(substring(cofac_name_upper,1,$size_company_name))"/></xsl:call-template>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:text>1</xsl:text>
			<xsl:value-of select="format-number(fed_ein,'000000000')"/>
			<xsl:text>PPD</xsl:text>
			<xsl:text>PAYROLL   </xsl:text>
			<xsl:text>      </xsl:text>
			<xsl:value-of select="substring(check_date,3,2)"/>
			<xsl:value-of select="substring(check_date,6,2)"/>
			<xsl:value-of select="substring(check_date,9,2)"/>
			<xsl:text>   </xsl:text>
			<xsl:text>1</xsl:text>
			<xsl:value-of select="substring(parent::*/bank_routing_transit,1,$size_routing_transit)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_transit - string-length(substring(parent::*/bank_routing_transit,1,$size_routing_transit))"/></xsl:call-template>
			<xsl:value-of select="substring(parent::*/bank_routing_aba,1,$size_routing_aba)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_aba - string-length(substring(parent::*/bank_routing_aba,1,$size_routing_aba))"/></xsl:call-template>
			<xsl:value-of select="format-number(batch_number,'0000000')"/>
			<xsl:text>&#xD;&#xA;</xsl:text>
			
			<xsl:for-each select="payee">
				<xsl:for-each select="payee_detail">
					<xsl:apply-templates select="payee_detail"/>
					<xsl:variable name="size_employee_dfi_account" select="17"/>
					<xsl:variable name="size_employee_id" select="15"/>
					<xsl:variable name="size_employee_name" select="22"/>
					<xsl:text>6</xsl:text>
					<xsl:choose>
						<xsl:when test="payee_prenote='Y' and payee_account_type='S'">  <xsl:text>33</xsl:text></xsl:when>
						<xsl:when test="payee_prenote='Y'">                             <xsl:text>23</xsl:text></xsl:when>
						<xsl:when test="payee_account_type='S' and payee_amount &lt; 0"><xsl:text>37</xsl:text></xsl:when>
						<xsl:when test="payee_account_type='S'">                        <xsl:text>32</xsl:text></xsl:when>
						<xsl:when test="payee_amount &lt; 0">                           <xsl:text>27</xsl:text></xsl:when>
						<xsl:otherwise>                                                 <xsl:text>22</xsl:text></xsl:otherwise>
					</xsl:choose>
					<xsl:value-of select="substring(payee_routing_transit,1,$size_routing_transit)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_transit - string-length(substring(payee_routing_transit,1,$size_routing_transit))"/></xsl:call-template>
					<xsl:value-of select="substring(payee_routing_aba,1,$size_routing_aba)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_aba - string-length(substring(payee_routing_aba,1,$size_routing_aba))"/></xsl:call-template>
					<xsl:value-of select="substring(payee_routing_check,1,$size_routing_check)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_check - string-length(substring(payee_routing_check,1,$size_routing_check))"/></xsl:call-template>
					<xsl:value-of select="substring(payee_account,1,$size_employee_dfi_account)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_employee_dfi_account - string-length(substring(payee_account,1,$size_employee_dfi_account))"/></xsl:call-template>
					<xsl:choose>
						<xsl:when test="payee_prenote='Y'">  <xsl:text>0000000000</xsl:text></xsl:when>
						<xsl:when test="payee_amount &lt; 0"><xsl:value-of select="format-number(payee_amount*-100,'0000000000')"/></xsl:when>
						<xsl:otherwise>                      <xsl:value-of select="format-number(payee_amount*100,'0000000000')"/></xsl:otherwise>
					</xsl:choose>
					<xsl:value-of select="substring(payee_number,1,$size_employee_id)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_employee_id - string-length(substring(payee_number,1,$size_employee_id))"/></xsl:call-template>
					<xsl:value-of select="substring(payee_name,1,$size_employee_name)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_employee_name - string-length(substring(payee_name,1,$size_employee_name))"/></xsl:call-template>
					<xsl:text>  </xsl:text>
					<xsl:text>0</xsl:text>
					<xsl:value-of select="substring(parent::*/parent::*/parent::*/bank_routing_transit,1,$size_routing_transit)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_transit - string-length(substring(parent::*/parent::*/parent::*/bank_routing_transit,1,$size_routing_transit))"/></xsl:call-template>
					<xsl:value-of select="substring(parent::*/parent::*/parent::*/bank_routing_aba,1,$size_routing_aba)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_aba - string-length(substring(parent::*/parent::*/parent::*/bank_routing_aba,1,$size_routing_aba))"/></xsl:call-template>
 				        <xsl:value-of select="format-number(seq,'0000000')" />
					<xsl:text>&#xD;&#xA;</xsl:text>
				</xsl:for-each>
				
				<xsl:if test="parent::*/parent::*/ach_method='D'">
					<xsl:variable name="size_employee_dfi_account" select="17"/>
					<xsl:variable name="size_cofac_id" select="9"/>
					<xsl:variable name="size_employee_name" select="22"/>
					<xsl:text>6</xsl:text>
					<xsl:choose>
						<xsl:when test="parent::*/parent::*/prenote_needed='Y'">  <xsl:text>28</xsl:text></xsl:when>
						<xsl:otherwise>                                           <xsl:text>27</xsl:text></xsl:otherwise>
					</xsl:choose>
					<xsl:value-of select="substring(parent::*/parent::*/bank_routing_transit,1,$size_routing_transit)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_transit - string-length(substring(parent::*/parent::*/bank_routing_transit,1,$size_routing_transit))"/></xsl:call-template>
					<xsl:value-of select="substring(parent::*/parent::*/bank_routing_aba,1,$size_routing_aba)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_aba - string-length(substring(parent::*/parent::*/bank_routing_aba,1,$size_routing_aba))"/></xsl:call-template>
					<xsl:value-of select="substring(parent::*/parent::*/bank_routing_check,1,$size_routing_check)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_check - string-length(substring(parent::*/parent::*/bank_routing_check,1,$size_routing_check))"/></xsl:call-template>
					<xsl:value-of select="substring(parent::*/employer_bank_account,1,$size_employee_dfi_account)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_employee_dfi_account - string-length(substring(parent::*/employer_bank_account,1,$size_employee_dfi_account))"/></xsl:call-template>
					<xsl:choose>
						<xsl:when test="parent::*/parent::*/prenote_needed='Y'"><xsl:text>0000000000</xsl:text></xsl:when>
						<xsl:otherwise>                                         <xsl:value-of select="format-number(sum(payee_detail[payee_prenote='N']/payee_amount)*100, '0000000000')"/></xsl:otherwise>
					</xsl:choose>
					<xsl:value-of select="format-number(parent::*/company,'00')"/>
					<xsl:value-of select="format-number(parent::*/facility,'0000')"/>
					<xsl:call-template name="Space"><xsl:with-param name="count" select="$size_cofac_id"/></xsl:call-template>
					<xsl:value-of select="substring(parent::*/cofac_name_upper,1,$size_employee_name)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_employee_name - string-length(substring(parent::*/cofac_name_upper,1,$size_employee_name))"/></xsl:call-template>
					<xsl:text>  </xsl:text>
					<xsl:text>0</xsl:text>
					<xsl:value-of select="substring(parent::*/parent::*/bank_routing_transit,1,$size_routing_transit)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_transit - string-length(substring(parent::*/parent::*/bank_routing_transit,1,$size_routing_transit))"/></xsl:call-template>
					<xsl:value-of select="substring(parent::*/parent::*/bank_routing_aba,1,$size_routing_aba)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_aba - string-length(substring(parent::*/parent::*/bank_routing_aba,1,$size_routing_aba))"/></xsl:call-template>
					<xsl:value-of select="format-number((payee_detail[last()]/seq) + 1, '0000000')"/>
					<xsl:text>&#xD;&#xA;</xsl:text>
				</xsl:if>
				
				<xsl:variable name="size_opt_1" select="19"/>
				<xsl:variable name="size_opt_2" select="6"/>
				<xsl:text>8</xsl:text>
				<xsl:text>200</xsl:text>
				<xsl:choose>
					<xsl:when test="parent::*/parent::*/ach_method='D'"> 
	  					<xsl:value-of select="format-number(count(payee_detail/payee_amount)+1, '000000')"/>
					</xsl:when>
					<xsl:otherwise>  
						<xsl:value-of select="format-number(count(payee_detail/payee_amount), '000000')"/>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:choose>
					<xsl:when test="parent::*/parent::*/ach_method='D'"> 
	  					<xsl:value-of select="substring(format-number(sum(payee_detail/payee_routing_hash) + parent::*/parent::*/bank_routing_hash, '00000000000000000000'),11,10)"/>
					</xsl:when>
					<xsl:otherwise>  
						<xsl:value-of select="substring(format-number(sum(payee_detail/payee_routing_hash), '00000000000000000000'),11,10)"/>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:choose>
					<xsl:when test="parent::*/parent::*/ach_method='D'"> 
	  					<xsl:value-of select="format-number(sum(payee_detail[payee_prenote='N']/payee_amount)*100, '000000000000')"/>
					</xsl:when>
					<xsl:otherwise>  
						<xsl:text>000000000000</xsl:text>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:value-of select="format-number(sum(payee_detail[payee_prenote='N']/payee_amount)*100, '000000000000')"/>
				<xsl:text>1</xsl:text>
				<xsl:value-of select="format-number(parent::*/fed_ein,'000000000')"/>
				<xsl:call-template name="Space"><xsl:with-param name="count" select="$size_opt_1"/></xsl:call-template>
				<xsl:call-template name="Space"><xsl:with-param name="count" select="$size_opt_2"/></xsl:call-template>
				<xsl:value-of select="substring(parent::*/parent::*/bank_routing_transit,1,$size_routing_transit)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_transit - string-length(substring(parent::*/parent::*/bank_routing_transit,1,$size_routing_transit))"/></xsl:call-template>
				<xsl:value-of select="substring(parent::*/parent::*/bank_routing_aba,1,$size_routing_aba)"/><xsl:call-template name="Space"><xsl:with-param name="count" select="$size_routing_aba - string-length(substring(parent::*/parent::*/bank_routing_aba,1,$size_routing_aba))"/></xsl:call-template>
				<xsl:value-of select="format-number(parent::*/batch_number,'0000000')"/>
				<xsl:text>&#xD;&#xA;</xsl:text>
			</xsl:for-each>
		</xsl:for-each>
	
		<xsl:variable name="filler_rows" select="10 - ((count(//bank) + count(//batch) + count(//payee_amount) + count(//batch) +1) mod 10)"/>
		<xsl:variable name="filler_rowsD" select="10 - ((count(//bank) + count(//batch) + count(//payee_amount) + count(//batch) + count(//batch) +1) mod 10)"/>
		<xsl:variable name="size_opt_3" select="39"/>
		<xsl:text>9</xsl:text>
		<xsl:value-of select="format-number(count(//batch), '000000')"/>
		<xsl:value-of select="format-number((count(//bank) + count(//batch) + count(//payee_amount) + count(//batch) + 1 + $filler_rows) div 10 , '000000')"/>
		<xsl:choose>
			<xsl:when test="ach_method='D'"> 
				<xsl:value-of select="format-number(count(//payee_detail/payee_amount)+ count(//batch), '00000000')"/>
			</xsl:when>
			<xsl:otherwise>  
				<xsl:value-of select="format-number(count(//payee_detail/payee_amount), '00000000')"/>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:choose>
			<xsl:when test="ach_method='D'"> 
	  			<xsl:value-of select="substring(format-number(sum(//payee_routing_hash) + (sum(//bank_routing_hash) * count(//batch)), '00000000000000000000'),11,10)"/>
			</xsl:when>
			<xsl:otherwise>  
				<xsl:value-of select="substring(format-number(sum(//payee_routing_hash), '00000000000000000000'),11,10)"/>
			</xsl:otherwise>
		</xsl:choose>		
		<xsl:choose>
			<xsl:when test="ach_method='D'"> 
				<xsl:value-of select="format-number(sum(//payee_detail[payee_prenote='N']/payee_amount)*100, '000000000000')"/>
			</xsl:when>
			<xsl:otherwise>  
				<xsl:text>000000000000</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:value-of select="format-number(sum(//payee_detail[payee_prenote='N']/payee_amount)*100, '000000000000')"/>
		<xsl:call-template name="Space"><xsl:with-param name="count" select="$size_opt_3"/></xsl:call-template>
		<xsl:text>&#xD;&#xA;</xsl:text>

		<xsl:choose>
			<xsl:when test="ach_method='D'"> 
				<xsl:if test="output_filler='Y' and $filler_rowsD &gt; 0 ">
					<xsl:call-template name="Filler"><xsl:with-param name="filler_count" select="$filler_rowsD"/></xsl:call-template>
				</xsl:if>		
			</xsl:when>
			<xsl:otherwise>  
				<xsl:if test="output_filler='Y' and $filler_rows &gt; 0 ">
					<xsl:call-template name="Filler"><xsl:with-param name="filler_count" select="$filler_rows"/></xsl:call-template>
				</xsl:if>		
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
