Change Log: `yii2-widget-activeform`
====================================

## Version 1.5.7

**Date**: 27-Sep-2018

- (enh #115): Enhance rendering of Bootstrap 4.x custom file control.

## Version 1.5.6

**Date**: 27-Sep-2018

- Bump up version.

## Version 1.5.5

**Date**: 26-Sep-2018

- New `ActiveForm` methods `isHorizontal`, `isInline`, `isVertical` for easy layout detection.
- Label styling enhancements.

## Version 1.5.4

**Date**: 22-Sep-2018

- Refactor code via `kartik\base\BootstrapInterface`. 
- (enh #113): Enhance checkbox styling for BS3.

## Version 1.5.3

**Date**: 20-Sep-2018

- (enh #112): Enhance checkbox styling for enclosed label for both BS4 and BS3.
- (enh #111): Enhance BS3 checkbox styling.
- Enhance to use `Config::hasCssClass`.
- Better styling for Bootstrap 4.x hint block.
- (enh #109): Correct BS3 label styling and rendering for checkboxes and radios.
- (enh #108): Add bootstrap grid column css size map configuration.
- (enh #107): Add bootstrap 4 CSS highlight class for server validation errors.

## Version 1.5.2

**Date**: 05-Sep-2018

- Add BS4 custom checkbox & custom radio controls support.
- (kartik-v/yii2-krajee-base#94): Refactor code and consolidate / optimize properties within traits.
- Add Bootstrap button default CSS and icon prefix parsing.
- (enh #102): Enhance size modifier detection and input feedback icons.
- (bug #101): Correct `addClass` assignment for HTML5 inputs.
- (enh #100): Control ActiveField addons highlight for success & error states.
- (enh #99): Correct ActiveField wrapper templates when `skipFormLayout` is set to `true`.

## Version 1.5.1

**Date**: 16-Aug-2018

- (bug #98): Correct ActiveForm css variables init.

## Version 1.5.0

**Date**: 16-Aug-2018

- Implement AddonTrait.
- (enh #95): Add Bootstrap 4.x Support.
- (bug #94): Add missing comma in activeform css.
- Reorganize source code in `src` directory.
- (enh #91, #92): Correct validation for getting form layout style.
- Set krajee base dependency to v1.9.x.

## Version 1.4.9

**Date**: 05-Mar-2018

- (enh #89): Optimize and remove redundant code.
- (enh #88): Do not render addon content if empty.
- (enh #83): Correct PHPDoc to ensure correct return value for `ActiveForm::field()` method.
- (enh #82): Allow configuration of `itemOptions` for `checkboxButtonGroup` and `radioButtonGroup`.
- (enh #81): Change visibility of `$_pluginHintKeys`.
- (enh #79, #80): Allow configuration of multiple addons.
- (bug #78): Correct offset CSS class generation for horizontal forms.
- CSS enhancements for addons and other styling validation enhancements.
- Add contribution and issue/PR log templates.
- Enhance PHP Documentation for all classes and methods in the extension.
- (enh #76): Refactor code with additional enhancements for horizontal layout (with code support by Enrica):
    - ActiveForm changes
        - allow `formConfig` to be changed dynamically between `ActiveForm::begin` and `ActiveForm::end` and move `getFormLayoutStyle` to ActiveField
    - ActiveField changes
        - set default template moved from ActiveForm to ActiveField.initLayout()
          (template and css are properties of an ActiveField)
        - Enhance public options `labelSpan` and `deviceSize` on level ActiveField also
        - Defaulting of `labelSpan` and `deviceSize` Priority: 1. Option (fieldConfig),
           2. formConfig, 3. _settings (default)
        - Build CSS for label, offset and Input on level field
        - bug: Fix for checkbox/radio showLabels=>false
        - enh: New option `horizontalCssClasses` compatible with yii/bootstrap/ActiveForm with
               config options for `wrapper`, `label`, `error`, `hint`. These options give complete 
               control for all classes. `labelSpan` still works and `wrapper` is added if there
               is no `col-` tag defined.
        - enh: Add template with `{beginWrapper`}, `{endWrapper}` to enclose input, hint, error
        - enh: Optionally template `{label}` could be split into `{beginLabel}`,
               `{labelTitle}` and `{endLabel}` tag. `{label}` is still working as usual
- (bug #75): Allow `ActiveForm::fieldConfig` to be configured as Closure.
- (enh #72): Better hint container markup rendering.

## Version 1.4.8

**Date:** 28-Apr-2016

- (enh #74): Add branch alias for dev-master latest release.
- (bug #73): Correct dependency for `ActiveFormAsset`.

## Version 1.4.7

**Date:** 05-Dec-2015

- (bug #70): Correct `staticOnly` form render.
- (bug #67, #69): Fix typo for `HINT_DEFAULT`.

## Version 1.4.6

**Date:** 05-Dec-2015

- (enh #66): Better hint data fetch and code reformatting. Refer [updated docs and demo](http://demos.krajee.com/widget-details/active-field#input-hints).
- (bug #65): Fixes to staticOnly form rendering.
- (enh #64): Enhancement to display and style hints via icon popups or label hover
- (enh #61): Use model `getAttributeLabel()` as default in `initPlaceholder`.

## Version 1.4.5

**Date:** 22-Oct-2015

- (enh #60): Enhancements to `checkboxButtonGroup` and `radioButtonGroup`.
- (enh #59): Added .gitignore for composer stuff.


## Version 1.4.4

**Date:** 08-Jul-2015

- (enh #56): Implement feedback icons within inputs.

## Version 1.4.3

**Date:** 17-Jun-2015

- (enh #55): Set composer ## Version dependencies.

## Version 1.4.2

**Date:** 11-May-2015

- (enh #54): Set default ActiveForm field template to be consistent with yii\widgets\ActiveForm.
- (enh #49, #50): Updates to hint rendering for latest yii ActiveField upgrade.
- (enh #48): Various enhancements to Horizontal Form Layout Styles.
- (enh kartik-v/yii2-widgets#243): Enhance CSS style `kv-fieldset-inline`.
- (bug #46): Bootstrap input group addons for horizontal forms.
- (enh #42): New ActiveField property `skipFormLayout` to override and skip special form layout styling.
- (enh #41): New properties for adding or wrapping markup before LABEL, ERROR & HINT blocks.
- (enh #40): Initialize ActiveField template more correctly.
- (enh #39): Change ActiveField private properties to protected.
- (enh #38): Fix `autoPlaceholder` property for INLINE forms when `showLabels` is `true`.
- (enh #37): Scale inputs to full width in horizontal forms when `showLabels` is `ActiveForm:;SCREEN_READER`.
- (enh #36): Prevent offset of checkbox/radio labels for horizontal forms when `enclosedByLabel` is `false`.
- (bug #33): Correct autoPlaceholder based attribute label generation for tabular inputs.
- (enh #32): Create new `checkboxButtonGroup` & `radioButtonGroup` in ActiveField.

## Version 1.4.1

**Date:** 14-Feb-2015

- (enh #30): Add `control-label` class to labels for Vertical form.
- Set copyright year to current.

## Version 1.4.0

**Date:** 28-Jan-2015

- (enh #28): Enhancements for error and hint display for horizontal forms.
- (enh #27): New property `staticValue` in ActiveField.
- (enh #26): Enhance `ActiveField::staticInput` to include options to show error and hint.
- (enh #25): Default `showHints` to `true` for all form types in ActiveForm.
- (enh #24): Allow static data forms through new `ActiveForm::staticOnly` property.
- (enh #22): Enhance active field template for controlling labels, hints, & errors.
- (enh #21): Prevent display of error and hint blocks for static input.
- (enh #20): Ability to add markup before and after ActiveField Input.
- (enh #19): Add new `showHints` property to ActiveField configuration.

## Version 1.3.0

**Date:** 04-Dec-2014

- (enh #13): Allow `showLabels` property in ActiveForm & ActiveField to be tristate:
    - `true`: show labels
    - `false`: hide labels
    - `ActiveForm::SCREEN_READER`: show in screen reader only (hide from normal display)
- (enh #12): Include new `disabled` and `readonly` properties in ActiveForm.
- (enh #9): Enhance support for labels and horizontal form layouts
    - Allow labels to be set to `false` to hide them completely
    - Enhance HORIZONTAL forms to style labels appropriately when they are blank/empty.
    - Enhance HORIZONTAL forms to style labels, hints, and errors appropriately when they are set to false to fill the container width
    
## Version 1.2.0

**Date:** 26-Nov-2014

- (bug #7): Fix custom labels rendering for checkboxes
- Set release to stable

## Version 1.1.0

**Date:** 17-Nov-2014

- (enh #6): Fix incorrect alignment of inputs, buttons, and error block for INLINE FORM orientation.
- (enh #5): Add special styling for bootstrap input group button addons for success and error states.
- Clean up invalid assets, unneeded classes, and refactor code.
- (enh #1): Enhance ActiveField inputs to include bootstrap default styles.

## Version 1.0.0

**Date:** 08-Nov-2014

- Initial release 
- Sub repo split from [yii2-widgets](https://github.com/kartik-v/yii2-widgets)