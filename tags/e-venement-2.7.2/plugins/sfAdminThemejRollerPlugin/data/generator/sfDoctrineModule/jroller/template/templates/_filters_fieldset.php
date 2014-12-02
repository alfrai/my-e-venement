<tr>
  <td colspan="2" class="fieldset">
    <div class="ui-widget ui-widget-content ui-corner-all">
    <table>
      <thead>
        <tr><td colspan="2">
          <div class="ui-widget-header ui-corner-all fg-toolbar">
            <h2>[?php echo __(ucfirst($name)) ?]</h2>
          </div>
        </td></tr>
      </thead>
      <tbody>
        [?php foreach ($fieldset as $name => $field): ?]
          [?php if ((isset($form[$name]) && $form[$name]->isHidden()) || (!isset($form[$name]) && $field->isReal())) continue ?]
          [?php include_partial('filters_field', array(
            'name'       => $name,
            'attributes' => $field->getConfig('attributes', array()),
            'label'      => $field->getConfig('label'),
            'help'       => $field->getConfig('help'),
            'form'       => $form,
            'field'      => $field,
            'class'      => 'sf_admin_form_row sf_admin_'.strtolower($field->getType()).' sf_admin_filter_field_'.$name,
          )) ?]
        [?php endforeach; ?]
      </tbody>
    </table>
    </div>
  </td>
</tr>
