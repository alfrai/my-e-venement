<?php use_helper('I18N', 'Date', 'CrossAppLink') ?>
<?php include_partial('assets') ?>
<?php use_stylesheet('grp-event') ?>

<div id="sf_admin_container" class="li_grp_professional sf_admin_show ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Contact file', array(), 'messages') ?></h1>
  </div>

    <div class="sf_admin_actions_block ui-widget">
      <?php include_partial('form_actions', array('professional' => $professional, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
    </div>

  <div class="ui-helper-clearfix"></div>

  <p class="contact">
    <?php echo cross_app_link_to($professional->Contact,'rp','contact/show?id='.$professional->Contact->id) ?>
    (<?php echo cross_app_link_to($professional->Organism,'rp','organism/show?id='.$professional->Organism->id) ?>
    -
    <?php echo $professional->name ?>)
  </p>
  
  <div class="ui-widget ui-widget-content ui-corner-all">
    <div class="fg-toolbar ui-widget-header ui-corner-all">
      <h2><?php echo __('Events', array(), 'messages') ?></h2>
    </div>
    <?php $last_event_id = 0 ?>
    <ul class="entries">
      <?php foreach ( $professional->ContactEntries as $ce ): ?>
      <?php foreach ( $ce->Entries as $entry ): ?>
      <?php if ( $last_event_id != $entry->ManifestationEntry->Manifestation->Event->id ): ?>
      <?php if ( $last_event_id != 0 ): ?></ul></li><?php endif ?>
      <li class="event event-<?php echo $entry->ManifestationEntry->Manifestation->Event->id ?>">
        <span class="entry_id"><?php echo link_to('#'.$entry->ManifestationEntry->Manifestation->Event->id,'event/edit?id='.$entry->ManifestationEntry->Manifestation->Event->id) ?>:</span>
        <span class="event"><?php echo cross_app_link_to($entry->ManifestationEntry->Manifestation->Event,'event','event/edit?id='.$entry->ManifestationEntry->Manifestation->Event->id) ?></span>
        <ul>
      <?php endif ?>
      <?php if ( $entry->EntryTickets->count() ): ?>
          <li class="<?php echo $entry->accepted ? 'accepted' : '' ?>">
            <span class="manifestation_happens_at"><?php echo cross_app_link_to($entry->ManifestationEntry->Manifestation->getFormattedDate(),'event','manifestation/show?id='.$entry->ManifestationEntry->Manifestation->id) ?></span>
            <?php foreach ( $entry->EntryTickets as $et ): ?>
            <span class="tickets" title="<?php echo $entry->accepted ? __('Accepted') : '' ?>"><?php echo $et->quantity.' '.$et->Price ?></span>
            <?php endforeach ?>
            <?php if ( $ce->transaction_id ): ?>
              <a class="transpose" title="<?php echo __('Transpose to ticketting') ?>" href="<?php echo cross_app_url_for('tck','ticket/sell?id='.$ce->transaction_id) ?>">&gt;&gt;</a>
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

  <?php include_partial('themeswitcher') ?>
</div>
