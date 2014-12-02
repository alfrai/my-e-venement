<?php

/**
 * Location form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class LocationForm extends BaseLocationForm
{
  /**
   * @see AddressableForm
   */
  public function configure()
  {
    parent::configure();
    
    unset($this->widgetSchema['booked_by_list']);
    $tinymce = array(
      'width'   => 400,
      'height'  => 300,
    );
    $this->widgetSchema['description'] = new liWidgetFormTextareaTinyMCE($tinymce);
  }
}
