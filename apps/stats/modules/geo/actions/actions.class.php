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
      ->addByTicketsCriteria()
      ->addEventCriterias()
      ->addManifestationCriteria()
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
    
    $criterias = $this->getCriterias();
    $this->data = $this->getData($request->getParameter('type','ego'), isset($criterias['by_tickets']) && $criterias['by_tickets'] === 'y');
    
    $total = 0;
    foreach ( $this->data as $data )
      $total += $data;
    
    $this->lines = array();
    foreach ( $this->data as $name => $data )
    {
      $this->lines[] = array(
        'name' => __($name),
        'qty' => $data,
        'percent' => format_number(round($data*100/$total,2)),
      );
    }
    
    $this->lines[] = array(
      'name' => __('Total'),
      'qty'   => $total,
      'percent' => 100,
    );
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => in_array('microsoft',$params['option']),
      'fields' => array('name','qty','percent'),
      'tunnel' => false,
      'noheader' => false,
    );
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfConfig::get('software_internals_charset');
    
    sfConfig::set('sf_escaping_strategy', false);
    $confcsv = sfConfig::get('software_internals_csv');
    if ( isset($confcsv['set_charset']) && $confcsv['set_charset'] ) sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
    if ( $request->hasParameter('debug') )
    {
      $this->setLayout('layout');
      $this->getResponse()->sendHttpHeaders();
    }
    else
      sfConfig::set('sf_web_debug', false);
  }
  
  public function executeData(sfWebRequest $request)
  {
    $criterias = $this->getCriterias();
    $this->data = $this->getData($request->getParameter('type','ego'), isset($criterias['by_tickets']) && $criterias['by_tickets'] === 'y');
    
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
      ->andWhere('tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL')
      ->andWhere('tck.cancelling IS NULL AND tck.id NOT IN (SELECT tck2.cancelling FROM Ticket tck2 WHERE cancelling IS NOT NULL)')
      ->andWhere('tck.duplicating IS NULL')
      ->leftJoin('tck.Gauge g')
      ->andWhereIn('g.workspace_id', array_keys($this->getUser()->getWorkspacesCredentials()))
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('m.Event e')
      ->andWhereIn('e.meta_event_id', array_keys($this->getUser()->getMetaEventsCredentials()))
      ->leftJoin('tck.Price p')
      ->leftJoin('p.Users u')
      ->andWhere('u.id = ?', $this->getUser()->getId())
    ;
  }
  protected function addFiltersToQuery(Doctrine_Query $q)
  {
    $criterias = $this->getCriterias();
    
    if ( isset($criterias['groups_list']) && is_array($criterias['groups_list']) )
    {
      $q->leftJoin('c.Groups gc')
        ->leftJoin('c.Professionals pro')
        ->leftJoin('pro.Groups gp')
        ->andWhere('(TRUE')
        ->andWhereIn('gc.id', $criterias['groups_list'])
        ->orWhereIn('gp.id', $criterias['groups_list'])
        ->andWhere('TRUE)')
      ;
    }
    
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
    if ( isset($criterias['manifestations_list']) && is_array($criterias['manifestations_list']) )
      $q->andWhereIn('m.id', $criterias['manifestations_list']);
    
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
  
  protected function getData($type = 'ego', $count_tickets = false)
  {
    $this->type = $type;
    $res = array();
    $total = 0;
    switch ( $type ) {
    
    case 'postalcodes':
      foreach ( $this->buildQuery()
        ->select('c.id, c.postalcode, count(DISTINCT tck.id) AS qty')
        ->groupBy('c.id, c.postalcode')
        ->fetchArray() as $pc )
      {
        if ( !isset($res[$pc['postalcode']]) )
          $res[$pc['postalcode']] = 0;
        $res[$pc['postalcode']] += $count_tickets ? $pc['qty'] : 1;
      }
      arsort($res);
      
      $cpt = 0;
      $others = 0;
      foreach ( $res as $code => $qty )
      {
        if ( intval($code).'' !== ''.$code )
        {
          $others += $qty;
          unset($res[$code]);
          continue;
        }
        if ( $cpt >= sfConfig::get('app_geo_limits_'.$type, 8) )
        {
          $others += $qty;
          unset($res[$code]);
        }
        $cpt++;
      }
      $res['others'] = $others;
      arsort($res);
    break;
    
    case 'departments':
      foreach ( $this->buildQuery()
        ->select('c.id, substr(c.postalcode,1,2) AS dpt, count(DISTINCT tck.id) AS qty')
        ->groupBy('c.id, c.postalcode')
        ->fetchArray() as $pc )
      {
        if ( !isset($res[$pc['dpt']]) )
          $res[$pc['dpt']] = 0;
        $res[$pc['dpt']] += $count_tickets ? $pc['qty'] : 1;
        $total += $count_tickets ? $pc['qty'] : 1;
      }
      arsort($res);
      
      $cpt = 0;
      $others = 0;
      foreach ( $res as $code => $qty )
      {
        if ( intval($code).'' !== ''.$code )
        {
          $others += $qty;
          unset($res[$code]);
          continue;
        }
        if ( $cpt >= sfConfig::get('app_geo_limits_'.$type, 4) )
        {
          $others += $qty;
          unset($res[$code]);
        }
        $cpt++;
      }
      $res['others'] = $others;
      
      foreach ( Doctrine::getTable('GeoFrDepartment')->createQuery('gd')
        ->andWhereIn('gd.num', array_keys($res))
        ->execute() as $dpt )
      {
        $res[$dpt->name] = $res[$dpt->num];
        unset($res[$dpt->num]);
      }
      arsort($res);
    break;
    
    case 'regions':
      $dpts = array();
      foreach ( Doctrine::getTable('GeoFrDepartment')->createQuery('gd')
        ->leftJoin('gd.Region gr')
        ->select('gd.num, gr.id AS region_id, gr.name AS region')
        ->fetchArray() as $dpt )
        $dpts[$dpt['num']] = $dpt['region'];
      $dpts[''] = '';
      
      foreach ( $this->buildQuery()
        ->select('c.id, substr(c.postalcode,1,2) AS dpt, count(DISTINCT tck.id) AS qty')
        ->groupBy('c.id, c.postalcode')
        ->fetchArray() as $pc )
      {
        if ( !isset($dpts[trim($pc['dpt'])]) )
          $pc['dpt'] = '';
        if ( !isset($res[$dpts[trim($pc['dpt'])]]) )
          $res[$dpts[trim($pc['dpt'])]] = 0;
        $res[$dpts[trim($pc['dpt'])]] += $count_tickets ? $pc['qty'] : 1;
        $total += $count_tickets ? $pc['qty'] : 1;
      }
      $others = 0;
      if ( isset($res['']) )
      {
        $others += $res[''];
        unset($res['']);
      }
      
      $cpt = 0;
      foreach ( $res as $code => $qty )
      {
        if ( $cpt >= sfConfig::get('app_geo_limits_'.$type, 4) )
        {
          $others += $qty;
          unset($res[$code]);
        }
        $cpt++;
      }
      $res['others'] = $others;
      arsort($res);
    break;
    
    case 'countries':
      $dpts = array();
      $tmp = sfConfig::get('app_about_client', array());
      $default_country = isset($tmp['country']) ? $tmp['country'] : '';
      
      foreach ( $this->buildQuery()
        ->select('c.id, c.country, count(DISTINCT tck.id) AS qty')
        ->groupBy('c.id, c.country')
        ->fetchArray() as $pc )
      {
        if ( !trim($pc['country']) )
          $pc['country'] = $default_country;
        $pc['country'] = trim(strtolower($pc['country']));
        if ( !isset($res[$pc['country']]) )
          $res[$pc['country']] = 0;
        $res[$pc['country']] += $count_tickets ? $pc['qty'] : 1;
        $total = $count_tickets ? $pc['qty'] : 1;
      }
      $others = 0;
      if ( isset($res['']) )
      {
        $others += $res[''];
        unset($res['']);
      }
      
      $cpt = 0;
      foreach ( $res as $country => $qty )
      {
        if ( $cpt >= sfConfig::get('app_geo_limits_'.$type, 4) )
        {
          $others += $qty;
          unset($res[$country]);
        }
        elseif ( ucwords($country) != $country )
        {
          $res[ucwords($country)] = $res[$country];
          unset($res[$country]);
        }
        $cpt++;
      }
      $res['others'] = $others;
      arsort($res);
    break;
    
    default:
      $client = sfConfig::get('app_about_client',array());
      $res = array('exact' => 0, 'department' => 0, 'region' => 0, 'country' => 0, 'others' => 0);
      $q = $this->buildQuery()
        ->select('c.id, count(DISTINCT tck.id) AS qty')
        ->groupBy('c.id')
        ->andWhere('c.postalcode = ?', $client['postalcode'])
        ->andWhere('c.country ILIKE ? OR c.country IS NULL OR c.country = ?', array(isset($client['country']) ? $client['country'] : 'France', ''));
      $res['exact'] = 0;
      if ( $count_tickets )
      foreach ( $q->fetchArray() as $c )
      {
        $res['exact'] += $c['qty'];
        $total += $c['qty'];
      }
      else
      {
        $res['exact'] = $q->count();
        $total = 1;
      }
      
      $q = $this->buildQuery()
        ->select('c.id, count(DISTINCT tck.id) AS qty')
        ->groupBy('c.id')
        ->andWhere('substring(c.postalcode, 1, 2) = substring(?, 1, 2)', $client['postalcode'])
        ->andWhere('c.country ILIKE ? OR c.country IS NULL OR c.country = ?', array(isset($client['country']) ? $client['country'] : 'France', ''));
      $res['department'] = -$res['exact'];
      if ( $count_tickets )
      foreach ( $q->fetchArray() as $c )
      {
        $res['department'] += $c['qty'];
        $total += $c['qty'];
      }
      else
      {
        $res['department'] += $q->count();
        $total += 1;
      }
      
      $q = $this->buildQuery()
        ->select('c.id, count(DISTINCT tck.id) AS qty')
        ->groupBy('c.id')
        ->andWhere('substring(c.postalcode, 1, 2) IN (SELECT gd.num FROM GeoFrRegion gr LEFT JOIN gr.Departments gd LEFT JOIN gr.Departments gdc WHERE gdc.num = substring(?, 1, 2))', $client['postalcode'])
        ->andWhere('c.country ILIKE ? OR c.country IS NULL OR c.country = ?', array(isset($client['country']) ? $client['country'] : 'France', ''));
      $res['region'] = -$res['department'] -$res['exact'];
      if ( $count_tickets )
      foreach ( $q->fetchArray() as $c )
      {
        $res['region'] += $c['qty'];
        $total += $c['qty'];
      }
      else
      {
        $res['region'] += $q->count();
        $total += 1;
      }
      
      $q = $this->buildQuery()
        ->select('c.id, count(DISTINCT tck.id) AS qty')
        ->groupBy('c.id')
        ->andWhere('c.country ILIKE ? OR c.country IS NULL OR c.country = ?', array(isset($client['country']) ? $client['country'] : 'France', ''));
      $res['country'] = -$res['region'] -$res['department'] -$res['exact'];
      if ( $count_tickets )
      foreach ( $q->fetchArray() as $c )
      {
        $res['country'] += $c['qty'];
        $total += $c['qty'];
      }
      else
      {
        $res['country'] += $q->count();
        $total += 1;
      }
      
      $q = $this->buildQuery()
        ->select('c.id, count(DISTINCT tck.id) AS qty')
        ->groupBy('c.id');
      $res['others'] = -$res['country'] -$res['region'] -$res['department'] -$res['exact'];
      if ( $count_tickets )
      foreach ( $q->fetchArray() as $c )
      {
        $res['others'] += $c['qty'];
        $total += $c['qty'];
      }
      else
      {
        $res['others'] += $q->count();
        $total += 1;
      }
      break;
    }
    
    return $res;
  }
}
