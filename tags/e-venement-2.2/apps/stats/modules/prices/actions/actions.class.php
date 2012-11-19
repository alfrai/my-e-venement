<?php

/**
 * prices actions.
 *
 * @package    e-venement
 * @subpackage prices
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class pricesActions extends sfActions
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
    $this->form->addUsersCriteria();
    $this->form->addEventCriterias();
    if ( is_array($this->getUser()->getAttribute('stats.criterias',array(),'admin_module')) )
      $this->form->bind($this->getUser()->getAttribute('stats.criterias',array(),'admin_module'));
  }
  
  public function executeCsv(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Date','CrossAppLink','Number'));
    $param = $request->getParameter('id');
    
    $this->lines = $this->getPrices($param == 'asked', $param == 'ordered', $param == 'all', 'array');
    
    switch ( $param ) {
    case 'all':
      $this->name = __('Global repartition');
      break;
    case 'ordered':
      $this->name = __('Engaged tickets');
      break;
    case 'asked':
      $this->name = __('Asked tickets');
      break;
    default:
      $this->name = __('Printed tickets');
      break;
    }
    
    $total = 0;
    foreach ( $this->lines as $line )
      $total += $line['nb'];
    
    foreach ( $this->lines as $key => $line )
      $this->lines[$key]['percent'] = format_number(round($line['nb']*100/$total,2));
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => in_array('microsoft',$params['option']['ms']),
      'fields' => array('name','nb','percent'),
      'tunnel' => false,
      'noheader' => false,
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
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Date','CrossAppLink'));
    
    $g = new stGraph();
    $g->bg_colour = '#FFFFFF';
    
    //Set the transparency, line colour to separate each slice etc.
    $g->pie(80,'#78B9EC','{font-size: 12px; color: #78B9EC;');
    
    $prices = $this->getPrices(
      $request->getParameter('id') == 'asked',
      $request->getParameter('id') == 'ordered',
      $request->getParameter('id') == 'all'
    );
    
    $total = 0;
    $names = $data = array();
    foreach ( $prices as $price )
      $total += $price->nb;
    foreach ( $prices as $price )
    {
      $data[] = round($price->nb*100/$total);
      $names[] = $price->name.' ('.$price->nb.')';
    }
    
    $g->pie_values($data,$names);
    $g->pie_slice_colours( array('#d01f3c','#3537a0','#35a088','#d0841f','#cbd01f') );
    
    //To display value as tool tip
    $g->set_tool_tip( __('#x_label# ticket(s): #val#%') );
    
    if ( !$request->hasParameter('debug') )
    {
      echo $g->render();
      return sfView::NONE;
    }
  }
  
  protected function getPrices($asked = false, $ordered = false, $all = false, $type = NULL)
  {
    $criterias = $this->getUser()->getAttribute('stats.criterias',array(),'admin_module');
    $dates['from'] = isset($criterias['dates']) && $criterias['dates']['from']['day'] && $criterias['dates']['from']['month'] && $criterias['dates']['from']['year']
      ? strtotime($criterias['dates']['from']['year'].'-'.$criterias['dates']['from']['month'].'-'.$criterias['dates']['from']['day'])
      : strtotime('- 1 weeks');
    $dates['to']   = isset($criterias['dates']) && $criterias['dates']['to']['day'] && $criterias['dates']['to']['month'] && $criterias['dates']['to']['year']
      ? strtotime($criterias['dates']['to']['year'].'-'.$criterias['dates']['to']['month'].'-'.$criterias['dates']['to']['day'].' 23:59:59')
      : strtotime('+ 3 weeks + 1 day');
    if ( isset($criterias['users']) && count($criterias['users']) > 0 )
    {
      if ( !$criterias['users'][0] )
        array_shift($criterias['users']);
    }
    
    $q = Doctrine::getTable('Price')->createQuery('p')
      ->select('p.id, p.name, p.value, count(t.id) AS nb')
      ->leftJoin('p.Tickets t')
      ->leftJoin('t.Manifestation m')
      ->leftJoin('m.Event e')
      ->leftJoin('t.Transaction tr')
      ->andWhere('(TRUE')
      ->andWhereIn('tr.workspace_id',array_keys($this->getUser()->getWorkspacesCredentials()))
      ->orWhere('tr.workspace_id IS NULL')
      ->andWhere('TRUE)')
      ->andWhereIn('e.meta_event_id',array_keys($this->getUser()->getMetaEventsCredentials()))
      ->andWhere('t.duplicate IS NULL')
      ->andWhere('t.cancelling IS NULL')
      ->andWhere('t.id NOT IN (SELECT tt.cancelling FROM ticket tt WHERE tt.cancelling IS NOT NULL)')
      ->andWhere('m.happens_at > ?',date('Y-m-d H:i:s',$dates['from']))
      ->andWhere('m.happens_at <= ?',date('Y-m-d H:i:s',$dates['to']))
      ->groupBy('p.id, p.name, p.value');
    
    if ( isset($criterias['users']) && count($criterias['users']) > 0 )
      $q->andWhereIn('t.sf_guard_user_id',$criterias['users']);

    if ( !$all )
    {
      $q->andWhere($asked || $ordered ? 'NOT (t.printed OR t.integrated)' : '(t.printed OR t.integrated)');
      if ( $ordered)
        $q->andWhere('t.transaction_id IN (SELECT oo.transaction_id FROM Order oo)');
      if ( $asked )
        $q->andWhere('t.transaction_id NOT IN (SELECT oo.transaction_id FROM Order oo)');
    }
    
    return $type == 'array' ? $q->fetchArray() : $q->execute();
  }
}
