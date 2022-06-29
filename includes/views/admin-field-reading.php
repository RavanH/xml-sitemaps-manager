<fieldset id="wpsm_sitemaps">
	<legend class="screen-reader-text">
		<?php echo esc_html( translate( 'XML Sitemap' ) ); ?>
	</legend>
	<label>
		<input name="xmlsm_sitemaps_enabled" type="checkbox" id="xmlsm_sitemaps_enabled" value="1"<?php checked( $xmlsm_sitemaps_enabled ); ?> />
		<?php esc_html_e( 'XML Sitemaps enabled', 'wp-sitemaps-manager' ); ?>
	</label>

	<?php if ( $xmlsm_sitemaps_enabled ) : ?>
		<span class="description">
			&nbsp;&ndash;&nbsp;
			<!-- <a href="<?php echo admin_url( 'options-general.php' ); ?>?page=wpsm" id="wpsm_link"><?php echo esc_html( translate( 'Settings' ) ); ?></a> | -->
			<a href="<?php echo get_sitemap_url( 'index' ); ?>" target="_blank"><?php echo esc_html( translate( 'View' ) ); ?><span class="dashicons dashicons-external" style="font-size:inherit;vertical-align:inherit;text-align:inherit"></span></a>
		</span>

		<br>

		<label>
			<input name="xmlsm_sitemaps_fixes" type="checkbox" id="xmlsm_sitemaps_fixes" value="1"<?php checked( $xmlsm_sitemaps_fixes ); ?> />
			<?php esc_html_e( 'Apply bugfixes and optimizations', 'wp-sitemaps-manager' ); ?>
		</label>
		<p class="description">
			<?php
			printf(
				/* translators: %s: FAQ's URL. */
				__( 'Recommended patches and optimizations, supplied by the WordPress community. <a href="%s">Learn more</a>.', 'wp-sitemaps-manager' ),
				__( 'https://wordpress.org/plugins/wp-sitemaps-manager/#tab-description', 'wp-sitemaps-manager' )
			);
			?>
		</p>

		<ul class="export-filters">
			<?php foreach ( wp_get_sitemap_providers() as $type => $provider ) :
				$subtypes = $provider->get_object_subtypes();
				$provider_nice_name = array_key_exists( $type, $provider_names ) ? $provider_names[$type] : sprintf( /* translators: %s: Sitemap slug. */ esc_html__( 'Sitemap provider: %s', 'wp-sitemaps-manager' ), $type );
				$object_type = array_key_exists( $type, $provider_object_types ) ? $provider_object_types[$type] : 'unkown_object_type'; ?>
			<li>
				<?php
					include WPSM_DIR . '/includes/views/admin-field-sitemap-provider.php';
				?>
			</li>
			<?php endforeach; ?>
		</ul>

	<?php endif; ?>

</fieldset>
<script>
jQuery( 'document' ).ready( function( $ ) {
	if ( window.location.hash === '#wpsm_sitemaps' ) {
		let wpsm_sitemaps = $( '#wpsm_sitemaps' );
		$( 'html, body' ).animate( { scrollTop: wpsm_sitemaps.offset().top-40 }, 800, function(){wpsm_sitemaps.closest( 'td' ).addClass( 'highlight' );} );
	}
} );
</script>
