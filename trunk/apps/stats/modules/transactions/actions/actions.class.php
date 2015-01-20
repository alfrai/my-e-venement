<?php

/**
 * transactions actions.
 *
 * @package    e-venement
 * @subpackage transactions
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class transactionsActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    if ( $request->hasParameter('criterias') )
    {
      $this->criterias = $request->getParameter('criterias');
      $this->getUser()->setAttribute('stats.criterias',$this->criterias,'admin_module');
      $this->redirect($this->getContext()->getModuleName().'/index');
    }
    
    $this->form = new StatsCriteriasForm();
    $this->form->addUsersCriteria();
    $this->form->addEventCriterias();
    if ( is_array($this->getUser()->getAttribute('stats.criterias',array(),'admin_module')) )
      $this->form->bind($this->getUser()->getAttribute('stats.criterias',array(),'admin_module'));
  }

  public function executeCsv(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Date','CrossAppLink','Number'));
    $param = $request->getParameter('id');
    
    $this->lines = $this->getTransactions($param == 'asked', $param == 'ordered', $param == 'all', 'array');
    
    switch ( $param ) {
    case 'all':
      $this->name = __('Global repartition');
      break;
    case 'ordered':
      $this->name = __('Engaged transactions');
      break;
    case 'asked':
      $this->name = __('Asked transactions');
      break;
    default:
      $this->name = __('Printed transactions');
      break;
    }
    
    $total = 0;
    foreach ( $this->lines as $line )
      $total += $line['nb'];
    
    foreach ( $this->lines as $key => $line )
      $this->lines[$key]['percent'] = format_number(round($line['nb']*100/$total,2));
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => in_array('microsoft',$params['option']),
      'fields' => array('name','nb','percent'),
      'tunnel' => false,
      'noheader' => false,
    );
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfConfig::get('software_internals_charset');
    
    sfConfig::set('sf_escaping_strategy', false);
    $confcsv = sfConfig::get('software_internals_csv'); if ( isset($confcsv['set_charset']) && $confcsv['set_charset'] ) sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
    if ( $request->hasParameter('debug') )
    {
      $this->setLayout(true);
      $this->getResponse()->sendHttpHeaders();
    }
    else
      sfConfig::set('sf_web_debug', false);
  }
  
  public function executeData(sfWebRequest $request)
  {
    $this->prices = $this->getTransactions(
      $request->getParameter('id') == 'asked',
      $request->getParameter('id') == 'ordered',
      $request->getParameter('id') == 'all'
    );
    
    if ( !$request->hasParameter('debug') )
    {
      $this->setLayout('raw');
      sfConfig::set('sf_debug',false);
      $this->getResponse()->setContentType('application/json');
    }
  }
  
  protected function getTransactions($asked = false, $ordered = false, $all = false, $type = NULL)
  {
    $criterias = $this->getUser()->getAttribute('stats.criterias',array(),'admin_module');
    $dates['from'] = isset($criterias['dates']) && $criterias['dates']['from']['day'] && $criterias['dates']['from']['month'] && $criterias['dates']['from']['year']
      ? strtotime($criterias['dates']['from']['year'].'-'.$criterias['dates']['from']['month'].'-'.$criterias['dates']['from']['day'])
      : strtotime('- 1 weeks');
    $dates['to']   = isset($criterias['dates']) && $criterias['dates']['to']['day'] && $criterias['dates']['to']['month'] && $criterias['dates']['to']['year']
      ? strtotime($criterias['dates']['to']['year'].'-'.$criterias['dates']['to']['month'].'-'.$criterias['dates']['to']['day'].' 23:59:59')
      : strtotime('+ 3 weeks + 1 day');
    if ( isset($criterias['users']) && count($criterias['users']) > 0 )
    {
      if ( !$criterias['users'][0] )
        array_shift($criterias['users']);
    }
    
    $q = Doctrine::getTable('Price')->createQuery('p')
      ->select('p.id, pt.name, p.value, count(DISTINCT tr.id) AS nb')
      ->leftJoin('p.Tickets t')
      ->leftJoin('t.Manifestation m')
      ->leftJoin('m.Gauges g')
      ->leftJoin('m.Event e')
      ->leftJoin('t.Transaction tr')
      ->andWhere('(TRUE')
      ->andWhereIn('g.workspace_id',array_keys($this->getUser()->getWorkspacesCredentials()))
      ->orWhere('g.workspace_id IS NULL')
      ->andWhere('TRUE)')
      ->andWhereIn('e.meta_event_id',array_keys($this->getUser()->getMetaEventsCredentials()))
      ->andWhere('t.id NOT IN (SELECT ttd.duplicating FROM Ticket ttd WHERE ttd.duplicating IS NOT NULL)')
      ->andWhere('t.cancelling IS NULL')
      ->andWhere('t.id NOT IN (SELECT tt.cancelling FROM ticket tt WHERE tt.cancelling IS NOT NULL)')
      ->andWhere('m.happens_at > ?',date('Y-m-d H:i:s',$dates['from']))
      ->andWhere('m.happens_at <= ?',date('Y-m-d H:i:s',$dates['to']))
      ->groupBy('p.id, pt.name, p.value');
    
    if ( isset($criterias['users']) && count($criterias['users']) > 0 )
      $q->andWhereIn('t.sf_guard_user_id',$criterias['users']);

    if ( !$all )
    {
      $q->andWhere($asked || $ordered ? '(t.printed_at IS NULL AND t.integrated_at IS NULL)' : '(t.printed_at IS NOT NULL OR t.integrated_at IS NOT NULL)');
      if ( $ordered)
        $q->andWhere('t.transaction_id IN (SELECT oo.transaction_id FROM Order oo)');
      if ( $asked )
        $q->andWhere('t.transaction_id NOT IN (SELECT oo.transaction_id FROM Order oo)');
    }
    
    return $type == 'array' ? $q->fetchArray() : $q->execute();
  }
}
