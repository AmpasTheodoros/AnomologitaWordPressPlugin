<?php
/*
Plugin Name: Anomologita
Description: A plugin to integrate AI features into WordPress.
Version: 1.0
Author: Your Name
*/

function ai_plugin_shortcode() {
  return "Welcome to AI-powered WordPress!";
}
add_shortcode('ai_plugin', 'ai_plugin_shortcode');

function get_ai_response($prompt) {
  $api_key = 'your-api-key-here';
  $response = wp_remote_post('https://api.openai.com/v1/engines/davinci/completions', array(
      'headers' => array(
          'Authorization' => 'Bearer ' . $api_key,
          'Content-Type' => 'application/json',
      ),
      'body' => json_encode(array(
          'prompt' => $prompt,
          'max_tokens' => 150,
      )),
  ));

  if (is_wp_error($response)) {
      error_log('Error in API request: ' . $response->get_error_message());
      return 'Error in API request: ' . $response->get_error_message();
  }

  $body = wp_remote_retrieve_body($response);
  $data = json_decode($body, true);

  if (!isset($data['choices'])) {
      error_log('Unexpected API response: ' . $body);
      return 'Error: Unexpected API response.';
  }

  return $data['choices'][0]['text'];
}

function ai_form_shortcode() {
  $form_html = '
  <form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">
      <label for="ai_query">Ask me anything:</label>
      <input type="text" id="ai_query" name="ai_query" value="' . (isset($_POST['ai_query']) ? esc_attr($_POST['ai_query']) : '') . '" />
      <input type="submit" name="ai_submit" value="Ask AI" />
  </form>';

  if (isset($_POST['ai_submit'])) {
      $query = sanitize_text_field($_POST['ai_query']);
      $response = get_ai_response($query);
      $form_html .= '<p>AI Response: ' . esc_html($response) . '</p>';
  }

  return $form_html;
}
add_shortcode('ai_form', 'ai_form_shortcode');


function ai_shortcode($atts) {
  $atts = shortcode_atts(array(
      'prompt' => 'Hello world',
  ), $atts);

  return get_ai_response($atts['prompt']);
}
add_shortcode('ai_response', 'ai_shortcode');
