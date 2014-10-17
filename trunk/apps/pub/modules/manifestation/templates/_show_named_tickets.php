<form action="<?php echo url_for('ticket/modNamedTickets?manifestation_id='.$manifestation->id) ?>" method="<?php echo sfConfig::get('sf_web_debug', false) ? 'get' : 'post' ?>" class="named-tickets">
  <?php use_javascript('pub-named-tickets?'.date('Ymd')) ?>
  <?php use_stylesheet('pub-named-tickets?'.date('Ymd')) ?>
  <h3><?php echo __('Assign a contact to your tickets') ?></h3>
  <div class="ticket sample">
    <h4>
      <span class="id"><input type="hidden" name="ticket[%%ticket_id%%][id]" value="" /></span>
      <span class="gauge_name"></span>
      <span class="value"></span>
      <span class="taxes"></span>
    </h4>
    <div cass="ticket">
      <span class="seat_name"></span>
      <span class="price_name"><select name="ticket[%%ticket_id%%][price_id]"></select></span>
      <span class="price_name"></span>
      <button value="true" class="delete" name="ticket[%%ticket_id%%][delete]" title="<?php echo __('Delete', null, 'sf_admin') ?>">X</button>
    </div>
    <div class="contact">
      <span class="contact_id">
        <input class="id" type="hidden" value="" name="ticket[%%ticket_id%%][contact][id]" />
        <input class="force" type="hidden" value="" name="ticket[%%ticket_id%%][contact][force]" />
      </span>
      <span class="contact_name">
        <label><?php echo __('Name') ?>:</label>
        <input type="text" value="" name="ticket[%%ticket_id%%][contact][name]" title="<?php echo __('Name') ?>" />
      </span>
      <span class="contact_firstname">
        <label><?php echo __('Firstname') ?>:</label>
        <input type="text" value="" name="ticket[%%ticket_id%%][contact][firstname]" title="<?php echo __('Firstname') ?>" />
      </span>
      <button class="me" name="ticket[%%ticket_id%%][me]" value="<?php echo $sf_user->getContact()->id ?>" title="<?php echo __('Give me this ticket') ?>"><?php echo __('Me') ?></button>
      <br/>
      <span class="contact_email">
        <label><?php echo __('Email address') ?>:</label>
        <input type="email" value="" name="ticket[%%ticket_id%%][contact][email]" title="<?php echo __('Email address') ?>" />
      </span>
      <br/>
      <span class="comment">
        <label><?php echo __('Any comment?') ?></label>
        <input type="text" value="" name="ticket[%%ticket_id%%][comment]" title="<?php echo __('Comment') ?>" maxlength="255" />
      </span>
    </div>
  </div>
</form>
<p class="submit">
  <a href="<?php echo url_for('transaction/show?id='.$sf_user->getTransactionId()) ?>">
    <button name="submit" value=""><?php echo __('Cart') ?></button>
  </a>
</p>
