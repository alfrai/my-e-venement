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
    $translit = sfConfig::get('software_internals_transliterate');
    $this->validatorSchema['name'] = new sfValidatorRegex(array(
      'pattern' => '/^[\w\d-\s_%€$£~&@§'.$translit['from'].']+$/',
    ),array(
      'invalid' => __('Some chars are not allowed here',null,'sf_admin'),
    ));

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

    $this->widgetSchema['manifestations_list']
      ->setOption('renderer_class','sfWidgetFormSelectDoubleList');
    $this->widgetSchema['users_list']
      ->setOption('expanded',true)
      ->setOption('order_by',array('username',''));
    $this->widgetSchema['workspaces_list']
      ->setOption('expanded',true)
      ->setOption('order_by',array('name',''));
    unset(
      $this->widgetSchema['member_cards_list'],
      $this->validatorSchema['member_cards_list'],
      $this->widgetSchema['manifestations_list']
    );
  }
}
