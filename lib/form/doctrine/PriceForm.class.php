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
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    
    $this->widgetSchema['x_days_valid']->setOption('type', 'number');
    
    $q = new Doctrine_Query();
    $q->from('Manifestation m')
      ->leftJoin("m.Event e")
      ->leftJoin("e.MetaEvent me")
      ->leftJoin("m.Location l")
      ->leftJoin("m.PriceManifestations pm")
      ->leftJoin("pm.Price p");
    $this->validatorSchema['manifestations_list']->setOption('query',$q);
    $this->widgetSchema['manifestations_list']->setOption('query',$q);
    $this->widgetSchema['manifestations_list']->setOption(
      'order_by',
       array('happens_at, e.name','')
    );

    $this->widgetSchema['color_id']
      ->setOption('order_by',array('name',''))
      ->setOption('method', 'getName');
    
    $this->widgetSchema['rank'] = new sfWidgetFormInputHidden;
    $this->widgetSchema['manifestations_list']
      ->setOption('renderer_class','sfWidgetFormSelectDoubleList');
    $this->widgetSchema['workspaces_list']
      ->setOption('expanded',true)
      ->setOption('order_by',array('name',''));
    $this->widgetSchema['users_list']
      ->setOption('expanded',true)
      ->setOption('order_by',array('username',''))
      ->setOption('query', $q = Doctrine::getTable('sfGuardUser')->createQuery('u')
        ->leftJoin('u.UserPrices pu WITH pu.price_id = ?', $this->object->isNew() ? 0 : $this->object->id)
        ->andWhere('u.is_active = ? OR pu.price_id IS NOT NULL', true)
      )
    ;
    unset(
      $this->widgetSchema['gauges_list'],
      $this->widgetSchema['member_cards_list'],
      $this->validatorSchema['member_cards_list'],
      $this->widgetSchema['manifestations_list'],
      $this->widgetSchema['products_list']
    );
    
    // select the current user by default if it is a price creation
    if ( $this->object->isNew() && sfContext::hasInstance() )
      $this->object->Users[] = sfContext::getInstance()->getUser()->getGuardUser();
  }
}
