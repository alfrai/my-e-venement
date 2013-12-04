  <?php if ( $gauge->getRaw('Workspace')->SeatedPlans->count() > 0 ): ?>
    <div class="picture">
      <p><a href="#" onclick="javascript: $(this).closest('.picture').find('.seated-plan').slideToggle('medium'); $(this).toggleClass('opened'); return false;"><?php echo __('Display venue') ?></a></p>
      <p class="seated-plan"><?php echo $gauge->getRaw('Workspace')->SeatedPlans[0]->OnlinePicture->getHtmlTag(array('app' => 'pub', 'title' => $gauge->Workspace)) ?></p>
    </div>
  <?php endif ?>
