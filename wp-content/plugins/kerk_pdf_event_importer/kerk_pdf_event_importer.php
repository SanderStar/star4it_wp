<?php
/*
Plugin Name: Kerk PDF Event Importer
Description: Upload a PDF, extract events, and create Event Organiser events.
Version: 1.10
Author: Sander Star
*/

if (!defined('ABSPATH')) exit;

class Kerk_PDF_Event_Importer {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_kerk_pdf_extract', [$this, 'handle_pdf_upload']);
        add_action('wp_ajax_kerk_process_events', [$this, 'process_events']);
    }

    public function add_admin_menu() {
        add_menu_page('Kerk PDF Event Importer', 'Kerk PDF Import', 'manage_options', 'kerk-pdf-import', [$this, 'admin_page']);
    }

    public function enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_kerk-pdf-import') return;
        wp_enqueue_script('kerk-pdf-js', plugin_dir_url(__FILE__).'kerk_pdf.js', ['jquery'], null, true);
        wp_localize_script('kerk-pdf-js', 'kerkPdfAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kerk_pdf_nonce')
        ]);
    }

    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Kerk PDF Event Importer</h1>
            <input type="file" id="kerk_pdf_file" accept="application/pdf" />
            <button id="kerk_pdf_upload">Upload & Extract</button>
            <br><br>
            <textarea id="kerk_pdf_textbox" rows="15" cols="100"></textarea>
            <br>
            <button id="kerk_pdf_process">Process Events</button>
            <div id="kerk_pdf_result"></div>
        </div>
        <?php
    }

    public function handle_pdf_upload() {
        check_ajax_referer('kerk_pdf_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        if (empty($_FILES['file'])) wp_send_json_error('No file uploaded');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $file = $_FILES['file'];
        $tmp_path = $file['tmp_name'];
        // Use Composer autoloader for Smalot PDF Parser
        if (!class_exists('Smalot\\PdfParser\\Parser')) {
            $autoload = dirname(__FILE__).'/vendor/autoload.php';
            if (file_exists($autoload)) {
                require_once($autoload);
            } else {
                wp_send_json_error('Composer autoloader not found. Run composer install.');
            }
        }
        if (!class_exists('Smalot\\PdfParser\\Parser')) {
            wp_send_json_error('Smalot PDF Parser not found. Run composer require smalot/pdfparser.');
        }
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($tmp_path);
        $text = $pdf->getText();
        $start = strpos($text, 'Kerkdiensten');
        $end = strpos($text, 'Crècherooster', $start);
        if ($start === false || $end === false || $end <= $start) {
            wp_send_json_error('Could not find Kerkdiensten or Crècherooster in PDF');
        }
        // Extract from 'Kerkdiensten' (including the title) up to 'Crècherooster'
        $extracted = trim(substr($text, $start, $end - $start));
        wp_send_json_success($extracted);
    }

    public function process_events() {
        check_ajax_referer('kerk_pdf_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        $json = stripslashes($_POST['data'] ?? '');
        $events = json_decode($json, true);
        if (!$events || !is_array($events)) wp_send_json_error('Invalid JSON');
        $created = 0;
        foreach ($events as $event) {
            $postarr = [
                'post_title' => $event['title'] ?? 'Kerkdienst',
                'post_content' => $event['description'] ?? '',
                'post_status' => 'publish',
                'post_type' => 'event',
            ];
            $event_id = wp_insert_post($postarr);
            if ($event_id && !is_wp_error($event_id)) {
                // Set Event Organiser meta fields
                if (!empty($event['start']) && !empty($event['end'])) {
                    update_post_meta($event_id, '_event_start_date', $event['start']);
                    update_post_meta($event_id, '_event_end_date', $event['end']);
                }
                    // Set event venue to 'De Lichtbron Valkenburg'
                    $venue_name = 'De Lichtbron Valkenburg';
                    if (function_exists('eo_get_venue_by_name') && function_exists('eo_insert_venue')) {
                        $venue = eo_get_venue_by_name($venue_name);
                        if (!$venue) {
                            $venue_id = eo_insert_venue(['name' => $venue_name]);
                        } else {
                            $venue_id = $venue->term_id;
                        }
                        if ($venue_id) {
                            update_post_meta($event_id, '_event_venue_id', $venue_id);
                        }
                    }
                // Assign category 'Kerkdiensten' (creates if not exists)
                $term = term_exists('kerkdiensten', 'event-category');
                if (!$term) {
                    $term = wp_insert_term('kerkdiensten', 'event-category');
                }
                if (!is_wp_error($term)) {
                    $term_id = is_array($term) ? $term['term_id'] : $term;
                    wp_set_object_terms($event_id, intval($term_id), 'event-category');
                }
                $created++;
            }
        }
        wp_send_json_success("Created $created events.");
    }
}

new Kerk_PDF_Event_Importer();
