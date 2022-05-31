<?php

namespace Core;

class Router
{

  private static $routes = [];

  private function __construct()
  {
  }
  /**
   * @param string $path
   * @param string|array|callable $action
   */
  public static function get($path, $action)
  {
    self::addRoute("GET", $path, $action);
  }

  /**
   * @param string $path
   * @param string|array|callable $action
   */
  public static function post($path, $action)
  {
    self::addRoute("POST", $path, $action);
  }

  /**
   * @param string $path
   * @param string|array|callable $action
   */
  public static function put($path, $action)
  {
    self::addRoute("PUT", $path, $action);
  }

  /**
   * @param string $path
   * @param string|array|callable $action
   */
  public static function delete($path, $action)
  {
    self::addRoute("DELETE", $path, $action);
  }

  // public function prefix($prefix, $action)

  /**
   * @param "css"|"js"|string $type
   * @param string $path
   * @param string $resource
   */
  public static function staticResource($type, $path, $resource)
  {
    if (!is_string($resource)) return;
    self::staticResources($type, $path, [$resource]);
  }

  public static function staticResources($type, $path, $resources)
  {
    if (!is_array($resources)) return;
    if (!in_array($type, ["css", "js"]) && count($resources) !== 1) return;
    global $_STYLES, $_SCRIPTS, $_MULTIMEDIA;
    $contents = [];
    $name = preg_replace("%\/%", "_", trim($path, "/"));
    $path = "/" . trim($path, "/");
    $resource_url = base_folder . $path;
    if ($type === "css") {
      $folder = "css";
    } elseif ($type === "js") {
      $folder = "js";
    } else {
      $folder = "multimedia";
    }
    foreach ($resources as $resource) {
      $resource = root . "/Resources/$folder/" . trim($resource, "/");
      if (!file_exists($resource) || !is_file($resource)) continue;
      if ($type === "css") {
        if (substr($resource, -4) !== ".$type" || mime_content_type($resource) !== "text/plain") continue;
      } else if ($type === "js") {
        if (substr($resource, -3) !== ".$type" || mime_content_type($resource) !== "text/plain") continue;
      }
      $contents[] = file_get_contents($resource);
    }
    if (count($contents) > 0) {
      if ($type === "css") {
        $content_type = "text/css";
        $_STYLES[$name] = $resource_url;
      } else if ($type === "js") {
        $content_type = "application/javascript";
        $_SCRIPTS[$name] = $resource_url;
      } else {
        $content_type = mime_content_type($resource);
        $_MULTIMEDIA[$name] = $resource_url;
      }
      self::addRoute("GET", $path, function () use ($content_type, $contents) {
        header("Content-Type: $content_type");
        return implode("\n", $contents);
      });
    }
  }

  public static function getRoutes()
  {
    return self::$routes;
  }

  /**
   * @param string $method
   * @param string $path
   * @param string|array|callable $action
   */
  private function addRoute($method, $path, $action)
  {
    die("Not implemented yet!");
    $params = [];
    $path = preg_replace_callback("/{((\w+)(:([^}]+))?)}/", function ($match) use (&$params) {
      array_push($params, $match[2]);
      return $match[4] ?? "([^\/]+)";
    }, $path);
    self::$routes["$method → $path"] = [
      "method" => $method,
      "path" => $path,
      "action" => $action,
      "params" => $params
    ];
  }

  public static function hasRoute($method, $path)
  {
    foreach (self::$routes as $route) {
      if ($route["method"] === $method && $route["path"] === $path) {
        return true;
      }
    }
    return false;
  }

  public static function run()
  {
    // die("Router::run() is not implemented yet.");
    global $_PARAMS;
    $_PARAMS = isset($_PARAMS) ? $_PARAMS : [];

    $method = strtoupper($_SERVER["REQUEST_METHOD"]);
    $_method = "_$method";
    $path = substr(parse_url($_SERVER["REQUEST_URI"])["path"], strlen(base_folder));
    $response = [];

    global $$_method;
    $$_method = isset($$_method) ? $$_method : [];

    foreach (self::$routes as $route) {
      if (preg_match("~^{$route["path"]}$~", $path, $matches)) {
        array_shift($matches);
        $response["params"] = $route["params"];
        $response["args"] = $matches;
        if ($route["method"] === $method) {
          $response["method"] = $method;
          if (is_string($route["action"])) {
            $action = explode("@", $route["action"]);
          } else if (is_array($route["action"])) {
            $action = $route["action"];
          } else if (is_callable($route["action"])) {
            $response["callback"] = $action = $route["action"];
          } else {
            $action = null;
          }
          if (!is_null($action) && !is_callable($action) && (count($action) >= 2) && ([$controller, $method] = $action) && method_exists($controller, $method)) {
            $response["callback"] = [$controller, $method];
          }
          break;
        }
      }
    }

    if (!$response) {
      $statusCode = 404;
      $status = "Route Not Found";
      header("{$_SERVER["SERVER_PROTOCOL"]} $statusCode $status", true, $statusCode);
      if (Utils::isAJAXrequest()) {
        echo json_encode(["statusCode" => $statusCode, "status" => $status]);
      } else {
        require_once root . "/Core/Views/Error.php";
      }
      return;
    }
    if (!isset($response["method"])) {
      $statusCode = 405;
      $status = "Method Not Allowed";
      header("{$_SERVER["SERVER_PROTOCOL"]} $statusCode $status", true, $statusCode);
      if (Utils::isAJAXrequest()) {
        echo json_encode(["statusCode" => $statusCode, "status" => $status]);
      } else {
        require_once root . "/Core/Views/Error.php";
      }
      return;
    }
    if (!isset($response["callback"])) {
      $statusCode = 500;
      $status = "Internal Server Error";
      header("{$_SERVER["SERVER_PROTOCOL"]} $statusCode $status", true, $statusCode);
      if (Utils::isAJAXrequest()) {
        echo json_encode(["statusCode" => $statusCode, "status" => $status]);
      } else {
        require_once root . "/Core/Views/Error.php";
      }
      return;
    }
    if ($response["params"] && (count($response["params"]) === count($response["args"]))) {
      $_PARAMS = array_combine($response["params"], $response["args"]);
    }
    $headers = getallheaders();
    $headers = array_combine(array_map("strtolower", array_keys($headers)), array_values($headers));
    if ($method !== "GET") {
      if ($method !== "POST") {
        if (
          strpos($headers["content-type"], "multipart/form-data; boundary=----WebKitFormBoundary") === 0 &&
          strpos(file_get_contents("php://input"), "------WebKitFormBoundaryq") === 0
        ) {
          self::parseFormData($method);
        } else if (strpos($headers["content-type"], "application/json") !== false) {
          $data = json_decode(file_get_contents("php://input"), true);
          if (is_array($data)) {
            $$_method = $data;
          }
        }
      } else {
        if (strpos($headers["content-type"], "application/json") !== false) {
          $data = json_decode(file_get_contents("php://input"), true);
          if (is_array($data)) {
            $_POST = $data;
          }
        }
      }
    }

    ob_start();
    $result = call_user_func_array($response["callback"], $response["args"]);
    ob_end_clean();
    if (!is_null($result)) {
      if (is_object($result)) {
        if (!method_exists($result, "__toString")) {
          // if (($class = get_class($result)) === "stdClass") {
          if (($class = get_class($result)) === "stdClass" || class_exists($class) && ($result = get_class_vars($class)) && count($result) > 0) {
            echo json_encode($result);
          }
          return;
        }
        try {
          $result = $result->__toString();
          if (!is_string($result)) return;
        } catch (\Throwable $th) {
          return;
        }
      }
      if (is_array($result)) {
        $result = json_encode($result);
      }
      if (is_string($result) || is_numeric($result) || is_bool($result)) {
        echo $result;
        return;
      }
    }
  }

  /**
   * @param "PUT"|"DELETE" $method
   */
  private function parseFormData($method)
  {
    if (!in_array($method, ["PUT", "DELETE"])) return;

    $method = "_$method";
    global $$method;
    $$method = isset($$method) ? $$method : [];

    $input = file_get_contents("php://input");
    $boundary = substr($input, 0, strpos($input, "\r\n"));
    $entries = explode($boundary, $input);
    array_pop($entries);
    array_shift($entries);
    $entries = array_map(function ($entry) {
      return ltrim($entry);
    }, $entries);

    foreach ($entries as $entry) {

      [$raw_headers, $body] = explode("\r\n\r\n", $entry, 2);
      $body = substr($body, 0, strlen($body) - 2);

      $raw_headers = explode("\r\n", $raw_headers);
      $headers = [];
      foreach ($raw_headers as $header) {
        [$name, $value] = explode(":", $header);
        $headers[strtolower($name)] = ltrim($value, " ");
      }

      if (isset($headers["content-disposition"])) {
        $filename = null;
        preg_match('/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', $headers["content-disposition"], $matches);
        $name = $matches[2];
        if (isset($matches[4]) && ($filename = $matches[4])) {
          $tmp_name = tempnam(sys_get_temp_dir(), "php");
          file_put_contents($tmp_name, $body);
          $_FILES[$name] = [
            "name" => $filename,
            "type" => $headers["content-type"],
            "size" => strlen($body),
            "tmp_name" => $tmp_name,
            "error" => UPLOAD_ERR_OK,
          ];
        } else {
          array_push($$method, "$name=$body");
        }
      }
    }
    parse_str(implode("&", $$method), $$method);
  }
}
