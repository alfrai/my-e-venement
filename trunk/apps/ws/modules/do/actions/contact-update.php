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

    if ( isset($request->getParameter('debug')) )
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
    $client['description'] = 'e-voucher';
    
    $form = new ContactForm();
    $form->setStrict();
    $form->bind($client);
    
    // the contact itself
    if ( !$form->isValid() )
    {
      $this->getResponse()->setStatusCode('412');
      return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
    }
    
    $contact = Doctrine::getTable('Contact')->createQuery('c')
      ->andWhere('email ILIKE ?',trim($client['email']))
      ->andWhere('name ILIKE ?',trim($client['name']))
      ->andWhere('firstname ILIKE ?',trim($client['firstname']))
      ->orderBy('updated_at DESC')
      ->limit(1)
      ->fetchOne();
    
    if ( $contact )
    {
      $client['id'] = $contact->id;
      $client['description'] .= ' '.trim($contact->description);
    }
    
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
    
    // storing the client ids in the current user
    $contact = $form->getObject();
    $this->getUser()->setAttribute('contact_id',$contact->id);
    
    // the response status
    $this->getResponse()->setStatusCode('201'); // 200 ?
    return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
