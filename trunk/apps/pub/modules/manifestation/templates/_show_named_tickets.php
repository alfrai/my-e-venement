<form
  action="<?php echo url_for('ticket/modNamedTickets?manifestation_id='.$manifestation->id
    .(isset($ticket) && $ticket->getRawValue() instanceof Ticket ? '&ticket_id='.$ticket->id : '')
    .(isset($transaction) && $transaction->getRawValue() instanceof Transaction ? '&transaction_id='.$transaction->id : '')
  ) ?>"
  method="<?php echo sfConfig::get('sf_web_debug', false) ? 'get' : 'post' ?>"
  class="named-tickets"
  <?php echo isset($ticket) && $ticket->getRawValue() instanceof Ticket ? 'id="ticket-'.$ticket->id.'"' : '' ?>
>
  <?php use_javascript('pub-named-tickets?'.date('Ymd')) ?>
  <?php use_stylesheet('pub-named-tickets?'.date('Ymd')) ?>
  <h3><?php echo __('Customize your seats') ?></h3>
  <div class="ticket sample">
    <h4>
      <span class="id"><input type="hidden" name="ticket[%%ticket_id%%][id]" value="" /></span>
      <span class="gauge_name"></span>
      <span class="value"></span>
      <span class="taxes"></span>
    </h4>
    <div class="price">
      <span class="seat_label"><?php if ( isset($display_mods) && !$display_mods ) echo __('Seat #') ?></span><span class="seat_name"></span>
      <span class="price_name"><select name="ticket[%%ticket_id%%][price_id]"></select></span>
      <?php if (!( isset($display_mods) && !$display_mods )): ?>
      <button value="true" class="delete" name="ticket[%%ticket_id%%][delete]" title="<?php echo __('Delete', null, 'sf_admin') ?>">X</button>
      <?php endif ?>
    </div>
    <div class="contact">
      <span class="contact_id">
        <input class="id" type="hidden" value="" name="ticket[%%ticket_id%%][contact][id]" />
        <input class="force" type="hidden" value="" name="ticket[%%ticket_id%%][contact][force]" />
      </span>
      <span class="contact_title">
        <label><?php echo __('Title') ?>:</label>
        <select type="text" value="" name="ticket[%%ticket_id%%][contact][title]" title="<?php echo __('Title') ?>">
          <option value=""><?php echo __('Title') ?></option>
          <?php foreach ( Doctrine::getTable('TitleType')->createQuery('t')->execute() as $title ): ?>
          <option value="<?php echo $title ?>"><?php echo $title ?></option>
          <?php endforeach ?>
        </select>
      </span>
      <span class="contact_name">
        <label><?php echo __('Name') ?></label>
        <input type="text" value="" name="ticket[%%ticket_id%%][contact][name]" title="<?php echo __('Name') ?>" />
      </span>
      <span class="contact_firstname">
        <label><?php echo __('Firstname') ?></label>
        <input type="text" value="" name="ticket[%%ticket_id%%][contact][firstname]" title="<?php echo __('Firstname') ?>" />
      </span>
      <br/>
      <span class="contact_email">
        <label><?php echo __('Email address') ?></label>
        <input type="email" value="" name="ticket[%%ticket_id%%][contact][email]" title="<?php echo __('Email address') ?>" />
      </span>
      <button class="me" name="ticket[%%ticket_id%%][me]" value="<?php echo $sf_user->getTransaction()->contact_id ?>" title="<?php echo __('Give me this ticket') ?>"><?php echo __('My seat') ?></button>
      <br/>
      <span class="comment">
        <label><?php echo __('Any comment?') ?></label>
        <input type="text" value="" name="ticket[%%ticket_id%%][comment]" title="<?php echo __('Comment') ?>" maxlength="255" />
      </span>
    </div>
  </div>
</form>
<?php if (!( isset($display_continue) && !$display_continue )): ?>
<p class="submit">
  <a href="<?php echo url_for('transaction/show?id='.$sf_user->getTransactionId()) ?>">
    <button name="submit" value=""><?php echo __('Continue') ?></button>
  </a>
</p>
<?php endif ?>
