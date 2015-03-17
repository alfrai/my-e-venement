<?php if ( $manifestation->getBestFreeSeat() ): ?>
<?php echo __('Rank %%rank%% (ex: Seat %%num%%)', array('%%rank%%' => $manifestation->getBestFreeSeat()->rank, '%%num%%' => $manifestation->getBestFreeSeat())) ?>
<?php else: ?>
<?php echo __('N/A') ?>
<?php endif ?>
