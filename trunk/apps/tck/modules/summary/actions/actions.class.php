<?php

require_once dirname(__FILE__).'/../lib/summaryGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/summaryGeneratorHelper.class.php';

/**
 * summary actions.
 *
 * @package    e-venement
 * @subpackage summary
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class summaryActions extends autoSummaryActions
{
  protected $type = 'debts';
  
  public function executeIndex(sfWebRequest $request)
  {
    $this->type = $this->type ? $this->type : 'debts';
    parent::executeIndex($request);
  }
  public function executeDuplicatas(sfWebRequest $request)
  {
    $this->type = 'duplicatas';
    $this->executeIndex($request);
    $this->setTemplate('index');
  }
  
  public function executeDebts(sfWebRequest $request)
  {
    if ( $request->hasParameter('all') )
      $this->type = 'all';
    $this->executeIndex($request);
    $this->setTemplate('index');
  }
  public function executeAsks(sfWebRequest $request)
  {
    $this->type = 'asks';
    $this->executeIndex($request);
    $this->setTemplate('index');
  }
  
  public function buildQuery()
  {
    $q = parent::buildQuery();
    $t = $q->getRootAlias();
    
    $q->andWhere('tck.id IS NOT NULL')
      ->leftJoin("$t.Contact c")
      ->leftJoin("$t.Professional p")
      ->leftJoin("$t.User u")
      ->leftJoin("$t.Payments pay")
      ->leftJoin('p.Organism o');

    switch ( $this->type ) {
    case 'asks':
      $q->andWhere('tck.printed = FALSE')
        ->andWhere('tck.duplicate IS NULL');
      break;
    case 'duplicatas':
      $q->andWhere('tck.duplicate IS NOT NULL')
        ->andWhere('tck.printed = TRUE');
      break;
    case 'debts':
      // debts
      $rq = new Doctrine_RawSql();
      $rq->select('t.id')
        ->from('Transaction t')
        ->addComponent('t','Transaction')
        ->andWhere("(SELECT SUM(value) FROM Ticket WHERE transaction_id = t.id AND printed AND duplicate IS NULL) > (SELECT SUM(value) FROM Payment WHERE transaction_id = t.id)");
      $ids = $rq->execute(array(),Doctrine::HYDRATE_NONE);
      foreach ( $ids as $key => $id )
        $ids[$key] = $id[0];
      $q->andWhereIn("$t.id",$ids)
        ->andWhere('tck.printed = TRUE');
    default:
      // all transactions
      break;
    }
    
    return $q;
  }
}
