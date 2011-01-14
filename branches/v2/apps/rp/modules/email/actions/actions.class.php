<?php

require_once dirname(__FILE__).'/../lib/emailGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/emailGeneratorHelper.class.php';

/**
 * email actions.
 *
 * @package    e-venement
 * @subpackage email
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class emailActions extends autoEmailActions
{
  public function executeTest(sfWebRequest $request)
  {
    $this->setTemplate('new');
  }
  public function executeNew(sfWebRequest $request)
  {
    $r = parent::executeNew($request);
    $criterias = $this->getUser()->getAttribute('contact.filters', $this->configuration->getFilterDefaults(), 'admin_module');
    
    if ( !is_array($criterias) )
      return $r;
    
    $groups = $criterias['groups_list'];
    unset($criterias['groups_list']);
    
    foreach ( $criterias as $name => $criteria )
    if ( !$criteria || !(is_array($criteria) && implode('',$criteria)) )
      unset($criterias[$name]);
    
    $professional_list = $contacts_list = array();
    
    if ( $criterias )
    {
      // standard filtering
      $filters = new ContactFormFilter($criterias);
      $q = $filters->buildQuery($criterias);
      foreach ( $q->execute() as $contact )
      if ( $contact->Professionals->count() > 0 )
      foreach ( $contact->Professionals as $pro )
        $professionals_list[] = $pro->id;
      else
        $contacts_list[] = $contact->id;
    }
    
    // groups filtering
    if ( count($groups) > 0 )
    {
      $q = Doctrine::getTable('Group')->createQuery();
      $a = $q->getRootAlias();
      $q->whereIn("$a.id",$groups);
      
      foreach ( $q->execute() as $group )
      {
        foreach ( $group->Professionals as $pro )
          $professionals_list[] = $pro->id;
        foreach ( $group->Contacts as $contact )
          $contacts_list[] = $contact->id;
      }
    }
    
    $this->form->setDefault('contacts_list',$contacts_list);
    $this->form->setDefault('professionals_list',$professionals_list);
    
    return $r;
  }
}
