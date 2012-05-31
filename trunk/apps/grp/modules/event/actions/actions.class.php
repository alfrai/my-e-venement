<?php

require_once dirname(__FILE__).'/../lib/eventGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/eventGeneratorHelper.class.php';

/**
 * event actions.
 *
 * @package    e-venement
 * @subpackage event
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class eventActions extends autoEventActions
{
  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( !$this->sort[0] )
    {
      $this->sort = array('name','');
      $this->pager->getQuery()->orderby('name');
    }
  }
  
  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);
    
    $q = new Doctrine_Query();
    $this->entry = $q->from('Entry e')
      ->leftJoin('e.ContactEntries ce')
      ->leftJoin('ce.Professional p')
      ->leftJoin('p.Contact c')
      ->leftJoin('p.Organism o')
      ->leftJoin('e.ManifestationEntries me')
      ->leftJoin('me.Manifestation m')
      ->where('e.event_id = ?',$request->getParameter('id'))
      ->orderBy('c.name, c.firstname')
      ->fetchOne();
    if ( !$this->entry )
    {
      $this->entry = new Entry;
      $this->entry->event_id = $request->getParameter('id');
      $this->entry->save();
    }
  }
}
