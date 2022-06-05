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
   * @param string $key
   * @param bool|string|array $validate
   * @return Method
   */
  public function param($key, $validate)
  {
    return $this->dispatchMethod("PARAM", $key, $validate);
  }

  /**
   * @param string $key
   * @param bool|string|array $validate
   * @return Method
   */
  public function file($key, $validate)
  {
    return $this->dispatchMethod("FILE", $key, $validate);
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
    $setErrors = function ($method, $key, $errorMessage) {
      // Validating if the method is GET or any other method and GET, depending on the current method
      $methods = ["PARAM", strtoupper($_SERVER["REQUEST_METHOD"])];
      if ($methods[1] !== "GET") array_push($methods, "GET");
      if (!in_array($method, $methods)) return;
      $this->setErrors($method, $key, $errorMessage);
    };

    [$required, $message] = $this->validateRequired($key, $validate);

    $data = ["key" => $key];
    $data["name"] = $this->getName($data["key"]);
    $data["value"] = null;
    if (!is_null($data["name"])) {
      $_method = "_$method";
      global $$_method;
      $props = explode(".", $key);
      foreach ($props as $prop) {
        if (!isset($$_method[$prop])) {
          if ($required) {
            $setErrors($method, $key, $message);
            $data["value"] = null;
          }
          break;
        } else {
          $data["value"] = $$_method[$prop];
        }
        if (count($props) > 1) {
          $$_method = $$_method[$prop];
        }
      }
    }
    return new Method($method, $data, $setErrors);
  }

  /**
   * @param string $key
   * @return string|null
   */
  private function getName($key)
  {
    $letters = "[a-zA-Z]+";
    preg_match("/^($letters(\.($letters))*)(:($letters))?$/", $key, $matches);
    if (count($matches) === 0) return null;
    array_shift($matches);
    return isset($matches[4]) ? $matches[4] : (isset($matches[2]) ? $matches[2] : $matches[0]);
  }

  /**
   * @param string $key
   * @param bool|string|array $validate
   */
  private function validateRequired($key, $validate)
  {
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
    return [$required, $message];
  }

  /**
   * @param string $method
   * @param string $key
   * @param string $errorMessage
   */
  private function setErrors($method, $key, $errorMessage)
  {
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
  }
}
