<?php

/**
 * Manifestation filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ManifestationFormFilter extends BaseManifestationFormFilter
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink'));
    $this->widgetSchema['organizers_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Organism',
      'url'   => cross_app_url_for('rp','organism/ajax'),
    ));
  }
}

