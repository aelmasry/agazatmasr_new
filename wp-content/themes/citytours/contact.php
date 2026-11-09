<?php

/**
 * Template Name: Contact US Page
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

global $ct_options;

?>
<!--<div id="map_contact" class="contact_2"></div>-->
<!-- end map-->
<div id="map" class="map"></div>
<div class="container margin_60">
    <div class="row">
        <?php
        if (!empty($_SERVER['REQUEST_METHOD'] == 'POST')) {
            
            $check = true;
            $first_name = sanitize_text_field($_POST["first_name"]);
            $last_name = sanitize_text_field($_POST["last_name"]);
            $name = $first_name . ' ' . $last_name;
            $email   = sanitize_email($_POST["email"]);
            $postMessage = esc_textarea($_POST["message"]);
            $phone = sanitize_text_field($_POST["phone"]);

            if ($_POST['captcha'] != $_SESSION['captcha']['code']) {
                $check = false;
                echo '<div class="error_message"><p> كود التحقق غير صحيح </p></div>';
            }

            if (!strlen($_POST["phone"]) == 10 && !ctype_digit($_POST["phone"])) {
                $check = false;
                echo '<div class="error_message"><p>الرجاء التحقق من رقم الموبيـل    </p></div>';
            }

            $subject = "موقع اجازات مصر : اتصل بنا";
            
            if ($check === true) {

                $message = "<html>
                <head>
                    <title>".$subject."</title>
                </head>

                <body>
                    <table>
                        <tr>
                            <th> Name </th>
                            <td>".$name."</td>
                        </tr>
                        <tr>
                            <th>E-Mail</th>
                            <td>".$email."</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>".$phone."</td>
                        </tr>
                        <tr>
                            <td colspan='2'>".nl2br($postMessage)."</td>
                        </tr>
                        </table>
                </body>
            </html>
            ";
            
                // get the blog administrator's email address
                $to = get_option('admin_email');
                $headers = "From: $name <$email>" . "\r\n";
                $headers .= "Reply-To: " . strip_tags($email) . "\r\n";
                $headers .= "CC: susan@example.com\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                // If email has been process for sending, display a success message
                if (wp_mail($to, $subject, $message, $headers)) {
                    echo '<div class="alert-success">';
                    echo '<p>شكرًا على اتصالك بنا ، وتوقع الرد قريبًا.</p>';
                    echo '</div>';
                } else {
                    echo '<div class="error_message"><p> حدث خطأ غير متوقع </p></div>';
                }
            }
        }
        ?>
        <div class="col-md-8 col-sm-8">
            <div class="form_title">
                <h3><strong><i class="icon-pencil"></i></strong>تعبئة النموذج</h3>
                <p>
                    نحن سعداء للتواصل معنا
                </p>
            </div>
            <div class="step">
                <form method="post" action="<?php echo esc_url(add_query_arg(array(), get_permalink())); ?>" id="contactform">
                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <label>الاسم الاول</label>
                                <input type="text" class="form-control" id="name_contact" name="first_name" placeholder="أدخل الاسم" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <label>الاسم الاخير</label>
                                <input type="text" class="form-control" id="lastname_contact" name="last_name" required placeholder="أدخل الاسم الاخير">
                            </div>
                        </div>
                    </div>
                    <!-- End row -->
                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <label>البريد الإلكتروني</label>
                                <input type="email" id="email" name="email" class="form-control" required placeholder="أدخل البريد الإلكتروني">
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <label>رقم الهاتف</label>
                                <input type="tel" id="phone" name="phone" class="form-control text-right" required placeholder="أدخل رقم الهاتف">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>الرسالة</label>
                                <textarea rows="5" id="message" name="message" class="form-control" required placeholder="أدخل الرسالة" style="height:200px;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php
                                    @include('simple-php-captcha.php');
                                    $_SESSION['captcha'] = simple_php_captcha();
                                ?>
                                 <img src="<?php echo esc_url(home_url($_SESSION['captcha']['image_src'])); ?>" alt="CAPTCHA code">
                                <input type="text" name="captcha" id="captcha" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="submit" value="إرسال" class="btn_1" id="submit-contact">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- End col-md-8 -->

        <div class="col-md-4 col-sm-4">
            <div class="box_style_1">
                <span class="tape"></span>
                <h4>العنوان <span><i class="icon-pin pull-right"></i></span></h4>
                <p>
                    أبراج المروة 2 برج رقم 1 شارع أحمد تيسير , الدور الثالث, مصر الجديدة,- القاهرة -جمهورية مصر العربية
                </p>
                <hr>
                <h4>للمزيد من المعلومات والمساعده <span><i class="icon-help pull-right"></i></span></h4>
                <p>
                    نتشرف بالاتصال بنا و مساعدتك
                </p>
                <ul id="contact-info">
                    <li>241 890 71 / 241 844 45</li>
                    <li><a href="mailto:info@agazatmasr.com" target="_top">info@agazatmasr.com</a>
                    </li>
                </ul>
            </div>
            <div class="box_style_4">
                <i class="icon_set_1_icon-57"></i>
                <h4>تحتاج <span>مساعدة ؟</span></h4>
                <a href="tel:01225993443" class="phone">01225993443</a>
                <small>الاحد الى الخميس: 8.00am - 6.00pm</small>
            </div>
        </div>
        <!-- End col-md-4 -->
    </div>
    <!-- End row -->
</div>
<!-- End container -->
<?php ct_google_maps_script(); ?>

<script type="text/javascript">
    $ = jQuery.noConflict();
    var lang = '<?php echo get_locale() ?>';
    lang = lang.replace('_', '-');

    $(document).ready(function(e) {

        
        var validation_rules = {
            first_name: {
                required: true
            },
            last_name: {
                required: true
            },
            email: {
                required: true,
                email: true
            },
            phone: {
                required: true,
                number: true
            },
            // captcha: {
            //     required: true
            // },
            message: {
                required: true,
                minlength: 50
            },
        };

        var validation_messages = {
            first_name: 'هذا الحقل إلزامي',
            last_name: 'هذا الحقل إلزامي',
            email: {
                required: 'هذا الحقل إلزامي',
                email: "رجاء إدخال عنوان بريد إلكتروني صحيح"
            },
            phone: {
                required: 'هذا الحقل إلزامي',
                number: "رجاء إدخال عدد بطريقة صحيحة"
            },
            captcha: {
                required: "هذا الحقل إلزامي"
            },
            message: {
                required: "هذا الحقل إلزامي",
                minlength: "الحد الأدنى لعدد الحروف هو  50"
            },
        };

        //validation form
        $('#contactform').validate({
            rules: validation_rules,
            messages: validation_messages,
            submitHandler: function(form) {
                $('#overlay').fadeIn();

                $(form).submit();
            }
        });

        var markersData = {
            'Center': [{
                name: 'اجـازات مصـر',
                type: 'Contact',
                location_latitude: 30.0783811,
                location_longitude: 31.3292984,
                map_image_url: '<?php echo CT_IMAGE_URL ?>/favicon.png',
                name_point: 'اجـازات مصـر',
                description_point: 'أبراج المروة 2 برج رقم 1 شارع أحمد تيسير , الدور الثالث , مصر الجديدة , القاهرة , جمهورية مصر العربية    \n\
              مواعيد العمل من الساعة 09.00 صباحا  حتى 5.00 مساءا   \n\
  تليفون أرضى :24189071 /  24184445  موبايل :-  01225993443',
                url_point: 'https://www.agazatmasr.com/'
            }, ]
        };

        var lati = 30.0783254;
        var long = 31.3292984;
        // var _center = [48.865633, 2.321236];
        // 30.0783811,31.32831,15.51z
        var _center = [lati, long];
        function drawContactMap() {
            if (typeof renderMap !== 'function' || !(window.google && google.maps)) {
                return;
            }
            renderMap(_center, markersData, 14, google.maps.MapTypeId.ROADMAP, false);
        }
        drawContactMap();
        window.addEventListener('load', drawContactMap);
    })
</script>
<?php
get_footer();
