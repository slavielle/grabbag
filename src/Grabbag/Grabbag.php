<?php

namespace Grabbag;

use Grabbag\Resolver;
use Grabbag\Result;

/**
 * Grabber Allows to grab value(s) on object chain.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class Grabbag extends Resolver {
  
  public function grab($paths) {
    $result = new Result($this->items, NULL);
    $result->grab($paths);
    return $result;
  }

}
