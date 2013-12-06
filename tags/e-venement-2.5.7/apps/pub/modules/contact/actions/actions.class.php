<?php

/**
 * contact actions.
 *
 * @package    symfony
 * @subpackage contact
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class contactActions extends sfActions
{
  public function executeNew(sfWebRequest $request)
  {
    $this->executeEdit($request);
    $this->setTemplate('edit');
  }
  
  public function executeUpdate(sfWebRequest $request)
  {
    // creating form
    try { $this->form = new ContactPublicForm($this->getUser()->getContact()); }
    catch ( liEvenementException $e )
    { $this->form = new ContactPublicForm; }
    
    // formatting data
    $contact = $request->getParameter('contact');
    if ( sfConfig::has('app_contact_capitalize') && is_array($fields = sfConfig::get('app_contact_capitalize')) )
    foreach ( $fields as $field )
    if ( isset($contact[$field]) )
      $contact[$field] = mb_strtoupper($contact[$field],'UTF-8');
    
    // validating and saving form
    $this->form->bind($contact);
    if ( $this->form->isValid() )
    {
      $this->form->save();
      
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('notice',__('Contact updated'));
      
      try { $this->getUser()->getContact(); }
      catch ( liEvenementException $e )
      { $this->getUser()->setContact($this->form->getObject()); }
      
      $this->redirect('contact/index');
    }
    
    $this->setTemplate('edit');
    return 'Success';
  }
  public function executeEdit(sfWebRequest $request)
  {
    try {
      $this->form = new ContactPublicForm($this->getUser()->getContact());
      $this->form->setDefault('phone_type',$this->getUser()->getContact()->Phonenumbers[0]->name);
      $this->form->setDefault('phone_number',$this->getUser()->getContact()->Phonenumbers[0]->number);
    }
    catch ( liEvenementException $e )
    { $this->form = new ContactPublicForm; }
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    try { $this->form = new ContactPublicForm($this->getUser()->getContact()); }
    catch ( liEvenementException $e )
    { $this->redirect('contact/new'); }
    
    $this->manifestations = Doctrine::getTable('Manifestation')->createQuery('m')
      ->leftJoin('m.Tickets tck')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('t.Order order')
      ->andWhere('order.id IS NOT NULL OR tck.printed = TRUE OR tck.integrated = TRUE')
      ->andWhere('t.contact_id = ?',$this->getUser()->getContact()->id)
      ->execute();
    
    $this->contact = $this->getUser()->getContact();
  }
}
