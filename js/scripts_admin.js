/**
 * Created by jules on 7/17/2015.
 */
var $cfcf7_new_entry = jQuery('#cfcf7-new-entry').eq(0);

if ($cfcf7_new_entry.length > 0) {

    var cfcf7_new_and_rule_html = $cfcf7_new_entry.find('.cfcf7-and-rule')[0].outerHTML;
    var cfcf7_new_entry_html = $cfcf7_new_entry.html();

    var regex = /show \[(.*)\] if \[(.*)\] (equals|not equals|equals \(regex\)|not equals \(regex\)|>|>=|<=|<|is empty|not empty) "(.*)"/g;
    var regex_and = /and if \[(.*)\] (equals|not equals|equals \(regex\)|not equals \(regex\)|>|>=|<=|<|is empty|not empty) "(.*)"/g;


    if (_wpcf7 == null) { var _wpcf7 = wpcf7}; // wpcf7 4.8 fix

    var old_compose = _wpcf7.taggen.compose;

    var regexes = [
        { label: cfcf7_options_0.regex_email_label, desc: cfcf7_options_0.regex_email },
        { label: cfcf7_options_0.regex_numeric_label, desc: cfcf7_options_0.regex_numeric },
        { label: cfcf7_options_0.regex_alphanumeric_label, desc: cfcf7_options_0.regex_alphanumeric },
        { label: cfcf7_options_0.regex_alphabetic_label, desc: cfcf7_options_0.regex_alphabetic },
        { label: cfcf7_options_0.regex_date_label, desc: cfcf7_options_0.regex_date },
        { label: cfcf7_options_0.regex_custom_1_label, desc: cfcf7_options_0.regex_custom_1 },
        { label: cfcf7_options_0.regex_custom_2_label, desc: cfcf7_options_0.regex_custom_2 },
        { label: cfcf7_options_0.regex_custom_3_label, desc: cfcf7_options_0.regex_custom_3 },
        { label: cfcf7_options_0.regex_custom_4_label, desc: cfcf7_options_0.regex_custom_4 },
        { label: cfcf7_options_0.regex_custom_5_label, desc: cfcf7_options_0.regex_custom_5 },
    ];

    var i = regexes.length;
    while (i--) {
        if (null == regexes[i].label || null == regexes[i].desc || regexes[i].label == '' || regexes[i].desc == '') {
            regexes.splice(i,1);
        }
    }

    var termTemplate = "<span class='ui-autocomplete-term'>%s</span>";

    (function($) {

        $('#cfcf7-entries').sortable();
        $(('.cfcf7-and-rules')).sortable();


        // ...before overwriting the jQuery extension point
        _wpcf7.taggen.compose = function(tagType, $form)
        {

           $('#tag-generator-panel-group-style-hidden').val($('#tag-generator-panel-group-style').val());

            // original behavior - use function.apply to preserve context
            var ret = old_compose.apply(this, arguments);
            //tagType = arguments[0];
            //$form = arguments[1];

            // START: code here will be executed after the _wpcf7.taggen.update function
            if (tagType== 'group') ret += "[/group]";
            if (tagType== 'repeater') ret += "[/repeater]";

            // END

            if (tagType== 'togglebutton') {
                $val1 = $('#tag-generator-panel-togglebutton-value-1');
                $val2 = $('#tag-generator-panel-togglebutton-value-2');
                var val1 = $val1.val();
                var val2 = $val2.val();

                if (val1 == "") val1 = $val1.data('default');
                if (val2 == "") val2 = $val2.data('default');

                str_val = ' "'+val1+'" "'+val2+'"';

                ret = ret.replace(']', str_val+']');
            }

            return ret;
        };

        var index = $('#cfcf7-entries .entry').length;
        var index_and = 0;

        $('#cfcf7-add-button').click(function(){

            var id = add_condition_fields();

            return false;

        });

        function clear_all_condition_fields() {
            $('.entry').remove();
        }

        function add_condition_fields() {
            $('<div class="entry" id="entry-'+index+'">'+(cfcf7_new_entry_html.replace(/{id}/g, index))+'</div>').appendTo('#cfcf7-entries');
            index++;
            update_entries();
            return (index-1);
        }

        function add_and_condition_fields(id) {
            // $('#entry-'+id+' .cfcf7-and-rules').eq(0).append($cfcf7_new_and_rule.clone());
            $('#entry-'+id+' .cfcf7-and-rules').eq(0).append(cfcf7_new_and_rule_html.replace(/{id}/g, index-1).replace(/\[and_rules\]\[0\]/g, '[and_rules]['+index_and+']'));
            index_and++;
            return (index_and-1);
        }

        function import_condition_fields() {

            $if_values = $('.if-value');

            var lines = $('#cfcf7-settings-text').val().split(/\r?\n/);

            var id = -1;

            for (var i = 0; i<lines.length; i++) {

                var str = lines[i];

                var match = regex.exec(str);

                if (match != null) {

                    index_and = 0; // reset this for each first condition (This one has and_index [0]).

                    id = add_condition_fields();

                    $('#entry-'+id+' .then-field-select').val(match[1]);
                    $('#entry-'+id+' .if-field-select').val(match[2]);
                    $('#entry-'+id+' .operator').val(match[3]);
                    $('#entry-'+id+' .if-value').val(match[4]);

                    index_and = 1; // the next and condition will gave and_index[1];

                    regex.lastIndex = 0;

                }

                match = regex_and.exec(str);

                if (match != null && id != -1) {

                    var and_id = add_and_condition_fields(id);

                    $('#entry-'+id+' .cfcf7-and-rule:last-child .if-field-select').val(match[1]);
                    $('#entry-'+id+' .cfcf7-and-rule:last-child .operator').val(match[2]);
                    $('#entry-'+id+' .cfcf7-and-rule:last-child .if-value').val(match[3]);

                    regex_and.lastIndex = 0;

                }
            }
        }

        // export/import settings

        $('#cfcf7-settings-text-wrap').hide();

        $('#cfcf7-settings-to-text').click(function() {
            $('#cfcf7-settings-text-wrap').show();

            $('#cfcf7-settings-text').val('');
            $('#cfcf7-entries .entry').each(function() {
                var $entry = $(this);
                var line = 'show [' + $entry.find('.then-field-select').val() + ']';
                var text_indent = line.length-3;
                $entry.find('.cfcf7-and-rule').each(function(i) {
                    $and_rule = $(this);
                    if (i>0) {

                        line += '\n'+' '.repeat(text_indent)+'and';

                    }
                    line += ' if [' + $and_rule.find('.if-field-select').val() + ']'
                    + ' ' + $and_rule.find('.operator').val()
                    + ' "' + $and_rule.find('.if-value').val() + '"';
                });
                $('#cfcf7-settings-text').val($('#cfcf7-settings-text').val() + line + "\n" ).select();
            });
            return false;
        });

        $if_values = $('.if-value');

        $('#add-fields').click(function() {
            import_condition_fields();
            update_entries();
            return false;
        });

        $('#overwrite-fields').click(function() {
            clear_all_condition_fields();
            import_condition_fields();
            update_entries();
            return false;
        });

        $('#cfcf7-settings-text-clear').click(function() {
            $('#cfcf7-settings-text-wrap').hide();
            $('#cfcf7-settings-text').val('');
            return false;
        });

        function update_entries() {
            $if_values = $('.if-value');
            init_autocomplete();
            $if_values.css({'visibility':'visible'});
            $if_values.autocomplete( "disable" );

            $('#cfcf7-entries .cfcf7-and-rule').each(function() {
                var $and_rule = $(this);
                if ($and_rule.find('.operator').eq(0).val() === 'is empty' || $and_rule.find('.operator').eq(0).val() === 'not empty') {
                    $and_rule.find('.if-value').eq(0).css({'visibility':'hidden'});
                } else if ($and_rule.find('.operator').eq(0).val().endsWith('(regex)')) {
                    $and_rule.find('.if-value').eq(0).autocomplete( "enable" );
                }
            });

            scale_and_button();

            set_events();
        }

        function init_autocomplete() {

            $if_values.autocomplete({
                disabled: true,
                source: function(request, response) {
                    var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
                    response($.grep(regexes, function(value) {
                        return matcher.test(value.label || value.value || value) || matcher.test(value.desc);
                    }));
                },
                focus: function( event, ui ) {
                    $( event.target ).val( ui.item.desc );
                    return false;
                },
                select: function( event, ui ) {
                    $( event.target ).val( ui.item.desc );
                    return false;
                },
                open: function(e,ui) {
                    $el = $(e.target);
                    var styledTerm = termTemplate.replace('%s', $el.val());

                    $('.ui-autocomplete').find('em').each(function() {
                        var me = $(this);
                        me.html( me.text().replace($el.val(), styledTerm) );
                    });
                },
                minLength: 0
            }).each(function() {
                $(this).autocomplete( "instance" )._renderItem = function( ul, item ) {
                    return $("<li>")
                    .append("<div><em>" + item.label + "</em><br><em>" + item.desc + "</em></div>")
                    .appendTo(ul);
                }
            });
            $if_values.on('focus', function() {
                $(this).autocomplete("search");
            });
        }

        update_entries();

        function set_events() { // called at the end of update_entries

            $('.cfcf7-and-rules').sortable();

            $('.and-button').off('click').click(function() {
                $this = $(this);
                $andblock = $this.closest('.cfcf7-and-rule');
                $andblocks_container = $this.closest('.cfcf7-and-rules');
                next_index = $andblocks_container.data('next-index');
                $andblocks_container.data('next-index',next_index+1);
                var and_i = next_index;
                clone_html = $andblock.get(0).outerHTML.replace(/cfcf7_options\[([0-9]*)\]\[and_rules\]\[([0-9]*)\]/g, 'cfcf7_options[$1][and_rules]['+and_i+']');
                $andblock.after(clone_html);
                update_entries();
                return false;
            });

            $('.delete-button').off('click').click(function(){
                $and_rule = $(this).closest('.cfcf7-and-rule');
                if ($and_rule.siblings().length > 0) {
                    $and_rule.remove();
                } else {
                    $and_rule[0].closest('.entry').remove();
                }

                update_entries();

                return false;
            });

            $('.operator').off('change').change(function() {
                update_entries();
                return false;
            });
        }

        function scale_and_button() {
            $('.cfcf7-and-rule:first-child .and-button').each(function(){
               $and_button = $(this);
               num_and_rules = $and_button.closest('.cfcf7-and-rule').siblings().length+1;
               var height = (34*num_and_rules-12)+'px';
               $and_button.css({'height':height,'line-height':height});
            });
        }

    })( jQuery );

}

(function($) {
    // ------------------------------------
    //            OPTIONS PAGE
    // ------------------------------------

    $(document).ready(function() {

        $('.cfcf7-options-notice .notice-dismiss-2').click(function () {
            $('.cfcf7-options-notice .notice-dismiss').click();
        });
        $('.cfcf7-options-notice .notice-dismiss').click(function () {
            cfcf7_dismiss_notice();
        });

        function cfcf7_dismiss_notice() {
            console.log(ajaxurl);

            $('input[name="cfcf7_options[notice_dismissed]"]').val('true');

            $.post(ajaxurl, {action:'cfcf7_dismiss_notice'}, function(response) {
                // nothing to do. dismiss_notice option should be set to TRUE server side by now.
            });
        }

    });
})( jQuery );