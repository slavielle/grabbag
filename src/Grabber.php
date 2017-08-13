<?php

namespace slavielle\grabbag;

use slavielle\grabbag\Resolver;

class Grabber extends Resolver {

  public function grab($path, $defaultValue = NULL) {
    return $this->resolve(new Path($path));
  }

}
