<?php

/**
 * TraceableTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class TraceableTable extends PluginTraceableTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object TraceableTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Traceable');
    }
}