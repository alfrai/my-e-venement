<?php

/**
 * Addressable filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class AddressableFormFilter extends BaseAddressableFormFilter
{
  public function configure()
  {
  }
  public function addNameColumnQuery(Doctrine_Query $q, $field, $values)
  {
    $a = $q->getRootAlias();
    $charset = sfConfig::get('software_internals_charset');
    $transliterate = sfConfig::get('software_internals_transliterate');
    if (is_array($values) && isset($values['text']) && '' != $values['text'])
      $q->addWhere(sprintf("translate(%s.%s,'%s-','%s ') ILIKE ?", $q->getRootAlias(), $field, $transliterate['from'], $transliterate['to']), str_replace('-',' ',iconv($charset['db'],$charset['ascii'],$values['text'])).'%');
    return $q;
  }
  public function addFirstnameColumnQuery(Doctrine_Query $q, $field, $values)
  {
    return $this->addNameColumnQuery($q, $field, $values);
  }
}
