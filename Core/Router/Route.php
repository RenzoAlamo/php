<?php

namespace Core\Router;

use Closure;
use Core\Utils;

class Route
{

  private static $prefix = "";
  private static $routes = [];

  private function __construct()
  {
  }

  /**
   * @param string prefix The prefix to use for the routes.
   * @param Closure callback The function to be called when the route is matched.
   */
  public static function prefix($prefix, $callback)
  {
    if (!is_string($prefix) || !($callback instanceof Closure)) return;
    $prefix = trim($prefix, " /");
    if (strlen($prefix) > 0) {
      $prefix = "/$prefix";
    }
    self::$prefix .= $prefix;
    $callback();
    if (strlen(self::$prefix) > 0) {
      self::$prefix = substr(self::$prefix, 0, strlen(self::$prefix) - strlen($prefix));
    }
  }

  /**
   * @param string $path
   * @param string|array|callable $action
   */
  public static function get($path, $action)
  {
    return self::addRoute("GET", $path, $action);
  }

  /**
   * @param string $path
   * @param string|array|callable $action
   */
  public static function post($path, $action)
  {
    return self::addRoute("POST", $path, $action);
  }

  /**
   * @param string $path
   * @param string|array|callable $action
   */
  public static function put($path, $action)
  {
    return self::addRoute("PUT", $path, $action);
  }

  /**
   * @param string $path
   * @param string|array|callable $action
   */
  public static function delete($path, $action)
  {
    return self::addRoute("DELETE", $path, $action);
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
  private static function addRoute($method, $path, $action)
  {
    $path = self::$prefix . "/" . trim($path, " /");
    if (strlen($path) !== 1) {
      $path = rtrim($path, "/");
    }
    // /{((\w+)(:([^}]+))?)}/
    $original_path = $path;
    $params = [];
    $letter = "[a-zA-Z]";
    $generic_regex = "([^/]+)";
    $path = preg_replace_callback("/{($letter(\w*$letter)?)}/", function ($match) use (&$params, $generic_regex) {
      [, $param] = $match;
      array_push($params, $param);
      return $generic_regex;
    }, $path);
    self::$routes["$method → $original_path"] = [
      "method" => $method,
      "path" => $path,
      "action" => $action,
      "params" => count($params) > 0 ? array_reduce($params, function ($previous, $current) use ($generic_regex) {
        $previous[$current] = $generic_regex;
        return $previous;
      }, []) : [],
    ];
    $changeRegex = function ($param, $regex) use ($method, $original_path) {
      if (
        !isset(self::$routes["$method → $original_path"]["params"][$param]) ||
        (!is_string($regex) || strlen(trim($regex)) === 0 || preg_match("~$regex~", "") === false)
      ) return;
      self::$routes["$method → $original_path"]["params"][$param] = $regex;
      // self::$routes = array_reduce(self::$routes, function ($previous, $current) use ($method, $path, $param, $regex) {
      //   if ($current["method"] === $method && $current["path"] === $path) {
      //     $path = preg_replace("/\{$param\}/", "($regex)", $current["original_path"]);
      //     $current["params"][$param] = $regex;
      //   }
      //   $previous["$method → $path"] = $current;
      //   return $previous;
      // }, []);
    };
    return new Validate($changeRegex);
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
    global $_PARAMS;

    $method = strtoupper($_SERVER["REQUEST_METHOD"]);
    $_method = "_$method";
    $path = substr(parse_url($_SERVER["REQUEST_URI"])["path"], strlen(base_folder));
    $response = [];

    global $$_method;

    $setError = function ($statusCode, $status) {
      header("{$_SERVER["SERVER_PROTOCOL"]} $statusCode $status", true, $statusCode);
      if (Utils::isAJAXrequest()) {
        echo json_encode(["statusCode" => $statusCode, "status" => $status]);
      } else {
        require_once root . "/Core/Views/Error.php";
      }
    };

    $paramError = false;
    foreach (self::$routes as $route) {
      if (preg_match("~^{$route["path"]}$~", $path, $matches)) {
        array_shift($matches);
        if (count($matches) > 0) {
          $count = -1;
          foreach ($route["params"] as $regex) {
            $count += 1;
            if (preg_match("~^$regex$~", $matches[$count]) === false) {
              $paramError = true;
              break;
            }
          }
          if ($paramError) {
            break;
          }
        }

        $response["params"] = array_keys($route["params"]);
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


    if ($paramError) {
      $setError(400, "Bad Request");
      return;
    }
    if (!$response) {
      $setError(404, "Route Not Found");
      return;
    }
    if (!isset($response["method"])) {
      $setError(405, "Method Not Allowed");
      return;
    }
    if (!isset($response["callback"])) {
      $setError(500, "Internal Server Error");
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
