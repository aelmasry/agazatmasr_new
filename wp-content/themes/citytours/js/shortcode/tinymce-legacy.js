/**
* v3.X TinyMCE specific functions. (before wordpress 3.9)
*/

(function() {
  tinymce.create('tinymce.plugins.ctShortcode', {

    init : function(ed, url){
      tinymce.plugins.ctShortcode.theurl = url;
    },

    createControl : function(btn, e) {
      if ( btn == 'ct_shortcode_button' ) {
        var a   = this;
        var btn = e.createSplitButton('ct_shortcode_button', {
          title: 'CT Shortcode',
          image: tinymce.plugins.ctShortcode.theurl + '/citytours.png',
          icons: false,
        });

        btn.onRenderMenu.add(function (c, b) {

          // Layouts
          c = b.addMenu({title:'Layouts'});

          a.render( c, 'Row', 'row' );
          a.render( c, 'One Half', 'one-half' );
          a.render( c, 'One Third', 'one-third' );
          a.render( c, 'One Fourth', 'one-fourth' );
          a.render( c, 'Two Third', 'two-third' );
          a.render( c, 'Three Fourth', 'three-fourth' );
          a.render( c, 'Column', 'column' );

          // Elements
          c = b.addMenu({title:'Elements'});

          a.render( c, 'Banner', 'banner' );
          a.render( c, 'Blockquote', 'blockquote' );
          a.render( c, 'Button', 'button' );
          a.render( c, 'Checklist', 'checklist' );
          a.render( c, 'Icon Box', 'icon-box' );
          a.render( c, 'Icon List', 'icon-list' );
          a.render( c, 'Pricing Table', 'pricing-table' );
          a.render( c, 'Review', 'review' );
          a.render( c, 'Tooltip', 'tooltip' );

          // Group
          c = b.addMenu({title:'Group'});

          a.render( c, 'Toggle/Accordion Container', 'toggles' );
          a.render( c, 'Toggle', 'toggle' );
          a.render( c, 'Tabs', 'tabs' );
          a.render( c, 'Tab', 'tab' );

        });
        return btn;
      }
      return null;
    },

    render : function(ed, title, id) {
      ed.add({
        title: title,
        onclick: function () {

          if( id === 'row' ) {
            tinyMCE.activeEditor.selection.setContent('[row]...[/row]');
          }

          if( id === 'one-half' ) {
            tinyMCE.activeEditor.selection.setContent('[one_half (offset="{0-6}") ]...[/one_half]');
          }

          if( id === 'one-third' ) {
            tinyMCE.activeEditor.selection.setContent('[one_third (offset="{0-8}") ]...[/one_third]');
          }

          if( id === 'one-fourth' ) {
            tinyMCE.activeEditor.selection.setContent('[one_fourth (offset="{0-9}") ]...[/one_fourth]');
          }

          if( id === 'two-third' ) {
            tinyMCE.activeEditor.selection.setContent('[two_third (offset="{0-4}") ]...[/two_third]');
          }

          if( id === 'three-fourth' ) {
            tinyMCE.activeEditor.selection.setContent('[three_fourth (offset="{0-3}") ]...[/three_fourth]');
          }

          if( id === 'column' ) {
            tinyMCE.activeEditor.selection.setContent('[column (lg = "{1-12}") (md = "{1-12}") (sm = "{1-12}") (xs = "{1-12}") (lgoff = "{0-12}") (mdoff = "{0-12}") (smoff = "{0-12}") (xsoff = "{0-12}") (lghide = "yes|no") (mdhide = "yes|no") (smhide = "yes|no") (xshide = "yes|no") (lgclear = "yes|no") (mdclear = "yes|no") (smclear = "yes|no") (xsclear = "yes|no") ]...[/column]');
          }

          if( id === 'banner' ) {
            tinyMCE.activeEditor.selection.setContent('[banner (style="colored")]...[/banner]');
          }

          if( id === 'blockquote' ) {
            tinyMCE.activeEditor.selection.setContent('[blockquote]...[/blockquote]');
          }

          if( id === 'button' ) {
            tinyMCE.activeEditor.selection.setContent('[button link="" (target="_blank|_self|_parent|_top|framename") (size="medium|full") (style="outline|white|green")]...[/button]');
          }

          if( id === 'checklist' ) {
            tinyMCE.activeEditor.selection.setContent('[checklist]...[/checklist]');
          }

          if( id === 'icon-box' ) {
            tinyMCE.activeEditor.selection.setContent('[icon_box (style="style2|style3") icon_class=""]...[/icon_box]');
          }

          if( id === 'icon-list' ) {
            tinyMCE.activeEditor.selection.setContent('[icon_list]...[/icon_list]');
          }

          if( id === 'pricing-table' ) {
            tinyMCE.activeEditor.selection.setContent('[pricing_table style="" price="" title="" btn_title="" btn_url="" btn_target="" btn_color="" btn_class="" ribbon_img_url="" (is_featured="yes")]...[/pricing_table]');
          }

          if( id === 'review' ) {
            tinyMCE.activeEditor.selection.setContent('[review name="" (rating="{1-5}") img_url=""]...[/review]');
          }

          if( id === 'tooltip' ) {
            tinyMCE.activeEditor.selection.setContent('[tooltip title="" (effect="1|2|3|4") (position="top|right|bottom|left") (style="advanced")]...[/tooltip]');
          }

          if( id === 'toggles' ) {
            tinyMCE.activeEditor.selection.setContent('[toggles (toggle_type="accordion|toggle") ][toggle title="Toggle Title" (active="yes|no")]...[/toggle][/toggles]');
          }

          if( id === 'toggle' ) {
            tinyMCE.activeEditor.selection.setContent('[toggle title="Toggle Title" (active="yes|no")]...[/toggle]');
          }

          if( id === 'tabs' ) {
            tinyMCE.activeEditor.selection.setContent('[tabs active_tab_index="{1-}"]...[/tabs]');
          }

          if( id === 'tab' ) {
            tinyMCE.activeEditor.selection.setContent('[tab id="" title="" active="yes|no"]...[/tab]');
          }

          return false;

        }
      });
    }
  
  });

  tinymce.PluginManager.add('ct_shortcode', tinymce.plugins.ctShortcode);

})();