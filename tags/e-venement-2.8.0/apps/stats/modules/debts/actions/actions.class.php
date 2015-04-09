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
    $confcsv = sfConfig::get('software_internals_csv'); if ( isset($confcsv['set_charset']) && $confcsv['set_charset'] ) sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
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
            (SELECT sum(value) FROM ticket WHERE (printed_at IS NOT NULL AND printed_at <= d.date::date OR integrated_at IS NOT NULL AND integrated_at <= d.date::date OR cancelling IS NOT NULL AND created_at <= d.date::date) AND id NOT IN (SELECT cancelling FROM ticket WHERE cancelling IS NOT NULL)) AS outcome,
            (SELECT sum(value) FROM payment WHERE created_at <= d.date::date) AS income
          FROM (SELECT '".implode("'::date AS date UNION SELECT '",$days)."'::date AS date) AS d
          ORDER BY date";
    $stmt = $pdo->prepare($q);
    $stmt->execute();
    
    return $stmt->fetchAll();
  }
}
