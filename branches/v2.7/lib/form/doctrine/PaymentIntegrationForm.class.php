<?php

/**
 * Gauge form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PaymentIntegrationForm extends BaseFormDoctrine
{
  protected $manifestation;
  
  public function getModelName()
  {
    return 'Payment';
  }
  
  public function save($con = null)
  {
    $this->object->payment_method_id = $this->getValue('payment_method_id');
    $this->object->created_at = $this->getValue('created_at');
    
    $created_at = $this->getValue('created_at');
    
    $q = new Doctrine_Query;
    $q->from('Transaction t')
      ->leftJoin('t.Tickets tck')
      ->leftJoin('tck.Cancelling c')
      ->leftJoin('tck.Duplicated d')
      ->andWhere('tck.manifestation_id = ?',$this->manifestation->id)
      ->andWhere('c.id IS NULL')
      ->andWhere('d.id IS NULL')
      ->andWhereIn('tck.price_id',$this->getValue('price_id'))
      ->andWhere('tck.integrated_at IS NOT NULL')
      ->andWhere('(SELECT count(*) FROM payment p WHERE p.transaction_id = t.id) = 0')
      ->andWhere('(SELECT count(DISTINCT tck2.manifestation_id) FROM ticket tck2 WHERE tck2.transaction_id = t.id) = 1');
    $transactions = $q->execute();
    
    if ( $transactions->count() == 0 )
    {
      throw new liEvenementException('No ticket integrated to pay...');
    }
    
    $total = $nb = 0;
    foreach ( $transactions as $t )
    {
      $sum = 0;
      foreach ( $t->Tickets as $ticket )
      {
        $sum += $ticket->value;
      }
      $nb += $t->Tickets->count();
      $total += $sum;
      
      if ( $sum > 0 )
      {
        $p = new Payment;
        $p->transaction_id = $t->id;
        $p->value = $sum;
        $p->payment_method_id = $this->getValue('payment_method_id2');
        if ( $created_at )
          $p->created_at = $created_at;
        $p->save();
      }
    }
    
    if ( $total > 0 )
    {
      $this->object->Transaction = new Transaction;
      $this->object->Transaction->professional_id = $this->getValue('professional_id');
      $this->object->Transaction->contact_id = Doctrine::getTable('Contact')->createQuery('c')->andWhere('p.id = ?',$this->getValue('professional_id'))->fetchOne()->id;
      $this->object->value = $total;
      $this->object->payment_method_id = $this->getValue('payment_method_id');
      if ( $created_at )
        $this->object->created_at = $created_at;
      $this->object->save();
      
      // counterpart for equilibrated transaction
      $p = $this->object->copy();
      $p->payment_method_id = $this->getValue('payment_method_id2');
      $p->value = -$p->value;
      $p->id = NULL;
      $p->save();
      
      // messages
      if ( sfContext::hasInstance() )
      {
        sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
        sfContext::getInstance()->getUser()->setFlash('notice',__('Transaction #%%t%% has been created to centralize payments for %%nb%% tickets',array('%%t%%' => $p->transaction_id, '%%nb%%' => $nb)));
      }
    }
    
    return $this->object;
  }
  
  public function __construct(Manifestation $manifestation)
  {
    $this->manifestation = $manifestation;
    $this->object = new Payment;
    parent::__construct();
  }
  
  public function configure()
  {
    $this->widgetSchema->setNameFormat('pay[%s]');
    
    $q = Doctrine::getTable('Price')->createQuery('p')
      ->leftJoin('p.PriceManifestations pm')
      ->andWhere('pm.manifestation_id = ?',$this->manifestation->id);
    
    $this->widgetSchema   ['price_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Price',
      'query' => $q,
      'add_empty' => true,
      'multiple' => true,
      'order_by' => array('name',''),
    ));
    $this->validatorSchema['price_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Price',
      'required'  => true,
      'query' => $q,
      'multiple' => true,
    ));
    
    $this->widgetSchema   ['professional_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Professional',
      'url' => cross_app_url_for('rp','professional/ajax'),
      'label' => 'Organism',
    ));
    $this->validatorSchema['professional_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Professional',
      'required'  => true,
    ));
    
    $this->widgetSchema   ['payment_method_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'PaymentMethod',
      'add_empty' => true,
      'order_by' => array('name',''),
    ));
    $this->validatorSchema['payment_method_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'PaymentMethod',
      'required'  => true,
    ));
    $this->widgetSchema   ['payment_method_id2'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'PaymentMethod',
      'label' => 'Compensatory payment method',
      'add_empty' => true,
      'order_by' => array('name',''),
    ));
    $this->validatorSchema['payment_method_id2'] = new sfValidatorDoctrineChoice(array(
      'model' => 'PaymentMethod',
      'required'  => true,
    ));
    
    /*
    $this->widgetSchema   ['contact_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Contact',
      'url'   => cross_app_url_for('rp','contact/ajax'),
    ));
    $this->validatorSchema['contact_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Contact',
      'required'  => true,
    ));
    $this->widgetSchema   ['organism_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Contact',
      'url'   => cross_app_url_for('rp','organism/ajax'),
    ));
    $this->validatorSchema['organism_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Contact',
      'required'  => true,
    ));
    */
    
    $this->widgetSchema   ['created_at'] = new liWidgetFormJQueryDateText(array(
      'label' => 'Dated',
      'culture' => sfContext::hasInstance() ? sfContext::getInstance()->getUser()->getCulture() : NULL,
    ));
    $this->validatorSchema['created_at'] = new sfValidatorDate(array(
      'required' => false,
    ));
  }
}
