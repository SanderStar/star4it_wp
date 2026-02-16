<?php
/*
Plugin Name: Kerk Event Importer
Description: Import and create Event Organiser events from JSON data.
Version: 2.4
Author: Sander Star
*/

if (!defined('ABSPATH')) exit;

class Kerk_Event_Importer {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_kerk_process_events', [$this, 'process_events']);
    }

    public function add_admin_menu() {
        add_menu_page('Kerk Event Importer', 'Kerk Event Import', 'manage_options', 'kerk-event-import', [$this, 'admin_page']);
    }

    public function enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_kerk-event-import') return;
        wp_enqueue_script('kerk-event-js', plugin_dir_url(__FILE__).'kerk_event.js', ['jquery'], null, true);
        wp_localize_script('kerk-event-js', 'kerkEventAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kerk_event_nonce')
        ]);
    }

    public function admin_page() {
        ?>
        <style>
        .kerk-steps { max-width: 700px; margin: 0 auto; font-family: Arial, sans-serif; }
        .kerk-step { background: #f9f9f9; border-radius: 8px; margin-bottom: 24px; padding: 24px 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .kerk-step-title { font-size: 1.2em; font-weight: bold; margin-bottom: 12px; color: #2c3e50; }
        .kerk-step-actions { margin-top: 8px; }
        .kerk-step textarea { width: 100%; font-family: monospace; }
        .kerk-step button { background: #2980b9; color: #fff; border: none; border-radius: 4px; padding: 8px 18px; font-size: 1em; cursor: pointer; margin-top: 8px; }
        .kerk-step button:hover { background: #3498db; }
        .kerk-step .kerk-step-desc { color: #555; margin-bottom: 8px; }
        </style>
        <div class="wrap kerk-steps">
            <h1>Kerk Event Importer</h1>
            <div class="kerk-step">
                <div class="kerk-step-title">Convert Text to JSON</div>
                <div class="kerk-step-desc">Paste or enter event text below. Optionally, enter an AI agent question to guide the conversion, then click 'Convert to JSON' to generate a JSON array for import.</div>
                <div class="kerk-step-actions" style="display: flex; gap: 12px; align-items: flex-start;">
                    <textarea id="kerk_text_input" rows="6" placeholder="Paste event text here..." style="flex:2;"></textarea>
                    <input type="text" id="kerk_extra_input" placeholder="AI agent question (optional)" style="flex:1; min-width:220px;" />
                </div>
                <div class="kerk-step-actions">
                    <button id="kerk_convert_json">Convert to JSON</button>
                </div>
            </div>
            <div class="kerk-step">
                <div class="kerk-step-title">Process Events</div>
                <div class="kerk-step-desc">Paste or enter the event data (JSON format) below and click 'Process Events' to create WordPress events.</div>
                <div class="kerk-step-actions">
                    <textarea id="kerk_event_textbox" rows="10" placeholder="Paste event data here..."></textarea>
                </div>
                <div class="kerk-step-actions">
                    <button id="kerk_event_process">Process Events</button>
                </div>
                <div id="kerk_event_result" style="margin-top:16px;"></div>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const processBtn = document.getElementById('kerk_event_process');
            const textbox = document.getElementById('kerk_event_textbox');
            const resultDiv = document.getElementById('kerk_event_result');
            const convertBtn = document.getElementById('kerk_convert_json');
            const textInput = document.getElementById('kerk_text_input');
            const extraInput = document.getElementById('kerk_extra_input');

            if (convertBtn && textInput && textbox) {
                convertBtn.addEventListener('click', function() {
                    // Simple example: split lines, wrap each as an event with title, add extra info if present
                    const lines = textInput.value.split('\n').map(l => l.trim()).filter(l => l);
                    const extra = extraInput ? extraInput.value.trim() : '';
                    const events = lines.map(line => {
                        let event = { title: line };
                        if (extra) event.ai_question = extra;
                        return event;
                    });
                    textbox.value = JSON.stringify(events, null, 2);
                });
            }

            if (processBtn && textbox) {
                processBtn.addEventListener('click', function() {
                    const data = textbox.value;
                    resultDiv.textContent = 'Processing...';
                    fetch(ajaxurl, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: new URLSearchParams({
                            action: 'kerk_process_events',
                            nonce: '<?php echo wp_create_nonce('kerk_event_nonce'); ?>',
                            data: data
                        })
                    })
                    .then(r => r.json())
                    .then(resp => {
                        if (resp.success) {
                            resultDiv.textContent = resp.data;
                        } else {
                            resultDiv.textContent = 'Error: ' + (resp.data || resp.message);
                        }
                    })
                    .catch(e => {
                        resultDiv.textContent = 'Error: ' + e;
                    });
                });
            }
        });
        </script>
        <?php
    }



    public function process_events() {
        check_ajax_referer('kerk_event_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        $json = stripslashes($_POST['data'] ?? '');
        $events = json_decode($json, true);
        if (!$events || !is_array($events)) wp_send_json_error('Invalid JSON');
        $created = 0;
        foreach ($events as $event) {
            $venue = get_term_by('name', 'De Lichtbron Valkenburg', 'event-venue');
            $cat = get_term_by('slug', 'kerkdiensten', 'event-category');
            $args = [
                'post_title' => $event['title'] ?? 'Kerkdienst',
                'post_content' => $event['description'] ?? '',
                'start' => new DateTime($event['start']),
                'end' => new DateTime($event['end']),
                'venue' => $venue->slug ?? '',
                'post_status' => 'publish',
                'schedule' => 'once'
            ];
            if (function_exists('eo_insert_event')) {
                $event_id = eo_insert_event($args);
                if ($event_id && !is_wp_error($event_id)) {
                    if ($cat) {
                        wp_set_object_terms($event_id, [$cat->slug], 'event-category', false);
                    }
                    $created++;
                }
            }
        }
        wp_send_json_success("Created $created events.");
    }
}

new Kerk_Event_Importer();
