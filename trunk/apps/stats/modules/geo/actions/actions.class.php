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
      ->addGroupsCriteria()
      ->removeDatesCriteria();
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
  
  protected function getData()
  {
    $cp = 74700;
    $res = array('exact' => 0, 'department' => 0, 'region' => 0, 'country' => 0, 'others' => 0);
    
    $client = sfConfig::get('app_about_client',array());
    
    $res['exact'] = Doctrine_Query::create()->from('Contact c')
      ->andWhere('c.postalcode = ?', $cp)
      ->andWhere('c.country ILIKE ?', isset($client['country']) ? $client['country'] : 'France')
      ->count();
    
    $res['department'] = Doctrine_Query::create()->from('Contact c')
      ->andWhere('substring(c.postalcode, 1, 2) = substring(?, 1, 2)', $cp)
      ->andWhere('c.country ILIKE ?', isset($client['country']) ? $client['country'] : 'France')
      ->count() - $res['exact'];
    
    $res['region'] = Doctrine_Query::create()->from('Contact c')
      ->andWhere('substring(c.postalcode, 1, 2) IN (SELECT gd.num FROM GeoFrDepartment gd WHERE gd.num = substring(?, 1, 2))', $cp)
      ->andWhere('c.country ILIKE ?', isset($client['country']) ? $client['country'] : 'France')
      ->count() - $res['department'] - $res['exact'];
    
    return $res;
    
    /*
    $criterias = $this->getCriterias();
    if ( isset($criterias['groups_list']) && $criterias['groups_list'] )
      $q->andWhereIn('g.id',$criterias['groups_list']);
    */
  }
}
