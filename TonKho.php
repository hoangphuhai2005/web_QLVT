<?php
// --- 1. KẾT NỐI & DỮ LIỆU ---
// Kiểm tra nếu chưa có biến kết nối thì gọi file db
if (!isset($conn)) {
    if (file_exists('db.php')) require_once 'db.php';
    else die("Lỗi: Không tìm thấy file db.php");
}

// Lấy danh sách cho bộ lọc (Loại hàng & Đơn vị)
try {
    $types = $conn->query("SELECT DISTINCT loai_hang FROM sanpham WHERE loai_hang != '' ORDER BY loai_hang")->fetchAll(PDO::FETCH_COLUMN);
    $units = $conn->query("SELECT DISTINCT don_vi FROM sanpham WHERE don_vi != '' ORDER BY don_vi")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) { $types = []; $units = []; }

// --- 2. XỬ LÝ LỌC (BACKEND) ---
$sql = "SELECT * FROM sanpham";
$conditions = []; 
$params = [];

// A. Tìm kiếm từ khóa
if (!empty($_GET['k'])) { 
    $conditions[] = "(ma_sp LIKE ? OR ten_sp LIKE ?)"; 
    $params[] = "%".$_GET['k']."%"; $params[] = "%".$_GET['k']."%"; 
}

// B. Lọc Loại hàng
if (!empty($_GET['loai'])) {
    $conditions[] = "loai_hang = ?";
    $params[] = $_GET['loai'];
}

// C. Lọc Tồn kho (Radio button)
if (isset($_GET['status']) && $_GET['status'] !== '') {
    if ($_GET['status'] == 'con') $conditions[] = "so_luong > 0";
    if ($_GET['status'] == 'het') $conditions[] = "so_luong = 0";
    if ($_GET['status'] == 'duoi_dinh_muc') $conditions[] = "so_luong <= 10"; 
}

// D. Lọc thời gian
if (!empty($_GET['d_from'])) { $conditions[] = "ngay_tao >= ?"; $params[] = $_GET['d_from']; }
if (!empty($_GET['d_to'])) { $conditions[] = "ngay_tao <= ?"; $params[] = $_GET['d_to']; }

// Ghép SQL
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY id DESC";

// Thực thi
$products = [];
if(isset($conn)) {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
}

// --- 3. XỬ LÝ THÊM MỚI ---
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['act']) && $_POST['act'] == 'add') {
    try {
        $stmt = $conn->prepare("INSERT INTO sanpham (ma_sp, ten_sp, loai_hang, don_vi, so_luong, ngay_tao) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['ma'], $_POST['ten'], $_POST['loai'], $_POST['dvt'], $_POST['sl'], date('Y-m-d')]);
        // Refresh lại trang hiện tại để thấy dữ liệu mới
        echo "<script>window.location.href='?page=tonkho';</script>";
    } catch (Exception $e) {
        $msg = "<div style='padding:10px; background:#fee2e2; color:red; margin-bottom:10px; border-radius:5px;'>❌ Mã hàng '{$_POST['ma']}' đã tồn tại!</div>";
    }
}
?>

<style>
    /* CSS GIAO DIỆN KIOTVIET STYLE */
    .kiot-wrapper { display: flex; gap: 20px; height: calc(100vh - 90px); align-items: flex-start; }
    
    /* 1. SIDEBAR LỌC (Cố định bên trái) */
    .filter-sidebar {
        width: 250px; flex-shrink: 0;
        background: #fff; border-radius: 4px;
        border: 1px solid #e2e8f0;
        padding: 15px;
        height: 100%; overflow-y: auto;
        box-sizing: border-box;
    }
    
    .filter-group { margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }
    .f-title { font-weight: bold; color: #166534; margin-bottom: 10px; font-size: 13px; text-transform: uppercase; }
    
    .kiot-input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; margin-bottom: 5px; box-sizing: border-box; }
    .kiot-radio { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 13px; color: #333; cursor: pointer; }
    .kiot-radio input { accent-color: #16a34a; cursor: pointer; transform: scale(1.1); }

    /* 2. BẢNG DỮ LIỆU (Bên phải) */
    .data-content { flex: 1; display: flex; flex-direction: column; height: 100%; overflow: hidden; }
    
    .top-actions { display: flex; justify-content: space-between; margin-bottom: 10px; align-items: center; }
    .btn-add { background: #16a34a; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 13px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .btn-add:hover { background: #15803d; }

    .table-frame { 
        background: white; border: 1px solid #e2e8f0; border-radius: 4px; 
        flex: 1; overflow: hidden; 
        display: flex; flex-direction: column;
    }
    .table-scroll-area { flex: 1; overflow-y: auto; }
    
    table { width: 100%; border-collapse: collapse; }
    th { position: sticky; top: 0; background: #dcfce7; color: #14532d; padding: 10px 15px; text-align: left; font-size: 12px; border-bottom: 1px solid #16a34a; z-index: 5; font-weight: 700; text-transform: uppercase; }
    td { padding: 10px 15px; border-bottom: 1px solid #eee; color: #333; font-size: 13px; }
    tr:hover { background: #f0fdf4; cursor: pointer; }

    /* Badge */
    .tag { padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; display: inline-block; min-width: 60px; text-align: center; }
    .tag-ok { background: #dcfce7; color: #166534; }
    .tag-low { background: #ffedd5; color: #c2410c; }
    .tag-out { background: #fee2e2; color: #b91c1c; }

    /* Modal */
    .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; }
    .modal-box { background:white; width:500px; padding:25px; border-radius:8px; animation: popUp 0.3s; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    @keyframes popUp { from{transform:scale(0.8);opacity:0} to{transform:scale(1);opacity:1} }
</style>

<div class="kiot-wrapper">

    <form id="filterForm" method="GET" action="" class="filter-sidebar">
        <input type="hidden" name="page" value="tonkho">
        
        <div class="filter-group">
            <div class="f-title">Tìm kiếm</div>
            <input type="text" name="k" class="kiot-input" placeholder="Mã hàng, tên hàng..." value="<?= htmlspecialchars($_GET['k']??'') ?>" onchange="this.form.submit()">
        </div>

        <div class="filter-group">
            <div class="f-title">Trạng thái tồn</div>
            <label class="kiot-radio">
                <input type="radio" name="status" value="" <?= empty($_GET['status'])?'checked':'' ?> onchange="this.form.submit()"> 
                Tất cả
            </label>
            <label class="kiot-radio">
                <input type="radio" name="status" value="con" <?= (isset($_GET['status']) && $_GET['status']=='con')?'checked':'' ?> onchange="this.form.submit()"> 
                Còn hàng
            </label>
            <label class="kiot-radio">
                <input type="radio" name="status" value="het" <?= (isset($_GET['status']) && $_GET['status']=='het')?'checked':'' ?> onchange="this.form.submit()"> 
                Hết hàng
            </label>
            <label class="kiot-radio">
                <input type="radio" name="status" value="duoi_dinh_muc" <?= (isset($_GET['status']) && $_GET['status']=='duoi_dinh_muc')?'checked':'' ?> onchange="this.form.submit()"> 
                Dưới định mức (<=10)
            </label>
        </div>

        <div class="filter-group">
            <div class="f-title">Nhóm hàng</div>
            <select name="loai" class="kiot-input" onchange="this.form.submit()">
                <option value="">-- Tất cả --</option>
                <?php foreach($types as $t): ?>
                    <option value="<?= $t ?>" <?= (isset($_GET['loai']) && $_GET['loai'] == $t) ? 'selected' : '' ?>>
                        <?= $t ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <div class="f-title">Ngày tạo</div>
            <div style="font-size:12px; margin-bottom:3px;">Từ ngày:</div>
            <input type="date" name="d_from" class="kiot-input" value="<?= $_GET['d_from']??'' ?>" onchange="this.form.submit()">
            <div style="font-size:12px; margin-bottom:3px;">Đến ngày:</div>
            <input type="date" name="d_to" class="kiot-input" value="<?= $_GET['d_to']??'' ?>" onchange="this.form.submit()">
        </div>
        
        <div style="text-align: center; margin-top: 10px;">
            <a href="?page=tonkho" style="color: #64748b; text-decoration: none; font-size: 13px; border: 1px solid #ccc; padding: 5px 10px; border-radius: 4px; background: #f8fafc;">
                <i class="fa-solid fa-rotate-right"></i> Xóa bộ lọc
            </a>
        </div>
    </form>

    <div class="data-content">
        <div class="top-actions">
            <div style="font-size: 18px; font-weight: bold; color: #166534; display:flex; align-items:center;">
                <i class="fa-solid fa-list" style="margin-right:8px;"></i> Danh sách hàng hóa
                <span style="font-weight:normal; color:#666; font-size:14px; margin-left:10px; background:#e2e8f0; padding:2px 8px; border-radius:10px;">
                    <?= count($products) ?> kết quả
                </span>
            </div>
            <button class="btn-add" onclick="document.getElementById('modalAdd').style.display='flex'">
                <i class="fa-solid fa-plus"></i> Thêm mới
            </button>
        </div>

        <?= $msg ?>

        <div class="table-frame">
            <div class="table-scroll-area">
                <table>
                    <thead>
                        <tr>
                            <th width="50">STT</th>
                            <th width="150">Mã Hàng</th>
                            <th>Tên Hàng</th>
                            <th width="120">Loại</th>
                            <th width="80">ĐVT</th>
                            <th width="100">Số Lượng</th>
                            <th width="100">Ngày Tạo</th>
                            <th width="100">Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($products) > 0): $i=1; foreach($products as $r): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td style="font-weight:bold; color:#334155"><?= htmlspecialchars($r['ma_sp']) ?></td>
                            <td style="font-weight:500"><?= htmlspecialchars($r['ten_sp']) ?></td>
                            <td><?= $r['loai_hang'] ?></td>
                            <td><?= $r['don_vi'] ?></td>
                            <td style="font-weight:bold; color:<?= $r['so_luong']>0 ? '#166534':'#b91c1c' ?>">
                                <?= number_format($r['so_luong']) ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($r['ngay_tao'])) ?></td>
                            <td>
                                <?php 
                                    if($r['so_luong'] == 0) echo '<span class="tag tag-out">Hết</span>';
                                    elseif($r['so_luong'] <= 10) echo '<span class="tag tag-low">Sắp hết</span>';
                                    else echo '<span class="tag tag-ok">Còn</span>';
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:50px; color:#999;">
                                <i class="fa-solid fa-box-open" style="font-size:30px; margin-bottom:10px;"></i><br>
                                Không tìm thấy dữ liệu phù hợp
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div id="modalAdd" class="modal">
    <div class="modal-box">
        <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
            <h3 style="margin:0; color:#166534">Thêm Hàng Hóa Mới</h3>
            <span onclick="document.getElementById('modalAdd').style.display='none'" style="cursor:pointer; font-size:24px;">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="act" value="add">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label style="font-size:12px; font-weight:bold;">Mã hàng (*)</label>
                    <input type="text" name="ma" class="kiot-input" required placeholder="VD: SP001">
                </div>
                <div>
                    <label style="font-size:12px; font-weight:bold;">Nhóm hàng</label>
                    <input type="text" name="loai" class="kiot-input" list="typeList" placeholder="Chọn hoặc nhập mới">
                    <datalist id="typeList"><?php foreach($types as $t) echo "<option value='$t'>"; ?></datalist>
                </div>
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="font-size:12px; font-weight:bold;">Tên hàng (*)</label>
                <input type="text" name="ten" class="kiot-input" required placeholder="Nhập tên sản phẩm...">
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:20px;">
                <div>
                    <label style="font-size:12px; font-weight:bold;">Đơn vị tính</label>
                    <input type="text" name="dvt" class="kiot-input" list="unitList">
                    <datalist id="unitList"><?php foreach($units as $u) echo "<option value='$u'>"; ?></datalist>
                </div>
                <div>
                    <label style="font-size:12px; font-weight:bold;">Tồn kho ban đầu</label>
                    <input type="number" name="sl" class="kiot-input" value="0">
                </div>
            </div>
            
            <div style="text-align:right; border-top:1px solid #eee; padding-top:15px;">
                <button type="button" class="btn-add" style="background:#fff; color:#333; border:1px solid #ccc; display:inline-flex;" onclick="document.getElementById('modalAdd').style.display='none'">Bỏ qua</button>
                <button type="submit" class="btn-add" style="display:inline-flex;">Lưu lại</button>
            </div>
        </form>
    </div>
</div>