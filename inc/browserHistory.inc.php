<?php
    require_once('database.php');
    connectToDatabase();
    
   /*   Retrieve hook browser history from browserId
    *   $browserID = the id browser generated in hook.js
    */
    function getBrowserHistory($browserId) {
        if(isset($browserId) && !empty($browserId)) {
            if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("SELECT date, browser_id, type, module, event FROM event_browsers WHERE browser_id = ? ORDER BY date DESC"))) {
                return;
            }

            if (!$stmt->bind_param("s", $browserId)) {
                return;
            }
            
            if(!$stmt->execute()) {
                return;
            }

            $outDate = NULL;
            $outBrowserId = NULL;
            $outType = NULL;
            $outModule = NULL;
            $outEvent = NULL;
            $browserHistory = array();

            $stmt->store_result();
            $stmt->bind_result($outDate, $outBrowserId, $outType, $outModule, $outEvent);
            while ($stmt->fetch()) {
                $phpdate = date("d/m/Y H:i:s", strtotime($outDate));
                $browserHistory[] = array('date'=>$phpdate, 'browserId'=>$outBrowserId, 'type'=>$outType, 'module'=>$outModule, 'event'=>$outEvent);
            }

            $stmt->free_result();
            $stmt->close(); 

            return $browserHistory;  
        }
    }

   /*   Retrieve last command oputput from browserId
    *   $browserID = the id browser generated in hook.js
    *   TODO : move into another file (its not an history panel func)
    */
    function getLastBrowserCommandOutput($browserId) {
        if(isset($browserId) && !empty($browserId)) {
            if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("SELECT date, browser_id, event_type, event FROM event_browsers WHERE browser_id = ? AND event_type = 'command result' ORDER BY date DESC LIMIT 1"))) {
                return;
            }

            if (!$stmt->bind_param("s", $browserId)) {
                return;
            }
            
            if(!$stmt->execute()) {
                return;
            }

            $outDate = NULL;
            $outBrowserId = NULL;
            $outEventType = NULL;
            $outEvent = NULL;
            $browserLastEvent = array();

            $stmt->store_result();
            $stmt->bind_result($outDate, $outBrowserId, $outEventType, $outEvent);
            while ($stmt->fetch()) {
                $phpdate = date("d/m/Y H:i:s", strtotime($outDate));
                $browserLastEvent[] = array('date'=>$phpdate, 'browserId'=>$outBrowserId, 'eventType'=>$outEventType, 'event'=>$outEvent);
            }

            $stmt->free_result();
            $stmt->close(); 

            return $browserLastEvent;  
        }
    }
?>

