<?php

/**
 * activity actions.
 *
 * @package    e-venement
 * @subpackage activity
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class activityActions extends sfActions
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
    if ( is_array($this->getUser()->getAttribute('stats.criterias',array(),'admin_module')) )
      $this->form->bind($this->getUser()->getAttribute('stats.criterias',array(),'admin_module'));
  }
  
  public function executeCsv(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Date'));
    $this->lines = $this->getRawData();
    
    foreach ( $this->lines as $nb => $line )
      $this->lines[$nb]['date'] = format_date($line['date']);
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => isset($params['option']['ms']),
      'fields' => array('name'),
      'tunnel' => false,
      'noheader' => false,
      'fields'   => array('date','passing','printed','ordered','asked'),
    );
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfContext::getInstance()->getConfiguration()->charset;
    
    sfConfig::set('sf_escaping_strategy', false);
    sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
    if ( !$request->hasParameter('debug') )
    {
      sfConfig::set('sf_web_debug', false);
      $this->getResponse()->setContentType('text/comma-separated-values');
      $this->getResponse()->sendHttpHeaders();
    }
    else
      $this->setLayout(true);
  }
  
  public function executeData(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Date'));
    $dates = $this->getRawData();
    
    $bars = array();
    $bars['printed'] = new stBarOutline(40,'#ec7890','#fe3462');
    $bars['printed']->key(__('Printed'), 10);
    $bars['ordered'] = new stBarOutline(40,'#eca478','#fe8134');
    $bars['ordered']->key(__('Engaged'), 10);
    $bars['asked']   = new stBarOutline(40,'#789aec','#1245b9');
    $bars['asked']  ->key(__('Asked'), 10);
    $bars['passing']  = new stLineHollow(2,4,'#17b912');
    $bars['passing'] ->key(__('Admissions'), 10);
    
    //Passing the random data to bar chart
    $names = $max = array();
    foreach ( $dates as $date )
    {
      $names[] = format_date($date['date']);
      $max[] = max(array($date['printed'],$date['ordered'],$date['asked'],$date['passing']));
      $bars['printed']->data[] = $date['printed'];
      $bars['ordered']->data[] = $date['ordered'];
      $bars['asked']  ->data[] = $date['asked'];
      $bars['passing'] ->data[] = $date['passing'];
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
    $g->set_x_label_style( 10, '#18A6FF', 2, count($names) > 61 ? 2 : 1 );
 
    // To tick the values on x-axis
    // 2 means tick every 2nd value
    //$g->set_x_axis_steps( count($names) < 32 ? 1 : 2 );
 
    //set maximum value for y-axis
    //we can fix the value as 20, 10 etc.
    //but its better to use max of data
    $max = ceil(max($max) / 10) * 10;
    $g->set_y_max($max);
    $g->y_label_steps(10);
    $g->set_y_legend( __('Number of tickets'), 12, '#18A6FF' );
    echo $g->render();
 
    return sfView::NONE;
  }
  
  protected function getRawData()
  {
    $criterias = $this->getUser()->getAttribute('stats.criterias',array(),'admin_module');
    $dates['from'] = $criterias['dates']['from']['day'] && $criterias['dates']['from']['month'] && $criterias['dates']['from']['year']
      ? strtotime($criterias['dates']['from']['year'].'-'.$criterias['dates']['from']['month'].'-'.$criterias['dates']['from']['day'])
      : strtotime('- 3 weeks');
    $dates['to']   = $criterias['dates']['to']['day'] && $criterias['dates']['to']['month'] && $criterias['dates']['to']['year']
      ? strtotime($day = $criterias['dates']['to']['year'].'-'.$criterias['dates']['to']['month'].'-'.$criterias['dates']['to']['day'].' 23:59:59')
      : strtotime('+ 1 weeks + 1 day');
    
    for ( $days = array($dates['from']) ; $days[count($days)-1]+86400 < $dates['to'] ; $days[] = $days[count($days)-1]+86400 );
    foreach ( $days as $key => $day )
      $days[$key] = date('Y-m-d',$day);
    
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $q = "SELECT d.date,
            (SELECT count(id) FROM ticket WHERE updated_at >= d.date::date AND updated_at <= (d.date||' 23:59:59')::timestamp AND (printed OR integrated) AND duplicate IS NULL AND cancelling IS NULL AND id NOT IN (SELECT cancelling FROM ticket WHERE cancelling IS NOT NULL)) AS printed,
            (SELECT count(id) FROM ticket WHERE NOT (printed OR integrated) AND transaction_id IN (SELECT transaction_id FROM accounting WHERE updated_at >= d.date::date AND updated_at <= (d.date||' 23:59:59')::timestamp AND type = 'order') AND duplicate IS NULL AND cancelling IS NULL AND id NOT IN (SELECT cancelling FROM ticket WHERE cancelling IS NOT NULL)) AS ordered,
            (SELECT count(id) FROM ticket WHERE created_at >= d.date::date AND created_at <= (d.date||' 23:59:59')::timestamp AND NOT (printed OR integrated) AND transaction_id NOT IN (SELECT transaction_id FROM accounting WHERE type = 'order') AND duplicate IS NULL AND cancelling IS NULL AND id NOT IN (SELECT cancelling FROM ticket WHERE cancelling IS NOT NULL)) AS asked,
            (SELECT count(t.id) FROM ticket t LEFT JOIN manifestation m ON m.id = t.manifestation_id WHERE happens_at >= d.date::date AND happens_at <= (d.date||' 23:59:59')::timestamp AND (printed OR integrated) AND duplicate IS NULL AND cancelling IS NULL AND t.id NOT IN (SELECT cancelling FROM ticket WHERE cancelling IS NOT NULL)) AS passing
          FROM (SELECT '".implode("' AS date UNION SELECT '",$days)."') AS d
          ORDER BY date";
    $stmt = $pdo->prepare($q);
    $stmt->execute();
    
    return $stmt->fetchAll();
  }
}
