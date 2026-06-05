<?php
/**
 * HD\Core\Helper — Compatibility shim for PLLModule and other hd-theme modules.
 *
 * The 2026-master PLLModule references HD\Core\Helper.
 * In the SPL theme the real implementation lives in HD\Utilities\Helpers\Helper.
 * This shim keeps the PLLModule source unchanged by delegating every static
 * call to the concrete implementation.
 *
 * @package HD\Core
 */

namespace HD\Core;

use HD\Utilities\Helpers\Helper as UtilHelper;

defined( 'ABSPATH' ) || exit;

class Helper extends UtilHelper {
	// All methods are inherited from UtilHelper.
	// No additional code needed — PHP class extension handles delegation.
}
