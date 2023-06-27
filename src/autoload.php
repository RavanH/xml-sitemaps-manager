<?php

/**
 * XML Sitemap Manager Autoloader.
 *
 * @since 0.5
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
function xmlsm_autoloader( $class ) {
	// Skip this if not in our namespace.
	if ( 0 !== strpos( $class, 'XMLSitemapsManager' ) ) {
		return;
	}

    // Replace namespace separators with directory separators in the relative
    // class name, append with .php
    $class_path = str_replace( array( 'XMLSitemapsManager\\', '\\' ), array( '', '/' ), $class);

    $file =  __DIR__ . '/src/' . $class_path . '.php';
    // if the file exists, require it
    if ( file_exists( $file ) ) {
        require $file;
    }
}

spl_autoload_register( 'xmlsm_autoloader' );
