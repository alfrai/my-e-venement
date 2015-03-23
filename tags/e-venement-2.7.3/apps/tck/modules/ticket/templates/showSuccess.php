<div id="sf_admin_container" class="sf_admin_edit ui-widget ui-widget-content ui-corner-all ticket-show">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __("Ticket #%%id%% log",array('%%id%%' => $ticket->id)) ?></h1>
  </div>

  <?php include_partial('global/flashes') ?>

    <table class="tickets sf_admin_list">
      <caption class="fg-toolbar ui-widget-header ui-corner-top">
        <h1><?php echo __('Informations') ?></h1>
      </caption>
      <tbody>
        <tr><?php include_partial('show_ticket_user',array('ticket' => $ticket)) ?></tr>
        <tr><?php include_partial('show_ticket_contact',array('ticket' => $ticket)) ?></tr>
        <tr><?php include_partial('show_ticket_transaction',array('ticket' => $ticket)) ?></tr>
        <tr><?php include_partial('show_ticket_manifestation',array('ticket' => $ticket)) ?></tr>
        <tr><?php include_partial('show_ticket_cancel',array('ticket' => $ticket)) ?></tr>
        <tr><?php include_partial('show_ticket_duplicate',array('ticket' => $ticket)) ?></tr>
        <tr><?php include_partial('show_ticket_price',array('ticket' => $ticket)) ?></tr>
        <tr><?php include_partial('show_ticket_workspace',array('ticket' => $ticket)) ?></tr>
        <?php if ( $sf_user->hasCredential('tck-member-card') ): ?>
        <tr><?php include_partial('show_ticket_member_card',array('ticket' => $ticket)) ?></tr>
        <?php endif ?>
        <?php if ( $sf_user->hasCredential('tck-control') ): ?>
        <tr><?php include_partial('show_ticket_controls',array('ticket' => $ticket)) ?></tr>
        <?php endif ?>
      </tbody>
    </table>
    
    <table class="versions sf_admin_list">
      <caption class="fg-toolbar ui-widget-header ui-corner-top">
        <h1><?php echo __('Versions') ?></h1>
      </caption>
      <tbody>
        <?php foreach ( $versions as $version ): ?>
        <tr><?php include_partial('show_version_detail',array('version' => $version)) ?></tr>
        <?php endforeach ?>
      </tbody>
      <thead>
        <tr><?php include_partial('show_version_header') ?></tr>
      </thead>
    </table>
    
    <?php if ( $sf_user->isSuperAdmin() ): ?>
    <table class="versions sf_admin_list">
      <caption class="fg-toolbar ui-widget-header ui-corner-top">
        <h1><?php echo __('Super Administration') ?></h1>
      </caption>
      <tbody>
        <tr><td><center>
          <?php $f = new sfForm ?>
          <a
            href="<?php echo url_for('ticket/resetPrinting?id='.$ticket->id.'&_csrf_token='.$f->getCSRFToken()) ?>"
            onclick="javascript: return confirm('<?php echo __('Are you sure?',null,'sf_admin') ?>');"
          >Reset printing state</a>
        </center></td></tr>
      </tbody>
    </table>
    <?php endif ?>
    
    <div style="clear: both;"></div>

</div>
