<?php
/*
Plugin Name: Anomologita
Description: A plugin to integrate AI features into WordPress.
Version: 1.0
Author: Your Name
*/

function custom_message_form() {
  // // Check if user is logged in
  // if (!is_user_logged_in()) {
  //     return "You must be logged in to submit a message.";
  // }

  // Form HTML
  $form = '
  <form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">
      <textarea name="custom_message_text" required></textarea>
      <input type="submit" name="custom_message_submit" value="Submit Message">
  </form>';

  return $form;
}
add_shortcode('submit_message', 'custom_message_form');

function handle_message_submission() {
  global $wpdb; // Global WordPress database access

  if (isset($_POST['custom_message_submit']) && !empty($_POST['custom_message_text'])) {
      $text = sanitize_textarea_field($_POST['custom_message_text']);
      $table_name = $wpdb->prefix . 'messages';

      // Insert data into the database
      $wpdb->insert(
          $table_name,
          array(
              'text' => $text,
              'authorized' => false,  // Default to false, change based on your authorization logic
              'likes' => 0,           // Default to 0
              'ticketNumber' => null, // Assuming no ticket number at submission
          ),
          array(
              '%s',    // placeholder for 'text' (string)
              '%d',    // placeholder for 'authorized' (boolean/int)
              '%d',    // placeholder for 'likes' (int)
              '%d'     // placeholder for 'ticketNumber' (int)
          )
      );

      if ($wpdb->insert_id > 0) {
          echo '<div>Message submitted successfully!</div>';
      } else {
          echo '<div>Error in message submission.</div>';
      }
  }
}
add_action('init', 'handle_message_submission');

function display_authorized_messages() {
  global $wpdb; // Access to the WordPress database class
  $table_name = $wpdb->prefix . 'messages';
  
  // Query to retrieve messages where 'authorized' is true, sorted by 'time' descending
  $query = "SELECT * FROM $table_name WHERE authorized = 0 ORDER BY time DESC";
  $messages = $wpdb->get_results($query);
  
  // Initialize an output variable
  $output = '<div class="custom-messages-list">';
  
  if ($messages) {
      foreach ($messages as $message) {
          $output .= '<div class="message">';
          $output .= '<p>' . esc_textarea($message->text) . '</p>';
          $output .= '<p>Likes: ' . intval($message->likes) . '</p>';
          $output .= '<p>Submitted on: ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($message->time)) . '</p>';
          $output .= '</div>';
      }
  } else {
      $output .= '<p>No messages found.</p>';
  }
  
  $output .= '</div>';
  
  return $output;
}
add_shortcode('display_messages', 'display_authorized_messages');



