<?php

/**
 * Event form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EventForm extends BaseEventForm
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink'));
    
    $tinymce = array(
      'width'   => 425,
      'height'  => 300,
    );
    $this->widgetSchema['description'] = new sfWidgetFormTextareaTinyMCE($tinymce);
    $this->widgetSchema['extradesc'] = new sfWidgetFormTextareaTinyMCE($tinymce);
    $this->widgetSchema['extraspec'] = new sfWidgetFormTextareaTinyMCE($tinymce);
    $this->widgetSchema['name'] = new sfWidgetFormTextarea(array(), array('rows' => '1', 'cols' => 58));
    
    $this->widgetSchema['meta_event_id']->setOption('query',EventFormFilter::addCredentialsQueryPart(Doctrine::getTable('MetaEvent')->createQuery('me')));
    $this->widgetSchema['companies_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Organism',
      'url'   => cross_app_url_for('rp','organism/ajax'),
    ));
    
    $this->validatorSchema['duration'] = new sfValidatorString(array('required' => false));
    
    if ( $this->object->isNew() )
    {
      $this->object->id = 12;
      $this->object->Manifestations[] = new Manifestation;
      $this->object->Manifestations[] = new Manifestation;
      $this->embedRelation('Manifestations');
      $order = array(
        'vat_id',
        'duration',
        'location_id',
        'color_id',
        'vat_id',
        'online_limit',
        'no_print',
      );
      foreach ( $this->object->Manifestations as $key => $manif )
      {
        foreach(array(
          'event_id', 'sf_guard_user_id', 'version',
          'workspaces_list', 'prices_list', 'organizers_list',
          'description',
          'depends_on', 'contact_id',
          'ends_at',
          'ExtraInformations',
          'reservation_optional', 'reservation_blocking', 'reservation_ends_at', 'reservation_begins_at',
          'reservation_confirmed', 'reservation_description', 'contact_id', 'booking_list',
        ) as $field )
          unset($this->widgetSchema['Manifestations'][$key][$field]);
        foreach ( $order as $fieldName )
        {
          $field = $this->widgetSchema['Manifestations'][$key][$fieldName];
          unset($this->widgetSchema['Manifestations'][$key][$fieldName]);
          $this->widgetSchema['Manifestations'][$key][$fieldName] = $field;
        }
        $this->widgetSchema['Manifestations'][$key]['no_print']->setLabel('Preprinted ticketting');
        unset($this->validatorSchema['Manifestations'][$key]['event_id']);
      }
    }
    
    parent::configure();
  }
  
  // for embedded Manifestations
  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    foreach ( $taintedValues['Manifestations'] as $key => $manif )
    if ( !(isset($manif['location_id']) && $manif['location_id'])
      || !(isset($manif['happens_at'])  && isset($manif['happens_at']['minute']) && $manif['happens_at']['minute'] && isset($manif['happens_at']['hour']) && $manif['happens_at']['hour'] && isset($manif['happens_at']['day']) && $manif['happens_at']['day'] && isset($manif['happens_at']['month']) && $manif['happens_at']['month'] && isset($manif['happens_at']['year']) && $manif['happens_at']['year'])
      || !(isset($manif['vat_id'])      && $manif['vat_id']) )
    {
      unset(
        $taintedValues['Manifestations'][$key],
        $this->validatorSchema['Manifestations'][$key],
        $this->embeddedForms['Manifestations'][$key],
        $this->object->Manifestations[$key]
      );
    }
    
    return parent::bind($taintedValues, $taintedFiles);
  }
}
