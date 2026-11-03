<?php
/**
 * Product Category Created Webhook Configuration
 *
 * @package MicrosoftClarity
 * @since 0.10.28
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

return array(
	'name'     => 'BrandAgent Collection Created',
	'topic'    => 'product_cat.created',
	'endpoint' => BRANDAGENT_WEBHOOK_BASE_URL . 'collections/created',
);
