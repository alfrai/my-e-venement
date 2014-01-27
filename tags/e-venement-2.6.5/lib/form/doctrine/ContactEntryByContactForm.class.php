<?php

/**
 * ContactEntryByContact form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ContactEntryByContactForm extends BaseContactEntryForm
{
  public function configure()
  {
    if ( $this->getObject()->isNew() )
      $this->widgetSchema->setNameFormat('contact_entry_new[%s]');
    
    $this->widgetSchema['professional_id'] = new sfWidgetFormInputHidden;
    
    $this->widgetSchema   ['event_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Event',
      'query' => $q = Doctrine::getTable('Event')->retrieveList()->select('e.*'),
      'order_by' => array('name', ''),
    ));
    $this->validatorSchema['event_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Event',
      'query' => $q,
    ));
    
    unset(
      $this->widgetSchema   ['entry_id'],
      $this->validatorSchema['entry_id'],
      $this->widgetSchema   ['comment1'],
      $this->validatorSchema['comment1'],
      $this->widgetSchema   ['comment2'],
      $this->validatorSchema['comment2'],
      $this->widgetSchema   ['transaction_id'],
      $this->validatorSchema['transaction_id'],
      $this->widgetSchema   ['confirmed'],
      $this->validatorSchema['confirmed']
    );
    
    $this->enableCSRFProtection();
  }
  
  public function restoreProfessionalId()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink'));
    $this->widgetSchema['professional_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Professional',
      'url'   => cross_app_url_for('rp', 'professional/ajax'),
    ));
    return $this;
  }
  
  public function setDefault($name, $default)
  {
    if ( $name == 'professional_id' )
    {
        $q = Doctrine_Query::create()
        ->from('Event e1')
        ->select('e1.id')
        ->leftJoin('e1.Manifestations m1')
        ->leftJoin('m1.ManifestationEntries mentry')
        ->leftJoin('mentry.Entry entry')
        ->leftJoin('entry.ContactEntries ce')
        ->andWhere('ce.professional_id = ?', $default);
        
      $this->widgetSchema['event_id']->getOption('query')
        ->andWhere("e.id NOT IN ($q)", $default);
    }
    
    return parent::setDefault($name, $default);
  }
  
  public function doSave($con = null)
  {
    $entry = Doctrine::getTable('Entry')->createQuery('e')
      ->leftJoin('e.Event ev')
      ->andWhere('ev.id = ?',$this->values['event_id'])
      ->fetchOne();
    if ( !$entry )
    {
      $entry = new Entry;
      $entry->event_id = $this->values['event_id'];
      $entry->save();
    }
    
    $this->object->entry_id = $entry->id;
    $this->object->confirmed = false;
    parent::doSave($con);
  }
}
