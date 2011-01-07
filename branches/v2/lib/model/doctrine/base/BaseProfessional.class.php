<?php

/**
 * BaseProfessional
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $name
 * @property integer $organism_id
 * @property integer $contact_id
 * @property integer $professional_type_id
 * @property string $contact_number
 * @property string $contact_email
 * @property string $department
 * @property string $description
 * @property ProfessionalType $ProfessionalType
 * @property Contact $Contact
 * @property Organism $Organism
 * @property Doctrine_Collection $Groups
 * @property Doctrine_Collection $GroupProfessionals
 * 
 * @method string              getName()                 Returns the current record's "name" value
 * @method integer             getOrganismId()           Returns the current record's "organism_id" value
 * @method integer             getContactId()            Returns the current record's "contact_id" value
 * @method integer             getProfessionalTypeId()   Returns the current record's "professional_type_id" value
 * @method string              getContactNumber()        Returns the current record's "contact_number" value
 * @method string              getContactEmail()         Returns the current record's "contact_email" value
 * @method string              getDepartment()           Returns the current record's "department" value
 * @method string              getDescription()          Returns the current record's "description" value
 * @method ProfessionalType    getProfessionalType()     Returns the current record's "ProfessionalType" value
 * @method Contact             getContact()              Returns the current record's "Contact" value
 * @method Organism            getOrganism()             Returns the current record's "Organism" value
 * @method Doctrine_Collection getGroups()               Returns the current record's "Groups" collection
 * @method Doctrine_Collection getGroupProfessionals()   Returns the current record's "GroupProfessionals" collection
 * @method Professional        setName()                 Sets the current record's "name" value
 * @method Professional        setOrganismId()           Sets the current record's "organism_id" value
 * @method Professional        setContactId()            Sets the current record's "contact_id" value
 * @method Professional        setProfessionalTypeId()   Sets the current record's "professional_type_id" value
 * @method Professional        setContactNumber()        Sets the current record's "contact_number" value
 * @method Professional        setContactEmail()         Sets the current record's "contact_email" value
 * @method Professional        setDepartment()           Sets the current record's "department" value
 * @method Professional        setDescription()          Sets the current record's "description" value
 * @method Professional        setProfessionalType()     Sets the current record's "ProfessionalType" value
 * @method Professional        setContact()              Sets the current record's "Contact" value
 * @method Professional        setOrganism()             Sets the current record's "Organism" value
 * @method Professional        setGroups()               Sets the current record's "Groups" collection
 * @method Professional        setGroupProfessionals()   Sets the current record's "GroupProfessionals" collection
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseProfessional extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('professional');
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             ));
        $this->hasColumn('organism_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('contact_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('professional_type_id', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('contact_number', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             ));
        $this->hasColumn('contact_email', 'string', 255, array(
             'type' => 'string',
             'email' => true,
             'length' => 255,
             ));
        $this->hasColumn('department', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             ));
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('ProfessionalType', array(
             'local' => 'professional_type_id',
             'foreign' => 'id',
             'onDelete' => 'SET NULL'));

        $this->hasOne('Contact', array(
             'local' => 'contact_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));

        $this->hasOne('Organism', array(
             'local' => 'organism_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));

        $this->hasMany('Group as Groups', array(
             'refClass' => 'GroupProfessional',
             'local' => 'professional_id',
             'foreign' => 'group_id'));

        $this->hasMany('GroupProfessional as GroupProfessionals', array(
             'local' => 'id',
             'foreign' => 'professional_id'));

        $timestampable0 = new Doctrine_Template_Timestampable(array(
             ));
        $this->actAs($timestampable0);
    }
}