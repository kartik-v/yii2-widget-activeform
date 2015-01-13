<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-widgets
 * @subpackage yii2-widget-activeform
 * @version 1.3.0
 */

namespace kartik\form;

use yii\web\AssetBundle;

/**
 * Asset bundle for ActiveForm Widget
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class ActiveFormAsset extends AssetBundle
{
    public $sourcePath = '@vendor/kartik-v/yii2-widget-activeform/assets';

    public $css = [
        'css/activeform.css'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
