<?php

/**
 * EntryTicketsTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class EntryTicketsTable extends PluginEntryTicketsTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object EntryTicketsTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('EntryTickets');
    }
}