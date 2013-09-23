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
<?php echo $form->renderFormTag(url_for('ticket/batchIntegrate?manifestation_id='.$manifestation->id),array('id' => 'pay')) ?>
  <div class="ui-widget-content ui-corner-all">
    <div class="fg-toolbar ui-widget-header ui-corner-all">
      <h2><?php echo __('Paying integrated tickets') ?></h2>
    </div>
    <div class="sf_admin_form_row sf_admin_file sf_admin_form_field_price_id <?php echo $form['price_id']->hasError() ? 'ui-state-error' : '' ?> ui-corner-all">
      <?php echo $form['price_id']->renderLabel() ?>
      <span><?php echo $form['price_id'] ?></span>
    </div>
    <div class="sf_admin_form_row sf_admin_file sf_admin_form_field_professional_id <?php echo $form['professional_id']->hasError() ? 'ui-state-error' : '' ?> ui-corner-all">
      <?php echo $form['professional_id']->renderLabel() ?>
      <span><?php echo $form['professional_id'] ?></span>
    </div>
    <div class="sf_admin_form_row sf_admin_file sf_admin_form_field_payment_method_id <?php echo $form['payment_method_id']->hasError() ? 'ui-state-error' : '' ?> ui-corner-all">
      <?php echo $form['payment_method_id']->renderLabel() ?>
      <span><?php echo $form['payment_method_id'] ?></span>
    </div>
    <div class="sf_admin_form_row sf_admin_file sf_admin_form_field_payment_method_id2 <?php echo $form['payment_method_id2']->hasError() ? 'ui-state-error' : '' ?> ui-corner-all">
      <?php echo $form['payment_method_id2']->renderLabel() ?>
      <span><?php echo $form['payment_method_id2'] ?></span>
    </div>
    <div class="sf_admin_form_row sf_admin_file sf_admin_form_field_created_at <?php echo $form['created_at']->hasError() ? 'ui-state-error' : '' ?> ui-corner-all">
      <?php echo $form['created_at']->renderLabel() ?>
      <span><?php echo $form['created_at'] ?></span>
    </div>
    <div class="sf_admin_form_row sf_admin_submit sf_admin_form_field_submit">
      <label><?php echo $form->renderHiddenFields() ?></label>
      <span class="fg-button ui-state-default fg-button-icon-left">
        <span class="ui-icon ui-icon-arrowthickstop-1-s"></span>
        <input type="submit" value="<?php echo __('Valid') ?>" name="" class="ui-widget-content ui-corner-all ui-widget batch" />
      </span>
    </div>
    <div style="clear: both"></div>
  </div>
</form>
