          <?php echo $f->renderHiddenFields() ?>
          <p>
            <a href="<?php echo cross_app_url_for('rp','contact/show?id='.$ce->Professional->Contact->id) ?>"><?php echo $ce->Professional->Contact ?></a>
            <span class="professional"><?php echo $ce->Professional->name ? $ce->Professional->name : $ce->Professional->ProfessionalType ?></span>
            <span class="picto"><?php echo $ce->Professional->getRaw('groups_picto') ?></span>
            <a style="float: right" class="fg-button-mini fg-button ui-state-default fg-button-icon-left" href="<?php echo url_for('professional/show?id='.$ce->professional_id) ?>"><span class="ui-icon ui-icon-document"></span><?php echo __('Show','','sf_admin') ?></a>
            <br/>
            <a href="<?php echo cross_app_url_for('rp','organism/show?id='.$ce->Professional->Organism->id) ?>"><?php echo $ce->Professional->Organism ?></a>
            <span class="picto"><?php echo $ce->Professional->Organism->getRaw('groups_picto') ?></span>
            <?php $schema = $f->getWidgetSchema(); $schema['professional_id'] = new sfWidgetFormInputHidden(); echo $f['professional_id'] ?>
          </p>
          <p title="<?php echo __('Note') ?>"><?php echo $f['comment1'] ?></p>
          <p title="<?php echo __('Confirmation comment') ?>"><?php echo $f['comment2'] ?></p>
          <p title="<?php echo __('Confirmed') ?>"><?php echo $f['confirmed'] ?></p>
          <p class="sf_admin_actions">
            <?php echo link_to(__('Delete',array(),'sf_admin'), 'contact_entry/del?id='.$ce->id, array('class' => 'delete')); ?>
            <input type="submit" value="<?php echo __('Save',array(),'sf_admin') ?>" />
          </p>
