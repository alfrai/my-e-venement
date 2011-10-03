<?php

/**
 * mod actions.
 *
 * @package    e-venement
 * @subpackage mod
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class modActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->remote_authentications = Doctrine_Core::getTable('RemoteAuthentication')
      ->createQuery('a')
      ->execute();
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->form = new RemoteAuthenticationForm();
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new RemoteAuthenticationForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($remote_authentication = Doctrine_Core::getTable('RemoteAuthentication')->find(array($request->getParameter('id'))), sprintf('Object remote_authentication does not exist (%s).', $request->getParameter('id')));
    $this->form = new RemoteAuthenticationForm($remote_authentication);
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($remote_authentication = Doctrine_Core::getTable('RemoteAuthentication')->find(array($request->getParameter('id'))), sprintf('Object remote_authentication does not exist (%s).', $request->getParameter('id')));
    $this->form = new RemoteAuthenticationForm($remote_authentication);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $this->forward404Unless($remote_authentication = Doctrine_Core::getTable('RemoteAuthentication')->find(array($request->getParameter('id'))), sprintf('Object remote_authentication does not exist (%s).', $request->getParameter('id')));
    $remote_authentication->delete();

    $this->redirect('mod/index');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $remote_authentication = $form->save();

      $this->redirect('mod/edit?id='.$remote_authentication->getId());
    }
  }
}
