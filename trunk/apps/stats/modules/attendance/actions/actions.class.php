<?php

/**
 * attendance actions.
 *
 * @package    e-venement
 * @subpackage attendance
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class attendanceActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
  }
  
  public function executeData(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Date','CrossAppLink'));
    
    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->select('*')
      ->addSelect('(SELECT sum(gg.value) FROM gauge gg WHERE m.id = gg.manifestation_id) AS gauge')
      ->addSelect('(SELECT sum(tt.printed AND duplicate IS NULL) FROM ticket tt WHERE m.id = tt.manifestation_id AND tt.id NOT IN (SELECT ttt.cancelling FROM ticket ttt WHERE ttt.cancelling IS NOT NULL AND ttt.manifestation_id = m.id AND ttt.printed = TRUE)) AS printed')
      ->addSelect('(SELECT sum(NOT tt2.printed AND duplicate IS NULL) FROM ticket tt2 WHERE m.id = tt2.manifestation_id AND tt2.id NOT IN (SELECT ttt2.cancelling FROM ticket ttt2 WHERE ttt2.cancelling IS NOT NULL AND ttt2.manifestation_id = m.id) AND tt2.transaction_id IN (SELECT oo.transaction_id FROM order oo)) AS ordered')
      ->addSelect('(SELECT sum(NOT tt3.printed AND duplicate IS NULL) FROM ticket tt3 WHERE m.id = tt3.manifestation_id AND tt3.id NOT IN (SELECT ttt3.cancelling FROM ticket ttt3 WHERE ttt3.cancelling IS NOT NULL AND ttt3.manifestation_id = m.id) AND tt3.transaction_id NOT IN (SELECT oo3.transaction_id FROM order oo3)) AS asked')
      ->andWhere('m.happens_at < ?',date('Y-m-d',strtotime('+ 2 month')))
      ->andWhere('m.happens_at >= ?',date('Y-m-d',strtotime('- 1 month')))
      ->orderBy('m.happens_at, e.name');
    $manifs = $q->execute();
    
    //$bar0 = new stBarOutline( 40, '#7cec78', '#17b912' );
    //$bar0->key( __('Gauge'), 10 );
    $bar1 = new stBarOutline( 40, '#789aec', '#1245b9' );
    $bar1->key( __('Asked tickets'), 10 );
    $bar2 = new stBarOutline( 40, '#eca478', '#fe8134' );
    $bar2->key( __('Engaged tickets'), 10 );
    $bar3 = new stBarOutline( 40, '#ec7890', '#fe3462' );
    $bar3->key( __('Printed tickets'), 10 );
    
    //Passing the random data to bar chart
    $names = array();
    $bar0->data = $bar3->data = array();
    foreach ( $manifs as $manif )
    {
      $names[] = $manif->Event.' @ '.format_date($manif->happens_at);
      
      //$bar0->data[] = $manif->gauge;
      $bar1->add_link(100 * $manif->asked/($manif->gauge != 0 ? $manif->gauge : 1),cross_app_url_for('event','manifestation/show?id='.$manif->id,true));
      $bar2->add_link(100 * $manif->ordered/($manif->gauge != 0 ? $manif->gauge : 1),cross_app_url_for('event','manifestation/show?id='.$manif->id,true));
      $bar3->add_link(100 * $manif->printed/($manif->gauge != 0 ? $manif->gauge : 1),cross_app_url_for('event','manifestation/show?id='.$manif->id,true));
    }
    
    //Creating a stGraph object
    $g = new stGraph();
    $g->title( __('Gauge filling'), '{font-size: 20px;}' );
    $g->bg_colour = '#E4F5FC';
    $g->set_inner_background( '#E3F0FD', '#CBD7E6', 90 );
    $g->x_axis_colour( '#8499A4', '#E4F5FC' );
    $g->y_axis_colour( '#8499A4', '#E4F5FC' );
 
    //Pass stBarOutline object i.e. $bar to graph
    //$g->data_sets[] = $bar0;
    $g->data_sets[] = $bar1;
    $g->data_sets[] = $bar2;
    $g->data_sets[] = $bar3;
 
    //Setting labels for X-Axis
    $g->set_x_labels($names);
 
    // to set the format of labels on x-axis e.g. font, color, step
    $g->set_x_label_style( 10, '#18A6FF', 2, 1 );
 
    // To tick the values on x-axis
    // 2 means tick every 2nd value
    //$g->set_x_axis_steps( 1 );
 
    //set maximum value for y-axis
    //we can fix the value as 20, 10 etc.
    //but its better to use max of data
    $g->set_y_max(100);
    $g->y_label_steps( 4 );
    $g->set_y_legend( __('Percentage on gauge'), 12, '#18A6FF' );
    echo $g->render();
 
    return sfView::NONE;
  }
}
