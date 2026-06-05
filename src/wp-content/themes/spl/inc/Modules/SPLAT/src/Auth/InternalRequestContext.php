<?php
/**
 * Trusted marker for in-process REST dispatch.
 *
 * @package SPLAT\Auth
 */

namespace SPLAT\Auth;

defined( 'ABSPATH' ) || exit;

final class InternalRequestContext {

	/** @var int */
	private static int $depth = 0;

	/**
	 * Execute a callback within a trusted internal context.
	 *
	 * @template T
	 *
	 * @param callable(): T $callback
	 *
	 * @return T
	 */
	public static function run( callable $callback ): mixed {
		++self::$depth;

		try {
			return $callback();
		} finally {
			--self::$depth;
		}
	}

	/**
	 * Whether the current execution is inside a trusted internal context.
	 */
	public static function isActive(): bool {
		return self::$depth > 0;
	}
}

