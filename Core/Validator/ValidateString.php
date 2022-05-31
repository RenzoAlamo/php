<?php

namespace Core\Validator;

use Closure;

class ValidateString
{

  private $method, $key;
  /** @var string|null */ private $value;
  private $valid;
  private $fnSetErrors;

  /**
   * @param string $method
   * @param array $data
   * @param bool $valid
   * @param Closure $setErrors
   */
  public function __construct($method, $data, $valid, $setErrors)
  {
    $this->method = $method;
    $this->key = $data["key"];
    $this->value = $data["value"];
    $this->valid = $valid;
    $this->fnSetErrors = $setErrors;
  }

  /**
   * @param int $length
   * @param string|null $errorMessage
   * @return ValidateString
   */
  public function length($length, $errorMessage = "")
  {
    if ($this->valid && is_numeric($length) && strlen($this->value) !== $length) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe tener {$length} caracteres.";
      }
      $this->setErrors()($this->method, $this->key, $errorMessage);
    }
    return $this;
  }

  /**
   * @param int $minLength
   * @param string|null $errorMessage
   * @return ValidateString
   */
  public function minLength($minLength, $errorMessage = "")
  {
    if ($this->valid && is_numeric($minLength) && strlen($this->value) < $minLength) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe tener una longitud de al menos {$minLength} caracteres.";
      }
      $this->setErrors()($this->method, $this->key, $errorMessage);
    }
    return $this;
  }

  /**
   * @param int $maxLength
   * @param string|null $errorMessage
   * @return ValidateString
   */
  public function maxLength($maxLength, $errorMessage = "")
  {
    if ($this->valid && is_numeric($maxLength) && strlen($this->value) > $maxLength) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe tener una longitud de no más de {$maxLength} caracteres.";
      }
      $this->setErrors()($this->method, $this->key, $errorMessage);
    }
    return $this;
  }

  /**
   * @param string|null $errorMessage
   */
  public function email($errorMessage = "")
  {
    $l = "a-zA-Z";
    $d = "0-9";
    if ($this->valid && !preg_match("/^[$l]\w{3,59}[$l$d]@[$l$d][$l$d-]{1,61}[$l$d](\.[$l$d]{2,63}){1,2}$/", $this->value)) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe ser un email válido.";
      }
      $this->setErrors()($this->method, $this->key, $errorMessage);
    }
  }

  /**
   * @param string|null $errorMessage
   * @return ValidateString
   */
  public function noSpaces($errorMessage = "")
  {
    $s = "\s";
    if ($this->valid && preg_match("/$s/", $this->value)) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} no debe contener espacios.";
      }
      $this->setErrors()($this->method, $this->key, $errorMessage);
    }
    return $this;
  }

  /**
   * @param string|null $errorMessage
   * @return ValidateString
   */
  public function oneSpaceBetwwenWords($errorMessage = "")
  {
    $s = "\s";
    if ($this->valid && preg_match("/$s$s/", $this->value)) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} no debe contener más de un espacio entre palabras.";
      }
      $this->setErrors()($this->method, $this->key, $errorMessage);
    }
    return $this;
  }

  /**
   * @param string|null $errorMessage
   * @return ValidateString
   */
  public function onlyLetters($errorMessage = "")
  {
    if ($this->valid && preg_match("/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ]/", $this->value)) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe contener solo letras.";
      }
      $this->setErrors()($this->method, $this->key, $errorMessage);
    }
    return $this;
  }

  /**
   * @param string|null $errorMessage
   * @return ValidateString
   */
  public function url($errorMessage = "")
  {
    if ($this->valid) {
      if (!filter_var($this->value, FILTER_VALIDATE_URL)) {
        if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
          $errorMessage = "El campo {$this->key} debe ser una URL válida.";
        }
        $this->setErrors()($this->method, $this->key, $errorMessage);
      }
    }
    return $this;
  }

  /**
   * @param string|null $errorMessage
   * @return ValidateString
   */
  public function name($errorMessage = "")
  {
    if ($this->valid && !$this->nameRegex("name", $this->value)) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe ser un nombre válido.";
      }
      $this->setErrors()($this->method, $this->key, $errorMessage);
    }
  }

  /**
   * @param string|null $errorMessage
   * @return ValidateString
   */
  public function lastname($errorMessage = "")
  {
    if ($this->valid && !$this->nameRegex("lastname", $this->value)) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe ser un apellido válido.";
      }
      $this->setErrors()($this->method, $this->key, $errorMessage);
    }
  }

  /**
   * @param string|null $errorMessage
   * @return ValidateString
   */
  public function fullname($errorMessage = "")
  {
    if ($this->valid && !$this->nameRegex("fullname", $this->value)) {
      if (!is_string($errorMessage) || strlen(trim($errorMessage)) === 0) {
        $errorMessage = "El campo {$this->key} debe ser un nombre completo válido.";
      }
      $this->setErrors()($this->method, $this->key, $errorMessage);
    }
  }

  private function nameRegex($type, $value)
  {
    $name = "[A-ZÁÉÍÓÚÑÜ][a-záéíóúñü]{2,40}";
    switch ($type) {
      case "name":
        $size = "0,0";
      case "lastname":
        $size = "1,";
      case "fullname":
        $size = "2,";
      default:
        # code...
        break;
    }
    if (isset($size)) {
      return preg_match("/^$name( $name)\{$size\}$/", $value);
    }
  }

  /**
   * @return Closure
   */
  private function setErrors()
  {
    return $this->fnSetErrors;
  }
}
