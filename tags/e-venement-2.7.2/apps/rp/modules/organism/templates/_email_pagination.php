<?php
$first = ($pager->getPage() * $pager->getMaxPerPage() - $pager->getMaxPerPage() + 1);
$last = $first + $pager->getMaxPerPage() - 1;
?>

<table id="sf_admin_pager">
  <tbody>
    <tr>
      <td class="left"></td>
      <td class="center">
        <table align="center" class="sf_admin_pagination">
          <tbody>
            <tr>
              <?php if ($pager->haveToPaginate()): ?>
              <td class="button">
                <a href="<?php echo url_for('emailList?id='.$email_id.'&page=1') ?>"<?php if ($pager->getPage() == 1) echo ' class="ui-state-disabled"' ?>>
                  <span class="ui-icon ui-icon-seek-first"></span>                </a>
              </td>

              <td class="button">
                <a href="<?php echo url_for('emailList?id='.$email_id.'&page='.$pager->getPreviousPage()) ?>"<?php if ($pager->getPage() == 1) echo ' class="ui-state-disabled"' ?>>
                  <span class="ui-icon ui-icon-seek-prev"></span>                </a>
              </td>

              <td class="button">
                <a href="<?php echo url_for('emailList?id='.$email_id.'&page='.$pager->getNextPage()) ?>"<?php if ($pager->getPage() == $pager->getLastPage()) echo ' class="ui-state-disabled"' ?>>
                  <span class="ui-icon ui-icon-seek-next"></span>                </a>
              </td>

              <td class="button">
                <a href="<?php echo url_for('emailList?id='.$email_id.'&page='.$pager->getLastPage()) ?>"<?php if ($pager->getPage() == $pager->getLastPage()) echo ' class="ui-state-disabled"' ?>>
                  <span class="ui-icon ui-icon-seek-end"></span>                </a>
              </td>
              <?php endif; ?>
            </tr>
          </tbody>
        </table>
      </td>
      <td class="right">
        <?php
      	echo __('View %1% - %2% of %3%',
          array(
            '%1%' => $first,
            '%2%' => ($last > $pager->getNbResults()) ? $pager->getNbResults() : $last,
            '%3%' => $pager->getNbResults()
          )
      	)
      	?>
      </td>
    </tr>
  </tbody>
</table>
