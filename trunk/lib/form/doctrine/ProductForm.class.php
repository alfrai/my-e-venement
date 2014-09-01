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
    if ( !$this->object->isNew() )
    {
      $this->embedRelation('PriceProducts AS prices');
      $this->embedRelation('Picture AS picture');
      foreach ( array('name', 'type', 'version', 'height', 'width', 'content_encoding') as $fieldName )
        unset($this->widgetSchema['picture'][$fieldName], $this->validatorSchema['picture'][$fieldName]);
      $this->validatorSchema['picture']['content_file']->setOption('required',false);
    }
    unset($this->widgetSchema['picture_id'], $this->validatorSchema['picture_id']);
    
    $this->widgetSchema   ['prices_list']
      ->setOption('query', $q = Doctrine::getTable('Price')->createQuery('p')
        ->leftJoin('p.PricePOS pos')
        ->andWhere('pos.id IS NOT NULL OR pdt.id IS NOT NULL')
        ->leftJoin('p.Products pdt WITH pdt.id = ?', $this->object->id)
        ->leftJoin('p.PriceProducts ppdt WITH ppdt.product_id = ?', $this->object->id)
      )
      ->setOption('order_by', array('pdt.id IS NULL, ppdt.value DESC, p.name', ''))
      ->setOption('multiple', true)
      ->setOption('add_empty', false)
      ->setOption('expanded', true)
    ;
    $this->validatorSchema['prices_list']
      ->setOption('query', $q)
      ->setOption('multiple', true)
    ;
    
    if ( !sfContext::hasInstance() )
      return;
    $this->user = sfContext::getInstance()->getUser();
    
    $this->widgetSchema['prices_list']->getOption('query')->leftJoin('p.Users pu')->andWhere('pu.id = ?', $this->user->getId());
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
