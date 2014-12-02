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
                <a href="<?php echo url_for('@contact?page=1') ?>"<?php if ($pager->getPage() == 1) echo ' class="ui-state-disabled"' ?>>
                  <span class="ui-icon ui-icon-seek-first"></span>                </a>
              </td>

              <td class="button">
                <a href="<?php echo url_for('@contact?page='.$pager->getPreviousPage()) ?>"<?php if ($pager->getPage() == 1) echo ' class="ui-state-disabled"' ?>>
                  <span class="ui-icon ui-icon-seek-prev"></span>                </a>
              </td>

              <td align="center">
                <?php echo __('Page') ?>
                <input type="text" onkeypress="javascript: if(event.keyCode == 13){ window.location = 'contact?page='+this.value; this.form.onsubmit = function(){ return false;}; }" name="page" value="<?php echo $pager->getPage() ?>" maxlength="7" size="2" />
                <?php echo __('of %1%', array('%1%' => $pager->getLastPage())) ?>
            	</td>

              <td class="button">
                <a href="<?php echo url_for('@contact?page='.$pager->getNextPage()) ?>"<?php if ($pager->getPage() == $pager->getLastPage()) echo ' class="ui-state-disabled"' ?>>
                  <span class="ui-icon ui-icon-seek-next"></span>                </a>
              </td>

              <td class="button">
                <a href="<?php echo url_for('@contact?page='.$pager->getLastPage()) ?>"<?php if ($pager->getPage() == $pager->getLastPage()) echo ' class="ui-state-disabled"' ?>>
                  <span class="ui-icon ui-icon-seek-end"></span>                </a>
              </td>
              <?php endif; ?>
            </tr>
          </tbody>
        </table>
      </td>
      <td class="right">
        <?php
          $q = $pager->getQuery();
          if ( $q->getRawValue() instanceof Doctrine_Query )
          {
            $res = $q->copy()
              ->select('count(DISTINCT (c.id,y.id)) AS nb_indiv')
              ->removeDqlQueryPart('orderby')
              ->removeDqlQueryPart('limit')
              ->removeDqlQueryPart('offset')
              ->fetchArray();
            $nb_indiv = $res[0]['nb_indiv'];
          }
          else
            $nb_indiv = '-';
        ?>
        <?php
      	echo __('View %1% - %2% of %3% (%4%)',
          array(
            '%1%' => $first,
            '%2%' => ($last > $pager->getNbResults()) ? $pager->getNbResults() : $last,
            '%3%' => $pager->getNbResults(),
            '%4%' => $nb_indiv,
          )
      	)
      	?>
      </td>
    </tr>
  </tbody>
</table>
