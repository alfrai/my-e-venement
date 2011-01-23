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
      $this->pager->getQuery()->orderby('me.name, name');
    }
  }

  public function executeUpdateIndexes(sfWebRequest $request)
  {
    $table = Doctrine_Core::getTable('Event');
    $table->batchUpdateIndex();
    
    $this->getUser()->setFlash('notice',"Events' index table has been updated.");
    
    $this->redirect('event');
  }
}
