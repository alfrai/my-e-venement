<?php

/**
 * EntryElement form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EntryElementForm extends BaseEntryElementForm
{
  public function configure()
  {
    $this->widgetSchema['manifestation_entry_id'] = new sfWidgetFormInputHidden();
    $this->widgetSchema['contact_entry_id'] = new sfWidgetFormInputHidden();
    $this->enableCSRFProtection();
  }
}
