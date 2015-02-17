<?php

/**
 * ledger actions.
 *
 * @package    e-venement
 * @subpackage ledger
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ledgerActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->redirect('ledger/cash');
  }
  
  public function executeSales(sfWebRequest $request)
  {
    // because loading this page with a lot of data is really long
    set_time_limit(240);
    ini_set('memory_limit','512M');
    
    $this->options = $criterias = $this->formatCriterias($request);
    $dates = $criterias['dates'];
    
    // redirect to avoid POST re-sending
    if ( $request->getParameter($this->form->getName(),false) )
      $this->redirect('ledger/sales');
    
    require(__DIR__.'/sales-events.php');
    require(__DIR__.'/sales-products.php');
  }
  
  public function executeExtract(sfWebRequest $request)
  {
    return require(dirname(__FILE__).'/extract.php');
  }
  
  public function executeCash(sfWebRequest $request)
  {
    $criterias = $this->formatCriterias($request);
    $this->dates = $criterias['dates'];
    $this->not_a_ledger = false;
    
    // redirect to avoid POST re-sending
    if ( $request->getParameter($this->form->getName(),false) )
      $this->redirect('ledger/cash');
    
    $this->methods = $this->buildCashQuery($criterias)->execute();
    
    $ratios = array();
    if ( isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 
      || isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 )
    {
      $this->not_a_ledger = true;
      foreach ( $this->methods as $method )
      foreach ( $method->Payments as $key => $payment )
      {
        if ( !isset($ratios[$payment->transaction_id]) )
        {
          $q = Doctrine_Query::create()->from('Transaction t')
            ->select('t.id')
            ->addSelect('(SELECT SUM(tck1.value) FROM Ticket tck1 WHERE (tck1.printed_at IS NOT NULL OR tck1.integrated_at IS NOT NULL OR tck1.cancelling IS NOT NULL) AND tck1.duplicating IS NULL AND tck1.transaction_id = t.id) AS total')
            ->andWhere('t.id = ?',$payment->transaction_id);
          if ( isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
            $q->addSelect('(SELECT SUM(tck2.value) FROM Ticket tck2 WHERE (tck2.printed_at IS NOT NULL OR tck2.integrated_at IS NOT NULL OR tck2.cancelling IS NOT NULL) AND tck2.duplicating IS NULL AND tck2.transaction_id = t.id AND tck2.manifestation_id IN ('.implode(',', $criterias['manifestations']).')) AS subtotal');
          if ( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 )
            $q->addSelect('(SELECT SUM(tck2.value) FROM Ticket tck2 LEFT JOIN tck2.Gauge tckg WHERE (tck2.printed_at IS NOT NULL OR tck2.integrated_at IS NOT NULL OR tck2.cancelling IS NOT NULL) AND tck2.duplicating IS NULL AND tck2.transaction_id = t.id AND tckg.workspace_id IN ('.implode(',', $criterias['workspaces']).')) AS subtotal');
          $tr = $q->fetchArray();
          $ratios[$payment->transaction_id] = floatval($tr[0]['total']) > 0 ? $tr[0]['subtotal']/$tr[0]['total'] : 0;
        }
        $payment->ratio = $ratios[$payment->transaction_id];
        if ( $ratios[$payment->transaction_id] == 0 )
          unset($method->Payments[$key]);
      }
    }
  }
  
  public function executeBoth(sfWebRequest $request)
  {
    require dirname(__FILE__).'/both.php';
  }
  
  protected function formatCriterias(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $this->form = new LedgerCriteriasForm();
    $criterias = $request->getParameter($this->form->getName(),array());
    
    // specific for a contact/organism
    foreach ( array('contact_id', 'organism_id') as $param )
    if ( $request->hasParameter($param) )
      $criterias[$param] = $request->getParameter($param);
    
    // session stuff
    if ( $criterias )
      $this->getUser()->setAttribute('ledger.criterias', $criterias, 'tck_module');
    $criterias = $this->getUser()->getAttribute('ledger.criterias',$criterias,'tck_module');
    
    // Hack for form validation
    if ( isset($criterias['users']) && $criterias['users'][0] === '' && count($criterias['users']) == 1 )
      unset($criterias['users']);
    if ( isset($criterias['workspaces']) && $criterias['workspaces'][0] === '' && count($criterias['workspaces']) == 1 )
      unset($criterias['workspaces']);
    
    $this->form->bind($criterias, $request->getFiles($this->form->getName()));
    if ( !$this->form->isValid() )
    {
      $this->getUser()->setFlash('error',__('Submitted values are invalid'));
    }
    
    $dates = array(
      isset($criterias['dates']) && $criterias['dates']['from']['day']
        ? strtotime($criterias['dates']['from']['year'].'-'.$criterias['dates']['from']['month'].'-'.$criterias['dates']['from']['day'])
        : strtotime(sfConfig::has('app_ledger_date_begin') ? sfConfig::get('app_ledger_date_begin').' 0:00' : '1 week ago 0:00'),
      isset($criterias['dates']) && $criterias['dates']['to']['day']
        ? strtotime($criterias['dates']['to']['year'].'-'.$criterias['dates']['to']['month'].'-'.$criterias['dates']['to']['day'])
        : strtotime(sfConfig::has('app_ledger_date_end') ? sfConfig::get('app_ledger_date_end').' 0:00' : 'tomorrow 0:00'),
    );
    
    if ( $dates[0] > $dates[1] )
    {
      $buf = $dates[1];
      $dates[1] = $dates[0];
      $dates[0] = $buf;
    }
    foreach ( $dates as $key => $value )
      $dates[$key] = date('Y-m-d',$value);
    $criterias['dates'] = $dates;
    
    // get all selected criterias
    foreach ( array(
      'manifestations' => 'Manifestation',
      'workspaces' => 'Workspace',
      'users' => 'sfGuardUser',
    ) as $criteria => $table )
    {
      $this->$criteria = false;
      if ( isset($criterias[$criteria]) && is_array($criterias[$criteria]) && $criterias[$criteria][0] )
        $this->$criteria = Doctrine::getTable($table)->createQuery('t')
          ->andWhereIn('t.id',$criterias[$criteria])
          ->execute();
    }
    
    return $criterias;
  }
  
  protected function buildCashQuery($criterias)
  {
    $dates = $criterias['dates'];
    
    $q = Doctrine::getTable('PaymentMethod')->createQuery('m')
      ->leftJoin('m.Payments p')
      ->leftJoin('p.Transaction t')
      ->leftJoin('t.Contact c')
      ->leftJoin('t.Professional pro')
      ->leftJoin('pro.Organism o')
      ->leftJoin('p.User u')
      ->leftJoin('u.MetaEvents me')
      ->leftJoin('u.Workspaces ws')
      ->orderBy('m.name, m.id, t.id, p.value, p.created_at')
      ->select('m.*, p.*, t.*, u.*, c.*, pro.*, o.*');
    
    $q->andWhere('p.created_at >= ? AND p.created_at < ?',array(
      $dates[0],
      $dates[1],
    ));
    
    if ( isset($criterias['payment_limit_with_tck_date']) && $criterias['payment_limit_with_tck_date'] )
    {
      $q->andWhere('t.id IN (SELECT tck.transaction_id FROM Ticket tck WHERE (tck.cancelling IS NULL AND tck.printed_at >= ? AND tck.printed_at < ?) OR (tck.cancelling IS NULL AND tck.integrated_at >= ? AND tck.integrated_at < ?) OR (tck.cancelling IS NOT NULL AND tck.created_at >= ? AND tck.created_at < ?))',array(
        $dates[0],
        $dates[1],
        $dates[0],
        $dates[1],
        $dates[0],
        $dates[1],
      ));
    }
    
    if ( isset($criterias['users']) && is_array($criterias['users']) && isset($criterias['users'][0]) )
      $q->andWhereIn('p.sf_guard_user_id',$criterias['users']);
    
    foreach ( array('contact_id' => 'c.id', 'organism_id' => 'o.id') as $criteria => $field )
    if ( isset($criterias[$criteria]) )
      $q->andWhere($field.' = ?', $criterias[$criteria]);
    
    // restrict access to our own user
    $q = $this->restrictQueryToCurrentUser($q);
    
    return $q;
  }
  
  // restrict access to our own user
  protected static function restrictQueryToCurrentUser($q, $alias = 'u')
  {
    if ( !sfContext::getInstance()->getUser()->hasCredential('tck-ledger-all-users') )
    $q->andWhere($alias.'.id = ?',sfContext::getInstance()->getUser()->getId());
    
    return $q;
  }
}
