<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

  /**
    * creates or updates a contact file
    * updates a transaction with a given contact
    * only available if $config['vel']['resa-noid'] is set
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    * POST params: a var "json" containing this kind of json content
    *   - user: a json array describing the contact/user (see in the code for sample)
    *   - transaction: the transaction id concerned
    * Returns :
    *   - HTTP return code
    *     . 200 if the contact has been well updated
    *     . 403 if authentication as a valid webservice has failed
    *     . 406 if the input POST content doesn't embed the required values
    *     . 410 if the user's content is not ok
    *     . 412 if the input user's json content doesn't pass its checksum
    *     . 500 if there was a problem processing the demand
    *
    **/

    try { $this->authenticate($request); }
    catch ( sfException $e )
    {
      $this->getResponse()->setStatusCode('403');
      return sfView::NONE;
    }
    
    if ( $request->hasParameter('debug') )
      print_r(json_decode($request->getParameter('user'),true));
    
    // preconditions
    try {
      $client = wsConfiguration::getData($request->getParameter('user'));
    }
    catch ( sfSecurityException $e )
    {
      $this->getResponse()->setStatusCode('412');
      return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
    }
    
  /**
    * ex of input user array :
    * array(
    *   firstname => string,
    *   lastname => string,
    *   email => string,
    *   address => string,
    *   postal => string,
    *   city => string,
    *   [...]
    * );
    *
    **/
    
    $c = array();
    foreach ( array(
      'email' => 'email',
      'firstname' => 'firstname',
      'address' => 'address',
      'city' => 'city',
      'country' => 'country',
      'lastname' => 'name',
      'postal' => 'postalcode',
    ) as $from => $to )
      $c[$to] = trim($client[$from]);
    $phonenumber = trim($client['telephone']);
    $c['description'] = 'e-voucher';
    $c['confirmed'] = true;
    $c['family_contact'] = true;

    // to for capitalization of some fields
    if ( is_array($opts = sfConfig::get('app_infos_capitalize',array())) )
    foreach ( $opts as $field )
      $c[$field] = mb_strtoupper($c[$field],'UTF-8');
    
    $form = new ContactForm;
    $c[$form->getCSRFFieldName()] = $form->getCSRFToken();
    $form->setStrict();
    $form->bind($c);
    
    // the contact itself
    if ( !$form->isValid() )
    {
      $this->form = $form;
      $this->getResponse()->setStatusCode('410');
      return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
    }
    
    $contact = Doctrine::getTable('Contact')->createQuery('c')
      ->andWhere('trim(email) ILIKE ?',trim($c['email']))
      ->andWhere('trim(name) ILIKE ?',trim($c['name']))
      ->andWhere('trim(firstname) ILIKE ?',trim($c['firstname']))
      ->orderBy('updated_at DESC')
      ->fetchOne();
    
    if ( $contact )
    {
      unset($c[$form->getCSRFFieldName()]);
      foreach ( $c as $key => $value )
        $contact->$key = $value;
      $c['description'] .= ' '.trim($contact->description);
      $contact->save();
    }
    else
    {
      $form->save();
      $contact = $form->getObject();
    }
    
    $new_phone = $contact->Phonenumbers->count() == 0; // if 0 number -> true / if more -> false
    foreach ( $contact->Phonenumbers as $phone )
    if ( str_replace(' ','',$phone->number) == str_replace(' ','',$phonenumber) )
      $new_phone = true;
    
    // the phone number
    if ( $new_phone )
    {
      $phone = new ContactPhonenumber();
      $phone->number = $phonenumber;
      $phone->contact_id = $contact->id;
      $phone->save();
    }
    
    // the automatic groups
    $groups = Doctrine::getTable('Group')->createQuery('g')
      ->leftJoin('g.Online o')
      ->andWhere('o.id IS NOT NULL')
      ->execute();
    foreach ( $groups as $group )
      $contact->Groups[] = $group;
    $contact->save();
    
    // storing the client ids in the current user
    $this->getUser()->setAttribute('contact_id',$contact->id);
    
    // updating the current transaction
    if ( $transaction = Doctrine::getTable('Transaction')
      ->findOneById($this->getUser()->getAttribute('transaction_id'),$this->getUser()->getAttribute('old_transaction_id')) )
    {
      $transaction->Contact = $contact;
      $transaction->sf_guard_user_id = $this->getUser()->getAttribute('ws_id');
      $transaction->save();
    }
    
    // the response status
    $this->getResponse()->setStatusCode('201'); // 200 ?
    return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
