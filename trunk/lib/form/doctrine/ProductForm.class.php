<?php

/**
 * Product form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProductForm extends BaseProductForm
{
  protected $user = NULL;
  public function configure()
  {
    parent::configure();
    
    //$this->object->Declinations[] = new ProductDeclination;
    $this->embedRelation('Declinations AS declinations');
    
    $this->embedRelation('PriceProducts AS prices');
    $this->embedRelation('Picture AS picture');
    foreach ( array('name', 'type', 'version', 'height', 'width', 'content_encoding') as $fieldName )
      unset($this->widgetSchema['picture'][$fieldName], $this->validatorSchema['picture'][$fieldName]);
    $this->validatorSchema['picture']['content_file']->setOption('required',false);
    unset($this->widgetSchema['picture_id'], $this->validatorSchema['picture_id']);
    
    $this->widgetSchema   ['prices_list']
      ->setOption('query', $q = Doctrine::getTable('Price')->createQuery('p')
        ->leftJoin('p.PricePOS pos')
        ->andWhere('pos.id IS NOT NULL OR pdt.id IS NOT NULL')
        ->leftJoin('p.Products pdt WITH pdt.id = ?', $this->object->id)
        ->leftJoin('p.PriceProducts ppdt WITH ppdt.product_id = ?', $this->object->id)
      )
      ->setOption('order_by', array('pdt.id IS NULL, ppdt.value DESC, pt.name', ''))
      ->setOption('multiple', true)
      ->setOption('add_empty', false)
      ->setOption('expanded', true)
    ;
    $this->validatorSchema['prices_list']
      ->setOption('query', $q)
      ->setOption('multiple', true)
    ;
    
    $this->widgetSchema['vat_id']->setOption('order_by', array('value', ''));
    
    // LINKS
    $this->widgetSchema   ['linked_manifestations_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Manifestation',
      'url' => cross_app_url_for('event', 'manifestation/ajax'),
      'config' => '{ max: '.sfConfig::get('app_manifestation_depends_on_limit',20).' }',
    ));
    
    $this->widgetSchema   ['linked_prices_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Price',
      'url' => cross_app_url_for('event', 'price/ajax'),
      'config' => '{ max: '.sfConfig::get('app_manifestation_depends_on_limit',20).' }',
    ));
    /*
    // commenting for optimization purposes
    $this->widgetSchema   ['linked_prices_list']
      ->setOption('renderer_class','liWidgetFormSelectDoubleListJQuery')
      ->setOption('query', Doctrine::getTable('Price')->createQuery('p')
        ->leftJoin('p.Users u')
        ->leftJoin('p.Products pdt WITH pdt.id = ?', $this->object->id ? $this->object->id : 0)
        ->where('p.id IS NOT NULL')
      )
    ;
    $this->widgetSchema   ['linked_products_list']
      ->setOption('renderer_class','liWidgetFormSelectDoubleListJQuery')
      ->setOption('query', Doctrine::getTable('Product')->createQuery('p')
        ->andWhere('p.id != ?', $this->object->id ? $this->object->id : 0)
      )
    ;
    */
    $this->widgetSchema   ['linked_products_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Product',
      'url' => cross_app_url_for('pos', 'product/ajax'),
      'config' => '{ max: '.sfConfig::get('app_manifestation_depends_on_limit',20).' }',
    ));
    
    
    // applying the widget's query to its validator
    foreach ( array('linked_prices_list', 'linked_products_list') as $field )
      $this->validatorSchema[$field]
        ->setOption('query', $this->widgetSchema[$field]->getOption('query'));
    
    // ordering
    foreach ( array('meta_event_id', 'linked_prices_list', 'linked_workspaces_list', 'linked_meta_events_list', 'linked_products_list' => 'pt') as $field => $root )
    {
      if ( is_int($field) )
      {
        $field = $root;
        $root = NULL;
      }
      if ( $root )
        $root .= '.';
      $this->widgetSchema[$field]->setOption('order_by', array($root.'name',''));
    }
    
    // USER RELATED CONSTRAINTS
    if ( !sfContext::hasInstance() )
      return;
    $this->user = sfContext::getInstance()->getUser();
    
    $this->widgetSchema['prices_list']->getOption('query')->leftJoin('p.Users pu')->andWhere('pu.id = ?', $this->user->getId());
    
    //$this->widgetSchema['linked_prices_list']->getOption('query')->leftJoin('p.Users pu')->orWhere('pu.id = ?', $this->user->getId());
    foreach ( array(
      'meta_event_id' => 'getMetaEventsCredentials',
      'linked_workspaces_list' => 'getWorkspacesCredentials',
      'linked_meta_events_list' => 'getMetaEventsCredentials',
    ) as $field => $fct )
    if ( method_exists($this->user, $fct) )
      $this->widgetSchema[$field]->setOption('query', Doctrine::getTable($this->widgetSchema[$field]->getOption('model'))
        ->createQuery('a')
        ->orWhereIn('a.id', array_keys($this->user->$fct()))
      );
  }
  
  public function doSave($con = NULL)
  {
    foreach ( array('picture' => array('width' => 500, 'height' => 800),) as $picform_name => $dimensions )
    {
      $file = $this->values[$picform_name]['content_file'];
      unset($this->values[$picform_name]['content_file']);
      
      if (!( $file instanceof sfValidatedFile ))
        unset($this->embeddedForms[$picform_name]);
      else
      {
        // data translation
        $this->values[$picform_name]['content']  = base64_encode(file_get_contents($file->getTempName()));
        $this->values[$picform_name]['name']     = $file->getOriginalName();
        $this->values[$picform_name]['width']    = $dimensions['width'];
        $this->values[$picform_name]['height']   = $dimensions['height'];
        
        $type = PictureForm::getRealType($file);
        $this->values[$picform_name]['type']     = $type['mime'];
        if ( isset($type['content-encoding']) )
          $this->values[$picform_name]['content_encoding'] = $type['content-encoding'];

        $this->values['updated_at'] = date('Y-m-d H:i:s'); // this is a hack to force root object update
      }
    }
    
    return parent::doSave($con);
  }
}
