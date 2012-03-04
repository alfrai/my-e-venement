<?php

/**
 * ProfessionalTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ProfessionalTable extends PluginProfessionalTable
{
  public function createQuery($alias = 'p')
  {
    $o = $alias != 'o' ? 'o' : 'o1';
    $c = $alias != 'c' ? 'c' : 'c1';
    $t = $alias != 't' ? 't' : 't1';
    
    $query = parent::createQuery($alias)
        ->leftJoin("$alias.Organism $o")
        ->leftJoin("$alias.ProfessionalType $t")
        ->leftJoin("$alias.Contact $c")
        ->orderBy("$c.name, $c.firstname, $o.name, $alias.name, $t.name");
    return $query;
  }

    /**
     * Returns an instance of this class.
     *
     * @return object ProfessionalTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Professional');
    }
}
