%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Multiple Choice Selection List Include                      *
*********************************************************************
%}
  function moveSelOver() {
      var boxLength = document.Chg.choiceBox.length;
      var selectedItem = document.Chg.available.selectedIndex;
      var selectedText = document.Chg.available.options[selectedItem].text;
      var selectedValue = document.Chg.available.options[selectedItem].value;
      var i;
      var isNew = true;
      if (boxLength != 0) {
          for (i = 0; i < boxLength; i++) {
              thisitem = document.Chg.choiceBox.options[i].text;
              if (thisitem == selectedText) {
                  isNew = false;
                  break;
              }
          }
      }
      if (isNew) {
          newoption = new Option(selectedText, selectedValue, false, false);
          document.Chg.choiceBox.options[boxLength] = newoption;
      }
      document.Chg.available.selectedIndex=-1;
  }

  function removeSel(box) {
      for(var i=0; i<box.options.length; i++) {
          if(box.options[i].selected && box.options[i] != "") {
              box.options[i].value = "";
              box.options[i].text = "";
          }
      }
      bumpUp(box);
      }
  function bumpUp(abox) {
      for(var i = 0; i < abox.options.length; i++) {
          if(abox.options[i].value == "")  {
              for(var j = i; j < abox.options.length - 1; j++)  {
                 abox.options[j].value = abox.options[j + 1].value;
                 abox.options[j].text = abox.options[j + 1].text;
              }
              var ln = i;
              break;
          }
      }
      if(ln < abox.options.length)  {
          abox.options.length -= 1;
          bumpUp(abox);
      }
  }
  function moveSelUp(dbox) {
      for(var i = 0; i < dbox.options.length; i++) {
          if (dbox.options[i].selected && dbox.options[i] != "" && dbox.options[i] != dbox.options[0]) {
              var tmpval = dbox.options[i].value;
              var tmpval2 = dbox.options[i].text;
              dbox.options[i].value = dbox.options[i - 1].value;
              dbox.options[i].text = dbox.options[i - 1].text;
              dbox.options[i-1].value = tmpval;
              dbox.options[i-1].text = tmpval2;
              dbox.options[i].selected = false;
              dbox.options[i-1].selected = true;
          }
      }
  }
  function moveSelDown(ebox) {
      for(var i = 0; i < ebox.options.length; i++) {
          if (ebox.options[i].selected && ebox.options[i] != "" && ebox.options[i+1] != ebox.options[ebox.options.length]) {
              var tmpval = ebox.options[i].value;
              var tmpval2 = ebox.options[i].text;
              ebox.options[i].value = ebox.options[i+1].value;
              ebox.options[i].text = ebox.options[i+1].text;
              ebox.options[i+1].value = tmpval;
              ebox.options[i+1].text = tmpval2;
              ebox.options[i].selected = false;
              ebox.options[i+1].selected = true;
              var i = ebox.options.length;

         }
      }
  }
