<div class="tab-pane <?php echo esc_attr($active_class) ?>" id="flights">
    <h3>
        <?php esc_html_e('Search flights', 'citytours') ?>
    </h3>
    <form role="search" method="get" id="search-flights-form" action="<?php echo esc_url(home_url('/flight')); ?>">
        <div class="row choise-btns">
            <!-- start of check -->

            <div class="form-group">
                <input name="flightType" checked class="form-check-input" type="radio" id="way-2" value="OneWay">
                <label for="way-2" class="way one-way">ذهاب فقط </label>
            </div>

            <!-- start of check -->

            <div class="form-group">
                <input name="flightType" class="form-check-input" type="radio" id="way-1" value="RoundTrip">
                <label for="way-1" class="way way-back"> ذهاب و عودة</label>
            </div>

        </div>

        <div class="row flight-con">

            <div class="form-group">
                <label> من </label>
                <input type="hidden" name="from_code" id="fromCode">
                <input name="From" id="amadeus_air_dep_from" type="text" class="form-control is-valid" placeholder="مطار أو مدينة" value="" required>
            </div>

            <div class="form-group">
                <label>الى</label>
                <input type="hidden" name="to_code" id="toCode">
                <input name="To" id="amadeus_air_dep_to" type="text" class="form-control is-valid amadeus_air_dep" placeholder="مطار أو مدينة" value="" required>
            </div>

            <div class="form-group ">
                <label>تاريخ المغادرة</label>
                <input name="DepartureDate" type="date" data-date-format="<?php echo ct_get_date_format('html'); ?>" class="date-pick form-control is-valid" placeholder="" autocomplete="off" required>
            </div>

            <div class="form-group return-date disable">
                <label>تاريخ العودة</label>
                <input name="ReturnDate" type="date" data-date-format="<?php echo ct_get_date_format('html'); ?>" class="date-pick form-control is-valid" placeholder="" autocomplete="off">
            </div>

        </div>

        <div class="row people">

            <div class="right-side">
                <div class="form-group">
                    <label>البالغين</label>

                    <div class="numbers-row">
                        <input type="text" value="1" class="qty2 form-control" name="adults">

                        <div class="inc button_inc">+</div>
                        <div class="dec button_inc">-</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>طفل (2-12)</label>

                    <div class="numbers-row">
                        <input type="text" value="0" class="qty2 form-control" name="children">

                        <div class="inc button_inc">+</div>
                        <div class="dec button_inc">-</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>رضيع (0-2) </label>

                    <div class="numbers-row">
                        <input type="text" value="0" class="qty2 form-control" name="INF">

                        <div class="inc button_inc">+</div>
                        <div class="dec button_inc">-</div>
                    </div>
                </div>
            </div>


            <div class="left-side">
                <div class="form-group">
                    <label>درجة المقصوره</label>
                    <select name="CabinClasses" class="form-control">
                        <option value="">لا يوجد أفضلية</option>
                        <option value="Y">الدرجة السياحية</option>
                        <option value="W">متميزة</option>
                        <option value="C">درجة رجال الاعمال</option>
                        <option value="F">الدرجة الاولى</option>
                    </select>
                </div>
            </div>

        </div>

        <!-- end of form-row -->

        <button class="btn_1 green">
            <!-- <i class="icon-search"></i> -->
            <?php esc_html_e('Search now', 'citytours'); ?>
        </button>

    </form>
    <!-- end of form -->
</div>
