    <?php foreach ($configuration->getFormFields($form, 'show') as $fieldset => $fields): ?>
    <?php foreach ($fields as $name => $field): ?>
		  <?php $attributes = $field->getConfig('attributes', array()); ?>
			<?php if ($field->isPartial()): ?>
		    <?php include_partial('seated_plan/'.$name, array('seated_plan' => $seated_plan, 'form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?>
		  <?php elseif ($field->isComponent()): ?>
		    <?php include_component('seated_plan', $name, array('seated_plan' => $seated_plan, 'form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?>
		  <?php else: ?>
        <?php echo $form->getObject()->get($name) ? $form->getObject()->get($name) : "&nbsp;" ?>
      <?php endif; ?>
    <?php endforeach; ?>
    <?php endforeach; ?>
