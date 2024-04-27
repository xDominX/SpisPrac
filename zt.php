<?php
/*
Plugin Name: ZT
Description: Spis Prac
Version: 1.0
Author: Domin
*/
error_reporting(0);
?>
<head>
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'zt.css', __FILE__ ); ?>">
</head>
<?php
// Obsługa żądania dodawania nowego wpisu
if (isset($_POST['submit'])) {
    global $wpdb;
    $id_users = intval($_POST['id_users']);
    $data = $_POST['data'];
    $komu = sanitize_text_field($_POST['komu']);
    $co = sanitize_text_field($_POST['co']);
    $czas = $_POST['czas'];
    $uwagi = sanitize_text_field($_POST['uwagi']);
    $wpdb->insert(
        'Spisprac',
        array(
            'id_users' => $id_users,
            'data' => $data,
            'komu' => $komu,
            'co' => $co,
            'czas' => $czas,
            'uwagi' => $uwagi
        )
    );
    if ($wpdb->last_error) {
        echo '<div class="notice notice-error"><p>Wystąpił błąd podczas dodawania wpisu: ' . $wpdb->last_error . '</p></div>';
    } else {
        echo '<div class="notice notice-success"><p>Wpis został pomyślnie dodany!</p></div>';
    }
}
// Dodanie menu i podmenu do panelu administracyjnego
function dodaj_moja_zakladke_admin() {
    add_menu_page(
        'SPIS PRAC',      // Tytuł zakładki
        'SPIS PRAC',      // Nazwa wyświetlana w menu
        'manage_options', // Wymagane uprawnienia do wyświetlenia
        'spis_prac',      // Unikalny identyfikator
        'funkcja_do_wyswietlania_zakladki', // Funkcja do wyświetlania zawartości zakładki
        'dashicons-admin-generic', // Ikona (opcjonalnie)
        6                          // Pozycja w menu (opcjonalnie)
    );
    add_submenu_page(
        'spis_prac',       // Unikalny identyfikator głównej zakładki
        'Dodaj wpis',      // Tytuł podzakładki
        'Dodaj wpis',      // Nazwa wyświetlana w menu
        'manage_options',  // Wymagane uprawnienia do wyświetlenia
        'dodaj_wpis',      // Unikalny identyfikator podzakładki
        'funkcja_do_wyswietlania_podzakladki' // Funkcja do wyświetlania zawartości podzakładki
    );
}
add_action('admin_menu', 'dodaj_moja_zakladke_admin');
// Funkcja wyświetlająca zawartość głównej zakładki
function funkcja_do_wyswietlania_zakladki() {
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM Spisprac", OBJECT);
    echo '<div class="wrap">';
    echo '<h2>Spis prac</h2>';
    // Tabela
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>ID Użytkownika <input type="text" class="column-filter" data-column="1"></th>';
    echo '<th>Data <input type="text" class="column-filter" data-column="2"></th>';
    echo '<th>Komu <input type="text" class="column-filter" data-column="3"></th>';
    echo '<th>Co zrobione <input type="text" class="column-filter" data-column="4"></th>';
    echo '<th>Czas <input type="text" class="column-filter" data-column="5"></th>';
    echo '<th>Uwagi <input type="text" class="column-filter" data-column="6"></th>';
    echo '<th>Akcje</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($results as $row) {
        echo '<tr>';
        echo '<td>' . $row->id . '</td>';
        echo '<td class="filterable">' . $row->id_users . '</td>';
        echo '<td class="filterable">' . $row->data . '</td>';
        echo '<td class="filterable">' . $row->komu . '</td>';
        echo '<td class="filterable">' . $row->co . '</td>';
        echo '<td class="filterable">' . $row->czas . '</td>';
        echo '<td class="filterable">' . $row->uwagi . '</td>';
        echo '<td><a href="?page=spis_prac&action=edit&id=' . $row->id . '">Edytuj</a> | <a href="?page=spis_prac&action=delete&id=' . $row->id . '">Usuń</a></td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
// Funkcja wyświetlająca zawartość podzakładki
function funkcja_do_wyswietlania_podzakladki() {
    ?>
    <div id="edit-form-wrapper" class="wrap">
        <h2>Dodaj nowy wpis do tabeli Spisprac</h2>
        <form method="post">
            <label for="id_users">ID Użytkownika:</label>
            <input type="number" name="id_users" id="id_users" required><br><br>
            <label for="data">Data:</label>
            <input type="date" name="data" id="data" required><br><br>
            <label for="komu">Komu:</label>
            <input type="text" name="komu" id="komu" required><br><br>
            <label for="co">Co:</label>
            <input type="text" name="co" id="co" required><br><br>
            <label for="czas">Czas:</label>
            <input type="number" name="czas" id="czas" required><br><br>
            <label for="uwagi">Uwagi:</label>
            <textarea name="uwagi" id="uwagi" required></textarea><br><br>
            <input type="submit" name="submit" value="Dodaj wpis">
        </form>
    </div>
    <?php
}
// JavaScript do filtrowania
?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let filters = document.querySelectorAll('.column-filter');
        filters.forEach(function (filter) {
            filter.addEventListener('input', function () {
                let index = parseInt(filter.getAttribute('data-column'));
                let value = filter.value.toUpperCase();
                let table = filter.closest('table');
                let rows = table.querySelectorAll('tbody tr');
                rows.forEach(function (row) {
                    let td = row.getElementsByTagName('td')[index];
                    if (td) {
                        let textValue = td.textContent || td.innerText;
                        if (textValue.toUpperCase().indexOf(value) > -1) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        });
    });
</script>
<?php
// Obsługa żądania usuwania
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    global $wpdb;
    $id = intval($_GET['id']);
    $result = $wpdb->delete("Spisprac", array('id' => $id), array('%d'));
    if ($result) {
        echo '<div class="notice notice-success"><p>Wpis został pomyślnie usunięty!</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Wystąpił błąd podczas usuwania wpisu.</p></div>';
    }
}
// Obsługa żądania edycji
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    global $wpdb;
    $entry = $wpdb->get_row("SELECT * FROM Spisprac WHERE id = $id", OBJECT);
    if ($entry) {
        ?>
        <div id="edit-form-wrapper" class="wrap">
            <h2>Edytuj wpis</h2>
            <form method="post" action="?page=spis_prac&action=save_edit&id=<?php echo $entry->id; ?>">
                <label for="id_users">ID Użytkownika:</label>
                <input type="text" name="id_users" id="id_users" value="<?php echo $entry->id_users; ?>" required><br><br>
                <label for="data">Data:</label>
                <input type="date" name="data" id="data" value="<?php echo $entry->data; ?>" required><br><br>
                <label for="komu">Komu:</label>
                <input type="text" name="komu" id="komu" value="<?php echo $entry->komu; ?>" required><br><br>
                <label for="co">Co:</label>
                <input type="text" name="co" id="co" value="<?php echo $entry->co; ?>" required><br><br>
                <label for="czas">Czas:</label>
                <input type="number" name="czas" id="czas" value="<?php echo $entry->czas; ?>" required><br><br>
                <label for="uwagi">Uwagi:</label>
                <textarea name="uwagi" id="uwagi" required><?php echo $entry->uwagi; ?></textarea><br><br>
                <input type="submit" name="submit_edit" value="Zapisz zmiany">
            </form>
        </div>
        <?php
    } else {
        echo '<div class="notice notice-error"><p>Nie znaleziono wpisu do edycji.</p></div>';
    }
}
// Obsługa żądania zapisu edycji
if (isset($_POST['submit_edit'])) {
    global $wpdb;
    $id = intval($_GET['id']);
    $id_users = sanitize_text_field($_POST['id_users']);
    $data = sanitize_text_field($_POST['data']);
    $komu = sanitize_text_field($_POST['komu']);
    $co = sanitize_text_field($_POST['co']);
    $czas = sanitize_text_field($_POST['czas']);
    $uwagi = sanitize_textarea_field($_POST['uwagi']);
    $result = $wpdb->update(
        'Spisprac',
        array(
            'id_users' => $id_users,
            'data' => $data,
            'komu' => $komu,
            'co' => $co,
            'czas' => $czas,
            'uwagi' => $uwagi
        ),
        array('id' => $id),
        array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        ),
        array('%d')
    );
    if ($result !== false) {
        echo '<div class="notice notice-success"><p>Wpis został pomyślnie zaktualizowany!</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Wystąpił błąd podczas aktualizacji wpisu.</p></div>';
    }
}
?>
