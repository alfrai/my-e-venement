<?php

/**
 * SeatedPlanZoneTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class SeatedPlanZoneTable extends PluginSeatedPlanZoneTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object SeatedPlanZoneTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('SeatedPlanZone');
    }
}