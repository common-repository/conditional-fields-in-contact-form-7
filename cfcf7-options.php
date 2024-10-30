<?php

define('CFCF7_SLUG', 'cfcf7');
define('CFCF7_OPTIONS', CFCF7_SLUG.'_options');
define('CFCF7_TEXT_DOMAIN', CFCF7_SLUG.'_text_domain');

define('CFCF7_DEFAULT_ANIMATION', 'yes');
define('CFCF7_DEFAULT_ANIMATION_INTIME', 200);
define('CFCF7_DEFAULT_ANIMATION_OUTTIME', 200);
define('CFCF7_DEFAULT_NOTICE_DISMISSED', false);

$cfcf7_default_options = array(
    'animation' => CFCF7_DEFAULT_ANIMATION,
    'animation_intime' => CFCF7_DEFAULT_ANIMATION_INTIME,
    'animation_outtime' => CFCF7_DEFAULT_ANIMATION_OUTTIME,
    'notice_dismissed' => CFCF7_DEFAULT_NOTICE_DISMISSED
);

if ( ! defined( 'WPCF7_ADMIN_READ_WRITE_CAPABILITY' ) ) {
	define( 'WPCF7_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );
}

$cfcf7_default_options = apply_filters('cfcf7_default_options', $cfcf7_default_options);

$cfcf7_options = get_option(CFCF7_OPTIONS);

if (!is_array($cfcf7_options)) {
	$cfcf7_options = $cfcf7_default_options;
	update_option(CFCF7_OPTIONS, $cfcf7_options);
}

if(isset($_POST['reset'])) {
    update_option(CFCF7_OPTIONS, $cfcf7_default_options);
    $cfcf7_options['cfcf7_settings_saved'] = 0;
}

// this setting will only be 0 as long as the user has not saved any settings. Once the user has saved the cfcf7 settings, this value will always remain 1.
if (!key_exists('cfcf7_settings_saved',$cfcf7_options)) $cfcf7_options['cfcf7_settings_saved'] = 0;

if ($cfcf7_options['cfcf7_settings_saved'] == 0) {
    $cfcf7_options = $cfcf7_default_options;
}

// LINE 37: removed some ninja_forms related code. Not sure what it was doing here. Keep this reminder here for a while in case problems pop up.
// Remove in future update. (Jules 17/02/2018)

add_action( 'admin_enqueue_scripts', 'cfcf7_load_page_options_wp_admin_style' );
function cfcf7_load_page_options_wp_admin_style() {
    wp_register_style( 'cfcf7_admin_css', plugins_url('css/admin-style.css',__FILE__), false, CFCF7_VERSION );
    wp_enqueue_style( 'cfcf7_admin_css' );
}


add_action('admin_menu', 'cfcf7_admin_add_page');
function cfcf7_admin_add_page() {
    add_submenu_page('wpcf7', __( 'Conditional fields', 'cfcf7' ), __( 'Conditional fields', 'cfcf7' ), WPCF7_ADMIN_READ_WRITE_CAPABILITY, 'cfcf7', 'cfcf7_options_page' );
}

function cfcf7_options_page() {
    global $cfcf7_options;

//    // Include in admin_enqueue_scripts action hook
//    wp_enqueue_media();
//    //wp_enqueue_script( 'custom-background' );
//    wp_enqueue_script( 'cfcf7-image-upload', plugins_url('framework/js/bdwm-image-upload.js',__FILE__), array('jquery'), '1.0.0', true );

    if (isset($_POST['reset'])) {
        echo '<div id="message" class="updated fade"><p><strong>'.__( 'Settings restored to defaults', 'cfcf7' ).'</strong></p></div>';
    } else if (isset($_REQUEST['settings-updated'])) {
        echo '<div id="message" class="updated fade"><p><strong>'.__( 'Settings updated', 'cfcf7' ).'</strong></p></div>';
    }

    ?>

    <div class="wrap cfcf7-admin-wrap">
        <form action="options.php" method="post">
            <?php settings_fields(CFCF7_OPTIONS); ?>

            <input type="hidden" value="1" id="cfcf7_settings_saved" name="<?php echo CFCF7_OPTIONS.'[cfcf7_settings_saved]' ?>">
            <input type="hidden" name="<?php echo CFCF7_OPTIONS.'[notice_dismissed]' ?>" value="<?php echo $cfcf7_options['notice_dismissed'] ?>" />


            <h3><?php echo __( 'Default animation Settings', 'cfcf7' ); ?></h3>

            <?php

            cfcf7_input_select('animation', array(
                'label' => __( 'Animation', 'cfcf7' ),
                'description' => __( 'Use animations while showing/hiding groups', 'cfcf7' ),
                'options' => array('no'=> __( 'Disabled', 'cfcf7' ), 'yes' => __( 'Enabled', 'cfcf7' ))
            ));

            cfcf7_input_field('animation_intime', array(
                'label' => __( 'Animation In time', 'cfcf7' ),
                'description' => __( 'A positive integer value indicating the time, in milliseconds, it will take for each group to show.', 'cfcf7' ),
            ));

            cfcf7_input_field('animation_outtime', array(
                'label' => __( 'Animation Out Time', 'cfcf7' ),
                'description' => __( 'A positive integer value indicating the time, in milliseconds, it will take for each group to hide.', 'cfcf7' ),
            ));

            submit_button();

            ?>

        </form></div>

    <h3><?php echo __( 'Restore Default Settings', 'cfcf7' ); ?></h3>
    <form method="post" id="reset-form" action="">
        <p class="submit">
            <input name="reset" class="button button-secondary" type="submit" value="<?php echo __( 'Restore defaults', 'cfcf7' ); ?>" >
            <input type="hidden" name="action" value="reset" />
        </p>
    </form>
    <script>
        (function($){
            $('#reset-form').submit(function() {
                return confirm('<?php echo __( 'Are you sure you want to reset the plugin settings to the default values? All changes you have previously made will be lost.', 'cfcf7' ); ?>');
            });
        }(jQuery))
    </script>

    <?php
}


function cfcf7_image_field($slug, $args) {

    global $cfcf7_options, $cfcf7_default_options;

    $defaults = array(
        'title'=>'Image',
        'description' => '',
        'choose_text' => __( 'Choose an image', 'cfcf7' ),
        'update_text' => __( 'Use image', 'cfcf7' ),
        'default' => $cfcf7_default_options[$slug]
    );

    $args = wp_parse_args( $args, $defaults );
    extract($args);
    $label; $description; $choose_text; $update_text; $default;

    if (!key_exists($slug, $cfcf7_options)) {
        $cfcf7_options[$slug] = $default;
    }

    ?>
    <div class="option-line">
        <span class="label"><?php echo $label; ?></span>
        <?php
        if ($description) {
            ?>
            <p><?php echo $description; ?></p>
            <?php
        }
        ?>
        <div>
        <div class="image-container" id="default-thumbnail-preview_<?php echo $slug ?>">
            <?php
            if ($cfcf7_options[$slug] != '') {
                $img_info = wp_get_attachment_image_src($cfcf7_options[$slug], 'full');
                $img_src = $img_info[0];
                ?>
                <img src="<?php echo $img_src ?>" height="100">
                <?php
            }
            ?>
        </div>
        <a class="choose-from-library-link" href="#"
           data-field="<?php echo CFCF7_OPTIONS.'_'.$slug ?>"
           data-image_container="default-thumbnail-preview_<?php echo $slug ?>"
           data-choose="<?php echo $choose_text; ?>"
           data-update="<?php echo $update_text; ?>"><?php __( 'Choose image', 'cfcf7' ); ?>
        </a>
        <input type="hidden" value="<?php echo $cfcf7_options[$slug] ?>" id="<?php echo CFCF7_OPTIONS.'_'.$slug ?>" name="<?php echo CFCF7_OPTIONS.'['.$slug.']' ?>">
        </div>
    </div>
    <?php

}

function cfcf7_input_field($slug, $args) {
    global $cfcf7_options, $cfcf7_default_options;

    $defaults = array(
        'label'=>'',
        'desription' => '',
        'default' => $cfcf7_default_options[$slug],
        'label_editable' => false
    );

    $args = wp_parse_args( $args, $defaults );
    extract($args);

    $label; $description; $default; $label_editable;

    if (!key_exists($slug, $cfcf7_options)) {
        $cfcf7_options[$slug] = $default;
        $cfcf7_options[$slug.'_label'] = $label;
    }

    ?>
    <div class="option-line">
        <?php if ($label_editable) { ?>
            <span class="label editable"><input type="text" data-default-value="<?php echo $label ?>" value="<?php echo $cfcf7_options[$slug.'_label'] ?>" id="<?php echo CFCF7_OPTIONS.'_'.$slug.'_label' ?>" name="<?php echo CFCF7_OPTIONS.'['.$slug.'_label]' ?>"></span>
        <?php } else { ?>
            <span class="label"><?php echo $label ?></span>
        <?php } ?>
        <span class="field"><input type="text" data-default-value="<?php echo $default ?>" value="<?php echo $cfcf7_options[$slug] ?>" id="<?php echo CFCF7_OPTIONS.'_'.$slug ?>" name="<?php echo CFCF7_OPTIONS.'['.$slug.']' ?>"></span>
        <span class="description"><?php echo $description ?><?php if (!empty($default)) echo ' ('.__( 'Default: ', 'cfcf7' ).$default.')' ?></span>
    </div>
    <?php

}

function cfcf7_input_select($slug, $args) {
    global $cfcf7_options, $cfcf7_default_options;

    $defaults = array(
        'label'=>'',
        'desription' => '',
        'options' => array(), // array($name => $value)
        'default' => $cfcf7_default_options[$slug],
    );

    $args = wp_parse_args( $args, $defaults );
    extract($args);

    $label; $description; $options; $default;

    if (!key_exists($slug, $cfcf7_options)) {
        $cfcf7_options[$slug] = $default;
    }

    // $first_element = array('-1' => '-- Select --');
    // $options = array_merge($first_element, $options);

    ?>
    <div class="option-line">
        <span class="label"><?php echo $label ?></span>
        <span class="field">
			<select id="<?php echo CFCF7_OPTIONS.'_'.$slug ?>" data-default-value="<?php echo $default ?>" name="<?php echo CFCF7_OPTIONS.'['.$slug.']' ?>">
<?php
foreach($options as $value => $text) {
    ?>
    <option value="<?php echo $value ?>" <?php echo $cfcf7_options[$slug]==$value?'selected':'' ?>><?php echo $text ?></option>
    <?php
}
?>
			</select>			
		</span>
        <span class="description"><?php echo $description ?><?php if (!empty($default)) echo ' ('.__( 'Default: ', 'cfcf7' ).$options[$default].')' ?></span>
    </div>
    <?php

}

function cfcf7_checkbox($slug, $args) {
    global $cfcf7_options, $cfcf7_default_options;

    $defaults = array(
        'label'=>'',
        'desription' => '',
        'default' => $cfcf7_default_options[$slug],
    );

    $args = wp_parse_args( $args, $defaults );
    extract($args);

    $label; $description; $default;

    ?>
    <div class="option-line">
        <span class="label"><?php echo $label ?></span>
        <span class="field">
			
			<input type="checkbox" data-default-value="<?php echo $default ?>" name="<?php echo CFCF7_OPTIONS.'['.$slug.']' ?>" value="1" <?php checked('1', $cfcf7_options[$slug]) ?>>
		</span>
        <span class="description"><?php echo $description ?><?php if (!empty($default)) echo ' ('.__( 'Default: ', 'cfcf7' ).$default.')' ?></span>
    </div>
    <?php
}

function cfcf7_regex_collection() {
    global $cfcf7_options, $cfcf7_default_options;

}

add_action('admin_init', 'cfcf7_admin_init');
function cfcf7_admin_init(){
    register_setting( CFCF7_OPTIONS, CFCF7_OPTIONS, 'cfcf7_options_sanitize' );
}

function cfcf7_options_sanitize($input) {
    return $input;
}

add_action( 'wp_ajax_cfcf7_dismiss_notice', 'cfcf7_dismiss_notice' );
function cfcf7_dismiss_notice() {
    global $cfcf7_options;
    $cfcf7_options['notice_dismissed'] = true;
    $cfcf7_options['cfcf7_settings_saved'] = 1;
    update_option(CFCF7_OPTIONS,$cfcf7_options);
}