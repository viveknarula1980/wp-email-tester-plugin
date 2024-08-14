<?php
/*
Plugin Name: Simple WP Email Tester
Description: Test email sending in your WordPress setup.
Version: 0.1.1
Author: SeQuere Technologies
Text Domain: simple_wp_email_tester
Author URI: https://sequere.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Register the submenu page
function simple_wp_email_tester_register_page() {
    add_submenu_page(
        'tools.php',
        __( 'Simple WP Email Tester', 'simple-wp-email-tester' ),
        __( 'Simple WP Email Tester', 'simple-wp-email-tester' ),
        'manage_options',
        'simple_wp_email_tester',
        'simple_wp_email_tester_page'
    );
}
add_action( 'admin_menu', 'simple_wp_email_tester_register_page' );

// Display the submenu page
function simple_wp_email_tester_page() {
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['simple_wp_email_tester_nonce'] ) && wp_verify_nonce( $_POST['simple_wp_email_tester_nonce'], 'simple_wp_email_tester_action' ) ) {
        $email_to = sanitize_email( $_POST['email_to'] );
        $email_subject = sanitize_text_field( $_POST['email_subject'] );
        $email_format = sanitize_text_field( $_POST['email_format'] );
        $attachments = '';

        if ( ! is_email( $email_to ) ) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Invalid email address.', 'simple-wp-email-tester' ) . '</p></div>';
        } else {
            if ( ! empty( $_FILES['email_attachment']['name'] ) ) {
                $uploaded_file = wp_handle_upload( $_FILES['email_attachment'], array( 'test_form' => false ) );
                if ( ! isset( $uploaded_file['error'] ) ) {
                    $attachments = $uploaded_file['file'];
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Error uploading file: ', 'simple-wp-email-tester' ) . esc_html( $uploaded_file['error'] ) . '</p></div>';
                }
            }

            $headers = array( 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>' );
            if ( 'html' === $email_format ) {
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                $email_body = sprintf(
                    '<html><head></head><body><p>%s</p></body></html>',
                    esc_html__( 'This is a test email from ', 'simple-wp-email-tester' ) . get_bloginfo( 'name' )
                );
            } else {
                $headers[] = 'Content-Type: text/plain; charset=utf-8';
                $email_body = esc_html__( 'This is a test email from ', 'simple-wp-email-tester' ) . get_bloginfo( 'name' );
            }

            $sent = wp_mail( $email_to, $email_subject, $email_body, $headers, $attachments );
            $message = $sent ? 'Test Email has been sent. Please check your inbox/spam.' : 'Test Email has not been sent. Please contact your Server Administrator.';
            $notice_class = $sent ? 'success' : 'error';
            echo "<div class='notice notice-{$notice_class} is-dismissible'><p>" . esc_html__( $message, 'simple-wp-email-tester' ) . '</p></div>';
        }
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'Simple WP Email Tester', 'simple-wp-email-tester' ) . '</h1>';
    echo '<form id="simple_wp_email_tester_form" method="post" enctype="multipart/form-data">';
    echo '<div class="form-group"><label for="email_to">' . esc_html__( 'Send Email to:', 'simple-wp-email-tester' ) . '</label><input type="email" id="email_to" name="email_to"/></div>';
    echo '<div class="form-group"><label for="email_subject">' . esc_html__( 'Subject', 'simple-wp-email-tester' ) . '</label><input type="text" id="email_subject" name="email_subject" value="' . esc_attr__( 'Simple WP email tester', 'simple-wp-email-tester' ) . '"/></div>';
    echo '<div class="form-group"><label for="email_format">' . esc_html__( 'Email Format', 'simple-wp-email-tester' ) . '</label><select id="email_format" name="email_format"><option value="html">' . esc_html__( 'HTML Email', 'simple-wp-email-tester' ) . '</option><option value="plain">' . esc_html__( 'Plain Email', 'simple-wp-email-tester' ) . '</option></select></div>';
    echo '<div class="form-group"><label for="email_attachment">' . esc_html__( 'Attach a file (optional)', 'simple-wp-email-tester' ) . '</label><input type="file" id="email_attachment" name="email_attachment"/></div>';
    wp_nonce_field( 'simple_wp_email_tester_action', 'simple_wp_email_tester_nonce' );
    submit_button( __( 'Send Test Email', 'simple-wp-email-tester' ) );
    echo '</form>';
    echo '</div>';

    echo '<script>
    document.getElementById("simple_wp_email_tester_form").addEventListener("submit", function(event) {
        var emailField = document.getElementById("email_to");
        var emailError = emailField.nextElementSibling;
        var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

        emailError && emailError.remove(); // Remove any existing error messages

        if (!emailPattern.test(emailField.value)) {
            event.preventDefault();
            var errorMessage = document.createElement("p");
            errorMessage.className = "error";
            errorMessage.style.color = "red";
            errorMessage.textContent = "' . esc_js( 'Please enter a valid email address.', 'simple-wp-email-tester' ) . '";
            emailField.insertAdjacentElement("afterend", errorMessage);
            emailField.focus();
        }
    });
    </script>';

    echo '<style>
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
        padding: 6px;
    }
    </style>';
}
?>
