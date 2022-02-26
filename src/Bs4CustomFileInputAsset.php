<?php

/**
 * @copyright  Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2022
 * @package    yii2-widgets
 * @subpackage yii2-widget-activeform
 * @version    1.6.2
 */

namespace kartik\form;

use kartik\base\PluginAssetBundle;

/**
 * Asset bundle for the custom file input animation for bootstrap 4.x.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since  1.0
 */
class Bs4CustomFileInputAsset extends PluginAssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('js', ['js/bs-custom-file-input']);
        parent::init();
    }
}
