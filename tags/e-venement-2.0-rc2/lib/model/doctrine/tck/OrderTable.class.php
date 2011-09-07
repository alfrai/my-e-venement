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
    
    public function doSelectIndex(Doctrine_Query $q)
    {
      $o = $q->getRootAlias();
      $q->leftJoin("$o.Transaction t")
        ->leftJoin('t.Tickets tck')
        ->leftJoin('tck.Manifestation m')
        ->leftJoin('m.Location l')
        ->leftJoin('m.Event e')
        ->leftJoin('m.Workspaces w')
        ->leftJoin('e.MetaEvent me')
        ->leftJoin('e.EventCategory ec')
        ->leftJoin('t.Contact c')
        ->leftJoin('t.Professional p')
        ->leftJoin('p.Organism org')
        ->andWhere('NOT t.closed');
      return $q;
    }
}
