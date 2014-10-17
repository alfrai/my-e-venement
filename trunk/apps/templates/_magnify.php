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
      $('.magnify button').unbind('click').click(function(){
        var operand = $(this).val() == '+' ? '*' : '/';
        $('.gauge .seated-plan.picture')
          .css('transition-property', 'transform')
          .css('transition-duration', '1s')
          .each(function(){
            var factor = 1.3;
            var old_scale = parseFloat($(this).attr('data-scale'));
            var new_scale = operand == '*' ? old_scale*factor : old_scale/factor;
            $(this).css('transform', 'scale('+new_scale+')')
              .attr('data-scale', new_scale);
            
            // the scroll
            var elt = $(this);
            for ( i = 0 ; i < 3 ; i++ )
            {
              elt = $(elt).parent();
              if ( $(elt).css('overflow-x') === 'auto' )
              {
                var hscroll = ($(elt).find('.seated-plan.picture').width()*new_scale - $(elt).find('.seated-plan.picture').width()*old_scale )/2; // scroll horizontally to the middle of the venue
                var vscroll = ($(elt).find('.seated-plan.picture').height()*new_scale - $(elt).find('.seated-plan.picture').height()*old_scale )/2; // scroll vertically to the middle of the venue
                $(elt).animate({ scrollLeft: $(elt).scrollLeft() + hscroll, scrollTop: $(elt).scrollTop() + vscroll }, 1000);
                
                break;
              }
            }
          })
        ;
        return false;
      });
    });
  </script>
</div>

