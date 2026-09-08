<?php
// Kết nối DB
if (!isset($conn)) { if (file_exists('db.php')) require_once 'db.php'; }

// --- XỬ LÝ NGHIỆP VỤ (CONTROLLER) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['act'])) {
    
    // 1. TẠO LỆNH MỚI
    if ($_POST['act'] == 'create') {
        try {
            $tong = $_POST['cuoc'] + $_POST['phi'];
            $sql = "INSERT INTO lenh_van_chuyen (ten_khach_hang, loai_xe_yeu_cau, noi_di, noi_den, cuoc_phi, phi_khac, tong_cong, ghi_chu, trang_thai_lenh, ngay_tao) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Cho_Xu_Ly', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_POST['khach'], $_POST['xe_yc'], $_POST['di'], $_POST['den'], $_POST['cuoc'], $_POST['phi'], $tong, $_POST['note']]);
            echo "<script>alert('✅ Đã lập lệnh mới!'); window.location.href='?page=lenhvanchuyen';</script>";
        } catch(Exception $e) { echo "<script>alert('Lỗi: ".$e->getMessage()."');</script>"; }
    }

    // 2. PHÂN CÔNG TÀI XẾ
    if ($_POST['act'] == 'assign') {
        $ma_lenh = $_POST['ma_lenh'];
        $ma_tx = $_POST['ma_tx'];
        $conn->query("UPDATE lenh_van_chuyen SET ma_tai_xe_phu_trach = $ma_tx, trang_thai_lenh = 'Da_Phan_Cong', ngay_tiep_nhan = NOW() WHERE ma_lenh = $ma_lenh");
        $conn->query("UPDATE taixe SET trang_thai = 'Dang_ban' WHERE ma_tai_xe = $ma_tx");
        echo "<script>window.location.href='?page=lenhvanchuyen';</script>";
    }

    // 3. CẬP NHẬT TRẠNG THÁI (Chạy / Giao / Sự cố)
    if ($_POST['act'] == 'update_status') {
        $id = $_POST['id'];
        $status = $_POST['status'];
        $note = $_POST['note_su_co'] ?? '';
        
        $sql = "UPDATE lenh_van_chuyen SET trang_thai_lenh = ?";
        if($note) $sql .= ", chi_tiet_su_co = '$note'";
        $sql .= " WHERE ma_lenh = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$status, $id]);
        echo "<script>window.location.href='?page=lenhvanchuyen';</script>";
    }

    // 4. HOÀN TẤT (LƯU LỊCH SỬ)
    if ($_POST['act'] == 'finish') {
        $id = $_POST['id'];
        $tx_id = $_POST['tx_id'];
        
        $conn->query("UPDATE lenh_van_chuyen SET trang_thai_lenh = 'Hoan_Thanh' WHERE ma_lenh = $id");
        $conn->query("UPDATE taixe SET trang_thai = 'San_sang' WHERE ma_tai_xe = $tx_id");
        echo "<script>alert('✅ Đã lưu vào lịch sử!'); window.location.href='?page=lenhvanchuyen';</script>";
    }
}

// --- LẤY DỮ LIỆU ---
// 1. Tài xế rảnh
$drivers = $conn->query("SELECT * FROM taixe WHERE trang_thai = 'San_sang'")->fetchAll(PDO::FETCH_ASSOC);

// 2. Danh sách ĐANG HOẠT ĐỘNG (Chưa hoàn thành)
$sql_active = "SELECT L.*, T.ho_ten, T.sdt, T.bang_lai 
               FROM lenh_van_chuyen L LEFT JOIN taixe T ON L.ma_tai_xe_phu_trach = T.ma_tai_xe 
               WHERE L.trang_thai_lenh != 'Hoan_Thanh' 
               ORDER BY L.ma_lenh DESC";
$active_orders = $conn->query($sql_active)->fetchAll(PDO::FETCH_ASSOC);

// 3. Danh sách LỊCH SỬ (Đã hoàn thành) - Lấy 20 tin mới nhất
$sql_history = "SELECT L.*, T.ho_ten 
                FROM lenh_van_chuyen L LEFT JOIN taixe T ON L.ma_tai_xe_phu_trach = T.ma_tai_xe 
                WHERE L.trang_thai_lenh = 'Hoan_Thanh' 
                ORDER BY L.ma_lenh DESC LIMIT 20";
$history_orders = $conn->query($sql_history)->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .grid-top { display: grid; grid-template-columns: 320px 1fr; gap: 20px; margin-bottom: 30px; }
    .panel { background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    
    .form-group { margin-bottom: 12px; }
    .lbl { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 4px; }
    .inp, .sel, textarea { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; }
    
    .btn { width: 100%; padding: 10px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; color: white; }
    .btn-create { background: #16a34a; } .btn-create:hover { background: #15803d; }
    
    /* Action Buttons */
    .btn-action { padding: 5px 10px; font-size: 11px; margin-right: 3px; border-radius: 3px; border: none; cursor: pointer; color: white; }
    .act-start { background: #2563eb; } 
    .act-issue { background: #dc2626; } 
    .act-deliv { background: #d97706; } 
    .act-done { background: #059669; }

    /* Badges & Table */
    .badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; display: inline-block; }
    .st-new { background: #e2e8f0; color: #475569; }
    .st-assign { background: #dbeafe; color: #1e40af; }
    .st-run { background: #fef9c3; color: #854d0e; animation: pulse 2s infinite; }
    .st-deliv { background: #ffedd5; color: #c2410c; }
    .st-error { background: #fee2e2; color: #991b1b; }
    .st-ok { background: #dcfce7; color: #166534; }

    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }
    
    table { width:100%; border-collapse:collapse; font-size:13px; }
    th { padding:10px; background:#f8fafc; text-align:left; border-bottom:2px solid #e2e8f0; }
    td { padding:10px; border-bottom:1px solid #f1f5f9; vertical-align:top; }
</style>

<div class="grid-top">
    
    <div class="panel">
        <h3 style="margin-top:0; color:#166534">📄 Lập Lệnh Mới</h3>
        <form method="POST">
            <input type="hidden" name="act" value="create">
            <div class="form-group"><label class="lbl">Khách hàng</label><input type="text" name="khach" class="inp" required></div>
            <div style="display:flex; gap:10px">
                <div class="form-group" style="flex:1"><label class="lbl">Điểm đi</label><input type="text" name="di" class="inp" required></div>
                <div class="form-group" style="flex:1"><label class="lbl">Điểm đến</label><input type="text" name="den" class="inp" required></div>
            </div>
            <div class="form-group">
                <label class="lbl">Loại xe</label>
                <select name="xe_yc" class="sel">
                    <option value="Xe tải nhẹ">Xe tải nhẹ (1-2 tấn)</option>
                    <option value="Xe tải trung">Xe tải trung (5-8 tấn)</option>
                    <option value="Container">Container / Đầu kéo</option>
                    <option value="Xe lạnh">Xe đông lạnh</option>
                </select>
            </div>
            <div style="display:flex; gap:10px">
                <div class="form-group" style="flex:1"><label class="lbl">Cước (VNĐ)</label><input type="number" name="cuoc" class="inp" required></div>
                <div class="form-group" style="flex:1"><label class="lbl">Phí khác</label><input type="number" name="phi" value="0" class="inp"></div>
            </div>
            <div class="form-group"><label class="lbl">Ghi chú</label><textarea name="note" class="inp" rows="2"></textarea></div>
            <button type="submit" class="btn btn-create">TẠO LỆNH</button>
        </form>
    </div>

    <div class="panel">
        <h3 style="margin-top:0; color:#1e293b; display:flex; justify-content:space-between">
            <span>📡 Điều Phối Vận Hành</span>
            <span style="font-size:12px; color:#64748b; font-weight:normal">Đang xử lý: <b><?= count($active_orders) ?></b> đơn</span>
        </h3>
        
        <?php if(empty($active_orders)): ?>
            <div style="text-align:center; padding:30px; color:#94a3b8; border:2px dashed #e2e8f0; border-radius:8px">
                <i class="fa-solid fa-check-circle" style="font-size:30px"></i><br>Không có đơn hàng nào đang chạy
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Lệnh / Tuyến</th>
                        <th>Tài Xế Phụ Trách</th>
                        <th>Trạng Thái</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($active_orders as $o): 
                        $st = $o['trang_thai_lenh'];
                        $st_badge = '<span class="badge st-new">Chờ xử lý</span>';
                        if($st == 'Da_Phan_Cong') $st_badge = '<span class="badge st-assign">Đã điều xe</span>';
                        if($st == 'Dang_Van_Chuyen') $st_badge = '<span class="badge st-run"><i class="fa-solid fa-truck-fast"></i> Đang chạy</span>';
                        if($st == 'Da_Giao_Hang') $st_badge = '<span class="badge st-deliv">Chờ xác nhận</span>';
                        if($st == 'Su_Co') $st_badge = '<span class="badge st-error">⚠️ SỰ CỐ</span>';
                    ?>
                    <tr>
                        <td>
                            <b>#<?= $o['ma_lenh'] ?></b><br>
                            <?= $o['ten_khach_hang'] ?><br>
                            <small style="color:#64748b"><?= $o['noi_di'] ?> ➝ <?= $o['noi_den'] ?></small>
                        </td>
                        <td>
                            <?php if($o['ma_tai_xe_phu_trach']): ?>
                                <b><?= $o['ho_ten'] ?></b><br>
                                <small><i class="fa-solid fa-phone"></i> <?= $o['sdt'] ?></small>
                            <?php else: ?>
                                <em style="color:#999">Chưa gán</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $st_badge ?>
                            <?php if($st == 'Su_Co') echo "<div style='font-size:10px; color:red; margin-top:2px'>{$o['chi_tiet_su_co']}</div>"; ?>
                        </td>
                        <td>
                            <?php if($st == 'Cho_Xu_Ly'): ?>
                                <form method="POST" style="display:flex; gap:5px">
                                    <input type="hidden" name="act" value="assign">
                                    <input type="hidden" name="ma_lenh" value="<?= $o['ma_lenh'] ?>">
                                    <select name="ma_tx" style="font-size:11px; max-width:100px">
                                        <option value="">Chọn TX...</option>
                                        <?php foreach($drivers as $d) echo "<option value='{$d['ma_tai_xe']}'>{$d['ho_ten']} ({$d['bang_lai']})</option>"; ?>
                                    </select>
                                    <button class="btn-action act-start">Duyệt</button>
                                </form>
                            <?php elseif($st == 'Da_Phan_Cong'): ?>
                                <form method="POST">
                                    <input type="hidden" name="act" value="update_status">
                                    <input type="hidden" name="id" value="<?= $o['ma_lenh'] ?>">
                                    <input type="hidden" name="status" value="Dang_Van_Chuyen">
                                    <button class="btn-action act-start"><i class="fa-solid fa-play"></i> Chạy</button>
                                </form>
                            <?php elseif($st == 'Dang_Van_Chuyen'): ?>
                                <div style="display:flex; gap:3px">
                                    <button class="btn-action act-issue" onclick="document.getElementById('sc_<?= $o['ma_lenh'] ?>').style.display='block'">Sự cố</button>
                                    <form method="POST">
                                        <input type="hidden" name="act" value="update_status">
                                        <input type="hidden" name="id" value="<?= $o['ma_lenh'] ?>">
                                        <input type="hidden" name="status" value="Da_Giao_Hang">
                                        <button class="btn-action act-deliv">Đã giao</button>
                                    </form>
                                </div>
                                <form method="POST" id="sc_<?= $o['ma_lenh'] ?>" style="display:none; margin-top:5px">
                                    <input type="hidden" name="act" value="update_status">
                                    <input type="hidden" name="id" value="<?= $o['ma_lenh'] ?>">
                                    <input type="hidden" name="status" value="Su_Co">
                                    <input type="text" name="note_su_co" placeholder="Lý do..." style="width:100%; font-size:10px;">
                                    <button class="btn-action act-issue" style="width:100%">Gửi</button>
                                </form>
                            <?php elseif($st == 'Su_Co'): ?>
                                <form method="POST">
                                    <input type="hidden" name="act" value="update_status">
                                    <input type="hidden" name="id" value="<?= $o['ma_lenh'] ?>">
                                    <input type="hidden" name="status" value="Dang_Van_Chuyen">
                                    <button class="btn-action act-start">Đã sửa (Chạy tiếp)</button>
                                </form>
                            <?php elseif($st == 'Da_Giao_Hang'): ?>
                                <form method="POST" onsubmit="return confirm('Hoàn tất và lưu hồ sơ?')">
                                    <input type="hidden" name="act" value="finish">
                                    <input type="hidden" name="id" value="<?= $o['ma_lenh'] ?>">
                                    <input type="hidden" name="tx_id" value="<?= $o['ma_tai_xe_phu_trach'] ?>">
                                    <button class="btn-action act-done">Lưu Hồ Sơ</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="panel" style="background:#f8fafc; border:1px solid #cbd5e1;">
    <h3 style="margin-top:0; color:#475569">🗄️ Lịch Sử Chuyến Xe (Đã hoàn tất)</h3>
    <?php if(empty($history_orders)): ?>
        <p style="color:#64748b; font-style:italic">Chưa có lịch sử lưu trữ.</p>
    <?php else: ?>
        <table style="color:#475569">
            <thead>
                <tr>
                    <th>Mã Lệnh</th>
                    <th>Ngày Tạo</th>
                    <th>Khách Hàng</th>
                    <th>Tài Xế Thực Hiện</th>
                    <th>Doanh Thu</th>
                    <th>Trạng Thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($history_orders as $h): ?>
                <tr>
                    <td><b>#<?= $h['ma_lenh'] ?></b></td>
                    <td><?= date('d/m/Y', strtotime($h['ngay_tao'])) ?></td>
                    <td><?= $h['ten_khach_hang'] ?><br><small><?= $h['noi_di'] ?> ➝ <?= $h['noi_den'] ?></small></td>
                    <td><?= $h['ho_ten'] ?></td>
                    <td><?= number_format($h['tong_cong']) ?> đ</td>
                    <td><span class="badge st-ok">Hoàn tất</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>