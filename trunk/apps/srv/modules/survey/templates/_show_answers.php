<?php // TODO ?>
<ol>
<?php foreach ( $form->getObject()->Queries as $query ): ?>
<li><?php echo $query ?><ul>
<?php foreach ( $query->Answers as $answer ): ?>
  <li><?php echo $answer->value ?></li>
<?php endforeach ?>
</ul></li>
<?php endforeach ?>
</ol>
