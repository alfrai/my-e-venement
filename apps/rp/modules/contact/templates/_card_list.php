<script type="text/javascript"><!--
  if ( LI == undefined )
    var LI = {};
  
  $(document).ready(function(){
    LI.get_member_card_index();
  });
  
  LI.get_member_card_index = function()
  {
    $.get('<?php echo url_for('member_card/index?contact_id='.$contact->id.'&page=1') ?>',function(data){
      data = $.parseHTML(data);
      
      if ( $('#member-cards .list > table').length > 0 )
        $('#member-cards .list > table').replaceWith($(data).find('.sf_admin_list > table'));
      else
        $(data).find('.sf_admin_list > table')
          .appendTo('#member-cards .list');
      
      $('#member-cards .list').addClass('sf_admin_list');
      $('#member-cards .list > table').find('caption').remove();
      $('#member-cards .list > table a').unbind('click').click(function(){
        $.get($(this).attr('href')+'&contact_id=<?php echo $contact->id ?>',get_member_card_index);
        return false;
      });
      
      $('#member-cards .list > table > tbody a').unbind();
      
      if ( LI.get_member_card_index_callbacks != undefined ) {
        $.each(LI.get_member_card_index_callbacks, function(i, fct){ fct(); });
      }
    });
  }
  
--></script>
