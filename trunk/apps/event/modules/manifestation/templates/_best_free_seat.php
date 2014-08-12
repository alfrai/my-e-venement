  <li>
    <?php echo $manifestation ?>
    <?php if ( $manifestation->getBestFreeSeat(3)->count() > 0 ): ?>
      <ol>
      <?php foreach ( $manifestation->getBestFreeSeat(3) as $seat ): ?>
        <li><?php echo __('Rank %%rank%%', array('%%rank%%' => $seat->rank)) ?>: <?php echo $seat->name ?></li>
      <?php endforeach ?>
      </ol>
    <?php else: ?>
      <ul>
        <li><?php echo __('No free seat') ?></li>
      </ul>
    <?php endif ?>
  </li>
