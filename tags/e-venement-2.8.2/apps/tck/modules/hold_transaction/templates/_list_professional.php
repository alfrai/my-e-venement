<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  $tform = new sfForm;
  $tform->setDefault('professional_id', $hold_transaction->Transaction->professional_id);
  $tform->setDefault('transaction_id', $hold_transaction->transaction_id);
  $ws = $tform->getWidgetSchema()->setNameFormat('transaction[%s]['.$hold_transaction->transaction_id.']');
  $vs = $tform->getValidatorSchema();
  $ws['professional_id'] = new sfWidgetFormDoctrineChoice(array(
    'model' => 'Professional',
    'add_empty' => true,
    'order_by' => array('o.name',''),
    'query' => Doctrine::getTable('Professional')->createQuery('p')->andWhere('p.contact_id = ?', $hold_transaction->Transaction->contact_id),
    'method' => 'getFullDesc',
  ));
  $vs['professional_id'] = new sfValidatorDoctrineChoice(array(
    'model' => 'Contact',
    'required' => false,
  ));
?>
<?php echo $tform->renderFormTag(url_for('hold_transaction/addProfessional?id='.$hold_transaction->id), array('method' => 'get', 'target' => '_blank')); ?>
  <?php echo $tform->renderHiddenFields() ?>
  <?php echo $tform['professional_id'] ?>
  <?php foreach ( $tform->getJavascripts() as $js ) use_javascript($js) ?>
  <?php foreach ( $tform->getStylesheets() as $css => $type ) use_stylesheet($css, 'last', $type) ?>
</form>
