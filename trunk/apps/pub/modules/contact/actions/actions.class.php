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
    try { $this->form = new ContactPublicForm($this->getUser()->getContact()); }
    catch ( liEvenementException $e )
    { $this->form = new ContactPublicForm; }
    
    $this->form->bind($request->getParameter('contact'));
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
    try { $this->form = new ContactPublicForm($this->getUser()->getContact()); }
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
