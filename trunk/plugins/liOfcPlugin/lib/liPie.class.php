<?php

/**
 * liBar class.
 *
 * This class provides an abstraction layer to the PHP Ofc library
 *
 * @package    stOfcPlugin
 * @author     Baptiste SIMON <baptiste.simon@e-glop.net>
 */

/**
 * Plugin
 */

class liPie extends OFC_Charts_Pie
{
  public function liPie()
  {
    parent::OFC_Charts_Pie();
    $this->set_colours(array('#4ECDC4', '#C7F464', '#FF6B6B', '#C44D58', '#556270'));
    $this->set_alpha('0.7');
  }
  
  public function set_values($values)
  {
    parent::set_values($values);
    
    // rotate colours to avoid repetitions
    $colours = $this->colours;
    while ( count($this->colours) < count($values) )
    {
      $colours[] = array_shift($colours);
      $this->colours = array_merge($this->colours,$colours);
    }
  }
}
