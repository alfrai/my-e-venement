<?php

/**
 * OptionLabels form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OptionLabelsForm extends BaseOptionLabelsForm
{
  /**
   * @see OptionForm
   */
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    
    parent::configure();
    $this->model = 'OptionLabels';
    
    self::enableCSRFProtection();
    
    foreach ( array('type','name','value','sf_guard_user_id','created_at','updated_at',) as $id )
    {
      unset($this->widgetSchema   [$id]);
      unset($this->validatorSchema[$id]);
    }
    
    $this->widgets = array(
      '' => array(
        'width'         => array('label' => 'Page width', 'type' => 'number', 'helper' => 'mm', 'default' => '210'),
        'height'        => array('label' => 'Page height', 'type' => 'number', 'helper' => 'mm', 'default' => '297'),
        'nb-x'          => array('label' => 'How many rows', 'type' => 'integer', 'helper' => '', 'default' => '2'),
        'nb-y'          => array('label' => 'How many lines', 'type' => 'integer', 'helper' => '', 'default' => '7'),
        'left-right'    => array('label' => 'Horizontal margins', 'type' => 'number', 'helper' => 'mm', 'default' => '15'),
        'top-bottom'    => array('label' => 'Vertical margins', 'type' => 'number', 'helper' => 'mm', 'default' => '4'),
        'printer-x'     => array('label' => 'Horizontal printer margins', 'type' => 'number', 'helper' => 'mm', 'default' => '14'),
        'printer-y'     => array('label' => 'Vertical printer margins', 'type' => 'number', 'helper' => 'mm', 'default' => '12'),
        'margin-x'      => array('label' => 'Horizontal margin between rows', 'type' => 'number', 'helper' => 'mm', 'default' => '3'),
        'margin-y'      => array('label' => 'Vertical margin between lines', 'type' => 'number', 'helper' => 'mm', 'default' => '0'),
        'padding-x'     => array('label' => 'Horizontal padding', 'type' => 'number', 'helper' => 'mm', 'default' => '2.5'),
        'padding-y'     => array('label' => 'Vertical padding', 'type' => 'number', 'helper' => 'mm', 'default' => '1.5'),
        'font-family'   => array('label' => 'Font', 'type' => 'string', 'helper' => '', 'default' => 'verdana'),
        'font-size'     => array('label' => 'Font size', 'type' => 'integer', 'helper' => 'px', 'default' => '11'),
        'free-css'      => array('label' => 'Free CSS', 'type' => 'string', 'helper' => '', 'default' => ''),
        'free-js'       => array('label' => 'Free JS', 'type' => 'string', 'helper' => '', 'default' => ''),
      ),
    );

    foreach ( $this->widgets as $fieldset )
    foreach ( $fieldset as $name => $value )
    {
      $validator_class = 'sfValidator'.strtoupper(substr($value['type'],0,1)).strtolower(substr($value['type'],1));
      
      $this->widgetSchema[$name]    = new sfWidgetFormInputText(array(
          'label'                 => $value['label'],
          'default'               => $value['default'],
        ),
        array(
          'title'                 => __('default:').' '.$value['default'].' '.$value['helper'],
      ));
      $this->validatorSchema[$name] = new $validator_class(array(
        'required' => false,
      ));
    }
    $this->widgetSchema['free-css']   = new sfWidgetFormTextarea(array('label' => $fieldset['free-css']['label'],));
    $this->widgetSchema['free-js']    = new sfWidgetFormTextarea(array('label' => $fieldset['free-js']['label'],));
  }
  
  
  public static function getDBOptions()
  {
    $r = array();
    
    $r['width'] = 210;
    $r['height'] = 297;
    $r['nb-x'] = 2;
    $r['nb-y'] = 7;
    $r['left-right'] = 15;
    $r['top-bottom'] = 4;
    $r['printer-x'] = 14;
    $r['printer-y'] = 12;
    $r['margin-x'] = 3;
    $r['padding-x'] = 2.5;
    $r['padding-y'] = 1.5;
    $r['font-family'] = 'verdana';
    $r['font-size'] = 11;
    
    foreach ( self::buildOptionsQuery()->fetchArray() as $opt )
      $r[$opt['name']] = $opt['value'];
    return $r;
  }
  
  protected static function buildOptionsQuery()
  {
    return $q = Doctrine::getTable('OptionLabels')->createQuery('ol')
      ->andWhere('ol.sf_guard_user_id IS NULL');
  }
}
