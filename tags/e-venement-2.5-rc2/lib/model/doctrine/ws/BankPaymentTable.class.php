<?php

/**
 * BankPaymentTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class BankPaymentTable extends PluginBankPaymentTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object BankPaymentTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('BankPayment');
    }
}