<?php

/**
 * Hold filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class HoldFormFilter extends BaseHoldFormFilter
{
  /**
   * @see TraceableFormFilter
   */
  public function configure()
  {
    parent::configure();
    
    $this->widgetSchema['next'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Hold',
      'url'   => url_for('hold/ajax?with=feeders'),
    ));
    $this->widgetSchema['feeder_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Hold',
      'url'   => url_for('hold/ajax?with=next'),
    ));
    $this->validatorSchema['feeder_id'] = $this->validatorSchema['next'];
  }
}
