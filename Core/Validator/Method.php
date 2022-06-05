<?php

namespace Core\Validator;

use Closure;

class Method
{

  private $method, $key, $name;
  private $data;
  private $value;
  private $setErrors;

  /**
   * @param string $method
   * @param array $data
   * @param Closure $setErrors
   */
  public function __construct($method, $data, $setErrors)
  {
    $this->method = $method;
    $this->data = $data;
    $this->key = (string) $data["key"];
    $this->name = (string) $data["name"];
    $this->value = $data["value"];
    $this->setErrors = $setErrors;
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
          $errorMessage = "El campo {$this->name} debe ser un número.";
        }
        ($this->setErrors)($this->method, $this->key, $errorMessage);
      } else {
        if (strpos($this->value, ".") !== false) {
          $this->value = floatval($this->value);
        } else {
          $this->value = intval($this->value);
        }
        $valid = true;
      }
    }
    return new ValidateNumber($this->method, $this->data, $valid, $this->setErrors);
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
          $errorMessage = "El campo {$this->name} debe ser una cadena de texto.";
        }
        ($this->setErrors)($this->method, $this->key, $errorMessage);
      } else {
        $valid = true;
      }
    }
    return new ValidateString($this->method, $this->data, $valid, $this->setErrors);
  }
}
