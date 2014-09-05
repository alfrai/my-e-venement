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
  protected $is_auth_action = true;
  
  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }
  
  public function isAuthenticatingModule()
  {
    return $this->is_auth_action;
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    $this->register = $request->hasParameter('register');
    $this->form = new LoginForm;
    
    $this->getContext()->getConfiguration()->loadHelpers(array('Url'));
    if ( url_for('login/index') != $request->getPathInfoPrefix().$request->getPathInfo() )
      $this->form->setDefault('url_back', $request->getPathInfoPrefix().$request->getPathInfo());
  }
  
  public function executeForgot(sfWebRequest $request)
  {
    $this->form = new LoginForm();
    $this->form->bind(array('email' => $this->getRecoveryEmail()));
    $this->form->isRecovery();
  }
  public function executeSend(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('I18N', 'Url'));
    $this->form = new LoginForm();
    $this->form->isRecovery();
    $this->form->bind($request->getParameter('login'));
    if ( $this->form->isValid(false) )
    {
      $this->setRecoveryEmail($this->form->getValue('email'));
      $this->setRecoveryCode($code = md5(rand(0,10000000)));
      
      // sending the email
      $this->email = new Email;
      $this->email->not_a_test = true;
      $this->email->field_from = sfConfig::get('app_informations_email','web@libre-informatique.fr');
      $this->email->to = $this->getRecoveryEmail();
      $this->email->field_subject = __('Reset your password for %%name%%', array('%%name%%' => sfConfig::get('app_informations_title','')));
      $this->email->content = __('The recovery code to reset your password is %%code%%. Copy it into the password recovery form where you just have been redirected or follow this link:', array('%%code%%' => $code));
      $this->email->content .= "\n".url_for('login/recover?code='.$code, true);
      $this->email->setMailer($this->getMailer());
      $this->email->save();
      if ( !$this->email->sent )
      {
        $this->getUser()->setFlash('error', __('Please try again.'));
        $this->redirect('login/forgot');
      }
      
      $this->getUser()->setFlash('notice', __('An email has been sent to your address (%%addr%%). Check it and copy the given code below.', array('%%addr%%' => $this->form->getValue('email'))));
      $this->redirect('login/recover');
    }
    
    $this->getUser()->setFlash('error', __('Invalid email address, please try again.'));
    //$this->redirect('login/forgot');
    $this->setTemplate('forgot');
  }
  public function executeRecover(sfWebRequest $request)
  {
    $this->form = new LoginForm();
    if ( !($code  = $this->getRecoveryCode())
      || !($email = $this->getRecoveryEmail())
    )
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error', __('Please try again.'));
      $this->redirect('login/forgot');
    }
    
    $this->getUser()->setFlash('success', 'Now, fill your new password twice.');
    if ( $request->hasParameter('code') )
      $this->form->setDefault('recovery_code', $request->getParameter('code', ''));
    $this->form->isRecovering($email, $code);
  }
  public function executeReset(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->form = new LoginForm();

    // precondition
    if ( !($code  = $this->getRecoveryCode())
      || !($email = $this->getRecoveryEmail())
    )
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error', __('Please try again.'));
      $this->redirect('login/forgot');
    }
    
    $this->form->isRecovering($email, $code);
    $this->form->bind(array_merge($request->getParameter('login',array()), array('email' => $email)));
    if ( !$this->form->isValid(false) )
    {
      $this->getUser()->setFlash('error', __('Please try again.'));
      $this->redirect('login/recover');
    }
    
    // everything is correct, make the change
    if ( $this->form->getValue('password') === $this->form->getValue('password_again') )
    {
      $contact = Doctrine::getTable('Contact')->findOneByEmail($this->form->getValue('email'));
      
      $contact->password = $this->form->getValue('password');
      $contact->save();
      
      $this->getUser()->setFlash('success', __('Your password has been changed. Please login now.'));
      $this->resetRecoveryData();
      $this->redirect('login/index');
    }
    
    $this->getUser()->setFlash('error', __('Passwords do not match. Please try again.'));
    $this->redirect('login/recover');
  }
  
  public function executeOut(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->getUser()->logout();
    $this->getUser()->setFlash('notice',__('You have been logged out.'));
    $this->redirect('login/index');
  }
  
  public function executeValidate(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $this->form = new LoginForm();
    $this->form->bind($request->getParameter('login'));
    
    if ( $this->form->isValid() )
    {
      $this->getUser()->setFlash('notice',__('You are authenticated.'));
      return $this->redirect($request->hasParameter('register')
        ? 'cart/register'
        : $this->form->getValue('url_back') ? $this->form->getValue('url_back') : 'homepage');
    }
    
    $this->errors = $this->form->getErrorSchema()->getErrors();
    $this->getUser()->setFlash('error',__('Authentication failure.'));
    $this->setTemplate('index');
  }
  
  public function setRecoveryEmail($email)
  { $this->getUser()->setAttribute('recovery.email', $email, 'pub'); return $this; }
  public function getRecoveryEmail()
  { return $this->getUser()->getAttribute('recovery.email', false, 'pub'); }
  public function setRecoveryCode($code)
  { $this->getUser()->setAttribute('recovery.code', $code, 'pub'); return $this; }
  public function getRecoveryCode()
  { return $this->getUser()->getAttribute('recovery.code', false, 'pub'); }
  public function resetRecoveryData()
  {
    $this->getUser()->getAttributeHolder()->remove('recovery.email', 'pub');
    $this->getUser()->getAttributeHolder()->remove('recovery.code', 'pub');
  }
  
  public function executeCulture(sfWebRequest $request)
  {
    $cultures = array_keys(sfConfig::get('project_internals_cultures', array('fr' => 'Français')));
    
    // culture defined explicitly
    if ( $request->hasParameter('lang') && in_array($request->getParameter('lang'), $cultures) )
    {
      $this->getUser()->setCulture($request->getParameter('lang'));
      $this->getUser()->setAttribute('global_culture_forced', true);
    }
    
    if ( !$this->getUser()->getAttribute('global_culture_forced', false) )
    {
      // all the browser's languages
      $user_langs = array();
      foreach ( $request->getLanguages() as $lang )
      if ( !isset($user_lang[substr($lang, 0, 2)]) )
        $user_langs[substr($lang, 0, 2)] = $lang;
      
      // comparing to the supported languages
      $done = false;
      foreach ( $user_langs as $culture => $lang )
      if ( in_array($culture, $cultures) )
      {
        $done = $culture;
        $this->getUser()->setCulture($culture);
        break;
      }
      
      // culture by default
      if ( !$done )
        $this->getUser()->setCulture($cultures[0]);
    }
    
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->getUser()->setFlash('success', __('Now you are making the experience of e-venement in your favorite language.'));
    $this->redirect('event/index');
  }
}
