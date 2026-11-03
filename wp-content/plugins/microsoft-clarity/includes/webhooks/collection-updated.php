<?php
/**
 * Product Category Updated Webhook Configuration
 *
 * @package MicrosoftClarity
 * @since 0.10.28
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

return array(
	'name'     => 'BrandAgent Collection Updated',
	'topic'    => 'product_cat.updated',
	'endpoint' => BRANDAGENT_WEBHOOK_BASE_URL . 'collections/updated',
);
