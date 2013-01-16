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
  /**
    * create a totally new account for the current client
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    *   - mod : if set, the system will try to modify the account in database based on name and email
    * POST params: a var "json" containing this kind of json content
    *   - lastname: le nom de famille (required)
    *   - firstname: le prénom (required)
    *   - address:
    *   - postal: postal code
    *   - city:
    *   - country:
    *   - email: email (required)
    *   - tel:
    *   - passwd: the client md5(passwd) (already encrypted, required)
    * Returns :
    *   - HTTP return code
    *     . 201 if a new client account has been created
    *     . 403 if authentication as a valid webservice has failed
    *     . 406 if the json content doesn't embed the required values
    *     . 412 if the json array is not conform with its checksum
    *     . 500 if there was a problem processing the demand
    *
    **/
  public function executeAccount(sfWebRequest $request)
  {
    try { $this->authenticate($request); }
    catch ( sfSecurityException $e )
    {
      $this->getResponse()->setStatusCode('403');
      return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
    }
    
    if ( isset($request->getParameter('debug')) )
      print_r(json_decode($request->getParameter('json'),true));
    
    // preconditions
    try {
      $client = wsConfiguration::getData($json);
    }
    catch ( sfSecurityException $e )
    {
      $this->getResponse()->setStatusCode('412');
      return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
    }
    
    foreach ( array(
      'name' => 'lastname',
      'postal' => 'postalcode',
      'telephone' => 'phonenumber') as $from => $to )
    {
      $client[$from] = $client[$to];
      unset($client[$from]);
    }
    $phonenumber = $client['phonenumber'];
    unset($client['phonenumber']);
    
    // to for capitalization of some fields
    if ( is_array($opts = sfConfig::get('app_infos_capitalize',array())) )
    foreach ( $opts as $field )
      $client[$field] = mb_strtoupper($client[$field],'UTF_8');
    
    $form = new ContactForm();
    $form->setStrict();
    $form->bind($client);
    
    // the contact itself
    if ( !$form->isValid() )
    {
      $this->getResponse()->setStatusCode('412');
      return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
    }
    
    $contacts = Doctrine::getTable('Contact')->createQuery('c')
      ->andWhere('email ILIKE ?',$client['email'])
      ->andWhere('name ILIKE ?',$client['name'])
      ->orderBy('updated_at DESC')
      ->limit(1)
      ->execute();
    $contact = $contacts[0];
    $client['id'] = $contact->id;
    
    $form->bind($client);
    $form->save();
    
    $new_phone = false;
    foreach ( $contact->Phonenumbers as $phone )
    if ( str_replace(' ','',$phone->number) == str_replace(' ','',$phonenumber) )
      $new_phone = true;
    
    // the phone number
    if ( $new_phone )
    {
      $phone = new ContactPhonenumber();
      $phone->number = $phonenumber;
      $phone->contact_id = $form->getObject()->id;
      $phone->save();
    }
    
    // the response status
    $this->getResponse()->setStatusCode('201');
    return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
  }

  protected function authenticate(sfWebRequest $request)
  {
    return wsConfiguration::authenticate($request);
  }
}
