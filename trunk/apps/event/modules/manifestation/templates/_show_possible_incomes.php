<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_show_possible_incomes">
  <p class="min"><label><?php echo __('Min.') ?></label> <span></span></p>
  <p class="max"><label><?php echo __('Max.') ?></label> <span></span></p>
  <a href="<?php echo url_for('manifestation/possibleIncomes?id='.$form->getObject()->id) ?>"></a>
</div>
<script type="text/javascript"><!--
  $(document).ready(function(){
    $.get($('.sf_admin_form_field_show_possible_incomes a').prop('href'), function(json){
      $.each(json, function(id, n){
        $('.sf_admin_form_field_show_possible_incomes .'+id+' span').text(n.currency);
      });
    });
  });
--></script>
