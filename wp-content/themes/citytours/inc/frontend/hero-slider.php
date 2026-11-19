<?php
/**
 * Homepage hero (Smart Slider 3, slider #2) LCP optimisation.
 *
 * The slides still reference ~1 MB PNGs. Smart Slider stores those paths in
 * the database, so instead of editing slide records this swaps the rendered
 * shortcode for pre-optimised WebP/AVIF files that ship with the theme.
 *
 * Scope: slider #2 only.
 * - Slide 1: eager + fetchpriority=high + intrinsic size (it is the LCP).
 * - Slides 2-4: native lazy loading, low priority.
 * - Mobile art direction via <picture media> (the slider is 50vh below 992px).
 * - No rel=preload, so the img URL is requested exactly once.
 *
 * @package citytours
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Optimised hero files and per-slide attributes.
 *
 * @return array<int, array<string, mixed>>
 */
function ct_hero_slider_images()
{
    return array(
        1 => array(
            'desktop_webp' => 'hero-1-edit-desktop-1136.webp',
            'desktop_avif' => 'hero-1-edit-desktop-1136.avif',
            'mobile_webp'  => 'hero-1-edit-mobile-860.webp',
            'mobile_avif'  => 'hero-1-edit-mobile-860.avif',
            'width'        => 1136,
            'height'       => 568,
            'loading'      => 'eager',
            'priority'     => 'high',
            // AVIF decodes slower than WebP. For the LCP image the transfer
            // saving is not worth it, so slide 1 is served as WebP only.
            'avif'         => false,
        ),
        2 => array(
            'desktop_webp' => 'hero-2-desktop-1136.webp',
            'desktop_avif' => 'hero-2-desktop-1136.avif',
            'mobile_webp'  => 'hero-2-mobile-860.webp',
            'mobile_avif'  => 'hero-2-mobile-860.avif',
            'width'        => 1136,
            'height'       => 568,
            'loading'      => 'lazy',
            'priority'     => 'low',
            'avif'         => true,
        ),
        3 => array(
            'desktop_webp' => 'hero-3-desktop-1136.webp',
            'desktop_avif' => 'hero-3-desktop-1136.avif',
            'mobile_webp'  => 'hero-3-mobile-860.webp',
            'mobile_avif'  => 'hero-3-mobile-860.avif',
            'width'        => 1136,
            'height'       => 568,
            'loading'      => 'lazy',
            'priority'     => 'low',
            'avif'         => true,
        ),
        4 => array(
            'desktop_webp' => 'hero-4-desktop-1136.webp',
            'desktop_avif' => 'hero-4-desktop-1136.avif',
            'mobile_webp'  => 'hero-4-mobile-860.webp',
            'mobile_avif'  => 'hero-4-mobile-860.avif',
            'width'        => 1136,
            'height'       => 568,
            'loading'      => 'lazy',
            'priority'     => 'low',
            'avif'         => true,
        ),
    );
}

/**
 * Base URL of the shipped hero images.
 *
 * @return string
 */
function ct_hero_slider_base_url()
{
    return get_template_directory_uri() . '/assets/img/hero/';
}

/**
 * Build the <picture> markup for one slide.
 *
 * @param array<string, mixed> $image Image definition.
 * @param string               $alt   Original alt attribute.
 * @param string               $title Original title attribute.
 *
 * @return string
 */
function ct_hero_slider_picture($image, $alt = '', $title = '')
{
    $base = ct_hero_slider_base_url();

    $alt   = esc_attr(html_entity_decode((string) $alt, ENT_QUOTES, 'UTF-8'));
    $title = esc_attr(html_entity_decode((string) $title, ENT_QUOTES, 'UTF-8'));

    $desktop_webp = esc_url($base . $image['desktop_webp']);
    $mobile_webp  = esc_url($base . $image['mobile_webp']);
    $desktop_sizes = '(max-width: 991px) 100vw, 1135px';

    $sources = '';

    // Mobile art direction: same breakpoint as the 50vh slider height rule.
    if (!empty($image['avif'])) {
        $sources .= sprintf(
            '<source media="(max-width: 991px)" type="image/avif" srcset="%s">',
            esc_url($base . $image['mobile_avif'])
        );
    }
    $sources .= sprintf(
        '<source media="(max-width: 991px)" type="image/webp" srcset="%s 860w" sizes="100vw">',
        $mobile_webp
    );

    if (!empty($image['avif'])) {
        $sources .= sprintf(
            '<source type="image/avif" srcset="%s">',
            esc_url($base . $image['desktop_avif'])
        );
    }
    $sources .= sprintf(
        '<source type="image/webp" srcset="%s 1136w" sizes="%s">',
        $desktop_webp,
        $desktop_sizes
    );

    /*
     * skip-lazy keeps Jetpack's lazy loader away from the LCP image. The
     * remaining slides intentionally do not carry it so they stay deferred.
     */
    $class = 'eager' === $image['loading'] ? ' class="skip-lazy" data-skip-lazy="1"' : '';

    $attrs = sprintf(
        'src="%s" srcset="%s 1136w" sizes="%s" width="%d" height="%d" alt="%s" loading="%s" decoding="async" fetchpriority="%s"%s',
        $desktop_webp,
        $desktop_webp,
        $desktop_sizes,
        $image['width'],
        $image['height'],
        $alt,
        esc_attr($image['loading']),
        esc_attr($image['priority']),
        $class
    );

    if ('' !== $title) {
        $attrs .= sprintf(' title="%s"', $title);
    }

    return '<picture class="ct-hero-picture">' . $sources . '<img ' . $attrs . '></picture>';
}

/**
 * Swap the slide background images inside slider #2's shortcode output.
 *
 * @param string $output Shortcode output.
 * @param string $tag    Shortcode tag.
 * @param array  $attr   Shortcode attributes.
 *
 * @return string
 */
function ct_hero_optimise_smart_slider($output, $tag, $attr)
{
    if ('smartslider3' !== $tag) {
        return $output;
    }

    if (empty($attr['slider']) || 2 !== (int) $attr['slider']) {
        return $output;
    }

    if (!is_string($output) || false === strpos($output, 'n2-ss-slide-backgrounds')) {
        return $output;
    }

    $images = ct_hero_slider_images();

    // Never risk a broken hero if the assets were not deployed.
    if (!file_exists(get_template_directory() . '/assets/img/hero/' . $images[1]['desktop_webp'])) {
        return $output;
    }

    $start = strpos($output, 'n2-ss-slide-backgrounds');
    $end   = strpos($output, 'n2-ss-slider-4', $start);
    if (false === $end) {
        $end = strlen($output);
    }

    $region = substr($output, $start, $end - $start);

    // Smart Slider wraps background images in <picture>; fall back to <img>.
    $pattern = '~<picture\b[^>]*>\s*<img\b[^>]*>\s*</picture>|<img\b[^>]*>~i';

    if (!preg_match_all($pattern, $region, $matches, PREG_OFFSET_CAPTURE)) {
        return $output;
    }

    $result = '';
    $cursor = 0;
    $found  = 0;

    foreach ($matches[0] as $match) {
        $html   = $match[0];
        $offset = $match[1];

        // The closest preceding data-public-id owns this background image.
        $before = substr($region, 0, $offset);
        if (!preg_match_all('~data-public-id="(\d+)"~', $before, $ids)) {
            continue;
        }
        $slide = (int) end($ids[1]);

        if (!isset($images[$slide])) {
            continue;
        }

        if (!preg_match('~<img\b[^>]*>~i', $html, $img)) {
            continue;
        }

        $alt = preg_match('~\balt="([^"]*)"~i', $img[0], $a) ? $a[1] : '';
        $title = preg_match('~\btitle="([^"]*)"~i', $img[0], $t) ? $t[1] : '';

        $result .= substr($region, $cursor, $offset - $cursor);
        $result .= ct_hero_slider_picture($images[$slide], $alt, $title);
        $cursor  = $offset + strlen($html);
        $found++;
    }

    if (!$found) {
        return $output;
    }

    $result .= substr($region, $cursor);

    return substr($output, 0, $start) . $result . substr($output, $end);
}
add_filter('do_shortcode_tag', 'ct_hero_optimise_smart_slider', 10, 3);
