<?php

require_once __DIR__ . "/init.php";

spl_autoload_register(function ($class) {
  $file = root . "/" . str_replace("\\", "/", $class) . ".php";
  if (file_exists($file)) {
    print_r($file);
    require_once $file;
  }
});
