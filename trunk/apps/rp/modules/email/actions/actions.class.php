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
  /*
  public function executeShow(sfWebRequest $request)
  {
    // not using the Doctrine::getTable('Email')->createQuery() because it's too long
    $this->email = Doctrine_Query::create()
      ->from('Email')
      ->where('id = ?',$request->getParameter('id'))
      ->limit(1)
      ->execute();
    $this->email = $this->email[0];
    $this->forward404Unless($this->email);
    $this->form = $this->configuration->getForm($this->email);
  }
  */
  
  public function executeContent(sfWebRequest $request)
  {
    sfConfig::set('sf_escaping_strategy', false);
    sfConfig::set('sf_web_debug', false);
    $this->object = $this->getRoute()->getObject();
    $this->column = 'content';
  }
  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( !$this->sort[0] )
    {
      $this->sort = array('sent','');
      $q = $this->pager->getQuery()
        ->orderby("sent, updated_at DESC, created_at DESC");
    }
  }

  public function executeCopy(sfWebRequest $request)
  {
    $this->email = $this->getRoute()->getObject()->copy(true);
    $this->email->Professionals = $this->getRoute()->getObject()->Professionals;
    $this->email->Contacts = $this->getRoute()->getObject()->Contacts;
    $this->form = $this->configuration->getForm($this->email);
    $this->setTemplate('new');
  }
  public function executeEdit(sfWebRequest $request)
  {
    $r = parent::executeEdit($request);
    
    // if object has been sent, cannot be modified again
    if ( $this->email->sent )
      $this->setTemplate('show');
    
    return $r;
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $email = $request->getParameter('email');
    $this->email = $this->getRoute()->getObject();
    $this->form = $this->configuration->getForm($this->email);
    
    // testing
    if ( !$email['test_address']
      && !$email['load'] )
    {
      $this->form->getValidator('test_address')->setOption('required',false);
      $this->email->not_a_test = true;
    }
    
    // loading templates
    if ( $email['load'] )
    {
      $email['content'] = file_get_contents($email['load']);
      unset($email['load'],$email['test_address']);
      $request->setParameter('email',$email);
    }
    
    // mailer
    $this->email->mailer = $this->getMailer();
    $this->email->test_address = $email['test_address'];
    
    if ( $this->email->sent )
    {
      $this->getUser()->setFlash('error',"You can't modify an email already sent !");
      $this->redirect('@email_show',$this->email);
    }
    
    try {
      $this->processForm($request, $this->form);
    }
    catch(Swift_TransportException $e)
    {
      $this->getUser()->setFlash('error','An error occured sending the email (smtp unreachable)');
      $this->redirect('email/edit?id='.$this->email->id);
    }
    
    $this->setTemplate('edit');
  }
  public function executeCreate(sfWebRequest $request)
  {
    $this->form = $this->configuration->getForm();
    $this->email = $this->form->getObject();
    $this->email->not_a_test = false;
    
    if ( $this->getUser() instanceof sfGuardSecurityUser )
      $this->email->sf_guard_user_id = $this->getUser()->getId();
    
    // loading templates
    $email = $request->getParameter('email');
    if ( $email['load'] )
    {
      $email['content'] = file_get_contents($email['load']);
      unset($email['load'],$email['test_address']);
      $request->setParameter('email',$email);
    }
    
    $this->processForm($request, $this->form);
    
    $this->setTemplate('new');
  }
  public function executeNew(sfWebRequest $request)
  {
    $r = parent::executeNew($request);
    $criterias = $this->getUser()->getAttribute('contact.filters', $this->configuration->getFilterDefaults(), 'admin_module');
    
    if ( !is_array($criterias) )
      return $r;
    
    //$groups = isset($criterias['groups_list']) ? $criterias['groups_list'] : array();
    //unset($criterias['groups_list']);
    
    foreach ( $criterias as $name => $criteria )
    if ( !$criteria || !is_string($criteria) && !(is_array($criteria) && implode('',$criteria)) )
      unset($criterias[$name]);
    
    $professionals_list = $contacts_list = array();
    
    if ( $criterias )
    {
      // standard filtering
      $filters = new ContactFormFilter($criterias);
      $q = $filters->buildQuery($criterias);
      foreach ( $q->execute() as $contact )
      {
        // check if it's in a group because of a link to an organism or not
        $groups_pro = array();
        $group_pro = false;
        if ( isset($criterias['groups_list']) && $criterias['groups_list'] )
        {
          foreach ( $contact->Professionals as $pro )
          foreach ( $pro->Groups as $group )
            $groups_pro[$group->id] = $group;
          foreach ( $criterias['groups_list'] as $grpid )
          {
            $group_pro = isset($groups_pro[$grpid]);
            if ( $group_pro )
              break;
          }
        }
        
        if ( $contact->Professionals->count() > 0
          && ($filters->showProfessionalData() || $group_pro) )
        foreach ( $contact->Professionals as $pro )
          $professionals_list[] = $pro->id;
        else
          $contacts_list[] = $contact->id;
      }
    }
    
    $this->form->setDefault('contacts_list',$contacts_list);
    $this->form->setDefault('professionals_list',$professionals_list);
    
    return $r;
  }
}
