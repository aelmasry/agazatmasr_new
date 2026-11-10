<?php
if (!function_exists('ct_load_custom_styles')) 
{

    function ct_load_custom_styles()
    {
        global $post, $ct_options;

        if (empty($ct_options['enable-custom-styles'])) {
            return;
        }

        ob_start();
        ?>
        
        <style type="text/css">


            /* ===========================================================
            =========================    Body    =========================
            =========================================================== */
/*            body{ 
                color:<?php echo $ct_options['body-font']['color'] ?>;
                font-size:<?php echo $ct_options['body-font']['font-size'] ?>;
                line-height:<?php echo $ct_options['body-font']['line-height'] ?>;
                font-family:<?php echo $ct_options['body-font']['font-family'] ?>;
                font-weight:<?php echo $ct_options['body-font']['font-weight'] ?>;

                <?php if ( isset($ct_options['body-background']['background-color']) && !empty($ct_options['body-background']['background-color']) ) : ?>
                background-color:<?php echo $ct_options['body-background']['background-color'] ?>;
                <?php endif; ?>
                
                <?php if ( (isset($ct_options['body-background']['background-image'])) && ($ct_options['body-background']['background-image'] != "") ) : ?>
                background-image:url(<?php echo esc_url($ct_options['body-background']['background-image']); ?>);
                <?php endif; ?>
                
                <?php if ( isset($ct_options['body-background']['background-repeat']) && !empty($ct_options['body-background']['background-repeat']) ) : ?>
                background-repeat:<?php echo $ct_options['body-background']['background-repeat'] ?>;
                <?php endif; ?>
                
                <?php if ( isset($ct_options['body-background']['background-position']) && !empty($ct_options['body-background']['background-position']) ) : ?>
                background-position:<?php echo $ct_options['body-background']['background-position'] ?>;
                <?php endif; ?>
                
                <?php if ( isset($ct_options['body-background']['background-size']) && !empty($ct_options['body-background']['background-size']) ) : ?>
                background-size:<?php echo $ct_options['body-background']['background-size'] ?>;
                <?php endif; ?>
                
                <?php if ( isset($ct_options['body-background']['background-attachment']) && !empty($ct_options['body-background']['background-attachment']) ) : ?>
                background-attachment:<?php echo $ct_options['body-background']['background-attachment'] ?>;
                <?php endif; ?>
            }*/

            /* ===========================================================
            ======================== Breadcrumb ==========================
            =========================================================== */
            <?php if ( isset($ct_options['header_breadcrumb']) && empty($ct_options['header_breadcrumb']) ) : ?>
            #position {
                display: none;
            }
            <?php endif; ?>


            /* ===========================================================
            ========    Plain Header Background Color    =================
            =========================================================== */
            <?php if ( ! empty( $ct_options['header-bg'] ) ): ?>
            header.plain, .no-header-image header{
                background-color:<?php echo $ct_options['header-bg'] ?>;
            }
            <?php endif; ?>


            /* ===========================================================
            =========    Sticky Header Background Color    ===============
            =========================================================== */
            <?php if ( ! empty( $ct_options['header-sticky-bg'] ) ): ?>
            header.sticky, header.plain.sticky{
                background-color:<?php echo $ct_options['header-sticky-bg'] ?>;
            }
            <?php endif; ?>


            /* ===========================================================
            ===================    Menu Font Style    ====================
            =========================================================== */
            .main-menu > div > ul > li > a, header.plain .main-menu > div > ul > li > a{
                font-size:16px;
                line-height:<?php echo $ct_options['menu-font']['line-height'] ?>;
                /*font-family:<?php echo $ct_options['menu-font']['font-family'] ?>;*/
                font-weight:600;
                color:<?php echo $ct_options['menu-font-color']['regular'] ?>;
                padding: 10px 8px;
            }
            
            .main-menu > div > ul > li:hover > a{
                color:<?php echo $ct_options['menu-font-color']['hover'] ?>;
            }


            /* ===========================================================
            ================    Menu Popup Font Style    =================
            =========================================================== */
            .main-menu ul ul li a{
                font-size:<?php echo $ct_options['menu-popup-font']['font-size'] ?>;
                line-height:<?php echo $ct_options['menu-popup-font']['line-height'] ?>;
                font-family:<?php // echo $ct_options['menu-popup-font']['font-family'] ?>;
                font-weight:<?php echo $ct_options['menu-popup-font']['font-weight'] ?>;
                padding: 10px 8px;
            }


            /* ===========================================================
            ===========    Menu Font Style in Sticky Header    ===========
            =========================================================== */
            .sticky .main-menu > div > ul > li > a, header.plain.sticky .main-menu > div > ul > li > a{
                /*font-size:<?php echo $ct_options['menu-sticky-font']['font-size'] ?>;*/
                line-height:<?php echo $ct_options['menu-sticky-font']['line-height'] ?>;
                /*font-family:<?php echo $ct_options['menu-sticky-font']['font-family'] ?>;*/
                font-weight:<?php echo $ct_options['menu-sticky-font']['font-weight'] ?>;
                color:<?php echo $ct_options['menu-sticky-font-color']['regular'] ?> !important;
            }
            .sticky .main-menu > div > ul > li:hover > a{
                color:<?php echo $ct_options['menu-sticky-font-color']['hover'] ?> !important;
            }


            /* ===========================================================
            ===========    MEDIA QUERIES for MOBILE Menu    ===========
            =========================================================== */
            @media only screen and (min-width: 992px){
                .main-menu ul ul li a{
                    color:<?php echo $ct_options['menu-popup-font-color']['regular'] ?>;
                }
                .main-menu ul ul li:hover > a{
                    color:<?php echo $ct_options['menu-popup-font-color']['hover'] ?>;
                }
            }
            @media only screen and (max-width: 991px){
                header:not(.sticky) .main-menu > div > ul > li > a {
                    font-size:<?php echo $ct_options['menu-sticky-font']['font-size'] ?>;
                    line-height:<?php echo $ct_options['menu-sticky-font']['line-height'] ?>;
                    font-family:<?php echo $ct_options['menu-sticky-font']['font-family'] ?>;
                    font-weight:<?php echo $ct_options['menu-sticky-font']['font-weight'] ?>;
                }

                .main-menu li,
                .main-menu a {
                    color:<?php echo $ct_options['menu-sticky-font-color']['regular'] ?> !important;
                }

                .main-menu ul ul li a{
                    color:<?php echo $ct_options['menu-popup-font-color']['regular'] ?> !important;
                }
                .main-menu ul ul li:hover > a{
                    color:<?php echo $ct_options['menu-popup-font-color']['hover'] ?> !important;
                }

                .main-menu ul li a:hover,
                a.menu-item-has-children:hover,
                a.menu-item-has-children:focus,
                a.menu-item-has-children-mega:hover,
                a.menu-item-has-children-mega:focus {
                    color:<?php echo $ct_options['menu-sticky-font-color']['hover'] ?> !important;
                }
            }

        </style>
        
        <?php
        
            $content = ob_get_clean();
            $content = str_replace(array("\r\n", "\r"), "\n", $content);
            $lines = explode("\n", $content);

            $new_lines = array();

            foreach ($lines as $i => $line) {
                if (!empty($line))
                    $new_lines[] = trim($line);
            }
            echo implode($new_lines);
    }

}