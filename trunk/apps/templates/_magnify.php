<?php use_stylesheet('magnify') ?>
<?php use_javascript('jquery.nicescroll.min.js') ?>
<div class="magnify" title="<?php echo __('Zoom') ?>">
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
      $('.magnify button')
        .unbind('click').click(function(){
        var operand = $(this).val() == '+' ? '*' : '/';
        $('.seated-plan.picture')
          .css('transition-property', 'transform')
          .css('transition-duration', '1s')
          .each(function(){
            var factor = 1.3;
            var old_scale = parseFloat($(this).attr('data-scale'));
            var new_scale = operand == '*' ? old_scale*factor : old_scale/factor;
            if ( operand != '*' && new_scale < parseFloat($(this).attr('data-scale-init')) )
              new_scale = parseFloat($(this).attr('data-scale'));
            
            $(this).css('transform', 'scale('+new_scale+')')
              .attr('data-scale', new_scale);
            if ( LI.seatedPlanScroll != undefined )
              LI.seatedPlanScroll($(this), old_scale, new_scale);
          })
        ;
        return false;
      });
    });
    
    if ( LI == undefined )
      LI = {};
    LI.seatedPlanScroll = function(widget, old_scale, new_scale)
    {
      // the scroll
      var elt = $(widget);
      for ( i = 0 ; i < 3 ; i++ )
      {
        elt = $(elt).parent();
        if ( $(elt).css('overflow-x') === 'auto' )
        {
          var hscroll = ($(widget).width() *new_scale - $(widget).width() *old_scale )/2; // scroll horizontally to the middle of the venue
          var vscroll = ($(widget).height()*new_scale - $(widget).height()*old_scale )/2; // scroll vertically to the middle of the venue
          var opt = { scrollLeft: $(elt).scrollLeft() + hscroll, scrollTop: $(elt).scrollTop() + vscroll }
          
          $(widget).unbind('transitionend webkitTransitionEnd').on('transitionend webkitTransitionEnd', function(){
            if ( $(elt).overscroll != undefined )
              $(elt).removeOverscroll();
            $(elt).animate(opt, 1000);
            if ( $(elt).overscroll != undefined )
              $(elt).overscroll(opt);
          });
          
          break;
        }
      }
    }
  </script>
</div>

