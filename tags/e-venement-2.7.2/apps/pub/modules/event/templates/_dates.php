<?php if ( $event->Manifestations->count() > 0 ): ?>
<ul>
<?php foreach ( $event->Manifestations as $manif ): ?>
  <li class="month-<?php echo format_date(strtotime($manif->happens_at), 'yyyyMM') ?>" data-time="<?php echo strtotime($manif->happens_at) ?>"><?php echo link_to($manif->getFormattedDate(),'manifestation/edit?id='.$manif->id) ?></li>
<?php endforeach ?>
</ul>
<?php endif ?>

