<?php

/**
 * BaseManifestationOrganizer
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $organism_id
 * @property integer $manifestation_id
 * @property Organism $Organism
 * @property Manifestation $Manifestation
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseManifestationOrganizer extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('manifestation_organizer');
        $this->hasColumn('organism_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
        $this->hasColumn('manifestation_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Organism', array(
             'local' => 'organism_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));

        $this->hasOne('Manifestation', array(
             'local' => 'manifestation_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));
    }
}