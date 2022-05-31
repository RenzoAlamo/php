<?php

namespace Core;

class View
{

  private function __construct()
  {
  }
  /**
   * @param string $view
   * @param array $options
   */
  public static function render($view, $options = [])
  {
    $view = root . "/Views/" . str_replace(".", "/", $view) . ".php";
    if (file_exists($view)) {
      if (is_array($options)) {
        // if (isset($options["styles"]) && is_array($options["styles"]) && count($options["styles"]) > 0) {
        //   $styles = [];
        //   foreach ($options["styles"] as $route => $style_path) {
        //     $route = "/" . trim($route, "/");
        //     $base_css = "/Views/" . preg_replace("/.css$/", "", $style_path) . ".css";
        //     $css = __DIR__ . "/..$base_css";
        //     if (!file_exists($css)) return;
        //     if (!Router::hasRoute("GET", $route)) {
        //       Router::get($route, function () use ($css) {
        //         header("Content-Type: text/css");
        //         echo file_get_contents($css);
        //       });
        //       Router::run();
        //     }
        //     $styles[trim($route, "/")] = base_folder . $route;
        //   }
        // }
        // if (isset($options["scripts"]) && is_array($options["scripts"]) && count($options["scripts"]) > 0) {
        //   $scripts = [];
        //   foreach ($options["scripts"] as $route => $script_path) {
        //     $route = "/" . trim($route, "/");
        //     $base_js = "/Views/" . preg_replace("/.js$/", "", $script_path) . ".js";
        //     $js = __DIR__ . "/..$base_js";
        //     if (!file_exists($js)) return;
        //     if (!Router::hasRoute("GET", $route)) {
        //       Router::get($route, function () use ($js) {
        //         header("Content-Type: text/javascript");
        //         echo file_get_contents($js);
        //       });
        //     }
        //     $scripts[trim($route, "/")] = base_folder . $route;
        //   }
        // }
        // if (isset($options["images"]) && is_array($options["images"]) && count($options["images"]) > 0) {
        //   $images = [];
        //   foreach ($options["images"] as $route => $image_path) {
        //     $route = "/" . trim($route, "/");
        //     $base_img = "/Views/$image_path";
        //     $img = __DIR__ . "/..$base_img";
        //     if (!file_exists($img)) return;
        //     if (!Router::hasRoute("GET", $route)) {
        //       Router::get($route, function () use ($img) {
        //         header("Content-Type: image/gif");
        //         echo file_get_contents($img);
        //       });
        //       Router::run();
        //     }
        //     $images[trim($route, "/")] = base_folder . $route;
        //   }
        // }
        if (isset($options["data"]) && is_array($options["data"]) && count($options["data"]) > 0) {
          extract($options["data"]);
        }
      }
    } else {
      $statusCode = 404;
      $status = "View Not Found";
      header("{$_SERVER["SERVER_PROTOCOL"]} $statusCode $status", true, $statusCode);
      $view = __DIR__ . "/Views/Error.php";
    }
    global $_PUT, $_DELETE, $_PARAMS, $_STYLES, $_SCRIPTS, $_MULTIMEDIA;
    ob_start();
    require_once $view;
    return ob_get_contents();
  }

  public static function getContent($view)
  {
    if (file_exists($view)) {
      return htmlspecialchars(file_get_contents($view));
    }
  }
}
