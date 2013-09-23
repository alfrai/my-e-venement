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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php include_stylesheets_for_form($form) ?>
<?php include_javascripts_for_form($form) ?>

<div class="sf_admin_filter ui-helper-reset ui-helper-clearfix" id="sf_admin_filter" title="<?php echo __('Filters')?>">
  <?php if ($form->hasGlobalErrors()): ?>
    <?php echo $form->renderGlobalErrors() ?>
  <?php endif; ?>

  <form action="<?php echo url_for($url, array('action' => 'filter')) ?>" method="post">
    <table>
      <tfoot>
        <tr>
          <td colspan="2">
            <div style="text-align:right">
              <?php echo $form->renderHiddenFields() ?>
              <?php echo link_to(__('Reset', array(), 'sf_admin'), $url, array('action' => 'filter'), array('query_string' => '_reset', 'method' => 'post', 'class' =>  'fg-button ui-state-default ui-corner-all', 'id' => 'sf_admin_filter_reset')) ?>
              <input type="submit" value="<?php echo __('Filter', array(), 'sf_admin') ?>" class="fg-button ui-state-default ui-corner-all" id="sf_admin_filter_submit" />
            </div>
          </td>
        </tr>
      </tfoot>
      <tbody>
        <?php foreach ($configuration->getFormFilterFields($form) as $name => $field): ?>
        <?php if ( is_object($field->getRawValue()) ): ?>
          <?php if ((isset($form[$name]) && $form[$name]->isHidden()) || (!isset($form[$name]) && $field->isReal())) continue ?>
          <?php include_partial('filters_field', array(
            'name'       => $name,
            'attributes' => $field->getConfig('attributes', array()),
            'label'      => $field->getConfig('label'),
            'help'       => $field->getConfig('help'),
            'form'       => $form,
            'field'      => $field,
            'class'      => 'sf_admin_form_row sf_admin_'.strtolower($field->getType()).' sf_admin_filter_field_'.$name,
          )) ?>
        <?php else: ?>
        <?php include_partial('global/filters_fieldset', array(
          'form' => $form, 
          'name'       => $name,
          'form'       => $form,
          'fieldset'      => $field,
        )); ?>
        <?php endif ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </form>
</div>
