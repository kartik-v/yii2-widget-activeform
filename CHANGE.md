version 1.4.4
=============
**Date:** 08-Jul-2015

- (enh #56): Implement feedback icons within inputs.

version 1.4.3
=============
**Date:** 17-Jun-2015

- (enh #55): Set composer version dependencies.

version 1.4.2
=============
**Date:** 11-May-2015

- (enh #32): Create new `checkboxButtonGroup` & `radioButtonGroup` in ActiveField.
- (bug #33): Correct autoPlaceholder based attribute label generation for tabular inputs.
- (enh #36): Prevent offset of checkbox/radio labels for horizontal forms when `enclosedByLabel` is `false`.
- (enh #37): Scale inputs to full width in horizontal forms when `showLabels` is `ActiveForm:;SCREEN_READER`.
- (enh #38): Fix `autoPlaceholder` property for INLINE forms when `showLabels` is `true`.
- (enh #39): Change ActiveField private properties to protected.
- (enh #40): Initialize ActiveField template more correctly.
- (enh #41): New properties for adding or wrapping markup before LABEL, ERROR & HINT blocks.
- (enh #42): New ActiveField property `skipFormLayout` to override and skip special form layout styling.
- (bug #46): Bootstrap input group addons for horizontal forms.
- (enh kartik-v/yii2-widgets#243): Enhance CSS style `kv-fieldset-inline`.
- (enh #48): Various enhancements to Horizontal Form Layout Styles.
- (enh #49, #50): Updates to hint rendering for latest yii ActiveField upgrade.
- (enh #54): Set default ActiveForm field template to be consistent with yii\widgets\ActiveForm.

version 1.4.1
=============
**Date:** 14-Feb-2015

- (enh #30): Add `control-label` class to labels for Vertical form.
- Set copyright year to current.

version 1.4.0
=============
**Date:** 28-Jan-2015

- (enh #19): Add new `showHints` property to ActiveField configuration.
- (enh #20): Ability to add markup before and after ActiveField Input.
- (enh #21): Prevent display of error and hint blocks for static input.
- (enh #22): Enhance active field template for controlling labels, hints, & errors.
- (enh #24): Allow static data forms through new `ActiveForm::staticOnly` property.
- (enh #25): Default `showHints` to `true` for all form types in ActiveForm.
- (enh #26): Enhance `ActiveField::staticInput` to include options to show error and hint.
- (enh #27): New property `staticValue` in ActiveField.
- (enh #28): Enhancements for error and hint display for horizontal forms.

version 1.3.0
=============
**Date:** 04-Dec-2014

- (enh #9): Enhance support for labels and horizontal form layouts
    - Allow labels to be set to `false` to hide them completely
    - Enhance HORIZONTAL forms to style labels appropriately when they are blank/empty.
    - Enhance HORIZONTAL forms to style labels, hints, and errors appropriately when they are set to false to fill the container width
- (enh #12): Include new `disabled` and `readonly` properties in ActiveForm.
- (enh #13): Allow `showLabels` property in ActiveForm & ActiveField to be tristate:
    - `true`: show labels
    - `false`: hide labels
    - `ActiveForm::SCREEN_READER`: show in screen reader only (hide from normal display)
    
version 1.2.0
=============
**Date:** 26-Nov-2014

- (bug #7): Fix custom labels rendering for checkboxes
- Set release to stable

version 1.1.0
=============
**Date:** 17-Nov-2014

- (enh #1): Enhance ActiveField inputs to include bootstrap default styles.
- Clean up invalid assets, unneeded classes, and refactor code.
- (enh #5): Add special styling for bootstrap input group button addons for success and error states.
- (enh #6): Fix incorrect alignment of inputs, buttons, and error block for INLINE FORM orientation.

version 1.0.0
=============
**Date:** 08-Nov-2014

- Initial release 
- Sub repo split from [yii2-widgets](https://github.com/kartik-v/yii2-widgets)