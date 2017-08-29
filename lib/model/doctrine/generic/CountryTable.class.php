<?php

/**
 * CountryTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class CountryTable extends PluginCountryTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object CountryTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Country');
    }
    
    public function retrieveNationalities()
    {
      $culture = sfContext::hasInstance() ? sfContext::getInstance()->getUser()->getCulture() : 'fr';
      
      $q = parent::createQuery('c')
        ->innerJoin('c.Translation ct WITH ct.lang = ?', $culture)
        ->andWhere('ct.nationality IS NOT NULL')
      ;
      
      return $q;
    }
}