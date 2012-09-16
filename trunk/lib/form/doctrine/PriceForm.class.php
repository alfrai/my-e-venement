<?php

/**
 * Price form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PriceForm extends BasePriceForm
{
  public function configure()
  {
    $q = new Doctrine_Query();
    $q->from('Manifestation m')
      ->leftJoin("m.Event e")
      ->leftJoin("e.MetaEvent me")
      ->leftJoin("m.Location l")
      ->leftJoin("m.PriceManifestations pm")
      ->leftJoin("pm.Price p");
    $this->widgetSchema['manifestations_list']->setOption('query',$q);
    $this->widgetSchema['manifestations_list']->setOption(
      'order_by',
       array('happens_at, e.name','')
    );
    $this->widgetSchema['manifestations_list']->setOption('renderer_class','sfWidgetFormSelectDoubleList');
    $this->widgetSchema['users_list']->setOption('renderer_class','sfWidgetFormSelectDoubleList');
    
    unset($this->widgetSchema['member_cards_list'], $this->validatorSchema['member_cards_list']);
  }
}
