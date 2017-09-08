<?php if ( $sf_user->hasCredential('tck-member-cards') ): ?>

<?php if ( $form->mcform['member_card_type_id']->getWidget()->getOption('query')->count() > 0 ): ?>
<h3><?php echo __('Associate member cards') ?></h3>
<div class="sf_admin_form_subform member_card">
    <?php foreach ( array('member_card_type_id', 'price_id', 'quantity', 'event_id') as $field ): ?>
    <div class="sf_admin_form_row sf_admin_text member_card_<?php echo $field ?>">
        <?php if ( ! $form->mcform[$field]->getWidget() instanceof sfWidgetFormInputHidden ): ?>
            <label for="member_card_<?php echo $field ?>"><?php echo $form->mcform[$field]->renderLabel() ?></label>
            <div class="label ui-helper-clearfix"><?php echo $form->mcform[$field]->renderHelp() ?></div>
        <?php endif ?>
        <div class="widget "><?php echo $form->mcform[$field] ?></div>
    </div>
    <?php endforeach ?>
    <div class="sf_admin_form_row sf_admin_button member_card_submit">
        <div class="widget">
            <a
                class="fg-button-mini fg-button ui-state-default fg-button-icon-left"
                href="<?php echo url_for('manifestation/associateMemberCards?id='.$form->getObject()->id) ?>"
                onclick="javascript: var form = $('<form>').append($(this).closest('.sf_admin_form_subform').find('.widget').clone()); $.get($(this).prop('href'), form.serialize(), function(json){ console.info(json); $('#transition .close').click(); }); return false;"
            >
                <span class="ui-icon ui-icon-refresh"></span>
                <?php echo __('Associate') ?>
            </a>
        </div>
    </div>
</div>
<?php endif ?>

<h3><?php echo __('Seat pass') ?></h3>

<div class="sf_admin_form_row sf_admin_text member_card_seat">
    <a class="fg-button-mini fg-button ui-state-default fg-button-icon-left"
       href="<?php echo url_for('manifestation/seatMemberCards?id='.$form->getObject()->id) ?>"
       onclick="javascript: $.get($(this).prop('href'), function(json){ console.info(json); $('#transition .close').click(); }); return false;"
    >
        <span class="ui-icon ui-icon-radio-off"></span>
        <?php echo __('Seat pass') ?>
    </a>
</div>


<?php endif ?>
