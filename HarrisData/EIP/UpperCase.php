  function chkUpper(fld){
    if(/[^0-9A-Z]/.test(fld.value)){
        fld.value=fld.value.toUpperCase().replace(/([^0-9A-Z])/g,"");
    }
  }