<?php

/**
 * ContactEntry form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ContactEntryForm extends BaseContactEntryForm
{
  public function configure()
  {
    if ( $this->getObject()->isNew() )
      $this->widgetSchema->setNameFormat('contact_entry_new[%s]');
    
    $this->widgetSchema['entry_id'] = new sfWidgetFormInputHidden();
    $this->widgetSchema['professional_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Professional',
      'url'   => cross_app_url_for('rp','professional/ajax'),
    ));
    
    $this->widgetSchema['comment1'] = new sfWidgetFormInputText(array(
    ));
    $this->widgetSchema['comment2'] = new sfWidgetFormInputText(array(
      'label' => 'Note',
    ));
    
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputHidden;
    
    $this->enableCSRFProtection();
  }
  
  public function reduce()
  {
    foreach ( array('comment1','professional_id',) as $field )
      $this->widgetSchema[$field] = new sfWidgetFormInputHidden;
  }
}
