<?php

/**
 * ContactTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ContactTable extends PluginContactTable
{
  public function createQueryByEmailId($id)
  {
    $q = $this->createQuery();
    $a = $q->getRootAlias();
    $q->leftJoin("$a.Emails ce")
      ->leftJoin("p.Emails pe")
      ->andWhere('(TRUE')
      ->andWhere('ce.sent = TRUE')
      ->andWhere('ce.id = ?',$id)
      ->orWhere('pe.id = ?',$id)
      ->andWhere('pe.sent = TRUE')
      ->andWhere('TRUE)')
      ->orderby('name','firstname');
    return $q;
  }
  
  public function createQueryByGroupId($id)
  {
    $q = $this->createQuery();
    $a = $q->getRootAlias();
    $q->leftJoin("$a.Groups gc")
      ->leftJoin("p.Groups gp")
      ->where('gc.id = ?',$id)
      ->orWhere('gp.id = ?',$id)
      ->orderby('name','firstname');
    return $q;
  }
  
  public function createQuery($alias = 'a')
  {
    $p  = 'p'   != $alias ? 'p'   : 'p1';
    $pt = 'pt'  != $alias ? 'pt'  : 'pt1';
    $o  = 'o'   != $alias ? 'o'   : 'o1';
    $gp = 'gp'  != $alias ? 'gp'  : 'gp1';
    $gc = 'gc'  != $alias ? 'gc'  : 'gc1';
    $gpu = 'gpu'!= $alias ? 'gpu' : 'gpu1';
    $gcu = 'gcu'!= $alias ? 'gcu' : 'gcu1';
    $u  = 'u'   != $alias ? 'u'   : 'u1';
    $pn = 'pn'  != $alias ? 'pn'  : 'pn1';
    $y  = 'y'   != $alias ? 'y'   : 'y1';
    
    $query = parent::createQuery($alias)
      ->leftJoin("$alias.Professionals $p")
      ->leftJoin("$p.ProfessionalType $pt")
      //->leftJoin("$p.Groups $gp")
      //->leftJoin("$gp.User $gpu")
      ->leftJoin("$p.Organism $o")
      //->leftJoin("$alias.Groups $gc")
      //->leftJoin("$gc.User $gcu")
      ->leftJoin("$alias.Phonenumbers $pn")
      ->leftJoin("$alias.YOBs $y");

    return $query;
  }

  public function findWithTickets($id)
  {
    $q = $this->createQuery('c')
      ->leftJoin("c.Transactions transac")
      ->leftJoin('transac.Payments payment')
      /*
      ->leftJoin('transac.Tickets tck ON transaction.id = tck.transaction_id AND tck.id NOT IN (SELECT tck2.cancelling FROM ticket tck2 WHERE tck2.cancelling IS NOT NULL) AND tck.duplicate IS NULL')
      ->leftJoin('tck.Manifestation manifestation')
      ->leftJoin('manifestation.Event event')
      */
      ->andWhere('c.id = ?',$id);
    $contacts = $q->execute();
    return $contacts[0];
  }
  
  public function doSelectOnlyGrp(Doctrine_Query $q)
  {
    $a = $q->getRootAlias();
    $q->leftJoin("p.ContactEntries ce")
      ->andWhere('ce.id IS NOT NULL');
    return $q;
  }
  
    /*
     * Returns an instance of this class.
     *
     * @return object ContactTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Contact');
    }
}
