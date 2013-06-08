<?php
// A simple string class
// Should be replaced by SplString in the future
class HBString {
  public function __construct($string) {
    $this->_string = (string)$string;
  }

  public function __toString() {
    return $this->_string;
  }
}