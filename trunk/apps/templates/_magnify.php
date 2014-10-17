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
            /*
            var elt = $(this);
            for ( i = 0 ; i < 3 ; i++ )
            {
              elt = $(elt).parent();
              if ( $(elt).css('overflow-x') === 'auto' )
              {
                // scroll horizontally to the middle of the venue
                var hscroll = ($(elt).find('.seated-plan.picture').width()*new_scale - $(elt).find('.seated-plan.picture').width()*old_scale )/2;
                $(elt).animate({ scrollLeft: $(elt).scrollLeft() + hscroll }, 1000);
                break;
              }
            }
            */
          })
        ;
        return false;
      });
      
      /*
      $(document).scroll(function(event){
        if ( event.ctrlKey == true )
        {
          console.error(event.pageX+' '+event.pageY);
          if (event.originalEvent.wheelDelta > 0 || event.originalEvent.detail < 0)
          {
            // scroll up
            console.error('scroll up');
          }
          else {
            // scroll down
            console.error('scroll down');
          }
          return false;
        }
      });
      */
    });
  </script>
</div>

