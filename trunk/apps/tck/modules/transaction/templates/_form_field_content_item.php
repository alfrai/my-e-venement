<?php use_helper('Number') ?>
          <h4>Gauge 1</h4>
          <table class="declinations">
            <tbody>
              <tr class="declination">
                <td class="qty nb">2</td>
                <td class="price">TP</td>
                <td class="tep nb monney" title="<?php echo __('PET') ?>"><?php echo format_currency(19, '€') ?></td>
                <td class="vat nb monney" title="<?php echo __('VAT') ?>"><?php echo format_currency(1, '€') ?></td>
                <td class="pit nb monney" title="<?php echo __('Total') ?>"><?php echo format_currency(20, '€') ?></td>
              </tr>
              <tr class="declination">
                <td class="qty nb">1</td>
                <td class="price">EXO</td>
                <td class="tep nb monney" title="<?php echo __('PET') ?>"><?php echo format_currency(9.5, '€') ?></td>
                <td class="vat nb monney" title="<?php echo __('VAT') ?>"><?php echo format_currency(0.5, '€') ?></td>
                <td class="pit nb monney" title="<?php echo __('Total') ?>"><?php echo format_currency(10, '€') ?></td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="total">
                <td class="qty nb"></td>
                <td class="price">&nbsp;</td>
                <td class="tep nb monney" title="<?php echo __('PET') ?>"></td>
                <td class="vat nb monney" title="<?php echo __('VAT') ?>"></td>
                <td class="pit nb monney" title="<?php echo __('Total') ?>"></td>
                <td class="currency">€</td>
              </tr>
            </tfoot>
          </table>
