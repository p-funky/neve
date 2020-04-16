<?php
/**
 * Changleog Handler
 *
 * Handles parsing for Changelog files.
 */

namespace Neve_Dash;

/**
 * Class Changelog_Handler
 *
 * @package neve
 */
class Changelog_Handler {
	/**
	 * Get the parsed changelog.
	 *
	 * @param string $changelog_path the changelog path.
	 *
	 * @return array|null
	 */
	public function get_changelog( $changelog_path ) {

		if ( ! is_file( $changelog_path ) ) {
			return null;
		}

		return $this->parse_changelog( $changelog_path );
	}

	/**
	 * Return the releases changes array.
	 *
	 * @param string $changelog_path the changelog path.
	 *
	 * @return array $releases - changelog.
	 */
	private function parse_changelog( $changelog_path ) {
		WP_Filesystem();
		global $wp_filesystem;
		$changelog = $wp_filesystem->get_contents( $changelog_path );
		if ( is_wp_error( $changelog ) ) {
			$changelog = '';
		}
		$changelog = explode( PHP_EOL, $changelog );
		$releases  = [];

		foreach ( $changelog as $changelog_line ) {
			if ( strpos( $changelog_line, '**Changes:**' ) !== false || empty( $changelog_line ) ) {
				continue;
			}
			if ( substr( ltrim( $changelog_line ), 0, 3 ) === '###' ) {
				if ( isset( $release ) ) {
					$releases[] = $release;
				}

				preg_match( '/[0-99].[0-99].[0-99]/', $changelog_line, $found_v );
				preg_match( '/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/', $changelog_line, $found_d );
				$release = array(
					'version' => $found_v[0],
					'date'    => $found_d[0],
				);
			} else {
				if ( preg_match( '/[*|-]?\s?(\[fix]|\[Fix]|fix|Fix)[:]?\s?\b/', $changelog_line ) ) {
					$changelog_line     = preg_replace( '/[*|-]?\s?(\[fix]|\[Fix]|fix|Fix)[:]?\s?\b/', '', $changelog_line );
					$release['fixes'][] = trim( str_replace( '*', '', $changelog_line ) );
					continue;
				}

				if ( preg_match( '/[*|-]?\s?(\[feat]|\[Feat]|feat|Feat)[:]?\s?\b/', $changelog_line ) ) {
					$changelog_line        = preg_replace( '/[*|-]?\s?(\[feat]|\[Feat]|feat|Feat)[:]?\s?\b/', '', $changelog_line );
					$release['features'][] = trim( str_replace( [ '*', '-' ], '', $changelog_line ) );
					continue;
				}

				$changelog_line = trim( str_replace( [ '*', '-' ], '', $changelog_line ) );

				if ( empty( $changelog_line ) ) {
					continue;
				}

				$release['tweaks'][] = $changelog_line;
			}
		}
		return $releases;
	}

}
