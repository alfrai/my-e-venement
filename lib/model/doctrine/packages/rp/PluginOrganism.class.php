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
 * PluginOrganism
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginOrganism extends BaseOrganism
{
  public function getIndexesPrefix()
  {
    return strtolower(get_class($this));
  }
  
/**
 * function getVCard()
 * generates a vCard from Contact $this
 * It is optimized for the Zimbra data structure but fits to the vCard standard.
 *
 * Reversible fields:
 *  * n:LastName / Contact::name
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
 *  * n:;FirstName
 *  * adr:;;;;Region
 *  * adr:TYPE=WORK
 *  * fn:
 *
 */
  /**
   * function getVcard()
   * @return liVCard matching $this
   **/
  public function getVcard($dummy = NULL)
  {
    $vCard = parent::getVcard(true);
    
    $vCard['title'] = (string)$this->Category;
    
    // tel perso
    foreach ( $this->Phonenumbers as $pn )
    if ( trim($pn->number) )
    $vCard['tel'] = array(
      'Value' => $pn->number,
      'Type' => array(
        'work',
        stripos($pn->name, 'fax') !== false ? 'fax' : 'voice',
      ),
    );
    
    $vCard['url'] = $this->url;
    
    // description
    $vCard['note'] = $this->description;
    
    // END
    return $vCard;
  }
}
