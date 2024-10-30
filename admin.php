<?php

add_action( 'admin_enqueue_scripts', 'cfcf7_admin_enqueue_scripts', 11 ); // set priority so scripts and styles get loaded later.

function cfcf7_admin_enqueue_scripts( $hook_suffix ) {
	if ( false === strpos( $hook_suffix, 'wpcf7' ) ) {
		return; //don't load styles and scripts if this isn't a CF7 page.
	}

	wp_enqueue_script('cfcf7-scripts-admin', cfcf7_plugin_url( 'js/scripts_admin.js' ),array('jquery-ui-autocomplete', 'jquery-ui-sortable'), CFCF7_VERSION,true);
	wp_localize_script('cfcf7-scripts-admin', 'cfcf7_options_0', get_option(CFCF7_OPTIONS));

}

add_filter('wpcf7_editor_panels', 'add_conditional_panel');

function add_conditional_panel($panels) {
	if ( current_user_can( 'wpcf7_edit_contact_form' ) ) {
		$panels['cfcf7-conditional-panel'] = array(
			'title'    => __( 'Conditional fields', 'cfcf7' ),
			'callback' => 'cfcf7_editor_panel_conditional'
		);
	}
	return $panels;
}

function cfcf7_all_field_options($post, $selected = '-1') {
	$all_fields = $post->scan_form_tags();
	?>
	<option value="-1" <?php echo $selected == '-1'?'selected':'' ?>><?php echo __( '-- Select field --', 'cfcf7' ) ?></option>
	<?php
	foreach ($all_fields as $tag) {
		if ($tag['type'] == 'group' || $tag['name'] == '') continue;
		?>
		<option value="<?php echo $tag['name']; ?>" <?php echo $selected == $tag['name']?'selected':'' ?>><?php echo $tag['name']; ?></option>
		<?php
	}
}

function cfcf7_all_group_options($post, $selected = '-1') {
	$all_groups = $post->scan_form_tags(array('type'=>'group'));

	?>
	<option value="-1" <?php echo $selected == '-1'?'selected':'' ?>><?php echo __( '-- Select group --', 'cfcf7' ) ?></option>
	<?php
	foreach ($all_groups as $tag) {
		?>
		<option value="<?php echo $tag['name']; ?>" <?php echo $selected == $tag['name']?'selected':'' ?>><?php echo $tag['name']; ?></option>
		<?php
	}
}

if (!function_exists('all_operator_options')) {
	function all_operator_options($selected = 'equals') {
		$all_options = array('equals', 'not equals');
		$all_options = apply_filters('cfcf7_get_operators', $all_options);
		foreach($all_options as $option) {
			
			if ($option == 'equals') $text_option = __( 'equals', 'cfcf7' );
			if ($option == 'not equals') $text_option = __( 'not equals', 'cfcf7' );
			
			?>
			
			<option value="<?php echo htmlentities($option) ?>" <?php echo $selected == $option?'selected':'' ?>><?php echo htmlentities($text_option) ?></option>
			<?php
		}
	}
}

function cfcf7_editor_panel_conditional($form) {

	$form_id = $_GET['post'];
	$cfcf7_entries = get_post_meta($form_id,'cfcf7_options',true);

	if (!is_array($cfcf7_entries)) $cfcf7_entries = array();

	$cfcf7_entries = array_values($cfcf7_entries);

	?>
    <div class="cfcf7-inner-container">
        <h3><?php echo esc_html( __( 'Conditional fields', 'cfcf7' ) ); ?></h3>

        <?php
        print_entries_html($form);
        ?>

        <div id="cfcf7-entries">
    <!--        <pre>--><?php //print_r($cfcf7_entries) ?><!--</pre>-->
            <?php
            print_entries_html($form, $cfcf7_entries);
            ?>
        </div>

        <span id="cfcf7-add-button" title="add new rule"><?php echo __( '+ add new conditional rule', 'cfcf7' ) ?></span>

        <div id="cfcf7-text-entries">
            <p><a href="#" id="cfcf7-settings-to-text" class="button-primary"><?php echo __( 'import/export', 'cfcf7' ) ?></a></p>
            <div id="cfcf7-settings-text-wrap">
                <textarea id="cfcf7-settings-text"></textarea>
                <br><br>
                <?php echo __( 'Import actions (Beta feature!):', 'cfcf7' ) ?>&emsp;<input type="button" value="<?php echo __( 'Add conditions', 'cfcf7' ) ?>" id="add-fields" class="button-primary">&emsp;<input type="button" value="<?php echo __( 'Overwrite conditions', 'cfcf7' ) ?>" id="overwrite-fields" class="button-secondary">&emsp;<a href="#" id="cfcf7-settings-text-clear"><?php echo __( 'Close', 'cfcf7' ) ?></a>
				<br><br>
                <span style="color:red"><?php echo __( '<b>WARNING:</b> If you screw something up, just reload the page without saving. If you click <em>save</em> after screwing up, youre screwed.', 'cfcf7' ) ?> </span>

            </div>
        </div>
    </div>
<?php
}

// duplicate conditions on duplicate form part 1.
add_filter('wpcf7_copy','cfcf7_copy', 10, 2);
function cfcf7_copy($new_form,$current_form) {

	$id = $current_form->id();
	$props = $new_form->get_properties();
	$props['messages']['cfcf7_copied'] = $id;
	$new_form->set_properties($props);

	return $new_form;
}

// duplicate conditions on duplicate form part 2.
add_action('wpcf7_after_save','cfcf7_after_save',10,1);
function cfcf7_after_save($contact_form) {
	$props = $contact_form->get_properties();
	$original_id = isset($props['messages']['cfcf7_copied']) ? $props['messages']['cfcf7_copied'] : 0;
	if ($original_id !== 0) {
		$post_id = $contact_form->id();
		unset($props['messages']['cfcf7_copied']);
		$contact_form->set_properties($props);
		update_post_meta( $post_id, 'cfcf7_options', get_post_meta($original_id, 'cfcf7_options', true));
		return;
	}
}

// wpcf7_save_contact_form callback
add_action( 'wpcf7_save_contact_form', 'cfcf7_save_contact_form', 10, 1 );
function cfcf7_save_contact_form( $contact_form )
{

	if ( ! isset( $_POST ) || empty( $_POST ) || ! isset( $_POST['cfcf7_options'] ) || ! is_array( $_POST['cfcf7_options'] ) ) {
		return;
	}
	$post_id = $contact_form->id();
	if ( ! $post_id )
		return;

	unset($_POST['cfcf7_options']['{id}']); // remove the dummy entry

    $options = cfcf7_sanitize_options($_POST['cfcf7_options']);



	update_post_meta( $post_id, 'cfcf7_options', $options );

    return;

};

function cfcf7_sanitize_options($options) {
    //$options = array_values($options);
    $sanitized_options = [];
    foreach ($options as $option_entry) {
	    $sanitized_option = [];
	    $sanitized_option['then_field'] = sanitize_text_field($option_entry['then_field']);
	    foreach ($option_entry['and_rules'] as $and_rule) {
		    $sanitized_option['and_rules'][] = [
		            'if_field' => sanitize_text_field($and_rule['if_field']),
		            'operator' => $and_rule['operator'],
		            'if_value' => sanitize_text_field($and_rule['if_value']),
            ];
        }

	    $sanitized_options[] = $sanitized_option;
    }
    return $sanitized_options;
}

function print_entries_html($form, $cfcf7_entries = false) {

    $is_dummy = !$cfcf7_entries;

    if ($is_dummy) {
	    $cfcf7_entries = array(
		    '{id}' => array(
			    'then_field' => '-1',
			    'and_rules' => array(
				    0 => array(
					    'if_field' => '-1',
					    'operator' => 'equals',
					    'if_value' => ''
				    )
			    )
		    )
	    );
    }

	foreach($cfcf7_entries as $i => $entry) {

		// check for backwards compatibility ( < 2.0 )
		if (!key_exists('and_rules', $cfcf7_entries[$i]) || !is_array($cfcf7_entries[$i]['and_rules'])) {
			$cfcf7_entries[$i]['and_rules'][0] = $cfcf7_entries[$i];
		}

		$and_entries = array_values($cfcf7_entries[$i]['and_rules']);

		if ($is_dummy) {
?>
        <div id="cfcf7-new-entry">
<?php
        } else {
?>
        <div class="entry" id="entry-<?php echo $i ?>">
<?php
        }
		?>
            <div class="cfcf7-if">
				<span class="label"><?php echo __( 'Show', 'cfcf7' ) ?></span>
                <select name="cfcf7_options[<?php echo $i ?>][then_field]" class="then-field-select"><?php cfcf7_all_group_options($form, $entry['then_field']); ?></select>
            </div>
            <div class="cfcf7-and-rules" data-next-index="<?php echo count($and_entries) ?>">
				<?php
				foreach($and_entries as $and_i => $and_entry) {
					?>
                    <div class="cfcf7-and-rule">
                        <span class="rule-part if-txt label">if</span>
                        <select name="cfcf7_options[<?php echo $i ?>][and_rules][<?php echo $and_i ?>][if_field]"
                                class="rule-part if-field-select"><?php cfcf7_all_field_options( $form, $and_entry['if_field'] ); ?></select>
                        <select name="cfcf7_options[<?php echo $i ?>][and_rules][<?php echo $and_i ?>][operator]"
                                class="rule-part operator"><?php all_operator_options( $and_entry['operator'] ) ?></select>
                        <input name="cfcf7_options[<?php echo $i ?>][and_rules][<?php echo $and_i ?>][if_value]" class="rule-part if-value" type="text"
                               placeholder="<?php echo __( 'value', 'cfcf7' ) ?>" value="<?php echo $and_entry['if_value'] ?>">
                        <span class="and-button">&nbsp;<?php echo __( 'And', 'cfcf7' ) ?>&nbsp;</span>
                        <span title="delete rule" class="rule-part delete-button">&nbsp;<?php echo __( 'Remove', 'cfcf7' ) ?>&nbsp;</span>
                    </div>
					<?php
				}
				?>
            </div>
        </div>
		<?php
	}
}