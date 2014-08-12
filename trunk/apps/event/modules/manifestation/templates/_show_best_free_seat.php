<div class="sf_admin_form_row sf_admin_form_field_best_rank">
  <label><?php echo __('Best free seat') ?></label>:
  <span class="seat"><?php echo ($seat = $form->getObject()->getBestFreeSeat())
    ? __('Rank %%rank%% (ex: Seat %%num%%)', array('%%num%%' => $seat->name, '%%rank%%' => $seat->rank))
    : __('%%n%% free seat', array('%%n%%' => 0))
  ?></span>
</div>
