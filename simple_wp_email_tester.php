<?php
/*
Plugin Name: Simple WP Email Tester
Description: With Simple WP Test Email Plugin you can test if an email is sending in your WordPress setup.
Version: 0.1.1
Author: SeQuere Technologies
Text Domain: simple_wp_email_tester
Author URI: https://sequere.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Register the submenu page
function register_seq_simple_wp_email_tester_page() {
    add_submenu_page(
        'tools.php',
        __( 'Simple WP Email Tester', 'simple-wp-email-tester' ),
        __( 'Simple WP Email Tester', 'simple-wp-email-tester' ),
        'manage_options',
        'simple_wp_email_tester',
        'seq_simple_wp_email_tester'
    );
}
add_action( 'admin_menu', 'register_seq_simple_wp_email_tester_page' );

// Display the submenu page
function seq_simple_wp_email_tester() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Simple WP Email Tester', 'simple-wp-email-tester' ); ?></h1>
        <form id="seq_wp_email_tester_form" method="post" enctype="multipart/form-data"> 
            <?php
            if ( isset( $_POST['seq_wp_mail_to'] ) ) {
                if ( isset( $_POST['seq_wp_email_tester_nonce_field'] ) && wp_verify_nonce( $_POST['seq_wp_email_tester_nonce_field'], 'seq_wp_email_tester_nonce_action' ) ) {
                    $seq_wp_mail_to = sanitize_email( $_POST['seq_wp_mail_to'] );
                    if ( ! is_email( $seq_wp_mail_to ) ) {
                        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Invalid email address.', 'simple-wp-email-tester' ) . '</p></div>';
                    } else {
                        $seq_wp_mail_subject = sanitize_text_field( $_POST['seq_wp_mail_subject'] );
                        $seq_wp_email_format = isset( $_POST['seq_wp_email_format'] ) ? sanitize_text_field( $_POST['seq_wp_email_format'] ) : 'html';

                        $seq_simple_wp_email_body = __( 'This is the test mail from ', 'simple-wp-email-tester' ) . get_bloginfo( 'name' );

                        $seq_simple_wp_email_tester_attachment = '';
                        if ( ! empty( $_FILES['seq_wp_mail_attachment']['name'] ) ) {
                            $uploaded_file = $_FILES['seq_wp_mail_attachment'];
                            $upload_overrides = array( 'test_form' => false );
                            $movefile = wp_handle_upload( $uploaded_file, $upload_overrides );
                            if ( $movefile && ! isset( $movefile['error'] ) ) {
                                $seq_simple_wp_email_tester_attachment = $movefile['file'];
                            } else {
                                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Error uploading file: ', 'simple-wp-email-tester' ) . esc_html( $movefile['error'] ) . '</p></div>';
                            }
                        }

                        if ( 'html' === $seq_wp_email_format ) {
                            $seq_simple_wp_email_tester_headers = array( 'Content-Type: text/html; charset=UTF-8' );
                            $seq_simple_wp_email_body = '<html><head></head><title>' . esc_html__( 'Simple WP Test Email', 'simple-wp-email-tester' ) . '</title><body><div>' . esc_html( $seq_simple_wp_email_body ) . '</div></body></html>';
                        } else {
                            $seq_simple_wp_email_tester_headers = array( 'Content-Type: text/plain; charset=utf-8' );
                        }

                        $seq_simple_wp_email_tester_headers[] = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>';

                        $seq_simple_wp_email_tester = wp_mail( $seq_wp_mail_to, $seq_wp_mail_subject, $seq_simple_wp_email_body, $seq_simple_wp_email_tester_headers, $seq_simple_wp_email_tester_attachment );

                        if ( $seq_simple_wp_email_tester ) {
                            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Test Email has been sent. This message does not confirm that you have received the email. Please check your inbox/spam.', 'simple-wp-email-tester' ) . '</p></div>';
                        } else {
                            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Test Email has not been sent. There must have been some problem on WordPress. Please contact your Server Administrator', 'simple-wp-email-tester' ) . '</p></div>';
                        }
                    }
                }
            }
            ?>
            <div class="form-group">
                <label for="seq_wp_mail_to"><?php esc_html_e( 'Send Email to:', 'simple-wp-email-tester' ); ?></label>
                <input type="email" id="seq_wp_mail_to" name="seq_wp_mail_to" value=""/>
                <p id="seq_wp_mail_to_error" class="error" style="color: red;"></p>
            </div>
            <div class="form-group">
                <label for="seq_wp_mail_subject"><?php esc_html_e( 'Subject', 'simple-wp-email-tester' ); ?></label>
                <input type="text" id="seq_wp_mail_subject" name="seq_wp_mail_subject" value="<?php esc_attr_e( 'Simple WP email tester', 'simple-wp-email-tester' ); ?>"/>
            </div>
            <div class="form-group">
                <label for="seq_wp_email_format"><?php esc_html_e( 'Email Format', 'simple-wp-email-tester' ); ?></label>
                <select id="seq_wp_email_format" name="seq_wp_email_format">
                    <option value="html"><?php esc_html_e( 'HTML Email', 'simple-wp-email-tester' ); ?></option>
                    <option value="plain"><?php esc_html_e( 'Plain Email', 'simple-wp-email-tester' ); ?></option>
                </select>
            </div>
            <div class="form-group">
                <label for="seq_wp_mail_attachment"><?php esc_html_e( 'Attach a file (optional)', 'simple-wp-email-tester' ); ?></label>
                <input type="file" id="seq_wp_mail_attachment" name="seq_wp_mail_attachment"/>
            </div>
            <?php wp_nonce_field( 'seq_wp_email_tester_nonce_action', 'seq_wp_email_tester_nonce_field' ); ?>
            <?php submit_button( __( 'Send Test Email', 'simple-wp-email-tester' ) ); ?>
        </form>
    </div>
    <script>
    document.getElementById('seq_wp_email_tester_form').addEventListener('submit', function(event) {
        var emailField = document.getElementById('seq_wp_mail_to');
        var emailError = document.getElementById('seq_wp_mail_to_error');
        var email = emailField.value;
        var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

        emailError.textContent = ''; // Clear previous error message

        if (!emailPattern.test(email)) {
            event.preventDefault();
            emailError.textContent = '<?php esc_html_e('Please enter a valid email address.', 'simple-wp-email-tester'); ?>';
            emailField.focus();
        }
    });
    </script>
    <style>
    .form-group {
        margin: 15px 0;
    }
    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .form-group input[type="email"],
    .form-group input[type="text"],
    .form-group select,
    .form-group input[type="file"] {
        width: 100%;
        max-width: 400px;
        padding: 5px;
    }
    </style>
    <?php 
}
?>
