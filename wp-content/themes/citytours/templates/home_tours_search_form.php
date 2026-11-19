<div class="tab-pane <?php echo esc_attr($active_class) ?>" id="tours">
    <h3>
        <?php esc_html_e('Search Tours', 'citytours') ?>
    </h3>

    <form role="search" method="get" id="search-tour-form" action="<?php echo esc_url(home_url('/')); ?>">
        <input type="hidden" name="post_type" value="tour">
        <!-- End row -->
        <div class="row bottom-part">
            <div class="form-group">
                <label>
                    <?php esc_html_e('tour search terms', 'citytours') ?>
                </label>
                <?php
                $all_tour_types = ct_unique_tour_types_by_name(get_terms('tour_type', array('hide_empty' => 0, 'parent' => 0, 'orderby' => 'term_order', 'order' => 'ASC')));
                if (!empty($all_tour_types)) :
                ?>
                    <select class="form-control" name="type" id="tour_types">
                        <option value="" selected>
                            <?php esc_html_e('Select a country', 'citytours') ?>
                        </option>
                        <?php
                        foreach ($all_tour_types as $each_tour_type) {
                            $term_id = $each_tour_type->term_id;
                            $icon_class = get_tax_meta($term_id, 'ct_tax_icon_class');
                        ?>
                            <option value="<?php echo esc_attr($term_id) ?>">
                                <?php echo esc_html($each_tour_type->name) ?>
                            </option>
                        <?php } ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="form-group">
            <label>
                    <?php esc_html_e('Things to do', 'citytours') ?>
                </label>
                
                <select class="form-control" name="city" id="tour_city" disabled="disabled">
                    <option value="" selected>
                        <?php esc_html_e('All tours', 'citytours') ?>
                    </option>
                </select>
            </div>


        </div>
        <div class="row top-part">

            <div class="form-group">
                <label>
                    <?php esc_html_e('tour search terms', 'citytours') ?>
                </label>
                <input type="text" class="form-control" name="s" placeholder="<?php esc_html_e('Type your search terms', 'citytours') ?>">
            </div>

            <div class="form-group">
                <label>
                    <i class="icon-calendar-7"></i>
                    <?php esc_html_e('Date', 'citytours') ?>
                </label>
                <input class="date-pick form-control" data-date-format="<?php echo ct_get_date_format('html'); ?>" type="date" name="date" value="" autocomplete="off">
            </div>

        </div>
        <!-- End row -->
        <button class="btn_1 green">
            <!-- <i class="icon-search"></i> -->
            <?php esc_html_e('Search now', 'citytours') ?>
        </button>
    </form>
</div>
