<?php

/**
 * MemberCardTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class MemberCardTable extends PluginMemberCardTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object MemberCardTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('MemberCard');
    }
}