<?php
    if( ! current_user_can( 'manage_options' ) ) {
        exit();
    }

    $saved = '';

    if( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'] ) ) {
        if (isset($_POST['block_users'])) {
            update_option("dabaracc_users", true);
        } else {
            update_option("dabaracc_users", false);
        }

        if (isset($_POST['block_comments'])) {
            update_option("dabaracc_comments", true);
        } else {
            update_option("dabaracc_comments", false);
        }

        $saved = "<p><strong>All settings saved!</strong></p>";
    }
?>
<div class="wrap">
    <h2>Block User Registration & Comments</h2>
    <?php echo $saved; ?>
    <form action="" method="POST">
        <p>
            <?php wp_nonce_field(); ?>
            <input type="checkbox" name="block_users" <?php
            if ( get_option( "dabaracc_users" ) !== false && get_option( "dabaracc_users" ) ) {
                echo 'checked="checked" ';
            }
            ?> /> Block All User Registrations
        </p>
        <p>
            <input type="checkbox" name="block_comments" <?php
            if ( get_option( "dabaracc_comments" ) !== false && get_option( "dabaracc_comments" ) ) {
                echo 'checked="checked" ';
            }
            ?> /> Block All Comments
        </p>
        <p>
            <button type="submit">Save Options</button>
        </p>
    </form>
</div>