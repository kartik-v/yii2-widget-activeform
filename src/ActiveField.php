<?php

/**
 * @copyright  Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2019
 * @package    yii2-widgets
 * @subpackage yii2-widget-activeform
 * @version    1.5.8
 */

namespace kartik\form;

use kartik\base\Config;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use kartik\base\AddonTrait;
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
 *         'content'=>'<i class="fas fa-mobile-alt"></i>'
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
 * @property ActiveForm $form
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since  1.0
 */
class ActiveField extends YiiActiveField
{
    use AddonTrait;

    /**
     * @var string an empty string value
     */
    const NOT_SET = '';

    /**
     * @var string HTML radio input type
     */
    const TYPE_RADIO = 'radio';

    /**
     * @var string HTML checkbox input type
     */
    const TYPE_CHECKBOX = 'checkbox';

    /**
     * @var string the default height for the Krajee multi select input
     */
    const MULTI_SELECT_HEIGHT = '145px';

    /**
     * @var string default hint type that is displayed below the input
     */
    const HINT_DEFAULT = 1;

    /**
     * @var string special hint type that allows display via an indicator icon or on hover/click of the field label
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
     * @var bool whether to auto offset toggle inputs (checkboxes / radios) horizontal form layout for BS 4.x forms.
     * This will read the `labelSpan` and automatically offset the checkboxes/radios.
     */
    public $autoOffset = true;

    /**
     * @var bool whether to render the wrapper in the template if [[wrapperOptions]] is empty.
     */
    public $renderEmptyWrapper = false;

    /**
     * @inheritdoc
     */
    public $labelOptions = [];

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
     * - `icon`: _string_, the markup to display the help icon. Defaults to
     *    - `<i class="glyphicon glyphicon-question-sign text-info"></i>` for Bootstrap 3.x.
     *    - `<i class="fas fa-question-circle text-info"></i>` for Bootstrap 4.x.
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
     * - `prefix`: _string_, the icon CSS class prefix to use if `type` is `icon`. Defaults to `glyphicon glyphicon-` for
     *    Bootstrap 3.x and `fas fa-` for Bootstrap 4.x.
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
     * @var string content to be placed before field within the form group at the beginning
     */
    public $contentBeforeField = '';

    /**
     * @var string content to be placed after field within the form group at the end
     */
    public $contentAfterField = '';

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
     * @var string the template for rendering the Bootstrap 4.x custom file browser control
     * @see https://getbootstrap.com/docs/4.1/components/forms/#file-browser
     */
    public $customFileTemplate = "<div class=\"custom-file\">\n{input}\n{label}\n</div>\n{error}\n{hint}";

    /**
     * @var string the template for rendering checkboxes and radios for a default Bootstrap markup without an enclosed
     * label
     */
    public $checkTemplate = "{input}\n{label}\n{error}\n{hint}";

    /**
     * @var string the template for rendering checkboxes and radios for a default Bootstrap markup with an enclosed
     * label
     */
    public $checkEnclosedTemplate = "{beginLabel}\n{input}\n{labelTitle}\n{endLabel}\n{error}\n{hint}";

    /**
     * @var array the HTML attributes for the container wrapping BS4 checkbox or radio controls within which the content
     * will be rendered via the [[checkTemplate]] or [[checkEnclosedTemplate]]
     */
    public $checkWrapperOptions = [];

    /**
     * @var array addon options for text and password inputs. The following settings can be configured:
     * - `prepend`: _array_, the prepend addon configuration
     *      - `content`: _string_, the prepend addon content
     *      - `asButton`: _boolean_, whether the addon is a button or button group. Defaults to false.
     *      - `options`: _array_, the HTML attributes to be added to the container.
     * - `append`: _array_, the append addon configuration
     *      - `content`: _string_|_array_, the append addon content
     *      - `asButton`: _boolean_, whether the addon is a button or button group. Defaults to false.
     *      - `options`: _array_, the HTML attributes to be added to the container.
     * - `groupOptions`: _array_, HTML options for the input group
     * - `contentBefore`: _string_, content placed before addon
     * - `contentAfter`: _string_, content placed after addon
     */
    public $addon = [];

    /**
     * @var bool whether to highlight error and success states on input group addons automatically
     */
    public $highlightAddon = true;

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
     * @var boolean whether to show required asterisk/star indicator after each field label when the model attribute is
     * set to have a `required` validation rule. This will add a CSS class `has-star` to the label and show the required
     * asterisk/star after the label based on CSS `::after` styles. If you want any other label markup to show a
     * required asterisk for a required model attribute field, then just add the CSS class `has-star` to the label/span
     * markup element within the active field container with CSS class `form-group`.
     */
    public $showRequiredIndicator = true;

    /**
     * @var boolean whether the label is to be hidden and auto-displayed as a placeholder
     */
    public $autoPlaceholder;

    /**
     * @var array options for the wrapper tag, used in the `{beginWrapper}` token within [[template]].
     */
    public $wrapperOptions = [];

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
     * @var boolean whether the hint icon is beside the input.
     */
    private $_iconBesideInput = false;

    /**
     * @var string the identifier for the hint popover container.
     */
    private $_hintPopoverContainer;

    /**
     * @inheritdoc
     */
    public function begin()
    {
        if ($this->_hasFeedback) {
            Html::addCssClass($this->options, 'has-feedback');
        }
        return parent::begin() . $this->contentBeforeField;
    }

    /**
     * @inheritdoc
     */
    public function end()
    {
        return $this->contentAfterField . parent::end();
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
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
     * - `custom`: _bool_, whether to render bootstrap 4.x custom checkbox/radio styled control. Defaults to `false`.
     *    This is applicable only for Bootstrap 4.x forms.
     * @see https://getbootstrap.com/docs/4.1/components/forms/#checkboxes-and-radios-1
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
     * @param bool|null $enclosedByLabel whether to enclose the radio within the label. If `true`, the method will
     * still use [[template]] to layout the checkbox and the error message except that the radio is enclosed by
     * the label tag.
     *
     * @return ActiveField object
     * @throws InvalidConfigException
     */
    public function checkbox($options = [], $enclosedByLabel = null)
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
     *
     * - `custom`: _bool_, whether to render bootstrap 4.x custom checkbox/radio styled control. Defaults to `false`.
     *    This is applicable only for Bootstrap 4.x forms.
     * @see https://getbootstrap.com/docs/4.1/components/forms/#checkboxes-and-radios-1
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
     * @throws InvalidConfigException
     */
    public function checkboxList($items, $options = [])
    {
        return $this->getToggleFieldList(self::TYPE_CHECKBOX, $items, $options);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function dropDownList($items, $options = [])
    {
        $this->initDisability($options);
        Html::addCssClass($options, $this->isCustomControl($options) ? 'custom-select' : $this->addClass);
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
     * Checks whether bootstrap 4.x custom control based on `options` parameter
     * @param array $options HTML attributes for the control
     * @return bool
     * @throws InvalidConfigException
     */
    protected function isCustomControl(&$options)
    {
        return ArrayHelper::remove($options, 'custom', false) && $this->form->isBs4();
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function fileInput($options = [])
    {
        if ($this->isCustomControl($options)) {
            Html::removeCssClass($options, 'form-control');
            Html::addCssClass($options, 'custom-file-input');
            Html::addCssClass($this->labelOptions, 'custom-file-label');
            $this->template = $this->customFileTemplate;
        }
        return parent::fileInput($options);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function input($type, $options = [])
    {
        $this->initFieldOptions($options);
        if ($this->isCustomControl($options) && $type === 'range') {
            Html::addCssClass($options, 'custom-range');
        }
        if ($type !== 'range' && $type !== 'color') {
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
        if ($this->_offset) {
            $label = '';
        }
        return parent::label($label, $options);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function listBox($items, $options = [])
    {
        $this->initDisability($options);
        Html::addCssClass($options, $this->isCustomControl($options) ? 'custom-select' : $this->addClass);
        return parent::listBox($items, $options);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function passwordInput($options = [])
    {
        $this->initFieldOptions($options);
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
     *
     * - `custom`: _bool_, whether to render bootstrap 4.x custom checkbox/radio styled control. Defaults to `false`.
     *    This is applicable only for Bootstrap 4.x forms.
     * @see https://getbootstrap.com/docs/4.1/components/forms/#checkboxes-and-radios-1
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
     * @param bool|null $enclosedByLabel whether to enclose the radio within the label. If `true`, the method will still
     * use [[template]] to layout the checkbox and the error message except that the radio is enclosed by the label tag.
     *
     * @return ActiveField object
     * @throws InvalidConfigException
     */
    public function radio($options = [], $enclosedByLabel = null)
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
     *
     * - `custom`: _bool_, whether to render bootstrap 4.x custom checkbox/radio styled control. Defaults to `false`.
     *    This is applicable only for Bootstrap 4.x forms.
     * @see https://getbootstrap.com/docs/4.1/components/forms/#checkboxes-and-radios-1
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
     * @throws InvalidConfigException
     */
    public function radioList($items, $options = [])
    {
        return $this->getToggleFieldList(self::TYPE_RADIO, $items, $options);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
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
            $this->initFieldOptions($this->inputOptions);
            $this->initDisability($this->inputOptions);
            $this->buildTemplate();
        }
        return parent::render($content);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function textInput($options = [])
    {
        $this->initFieldOptions($options);
        Html::addCssClass($options, $this->addClass);
        $this->initDisability($options);
        return parent::textInput($options);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function textarea($options = [])
    {
        $this->initFieldOptions($options);
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
     * @throws InvalidConfigException
     */
    public function staticInput($options = [])
    {
        $content = isset($this->staticValue) ? $this->staticValue :
            Html::getAttributeValue($this->model, $this->attribute);
        $this->form->addCssClass($options, ActiveForm::BS_FORM_CONTROL_STATIC);
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
     * @throws InvalidConfigException
     */
    public function multiselect($items, $options = [])
    {
        $opts = $options;
        $this->initDisability($opts);
        $opts['encode'] = false;
        $height = ArrayHelper::remove($opts, 'height', self::MULTI_SELECT_HEIGHT);
        $selector = ArrayHelper::remove($opts, 'selector', self::TYPE_CHECKBOX);
        $container = ArrayHelper::remove($opts, 'container', []);
        Html::addCssStyle($container, 'height:' . $height, true);
        Html::addCssClass($container, $this->addClass . ' input-multiselect');
        $container['tabindex'] = 0;
        $this->_multiselect = Html::tag('div', '{input}', $container);
        return $selector == self::TYPE_RADIO ? $this->radioList($items, $opts) : $this->checkboxList($items, $opts);
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
     * @throws InvalidConfigException
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
     * @throws InvalidConfigException
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
     * Gets bootstrap grid column CSS based on size
     * @param string $size
     * @return string
     * @throws InvalidConfigException
     */
    protected function getColCss($size)
    {
        $bsVer = $this->form->isBs4() ? '4' : '3';
        $sizes = ArrayHelper::getValue($this->form->bsColCssPrefixes, $bsVer, []);
        if ($size == self::NOT_SET || !isset($sizes[$size])) {
            return 'col-' . ActiveForm::SIZE_MEDIUM . '-';
        }
        return $sizes[$size];
    }

    /**
     * Generates a toggle field (checkbox or radio)
     *
     * @param string $type the toggle input type 'checkbox' or 'radio'.
     * @param array $options options (name => config) for the toggle input list container tag.
     * @param bool|null $enclosedByLabel whether the input is enclosed by the label tag
     *
     * @return ActiveField object
     * @throws InvalidConfigException
     */
    protected function getToggleField($type = self::TYPE_CHECKBOX, $options = [], $enclosedByLabel = null)
    {
        $this->initDisability($options);
        $custom = $this->isCustomControl($options);
        $isBs4 = $this->form->isBs4();
        if ($enclosedByLabel === null) {
            $enclosedByLabel = !$isBs4 && !$custom;
        }
        if (!isset($options['template'])) {
            $this->template = $enclosedByLabel ? $this->checkEnclosedTemplate : $this->checkTemplate;
        } else {
            $this->template = $options['template'];
            unset($options['template']);
        }
        $prefix = $isBs4 ? ($custom ? 'custom-control' : 'form-check') : $type;
        Html::removeCssClass($options, 'form-control');
        $this->form->removeCssClass($this->labelOptions, ActiveForm::BS_CONTROL_LABEL);
        Html::addCssClass($this->checkWrapperOptions, $prefix);
        if ($isBs4) {
            Html::addCssClass($this->labelOptions, "{$prefix}-label");
            Html::addCssClass($options, "{$prefix}-input");
            if ($custom) {
                Html::addCssClass($this->checkWrapperOptions, "custom-{$type}");
            }
        } elseif (!$enclosedByLabel) {
            Html::addCssClass($this->checkWrapperOptions, "not-enclosed");
        }
        $this->template = Html::tag('div', $this->template, $this->checkWrapperOptions);
        if ($this->form->isHorizontal()) {
            Html::removeCssClass($this->labelOptions, $this->getColCss($this->deviceSize) . $this->labelSpan);
            if ($this->autoOffset) {
                $this->template = Html::tag('div', '', ['class' => $this->_labelCss]) .
                    Html::tag('div', $this->template, ['class' => $this->_inputCss]);
            } else {
                Html::removeCssClass($this->options, 'row');
            }
        }
        if ($this->form->isInline()) {
            Html::removeCssClass($this->labelOptions, ActiveForm::SCREEN_READER);
        }
        if ($enclosedByLabel) {
            if (isset($options['label'])) {
                $this->parts['{labelTitle}'] = $options['label'];
            }
            $this->parts['{beginLabel}'] = Html::beginTag('label', $this->labelOptions);
            $this->parts['{endLabel}'] = Html::endTag('label');
        }
        return parent::$type($options, false);
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
     * @throws InvalidConfigException
     */
    protected function initActiveField()
    {
        if (isset($this->enableError)) {
            $this->showErrors = $this->enableError;
        }
        if (isset($this->enableLabel)) {
            $this->showLabels = $this->enableLabel;
        }
        $isBs4 = $this->form->isBs4();
        $isInline = $this->form->isInline();
        $isHorizontal = $this->form->isHorizontal();
        if ($isBs4) {
            $errCss = $this->form->tooltipStyleFeedback ? 'invalid-tooltip' : 'invalid-feedback';
            Html::addCssClass($this->errorOptions, $errCss);
        }
        $showLabels = $this->getConfigParam('showLabels');
        $this->_isHintSpecial = $this->hintType === self::HINT_SPECIAL;
        if ($isInline && !isset($this->autoPlaceholder) && $showLabels !== true) {
            $this->autoPlaceholder = true;
        } elseif (!isset($this->autoPlaceholder)) {
            $this->autoPlaceholder = false;
        }
        if (!isset($this->labelOptions['class']) && ($isHorizontal || !$isBs4 && !$isInline)) {
            $this->labelOptions['class'] = $this->form->getCssClass(ActiveForm::BS_CONTROL_LABEL);
        }
        if ($showLabels === ActiveForm::SCREEN_READER) {
            Html::addCssClass($this->labelOptions, ActiveForm::SCREEN_READER);
        }
        if ($this->showRequiredIndicator) {
            Html::addCssClass($this->labelOptions, 'has-star');
        }
        if ($this->highlightAddon) {
            Html::addCssClass($this->options, 'highlight-addon');
        }
        if ($isHorizontal) {
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
     * @throws InvalidConfigException
     */
    protected function initHorizontal()
    {
        $hor = $this->horizontalCssClasses;
        $span = $this->getConfigParam('labelSpan', '');
        $size = $this->getConfigParam('deviceSize', '');
        if ($this->form->isBs4()) {
            Html::addCssClass($this->options, 'row');
        }
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
            $prefix = $this->getColCss($size);
            $this->_labelCss = $prefix . $span;
            $this->_inputCss = $prefix . ($this->form->fullSpan - $span);
        }

        if (isset($hor['wrapper'])) {
            if ($span !== self::NOT_SET) {
                $this->_inputCss .= " ";
            }
            $this->_inputCss .= $hor['wrapper'];
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
     * @throws InvalidConfigException
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
     * @throws InvalidConfigException
     */
    protected function buildLayoutParts($showLabels, $showErrors)
    {
        if (!$showErrors) {
            $this->_settings['error'] = '';
        }
        if ($this->skipFormLayout) {
            $this->mergeSettings($showLabels, $showErrors);
            $this->parts['{beginWrapper}'] = '';
            $this->parts['{endWrapper}'] = '';
            $this->parts['{beginLabel}'] = '';
            $this->parts['{labelTitle}'] = '';
            $this->parts['{endLabel}'] = '';
            return;
        }
        if (!empty($this->_inputCss)) {
            $inputDivClass = $this->_inputCss;
            if ($showLabels === false || $showLabels === ActiveForm::SCREEN_READER) {
                $inputDivClass = $this->getColCss($this->deviceSize) . $this->form->fullSpan;
            }
            Html::addCssClass($this->wrapperOptions, $inputDivClass);
        }
        if (!isset($this->parts['{beginWrapper}'])) {
            if ($this->renderEmptyWrapper || !empty($this->wrapperOptions)) {
                $options = $this->wrapperOptions;
                $tag = ArrayHelper::remove($options, 'tag', 'div');
                $this->parts['{beginWrapper}'] = Html::beginTag($tag, $options);
                $this->parts['{endWrapper}'] = Html::endTag($tag);
            } else {
                $this->parts['{beginWrapper}'] = $this->parts['{endWrapper}'] = '';
            }
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
     * @throws InvalidConfigException
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
        $iconCss = $this->form->isBs4() ? 'fas fa fa-question-circle' : 'glyphicon glyphicon-question-sign';
        $attr = 'style="width:100%"{id}';
        $defaultSettings = [
            'showIcon' => true,
            'iconBesideInput' => false,
            'labelTemplate' => '{label}{help}',
            'inputTemplate' => "<table {$attr}><tr><td>{input}</td>" . '<td style="width:5%">{help}</td></tr></table>',
            'onLabelClick' => false,
            'onLabelHover' => true,
            'onIconClick' => true,
            'onIconHover' => false,
            'labelCssClass' => 'kv-hint-label',
            'iconCssClass' => 'kv-hint-icon',
            'contentCssClass' => 'kv-hint-content',
            'icon' => '<i class="' . $iconCss . ' text-info"></i>',
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
     * Initializes sizes and placeholder based on $autoPlaceholder
     *
     * @param array $options the HTML attributes for the input
     * @throws InvalidConfigException
     */
    protected function initFieldOptions(&$options)
    {
        $this->initFieldSize($options, 'lg');
        $this->initFieldSize($options, 'sm');
        if ($this->autoPlaceholder) {
            $label = $this->model->getAttributeLabel(Html::getAttributeName($this->attribute));
            $this->inputOptions['placeholder'] = $label;
            $options['placeholder'] = $label;
        }
        $this->addErrorClassBS4($options);
    }

    /**
     * Initializes field by detecting the bootstrap CSS size and sets a size modifier CSS to the field container
     * @param array $options the HTML options
     * @param string $size the size to init
     * @throws InvalidConfigException
     */
    protected function initFieldSize($options, $size)
    {
        $isBs4 = $this->form->isBs4();
        if ($isBs4 && Config::hasCssClass($options, "form-control-{$size}") ||
            !$isBs4 && Config::hasCssClass($options, "input-{$size}") ||
            isset($this->addon['groupOptions']) &&
            Config::hasCssClass($this->addon['groupOptions'], "input-group-{$size}")) {
            Html::addCssClass($this->options, "has-size-{$size}");
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
     * @throws InvalidConfigException
     */
    protected function buildTemplate()
    {
        $showLabels = $showErrors = $input = $error = null;
        extract($this->_settings);
        if ($this->_isStatic || (isset($this->showErrors) && !$this->showErrors) ||
            (!$this->skipFormLayout && !$this->getConfigParam('showErrors'))) {
            $showErrors = false;
        }
        $showLabels = $showLabels && $this->hasLabels();
        $this->buildLayoutParts($showLabels, $showErrors);
        extract($this->_settings);
        if (!$showErrors) {
            Html::addCssClass($this->options, 'hide-errors');
        }
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
     * @throws InvalidConfigException
     */
    protected function generateAddon()
    {
        if (empty($this->addon)) {
            return '{input}';
        }
        $addon = $this->addon;
        $isBs4 = $this->form->isBs4();
        $prepend = $this->getAddonContent('prepend', $isBs4);
        $append = $this->getAddonContent('append', $isBs4);
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
        $prefix = ArrayHelper::getValue($config, 'prefix', $this->form->getDefaultIconPrefix());
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
        Html::addCssClass($options, ['form-control-feedback', "kv-feedback-{$cat}"]);
        $icon = $type === 'raw' ? $markup : Html::tag('i', '', ['class' => $prefix . $markup]);
        return Html::tag('span', $icon, $options) .
            Html::tag('span', $desc, ['id' => $key, 'class' => ActiveForm::SCREEN_READER]);
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
     * - `custom`: _bool_, whether to render bootstrap 4.x custom checkbox/radio styled control. Defaults to `false`.
     *    This is applicable only for Bootstrap 4.x forms.
     * @see https://getbootstrap.com/docs/4.1/components/forms/#checkboxes-and-radios-1
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
     * @param boolean $asBtnGrp whether to generate the toggle list as a bootstrap button group
     *
     * @return ActiveField object
     * @throws InvalidConfigException
     */
    protected function getToggleFieldList($type, $items, $options = [], $asBtnGrp = false)
    {
        $isBs4 = $this->form->isBs4();
        $disabled = ArrayHelper::remove($options, 'disabledItems', []);
        $readonly = ArrayHelper::remove($options, 'readonlyItems', []);
        $cust = $this->isCustomControl($options);
        $pre = $cust ? 'custom-control' : 'form-check';
        if ($asBtnGrp) {
            Html::addCssClass($options, ['btn-group', 'btn-group-toggle']);
            $options['data-toggle'] = 'buttons';
            $options['inline'] = true;
            if (!isset($options['itemOptions']['labelOptions']['class'])) {
                $options['itemOptions']['labelOptions']['class'] = 'btn ' . $this->form->getDefaultBtnCss();
            }
        }
        $in = ArrayHelper::remove($options, 'inline', false);
        $inputType = "{$type}List";
        $opts = ArrayHelper::getValue($options, 'itemOptions', []);
        $this->initDisability($opts);
        $css = $this->form->disabled ? ' disabled' : '';
        $css .= $this->form->readonly ? ' readonly' : '';
        if ($isBs4) {
            Html::addCssClass($this->labelOptions, 'pt-0');
        }
        if (!$isBs4 && $in && !isset($options['itemOptions']['labelOptions']['class'])) {
            $options['itemOptions']['labelOptions']['class'] = "{$type}-inline{$css}";
        } elseif (!isset($options['item'])) {
            $labelOpts = ArrayHelper::getValue($opts, 'labelOptions', []);
            $options['item'] = function ($index, $label, $name, $checked, $value)
            use ($type, $css, $disabled, $readonly, $asBtnGrp, $labelOpts, $opts, $in, $isBs4, $cust, $pre, $options) {
                $id = isset($options['id']) ? $options['id'] . '-' . $index :
                    strtolower(preg_replace('/[^a-zA-Z0-9=\s—–-]+/u', '-', $name)) . '-' . $index;
                $opts += [
                    'data-index' => $index,
                    'value' => $value,
                    'disabled' => $this->form->disabled,
                    'readonly' => $this->form->readonly,
                ];
                $enclosedLabel = !$cust && !$isBs4 || $asBtnGrp;
                if ($enclosedLabel) {
                    $opts += ['label' => $label];
                }
                if (!isset($opts['id'])) {
                    $opts['id'] = $id;
                }
                $wrapperOptions = [];
                if ($isBs4 && !$asBtnGrp) {
                    $opts += ['class' => "{$pre}-input"];
                    Html::addCssClass($labelOpts, "{$pre}-label");
                    $wrapperOptions = ['class' => [$pre . ($cust ? ' custom-' . $type : '')]];
                    if ($in) {
                        Html::addCssClass($wrapperOptions, "{$pre}-inline");
                    }
                } elseif (!$isBs4) {
                    $wrapperOptions = ['class' => [$type . $css]];
                }
                if ($asBtnGrp) {
                    if ($checked) {
                        Html::addCssClass($labelOpts, 'active');
                    }
                    $opts['autocomplete'] = 'off';
                }
                if (!empty($disabled) && in_array($value, $disabled) || $this->form->disabled) {
                    Html::addCssClass($labelOpts, 'disabled');
                    $opts['disabled'] = true;
                }
                if (!empty($readonly) && in_array($value, $readonly) || $this->form->readonly) {
                    Html::addCssClass($labelOpts, 'disabled');
                    $opts['readonly'] = true;
                }
                $opts['labelOptions'] = $labelOpts;
                $out = Html::$type($name, $checked, $opts);
                if (!$enclosedLabel) {
                    $out .= Html::label($label, $opts['id'], $labelOpts);
                }
                return $asBtnGrp ? $out : Html::tag('div', $out, $wrapperOptions);
            };
        }
        return parent::$inputType($items, $options);
    }

    /**
     * Adds Bootstrap 4 validation class to the input options if needed.
     * @param array $options
     * @throws InvalidConfigException
     */
    protected function addErrorClassBS4(&$options)
    {
        $attributeName = Html::getAttributeName($this->attribute);
        if ($this->form->isBs4() &&
            $this->model->hasErrors($attributeName) &&
            $this->form->validationStateOn === ActiveForm::VALIDATION_STATE_ON_CONTAINER) {
            Html::addCssClass($options, 'is-invalid');
        }
    }
}
