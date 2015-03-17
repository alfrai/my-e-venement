<?php

/**
 * MemberCardType form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MemberCardTypeForm extends BaseMemberCardTypeForm
{
  protected $user;
  
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink'));
    $this->user = sfContext::getInstance()->getUser();
    
    $this->widgetSchema['users_list']
      ->setOption('expanded',true)
      ->setOption('order_by',array('username',''));
    $this->widgetSchema   ['product_declination_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'url' => cross_app_url_for('pos', 'declination/ajax'),
      'model' => 'ProductDeclination',
    ));
    $this->validatorSchema['product_declination_id']->setOption('query', Doctrine::getTable('ProductDeclination')->createQuery('pd')
      ->leftJoin('pd.Product p')
      ->andWhereIn('p.meta_event_id IS NULL OR p.meta_event_id', array_keys($this->user->getMetaEventsCredentials()))
      ->leftJoin('p.Prices price')
      ->leftJoin('price.Users pu')
      ->andWhere('pu.id = ?', $this->user->getId())
    );
  }
}
