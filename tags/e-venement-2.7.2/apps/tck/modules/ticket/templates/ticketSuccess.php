<?php use_helper('Number') ?>
<?php include_partial('assets') ?>
<?php include_partial('global/flashes') ?>
<form action="" method="post" id="prices">
<div class="gauge ui-widget-content ui-corner-all"></div>
<div class="manifestations_list ui-widget-content ui-corner-all">
    <?php if ( $manifestations->count() == 0 && isset($manfestation_id) ): ?>
    <input class="empty_manifestation" type="hidden" name="empty_manifestation" value="<?php echo $manifestation_id ?>" />
    <?php endif ?>
    
    <?php echo $form->renderHiddenFields(); $cpt = 0; ?>
    <ul>
    <?php foreach ( $manifestations as $manif ): ?>
      <li class="manif"><?php echo include_partial('ticket_manifestation',array(
        'active' => true,
        'manif' => $manif,
        'workspace' => false,
        'transaction' => $transaction,
        'first' => $cpt++ == 0 ? true : false)) ?></li>
    <?php endforeach ?>
      <li class="total">
        <span></span>
        <span></span>
        <span></span>
        <span class="total"><?php echo format_currency(0,'€') ?></span>
      </li>
    </ul>
</div>
</form>
