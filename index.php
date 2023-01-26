<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023 Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   1.0.0
 */
header('Expires: ' . gmdate('D, d M Y H:i:s', time() - 60) . ' GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

header('Location: ../', true, 301);
