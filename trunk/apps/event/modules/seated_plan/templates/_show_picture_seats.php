<?php if ( !isset($seated_plan) && isset($form) ) $seated_plan = $form->getObject(); ?>
<script type="text/javascript">
  $(document).ready(function(){
    // avoiding JS errors
    if ( !$.isFunction(seated_plan_mouseup) )
      return;
    
    $('.picture.seated-plan img').load(function(){
      var ref = $('.picture.seated-plan');
      var f = seated_plan_mouseup;
      var dec = decodeURIComponent;
      <?php $seats = array(); foreach ( $seated_plan->Seats as $seat ) $seats[$seat->name] = $seat; ksort($seats); foreach ( $seats as $seat ):  ?>f({position:{x:<?php echo $seat->x ?>,y:<?php echo $seat->y ?>},name:dec("<?php echo rawurlencode($seat->name); ?>"),diameter:<?php echo $seat->diameter ?>,object:ref});<?php endforeach ?>
    });
  });
</script>
