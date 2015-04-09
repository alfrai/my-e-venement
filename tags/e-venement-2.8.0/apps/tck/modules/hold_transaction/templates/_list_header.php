<script type="text/javascript">
$(document).ready(function(){ setTimeout(function(){
  $('#sf_admin_actions_menu_list a').clone().appendTo('#sf_admin_header .ui-widget')
    .mouseenter(function(){ $(this).addClass('ui-state-hover') })
    .mouseleave(function(){ $(this).removeClass('ui-state-hover') })
  ;
}, 500); });
</script>
<div class="ui-widget"></div>
