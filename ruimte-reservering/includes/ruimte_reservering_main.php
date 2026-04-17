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
    $back_url = 'admin.php?page=rr_ruimtes';
    echo '<form method="post">';
    echo '<p><label>Naam:<br><input type="text" name="rr_naam" value="' . esc_attr($naam) . '" required></label></p>';
    echo '<p>';
    echo '<button type="submit" class="button button-primary">Opslaan</button> ';
    echo '<a href="' . $back_url . '" class="button">Annuleren</a>';
    echo '</p>';
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
    $back_url = 'admin.php?page=rr_personen';
    echo '<form method="post">';
    echo '<p><label>Naam:<br><input type="text" name="rr_naam" value="' . esc_attr($naam) . '" required></label></p>';
    echo '<p><label>Telefoonnummer:<br><input type="text" name="rr_telefoon" value="' . esc_attr($tel) . '"></label></p>';
    echo '<p>';
    echo '<button type="submit" class="button button-primary">Opslaan</button> ';
    echo '<a href="' . $back_url . '" class="button">Annuleren</a>';
    echo '</p>';
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
    echo '<table class="widefat"><thead><tr><th>Ruimtes</th><th>Persoon</th><th>Start</th><th>Eind</th><th>Aantal personen</th><th>Goedgekeurd</th><th>Acties</th></tr></thead><tbody>';
    foreach ($reserveringen as $r) {
        $ruimte_ids = get_post_meta($r->ID, 'ruimte_ids', true);
        if (!is_array($ruimte_ids)) { $ruimte_ids = $ruimte_ids ? array($ruimte_ids) : array(); }
        $ruimte_namen = array();
        foreach ($ruimte_ids as $rid) {
            $ruimte_namen[] = get_the_title($rid);
        }
        $persoon = get_post_meta($r->ID, 'persoon_id', true);
        $start = get_post_meta($r->ID, 'start_dt', true);
        $eind = get_post_meta($r->ID, 'eind_dt', true);
        $aantal_personen = get_post_meta($r->ID, 'aantal_personen', true);
        $goedgekeurd = get_post_meta($r->ID, 'goedgekeurd', true);
        $goedgekeurd_label = $goedgekeurd == '1' ? 'Ja' : 'Nee';
        echo '<tr><td>' . implode(', ', $ruimte_namen) . '</td><td>' . esc_html(get_the_title($persoon)) . '</td><td>' . esc_html($start) . '</td><td>' . esc_html($eind) . '</td><td>' . esc_html($aantal_personen) . '</td><td>' . $goedgekeurd_label . '</td>';
        echo '<td><a href="admin.php?page=rr_reserveringen&action=edit&id=' . $r->ID . '">Bewerken</a> | <a href="admin.php?page=rr_reserveringen&action=delete&id=' . $r->ID . '" onclick="return confirm(\'Weet je het zeker?\')">Verwijderen</a></td></tr>';
    }
    echo '</tbody></table></div>';
}

function rr_admin_reserveringen_form($id = 0) {
    $ruimte_ids = array();
    $persoon_id = '';
    $start = '';
    $eind = '';
    $aantal_personen = '';
    $goedgekeurd = '';
    $conflict = false;
    if ($id) {
        $ruimte_ids = get_post_meta($id, 'ruimte_ids', true);
        if (!is_array($ruimte_ids)) { $ruimte_ids = $ruimte_ids ? array($ruimte_ids) : array(); }
        $persoon_id = get_post_meta($id, 'persoon_id', true);
        $start = get_post_meta($id, 'start_dt', true);
        $eind = get_post_meta($id, 'eind_dt', true);
        $aantal_personen = get_post_meta($id, 'aantal_personen', true);
        $goedgekeurd = get_post_meta($id, 'goedgekeurd', true);
    } else {
        $goedgekeurd = '0'; // standaard false bij nieuwe reservering
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rr_ruimte_ids'])) {
        $ruimte_ids = array_map('intval', $_POST['rr_ruimte_ids']);
        $persoon_id = intval($_POST['rr_persoon_id']);
        $start = sanitize_text_field($_POST['rr_start']);
        $eind = sanitize_text_field($_POST['rr_eind']);
        $aantal_personen = intval($_POST['rr_aantal_personen']);
        $goedgekeurd = isset($_POST['rr_goedgekeurd']) ? '1' : '0';

        $fout = '';
        if (empty($ruimte_ids)) {
            $fout .= '<li>Kies minimaal één ruimte.</li>';
        }
        if (empty($persoon_id)) {
            $fout .= '<li>Kies een persoon.</li>';
        }
        if (empty($start)) {
            $fout .= '<li>Vul een start datum/tijd in.</li>';
        }
        if (empty($eind)) {
            $fout .= '<li>Vul een eind datum/tijd in.</li>';
        }
        if (!empty($start) && !empty($eind)) {
            $start_ts = strtotime($start);
            $eind_ts = strtotime($eind);
            if ($start_ts === false || $eind_ts === false) {
                $fout .= '<li>Ongeldige datum/tijd.</li>';
            } elseif ($start_ts >= $eind_ts) {
                $fout .= '<li>De startdatum/tijd moet vóór de einddatum/tijd liggen.</li>';
            }
        }

        if ($fout) {
            echo '<div class="error notice"><ul>' . $fout . '</ul></div>';
        } else {
            $conflict = rr_reservering_heeft_conflict($ruimte_ids, $start, $eind, $id ? $id : null);
            if ($conflict) {
                echo '<div class="error notice"><p>Conflict: De geselecteerde ruimte(s) zijn al gereserveerd in deze periode (' . esc_html(implode(', ', array_map('get_the_title', $conflict->ID ? get_post_meta($conflict->ID, 'ruimte_ids', true) : array()))) . ').</p></div>';
            } else {
                if ($id) {
                    update_post_meta($id, 'ruimte_ids', $ruimte_ids);
                    update_post_meta($id, 'persoon_id', $persoon_id);
                    update_post_meta($id, 'start_dt', $start);
                    update_post_meta($id, 'eind_dt', $eind);
                    update_post_meta($id, 'aantal_personen', $aantal_personen);
                    update_post_meta($id, 'goedgekeurd', $goedgekeurd);
                    echo '<div class="updated notice"><p>Reservering bijgewerkt.</p></div>';
                } else {
                    $rid = wp_insert_post(['post_type'=>'reservering','post_title'=>'Reservering','post_status'=>'publish']);
                    update_post_meta($rid, 'ruimte_ids', $ruimte_ids);
                    update_post_meta($rid, 'persoon_id', $persoon_id);
                    update_post_meta($rid, 'start_dt', $start);
                    update_post_meta($rid, 'eind_dt', $eind);
                    update_post_meta($rid, 'aantal_personen', $aantal_personen);
                    update_post_meta($rid, 'goedgekeurd', $goedgekeurd);
                    echo '<div class="updated notice"><p>Reservering toegevoegd.</p></div>';
                }
            }
        }
    }
    $back_url = 'admin.php?page=rr_reserveringen';
    $ruimtes = get_posts(['post_type'=>'ruimte','numberposts'=>-1]);
    $personen = get_posts(['post_type'=>'persoon','numberposts'=>-1]);
    echo '<form method="post">';
    $veld_style = 'style="width: 320px; max-width: 100%;"';
    echo '<p><label>Ruimtes:<br><select name="rr_ruimte_ids[]" multiple size="4" required ' . $veld_style . '>';
    foreach ($ruimtes as $r) {
        $sel = in_array($r->ID, $ruimte_ids) ? 'selected' : '';
        echo '<option value="' . $r->ID . '" ' . $sel . '>' . esc_html($r->post_title) . '</option>';
    }
    echo '</select><br><small>Houd Ctrl (Windows) of Cmd (Mac) ingedrukt om meerdere ruimtes te selecteren.</small></label></p>';
    echo '<p><label>Persoon:<br><select name="rr_persoon_id" required ' . $veld_style . '>';
    foreach ($personen as $p) {
        $sel = $persoon_id == $p->ID ? 'selected' : '';
        echo '<option value="' . $p->ID . '" ' . $sel . '>' . esc_html($p->post_title) . '</option>';
    }
    echo '</select></label></p>';
    echo '<p><label>Start datum/tijd:<br><input type="datetime-local" name="rr_start" value="' . esc_attr($start) . '" required ' . $veld_style . '></label></p>';
    echo '<p><label>Eind datum/tijd:<br><input type="datetime-local" name="rr_eind" value="' . esc_attr($eind) . '" required ' . $veld_style . '></label></p>';
    echo '<p><label>Aantal personen:<br><input type="number" name="rr_aantal_personen" value="' . esc_attr($aantal_personen) . '" min="1" step="1" required ' . $veld_style . '></label></p>';
    $checked = $goedgekeurd == '1' ? 'checked' : '';
    echo '<p><label><input type="checkbox" name="rr_goedgekeurd" value="1" ' . $checked . '> Goedgekeurd</label></p>';
    echo '<p>';
    echo '<button type="submit" class="button button-primary">Opslaan</button> ';
    echo '<a href="' . $back_url . '" class="button">Annuleren</a>';
    echo '</p>';
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
        $ruimte_ids = get_post_meta($r->ID, 'ruimte_ids', true);
        if (!is_array($ruimte_ids)) { $ruimte_ids = $ruimte_ids ? array($ruimte_ids) : array(); }
        $ruimte_namen = array();
        foreach ($ruimte_ids as $rid) {
            $ruimte_namen[] = get_the_title($rid);
        }
        $persoon = get_post_meta($r->ID, 'persoon_id', true);
        $start = get_post_meta($r->ID, 'start_dt', true);
        $eind = get_post_meta($r->ID, 'eind_dt', true);
        $aantal_personen = get_post_meta($r->ID, 'aantal_personen', true);
        $goedgekeurd = get_post_meta($r->ID, 'goedgekeurd', true);
        $goedgekeurd_label = $goedgekeurd == '1' ? 'Ja' : 'Nee';
        echo "BEGIN:VEVENT\n";
        echo "UID:rr-" . $r->ID . "@example.com\n";
        echo "DTSTART:" . gmdate('Ymd\\THis\\Z', strtotime($start)) . "\n";
        echo "DTEND:" . gmdate('Ymd\\THis\\Z', strtotime($eind)) . "\n";
        echo "SUMMARY:" . addslashes(implode(', ', $ruimte_namen)) . "\n";
        echo "DESCRIPTION:Verantwoordelijke: " . addslashes(get_the_title($persoon)) . "\\nAantal personen: " . addslashes($aantal_personen) . "\\nGoedgekeurd: " . $goedgekeurd_label . "\n";
        echo "END:VEVENT\n";
    }
    echo "END:VCALENDAR";
    exit;
}
add_action('template_redirect', 'rr_ical_template');

function rr_reservering_heeft_conflict($ruimte_ids, $start, $eind, $exclude_id = null) {
    if (empty($ruimte_ids) || !$start || !$eind) return false;
    $args = [
        'post_type' => 'reservering',
        'numberposts' => -1,
    ];
    $reserveringen = get_posts($args);
    foreach ($reserveringen as $r) {
        if ($exclude_id && $r->ID == $exclude_id) continue;
        $r_ids = get_post_meta($r->ID, 'ruimte_ids', true);
        if (!is_array($r_ids)) { $r_ids = $r_ids ? array($r_ids) : array(); }
        if (count(array_intersect($ruimte_ids, $r_ids)) === 0) continue; // geen overlappende ruimte
        $r_start = get_post_meta($r->ID, 'start_dt', true);
        $r_eind = get_post_meta($r->ID, 'eind_dt', true);
        if (!$r_start || !$r_eind) continue;
        // Controleer overlap: (start < r_eind) && (eind > r_start)
        if (strtotime($start) < strtotime($r_eind) && strtotime($eind) > strtotime($r_start)) {
            return $r;
        }
    }
    return false;
}

