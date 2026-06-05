<?php
/**
 * Core module interface for SPLAT.
 *
 * Every module MUST implement this interface to be auto-discovered.
 * boot() is the ONLY entry point — module OFF → boot() NOT called → zero footprint.
 *
 * @package SPLAT\Contracts
 */

namespace SPLAT\Contracts;

defined( 'ABSPATH' ) || exit;

interface ModuleInterface {

	/** Unique slug used as config key. */
	public static function slug(): string;

	/** Human-readable title for UI. */
	public static function title(): string;

	/** Short description. */
	public static function description(): string;

	/** Default option values. */
	public static function defaults(): array;

	/** Whether this module cannot be toggled off. */
	public static function alwaysActive(): bool;

	/** Register ALL WordPress hooks for this module. */
	public function boot(): void;
}

