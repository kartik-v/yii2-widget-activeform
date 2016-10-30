<?php

/**
 * @copyright  Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2016
 * @package    yii2-widgets
 * @subpackage yii2-widget-activeform
 * @version    1.4.9
 */

namespace kartik\form;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\widgets\ActiveField as YiiActiveField;

/**
 * ActiveField represents a form input field within an [[ActiveForm]] and extends the [[YiiActiveField]] component
 * to handle various bootstrap functionality like form types, input groups/addons, toggle buttons, feedback icons, and
 * other enhancements
 *
 * Usage example with addons:
 *
 * ```php
 * // $form is your active form instance
 * echo $form->field($model, 'email', ['addon' => ['type'=>'prepend', 'content'=>'@']]);
 * echo $form->field($model, 'amount_paid', ['addon' => ['type'=>'append', 'content'=>'.00']]);
 * echo $form->field($model, 'phone', [
 *     'addon' => [
 *         'type'=>'prepend',
 *         'content'=>'<i class="glyphicon glyphicon-phone"></i>'
 *     ]
 * ]);
 * ```
 *
 * Usage example with horizontal form and advanced field layout CSS configuration:
 *
 * ```php
 * echo $form->field($model, 'email', ['labelSpan' => 2, 'deviceSize' => ActiveForm::SIZE_SMALL]]);
 * echo $form->field($model, 'amount_paid', ['horizontalCssClasses' => ['wrapper' => 'hidden-xs']]);
 * echo $form->field($model, 'phone', [
 *     'horizontalCssClasses' => ['label' => 'col-md-2 col-sm-3 col-xs-12 myRedClass']
 * ]);
 * echo $form->field($model, 'special', [
 *     'template' => '{beginLabel}For: {labelTitle}{endLabel}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}'
 * ]);
 * ```
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since  1.0
 */
class ActiveField extends YiiActiveField
{
    /**
     * An empty string value
     */
    const NOT_SET = '';
    /**
     * HTML radio input type
     */
    const TYPE_RADIO = 'radio';
    /**
     * HTML checkbox input type
     */
    const TYPE_CHECKBOX = 'checkbox';
    /**
     * Inline style flag for rendering checkboxes (as per bootstrap styling)
     */
    const STYLE_INLINE = 'inline';
    /**
     * The default height for the Krajee multi select input
     */
    const MULTI_SELECT_HEIGHT = '145px';
    /**
     * Default hint type that is displayed below the input
     */
    const HINT_DEFAULT = 1;
    /**
     * Special hint type that allows display via an indicator icon or on hover/click of the field label
     */
    const HINT_SPECIAL = 2;

    /**
     * @var array the list of hint keys that will be used by ActiveFieldHint jQuery plugin
     */
    protected static $_pluginHintKeys = [
        'iconCssClass',
        'labelCssClass',
        'contentCssClass',
        'hideOnEscape',
        'hideOnClickOut',
        'title',
        'placement',
        'container',
        'animation',
        'delay',
        'template',
        'selector',
        'viewport',
    ];

    /**
     * @var boolean whether to override the form layout styles and skip field formatting as per the form layout.
     * Defaults to `false`.
     */
    public $skipFormLayout = false;

    /**
     * @var integer the hint display type. If set to `self::HINT_DEFAULT`, the hint will be displayed as a text block below
     * each input. If set to `self::HINT_SPECIAL`, then the `hintSettings` will be applied to display the field
     * hint.
     */
    public $hintType = self::HINT_DEFAULT;

    /**
     * @var array the settings for displaying the hint. These settings are parsed only if `hintType` is set to
     * `self::HINT_SPECIAL`. The following properties are supported:
     * - `showIcon`: _boolean_, whether to display the hint via a help icon indicator. Defaults to `true`.
     * - `icon`: _string_, the markup to display the help icon. Defaults to `<i class="glyphicon glyphicon-question-sign
     *   text-info"></i>`.
     * - `iconBesideInput`: _boolean_, whether to display the icon beside the input. Defaults to `false`. The following
     *   actions will be taken based on this setting:
     *   - if set to `false` the help icon is displayed beside the label and the `labelTemplate` setting is used to
     *     render the icon and label markups.
     *   - if set to `true` the help icon is displayed beside the input and the `inputTemplate` setting is used to
     *     render the icon and input markups.
     * - `labelTemplate`: _string_, the template to render the help icon and the field label. Defaults to `{label}{help}`,
     *   where
     *   - `{label}` will be replaced by the ActiveField label content
     *   - `{help}` will be replaced by the help icon indicator markup
     * - `inputTemplate`: _string_, the template to render the help icon and the field input. Defaults to `'<div
     *   style="width:90%; float:left">{input}</div><div style="padding-top:7px">{help}</div>',`, where
     *   - `{input}` will be replaced by the ActiveField input content
     *   - `{help}` will be replaced by the help icon indicator markup
     * - `onLabelClick`: _boolean_, whether to display the hint on clicking the label. Defaults to `false`.
     * - `onLabelHover`: _boolean_, whether to display the hint on hover of the label. Defaults to `true`.
     * - `onIconClick`: _boolean_, whether to display the hint on clicking the icon. Defaults to `true`.
     * - `onIconHover`: _boolean_, whether to display the hint on hover of the icon. Defaults to `false`.
     * - `iconCssClass`: _string_, the CSS class appended to the `span` container enclosing the icon.
     * - `labelCssClass`: _string_, the CSS class appended to the `span` container enclosing label text within label tag.
     * - `contentCssClass`: _string_, the CSS class appended to the `span` container displaying help content within
     *   popover.
     * - `hideOnEscape`: _boolean_, whether to hide the popover on clicking escape button on the keyboard. Defaults to `true`.
     * - `hideOnClickOut`: _boolean_, whether to hide the popover on clicking outside the popover. Defaults to `true`.
     * - `title`: _string_, the title heading for the popover dialog. Defaults to empty string, whereby the heading is not
     *   displayed.
     * - `placement`: _string_, the placement of the help popover on hover or click of the icon or label. Defaults to
     *   `top`.
     * - `container`: _string_, the specific element to which the popover will be appended to. Defaults to `table` when
     *   `iconBesideInput` is `true`, else defaults to `form`
     * - `animation`: _boolean_, whether to add a CSS fade transition effect when opening and closing the popover. Defaults to
     *   `true`.
     * - `delay``: _integer_|_array_, the number of milliseconds it will take to open and close the popover. Defaults to `0`.
     * - `selector`: _integer_, the specified selector to add the popover to. Defaults to boolean `false`.
     * - `viewport`: _string_|_array_, the element within which the popover will be bounded to. Defaults to
     *   `['selector' => 'body', 'padding' => 0]`.
     */
    public $hintSettings = [];

    /**
     * @var array the feedback icon configuration (applicable for [bootstrap text inputs](http://getbootstrap.com/css/#with-optional-icons)).
     * This must be setup as an array containing the following keys:
     *
     * - `type`: _string_, the icon type to use. Should be one of `raw` or `icon`. Defaults to `icon`, where the `default`,
     *   `error` and `success` settings will be treated as an icon CSS suffix name. If set to `raw`, they will be
     *   treated as a raw content markup.
     * - `prefix`: _string_, the icon CSS class prefix to use if `type` is `icon`. Defaults to `glyphicon glyphicon-`.
     * - `default`: _string_, the icon (CSS class suffix name or raw markup) to show by default. If not set will not be
     *   shown.
     * - `error`: _string_, the icon (CSS class suffix name or raw markup) to use when input has an error validation. If
     *   not set will not be shown.
     * - `success`: _string_, the icon (CSS class suffix name or raw markup) to use when input has a success validation. If
     *   not set will not be shown.
     * - `defaultOptions`: _array_, the HTML attributes to apply for default icon. The special attribute `description` can
     *   be set to describe this feedback as an `aria` attribute for accessibility. Defaults to `(default)`.
     * - `errorOptions`: _array_, the HTML attributes to apply for error icon. The special attribute `description` can be
     *   set to describe this feedback as an `aria` attribute for accessibility. Defaults to `(error)`.
     * - `successOptions`: _array_, the HTML attributes to apply for success icon. The special attribute `description` can
     *   be set to describe this feedback as an `aria` attribute for accessibility. Defaults to `(success)`.
     *
     * @see http://getbootstrap.com/css/#with-optional-icons
     */
    public $feedbackIcon = [];

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
     * - `prepend`: _array_, the prepend addon configuration
     * - `content`: _string_, the prepend addon content
     * - `asButton`: _boolean_, whether the addon is a button or button group. Defaults to false.
     * - `options`: _array_, the HTML attributes to be added to the container.
     * - `append`: _array_, the append addon configuration
     * - `content`: _string_|_array_, the append addon content
     * - `asButton`: _boolean_, whether the addon is a button or button group. Defaults to false.
     * - `options`: _array_, the HTML attributes to be added to the container.
     * - `groupOptions`: _array_, HTML options for the input group
     * - `contentBefore`: _string_, content placed before addon
     * - `contentAfter`: _string_, content placed after addon
     */
    public $addon = [];

    /**
     * @var string CSS classname to add to the input
     */
    public $addClass = 'form-control';

    /**
     * @var string the static value for the field to be displayed for the static input OR when the form is in
     * staticOnly mode. This value is not HTML encoded.
     */
    public $staticValue;

    /**
     * @var boolean|string whether to show labels for the field. Should be one of the following values:
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
     * @var string inherits and overrides values from parent class. The value can be overridden within
     * [[ActiveForm::field()]] method. The following tokens are supported:
     * - `{beginLabel}`: Container begin tag for labels (to be used typically along with `{labelTitle}` token
     *   when you do not wish to directly use the `{label}` token)
     * - `{labelTitle}`: Label content without tags (to be used typically when you do not wish to directly use 
     *   the `{label` token)
     * - `{endLabel}`: Container end tag for labels (to be used typically along with `{labelTitle}` token
     *   when you do not wish to directly use the `{label}` token)
     * - `{label}`: Full label tag with begin tag, content and end tag
     * - `{beginWrapper}`: Container for input,error and hint start tag. Uses a `<div>` tag if there is a input wrapper
     *    CSS detected, else defaults to empty string.
     * - `{input}`: placeholder for input control whatever it is
     * - `{hint}`: placeholder for hint/help text including sub container
     * - `{error}`: placeholder for error text including sub container
     * - `{endWrapper}`: end tag for `{beginWrapper}`. Defaults to `</div>` if there is a input wrapper CSS detected,
     *    else defaults to empty string.
     */
    public $template = "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}";

    /**
     *
     * @var integer the bootstrap grid column width (usually between 1 to 12)
     */
    public $labelSpan;

    /**
     *
     * @var string one of the bootstrap sizes (refer the ActiveForm::SIZE constants)
     */
    public $deviceSize;

    /**
     * @var boolean whether to render the error. Default is `true` except for layout `inline`.
     */
    public $enableError;

    /**
     * @var boolean whether to render the label. Default is `true`.
     */
    public $enableLabel;

    /**
     * @var null|array CSS grid classes for horizontal layout. This must be an array with these keys:
     * - `offset`: the offset grid class to append to the wrapper if no label is rendered
     * - `label`: the label grid class
     * - `wrapper`: the wrapper grid class
     * - `error`: the error grid class
     * - `hint`: the hint grid class
     * These options are compatible and similar to [[\yii\bootstrap\ActiveForm]] and provide a complete flexible
     * container. If `labelSpan` is set in [[ActiveForm::formConfig]] and `wrapper` is also set, then both css options
     * are concatenated. If `wrapper` contains a 'col-' class wrapper, it overrides the tag from `labelSpan`.
     */
    public $horizontalCssClasses;

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
        'showErrors' => true,
        'labelSpan' => ActiveForm::DEFAULT_LABEL_SPAN,
        'deviceSize' => ActiveForm::SIZE_MEDIUM,
    ];

    /**
     * @var boolean whether there is a feedback icon configuration set
     */
    protected $_hasFeedback = false;

    /**
     * @var boolean whether there is a feedback icon configuration set
     */
    protected $_isHintSpecial = false;

    /**
     * @var string the label additional css class for horizontal forms and special inputs like checkbox and radio.
     */
    private $_labelCss;

    /**
     * @var string the input container additional css class for horizontal forms and special inputs like checkbox and
     * radio.
     */
    private $_inputCss;

    /**
     * @var string the offset class for error and hint for horizontal forms or for special inputs like checkbox and
     * radio.
     */
    private $_offsetCss;

    /**
     * @var boolean whether the hint icon is beside the input.
     */
    private $_iconBesideInput = false;

    /**
     * @var string the identifier for the hint popover container.
     */
    private $_hintPopoverContainer;

    /**
     * Parses and returns addon content
     *
     * @param string|array $addon the addon parameter or the array of addon parameters
     *
     * @return string
     */
    public static function getAddonContent($addon)
    {
        if (!is_array($addon)) {
            return $addon;
        }
        if (!ArrayHelper::isIndexed($addon)) {
            $addon = [$addon]; //pack existing array into indexed array
        }
        $html = "";
        foreach ($addon as $addonItem) {
            $content = ArrayHelper::getValue($addonItem, 'content', '');
            $options = ArrayHelper::getValue($addonItem, 'options', []);
            $suffix = ArrayHelper::getValue($addonItem, 'asButton', false) ? 'btn' : 'addon';
            Html::addCssClass($options, 'input-group-' . $suffix);
            $html .= Html::tag('span', $content, $options);
        }
        return $html;
    }

    /**
     * @inheritdoc
     */
    public function begin()
    {
        if ($this->_hasFeedback) {
            Html::addCssClass($this->options, 'has-feedback');
        }
        return parent::begin();
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initActiveField();
    }

    /**
     * Renders a checkbox. This method will generate the "checked" tag attribute according to the model attribute value.
     *
     * @param array $options the tag options in terms of name-value pairs. The following options are specially
     * handled:
     *
     * - `uncheck`: _string_, the value associated with the uncheck state of the checkbox. If not set, it will take
     *   the default value `0`. This method will render a hidden input so that if the checkbox is not checked and is
     *   submitted, the value of this attribute will still be submitted to the server via the hidden input.
     * - `label`: _string_, a label displayed next to the checkbox. It will NOT be HTML-encoded. Therefore you can
     *   pass in HTML code such as an image tag. If this is is coming from end users, you should [[Html::encode()]]
     *   it to prevent XSS attacks. When this option is specified, the checkbox will be enclosed by a label tag.
     * - `labelOptions`: _array_, the HTML attributes for the label tag. This is only used when the "label" option is
     *   specified.
     * - `container: boolean|array, the HTML attributes for the checkbox container. If this is set to false, no
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
     * Renders a list of checkboxes. A checkbox list allows multiple selection, like [[listBox()]]. As a result, the
     * corresponding submitted value is an array. The selection of the checkbox list is taken from the value of the
     * model attribute.
     *
     * @param array $items the data item used to generate the checkboxes. The array values are the labels, while the
     * array keys are the corresponding checkbox values. Note that the labels will NOT be HTML-encoded, while the
     * values will be encoded.
     * @param array $options options (name => config) for the checkbox list. The following options are specially
     * handled:
     * - `unselect`: _string_, the value that should be submitted when none of the checkboxes is selected. By setting this
     *   option, a hidden input will be generated.
     * - `separator`: _string_, the HTML code that separates items.
     * - `inline`: _boolean_, whether the list should be displayed as a series on the same line, default is false
     * - `item: callable, a callback that can be used to customize the generation of the HTML code corresponding to a
     *   single item in $items. The signature of this callback must be:
     * ~~~
     * function ($index, $label, $name, $checked, $value)
     * ~~~
     *
     * where `$index` is the zero-based index of the checkbox in the whole list; `$label` is the label for the checkbox;
     * and `$name`, `$value` and `$checked` represent the name, value and the checked status of the checkbox input.
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
    public function dropDownList($items, $options = [])
    {
        $this->initDisability($options);
        Html::addCssClass($options, $this->addClass);
        return parent::dropDownList($items, $options);
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
        if ($this->_isHintSpecial) {
            Html::addCssClass($options, 'kv-hint-block');
        }
        return parent::hint($this->generateHint($content), $options);
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
     * @inheritdoc
     */
    public function label($label = null, $options = [])
    {
        $hasLabels = $this->hasLabels();
        $processLabels = $label !== false && $this->_isHintSpecial && $hasLabels !== false &&
            $hasLabels !== ActiveForm::SCREEN_READER && ($this->getHintData('onLabelClick') || $this->getHintData(
                    'onLabelHover'
                ));
        if ($processLabels) {
            if ($label === null) {
                $label = $this->model->getAttributeLabel($this->attribute);
            }
            $opts = ['class' => 'kv-type-label'];
            Html::addCssClass($opts, $this->getHintIconCss('Label'));
            $label = Html::tag('span', $label, $opts);
            if ($this->getHintData('showIcon') && !$this->getHintData('iconBesideInput')) {
                $label = strtr(
                    $this->getHintData('labelTemplate'),
                    ['{label}' => $label, '{help}' => $this->getHintIcon()]
                );
            }
        }
        if (strpos($this->template, '{beginLabel}') !== false) {
            $this->renderLabelParts($label, $options);
        }

        return parent::label($label, $options);
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
     * Renders a radio button. This method will generate the "checked" tag attribute according to the model attribute
     * value.
     *
     * @param array $options the tag options in terms of name-value pairs. The following options are specially
     * handled:
     * - `uncheck`: _string_, the value associated with the uncheck state of the radio button. If not set, it will take the
     *   default value '0'. This method will render a hidden input so that if the radio button is not checked and is
     *   submitted, the value of this attribute will still be submitted to the server via the hidden input.
     * - `label`: _string_, a label displayed next to the radio button. It will NOT be HTML-encoded. Therefore you can pass
     *   in HTML code such as an image tag. If this is is coming from end users, you should [[Html::encode()]] it to
     *   prevent XSS attacks. When this option is specified, the radio button will be enclosed by a label tag.
     * - `labelOptions`: _array_, the HTML attributes for the label tag. This is only used when the "label" option is
     *   specified.
     * - `container: boolean|array, the HTML attributes for the checkbox container. If this is set to false, no
     *   container will be rendered. The special option `tag` will be recognized which defaults to `div`. This
     *   defaults to: `['tag' => 'div', 'class'=>'radio']`
     * The rest of the options will be rendered as the attributes of the resulting tag. The values will be HTML-encoded
     *   using [[Html::encode()]]. If a value is null, the corresponding attribute will not be rendered.
     *
     * @param boolean $enclosedByLabel whether to enclose the radio within the label. If `true`, the method will still
     * use [[template]] to layout the checkbox and the error message except that the radio is enclosed by the label tag.
     *
     * @return ActiveField object
     */
    public function radio($options = [], $enclosedByLabel = true)
    {
        return $this->getToggleField(self::TYPE_RADIO, $options, $enclosedByLabel);
    }

    /**
     * Renders a list of radio buttons. A radio button list is like a checkbox list, except that it only allows single
     * selection. The selection of the radio buttons is taken from the value of the model attribute.
     *
     * @param array $items the data item used to generate the radio buttons. The array keys are the labels, while the
     * array values are the corresponding radio button values. Note that the labels will NOT be HTML-encoded, while
     * the values will.
     * @param array $options options (name => config) for the radio button list. The following options are specially
     * handled:
     *
     * - `unselect`: _string_, the value that should be submitted when none of the radio buttons is selected. By setting
     *   this option, a hidden input will be generated.
     * - `separator`: _string_, the HTML code that separates items.
     * - `inline`: _boolean_, whether the list should be displayed as a series on the same line, default is false
     * - `item: callable, a callback that can be used to customize the generation of the HTML code corresponding to a
     *   single item in $items. The signature of this callback must be:
     *
     * ~~~
     * function ($index, $label, $name, $checked, $value)
     * ~~~
     *
     * where `$index` is the zero-based index of the radio button in the whole list; `$label` is the label for the radio
     * button; and `$name`, `$value` and `$checked` represent the name, value and the checked status of the radio button
     * input.
     *
     * @return ActiveField object
     */
    public function radioList($items, $options = [])
    {
        return $this->getToggleFieldList(self::TYPE_RADIO, $items, $options);
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
            $this->buildTemplate();
            $this->staticInput();
        } else {
            $this->initPlaceholder($this->inputOptions);
            $this->initDisability($this->inputOptions);
            $this->buildTemplate();
        }
        return parent::render($content);
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
    public function widget($class, $config = [])
    {
        if (property_exists($class, 'disabled') && property_exists($class, 'readonly')) {
            $this->initDisability($config);
        }
        return parent::widget($class, $config);
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
     * Renders a multi select list box. This control extends the checkboxList and radioList available in
     * [[YiiActiveField]] - to display a scrolling multi select list box.
     *
     * @param array $items the data item used to generate the checkboxes or radio.
     * @param array $options the options for checkboxList or radioList. Additional parameters
     * - `height`: _string_, the height of the multiselect control - defaults to 145px
     * - `selector`: _string_, whether checkbox or radio - defaults to checkbox
     * - `container`: _array_, options for the multiselect container
     * - `unselect`: _string_, the value that should be submitted when none of the radio buttons is selected. By setting
     *   this option, a hidden input will be generated.
     * - `separator`: _string_, the HTML code that separates items.
     * - `item: callable, a callback that can be used to customize the generation of the HTML code corresponding to a
     *   single item in $items. The signature of this callback must be:
     * - `inline`: _boolean_, whether the list should be displayed as a series on the same line, default is false
     * - `selector`: _string_, whether the selection input is [[TYPE_RADIO]] or [[TYPE_CHECKBOX]]
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
     * @param array $items the data item used to generate the radios. The array values are the labels, while the array
     * keys are the corresponding radio values. Note that the labels will NOT be HTML-encoded, while the values
     * will be encoded.
     * @param array $options options (name => config) for the radio button list. The following options are specially
     * handled:
     *
     * - `unselect`: _string_, the value that should be submitted when none of the radios is selected. By setting this
     *   option, a hidden input will be generated. If you do not want any hidden input, you should explicitly set
     *   this option as null.
     * - `separator`: _string_, the HTML code that separates items.
     * - `item: callable, a callback that can be used to customize the generation of the HTML code corresponding to a
     *   single item in $items. The signature of this callback must be:
     *
     * ~~~
     * function ($index, $label, $name, $checked, $value)
     * ~~~
     *
     * where $index is the zero-based index of the radio button in the whole list; $label is the label for the radio
     * button; and $name, $value and $checked represent the name, value and the checked status of the radio button
     * input.
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
     * @param array $items the data item used to generate the checkboxes. The array values are the labels, while the
     * array keys are the corresponding checkbox values. Note that the labels will NOT be HTML-encoded, while the
     * values will.
     * @param array $options options (name => config) for the checkbox button list. The following options are specially
     * handled:
     *
     * - `unselect`: _string_, the value that should be submitted when none of the checkboxes is selected. By setting this
     *   option, a hidden input will be generated. If you do not want any hidden input, you should explicitly set
     *   this option as null.
     * - `separator`: _string_, the HTML code that separates items.
     * - `item: callable, a callback that can be used to customize the generation of the HTML code corresponding to a
     *   single item in $items. The signature of this callback must be:
     *
     * ~~~
     * function ($index, $label, $name, $checked, $value)
     * ~~~
     *
     * where $index is the zero-based index of the checkbox button in the whole list; $label is the label for the
     * checkbox button; and $name, $value and $checked represent the name, value and the checked status of the
     * checkbox button input.
     *
     * @return ActiveField object
     */
    public function checkboxButtonGroup($items, $options = [])
    {
        return $this->getToggleFieldList(self::TYPE_CHECKBOX, $items, $options, true);
    }

    /**
     * Generates the hint icon
     *
     * @return string
     */
    protected function getHintIcon()
    {
        if (!$this->getHintData('showIcon')) {
            return '';
        }
        $options = [];
        Html::addCssClass($options, $this->getHintIconCss('Icon'));
        return Html::tag('span', $this->getHintData('icon'), $options);
    }

    /**
     * Generates a toggle field (checkbox or radio)
     *
     * @param string $type the toggle input type 'checkbox' or 'radio'.
     * @param array $options options (name => config) for the toggle input list container tag.
     * @param boolean $enclosedByLabel whether the input is enclosed by the label tag
     *
     * @return ActiveField object
     */
    protected function getToggleField($type = self::TYPE_CHECKBOX, $options = [], $enclosedByLabel = true)
    {
        $this->initDisability($options);
        $inputType = 'active' . ucfirst($type);
        $disabled = ArrayHelper::getValue($options, 'disabled', false);
        $css = $disabled ? $type . ' disabled' : $type;
        $container = ArrayHelper::remove($options, 'container', ['class' => $css]);
        if ($enclosedByLabel) {
            $this->_offset = true;
            $this->parts['{label}'] = '';
            $showLabels = $this->hasLabels();
            if ($showLabels === false) {
                $options['label'] = '';
                $this->showLabels = true;
            }
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
     * Gets configuration parameter from formConfig
     *
     * @param string $param the parameter name
     * @param mixed $default the default parameter value
     *
     * @return bool the parsed parameter value
     */
    protected function getConfigParam($param, $default = true)
    {
        return isset($this->$param) ? $this->$param : ArrayHelper::getValue($this->form->formConfig, $param, $default);
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
     * Initialize the active field
     */
    protected function initActiveField()
    {
        if (isset($this->enableError)) {
            $this->showErrors = $this->enableError;
        }
        if (isset($this->enableLabel)) {
            $this->showLabels = $this->enableLabel;
        }
        $showLabels = $this->getConfigParam('showLabels');
        $this->_isHintSpecial = $this->hintType === self::HINT_SPECIAL;
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
        if ($this->form->type === ActiveForm::TYPE_HORIZONTAL) {
            $this->initHorizontal();
        }
        $this->initLabels();
        $this->initHints();
        $this->_hasFeedback = !empty($this->feedbackIcon) && is_array($this->feedbackIcon);
        $this->_iconBesideInput = ArrayHelper::getValue($this->hintSettings, 'iconBesideInput') ? true : false;
        if ($this->_iconBesideInput) {
            $id = ArrayHelper::getValue($this->options, 'id', '');
            $this->_hintPopoverContainer = $id ? "#{$id}-table" : '';
        } else {
            $id = ArrayHelper::getValue($this->form->options, 'id', '');
            $this->_hintPopoverContainer = $id ? "#{$id}" : '';
        }
    }

    /**
     * Initialize label options
     */
    protected function initLabels()
    {
        $labelCss = $this->_labelCss;
        if ($this->hasLabels() === ActiveForm::SCREEN_READER) {
            Html::addCssClass($this->labelOptions, ActiveForm::SCREEN_READER);
        } elseif ($labelCss != self::NOT_SET) {
            Html::addCssClass($this->labelOptions, $labelCss);
        }
    }

    /**
     * Validate label display status
     *
     * @return boolean|string whether labels are to be shown
     */
    protected function hasLabels()
    {
        $showLabels = $this->getConfigParam('showLabels'); // plus abfrage $this-showLabels kombinieren.
        if ($this->autoPlaceholder && $showLabels !== ActiveForm::SCREEN_READER) {
            $showLabels = false;
        }
        return $showLabels;
    }

    /**
     * Prepares bootstrap grid col classes for horizontal layout including label and input tags and initiate private
     * CSS variables. The process order for 'labelSpan' and 'wrapper' is as follows:
     *
     * - Step 1: Check `$labelSpan` and `$deviceSize`.
     * - Step 2: Check `formConfig(['labelSpan' => x, 'deviceSize' => xy]) and build css tag.
     * - If `horizontalCssClasses['wrapper']` is set and no 'col-' tag then add this to css tag from Step 1.
     * - If `horizontalCssClasses['wrapper']` is set and wrapper has 'col-' tag then override css tag completely.
     * - If no `$labelSpan` and no `horizontalCssClasses['wrapper']` is set then use default from [[$_settings]].
     *   Similar behavior to `horizontalCssClasses['label']`.
     */
    protected function initHorizontal()
    {
        $hor = $this->horizontalCssClasses;
        $span = $this->getConfigParam('labelSpan', '');
        $size = $this->getConfigParam('deviceSize', '');

        // check horizontalCssClasses['wrapper'] if there is a col- class
        if (isset($hor['wrapper']) && strpos($hor['wrapper'], 'col-') !== false) {
            $span = '';
        }
        if (empty($span) && !isset($hor['wrapper'])) {
            $span = $this->_settings['labelSpan'];
        }
        if (empty($size)) {
            $size = ArrayHelper::getValue($this->_settings, 'deviceSize');
        }
        $this->deviceSize = $size;

        if ($span != self::NOT_SET && intval($span) > 0) {
            $span = intval($span);

            // validate if invalid labelSpan is passed - else set to DEFAULT_LABEL_SPAN
            if ($span <= 0 || $span >= $this->form->fullSpan) {
                $span = $this->form->fullSpan;
            }

            // validate if invalid deviceSize is passed - else default to SIZE_MEDIUM
            $sizes = [ActiveForm::SIZE_TINY, ActiveForm::SIZE_SMALL, ActiveForm::SIZE_MEDIUM, ActiveForm::SIZE_LARGE];
            if ($size == self::NOT_SET || !in_array($size, $sizes)) {
                $size = ActiveForm::SIZE_MEDIUM;
            }

            $this->labelSpan = $span;

            $prefix = "col-{$size}-";
            $this->_labelCss = $prefix . $span;
            $this->_inputCss = $prefix . ($this->form->fullSpan - $span);
            $this->_offsetCss = $prefix . "offset-" . $span;
        }

        if (isset($hor['wrapper'])) {
            if ($span !== self::NOT_SET) {
                $this->_inputCss .= " ";
            }
            $this->_inputCss .= $hor['wrapper'];
        }

        if (isset($hor['offset'])) {
            $this->_offsetCss = $hor['offset'];
        }

        if (isset($hor['label'])) {
            if ($span !== self::NOT_SET) {
                $this->_labelCss .= " ";
            }
            $this->_labelCss .= $hor['label'];
        }

        if (isset($hor['error'])) {
            Html::addCssClass($this->errorOptions, $hor['error']);
        }
    }

    /**
     * Initialize layout settings for label, input, error and hint blocks and for various bootstrap 3 form layouts
     */
    protected function initLayout()
    {
        $showLabels = $this->hasLabels();
        $showErrors = $this->getConfigParam('showErrors');
        $this->mergeSettings($showLabels, $showErrors);
        $this->buildLayoutParts($showLabels, $showErrors);
    }

    /**
     * Merges the parameters for layout settings
     *
     * @param boolean $showLabels whether to show labels
     * @param boolean $showErrors whether to show errors
     */
    protected function mergeSettings($showLabels, $showErrors)
    {
        $this->_settings['showLabels'] = $showLabels;
        $this->_settings['showErrors'] = $showErrors;
    }

    /**
     * Builds the field layout parts
     *
     * @param boolean $showLabels whether to show labels
     * @param boolean $showErrors whether to show errors
     */
    protected function buildLayoutParts($showLabels, $showErrors)
    {
        if (!$showErrors) {
            $this->_settings['error'] = '';
        }
        $this->parts['{beginWrapper}'] = '';
        $this->parts['{endWrapper}'] = '';
        if ($this->skipFormLayout) {
            $this->mergeSettings($showLabels, $showErrors);
            $this->parts['{beginLabel}'] = '';
            $this->parts['{labelTitle}'] = '';
            $this->parts['{endLabel}'] = '';
            return;
        }
        if (!empty($this->_inputCss)) {
            $offsetDivClass = $this->_offsetCss . " " . $this->_inputCss;
            $inputDivClass = ($this->_offset) ? $offsetDivClass : $this->_inputCss;
            if ($showLabels === false || $showLabels === ActiveForm::SCREEN_READER) {
                $inputDivClass = "col-{$this->deviceSize}-{$this->form->fullSpan}";
            }
            $this->parts['{beginWrapper}'] = Html::beginTag('div', ['class' => $inputDivClass]);
            $this->parts['{endWrapper}'] = Html::endTag('div');
        }
        $this->mergeSettings($showLabels, $showErrors);
    }

    /**
     * Sets the layout element container
     *
     * @param string $type the layout element type
     * @param string $css the css class for the container
     * @param boolean $chk whether to create the container for the layout element
     */
    protected function setLayoutContainer($type, $css = '', $chk = true)
    {
        if (!empty($css) && $chk) {
            $this->_settings[$type] = "<div class='{$css}'>{{$type}}</div>";
        }
    }

    /**
     * Initialize hint settings
     */
    protected function initHints()
    {
        if ($this->hintType !== self::HINT_SPECIAL) {
            return;
        }
        $container = $this->_hintPopoverContainer;
        if ($container === '') {
            $container = $this->_iconBesideInput ? 'table' : 'form';
        }
        $defaultSettings = [
            'showIcon' => true,
            'iconBesideInput' => false,
            'labelTemplate' => '{label}{help}',
            'inputTemplate' => '<table style="width:100%"' . '{id}' . '><tr><td>{input}</td>' .
                '<td style="width:5%">{help}</td></tr></table>',
            'onLabelClick' => false,
            'onLabelHover' => true,
            'onIconClick' => true,
            'onIconHover' => false,
            'labelCssClass' => 'kv-hint-label',
            'iconCssClass' => 'kv-hint-icon',
            'contentCssClass' => 'kv-hint-content',
            'icon' => '<i class="glyphicon glyphicon-question-sign text-info"></i>',
            'hideOnEscape' => true,
            'hideOnClickOut' => true,
            'placement' => 'top',
            'container' => $container,
            'viewport' => ['selector' => 'body', 'padding' => 0],
        ];
        $this->hintSettings = array_replace_recursive($defaultSettings, $this->hintSettings);
        Html::addCssClass($this->options, 'kv-hint-special');
        foreach (static::$_pluginHintKeys as $key) {
            $this->setHintData($key);
        }
    }

    /**
     * Sets a hint property setting as a data attribute within `self::$options`
     *
     * @param string $key the hint property key
     */
    protected function setHintData($key)
    {
        if (isset($this->hintSettings[$key])) {
            $value = $this->hintSettings[$key];
            $this->options['data-' . Inflector::camel2id($key)] = is_bool($value) ? (int)$value : $value;
        }
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
     * Gets a hint configuration setting value
     *
     * @param string $key the hint setting key to fetch
     * @param mixed $default the default value if not set
     *
     * @return mixed
     */
    protected function getHintData($key, $default = null)
    {
        return ArrayHelper::getValue($this->hintSettings, $key, $default);
    }

    /**
     * Gets the hint icon css based on `hintSettings`
     *
     * @param string $type whether `Label` or `Icon`
     *
     * @return array the css to be applied
     */
    protected function getHintIconCss($type)
    {
        $css = ["kv-hintable"];
        if ($type === 'Icon') {
            $css[] = 'hide';
        }
        if (!empty($this->hintSettings["on{$type}Click"])) {
            $css[] = "kv-hint-click";
        }
        if (!empty($this->hintSettings["on{$type}Hover"])) {
            $css[] = "kv-hint-hover";
        }
        return $css;
    }

    /**
     * Builds the final template based on the bootstrap form type, display settings for label, error, and hint, and
     * content before and after label, input, error, and hint.
     */
    protected function buildTemplate()
    {
        $showLabels = $showErrors = $input = $error = null;
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
        if ($this->_isHintSpecial && $this->getHintData('iconBesideInput') && $this->getHintData('showIcon')) {
            $id = $this->_hintPopoverContainer ? ' id="' . $this->_hintPopoverContainer . '"' : '';
            $help = strtr($this->getHintData('inputTemplate'), ['{help}' => $this->getHintIcon(), '{id}' => $id,]);
            $input = str_replace('{input}', $help, $input);
        }
        $newInput = $this->contentBeforeInput . $this->generateAddon() . $this->renderFeedbackIcon() .
            $this->contentAfterInput;
        $newError = "{$this->contentBeforeError}{error}{$this->contentAfterError}";
        $config = [
            '{beginLabel}' => $showLabels ? '{beginLabel}' : "",
            '{endLabel}' => $showLabels ? '{endLabel}' : "",
            '{label}' => $showLabels ? "{$this->contentBeforeLabel}{label}{$this->contentAfterLabel}" : "",
            '{labelTitle}' => $showLabels ? "{$this->contentBeforeLabel}{labelTitle}{$this->contentAfterLabel}" : "",
            '{input}' => str_replace('{input}', $newInput, $input),
            '{error}' => $showErrors ? str_replace('{error}', $newError, $error) : '',
        ];
        $this->template = strtr($this->template, $config);
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
     * Render the bootstrap feedback icon
     *
     * @see http://getbootstrap.com/css/#with-optional-icons
     *
     * @return string
     */
    protected function renderFeedbackIcon()
    {
        if (!$this->_hasFeedback) {
            return '';
        }
        $config = $this->feedbackIcon;
        $type = ArrayHelper::getValue($config, 'type', 'icon');
        $prefix = ArrayHelper::getValue($config, 'prefix', 'glyphicon glyphicon-');
        $id = Html::getInputId($this->model, $this->attribute);
        return $this->getFeedbackIcon($config, 'default', $type, $prefix, $id) .
        $this->getFeedbackIcon($config, 'success', $type, $prefix, $id) .
        $this->getFeedbackIcon($config, 'error', $type, $prefix, $id);
    }

    /**
     * Render the label parts
     *
     * @param string|null $label the label or null to use model label
     * @param array $options the tag options
     */
    protected function renderLabelParts($label = null, $options = [])
    {
        $options = array_merge($this->labelOptions, $options);
        if ($label === null) {
            if (isset($options['label'])) {
                $label = $options['label'];
                unset($options['label']);
            } else {
                $attribute = Html::getAttributeName($this->attribute);
                $label = Html::encode($this->model->getAttributeLabel($attribute));
            }
        }
        if (!isset($options['for'])) {
            $options['for'] = Html::getInputId($this->model, $this->attribute);
        }
        $this->parts['{beginLabel}'] = Html::beginTag('label', $options);
        $this->parts['{endLabel}'] = Html::endTag('label');
        if (!isset($this->parts['{labelTitle}'])) {
            $this->parts['{labelTitle}'] = $label;
        }
    }

    /**
     * Generates a feedback icon
     *
     * @param array $config the feedback icon configuration
     * @param string $cat the feedback icon category
     * @param string $type the feedback icon type
     * @param string $prefix the feedback icon prefix
     * @param string $id the input attribute identifier
     *
     * @return string
     */
    protected function getFeedbackIcon($config, $cat, $type, $prefix, $id)
    {
        $markup = ArrayHelper::getValue($config, $cat, null);
        if ($markup === null) {
            return '';
        }
        $desc = ArrayHelper::remove($options, 'description', "({$cat})");
        $options = ArrayHelper::getValue($config, $cat . 'Options', []);
        $options['aria-hidden'] = true;
        $key = $id . '-' . $cat;
        $this->inputOptions['aria-describedby'] = empty($this->inputOptions['aria-describedby']) ? $key :
            $this->inputOptions['aria-describedby'] . ' ' . $key;
        Html::addCssClass($options, 'form-control-feedback');
        Html::addCssClass($options, 'kv-feedback-' . $cat);
        $icon = $type === 'raw' ? $markup : Html::tag('i', '', ['class' => $prefix . $markup]);
        return Html::tag('span', $icon, $options) . Html::tag('span', $desc, ['id' => $key, 'class' => 'sr-only']);
    }

    /**
     * Renders a list of checkboxes / radio buttons. The selection of the checkbox / radio buttons is taken from the
     * value of the model attribute.
     *
     * @param string $type the toggle input type 'checkbox' or 'radio'.
     * @param array $items the data item used to generate the checkbox / radio buttons. The array keys are the labels,
     * while the array values are the corresponding checkbox / radio button values. Note that the labels will NOT
     * be HTML-encoded, while the values will be encoded.
     * @param array $options options (name => config) for the checkbox / radio button list. The following options are
     * specially handled:
     *
     * - `unselect`: _string_, the value that should be submitted when none of the checkbox / radio buttons is selected. By
     *   setting this option, a hidden input will be generated.
     * - `separator`: _string_, the HTML code that separates items.
     * - `inline`: _boolean_, whether the list should be displayed as a series on the same line, default is false
     * - `disabledItems`: _array_, the list of values that will be disabled.
     * - `readonlyItems`: _array_, the list of values that will be readonly.
     * - `item: callable, a callback that can be used to customize the generation of the HTML code corresponding to a
     *   single item in $items. The signature of this callback must be:
     *
     * ~~~
     * function ($index, $label, $name, $checked, $value)
     * ~~~
     *
     * where $index is the zero-based index of the checkbox/ radio button in the whole list; $label is the label for
     * the checkbox/ radio button; and $name, $value and $checked represent the name, value and the checked status
     * of the checkbox/ radio button input.
     *
     * @param boolean $asButtonGroup whether to generate the toggle list as a bootstrap button group
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
        $opts = ArrayHelper::getValue($options, 'itemOptions', []);
        $this->initDisability($opts);
        $css = $this->form->disabled ? ' disabled' : '';
        $css .= $this->form->readonly ? ' readonly' : '';
        if ($inline && !isset($options['itemOptions']['labelOptions']['class'])) {
            $options['itemOptions']['labelOptions']['class'] = "{$type}-inline{$css}";
        } elseif (!isset($options['item'])) {
            $labelOptions = ArrayHelper::getValue($opts, 'labelOptions', []);
            $options['item'] = function ($index, $label, $name, $checked, $value)
            use ($type, $css, $disabled, $readonly, $asButtonGroup, $labelOptions, $opts) {
                $opts += [
                    'data-index' => $index,
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
                if ($checked && $asButtonGroup) {
                    Html::addCssClass($labelOptions, 'active');
                }
                $opts['labelOptions'] = $labelOptions;
                $out = Html::$type($name, $checked, $opts);
                return $asButtonGroup ? $out : "<div class='{$type}{$css}'>{$out}</div>";
            };
        }
        return parent::$inputType($items, $options);
    }
}
