<?php

/**
 * Option form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OptionForm extends sfForm
{
  protected $model = NULL;
  public $widgets = array();
  
  public function configure()
  {
  }
  
  public function getModel()
  {
    return $this->model;
  }
  
  public function save($user_id = NULL, $params = NULL)
  {
    if ( !$this->model )
      throw new liEvenementException('No model given to the form');
    
    if ( !$params ) $params = $this->getValues();
    
    $q = Doctrine_Query::create()
      ->delete($this->model);
    if ( $user_id )
      $q->where('sf_guard_user_id = ?',$user_id);
    else
      $q->where('sf_guard_user_id IS NULL');
    $q->execute();
    
    $cpt = 0;
    foreach ( $params as $name => $values )
    {
      if ( !is_array($values) )
        $values = array($values);
      foreach ( $values as $value )
      if ( !is_null($value) )
      {
        $opt = new $this->model();
        $opt->sf_guard_user_id = $user_id;
        $opt->name  = $name;
        $opt->value = $value;
        $opt->save();
        $cpt++;
      }
    }
    
    return $cpt;
  }
}
