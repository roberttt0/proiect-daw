<?php
require __DIR__ . '/services/config.php'; 

try {
    
    $stmtDisp = $pdo->query("SELECT SUM(stoc_disponibil) as total FROM Carte");
    $rowDisp = $stmtDisp->fetch(PDO::FETCH_ASSOC);
    $disponibile = (int)($rowDisp['total'] ?? 0);

    $stmtImpr = $pdo->query("SELECT COUNT(*) as total FROM Imprumut WHERE data_retur IS NULL");
    $rowImpr = $stmtImpr->fetch(PDO::FETCH_ASSOC);
    $imprumutate = (int)($rowImpr['total'] ?? 0);

    $total = $disponibile + $imprumutate;
    if ($total == 0) $total = 1;

    $latime = 450;
    $inaltime = 200;
    $imagine = imagecreate($latime, $inaltime);

    $alb        = imagecolorallocate($imagine, 255, 255, 255);
    $verde      = imagecolorallocate($imagine, 40, 167, 69);
    $rosu       = imagecolorallocate($imagine, 220, 53, 69);
    $text_color = imagecolorallocate($imagine, 50, 50, 50);
    $gri_fundal = imagecolorallocate($imagine, 235, 235, 235);

    $lungime_max = 300;
    $bara_verde = (int)(($disponibile / $total) * $lungime_max);
    $bara_rosie = (int)(($imprumutate / $total) * $lungime_max);

    imagefilledrectangle($imagine, 50, 60, 50 + $lungime_max, 80, $gri_fundal);
    imagefilledrectangle($imagine, 50, 60, 50 + $bara_verde, 80, $verde);

    imagefilledrectangle($imagine, 50, 120, 50 + $lungime_max, 140, $gri_fundal);
    imagefilledrectangle($imagine, 50, 120, 50 + $bara_rosie, 140, $rosu);

    imagestring($imagine, 5, 50, 40, "Carti Disponibile: $disponibile", $text_color);
    imagestring($imagine, 5, 50, 100, "Carti Imprumutate: $imprumutate", $text_color);

    if (ob_get_length()) ob_clean();
    header('Content-Type: image/png');
    imagepng($imagine);

} catch (PDOException $e) {
    exit;
}