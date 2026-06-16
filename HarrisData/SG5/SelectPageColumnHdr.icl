%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Customer Maintenance Stored Procedures                       *
**********************************************************************
%}

%{ Column Header formats for "Select" pages. %}

%if (sortVar == "sort")
   <th class="colhdr$(sortVar)" $(helpCursor)><span title="$(orderByDisplay)">$(columnHdr)</span></th>
%else
   <th class="colhdr">$(columnHdr)</th>
%endif
