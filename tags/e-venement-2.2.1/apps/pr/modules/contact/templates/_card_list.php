<script type="text/javascript"><!--
  $(document).ready(function(){
    $.get('<?php echo url_for('member_card/index?contact_id='.$contact->id) ?>',get_member_card_index);
  });
  
  function get_member_card_index(data)
  {
    if ( $('#member-cards .list > table').length > 0 )
      $('#member-cards .list > table').replaceWith($(data).find('.sf_admin_list > table'));
    else
      $(data).find('.sf_admin_list > table')
        .appendTo('#member-cards .list');
    
    $('#member-cards .list').addClass('sf_admin_list');
    $('#member-cards .list > table').find('caption').remove();
    $('#member-cards .list > table a').click(function(){
      $.get($(this).attr('href')+'&contact_id=<?php echo $contact->id ?>',get_member_card_index);
      return false;
    });
    
    $('#member-cards .list > table > tbody a').unbind();
  }
--></script>
<?php /*
    <?php use_helper('Number') ?>
    <?php require_once(dirname(__FILE__).'/../lib/MemberCardHelper.class.php'); ?>
    <?php $cpt = 0 ?>
    <?php foreach ( $contact->MemberCards as $card ): ?>
    <?php if ( strtotime($card->expire_at) > strtotime('now') ): ?>
      <li class="card">
        <?php echo $card ?>
        <?php if ( $card->value > 0 ): ?>(<?php echo format_currency($card->value,'€') ?>)<?php endif ?>
        <?php $card_helper = new MemberCardHelper(); echo $card_helper->linkToDelete($card, array(  'params' =>   array(  ),  'confirm' => 'Are you sure?',  'class_suffix' => 'delete',  'label' => 'Delete',)) ?></li>
      </li>
      <?php $cpt++ ?>
    <?php endif ?>
    <?php endforeach ?>
    <?php if ( $cpt == 0 ): ?>
      <li><?php echo __('No card') ?></li>
    <?php endif ?>
*/
