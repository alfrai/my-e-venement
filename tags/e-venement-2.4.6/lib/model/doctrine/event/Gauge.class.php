<?php

/**
 * Gauge
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Gauge extends PluginGauge
{
  public function __toString()
  {
    return $this->Workspace->name;
  }
  public function preSave($event)
  {
    if ( is_null($this->value) )
      $this->value = 0;
    parent::preSave($event);
  }
}
