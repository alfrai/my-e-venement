<?php

/**
 * Traceable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Traceable extends PluginTraceable
{
  public function preSave($event)
  {
    if ( sfContext::hasInstance() && sfContext::getInstance()->getUser()->getId() && $this->isModified() )
      $this->sf_guard_user_id = sfContext::getInstance()->getUser()->getId();
    parent::preSave($event);
  }
  
  public function copy($deep = FALSE)
  {
    $t = parent::copy($deep);
    
    $t->updated_at = NULL;
    $t->created_at = NULL;
    $t->sf_guard_user_id = NULL;
    
    return $t;
  }
}
