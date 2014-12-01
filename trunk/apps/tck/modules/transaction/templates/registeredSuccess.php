<?php use_javascript('/sfAdminThemejRollerPlugin/js/jquery.min.js', 'first') ?>
<?php use_javascript('helper') ?>
<?php use_javascript('tck-registered') ?>
<?php use_helper('Number') ?>
<?php include_partial('global/flashes') ?>
<div class="tck-transaction-registered ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
  <div class="ui-widget-content ui-corner-all">
    <div class="ui-widget-header ui-corner-all fg-toolbar">
      <h2><?php echo __('Transaction') ?> #<?php echo link_to($transaction->id, 'transaction/edit?id='.$transaction->id) ?></h2>
    </div>
    <div class="tck-registered">
      <?php foreach ( $forms as $form ): ?>
      <?php echo $form->renderFormTag(url_for('transaction/register')) ?>
        <?php $ticket = $form->getObject() ?>
        <?php use_javascripts_for_form($form) ?>
        <?php use_stylesheets_for_form($form) ?>
        
        <?php echo $form->renderHiddenFields() ?>
        <label>
          #<?php echo link_to($ticket->id, 'ticket/show?id='.$ticket->id) ?>
          <?php if ( $ticket->seat_id ): ?>
          / <?php echo $ticket->Seat ?>
          <?php endif ?>
        </label>
        <span class="contact_id" title="<?php echo __('Contact') ?>"><?php echo $form['contact_id'] ?></span>
        <span class="comment" title="<?php echo __('Comment') ?>"><?php echo $form['comment'] ?></span>
        <?php try { ?>
        <span class="price_id"><?php echo $form['price_id'] ?></span>
        <?php  } catch ( InvalidArgumentException $e ) { } ?>
        <?php if ( $sf_user->hasCredential('tck-transaction-reduc') ): ?>
        <span class="reduc" title="<?php echo __('Reduction') ?>"><?php echo $form['reduc'] ?> (€|%)</span>
        <span class="value"><?php echo format_currency($ticket->value, '€') ?></span>
        <?php endif ?>
        <span><input type="submit" value="<?php echo __('Validate', null, 'sf_admin') ?>" name="submit" /><input type="hidden" name="id" value="<?php echo $ticket->id ?>" /></span>
      </form>
      <?php endforeach ?>
    </div>
  </div>
</div>
