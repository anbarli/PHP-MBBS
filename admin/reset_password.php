<?php
/**
 * ï¿½?ifre Sifirlama Araci
 * Bu dosyayi kullandiktan sonra silin!
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

// Yeni sifre (buraya istediginiz sifreyi yazin)
$newPassword = 'yeni_sifreniz123';

// ï¿½?ifreyi hash'le
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

echo "Yeni sifre: $newPassword\n";
echo "Hash'lenmis sifre: $hashedPassword\n\n";

echo "Bu hash'i admin.env dosyasindaki ADMIN_PASSWORD satirina yapistirin.\n";
echo "ï¿½rnek:\n";
echo "ADMIN_PASSWORD=$hashedPassword\n\n";

echo "âš ï¸  Bu dosyayi kullandiktan sonra silin!\n";

// Dosyayi otomatik sil
unlink(__FILE__);
?> 

