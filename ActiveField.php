<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @package yii2-widgets
 * @subpackage yii2-widget-activeform
 * @version 1.3.0
 */

namespace kartik\form;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * Extends the ActiveField widget to handle various
 * bootstrap form types and handle input groups.
 *
 * ADDITIONAL VARIABLES/PARAMETERS:
 * ===============================
 *
 * @param boolean $autoPlaceholder whether to display the label as a placeholder (default false)
 * @param array   $addon whether to prepend or append an addon to an input group - contains these keys:
 * - prepend: array the prepend addon configuration
 *     - content: string the prepend addon content
 *     - asButton: boolean whether the addon is a button or button group. Defaults to false.
 *     - options: array the HTML attributes to be added to the container.
 * - append: array the append addon configuration
 *     - content: string/array the append addon content
 *     - asButton: boolean whether the addon is a button or button group. Defaults to false.
 *     - options: array the HTML attributes to be added to the container.
 * - groupOptions: array HTML options for the input group
 * - contentBefore: string content placed before addon
 * - contentAfter: string content placed after addon
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
 * @since 1.0
 */
class ActiveField extends \yii\widgets\ActiveField
{

    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';
    const STYLE_INLINE = 'inline';
    const MULTI_SELECT_HEIGHT = '145px';

    /**
     * @var string content to be placed before input
     */
    public $contentBeforeInput = '';

    /**
     * @var string content to be placed after input
     */
    public $contentAfterInput = '';

    /**
     * @var array addon options for text and password inputs
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
    private $_offset = false;

    /**
     * @var boolean the container for multi select
     */
    private $_multiselect = '';

    /**
     * @var boolean is it a static input
     */
    private $_isStatic = false;

    /**
     * @inherit doc
     */
    public function init()
    {
        parent::init();
        if ($this->form->type === ActiveForm::TYPE_INLINE && !isset($this->autoPlaceholder)) {
            $this->autoPlaceholder = true;
        } elseif (!isset($this->autoPlaceholder)) {
            $this->autoPlaceholder = false;
        }
        if ($this->form->type === ActiveForm::TYPE_HORIZONTAL || $this->form->type === ActiveForm::TYPE_VERTICAL) {
            Html::addCssClass($this->labelOptions, 'control-label');
        }
        if ($this->showLabels === ActiveForm::SCREEN_READER) {
            Html::addCssClass($this->labelOptions, ActiveForm::SCREEN_READER);
        }
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
        $content = isset($this->staticValue) ? $this->staticValue : Html::getAttributeValue($this->model,
            $this->attribute);
        Html::addCssClass($options, 'form-control-static');
        $this->parts['{input}'] = Html::tag('div', $content, $options);
        $this->_isStatic = true;
        return $this;
    }

    /**
     * @inherit doc
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
            $label = $this->model->getAttributeLabel($this->attribute);
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
     * @inherit doc
     */
    public function textInput($options = [])
    {
        $this->initPlaceholder($options);
        Html::addCssClass($options, $this->addClass);
        $this->initDisability($options);
        return parent::textInput($options);
    }

    /**
     * @inherit doc
     */
    public function passwordInput($options = [])
    {
        $this->initPlaceholder($options);
        Html::addCssClass($options, $this->addClass);
        $this->initDisability($options);
        return parent::passwordInput($options);
    }

    /**
     * @inherit doc
     */
    public function textarea($options = [])
    {
        $this->initPlaceholder($options);
        Html::addCssClass($options, $this->addClass);
        $this->initDisability($options);
        return parent::textarea($options);
    }

    /**
     * @inherit doc
     */
    public function dropDownList($items, $options = [])
    {
        $this->initDisability($options);
        Html::addCssClass($options, $this->addClass);
        return parent::dropDownList($items, $options);
    }

    /**
     * @inherit doc
     */
    public function listBox($items, $options = [])
    {
        $this->initDisability($options);
        Html::addCssClass($options, $this->addClass);
        return parent::listBox($items, $options);
    }

    /**
     * Renders a radio button.
     * This method will generate the "checked" tag attribute according to the model attribute value.
     *
     * @param array   $options the tag options in terms of name-value pairs. The following options are specially
     *     handled:
     *
     * - uncheck: string, the value associated with the uncheck state of the radio button. If not set,
     *   it will take the default value '0'. This method will render a hidden input so that if the radio button
     *   is not checked and is submitted, the value of this attribute will still be submitted to the server
     *   via the hidden input.
     * - label: string, a label displayed next to the radio button.  It will NOT be HTML-encoded. Therefore you can
     *     pass
     *   in HTML code such as an image tag. If this is is coming from end users, you should [[Html::encode()]] it to
     *     prevent XSS attacks. When this option is specified, the radio button will be enclosed by a label tag.
     * - labelOptions: array, the HTML attributes for the label tag. This is only used when the "label" option is
     *     specified.
     * - container: boolean|array, the HTML attributes for the checkbox container. If this is set to false, no
     *     container will be rendered. The special option `tag` will be recognized which defaults to `div`. This
     *     defaults to:
     *   `['tag' => 'div', 'class'=>'radio']`
     *
     * The rest of the options will be rendered as the attributes of the resulting tag. The values will
     * be HTML-encoded using [[Html::encode()]]. If a value is null, the corresponding attribute will not be rendered.
     * @param boolean $enclosedByLabel whether to enclose the radio within the label.
     * If true, the method will still use [[template]] to layout the checkbox and the error message
     * except that the radio is enclosed by the label tag.
     *
     * @return ActiveField object
     */
    public function radio($options = [], $enclosedByLabel = true)
    {
        return $this->getToggleField(self::TYPE_RADIO, $options, $enclosedByLabel);
    }

    /**
     * Generates a toggle field (checkbox or radio)
     *
     * @param string $type the toggle input type 'checkbox' or 'radio'.
     * @param array  $items the data item used to generate the checkboxes / radios.
     * The array values are the labels, while the array keys are the corresponding checkbox / radio values.
     * @param array  $options options (name => config) for the toggle input list container tag.
     *
     * @return ActiveField object
     */
    protected function getToggleField($type = self::TYPE_CHECKBOX, $options = [], $enclosedByLabel = true)
    {
        $this->initDisability($options);
        $this->_offset = true;
        $inputType = 'active' . ucfirst($type);
        $disabled = ArrayHelper::getValue($options, 'disabled', false);
        $readonly = ArrayHelper::getValue($options, 'readonly', false);
        $css = $disabled ? $type . ' disabled' : $type;
        $container = ArrayHelper::remove($options, 'container', ['class' => $css]);
        if ($enclosedByLabel) {
            $this->parts['{label}'] = '';
        } else {
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
     * Renders a checkbox.
     * This method will generate the "checked" tag attribute according to the model attribute value.
     *
     * @param array   $options the tag options in terms of name-value pairs. The following options are specially
     *     handled:
     *
     * - uncheck: string, the value associated with the uncheck state of the radio button. If not set,
     *   it will take the default value '0'. This method will render a hidden input so that if the radio button
     *   is not checked and is submitted, the value of this attribute will still be submitted to the server
     *   via the hidden input.
     * - label: string, a label displayed next to the checkbox.  It will NOT be HTML-encoded. Therefore you can pass
     *   in HTML code such as an image tag. If this is is coming from end users, you should [[Html::encode()]] it to
     *     prevent XSS attacks. When this option is specified, the checkbox will be enclosed by a label tag.
     * - labelOptions: array, the HTML attributes for the label tag. This is only used when the "label" option is
     *     specified.
     * - container: boolean|array, the HTML attributes for the checkbox container. If this is set to false, no
     *     container will be rendered. The special option `tag` will be recognized which defaults to `div`. This
     *     defaults to:
     *   `['tag' => 'div', 'class'=>'checkbox']`
     *
     * The rest of the options will be rendered as the attributes of the resulting tag. The values will
     * be HTML-encoded using [[Html::encode()]]. If a value is null, the corresponding attribute will not be rendered.
     * @param boolean $enclosedByLabel whether to enclose the checkbox within the label.
     * If true, the method will still use [[template]] to layout the checkbox and the error message
     * except that the checkbox is enclosed by the label tag.
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
     * - unselect: string, the value that should be submitted when none of the radio buttons is selected.
     *   By setting this option, a hidden input will be generated.
     * - separator: string, the HTML code that separates items.
     * - item: callable, a callback that can be used to customize the generation of the HTML code
     *   corresponding to a single item in $items. The signature of this callback must be:
     * - inline: boolean, whether the list should be displayed as a series on the same line, default is false
     * - selector: string, whether the selection input is [[self::TYPE_RADIO]] or [[self::TYPE_CHECKBOX]]
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
        return $selector == self::TYPE_RADIO ? $this->radioList($items, $options) : $this->checkboxList($items,
            $options);
    }

    /**
     * Renders a list of radio buttons.
     * A radio button list is like a checkbox list, except that it only allows single selection.
     * The selection of the radio buttons is taken from the value of the model attribute.
     *
     * @param array $items the data item used to generate the radio buttons.
     * The array keys are the labels, while the array values are the corresponding radio button values.
     * Note that the labels will NOT be HTML-encoded, while the values will.
     * @param array $options options (name => config) for the radio button list. The following options are specially
     *     handled:
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
     * The array keys are the labels, while the array values are the corresponding checkbox / radio button values.
     * Note that the labels will NOT be HTML-encoded, while the values will.
     * @param array  $options options (name => config) for the checkbox / radio button list. The following options are
     *     specially handled:
     *
     * - unselect: string, the value that should be submitted when none of the checkbox / radio buttons is selected.
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
     * where $index is the zero-based index of the checkbox/ radio button in the whole list; $label
     * is the label for the checkbox/ radio button; and $name, $value and $checked represent the name,
     * value and the checked status of the checkbox/ radio button input.
     *
     * @return ActiveField object
     */
    protected function getToggleFieldList($type, $items, $options = [])
    {
        $inline = ArrayHelper::remove($options, 'inline', false);
        $inputType = "{$type}List";
        $this->initDisability($options['itemOptions']);
        $css = $this->form->disabled ? ' disabled' : '';
        $css = $this->form->readonly ? $css . ' readonly' : $css;
        if ($inline && !isset($options['itemOptions']['labelOptions']['class'])) {
            $options['itemOptions']['labelOptions']['class'] = "{$type}-inline{$css}";
        } elseif (!isset($options['item'])) {
            $options['item'] = function ($index, $label, $name, $checked, $value) use ($type, $css) {
                return "<div class='{$type}{$css}'>" . Html::$type($name, $checked, [
                    'label' => $label,
                    'value' => $value,
                    'disabled' => $this->form->disabled,
                    'readonly' => $this->form->readonly,
                ]) . "</div>";
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
     * @param array $items the data item used to generate the checkboxes.
     * The array values are the labels, while the array keys are the corresponding checkbox values.
     * Note that the labels will NOT be HTML-encoded, while the values will.
     * @param array $options options (name => config) for the checkbox list. The following options are specially
     *     handled:
     *
     * - unselect: string, the value that should be submitted when none of the checkboxes is selected.
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
     * @inherit doc
     */
    public function widget($class, $config = [])
    {
        if (property_exists($class, 'disabled') && property_exists($class, 'readonly')) {
            $this->initDisability($config);
        }
        return parent::widget($class, $config);
    }

    /**
     * @inherit doc
     */
    public function label($label = null, $options = [])
    {
        if ($label === false) {
            $this->showLabels = false;
        }
        return parent::label($label, $options);
    }

    /**
     * @inherit doc
     */
    public function render($content = null)
    {
        if ($this->form->staticOnly === true) {
            $field = $this->staticInput();
            $this->initTemplate();
            $this->buildTemplate();
            return parent::render(null);
        }
        $this->initTemplate();
        $this->initPlaceholder($this->inputOptions);
        $this->initAddon();
        $this->initDisability($this->inputOptions);
        $this->buildTemplate();
        return parent::render($content);
    }

    /**
     * Initializes template based on layout settings for label, input,
     * error and hint blocks and for various bootstrap 3 form layouts
     */
    protected function initTemplate()
    {
        /**
         * @var ActiveForm $form
         */
        $form = $this->form;
        $inputDivClass = '';
        $errorDivClass = '';
        $showLabels = isset($this->showLabels) ? $this->showLabels :
            ArrayHelper::getValue($form->formConfig, 'showLabels', true);
        $showErrors = isset($this->showErrors) ? $this->showErrors :
            ArrayHelper::getValue($form->formConfig, 'showErrors', true);
        $showHints = isset($this->showHints) ? $this->showHints :
            ArrayHelper::getValue($form->formConfig, 'showHints', true);
        if (!isset($this->parts['{hint}'])) {
            $showHints = false;
        }
        if ($form->hasInputCss()) {
            $offsetDivClass = $form->getOffsetCss();
            $inputDivClass = ($this->_offset) ? $offsetDivClass : $form->getInputCss();
            $error = $showErrors ? "{error}\n" : "";
            if ($showLabels === false) {
                $size = ArrayHelper::getValue($form->formConfig, 'deviceSize', ActiveForm::SIZE_MEDIUM);
                $errorDivClass = "col-{$size}-{$form->fullSpan}";
                $inputDivClass = $errorDivClass;
            } elseif ($form->hasOffsetCss()) {
                $errorDivClass = $offsetDivClass;
            }
        }
        if ($this->autoPlaceholder && $showLabels !== ActiveForm::SCREEN_READER) {
            $showLabels = false;
        }
        $input = '{input}';
        $label = '{label}';
        $error = '{error}';
        $hint = '{hint}';
        if (!empty($inputDivClass)) {
            $input = "<div class='{$inputDivClass}'>{input}</div>";
        }
        if (!empty($this->_multiselect)) {
            $input = str_replace('{input}', $this->_multiselect, $input);
        }
        if ($this->_isStatic && $this->showErrors !== true) {
            $showErrors = false;
        }
        if (!empty($errorDivClass) && $showErrors) {
            $error = "<div class='{$errorDivClass}'>{error}</div>";
        }
        if (!empty($errorDivClass) && $showHints) {
            $hint = "<div class='{$errorDivClass}'>{hint}</div>";
        }
        $this->template = strtr($this->template, [
            '{label}' => $showLabels ? $label : '',
            '{input}' => $input,
            '{error}' => $showErrors ? $error : '',
            '{hint}' => $showHints ? $hint : ''
        ]);
    }

    /**
     * Builds the template based on content before and after input
     *
     * @return void
     */
    protected function buildTemplate()
    {
        $this->template = strtr($this->template, [
            '{input}' => $this->contentBeforeInput . '{input}' . $this->contentAfterInput
        ]);
    }

    /**
     * Initializes the addon for text inputs
     */
    protected function initAddon()
    {
        if (!empty($this->addon)) {
            $addon = $this->addon;
            $prepend = static::getAddonContent(ArrayHelper::getValue($addon, 'prepend', ''));
            $append = static::getAddonContent(ArrayHelper::getValue($addon, 'append', ''));
            $addonText = $prepend . '{input}' . $append;
            $group = ArrayHelper::getValue($addon, 'groupOptions', []);
            Html::addCssClass($group, 'input-group');
            $contentBefore = ArrayHelper::getValue($addon, 'contentBefore', '');
            $contentAfter = ArrayHelper::getValue($addon, 'contentAfter', '');
            $addonText = Html::tag('div', $contentBefore . $addonText . $contentAfter, $group);
            $this->template = str_replace('{input}', $addonText, $this->template);
        }
    }

    /**
     * Parses and returns addon content
     *
     * @param string /array $addon the addon parameter
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
}
