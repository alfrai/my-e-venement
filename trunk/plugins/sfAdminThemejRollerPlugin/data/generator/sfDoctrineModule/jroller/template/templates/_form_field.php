[?php if ($field->isPartial()): ?]
  [?php include_partial('<?php echo $this->getModuleName() ?>/'.$name, array('form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?]
[?php elseif ($field->isComponent()): ?]
  [?php include_component('<?php echo $this->getModuleName() ?>', $name, array('form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?]
[?php else: ?]
  [?php $widgets = isset($form[$name]) ? array($form[$name]) : array(); ?]
  [?php if ( count($widgets) == 0 ) foreach ( $langs = sfConfig::get('project_internals_cultures',array('fr')) as $culture => $lang ): ?]
    [?php $widgets[$culture] = $form[$culture][$name]; ?]
  [?php endforeach ?]
  
  [?php $errors = 0; foreach ( $widgets as $widget ) $errors += $widget->hasError() ? 1 : 0; ?]
  
  <div class="[?php echo $class ?][?php $errors > 0 and print ' ui-state-error ui-corner-all li-nb-errors-'.count($errors) ?]">
    [?php $widget = array_values($widgets); $widget = $widget[0]; ?]
    [?php echo $widget->renderLabel($label) ?]
    <div class="label ui-helper-clearfix">
    
      [?php if ($help || $help = $widget->renderHelp()): ?]
        <div class="help">
          <span class="ui-icon ui-icon-help floatleft"></span>
          [?php echo __(strip_tags($help), array(), '<?php echo $this->getI18nCatalogue() ?>') ?]
        </div>
      [?php endif; ?]
    </div>

    [?php foreach ( $widgets as $culture => $widget ): ?]
    <div class="widget [?php echo $culture !== 0 ? 'culture-'.$culture.'" title="'.$langs[$culture].'"' : '"' ?]>
      [?php echo $widget->render($attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes) ?]
      [?php if ( $culture !== 0 ): ?]
      <span class="culture">[?php echo $culture ?]</span>
      <span class="lang culture-[?php echo $culture ?]">[?php echo $langs[$culture] ?]</span>
      [?php endif ?]
  
      [?php if ($widget->hasError()): ?]
        <div class="errors">
          <span class="ui-icon ui-icon-alert floatleft"></span>
          [?php echo $widget->renderError() ?]
        </div>
      [?php endif; ?]
    </div>
    [?php endforeach ?]
  </div>
[?php endif; ?]
