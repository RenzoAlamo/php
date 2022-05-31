<?php

namespace Core\Validator;

class Validate
{

  private $setHeaderError;
  private $headerError = false;
  private $errors = [
    // "GET" => [],
    // "POST" => [],
    // "PUT" => [],
    // "DELETE" => [],
  ];

  /**
   * @param bool $setHeaderError
   */
  public function __construct($setHeaderError = false)
  {
    $this->setHeaderError = $setHeaderError;
  }

  /**
   * @param string $key
   * @param bool|string|array $validate
   * @return Method
   */
  public function get($key, $validate = null)
  {
    return $this->dispatchMethod("GET", $key, $validate);
  }

  /**
   * @param string $key
   * @param bool|string|array $validate
   * @return Method
   */
  public function post($key, $validate = null)
  {
    return $this->dispatchMethod("POST", $key, $validate);
  }

  /**
   * @param string $key
   * @param bool|string|array $validate
   * @return Method
   */
  public function put($key, $validate = null)
  {
    return $this->dispatchMethod("PUT", $key, $validate);
  }

  /**
   * @param string $key
   * @param bool|string|array $validate
   * @return Method
   */
  public function delete($key, $validate = null)
  {
    return $this->dispatchMethod("DELETE", $key, $validate);
  }

  /**
   * @return bool
   */
  public function hasErrors()
  {
    return array_reduce($this->errors, function ($previous, $current) {
      return $previous || count($current) > 0;
    }, false);
  }

  /**
   * @return array
   */
  public function getDetailedErrors()
  {
    return $this->errors;
  }

  public function getErrors()
  {
    return array_reduce($this->getDetailedErrors(), function ($previous, $current) {
      return array_merge($previous, $current);
    }, []);
  }

  public function getOnlyErrors()
  {
    return array_reduce($this->getErrors(), function ($previous, $current) {
      return array_merge($previous, $current);
    }, []);
  }

  /**
   * @param string $method
   * @param string $key
   * @param bool|string|array $validate
   * @return Method
   */
  private function dispatchMethod($method, $key, $validate)
  {
    // Function to set errors
    $setErrors = function ($method, $key, $errorMessage) {
      // Validating if the method is GET or any other method and GET, depending on the current method
      $currentMethod = strtoupper($_SERVER["REQUEST_METHOD"]);
      if (!in_array($method, ($currentMethod === "GET" ? [$currentMethod] : ["GET", $currentMethod]))) return;
      // Set header error for invalid data in request header
      if ($this->setHeaderError && !$this->headerError) {
        $statusCode = 422;
        $status = "Unprocessable Entity";
        header("{$_SERVER["SERVER_PROTOCOL"]} $statusCode $status", true, $statusCode);
        $this->headerError = true;
      }
      // Set errors
      if (!isset($this->errors[$method][$key])) {
        $this->errors[$method][$key] = [$errorMessage];
      } else {
        $this->errors[$method][$key][] = $errorMessage;
      }
    };

    // Validating if is required
    $required = true;
    $message = "El campo $key es requerido.";
    if (is_array($validate)) {
      foreach ($validate as $index => $value) {
        if (($index === "required" || $index === 0) && is_bool($value)) {
          $required = $value;
        }
        if (($index === "message" || $index === 1) && is_string($value) && trim($value) !== "") {
          $message = $value;
        }
      }
    } else if (is_string($validate) && trim($validate) !== "") {
      $message = $validate;
    } else if (is_bool($validate)) {
      $required = $validate;
    }


    $data = ["key" => $key, "value" => null];
    $_method = "_$method";
    global $$_method; // $_GET, $_POST, $_PUT, $_DELETE
    $props = explode(".", $key); // Example: $_GET ["user.name"] => $_GET ["user"] ["name"]
    if (count($props) === 1) {
      if (!isset($$_method[$key])) { // If the key doesn't exists, set errors and set value to null
        if ($required) {
          $setErrors($method, $key, $message);
          $data["value"] = null;
        }
      } else { // If the key exists, set value
        $data["value"] = $$_method[$key];
      }
    } else {
      foreach ($props as $index => $prop) {
        if (!isset($$_method[$prop])) {
          if ($required) {
            $setErrors($method, $key, $message);
            $data["value"] = null;
          }
          break;
        } else {
          $data["value"] = $$_method[$prop];
        }
        $$_method = $$_method[$prop];
      }
    }
    return new Method($method, $data, $setErrors);
  }
}
