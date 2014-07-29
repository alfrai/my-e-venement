<ol class="sf_admin_form_row sf_admin_form_field_show_queries">
<?php foreach ( $form->getObject()->Queries as $query ): ?>
  <li>
    <?php echo link_to($query, 'query/edit?id='.$query->id) ?>
    <div class="widget">
      <?php echo $query->render(); ?>
    </div>
  </li>
<?php endforeach ?>
</ol>
