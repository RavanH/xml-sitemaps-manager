<?php
/**
 * XML Sitemaps Manager admin fields.
 *
 * @package XML Sitemaps Manager
 */

?>
<fieldset id="xml_sitemaps">
	<legend class="screen-reader-text">
		<?php esc_html_e( 'XML Sitemap' ); ?>
	</legend>

	<?php if ( 1 === (int) get_option( 'blog_public' ) ) : ?>

		<p>
			<?php esc_html_e( 'XML Sitemap' ); ?>

			<?php if ( $active_providers ) : ?>
			<span class="description">
				&nbsp;&rarr;&nbsp;
				<a href="<?php echo esc_url( get_sitemap_url( 'index' ) ); ?>" target="_blank"><?php esc_html_e( 'View' ); ?><span class="dashicons dashicons-external" style="font-size:inherit;vertical-align:inherit;text-align:inherit"></span></a>
			</span>
			<?php endif; ?>

		</p>

		<ul class="subsection sitemap-providers" style="margin:0 0 0 24px">
			<?php
			foreach ( wp_get_sitemap_providers() as $sitemap => $provider ) :
				$subtypes           = $provider->get_object_subtypes();
				$provider_nice_name = array_key_exists( $sitemap, $provider_nice_names ) ? $provider_nice_names[ $sitemap ] : sprintf( /* translators: %s: Sitemap slug. */ __( 'Sitemap provider: %s', 'xml-sitemaps-manager' ), $sitemap );
				?>
			<li>
				<fieldset>
					<legend class="screen-reader-text">
						<?php echo esc_html( $provider_nice_name ); ?>
					</legend>

					&rdca;&nbsp;
					<label>
						<input name="xmlsm_sitemap_providers[<?php echo esc_attr( $sitemap ); ?>]" type="checkbox" class="toggle-subsection" id="xmlsm_sitemap_providers_<?php echo esc_attr( $sitemap ); ?>" value="1"<?php checked( is_array( $active_providers ) && in_array( $sitemap, $active_providers, true ) ); ?> />
						<strong>
							<?php echo esc_html( $provider_nice_name ); ?>
						</strong>
					</label>

					<?php if ( ! empty( $subtypes ) ) : ?>
						<fieldset class="subsection sitemap-provider<?php echo esc_attr( $sitemap ); ?> <?php echo is_array( $active_providers ) && in_array( $sitemap, $active_providers, true ) ? '' : ' hidden'; ?>" style="margin-left:40px">
							<legend class="screen-reader-text"><?php esc_html_e( 'Exclude:' ); ?></legend>

							&rdca;&nbsp;
							<?php esc_html_e( 'Exclude:' ); ?>

							<?php
							foreach ( $subtypes as $subtype ) :
								$disabled_subtype = ! empty( $disabled_subtypes[ $sitemap ] ) && is_array( $disabled_subtypes[ $sitemap ] ) && in_array( $subtype->name, $disabled_subtypes[ $sitemap ], true );
								?>
								&nbsp;
								<label>
									<input name="xmlsm_disabled_subtypes[<?php echo esc_attr( $sitemap ); ?>][<?php echo esc_attr( $subtype->name ); ?>]" type="checkbox" id="xmlsm_disabled_subtypes_<?php echo esc_attr( $sitemap ); ?>_<?php echo esc_attr( $subtype->name ); ?>" value="1"<?php checked( $disabled_subtype ); ?> />
									<?php echo esc_html( $subtype->label ); ?>
								</label>
								&nbsp;
							<?php endforeach; ?>
						</fieldset>
					<?php endif; ?>

				</fieldset>
			</li>
				<?php
			endforeach;
			?>
		</ul>

		<?php if ( $active_providers ) : ?>
			<br>
			<p>
				<label>
					<input name="xmlsm_sitemaps_fixes" type="checkbox" id="xmlsm_sitemaps_fixes" value="1"<?php checked( $sitemaps_fixes ); ?> />
					<?php esc_html_e( 'Apply bug fixes and optimizations', 'xml-sitemaps-manager' ); ?>
				</label>
			</p>
			<p class="description">
				<?php esc_html_e( 'Recommended patches and optimizations, provided by the WordPress community.', 'xml-sitemaps-manager' ); ?>
				<a href="https://wordpress.org/plugins/xml-sitemaps-manager/#tab-description"><?php esc_html_e( 'Learn more' ); ?></a>
			</p>
			<br>
			<p>
				<label>
					<input name="xmlsm_lastmod" type="checkbox" id="xmlsm_lastmod" value="1"<?php checked( $lastmod ); ?> />
					<?php esc_html_e( 'Last Modified' ); ?>
				</label>
				&nbsp;
				<form action="" method="post">
					<?php wp_nonce_field( XMLSM_BASENAME . '-help', '_xmlsm_help_nonce' ); ?>
					<?php // TODO add button(s) to prime medadata. ?>
					<input type="submit" name="xmlsm-clear-lastmod-meta" class="button button-small" value="<?php esc_attr_e( 'Purge lastmod data caches', 'xml-sitemaps-manager' ); ?>" />
				</form>
			</p>
			<p class="description">
				<?php esc_html_e( 'Add latest modification dates to the sitemap index, taxonomy and user sitemaps.', 'xml-sitemaps-manager' ); ?>
			</p>
			<br>
			<p>
				<label>
					<?php esc_html_e( 'Maximum entries:', 'xml-sitemaps-manager' ); ?>
					<input name="xmlsm_max_urls" type="number" step="1000" min="1000" id="xmlsm_max_urls" value="<?php echo is_numeric( $max_urls ) && $max_urls > 0 ? esc_attr( $max_urls ) : ''; ?>" class="small-text">
				</label>
			</p>
			<p class="description">
				<?php esc_html_e( 'The maximum number of URLs per sitemap. Default 2000.', 'xml-sitemaps-manager' ); ?>
			</p>
		<?php endif; ?>

	<?php else : ?>

	<p class="description">
		<?php printf( /* translators: Search engine visibility */ esc_html__( 'The XML Sitemaps are disabled because of your site\'s %s setting.', 'xml-sitemaps-manager' ), '<strong>' . esc_html__( 'Search engine visibility' ) . '</strong>' ); ?>
	</p>

	<?php endif; ?>

</fieldset>
<script>
if ( window.location.hash ) {
	let loc = jQuery( window.location.hash );
	if (loc.length) {
		jQuery( 'html, body' ).animate( { scrollTop: loc.offset().top-40 }, 400, 'swing', function(){ loc.closest( 'td' ).addClass( 'highlight' ); } );
	}
}
jQuery( 'document' ).ready( function( $ ) {
	$('input.toggle-subsection').change( function() {
		console.log('toggle');
		$( this ).parents('fieldset').first().find('.subsection').first().toggle('fast');
	});
} );
</script>
