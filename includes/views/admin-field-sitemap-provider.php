<fieldset>
	<legend class="screen-reader-text">
		<?php echo $provider_nice_name; ?>
	</legend>

	<label>
		<input name="xmlsm_sitemap_providers[<?php echo $type; ?>]" type="checkbox" id="xmlsm_sitemap_providers_<?php echo $type; ?>" value="1"<?php checked( in_array( $type, $xmlsm_sitemap_providers ) ); ?> />
		<strong>
			<?php echo $provider_nice_name; ?>
		</strong>
	</label>

	<?php if ( in_array( $type, $xmlsm_sitemap_providers ) ) : ?>
		<?php if ( empty ( $subtypes ) ) : ?>
			<span class="description">
				&nbsp;&ndash;&nbsp;
				<a href="<?php echo get_sitemap_url( $type ); ?>" target="_blank"><?php echo esc_html( translate( 'View' ) ); ?><span class="dashicons dashicons-external" style="font-size:inherit;vertical-align:inherit;text-align:inherit"></span></a>
			</span>
			<br>
		<?php else : ?>
			<br>
			<?php echo esc_html( translate( 'Deactivate' ) ); ?>

			<ul class="export-filters">
				<?php foreach ( $subtypes as $subtype ) :
					$disabled = ! empty( $xmlsm_disabled_subtypes[$type] ) && is_array( $xmlsm_disabled_subtypes[$type] ) && in_array( $subtype->name, $xmlsm_disabled_subtypes[$type] ); ?>
				<li>
					<label>
						<input name="xmlsm_disabled_subtypes[<?php echo $type; ?>][<?php echo $subtype->name; ?>]" type="checkbox" id="xmlsm_disabled_subtypes_<?php echo $type; ?>_<?php echo $subtype->name; ?>" value="1"<?php checked( $disabled ); ?> />
						<?php echo $subtype->label; ?>
					</label>
					<?php if ( ! $disabled ) : ?>
						<span class="description">
							&nbsp;&ndash;&nbsp;
							<a href="<?php echo get_sitemap_url( $type, $subtype->name ); ?>" target="_blank"><?php echo esc_html( translate( 'View' ) ); ?><span class="dashicons dashicons-external" style="font-size:inherit;vertical-align:inherit;text-align:inherit"></span></a>
						</span>
						<br>

<!--					<label>
							<?php //echo esc_html( translate( 'Priority' ) ); ?>
							<input name="wpsm_sitemaps_priority[<?php //echo $type; ?>][<?php //echo $subtype->name; ?>]" type="number" step="0.1" min="0" max="1" id="wpsm_sitemaps_priority_<?php //echo $type; ?>_<?php //echo $subtype->name; ?>" value="<?php //echo ! empty( $wpsm_sitemaps_priority[$type] ) && is_array( $wpsm_sitemaps_priority[$type] ) && array_key_exists( $subtype->name, $wpsm_sitemaps_priority[$type] ) ? $wpsm_sitemaps_priority[$type][$subtype->name] : ''; ?>" class="small-text">
						</label>
-->
					<?php endif; ?>
				</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php echo esc_html( translate( 'Activate' ) ); ?>

		<ul class="export-filters">
			<li>
				<label>
					<input name="xmlsm_sitemaps_lastmod[<?php echo $type; ?>]" type="checkbox" id="xmlsm_sitemaps_lastmod_<?php echo $type; ?>" value="1"<?php checked( in_array( $type, $xmlsm_sitemaps_lastmod ) ); ?> />
					<?php echo esc_html( translate( 'Last Modified' ) ); ?>
				</label>
			</li>
	<!--
			<li>
				<label>
					<input name="wpsm_sitemaps_changefreq[<?php //echo $type; ?>]" type="checkbox" id="wpsm_sitemaps_changefreq_<?php //echo $type; ?>" value="1"<?php //checked( in_array( $type, $xmlsm_sitemaps_lastmod ) ); ?> />
					<?php //echo esc_html( translate( 'Change Frequency' ) ); ?>
				</label>
			</li>
			<li>
				<label>
					<input name="wpsm_sitemaps_priority[<?php //echo $type; ?>]" type="checkbox" id="wpsm_sitemaps_priority_<?php //echo $type; ?>" value="1"<?php //checked( in_array( $type, $xmlsm_sitemaps_lastmod ) ); ?> />
					<?php //echo esc_html( translate( 'Priority' ) ); ?>
				</label>
			</li>
	-->
		</ul>

		<label>
			<?php esc_html_e( 'Maximum number of URLs included in a sitemap:', 'wp-sitemaps-manager' ); ?>
			<input name="xmlsm_sitemaps_max_urls[<?php echo $object_type; ?>]" type="number" step="1000" min="1000" id="xmlsm_sitemaps_max_urls_<?php echo $object_type; ?>" value="<?php echo array_key_exists( $object_type, $xmlsm_sitemaps_max_urls ) ? $xmlsm_sitemaps_max_urls[$object_type] : ''; ?>" class="small-text">
		</label>

	<?php endif; ?>

</fieldset>
