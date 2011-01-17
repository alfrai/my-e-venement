<?php

/**
 * PluginEmail
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginEmail extends BaseEmail
{
  public $not_a_test   = false;
  public $test_address = NULL;
  public $mailer       = NULL;
  public $to       = array();
  
  protected function send()
  {
    $this->to = array();
    
    // sending one by one to linked ...
    // contacts
    foreach ( $this->Contacts as $contact )
    if ( $contact->email )
      $this->to[] = $contact->email;
    // professionals
    foreach ( $this->Professionals as $pro )
    if ( $pro->contact_email )
      $this->to[] = $pro->contact_email;
    // organisms
    foreach ( $this->Organisms as $organism )
    if ( $organism->email )
      $this->to[] = $organism->email;
    
    // concatenate addresses
    /*
    if ( $this->field_to )
      $this->to = array_merge($this->to,explode(',',str_replace(' ','',$this->field_to)));
    */
    $this->field_to = implode(', ',$this->to);
    return $this->raw_send();
  }

  protected function sendTest()
  {
    if ( !$this->test_address )
      return false;
    
    return $this->raw_send(array($this->test_address),true);
  }
  
  protected function raw_send($to = array(), $immediatly = false)
  {
    $to = is_array($to) && count($to) > 0 ? $to : $this->to;
    if ( !$to )
      return false;
    
    $message = $this->compose(Swift_Message::newInstance()->setTo($to));
    
    return $immediatly === true
      ? $this->mailer->sendNextImmediately()->send($message)
      : $this->mailer->batchSend($message);
  }

  protected function compose(Swift_Message $message)
  {
    return $message
      ->setFrom($this->field_from)
      ->setSubject($this->field_subject)
      ->setBody($this->content,'text/html');
  }
}
