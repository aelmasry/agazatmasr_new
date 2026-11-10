<div id="modify_search">
    
    <div class="collapse show" >
        <div class="modify_search_wp">
            <form role="search" method="get" id="search-blog-form" action="<?php echo esc_url(home_url('/blog')); ?>">

                <div class="form-group">
                    <label> الوجهات السياحية </label>
                    <?php
                    $states = get_terms('state', array('hide_empty' => false, 'parent' => 0));
                    ?>
                        <select class="form-control" name="country" id="blog_countries">
                            <option value="" selected><?php esc_html_e('Select a country', 'citytours') ?></option>
                            <?php
                            foreach ($states as $country) {
                                if (!empty($country->name)) :
                                    $term_id = $country->term_id;
                                    $icon_class = get_tax_meta($term_id, 'ct_tax_icon_class');
                            ?>
                                    <option value="<?php echo esc_attr($term_id) ?>" <?php echo (isset($_REQUEST['country']) && $_REQUEST['country'] == $term_id) ? "selected='selected'" : ""; ?>><?php echo esc_html($country->name) ?></option>
                            <?php endif;
                            } ?>
                        </select>
                </div>

                <div class="form-group">
                    <label> المدن السياحية </label>

                    <?php
                    if (isset($_REQUEST['country']) && !empty($_REQUEST['country'])) {
                        $cities = get_terms('state', array('hide_empty' => false, 'parent' => $_REQUEST['country']));
                    }
                    ?>
                    <select class="form-control" name="city" id="blog_cities">
                        <option value="" selected><?php esc_html_e('Select city', 'citytours') ?></option>
                        <?php
                        if (isset($_REQUEST['country']) && !empty($_REQUEST['country'])) {
                            foreach ($cities as $city) {
                                $term_id = $city->term_id;
                                $icon_class = get_tax_meta($term_id, 'ct_tax_icon_class');
                        ?>
                                <option value="<?php echo esc_attr($term_id) ?>" <?php echo (isset($_REQUEST['city']) && $_REQUEST['city'] == $term_id) ? "selected='selected'" : ""; ?>><?php echo esc_html($city->name) ?></option>
                        <?php }
                        } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label> معلومات تهمك وانت مسافر </label>
                    <?php
                    if (isset($_REQUEST['city']) && !empty($_REQUEST['city'])) {
                        $citiesInfos = get_terms('state', array('hide_empty' => false, 'parent' => $_REQUEST['city']));
                    }
                    ?>
                    <select class="form-control" name="info" id="infos">
                        <option value="" selected>عاوز تعرف ايه</option>
                        <?php
                        if (isset($_REQUEST['city']) && !empty($_REQUEST['city'])) {
                            foreach ($citiesInfos as $info) {
                                $term_id = $info->term_id;
                                $icon_class = get_tax_meta($term_id, 'ct_tax_icon_class');
                        ?>
                                <option value="<?php echo esc_attr($term_id) ?>" <?php echo (isset($_REQUEST['info']) && $_REQUEST['info'] == $term_id) ? "selected='selected'" : ""; ?>><?php echo esc_html($info->name) ?></option>
                        <?php }
                        } ?>
                    </select>
                </div>

                <button class="btn_1 green"> <i class="icon-search"></i> <?php esc_html_e('Search...', 'citytours') ?></button>
            </form>
        </div>
    </div>
    <!--End collapse -->
</div>

<script type="text/javascript">
    jQuery.noConflict();
    jQuery(document).ready(function($) {

        // load continents
        $('#blog_countries').change(function() {
            var city = $(this).val();
            get_cities(city);
        })

        // load continents
        $('#blog_cities').change(function() {
            var city = $(this).val();
            get_cities_info(city);
        })


        function get_cities(countryId) {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: "filterByCountryId",
                    'id': countryId,
                },
                success: function(response) {
                    var options = '<option value="" selected><?php esc_html_e('Select city', 'citytours') ?></option>';
                    $.each(response.cities, function(index, item) {
                        if (countryId == item['term_id']) {
                            options += '<option value="' + item['term_id'] + '" selected>' + item['name'] + '</option>';
                        } else {
                            options += '<option value="' + item['term_id'] + '">' + item['name'] + '</option>';
                        }
                    });

                    console.log("options", options);
                    $('#blog_cities').html(options);
                    $('#blog_cities').prop("disabled", false);

                }
            });
        }

        function get_cities_info(city) {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: "filterByCountryId",
                    'id': city,
                },
                success: function(response) {
                    var options = '<option value="" selected> جميع المعلومات </option>';
                    $.each(response.cities, function(index, item) {
                        if (city == item['term_id']) {
                            options += '<option value="' + item['term_id'] + '" selected>' + item['name'] + '</option>';
                        } else {
                            options += '<option value="' + item['term_id'] + '">' + item['name'] + '</option>';
                        }
                    });

                    $('#infos').html(options);
                    $('#infos').prop("disabled", false);

                }
            });
        }

    });
</script>
<style type="text/css">
    .modify_search_wp {
        border-top:none !important;
        padding: 0 !important;
    }
    .blog_search h4 {
        border: none;
        font-family: inherit;
        font-size: inherit;
        color: #fff;
        background: #545353;
        cursor: pointer;
        padding: 12px 20px;
        display: inline-block;
        outline: 0;
        font-size: 12px;
        -webkit-transition: all .3s;
        -moz-transition: all .3s;
        transition: all .3s;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        text-transform: uppercase;
        font-weight: 700;
        display: block;
        text-align: center;
    }
</style>