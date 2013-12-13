<?php

require_once dirname(__FILE__).'/../lib/transactionGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/transactionGeneratorHelper.class.php';

/**
 * transaction actions.
 *
 * @package    e-venement
 * @subpackage transaction
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class transactionActions extends autoTransactionActions
{
  public function executeEdit(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
    parent::executeEdit($request);
    
    $this->form = array();
    
    // Contact
    $this->form['contact_id'] = new sfForm;
    $this->form['contact_id']->setDefault('contact_id', $this->transaction->contact_id);
    $ws = $this->form['contact_id']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['contact_id']->getValidatorSchema();
    $ws['contact_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Contact',
      'url'   => cross_app_url_for('rp', 'contact/ajax'),
    ));
    $vs['contact_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Contact',
      'required' => false,
    ));
    
    // Professional
    $this->form['professional_id'] = false;
    $this->form['professional_id'] = new sfForm;
    $this->form['professional_id']->setDefault('professional_id', $this->transaction->professional_id);
    $ws = $this->form['professional_id']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['professional_id']->getValidatorSchema();
    $ws['professional_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Professional',
      'add_empty' => true,
      'query' => Doctrine::getTable('Professional')->createQuery('p')->andWhere('c.id = ?',$this->transaction->contact_id),
      'method' => 'getFullDesc',
    ));
    $vs['professional_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Professional',
      'required' => false,
    ));
    
    $this->form['description'] = new sfForm;
    $this->form['description']->setDefault('description', $this->transaction->description);
    $ws = $this->form['description']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['description']->getValidatorSchema();
    $ws['description'] = new sfWidgetFormTextarea();
    $vs['description'] = new sfValidatorString(array('required' => false,));
  }
  
  public function executeComplete(sfWebRequest $request)
  {
    // initialization
    $this->executeEdit($request);
    
    // prepare response
    $this->json = array(
      'error' => array(false, ''),
      'success' => array(
        'success_fields' => array(),
        'error_fields'   => array(),
      ),
      'base_model' => 'transaction',
    );
    
    // get back data
    $params = $request->getParameter('transaction',array());
    if (!( is_array($params) && count($params) > 0 ))
      $this->json['error'] = array('true', 'The given data is incorrect');
    
    if ( !isset($params['_csrf_token']) )
    {
      $this->json['error'] = array(true, 'No CSRF tocken given.');
      return '';
    }
    
    // direct transaction's fields
    foreach ( array('contact_id', 'professional_id', 'description') as $field )
    if ( isset($params[$field]) )
    {
      $this->form[$field]->bind(array($field => $params[$field], '_csrf_token' => $params['_csrf_token']));
      if ( $this->form[$field]->isValid() )
      {
        $this->json['success']['success_fields'][$field] = array(
          'data' => $params[$field],
          'content' => array(
            'url'   => '',
            'text'  => '',
            'load'  => array(
              'target' => NULL,
              'type'   => NULL,
              'data'   => NULL,
              'reset'  => true,
              'default'=> NULL,
            ),
          ),
        );
        
        // data to bring back
        switch($field) {
        case 'contact_id':
          $this->json['success']['success_fields'][$field]['content']['load']['target'] = '#li_transaction_field_professional_id select:first';
          $this->json['success']['success_fields'][$field]['content']['load']['type']   = 'options';
          
          if ( $params[$field] )
          {
            $object = Doctrine::getTable('Contact')->findOneById($params[$field]);
            foreach ( $object->Professionals as $pro )
              $this->json['success']['success_fields'][$field]['content']['load']['data'][$pro->id]
                = $pro->full_desc;
            $this->json['success']['success_fields'][$field]['content']['load']['default'] = $this->transaction->professional_id;
            
            $this->json['success']['success_fields'][$field]['content']['url']  = cross_app_url_for('rp', 'contact/show?id='.$params[$field], true);
            $this->json['success']['success_fields'][$field]['content']['text'] = (string)$object;
          }
          break;
        }
        
        $this->transaction->$field = $params[$field] ? $params[$field] : NULL;
        $this->transaction->save();
      }
      else
      {
        $this->json['success']['error_fields'][$field] = (string)$this->form[$field]->getErrorSchema();
      }
    }
    
    $this->setTemplate('json');
    $this->dealWithDebugMode($request);
    return '';
  }
  
  /**
   * function executeGetManifestations
   * @param sfWebRequest $request, given by the framework
   * @return ''
   * @display a json array containing :
   * json:
   *   [manifestation_id]: integer
   *     name: string
   *     happens_at: string (PGSQL format)
   *     ends_at: string
   *     event_url:  xxx (absolute) link
   *     manifestation_url:  xxx (absolute) link
   *     location: string
   *     location_url: xxx (absolute) link
   *     gauge_url: xxx (absolute) data to display the global gauge
   *     gauges:
   *       [gauge_id]: integer
   *         name: xxx
   *         gauge_url: xxx (absolute) data to display the gauge
   *         seated_plan_url: xxx (optional) the absolute path to the plan's picture
   *         seated_plan_seats_url: xxx (optional) the absolute path to the seats definition and allocation
   *         prices:
   *           [id-cancellation_state-printed_state]: integer
   *             printed: boolean
   *             cancelling: boolean
   *             qty: integer, the quantity of ticket
   *             pit: float, the price including taxes
   *             vat: float, the current VAT value
   *             tep: float, the price excluding taxes
   *             price_name: string, the price's name
   *             [ids]:
   *               tickets' id
   *             [numerotation]:
   *               tickets' numerotation
   **/
  public function executeGetManifestations(sfWebRequest $request)
  {
    parent::executeShow($request);
    $this->json = array();
    
    foreach ( $this->transaction->Tickets as $ticket )
    {
      // by manifestation
      if ( !isset($this->json[$ticket->Gauge->manifestation_id]) )
        $this->json[$ticket->Gauge->manifestation_id] = array(
          'name' => (string)$ticket->Gauge->Manifestation->Event,
          'event_url' => cross_app_url_for('event', 'event/show?id='.$ticket->Gauge->Manifestation->event_id, true),
          'happens_at' => (string)$ticket->Gauge->Manifestation->happens_at,
          'ends_at' => (string)$ticket->Gauge->Manifestation->ends_at,
          'manifestation_url'  => cross_app_url_for('event', 'manifestation/show?id='.$ticket->Gauge->manifestation_id,true),
          'location' => (string)$ticket->Manifestation->Location,
          'location_url' => cross_app_url_for('event', 'location/show?id='.$ticket->Manifestation->location_id,true),
          'gauge_url' => cross_app_url_for('event','',true),
        );
      
      // by manifestation's gauge
      if ( !isset($this->json[$ticket->Gauge->manifestation_id]['gauges']) )
        $this->json[$ticket->Gauge->manifestation_id]['gauges'] = array();
      if ( !isset($this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]) )
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id] = array(
          'name' => (string)$ticket->Gauge->Workspace,
          'gauge_url' => cross_app_url_for('event','',true)
        );
      if ( $seated_plan = $ticket->Manifestation->Location->getWorkspaceSeatedPlan($ticket->Gauge->workspace_id) )
      {
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['seated_plan_url']
          = cross_app_url_for('default', 'picture/display?id='.$seated_plan->picture_id,true);
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['seated_plan_seats_url']
          = cross_app_url_for('event',   'seated_plan/getSeats?id='.$seated_plan->id.'&gauge_id='.$ticket->gauge_id.'&transaction_id='.$this->transaction->id,true);
      }
      
      // by price
      if ( !isset($this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices']) )
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'] = array();
      $pname = $ticket->price_id.'-'
        .($ticket->cancelling ? 'cancel' : 'normal').'-'
        .($ticket->printed_at || $ticket->integrated_at ? 'done' : 'todo')
      ;
      if ( !isset($this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]) )
        $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname] = array(
          'printed' => $ticket->printed_at || $ticket->integrated_at,
          'cancelling' => $ticket->cancelling ? true : false,
          'qty' => 0,
          'pit' => 0,
          'vat' => 0,
          'tep' => 0,
          'price_name' => '',
          'ids' => array(),
          'numerotation' => array()
        );
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['ids'][] = $ticket->id;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['numerotation'][] = $ticket->numerotation;
      
      // by group of tickets
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['price_name'] = $ticket->price_name;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['qty']++;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['pit'] += $ticket->value;
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['tep'] += $tep = round($ticket->value/(1+$ticket->vat),2);
      $this->json[$ticket->Gauge->manifestation_id]['gauges'][$ticket->gauge_id]['prices'][$pname]['vat'] += $ticket->value - $tep;
    }
    
    $this->dealWithDebugMode($request);
    $this->setTemplate('json');
    return '';
  }
  
  public function executeNew(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('I18N'));
    parent::executeNew($request);
    
    $this->transaction->save();
    
    $this->getUser()->setFlash('success', __('Transaction created'));
    $this->redirect('transaction/edit?id='.$this->transaction->id);
  }
  public function executeShow(sfWebRequest $request)
  { $this->redirect('transaction/edit?id='.$request->getParameter('id')); }
  public function executeBatchDelete(sfWebRequest $request)
  { $this->forward404('You are not supposed to be here...'); }
  public function executeDelete(sfWebRequest $request)
  { $this->forward404('You are not supposed to be here...'); }
  public function executeCreate(sfWebRequest $request)
  { $this->forward404('You are not supposed to be here...'); }
  public function executeUpdate(sfWebRequest $request)
  { $this->forward404('You are not supposed to be here...'); }
  
  protected function dealWithDebugMode(sfWebRequest $request)
  {
    sfConfig::set('sf_debug',false);
    if ( $request->hasParameter('debug') && $this->getContext()->getConfiguration()->getEnvironment() == 'dev' )
    {
      $this->getResponse()->setContentType('text/html');
      sfConfig::set('sf_debug',true);
      $this->setLayout('layout');
    }
  }
}
