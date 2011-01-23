<?php

/**
 * BaseLocation
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $description
 * @property integer $contact_id
 * @property integer $organism_id
 * @property integer $gauge_max
 * @property integer $gauge_min
 * @property Organism $Organism
 * @property Contact $Contact
 * @property SeatingPlan $SeatingPlan
 * @property Doctrine_Collection $Manifestations
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseLocation extends Addressable
{
    public function setTableDefinition()
    {
        parent::setTableDefinition();
        $this->setTableName('location');
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             ));
        $this->hasColumn('contact_id', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('organism_id', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('gauge_max', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('gauge_min', 'integer', null, array(
             'type' => 'integer',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Organism', array(
             'local' => 'organism_id',
             'foreign' => 'id',
             'onDelete' => 'SET NULL',
             'onUpdate' => 'CASCADE'));

        $this->hasOne('Contact', array(
             'local' => 'contact_id',
             'foreign' => 'id',
             'onDelete' => 'SET NULL',
             'onUpdate' => 'CASCADE'));

        $this->hasOne('SeatingPlan', array(
             'local' => 'id',
             'foreign' => 'location_id'));

        $this->hasMany('Manifestation as Manifestations', array(
             'local' => 'id',
             'foreign' => 'location_id'));

        $searchable0 = new Doctrine_Template_Searchable(array(
             'fields' => 
             array(
              0 => 'name',
              1 => 'address',
              2 => 'city',
             ),
             ));
        $this->actAs($searchable0);
    }
}