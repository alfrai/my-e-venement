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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

/**
 * liVCardContact binds a vCard with an e-venement Contact. It is optimized for the Zimbra data structure but fits to the vCard format.
 *
 * Reversible fields:
 *  * n:LastName / Contact::name
 *  * n:;FirstName / Contact::firstname
 *  * n:;;;Prefixes / Contact::title
 *  * adr:;;StreetAddress / Contact::address
 *  * adr:;;;Locality / Contact::address
 *  * adr:;;;;;PostalCode / Contact::postalcode
 *  * adr:;;;;;;Country / Contact::country
 *  * tel: / Contact::Phonenumbers -- with smart/random updates from CardDAV to e-venement
 *  * email: / Contact::email -- with smart/random updates from CardDAV to e-venement (under the condition that orders have not changed or changes are understandable)
 *  * rev: / Contact::updated_at
 *  * note: / Contact::description
 *  * uid: / Contact::vcard_uid
 *
 * Non-reversible fields (will be resetted on every change in the e-venement datas)
 *  * org:
 *  * adr:;;;;Region
 *  * adr:TYPE=WORK
 *  * fn:
 *
 */
  class liVCardContact extends liVCard
  {
    protected $contact;
    protected $timezone_hack = false;
    
    public function __construct(Contact $contact, $options = NULL)
    {
      // a hack for bad time-confirgured CardDAV repositories
      if ( isset($options['timezone_hack']) )
      {
        $this->timezone_hack = $options['timezone_hack'];
        unset($options['timezone_hack']);
      }
      
      $this->setOptions($options);
      $this->contact = $contact;
      
      $this['fn'] = (string)$contact;
      $this['n']  = array(
        'LastName'  => $contact->name,
        'FirstName' => $contact->firstname,
        'Prefixes'  => $contact->title,
      );
      
      $str = '';
      foreach ( $contact->Professionals as $pro )
        $this['org'] = array(
          'Name' => (string)$pro->Organism,
          'Unit1' => $pro->department,
          'Unit2' => $pro->name_type,
        );
      if ( $contact->Professionals->count() > 0 )
        $this['title'] = $contact->Professionals[0]->name_type;
      
      $this['adr'] = array(
        'StreetAddress' => $this->sanitizeNLForVcf($contact->address),
        'Locality'      => $contact->city,
        'PostalCode'    => $contact->postalcode,
        'Region'        => $contact->region,
        'Country'       => $contact->country,
        'Type'          => array('home', 'postal', 'parcel'),
      );
      foreach ( $contact->Professionals as $pro )
      if ( $pro->Organism->address || $pro->Organism->postalcode || $pro->Organism->city || $pro->Organism->country )
      $this['adr'] = $arr = array(
        'ExtendedAddress' => '',
        'POBox'         => '',
        'StreetAddress' => $this->sanitizeNLForVcf(implode("\n", array($pro->Organism->name, $pro->Organism->address,))),
        'Locality'      => $pro->Organism->city,
        'PostalCode'    => $pro->Organism->postalcode,
        'Country'       => $pro->Organism->country,
        'Type'          => array('work', 'postal', 'parcel'),
      );
      
      // tel perso
      foreach ( $contact->Phonenumbers as $pn )
      if ( trim($pn->number) )
      $this['tel'] = array(
        'Value' => $pn->number,
        'Type' => array(
          'home',
          stripos($pn->name, 'fax') !== false ? 'fax' : 'voice',
        ),
      );
      // tel pro
      foreach ( $contact->Professionals as $pro )
      if ( trim($pro->contact_number) )
      $this['tel'] = array(
        'Value' => $pro->contact_number,
        'Type' => array(
          'work',
          'voice',
        ),
      );
      
      if ( trim($contact->email) )
      $this['email'] = array(
        'Value' => $contact->email,
        'Type'  => array('pref','internet'),
      );
      foreach ( $contact->Professionals as $pro )
      {
        if ( trim($pro->contact_email) )
        $this['email'] = array(
          'Value' => $pro->contact_email,
          'Type'  => array('work','internet'),
        );
        if ( trim($pro->Organism->url) )
        $this['url'] = $pro->Organism->url;
      }
      
      if ( $this->timezone_hack )
      {
        $time = strtotime($contact->updated_at);
        $this['rev'] = date('Y-m-d',$time).'T'.date('H:i:s',$time).'Z';
      }
      else
        $this['rev'] = $contact->updated_at_iso_8601;
      
      $this['note'] = $contact->description;
      
      // END
    }
    
    protected function sanitizeNLForVcf($str)
    {
      return str_replace(array("\n","\r"),array('\n',''),$str);
    }
  }
