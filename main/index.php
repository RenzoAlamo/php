<?php

require_once __DIR__ . "/../Config/autoload.php";

use Core\Router;
use Core\Validator\Validate;
use Core\View;

// die(json_encode(["base_folder" => base_folder, "root" => root]));
// Router::staticResources("css", "/index.style", ["BackColor.css", "Error.css"]);
// Router::staticResource("js", "/index.script", "Error.js");
// Router::staticResource("img", "/index.image", "wallet_1.gif");

Router::get("/", function () {
  // print_r("<h1>View INDEX</h1>");
  // return View::render("index");
  return $_SERVER;
});

Router::get("/contact", function () {
  $validate = new Validate();
  $validate->get("age")->number()->min(1);
  $validate->get("name")->string()->minLength(3);
  $validate->post("email")->string()->email();
  if ($validate->hasErrors()) {
    return ["getErrors" => $validate->getErrors(), "getOnlyErrors" => $validate->getOnlyErrors(), "getDetailedErrors" => $validate->getDetailedErrors()];
  } else {
    return ["response" => "OK"];
  }
});

Router::post("/asd/qwe/{id}", function ($id) {
  $validate = new Validate(true);
  $validate->get("some", false)->number()->decimal();
  $validate->get("email")->string()->email();
  if ($validate->hasErrors()) {
    return $validate->getErrors();
  } else {
    return "OK";
  }
});

Router::put("/asd/qwe/{id}", function ($id) {
  global $_PUT, $_PARAMS;
  return ["method" => "PUT", '$_PARAMS' => $_PARAMS, '$_PUT' => $_PUT];
});

Router::delete("/asd/qwe/{id}", function ($id) {
  global $_DELETE, $_PARAMS;
  return ["method" => "DELETE", '$_PARAMS' => $_PARAMS, '$_DELETE' => $_DELETE];
});

Router::run();
