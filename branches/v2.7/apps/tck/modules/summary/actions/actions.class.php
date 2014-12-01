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
  
  public function executeFilter(sfWebRequest $request)
  {
    $this->type = $request->getParameter('type');
    
    $this->hasFilters = $this->getUser()->getAttribute('contact.filters', $this->configuration->getFilterDefaults(), 'admin_module');
    $this->setPage(1);

    if ($request->hasParameter('_reset'))
    {
      $this->setFilters($this->configuration->getFilterDefaults());
      $this->redirect($this->type ? 'summary/'.$this->type : '@transaction');
    }

    $this->filters = $this->configuration->getFilterForm($this->getFilters());

    $this->filters->bind($request->getParameter($this->filters->getName()));
    if ($this->filters->isValid())
    {
      $this->setFilters($this->filters->getValues());
      $this->redirect($this->type ? 'summary/'.$this->type : '@transaction');
    }

    $this->pager = $this->getPager();
    $this->sort = $this->getSort();

    $this->setTemplate('index');

    parent::executeFilter($request);
  }
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
    $this->class = 'asks';
    $this->executeIndex($request);
    $this->setTemplate('index');
  }
  public function executeDeleteDemands(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    Doctrine::getTable('Ticket')->createQuery('tck')
      ->delete()
      ->andWhere('tck.transaction_id = ?',$request->getParameter('id'))
      ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL')
      ->andWhere('tck.transaction_id NOT IN (SELECT o.transaction_id FROM Order o)')
      ->execute();
      
    $this->getUser()->setFlash('notice',__('Demands deleted properly'));
    $this->redirect('summary/asks');
  }
  
  public function executeSearch(sfWebRequest $request)
  {
    $this->type = false;
    parent::executeIndex($request);
    
    $q = array('Contact' => NULL, 'Organism' => NULL);
    $cpt = 0;
    foreach ( $q as $tname => $query )
    {
      $cpt++;
      $table = Doctrine::getTable($tname);
      $search = $this->sanitizeSearch($s = $request->getParameter('s'));
      $transliterate = sfConfig::get('software_internals_transliterate',array());
      $q[$tname] = $table->search($search.'*',Doctrine_Query::create()->from($tname.' tt'.$cpt));
      $q[$tname]->select("tt$cpt.id");
    }
    
    $a = $this->pager->getQuery()->getRootAlias();
    $this->pager->getQuery()->andWhere("$a.contact_id IN (".$q['Contact'].") OR p.organism_id IN (".$q['Organism'].")",array($s,$s));
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
    
    $this->setTemplate('index');
  }
  public static function sanitizeSearch($search)
  {
    $nb = mb_strlen($search);
    $charset = sfConfig::get('software_internals_charset');
    $transliterate = sfConfig::get('software_internals_transliterate',array());
    
    $search = str_replace(preg_split('//u', $transliterate['from'], -1), preg_split('//u', $transliterate['to'], -1), $search);
    $search = str_replace(array('@','.','-','+',',',"'"),' ',$search);
    $search = mb_strtolower(iconv($charset['db'],$charset['ascii'], mb_substr($search,$nb-1,$nb) == '*' ? mb_substr($search,0,$nb-1) : $s$
    return $search;
  }
  public function buildQuery()
  {
    $q = parent::buildQuery();
    $t = $q->getRootAlias();
    
    $q->leftJoin("$t.User u")
      ->leftJoin("$t.Payments pay")
      ->orderBy("$t.id DESC");
    
    if ( !$q->contains("LEFT JOIN $t.Professional p") )
      $q->leftJoin("$t.Professional p");
    if ( !$q->contains("LEFT JOIN p.Organism o") )
      $q->leftJoin("p.Organism o");
    if ( !$q->contains("LEFT JOIN $t.Contact c") )
      $q->leftJoin("$t.Contact c");

    switch ( $this->type ) {
    case 'asks':
      $q->andWhere('tck.printed_at IS NULL')
        ->andWhere('tck.duplicating IS NULL');
      break;
    case 'duplicatas':
      $q->andWhere('tck.id IN (SELECT tck2.duplicating FROM Ticket tck2)')
        ->andWhere('tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL');
      break;
    case 'debts':
      // debts
      $rq = new Doctrine_RawSql();
      $rq->select('t.id')
        ->from('Transaction t')
        ->addComponent('t','Transaction')
        //->andWhere("(SELECT CASE WHEN SUM(tt.value) IS NULL THEN 0 ELSE SUM(tt.value) END FROM Ticket tt WHERE transaction_id = t.id AND (tt.printed OR tt.cancelling IS NOT NULL) AND tt.duplicating IS NULL) != (CASE WHEN (SELECT SUM(pp.value) FROM Payment pp WHERE pp.transaction_id = t.id) IS NULL THEN 0 ELSE (SELECT SUM(pp.value) FROM Payment pp WHERE pp.transaction_id = t.id) END)");
        ->andWhere("(SELECT CASE WHEN SUM(tt.value) IS NULL THEN 0 ELSE SUM(tt.value) END FROM Ticket tt WHERE transaction_id = t.id AND (tt.printed_at IS NOT NULL OR tt.cancelling IS NOT NULL OR tt.integrated_at IS NOT NULL) AND tt.duplicating IS NULL) != (SELECT CASE WHEN SUM(pp.value) IS NULL THEN 0 ELSE SUM(pp.value) END FROM Payment pp WHERE pp.transaction_id = t.id)");
      $ids = $rq->execute(array(),Doctrine::HYDRATE_NONE);
      foreach ( $ids as $key => $id )
        $ids[$key] = $id[0];
      $q->andWhereIn("$t.id",$ids);
      break;
    default:
      // all transactions
      break;
    }
    
    return $q;
  }
}
