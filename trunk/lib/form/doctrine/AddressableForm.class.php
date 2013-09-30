<?php

/**
 * Addressable form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class AddressableForm extends BaseAddressableForm
{
  public function configure()
  {
    $this->widgetSchema['vcard_uid'] = new sfWidgetFormInputHidden;
    
    if ( is_null($this->object->vcard_uid) )
      unset($this->widgetSchema['vcard_uid']);
  }
}
