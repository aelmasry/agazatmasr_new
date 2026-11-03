/**
* v4.X TinyMCE specific functions. (from wordpress 3.9)
*/

(function() {

  tinymce.PluginManager.add('ct_shortcode', function(editor, url) {

    editor.addButton('ct_shortcode_button', {

      type  : 'menubutton',
      title  : 'CT Shortcode',
      style : 'background-image: url("' + url + '/citytours.png' + '"); background-repeat: no-repeat; background-position: 2px 2px;"',
      icon  : true,
      menu  : [
        { text: 'Layouts',
          menu : [
             { text : 'Row', onclick: function() {editor.insertContent('[row]...[/row]');} },
             { text : 'One Half', onclick: function() {editor.insertContent('[one_half (offset="{0-6}") ]...[/one_half]');} },
             { text : 'One Third', onclick: function() {editor.insertContent('[one_third (offset="{0-8}") ]...[/one_third]');} },
             { text : 'One Fourth', onclick: function() {editor.insertContent('[one_fourth (offset="{0-9}") ]...[/one_fourth]');} },
             { text : 'Two Third', onclick: function() {editor.insertContent('[two_third (offset="{0-4}") ]...[/two_third]');} },
             { text : 'Three Fourth', onclick: function() {editor.insertContent('[three_fourth (offset="{0-3}") ]...[/three_fourth]');} },
             { text : 'Column', onclick: function() {editor.insertContent('[column (lg = "{1-12}") (md = "{1-12}") (sm = "{1-12}") (xs = "{1-12}") (lgoff = "{0-12}") (mdoff = "{0-12}") (smoff = "{0-12}") (xsoff = "{0-12}") (lghide = "yes|no") (mdhide = "yes|no") (smhide = "yes|no") (xshide = "yes|no") (lgclear = "yes|no") (mdclear = "yes|no") (smclear = "yes|no") (xsclear = "yes|no") ]...[/column]');} },
             { text : 'Container', onclick: function() {editor.insertContent('[container]...[/container]');} },
          ]
        },
        { text: 'Main Elements',
          menu : [
             { text : 'Banner', onclick: function() {editor.insertContent('[banner (style="colored")]...[/banner]');} },
             { text : 'Blockquote', onclick: function() {editor.insertContent('[blockquote]...[/blockquote]');} },
             { text : 'Button', onclick: function() {editor.insertContent('[button link="" (target="_blank|_self|_parent|_top|framename") (size="medium|full") (style="outline|white|green")]...[/button]');} },
             { text : 'Checklist', onclick: function() {editor.insertContent('[checklist]...[/checklist]');} },
             { text : 'Icon Box', onclick: function() {editor.insertContent('[icon_box (style="style2|style3") icon_class=""]...[/icon_box]');} },
             { text : 'Icon List', onclick: function() {editor.insertContent('[icon_list]...[/icon_list]');} },
             { text : 'Pricing Table', onclick: function() {editor.insertContent('[pricing_table style="" price="" title="" btn_title="" btn_url="" btn_target="" btn_color="" btn_class="" ribbon_img_url="" (is_featured="yes")]...[/pricing_table]');} },
             { text : 'Review', onclick: function() {editor.insertContent('[review name="" (rating="{1-5}") img_url=""]...[/review]');} },
             { text : 'Tooltip', onclick: function() {editor.insertContent('[tooltip title="" (effect="1|2|3|4") (position="top|right|bottom|left") (style="advanced")]...[/tooltip]');} },
          ]
        },
        { text: 'Group',
          menu : [
             { text : 'Toggles/Accordions', onclick: function() {editor.insertContent('[toggles (toggle_type="accordion|toggle") ][toggle title="Toggle Title" (active="yes|no")]...[/toggle][/toggles]');} },
             { text : 'Toggle', onclick: function() {editor.insertContent('[toggle title="Toggle Title" (active="yes|no")]...[/toggle]');} },
             { text : 'Tabs', onclick: function() {editor.insertContent('[tabs active_tab_index="{1-}"]...[/tabs]');} },
             { text : 'Tab', onclick: function() {editor.insertContent('[tab id="" title="" active="yes|no"]...[/tab]');} },
          ]
        },
        { text: 'Pages & Lists',
          menu : [
             { text : 'Hotel Cart Page', onclick: function() {editor.insertContent('[hotel_cart]');} },
             { text : 'Hotel CheckOut Page', onclick: function() {editor.insertContent('[hotel_checkout]');} },
             { text : 'Hotel Booking Confirmation Page', onclick: function() {editor.insertContent('[hotel_booking_confirmation]');} },
             { text : 'Tour Cart Page', onclick: function() {editor.insertContent('[tour_cart]');} },
             { text : 'Tour CheckOut Page', onclick: function() {editor.insertContent('[tour_checkout]');} },
             { text : 'Tour Booking Confirmation Page', onclick: function() {editor.insertContent('[tour_booking_confirmation]');} },
          ]
        }
      ]

    });

  });

})();