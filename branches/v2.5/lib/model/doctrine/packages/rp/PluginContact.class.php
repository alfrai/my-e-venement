<?php

/**
 * PluginContact
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginContact extends BaseContact
{
  public function preSave($event)
  {
    foreach ( $this->YOBs as $key => $yob )
    if ( !$yob['year'] )
      unset($this->YOBs[$key]);
    
    foreach ( $this->Relationships as $key => $rel )
    if ( !$rel['to_contact_id'] )
      unset($this->Relationships[$key]);
    
    return parent::preSave($event);
  }
  
  public function postInsert($event)
  {
    foreach ( $this->Professionals as $pro )
      $pro->contact_id = $this->id;
    
    foreach ( $this->Phonenumbers as $pn )
      $pn->contact_id = $this->id;
    
    parent::postInsert($event);
  }
}
