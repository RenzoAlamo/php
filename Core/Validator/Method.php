<?php

namespace Core\Validator;

use Closure;

class Method
{

  private $method, $key;
  private $value;
  private $fnSetErrors;

  /**
   * @param string $method
   * @param array $data
   * @param Closure $setErrors
   */
  public function __construct($method, $data, $setErrors)
  {
    $this->method = $method;
    $this->key = $data["key"];
    $this->value = $data["value"];
    $this->fnSetErrors = $setErrors;
  }

  /**
   * @param string $errorMessage
   * @return ValidateNumber
   */
  public function number($errorMessage = "")
  {
    $valid = false;
    if (!is_null($this->value)) {
      $_method = "_{$this->method}";
      global $$_method;
      if (!is_numeric($this->value)) {
        if (strlen(trim($errorMessage)) === 0) {
          $errorMessage = "El campo {$this->key} debe ser un número.";
        }
        $this->setErrors()($this->method, $this->key, $errorMessage);
      } else {
        if (strpos($this->value, ".") !== false) {
          $this->value = floatval($this->value);
        } else {
          $this->value = intval($this->value);
        }
        $valid = true;
      }
    }
    return new ValidateNumber($this->method, ["key" => $this->key, "value" => $this->value], $valid, $this->fnSetErrors);
  }

  /**
   * @param string $errorMessage
   * @return ValidateString
   */
  public function string($errorMessage = "")
  {
    $valid = false;
    if (!is_null($this->value)) {
      $_method = "_{$this->method}";
      global $$_method;
      if (!is_string($this->value)) {
        if (strlen(trim($errorMessage)) === 0) {
          $errorMessage = "El campo {$this->key} debe ser una cadena de texto.";
        }
        $this->setErrors()($this->method, $this->key, $errorMessage);
      } else {
        $valid = true;
      }
    }
    return new ValidateString($this->method, ["key" => $this->key, "value" => $this->value], $valid, $this->fnSetErrors);
  }

  /**
   * @return Closure
   */
  private function setErrors()
  {
    return $this->fnSetErrors;
  }
}
