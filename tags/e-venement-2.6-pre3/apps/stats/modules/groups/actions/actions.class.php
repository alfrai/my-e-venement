<?php

/**
 * groups actions.
 *
 * @package    e-venement
 * @subpackage groups
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class groupsActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new StatsCriteriasForm;
    $this->form->addIntervalCriteria();
    $this->form->addGroupsCriteria();
    
    if ( $request->hasParameter('criterias') )
    {
      $this->criterias = $request->getParameter('criterias',array());
      $this->form->bind($this->criterias);
      if ( $this->form->isValid() )
      {
        $this->getUser()->setAttribute('stats.criterias',$this->criterias,'admin_module');
        $this->redirect($this->getContext()->getModuleName().'/index');
      }
      
      die(print_r($this->criterias,true));
      $this->criterias = array();
    }
    
    if ( is_array($this->getUser()->getAttribute('stats.criterias',array(),'admin_module')) )
      $this->form->bind($this->getUser()->getAttribute('stats.criterias',array(),'admin_module'));
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
      : strtotime('- 4 weeks');
    $dates['to']   = isset($criterias['dates']) && $criterias['dates']['to']['day'] && $criterias['dates']['to']['month'] && $criterias['dates']['to']['year']
      ? strtotime($day = $criterias['dates']['to']['year'].'-'.$criterias['dates']['to']['month'].'-'.$criterias['dates']['to']['day'].' 23:59:59')
      : strtotime('+ 2 day');
    $interval = isset($criterias['interval']) && intval($criterias['interval']) > 0
      ? intval($criterias['interval'])
      : 1;
    $groups_list = isset($criterias['groups_list']) && is_array($criterias['groups_list']) && count($criterias['groups_list'])
      ? $criterias['groups_list']
      : array();
    
    for ( $days = array($dates['from']) ; $days[count($days)-1]+86400*$interval < $dates['to'] ; $days[] = $days[count($days)-1]+86400*$interval );
    foreach ( $days as $key => $day )
      $days[$key] = date('Y-m-d',$day);
    
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $where_groups = !$groups_list ? '' : 'group_id IN ('.implode(',',$groups_list).') AND';
    $q = "SELECT d.date, d.date + '$interval days'::interval AS end,
            (SELECT sum(CASE WHEN (SELECT count(yy.id) FROM y_o_b yy WHERE yy.contact_id = gc.contact_id) > 0 THEN (SELECT count(y.id) FROM y_o_b y WHERE y.contact_id = gc.contact_id) ELSE 1 END)::integer      FROM Group_Contact      gc WHERE $where_groups gc.created_at <= d.date::date) +
            (SELECT count(gp.professional_id) FROM Group_Professional gp WHERE $where_groups gp.created_at <= d.date::date) +
            (SELECT count(go.organism_id)     FROM Group_Organism     go WHERE $where_groups go.created_at <= d.date::date) +
            (SELECT count(gd1.*)              FROM Group_Deleted     gd1 WHERE $where_groups gd1.created_at <= d.date::date) -
            (SELECT count(gd2.*)              FROM Group_Deleted     gd2 WHERE $where_groups gd2.updated_at <= d.date::date) AS nb
          FROM (SELECT '".implode("'::date AS date UNION SELECT '",$days)."'::date AS date) AS d
          ORDER BY date";
    $stmt = $pdo->prepare($q);
    $stmt->execute();
    
    return $stmt->fetchAll();
  }
  
  public function executeCsv(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Number'));
    $this->lines = $this->getRawData();
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => in_array('microsoft',$params['option']),
      'fields' => array('date', 'nb',),
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
}
