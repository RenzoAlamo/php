<?php

namespace Core\Validator;

use Closure;

class ValidateNumber
{

  private $method, $key;
  /**  @var int|float|null */ private $value;
  private $isDecimal, $valid;
  private $setErrors;

  /**
   * @param string $method
   * @param array $data
   * @param bool $valid
   * @param Closure $setErrors
   */
  public function __construct($method, $data, $valid, $setErrors)
  {
    $this->method = $method;
    $this->key = (string) $data["key"];
    $this->value = $data["value"];
    $this->isDecimal = is_double($this->value);
    $this->valid = $valid;
    $this->setErrors = $setErrors;
  }

  /**
   * @param int $min
   * @param string|null $errorMessage
   * @return ValidateNumber
   */
  public function min($min, $errorMessage = "")
  {
    if ($this->valid && $this->value < $min) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe ser mayor o igual a {$min}.";
      }
      ($this->setErrors)($this->method, $this->key, $errorMessage);
    }
    return $this;
  }


  /**
   * @param int $max
   * @param string|null $errorMessage
   * @return ValidateNumber
   */
  public function max($max, $errorMessage = "")
  {
    if ($this->valid && $this->value > $max) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe ser menor o igual a {$max}.";
      }
      ($this->setErrors)($this->method, $this->key, $errorMessage);
    }
    return $this;
  }

  /**
   * @param int $length
   * @param string|null $errorMessage
   * @return ValidateNumber
   */
  public function length($length, $errorMessage = "")
  {
    if ($this->valid && !$this->isDecimal && strlen(strval($this->value)) !== $length) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe tener {$length} caracteres.";
      }
      ($this->setErrors)($this->method, $this->key, $errorMessage);
    }
    return $this;
  }

  /**
   * @param int $minLength
   * @param string|null $errorMessage
   * @return ValidateNumber
   */
  public function minLength($minLength, $errorMessage = "")
  {
    if ($this->valid && !$this->isDecimal && strlen(strval($this->value)) < $minLength) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe tener una longitud de al menos {$minLength}.";
      }
      ($this->setErrors)($this->method, $this->key, $errorMessage);
    }
    return $this;
  }

  /**
   * @param int $maxLength
   * @param string|null $errorMessage
   * @return ValidateNumber
   */
  public function maxLength($maxLength, $errorMessage = "")
  {
    if ($this->valid && !$this->isDecimal && strlen(strval($this->value)) > $maxLength) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe tener una longitud de no más de {$maxLength} dígitos.";
      }
      ($this->setErrors)($this->method, $this->key, $errorMessage);
    }
    return $this;
  }

  /**
   * @param int $min
   * @param int $max
   * @param string|null $errorMessage
   * @return ValidateNumber
   */
  public function between($min, $max, $errorMessage = "")
  {
    if ($this->valid && ($this->value < $min || $this->value > $max)) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe estar entre {$min} y {$max}.";
      }
      ($this->setErrors)($this->method, $this->key, $errorMessage);
    }
    return $this;
  }

  /**
   * @param string|null $errorMessage
   * @return ValidateNumber
   */
  public function decimal($errorMessage = "")
  {
    if ($this->valid && !$this->isDecimal) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe ser un número decimal.";
      }
      ($this->setErrors)($this->method, $this->key, $errorMessage);
    }
    return $this;
  }

  /**
   * @param array $values
   * @param string|null $errorMessage
   * @return ValidateNumber
   */
  public function isIn($values, $errorMessage = "")
  {
    if ($this->valid && !in_array($this->value, $values)) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe ser uno de los siguientes valores: " . implode(", ", $values) . ".";
      }
      ($this->setErrors)($this->method, $this->key, $errorMessage);
    }
    return $this;
  }
}
