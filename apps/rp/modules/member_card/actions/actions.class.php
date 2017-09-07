<?php

require_once dirname(__FILE__).'/../lib/member_cardGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/member_cardGeneratorHelper.class.php';

/**
 * member_card actions.
 *
 * @package    e-venement
 * @subpackage member_card
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class member_cardActions extends autoMember_cardActions
{
  public function executeSetSeat(sfWebRequest $request)
  {
    $this->mc = $this->getRoute()->getObject();
    $seat_name = trim($request->getParameter('seat_name', ''));
    
    $this->mc->privileged_seat_name = $seat_name ? $seat_name : NULL;
    $this->mc->save();
  }
  
  public function executeCsv(sfWebRequest $request) {
    $this->getContext()->getConfiguration()->loadHelpers(array('Number','Date'));
    $this->executeIndex($request);
    
    $this->lines = array();
    $member_cards = $this->pager->getQuery()
      ->select('mc.*, c.*')
      ->removeDqlQueryPart('limit')
      ->removeDqlQueryPart('offset')
      ->execute();
    
    foreach ( $member_cards as $mc )
      $this->lines[] = array(
        'num' => $mc->id,
        'name' => $mc->name,
        'contact' => (string)$mc->Contact,
        'address' => $mc->Contact->full_address,
        'email'   => $mc->Contact->email,
        'created_at' => format_date($mc->created_at),
        'expire_at' => format_date($mc->expire_at),
        'value' => format_currency($mc->getValue(),$this->getContext()->getConfiguration()->getCurrency()),
      );
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => in_array('microsoft',$params['option']),
      'tunnel' => false,
      'noheader' => false,
      'fields'   => array('num','name','contact','address','email','created_at','expire_at','value'),
    );
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset = sfConfig::get('software_internals_charset');
    
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
  
  public function checkQRcode($pid)
  {
    // QRcode : {"type":"MemberCard","member_card_id":620}
    $id = null;

    if ( $data = json_decode($pid, true) ) {
      if ( $data['type'] == 'MemberCard' )
      {
        $id = (int)$data['member_card_id'];
      }
    }

    return $id;
  }
  
  public function checkEANcode($pid)
  {
    try { $id = liBarcode::decode_ean($pid); }
    catch ( sfException $e )
    { $id = null; }

    return $id;      
  }
  
  public function executeCheck(sfWebRequest $request)
  {
    $this->type = '';
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url','CrossAppLink'));
    $q = Doctrine::getTable('MemberCard')->retreiveListOfActivatedCards()
      ->select('mc.*, c.*')
      ->leftJoin('c.Archives ca')
      ->addSelect('(SELECT sum(pp.value) FROM Payment pp WHERE pp.member_card_id = mc.id) AS value')
      ->addSelect('(SELECT count(mcp.id) FROM MemberCardPrice mcp WHERE mcp.member_card_id = mc.id) AS nb_prices');
    
    if ( !$pid = $request->getParameter('id') )
      return 'Success';  
    
    $filter = '';
    
    if ( $id = $this->checkQRcode($pid) ) {
      $filter = 'qrcode';
    } else if ( $id = $this->checkEANcode($pid) ) {
      $filter = 'id';      
    } else {
      if ( intval('9'.$pid).'' !== '9'.$pid )
        return 'Success';  
      $id = intval($pid);
      $filter = sfConfig::get('app_cards_id', 'id');
    }

    switch($filter) {
      case 'id':
        $q->andWhere('c.id = ? OR ca.old_id = ?',array($id,$id));
      break;
      case 'qrcode':
        $q->andWhere('mc.id = ?', $id); 
      break;
      default:
        return 'Success';  
    }

    $this->member_cards = $q->orderBy('mc.expire_at > NOW() DESC, CASE WHEN mc.expire_at > NOW() THEN NOW() - mc.expire_at ELSE mc.expire_at - NOW() END DESC, mc.created_at')
      ->execute();
    
    if ( $this->member_cards->count() == 0 )
    {
      $this->type = 'failure';
      return 'Success';
    }
    
    $this->member_card = $this->member_cards[0];
    $this->nb_valid = 0;
    foreach ( $this->member_cards as $mc )
    if ( strtotime($mc->expire_at) > strtotime('now') )
    {
      $this->member_card->checks_count = $this->member_card->checks_count+1;
      $this->member_card->save();
      $this->nb_valid++;
    }
    
    $this->type = $this->member_card && $this->nb_valid > 0
      ? 'success'
      : 'failure';
  }
  public function executeIndex(sfWebRequest $request)
  {
    $this->request = $request;
    parent::executeIndex($request);
  }
  protected function getPager()
  {
    $q = $this->buildQuery();
    if ( isset($this->request) && $this->request->hasParameter('contact_id') )
    {
      $this->forward404Unless( intval($this->request->getParameter('contact_id')) > 0 );
      
      $a = $q->getRootAlias();
      $q->andWhere("$a.contact_id = ?",$this->request->getParameter('contact_id'));
    }
    
    $pager = $this->configuration->getPager('MemberCard');
    $pager->setQuery($q);
    $pager->setPage($this->getPage());
    $pager->init();
    
    return $pager;
  }
  
  public function executeDelete(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $request->checkCSRFProtection();

    $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));
   
    $this->card = $this->getRoute()->getObject();
    $this->contact = $this->card->Contact;
    $this->transaction_id = $this->card->Payments->count() > 0 ? $this->card->Payments[0]->transaction_id : NULL;
    
    try {
      $this->card->delete();
    }
    catch ( liEvenementException $e )
    {
      $this->getUser()->setFlash('error',__('This member card has been used to print tickets'));
      return $this->redirect('contact/card?id='.$this->contact->id);
    }
    
    $this->getUser()->setFlash('notice', 'The item was deleted successfully.');
    
    if ( is_null($this->transaction_id) )
      $this->redirect('contact/card?id='.$this->contact->id);
    else
    {
      $this->getContext()->getConfiguration()->loadHelpers('CrossAppLink');
      $this->redirect(cross_app_url_for('tck','ticket/pay?id='.$this->transaction_id));
    }
  }
}
