<?php

/**
 * geo actions.
 *
 * @package    e-venement
 * @subpackage geo
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class geoActions extends sfActions
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
      $this->setCriterias($this->criterias);
      $this->redirect($this->getContext()->getModuleName().'/index');
    }
    
    $this->form = new StatsCriteriasForm();
    $this->form
      ->addEventCriterias()
      ->addGroupsCriteria();
    if ( is_array($this->getCriterias()) )
      $this->form->bind($this->getCriterias());
  }
  
  protected function getCriterias()
  {
    return $this->getUser()->getAttribute('stats.criterias',array(),'admin_module');
  }
  protected function setCriterias($values)
  {
    $this->getUser()->setAttribute('stats.criterias',$values,'admin_module');
    return $this;
  }
  
  public function executeCsv(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Date','CrossAppLink','Number'));
    $param = $request->getParameter('id');
    
    $this->lines = $this->getData()->toArray();
    
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
    $this->data = $this->getData();
    
    if ( !$request->hasParameter('debug') )
    {
      $this->setLayout('raw');
      sfConfig::set('sf_debug',false);
      $this->getResponse()->setContentType('application/json');
    }
  }
  
  protected function buildQuery()
  {
    return  $this->addFiltersToQuery(Doctrine_Query::create()->from('Contact c'))
      ->leftJoin('c.Transactions t')
      ->leftJoin('t.Tickets tck')
      ->leftJoin('tck.Gauge g')
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('m.Event e')
      ->andWhere('tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL')
      ->andWhere('tck.id NOT IN (SELECT tck2.cancelling FROM Ticket tck2 WHERE cancelling IS NOT NULL)')
      ->andWhere('tck.duplicating IS NULL');
  }
  protected function addFiltersToQuery(Doctrine_Query $q)
  {
    $criterias = $this->getCriterias();
    
    if ( isset($criterias['groups_list']) && is_array($criterias['groups_list']) )
      $q->leftJoin('c.Groups gc')
        ->andWhereIn('gc.id',$criterias['groups_list']);
    
    if ( isset($criterias['meta_events_list']) && is_array($criterias['meta_events_list']) )
      $q->andWhereIn('e.meta_event_id', $criterias['meta_events_list']);
    if ( isset($criterias['event_categories_list']) && is_array($criterias['event_categories_list']) )
      $q->andWhereIn('e.event_category_id', $criterias['event_categories_list']);
    if ( isset($criterias['workspaces_list']) && is_array($criterias['workspaces_list']) )
      $q->andWhereIn('g.workspace_id', $criterias['workspaces_list']);
    if ( isset($criterias['sf_guard_users_list']) && is_array($criterias['sf_guard_users_list']) )
      $q->andWhereIn('tck.sf_guard_user_id', $criterias['sf_guard_users_list']);
    if ( isset($criterias['events_list']) && is_array($criterias['events_list']) )
      $q->andWhereIn('m.event_id', $criterias['events_list']);
    
    if ( isset($criterias['dates']) && is_array($criterias['dates']) )
    {
      foreach ( array('from' => '>=', 'to' => '<') as $margin => $operand )
      if ( isset($criterias['dates'][$margin]) && is_array($criterias['dates'][$margin]) )
      if ( isset($criterias['dates'][$margin]['day']) && isset($criterias['dates'][$margin]['month']) && isset($criterias['dates'][$margin]['year']) )
      if ( $criterias['dates'][$margin]['day'] && $criterias['dates'][$margin]['month'] && $criterias['dates'][$margin]['year'])
      {
        $q->andWhere(
          'tck.printed_at '.$operand.' ? OR tck.printed_at IS NULL AND tck.integrated_at '.$operand.' ?',
          array(
            $criterias['dates'][$margin]['year'].'-'.$criterias['dates'][$margin]['month'].'-'.$criterias['dates'][$margin]['day'],
            $criterias['dates'][$margin]['year'].'-'.$criterias['dates'][$margin]['month'].'-'.$criterias['dates'][$margin]['day']
          )
        );
      }
    }
    
    return $q;
  }
  
  protected function getData()
  {
    $res = array('exact' => 0, 'department' => 0, 'region' => 0, 'country' => 0, 'others' => 0);
    
    $client = sfConfig::get('app_about_client',array());
    
    $res['exact'] = $this->buildQuery()
      ->andWhere('c.postalcode = ?', $client['postalcode'])
      ->andWhere('c.country ILIKE ? OR c.country IS NULL OR c.country = ?', array(isset($client['country']) ? $client['country'] : 'France', ''))
      ->count();
    
    $res['department'] = $this->buildQuery()
      ->andWhere('substring(c.postalcode, 1, 2) = substring(?, 1, 2)', $client['postalcode'])
      ->andWhere('c.country ILIKE ? OR c.country IS NULL OR c.country = ?', array(isset($client['country']) ? $client['country'] : 'France', ''))
      ->count() - $res['exact'];
    
    $res['region'] = $this->buildQuery()
      ->andWhere('substring(c.postalcode, 1, 2) IN (SELECT gd.num FROM GeoFrRegion gr LEFT JOIN gr.Departments gd LEFT JOIN gr.Departments gdc WHERE gdc.num = substring(?, 1, 2))', $client['postalcode'])
      ->andWhere('c.country ILIKE ? OR c.country IS NULL OR c.country = ?', array(isset($client['country']) ? $client['country'] : 'France', ''))
      ->count() - $res['department'] - $res['exact'];
    
    $res['country'] = $this->buildQuery()
      ->andWhere('c.country ILIKE ? OR c.country IS NULL OR c.country = ?', array(isset($client['country']) ? $client['country'] : 'France', ''))
      ->count() - $res['region'] - $res['department'] - $res['exact'];
      
    $res['others'] = $this->buildQuery()
      ->count() - $res['country'] - $res['region'] - $res['department'] - $res['exact'];
    
    return $res;
  }
}
