<?php include_partial('global/assets') ?>
<?php $sf_response->removeStylesheet('default') ?>
<?php use_stylesheet('default-data') ?>
<div id="sf_admin_container">
  <?php include_partial('global/flashes') ?>
  <div id="sf_admin_content">
    <div class="backups ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
      <div class="ui-widget-content ui-corner-all">
        <div class="ui-widget-header ui-corner-all fg-toolbar">
          <h2>Backups</h2>
        </div>
        <ol>
          <?php foreach ( $directory->ls('*', 'desc') as $filename ): ?>
            <li>
              <span class="size"><?php echo $directory->fileSizeHR($filename) ?></span>
              <span class="mtime"><?php echo date('Y-m-d H:i:s', $directory->getFileLastModification($filename)) ?></span>
              <?php echo link_to($filename, 'data/file?uri='.$filename, array('target' => '_blank')) ?>
            </li>
          <?php endforeach ?>
        </ol>
      </div>
    </div>
  </div>
</div>
