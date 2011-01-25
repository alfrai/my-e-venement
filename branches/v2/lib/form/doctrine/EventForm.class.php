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
    
    $this->widgetSchema['companies_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Organism',
      'url'   => cross_app_url_for('rp','organism/ajax'),
    ));
    
    $this->validatorSchema['duration'] = new sfValidatorString();
  }
}
