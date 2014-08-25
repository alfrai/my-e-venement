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
      $('.magnify button').click(function(){
        var factor = $(this).val() == '+' ? 1.2 : 0.8;
        $('.gauge .seated-plan.picture')
          .each(function(){
            var new_scale = parseFloat($(this).attr('data-scale'))*factor;
            $(this).css('transform', 'scale('+new_scale+')')
              .attr('data-scale', new_scale);
          });
        return false;
      });
    });
  </script>
</div>

