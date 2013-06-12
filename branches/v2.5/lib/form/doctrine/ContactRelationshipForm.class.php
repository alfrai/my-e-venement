<?php

/**
 * ContactRelationship form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ContactRelationshipForm extends BaseContactRelationshipForm
{
  public function configure()
  {
    $this->widgetSchema['to_contact_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Contact',
      'url'   => cross_app_url_for('rp','contact/ajax'),
    ));
    $this->widgetSchema['to_contact_id']->setLabel('Contact');
    $this->widgetSchema['from_contact_id'] = new sfWidgetFormInputHidden;
    $this->widgetSchema['contact_relationship_type_id']->setLabel('Relationship');
    
    if ( !$this->object->isNew() )
    {
      $this->defaults['url'] = $this->object->url;
      $this->widgetSchema   ['url'] = new sfWidgetFormInputHidden;
      $this->validatorSchema['url'] = new sfValidatorString(array('required' => false));
    }
    $fields = array('to_contact_id', 'contact_relationship_type_id',);
    $this->useFields($fields);
  }
}
