<?php use_helper('Number') ?>
<?php if (! (isset($detail['fake']) && $detail['fake']) ): ?>
  <?php use_javascript('tck-touchscreen-dataloader') ?>
  <div class="families sample">
    <div class="family" id="li_transaction_<?php echo strtolower($detail['model']) ?>_" data-family-id="">
      <h3 class="ui-corner-all">
        <a target="_blank" class="event"></a>
        <a target="_blank" class="happens_at" title=""></a>
        <a target="_blank" class="location"></a>
        <a target="_blank" class="fg-button-mini fg-button ui-state-default fg-button-icon-left ui-priority-secondary" href="#">
          <span class="ui-icon ui-icon-trash"></span>
          <?php echo __('Delete', null, 'sf_admin') ?>
        </a>
      </h3>
      <div class="items">
        <div class="item ui-corner-all highlight" id="li_transaction_item_">
          <?php include_partial('form_field_content_item_sample') ?>
        </div>
        <div class="item total">
          <?php include_partial('form_field_content_item_total') ?>
        </div>
      </div>
    </div>
    <div class="family total">
      <div class="items">
        <div class="item total">
          <?php include_partial('form_field_content_item_total') ?>
        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript">
    if ( li.urls == undefined )
      li.urls = {};
    li.urls['<?php echo $id ?>'] = '<?php echo url_for($detail['data_url'].'?id='.$transaction->id) ?>';
  </script>
<?php else: ?>
  <?php include_partial('form_field_content_fake') ?>
<?php endif ?>
