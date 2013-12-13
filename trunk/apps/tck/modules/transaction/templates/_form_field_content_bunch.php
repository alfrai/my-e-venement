<?php use_helper('Number') ?>
<?php if (! (isset($detail['fake']) && $detail['fake']) ): ?>
  <script type="text/javascript">
    $(document).ready(function(){
      $.get('<?php echo url_for($detail['data_url'].'?id='.$transaction->id) ?>',function(data){
        $('#li_transaction_<?php echo $id ?> .families:not(.sample)').remove();
        if ( typeof data != 'object' )
          alert("<?php echo __('An error occured. Please try again.') ?>");
        
        // first element, parent of all
        var wglobal = $('#li_transaction_<?php echo $id ?> .families.sample').clone(true)
          .removeClass('sample');
        wglobal.find('.family:not(.total)').remove();
        wglobal.appendTo($('#li_transaction_<?php echo $id ?>'))
        
        var currency = $('#li_transaction_<?php echo $id ?> .currency').html();
        
        $.each(data, function(manifestation_id, manifestation){
          var wmanif = $('#li_transaction_<?php echo $id ?> .families.sample .family:not(.total)').clone(true);
          wmanif.find('.item:not(.total)').remove();
          
          happens_at = new Date(manifestation.happens_at.replace(' ','T'));
          ends_at = new Date(manifestation.ends_at.replace(' ','T'));
          
          // in progress: manifestation
          wmanif.prop('id', wmanif.prop('id')+manifestation_id);
          wmanif.find('h3 .event').text(manifestation.name).prop('href',manifestation.event_url);
          wmanif.find('h3 .happens_at').text(happens_at.toLocaleString().replace(/:\d\d \w+$/,'')).prop('href',manifestation.manifestation_url).prop('title', ends_at.toLocaleString().replace(/:\d\d \w+$/,''));
          wmanif.find('h3 .location').text(manifestation.location).prop('href',manifestation.location_url);
          // TODO: gauge_url
          
          wmanif.insertBefore(wglobal.find('.family.total'));
          
          // in progress: gauge
          $.each(manifestation.gauges, function(index, gauge){
            var wgauge = $('#li_transaction_<?php echo $id ?> .families.sample .item:not(.total)').clone(true);
            wgauge.find('.declination').remove();
            
            wgauge.prop('id', wgauge.prop('id')+index);
            wgauge.find('h4').text(gauge.name);
            // TODO: gauge_url
            // TODO: seated_plan_url
            // TODO: seated_plan_seats_url
            
            wgauge.insertBefore(wmanif.find('.item.total'));
            
            // in progress: prices
            $.each(gauge['prices'], function(index, tickets){
              var wtickets = $('#li_transaction_<?php echo $id ?> .families.sample .declination').clone(true);
              
              wtickets.addClass(tickets.cancelling ? 'cancelling' : '');
              wtickets.addClass(tickets.printed ? 'printed' : '');
              wtickets.find('.qty').html(tickets.qty);
              wtickets.find('.price').html(tickets.price_name);
              wtickets.find('.pit').html(tickets.pit.toFixed(2)+' '+currency);
              wtickets.find('.vat').html(tickets.vat.toFixed(2)+' '+currency);
              wtickets.find('.tep').html(tickets.tep.toFixed(2)+' '+currency);
              // TODO: ids
              // TODO: nums
              
              wtickets.appendTo(wgauge.find('.declinations tbody'));
            }); // each bunch of tickets
          }); // each gauge
        }); // each manifestation
        
        $('#li_transaction_<?php echo $id ?> .item .total').select();
      }); // end of ajax GET request
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
