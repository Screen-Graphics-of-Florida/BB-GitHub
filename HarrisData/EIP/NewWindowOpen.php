function NewWindow(winURL, winName, pctHeight, pctWidth, scroll, resize, tool, menu, status) {
      w = window.screen.availWidth * pctWidth / 100;
      h = window.screen.availHeight * pctHeight / 100;
      var winl = (screen.width - w) / 3;
      var wint = (screen.height - h) / 4;
      winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable='+resize+',toolbar='+tool+',menubar='+menu+',status='+status+''
      win = window.open(winURL, winName, winprops)
      <!--if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }-->
  }