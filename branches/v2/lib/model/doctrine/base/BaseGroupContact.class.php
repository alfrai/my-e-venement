<?php

/**
 * BaseGroupContact
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $contact_id
 * @property Contact $Contact
 * 
 * @method integer      getContactId()  Returns the current record's "contact_id" value
 * @method Contact      getContact()    Returns the current record's "Contact" value
 * @method GroupContact setContactId()  Sets the current record's "contact_id" value
 * @method GroupContact setContact()    Sets the current record's "Contact" value
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseGroupContact extends GroupDetail
{
    public function setTableDefinition()
    {
        parent::setTableDefinition();
        $this->setTableName('group_contact');
        $this->hasColumn('contact_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Contact', array(
             'local' => 'contact_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));
    }
}