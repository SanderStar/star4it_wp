<?php

/* -------------------------------------------------------
   Custom Admin Pagina's
------------------------------------------------------- */

function rr_admin_menu_pages() {
    add_menu_page('Ruimte Reservering', 'Ruimte Reservering', 'manage_options', 'rr_dashboard', 'rr_dashboard_page', 'dashicons-calendar-alt', 6);
    add_submenu_page('rr_dashboard', 'Ruimtes', 'Ruimtes', 'manage_options', 'rr_ruimtes', 'rr_admin_ruimtes_page');
    add_submenu_page('rr_dashboard', 'Personen', 'Personen', 'manage_options', 'rr_personen', 'rr_admin_personen_page');
    add_submenu_page('rr_dashboard', 'Reserveringen', 'Reserveringen', 'manage_options', 'rr_reserveringen', 'rr_admin_reserveringen_page');
}
add_action('admin_menu', 'rr_admin_menu_pages');

function rr_dashboard_page() {
    echo '<div class="wrap"><h1>Ruimte Reservering</h1>';
    echo '<p>Welkom bij het Ruimte Reservering beheer. Gebruik het menu om ruimtes, personen en reserveringen te beheren.</p>';
    echo '<p>iCal voor ruimte reservering: https://[domein]/ruimte-reservering.ics</p>';
    echo '</div>';
}

// --- RUIMTES ---
function rr_admin_ruimtes_page() {
    echo '<div class="wrap"><h1>Ruimtes</h1>';
    echo '<a href="admin.php?page=rr_ruimtes&action=add" class="button button-primary">Nieuwe ruimte</a><br><br>';
    if (isset($_GET['action']) && $_GET['action'] === 'add') {
        rr_admin_ruimtes_form();
        echo '</div>';
        return;
    }
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        rr_admin_ruimtes_form(intval($_GET['id']));
        echo '</div>';
        return;
    }
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        wp_delete_post(intval($_GET['id']), true);
        echo '<div class="updated notice"><p>Ruimte verwijderd.</p></div>';
    }
    $ruimtes = get_posts(['post_type'=>'ruimte','numberposts'=>-1]);
    echo '<table class="widefat"><thead><tr><th>Naam</th><th>Acties</th></tr></thead><tbody>';
    foreach ($ruimtes as $r) {
        echo '<tr><td>' . esc_html($r->post_title) . '</td>';
        echo '<td><a href="admin.php?page=rr_ruimtes&action=edit&id=' . $r->ID . '">Bewerken</a> | <a href="admin.php?page=rr_ruimtes&action=delete&id=' . $r->ID . '" onclick="return confirm(\'Weet je het zeker?\')">Verwijderen</a></td></tr>';
    }
    echo '</tbody></table></div>';
}

function rr_admin_ruimtes_form($id = 0) {
    $naam = '';
    if ($id) {
        $post = get_post($id);
        $naam = $post->post_title;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rr_naam'])) {
        $naam = sanitize_text_field($_POST['rr_naam']);
        if ($id) {
            wp_update_post(['ID'=>$id,'post_title'=>$naam]);
            echo '<div class="updated notice"><p>Ruimte bijgewerkt.</p></div>';
        } else {
            wp_insert_post(['post_type'=>'ruimte','post_title'=>$naam,'post_status'=>'publish']);
            echo '<div class="updated notice"><p>Ruimte toegevoegd.</p></div>';
        }
    }
    echo '<form method="post">';
    echo '<p><label>Naam:<br><input type="text" name="rr_naam" value="' . esc_attr($naam) . '" required></label></p>';
    echo '<p><button type="submit" class="button button-primary">Opslaan</button></p>';
    echo '</form>';
}

// --- PERSONEN ---
function rr_admin_personen_page() {
    echo '<div class="wrap"><h1>Personen</h1>';
    echo '<a href="admin.php?page=rr_personen&action=add" class="button button-primary">Nieuwe persoon</a><br><br>';
    if (isset($_GET['action']) && $_GET['action'] === 'add') {
        rr_admin_personen_form();
        echo '</div>';
        return;
    }
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        rr_admin_personen_form(intval($_GET['id']));
        echo '</div>';
        return;
    }
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        wp_delete_post(intval($_GET['id']), true);
        echo '<div class="updated notice"><p>Persoon verwijderd.</p></div>';
    }
    $personen = get_posts(['post_type'=>'persoon','numberposts'=>-1]);
    echo '<table class="widefat"><thead><tr><th>Naam</th><th>Telefoon</th><th>Acties</th></tr></thead><tbody>';
    foreach ($personen as $p) {
        $tel = get_post_meta($p->ID, 'telefoon', true);
        echo '<tr><td>' . esc_html($p->post_title) . '</td><td>' . esc_html($tel) . '</td>';
        echo '<td><a href="admin.php?page=rr_personen&action=edit&id=' . $p->ID . '">Bewerken</a> | <a href="admin.php?page=rr_personen&action=delete&id=' . $p->ID . '" onclick="return confirm(\'Weet je het zeker?\')">Verwijderen</a></td></tr>';
    }
    echo '</tbody></table></div>';
}

function rr_admin_personen_form($id = 0) {
    $naam = '';
    $tel = '';
    if ($id) {
        $post = get_post($id);
        $naam = $post->post_title;
        $tel = get_post_meta($id, 'telefoon', true);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rr_naam'])) {
        $naam = sanitize_text_field($_POST['rr_naam']);
        $tel = sanitize_text_field($_POST['rr_telefoon']);
        if ($id) {
            wp_update_post(['ID'=>$id,'post_title'=>$naam]);
            update_post_meta($id, 'telefoon', $tel);
            echo '<div class="updated notice"><p>Persoon bijgewerkt.</p></div>';
        } else {
            $pid = wp_insert_post(['post_type'=>'persoon','post_title'=>$naam,'post_status'=>'publish']);
            update_post_meta($pid, 'telefoon', $tel);
            echo '<div class="updated notice"><p>Persoon toegevoegd.</p></div>';
        }
    }
    echo '<form method="post">';
    echo '<p><label>Naam:<br><input type="text" name="rr_naam" value="' . esc_attr($naam) . '" required></label></p>';
    echo '<p><label>Telefoonnummer:<br><input type="text" name="rr_telefoon" value="' . esc_attr($tel) . '"></label></p>';
    echo '<p><button type="submit" class="button button-primary">Opslaan</button></p>';
    echo '</form>';
}

// --- RESERVERINGEN ---
function rr_admin_reserveringen_page() {
    echo '<div class="wrap"><h1>Reserveringen</h1>';
    echo '<a href="admin.php?page=rr_reserveringen&action=add" class="button button-primary">Nieuwe reservering</a><br><br>';
    if (isset($_GET['action']) && $_GET['action'] === 'add') {
        rr_admin_reserveringen_form();
        echo '</div>';
        return;
    }
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        rr_admin_reserveringen_form(intval($_GET['id']));
        echo '</div>';
        return;
    }
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        wp_delete_post(intval($_GET['id']), true);
        echo '<div class="updated notice"><p>Reservering verwijderd.</p></div>';
    }
    $reserveringen = get_posts(['post_type'=>'reservering','numberposts'=>-1]);
    echo '<table class="widefat"><thead><tr><th>Ruimte</th><th>Persoon</th><th>Start</th><th>Eind</th><th>Acties</th></tr></thead><tbody>';
    foreach ($reserveringen as $r) {
        $ruimte = get_post_meta($r->ID, 'ruimte_id', true);
        $persoon = get_post_meta($r->ID, 'persoon_id', true);
        $start = get_post_meta($r->ID, 'start_dt', true);
        $eind = get_post_meta($r->ID, 'eind_dt', true);
        echo '<tr><td>' . esc_html(get_the_title($ruimte)) . '</td><td>' . esc_html(get_the_title($persoon)) . '</td><td>' . esc_html($start) . '</td><td>' . esc_html($eind) . '</td>';
        echo '<td><a href="admin.php?page=rr_reserveringen&action=edit&id=' . $r->ID . '">Bewerken</a> | <a href="admin.php?page=rr_reserveringen&action=delete&id=' . $r->ID . '" onclick="return confirm(\'Weet je het zeker?\')">Verwijderen</a></td></tr>';
    }
    echo '</tbody></table></div>';
}

function rr_admin_reserveringen_form($id = 0) {
    $ruimte_id = '';
    $persoon_id = '';
    $start = '';
    $eind = '';
    if ($id) {
        $ruimte_id = get_post_meta($id, 'ruimte_id', true);
        $persoon_id = get_post_meta($id, 'persoon_id', true);
        $start = get_post_meta($id, 'start_dt', true);
        $eind = get_post_meta($id, 'eind_dt', true);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rr_ruimte_id'])) {
        $ruimte_id = intval($_POST['rr_ruimte_id']);
        $persoon_id = intval($_POST['rr_persoon_id']);
        $start = sanitize_text_field($_POST['rr_start']);
        $eind = sanitize_text_field($_POST['rr_eind']);
        if ($id) {
            update_post_meta($id, 'ruimte_id', $ruimte_id);
            update_post_meta($id, 'persoon_id', $persoon_id);
            update_post_meta($id, 'start_dt', $start);
            update_post_meta($id, 'eind_dt', $eind);
            echo '<div class="updated notice"><p>Reservering bijgewerkt.</p></div>';
        } else {
            $rid = wp_insert_post(['post_type'=>'reservering','post_title'=>'Reservering','post_status'=>'publish']);
            update_post_meta($rid, 'ruimte_id', $ruimte_id);
            update_post_meta($rid, 'persoon_id', $persoon_id);
            update_post_meta($rid, 'start_dt', $start);
            update_post_meta($rid, 'eind_dt', $eind);
            echo '<div class="updated notice"><p>Reservering toegevoegd.</p></div>';
        }
    }
    $ruimtes = get_posts(['post_type'=>'ruimte','numberposts'=>-1]);
    $personen = get_posts(['post_type'=>'persoon','numberposts'=>-1]);
    echo '<form method="post">';
    echo '<p><label>Ruimte:<br><select name="rr_ruimte_id" required>';
    foreach ($ruimtes as $r) {
        $sel = $ruimte_id == $r->ID ? 'selected' : '';
        echo '<option value="' . $r->ID . '" ' . $sel . '>' . esc_html($r->post_title) . '</option>';
    }
    echo '</select></label></p>';
    echo '<p><label>Persoon:<br><select name="rr_persoon_id" required>';
    foreach ($personen as $p) {
        $sel = $persoon_id == $p->ID ? 'selected' : '';
        echo '<option value="' . $p->ID . '" ' . $sel . '>' . esc_html($p->post_title) . '</option>';
    }
    echo '</select></label></p>';
    echo '<p><label>Start datum/tijd:<br><input type="datetime-local" name="rr_start" value="' . esc_attr($start) . '" required></label></p>';
    echo '<p><label>Eind datum/tijd:<br><input type="datetime-local" name="rr_eind" value="' . esc_attr($eind) . '" required></label></p>';
    echo '<p><button type="submit" class="button button-primary">Opslaan</button></p>';
    echo '</form>';
}

// --- iCal output (verplaatst uit hoofdpluginbestand) ---
function rr_add_ical_rewrite() {
    add_rewrite_rule('ruimte-reservering\.ics$', 'index.php?rr_ical=1', 'top');
}
add_action('init', 'rr_add_ical_rewrite');

function rr_add_ical_query_var($vars) {
    $vars[] = 'rr_ical';
    return $vars;
}
add_filter('query_vars', 'rr_add_ical_query_var');

function rr_ical_template() {
    if (intval(get_query_var('rr_ical')) !== 1) return;
    if (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename="ruimte-reservering.ics"');
    echo "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//Ruimte Reservering//NL\n";
    $reserveringen = get_posts(['post_type' => 'reservering', 'numberposts' => -1]);
    foreach ($reserveringen as $r) {
        $ruimte = get_post_meta($r->ID, 'ruimte_id', true);
        $persoon = get_post_meta($r->ID, 'persoon_id', true);
        $start = get_post_meta($r->ID, 'start_dt', true);
        $eind = get_post_meta($r->ID, 'eind_dt', true);
        echo "BEGIN:VEVENT\n";
        echo "UID:rr-" . $r->ID . "@example.com\n";
        echo "DTSTART:" . gmdate('Ymd\THis\Z', strtotime($start)) . "\n";
        echo "DTEND:" . gmdate('Ymd\THis\Z', strtotime($eind)) . "\n";
        echo "SUMMARY:" . addslashes(get_the_title($ruimte)) . "\n";
        echo "DESCRIPTION:Verantwoordelijke: " . addslashes(get_the_title($persoon)) . "\n";
        echo "END:VEVENT\n";
    }
    echo "END:VCALENDAR";
    exit;
}
add_action('template_redirect', 'rr_ical_template');

