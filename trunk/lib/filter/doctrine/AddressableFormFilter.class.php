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
    $charset = sfConfig::('sofware_internals_charset');
    print_r($charset);
    die();
    if (is_array($values) && isset($values['text']) && '' != $values['text'])
      $q->addWhere(sprintf('%s.%s ILIKE ?', $q->getRootAlias(), $field), iconv($charset['db'],$charset['ascii'],$values['text']).'%');
    return $q;
  }
  public function addFirstnameColumnQuery(Doctrine_Query $q, $field, $values)
  {
    return $this->addNameColumnQuery($q, $field, $values);
  }
}
