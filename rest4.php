<?php
require 'php/class.xcUtils.php';
try {
  // Auto loads classes files
  function __autoload($class) {
    if(!strpos($class,'_')) {
      if(xcUtils::fileExists('php/class.' . $class . '.php')) {
        require_once 'php/class.' . $class . '.php';
      } else {
        trigger_error('Class not found : php/class.' . $class . '.php');
      }
    }
  }
  // Instantiate the Server
  $server=RestServer::Instance();
  $status=$server->run();
  unset($server);
  exit($status);
} catch (Exception $e) {
  $content='Alert, fatal error on ' . $_SERVER['SERVER_NAME']
           .'Uri: ' . $_SERVER['REQUEST_URI'] . "\n"
           .'File: ' . $e->getFile() . "\n"
           .'Line: ' . $e->getLine() . "\n"
           .'Message: '.$e->getMessage()
           .'Stack: ';
  $stack=$e->getTrace();
  foreach($stack as $key=>$level) {
    $content.="\n".'- '.$key.': File: '.$level['file'].' Line: '.$level['line']
              .' Function: '.$level['function'];
  }
  if(defined('DEBUG_MAIL')&&DEBUG_MAIL) {
    @mail(DEBUG_MAIL,'Fatal error on ' . $_SERVER['SERVER_NAME'],$content);
  }
  header('Date: ' . gmdate('D, d M Y H:i:s') . ' GMT', true, 500);
  echo 'Internal Servor Error, you just discovered a new bug.';
  if(defined('DEBUG_PRINT')&&DEBUG_PRINT) {
    echo $content;
  }
}
