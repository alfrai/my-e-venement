<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Baptiste SIMON <baptiste.simon AT libre-informatique.fr>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * liWidgetFormSelectDoubleListJQuery represents a multiple select displayed as a double list.
 *
 * This widget needs some JavaScript to work. So, you need to include the JavaScripts
 * files returned by the getJavaScripts() method.
 *
 * If you use symfony 1.2, it can be done automatically for you.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormSelectDoubleList.class.php 30760 2010-08-25 11:50:26Z fabien $
 */
class liWidgetFormSelectDoubleListJQuery extends sfWidgetFormSelectDoubleList
{
  /**
   * Constructor.
   *
   * Available options:
   *
   * * js_file:            sfPath to JS file managing the double widget
   * * js_object:          the name of the JS object in the JS file (used by default template)
  **/
  public function configure($options = array(), $attributes = array())
  {
    $this->addOption('js_file', 'li_double_list');
    $this->addOption('js_object', 'liDoubleList');
    parent::configure($options, $attributes);
    
    $associated_first = isset($options['associated_first']) ? $options['associated_first'] : true;
    if ($associated_first)
    {
      $associate_image = 'previous.png';
      $unassociate_image = 'next.png';
      $float = 'left';
    }
    else
    {
      $associate_image = 'next.png';
      $unassociate_image = 'previous.png';
      $float = 'right';
    }

    $this->addOption('template', <<<EOF
<div class="%class%">
  <div style="float: left">
    <div style="float: $float">
      <div class="double_list_label">%label_associated%</div>
      %associated%
    </div>
    <div style="float: $float; margin-top: 2em">
      %associate%
      <br />
      %unassociate%
    </div>
    <div style="float: $float">
      <div class="double_list_label">%label_unassociated%</div>
      %unassociated%
    </div>
  </div>
  <br style="clear: both" />
  <script type="text/javascript">
    %js_object%.init(document.getElementById('%id%'), '%class_select%-selected');
  </script>
</div>
EOF
);
  }
  
  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The value selected in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    foreach ( $this->getJavascripts() as $js )
      use_javascript($js);
    
    if (is_null($value))
    {
      $value = array();
    }

    $choices = $this->getOption('choices');
    if ($choices instanceof sfCallable)
    {
      $choices = $choices->call();
    }

    $associated = array();
    $unassociated = array();
    foreach ($choices as $key => $option)
    {
      if (in_array(strval($key), $value))
      {
        $associated[$key] = $option;
      }
      else
      {
        $unassociated[$key] = $option;
      }
    }

    $size = isset($attributes['size']) ? $attributes['size'] : (isset($this->attributes['size']) ? $this->attributes['size'] : 10);

    $associatedWidget = new sfWidgetFormSelect(array('multiple' => true, 'choices' => $associated), array('size' => $size, 'class' => $this->getOption('class_select').'-selected'));
    $unassociatedWidget = new sfWidgetFormSelect(array('multiple' => true, 'choices' => $unassociated), array('size' => $size, 'class' => $this->getOption('class_select')));

    return strtr($this->getOption('template'), array(
      '%class%'              => $this->getOption('class'),
      '%class_select%'       => $this->getOption('class_select'),
      '%id%'                 => $this->generateId($name),
      '%label_associated%'   => $this->getOption('label_associated'),
      '%label_unassociated%' => $this->getOption('label_unassociated'),
      '%associate%'          => sprintf('<a href="#" onclick="%s">%s</a>', "liDoubleList.move($(this).closest('.double_list').find('.double_list_select'), $(this).closest('.double_list').find('.double_list_select-selected')); return false;", $this->getOption('associate')),
      '%unassociate%'        => sprintf('<a href="#" onclick="%s">%s</a>', "liDoubleList.move($(this).closest('.double_list').find('.double_list_select-selected'), $(this).closest('.double_list').find('.double_list_select')); return false;", $this->getOption('unassociate')),
      '%associated%'         => $associatedWidget->render($name),
      '%unassociated%'       => $unassociatedWidget->render('unassociated_'.$name),
      '%js_object%'          => $this->getOption('js_object'),
    ));
  }

  /**
   * Gets the JavaScript paths associated with the widget.
   *
   * @return array An array of JavaScript paths
   */
  public function getJavascripts()
  {
    return array($this->getOption('js_file'));
  }
}
