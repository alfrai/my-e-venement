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

  class liCardDavVCardContact extends liCardDavVCard
  {
    protected $contact = NULL;
    
    /**
     * function getContact()
     * retrieve the contact linked to the current liCardDavVCard
     * updates the current self::contact
     *
     * @return Contact new Contact if UID not present in the DB, existing Contact if found, FALSE if self::id is not set
     *
     */
    public function getContact()
    {
      if ( !$this->id )
        return false;
      
      if ( $this->contact instanceof Contact )
        return $this->contact;
      
      if (!( $this->contact = Doctrine::getTable('Contact')->findOneByUid($this->id) ))
      {
        $this->contact = new Contact;
        $this->contact->uid = $this->id;
      }
      
      return $this->contact;
    }

/**
 * function updateContact()
 * Saves the current liCardDavVCard into its e-venement's peer
 * Updates the linked contact or creates a new one if it does not exist
 *
 * @return Contact or FALSE if the self::id is not set
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
    public function updateContact()
    {
      if ( !$this->getContact() )
        return false;
      
      // easy translations
      $translations = array(
        'n'     => array(
          'LastName'  => 'name',
          'FirstName' => 'firstname',
          'Prefixes'  => 'title',
          'StreetAddress' => 'address',
          'Locality'  => 'city',
          'PostalCode' => 'postalcode',
          'Country'   => 'country',
        ),
        'rev'   => 'updated_at',
        'note'  => 'description',
        'uid'   => 'vcard_uid',
      );
      
      foreach ( $translations as $vcf_field => $subfields )
      {
        if ( is_array($subfields) )
        foreach ( $subfields as $subfield => $translation )
          $this->contact->$translation = $this->sanitizeNLForContacts($this[$vcf_field][$subfield]);
        else
          $this->contact->$subfields = $this->sanitizeNLForContacts($this[$vcf_field]);
      }
      
      //echo $this->contact->toArray();
      return $this;
    }
    
    public function saveContact()
    {
      if ( !$this->getContact() )
        throw new liCardDavException('No contact can be used to store data in the DB');
      
      $this->contact->save();
      return $this;
    }
    
    protected sanitizeNLForContacts($str)
    {
      return str_replace('\n',"\n",$str);
    }
  }
