  var  avlText = new Array();
  var  avlCode = new Array();
  avlText[0] = "Warehouse";
  avlCode[0] = "wh";
  avlText[1] = "Item Number";
  avlCode[1] = "it";
  avlText[2] = "Vendor Number";
  avlCode[2] = "vn";
    
  function loadAvailable(selectName) {
           for (x = 0; x < avlCode.length; x++) {
          newoption = new Option(avlText[x],avlCode[x],false,false);
          selectName.options[x] = newoption;
      }
  }
  function loadOption(x,selectName,i) {
         if (i > selectName.length) {
          for (j = 0; j < i; j++) {
              if (selectName.options[j] == null) {
                  newoption = new Option(" "," ",false,false);
                  selectName.options[j] = newoption;
              }
          }
      }
      newoption = new Option(avlText[x],avlCode[x],false,false);
      selectName.options[i] = newoption;
  }
  function saveSel(selectName,fieldName) {
      var boxLength = selectName.length;
      if (boxLength != 0) {
          for (i = 0; i < boxLength; i++) {
              if (i == 0) {
                  fieldName.value =  "@@" + selectName.options[i].value + "sq" + (i+1);
              }
              else {
                  fieldName.value =  fieldName.value + "}{" + "@@" + selectName.options[i].value + "sq" + (i+1);
              }
          }
      }
      return true;
  }