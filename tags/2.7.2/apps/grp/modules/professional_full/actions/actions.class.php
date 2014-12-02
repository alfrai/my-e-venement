<?php

require_once dirname(__FILE__).'/../lib/professional_fullGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/professional_fullGeneratorHelper.class.php';

/**
 * professional_full actions.
 *
 * @package    e-venement
 * @subpackage professional_full
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class professional_fullActions extends autoProfessional_fullActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->redirect('professional/index');
  }
  
  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);
    
    $q = Doctrine::getTable('Professional')->createQuery('p')
      ->andWhere('p.id = ?',$request->getParameter('id'));
    Doctrine::getTable('Professional')->doSelectOnlyGrp($q);
    $this->professional = $q->fetchOne();
  }
  public function executeNew(sfWebRequest $request)
  {
    $this->form = new ContactEntryByContactForm;
    $this->form->restoreProfessionalId();
  }
  public function executeCreate(sfWebRequest $request)
  {
    $this->form = new ContactEntryByContactForm;
    $this->form->restoreProfessionalId();
    $this->form->bind($request->getParameter('contact_entry_new'));
    if ( $this->form->isValid() )
    {
      $this->form->save();
      $this->getUser()->setFlash('success', 'The item was created successfully.');
      $this->redirect('professional_full/edit?id='.$this->form->getObject()->Professional->id);
    }
    
    $this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
    $this->setTemplate('new');
  }
  public function executeUpdate(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  public function executeDelete(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  public function executeBatchDelete(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
}
