<?php

/**
 * BaseManifestation
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $event_id
 * @property integer $location_id
 * @property integer $color_id
 * @property timestamp $happens_at
 * @property integer $duration
 * @property string $description
 * @property decimal $vat
 * @property boolean $seated
 * @property boolean $online
 * @property Event $Event
 * @property Location $Location
 * @property Color $Color
 * @property Doctrine_Collection $Organizers
 * @property Doctrine_Collection $Workspaces
 * @property Doctrine_Collection $ManifestationWorkspaces
 * @property Doctrine_Collection $ManifestationOrganizers
 * @property Doctrine_Collection $Prices
 * @property Doctrine_Collection $PriceManifestations
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseManifestation extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('manifestation');
        $this->hasColumn('event_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('location_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('color_id', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('happens_at', 'timestamp', null, array(
             'type' => 'timestamp',
             'notnull' => true,
             ));
        $this->hasColumn('duration', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             ));
        $this->hasColumn('vat', 'decimal', 5, array(
             'type' => 'decimal',
             'scale' => 2,
             'notnull' => true,
             'default' => 0,
             'length' => 5,
             ));
        $this->hasColumn('seated', 'boolean', null, array(
             'type' => 'boolean',
             'default' => false,
             'notnull' => true,
             ));
        $this->hasColumn('online', 'boolean', null, array(
             'type' => 'boolean',
             'default' => false,
             'notnull' => true,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Event', array(
             'local' => 'event_id',
             'foreign' => 'id',
             'onDelete' => 'RESTRICT',
             'onUpdate' => 'CASCADE'));

        $this->hasOne('Location', array(
             'local' => 'location_id',
             'foreign' => 'id',
             'onDelete' => 'RESTRICT',
             'onUpdate' => 'CASCADE'));

        $this->hasOne('Color', array(
             'local' => 'color_id',
             'foreign' => 'id',
             'onDelete' => 'SET NULL',
             'onUpdate' => 'CASCADE'));

        $this->hasMany('Organism as Organizers', array(
             'refClass' => 'ManifestationOrganizer',
             'local' => 'manifestation_id',
             'foreign' => 'organism_id'));

        $this->hasMany('Workspace as Workspaces', array(
             'refClass' => 'ManifestationWorkspace',
             'local' => 'manifestation_id',
             'foreign' => 'workspace_id'));

        $this->hasMany('ManifestationWorkspace as ManifestationWorkspaces', array(
             'local' => 'id',
             'foreign' => 'manifestation_id'));

        $this->hasMany('ManifestationOrganizer as ManifestationOrganizers', array(
             'local' => 'id',
             'foreign' => 'manifestation_id'));

        $this->hasMany('Price as Prices', array(
             'refClass' => 'PriceManifestation',
             'local' => 'manifestation_id',
             'foreign' => 'price_id'));

        $this->hasMany('PriceManifestation as PriceManifestations', array(
             'local' => 'id',
             'foreign' => 'manifestation_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}