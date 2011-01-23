<?php

/**
 * BaseEventCompany
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $organism_id
 * @property integer $event_id
 * @property Organism $Organism
 * @property Event $Event
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseEventCompany extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('event_company');
        $this->hasColumn('organism_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
        $this->hasColumn('event_id', 'integer', null, array(
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

        $this->hasOne('Event', array(
             'local' => 'event_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));
    }
}