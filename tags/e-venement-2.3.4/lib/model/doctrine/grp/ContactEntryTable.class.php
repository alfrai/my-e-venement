<?php

/**
 * ContactEntryTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ContactEntryTable extends PluginContactEntryTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object ContactEntryTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('ContactEntry');
    }
}