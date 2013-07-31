//<?php
/**
 * SaveToSHK
 *
 * Save content of catalog products to Shopkeeper database table
 *
 * @category    plugin
 * @version     1.3
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @package     modx
 * @author      Andchir
 * @internal    @properties &template=Template id;string;13 &tv_price=Price TV ID;string;1
 * @internal    @events OnBeforeDocFormSave,OnDocFormPrerender
 * @internal    @disabled 1
 */

require_once MODX_BASE_PATH."assets/snippets/shopkeeper/module/shk_save_plugin.inc.php";
