%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                    *
*  Job: View Radio Button (Included in WildCardPage.icl)            *
*********************************************************************
%}
  &nbsp;

  %if (viewRadioButton=="FeatureFamily")
      View:
          <input type="radio" name="dspFeatFamily" $(featureChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/WILDCARD$(d2wVarBase)$(orderByVarBase)&amp;wildCardSearch=$(advSrchVar)&amp;chgFeatFamily=I'">Featured Item
          <input type="radio" name="dspFeatFamily" $(familyChecked)  onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/WILDCARD$(d2wVarBase)$(orderByVarBase)&amp;wildCardSearch=$(advSrchVar)&amp;chgFeatFamily=F'">$(groupTypeDesc)
      %if (d2wName=="FeaturedItem.d2w" && useVendorCatalog=="Y")
          <input type="radio" name="dspFeatFamily" $(catalogChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/WILDCARD$(d2wVarBase)$(orderByVarBase)&amp;wildCardSearch=$(advSrchVar)&amp;chgFeatFamily=C'">Vendor Item
      %endif
  %elif (viewRadioButton=="RuleType")
      View:
          <input type="radio" name="dspRuleType" $(constraintChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgConstraint=Y'">Constraint
          <input type="radio" name="dspRuleType" $(parameterChecked)  onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgParameter=Y'">Parameter
  %elif (viewRadioButton=="FeatureGroup")
      View:
          <input type="radio" name="selRec" $(selChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/LOAD$(d2wVarBase)&amp;chgSelRec=S'">Selected
          <input type="radio" name="selRec" $(availChecked)  onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/LOAD$(d2wVarBase)&amp;chgSelRec=A'">Available
          <input type="radio" name="selRec" $(allChecked)  onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/LOAD$(d2wVarBase)&amp;chgSelRec=L'">All
  %endif