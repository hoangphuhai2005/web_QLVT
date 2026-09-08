<?php
$pass_input = '123456';
$hash_trong_db = '$2y$10$izA.VIxGAtCXu84ofdGefuMKy8uJBo1h9hMDZrQi2L1UPNDj/h5fG';

if (password_verify($pass_input, $hash_trong_db)) {
    echo "Mật khẩu khớp! Hãy kiểm tra lại cột password trong Database có giống hệt chuỗi trên không.";
} else {
    echo "Mật khẩu không khớp. Hãy copy lại chuỗi hash chuẩn.";
}
?>