<?php

/**
 * @copyright  Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2016
 * @package    yii2-widgets
 * @subpackage yii2-widget-activeform
 * @version    1.4.9a
 * 
 * changes 1.4.9a: 
 * - dynamic change of parameter 'formConfig and move 'getFormLayoutStyle' to ActiveField
 * - fix issue #75 (closure as fieldConfig)
 * 
 * changes 1.4.9b:
 * 
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

    const NOT_SET = '';
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
     * @var string the default field class name when calling [[field()]] to create a new field.
     * @see fieldConfig
     * add default fieldClass for fix of #75
     */
    public $fieldClass = 'kartik\form\ActiveField';


    /**
     * @var string form orientation type (for bootstrap styling). Either
     * 'vertical', 'horizontal' or 'vertical'. For compatibility with Yii bootstrap
     * ActiveForm widget 'default' means 'vertical'.
     * Defaults to 'vertical'.
     */
    public $type;
    
    /**
     *
     * @var type string synonym for '$type' for Comp. with Yii bootstrap.
     */
    public $layout;

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
     * new from Enrica 1.4.9a
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
     * @var boolean whether all inputs in form are to be readonly
     */
    public $readonly = false;

    /**
     * @var string the label additional css class for horizontal forms and special inputs like checkbox and radio.
     */
    private $_labelCss;

    /**
     * @var string the input container additional css class for horizontal forms and special inputs like checkbox and
     *     radio.
     */
    private $_inputCss;

    /**
     * @var string the offset class for error and hint for horizontal forms
     * or for special inputs like checkbox and radio.
     */
    private $_offsetCss;

    /**
     * @var array the default form configuration
     */
    private $_config = [
        self::TYPE_VERTICAL => [
    //        'labelSpan' => self::NOT_SET, // must be between 1 and 12
    //        'deviceSize' => self::NOT_SET, // must be one of the SIZE modifiers
            'showLabels' => true, // show or hide labels (mainly useful for inline type form)
            'showErrors' => true, // show or hide errors (mainly useful for inline type form)
            'showHints' => true  // show or hide hints below the input
        ],
        self::TYPE_HORIZONTAL => [
    //        'labelSpan' => self::DEFAULT_LABEL_SPAN,
    //        'deviceSize' => self::SIZE_MEDIUM,
            'showLabels' => true,
            'showErrors' => true,
            'showHints' => true,
        ],
        self::TYPE_INLINE => [
    //        'labelSpan' => self::NOT_SET,
    //        'deviceSize' => self::NOT_SET,
            'showLabels' => self::SCREEN_READER,
            'showErrors' => false,
            'showHints' => true,
        ],
    ];

    /**
     * Initializes the form configuration array
     * and parameters for the form.
     */
    protected function initForm()
    {
        // Enrica: 1.4.9b
        if (!isset($this->type) && isset($this->layout)){
            $this->type = $this->layout;
        }
        if ($this->type === 'default'){
            $this->type = self::TYPE_VERTICAL;
        }
        // end Enrica 1.4.9b
        if (!isset($this->type) || strlen($this->type) == 0) {
            $this->type = self::TYPE_VERTICAL;
        }
        //Enrica new 1.4.9a
        if (!in_array($this->type, [self::TYPE_VERTICAL, self::TYPE_HORIZONTAL, self::TYPE_INLINE])) {
            throw new InvalidConfigException('Invalid layout type: ' . $this->type);
        }
        // end Enrica

        $this->formConfig = array_replace_recursive($this->_config[$this->type], $this->formConfig);

        /* Enrica: Remove for fix #75
        if (!isset($this->fieldConfig['class'])) {
            $this->fieldConfig['class'] = ActiveField::className();
        }
        */
    //    $this->_inputCss = self::NOT_SET;
    //    $this->_offsetCss = self::NOT_SET;
        $class = 'form-' . $this->type;
        /* Fixes the button alignment for inline forms containing error block */
        if ($this->type === self::TYPE_INLINE && $this->formConfig['showErrors']) {
            $class .= ' ' . $class . '-block';
        }
        if ($this->type === self::TYPE_HORIZONTAL) {
            $class .= ' kv-form-horizontal';
        }
        Html::addCssClass($this->options, $class);
    }

    /**
     * Initializes the widget.
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        if (!is_int($this->fullSpan) && $this->fullSpan < 1) {
            throw new InvalidConfigException("The 'fullSpan' property must be a valid positive integer.");
        }
        $this->initForm();
    //    $config = $this->formConfig;
    //    $span = $config['labelSpan']; Enrica is done in getFormLayoutStyle already
    //    $size = $config['deviceSize'];
    //    $formStyle = $this->getFormLayoutStyle();
    //    $this->_labelCss = $formStyle['labelCss'];
    //    $this->_inputCss = $formStyle['inputCss'];
    //    $this->_offsetCss = $formStyle['offsetCss'];
    /**    
        if ($span != self::NOT_SET && intval($span) > 0) {
            $span = intval($span);

            if ($span <= 0 && $span >= $this->fullSpan) {
                $span = self::DEFAULT_LABEL_SPAN;
            }

            if ($size == self::NOT_SET) {
                $size = self::SIZE_MEDIUM;
            }

            $prefix = "col-{$size}-";
            $this->_labelCss = $prefix . $span;
            $this->_inputCss = $prefix . ($this->fullSpan - $span);
            $this->_offsetCss = "col-" . $size . "-offset-" . $span . " " . $this->_inputCss;
        }
    */
    /*  Enrica 1.4.9a: moved to ActiveField    
        if ($this->_inputCss == self::NOT_SET && empty($this->fieldConfig['template'])) {
            $this->fieldConfig['template'] = "{label}\n{input}\n{hint}\n{error}";
        }
     
     */

        parent::init();
        $this->registerAssets();
    }

    /* moved to ActiveField::initCssWrappers
    public function getFormLayoutStyle()
    {
        $config = $this->formConfig;
        $span = $config['labelSpan'];
        $size = $config['deviceSize'];
        $labelCss = $inputCss = $offsetCss = self::NOT_SET;

        if ($span != self::NOT_SET && intval($span) > 0) {
            $span = intval($span);

            if ($span <= 0 && $span >= $this->fullSpan) {
                $span = self::DEFAULT_LABEL_SPAN;
            }

            // Enrica 1.4.9a: add check valid values
            if ($size == self::NOT_SET || !in_array($size, [self::SIZE_TINY,
                    self::SIZE_SMALL, self::SIZE_MEDIUM, self::SIZE_LARGE])) {
                $size = self::SIZE_MEDIUM;
            }

            $prefix = "col-{$size}-";
            $labelCss = $prefix . $span;
            $inputCss = $prefix . ($this->fullSpan - $span);
            $offsetCss = "col-" . $size . "-offset-" . $span . " " . $inputCss;
        }
        return ['labelCss' => $labelCss, 'inputCss' => $inputCss, 'offsetCss' => $offsetCss];
    } */

    /**
     * Gets label css property
     *
     * @return string
     
    public function getLabelCss()
    {
        // Enrica change 1.4.9a: dynamic rebuild also after init
        //return $this->_labelCss;
        $formStyle = $this->getFormLayoutStyle();
        return $formStyle['labelCss'];
    }
*/
    /**
     * Sets label css property
     *
     * @param string $class
     */
    public function setLabelCss($class)
    {
       // $this->_labelCss = $class;
        $this->fieldConfig['horizontalCssClass']['label'] = $class;
    }
    

    /**
     * Gets input css property
     *
     * @return string
     
    public function getInputCss()
    {
        return $this->_inputCss;
    }
*/
    /**
     * Sets input css property
     *
     * @param string $class
     */
    public function setInputCss($class)
    {
        // $this->_inputCss = $class; Enrica: moved to ActiveField
        $this->fieldConfig['horizontalCssClass']['wrapper'] = $class;
    }

    /**
     * Checks if input css property is set
     *
     * @return bool
     
    public function hasInputCss()
    {
        return ($this->_inputCss != self::NOT_SET);
    }
*/
    /**
     * Gets offset css property
     *
     * @return string
    
    public function getOffsetCss()
    {
        return $this->_offsetCss;
    }
*/
    /**
     * Sets offset css property
     *
     * @param string $class
     */
    public function setOffsetCss($class)
    {
        //$this->_offsetCss = $class;
        $this->fieldConfig['horizontalCssClass']['offset'] = $class;
    }

    /**
     * Checks if offset css property is set
     *
     * @return bool
     */
    public function hasOffsetCss()
    {
        return ($this->_offsetCss != self::NOT_SET);
    }

    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $view = $this->getView();
        ActiveFormAsset::register($view);
        $id = 'jQuery("#' . $this->options['id'] . ' .kv-hint-special")';
        $view->registerJs('var $el='.$id.';if($el.length){$el.each(function(){$(this).activeFieldHint()});}');
    }
}