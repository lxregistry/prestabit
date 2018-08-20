<?php
/**
* 2018 LX
*
* NOTICE OF LICENSE
*
*  @author    LX
*  @copyright 2018 LX
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_0($module)
{
    $module->registerHook('displayOrderDetail');

    return true;
}
