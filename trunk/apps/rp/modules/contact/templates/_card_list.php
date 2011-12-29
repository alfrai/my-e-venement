    <?php require_once(dirname(__FILE__).'/../lib/MemberCardHelper.class.php'); ?>
    <?php $cpt = 0 ?>
    <?php foreach ( $contact->MemberCards as $card ): ?>
    <?php if ( strtotime($card->expire_at) > strtotime('now') ): ?>
      <li class="card"><?php echo $card ?></li>
      <?php $card_helper = new MemberCardHelper(); echo $card_helper->linkToDelete($card, array(  'params' =>   array(  ),  'confirm' => 'Are you sure?',  'class_suffix' => 'delete',  'label' => 'Delete',)) ?></li>
      <?php $cpt++ ?>
    <?php endif ?>
    <?php endforeach ?>
    <?php if ( $cpt == 0 ): ?>
      <li><?php echo __('No card') ?></li>
    <?php endif ?>
