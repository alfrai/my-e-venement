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
  public function executeBatchExceptions(sfWebRequest $request)
  {
    $this->filters = $this->getUser()->getAttribute($this->getModuleName().'.filters', $this->configuration->getFilterDefaults(), 'admin_module');
    $this->filters['excluded_ids'] = $request->getParameter('ids');
    $this->getUser()->setAttribute($this->getModuleName().'.filters', $this->filters, 'admin_module');
  }
  public function executeData(sfWebRequest $request)
  {
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $limit = 10;
    $sql = preg_replace('/^SELECT .* FROM/', '', $this->buildQuery()->removeDqlQueryPart('orderby')->getRawSql());
    
    switch ( $request->getParameter('which', 'referers') ) {
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
    $this->data = $stmt->fetchAll();
    
    // post production
    foreach ( $this->data as $key => $values )
    {
      unset($this->data[$key]);
      $this->data[$values['criteria']] = $values['nb'];
    }
    switch ( $request->getParameter('which', 'referers') ) {
    case 'campaigns':
      foreach ( $this->data as $criteria => $value )
      if ( !$criteria )
        unset($this->data[$criteria]);
      break;
    case 'evolution':
      $start = strtotime(date('Y-m-d'));
      foreach ( $this->data as $criteria => $value )
      {
        if ( strtotime($values['criteria']) < strtotime('-1 month', $start) )
          unset($this->data[$criteria]);
      }
      break;
    }
  }
}
