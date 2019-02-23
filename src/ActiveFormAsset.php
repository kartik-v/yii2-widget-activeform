<?php

/**
 * @copyright  Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2019
 * @package    yii2-widgets
 * @subpackage yii2-widget-activeform
 * @version    1.5.8
 */

namespace kartik\form;

use kartik\base\PluginAssetBundle;

/**
 * Asset bundle for the [[ActiveForm]] widget and [[ActiveField]] component.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since  1.0
 */
class ActiveFormAsset extends PluginAssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->depends = array_merge($this->depends, [
            'yii\widgets\ActiveFormAsset',
        ]);
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/activeform']);
        $this->setupAssets('js', ['js/activeform']);
        parent::init();
    }
}
