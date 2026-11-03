<?php
/**
 * Brand Agent third-party connectors.
 *
 * The Clarity dashboard iframe owns the UI. This file is the privileged HMAC
 * bridge: admin-ajax operations are signed here and sent directly to AdsAgentServer.
 * The browser never receives the merchant secret, advertiser id, or connector id.
 *
 * @package MicrosoftClarity
 */

defined( 'ABSPATH' ) || exit;

define( 'BRANDAGENT_CONNECTORS_PATH', '/api/v1/advertiser/connectors' );
define( 'BRANDAGENT_HOUSECALL_PRO_API_KEY_PATH', '/api/housecallpro/api-key' );
define( 'BRANDAGENT_HOUSECALL_PRO_API_KEY_VALIDATE_PATH', '/api/housecallpro/api-key/validate' );
define( 'BRANDAGENT_CONNECTOR_SQUARE_PROVIDER_ID', 'square' );
define( 'BRANDAGENT_CONNECTOR_HUBSPOT_PROVIDER_ID', 'hubspot' );
define( 'BRANDAGENT_CONNECTOR_CALENDLY_PROVIDER_ID', 'calendly' );
define( 'BRANDAGENT_CONNECTOR_JOBBER_PROVIDER_ID', 'jobber' );
define( 'BRANDAGENT_CONNECTOR_HOUSECALL_PRO_PROVIDER_ID', 'housecallpro' );
define( 'BRANDAGENT_CONNECTOR_MAXIMUM_API_KEY_BYTES', 4096 );
define( 'BRANDAGENT_CONNECTOR_MAXIMUM_DURATION_MINUTES', 24 * 60 );
define( 'BRANDAGENT_CONNECTOR_DEFAULT_DURATION_MINUTES', 60 );
define( 'BRANDAGENT_CONNECTOR_MAXIMUM_SERVICE_COUNT', 200 );
define( 'BRANDAGENT_CONNECTOR_MAXIMUM_SERVICE_ID_BYTES', 512 );
define( 'BRANDAGENT_CONNECTOR_MAXIMUM_SERVICE_NAME_BYTES', 512 );
define( 'BRANDAGENT_CONNECTOR_MAXIMUM_DISPLAY_NAME_LENGTH', 80 );
define( 'BRANDAGENT_CONNECTOR_MAXIMUM_SERVICE_CATALOG_BYTES', 256 * 1024 );

/**
 * @return array Provider ids this plugin may proxy to AdsAgentServer.
 */
function brandagent_connectors_supported_provider_ids() {
	return array(
		BRANDAGENT_CONNECTOR_SQUARE_PROVIDER_ID,
		BRANDAGENT_CONNECTOR_HUBSPOT_PROVIDER_ID,
		BRANDAGENT_CONNECTOR_CALENDLY_PROVIDER_ID,
		BRANDAGENT_CONNECTOR_JOBBER_PROVIDER_ID,
		BRANDAGENT_CONNECTOR_HOUSECALL_PRO_PROVIDER_ID,
	);
}

/**
 * @return bool Whether this site has a usable plain-WordPress Brand Agents connection.
 */
function brandagent_connectors_has_wordpress_connection() {
	return function_exists( 'brandagent_wordpress_has_connection' )
		&& function_exists( 'brandagent_get_hmac_platform' )
		&& 'wordpress' === brandagent_get_hmac_platform()
		&& brandagent_wordpress_has_connection();
}

/**
 * admin-ajax entry point used by js/add_window_listeners.js after a
 * WORDPRESS_CONNECTOR_REQUEST postMessage from the Clarity iframe.
 *
 * The iframe does not send the nonce or connector ids. The parent holds the nonce
 * via wp_localize_script and PHP resolves connector ids from status.
 */
add_action( 'wp_ajax_brandagent_connectors', 'brandagent_connectors_ajax' );
function brandagent_connectors_ajax() {
	if ( function_exists( 'nocache_headers' ) ) {
		nocache_headers();
	}

	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'wp_ajax_brandagent_connectors' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid nonce.' ), 403 );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'WordPress administrator access is required.' ), 403 );
	}

	if ( ! brandagent_connectors_has_wordpress_connection() ) {
		wp_send_json_error(
			array( 'message' => 'Complete the WordPress Brand Agent connection before connecting an integration.' ),
			409
		);
	}

	$raw_operation   = isset( $_POST['operation'] ) ? wp_unslash( $_POST['operation'] ) : 'status';
	$raw_provider_id = isset( $_POST['providerId'] ) ? wp_unslash( $_POST['providerId'] ) : '';
	if ( ! is_string( $raw_operation ) || ! is_string( $raw_provider_id ) ) {
		wp_send_json_error(
			array(
				'code'    => 'invalid_request',
				'message' => 'Invalid connector request.',
			),
			400
		);
	}

	$operation                     = sanitize_key( $raw_operation );
	$provider_id                   = sanitize_key( $raw_provider_id );
	$api_key                       = isset( $_POST['apiKey'] ) ? wp_unslash( $_POST['apiKey'] ) : null;
	$duration_minutes              = isset( $_POST['durationMinutes'] ) ? wp_unslash( $_POST['durationMinutes'] ) : null;
	$configuration                 = isset( $_POST['configuration'] ) ? wp_unslash( $_POST['configuration'] ) : null;
	$replace_pending_authorization = false;
	if ( isset( $_POST['replacePendingAuthorization'] ) ) {
		$replace_pending_authorization = brandagent_connectors_parse_boolean(
			wp_unslash( $_POST['replacePendingAuthorization'] )
		);
		if ( null === $replace_pending_authorization ) {
			wp_send_json_error(
				array(
					'code'    => 'invalid_request',
					'message' => 'Invalid connector request.',
				),
				400
			);
		}
	}

	$expected_authorization_version = null;
	if ( isset( $_POST['expectedAuthorizationVersion'] ) ) {
		$raw_expected_authorization_version = wp_unslash( $_POST['expectedAuthorizationVersion'] );
		if ( ! is_scalar( $raw_expected_authorization_version ) ) {
			wp_send_json_error(
				array(
					'code'    => 'invalid_request',
					'message' => 'Invalid connector request.',
				),
				400
			);
		}
		$expected_authorization_version = sanitize_text_field(
			(string) $raw_expected_authorization_version
		);
	}
	$result = brandagent_connectors_execute_operation(
		$operation,
		$provider_id,
		$replace_pending_authorization,
		$expected_authorization_version,
		$api_key,
		$duration_minutes,
		$configuration
	);

	if ( is_wp_error( $result ) ) {
		$error_data = $result->get_error_data();
		$status     = ( is_array( $error_data ) && isset( $error_data['status'] ) )
			? (int) $error_data['status']
			: 500;
		if ( $status < 400 || $status > 599 ) {
			$status = 500;
		}
		$error = array(
			'code'    => $result->get_error_code(),
			'message' => $result->get_error_message(),
		);
		if ( is_array( $error_data ) && isset( $error_data['connector'] ) ) {
			$error['connector'] = brandagent_connectors_project_connector_for_ui( $error_data['connector'] );
		}
		if ( is_array( $error_data ) && isset( $error_data['services'] ) ) {
			$error['services'] = $error_data['services'];
		}
		wp_send_json_error( $error, $status );
	}

	wp_send_json_success( $result );
}

/**
 * Parse a strict boolean value from an admin-ajax field.
 *
 * @param mixed $value Candidate value.
 * @return bool|null Parsed value, or null when invalid.
 */
function brandagent_connectors_parse_boolean( $value ) {
	if ( is_bool( $value ) ) {
		return $value;
	}

	if ( is_int( $value ) && ( 0 === $value || 1 === $value ) ) {
		return 1 === $value;
	}

	if ( ! is_string( $value ) ) {
		return null;
	}

	$normalized = strtolower( trim( $value ) );
	if ( '1' === $normalized || 'true' === $normalized ) {
		return true;
	}

	if ( '0' === $normalized || 'false' === $normalized ) {
		return false;
	}

	return null;
}

/**
 * Count a UTF-8 string using the UTF-16 code units used by JavaScript and AdsAgentServer.
 *
 * @param mixed $value Candidate string.
 * @return int|null UTF-16 length.
 */
function brandagent_connectors_utf16_length( $value ) {
	if ( ! is_string( $value ) ) {
		return null;
	}

	$characters = preg_split( '//u', $value, -1, PREG_SPLIT_NO_EMPTY );
	if ( false === $characters ) {
		return null;
	}

	$length = 0;
	foreach ( $characters as $character ) {
		$length += 4 === strlen( $character ) ? 2 : 1;
	}

	return $length;
}

/**
 * Derive the legacy display-name fallback using the AdsAgentServer truncation rule.
 *
 * @param string $name Valid canonical service name.
 * @return string|null Safe display name.
 */
function brandagent_connectors_default_display_name( $name ) {
	$characters = preg_split( '//u', $name, -1, PREG_SPLIT_NO_EMPTY );
	if ( false === $characters ) {
		return null;
	}

	$display_name = '';
	$length       = 0;
	foreach ( $characters as $character ) {
		$character_length = 4 === strlen( $character ) ? 2 : 1;
		if ( $length + $character_length > BRANDAGENT_CONNECTOR_MAXIMUM_DISPLAY_NAME_LENGTH ) {
			break;
		}

		$display_name .= $character;
		$length       += $character_length;
	}

	$display_name = rtrim( $display_name );
	return '' === $display_name ? null : $display_name;
}

/**
 * Validate a merchant-owned service display name.
 *
 * @param mixed $value Candidate display name.
 * @return string|null Valid display name.
 */
function brandagent_connectors_validate_display_name( $value ) {
	$display_name_length = brandagent_connectors_utf16_length( $value );
	if (
		! is_string( $value )
		|| '' === $value
		|| trim( $value ) !== $value
		|| null === $display_name_length
		|| $display_name_length > BRANDAGENT_CONNECTOR_MAXIMUM_DISPLAY_NAME_LENGTH
		|| preg_match( '/[\x00-\x1F\x7F]/', $value )
	) {
		return null;
	}

	return $value;
}

/**
 * Run one allowlisted connector operation.
 *
 * @param string          $operation                      Requested operation.
 * @param string          $provider_id                    Target provider id (required for every operation but status).
 * @param bool            $replace_pending_authorization  Whether to replace the observed pending authorization.
 * @param int|string|null $expected_authorization_version Version required when replacing pending authorization.
 * @param mixed           $api_key                        API key used only by validate_api_key and submit_api_key.
 * @param mixed           $duration_minutes               Default duration used only by submit_api_key.
 * @param mixed           $configuration                  Structured configuration used by submit_api_key/apply_configuration.
 * @return array|WP_Error Result payload.
 */
function brandagent_connectors_execute_operation(
	$operation,
	$provider_id,
	$replace_pending_authorization = false,
	$expected_authorization_version = null,
	$api_key = null,
	$duration_minutes = null,
	$configuration = null
) {
	if ( 'status' === $operation ) {
		if ( null !== $api_key || null !== $duration_minutes || null !== $configuration ) {
			return new WP_Error(
				'invalid_request',
				'API-key fields are only valid for API-key authorization.',
				array( 'status' => 400 )
			);
		}
		return brandagent_connectors_get_status();
	}

	if (
		! in_array(
			$operation,
			array( 'connect', 'validate_api_key', 'submit_api_key', 'apply_configuration', 'apply_default_configuration', 'reauthorize', 'sync', 'disconnect' ),
			true
		)
	) {
		return new WP_Error(
			'invalid_operation',
			'Unsupported connector operation.',
			array( 'status' => 400 )
		);
	}

	if ( ! in_array( $provider_id, brandagent_connectors_supported_provider_ids(), true ) ) {
		return new WP_Error(
			'unsupported_provider',
			'This plugin does not support the requested connector provider.',
			array( 'status' => 400 )
		);
	}

	if (
		BRANDAGENT_CONNECTOR_HOUSECALL_PRO_PROVIDER_ID === $provider_id
		&& in_array( $operation, array( 'connect', 'reauthorize' ), true )
	) {
		return new WP_Error(
			'api_key_required',
			'Housecall Pro requires an API key.',
			array( 'status' => 400 )
		);
	}

	if ( 'validate_api_key' === $operation ) {
		if ( BRANDAGENT_CONNECTOR_HOUSECALL_PRO_PROVIDER_ID !== $provider_id ) {
			return new WP_Error(
				'unsupported_provider',
				'The requested connector does not support API-key validation.',
				array( 'status' => 400 )
			);
		}

		if (
			$replace_pending_authorization
			|| null !== $expected_authorization_version
			|| null !== $duration_minutes
			|| null !== $configuration
		) {
			return new WP_Error(
				'invalid_request',
				'API-key validation does not accept authorization or configuration fields.',
				array( 'status' => 400 )
			);
		}

		$validated_api_key = brandagent_connectors_validate_api_key( $api_key );
		if ( is_wp_error( $validated_api_key ) ) {
			return $validated_api_key;
		}

		return brandagent_connectors_validate_housecall_pro_api_key( $validated_api_key );
	}

	$reauthorization_payload = null;
	if ( in_array( $operation, array( 'reauthorize', 'submit_api_key' ), true ) ) {
		if ( $replace_pending_authorization ) {
			$expected_authorization_version = brandagent_connectors_parse_authorization_version(
				$expected_authorization_version
			);
			if ( null === $expected_authorization_version ) {
				return new WP_Error(
					'expected_authorization_version_required',
					'A positive expected authorization version is required to replace pending authorization.',
					array( 'status' => 400 )
				);
			}
			$reauthorization_payload = array(
				'replacePendingAuthorization'  => true,
				'expectedAuthorizationVersion' => $expected_authorization_version,
			);
		} elseif ( null !== $expected_authorization_version && '' !== $expected_authorization_version ) {
			return new WP_Error(
				'replace_pending_authorization_required',
				'Expected authorization version is only valid when replacing pending authorization.',
				array( 'status' => 400 )
			);
		}
	} elseif ( $replace_pending_authorization || null !== $expected_authorization_version ) {
		return new WP_Error(
			'invalid_request',
			'Pending authorization replacement is only valid for reauthorization.',
			array( 'status' => 400 )
		);
	}

	if ( 'apply_configuration' === $operation ) {
		if ( BRANDAGENT_CONNECTOR_HOUSECALL_PRO_PROVIDER_ID !== $provider_id ) {
			return new WP_Error(
				'unsupported_provider',
				'Configuration is not supported for the requested connector.',
				array( 'status' => 400 )
			);
		}

		if ( null !== $api_key || null !== $duration_minutes ) {
			return new WP_Error(
				'invalid_request',
				'Configuration is not supported for the requested connector.',
				array( 'status' => 400 )
			);
		}

		$validated_configuration = brandagent_connectors_parse_api_key_configuration( $configuration, null );
		if ( is_wp_error( $validated_configuration ) ) {
			return $validated_configuration;
		}

		return brandagent_connectors_apply_configuration_operation( $provider_id, $validated_configuration );
	}

	if ( 'submit_api_key' === $operation ) {
		if ( BRANDAGENT_CONNECTOR_HOUSECALL_PRO_PROVIDER_ID !== $provider_id ) {
			return new WP_Error(
				'unsupported_provider',
				'The requested connector does not support API-key authorization.',
				array( 'status' => 400 )
			);
		}

		$validated_api_key = brandagent_connectors_validate_api_key( $api_key );
		if ( is_wp_error( $validated_api_key ) ) {
			return $validated_api_key;
		}

		$validated_configuration = brandagent_connectors_parse_api_key_configuration(
			$configuration,
			$duration_minutes
		);
		if ( is_wp_error( $validated_configuration ) ) {
			return $validated_configuration;
		}

		return brandagent_connectors_connect_api_key_provider(
			$provider_id,
			$validated_api_key,
			$validated_configuration,
			$reauthorization_payload
		);
	}

	if ( 'apply_default_configuration' === $operation ) {
		if (
			BRANDAGENT_CONNECTOR_HOUSECALL_PRO_PROVIDER_ID !== $provider_id
			|| null !== $api_key
			|| null !== $configuration
		) {
			return new WP_Error(
				'invalid_request',
				'Default configuration is not supported for the requested connector.',
				array( 'status' => 400 )
			);
		}

		$validated_duration = brandagent_connectors_parse_duration_minutes( $duration_minutes );
		if ( null === $validated_duration ) {
			return new WP_Error(
				'invalid_duration',
				'Enter a default duration from 1 to 1440 minutes.',
				array( 'status' => 400 )
			);
		}

		return brandagent_connectors_apply_default_configuration( $provider_id, $validated_duration );
	}

	if ( null !== $api_key || null !== $duration_minutes || null !== $configuration ) {
		return new WP_Error(
			'invalid_request',
			'API-key fields are only valid for API-key authorization.',
			array( 'status' => 400 )
		);
	}

	if ( 'connect' === $operation ) {
		$response = brandagent_connectors_api_request(
			'POST',
			'',
			array( 'providerId' => $provider_id )
		);
		return is_wp_error( $response ) ? $response : brandagent_connectors_validate_authorization_response( $response );
	}

	$status = brandagent_connectors_get_raw_status();
	if ( is_wp_error( $status ) ) {
		return $status;
	}

	$connector = brandagent_connectors_find_provider_item( $status['connectors'], $provider_id );
	if ( empty( $connector['connectorId'] ) ) {
		return new WP_Error(
			'connector_not_found',
			'No connector exists for the requested provider.',
			array( 'status' => 404 )
		);
	}

	$connector_id = rawurlencode( $connector['connectorId'] );
	switch ( $operation ) {
		case 'reauthorize':
			$response = brandagent_connectors_api_request(
				'POST',
				'/' . $connector_id . ':reauthorize',
				$reauthorization_payload
			);
			return is_wp_error( $response ) ? $response : brandagent_connectors_validate_authorization_response( $response );
		case 'sync':
			$response = brandagent_connectors_api_request( 'POST', '/' . $connector_id . ':sync' );
			return is_wp_error( $response ) ? $response : brandagent_connectors_project_sync_response_for_ui( $response );
		case 'disconnect':
			$response = brandagent_connectors_api_request( 'DELETE', '/' . $connector_id );
			return is_wp_error( $response ) ? $response : array( 'disconnected' => true );
	}

	return new WP_Error(
		'invalid_operation',
		'Unsupported connector operation.',
		array( 'status' => 400 )
	);
}

	/**
	 * Validate a submitted API key without changing its supported character set.
	 *
	 * @param mixed $value Candidate API key.
	 * @return string|WP_Error Trimmed key or validation error.
	 */
	function brandagent_connectors_validate_api_key( $value ) {
		if ( ! is_string( $value ) || strlen( $value ) > BRANDAGENT_CONNECTOR_MAXIMUM_API_KEY_BYTES ) {
			return new WP_Error(
				'invalid_api_key',
				'Enter a valid Housecall Pro API key.',
				array( 'status' => 400 )
			);
		}

		if ( preg_match( '/[\x00-\x1F\x7F]/', $value ) ) {
			return new WP_Error(
				'invalid_api_key',
				'Enter a valid Housecall Pro API key.',
				array( 'status' => 400 )
			);
		}

		$value = trim( $value );
		if ( '' === $value ) {
			return new WP_Error(
				'api_key_required',
				'Enter your Housecall Pro API key.',
				array( 'status' => 400 )
			);
		}

		return $value;
	}

	/**
	 * Parse a bounded appointment duration.
	 *
	 * @param mixed $value Candidate duration.
	 * @return int|null Parsed duration or null when invalid.
	 */
	function brandagent_connectors_parse_duration_minutes( $value ) {
		if ( is_int( $value ) ) {
			$parsed = $value;
		} elseif ( is_string( $value ) && preg_match( '/^[1-9][0-9]*$/', $value ) ) {
			$parsed = (int) $value;
		} else {
			return null;
		}

		return $parsed >= 1 && $parsed <= BRANDAGENT_CONNECTOR_MAXIMUM_DURATION_MINUTES
			? $parsed
			: null;
	}

	/**
	 * Parse a persisted duration, allowing zero only for an unselected service.
	 *
	 * @param mixed $value    Candidate duration.
	 * @param bool  $selected Whether the service is selected.
	 * @return int|null Parsed duration or null when invalid.
	 */
	function brandagent_connectors_parse_persisted_duration_minutes( $value, $selected ) {
		if ( false === $selected && 0 === $value ) {
			return 0;
		}

		return brandagent_connectors_parse_duration_minutes( $value );
	}

	/**
	 * Parse legacy or structured Housecall Pro configuration.
	 *
	 * @param mixed $configuration    Structured browser configuration.
	 * @param mixed $duration_minutes Legacy uniform duration.
	 * @return array|WP_Error Validated configuration.
	 */
	function brandagent_connectors_parse_api_key_configuration( $configuration, $duration_minutes ) {
		if ( null === $configuration ) {
			$validated_duration = brandagent_connectors_parse_duration_minutes( $duration_minutes );
			if ( null === $validated_duration ) {
				return new WP_Error(
					'invalid_duration',
					'Enter a default duration from 1 to 1440 minutes.',
					array( 'status' => 400 )
				);
			}

			return array(
				'mode'            => 'default',
				'durationMinutes' => $validated_duration,
				'legacy'          => true,
			);
		}

		if ( null !== $duration_minutes || ! is_array( $configuration ) || ! isset( $configuration['mode'] ) ) {
			return new WP_Error(
				'invalid_configuration',
				'Enter a valid Housecall Pro service configuration.',
				array( 'status' => 400 )
			);
		}

		if ( 'default' === $configuration['mode'] ) {
			$validated_duration = isset( $configuration['durationMinutes'] )
				? brandagent_connectors_parse_duration_minutes( $configuration['durationMinutes'] )
				: null;
			if ( null === $validated_duration ) {
				return new WP_Error(
					'invalid_duration',
					'Enter a default duration from 1 to 1440 minutes.',
					array( 'status' => 400 )
				);
			}

			return array(
				'mode'            => 'default',
				'durationMinutes' => $validated_duration,
			);
		}

		if ( 'display_names' === $configuration['mode'] ) {
			if (
				! isset( $configuration['services'] )
				|| ! is_array( $configuration['services'] )
				|| empty( $configuration['services'] )
				|| count( $configuration['services'] ) > BRANDAGENT_CONNECTOR_MAXIMUM_SERVICE_COUNT
			) {
				return new WP_Error(
					'invalid_configuration',
					'Enter valid Housecall Pro product names.',
					array( 'status' => 400 )
				);
			}

			$services    = array();
			$service_ids = array();
			foreach ( $configuration['services'] as $service ) {
				$service_id   = is_array( $service ) && isset( $service['serviceId'] )
					? brandagent_connectors_validate_service_id( $service['serviceId'] )
					: null;
				$display_name = is_array( $service ) && isset( $service['displayName'] )
					? brandagent_connectors_validate_display_name( $service['displayName'] )
					: null;
				if (
					null === $service_id
					|| null === $display_name
					|| isset( $service_ids[ $service_id ] )
				) {
					return new WP_Error(
						'invalid_configuration',
						'Enter valid Housecall Pro product names.',
						array( 'status' => 400 )
					);
				}

				$service_ids[ $service_id ] = true;
				$services[]                 = array(
					'serviceId'   => $service_id,
					'displayName' => $display_name,
				);
			}

			return array(
				'mode'     => 'display_names',
				'services' => $services,
			);
		}

		if (
			'per_service' !== $configuration['mode']
			|| ! isset( $configuration['services'] )
			|| ! is_array( $configuration['services'] )
			|| count( $configuration['services'] ) > BRANDAGENT_CONNECTOR_MAXIMUM_SERVICE_COUNT
		) {
			return new WP_Error(
				'invalid_configuration',
				'Enter a valid Housecall Pro service configuration.',
				array( 'status' => 400 )
			);
		}

		$services    = array();
		$service_ids = array();
		foreach ( $configuration['services'] as $service ) {
			$service_id = is_array( $service ) && isset( $service['serviceId'] )
				? brandagent_connectors_validate_service_id( $service['serviceId'] )
				: null;
			$duration   = is_array( $service ) && isset( $service['durationMinutes'] )
				? brandagent_connectors_parse_duration_minutes( $service['durationMinutes'] )
				: null;
			if (
				null === $service_id
				|| null === $duration
				|| isset( $service_ids[ $service_id ] )
			) {
				return new WP_Error(
					'invalid_configuration',
					'Enter a valid Housecall Pro service configuration.',
					array( 'status' => 400 )
				);
			}

			$service_ids[ $service_id ] = true;
			$services[]                 = array(
				'serviceId'       => $service_id,
				'durationMinutes' => $duration,
			);
		}

		return array(
			'mode'     => 'per_service',
			'services' => $services,
		);
	}

	/**
	 * Validate the canonical service-catalog precondition.
	 *
	 * @param mixed $value Candidate version.
	 * @return string|null Valid version.
	 */
	function brandagent_connectors_validate_service_catalog_version( $value ) {
		return is_string( $value ) && preg_match( '/^v1:[a-f0-9]{64}$/', $value )
			? $value
			: null;
	}

	/**
	 * Validate a browser-supplied service id.
	 *
	 * @param mixed $value Candidate service id.
	 * @return string|null Valid id.
	 */
	function brandagent_connectors_validate_service_id( $value ) {
		if (
			! is_string( $value )
			|| '' === $value
			|| trim( $value ) !== $value
			|| strlen( $value ) > BRANDAGENT_CONNECTOR_MAXIMUM_SERVICE_ID_BYTES
			|| preg_match( '/[\x00-\x1F\x7F]/', $value )
		) {
			return null;
		}

		return $value;
	}

	/**
	 * Check authorization independently of recoverable setup or profile-sync readiness.
	 *
	 * @param mixed $connector Connector state.
	 * @return bool Whether the backend explicitly authorized the connector.
	 */
	function brandagent_connectors_is_authorized( $connector ) {
		return is_array( $connector )
			&& isset( $connector['authorizationStatus'] )
			&& is_string( $connector['authorizationStatus'] )
			&& 0 === strcasecmp( 'authorized', $connector['authorizationStatus'] );
	}

	/**
	 * Provision an API key, verify connector authorization, and apply service defaults.
	 *
	 * @param string     $provider_id            API-key provider id.
	 * @param string     $api_key                Validated API key.
	 * @param array      $configuration          Validated service configuration.
	 * @param array|null $reauthorization_payload Optional pending-authorization replacement payload.
	 * @return array|WP_Error Sanitized connection result.
	 */
	function brandagent_connectors_connect_api_key_provider(
		$provider_id,
		$api_key,
		$configuration,
		$reauthorization_payload = null
	) {
		$status = brandagent_connectors_get_raw_status();
		if ( is_wp_error( $status ) ) {
			return $status;
		}

		$existing_connector = brandagent_connectors_find_provider_item( $status['connectors'], $provider_id );
		$is_active_connector = is_array( $existing_connector )
			&& ! empty( $existing_connector['connectorId'] )
			&& (
				! isset( $existing_connector['authorizationStatus'] )
				|| 'disconnected' !== strtolower( (string) $existing_connector['authorizationStatus'] )
			);

		$validated = brandagent_connectors_validate_housecall_pro_api_key( $api_key );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		if ( $is_active_connector ) {
			$authorization = brandagent_connectors_api_request(
				'POST',
				'/' . rawurlencode( $existing_connector['connectorId'] ) . ':reauthorize',
				$reauthorization_payload
			);
		} else {
			$authorization = brandagent_connectors_api_request(
				'POST',
				'',
				array( 'providerId' => $provider_id )
			);
		}
		if ( is_wp_error( $authorization ) ) {
			return $authorization;
		}

		$authorization_state = is_array( $authorization ) && isset( $authorization['authorizationState'] )
			? $authorization['authorizationState']
			: '';
		if (
			! is_string( $authorization_state )
			|| '' === $authorization_state
			|| strlen( $authorization_state ) > 4096
			|| preg_match( '/[\x00-\x20\x7F]/', $authorization_state )
		) {
			return new WP_Error(
				'authorization_state_missing',
				'AdsAgentServer did not return a valid authorization state.',
				array( 'status' => 502 )
			);
		}

		$authorization_connector = isset( $authorization['connector'] ) && is_array( $authorization['connector'] )
			? $authorization['connector']
			: array();
		$authorization_version = isset( $authorization_connector['authorizationVersion'] )
			? brandagent_connectors_parse_authorization_version( $authorization_connector['authorizationVersion'] )
			: null;
		if (
			empty( $authorization_connector['connectorId'] )
			|| ! is_string( $authorization_connector['connectorId'] )
			|| null === $authorization_version
		) {
			return new WP_Error(
				'invalid_connector_response',
				'AdsAgentServer did not return a valid connector authorization attempt.',
				array( 'status' => 502 )
			);
		}

		// Begin/reauthorize must finish cleaning up the previous credential before storing its replacement.
		$stored = brandagent_connectors_set_housecall_pro_api_key( $api_key );
		if ( is_wp_error( $stored ) ) {
			return $stored;
		}

		$completed = brandagent_connectors_complete_api_key_callback( $provider_id, $authorization_state );
		if ( is_wp_error( $completed ) ) {
			return $completed;
		}

		$status = brandagent_connectors_get_raw_status();
		if ( is_wp_error( $status ) ) {
			return new WP_Error(
				'connector_status_refresh_failed',
				'Housecall Pro authorization could not be verified. Refresh the connector status before trying again.',
				array( 'status' => 502 )
			);
		}

		$connector = brandagent_connectors_find_provider_item( $status['connectors'], $provider_id );
		if ( empty( $connector['connectorId'] ) ) {
			return new WP_Error(
				'connector_status_refresh_failed',
				'Housecall Pro authorization could not be verified. Refresh the connector status before trying again.',
				array( 'status' => 502 )
			);
		}

		if (
			$authorization_connector['connectorId'] !== $connector['connectorId']
			|| ! isset( $connector['authorizationVersion'] )
			|| $authorization_version !== brandagent_connectors_parse_authorization_version( $connector['authorizationVersion'] )
		) {
			return new WP_Error(
				'authorization_version_mismatch',
				'The connector authorization changed. Refresh the connector status before trying again.',
				array(
					'status'    => 409,
					'connector' => brandagent_connectors_project_connector_for_ui( $connector ),
				)
			);
		}

		if ( ! brandagent_connectors_is_authorized( $connector ) ) {
			return new WP_Error(
				'authorization_callback_failed',
				'Housecall Pro authorization did not complete. Check the API key and reconnect.',
				array(
					'status'    => 409,
					'connector' => brandagent_connectors_project_connector_for_ui( $connector ),
				)
			);
		}

		$configured = ! empty( $configuration['legacy'] )
			? brandagent_connectors_apply_default_configuration(
				$provider_id,
				$configuration['durationMinutes'],
				$connector
			)
			: brandagent_connectors_apply_housecall_pro_configuration( $provider_id, $configuration, $connector );
		$result = array(
			'authorizationCompleted' => true,
			'connector'              => brandagent_connectors_project_connector_for_ui( $connector ),
			'configurationApplied'   => ! is_wp_error( $configured ),
		);
		if ( 'default' === $configuration['mode'] ) {
			$result['durationMinutes'] = $configuration['durationMinutes'];
		}
		if ( is_wp_error( $configured ) ) {
			$result['configurationErrorCode'] = $configured->get_error_code();
			$error_data                       = $configured->get_error_data();
			if ( is_array( $error_data ) && isset( $error_data['services'] ) ) {
				$result['services'] = $error_data['services'];
			}
			if ( is_array( $error_data ) && isset( $error_data['connector'] ) ) {
				$result['connector'] = brandagent_connectors_project_connector_for_ui( $error_data['connector'] );
			}
			$result['authorizationCompleted'] = brandagent_connectors_is_authorized( $result['connector'] );
			return $result;
		}

		$result['connector'] = $configured['connector'];
		$result['authorizationCompleted'] = brandagent_connectors_is_authorized( $result['connector'] );
		return $result;
	}

	/**
	 * Apply a keyless Housecall Pro configuration to the existing authorized connector.
	 *
	 * @param string $provider_id   Provider id.
	 * @param array  $configuration Validated configuration.
	 * @return array|WP_Error Safe result.
	 */
	function brandagent_connectors_apply_configuration_operation( $provider_id, $configuration ) {
		$status = brandagent_connectors_get_raw_status();
		if ( is_wp_error( $status ) ) {
			return $status;
		}

		$connector = brandagent_connectors_find_provider_item( $status['connectors'], $provider_id );
		if ( empty( $connector['connectorId'] ) ) {
			return new WP_Error(
				'connector_not_found',
				'No connector exists for the requested provider.',
				array( 'status' => 404 )
			);
		}

		$configured = brandagent_connectors_apply_housecall_pro_configuration(
			$provider_id,
			$configuration,
			$connector
		);
		$result = array(
			'authorizationCompleted' => brandagent_connectors_is_authorized( $connector ),
			'connector'              => brandagent_connectors_project_connector_for_ui( $connector ),
			'configurationApplied'   => ! is_wp_error( $configured ),
		);
		if ( 'default' === $configuration['mode'] ) {
			$result['durationMinutes'] = $configuration['durationMinutes'];
		}
		if ( is_wp_error( $configured ) ) {
			$result['configurationErrorCode'] = $configured->get_error_code();
			$error_data                       = $configured->get_error_data();
			if ( is_array( $error_data ) && isset( $error_data['services'] ) ) {
				$result['services'] = $error_data['services'];
			}
			if ( is_array( $error_data ) && isset( $error_data['connector'] ) ) {
				$result['connector'] = brandagent_connectors_project_connector_for_ui( $error_data['connector'] );
			}
			$result['authorizationCompleted'] = brandagent_connectors_is_authorized( $result['connector'] );
			return $result;
		}

		$result['connector'] = $configured['connector'];
		$result['authorizationCompleted'] = brandagent_connectors_is_authorized( $result['connector'] );
		return $result;
	}

	/**
	 * Reconcile browser durations with the canonical Housecall Pro catalog and replace configuration.
	 * Default mode may use backend-owned defaults when legacy connector data lacks a valid catalog.
	 *
	 * @param string $provider_id   Provider id.
	 * @param array  $configuration Validated configuration.
	 * @param array  $connector     Raw canonical connector.
	 * @return array|WP_Error Sanitized connector result.
	 */
	function brandagent_connectors_apply_housecall_pro_configuration( $provider_id, $configuration, $connector ) {
		if ( ! brandagent_connectors_is_authorized( $connector ) ) {
			return new WP_Error(
				'connection_not_connected',
				'Authorize Housecall Pro before applying configuration.',
				array(
					'status'    => 409,
					'connector' => brandagent_connectors_project_connector_for_ui( $connector ),
				)
			);
		}

		$catalog = brandagent_connectors_get_canonical_housecall_pro_catalog( $connector );
		if ( is_wp_error( $catalog ) ) {
			if (
				'default' === $configuration['mode']
				&& 'incomplete_service_catalog' === $catalog->get_error_code()
			) {
				return brandagent_connectors_apply_default_configuration(
					$provider_id,
					$configuration['durationMinutes'],
					$connector
				);
			}
			return $catalog;
		}

		$submitted_durations    = array();
		$submitted_display_names = array();
		if ( 'per_service' === $configuration['mode'] ) {
			foreach ( $configuration['services'] as $service ) {
				$submitted_durations[ $service['serviceId'] ] = $service['durationMinutes'];
			}
		} elseif ( 'display_names' === $configuration['mode'] ) {
			foreach ( $configuration['services'] as $service ) {
				$submitted_display_names[ $service['serviceId'] ] = $service['displayName'];
			}
		}

		$desired_services = array();
		$safe_services    = array();
		foreach ( $catalog['services'] as $service ) {
			$duration = 'default' === $configuration['mode']
				? $configuration['durationMinutes']
				: (
					'per_service' === $configuration['mode']
						? (
							isset( $submitted_durations[ $service['serviceId'] ] )
								? $submitted_durations[ $service['serviceId'] ]
								: BRANDAGENT_CONNECTOR_DEFAULT_DURATION_MINUTES
						)
						: $service['durationMinutes']
				);
			$display_name = isset( $submitted_display_names[ $service['serviceId'] ] )
				? $submitted_display_names[ $service['serviceId'] ]
				: $service['displayName'];
			unset( $submitted_durations[ $service['serviceId'] ] );
			unset( $submitted_display_names[ $service['serviceId'] ] );

			$desired_services[] = array(
				'serviceId'        => $service['serviceId'],
				'selected'         => 'display_names' === $configuration['mode'] ? $service['selected'] : true,
				'durationMinutes'  => $duration,
				'amountMinorUnits' => $service['amountMinorUnits'],
				'currency'         => $service['currency'],
				'displayName'      => $display_name,
			);
			$safe_services[]    = array(
				'serviceId'       => $service['serviceId'],
				'name'            => $service['name'],
				'displayName'     => $display_name,
				'selected'        => 'display_names' === $configuration['mode'] ? $service['selected'] : true,
				'durationMinutes' => $duration,
			);
		}

		if ( ! empty( $submitted_durations ) || ! empty( $submitted_display_names ) ) {
			return new WP_Error(
				'stale_service_catalog',
				'Housecall Pro services changed. Review the refreshed service list and try again.',
				array(
					'status'   => 409,
					'services' => $safe_services,
				)
			);
		}

		$configured = brandagent_connectors_api_request(
			'PATCH',
			'/' . rawurlencode( $connector['connectorId'] ) . '/configuration',
			array(
				'serviceCatalogVersion' => $catalog['serviceCatalogVersion'],
				'services'              => $desired_services,
			)
		);
		if ( is_wp_error( $configured ) ) {
			if ( 'stale_service_catalog' === $configured->get_error_code() ) {
				return brandagent_connectors_refresh_stale_service_catalog( $provider_id );
			}
			return $configured;
		}

		return array(
			'connector' => brandagent_connectors_project_connector_for_ui( $configured ),
		);
	}

	/**
	 * Refresh safe recovery data after a backend catalog-version conflict.
	 *
	 * @param string $provider_id Provider id.
	 * @return WP_Error Safe stale-catalog error.
	 */
	function brandagent_connectors_refresh_stale_service_catalog( $provider_id ) {
		$status = brandagent_connectors_get_raw_status();
		$data   = array( 'status' => 409 );
		if ( ! is_wp_error( $status ) ) {
			$connector = brandagent_connectors_find_provider_item( $status['connectors'], $provider_id );
			if ( is_array( $connector ) ) {
				$services = brandagent_connectors_project_housecall_pro_services_for_ui( $connector );
				if ( ! is_wp_error( $services ) ) {
					$data['services'] = $services;
				}
				$data['connector'] = $connector;
			}
		}

		return new WP_Error(
			'stale_service_catalog',
			'Housecall Pro services changed. Review the refreshed service list and try again.',
			$data
		);
	}

	/**
	 * Validate canonical Housecall Pro catalog used to construct the configuration replacement.
	 *
	 * @param array $connector Raw connector.
	 * @return array|WP_Error Canonical catalog.
	 */
	function brandagent_connectors_get_canonical_housecall_pro_catalog( $connector ) {
		if (
			! isset( $connector['providerDetails'] )
			|| ! is_array( $connector['providerDetails'] )
			|| ! array_key_exists( 'serviceCatalogVersion', $connector['providerDetails'] )
			|| ! array_key_exists( 'services', $connector['providerDetails'] )
			|| ! is_array( $connector['providerDetails']['services'] )
		) {
			return new WP_Error(
				'incomplete_service_catalog',
				'Housecall Pro returned an incomplete service catalog.',
				array( 'status' => 502 )
			);
		}

		$provider_details = $connector['providerDetails'];
		$services         = $provider_details['services'];
		$catalog_version  = brandagent_connectors_validate_service_catalog_version(
			$provider_details['serviceCatalogVersion']
		);
		if (
			null === $catalog_version
			|| empty( $services )
			|| count( $services ) > BRANDAGENT_CONNECTOR_MAXIMUM_SERVICE_COUNT
		) {
			return new WP_Error(
				'invalid_service_catalog',
				'Housecall Pro returned an invalid service catalog.',
				array( 'status' => 502 )
			);
		}

		$canonical   = array();
		$service_ids = array();
		foreach ( $services as $service ) {
			$service_id = is_array( $service ) && isset( $service['serviceId'] )
				? brandagent_connectors_validate_service_id( $service['serviceId'] )
				: null;
			$name       = is_array( $service ) && isset( $service['name'] )
				? brandagent_connectors_validate_service_name( $service['name'] )
				: null;
			$display_name = is_array( $service ) && isset( $service['displayName'] )
				? brandagent_connectors_validate_display_name( $service['displayName'] )
				: ( null !== $name ? brandagent_connectors_default_display_name( $name ) : null );
			if (
				null === $service_id
				|| null === $name
				|| null === $display_name
				|| isset( $service_ids[ $service_id ] )
			) {
				return new WP_Error(
					'invalid_service_catalog',
					'Housecall Pro returned an invalid service catalog.',
					array( 'status' => 502 )
				);
			}

			if (
				! array_key_exists( 'selected', $service )
				|| ! array_key_exists( 'durationMinutes', $service )
				|| ! array_key_exists( 'amountMinorUnits', $service )
				|| ! array_key_exists( 'currency', $service )
			) {
				return new WP_Error(
					'incomplete_service_catalog',
					'Housecall Pro returned an incomplete service catalog.',
					array( 'status' => 502 )
				);
			}

			if (
				! is_bool( $service['selected'] )
				|| null === brandagent_connectors_parse_persisted_duration_minutes(
					$service['durationMinutes'],
					$service['selected']
				)
				|| ! is_int( $service['amountMinorUnits'] )
				|| $service['amountMinorUnits'] < 0
				|| (
					null !== $service['currency']
					&& (
						! is_string( $service['currency'] )
						|| ! preg_match( '/^[A-Z]{3}$/', $service['currency'] )
					)
				)
			) {
				return new WP_Error(
					'invalid_service_catalog',
					'Housecall Pro returned an invalid service catalog.',
					array( 'status' => 502 )
				);
			}

			$service_ids[ $service_id ] = true;
			$canonical[]                = array(
				'serviceId'        => $service_id,
				'name'             => $name,
				'displayName'      => $display_name,
				'selected'         => $service['selected'],
				'durationMinutes'  => $service['durationMinutes'],
				'amountMinorUnits' => $service['amountMinorUnits'],
				'currency'         => $service['currency'],
			);
		}

		return array(
			'serviceCatalogVersion' => $catalog_version,
			'services'              => $canonical,
		);
	}

	/**
	 * Apply a default duration to an existing Housecall Pro connector.
	 *
	 * @param string     $provider_id     Provider id.
	 * @param int        $duration_minutes Validated duration.
	 * @param array|null $connector       Optional raw connector already loaded by the caller.
	 * @return array|WP_Error Sanitized connector result.
	 */
	function brandagent_connectors_apply_default_configuration( $provider_id, $duration_minutes, $connector = null ) {
		if ( null === $connector ) {
			$status = brandagent_connectors_get_raw_status();
			if ( is_wp_error( $status ) ) {
				return $status;
			}
			$connector = brandagent_connectors_find_provider_item( $status['connectors'], $provider_id );
		}

		if ( empty( $connector['connectorId'] ) ) {
			return new WP_Error(
				'connector_not_found',
				'No connector exists for the requested provider.',
				array( 'status' => 404 )
			);
		}

		if ( ! brandagent_connectors_is_authorized( $connector ) ) {
			return new WP_Error(
				'connection_not_connected',
				'Authorize Housecall Pro before applying configuration.',
				array(
					'status'    => 409,
					'connector' => brandagent_connectors_project_connector_for_ui( $connector ),
				)
			);
		}

		$configured = brandagent_connectors_api_request(
			'POST',
			'/' . rawurlencode( $connector['connectorId'] ) . '/configuration:defaults?durationMinutes=' . $duration_minutes
		);
		if ( is_wp_error( $configured ) ) {
			return $configured;
		}

		return array(
			'connector'       => brandagent_connectors_project_connector_for_ui( $configured ),
			'durationMinutes' => $duration_minutes,
		);
	}

	/**
	 * Validate and store the Housecall Pro API key through the WordPress-HMAC endpoint.
	 *
	 * @param string $api_key Validated API key.
	 * @return array|WP_Error Decoded response.
	 */
	function brandagent_connectors_set_housecall_pro_api_key( $api_key ) {
		$response = brandagent_connectors_signed_api_request(
			'POST',
			BRANDAGENT_HOUSECALL_PRO_API_KEY_PATH,
			array( 'apiKey' => $api_key )
		);
		if ( ! is_wp_error( $response ) ) {
			return $response;
		}

		if ( in_array( $response->get_error_code(), array( 'invalid_api_key', 'read_only_api_key' ), true ) ) {
			$error_code = $response->get_error_code();
			return new WP_Error(
				$error_code,
				'invalid_api_key' === $error_code
					? 'Housecall Pro rejected this API key. Double-check it and try again.'
					: 'This Housecall Pro API key does not have write access.',
				array( 'status' => 422 )
			);
		}

		return new WP_Error(
			'api_key_validation_failed',
			'Housecall Pro API key could not be verified. Try again shortly.',
			array( 'status' => 502 )
		);
	}

	/**
	 * Validate Housecall Pro access without storing credentials or creating a connector.
	 *
	 * @param string $api_key Validated API key.
	 * @return array|WP_Error Browser-safe validation result.
	 */
	function brandagent_connectors_validate_housecall_pro_api_key( $api_key ) {
		$response = brandagent_connectors_signed_api_request(
			'POST',
			BRANDAGENT_HOUSECALL_PRO_API_KEY_VALIDATE_PATH,
			array( 'apiKey' => $api_key )
		);
		if ( ! is_wp_error( $response ) ) {
			$services = brandagent_connectors_project_service_preview_catalog( $response );
			if ( is_wp_error( $services ) ) {
				return $services;
			}
			return array(
				'valid'    => true,
				'services' => $services,
			);
		}

		if ( in_array( $response->get_error_code(), array( 'invalid_api_key', 'read_only_api_key' ), true ) ) {
			$error_code = $response->get_error_code();
			return new WP_Error(
				$error_code,
				'invalid_api_key' === $error_code
					? 'Housecall Pro rejected this API key. Double-check it and try again.'
					: 'This Housecall Pro API key does not have write access.',
				array( 'status' => 422 )
			);
		}

		return new WP_Error(
			'api_key_validation_failed',
			'Housecall Pro API key could not be verified. Try again shortly.',
			array( 'status' => 502 )
		);
	}

	/**
	 * Validate and project the provider service preview.
	 *
	 * @param mixed $response Raw validation response.
	 * @return array|WP_Error Browser-safe service catalog.
	 */
	function brandagent_connectors_project_service_preview_catalog( $response ) {
		$encoded = wp_json_encode( $response );
		if (
			! is_array( $response )
			|| true !== ( isset( $response['valid'] ) ? $response['valid'] : false )
			|| ! isset( $response['services'] )
			|| ! is_array( $response['services'] )
			|| count( $response['services'] ) > BRANDAGENT_CONNECTOR_MAXIMUM_SERVICE_COUNT
			|| false === $encoded
			|| strlen( $encoded ) > BRANDAGENT_CONNECTOR_MAXIMUM_SERVICE_CATALOG_BYTES
		) {
			return new WP_Error(
				'invalid_service_catalog',
				'Housecall Pro returned an invalid service catalog.',
				array( 'status' => 502 )
			);
		}

		$services    = array();
		$service_ids = array();
		foreach ( $response['services'] as $service ) {
			$service_id = is_array( $service ) && isset( $service['serviceId'] )
				? brandagent_connectors_validate_service_id( $service['serviceId'] )
				: null;
			$name       = is_array( $service ) && isset( $service['name'] )
				? brandagent_connectors_validate_service_name( $service['name'] )
				: null;
			if ( null === $service_id || null === $name || isset( $service_ids[ $service_id ] ) ) {
				return new WP_Error(
					'invalid_service_catalog',
					'Housecall Pro returned an invalid service catalog.',
					array( 'status' => 502 )
				);
			}

			$service_ids[ $service_id ] = true;
			$services[]                 = array(
				'serviceId' => $service_id,
				'name'      => $name,
			);
		}

		return $services;
	}

	/**
	 * Validate a provider-owned service name.
	 *
	 * @param mixed $value Candidate name.
	 * @return string|null Valid name.
	 */
	function brandagent_connectors_validate_service_name( $value ) {
		if (
			! is_string( $value )
			|| '' === $value
			|| trim( $value ) !== $value
			|| strlen( $value ) > BRANDAGENT_CONNECTOR_MAXIMUM_SERVICE_NAME_BYTES
			|| preg_match( '/[\x00-\x1F\x7F]/', $value )
		) {
			return null;
		}

		return $value;
	}

	/**
	 * Complete the state-gated API-key callback without exposing the state to the browser.
	 *
	 * @param string $provider_id        Provider id.
	 * @param string $authorization_state One-time authorization state.
	 * @return array|WP_Error Transport result; authorization must be verified from connector status.
	 */
	function brandagent_connectors_complete_api_key_callback( $provider_id, $authorization_state ) {
		$backend = brandagent_connectors_get_backend_base_url();
		if ( is_wp_error( $backend ) ) {
			return $backend;
		}

		$url = rtrim( $backend, '/' )
			. '/api/connectors/oauth/'
			. rawurlencode( $provider_id )
			. '/api-key/callback?state='
			. rawurlencode( $authorization_state );
		$response = wp_remote_get( $url, array( 'timeout' => 30 ) );
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'connector_transport_failed',
				'Housecall Pro authorization could not be completed.',
				array( 'status' => 502 )
			);
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		if ( $status < 200 || $status >= 400 ) {
			return new WP_Error(
				'authorization_callback_failed',
				'Housecall Pro authorization could not be completed.',
				array( 'status' => 502 )
			);
		}

		// Completion HTML is deliberately neutral, including when authorization failed.
		return array();
	}

/**
 * Parse a positive AdsAgentServer authorization version.
 *
 * @param mixed $value Candidate version.
 * @return int|null Parsed version.
 */
function brandagent_connectors_parse_authorization_version( $value ) {
	if ( is_int( $value ) ) {
		return $value > 0 && $value <= 2147483647 ? $value : null;
	}

	if ( ! is_string( $value ) || ! preg_match( '/^[1-9][0-9]*$/', $value ) ) {
		return null;
	}

	$parsed = (int) $value;
	return $parsed > 0 && $parsed <= 2147483647 && (string) $parsed === $value
		? $parsed
		: null;
}

/**
 * Get browser-safe provider metadata and connector status.
 *
 * @return array|WP_Error Status payload.
 */
function brandagent_connectors_get_status() {
	$status = brandagent_connectors_get_raw_status();
	if ( is_wp_error( $status ) ) {
		return $status;
	}

	return array(
		'providers'                       => array_map( 'brandagent_connectors_project_provider_for_ui', $status['providers'] ),
		'connectors'                      => array_map( 'brandagent_connectors_project_connector_for_ui', $status['connectors'] ),
		'supportedProviderIds'            => brandagent_connectors_get_available_provider_ids( $status['providers'] ),
		'apiKeyValidationProviderIds'     => array( BRANDAGENT_CONNECTOR_HOUSECALL_PRO_PROVIDER_ID ),
		'apiKeyServicePreviewProviderIds' => array( BRANDAGENT_CONNECTOR_HOUSECALL_PRO_PROVIDER_ID ),
		'displayNameConfigurationProviderIds' => array( BRANDAGENT_CONNECTOR_HOUSECALL_PRO_PROVIDER_ID ),
	);
}

/**
 * Get raw allowlisted provider and connector objects for server-side operations.
 *
 * @return array|WP_Error Raw status payload.
 */
function brandagent_connectors_get_raw_status() {
	$providers = brandagent_connectors_api_request( 'GET', '/providers' );
	if ( is_wp_error( $providers ) ) {
		return $providers;
	}

	$connectors = brandagent_connectors_api_request( 'GET' );
	if ( is_wp_error( $connectors ) ) {
		return $connectors;
	}

	return array(
		'providers'  => brandagent_connectors_only_supported_items( $providers ),
		'connectors' => brandagent_connectors_only_supported_items( $connectors ),
	);
}

/**
 * Get enabled provider ids from the allowlisted provider metadata returned by AdsAgentServer.
 *
 * @param mixed $providers Provider metadata list.
 * @return array
 */
function brandagent_connectors_get_available_provider_ids( $providers ) {
	$provider_ids = array();
	if ( ! is_array( $providers ) ) {
		return $provider_ids;
	}

	foreach ( $providers as $provider ) {
		if ( is_array( $provider ) && isset( $provider['providerId'] ) && is_string( $provider['providerId'] ) ) {
			$provider_id = strtolower( $provider['providerId'] );
			// Older Calendly backends advertised interactive configuration. Only expose the zero-config contract.
			if (
				BRANDAGENT_CONNECTOR_CALENDLY_PROVIDER_ID === $provider_id
				&& ( ! array_key_exists( 'supportsConfiguration', $provider ) || false !== $provider['supportsConfiguration'] )
			) {
				continue;
			}
			$provider_ids[] = $provider_id;
		}
	}

	return array_values( array_unique( $provider_ids ) );
}

/**
 * Project provider metadata onto the fields required by the dashboard.
 *
 * @param mixed $provider Raw provider object.
 * @return array Browser-safe provider object.
 */
function brandagent_connectors_project_provider_for_ui( $provider ) {
	return brandagent_connectors_copy_allowed_fields(
		$provider,
		array(
			'providerId',
			'displayName',
		)
	);
}

/**
 * Project connector state without exposing its id or provider-specific account details.
 *
 * @param mixed $connector Raw connector object.
 * @return array Browser-safe connector object.
 */
function brandagent_connectors_project_connector_for_ui( $connector ) {
	$projected = brandagent_connectors_copy_allowed_fields(
		$connector,
		array(
			'providerId',
			'authorizationStatus',
			'authorizationVersion',
			'authorizationExpiresAtUtc',
			'readinessStatus',
			'failureCode',
			'lastSynchronizedAtUtc',
			'canReauthorize',
			'canSynchronize',
			'canDisconnect',
			'supportsConfiguration',
		)
	);

	if ( is_array( $connector ) && isset( $connector['capabilities'] ) && is_array( $connector['capabilities'] ) ) {
		$projected['capabilities'] = array_map(
			'brandagent_connectors_project_capability_for_ui',
			$connector['capabilities']
		);
	}

	if (
		is_array( $connector )
		&& isset( $connector['providerId'] )
		&& BRANDAGENT_CONNECTOR_HOUSECALL_PRO_PROVIDER_ID === strtolower( (string) $connector['providerId'] )
	) {
		$services = brandagent_connectors_project_housecall_pro_services_for_ui( $connector );
		if ( ! is_wp_error( $services ) ) {
			$projected['services'] = $services;
		}
	}

	return $projected;
}

/**
 * Project canonical Housecall Pro service configuration without exposing provider details.
 *
 * @param mixed $connector Raw connector.
 * @return array|WP_Error Browser-safe service configuration.
 */
function brandagent_connectors_project_housecall_pro_services_for_ui( $connector ) {
	$services = is_array( $connector )
		&& isset( $connector['providerDetails'] )
		&& is_array( $connector['providerDetails'] )
		&& isset( $connector['providerDetails']['services'] )
		? $connector['providerDetails']['services']
		: array();
	if ( ! is_array( $services ) || count( $services ) > BRANDAGENT_CONNECTOR_MAXIMUM_SERVICE_COUNT ) {
		return new WP_Error(
			'invalid_service_catalog',
			'Housecall Pro returned an invalid service catalog.',
			array( 'status' => 502 )
		);
	}

	$projected   = array();
	$service_ids = array();
	foreach ( $services as $service ) {
		$service_id = is_array( $service ) && isset( $service['serviceId'] )
			? brandagent_connectors_validate_service_id( $service['serviceId'] )
			: null;
		$name       = is_array( $service ) && isset( $service['name'] )
			? brandagent_connectors_validate_service_name( $service['name'] )
			: null;
		$display_name = is_array( $service ) && isset( $service['displayName'] )
			? brandagent_connectors_validate_display_name( $service['displayName'] )
			: ( null !== $name ? brandagent_connectors_default_display_name( $name ) : null );
		$duration   = is_array( $service ) && isset( $service['durationMinutes'] ) && isset( $service['selected'] )
			? brandagent_connectors_parse_persisted_duration_minutes(
				$service['durationMinutes'],
				$service['selected']
			)
			: null;
		if (
			null === $service_id
			|| null === $name
			|| null === $display_name
			|| null === $duration
			|| ! isset( $service['selected'] )
			|| ! is_bool( $service['selected'] )
			|| isset( $service_ids[ $service_id ] )
		) {
			return new WP_Error(
				'invalid_service_catalog',
				'Housecall Pro returned an invalid service catalog.',
				array( 'status' => 502 )
			);
		}

		$service_ids[ $service_id ] = true;
		$projected[]                = array(
			'serviceId'       => $service_id,
			'name'            => $name,
			'displayName'     => $display_name,
			'selected'        => $service['selected'],
			'durationMinutes' => $duration,
		);
	}

	return $projected;
}

/**
 * Project one connector capability.
 *
 * @param mixed $capability Raw capability object.
 * @return array Browser-safe capability object.
 */
function brandagent_connectors_project_capability_for_ui( $capability ) {
	return brandagent_connectors_copy_allowed_fields( $capability, array( 'name', 'isReady' ) );
}

/**
 * Project a synchronization result and its nested connector state.
 *
 * @param mixed $response Raw synchronization response.
 * @return array Browser-safe synchronization response.
 */
function brandagent_connectors_project_sync_response_for_ui( $response ) {
	$projected = brandagent_connectors_copy_allowed_fields(
		$response,
		array(
			'synchronizationStatus',
			'failureCode',
		)
	);

	if ( is_array( $response ) && isset( $response['connector'] ) && is_array( $response['connector'] ) ) {
		$projected['connector'] = brandagent_connectors_project_connector_for_ui( $response['connector'] );
	}

	return $projected;
}

/**
 * Copy only explicitly allowlisted fields from an API object.
 *
 * @param mixed $item   API object.
 * @param array $fields Allowed field names.
 * @return array
 */
function brandagent_connectors_copy_allowed_fields( $item, $fields ) {
	$projected = array();
	if ( ! is_array( $item ) ) {
		return $projected;
	}

	foreach ( $fields as $field ) {
		if ( array_key_exists( $field, $item ) ) {
			$projected[ $field ] = $item[ $field ];
		}
	}

	return $projected;
}

/**
 * Keep only explicitly supported entries from a connector/provider list.
 *
 * @param mixed $items API list.
 * @return array
 */
function brandagent_connectors_only_supported_items( $items ) {
	$supported = array();
	if ( ! is_array( $items ) ) {
		return $supported;
	}

	foreach ( $items as $item ) {
		if (
			is_array( $item )
			&& isset( $item['providerId'] )
			&& is_string( $item['providerId'] )
			&& in_array( strtolower( $item['providerId'] ), brandagent_connectors_supported_provider_ids(), true )
		) {
			$supported[] = $item;
		}
	}

	return array_values( $supported );
}

/**
 * Find a provider-owned item in an API list.
 *
 * @param mixed  $items       API response.
 * @param string $provider_id Provider id.
 * @return array|null Matching item.
 */
function brandagent_connectors_find_provider_item( $items, $provider_id ) {
	if ( ! is_array( $items ) ) {
		return null;
	}

	$disconnected_item = null;
	foreach ( $items as $item ) {
		if (
			is_array( $item )
			&& isset( $item['providerId'] )
			&& is_string( $item['providerId'] )
			&& 0 === strcasecmp( $item['providerId'], $provider_id )
		) {
			if (
				! isset( $item['authorizationStatus'] )
				|| 'disconnected' !== strtolower( (string) $item['authorizationStatus'] )
			) {
				return $item;
			}
			if ( null === $disconnected_item ) {
				$disconnected_item = $item;
			}
		}
	}

	return $disconnected_item;
}

/**
 * Validate that an authorization response contains a safe OAuth URL.
 *
 * @param mixed $response API response.
 * @return array|WP_Error Valid response.
 */
function brandagent_connectors_validate_authorization_response( $response ) {
	$url = is_array( $response ) && isset( $response['authorizationUrl'] )
		? $response['authorizationUrl']
		: '';

	if ( ! wp_http_validate_url( $url ) || 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) ) {
		return new WP_Error(
			'invalid_authorization_url',
			'AdsAgentServer did not return a valid HTTPS authorization URL.',
			array( 'status' => 502 )
		);
	}

	$projected = array( 'authorizationUrl' => $url );
	if ( isset( $response['connector'] ) && is_array( $response['connector'] ) ) {
		$projected['connector'] = brandagent_connectors_project_connector_for_ui( $response['connector'] );
	}
	return $projected;
}

/**
 * Call the advertiser-scoped connector facade with the site's WordPress HMAC credential.
 *
 * @param string     $method  HTTP method.
 * @param string     $suffix  Path suffix.
 * @param array|null $payload Optional JSON payload.
 * @return array|WP_Error Decoded response.
 */
function brandagent_connectors_api_request( $method, $suffix = '', $payload = null ) {
	return brandagent_connectors_signed_api_request(
		$method,
		BRANDAGENT_CONNECTORS_PATH . $suffix,
		$payload
	);
}

/**
 * Resolve the configured AdsAgentServer base URL.
 *
 * @return string|WP_Error Base URL or configuration error.
 */
function brandagent_connectors_get_backend_base_url() {
	if ( ! class_exists( 'BrandAgent_Config' ) || ! method_exists( 'BrandAgent_Config', 'get_backend_base_url' ) ) {
		return new WP_Error(
			'backend_unavailable',
			'Brand Agent backend URL is unavailable.',
			array( 'status' => 503 )
		);
	}

	$backend = BrandAgent_Config::get_backend_base_url();
	if ( empty( $backend ) || ! wp_http_validate_url( $backend ) ) {
		return new WP_Error(
			'backend_unavailable',
			'Brand Agent backend URL is unavailable.',
			array( 'status' => 503 )
		);
	}

	return $backend;
}

/**
 * Send and decode one WordPress-HMAC request to AdsAgentServer.
 *
 * @param string     $method  HTTP method.
 * @param string     $path    Absolute backend path including any query.
 * @param array|null $payload Optional JSON payload.
 * @return array|WP_Error Decoded response.
 */
function brandagent_connectors_signed_api_request( $method, $path, $payload = null ) {
	$backend = brandagent_connectors_get_backend_base_url();
	if ( is_wp_error( $backend ) ) {
		return $backend;
	}

	$url  = rtrim( $backend, '/' ) . $path;
	$body = null === $payload ? '' : wp_json_encode( $payload );
	if ( false === $body ) {
		return new WP_Error(
			'request_encoding_failed',
			'Could not encode the connector request.',
			array( 'status' => 500 )
		);
	}

	$response = brandagent_wordpress_sign_outbound_request( $url, $path, $body, $method );
	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'connector_transport_failed',
			$response->get_error_message(),
			array( 'status' => 502 )
		);
	}

	$status = (int) wp_remote_retrieve_response_code( $response );
	if ( 204 === $status ) {
		return array();
	}

	$raw     = wp_remote_retrieve_body( $response );
	$decoded = json_decode( $raw, true );
	if ( $status < 200 || $status >= 300 ) {
		$code = 'connector_request_failed';
		if ( is_array( $decoded ) && ! empty( $decoded['code'] ) ) {
			$code = sanitize_key( $decoded['code'] );
		} elseif ( is_array( $decoded ) && ! empty( $decoded['error'] ) ) {
			$code = sanitize_key( $decoded['error'] );
		}

		$message = '';
		foreach ( array( 'detail', 'title', 'error' ) as $message_key ) {
			if ( is_array( $decoded ) && ! empty( $decoded[ $message_key ] ) ) {
				$message = sanitize_text_field( $decoded[ $message_key ] );
				break;
			}
		}
		if ( '' === $message ) {
			$message = sprintf(
				'AdsAgentServer rejected the connector request (HTTP %d).',
				$status
			);
		}
		$error_data = array( 'status' => $status );
		if ( is_array( $decoded ) && isset( $decoded['connector'] ) ) {
			$error_data['connector'] = brandagent_connectors_project_connector_for_ui( $decoded['connector'] );
		}
		return new WP_Error( $code, $message, $error_data );
	}

	if ( ! is_array( $decoded ) ) {
		return new WP_Error(
			'invalid_connector_response',
			'AdsAgentServer returned an invalid connector response.',
			array( 'status' => 502 )
		);
	}

	return $decoded;
}
