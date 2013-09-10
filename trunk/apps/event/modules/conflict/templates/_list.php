<?php use_stylesheet('resource-conflict') ?>
<?php //include_partial('filters',array('form' => $form)) ?>

<?php $columns = array(
    'resource'      => __('Resource'),
    'date'          => __('Date'),
    'manifestation' => __('Manifestation'),
    'booked_from'   => __('Booked from'),
    'booked_until'  => __('Booked until'),
    'event'         => __('Event'),
    'location'      => __('Location'),
    'meta_event'    => __('Meta event'),
  ); ?>

<div class="sf_admin_list ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
  <table id="conflicts">
    <caption class="ui-widget-header ui-corner-top fg-toolbar">
      <?php //include_partial('filters_buttons') ?>
      <h1>
        <span class="ui-icon ui-icon-triangle-1-s"></span>
        <?php echo __('Use conflicts',null,'menu') ?>
      </h1>
    </caption>
    <thead class="ui-widget-header" style="display: table-header-group;">
      <?php include_partial('list_th_tabular', array('columns' => $columns)) ?>
    </thead>
    <tbody>
    <?php for ( $i = 0 ; $i < $manifestations->count() ; $i++ ): ?>
      <tr class="sf_admin_row ui-widget-content <?php echo $i%2 == 0 ? 'odd' : '' ?>">
        <?php include_partial('list_tr', array(
          'columns' => $columns,
          'manifestations' => $manifestations,
          'i' => $i,
          'show_nb' => count($conflicts[$manifestations[$i]->id])+1,
          'conflict' => $conflicts[$manifestations[$i]->id],
        )) ?>
        <?php foreach ( $conflicts[$manifestations[$i]->id] as $conflict ): ?>
          <?php if ( isset($manifestations[$i+1]) && $manifestations[$i+1]->id === $conflict['manifestation_id'] ): ?>
            <?php $i++ ?>
            <tr class="sf_admin_row ui-widget-content <?php echo $i%2 == 0 ? 'odd' : '' ?>">
              <?php include_partial('list_tr', array(
                'columns' => $columns,
                'manifestations' => $manifestations,
                'i' => $i,
                'show_nb' => 0,
                'conflict' => $conflict,
              )) ?>
            </tr>
          <?php endif ?>
        <?php endforeach ?>
        <?php /* ?>
        <td class="sf_admin_text conflicts_td_resource">
          <?php echo link_to($manifestations[$i]->Location, 'location/show?id='.$manifestations[$i]->Location->id) ?>
        </td>
        <td class="sf_admin_text conflicts_td_date">
          <?php echo $manifestations[$i]->getShortenedDate() ?>
        </td>
        <td class="sf_admin_text conflicts_td_meta_event">
          <?php echo $manifestations[$i]->Event->MetaEvent ?>
        </td>
        <td class="sf_admin_text conflicts_td_event">
          <?php echo link_to($manifestations[$i]->Event, 'event/show?id='.$manifestations[$i]->Event->id) ?>
        </td>
        <td class="sf_admin_text conflicts_td_manifestation">
          <?php echo link_to($manifestations[$i]->getShortenedDate(), 'manifestation/show?id='.$manifestations[$i]->id) ?>
        </td>
        <td class="sf_admin_text conflicts_td_location">
          <?php echo link_to($manifestations[$i]->Location, 'location/show?id='.$manifestations[$i]->Location->id) ?>
        </td>
            <tr class="sf_admin_row ui-widget-content <?php echo $i%2 == 0 ? '' : 'odd' ?>">
              <td class="sf_admin_text conflicts_td_resource">
              </td>
              <td class="sf_admin_text conflicts_td_date">
              </td>
              <td class="sf_admin_text conflicts_td_meta_event">
                <?php echo $manifestations[$i]->Event->MetaEvent ?>
              </td>
              <td class="sf_admin_text conflicts_td_event">
                <?php echo link_to($manifestations[$i]->Event, 'event/show?id='.$manifestations[$i]->Event->id) ?>
              </td>
              <td class="sf_admin_text conflicts_td_manifestation">
                <?php echo link_to($manifestations[$i]->getShortenedDate(), 'manifestation/show?id='.$manifestations[$i]->id) ?>
              </td>
              <td class="sf_admin_text conflicts_td_location">
                <?php echo link_to($manifestations[$i]->Location, 'location/show?id='.$manifestations[$i]->Location->id) ?>
              </td>
            </tr>
          <?php endif ?>
        <?php endforeach ?>
        <?php */ ?>
      </tr>
    <?php endfor ?>
    </tbody>
  </table>
</div>
