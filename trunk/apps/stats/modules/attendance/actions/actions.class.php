<?php

/**
 * attendance actions.
 *
 * @package    e-venement
 * @subpackage attendance
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class attendanceActions extends sfActions
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
    $this->form->addWithContactCriteria();
    $this->form->addEventCriterias();
    if ( is_array($this->getUser()->getAttribute('stats.criterias',array(),'admin_module')) )
      $this->form->bind($this->getUser()->getAttribute('stats.criterias',array(),'admin_module'));
  }
  
  public function executeCsv(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Number', 'Date'));
    $this->lines = $this->getManifs('array', true);
    
    foreach ( $this->lines as $key => $line )
    {
      // free seats
      $this->lines[$key]['free'] = $line['gauge']-$line['printed']-(sfConfig::get('project_tickets_count_demands',false) ? $line['asked'] : 0)-$line['ordered'];
      
      // percentages
      $this->lines[$key]['printed_percentage'] = $line['gauge'] > 0 ? format_number(round($line['printed']*100/$line['gauge'],2)) : 'N/A';
      $this->lines[$key]['ordered_percentage'] = $line['gauge'] > 0 ? format_number(round($line['ordered']*100/$line['gauge'],2)) : 'N/A';
      if ( sfConfig::get('project_tickets_count_demands',false) )
        $this->lines[$key]['asked_percentage']   = $line['gauge'] > 0 ? format_number(round($line['asked']  *100/$line['gauge'],2)) : 'N/A';
      else
        unset($this->lines[$key]['asked']);
      $this->lines[$key]['printed_gifts_percentage'] = $line['printed'] > 0 ? format_number(round($line['printed_gifts']*100/$line['printed'],2)) : 'N/A';
      $this->lines[$key]['printed_with_payment_percentage'] = $line['printed'] > 0 ? format_number(round($line['printed_with_payment']*100/$line['printed'],2)) : 'N/A';
      $this->lines[$key]['printed_deposits_percentage'] = $line['printed'] > 0 ? format_number(round($line['printed_deposits']*100/$line['printed'],2)) : 'N/A';
      $this->lines[$key]['free_percentage']    = $line['gauge'] > 0 ? format_number(round(($line['gauge']-$line['printed']-(sfConfig::get('project_tickets_count_demands',false) ? $line['asked'] : 0)-$line['ordered'])*100/$line['gauge'],2)) : 'N/A';
      
      // cashflow
      $this->lines[$key]['cashflow']    = format_number(round($line['cashflow'],2));
      
      // datetime
      $this->lines[$key]['dotw'] = format_datetime($this->lines[$key]['happens_at'],'EEEE');
      $this->lines[$key]['date'] = format_date($this->lines[$key]['happens_at'],'d');
      $this->lines[$key]['time'] = format_date($this->lines[$key]['happens_at'],'hh:mm');
      unset($this->lines[$key]['happens_at']);
    }
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => in_array('microsoft',$params['option']),
      'fields' => array(
        'event_name','dotw','date','time','location_name','location_city',
        'gauge','printed','printed_with_payment','printed_gifts','ordered','printed_deposits','asked','free',
        'printed_percentage','printed_with_payment_percentage','printed_gifts_percentage','ordered_percentage','asked_percentage','printed_deposits_percentage','free_percentage',
        'cashflow',),
      'tunnel' => false,
      'noheader' => false,
    );
    
    if ( !sfConfig::get('project_tickets_count_demands',false) )
    {
      unset($this->options['fields'][array_search('asked_percentage',$this->options['fields'])]);
      unset($this->options['fields'][array_search('asked',$this->options['fields'])]);
    }
    
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
    $this->manifs = $this->getManifs();
    if ( !$request->hasParameter('debug') )
    {
      $this->setLayout('raw');
      sfConfig::set('sf_web_debug', false);
      $this->getResponse()->setContentType('application/json');
    }
  }
  
  protected function getManifs($type = NULL, $extra_data = false)
  {
    $criterias = $this->getUser()->getAttribute('stats.criterias',array('dates' => array(
      'from' => array(
        'day' => NULL,
        'month' => NULL,
        'year' => NULL
      ),
      'to' => array(
        'day' => NULL,
        'month' => NULL,
        'year' => NULL
      )
    )),'admin_module');
    
    $dates['from'] = $criterias['dates']['from']['day'] && $criterias['dates']['from']['month'] && $criterias['dates']['from']['year']
      ? strtotime($criterias['dates']['from']['year'].'-'.$criterias['dates']['from']['month'].'-'.$criterias['dates']['from']['day'])
      : strtotime('- 1 weeks');
    $dates['to']   = $criterias['dates']['to']['day'] && $criterias['dates']['to']['month'] && $criterias['dates']['to']['year']
      ? strtotime($day = $criterias['dates']['to']['year'].'-'.$criterias['dates']['to']['month'].'-'.$criterias['dates']['to']['day'].' 23:59:59')
      : strtotime('+ 3 weeks + 1 day');
    
    $gids = false;
    $ws = false;
    if ( isset($criterias['workspaces_list']) && $criterias['workspaces_list'][0] )
    {
      $ws = $criterias['workspaces_list'];
      
      $q = Doctrine::getTable('Gauge')->createQuery('g')
        ->leftJoin('g.Manifestation m')
        ->leftJoin('m.Event e')
        ->andWhere('g.workspace_id IN ('.implode(',',$ws).')')
        ->andWhere('m.happens_at <= ?',date('Y-m-d H:i:s',$dates['to']))
        ->andWhere('m.happens_at > ?',date('Y-m-d H:i:s',$dates['from']))
        ->andWhereIn('e.meta_event_id',array_keys($this->getUser()->getMetaEventsCredentials()));
      $gauges = $q->execute();
      
      $gids = array();
      foreach ( $gauges as $gauge )
        $gids[] = $gauge->id;
    }
    
    // gauge
    $criteria_gg = $ws ? ' AND gg.workspace_id IN ('.implode(',',$ws).')' : '';
    $criteria_tt_gauge = $gids ? ' AND tt%%d%%.gauge_id IN ('.implode(',',$gids).')' : '';
    
    // tickets with or without contact
    $criteria_tt_contact = isset($criterias['with_contact']) && $criterias['with_contact']
      ? ' AND ttr%%d%%.contact_id IS '.($criterias['with_contact'] == 'yes' ? 'NOT' : '').' NULL'
      : '';

    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->select('m.id, m.happens_at, e.id, et.id, et.name AS event_name, l.name AS location_name, l.city AS location_city')
      //->select('m.*')
      ->addSelect('(SELECT sum(gg.value) FROM gauge gg WHERE m.id = gg.manifestation_id '.$criteria_gg.' AND gg.workspace_id IN (\''.implode("','",array_keys($this->getUser()->getWorkspacesCredentials())).'\')) AS gauge')
      ->addSelect('(SELECT sum((tt.printed_at IS NOT NULL OR tt.integrated_at IS NOT NULL) AND duplicating IS NULL AND cancelling IS NULL) FROM ticket tt LEFT JOIN tt.Transaction ttr WHERE m.id = tt.manifestation_id AND tt.id NOT IN (SELECT ttt.cancelling FROM ticket ttt WHERE ttt.cancelling IS NOT NULL AND ttt.manifestation_id = m.id) '.str_replace('%%d%%','',$criteria_tt_gauge).' '.str_replace('%%d%%','',$criteria_tt_contact).') AS printed')
      ->addSelect('(SELECT sum(NOT (tt2.printed_at IS NOT NULL OR tt2.integrated_at IS NOT NULL) AND duplicating IS NULL AND cancelling IS NULL) FROM ticket tt2 LEFT JOIN tt2.Transaction ttr2 WHERE m.id = tt2.manifestation_id AND tt2.id NOT IN (SELECT ttt2.cancelling FROM ticket ttt2 WHERE ttt2.cancelling IS NOT NULL AND ttt2.manifestation_id = m.id) AND tt2.transaction_id IN (SELECT oo.transaction_id FROM order oo) '.str_replace('%%d%%',2,$criteria_tt_gauge).' '.str_replace('%%d%%',2,$criteria_tt_contact).') AS ordered')
      ->addSelect('(SELECT sum(NOT (tt3.printed_at IS NOT NULL OR tt3.integrated_at IS NOT NULL) AND duplicating IS NULL AND cancelling IS NULL) FROM ticket tt3 LEFT JOIN tt3.Transaction ttr3 WHERE m.id = tt3.manifestation_id AND tt3.id NOT IN (SELECT ttt3.cancelling FROM ticket ttt3 WHERE ttt3.cancelling IS NOT NULL AND ttt3.manifestation_id = m.id) AND tt3.transaction_id NOT IN (SELECT oo3.transaction_id FROM order oo3) '.str_replace('%%d%%',3,$criteria_tt_gauge).' '.str_replace('%%d%%',3,$criteria_tt_contact).') AS asked')
      ->addSelect('(SELECT sum(tt4.value) FROM ticket tt4 LEFT JOIN tt4.Transaction ttr4 WHERE m.id = tt4.manifestation_id AND tt4.id NOT IN (SELECT ttt4.cancelling FROM ticket ttt4 WHERE ttt4.cancelling IS NOT NULL AND ttt4.manifestation_id = m.id) AND (tt4.printed_at IS NOT NULL OR tt4.integrated_at IS NOT NULL) AND tt4.cancelling IS NULL AND tt4.duplicating IS NULL '.str_replace('%%d%%',4,$criteria_tt_gauge).' '.str_replace('%%d%%',4,$criteria_tt_contact).') AS cashflow')
      ->andWhere('m.happens_at <= ?',date('Y-m-d H:i:s',$dates['to']))
      ->andWhere('m.happens_at > ?',date('Y-m-d H:i:s',$dates['from']))
      ->andWhereIn('e.meta_event_id',array_keys($this->getUser()->getMetaEventsCredentials()))
      ->orderBy('m.happens_at, et.name')
      ->groupBy('m.id, m.happens_at, e.id, et.lang, et.id, et.name, l.id, l.name, l.city');
    
    if ( true || $extra_data )
    $q->addSelect('(SELECT sum((tt5.printed_at IS NOT NULL OR tt5.integrated_at IS NOT NULL) AND tt5.duplicating IS NULL AND tt5.cancelling IS NULL) FROM ticket tt5 LEFT JOIN tt5.Transaction ttr5 WHERE m.id = tt5.manifestation_id AND tt5.id NOT IN (SELECT ttt5.cancelling FROM ticket ttt5 WHERE ttt5.cancelling IS NOT NULL AND ttt5.manifestation_id = m.id) '.str_replace('%%d%%','5',$criteria_tt_gauge).' '.str_replace('%%d%%','5',$criteria_tt_contact).' AND (SELECT count(ttp5.id) FROM Payment ttp5 WHERE ttp5.transaction_id = ttr5.id) > 0) AS printed_with_payment')
      ->addSelect('(SELECT sum((tt6.printed_at IS NOT NULL OR tt6.integrated_at IS NOT NULL) AND tt6.duplicating IS NULL AND tt6.cancelling IS NULL) FROM ticket tt6 LEFT JOIN tt6.Transaction ttr6 WHERE m.id = tt6.manifestation_id AND tt6.id NOT IN (SELECT ttt6.cancelling FROM ticket ttt6 WHERE ttt6.cancelling IS NOT NULL AND ttt6.manifestation_id = m.id) '.str_replace('%%d%%','6',$criteria_tt_gauge).' '.str_replace('%%d%%','6',$criteria_tt_contact).'  AND tt6.value = 0) AS printed_gifts')
      ->addSelect('(SELECT sum((tt7.printed_at IS NOT NULL OR tt7.integrated_at IS NOT NULL) AND tt7.duplicating IS NULL AND tt7.cancelling IS NULL) FROM ticket tt7 LEFT JOIN tt7.Transaction ttr7 LEFT JOIN ttr7.Payments ttp WHERE m.id = tt7.manifestation_id AND tt7.id NOT IN (SELECT ttt7.cancelling FROM ticket ttt7 WHERE ttt7.cancelling IS NOT NULL AND ttt7.manifestation_id = m.id) '.str_replace('%%d%%','7',$criteria_tt_gauge).' '.str_replace('%%d%%','7',$criteria_tt_contact).' AND ttr7.deposit = TRUE) AS printed_deposits')
    ;
    
    if ( isset($criterias['meta_events_list']) && $criterias['meta_events_list'][0] )
      $q->andWhereIn('e.meta_event_id',$criterias['meta_events_list']);
    if ( $gids )
      $q->andWhereIn('g.id',$gids);
    
    return $type == 'array' ? $q->fetchArray() : $q->execute();
  }
}
