<fieldset id="xml_sitemaps">
	<legend class="screen-reader-text">
		<?php echo esc_html( translate( 'XML Sitemap' ) ); ?>
	</legend>
	<label>
		<input name="xmlsm_sitemaps_enabled" type="checkbox" class="toggle-subsection" id="xmlsm_sitemaps_enabled" value="1"<?php checked( $sitemaps_enabled ); ?> />
		<?php esc_html_e( 'XML Sitemaps enabled', 'xml-sitemaps-manager' ); ?>
	</label>

	<?php if ( $sitemaps_enabled ) : ?>
		<span class="description">
			&nbsp;&ndash;&nbsp;
			<a href="<?php echo get_sitemap_url( 'index' ); ?>" target="_blank"><?php echo esc_html( translate( 'View' ) ); ?><span class="dashicons dashicons-external" style="font-size:inherit;vertical-align:inherit;text-align:inherit"></span></a>
		</span>
	<?php endif; ?>

	<br>

	<ul class="subsection sitemap-providers <?php echo $sitemaps_enabled ? '' : 'hidden'; ?>" style="margin:0 0 0 24px">
		<?php
		foreach ( wp_get_sitemap_providers() as $type => $provider ) :
			$subtypes = $provider->get_object_subtypes();
			$provider_nice_name = array_key_exists( $type, $provider_nice_names ) ? $provider_nice_names[$type] : sprintf( /* translators: %s: Sitemap slug. */ __( 'Sitemap provider: %s', 'xml-sitemaps-manager' ), $type );
		?>
		<li>
			<fieldset>
				<legend class="screen-reader-text">
					<?php echo esc_html( $provider_nice_name ); ?>
				</legend>

				<label>
					<input name="xmlsm_sitemap_providers[<?php echo esc_attr( $type ); ?>]" type="checkbox" class="toggle-subsection" id="xmlsm_sitemap_providers_<?php echo esc_attr( $type ); ?>" value="1"<?php checked( in_array( $type, $sitemap_providers ) ); ?> />
					<strong>
						<?php echo esc_html( $provider_nice_name ); ?>
					</strong>
				</label>

				<?php if ( $sitemaps_enabled && in_array( $type, $sitemap_providers ) && empty ( $subtypes ) ) : ?>
					<span class="description">
						&nbsp;&ndash;&nbsp;
						<a href="<?php echo get_sitemap_url( $type ); ?>" target="_blank"><?php echo esc_html( translate( 'View' ) ); ?><span class="dashicons dashicons-external" style="font-size:inherit;vertical-align:inherit;text-align:inherit"></span></a>
					</span>
					<br>
				<?php endif; ?>
				<?php if ( ! empty ( $subtypes ) ) : ?>
					<div class="subsection sitemap-provider <?php echo esc_attr( $type ); ?> <?php echo in_array( $type, $sitemap_providers ) ? '' : 'hidden'; ?>" style="margin-left:24px">
						<p>
							<?php echo esc_html( translate( 'Exclude:' ) ); ?>
						</p>
						<ul>
							<?php foreach ( $subtypes as $subtype ) :
								$disabled_subtype = ! empty( $disabled_subtypes[$type] ) && is_array( $disabled_subtypes[$type] ) && in_array( $subtype->name, $disabled_subtypes[$type] ); ?>
							<li style="margin:0;">
								<label>
									<input name="xmlsm_disabled_subtypes[<?php echo esc_attr( $type ); ?>][<?php echo esc_attr( $subtype->name ); ?>]" type="checkbox" id="xmlsm_disabled_subtypes_<?php echo esc_attr( $type ); ?>_<?php echo esc_attr( $subtype->name ); ?>" value="1"<?php checked( $disabled_subtype ); ?> />
									<?php echo esc_html( $subtype->label ); ?>
								</label>
								<?php if ( $sitemaps_enabled && ! $disabled_subtype ) : ?>
									<span class="description">
										&nbsp;&ndash;&nbsp;
										<a href="<?php echo get_sitemap_url( $type, $subtype->name ); ?>" target="_blank"><?php echo esc_html( translate( 'View' ) ); ?><span class="dashicons dashicons-external" style="font-size:inherit;vertical-align:inherit;text-align:inherit"></span></a>
									</span>
									<br>
								<?php endif; ?>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

			</fieldset>
		</li>
		<?php
		endforeach;
		?>
	</ul>

	<br>
	<label>
		<input name="xmlsm_sitemaps_fixes" type="checkbox" id="xmlsm_sitemaps_fixes" value="1"<?php checked( $sitemaps_fixes ); ?> />
		<?php esc_html_e( 'Apply bugfixes and optimizations', 'xml-sitemaps-manager' ); ?>
	</label>
	<p class="description">
		<?php
		printf(
			/* translators: %s: Plugin description URL. */
			__( 'Recommended patches and optimizations, provided by the WordPress community. <a href="%s">Learn more</a>.', 'xml-sitemaps-manager' ),
			__( 'https://wordpress.org/plugins/xml-sitemaps-manager/#tab-description', 'xml-sitemaps-manager' )
		);
		?>
	</p>

	<br>
	<label>
		<input name="xmlsm_lastmod" type="checkbox" id="xmlsm_lastmod" value="1"<?php checked( $lastmod ); ?> />
		<?php echo esc_html( translate( 'Last Modified' ) ); ?>
	</label>
	<p class="description">
		<?php  esc_html_e( 'Add Last Modified data to the sitemaps.', 'xml-sitemaps-manager' ); ?>
	</p>

	<br>
	<label>
		<?php esc_html_e( 'Maximum number of URLs:', 'xml-sitemaps-manager' ); ?>
		<input name="xmlsm_max_urls" type="number" step="1000" min="1000" id="xmlsm_max_urls" value="<?php echo is_numeric( $max_urls ) && $max_urls > 0 ? esc_attr( $max_urls ) : ''; ?>" class="small-text">
	</label>
	<p class="description">
		<?php  esc_html_e( 'The maximum number of URLs included in a sitemap. Default 2000.', 'xml-sitemaps-manager' ); ?>
	</p>


</fieldset>
<script>
jQuery( 'document' ).ready( function( $ ) {
	if ( window.location.hash ) {
		let loc = $( window.location.hash );
		if (loc.length) {
			$( 'html, body' ).animate( { scrollTop: loc.offset().top-40 }, 800, 'swing', function(){ loc.closest( 'td' ).addClass( 'highlight' ); } );
		}
	}
	$('input.toggle-subsection').change( function() {
		console.log('toggle');
        $( this ).parents('fieldset').first().find('.subsection').first().toggle('fast');
    });
} );
</script>
