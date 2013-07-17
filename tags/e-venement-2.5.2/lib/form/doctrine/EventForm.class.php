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
    if ( sfContext::hasInstance() )
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink'));
    
    $tinymce = array(
      'width'   => 425,
      'height'  => 300,
    );
    $this->widgetSchema['description'] = new sfWidgetFormTextareaTinyMCE($tinymce);
    $this->widgetSchema['extradesc'] = new sfWidgetFormTextareaTinyMCE($tinymce);
    $this->widgetSchema['extraspec'] = new sfWidgetFormTextareaTinyMCE($tinymce);
    $this->widgetSchema['name'] = new sfWidgetFormTextarea(array(), array('rows' => '1', 'cols' => 58));
    
    $this->widgetSchema['companies_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Organism',
      'url'   => cross_app_url_for('rp','organism/ajax'),
    ));
    
    $this->validatorSchema['duration'] = new sfValidatorString(array('required' => false));
    
    $this->widgetSchema['meta_event_id']->setOption('query',EventFormFilter::addCredentialsQueryPart(Doctrine::getTable('MetaEvent')->createQuery('me')));
    
    parent::configure();
  }
}
