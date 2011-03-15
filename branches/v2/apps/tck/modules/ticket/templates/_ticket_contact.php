  <form class="ui-corner-all ui-widget-content action" id="contact" action="<?php echo url_for('ticket/addContact') ?>" method="post">
    <p>
      <span class="title"><?php echo __('Contact') ?>:</span>
      <span class="contact">
        <input type="hidden" name="sf_method" value="put" />
        <input type="hidden" name="id" value="<?php echo $transaction->id ?>" />
        <input type="hidden" value="<?php echo $form->getCSRFToken() ?>" name="_csrf_token" />
        <?php
          $w = new sfWidgetFormDoctrineJQueryAutocompleter(array(
            'model' => 'Contact',
            'url'   => cross_app_url_for('rp','contact/ajax'),
          ));
          echo $w->render('contact_id');
        ?>
      </span>
      <span class="professional">
      </span>
    </p>
  </form>
