<?php

/**
 * @copyright  Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2018
 * @package    yii2-widgets
 * @subpackage yii2-widget-activeform
 * @version    1.5.7
 */

namespace kartik\form;

use kartik\base\BootstrapInterface;
use kartik\base\BootstrapTrait;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;
use yii\widgets\ActiveForm as YiiActiveForm;

/**
 * ActiveForm is a widget that builds an interactive HTML form for one or multiple data models and extends the
 * [[YiiActiveForm]] widget to handle various bootstrap form types and new functionality.
 *
 * Example:
 *
 * ~~~
 * // Horizontal Form
 * $form = ActiveForm::begin([
 *      'id' => 'form-signup',
 *      'type' => ActiveForm::TYPE_HORIZONTAL
 * ]);
 * // Inline Form
 * $form = ActiveForm::begin([
 *      'id' => 'form-login',
 *      'type' => ActiveForm::TYPE_INLINE
 *      'fieldConfig' => ['autoPlaceholder'=>true]
 * ]);
 * // Horizontal Form Configuration
 * $form = ActiveForm::begin([
 *      'id' => 'form-signup',
 *      'type' => ActiveForm::TYPE_HORIZONTAL
 *      'formConfig' => ['labelSpan' => 2, 'deviceSize' => ActiveForm::SIZE_SMALL]
 * ]);
 * ~~~
 *
 * @method ActiveField field(Model $model, string $attribute, array $options = [])
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since  1.0
 */
class ActiveForm extends YiiActiveForm implements BootstrapInterface
{
    use BootstrapTrait;

    /**
     * @var bool whether to render tooltip styled error and success messages. Applicable only for [[bsVersion]] 4.x
     * and above.
     */
    public $tooltipStyleFeedback = false;

    /**
     * @var int the default label span for horizontal forms which will offset the adjacent input accordingly.
     */
    const DEFAULT_LABEL_SPAN = 2;

    /**
     * @var int the bootstrap default full grid width.
     */
    const FULL_SPAN = 12;

    /**
     * @var string bootstrap styled vertical form layout (this is the default style)
     */
    const TYPE_VERTICAL = 'vertical';

    /**
     * @var string bootstrap styled horizontal form layout
     */
    const TYPE_HORIZONTAL = 'horizontal';

    /**
     * @var string bootstrap styled inline form layout
     */
    const TYPE_INLINE = 'inline';

    /**
     * @var string bootstrap screen reader style for labels
     */
    const SCREEN_READER = 'sr-only';

    /**
     * @inheritdoc
     */
    public $fieldClass = 'kartik\form\ActiveField';

    /**
     * @var string form orientation type (for bootstrap styling). Either [[TYPE_VERTICAL]], [[TYPE_HORIZONTAL]], or
     * [[TYPE_INLINE]]. Defaults to [[TYPE_VERTICAL]].
     */
    public $type;

    /**
     * @var integer the bootstrap grid width. Defaults to [[FULL_SPAN]].
     */
    public $fullSpan = self::FULL_SPAN;

    /**
     * @var array the configuration for the form. Takes in the following properties
     * - `labelSpan`: _integer_, the bootstrap grid column width (usually between 1 to 12)
     * - `deviceSize`: _string_, one of the bootstrap sizes (refer the ActiveForm::SIZE constants)
     * - `showLabels`: _boolean_|_string_, whether to show labels (true)_, hide labels (false)_, or display only for
     *   [[SCREEN_READER]]. This is mainly useful for inline forms.
     * - `showErrors`: _boolean_, whether to show errors (true) or hide errors (false). This is mainly useful for inline
     *   forms.
     * - `showHints`: _boolean_, whether to show hints (true) or hide errors (false). Defaults to `true`. The hint will be
     *   rendered only if a valid hint has been set through the `hint()` method.
     * ~~~
     * [
     *     'labelSpan' => 2,
     *     'deviceSize' => ActiveForm::SIZE_MEDIUM,
     *     'showLabels' => true,
     *     'showErrors' => true,
     *     'showHints' => true
     * ],
     * ~~~
     */
    public $formConfig = [];

    /**
     * @var array HTML attributes for the form tag. Defaults to `['role' => 'form']`.
     */
    public $options = ['role' => 'form'];

    /**
     * @var boolean whether all data in form are to be static inputs
     */
    public $staticOnly = false;

    /**
     * @var boolean whether all inputs in form are to be disabled
     */
    public $disabled = false;

    /**
     * @var boolean whether all inputs in form are to be readonly.
     */
    public $readonly = false;

    /**
     * @var array the default form configuration.
     */
    private $_config = [
        self::TYPE_VERTICAL => [
            'showLabels' => true, // show or hide labels (mainly useful for inline type form)
            'showErrors' => true, // show or hide errors (mainly useful for inline type form)
            'showHints' => true  // show or hide hints below the input
        ],
        self::TYPE_HORIZONTAL => [
            'showLabels' => true,
            'showErrors' => true,
            'showHints' => true,
        ],
        self::TYPE_INLINE => [
            'showLabels' => self::SCREEN_READER,
            'showErrors' => false,
            'showHints' => true,
        ],
    ];

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        $this->initBsVersion();
        if (!is_int($this->fullSpan) || $this->fullSpan < 1) {
            throw new InvalidConfigException("The 'fullSpan' property must be a valid positive integer.");
        }
        $this->initForm();
        parent::init();
        $this->registerAssets();
    }

    /**
     * Gets form layout style configuration. This method is used by [[\kartik\field\FieldRange]] widget.
     *
     * @return array the form layout style configuration.
     */
    public function getFormLayoutStyle()
    {
        $config = $this->formConfig;
        $span = isset($config['labelSpan']) ? $config['labelSpan'] : ActiveField::NOT_SET;
        $size = isset($config['deviceSize']) ? $config['deviceSize'] : ActiveField::NOT_SET;
        $labelCss = $inputCss = ActiveField::NOT_SET;
        $iSpan = intval($span);
        if ($span != ActiveField::NOT_SET && $iSpan > 0) {
            // validate if invalid `labelSpan` is passed else set to [[DEFAULT_LABEL_SPAN]]
            if ($iSpan <= 0 && $iSpan >= $this->fullSpan) {
                $iSpan = self::DEFAULT_LABEL_SPAN;
            }

            // validate if invalid `deviceSize` is passed else set to [[SIZE_MEDIUM]]
            if ($size == ActiveField::NOT_SET) {
                $size = self::SIZE_MEDIUM;
            }

            $prefix = "col-{$size}-";
            $labelCss = $prefix . $iSpan;
            $inputCss = $prefix . ($this->fullSpan - $iSpan);
        }
        return ['labelCss' => $labelCss, 'inputCss' => $inputCss];
    }

    /**
     * Registers the assets for the [[ActiveForm]] widget.
     * @throws InvalidConfigException
     */
    public function registerAssets()
    {
        $view = $this->getView();
        ActiveFormAsset::register($view);
        $id = 'jQuery("#' . $this->options['id'] . ' .kv-hint-special")';
        $js = 'var $el=' . $id . ';if($el.length){$el.each(function(){$(this).activeFieldHint()});}';
        if ($this->isBs4()) {
            $js .= "kvBs4InitForm();";
        }
        $view->registerJs($js);
    }

    /**
     * Whether an inline layout form
     * @return bool
     */
    public function isInline()
    {
        return $this->type === self::TYPE_INLINE;
    }

    /**
     * Whether a horizontal layout form
     * @return bool
     */
    public function isHorizontal()
    {
        return $this->type === self::TYPE_HORIZONTAL;
    }

    /**
     * Whether a vertical layout form
     * @return bool
     */
    public function isVertical()
    {
        return !$this->isHorizontal() && !$this->isInline();
    }

    /**
     * Initializes the form configuration array and parameters for the form.
     *
     * @throws InvalidConfigException
     */
    protected function initForm()
    {
        if (empty($this->type)) {
            $this->type = self::TYPE_VERTICAL;
        }
        if (!in_array($this->type, [self::TYPE_VERTICAL, self::TYPE_HORIZONTAL, self::TYPE_INLINE])) {
            throw new InvalidConfigException('Invalid layout type: ' . $this->type);
        }
        $this->formConfig = array_replace_recursive($this->_config[$this->type], $this->formConfig);
        $css = ["form-{$this->type}"];
        if ($this->isHorizontal()) {
            $css[] = 'kv-form-horizontal';
        }
        if ($this->isBs4()) {
            $css[] = 'kv-form-bs4';
            if ($this->tooltipStyleFeedback) {
                $css[] = 'tooltip-feedback';
            }
        }
        Html::addCssClass($this->options, $css);
    }
}
