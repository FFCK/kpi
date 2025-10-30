<?php
/**
 * Smarty plugin
 *
 * Type:     modifier
 * Name:     config_key
 * Purpose:  Convert hyphens to underscores in config keys for Smarty 4 compatibility
 */
function smarty_modifier_config_key($string)
{
    return str_replace('-', '_', $string);
}
?>
