<?php

namespace slavielle\grabbag;

use slavielle\grabbag\Resolver;
use slavielle\grabbag\Result;

/**
 * Grabber Allows to grab value(s) on object chain.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package slavielle\grabbag
 */
class Grabber extends Resolver {
  
  public function grab($paths) {
    $result = new Result($this->items, NULL);
    $result->grab($paths);
    return $result;
  }

}
