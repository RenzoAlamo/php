<?php

require_once __DIR__ . "/../Config/autoload.php";

use Core\Router\Route;
use Core\Validator\Validate;
use Core\View;

Route::staticResources("css", "/index.style", ["BackColor.css", "Error.css"]);
Route::staticResource("js", "/index.script", "Error.js");
Route::staticResource("img", "/index.image", "wallet_1.gif");

Route::get("/", function () {
  // print_r("<h1>View INDEX</h1>");
  // return View::render("index");
  // return $_SERVER;
  return array_keys(Route::getRoutes());
});

Route::get("/contact", function () {
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

Route::prefix("/api", function () {
  Route::get("/", function () {
    return "API";
  });
  Route::prefix("/user", function () {
    Route::get("/", function () {
      return "User";
    });
    Route::get("/{id:(\d+)}", function ($id) {
      return "User $id";
    });
    Route::post("/", function () {
      return "User";
    });
    Route::put("/{id:(\d+)}", function ($id) {
      return "User $id";
    });
    Route::delete("/{id:(\d+)}", function ($id) {
      return "User $id";
    });
    Route::prefix("/posts", function () {
      Route::get("/", function ($id) {
        return "User $id";
      });
      Route::get("/{id:(\d+)}", function ($id) {
        return "User $id";
      });
      Route::post("/", function ($id) {
        return "User $id";
      });
      Route::put("/{id:(\d+)}", function ($id) {
        return "User $id";
      });
      Route::delete("/{id:(\d+)}", function ($id) {
        return "User $id";
      });
    });
  });
});

Route::post("/asd/qwe/{id}", function ($id) {
  $validate = new Validate(true);
  $validate->get("some", false)->number()->decimal();
  $validate->get("email")->string()->email();
  if ($validate->hasErrors()) {
    return $validate->getErrors();
  } else {
    return "OK";
  }
});

Route::put("/asd/qwe/{id}", function ($id) {
  global $_PUT, $_PARAMS;
  return ["method" => "PUT", '$_PARAMS' => $_PARAMS, '$_PUT' => $_PUT];
});

Route::delete("/asd/qwe/{id}", function ($id) {
  global $_DELETE, $_PARAMS;
  return ["method" => "DELETE", '$_PARAMS' => $_PARAMS, '$_DELETE' => $_DELETE];
});

Route::run();
