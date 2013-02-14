<?php

/**
 * Event
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Event extends PluginEvent
{
  public function setUp()
  {
    parent::setUp();
    $this->_table->getTemplate('Doctrine_Template_Searchable')
      ->getPlugin()
      ->setOption('analyzer',new MySearchAnalyzer());
  }
  
  public function getAgeMinHR()
  { return $this->getAgeHR($this->age_min); }
  public function getAgeMaxHR()
  { return $this->getAgeHR($this->age_max); }
  
  public static function getAgeHR($age)
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('I18N'));
    
    // eg. if 18 month / 1.5 years
    if ( floor($age) != $age )
    {
      $r = __('%m% month old',array('%m%' => $age * 12));
    }
    else
    {
      $r = format_number_choice(
        __('[0]|[1]%y% year|(1,+Inf]%y% years old'),
        array('%y%' => floor($age)),
        floor($age)
      );
    }
    return $r;
  }
}
