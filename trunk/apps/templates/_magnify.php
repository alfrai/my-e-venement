<?php use_stylesheet('magnify') ?>
<div class="magnify">
  <button name="magnify-in" class="magnify-in" value="+">
    <div class="zoom-icon" jstcache="0"></div>
  </button>
  <div class="magnify-line"></div>
  <button name="magnify-out" class="magnify-out" value="-">
    <div class="zoom-icon" jstcache="0"></div>
  </button>
  <script type="text/javascript">
    $(document).ready(function(){
      $('html').css('cssText', 'overflow-x: auto !important'); // to be able to side-scroll
      $('.gauge .seated-plan.picture')
        .css('transition-property', 'transform')
        .css('transition-duration', '1s')
      ;
      $('.magnify button').click(function(){
        var operand = $(this).val() == '+' ? '*' : '/';
        $('.gauge .seated-plan.picture')
          .each(function(){
            var factor = 1.2;
            var new_scale = operand == '*' ? parseFloat($(this).attr('data-scale'))*factor : parseFloat($(this).attr('data-scale'))/factor;
            $(this).css('transform', 'scale('+new_scale+')')
              .attr('data-scale', new_scale);
          });
        return false;
      });
    });
  </script>
</div>

