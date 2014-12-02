<?php

/**
 * liGraph class.
 *
 * This class provides an abstraction layer to the PHP Ofc library
 *
 * @package    stOfcPlugin
 * @author     RASHID Dawood <daud55@gmail.com>
 */

/**
 * Plugin
 */

class liGraph extends open_flash_chart // graph
{
  public function liGraph()
  {
    parent::open_flash_chart();
    $this->set_bg_colour('#E3F0FD');
    $this->set_number_format('2',false,true,true);
  }
  
  public function render()
  {
    return (string)$this;
  }
  public function __toString()
  {
    return $this->toPrettyString();
  }
}
