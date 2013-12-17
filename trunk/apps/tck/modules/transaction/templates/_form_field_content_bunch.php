<?php use_helper('Number') ?>
<?php if (! (isset($detail['fake']) && $detail['fake']) ): ?>
  <?php use_javascript('tck-touchscreen-dataload') ?>
  <script type="text/javascript">
    $(document).ready(function(){
      $.get('<?php echo url_for($detail['data_url'].'?id='.$transaction->id) ?>',function(data){
        if ( data.error[0] )
        {
          alert(data.error[1]);
          return;
        }
        
        if (!( data.success.error_fields !== undefined && data.success.error_fields.manifestations === undefined ))
        {
          alert(data.success.error_fields.manifestations);
          return;
        }
        
        if ( data.success.success_fields.manifestations !== undefined && data.success.success_fields.manifestations.data !== undefined )
        {
          liCompleteContent(data.success.success_fields.manifestations.data.content, 'manifestations');
        }
      });
    });
  </script>
  <div class="families sample">
    <div class="family" id="li_transaction_manifestation_">
      <h3>
        <a target="_blank" class="event"></a>
        <a target="_blank" class="happens_at" title=""></a>
        <a target="_blank" class="location"></a>
      </h3>
      <div class="items">
        <div class="item ui-corner-all highlight" id="li_transaction_gauge_">
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
<?php else: ?>
  <?php //include_partial('form_field_content_fake') ?>
<?php endif ?>
