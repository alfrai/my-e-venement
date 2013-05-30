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

class liBarStack extends bar_stack
{
  public function liBarStack()
  {
    parent::bar_stack();
    $this->set_colours(array('red', 'orange', 'blue', 'green'));
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
