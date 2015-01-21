<?php

/**
 * PriceTranslation form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PriceTranslationForm extends BasePriceTranslationForm
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    $translit = sfConfig::get('software_internals_transliterate');
    $this->validatorSchema['name'] = new sfValidatorRegex(array(
      'pattern' => '/^[\w\d-\s_%€$£~&@§'.$translit['from'].']+$/',
    ),array(
      'invalid' => __('Some chars are not allowed here',null,'sf_admin'),
    ));
  }
}
<?php

/**
 * PriceTranslation form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PriceTranslationForm extends BasePriceTranslationForm
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    $translit = sfConfig::get('software_internals_transliterate');
    $this->validatorSchema['name'] = new sfValidatorRegex(array(
      'pattern' => '/^[\w\d-\s_%€$£~&@§'.$translit['from'].']+$/',
    ),array(
      'invalid' => __('Some chars are not allowed here',null,'sf_admin'),
    ));
  }
}
<?php

/**
 * PriceTranslation form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PriceTranslationForm extends BasePriceTranslationForm
{
  public function configure()
  {
  }
}
