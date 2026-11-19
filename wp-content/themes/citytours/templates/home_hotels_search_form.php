<div class="tab-pane <?php echo esc_attr($active_class) ?>" id="hotels">
    <h3>
        <?php esc_html_e('Search Hotels', 'citytours') ?>
    </h3>

    <form role="search" method="get" id="search-hotel-form" action="<?php echo esc_url(home_url('/')); ?>">
        <input type="hidden" name="post_type" value="hotel">
        <div class="row top-part">
            <?php
            $all_districts = get_terms('district', array('hide_empty' => false, 'parent' => 0));
            if (!empty($all_districts)) :
            ?>
                <div class="form-group">
                    <label>
                        <?php esc_html_e('Country', 'citytours') ?> *
                    </label>
                    <select class="form-control" name="country" id="countries">
                        <option value="" selected>
                            <?php esc_html_e('Select a country', 'citytours') ?>
                        </option>
                        <?php
                        foreach ($all_districts as $district) {
                            $term_id = $district->term_id;
                        ?>
                            <option value="<?php echo esc_attr($term_id) ?>">
                                <?php echo esc_html($district->name) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>
                    <?php esc_html_e('Preferred city area', 'citytours') ?>
                </label>
                <select class="form-control" name="districts" id="cities" disabled="disabled">
                    <option value="" selected>
                        <?php esc_html_e('Select city', 'citytours') ?>
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label>
                    <?php esc_html_e('Hotel name', 'citytours') ?>
                </label>
                <input type="text" class="form-control" id="hotel_name" name="s" placeholder="<?php esc_attr_e('Optionally type hotel name', 'citytours') ?>" autocomplete="on">
            </div>
        </div>
        <!-- End row -->

        <div class="row bottom-part">
            <div class="form-group">
                <label>
                    <i class="icon-calendar-7"></i>
                    <?php esc_html_e('Check in', 'citytours') ?>
                </label>
                <input class="date-pick form-control" data-date-format="<?php echo ct_get_date_format('html'); ?>" type="date" name="date_from" value="" autocomplete="off">
            </div>

            <div class="form-group">
                <label>
                    <i class="icon-calendar-7"></i>
                    <?php esc_html_e('Check out', 'citytours') ?>
                </label>
                <input class="date-pick form-control" data-date-format="<?php echo ct_get_date_format('html'); ?>" type="date" name="date_to" value="" autocomplete="off">
            </div>
        </div>
        <!-- End row -->

        <button class="btn_1 green">
            <!-- <i class="icon-search"></i> -->
            <?php
            esc_html_e('Search now', 'citytours');
            ?>
        </button>
    </form>
</div>
