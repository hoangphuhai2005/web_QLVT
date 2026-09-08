<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// --- 1. KIỂM TRA ĐĂNG NHẬP ---
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$current_user = $_SESSION['full_name'] ?? $_SESSION['user'] ?? 'Admin';

// --- 2. KẾT NỐI DB ---
require_once 'db.php'; 

// --- 3. XÁC ĐỊNH TRANG HIỆN TẠI ---
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// --- 4. LOGIC MENU ---
$is_kho_group = in_array($page, ['tonkho', 'phieunhap', 'phieuxuat']);
$is_vc_group = in_array($page, ['lenhvanchuyen', 'taixe']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Logistics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- CSS CƠ BẢN --- */
        :root { --primary: #14532d; --secondary: #16a34a; --bg-body: #f0fdf4; --white: #ffffff; --text: #1e293b; --hover: #166534; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg-body); color: var(--text); height: 100vh; overflow: hidden; }
        a { text-decoration: none; color: inherit; } ul { list-style: none; padding: 0; margin: 0; }
        
        /* SIDEBAR & HEADER (Giữ nguyên như cũ) */
        .sidebar { width: 260px; background: var(--primary); color: #dcfce7; display: flex; flex-direction: column; flex-shrink: 0; }
        .logo { padding: 20px; font-size: 18px; font-weight: bold; color: var(--white); border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 10px; }
        .menu { flex: 1; overflow-y: auto; padding-top: 10px; }
        .menu-link { display: flex; justify-content: space-between; padding: 12px 20px; cursor: pointer; transition: 0.2s; font-weight: 500; align-items: center; border-left: 4px solid transparent; }
        .menu-link:hover { background: var(--hover); color: var(--white); border-left-color: var(--secondary); padding-left: 25px; }
        .menu-link.active { background: var(--hover); color: var(--white); border-left-color: var(--secondary); }
        .submenu { display: none; background: #052e16; }
        .submenu.show { display: block; }
        .submenu a { display: block; padding: 10px 0 10px 50px; font-size: 14px; color: #bbf7d0; transition: 0.2s; }
        .submenu a:hover { color: var(--secondary); transform: translateX(5px); }
        .rotate { transform: rotate(180deg); }
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .header { background: var(--white); padding: 15px 30px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .content-area { flex: 1; padding: 25px; overflow-y: auto; }
        .grid-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .card { background: var(--white); padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 4px solid var(--secondary); }
        .card h3 { margin: 0; font-size: 28px; color: var(--primary); }
        .card p { margin: 5px 0 0; color: #64748b; font-size: 14px; }
        .css-chart { display: flex; align-items: flex-end; justify-content: space-around; height: 250px; padding-top: 20px; border-bottom: 2px solid #cbd5e1; }
        .bar-col { width: 40px; background: #16a34a; border-radius: 4px 4px 0 0; position: relative; transition: height 0.5s ease; }
        .bar-col:hover { background: #14532d; }
        .bar-label { position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%); font-size: 12px; color: #64748b; font-weight: bold; width: 100px; text-align: center; }
        .bar-value { position: absolute; top: -20px; left: 50%; transform: translateX(-50%); font-size: 11px; font-weight: bold; color: #16a34a; }

        /* --- PHẦN MỚI: CSS CHO POPUP MODAL (KHÔNG DÙNG JS) --- */
        
        /* Nút kích hoạt popup (Icon logout) */
        .btn-trigger-logout { color: #ef4444; margin-left: 15px; font-size: 20px; cursor: pointer; }
        
        /* Màn hình đen mờ che phủ (Mặc định ẩn) */
        .modal-overlay { 
            display: none; /* Ẩn đi */
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.5); z-index: 9999; 
            justify-content: center; align-items: center; 
            backdrop-filter: blur(2px); /* Hiệu ứng mờ nền */
        }

        /* Kỹ thuật :target - Khi ID này được gọi trên URL, nó sẽ hiện ra */
        .modal-overlay:target { display: flex; }

        /* Hộp thoại thông báo */
        .modal-box { 
            background: white; padding: 30px; border-radius: 12px; 
            text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2); 
            width: 320px; animation: popIn 0.3s ease;
        }

        /* Nút Yes/No */
        .modal-actions { margin-top: 25px; display: flex; justify-content: center; gap: 15px; }
        .btn-yes, .btn-no { 
            padding: 10px 30px; border-radius: 6px; 
            text-decoration: none; font-weight: bold; font-size: 14px; 
        }
        .btn-yes { background: #ef4444; color: white; box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3); }
        .btn-yes:hover { background: #dc2626; }
        
        .btn-no { background: #e2e8f0; color: #334155; }
        .btn-no:hover { background: #cbd5e1; }

        @keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="logo"><i class="fa-solid fa-leaf"></i> GREENWAY LOGISTICS</div>
        <ul class="menu">
            <li><a href="?page=dashboard" class="menu-link <?= $page=='dashboard'?'active':'' ?>"><span><i class="fa-solid fa-chart-pie" style="width:25px"></i> Tổng quan</span></a></li>
            <li>
                <a href="?page=tonkho" class="menu-link <?= $is_kho_group ? 'active' : '' ?>"><span><i class="fa-solid fa-warehouse" style="width:25px"></i> Quản lý Kho</span><i class="fa-solid fa-chevron-down <?= $is_kho_group ? 'rotate' : '' ?>"></i></a>
                <ul class="submenu <?= $is_kho_group ? 'show' : '' ?>">
                    <li><a href="?page=tonkho"><i class="fa-solid fa-box"></i> Tồn kho</a></li>
                    <li><a href="?page=phieunhap"><i class="fa-solid fa-file-import"></i> Nhập kho</a></li>
                    <li><a href="?page=phieuxuat"><i class="fa-solid fa-file-export"></i> Xuất kho</a></li>
                </ul>
            </li>
            <li>
                <a href="?page=lenhvanchuyen" class="menu-link <?= $is_vc_group ? 'active' : '' ?>"><span><i class="fa-solid fa-truck-front" style="width:25px"></i> Vận chuyển</span><i class="fa-solid fa-chevron-down <?= $is_vc_group ? 'rotate' : '' ?>"></i></a>
                <ul class="submenu <?= $is_vc_group ? 'show' : '' ?>">
                    <li><a href="?page=lenhvanchuyen"><i class="fa-solid fa-route"></i> Lệnh điều phối</a></li>
                    <li><a href="?page=taixe"><i class="fa-solid fa-id-card"></i> Đội xe & Tài xế</a></li>
                </ul>
            </li>
            <li><a href="?page=khachhang" class="menu-link <?= $page=='khachhang'?'active':'' ?>"><span><i class="fa-solid fa-users" style="width:25px"></i> Khách hàng & Đơn</span></a></li>
        </ul>
    </nav>

    <main class="main">
        <div class="header">
            <h2 style="margin:0; color:var(--primary); font-size:18px; text-transform: uppercase;">
                <?php 
                    switch($page) {
                        case 'tonkho': echo '<i class="fa-solid fa-boxes-stacked"></i> Quản lý Tồn kho'; break;
                        case 'phieunhap': echo '<i class="fa-solid fa-file-arrow-down"></i> Phiếu Nhập Kho'; break;
                        case 'phieuxuat': echo '<i class="fa-solid fa-file-arrow-up"></i> Phiếu Xuất Kho'; break;
                        case 'lenhvanchuyen': echo '<i class="fa-solid fa-map-location-dot"></i> Lệnh Điều Phối Xe'; break;
                        case 'taixe': echo '<i class="fa-solid fa-user-shield"></i> Quản lý Tài xế'; break;
                        case 'khachhang': echo '<i class="fa-solid fa-users-viewfinder"></i> Khách hàng & Đơn hàng'; break;
                        default: echo '<i class="fa-solid fa-chart-line"></i> Dashboard Tổng quan';
                    }
                ?>
            </h2>
            <div style="display:flex; align-items:center;">
                <div style="text-align:right; margin-right:15px">
                    <span style="font-size:12px; color:#64748b; display:block">Xin chào,</span>
                    <b style="color:var(--primary)"><?= htmlspecialchars($current_user) ?></b>
                </div>
                <i class="fa-solid fa-circle-user" style="font-size:35px; color:var(--secondary)"></i>
                
                <a href="#logout_confirm" class="btn-trigger-logout" title="Đăng xuất">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>

        <div class="content-area">
            <?php 
            if ($page == 'dashboard') {
                $count_sp = 0; $count_tx = 0; $count_lenh = 0;
                if (isset($conn)) {
                    try {
                        $s1 = $conn->query("SELECT COUNT(*) FROM sanpham"); if($s1) $count_sp = $s1->fetchColumn();
                        $s2 = $conn->query("SELECT COUNT(*) FROM taixe"); if($s2) $count_tx = $s2->fetchColumn();
                        $s3 = $conn->query("SELECT COUNT(*) FROM lenh_van_chuyen WHERE trang_thai_lenh='Dang_Van_Chuyen'"); if($s3) $count_lenh = $s3->fetchColumn();
                    } catch (PDOException $e) {}
                }
                $chart_data = [['label'=>'T2','value'=>12],['label'=>'T3','value'=>19],['label'=>'T4','value'=>15],['label'=>'T5','value'=>25],['label'=>'T6','value'=>22],['label'=>'T7','value'=>10],['label'=>'CN','value'=>5]];
                $max_val = 25;

                echo '<div class="grid-cards">
                    <div class="card"><h3>'.number_format($count_sp).'</h3><p>Mặt hàng trong kho</p></div>
                    <div class="card"><h3>'.number_format($count_lenh).'</h3><p>Chuyến xe đang chạy</p></div>
                    <div class="card"><h3>'.number_format($count_tx).'</h3><p>Tài xế hiện có</p></div>
                    <div class="card" style="border-left-color:#ef4444"><h3 style="color:#ef4444">0</h3><p>Cảnh báo sự cố</p></div>
                </div>
                <div style="background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.05)">
                    <h3 style="margin-top:0; font-size:18px; color:#334155">Thống kê tuần</h3>
                    <div class="css-chart">';
                        foreach($chart_data as $d) {
                            $h = ($d['value'] / $max_val) * 100;
                            echo '<div class="bar-col" style="height: '.$h.'%;"><span class="bar-value">'.$d['value'].'</span><span class="bar-label">'.$d['label'].'</span></div>';
                        }
                echo '</div></div>';
            } else {
                $file_map = ['tonkho'=>'TonKho.php', 'phieunhap'=>'PhieuNhap.php', 'phieuxuat'=>'PhieuXuat.php', 'lenhvanchuyen'=>'LenhVanChuyen.php', 'taixe'=>'TaiXe.php', 'khachhang'=>'QLyInforKH_va_DonHang.php'];
                if (array_key_exists($page, $file_map) && file_exists($file_map[$page])) include $file_map[$page];
                else echo "<div style='text-align:center; padding:30px; color:#666'>Không tìm thấy module hoặc đang phát triển.</div>";
            }
            ?>
        </div>
    </main>

    <div id="logout_confirm" class="modal-overlay">
        <div class="modal-box">
            <i class="fa-solid fa-circle-exclamation" style="font-size: 50px; color: #f59e0b; margin-bottom: 15px;"></i>
            <h3 style="margin: 0 0 10px 0; color: #333;">Xác nhận đăng xuất</h3>
            <p style="color: #666; margin-bottom: 25px;">Bạn có chắc chắn muốn thoát khỏi hệ thống không?</p>
            
            <div class="modal-actions">
                <a href="logout.php" class="btn-yes">Đồng ý</a>
                
                <a href="#" class="btn-no">Hủy</a>
            </div>
        </div>
    </div>

</body>
</html>
