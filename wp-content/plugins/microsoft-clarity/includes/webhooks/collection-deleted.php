<?php
/**
 * Product Category Deleted Webhook Configuration
 *
 * @package MicrosoftClarity
 * @since 0.10.28
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

return array(
	'name'     => 'BrandAgent Collection Deleted',
	'topic'    => 'product_cat.deleted',
	'endpoint' => BRANDAGENT_WEBHOOK_BASE_URL . 'collections/deleted',
);
