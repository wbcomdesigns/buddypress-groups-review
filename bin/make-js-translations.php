<?php
/**
 * Generate JS translation JSON files with the filenames WordPress actually requests.
 *
 * Why this exists instead of plain `wp i18n make-json`:
 *
 * 1. WP-CLI 2.12.0's make-json corrupts source paths whose basename ends in
 *    "admin.js" - it emits "assets/js/a.js" for "assets/js/admin.js" and
 *    "assets/js/bgr-group-criteria-a.js" for "bgr-group-criteria-admin.js".
 *    The JSON filename is an md5 of that path, so WordPress looks up the md5
 *    of the REAL path, finds nothing, and silently serves English.
 *
 * 2. WordPress hashes the path of the file that is actually REGISTERED. This
 *    plugin registers minified assets unless SCRIPT_DEBUG is on, so a JSON
 *    generated only for the unminified path never matches in production.
 *    We emit both variants.
 *
 * Run after any change to the .po files:
 *
 *     php bin/make-js-translations.php
 *
 * Keep `languages/*.json` OUT of .gitignore - these files must ship in the
 * release zip or every JS string falls back to English.
 *
 * @package BuddyPress_Group_Reviews
 */

/*
 * This is a standalone CLI build script. It never bootstraps WordPress, so the
 * WP_Filesystem / wp_remote_get / wp_json_encode / output-escaping sniffs do
 * not apply - there is no WP runtime to borrow those helpers from, and the only
 * output is a build log on stdout.
 */

// phpcs:disable WordPress.WP.AlternativeFunctions
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
// phpcs:disable WordPress.WP.GlobalVariablesOverride

declare( strict_types = 1 );

$plugin_dir = dirname( __DIR__ );
$lang_dir   = $plugin_dir . '/languages';
$slug       = 'bp-group-reviews';

/**
 * Parse a .po file into entries with their source references.
 *
 * @param string $path Path to the .po file.
 * @return array{headers:array<string,string>,entries:array<int,array{msgid:string,msgstr:string,refs:array<int,string>}>}
 */
function bgr_parse_po( string $path ): array {
	$lines   = file( $path, FILE_IGNORE_NEW_LINES );
	$entries = array();
	$headers = array();
	$cur     = array(
		'msgid'  => null,
		'msgstr' => null,
		'refs'   => array(),
	);
	$mode    = null;

	// `#:` reference comments appear BEFORE their msgid, so they are buffered
	// here and handed to the entry that follows. Attaching them to $cur
	// directly would bind them to the PREVIOUS entry (off-by-one).
	$pending_refs = array();

	$flush = static function () use ( &$cur, &$entries, &$pending_refs ) {
		if ( null !== $cur['msgid'] && '' !== $cur['msgid'] && null !== $cur['msgstr'] && '' !== $cur['msgstr'] ) {
			$entries[] = $cur;
		}
		$cur          = array(
			'msgid'  => null,
			'msgstr' => null,
			'refs'   => $pending_refs,
		);
		$pending_refs = array();
	};

	$unquote = static function ( string $line ): string {
		if ( ! preg_match( '/"(.*)"\s*$/s', $line, $m ) ) {
			return '';
		}
		return stripcslashes( $m[1] );
	};

	foreach ( $lines as $line ) {
		if ( str_starts_with( $line, '#~' ) ) { // Obsolete entry - never compiled.
			$mode = null;
			continue;
		}
		if ( str_starts_with( $line, '#:' ) ) {
			foreach ( preg_split( '/\s+/', trim( substr( $line, 2 ) ) ) as $ref ) {
				if ( '' !== $ref ) {
					$pending_refs[] = $ref;
				}
			}
			continue;
		}
		if ( str_starts_with( $line, '#' ) ) {
			continue;
		}
		if ( str_starts_with( $line, 'msgid ' ) ) {
			$flush();
			$cur['msgid'] = $unquote( $line );
			$mode         = 'msgid';
			continue;
		}
		if ( str_starts_with( $line, 'msgstr ' ) ) {
			$cur['msgstr'] = $unquote( $line );
			$mode          = 'msgstr';
			continue;
		}
		if ( str_starts_with( $line, 'msgid_plural' ) || str_starts_with( $line, 'msgstr[' ) ) {
			$mode = null; // Plurals are not emitted for JS here.
			continue;
		}
		if ( str_starts_with( $line, '"' ) && $mode ) {
			$cur[ $mode ] .= $unquote( $line );
			continue;
		}
		if ( '' === trim( $line ) ) {
			$mode = null;
		}
	}
	$flush();

	// Header block is the entry with an empty msgid; re-read it directly.
	if ( preg_match_all( '/^"([A-Za-z-]+):\s*(.*?)\\\\n"$/m', file_get_contents( $path ), $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $set ) {
			$headers[ $set[1] ] = $set[2];
		}
	}

	return array(
		'headers' => $headers,
		'entries' => $entries,
	);
}

// Discover every .po and every JS file referenced by them.
$po_files = glob( $lang_dir . '/' . $slug . '-*.po' );
if ( ! $po_files ) {
	fwrite( STDERR, "No .po files found in {$lang_dir}\n" );
	exit( 1 );
}

$written = 0;
$report  = array();

foreach ( $po_files as $po ) {
	if ( ! preg_match( '/-([a-z]{2}(?:_[A-Z]{2})?)\.po$/', basename( $po ), $m ) ) {
		continue;
	}
	$loc    = $m[1];
	$parsed = bgr_parse_po( $po );

	// Group translations by the JS file they came from.
	$by_js = array();
	foreach ( $parsed['entries'] as $e ) {
		foreach ( $e['refs'] as $ref ) {
			$file = preg_replace( '/:\d+$/', '', $ref );
			if ( ! str_ends_with( $file, '.js' ) ) {
				continue;
			}
			$by_js[ $file ][ $e['msgid'] ] = array( $e['msgstr'] );
		}
	}

	foreach ( $by_js as $js => $messages ) {
		if ( ! file_exists( $plugin_dir . '/' . $js ) ) {
			fwrite( STDERR, "  ! .po references a missing JS file: {$js}\n" );
			continue;
		}

		// WordPress hashes whichever file is REGISTERED. Emit the unminified
		// path and, when it exists, the minified path used in production.
		$emit_paths = array( $js );
		$min        = preg_replace( '#([^/]+)\.js$#', 'min/$1.min.js', $js );
		if ( file_exists( $plugin_dir . '/' . $min ) ) {
			$emit_paths[] = $min;
		}

		foreach ( $emit_paths as $target ) {
			$payload = array(
				'translation-revision-date' => $parsed['headers']['PO-Revision-Date'] ?? gmdate( 'c' ),
				'generator'                 => 'bin/make-js-translations.php',
				'source'                    => $target,
				'domain'                    => 'messages',
				'locale_data'               => array(
					'messages' => array_merge(
						array(
							'' => array(
								'domain'       => 'messages',
								'lang'         => $parsed['headers']['Language'] ?? $loc,
								'plural-forms' => $parsed['headers']['Plural-Forms'] ?? 'nplurals=2; plural=(n != 1);',
							),
						),
						$messages
					),
				),
			);

			$out = sprintf( '%s/%s-%s-%s.json', $lang_dir, $slug, $loc, md5( $target ) );
			file_put_contents( $out, wp_json_encode_compat( $payload ) );
			++$written;
			$report[ $loc ][] = sprintf( '%s (%d strings) -> %s', $target, count( $messages ), basename( $out ) );
		}
	}
}

/**
 * Encode JSON the way WP-CLI does (unescaped slashes and unicode).
 *
 * @param mixed $data Data to encode.
 * @return string
 */
function wp_json_encode_compat( $data ): string {
	return (string) json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
}

foreach ( $report as $loc => $items ) {
	echo "{$loc}:\n";
	foreach ( $items as $item ) {
		echo "  {$item}\n";
	}
}
echo "\nWrote {$written} JSON file(s).\n";
