<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Currency&Language swtich widget
 */
if ( ! class_exists( 'CTSettingsWidget') ) :
    class CTSettingsWidget extends WP_Widget {

        public function __construct() {
            $widget_ops = array( 
                'classname' => 'currency_switcher',
                'description' => __( 'Display Languages & Currencies.', 'citytours' ),
            );
            parent::__construct( 'ct_currency_lang_switcher', __( 'CityTours: Currency&Language Switcher', 'citytours' ), $widget_ops );
        }

        public function widget( $args, $instance ) {
            // add custom class contact box
            global $ct_options;

            extract( $args );

            if ( strpos( $before_widget, 'class' ) === false ) {
                $before_widget = str_replace( '>', 'class="'. 'contact-box' . '"', $before_widget );
            }
            else {
                $before_widget = str_replace( 'class="', 'class="'. 'contact-box' . ' ', $before_widget );
            }

            echo wp_kses_post( $before_widget );

            if ( ! empty( $instance['title'] ) ) {
                echo wp_kses_post( $before_title . apply_filters( 'widget_title', $instance['title'] ) . $after_title );
            }

            if ( ct_get_lang_count() > 1 ) { 
                ?>

                <div class="styled-select">
                    <select class="form-control cl-switcher" name="lang" id="lang">
                        <?php
                        $languages = icl_get_languages('skip_missing=1');
                        foreach ( $languages as $l ) {
                            $selected = ( $l['active'] ) ? 'selected' : '';
                            echo '<option ' . $selected . ' data-url="' . esc_url( $l['url'] ) . '">' . esc_html( $l['translated_name'] ) . '</option>';
                        } ?>
                    </select>
                </div>

                <?php 
            }

            if ( ct_is_woocommerce_integration_enabled() ) { 
                ?>

                <div class="styled-select">
                    <select class="form-control cl-switcher" name="currency" id="currency">
                        <?php 
                        $key = get_woocommerce_currency();
                        $selected = 'selected';
                        $params = $_GET;
                        $params['selected_currency'] = $key;
                        $paramString = http_build_query($params, '', '&amp;');
                        echo '<option ' . $selected . ' data-url="' . esc_url( strtok( $_SERVER['REQUEST_URI'], '?' ) . '?' . $paramString ) . '">' . esc_html( strtoupper( $key ) ) . '</option>';
                         ?>
                    </select>
                </div>

                <?php 
            } else if ( ! ct_is_woocommerce_integration_enabled() && ct_is_multi_currency() ) { 
                ?>

                <div class="styled-select">
                    <select class="form-control cl-switcher" name="currency" id="currency">
                        <?php
                            $all_currencies = ct_get_all_available_currencies();
                            foreach ( array_filter( $ct_options['site_currencies'] ) as $key => $content) {
                                $selected = ( ct_get_user_currency() == $key ) ? 'selected' : '';
                                $params = $_GET;
                                $params['selected_currency'] = $key;
                                $paramString = http_build_query($params, '', '&amp;');
                                echo '<option ' . $selected . ' data-url="' . esc_url( strtok( $_SERVER['REQUEST_URI'], '?' ) . '?' . $paramString ) . '">' . esc_html( strtoupper( $key ) ) . '</option>';
                            }
                        ?>
                    </select>
                </div>

                <?php 
            }

            echo wp_kses_post( $after_widget );
        }

        public function update( $new_instance, $old_instance ) {
            // Save widget options
            $instance = $old_instance;
            $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
            return $instance;
        }

        public function form( $instance ) {
            // Output admin widget options form
            $defaults = array( 'title' => __( 'Currency & Language Settings', 'citytours' ) );
            $instance = wp_parse_args( (array) $instance, $defaults ); ?>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php _e('Title', 'citytours') ?>:</label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php echo esc_attr( $instance['title'] ) ?>" />
            </p>

        <?php 
        }
    }
endif;

/*
 * Address widget
 */
if ( ! class_exists( 'CTContactInfo' ) ) :
    class CTContactInfo extends WP_Widget {

        public function __construct() {
            $widget_ops = array( 
                'classname' => 'contact_info',
                'description' => 'Display added Contact Info in stylised format.',
            );
            parent::__construct( 'ct_contact_info_widget', 'Citytours: Contact Info', $widget_ops );
        }

        /**
         * Outputs the content of the widget
         */
        public function widget( $args, $instance ) {
            extract( $args );

            if ( strpos( $before_widget, 'class' ) === false ) {
                $before_widget = str_replace( '>', 'class="'. 'contact-box' . '"', $before_widget );
            }
            else {
                $before_widget = str_replace( 'class="', 'class="'. 'contact-box' . ' ', $before_widget );
            }

            echo wp_kses_post( $before_widget );

            if ( ! empty( $instance['title'] ) ) {
                echo wp_kses_post( $before_title . apply_filters( 'widget_title', $instance['title'] ) . $after_title );
            }

            $phone = str_replace( ' ', '', $instance['phone'] );
            $phone = str_replace( '-', '', $phone );
            $phone = str_replace( '(', '', $phone );
            $phone = str_replace( ')', '', $phone );
            $phone = str_replace( '+', '', $phone );

            $icon_class = '';
            if ( $instance['show_icons'] ) { 
                $icon_class = ' show_icons';
            }

            ?>

            <div class="contact-info">
                <div class="box_style_2">
                <i class="icon_set_1_icon-57"></i>
                <h4>تحتاج <span>مساعدة ؟</span></h4>
                <?php if ( ! empty( $phone ) ) : ?>
                    <a href="tel:<?php echo $phone ?>" class="phone <?php echo $icon_class ?>"><?php echo $instance['phone'] ?></a>
                <?php endif; ?>

                <?php if ( ! empty( $instance['email'] ) ) : ?>
                    <a href="mailto:<?php echo $instance['email'] ?>" class="email <?php echo $icon_class ?>"><?php echo $instance['email'] ?></a>
                <?php endif; ?>

                <?php if ( ! empty( $instance['note'] ) ) : ?>
                    <small><?php echo $instance['note'] ?></small>
                <?php endif; ?>
            </div>
            </div>

            <?php

            echo wp_kses_post( $after_widget );
        }

        /**
         * Outputs the options form on admin
         */
        public function form( $instance ) {
            $defaults = array( 
                'title'         => __( 'Contact Info', 'citytours' ),
                'phone'         => '',
                'email'         => '',
                'note'         => '',
                'show_icons'    => true,
            );
            $instance = wp_parse_args( (array) $instance, $defaults ); 
            ?>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php _e( 'Title', 'citytours' ) ?>:</label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php echo esc_attr( $instance['title'] ) ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('phone') ); ?>"><?php _e( 'Phone Number', 'citytours' ) ?>:</label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('phone') ); ?>" name="<?php echo esc_attr( $this->get_field_name('phone') ); ?>" value="<?php echo esc_attr( $instance['phone'] ) ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('email') ); ?>"><?php _e( 'Email', 'citytours' ) ?>:</label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('email') ); ?>" name="<?php echo esc_attr( $this->get_field_name('email') ); ?>" value="<?php echo esc_attr( $instance['email'] ) ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('note') ); ?>"><?php _e( 'Note', 'citytours' ) ?>:</label>
                <textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id('note') ); ?>" name="<?php echo esc_attr( $this->get_field_name('note') ); ?>"><?php echo esc_attr( $instance['note'] ) ?> </textarea>
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('show_icons') ); ?>"><?php _e( 'Show Icons', 'citytours' ) ?>:</label>
                <input type="checkbox" class="widefat" id="<?php echo esc_attr( $this->get_field_id('show_icons') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_icons') ); ?>" <?php if ( $instance['show_icons'] ) echo 'checked' ?> />
            </p>

            <?php
        }

        /**
         * Processing widget options on save
         */
        public function update( $new_instance, $old_instance ) {
            $instance = $old_instance;

            $instance['title']      = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
            $instance['phone']      = ( ! empty( $new_instance['phone'] ) ) ? strip_tags( $new_instance['phone'] ) : '';
            $instance['email']      = ( ! empty( $new_instance['email'] ) ) ? strip_tags( $new_instance['email'] ) : '';
            $instance['note']      = ( ! empty( $new_instance['note'] ) ) ? strip_tags( $new_instance['note'] ) : '';
            $instance['show_icons'] = ( ! empty( $new_instance['show_icons'] ) ) ? true : '';

            return $instance;
        }
    }
endif;


function ct_register_widgets() {
    register_widget( 'CTContactInfo' );
    register_widget( 'CTSettingsWidget' );
}

add_action( 'widgets_init', 'ct_register_widgets' );