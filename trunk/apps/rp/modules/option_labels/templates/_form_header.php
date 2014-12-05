<?php $formats = array(
      'L6173' => array(
        'page-format'   => 'a4',
        'nb-x'          => 3,
        'nb-y'          => 8,
        'left-right'    => 0,
        'top-bottom'    => ,
        'margin-x'      => 0,
        'margin-y'      => 0,
        'padding-x'     => 2.5,
        'padding-y'     => 2,
      ),
      'L7159' => array(
        'page-format'   => 'a4',
        'nb-x'          => 3,
        'nb-y'          => 8,
        'left-right'    => 3,
        'top-bottom'    => 9,
        'margin-x'      => 0,
        'margin-y'      => 0,
        'padding-x'     => 2.5,
        'padding-y'     => 4,
      ),
      'L7160' => array(
        'page-format'   => 'a4',
        'nb-x'          => 3,
        'nb-y'          => 7,
        'left-right'    => 7,
        'top-bottom'    => 15,
        'margin-x'      => 2.5,
        'margin-y'      => 0,
        'padding-x'     => 2.5,
        'padding-y'     => 5,
      ),
      'L7562' => array(
        'page-format'   => 'a4',
        'nb-x'          => 2,
        'nb-y'          => 8,
        'left-right'    => 4.63,
        'top-bottom'    => 12.83,
        'margin-x'      => 2.54,
        'margin-y'      => 0,
        'padding-x'     => 2.5,
        'padding-y'     => 4,
      ),
      'L7163' => array(
        'page-format'   => 'a4',
        'nb-x'          => 2,
        'nb-y'          => 7,
        'left-right'    => 3,
        'top-bottom'    => 15,
        'margin-x'      => 0,
        'margin-y'      => 0,
        'padding-x'     => 2.5,
        'padding-y'     => 5,
      ),
) ?>

<form method="get" action="#" class="ui-corner-all ui-widget-content templates" onsubmit="javascript: return false;">
  <label><?php echo __('Templates') ?>:</label>
  <select name="label-formats">
    <option></option>
    <?php foreach ( $formats as $name => $values ): ?>
    <option><?php echo $name ?></option>
    <?php endforeach ?>
  </select>
  <div style="display: none;">
    <script type="text/javascript">
      $(document).ready(function(){
        $('.templates [name=label-formats]').change(function(){
          if ( !$(this).val() )
            return false;
          
          // apply the template
          $(this).closest('form').find('.'+$(this).val()+' input').each(function(){
            $('.data [name="'+$(this).prop('name')+'"]').val($(this).val());
          });
        });
      });
    </script>
    <?php foreach ( $formats as $name => $values ): ?>
    <p class="<?php echo $name ?>">
    <?php foreach ( $values as $name => $value ): ?>
      <input type="hidden" name="<?php echo $name ?>" value="<?php echo $value ?>" />
    <?php endforeach ?>
    </p>
    <?php endforeach ?>
  </div>
</form>
