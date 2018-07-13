<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class The_SEO_Framework\Generate_Description
 *
 * Generates Description SEO data based on content.
 *
 * @since 2.8.0
 */
class Generate_Description extends Generate {

	/**
	 * Constructor, loads parent constructor.
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Returns the meta description from custom fields. Falls back to autogenerated description.
	 *
	 * @since 3.0.6
	 * @uses $this->get_description_from_custom_field()
	 * @uses $this->get_generated_description()
	 *
	 * @param int|null $id     The post or term ID. Falls back to queried ID if null.
	 * @param bool     $escape Whether to escape the description.
	 * @return string The real description output.
	 */
	public function get_description( $id = null, $escape = true ) {

		if ( is_null( $id ) )
			$id = $this->get_the_real_ID();

		$desc = $this->get_description_from_custom_field( $id )
			 ?: $this->get_generated_description( $id, false );

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the custom user-inputted description.
	 *
	 * @since 3.0.6
	 *
	 * @param int|null $id The post or term ID. Falls back to queried ID if null.
	 * @param bool     $escape Whether to escape the description.
	 * @return string The custom field description.
	 */
	public function get_description_from_custom_field( $id = null, $escape = true ) {

		if ( is_null( $id ) )
			$id = $this->get_the_real_ID();

		$desc = '';

		if ( $this->is_front_page_by_id( $id ) ) {
			$desc = $this->get_option( 'homepage_description' );
		}
		if ( ! $desc ) {
			if ( $this->is_singular( $id ) ) {
				$desc = $this->get_custom_field( '_genesis_description', $id );
			} elseif ( $id ) {
				$data = $this->get_term_meta( $id );
				$desc = ! empty( $data['description'] ) ? $data['description'] : '';
			}
		}

		/**
		 * Applies filters 'the_seo_framework_custom_field_description' : string
		 *
		 * Filters the description from custom field, if any.
		 *
		 * @since 2.9.0
		 * @since 3.0.6 1. Duplicated from $this->generate_description() (to be deprecated)
		 *              2. Removed all arguments but the 'id' argument.
		 *
		 * @param string $desc The description.
		 * @param array  $args The description arguments.
		 */
		$desc = (string) \apply_filters( 'the_seo_framework_custom_field_description', $desc, [ 'id' => $id ] );

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Twitter meta description. Falls back to Open Graph description.
	 *
	 * @since 3.0.4
	 * @uses $this->get_open_graph_description()
	 *
	 * @param int|null $id     The post or term ID. Falls back to queried ID if null.
	 * @param bool     $escape Whether to escape the description.
	 * @return string The real Twitter description output.
	 */
	public function get_twitter_description( $id = null, $escape = true ) {

		if ( is_null( $id ) )
			$id = $this->get_the_real_ID();

		$desc = $this->get_custom_field( '_twitter_description', $id )
			 ?: $this->get_open_graph_description( $id, false );

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Open Graph meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 * @uses $this->get_generated_open_graph_description()
	 *
	 * @param int|null $id     The post or term ID. Falls back to queried ID if null.
	 * @param bool     $escape Whether to escape the description.
	 * @return string The real Open Graph description output.
	 */
	public function get_open_graph_description( $id = null, $escape = true ) {

		if ( is_null( $id ) )
			$id = $this->get_the_real_ID();

		$desc = $this->get_custom_field( '_open_graph_description', $id )
			 ?: $this->get_generated_open_graph_description( $id, false );

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the autogenerated meta description.
	 *
	 * @since 3.0.6
	 * @uses $this->generate_description()
	 * @staticvar array $cache
	 *
	 * @param int|null $id     The post or term ID. Falls back to queried ID if null.
	 * @param bool     $escape Whether to escape the description.
	 * @return string The generated description output.
	 */
	public function get_generated_description( $id = null, $escape = true ) {

		if ( is_null( $id ) )
			$id = $this->get_the_real_ID();

		static $cache = [];

		$desc = isset( $cache[ $id ] )
			  ? $cache[ $id ]
			  : $cache[ $id ] = $this->generate_description( '', [ 'id' => $id, 'social' => false, 'get_custom_field' => false, 'escape' => false ] );

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the autogenerated Twitter meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 * @uses $this->get_generated_open_graph_description()
	 *
	 * @param int|null $id     The post or term ID. Falls back to queried ID if null.
	 * @param bool     $escape Whether to escape the description.
	 * @return string The generated Twitter description output.
	 */
	public function get_generated_twitter_description( $id = null, $escape = true ) {
		return $this->get_generated_open_graph_description( $id, $escape );
	}

	/**
	 * Returns the autogenerated Open Graph meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 * @uses $this->generate_description()
	 * @staticvar array $cache
	 *
	 * @param int|null $id     The post or term ID. Falls back to queried ID if null.
	 * @param bool     $escape Whether to escape the description.
	 * @return string The generated Open Graph description output.
	 */
	public function get_generated_open_graph_description( $id = null, $escape = true ) {

		if ( is_null( $id ) )
			$id = $this->get_the_real_ID();

		static $cache = [];

		$desc = isset( $cache[ $id ] )
			  ? $cache[ $id ]
			  : $cache[ $id ] = $this->generate_description( '', [ 'id' => $id, 'social' => true, 'escape' => false ] );

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Creates description. Base function.
	 *
	 * @since 1.0.0
	 * @since 2.9.0 Added two filters.
	 * @since 3.0.6 Silently deprecated.
	 * @deprecated Use `get_description()` instead.
	 * @deprecated Use `get_generated_description()` instead.
	 *
	 * @param string $description The optional description to simply parse.
	 * @param array $args description args : {
	 *    @param int $id the term or page id.
	 *    @param string $taxonomy taxonomy name.
	 *    @param bool $is_home We're generating for the home page.
	 *    @param bool $get_custom_field Do not fetch custom title when false.
	 *    @param bool $social Generate Social Description when true.
	 * }
	 * @return string The description
	 */
	public function generate_description( $description = '', $args = [] ) {

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		$args = $this->reparse_description_args( $args );

		if ( $args['get_custom_field'] && empty( $description ) ) {
			//* Fetch from options, if any.
			$description = $this->description_from_custom_field( $args, false );

			/**
			 * Applies filters 'the_seo_framework_custom_field_description' : string
			 *
			 * Filters the description from custom field, if any.
			 *
			 * @since 2.9.0
			 * NOTE: MOVED!!
			 * @see get_description_from_custom_field()
			 *
			 * @param string $description The description.
			 * @param array $args The description arguments.
			 */
			$description = (string) \apply_filters( 'the_seo_framework_custom_field_description', $description, $args );

			//* We've already checked the custom fields, so let's remove the check in the generation.
			$args['get_custom_field'] = false;
		}

		//* Still no description found? Create an auto description based on content.
		if ( empty( $description ) || false === is_scalar( $description ) ) {
			$description = $this->generate_description_from_id( $args, false );

			/**
			 * Applies filters 'the_seo_framework_generated_description' : string
			 *
			 * Filters the generated description, if any.
			 *
			 * @since 2.9.0
			 *
			 * @param string $description The description.
			 * @param array $args The description arguments.
			 */
			$description = (string) \apply_filters( 'the_seo_framework_generated_description', $description, $args );
		}

		/**
		 * Applies filters 'the_seo_framework_do_shortcodes_in_description' : Boolean
		 * @since 2.6.6
		 */
		if ( \apply_filters( 'the_seo_framework_do_shortcodes_in_description', false ) )
			$description = \do_shortcode( $description );

		if ( $args['escape'] )
			$description = $this->escape_description( $description );

		return $description;
	}

	/**
	 * Parses and sanitizes description arguments.
	 *
	 * @since 2.5.0
	 * @since 3.0.6 Silently deprecated.
	 * @deprecated
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 * @return array $args parsed args.
	 */
	public function parse_description_args( $args = [], $defaults = [], $get_defaults = false ) {

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) {
			$defaults = [
				'id'               => $this->get_the_real_ID(),
				'taxonomy'         => '',
				'is_home'          => false,
				'get_custom_field' => true,
				'social'           => false,
				'escape'           => true,
			];

			/**
			 * Applies filters 'the_seo_framework_description_args' : array {
			 *    @param int $id the term or page id.
			 *    @param string $taxonomy taxonomy name.
			 *    @param bool $is_home We're generating for the home page.
			 *    @param bool $get_custom_field Do not fetch custom title when false.
			 *    @param bool $social Generate Social Description when true.
			 *    @param bool $escape Whether to escape the description.
			 * }
			 *
			 * @since 2.5.0
			 * @since 3.0.4 Added escape parameter.
			 * @since 3.0.6 Silently deprecated.
			 * @deprecated
			 *
			 * @param array $defaults The description defaults.
			 * @param array $args The input args.
			 */
			$defaults = (array) \apply_filters( 'the_seo_framework_description_args', $defaults, $args );
		}

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		// phpcs:disable -- it's ok.
		$args['id']               = isset( $args['id'] )               ? (int) $args['id']                : $defaults['id'];
		$args['taxonomy']         = isset( $args['taxonomy'] )         ? (string) $args['taxonomy']       : $defaults['taxonomy'];
		$args['is_home']          = isset( $args['is_home'] )          ? (bool) $args['is_home']          : $defaults['is_home'];
		$args['get_custom_field'] = isset( $args['get_custom_field'] ) ? (bool) $args['get_custom_field'] : $defaults['get_custom_field'];
		$args['social']           = isset( $args['social'] )           ? (bool) $args['social']           : $defaults['social'];
		$args['escape']           = isset( $args['escape'] )           ? (bool) $args['escape']           : $defaults['escape'];
		// phpcs:enable

		return $args;
	}

	/**
	 * Reparses description args.
	 *
	 * @since 2.6.0
	 * @since 2.9.2 Now passes args to filter.
	 * @since 3.0.6 Silently deprecated.
	 * @deprecated
	 *
	 * @param array $args required The passed arguments.
	 * @return array $args parsed args.
	 */
	public function reparse_description_args( $args = [] ) {

		$default_args = $this->parse_description_args( $args, '', true );

		if ( is_array( $args ) ) {
			if ( empty( $args ) ) {
				$args = $default_args;
			} else {
				$args = $this->parse_description_args( $args, $default_args );
			}
		} else {
			//* Old style parameters are used. Doing it wrong.
			$this->_doing_it_wrong( __METHOD__, 'Use $args = [] for parameters.', '2.5.0' );
			$args = $default_args;
		}

		return $args;
	}

	/**
	 * Creates description from custom fields.
	 *
	 * @since 2.4.1
	 * @since 3.0.6 Silently deprecated.
	 * @deprecated Use `get_description_from_custom_field()` instead.
	 *
	 * @param array $args description args : {
	 *    @param int $id the term or page id.
	 *    @param string $taxonomy taxonomy name.
	 *    @param bool $is_home We're generating for the home page.
	 * }
	 * @param bool $escape Escape the output if true.
	 * @return string|mixed The description.
	 */
	public function description_from_custom_field( $args = [], $escape = true ) {

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		$args = $this->reparse_description_args( $args );

		//* HomePage Description.
		$description = $this->get_custom_homepage_description( $args );

		if ( empty( $description ) ) {
			if ( $this->is_archive() ) {
				$description = $this->get_custom_archive_description( $args );
			} else {
				$description = $this->get_custom_singular_description( $args['id'] );
			}
		}

		if ( $escape )
			$description = $this->escape_description( $description );

		return $description;
	}

	/**
	 * Fetches HomePage Description from custom field.
	 *
	 * @since 2.6.0
	 * @since 2.9.0 1. Removed $args['taxonomy'] check.
	 *              2. Added $this->is_archive() check.
	 * @since 3.0.6 Silently deprecated.
	 * @deprecated Use `get_description_from_custom_field()` instead.
	 *
	 * @param array $args Description args.
	 * @return string The Description
	 */
	protected function get_custom_homepage_description( $args ) {

		$description = '';

		//= Dumb query. FIXME
		if ( $args['is_home'] || $this->is_real_front_page() || ( ! $this->is_archive() && $this->is_static_frontpage( $args['id'] ) ) )
			$description = $this->get_option( 'homepage_description' ) ?: '';

		return $description;
	}

	/**
	 * Fetches Singular Description from custom field.
	 *
	 * @since 2.6.0
	 * @since 3.0.6 Silently deprecated.
	 * @deprecated Use `get_description_from_custom_field()` instead.
	 *
	 * @param int $id The page ID.
	 * @return string The Description
	 */
	protected function get_custom_singular_description( $id ) {

		$description = '';

		if ( $this->is_singular( $id ) ) {
			$description = $this->get_custom_field( '_genesis_description', $id ) ?: '';
		}

		return $description;
	}

	/**
	 * Fetch Archive Description from custom field.
	 *
	 * @since 2.6.0
	 * @since 3.0.6 Silently deprecated.
	 * @deprecated Use `get_description_from_custom_field()` instead.
	 *
	 * @param array $args
	 * @return string The Description
	 */
	protected function get_custom_archive_description( $args ) {

		$description = '';
		$is_term = false;

		if ( $args['taxonomy'] && $args['id'] ) {
			$is_term = (bool) \get_term( $args['id'], $args['taxonomy'] );
		}

		if ( $is_term || $this->is_archive() ) {
			$data = $this->get_term_meta( $args['id'] );
			$description = empty( $data['description'] ) ? '' : $data['description'];
		}

		return $description;
	}

	/**
	 * Generates description from content while parsing filters.
	 *
	 * @since 2.3.3
	 * @since 3.0.0 No longer checks for protected posts.
	 *              Check is moved to $this->generate_the_description().
	 * @since 3.0.6 Silently deprecated.
	 * @deprecated Use `get_generated_description()` instead.
	 *
	 * @param array $args description args : {
	 *    @param int $id the term or page id.
	 *    @param string $taxonomy taxonomy name.
	 *    @param bool $is_home Whether we're generating for the home page.
	 *    @param bool $get_custom_field Do not fetch custom title when false.
	 *    @param bool $social Generate Social Description when true.
	 * }
	 * @param bool $escape Escape output when true.
	 * @return string $output The description.
	 */
	public function generate_description_from_id( $args = [], $escape = true ) {

		/**
		 * Applies filters bool 'the_seo_framework_enable_auto_description'
		 *
		 * @since 2.5.0
		 * @since 3.0.0 Now passes $args as the second parameter.
		 * @param bool  $autodescription Enable or disable the automated descriptions.
		 * @param array $args            The description arguments.
		 */
		$autodescription = (bool) \apply_filters( 'the_seo_framework_enable_auto_description', true, $args );
		if ( false === $autodescription )
			return '';

		$description = $this->generate_the_description( $args, false );

		if ( $escape )
			$description = $this->escape_description( $description );

		return (string) $description;
	}

	/**
	 * Generates description from content.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 : The output is always trimmed if $escape is false.
	 *              : The cache will no longer be maintained on previews or search.
	 * @since 3.0.0 : Now checks for protected posts.
	 * @since 3.0.6 Silently deprecated.
	 * @since 3.1.0 Removed the redundant preview check.
	 * @deprecated Use `get_generated_description()` instead.
	 *
	 * @param array $args description args : {
	 *    @param int $id the term or page id.
	 *    @param string $taxonomy taxonomy name.
	 *    @param bool $is_home We're generating for the home page.
	 *    @param bool $get_custom_field Do not fetch custom title when false.
	 *    @param bool $social Generate Social Description when true.
	 * }
	 * @param bool $escape Whether to escape the description.
	 *        NOTE: When this is false, be sure to trim the output.
	 * @return string The description.
	 */
	protected function generate_the_description( $args, $escape = true ) {

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		$args = $this->reparse_description_args( $args );

		//* Home Page description
		if ( $args['is_home'] || $this->is_real_front_page() || $this->is_front_page_by_id( $args['id'] ) )
			return $this->generate_home_page_description( $args['get_custom_field'], $escape );

		//* If the post is protected, don't generate a description.
		if ( $this->is_protected( $args['id'] ) )
			return '';

		/**
		 * Determines whether to prevent caching of transients.
		 * @since 2.8.0
		 */
		$_special_q = ( ! $this->is_admin() && $this->is_search() );
		$use_cache  = ! $_special_q && $this->is_option_checked( 'cache_meta_description' );

		/**
		 * Setup transient.
		 */
		$use_cache and $this->setup_auto_description_transient( $args['id'], $args['taxonomy'] );

		$term = $this->fetch_the_term( $args['id'] );

		/**
		 * @since 2.8.0: Added check for option 'cache_meta_description'.
		 */
		$excerpt = $use_cache ? $this->get_transient( $this->auto_description_transient ) : false;
		if ( false === $excerpt ) {
			$excerpt = [];

			/**
			 * @since 2.8.0:
			 *    1. Added check for option 'cache_meta_description' and search/preview.
			 *    2. Moved generation functions in two different methods.
			 */
			if ( $use_cache ) {
				$excerpt_normal = $this->get_description_excerpt_normal( $args['id'], $term );

				$excerpt['normal'] = $excerpt_normal['excerpt'];
				$excerpt['trim'] = $excerpt_normal['trim'];
				$excerpt['social'] = $this->get_description_excerpt_social( $args['id'], $term );

				/**
				 * Transient expiration: 1 week.
				 * Keep the description for at most 1 week.
				 */
				$expiration = WEEK_IN_SECONDS;

				$this->set_transient( $this->auto_description_transient, $excerpt, $expiration );
			} elseif ( $args['social'] ) {
				$excerpt['social'] = $this->get_description_excerpt_social( $args['id'], $term );
			} else {
				$excerpt_normal = $this->get_description_excerpt_normal( $args['id'], $term );

				$excerpt['normal'] = $excerpt_normal['excerpt'];
				$excerpt['trim'] = $excerpt_normal['trim'];
			}
		}

		/**
		 * Check for Social description, don't add blogname then.
		 * Also continues normally if it's the front page.
		 *
		 * @since 2.5.0
		 */
		if ( $args['social'] ) {
			//* No social description if nothing is found.
			$description = $excerpt['social'] ? $excerpt['social'] : '';
		} else {
			if ( $excerpt['normal'] ) {
				if ( $excerpt['trim'] ) {
					$description = $excerpt['normal'];
				} else {
					if ( $term || ! \has_excerpt( $args['id'] ) ) {
						$additions = $this->generate_description_additions( $args['id'], $term, false );

						$title_on_blogname = $this->get_title_on_blogname( $additions );
						$sep = $additions['sep'];
					} else {
						$title_on_blogname = $sep = '';
					}

					/* translators: 1: Title, 2: Separator, 3: Excerpt */
					$description = sprintf( \_x( '%1$s %2$s %3$s', '1: Title, 2: Separator, 3: Excerpt', 'autodescription' ), $title_on_blogname, $sep, $excerpt['normal'] );
				}
			} else {
				//* We still add the additions when no excerpt has been found.
				// i.e. home page or empty/shortcode filled page.
				$description = $this->get_title_on_blogname(
					$this->generate_description_additions( $args['id'], $term, true )
				);
			}
		}

		if ( $escape ) {
			$description = $this->escape_description( $description );
		} else {
			$description = trim( $description );
		}

		return $description;
	}

	/**
	 * Returns the generated description excerpt array for the normal description tag.
	 *
	 * @since 2.8.0
	 * @since 3.0.4 Now uses 300 characters instead of 155.
	 *
	 * @param int $id The post/term ID.
	 * @param bool|object The term object.
	 * @return array {
	 *    'excerpt' => string The excerpt. Unescaped.
	 *    'trim' => bool Whether to trim the additions.
	 * }
	 */
	public function get_description_excerpt_normal( $id = 0, $term = false ) {

		$additions = '';
		if ( $term || ! \has_excerpt( $id ) ) {
			$additions = $this->get_title_on_blogname(
				$this->generate_description_additions( $id, $term, false )
			);
		}

		//* If there are additions, add a trailing space.
		if ( $additions )
			$additions .= ' ';

		$additions_length = $additions ? mb_strlen( html_entity_decode( $additions ) ) : 0;
		/**
		 * Determine if the title is far too long (72+, rather than 75 in the Title guidelines).
		 * If this is the case, trim the "title on blogname" part from the description.
		 * @since 2.8.0
		 * @since 3.0.4 Increased to basis 300, from 155.
		 */
		if ( $additions_length > 71 ) {
			$max_char_length = 300;
			$trim = true;
		} else {
			$max_char_length = 300 - $additions_length;
			$trim = false;
		}

		$excerpt_normal = $this->generate_excerpt( $id, $term, $max_char_length );

		/**
		 * Put in array to be accessed later.
		 * @since 2.8.0 Added trim value.
		 */
		return [
			'excerpt' => $excerpt_normal,
			'trim'    => $trim,
		];
	}

	/**
	 * Returns the generated description excerpt for the social description tag.
	 *
	 * @since 2.8.0
	 *
	 * @param int $id The post/term ID.
	 * @param bool|object The term object.
	 * @return string The social description excerpt. Unescaped.
	 */
	public function get_description_excerpt_social( $id = 0, $term = false ) {

		$max_char_length = 200;

		$excerpt_social = $this->generate_excerpt( $id, $term, $max_char_length );

		return $excerpt_social;
	}

	/**
	 * Generates the home page description.
	 *
	 * @since 2.6.0
	 * @since 3.0.6 Silently deprecated.
	 * @deprecated Use `generate_description()` in combination with `get_custom_field( '_genesis_description' )` instead.
	 *
	 * @param bool $custom_field whether to check the Custom Field.
	 * @param bool $escape Whether to escape the output.
	 * @return string The description.
	 */
	public function generate_home_page_description( $custom_field = true, $escape = true ) {

		$id = $this->get_the_front_page_ID();

		/**
		 * Return early if description is found from Home Page Settings.
		 * Only do so when $args['get_custom_field'] is true.
		 * @since 2.3.4
		 */
		$description = $custom_field ? $this->get_custom_homepage_description( [ 'is_home' => true ] ) : '';

		if ( ! $description ) {
			$description = $this->get_title_on_blogname(
				$this->generate_description_additions( $id, '', true )
			);
		}

		return $escape ? $this->escape_description( $description ) : $description;
	}

	/**
	 * Determines whether to add description additions. (╯°□°）╯︵ ┻━┻
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Removed cache.
	 *              Whether an excerpt is available is no longer part of this check.

	 * @param int $id The current page or post ID.
	 * @param object|string $term The current Term.
	 * @return bool Whether to add description additions.
	 */
	public function add_description_additions( $id = '', $term = '' ) {

		/**
		 * Applies filters the_seo_framework_add_description_additions : {
		 *    @param bool true to add prefix.
		 *    @param int $id The Term object ID or The Page ID.
		 *    @param object $term The Term object.
		 * }
		 * @since 2.6.0
		 */
		$filter = \apply_filters( 'the_seo_framework_add_description_additions', true, $id, $term );
		$option = $this->get_option( 'description_additions' );

		return $option && $filter;
	}

	/**
	 * Gets Description Separator.
	 *
	 * Applies filters 'the_seo_framework_description_separator' : string
	 * @since 2.3.9
	 * @staticvar string $sep
	 *
	 * @return string The Separator, unescaped.
	 */
	public function get_description_separator() {

		static $sep = null;

		if ( isset( $sep ) )
			return $sep;

		return $sep = (string) \apply_filters(
			'the_seo_framework_description_separator',
			$this->get_separator( 'description', false )
		);
	}

	/**
	 * Returns translation string for "Title on Blogname".
	 *
	 * @since 2.8.0
	 * @since 3.1.0 Now returns empty if 'title' is omitted.
	 * @see $this->generate_description_additions()
	 *
	 * @param array $additions The description additions.
	 * @return string The description additions.
	 */
	protected function get_title_on_blogname( $additions ) {

		if ( empty( $additions['title'] ) )
			return '';

		$title    = $additions['title'];
		$on       = $additions['on'];
		$blogname = $additions['blogname'];

		/* translators: 1: Title, 2: on, 3: Blogname */
		return trim( sprintf( \_x( '%1$s %2$s %3$s', 'autodescription' ), $title, $on, $blogname ) );
	}

	/**
	 * Generates description additions.
	 *
	 * @since 2.6.0
	 * @since 2.9.2 Added filter.
	 * @since 3.0.6 The $ignore parameter is now considered in caching.
	 * @staticvar array $title string of titles.
	 * @staticvar string $on
	 * @access private
	 * @TODO deprecate and rewrite -- or completely remove.
	 * @see https://github.com/sybrew/the-seo-framework/issues/282
	 *
	 * @param int $id The post or term ID
	 * @param object|string $term The term object
	 * @param bool $ignore Whether to ignore options and filters.
	 * @return array : {
	 *    $title    => The title
	 *    $on       => The word separator
	 *    $blogname => The blogname
	 *    $sep      => The separator
	 * }
	 */
	public function generate_description_additions( $id = 0, $term = '', $ignore = false ) {

		static $title = [];

		if ( $ignore || $this->add_description_additions( $id, $term ) ) {

			if ( ! isset( $title[ $id ][ $ignore ] ) ) {
				$title[ $id ][ $ignore ] = $this->generate_description_title( $id, $term );
			}

			if ( $ignore || $this->is_option_checked( 'description_blogname' ) ) {

				static $on = null;
				if ( is_null( $on ) ) {
					/* translators: Front-end output. */
					$on = \_x( 'on', 'Placement. e.g. Post Title "on" Blog Name', 'autodescription' );
				}

				//* Already cached.
				$blogname = $this->get_blogname();
			} else {
				$on = '';
				$blogname = '';
			}

			//* Already cached.
			$sep = $this->get_description_separator();
		} else {
			$title[ $id ][ $ignore ] = '';
			$on = '';
			$blogname = '';
			$sep = '';
		}

		if ( \has_filter( 'the_seo_framework_generated_description_additions' ) ) {
			/**
			 * Applies filters 'the_seo_framework_generated_description_additions'
			 *
			 * @since 2.9.2
			 *
			 * @param array $data   The description data.
			 * @param int   $id     The object ID.
			 * @param mixed $term   The term object, or empty (falsy).
			 * @param bool  $ignore Whether the settings have been ignored.
			 */
			$data = \apply_filters_ref_array( 'the_seo_framework_generated_description_additions', [
				[
					'title'    => $title[ $id ][ $ignore ],
					'on'       => $on,
					'blogname' => $blogname,
					'sep'      => $sep,
				],
				$id,
				$term,
				$ignore,
			] );
		} else {
			$data = [
				'title'    => $title[ $id ][ $ignore ],
				'on'       => $on,
				'blogname' => $blogname,
				'sep'      => $sep,
			];
		}

		return $data;
	}

	/**
	 * Generates the Title for description.
	 *
	 * @since 2.5.2
	 * @since 3.0.6 Silently deprecated.
	 * @since 3.1.0 1. Removed third parameter, it's now queried.
	 *              2. Removed "Untitled" output.
	 * @TODO remove this feature?
	 * @deprecated
	 *
	 * @param int $id The page ID.
	 * @param object|string $term The term object.
	 * @return string The description title.
	 */
	public function generate_description_title( $id = null, $term = '' ) {

		if ( null === $id )
			$id = $this->get_the_real_ID();

		$title = '';

		if ( $this->is_front_page_by_id( $id ) ) :
			$title = $this->get_home_page_tagline();
		else :
			/**
			 * No need to parse these when generating social description.
			 *
			 * @since 2.5.0
			 */
			if ( $this->is_blog_page( $id ) ) {
				/**
				 * We're on the blog page now.
				 * @since 2.2.8
				 */
				$title = $this->get_unprocessed_generated_title( [ 'id' => $id ] );

				/**
				 * @TODO create option.
				 * @priority medium 2.8.0+
				 */
				/* translators: %s = Blog page title. Front-end output. */
				$title = sprintf( \__( 'Latest posts: %s', 'autodescription' ), $title );
			} elseif ( $term && isset( $term->term_id ) ) {
				//* We're on a taxonomy now.

				$data = $this->get_term_meta( $term->term_id );

				if ( ! empty( $data['doctitle'] ) ) {
					$title = $data['doctitle'];
				} elseif ( ! empty( $term->name ) ) {
					$title = $term->name;
				} elseif ( ! empty( $term->slug ) ) {
					$title = $term->slug;
				}
			} else {
				//* We're on a page or other archive.
				// FIXME Code debt because we're separating the "query" vs "caller"
				if ( $this->is_admin() ) {
					$title = $this->get_unprocessed_generated_title( [ 'id' => $id ] );
				} elseif ( $id === $this->get_the_real_ID() ) {
					$title = $this->get_unprocessed_generated_title();
				}
			}
		endif;

		return trim( $title );
	}

	/**
	 * Generates the excerpt.
	 * @NOTE Supply calculated $max_char_length to reflect actual output.
	 *
	 * @since 2.3.4
	 * @since 2.8.2 Now no longer escapes excerpt by accident in processing, preventing "too short" output.
	 * @since 3.0.4 The default $max_char_length has been increased from 155 to 300.
	 * @staticvar array $excerpt_cache Holds the excerpt
	 * @staticvar array $excerptlength_cache Holds the excerpt length
	 *
	 * @param int|string $page_id required : The Page ID
	 * @param object|null $term The Taxonomy Term.
	 * @param int $max_char_length The maximum excerpt char length.
	 * @return string $excerpt The excerpt, not escaped.
	 */
	public function generate_excerpt( $page_id, $term = '', $max_char_length = 300 ) {

		static $excerpt_cache = [];
		static $excerptlength_cache = [];

		$term_id = isset( $term->term_id ) ? $term->term_id : false;

		//* Put excerpt in cache.
		if ( ! isset( $excerpt_cache[ $page_id ][ $term_id ] ) ) {
			if ( $this->is_singular( $page_id ) ) {
				//* We're on the blog page now.
				$excerpt = $this->get_excerpt_by_id( '', $page_id, '', false );
			} elseif ( $term_id ) {
				//* We're on a taxonomy now. Fetch excerpt from latest term post.
				$excerpt = empty( $term->description ) ? $this->get_excerpt_by_id( '', '', $page_id, false ) : $this->s_description_raw( $term->description );
			} elseif ( $this->is_author() ) {
				$excerpt = $this->s_description_raw( \get_the_author_meta( 'description', (int) \get_query_var( 'author' ) ) );
			} else {
				$excerpt = '';
			}

			/**
			 * Applies filters 'the_seo_framework_fetched_description_excerpt' : string
			 *
			 * @since 2.9.0
			 *
			 * @param string $excerpt The excerpt to use.
			 * @param bool $page_id The current page/term ID
			 * @param object|mixed $term The current term.
			 * @param int $max_char_length Determines the maximum length of excerpt after trimming.
			 */
			$excerpt = (string) \apply_filters( 'the_seo_framework_fetched_description_excerpt', $excerpt, $page_id, $term, $max_char_length );

			$excerpt_cache[ $page_id ][ $term_id ] = $excerpt;
		}

		//* Fetch excerpt from cache.
		$excerpt = $excerpt_cache[ $page_id ][ $term_id ];

		/**
		 * Put excerptlength in cache.
		 * Why cache? My tests have shown that mb_strlen is 1.03x faster than cache fetching.
		 * However, _mb_strlen (compat) is about 1740x slower. And this is the reason it's cached!
		 */
		if ( ! isset( $excerptlength_cache[ $page_id ][ $term_id ] ) )
			$excerptlength_cache[ $page_id ][ $term_id ] = mb_strlen( $excerpt );

		//* Fetch the length from cache.
		$excerpt_length = $excerptlength_cache[ $page_id ][ $term_id ];

		//* Trunculate if the excerpt is longer than the max char length
		$excerpt = $this->trim_excerpt( $excerpt, $excerpt_length, $max_char_length );

		return (string) $excerpt;
	}

	/**
	 * Trims the excerpt by word and determines sentence stops.
	 *
	 * @since 2.6.0
	 *
	 * @param string $excerpt The untrimmed excerpt.
	 * @param int $excerpt_length The current excerpt length.
	 * @param int $max_char_length At what point to shave off the excerpt.
	 * @return string The trimmed excerpt.
	 */
	public function trim_excerpt( $excerpt, $excerpt_length, $max_char_length ) {

		if ( $excerpt_length <= $max_char_length )
			return trim( $excerpt );

		//* Cut string to fit $max_char_length.
		$sub_ex = mb_substr( $excerpt, 0, $max_char_length );
		$sub_ex = trim( html_entity_decode( $sub_ex ) );

		//* Split words in array separated by delimiter.
		$ex_words = explode( ' ', $sub_ex );

		//* Count to total words in the excerpt.
		$ex_total = count( $ex_words );

		//* Slice the complete excerpt and count the amount of words.
		$extra_ex_words = explode( ' ', trim( $excerpt ), $ex_total + 1 );
		$extra_ex_total = count( $extra_ex_words ) - 1;
		unset( $extra_ex_words[ $extra_ex_total ] );

		//* Calculate if last word exceeds.
		if ( $extra_ex_total >= $ex_total ) {
			$ex_cut = mb_strlen( $ex_words[ $ex_total - 1 ] );

			if ( $extra_ex_total > $ex_total ) {
				/**
				 * There are more words in the trimmed excerpt than the compared total excerpt.
				 * Remove the exceeding word.
				 */
				$excerpt = mb_substr( $sub_ex, 0, - $ex_cut );
			} else {
				/**
				 * The amount of words are the same in the comparison.
				 * Calculate if the chacterers are exceeding.
				 */
				$ex_extra_cut = mb_strlen( $extra_ex_words[ $extra_ex_total - 1 ] );

				if ( $ex_extra_cut > $ex_cut ) {
					//* Final word is falling off. Remove it.
					$excerpt = mb_substr( $sub_ex, 0, - $ex_cut );
				} else {
					//* We're all good here, continue.
					$excerpt = $sub_ex;
				}
			}
		}

		//* Remove trailing/leading commas and spaces.
		$excerpt = trim( $excerpt, ' ,' );

		//* Fetch last character.
		$last_char = substr( $excerpt, -1 );

		if ( ';' === $last_char ) {
			$excerpt = rtrim( $excerpt, ' ,.?!;' ) . '.';
		} else {
			$stops = [ '.', '?', '!' ];
			//* Add three dots if there's no full stop at the end of the excerpt.
			if ( ! in_array( $last_char, $stops, true ) )
				$excerpt .= '...';
		}

		return trim( $excerpt );
	}
}
