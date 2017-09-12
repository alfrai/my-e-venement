if ( LI == undefined ) { LI = {}; };
if ( LI.get_member_card_index_callbacks == undefined ) { LI.get_member_card_index_callbacks = []; };

    LI.memberCardChangeSeat = function(elt){
        var seat = prompt($(elt).prop('title') ? $(elt).prop('title') : $(elt).text());
        $.get($(elt).prop('href'), { seat_name: seat }, function(json){
            if ( $(elt).closest('.sf_admin_list').length == 0 ) {
                $(elt).siblings('.seat_name').text(json.member_card.privileged_seat_name);
                $(elt).siblings('img').show();
                setTimeout(function(){
                    $(elt).siblings('img').fadeOut();
                },2000);
                return;
            }
            
            $(elt).closest('.sf_admin_row').find('.sf_admin_list_td_privileged_seat_name').text(json.member_card.privileged_seat_name);
            $('#transition .close').click();
        });

        return false;
    }

LI.initMemberCardChangeSeat = function(){
    $('.sf_admin_action_changeseat a').click(function(){
        return LI.memberCardChangeSeat(this);
    });
}

$(document).ready(function(){
    LI.initMemberCardChangeSeat();
    LI.get_member_card_index_callbacks.push(LI.initMemberCardChangeSeat);
});

