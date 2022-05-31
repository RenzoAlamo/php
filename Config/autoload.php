<?php

require_once __DIR__ . "/init.php";

spl_autoload_register(function ($class) {
  print_r($class);
  $file = root . "/" . str_replace("\\", "/", $class) . ".php";
  if (file_exists($file)) {
    require_once $file;
  }
});
