<div class="control-box">
    <fieldset>
        <legend><?php echo sprintf( esc_html( $description ) ); ?></legend>

        <table class="form-table">
            <tbody>

            <tr>
                <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name Tag', 'cfcf7' ) ); ?></label></th>
                <td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
            </tr>

<!--
			<tr>
                <th colspan="2"><b><?php //echo esc_html( __( 'For style display:', 'cfcf7' ) ); ?></b></th>
            </tr>
			
            <tr>
                <td scope="row"><label for="display-block"><b><?php //echo esc_html( __( 'Display: block', 'cfcf7' ) ); ?></b></label></td>
                <td><input type="checkbox" name="display-block" class="option" id="display-block" /></td>
            </tr>

            <tr>
                <td scope="row"><label for="display-inline"><b><?php //echo esc_html( __( 'Display: inline', 'cfcf7' ) ); ?></b></label></td>
                <td><input type="checkbox" name="display-inline" class="option" id="display-inline" /></td>
            </tr>
			
			<tr>
                <td colspan="2"><span style="color:red"><?php //echo __( '<b>WARNING:</b> experimental features!', 'cfcf7' ); ?></span></td>
            </tr>
-->

            </tbody>
        </table>
    </fieldset>
</div>

<div class="insert-box">
    <input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

    <div class="submitbox">
        <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'cfcf7' ) ); ?>" />
    </div>

    <br class="clear" />
</div>