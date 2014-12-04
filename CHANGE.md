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