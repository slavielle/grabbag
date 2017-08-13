<?php

namespace slavielle\grabbag;

/**
 * Class HelpersService.
 */
use slavielle\grabbag\Fetcher;

class HelpersService {

  /**
   * Constructs a new HelpersService object.
   */
  public function __construct() {

  }

  public function grabber($object){
    return new Grabber($object);
  }
}
