<form action="<?php echo url_for('ticket/modNamedTickets?manifestation_id='.$manifestation->id) ?>" method="post" class="named-tickets">
  <?php use_javascript('pub-named-tickets?'.date('Ymd')) ?>
  <?php use_stylesheet('pub-named-tickets?'.date('Ymd')) ?>
  <h3><?php echo __('Assign a contact to your tickets') ?></h3>
  <div class="ticket sample">
    <h4>
      <span class="id"><input type="hidden" name="ticket[%%ticket_id%%][id]" value="" /></span>
      <span class="gauge_name"></span>
      <span class="seat_name"></span>
      <span class="price_name"></span>
    </h4>
    <span class="contact_id"><input type="hidden" value="" name="ticket[%%ticket_id%%][contact][id]" /></span>
    <span class="contact_name">
      <label><?php echo __('Name') ?><span class="extra"> / <?php echo __('Firstname') ?></span>:</label>
      <input type="text" value="" name="ticket[%%ticket_id%%][contact][name]" title="<?php echo __('Name') ?>" />
    </span>
    <span class="contact_firstname">
      <label><?php echo __('Firstname') ?>:</label>
      <input type="text" value="" name="ticket[%%ticket_id%%][contact][firstname]" title="<?php echo __('Firstname') ?>" />
    </span>
    <span class="contact_email">
      <label><?php echo __('Email address') ?>:</label>
      <input type="email" value="" name="ticket[%%ticket_id%%][contact][email]" title="<?php echo __('Email address') ?>" />
    </span>
  </div>
  <p class="submit"><input type="submit" name="submit" value="<?php echo __('Validate',null,'sf_admin') ?>" /></p>
</form>
