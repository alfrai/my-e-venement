<?php

/**
 * ManifestationTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ManifestationTable extends PluginManifestationTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object ManifestationTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Manifestation');
    }
  
  public function createQuery($alias = 'm', $light = false)
  {
    $e  = $alias != 'e'  ? 'e'  : 'e1';
    $me = $alias != 'me' ? 'me' : 'me1';
    $l  = $alias != 'l'  ? 'l'  : 'l1';
    $pm = $alias != 'pm' ? 'pm' : 'pm1';
    $p  = $alias != 'p'  ? 'p'  : 'p1';
    $g  = $alias != 'g'  ? 'g'  : 'g1';
    $t  = $alias != 't'  ? 't'  : 't1';
    $o  = $alias != 'o'  ? 'o'  : 'o1';
    $c  = $alias != 'c'  ? 'c'  : 'c1';
    $w  = $alias != 'w'  ? 'w'  : 'w1';
    $wuo = $alias != 'wuo' ? 'wuo' : 'wuo1';
    $tck = $alias != 'tck' ? 'tck' : 'tck1';
    $tr = $alias != 'tr'  ? 'tr' : 'tr1';
    $wu = $alias != 'wu'  ? 'wu' : 'wu1';
    $meu = $alias != 'meu' ? 'meu' : 'meu1';
    
    $q = parent::createQuery($alias)
      ->leftJoin("$alias.Event $e")
      ->leftJoin("$e.MetaEvent $me")
      ->leftJoin("$alias.Location $l");
    
    if ( !$light )
    {
      $uid = sfContext::hasInstance()
        ? intval(sfContext::getInstance()->getUser()->getId())
        : 0;
      
      $q->leftJoin("$alias.PriceManifestations $pm")
        ->leftJoin("$pm.Price $p")
        ->leftJoin("$alias.Gauges $g")
        ->leftJoin("$g.Workspace $w")
        ->leftJoin("$alias.Organizers $o")
        ->orderBy("$e.name, $me.name, $alias.happens_at, $alias.duration, $w.name");
      if ( $uid )
      $q->leftJoin("$w.Order $wuo ON $wuo.workspace_id = $w.id AND $wuo.sf_guard_user_id = ".$uid)
        ->orderBy("$e.name, $me.name, $alias.happens_at, $alias.duration, $wuo.rank")
        ->leftJoin("$w.Users $wu")
        ->leftJoin("$me.Users $meu")
        ->andWhere("$meu.id = ? AND $wu.id = ?",array($uid,$uid));
      
      //if ( sfContext::hasInstance() && $uid = sfContext::getInstance()->getUser()->getId() )
      //  $q->andWhere("$pm.id IS NULL OR $pm.price_id IN (SELECT price_id FROM UserPrice up WHERE up.user_id = ?)",$uid);
    }
    
    return $q;
  }

  public function createQueryByEventId($id)
  {
    $q = $this->createQuery();
    $a = $q->getRootAlias();
    $q
      ->where('e.id = ?',$id)
      ->orderby("e.name, $a.happens_at DESC, l.name");
    return $q;
  }
  public function createQueryByLocationId($id)
  {
    $q = $this->createQuery();
    $a = $q->getRootAlias();
    $q
      ->where('l.id = ?',$id)
      ->orderby("e.name, $a.happens_at DESC, l.name");
    return $q;
  }
  
  public function fetchOneByGaugeId($id)
  {
    return $this->createQuery('m')->andWhere('g.id = ?',$id)->fetchOne();
  }
}
