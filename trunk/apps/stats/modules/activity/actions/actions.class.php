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
    $this->form->addEventCriterias();
    $this->form->addIntervalCriteria();
    
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
      'ms' => in_array('microsoft',$params['option']),
      'fields' => array('name'),
      'tunnel' => false,
      'noheader' => false,
      'fields'   => array('date','passing','printed','ordered','asked'),
    );
    
    //if ( sfConfig::get('app_ticketting_hide_demands') )
    //  unset($this->options->fields['asked']);
    
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
    $this->dates = $this->getRawData();
    if ( !$request->hasParameter('debug') )
    {
      $this->setLayout('raw');
      sfConfig::set('sf_debug',false);
      $this->getResponse()->setContentType('application/json');
    }
  }
  
  protected function getRawData()
  {
    $criterias = $this->getUser()->getAttribute('stats.criterias',array(),'admin_module');
    $dates['from'] = isset($criterias['dates']) && $criterias['dates']['from']['day'] && $criterias['dates']['from']['month'] && $criterias['dates']['from']['year']
      ? strtotime($criterias['dates']['from']['year'].'-'.$criterias['dates']['from']['month'].'-'.$criterias['dates']['from']['day'])
      : strtotime('- 3 weeks');
    $dates['to']   = isset($criterias['dates']) && $criterias['dates']['to']['day'] && $criterias['dates']['to']['month'] && $criterias['dates']['to']['year']
      ? strtotime($day = $criterias['dates']['to']['year'].'-'.$criterias['dates']['to']['month'].'-'.$criterias['dates']['to']['day'].' 23:59:59')
      : strtotime('+ 1 weeks + 1 day');
    $interval = isset($criterias['interval']) && intval($criterias['interval']) > 0
      ? intval($criterias['interval'])
      : 1;
    
    for ( $days = array($dates['from']) ; $days[count($days)-1]+86400*$interval < $dates['to'] ; $days[] = $days[count($days)-1]+86400*$interval );
    foreach ( $days as $key => $day )
      $days[$key] = date('Y-m-d',$day);
    
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $q = "SELECT d.date, d.date + '$interval days'::interval AS end,
            (SELECT count(id) FROM ticket WHERE (printed_at IS NOT NULL AND printed_at >= d.date::date AND printed_at < d.date + '$interval days'::interval OR integrated_at IS NOT NULL AND integrated_at >= d.date::date AND integrated_at < d.date + '$interval days'::interval) AND duplicating IS NULL AND cancelling IS NULL AND id NOT IN (SELECT cancelling FROM ticket WHERE cancelling IS NOT NULL)) AS printed,
            (SELECT count(id) FROM ticket WHERE printed_at IS NULL AND integrated_at IS NULL AND transaction_id IN (SELECT transaction_id FROM order_table WHERE updated_at >= d.date::date AND updated_at < d.date + '$interval days'::interval) AND duplicating IS NULL AND cancelling IS NULL AND id NOT IN (SELECT cancelling FROM ticket WHERE cancelling IS NOT NULL)) AS ordered,
            (SELECT count(id) FROM ticket WHERE created_at >= d.date::date AND created_at < d.date + '$interval days'::interval AND printed_at IS NULL AND integrated_at IS NULL AND transaction_id NOT IN (SELECT transaction_id FROM order_table) AND duplicating IS NULL AND cancelling IS NULL AND id NOT IN (SELECT cancelling FROM ticket WHERE cancelling IS NOT NULL)) AS asked,
            (SELECT count(t.id) FROM ticket t LEFT JOIN manifestation m ON m.id = t.manifestation_id WHERE happens_at >= d.date::date AND happens_at < d.date + '$interval days'::interval AND (printed_at IS NOT NULL OR integrated_at IS NULL) AND t.duplicating IS NULL AND cancelling IS NULL AND t.id NOT IN (SELECT cancelling FROM ticket WHERE cancelling IS NOT NULL)) AS passing
          FROM (SELECT '".implode("'::date AS date UNION SELECT '",$days)."'::date AS date) AS d
          ORDER BY date";
    $stmt = $pdo->prepare($q);
    $stmt->execute();
    
    return $stmt->fetchAll();
  }
}
