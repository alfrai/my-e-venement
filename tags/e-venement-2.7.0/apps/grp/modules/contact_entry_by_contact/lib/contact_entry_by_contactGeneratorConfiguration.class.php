<?php

/**
 * contact_entry_by_contact module configuration.
 *
 * @package    e-venement
 * @subpackage contact_entry_by_contact
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: configuration.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class contact_entry_by_contactGeneratorConfiguration extends BaseContact_entry_by_contactGeneratorConfiguration
{
  public function getFormClass()
  {
    return 'ContactEntryByContactForm';
  }
}
