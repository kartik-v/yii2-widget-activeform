<?php

/**
 * @copyright  Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2016
 * @package    yii2-widgets
 * @subpackage yii2-widget-activeform
 * @version    1.4.9
 */

namespace kartik\form;

use yii\base\InvalidConfigException;
use yii\helpers\Html;

/**
 * Extends the ActiveForm widget to handle various
 * bootstrap form types.
 *
 * Example(s):
 * ```php
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
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since  1.0
 */
class ActiveForm extends \yii\widgets\ActiveForm
{
    const DEFAULT_LABEL_SPAN = 2; // this will offset the adjacent input accordingly
    const FULL_SPAN = 12; // bootstrap default full grid width

    /* Form Types */
    const TYPE_VERTICAL = 'vertical';
    const TYPE_HORIZONTAL = 'horizontal';
    const TYPE_INLINE = 'inline';

    /* Size Modifiers */
    const SIZE_TINY = 'xs';
    const SIZE_SMALL = 'sm';
    const SIZE_MEDIUM = 'md';
    const SIZE_LARGE = 'lg';

    /* Label Display Settings */
    const SCREEN_READER = 'sr-only';

    /**
     * @inheritdoc
     */
    public $fieldClass = 'kartik\form\ActiveField';

    /**
     * @var string form orientation type (for bootstrap styling). Either 'vertical', 'horizontal' or 'vertical'.
     *     Defaults to 'vertical'.
     */
    public $type;

    /**
     * @var int set the bootstrap grid width. Defaults to [[ActiveForm::FULL_SPAN]].
     */
    public $fullSpan = self::FULL_SPAN;

    /**
     * @var array the configuration for the form. Takes in the following properties
     * - labelSpan: int, the bootstrap grid column width (usually between 1 to 12)
     * - deviceSize: string, one of the bootstrap sizes (refer the ActiveForm::SIZE constants)
     * - showLabels: boolean|string, whether to show labels (true), hide labels (false), or display only for screen
     *     reader (ActiveForm::SCREEN_READER). This is mainly useful for inline forms.
     * - showErrors: boolean, whether to show errors (true) or hide errors (false). This is mainly useful for inline
     *     forms.
     * - showHints: boolean, whether to show hints (true) or hide errors (false). Defaults to `true`. The hint will be
     *     rendered only if a valid hint has been set through the `hint()` method.
     * ```
     * [
     *      'labelSpan' => 2,
     *      'deviceSize' => ActiveForm::SIZE_MEDIUM,
     *      'showLabels' => true,
     *      'showErrors' => true,
     *      'showHints' => true
     * ],
     * ```
     */
    public $formConfig = [];

    /**
     * @var array HTML attributes for the form tag. Default is `['role' => 'form']`.
     */
    public $options = ['role' => 'form'];

    /**
     * @var bool whether all data in form are to be static inputs
     */
    public $staticOnly = false;

    /**
     * @var bool whether all inputs in form are to be disabled
     */
    public $disabled = false;

    /**
     * @var bool whether all inputs in form are to be readonly
     */
    public $readonly = false;

    /**
     * @var array the default form configuration
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
        if (!is_int($this->fullSpan) || $this->fullSpan < 1) {
            throw new InvalidConfigException("The 'fullSpan' property must be a valid positive integer.");
        }
        $this->initForm();
        parent::init();
        $this->registerAssets();
    }

    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $view = $this->getView();
        ActiveFormAsset::register($view);
        $id = 'jQuery("#' . $this->options['id'] . ' .kv-hint-special")';
        $view->registerJs('var $el=' . $id . ';if($el.length){$el.each(function(){$(this).activeFieldHint()});}');
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
        $prefix = 'form-' . $this->type;
        $css = [$prefix];
        /* Fixes the button alignment for inline forms containing error block */
        if ($this->type === self::TYPE_INLINE && $this->formConfig['showErrors']) {
            $css[] = $prefix . '-block';
        }
        if ($this->type === self::TYPE_HORIZONTAL) {
            $css[] = 'kv-form-horizontal';
        }
        Html::addCssClass($this->options, $css);
    }
}