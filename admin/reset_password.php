<?php
/**
 * Şifre Sıfırlama Aracı
 * Bu dosyayı kullandıktan sonra silin!
 */

// Yeni şifre (buraya istediğiniz şifreyi yazın)
$newPassword = 'yeni_sifreniz123';

// Şifreyi hash'le
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

echo "Yeni şifre: $newPassword\n";
echo "Hash'lenmiş şifre: $hashedPassword\n\n";

echo "Bu hash'i admin.env dosyasındaki ADMIN_PASSWORD satırına yapıştırın.\n";
echo "Örnek:\n";
echo "ADMIN_PASSWORD=$hashedPassword\n\n";

echo "⚠️  Bu dosyayı kullandıktan sonra silin!\n";

// Dosyayı otomatik sil
unlink(__FILE__);
?> 