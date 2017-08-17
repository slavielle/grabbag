<?php

namespace slavielle\grabbag;

use slavielle\grabbag\Resolver;
use slavielle\grabbag\Result;

class Grabber extends Resolver {
  
  public function grab($paths, $defaultValue = NULL, $enableException = FALSE) {
    $result = new Result($this->object, NULL);
    $result->grab($paths, $defaultValue, $enableException);
    return $result;
  }

}
