<?php

/**
 * liXAxis class.
 *
 * This class provides an abstraction layer to the PHP Ofc library
 *
 * @package    liOfcPlugin
 * @author     Baptiste SIMON <baptiste.simon@e-glop.net>
 */

/**
 * Plugin
 */

class liXAxis extends x_axis
{
  public function liXAxis()
  {
    parent::x_axis();
    $this->set_grid_colour('#FAFAFA');
  }
}
