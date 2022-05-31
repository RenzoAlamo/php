<?php

global $_PUT, $_DELETE, $_PARAMS, $_STYLES, $_SCRIPTS, $_MULTIMEDIA;
$_PUT = [];
$_DELETE = [];
$_PARAMS = [];
$_STYLES = [];
$_SCRIPTS = [];
$_MULTIMEDIA = [];

define("production", false);
define("base_folder", "/php/VercelRouter");
define("root", $_SERVER["DOCUMENT_ROOT"] . base_folder);

if (production) {
  ini_set("display_errors", "Off");
  error_reporting(E_ALL);
} else {
  ini_set("display_errors", "On");
  error_reporting(0);
}
