<?php
/* Template Name: Flight Page */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

global $ct_options;

while (have_posts()) : the_post();

    $post_id = get_the_ID();

    $header_img_scr = ct_get_header_image_src($post_id);
    if (!empty($header_img_scr)) {
        $sidebar_position = ct_get_sidebar_position($post_id);
        $content_class = 'post-content';
        if ('no' != $sidebar_position)
            $content_class .= ' col-md-9';
        $header_content = ct_get_header_content($post_id);
        $header_img_height = ct_get_header_image_height($post_id);
?>
        <section class="parallax-window" data-parallax="scroll" data-image-src="<?php echo esc_url($header_img_scr) ?>" data-natural-width="1400" data-natural-height="<?php echo $header_img_height; ?>">
            <div class="parallax-content-1">
                <div>
                    <h1 class="page-title"><?php echo $header_content; ?></h1>
                </div>
            </div>
        </section><!-- End section -->

        <div id="position">

        <?php } else { ?>
            <div id="position" class="blank-parallax">
        <?php
    }

endwhile;
        ?>

        <div class="container"><?php ct_breadcrumbs(); ?></div>
            </div><!-- End Position -->

            <div class="container-fluid margin_60">
                <div class="row">
                    <div class="col-lg-9 col-md-9">
                        <?php
                        if (!empty($_REQUEST['adults'])) {
                            //                    https://www.epower.amadeus.com/touricotours/#AdtCount=1&CabinClass=&Culture=en-US&DepartureDate=15%2F12%2F2018&From=CAI&Method=Search&Page=Result&ReturnDate=&To=AMM&FlightType=OneWay
                            //                    $url = "https://www.epower.amadeus.com/touricotours/?AdtCount=" . $_REQUEST['adults'] . "&CabinClass=" . $_REQUEST['CabinClass'] . "&Culture=ar-SA&DepartureDate=" . date("d/m/Y", strtotime($_REQUEST['DepartureDate']))."&From=" . $_REQUEST['From'] . "&Method=Search&Page=Result&ReturnDate=" . date("d/m/Y", strtotime($_REQUEST['ReturnDate'])) . "&To=" . $_REQUEST['To']."&FlightType=".$_REQUEST['flightType'];
                            $url = "https://www.epower.amadeus.com/touricotours/?AdtCount=" . $_REQUEST['adults'] . "&CabinClass=" . $_REQUEST['CabinClass'] . "&Culture=ar-SA&DepartureDate=" . date("d/m/Y", strtotime($_REQUEST['DepartureDate'])) . "&From=" . $_REQUEST['from_code'] . "&Method=Search&Page=Result&ReturnDate=" . date("d/m/Y", strtotime($_REQUEST['ReturnDate'])) . "&To=" . $_REQUEST['to_code'] . "&FlightType=" . $_REQUEST['flightType'];
                        } else {
                            $url = "https://www.epower.amadeus.com/touricotours/?Culture=ar-SA";
                        }
                        ?>
                        <iframe style="margin: 0 auto; display: block; width: 100%; max-width: 960px; height: 818px;" src="<?php echo $url ?>">
                        </iframe>
                    </div>

                    <aside class="col-lg-3 col-md-3" <?php if ($is_fixed_sidebar) echo 'id="sidebar"'; ?>>
                        <?php // if (is_active_sidebar('ct-custom-sidebar-flight')) :
                        ?>
                        <?php // dynamic_sidebar('flight');
                        ?>
                        <?php // endif;
                        ?>
                        <?php ct_get_template('widget.php', '/templates/tour/'); ?>
                        <?php include 'contact_info.php'; ?>
                    </aside>

                </div><!-- End row -->
            </div><!-- End container -->

            <?php
            get_footer();
