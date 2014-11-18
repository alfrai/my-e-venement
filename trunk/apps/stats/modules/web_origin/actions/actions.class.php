<?php

require_once dirname(__FILE__).'/../lib/web_originGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/web_originGeneratorHelper.class.php';

/**
 * web_origin actions.
 *
 * @package    e-venement
 * @subpackage web_origin
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class web_originActions extends autoWeb_originActions
{
  public function executeRememberAll(sfWebRequest $request)
  {
    $q = $this->buildQuery();
    echo $q;
    die();
  }
  public function executeBatchExceptions(sfWebRequest $request)
  {
    $this->filters = $this->getUser()->getAttribute($this->getModuleName().'.filters', $this->configuration->getFilterDefaults(), 'admin_module');
    $this->filters['excluded_ids'] = $request->getParameter('ids');
    $this->getUser()->setAttribute($this->getModuleName().'.filters', $this->filters, 'admin_module');
  }
  public function executeData(sfWebRequest $request)
  {
    $this->debug($request);
    $this->data = $this->getData($request->getParameter('which', 'referers'));
  }
  public function executeCsv(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Date','CrossAppLink','Number'));
    
    $debug = $this->debug($request);
    $this->data = $this->getData($request->getParameter('which', 'referers'));
    
    $this->lines = array();
    $total = array_sum($this->data);
    $names = array('referers' => __('Referers'), 'campaigns' => __('Campaigns'), 'deal_done' => __('Done deals'), 'evolution' => __('Activity'));
    foreach ( $this->data as $key => $value )
      $this->lines[] = array(
        'name' => $key,
        'nb'   => $value,
        'percent'   => format_number(round($value*100/$total,2)),
      );
    $this->name = isset($names[$request->getParameter('which', 'referers')])
      ? $names[$request->getParameter('which', 'referers')]
      : $request->getParameter('which', 'referers');
    
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
    $confcsv = sfConfig::get('software_internals_csv');
    if ( isset($confcsv['set_charset']) && $confcsv['set_charset'] )
      sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
  }
  
  protected function debug(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Date', 'I18N'));
    if ( sfConfig::get('sf_web_debug', true) && $request->hasParameter('debug') )
    {
      $this->setLayout('layout');
      $this->getResponse()->setContentType('text/html');
      $this->getResponse()->setHttpHeader('Content-Disposition', NULL);
      $this->getResponse()->sendHttpHeaders();
    }
    else
      sfConfig::set('sf_web_debug', false);
    
    return sfConfig::get('sf_web_debug', false);
  }
  protected function getData($which = 'referers')
  {
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $limit = 10;
    $dql = $this->buildQuery()->removeDqlQueryPart('orderby');
    $sql = preg_replace('/^SELECT .* FROM/', '', $dql->getRawSql());
    
    switch ( $which ) {
    case 'referers':
      $domain = "regexp_replace(w.referer, '".WebOriginFormFilter::SQL_REGEX_URL_FORMAT."', '\\2', 'ix')";
      $q = "SELECT $domain AS criteria, count(w.id) AS nb FROM $sql GROUP BY $domain";
      break;
    case 'campaigns':
      $q = "SELECT w.campaign AS criteria, count(w.id) AS nb FROM $sql GROUP BY w.campaign";
      $limit++; // to remove the empty campaign in the post production
      break;
    case 'deal_done':
      $criteria = '(SELECT ppp.id IS NOT NULL OR ooo.id IS NOT NULL FROM transaction ttt LEFT JOIN payment ppp ON ppp.transaction_id = ttt.id LEFT JOIN order_table ooo ON ooo.transaction_id = ttt.id WHERE ttt.id = w.transaction_id)';
      $q = "SELECT $criteria AS criteria, count(w.id) AS nb FROM $sql GROUP BY $criteria";
      break;
    case 'evolution':
      $criteria = "date_trunc('day', w.created_at)";
      $q = "SELECT $criteria AS criteria, count(w.id) AS nb FROM $sql GROUP BY $criteria";
      $limit = 30;
    }
    
    $q .= " ORDER BY count(w.id) DESC LIMIT $limit";
    $stmt = $pdo->prepare($q);
    $stmt->execute();
    $data = $stmt->fetchAll();
    $this->type = 'pie';
    
    // post production
    foreach ( $data as $key => $values )
    {
      unset($data[$key]);
      $data[$values['criteria']] = $values['nb'];
    }
    switch ( $which ) {
    case 'campaigns':
      foreach ( $data as $criteria => $value )
      if ( !$criteria )
        unset($data[$criteria]);
    case 'referers':
      if ( ($value = $dql->count() - array_sum($data)) > 0 )
        $data[__('empty information')] = $dql->count() - array_sum($data); // the rest
      break;
    case 'evolution':
      $this->type = 'line';
      $start = strtotime(date('Y-m-d'));
      foreach ( $data as $criteria => $value )
      {
        if ( strtotime($values['criteria']) < strtotime('-1 month', $start) )
          unset($data[$criteria]);
      }
      
      // completing empty days
      for ( $i = 0 ; $i < 31 ; $i++ )
      {
        $key = date('Y-m-d 00:00:00', strtotime("-$i day"));
        $data[$key] = isset($data[$key]) ? $data[$key] : 0;
      }
      ksort($data);
      $tmp = array();
      foreach ( $data as $key => $value )
        $tmp[format_date($key)] = $value; // to have human readable dates
      $data = $tmp;
      break;
    }
    
    return $data;
  }
}
