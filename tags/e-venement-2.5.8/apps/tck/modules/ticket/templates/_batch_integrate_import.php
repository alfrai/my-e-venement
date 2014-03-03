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
<?php echo $form->renderFormTag(url_for('ticket/batchIntegrate?manifestation_id='.$manifestation->id),array('id' => 'import')) ?>
  <div class="ui-widget-content ui-corner-all">
    <div class="sf_admin_form_row sf_admin_file sf_admin_form_field_file">
      <?php echo $form['file']->renderLabel() ?>
      <span><?php echo $form['file'] ?></span>
    </div>
    <div class="sf_admin_form_row sf_admin_radio sf_admin_form_field_filetype">
      <?php echo $form['filetype']->renderLabel() ?>
      <?php echo $form['filetype'] ?>
    </div>
    <div class="sf_admin_form_row sf_admin_text sf_admin_form_field_transaction_ref_id">
      <?php echo $form['transaction_ref_id']->renderLabel() ?>
      #<?php echo $form['transaction_ref_id'] ?>
      <?php echo __("Enter here the id of the transaction where to delete tickets that you're going to integrate. This is not required.") ?>
    </div>
    <div class="sf_admin_form_row sf_admin_text sf_admin_form_field_translation_workspaces ui-widget-content ui-corner-all">
      <?php echo $form['translation_workspaces_ref1']->renderLabel() ?>
      <p><?php echo __('Please have a look into the file you want to import to get the proper name, as given by your partnair.') ?></p>
      <?php for ( $i = 0; isset($form['translation_workspaces_ref'.$i]) ; $i++ ): ?>
        <p>
        <?php echo $form['translation_workspaces_ref'.$i] ?>
        &rarr;
        <?php echo $form['translation_workspaces_dest'.$i] ?>
        </p>
      <?php endfor ?>
    </div>
    <div class="sf_admin_form_row sf_admin_text sf_admin_form_field_translation_prices ui-widget-content ui-corner-all">
      <?php echo $form['translation_prices_ref1']->renderLabel() ?>
      <p><?php echo __('Please have a look into the file you want to import to get the proper name, as given by your partnair.') ?></p>
      <?php for ( $i = 0; isset($form['translation_prices_ref'.$i]) ; $i++ ): ?>
        <p>
        <?php echo $form['translation_prices_ref'.$i]->render(array('title' => __($form['translation_prices_ref'.$i]->getWidget()->getLabel()))) ?>
        <?php echo $form['translation_categories_ref'.$i]->render(array('title' => __($form['translation_categories_ref'.$i]->getWidget()->getLabel()))) ?>
        &rarr;
        <?php echo $form['translation_prices_dest'.$i] ?>
        </p>
      <?php endfor ?>
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
