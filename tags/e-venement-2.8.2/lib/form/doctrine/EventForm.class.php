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
      'config'  => array(
        'extended_valid_elements' => 'html,head,body,hr[class|width|size|noshade],iframe[src|width|height|name|align],style',
        'convert_urls' => false,
        'urlconvertor_callback' => 'email_urlconvertor',
        'paste_as_text' => false,
        'plugins' => 'textcolor link image',
        'toolbar1' => 'formatselect fontselect fontsizeselect | link image | forecolor backcolor | undo redo',
        'toolbar2' => 'bold underline italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote',
        'force_br_newlines' => false,
        'force_p_newlines'  => false,
        'forced_root_block' => '',
      ),
    );
    
    $cultures = sfConfig::get('project_internals_cultures', array('fr' => 'Français'));
    foreach ( $cultures as $culture => $lang )
    {
      foreach ( array('description', 'extradesc', 'extraspec') as $field )
      {
        $this->widgetSchema   [$culture][$field]  = new liWidgetFormTextareaTinyMCE($tinymce);
        $this->validatorSchema[$culture][$field]->setOption('required', false);
      }
      $this->widgetSchema   [$culture]['name'] = new sfWidgetFormTextarea(array(), array('rows' => '1', 'cols' => 58));
      $this->validatorSchema[$culture]['name']->setOption('required', false);
    }
    
    $this->widgetSchema['meta_event_id']
      ->setOption('add_empty', true)
      ->setOption('query',EventFormFilter::addCredentialsQueryPart(Doctrine::getTable('MetaEvent')->createQuery('me')))
      ->setOption('order_by', array('me.name',''));
    $this->widgetSchema['event_category_id']->setOption('order_by', array('name',''));
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
          'reservation_optional', 'blocking', 'reservation_ends_at', 'reservation_begins_at',
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
    
    // pictures & co
    foreach ( array('picture_id' => 'Picture') as $field => $rel )
    {
      $this->embedRelation($rel);
      foreach ( array('name', 'type', 'version', 'height', 'width', 'content_encoding') as $fieldName )
        unset($this->widgetSchema[$rel][$fieldName], $this->validatorSchema[$rel][$fieldName]);
      $this->validatorSchema[$rel]['content_file']->setOption('required',false);
      unset($this->widgetSchema[$field], $this->validatorSchema[$field]);
    }
    
    parent::configure();
  }
  
  public function removeManifestations()
  {
    foreach ( $this->object->Manifestations as $key => $manif )
      unset($this->object->Manifestations[$key], $this->embeddedForms['Manifestations'][$key]);
    unset($this->validatorSchema['Manifestations']);
  }
  
  // for embedded Manifestations
  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    if ( isset($taintedValues['Manifestations']) && is_array($taintedValues['Manifestations']) )
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
    }
    
    return parent::bind($taintedValues, $taintedFiles);
  }
  
  public function doSave($con = NULL)
  {
    if ( sfContext::hasInstance() && !sfContext::getInstance()->getUser()->hasCredential('event-access-all') )
    {
      foreach ( $this->object->Manifestations as $manif )
      if ( $manif->contact_id !== sfContext::getInstance()->getUser()->getContactId() )
        throw new liBookingException('You cannot save an event object that has manifestations which does not belong to you.');
    }
    
    // picture
    foreach ( array('Picture' => array('width' => 200, 'height' => 300)) as $picform_name => $dimensions )
    {
      $file = $this->values[$picform_name]['content_file'];
      unset($this->values[$picform_name]['content_file']);
      
      if (!( $file instanceof sfValidatedFile ))
        unset($this->embeddedForms[$picform_name]);
      else
      {
        // data translation
        $this->values[$picform_name]['content']  = base64_encode(file_get_contents($file->getTempName()));
        $this->values[$picform_name]['name']     = $file->getOriginalName();
        $this->values[$picform_name]['width']    = $dimensions['width'];
        $this->values[$picform_name]['height']   = $dimensions['height'];
        
        $type = PictureForm::getRealType($file);
        $this->values[$picform_name]['type']     = $type['mime'];
        if ( isset($type['content-encoding']) )
          $this->values[$picform_name]['content_encoding'] = $type['content-encoding'];
        
        $this->values['updated_at'] = date('Y-m-d H:i:s'); // this is a hack to force root object update
      }
    }
    
    return parent::doSave($con);
  }
}
