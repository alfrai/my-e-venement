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

class liBarFilled extends bar_filled
{
  public function liBarFilled($colour = '#E2D66A', $outline_colour = '#577261')
  {
    parent::bar_filled($colour, $outline_colour);
    $this->set_alpha(0.7);
  }
  
  public function set_keys($keys, $set_colours = true)
  {
    if ( $set_colours )
    {
      $arr = array();
      foreach ( $keys as $value )
        $arr[] = $value->colour;
      $this->set_colours($arr);
    }
    
    return parent::set_keys($keys);
  }
}
