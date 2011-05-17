<?php

/**
 * TransactionTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class TransactionTable extends PluginTransactionTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object TransactionTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Transaction');
    }
  
  public function createQuery($alias = 't')
  {
    $tck = 'tck' != $alias ? 'tck' : 'tck2';
    $m   = 'm'   != $alias ? 'm'   : 'm2';
    
    $q = parent::createQuery($alias);
    $a = $q->getRootAlias();
    $q->leftJoin("$a.Tickets $tck")
      ->leftJoin("$tck.Manifestation $m");
    return $q;
  }
  
  public function findOneById($id)
  {
    $q = Doctrine::getTable('Transaction')->createQuery()
      ->andWhere('id = ?',$id);
    $tmp = $q->execute();
    return $tmp[0];
  }
}
