<?php $total = 0 ?>
<?php foreach ( $transaction->Tickets as $ticket ): ?>
<span
  class="tickets <?php echo $ticket->cancelling ? 'cancelling' : 'normal' ?> tickets-price-<?php echo $ticket->price_id ?>"
  title="#<?php echo $ticket->id ?>">
  <span class="nb"><?php echo $ticket->cancelling ? -1 : 1 ?></span>
  <span class="price-<?php echo $ticket->price_id ?>">
    <?php echo $ticket->Price->name ?>
    <?php $total += $ticket->value ?>
  </span>
</span>
<?php endforeach ?>
<?php if ( $transaction->Tickets->count() > 0 ): ?>
<span class="total"><?php echo format_currency($total,'€') ?></span>
<?php endif ?>
<script type="text/javascript"><!--
  $(document).ready(function(){
    types = ['normal','cancelling'];
    for ( i = 0 ; i < types.length ; i++ )
    {
      t = $('#tickets .tickets.cancelling:first-child');
      for ( t = $('#tickets .tickets.'+types[i]+':first-child') ; t.length > 0 ; t = t.next() )
      {
        t.find('.nb').html((i == 1 ? -1 : 1)*$('#tickets .'+t.find('> *:not(.nb)').attr('class')).length);
        $('#tickets .tickets.'+types[i]+'.tickets-'+t.find('*:not(.nb)').attr('class')+':not(:first)').each(function(){
          $('#tickets .tickets.'+types[i]+'.tickets-'+t.find('*:not(.nb)').attr('class')+':first').attr('title',
            $('#tickets .tickets.'+types[i]+'.tickets-'+t.find('*:not(.nb)').attr('class')+':first').attr('title')+"\n"+$(this).attr('title')
          );
          $(this).remove();
        });
      }
    }
  });
--></script>
