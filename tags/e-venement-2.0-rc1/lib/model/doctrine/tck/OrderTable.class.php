<?php

/**
 * OrderTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class OrderTable extends PluginOrderTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object OrderTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Order');
    }
}