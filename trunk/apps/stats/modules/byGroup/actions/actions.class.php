<?php

/**
 * byGroup actions.
 *
 * @package    e-venement
 * @subpackage byGroup
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class byGroupActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    /*
    if ( $request->hasParameter('criterias') )
    {
      $this->criterias = $request->getParameter('criterias');
      $this->getUser()->setAttribute('stats.criterias',$this->criterias,'admin_module');
      $this->redirect($this->getContext()->getModuleName().'/index');
    }
    
    $this->form = new StatsCriteriasForm();
    if ( is_array($this->getUser()->getAttribute('stats.criterias',array(),'admin_module')) )
      $this->form->bind($this->getUser()->getAttribute('stats.criterias',array(),'admin_module'));
    */
  }
  
  public function executeCsv(sfWebRequest $request)
  {
    $groups = $this->getGroups('array');
    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->andWhere('m.happens_at <= now()')
      ->limit('3')
      ->orderBy('m.happens_at DESC, e.name, l.name');
    $this->manifs = $manifs = $q->execute();
    
    $this->lines = array();
    foreach ( $groups as $group )
    {
      $this->lines[$group['id']]['name'] = $group['name'];
      foreach ( $manifs as $manif )
      if ( !isset($this->lines[$group['id']]['manif-'.$manif->id]) )
        $this->lines[$group['id']]['manif-'.$manif->id] = 0;
      
      $this->lines[$group['id']]['manif-'.$group['manifestation_id']] = $group['nb_entries'];
    }
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => in_array('microsoft',$params['option']),
      'fields' => array('name'),
      'tunnel' => false,
      'noheader' => false,
    );
    foreach ( $manifs as $manif )
      $this->options['fields'][] = 'manif-'.$manif->id;
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfConfig::get('software_internals_charset');
    
    sfConfig::set('sf_escaping_strategy', false);
    sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
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
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Date','CrossAppLink'));
    $groups = $this->getGroups();
    
    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->andWhere('m.happens_at <= now()')
      ->limit('3')
      ->orderBy('m.happens_at DESC, e.name, l.name');
    $manifs = $q->execute();
    
    $bars = array();
    $colors = array(
      $manifs[0]->id => array('#ec7890','#fe3462'),
      $manifs[1]->id => array('#eca478','#fe8134'),
      $manifs[2]->id => array('#789aec','#1245b9'),
      -1 => array('#7cec78','#17b912'),
    );
    foreach ( $manifs as $manif )
    {
      $id = !isset($colors[$manif->id]) ? -1 : $manif->id;
      $bars[$manif->id] = new stBarOutline( 40, $colors[$id][0], $colors[$id][1] );
      $bars[$manif->id]->key( $manif->getShortName(), 10 );
    }
    
    //Passing the random data to bar chart
    $names = $max = array();
    foreach ( $groups as $group )
    if ( isset($bars[$group['manifestation_id']]) )
    {
      $names[] = $group['name'];
      $max[] = $group['nb_entries'];
      $bars[$group['manifestation_id']]->add_link($group['nb_entries'],cross_app_url_for('rp','group/show?id='.$group['id'],true));
    }
    
    //Creating a stGraph object
    $g = new stGraph();
    //$g->title( __('Gauge filling'), '{font-size: 20px;}' );
    $g->bg_colour = '#E4F5FC';
    $g->bg_colour = '#FFFFFF';
    $g->set_inner_background( '#E3F0FD', '#CBD7E6', 90 );
    $g->x_axis_colour( '#8499A4', '#E4F5FC' );
    $g->y_axis_colour( '#8499A4', '#E4F5FC' );
 
    //Pass stBarOutline object i.e. $bar to graph
    $g->data_sets = $bars;
 
    //Setting labels for X-Axis
    $g->set_x_labels($names);
 
    // to set the format of labels on x-axis e.g. font, color, step
    $g->set_x_label_style( 10, '#18A6FF', 2, 1 );
 
    // To tick the values on x-axis
    // 2 means tick every 2nd value
    //$g->set_x_axis_steps( 1 );
 
    //set maximum value for y-axis
    //we can fix the value as 20, 10 etc.
    //but its better to use max of data
    $g->set_y_max($max);
    $g->y_label_steps( 4 );
    $g->set_y_legend( __('Percentage on gauge'), 12, '#18A6FF' );
    echo $g->render();
 
    return sfView::NONE;
  }
  
  protected function getGroups()
  {
    /*
    $criterias = $this->getUser()->getAttribute('stats.criterias',array(
      'dates' => array(
        'from' => array('day' => '', 'month' => '', 'year' => ''),
        'to' => array('day' => '', 'month' => '', 'year' => '')
      )
    ),'admin_module');
    $dates = array(
      'from' => $criterias['dates']['from']['day'] && $criterias['dates']['from']['month'] && $criterias['dates']['from']['year']
        ? strtotime($criterias['dates']['from']['year'].'-'.$criterias['dates']['from']['month'].'-'.$criterias['dates']['from']['day'])
        : strtotime('- 1 weeks'),
      'to' => $criterias['dates']['to']['day'] && $criterias['dates']['to']['month'] && $criterias['dates']['to']['year']
        ? strtotime($criterias['dates']['to']['year'].'-'.$criterias['dates']['to']['month'].'-'.$criterias['dates']['to']['day'].' 23:59:59')
        : strtotime('+ 3 weeks + 1 day')
    );
    $dates = array(':date1' => date('Y-m-d H:i:s',$dates['from']), ':date2' => date('Y-m-d H:i:s',$dates['to']));
    */
    
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $q = ' SELECT g.id, g.name,
                  m.id AS manifestation_id, m.happens_at,
                  e.id AS event_id, e.name AS event_name,
                  l.id AS location_id, l.name AS location_name, l.city AS location_city,
                  count(DISTINCT ctrl.ticket_id) AS nb_entries
           FROM group_table g
           LEFT JOIN group_contact gc ON gc.group_id = g.id
           LEFT JOIN contact c ON c.id = gc.contact_id
           LEFT JOIN transaction t ON t.contact_id = c.id AND t.professional_id IS NULL
           LEFT JOIN ticket tck ON tck.transaction_id = t.id
           LEFT JOIN (SELECT mm.* FROM manifestation mm LEFT JOIN event ee ON ee.id = mm.event_id WHERE mm.happens_at > now() AND ee.meta_event_id IN ('.implode(',',array_keys($this->getUser()->getMetaEventsCredentials())).') LIMIT 1) AS m ON m.id = tck.manifestation_id
           LEFT JOIN event e ON e.id = m.event_id
           LEFT JOIN location l ON l.id = m.location_id
           LEFT JOIN control ctrl ON ctrl.ticket_id = tck.id
           WHERE m.id IS NOT NULL
             AND (t.workspace_id IS NULL OR t.workspace_id IN ('.implode(',',array_keys($this->getUser()->getWorkspacesCredentials())).'))
           GROUP BY g.id, g.name, m.id, m.happens_at, e.id, e.name, l.id, l.name, l.city
           ORDER BY g.name, m.happens_at, e.name, l.name';
    $stmt1 = $pdo->prepare($q);
    $stmt1->execute();
    
    $q = ' SELECT g.id, g.name,
                  m.id AS manifestation_id, m.happens_at,
                  e.id AS event_id, e.name AS event_name,
                  l.id AS location_id, l.name AS location_name, l.city AS location_city,
                  count(DISTINCT ctrl.ticket_id) AS nb_entries
           FROM group_table g
           LEFT JOIN group_contact gc ON gc.group_id = g.id
           LEFT JOIN contact c ON c.id = gc.contact_id
           LEFT JOIN transaction t ON t.contact_id = c.id AND t.professional_id IS NULL
           LEFT JOIN ticket tck ON tck.transaction_id = t.id
           LEFT JOIN (SELECT mm.* FROM manifestation mm LEFT JOIN event ee ON ee.id = mm.event_id WHERE mm.happens_at > now() AND ee.meta_event_id IN ('.implode(',',array_keys($this->getUser()->getMetaEventsCredentials())).') LIMIT 1) AS m ON m.id = tck.manifestation_id
           LEFT JOIN event e ON e.id = m.event_id
           LEFT JOIN location l ON l.id = m.location_id
           LEFT JOIN control ctrl ON ctrl.ticket_id = tck.id
           WHERE m.id IS NOT NULL
             AND (t.workspace_id IS NULL OR t.workspace_id IN ('.implode(',',array_keys($this->getUser()->getWorkspacesCredentials())).'))
           GROUP BY g.id, g.name, m.id, m.happens_at, e.id, e.name, l.id, l.name, l.city
           ORDER BY g.name, m.happens_at, e.name, l.name';
    $stmt2 = $pdo->prepare($q);
    $stmt2->execute();
    
    return array_merge($stmt1->fetchAll(),$stmt2->fetchAll());
  }
}
