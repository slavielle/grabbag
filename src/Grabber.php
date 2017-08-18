<?php

namespace slavielle\grabbag;

use slavielle\grabbag\Resolver;
use slavielle\grabbag\Result;

/**
 * Grabber Allows to grab value(s) on object chain.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 */
class Grabber extends Resolver {
  
  public function grab($paths) {
    $result = new Result($this->object, NULL);
    $result->grab($paths);
    return $result;
  }

}
