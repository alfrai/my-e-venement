<?php

/**
 * Price filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PriceFormFilter extends BasePriceFormFilter
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink', 'I18N', 'Asset'));
    
    $this->widgetSchema['manifestations_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Manifestation',
      'url'   => cross_app_url_for('event', 'manifestation/ajax'),
    ));
    
    $this->widgetSchema['users_list']->setOption('query', $q = Doctrine_Query::create()->from('SfGuardUser u'));
    $this->validatorSchema['users_list']->setOption('query', $q);
  }
}
