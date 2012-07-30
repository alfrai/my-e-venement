<?php

/**
 * ManifestationEntry form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ManifestationEntryForm extends BaseManifestationEntryForm
{
  public function configure()
  {
    $this->widgetSchema['entry_id'] = new sfWidgetFormInputHidden();
    $this->widgetSchema['manifestation_id']->setOption('add_empty',true);
    $this->widgetSchema['manifestation_id']->setOption('query',
      Doctrine::getTable('Manifestation')->createQuery('m')
        ->leftJoin('w.GroupWorkspace gw')
        ->andWhere('gw.id IS NOT NULL')
    );
    $this->enableCSRFProtection();
  }
}
