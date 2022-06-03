<?php

class Validate
{

  private $changeRegex;

  /**
   * @param Closure $changeRegex
   */
  public function __construct($changeRegex)
  {
    $this->changeRegex = $changeRegex;
  }

  /**
   * @param array $regex
   */
  public function where($regex)
  {
    if (!is_array($regex)) return;
    foreach ($regex as $key => $value) {
      ($this->changeRegex)($key, $value);
    }
  }
}
