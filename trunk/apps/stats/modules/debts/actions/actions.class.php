<?php

/**
 * activity actions.
 *
 * @package    e-venement
 * @subpackage activity
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class debtsActions extends sfActions
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
    $this->form->addEventCriterias();
    $this->form->addIntervalCriteria();
    
    if ( is_array($this->getUser()->getAttribute('stats.criterias',array(),'admin_module')) )
      $this->form->bind($this->getUser()->getAttribute('stats.criterias',array(),'admin_module'));
  }
  
  public function executeCsv(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Number','Date'));
    $this->lines = $this->getRawData();
    
    foreach ( $this->lines as $nb => $line )
    {
      $this->lines[$nb]['date'] = format_date($line['date']);
      $this->lines[$nb]['debt'] = format_currency($line['outcome'] - $line['income'],'€');
      $this->lines[$nb]['outcome'] = format_currency(0+$line['income'],'€');
      $this->lines[$nb]['income'] = format_currency(0+$line['outcome'],'€');
    }
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => in_array('microsoft',$params['option']),
      'tunnel' => false,
      'noheader' => false,
      'fields'   => array('date','outcome','income','debt'),
    );
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfConfig::get('software_internals_charset');
    
    sfConfig::set('sf_escaping_strategy', false);
    sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
    if ( $request->hasParameter('debug') )
    {
      $this->getResponse()->sendHttpHeaders();
      $this->setLayout(true);
    }
    else
      sfConfig::set('sf_web_debug', false);
  }
  
  public function executeData(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Date'));
    $dates = $this->getRawData();
    
    $bars = array();
    $bars['debt'] = new stLineHollow(2,4,'#fe3462');
    $bars['debt']->key(__('Debts'), 10);
    
    // Passing the random data to bar chart
    $names = $max = array();
    foreach ( $dates as $date )
    {
      // legend
      $names[] = format_date($date['date']);
      
      // content
      $debt = $date['outcome'] - $date['income'];
      $values[] = $debt;
      $bars['debt']->data[] = $debt;
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
    $max = ceil(max($values) / 10) * 10;
    $min = floor(min($values) / 10) * 10;
    $g->set_y_max($max);
    $g->set_y_min($min);
    $g->y_label_steps(10);
    $g->set_y_legend( __('Debts value'), 12, '#18A6FF' );
    echo $g->render();
    
    return sfView::NONE;
  }
  
  protected function getRawData()
  {
    //$criterias = $this->getUser()->getAttribute('stats.criterias',array(),'admin_module');
    $beginning = strtotime('1 year ago'); //,strtotime('next month',strtotime(date('01-d-Y'))));
    for ( $days = array(
        strtotime(date('01-m-Y',strtotime('next month'))),
        strtotime(date('20-m-Y')),
        strtotime(date('10-m-Y')),
      )
      ; $days[count($days)-1] >= $beginning
      ; $days[] = strtotime('1 month ago',$days[count($days)-3]) );
    foreach ( $days as $key => $day )
      $days[$key] = date('Y-m-d',$day);
    
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $q = "SELECT d.date,
            (SELECT sum(value) FROM ticket WHERE updated_at <= d.date::date AND (printed OR integrated OR cancelling IS NOT NULL) AND duplicate IS NULL) AS outcome,
            (SELECT sum(value) FROM payment WHERE created_at <= d.date::date) AS income
          FROM (SELECT '".implode("'::date AS date UNION SELECT '",$days)."'::date AS date) AS d
          ORDER BY date";
    $stmt = $pdo->prepare($q);
    $stmt->execute();
    
    return $stmt->fetchAll();
  }
}
