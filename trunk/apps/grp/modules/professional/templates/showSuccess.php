<?php use_helper('I18N', 'Date', 'CrossAppLink') ?>
<?php include_partial('assets') ?>

<div id="sf_admin_container" class="li_grp_professional sf_admin_show ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Contact file', array(), 'messages') ?></h1>
  </div>
  
  <?php include_partial('professional/flashes') ?>

    <div class="sf_admin_actions_block ui-widget">
      <?php include_partial('form_actions', array('professional' => $professional, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
    </div>

  <div class="ui-helper-clearfix"></div>

  <p class="contact">
    <?php echo cross_app_link_to($professional->Contact,'rp','contact/show?id='.$professional->Contact->id) ?>
    (<?php echo cross_app_link_to($professional->Organism,'rp','organism/show?id='.$professional->Organism->id) ?>
    -
    <?php echo $professional->name ?>)
    <span class="picto"><?php echo $professional->getRaw('groups_picto') ?></span>
  </p>
  
  <div class="ui-widget ui-widget-content ui-corner-all">
    <div class="fg-toolbar ui-widget-header ui-corner-all">
      <h2><?php echo __('Events', array(), 'messages') ?></h2>
    </div>
    <?php $last_event_id = 0 ?>
    <ul class="entries">
      <?php foreach ( $professional->getRaw('ContactEntries') as $ce ): ?>
      <?php foreach ( $ce->Entries as $entry ): ?>
      <?php if ( $last_event_id != $entry->ManifestationEntry->Manifestation->Event->id ): ?>
      <?php if ( $last_event_id != 0 ): ?></ul></li><?php endif ?>
      <li class="event event-<?php echo $entry->ManifestationEntry->Manifestation->Event->id ?> <?php echo $ce->confirmed ? 'confirmed' : '' ?>">
        <span class="entry_id"><?php echo link_to('#'.$entry->ManifestationEntry->Manifestation->Event->id,'event/edit?id='.$entry->ManifestationEntry->Manifestation->Event->id) ?>:</span>
        <span class="event"><?php echo cross_app_link_to($entry->ManifestationEntry->Manifestation->Event,'event','event/edit?id='.$entry->ManifestationEntry->Manifestation->Event->id) ?></span>
        <?php if ( !is_null($ce->transaction_id) ): ?>
        <?php $form = new ContactEntryForm($ce); $form->reduce(); ?>
          <?php echo form_tag_for($form, '@contact_entry', array('class' => $ce->transaction_id ? 'transposed' : '',)) ?>
          <?php echo $form ?>
          <input type="submit" name="submit" value="<?php echo __('Save','','sf_admin') ?>" />
        </form>
        <?php endif ?>
        <ul>
      <?php endif ?>
      <?php if ( $entry->EntryTickets->count() ): ?>
          <li class="<?php echo $entry->accepted ? 'accepted' : '' ?> <?php echo $entry->second_choice ? 'second_choice' : '' ?>">
            <span class="manifestation_happens_at"><?php echo cross_app_link_to($entry->ManifestationEntry->Manifestation->getFormattedDate(),'event','manifestation/show?id='.$entry->ManifestationEntry->Manifestation->id) ?></span>
            <?php foreach ( $entry->EntryTickets as $et ): ?>
            <span class="tickets" title="<?php echo $entry->accepted ? __('Accepted') : '' ?>"><?php echo $et->quantity.' '.$et->Price ?></span>
            <?php endforeach ?>
          <?php if ( $ce->transaction_id ): ?>
            <a class="transpose" title="<?php echo __('Transpose to ticketting') ?>" href="<?php echo cross_app_url_for('tck','ticket/sell?id='.$ce->transaction_id) ?>">&gt;&gt;</a>
            <?php foreach ( $ce->Transaction->Translinked as $tr ): ?>
              <a class="cancelling" href="<?php echo cross_app_url_for('tck','ticket/pay?id='.$tr->id) ?>">#<?php echo $tr->id ?></a>
            <?php endforeach ?>
          <?php elseif ( $entry->accepted ): ?>
            <a class="transpose" title="<?php echo __('Transpose to ticketting') ?>" href="<?php echo url_for('contact_entry/transpose?id='.$ce->id) ?>">&gt;&gt;</a>
          <?php endif ?>
          </li>
      <?php endif ?>
      <?php $last_event_id = $entry->ManifestationEntry->Manifestation->Event->id ?>
      <?php endforeach ?>
      <?php endforeach ?>
      </li>
    </ul>
  </div>
  
  <script type="text/javascript"><!--
    $(document).ready(function(){
      $('form').submit(function(){
        $.post($(this).attr('action'),$(this).serialize(),function(data){
          location.reload();
        });
        return false;
      });
    });
  --></script>
  <?php include_partial('themeswitcher') ?>
</div>
