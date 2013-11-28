<?php

/**
 * liYAxis class.
 *
 * This class provides an abstraction layer to the PHP Ofc library
 *
 * @package    liOfcPlugin
 * @author     Baptiste SIMON <baptiste.simon@e-glop.net>
 */

/**
 * Plugin
 */

class liYAxis extends y_axis
{
  public function liYAxis()
  {
    parent::y_axis();
    $this->set_grid_colour('#FAFAFA');
  }
}
