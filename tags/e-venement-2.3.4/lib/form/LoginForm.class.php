<?php

/**
 * Login form for public app.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class LoginForm extends BaseForm
{
  public function configure()
  {
    $this->widgetSchema->setNameFormat('login[%s]');
    $this->disableCSRFProtection();
    
    $this->widgetSchema   ['email'] = new sfWidgetFormInputText();
    $this->validatorSchema['email'] = new sfValidatorEmail();
    
    $this->widgetSchema   ['password'] = new sfWidgetFormInputPassword();
    $this->validatorSchema['password'] = new sfValidatorString();
    
    parent::configure();
  }
  
  public function isValid()
  {
    if ( !parent::isValid() )
      return false;
    
    $contact = Doctrine_Query::create()->from('Contact c')
      ->where('c.email = ?',$this->getValue('email'))
      ->andWhere('c.password = ? AND c.password != ?',array($this->getValue('password'),''))
      ->orderBy('c.id')
      ->fetchOne();
    
    if ( $contact )
      sfContext::getInstance()->getUser()->setContact($contact);
    
    return $contact ? true : false;
  }
}
