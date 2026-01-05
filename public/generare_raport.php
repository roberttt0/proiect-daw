<?php
require __DIR__ . '/services/config.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Raport_Imprumuturi_' . date('Y-m-d') . '.csv');

$iesire = fopen('php://output', 'w');
fprintf($iesire, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($iesire, [
    'ID Imprumut', 
    'Nume Utilizator', 
    'Titlu Carte', 
    'Data Imprumut', 
    'Data Scadenta', 
    'Data Retur', 
    'Status Termen'
]);

$sql = "SELECT 
            i.id_imprumut, 
            CONCAT(u.nume, ' ', u.prenume) as nume_complet, 
            c.titlu, 
            i.data_imprumut, 
            i.data_scadenta, 
            i.data_retur
        FROM Imprumut i
        JOIN Utilizator u ON i.id_utilizator = u.id_utilizator
        JOIN Carte c ON i.id_carte = c.id_carte
        WHERE i.data_imprumut >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        ORDER BY i.id_imprumut";

$rezultat = $pdo->prepare($sql);
$rezultat->execute();

while ($rand = $rezultat->fetch()) {
    $status_termen = "";
    
    if ($rand['data_retur'] != NULL) {
        $status_termen = "Returnat";
    } elseif (strtotime($rand['data_scadenta']) < time()) {
        $status_termen = "INTARZIAT";
    } else {
        $status_termen = "In termen";
    }

    fputcsv($iesire, [
        $rand['id_imprumut'],
        $rand['nume_complet'],
        $rand['titlu'],
        $rand['data_imprumut'],
        $rand['data_scadenta'],
        $rand['data_retur'] ?? 'Nereturnat',
        $status_termen
    ]);
}

fclose($iesire);
exit;