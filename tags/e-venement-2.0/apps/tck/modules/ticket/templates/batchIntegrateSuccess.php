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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php include_partial('assets') ?>
<?php include_partial('global/flashes') ?>
<?php use_stylesheet('ticket-integrate') ?>
<?php echo $form->renderFormTag(url_for('ticket/batchIntegrate?manifestation_id='.$manifestation->id),array('class' => 'ui-widget-content ui-corner-all', 'id' => 'batch-integrate')) ?>
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Integrate tickets for %%manifestation%%',array('%%manifestation%%' => $manifestation)) ?></h1>
  </div>
  <div class="sf_admin_actions_block ui-widget">
    <?php include_partial('integrate_actions',array('manifestation' => $manifestation,)) ?>
  </div>
  <div class="ui-widget-content ui-corner-all">
    <div class="sf_admin_form_row sf_admin_file sf_admin_form_field_file">
      <?php echo $form['file']->renderLabel() ?>
      <span><?php echo $form['file'] ?></span>
    </div>
    <div class="sf_admin_form_row sf_admin_radio sf_admin_form_field_filetype">
      <?php echo $form['filetype']->renderLabel() ?>
      <?php echo $form['filetype'] ?>
    </div>
    <div class="sf_admin_form_row sf_admin_radio sf_admin_form_field_gauges_list">
      <?php echo $form['gauges_list']->renderLabel() ?>
      <?php echo $form['gauges_list'] ?>
    </div>
    <div class="sf_admin_form_row sf_admin_text sf_admin_form_field_transaction_ref_id">
      <?php echo $form['transaction_ref_id']->renderLabel() ?>
      #<?php echo $form['transaction_ref_id'] ?>
      <?php echo __("Enter here the id of the transaction where to delete tickets that you're going to integrate. This is not required.") ?>
    </div>
    <div class="sf_admin_form_row sf_admin_submit sf_admin_form_field_submit">
      <label><?php echo $form->renderHiddenFields() ?></label>
      <span class="fg-button ui-state-default fg-button-icon-left">
        <span class="ui-icon ui-icon-arrowthickstop-1-s"></span>
        <input type="submit" value="<?php echo __('Send') ?>" name="" />
      </span>
    </div>
  </div>
</form>
