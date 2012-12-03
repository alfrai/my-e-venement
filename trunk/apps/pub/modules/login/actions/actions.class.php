<?php

/**
 * login actions.
 *
 * @package    symfony
 * @subpackage login
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class loginActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new LoginForm();
  }
  
  public function executeValidate(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $this->form = new LoginForm();
    $this->form->bind($request->getParameter('login'));
    
    if ( $this->form->isValid() )
    {
      $this->getUser()->setFlash('notice',__('You are authenticated.'));
      $this->getUser()->setAttribute('contact_id',$this->form->getContact()->id);
      return $this->redirect('@homepage');
    }
    
    $this->errors = $this->form->getErrorSchema()->getErrors();
    $this->getUser()->setFlash('error',__('Authentication failure.'));
    $this->setTemplate('index');
  }
}
