<?php

/**
 * Gauge form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TicketsIntegrationForm extends BaseFormDoctrine
{
  protected $manifestation;
  
  public function getModelName()
  {
    return 'Transaction';
  }
  
  public function __construct(Manifestation $manifestation)
  {
    $this->manifestation = $manifestation;
    parent::__construct();
  }
  
  public function configure()
  {
    $this->widgetSchema->setNameFormat('integrate[%s]');
    
    $filetypes = array(
      'fb' => 'FranceBillet',
      'tkn' => 'Ticketnet',
    );
    $this->widgetSchema   ['filetype'] = new sfWidgetFormChoice(array(
      'choices' => $filetypes,
      'expanded' => true,
      'default' => 'fb',
    ));
    $this->validatorSchema['filetype'] = new sfValidatorChoice(array(
      'choices'   => array_keys($filetypes),
      'required'  => true,
    ));
    
    $this->widgetSchema   ['file'] = new sfWidgetFormInputFile();
    $this->validatorSchema['file'] = new sfValidatorFile(array(
      'required'  => true,
    ));
    
    $this->widgetSchema   ['transaction_ref_id'] = new sfWidgetFormInput(array(
      'label' => 'Reference transaction',
    ));
    $this->validatorSchema['transaction_ref_id'] = new sfValidatorDoctrineChoice(array(
      'required'  => false,
      'model' => 'Transaction',
    ));
    
    for ( $i = 0 ; $i < intval(sfConfig::has('app_tickets_foreign_max_items') ? sfConfig::get('app_tickets_foreign_max_items') : 4) ; $i++ )
    {
      // workspaces
      $this->widgetSchema   ['translation_workspaces_ref'.$i] = new sfWidgetFormInput(array(
        'label' => 'Translation for workspaces',
      ));
      $this->validatorSchema['translation_workspaces_ref'.$i] = new sfValidatorString(array(
        'required'  => false,
      ));
      $this->widgetSchema   ['translation_workspaces_dest'.$i] = new sfWidgetFormDoctrineChoice(array(
        'model' => 'Workspace',
        'query' => $q = Doctrine::getTable('Workspace')->createQuery('ws')->leftJoin('ws.Gauges g')->andWhere('g.manifestation_id = ?',$this->manifestation->id),
        'label' => '',
        'order_by' => array('name',''),
        'add_empty' => true,
      ));
      $this->validatorSchema['translation_workspaces_dest'.$i] = new sfValidatorDoctrineChoice(array(
        'required'  => false,
        'model' => 'Workspace',
        'query' => $q,
      ));
      
      // prices
      $this->widgetSchema   ['translation_prices_ref'.$i] = new sfWidgetFormInput(array(
        'label' => 'Translation for prices',
      ));
      $this->validatorSchema['translation_prices_ref'.$i] = new sfValidatorString(array(
        'required'  => false,
      ));
      $this->widgetSchema   ['translation_categories_ref'.$i] = new sfWidgetFormInput(array(
        'label' => 'Translation for categories',
      ));
      $this->validatorSchema['translation_categories_ref'.$i] = new sfValidatorString(array(
        'required'  => false,
      ));
      $this->widgetSchema   ['translation_prices_dest'.$i] = new sfWidgetFormDoctrineChoice(array(
        'model' => 'Price',
        'query' => $q = Doctrine::getTable('Price')->createQuery('p')->leftJoin('p.Manifestations m')->andWhere('m.id = ?',$this->manifestation->id),
        'label' => '',
        'order_by' => array('name',''),
        'add_empty' => true,
      ));
      $this->validatorSchema['translation_prices_dest'.$i] = new sfValidatorDoctrineChoice(array(
        'required'  => false,
        'model' => 'Price',
        'query' => $q,
      ));
    }
  }
}
