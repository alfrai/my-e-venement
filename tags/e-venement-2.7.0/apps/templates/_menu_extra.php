<?php $extras = sfConfig::get('project_menu_'.$name, array()) ?>
<?php foreach ( $extras as $label => $props ): ?>
  <?php if ( !isset($props['credential']) || isset($props['credential']) && $sf_user->hasCredential($props['credential']) ): ?>
  <li <?php if ( isset($props['extra_properties']) && is_array($props['extra_properties']) ) foreach ( $props['extra_properties'] as $name => $value ): ?>
    <?php echo $name ?>="<?php echo $value ?>" 
    <?php endforeach ?>
  >
    <a href="<?php echo $props['url'] ?>"
       <?php if ( isset($props['target']) ) echo 'target="'.$props['target'].'"'; ?>
    ><?php echo $label ?></a>
  </li>
  <?php endif ?>
<?php endforeach ?>
