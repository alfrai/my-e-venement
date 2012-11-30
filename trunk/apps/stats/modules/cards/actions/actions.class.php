<?php

/**
 * cards actions.
 *
 * @package    e-venement
 * @subpackage cards
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class cardsActions extends sfActions
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
    
    if ( $request->hasParameter('accounting') )
    {
      $this->accounting = $request->getParameter('accounting');
      $this->getUser()->setAttribute('stats.accounting',$this->accounting,'admin_module');
      $this->redirect($this->getContext()->getModuleName().'/index');
    }
    
    $this->form = new StatsCriteriasForm;
    if ( is_array($this->getUser()->getAttribute('stats.criterias',array(),'admin_module')) )
      $this->form->bind($this->getUser()->getAttribute('stats.criterias',array(),'admin_module'));
    
    if ( is_array($this->getUser()->getAttribute('stats.accounting',array(),'admin_module')) )
      $this->accounting = $this->getUser()->getAttribute('stats.accounting',array(),'admin_module');
    
    $this->dates = $this->getDatesCriteria();
    $this->cards = $this->getMembersCards($this->dates['from'],$this->dates['to']);
  }
  
  public function executeCsv(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Date'));
    $this->criterias  = $this->getUser()->getAttribute('stats.criterias',array(),'admin_module');
    $this->accounting = $this->getUser()->getAttribute('stats.accounting',array(),'admin_module');
    
    $dates = $this->getDatesCriteria();
    $this->lines = $this->getMembersCards($dates['from'],$dates['to']);
    
    $total = 0;
    foreach ( $this->lines as $line )
      $total += $line['nb'];
    
    foreach ( $this->lines as $key => $line )
    {
      unset($this->lines[$key][0],$this->lines[$key][1]);
      $this->lines[$key]['percent'] = round($line['nb']*100/$total,1);
      $this->lines[$key]['name'] = __($line['name']);
      $this->lines[$key]['nb'] = round($line['nb']);
      $this->lines[$key]['pit'] = $this->lines[$key]['nb']*$this->accounting['price'][$line['name']];
      if ( isset($this->accounting['vat']) && $this->accounting['vat'] )
      {
        $this->lines[$key]['tep'] = round($this->lines[$key]['pit']/(1+$this->accounting['vat']/100),2);
        $this->lines[$key]['vat'] = $this->lines[$key]['pit'] - $this->lines[$key]['tep'];
      }
    }
    
    $total = array('nb' => 0, 'pit' => 0, 'vat' => 0, 'tep' => 0);
    foreach ( $this->lines as $line )
    {
      $total['nb']  += $line['nb'];
      if ( isset($this->accounting['vat']) && $this->accounting['vat'] )
      {
        $total['tep'] += $line['tep'];
        $total['vat'] += $line['vat'];
      }
      $total['pit'] += $line['pit'];
    }
    $this->lines[] = array(
      'name' => __('Total'),
      'nb' => $total['nb'],
      'percent' => '100',
      'tep' => $total['tep'],
      'vat' => $total['vat'],
      'pit' => $total['pit'],
    );
    
    // the CSV ouput
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => in_array('microsoft',$params['option']['ms']),
      'fields' => array('name','nb','percent','tep','vat','pit'),
      'tunnel' => false,
      'noheader' => false,
    );
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfContext::getInstance()->getConfiguration()->charset;
    
    sfConfig::set('sf_escaping_strategy', false);
    sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
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
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Date'));
    
    // the graph
    $g = new stGraph();
    $g->bg_colour = '#FFFFFF';
    
    //Set the transparency, line colour to separate each slice etc.
    $g->pie(80,'#78B9EC','{font-size: 12px; color: #78B9EC;');
    
    $dates = $this->getDatesCriteria();
    $mc = $this->getMembersCards($dates['from'], $dates['to']);
    
    foreach ( $mc as $value )
    {
      $data[]   = round($value['nb']/365,2);
      $names[]  = __($value['name']);
    }
    
    $g->pie_values($data,$names);
    $g->pie_slice_colours( array('#d01f3c','#3537a0','#35a088','#d0841f','#cbd01f') );
    
    $g->set_tool_tip('#x_label#: #val#');
    
    if ( !$request->hasParameter('debug') )
    {
      echo $g->render();
      return sfView::NONE;
    }
  }
  
  protected function getMembersCards( $from = NULL, $until = NULL )
  {
    // default values
    if ( is_null($from) )
      $from = date('Y-m-d');
    if ( is_null($until) )
      $until = date('Y-m-d',strtotime('1 year'));
    
    // SQL query
    $q = "SELECT name, SUM(
            EXTRACT(epoch FROM CASE WHEN :until::date >= expire_at::date THEN expire_at::date ELSE :until::date END)
            -
            EXTRACT(epoch FROM CASE WHEN expire_at::date - '1 year'::interval >= :from::date THEN (expire_at::date - '1 year'::interval)::date ELSE :from::date END)
          )/60/60/24 AS nb
          FROM member_card
          WHERE expire_at::date - '1 year'::interval <= :from AND expire_at::date >= :from::date
            OR expire_at::date >= :until AND expire_at::date - '1 year'::interval <= :until::date
          GROUP BY name
          ORDER BY name";
    
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $stmt = $pdo->prepare($q);
    $stmt->execute(array('from' => $from, 'until' => $until));
    
    return $stmt->fetchAll();
  }
  
  public function getDatesCriteria()
  {
    $this->criterias = $this->getUser()->getAttribute('stats.criterias',array(),'admin_module');
    
    // dates
    $dates = $this->criterias['dates'];
    if ( isset($dates['from'])
      && isset($dates['from']['day']) && isset($dates['from']['month']) && isset($dates['from']['year'])
      && $dates['from']['day'] && $dates['from']['month'] && $dates['from']['year'] )
      $dates['from'] = $dates['from']['year'].'-'.$dates['from']['month'].'-'.$dates['from']['day'];
    else
      $dates['from'] = date('Y-m-d',strtotime(sfConfig::get('app_cards_expiration_delay').' ago'));
    
    if ( isset($dates['to'])
      && isset($dates['to']['day']) && isset($dates['to']['month']) && isset($dates['to']['year'])
      && $dates['to']['day'] && $dates['to']['month'] && $dates['to']['year'] )
      $dates['to'] = $dates['to']['year'].'-'.$dates['to']['month'].'-'.$dates['to']['day'];
    else
      $dates['to'] = date('Y-m-d');
    
    return $dates;
  }
}
