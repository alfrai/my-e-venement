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
        .unbind('click')
        .click(function(){
            LI.seatedPlanMagnify($(this).val() == '+' ? 'in' : 'out');
            return false;
        })
      ;
    });
  </script>
</div>

