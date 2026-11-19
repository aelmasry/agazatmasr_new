(function($){ 
    "use strict";

    $(document).ready( function() { 

        function showImportMessage( selected_demo, message, count, index ) {
            var html = '', percent = 0;

            if ( selected_demo ) {
                html += '<h3>Installing ' + $('.demo-info').html() + '</h3>';
            }
            if ( message ) {
                html += '<strong>' + message + '</strong>';
            }

            if ( count && index ) {
                percent = index / count * 100;
                if ( percent > 100 )
                    percent = 100;

                html += '<div class="import-progress-bar"><div style="width:' + percent + '%;"></div></div>';
            }

            $('.citytours-theme-demo #import-status').stop().show().html(html);
        }

        // import options
        function ct_import_options( options ) {
            if ( ! options.demo ) {
                // removeAlertLeavePage();
                return;
            }

            if ( options.import_options ) {
                var demo = options.demo,
                    data = { 
                        'action': 'ct_import_options', 
                        'demo': demo 
                    };

                showImportMessage( demo, 'Importing theme options' );

                $.post( ajaxurl, data, function(response) {
                    if ( response ) {
                        showImportMessage( demo, response );
                    }

                    ct_reset_menus( options );
                }).fail(function(response) {
                    ct_reset_menus( options );
                });
            } else {
                ct_reset_menus( options );
            }
        }

        // reset_menus
        function ct_reset_menus( options ) {
            if ( ! options.demo ) {
                // removeAlertLeavePage();
                return;
            }

            if ( options.reset_menus ) {
                var data = {
                        'action': 'ct_reset_menus'
                    };

                $.post( ajaxurl, data, function(response) {
                    if ( response ) {
                        showImportMessage( options.demo, response );
                    }
                    ct_reset_widgets( options );
                }).fail(function(response) {
                    ct_reset_widgets( options );
                });
            } else {
                ct_reset_widgets( options );
            }
        }

        // reset widgets
        function ct_reset_widgets( options ) {
            if ( ! options.demo ) {
                // removeAlertLeavePage();
                return;
            }

            if ( options.reset_widgets ) {
                var demo = options.demo,
                    data = {
                        'action': 'ct_reset_widgets'
                    };

                $.post( ajaxurl, data, function(response) {
                    if ( response ) {
                        showImportMessage( demo, response );
                    }
                    ct_import_dummy( options );
                }).fail(function(response) {
                    ct_import_dummy( options );
                });
            } else {
                ct_import_dummy( options );
            }
        }

        // import dummy content
        var dummy_index = 0, 
            dummy_count = 0, 
            dummy_process = 'import_start';

        function ct_import_dummy( options ) {
            if ( ! options.demo ) {
                // removeAlertLeavePage();
                return;
            }

            if ( options.import_dummy ) {
                var data = {
                        'action': 'ct_import_dummy', 
                        'process':'import_start', 
                        'demo': options.demo
                    };

                dummy_index = 0;
                dummy_count = 0;
                dummy_process = 'import_start';

                ct_import_dummy_process( options, data );
            } else {
                ct_import_widgets(options);
            }
        }

        // import dummy content process
        function ct_import_dummy_process( options, args ) {
            var demo = options.demo;

            $.post( ajaxurl, args, function(response) {
                if ( response && /^[\],:{}\s]*$/.test(response.replace(/\\["\\\/bfnrtu]/g, '@').
                    replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
                    replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
                    response = $.parseJSON(response);

                    if ( response.process != 'complete' ) {
                        var requests = {
                            'action': 'ct_import_dummy'
                        };

                        if (response.process) requests.process = response.process;
                        if (response.index) requests.index = response.index;

                        requests.demo = demo;
                        ct_import_dummy_process(options, requests);

                        dummy_index = response.index;
                        dummy_count = response.count;
                        dummy_process = response.process;

                        showImportMessage( demo, response.message, dummy_count, dummy_index );
                    } else {
                        showImportMessage( demo, response.message );
                        ct_import_widgets( options );
                    }
                } else {
                    showImportMessage( demo, 'Failed importing! Please check the "System Status" tab to ensure your server meets all requirements for a successful import. Settings that need attention will be listed in red.' );
                    ct_import_widgets( options );
                }
            }).fail(function(response) {
                var requests = {
                    'action': 'ct_import_dummy'
                };

                if ( dummy_index < dummy_count ) {
                    requests.process = dummy_process;
                    requests.index = ++dummy_index;
                    requests.demo = demo;

                    ct_import_dummy_process( options, requests );
                } else {
                    requests.process = dummy_process;
                    requests.demo = demo;

                    ct_import_dummy_process( options, requests );
                }
            });
        }

        // import widgets
        function ct_import_widgets( options ) {
            if ( ! options.demo ) {
                // removeAlertLeavePage();
                return;
            }

            if ( options.import_widgets ) {
                var demo = options.demo,
                    data = {
                        'action': 'ct_import_widgets', 
                        'demo': demo
                    };

                showImportMessage( demo, 'Importing widgets' );

                $.post( ajaxurl, data, function(response) {
                    if ( response ) {
                        showImportMessage(demo, response);
                    }
                    ct_import_finished( options );
                }).fail(function(response) {
                    ct_import_finished( options );
                });
            } else {
                ct_import_finished( options );
            }
        }

        // import finished
        function ct_import_finished( options ) {
            if ( ! options.demo ) {
                // removeAlertLeavePage();
                return;
            }

            setTimeout(function() {
                showImportMessage( options.demo, 'Finished! Please visit your site.');
                // setTimeout( removeAlertLeavePage, 3000 );
            }, 3000);
        }

        // tab switch ( Demo Importer / System Status )
        $('.citytours-theme-demo .demo-tab-switch').click( function(e) { 
            e.preventDefault();

            $('.citytours-theme-demo .demo-tab-switch').removeClass('active');
            $(this).addClass('active');

            var container_id = $(this).attr('href');

            $('.citytours-theme-demo .demo-tab').hide();

            $(container_id).show();
        } );

        $('.status_toggle2').click( function(e) { 
            e.preventDefault();

            $('.citytours-theme-demo .demo-tab-switch').removeClass('active');
            $('.citytours-theme-demo .demo-tab-switch#status_toggle').addClass('active');

            var container_id = $(this).attr('href');

            $('.citytours-theme-demo .demo-tab').hide();
            $(container_id).show();
        } );

        // Install Demo
        $('.citytours-install-demo-button1').on('click', function(e) { 
            e.preventDefault();

            var selected_demo = $(this).data('demo-id');
            var loading_img = $('.preview-'+selected_demo);
            var disable_preview = $('.preview-all');

            $('.import-success').hide();
            $('.import-failed').hide();

            // var confirm = window.confirm('This process will get you the pre-built layouts you see in the demo, a few dummy blog posts, products and it sets the theme’s options from the live theme’s demo. It will take a little while so please remember that patience is a virtue.\n\n*****\n\nREQUIREMENTS:\n\n• WooCommerce and Visual Composer must be a activated before the import is started\n• Memory Limit on your server to be set to: 256MB\n• PHP Max Execution Time to be set to: 300 seconds');
            var confirm = true;

            if(confirm == true) {

                loading_img.show();
                disable_preview.show();

                var data = {
                    action: 'ct_demo_importer',
                    demo_type: selected_demo
                };

                $('.importer-notice').hide();

                $.post(ajaxurl, data, function(response) {
                    // console.log(response);
                    // $('.importer-response').html(response);
                    // alert(response);
                    if( (response && response.indexOf('imported') != -1 ) ) 
                    {
                        $('.import-success').attr('style','display:block !important');
                    }
                    else
                    {
                        $('.import-failed').attr('style','display:block !important');
                    }
                    
                    console.log('success');
                }).fail(function() {
                    $('.import-failed').attr('style','display:block !important');
                }).always(function(response) {
                    loading_img.hide();
                    disable_preview.hide();
                });
            }
        });

        // install demo
        $( '.citytours-install-demo-button' ).live( 'click', function(e) {
            e.preventDefault();

            var $this = $(this),
                selected_demo = $this.data( 'demo-id' ),
                disabled = $this.attr('disabled');

            if (disabled)
                return;

            // addAlertLeavePage();

            $('#citytours-install-demo-type').val(selected_demo);
            $('#citytours-install-options').slideDown();

            $('html, body').stop().animate({
                scrollTop: $('#citytours-install-options').offset().top - 50
            }, 600);

        });

        // import
        $('#citytours-import-yes').click(function() {
            var demo = $('#citytours-install-demo-type').val(),
                options = {
                    demo: demo,
                    reset_menus: $('#citytours-reset-menus').is(':checked'),
                    reset_widgets: $('#citytours-reset-widgets').is(':checked'),
                    import_dummy: $('#citytours-import-dummy').is(':checked'),
                    import_widgets: $('#citytours-import-widgets').is(':checked'),
                    //import_sliders: $('#citytours-import-sliders').is(':checked'),
                    import_options: $('#citytours-import-options').is(':checked'),
                };

            if ( demo ) {
                showImportMessage( demo, '' );
                ct_import_options( options );
            }

            $('#citytours-install-options').slideUp();
        });

        // cancel import button
        $('#citytours-import-no').click(function() {
            $('#citytours-install-options').slideUp();
            
            // removeAlertLeavePage();
        });
    });

})( jQuery );