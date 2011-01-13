<?php

/**
 * Contact form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ContactForm extends BaseContactForm
{
  /**
   * @see AddressableForm
   */
  public function configure()
  {
    //unset($this->widgetSchema['emails_list']);
    //unset($this->validatorSchema['emails_list']);
    
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Asset'));
    use_javascript('/sfFormExtraPlugin/js/double_list.js');
    
    $this->widgetSchema   ['YOBs_list'] = new sfWidgetFormInputText(array('default' => $this->object->getYOBsString()));
    $this->validatorSchema['YOBs_list'] = new sfValidatorString(array('required' => false));
    
    $this->widgetSchema   ['title']     = new sfWidgetFormDoctrineJQueryAutocompleterGuide(array(
      'model' => 'TitleType',
      'url'   => url_for('title_type/ajax'),
      'method_for_query' => 'findOneByName',
    ));
    
    $this->widgetSchema['groups_list']->setOption(
      'order_by',
      array('u.id IS NULL DESC, u.username, name','')
    );
    
    parent::configure();
  }
  
  public function save($con = null)
  {
    $obj = $this->object;
    $r = parent::save($con);
    
    // get back given values
    $given = explode(',',str_replace(' ','',$this->getValue('YOBs_list')));
    
    // get back existing records
    $indb = array();
    foreach ( $obj->YOBs as $YOB )
      $indb[$YOB->id] = $YOB;
    
    // forget all values / records which are already recorded
    foreach ( $given as $key => $value )
    if ( ($id = array_search($value,$indb)) !== false )
    {
      unset($indb[$id]);
      unset($given[$key]);
    }
    
    // remove all existing records which have not been committed
    foreach ( $indb as $id => $YOB )
      $YOB->delete($con);
    
    // add all values committed which are not in DB
    foreach ( $given as $key => $value )
    if ( intval($value) )
    {
      $YOB = new YOB();
      $YOB->year = $value;
      $YOB->contact_id = $this->object->id;
      $YOB->save($con);
    }
    
    return $r;
  }
}
