<?php

require_once dirname(__FILE__).'/../lib/sfGuardUserGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sfGuardUserGeneratorHelper.class.php';

/**
 * sfGuardUser actions.
 *
 * @package    sfGuardPlugin
 * @subpackage sfGuardUser
 * @author     Fabien Potencier
 * @version    SVN: $Id: actions.class.php 23319 2009-10-25 12:22:23Z Kris.Wallsmith $
 */
class sfGuardUserActions extends autoSfGuardUserActions
{
  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( !$this->sort[0] )
    {
      $this->sort = array('username','');
      $this->pager->getQuery()->orderby('username');
    }
  }
  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);
    
    if ( !$this->getUser()->isSuperAdmin() )
    {
      $q = Doctrine::getTable('SfGuardPermission')->createQuery()
        ->whereIn('name',$this->getUser()->getCredentials())
        ->orderBy('name');
      $this->form->getWidget('permissions_list')->setOption('query',$q);
      
      $q = Doctrine::getTable('SfGuardGroup')->createQuery()
        ->whereIn('name',$this->getUser()->getGroupnames())
        ->orderBy('name');
      $this->form->getWidget('groups_list')->setOption('query',$q);
    }
  }
}
