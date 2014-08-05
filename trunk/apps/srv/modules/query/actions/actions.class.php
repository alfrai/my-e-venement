<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require_once dirname(__FILE__).'/../lib/queryGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/queryGeneratorHelper.class.php';

/**
 * query actions.
 *
 * @package    e-venement
 * @subpackage query
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class queryActions extends autoQueryActions
{
  public function executeNew(sfWebRequest $request)
  {
    parent::executeNew($request);
    if ( $sid = $request->getParameter('survey-id', false) )
      $this->form->setDefault('survey_id', $sid);
  }
  
  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));
    
    $query = $this->getRoute()->getObject();
    $survey_id = $query->survey_id;
    $query->delete();

    $this->getUser()->setFlash('notice', 'The item was deleted successfully.');

    $this->redirect('survey/edit?id='.$survey_id);
  }
  
  public function executeBackToSurvey(sfWebRequest $request)
  {
    if ( $request->hasParameter('id') )
    {
      $query = Doctrine::getTable('SurveyQuery')->findOneById($request->getParameter('id'));
      $this->redirect('survey/edit?id='.$query->survey_id);
    }
    else
      $this->redirect('@survey');
  }
  
  protected function getData($type = 'choices', $id)
  {
    switch ( $type ) {
    case 'choices':
      $q = Doctrine::getTable('SurveyQueryOption')->createQuery('o')
        ->leftJoin('o.Query q')
        ->leftJoin('q.Answers a WITH a.value = o.value')
        ->andWhere('q.id = ?', $id)
        ->select('o.id, o.value, count(a.id) AS nb')
        ->groupBy('o.id, o.value')
        ->orderBy('o.value')
      ;
      $data = $q->execute();
      break;
    
    case 'numbers':
      $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
      $q = "SELECT a.value, count(a.id) AS nb
            FROM ".Doctrine::getTable('SurveyAnswer')->getTableName()." a
            WHERE a.survey_query_id = :id
            GROUP BY a.value
            ORDER BY a.value DESC";
      $stmt = $pdo->prepare($q);
      $stmt->execute(array(':id' => $this->survey_query->id));
      
      $data = array();
      foreach ( $stmt->fetchAll() as $elt )
        $data[$elt['value']] = $elt['nb'];
      for ( $i = 0 ; $i < max(array_keys($data)) ; $i++ )
      if ( !isset($data[$i]) )
        $data[$i] = 0;
      ksort($data);
    break;
    
    default:
      $data = NULL;
      break;
    }
    
    return $data;
  }
  public function executeData(sfWebRequest $request)
  {
    parent::executeEdit($request);
    
    if ( $request->hasParameter('debug') && $this->getContext()->getConfiguration()->getEnvironment() === 'dev' )
    {
      $this->setLayout('layout');
      $this->getResponse()->setContentType('text/html');
    }
    
    $this->data = $this->getData($this->survey_query->stats, $this->survey_query->id);
    return ucfirst($this->survey_query->stats);
  }
  public function executeCsv(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Number'));
    parent::executeEdit($request);
    
    if ( $request->hasParameter('debug') && $this->getContext()->getConfiguration()->getEnvironment() === 'dev' )
    {
      $this->setLayout('layout');
      $this->getResponse()->setContentType('text/html');
    }
    
    $this->lines = $this->getData($this->survey_query->stats, $this->survey_query->id);
    if ( $this->survey_query->stats !== 'choices' )
    {
      $total = array_sum($this->lines);
      foreach ( $this->lines as $key => $value )
      {
        $this->lines[$key] = array(
          'name' => $key,
          'nb'   => $value,
          'percent' => format_number(round($value/$total*100,2)).'%',
        );
      }
    }
    else
    {
      $total = 0;
      foreach ( $this->lines as $key => $query )
        $total += $query->nb;
      
      foreach ( $this->lines as $key => $query )
        $this->lines[$key] = array(
          'name' => $query->name,
          'nb'   => $query->nb,
          'percent' => format_number(round($query->nb/$total*100,2)).'%',
        );
    }
    
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
    
    return ucfirst($this->survey_query->stats);
  }
  
  public function executeAjax(sfWebRequest $request)
  {
    $charset = sfConfig::get('software_internals_charset');
    $this->filters = true; // hack Beaulieu du 30/09/2013 à valider avant commit
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
    
    $q = Doctrine::getTable('SurveyQuery')
      ->createQuery('q')
      ->orderBy('qt.name')
      ->limit($request->getParameter('limit'));
    $q = Doctrine_Core::getTable('SurveyQuery')
      ->search($search.'*',$q);
    if ( $sid = $request->getParameter('survey_id') )
      $q->andWhere('survey_id = ?', $sid );
    $data = $q->execute()->getData();

    $queries = array();
    foreach ( $data as $query )
      $queries[$query->id] = (string) $query;
    
    return $this->renderText(json_encode($queries));
  }
}
