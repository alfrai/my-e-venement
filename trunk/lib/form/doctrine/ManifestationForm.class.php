<?php

/**
 * Manifestation form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ManifestationForm extends BaseManifestationForm
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink'));
    $this->widgetSchema['organizers_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Organism',
      'url'   => cross_app_url_for('rp','organism/ajax'),
    ));
    $this->widgetSchema['workspaces_list']->setOption('renderer_class','sfWidgetFormSelectDoubleList');
    $this->widgetSchema['event_id']->setOption('query',EventFormFilter::addCredentialsQueryPart(Doctrine::getTable('Event')->createQuery()));
    $this->widgetSchema['location_id']->setOption('add_empty',true);
    $this->widgetSchema['location_id']->setOption('order_by',array('name',''));
    
    $this->validatorSchema['duration'] = new sfValidatorString(array('required' => false));
    
    $this->widgetSchema['depends_on'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Manifestation',
      'url'   => url_for('manifestation/ajax?except='.$this->object->id),
    ));
  }
  protected function doSave($con = null)
  {
    $this->saveOrganizersList($con);
    if ( $this->isNew() )
      $this->saveWorkspacesList($con);
    
    BaseFormDoctrine::doSave($con);
  }
}
