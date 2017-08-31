<?php

/**
 * SeatedPlan
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class SeatedPlan extends PluginSeatedPlan
{
  public function render(array $gauges, array $attributes = array())
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('CrossAppLink'));
    
    // default values
    foreach ( array(
      'app'               => array('seats' => 'event', 'picture' => 'default'), // if defined as a string, the same app will be used to retrieve picture & seats
      'get-seats'         => isset($attributes['action']) ? $attributes['action'] : 'seated_plan/getSeats',
      'on-demand'         => false,
      'match-seated-plan' => true,
      'add-data-src'      => false,
      'hold-id'           => false
    ) as $key => $value )
    if ( !isset($attributes[$key]) )
      $attributes[$key] = $value;
    
    // if no picture is set
    if ( !$this->picture_id )
      return '';
    
    $img = $this->Picture->render(array(
      'app'   => is_array($attributes['app']) ? $attributes['app']['picture'] : $attributes['app'],
      'title' => $this->Picture,
      'width' => $this->ideal_width ? $this->ideal_width : '',
      'add-data-src' => $attributes['add-data-src'],
    ));
    
    
    $ids = array();
    foreach ( $gauges as $gauge )
      $ids[] = $gauge->id;
    
    // constructs the content of the GET part of the request
    $vars = array();
    if ( $attributes['match-seated-plan'] )
      $vars['id'] = $this->id;
    if ( $attributes['hold-id'] )
      $vars['hold-id'] = $attributes['hold-id'];
    if ( $ids )
      $vars['gauges_list'] = $ids;
    
    // constructs the GET part of the request
    $get = array();
    foreach ( $vars as $name => $value )
    {
      if ( !is_array($value) )
        $get[] = $name.'='.$value;
      else foreach ( $value as $val )
        $get[] = $name.'[]='.$val;
    }
    
    // the link to get back the seats
    $data = '<a
      href="'.cross_app_url_for(is_array($attributes['app']) ? $attributes['app']['seats'] : $attributes['app'], $attributes['get-seats']).($get ? '?'.implode('&', $get) : '').'"
      class="seats-url"
    ></a>';
    
    $canvas = '<canvas class="zones" data-urls-get="'.url_for('seats/getZones?gauge_ids='.json_encode($ids)).'"></canvas>';
            
    
    return '<span
      id="plan-'.$this->id.(count($gauges) > 0 ? '-manif-'.$gauges[0]->manifestation_id : '').'"
      class="seated-plan picture '.($attributes['on-demand'] ? 'on-demand' : '').'"
      style="'.(count($gauges) == 1 ? 'background-color: '.$this->background.';' : '').'"
    >'.$img.$data.$canvas.'</span>';
  }
  
  public function clearLinks()
  {
    $q = Doctrine::getTable('SeatLink')->createQuery('sl')
      ->where('sl.seat1 IN (SELECT s1.id FROM Seat as s1 WHERE s1.seated_plan_id = ?)', $this->id)
      ->orWhere('sl.seat2 IN (SELECT s2.id FROM Seat as s2 WHERE s2.seated_plan_id = ?)', $this->id)
      ->delete();
    $q->execute();
    return $this;
  }
  
  public function getLinks()
  {
    $links = array();
    
    foreach ( Doctrine::getTable('Seat')->createQuery('s')
      ->leftJoin('s.Neighbors n')
      ->andWhere('s.seated_plan_id = ?',$this->id)
      ->orderBy('s.name')
      ->execute() as $seat )
    foreach ( $seat->Neighbors as $neighbor )
    if ( !isset($links[$seat->id.'++'.$neighbor->id]) && !isset($links[$neighbor->id.'++'.$seat->id]) )
      $links[$seat->id.'++'.$neighbor->id] = array(
        $seat,
        $neighbor,
      );
    
    return $links;
  }
}
