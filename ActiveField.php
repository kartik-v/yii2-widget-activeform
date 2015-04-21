<?php

/**
 * @copyright  Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @package    yii2-widgets
 * @subpackage yii2-widget-activeform
 * @version    1.4.2
 */

namespace kartik\form;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * Extends the ActiveField widget to handle various bootstrap form types and handle input groups.
 *
 * Example(s):
 * ```php
 *    echo $this->form->field($model, 'email', ['addon' => ['type'=>'prepend', 'content'=>'@']]);
 *    echo $this->form->field($model, 'amount_paid', ['addon' => ['type'=>'append', 'content'=>'.00']]);
 *    echo $this->form->field($model, 'phone', ['addon' => ['type'=>'prepend', 'content'=>'<i class="glyphicon
 *     glyphicon-phone']]);
 * ```
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since  1.0
 */
class ActiveField extends \yii\widgets\ActiveField
{

    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';
    const STYLE_INLINE = 'inline';
    const MULTI_SELECT_HEIGHT = '145px';

    /**
     * @var boolean whether to override the form layout styles and skip field formatting
     * as per the form layout. Defaults to `false`.
     */
    public $skipFormLayout = false;

    /**
     * @var string content to be placed before label
     */
    public $contentBeforeLabel = '';

    /**
     * @var string content to be placed after label
     */
    public $contentAfterLabel = '';

    /**
     * @var string content to be placed before input
     */
    public $contentBeforeInput = '';

    /**
     * @var string content to be placed after input
     */
    public $contentAfterInput = '';

    /**
     * @var string content to be placed before error block
     */
    public $contentBeforeError = '';

    /**
     * @var string content to be placed after error block
     */
    public $contentAfterError = '';

    /**
     * @var string content to be placed before hint block
     */
    public $contentBeforeHint = '';

    /**
     * @var string content to be placed after hint block
     */
    public $contentAfterHint = '';

    /**
     * @var array addon options for text and password inputs. The following settings can be configured:
     * - prepend: array the prepend addon configuration
     * - content: string the prepend addon content
     * - asButton: boolean whether the addon is a button or button group. Defaults to false.
     * - options: array the HTML attributes to be added to the container.
     * - append: array the append addon configuration
     * - content: string/array the append addon content
     * - asButton: boolean whether the addon is a button or button group. Defaults to false.
     * - options: array the HTML attributes to be added to the container.
     * - groupOptions: array HTML options for the input group
     * - contentBefore: string content placed before addon
     * - contentAfter: string content placed after addon
     */
    public $addon = [];

    /**
     * @var string CSS classname to add to the input
     */
    public $addClass = 'form-control';

    /**
     * @var string the static value for the field to be displayed
     * for the static input OR when the form is in staticOnly mode.
     * This value is not HTML encoded.
     */
    public $staticValue;

    /**
     * @var boolean|string whether to show labels for the field. Should
     * be one of the following values:
     * - `true`: show labels for the field
     * - `false`: hide labels for the field
     * - `ActiveForm::SCREEN_READER`: show in screen reader only (hide from normal display)
     */
    public $showLabels;

    /**
     * @var boolean whether to show errors for the field
     */
    public $showErrors;

    /**
     * @var boolean whether to show hints for the field
     */
    public $showHints;

    /**
     * @var boolean whether the label is to be hidden and auto-displayed as a placeholder
     */
    public $autoPlaceholder;

    /**
     * @var boolean whether the input is to be offset (like for checkbox or radio).
     */
    protected $_offset = false;

    /**
     * @var boolean the container for multi select
     */
    protected $_multiselect = '';

    /**
     * @var boolean is it a static input
     */
    protected $_isStatic = false;

    /**
     * @var array the settings for the active field layout
     */
    protected $_settings = [
        'input' => '{input}',
        'error' => '{error}',
        'hint' => '{hint}',
        'showLabels' => true,
        'showErrors' => true
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $showLabels = $this->getConfigParam('showLabels');
        if ($this->form->type === ActiveForm::TYPE_INLINE && !isset($this->autoPlaceholder) && $showLabels !== true) {
            $this->autoPlaceholder = true;
        } elseif (!isset($this->autoPlaceholder)) {
            $this->autoPlaceholder = false;
        }
        if ($this->form->type === ActiveForm::TYPE_HORIZONTAL || $this->form->type === ActiveForm::TYPE_VERTICAL) {
            Html::addCssClass($this->labelOptions, 'control-label');
        }
        if ($showLabels === ActiveForm::SCREEN_READER) {
            Html::addCssClass($this->labelOptions, ActiveForm::SCREEN_READER);
        }
        $this->initLabels();
        $this->initLayout();
    }

    /**
     * Renders a static input (display only).
     *
     * @param array $options the tag options in terms of name-value pairs.
     *
     * @return ActiveField object
     */
    public function staticInput($options = [])
    {
        $content = isset($this->staticValue) ? $this->staticValue :
            Html::getAttributeValue($this->model, $this->attribute);
        Html::addCssClass($options, 'form-control-static');
        $this->parts['{input}'] = Html::tag('div', $content, $options);
        $this->_isStatic = true;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function input($type, $options = [])
    {
        $this->initPlaceholder($options);
        if ($type != 'range' || $type != 'color') {
            Html::addCssClass($options, $this->addClass);
        }
        $this->initDisability($options);
        return parent::input($type, $options);
    }

    /**
     * Initializes placeholder based on $autoPlaceholder
     *
     * @param array $options the HTML attributes for the input
     */
    protected function initPlaceholder(&$options)
    {
        if ($this->autoPlaceholder) {
            $label = $this->model->getAttributeLabel(Html::getAttributeName($this->attribute));
            $this->inputOptions['placeholder'] = $label;
            $options['placeholder'] = $label;
        }
    }

    /**
     * Validates and sets disabled or readonly inputs
     *
     * @param array $options the HTML attributes for the input
     */
    protected function initDisability(&$options)
    {
        if ($this->form->disabled && !isset($options['disabled'])) {
            $options['disabled'] = true;
        }
        if ($this->form->readonly && !isset($options['readonly'])) {
            $options['readonly'] = true;
        }
    }

    /**
     * @inheritdoc
     */
    public function textInput($options = [])
    {
        $this->initPlaceholder($options);
        Html::addCssClass($options, $this->addClass);
        $this->initDisability($options);
        return parent::textInput($options);
    }

    /**
     * @inheritdoc
     */
    public function passwordInput($options = [])
    {
        $this->initPlaceholder($options);
        Html::addCssClass($options, $this->addClass);
        $this->initDisability($options);
        return parent::passwordInput($options);
    }

    /**
     * @inheritdoc
     */
    public function textarea($options = [])
    {
        $this->initPlaceholder($options);
        Html::addCssClass($options, $this->addClass);
        $this->initDisability($options);
        return parent::textarea($options);
    }

    /**
     * @inheritdoc
     */
    public function dropDownList($items, $options = [])
    {
        $this->initDisability($options);
        Html::addCssClass($options, $this->addClass);
        return parent::dropDownList($items, $options);
    }

    /**
     * @inheritdoc
     */
    public function listBox($items, $options = [])
    {
        $this->initDisability($options);
        Html::addCssClass($options, $this->addClass);
        return parent::listBox($items, $options);
    }

    /**
     * Generates a toggle field (checkbox or radio)
     *
     * @param string $type the toggle input type 'checkbox' or 'radio'.
     * @param array  $options options (name => config) for the toggle input list container tag.
     *
     * @return ActiveField object
     */
    protected function getToggleField($type = self::TYPE_CHECKBOX, $options = [], $enclosedByLabel = true)
    {
        $this->initDisability($options);
        $inputType = 'active' . ucfirst($type);
        $disabled = ArrayHelper::getValue($options, 'disabled', false);
        $readonly = ArrayHelper::getValue($options, 'readonly', false);
        $css = $disabled ? $type . ' disabled' : $type;
        $container = ArrayHelper::remove($options, 'container', ['class' => $css]);
        if ($enclosedByLabel) {
            $this->_offset = true;
            $this->parts['{label}'] = '';
        } else {
            $this->_offset = false;
            if (isset($options['label']) && !isset($this->parts['{label}'])) {
                $this->parts['label'] = $options['label'];
                if (!empty($options['labelOptions'])) {
                    $this->labelOptions = $options['labelOptions'];
                }
            }
            $options['label'] = null;
            $container = false;
            unset($options['labelOptions']);
        }
        $input = Html::$inputType($this->model, $this->attribute, $options);
        if (is_array($container)) {
            $tag = ArrayHelper::remove($container, 'tag', 'div');
            $input = Html::tag($tag, $input, $container);
        }
        $this->parts['{input}'] = $input;
        $this->adjustLabelFor($options);
        return $this;
    }

    /**
     * Renders a radio button.
     * This method will generate the "checked" tag attribute according to the model attribute value.
     *
     * @param array   $options the tag options in terms of name-value pairs. The following options are specially
     * handled:
     *
     * - uncheck: string, the value associated with the uncheck state of the radio button. If not set,
     *   it will take the default value '0'. This method will render a hidden input so that if the radio button
     *   is not checked and is submitted, the value of this attribute will still be submitted to the server
     *   via the hidden input.
     * - label: string, a label displayed next to the radio button. It will NOT be HTML-encoded. Therefore you can
     *   pass in HTML code such as an image tag. If this is is coming from end users, you should [[Html::encode()]]
     *   it to prevent XSS attacks. When this option is specified, the radio button will be enclosed by a label tag.
     * - labelOptions: array, the HTML attributes for the label tag. This is only used when the "label" option is
     *   specified.
     * - container: boolean|array, the HTML attributes for the checkbox container. If this is set to false, no
     *   container will be rendered. The special option `tag` will be recognized which defaults to `div`. This
     *   defaults to:
     *   `['tag' => 'div', 'class'=>'radio']`
     * The rest of the options will be rendered as the attributes of the resulting tag. The values will be
     * HTML-encoded using [[Html::encode()]]. If a value is null, the corresponding attribute will not be rendered.
     *
     * @param boolean $enclosedByLabel whether to enclose the radio within the label. If `true`, the method will
     * still use [[template]] to layout the checkbox and the error message except that the radio is enclosed by
     * the label tag.
     *
     * @return ActiveField object
     */
    public function radio($options = [], $enclosedByLabel = true)
    {
        return $this->getToggleField(self::TYPE_RADIO, $options, $enclosedByLabel);
    }

    /**
     * Renders a checkbox. This method will generate the "checked" tag attribute according to the model attribute value.
     *
     * @param array   $options the tag options in terms of name-value pairs. The following options are specially
     * handled:
     *
     * - uncheck: string, the value associated with the uncheck state of the checkbox. If not set,
     *   it will take the default value '0'. This method will render a hidden input so that if the checkbox
     *   is not checked and is submitted, the value of this attribute will still be submitted to the server
     *   via the hidden input.
     * - label: string, a label displayed next to the checkbox. It will NOT be HTML-encoded. Therefore you can
     *   pass in HTML code such as an image tag. If this is is coming from end users, you should [[Html::encode()]]
     *   it to prevent XSS attacks. When this option is specified, the checkbox will be enclosed by a label tag.
     * - labelOptions: array, the HTML attributes for the label tag. This is only used when the "label" option is
     *   specified.
     * - container: boolean|array, the HTML attributes for the checkbox container. If this is set to false, no
     *   container will be rendered. The special option `tag` will be recognized which defaults to `div`. This
     *   defaults to:
     *   `['tag' => 'div', 'class'=>'radio']`
     * The rest of the options will be rendered as the attributes of the resulting tag. The values will be
     * HTML-encoded using [[Html::encode()]]. If a value is null, the corresponding attribute will not be rendered.
     *
     * @param boolean $enclosedByLabel whether to enclose the radio within the label. If `true`, the method will
     * still use [[template]] to layout the checkbox and the error message except that the radio is enclosed by
     * the label tag.
     *
     * @return ActiveField object
     */
    public function checkbox($options = [], $enclosedByLabel = true)
    {
        return $this->getToggleField(self::TYPE_CHECKBOX, $options, $enclosedByLabel);
    }

    /**
     * Renders a multi select list box. This control extends the checkboxList and radioList
     * available in yii\widgets\ActiveField - to display a scrolling multi select list box.
     *
     * @param array $items the data item used to generate the checkboxes or radio.
     * @param array $options the options for checkboxList or radioList. Additional parameters
     * - height: string, the height of the multiselect control - defaults to 145px
     * - selector: string, whether checkbox or radio - defaults to checkbox
     * - container: array, options for the multiselect container
     * - unselect: string, the value that should be submitted when none of the radio buttons is
     *   selected. By setting this option, a hidden input will be generated.
     * - separator: string, the HTML code that separates items.
     * - item: callable, a callback that can be used to customize the generation of the HTML code
     *   corresponding to a single item in $items. The signature of this callback must be:
     * - inline: boolean, whether the list should be displayed as a series on the same line,
     *   default is false
     * - selector: string, whether the selection input is [[self::TYPE_RADIO]] or
     *   [[self::TYPE_CHECKBOX]]
     *
     * @return ActiveField object
     */
    public function multiselect($items, $options = [])
    {
        $this->initDisability($options);
        $options['encode'] = false;
        $height = ArrayHelper::remove($options, 'height', self::MULTI_SELECT_HEIGHT);
        $selector = ArrayHelper::remove($options, 'selector', self::TYPE_CHECKBOX);
        $container = ArrayHelper::remove($options, 'container', []);
        Html::addCssStyle($container, 'height:' . $height, true);
        Html::addCssClass($container, $this->addClass . ' input-multiselect');
        $container['tabindex'] = 0;
        $this->_multiselect = Html::tag('div', '{input}', $container);
        return $selector == self::TYPE_RADIO ? $this->radioList($items, $options) :
            $this->checkboxList($items, $options);
    }

    /**
     * Renders a list of radio toggle buttons.
     *
     * @see http://getbootstrap.com/javascript/#buttons-checkbox-radio
     *
     * @param array $items the data item used to generate the radios.
     * The array values are the labels, while the array keys are the corresponding radio values.
     * Note that the labels will NOT be HTML-encoded, while the values will.
     * @param array $options options (name => config) for the radio button list. The following options are specially
     * handled:
     *
     * - unselect: string, the value that should be submitted when none of the radios is selected.
     *   By setting this option, a hidden input will be generated. If you do not want any hidden input,
     *   you should explicitly set this option as null.
     * - separator: string, the HTML code that separates items.
     * - item: callable, a callback that can be used to customize the generation of the HTML code
     *   corresponding to a single item in $items. The signature of this callback must be:
     *
     * ~~~
     * function ($index, $label, $name, $checked, $value)
     * ~~~
     *
     * where $index is the zero-based index of the radio button in the whole list; $label
     * is the label for the radio button; and $name, $value and $checked represent the name,
     * value and the checked status of the radio button input.
     *
     * @return ActiveField object
     */
    public function radioButtonGroup($items, $options = [])
    {
        return $this->getToggleFieldList(self::TYPE_RADIO, $items, $options, true);
    }


    /**
     * Renders a list of checkbox toggle buttons.
     *
     * @see http://getbootstrap.com/javascript/#buttons-checkbox-radio
     *
     * @param array $items the data item used to generate the checkboxes.
     * The array values are the labels, while the array keys are the corresponding checkbox
     * values. Note that the labels will NOT be HTML-encoded, while the values will.
     * @param array $options options (name => config) for the checkbox button list. The following options are specially
     * handled:
     *
     * - unselect: string, the value that should be submitted when none of the checkboxes is selected.
     *   By setting this option, a hidden input will be generated. If you do not want any hidden input,
     *   you should explicitly set this option as null.
     * - separator: string, the HTML code that separates items.
     * - item: callable, a callback that can be used to customize the generation of the HTML code
     *   corresponding to a single item in $items. The signature of this callback must be:
     *
     * ~~~
     * function ($index, $label, $name, $checked, $value)
     * ~~~
     *
     * where $index is the zero-based index of the checkbox button in the whole list; $label
     * is the label for the checkbox button; and $name, $value and $checked represent the name,
     * value and the checked status of the checkbox button input.
     *
     * @return ActiveField object
     */
    public function checkboxButtonGroup($items, $options = [])
    {
        return $this->getToggleFieldList(self::TYPE_CHECKBOX, $items, $options, true);
    }

    /**
     * Renders a list of radio buttons.
     * A radio button list is like a checkbox list, except that it only allows single selection.
     * The selection of the radio buttons is taken from the value of the model attribute.
     *
     * @param array $items the data item used to generate the radio buttons.
     * The array keys are the labels, while the array values are the corresponding radio button
     * values. Note that the labels will NOT be HTML-encoded, while the values will.
     * @param array $options options (name => config) for the radio button list. The following options are specially
     * handled:
     *
     * - unselect: string, the value that should be submitted when none of the radio buttons is selected.
     *   By setting this option, a hidden input will be generated.
     * - separator: string, the HTML code that separates items.
     * - inline: boolean, whether the list should be displayed as a series on the same line, default is false
     * - item: callable, a callback that can be used to customize the generation of the HTML code
     *   corresponding to a single item in $items. The signature of this callback must be:
     *
     * ~~~
     * function ($index, $label, $name, $checked, $value)
     * ~~~
     *
     * where $index is the zero-based index of the radio button in the whole list; $label
     * is the label for the radio button; and $name, $value and $checked represent the name,
     * value and the checked status of the radio button input.
     *
     * @return ActiveField object
     */
    public function radioList($items, $options = [])
    {
        return $this->getToggleFieldList(self::TYPE_RADIO, $items, $options);
    }

    /**
     * Renders a list of checkboxes / radio buttons.
     * The selection of the checkbox / radio buttons is taken from the value of the model attribute.
     *
     * @param string $type the toggle input type 'checkbox' or 'radio'.
     * @param array  $items the data item used to generate the checkbox / radio buttons.
     * The array keys are the labels, while the array values are the corresponding
     * checkbox / radio button values. Note that the labels will NOT be HTML-encoded,
     * while the values will.
     * @param array  $options options (name => config) for the checkbox / radio button list. The following
     * options are specially handled:
     *
     * - unselect: string, the value that should be submitted when none of the checkbox / radio buttons is selected.
     *   By setting this option, a hidden input will be generated.
     * - separator: string, the HTML code that separates items.
     * - inline: boolean, whether the list should be displayed as a series on the same line, default is false
     * - disabledItems: array, the list of values that will be disabled.
     * - readonlyItems: array, the list of values that will be readonly.
     * - item: callable, a callback that can be used to customize the generation of the HTML code
     *   corresponding to a single item in $items. The signature of this callback must be:
     *
     * ~~~
     * function ($index, $label, $name, $checked, $value)
     * ~~~
     *
     * where $index is the zero-based index of the checkbox/ radio button in the whole list; $label
     * is the label for the checkbox/ radio button; and $name, $value and $checked represent the name,
     * value and the checked status of the checkbox/ radio button input.
     *
     * @param bool   $asButtonGroup whether to generate the toggle list as a bootstrap button group
     *
     * @return ActiveField object
     */
    protected function getToggleFieldList($type, $items, $options = [], $asButtonGroup = false)
    {
        $disabled = ArrayHelper::remove($options, 'disabledItems', []);
        $readonly = ArrayHelper::remove($options, 'readonlyItems', []);
        if ($asButtonGroup) {
            Html::addCssClass($options, 'btn-group');
            $options['data-toggle'] = 'buttons';
            $options['inline'] = true;
            if (!isset($options['itemOptions']['labelOptions']['class'])) {
                $options['itemOptions']['labelOptions']['class'] = 'btn btn-default';
            }
        }
        $inline = ArrayHelper::remove($options, 'inline', false);
        $inputType = "{$type}List";
        $this->initDisability($options['itemOptions']);
        $css = $this->form->disabled ? ' disabled' : '';
        $css = $this->form->readonly ? $css . ' readonly' : $css;
        if ($inline && !isset($options['itemOptions']['labelOptions']['class'])) {
            $options['itemOptions']['labelOptions']['class'] = "{$type}-inline{$css}";
        } elseif (!isset($options['item'])) {
            $labelOptions = ArrayHelper::getValue($options, 'itemOptions.labelOptions');
            $options['item'] = function (
                $index,
                $label,
                $name,
                $checked,
                $value
            ) use (
                $type,
                $css,
                $disabled,
                $readonly,
                $asButtonGroup,
                $labelOptions
            ) {
                $opts = [
                    'label' => $label,
                    'value' => $value,
                    'disabled' => $this->form->disabled,
                    'readonly' => $this->form->readonly,
                ];
                if ($asButtonGroup && $checked) {
                    Html::addCssClass($labelOptions, 'active');
                }
                if (!empty($disabled) && in_array($value, $disabled) || $this->form->disabled) {
                    Html::addCssClass($labelOptions, 'disabled');
                    $opts['disabled'] = true;
                }
                if (!empty($readonly) && in_array($value, $readonly) || $this->form->readonly) {
                    Html::addCssClass($labelOptions, 'disabled');
                    $opts['readonly'] = true;
                }
                $opts['labelOptions'] = $labelOptions;
                $out = Html::$type($name, $checked, $opts);
                return $asButtonGroup ? $out : "<div class='{$type}{$css}'>{$out}</div>";
            };
        }
        return parent::$inputType($items, $options);
    }

    /**
     * Renders a list of checkboxes.
     * A checkbox list allows multiple selection, like [[listBox()]].
     * As a result, the corresponding submitted value is an array.
     * The selection of the checkbox list is taken from the value of the model attribute.
     *
     * @param array $items the data item used to generate the checkboxes. The array values are the labels, while the
     * array keys are the corresponding checkbox values. Note that the labels will NOT be HTML-encoded, while the
     * values will.
     * @param array $options options (name => config) for the checkbox list. The following options are specially
     * handled:
     * - unselect: string, the value that should be submitted when none of the checkboxes is selected.
     *   By setting this option, a hidden input will be generated.
     * - separator: string, the HTML code that separates items.
     * - inline: boolean, whether the list should be displayed as a series on the same line, default is false
     * - item: callable, a callback that can be used to customize the generation of the HTML code
     *   corresponding to a single item in $items. The signature of this callback must be:
     * ~~~
     * function ($index, $label, $name, $checked, $value)
     * ~~~
     *
     * where $index is the zero-based index of the checkbox in the whole list; $label
     * is the label for the checkbox; and $name, $value and $checked represent the name,
     * value and the checked status of the checkbox input.
     *
     * @return ActiveField object
     */
    public function checkboxList($items, $options = [])
    {
        return $this->getToggleFieldList(self::TYPE_CHECKBOX, $items, $options);
    }

    /**
     * @inheritdoc
     */
    public function widget($class, $config = [])
    {
        if (property_exists($class, 'disabled') && property_exists($class, 'readonly')) {
            $this->initDisability($config);
        }
        return parent::widget($class, $config);
    }

    /**
     * @inheritdoc
     */
    public function hint($content, $options = [])
    {
        if ($this->getConfigParam('showHints') === false) {
            $this->parts['{hint}'] = '';
            return $this;
        }
        return parent::hint($this->generateHint($content), $options);
    }

    /**
     * @inheritdoc
     */
    public function render($content = null)
    {
        if ($this->getConfigParam('showHints') === false) {
            $this->hintOptions['hint'] = '';
        } else {
            if ($content === null && !isset($this->parts['{hint}']) && !isset($this->hintOptions['hint'])) {
                $this->hintOptions['hint'] = $this->generateHint();
            }
            $this->template = strtr($this->template, ['{hint}' => $this->_settings['hint']]);
        }

        if ($this->form->staticOnly === true) {
            $field = $this->staticInput();
            $this->buildTemplate();
            return parent::render(null);
        }
        $this->initPlaceholder($this->inputOptions);
        $this->initDisability($this->inputOptions);
        $this->buildTemplate();
        return parent::render($content);
    }

    /**
     * Merges the parameters for layout settings
     *
     * @param bool $showLabels whether to show labels
     * @param bool $showErrors whether to show errors
     *
     * @return void
     */
    protected function mergeSettings($showLabels, $showErrors)
    {
        $this->_settings['showLabels'] = $showLabels;
        $this->_settings['showErrors'] = $showErrors;
    }

    /**
     * Sets the layout element container
     *
     * @param string $type the layout element type
     * @param string $css the css class for the container
     * @param bool   $chk whether to create the container for the layout element
     *
     * @return void
     */
    protected function setLayoutContainer($type, $css = '', $chk = true)
    {
        if (!empty($css) && $chk) {
            $this->_settings[$type] = "<div class='{$css}'>{{$type}}</div>";
        }
    }

    /**
     * Builds the field layout parts
     *
     * @param bool $showLabels whether to show labels
     * @param bool $showErrors whether to show errors
     *
     * @return void
     */
    protected function buildLayoutParts($showLabels, $showErrors)
    {
        if (!$showErrors) {
            $this->_settings['error'] = '';
        }
        if ($this->skipFormLayout) {
            $this->mergeSettings($showLabels, $showErrors);
            return;
        }
        $inputDivClass = '';
        $errorDivClass = '';
        if ($this->form->hasInputCss()) {
            $offsetDivClass = $this->form->getOffsetCss();
            $inputDivClass = ($this->_offset) ? $offsetDivClass : $this->form->getInputCss();
            if ($showLabels === false || $showLabels === ActiveForm::SCREEN_READER) {
                $size = ArrayHelper::getValue($this->form->formConfig, 'deviceSize', ActiveForm::SIZE_MEDIUM);
                $errorDivClass = "col-{$size}-{$this->form->fullSpan}";
                $inputDivClass = $errorDivClass;
            } elseif ($this->form->hasOffsetCss()) {
                $errorDivClass = $offsetDivClass;
            }
        }
        $this->setLayoutContainer('input', $inputDivClass);
        $this->setLayoutContainer('error', $errorDivClass, $showErrors);
        $this->setLayoutContainer('hint', $errorDivClass);
        $this->mergeSettings($showLabels, $showErrors);
    }

    /**
     * Initialize label options
     *
     * @return void
     */
    protected function initLabels()
    {
        $labelCss = $this->form->getLabelCss();
        if ($this->hasLabels() === ActiveForm::SCREEN_READER) {
            Html::addCssClass($this->labelOptions, ActiveForm::SCREEN_READER);
        } elseif ($labelCss != ActiveForm::NOT_SET) {
            Html::addCssClass($this->labelOptions, $labelCss);
        }
    }

    /**
     * Initialize layout settings for label, input, error and hint blocks
     * and for various bootstrap 3 form layouts
     *
     * @return void
     */
    protected function initLayout()
    {
        $showLabels = $this->hasLabels();
        $showErrors = $this->getConfigParam('showErrors');
        $this->mergeSettings($showLabels, $showErrors);
        $this->buildLayoutParts($showLabels, $showErrors);
    }

    /**
     * Builds the final template based on the bootstrap form type, display settings for
     * label, error, and hint, and content before and after label, input, error, and hint
     *
     * @return void
     */
    protected function buildTemplate()
    {
        extract($this->_settings);
        if ($this->_isStatic && $this->showErrors !== true) {
            $showErrors = false;
        }
        $showLabels = $showLabels && $this->hasLabels();
        $this->buildLayoutParts($showLabels, $showErrors);
        extract($this->_settings);
        if (!empty($this->_multiselect)) {
            $input = str_replace('{input}', $this->_multiselect, $input);
        }
        $this->template = strtr($this->template, [
            '{label}' => $showLabels ? "{$this->contentBeforeLabel}{label}{$this->contentAfterLabel}" : '',
            '{input}' => str_replace(
                '{input}',
                $this->contentBeforeInput . $this->generateAddon() . $this->contentAfterInput,
                $input
            ),
            '{error}' => $showErrors ? str_replace(
                '{error}',
                "{$this->contentBeforeError}{error}{$this->contentAfterError}",
                $error
            ) : ''
        ]);
    }

    /**
     * Generates the hint.
     *
     * @param string $content the hint content
     *
     * @return string
     */
    protected function generateHint($content = null)
    {
        if ($content === null && method_exists($this->model, 'getAttributeHint')) {
            $content = $this->model->getAttributeHint($this->attribute);
        }
        return $this->contentBeforeHint . $content . $this->contentAfterHint;
    }

    /**
     * Generates the addon markup
     *
     * @return string
     */
    protected function generateAddon()
    {
        if (empty($this->addon)) {
            return '{input}';
        }
        $addon = $this->addon;
        $prepend = static::getAddonContent(ArrayHelper::getValue($addon, 'prepend', ''));
        $append = static::getAddonContent(ArrayHelper::getValue($addon, 'append', ''));
        $content = $prepend . '{input}' . $append;
        $group = ArrayHelper::getValue($addon, 'groupOptions', []);
        Html::addCssClass($group, 'input-group');
        $contentBefore = ArrayHelper::getValue($addon, 'contentBefore', '');
        $contentAfter = ArrayHelper::getValue($addon, 'contentAfter', '');
        $content = Html::tag('div', $contentBefore . $content . $contentAfter, $group);
        return $content;
    }

    /**
     * Parses and returns addon content
     *
     * @param string|array $addon the addon parameter
     *
     * @return string
     */
    public static function getAddonContent($addon)
    {
        if (is_array($addon)) {
            $content = ArrayHelper::getValue($addon, 'content', '');
            $options = ArrayHelper::getValue($addon, 'options', []);
            if (ArrayHelper::getValue($addon, 'asButton', false) == true) {
                Html::addCssClass($options, 'input-group-btn');
                return Html::tag('div', $content, $options);
            } else {
                Html::addCssClass($options, 'input-group-addon');
                return Html::tag('span', $content, $options);
            }
        }
        return $addon;
    }

    /**
     * Gets configuration parameter from formConfig
     *
     * @param string $param the parameter name
     * @param mixed  $default the default parameter value
     *
     * @return the parsed parameter value
     */
    protected function getConfigParam($param, $default = true)
    {
        return isset($this->$param) ? $this->$param : ArrayHelper::getValue($this->form->formConfig, $param, $default);
    }

    /**
     * Validate label display status
     *
     * @return bool|string
     */
    protected function hasLabels()
    {
        $showLabels = $this->getConfigParam('showLabels');
        if ($this->autoPlaceholder && $showLabels !== ActiveForm::SCREEN_READER) {
            $showLabels = false;
        }
        return $showLabels;
    }
}