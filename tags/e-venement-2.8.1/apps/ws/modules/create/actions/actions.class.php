<?php

/**
 * create actions.
 *
 * @package    e-venement
 * @subpackage create
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class createActions extends sfActions
{
  /**
    * pre-login as a client
    * (distinct from the real "authentication" of the distant system, that's why we told it 'identification'
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    * POST params:
    *   - json : a json array containing all the information needed (note that the password has to be sent already md5'ized
    * Returns :
    *   - HTTP return code
    *     . 500 if there was a problem processing the demand
    *     . 403 if authentication as a valid webservice has failed
    *     . 412 if all the required arguments have not been sent
    *     . 201 if a new client account has been created
    *
    **/
  public function executeCreateClient(sfWebRequest $request)
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
    
    $form = new ContactForm();
    $form->setStrict();
    $form->bind($client);
    
    // the contact itself
    if ( !$form->isValid() )
    {
      $this->getResponse()->setStatusCode('412');
      return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
    }
    $form->save();
    
    // the phone number
    $phone = new ContactPhonenumber();
    $phone->number = $phonenumber;
    $phone->contact_id = $form->getObject()->id;
    $phone->save();
    
    // the response status
    $this->getResponse()->setStatusCode('201');
    return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
  }

  protected function authenticate(sfWebRequest $request)
  {
    return wsConfiguration::authenticate($request);
  }
}
