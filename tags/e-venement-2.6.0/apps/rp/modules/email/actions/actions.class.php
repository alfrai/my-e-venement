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
  public function executeUpload(sfWebRequest $request)
  {
    $this->email = $this->getRoute()->getObject();
  }
  public function executeAttach(sfWebRequest $request) {
    $email = $request->getParameter('email');
    $this->email = Doctrine::getTable('email')->find($email['id']);
    
    $arr = $request->getFiles();
    $file = new liFileAttachment($arr['attachment']['name'],$arr['attachment']['type'],$arr['attachment']['tmp_name'],$arr['attachment']['size'],sfConfig::get('sf_upload_dir'));
    $file->setEmail($this->email);
    $file->save(sfConfig::get('sf_upload_dir').'/'.$file->generateFilename());
    
    $this->getUser()->setFlash('notice','File attached.');
    $this->redirect('email/edit?id='.$this->email->id);
    return sfView::NONE;
  }
  public function executeDeleteAttachment(sfWebRequest $request)
  {
    $q = new Doctrine_Query;
    $attachment = $q->from('Attachment a')
      ->andWhere('a.id = ?',$request->getParameter('attachment_id'))
      ->fetchOne();
    unlink(sfConfig::get('sf_upload_dir').'/'.$attachment->filename);
    $attachment->delete();
    
    $this->getUser()->setFlash('notice','The item was deleted successfully.');
    $this->redirect('email/edit?id='.$request->getParameter('id'));
  }
  
  public function executeContent(sfWebRequest $request)
  {
    sfConfig::set('sf_escaping_strategy', false);
    sfConfig::set('sf_web_debug', false);
    $this->object = $this->getRoute()->getObject();
    $this->column = 'content';
  }
  public function executeCopy(sfWebRequest $request)
  {
    $this->email = $this->getRoute()->getObject()->copy();
    foreach ( array('Contacts', 'Professionals', 'Organisms') as $relation )
    foreach ( $this->getRoute()->getObject()->$relation as $obj )
    {
      $rel = $this->email->$relation;
      $rel[] = $obj;
    }
    $this->email->sent = false;
    
    foreach ( $this->getRoute()->getObject()->Attachments as $att )
      $this->email->Attachments[] = $att->copy();
    
    $this->email->save();
    $this->redirect('email/edit?id='.$this->email->id);
  }
  
  public function executeEdit(sfWebRequest $request)
  {
    $r = parent::executeEdit($request);
    
    // if object has been sent, cannot be modified again
    if ( !$this->email->sent )
      $this->form->removeAlreadyKnownReceipientsList();
    else
      $this->setTemplate('show');
    
    return $r;
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $email = $request->getParameter('email');
    $this->email = $this->getRoute()->getObject();
    $this->form = $this->configuration->getForm($this->email);
    $this->form->removeAlreadyKnownReceipientsList();
    
    // not for real (test sending, attachment, content templating...)
    if ( !(isset($email['test_address']) && $email['test_address'])
      && !(isset($email['load']) && $email['load']) )
    {
      $this->form->getValidator('test_address')->setOption('required',false);
      if ( !isset($email['attach']) )
        $this->email->not_a_test = true;
      else
      {
        unset($email['attach']);
      }
    }
    
    // loading templates
    if ( $email['load'] )
    {
      $email['content'] = file_get_contents($email['load']);
      unset($email['load'],$email['test_address']);
      $request->setParameter('email',$email);
    }
    
    // mailer
    $this->email->setMailer($this->getMailer());
    $this->email->test_address = $email['test_address'];
    
    if ( $this->email->sent )
    {
      $this->getUser()->setFlash('error',"You can't modify an email already sent !");
      $this->redirect(url_for('email/show?id='.$this->email->id));
    }
    
    $request->setParameter('email', $email);
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
    $this->executeNew($request);
    //$this->form = $this->configuration->getForm();
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
    
    // CONTACTS
    $criterias = $this->getUser()->getAttribute('contact.filters', $this->configuration->getFilterDefaults(), 'admin_module');
    if ( !is_array($criterias) )
      $criterias = array();
    
    foreach ( $criterias as $name => $criteria )
    if ( !$criteria || !is_string($criteria) && !(is_array($criteria) && implode('',$criteria)) )
      unset($criterias[$name]);
    
    if ( $criterias )
    {
      // standard filtering
      $filters = new ContactFormFilter($criterias);
      foreach ( $filters->buildQuery($criterias)->execute() as $contact )
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
          $this->form->getObject()->Professionals[] = $pro;
        else
          $this->form->getObject()->Contacts[] = $contact;
      }
    }
    
    // ORGANISMS
    $criterias = $this->getUser()->getAttribute('organism.filters', $this->configuration->getFilterDefaults(), 'admin_module');
    if ( !is_array($criterias) )
      $criterias = array();
    
    foreach ( $criterias as $name => $criteria )
    if ( !$criteria || !is_string($criteria) && !(is_array($criteria) && implode('',$criteria)) )
      unset($criterias[$name]);
    
    if ( $criterias )
    {
      // standard filtering
      $filters = new OrganismFormFilter($criterias);
      foreach ( $filters->buildQuery($criterias)->execute() as $organism )
        $this->form->getObject()->Organisms[] = $organism;
    }
    
    //$this->form->setDefault('contacts_list',$contacts_list);
    //$this->form->setDefault('professionals_list',$professionals_list);
    //$this->form->setDefault('organisms_list',$organisms_list);
    $this->form->removeAlreadyKnownReceipientsList();
    
    return $r;
  }
}
