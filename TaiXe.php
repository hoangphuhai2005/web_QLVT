<?php
// Kiểm tra kết nối
if (!isset($conn)) { if (file_exists('db.php')) require_once 'db.php'; }

// --- LOGIC NGHIỆP VỤ TỰ ĐỘNG (SYSTEM ACTOR) ---
function tu_dong_kiem_tra_tai_xe($conn) {
    $hom_nay = date('Y-m-d');
    
    // 1. Khóa tài xế hết hạn bằng lái
    $sql_expire = "UPDATE taixe SET trang_thai = 'Bi_khoa', ly_do_khoa = 'GPLX hết hạn' 
                   WHERE ngay_hethan_banglai < '$hom_nay' AND trang_thai != 'Bi_khoa'";
    $conn->query($sql_expire);

    // 2. Khóa tài xế sức khỏe yếu
    $sql_health = "UPDATE taixe SET trang_thai = 'Bi_khoa', ly_do_khoa = 'Sức khỏe không đạt' 
                   WHERE suc_khoe = 'Khong_Dat' AND trang_thai != 'Bi_khoa'";
    $conn->query($sql_health);
}
// Chạy kiểm tra ngay khi load trang
tu_dong_kiem_tra_tai_xe($conn);


// --- XỬ LÝ FORM: THÊM MỚI TÀI XẾ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['act']) && $_POST['act'] == 'add') {
    try {
        $sql = "INSERT INTO taixe (ho_ten, ngay_sinh, cccd, sdt, bang_lai, ngay_hethan_banglai, suc_khoe, ngay_kham_sk) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $_POST['ho_ten'], $_POST['ngay_sinh'], $_POST['cccd'], $_POST['sdt'],
            $_POST['bang_lai'], $_POST['ngay_hethan'], $_POST['suc_khoe'], $_POST['ngay_kham']
        ]);
        echo "<script>alert('✅ Thêm hồ sơ tài xế thành công!'); window.location.href='?page=taixe';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Lỗi: ".$e->getMessage()."');</script>";
    }
}

// --- XỬ LÝ FORM: CẬP NHẬT / ĐÁNH GIÁ / MỞ KHÓA ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['act']) && $_POST['act'] == 'update_status') {
    $ma = $_POST['ma_tx'];
    $tt = $_POST['trang_thai'];
    $vp = $_POST['vi_pham'];
    $sk = $_POST['suc_khoe'];
    $diem = $_POST['diem'];

    // Logic: Nếu vi phạm nặng hoặc sức khỏe yếu -> Buộc khóa
    $ly_do = "";
    if ($sk == 'Khong_Dat') { $tt = 'Bi_khoa'; $ly_do = "Sức khỏe không đạt"; }
    
    $sql = "UPDATE taixe SET trang_thai=?, suc_khoe=?, lich_su_vi_pham=?, diem_tin_nhiem=?, ly_do_khoa=? WHERE ma_tai_xe=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$tt, $sk, $vp, $diem, $ly_do, $ma]);
    
    // Chạy lại kiểm tra tự động để đảm bảo tính nhất quán
    tu_dong_kiem_tra_tai_xe($conn);
    
    echo "<script>alert('✅ Đã cập nhật hồ sơ & đánh giá!'); window.location.href='?page=taixe';</script>";
}

// Xóa tài xế
if (isset($_GET['del'])) {
    $conn->prepare("DELETE FROM taixe WHERE ma_tai_xe=?")->execute([$_GET['del']]);
    echo "<script>window.location.href='?page=taixe';</script>";
}

// Lấy danh sách
$drivers = $conn->query("SELECT * FROM taixe ORDER BY trang_thai ASC, diem_tin_nhiem DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .grid-layout { display: grid; grid-template-columns: 350px 1fr; gap: 20px; font-family: 'Segoe UI', sans-serif; }
    .panel { background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    h3 { margin-top: 0; color: #166534; border-bottom: 2px solid #f0fdf4; padding-bottom: 10px; }
    
    .form-group { margin-bottom: 12px; }
    .lbl { font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px; }
    .inp, .sel { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; }
    .row-2 { display: flex; gap: 10px; }
    
    .btn { width: 100%; padding: 10px; border: none; border-radius: 4px; font-weight: bold; color: white; cursor: pointer; }
    .btn-add { background: #16a34a; } .btn-add:hover { background: #15803d; }
    .btn-save { background: #2563eb; width: auto; padding: 5px 10px; font-size: 12px; }

    table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px; }
    th { background: #f8fafc; text-align: left; padding: 10px; border-bottom: 2px solid #e2e8f0; }
    td { padding: 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }

    /* Badge trạng thái */
    .badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; display: inline-block; }
    .st-ready { background: #dcfce7; color: #166534; }
    .st-busy { background: #fef9c3; color: #854d0e; }
    .st-lock { background: #fee2e2; color: #991b1b; }
    
    .alert-expiry { color: #dc2626; font-weight: bold; font-size: 11px; }
</style>

<div class="grid-layout">
    
    <div class="panel">
        <h3>📄 Tiếp Nhận Hồ Sơ Mới</h3>
        <form method="POST">
            <input type="hidden" name="act" value="add">
            
            <div class="form-group">
                <label class="lbl">Họ và Tên (*)</label>
                <input type="text" name="ho_ten" class="inp" required placeholder="VD: Nguyễn Văn A">
            </div>
            
            <div class="row-2">
                <div class="form-group" style="flex:1">
                    <label class="lbl">Ngày sinh</label>
                    <input type="date" name="ngay_sinh" class="inp">
                </div>
                <div class="form-group" style="flex:1">
                    <label class="lbl">CCCD</label>
                    <input type="text" name="cccd" class="inp">
                </div>
            </div>

            <div class="form-group">
                <label class="lbl">Số điện thoại (*)</label>
                <input type="text" name="sdt" class="inp" required>
            </div>

            <div style="border-top: 1px dashed #ccc; margin: 10px 0;"></div>
            <strong style="font-size:12px; color:#1e293b">Thông tin Pháp lý & Nghiệp vụ</strong>

            <div class="row-2">
                <div class="form-group" style="flex:1">
                    <label class="lbl">Hạng Bằng</label>
                    <select name="bang_lai" class="sel">
                        <option value="C">Hạng C</option>
                        <option value="FC">Hạng FC (Container)</option>
                        <option value="B2">Hạng B2</option>
                    </select>
                </div>
                <div class="form-group" style="flex:1">
                    <label class="lbl">Ngày hết hạn (*)</label>
                    <input type="date" name="ngay_hethan" class="inp" required title="Hệ thống sẽ tự khóa nếu hết hạn">
                </div>
            </div>

            <div class="row-2">
                <div class="form-group" style="flex:1">
                    <label class="lbl">Sức khỏe</label>
                    <select name="suc_khoe" class="sel">
                        <option value="Dat">Đạt chuẩn</option>
                        <option value="Khong_Dat">Không đạt</option>
                    </select>
                </div>
                <div class="form-group" style="flex:1">
                    <label class="lbl">Ngày khám</label>
                    <input type="date" name="ngay_kham" class="inp">
                </div>
            </div>

            <button type="submit" class="btn btn-add"><i class="fa-solid fa-user-plus"></i> LƯU HỒ SƠ</button>
        </form>
    </div>

    <div class="panel">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h3>🛡️ Quản Lý & Đánh Giá Tài Xế</h3>
            <span style="font-size:12px; color:#64748b">
                <i class="fa-solid fa-robot"></i> Hệ thống tự động kiểm tra GPLX & Sức khỏe
            </span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tài Xế / Pháp Lý</th>
                    <th>Trạng Thái & Hiệu Suất</th>
                    <th>Đánh Giá / Cập Nhật</th>
                    <th>Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($drivers as $d): 
                    $is_expired = (strtotime($d['ngay_hethan_banglai']) < time());
                    $expire_msg = $is_expired ? "<br><span class='alert-expiry'><i class='fa-solid fa-triangle-exclamation'></i> Hết hạn bằng!</span>" : "";
                ?>
                <tr>
                    <td>
                        <b><?= $d['ho_ten'] ?></b> (<?= $d['bang_lai'] ?>)<br>
                        <small>SĐT: <?= $d['sdt'] ?></small><br>
                        <small style="color:#666">Hạn bằng: <?= date('d/m/Y', strtotime($d['ngay_hethan_banglai'])) ?></small>
                        <?= $expire_msg ?>
                    </td>
                    <td>
                        <?php 
                        if($d['trang_thai'] == 'San_sang') echo '<span class="badge st-ready">Sẵn sàng</span>';
                        elseif($d['trang_thai'] == 'Dang_ban') echo '<span class="badge st-busy">Đang chạy</span>';
                        elseif($d['trang_thai'] == 'Bi_khoa') echo '<span class="badge st-lock">BỊ KHÓA</span>';
                        else echo '<span class="badge" style="background:#eee">Nghỉ phép</span>';
                        
                        if($d['trang_thai'] == 'Bi_khoa') {
                            echo "<br><small style='color:red'>Lý do: {$d['ly_do_khoa']}</small>";
                        }
                        ?>
                        <div style="margin-top:5px; font-size:12px;">
                            Điểm tin nhiệm: <b style="<?= $d['diem_tin_nhiem']<80 ? 'color:red':'color:green' ?>"><?= $d['diem_tin_nhiem'] ?>/100</b>
                        </div>
                    </td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="act" value="update_status">
                            <input type="hidden" name="ma_tx" value="<?= $d['ma_tai_xe'] ?>">
                            
                            <div style="display:flex; gap:5px; margin-bottom:5px;">
                                <select name="trang_thai" class="sel" style="font-size:11px; padding:2px;">
                                    <option value="San_sang" <?= $d['trang_thai']=='San_sang'?'selected':'' ?>>Sẵn sàng</option>
                                    <option value="Nghi_phep" <?= $d['trang_thai']=='Nghi_phep'?'selected':'' ?>>Nghỉ phép</option>
                                    <option value="Bi_khoa" <?= $d['trang_thai']=='Bi_khoa'?'selected':'' ?>>KHÓA (Kỷ luật)</option>
                                </select>
                                <select name="suc_khoe" class="sel" style="font-size:11px; padding:2px; width:80px">
                                    <option value="Dat" <?= $d['suc_khoe']=='Dat'?'selected':'' ?>>SK Tốt</option>
                                    <option value="Khong_Dat" <?= $d['suc_khoe']=='Khong_Dat'?'selected':'' ?>>SK Yếu</option>
                                </select>
                            </div>

                            <textarea name="vi_pham" class="inp" rows="1" placeholder="Ghi chú vi phạm/thưởng..." style="font-size:11px; margin-bottom:5px;"><?= $d['lich_su_vi_pham'] ?></textarea>
                            
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <input type="number" name="diem" value="<?= $d['diem_tin_nhiem'] ?>" style="width:50px; font-size:11px;" title="Điểm tín nhiệm">
                                <button type="submit" class="btn btn-save">Cập nhật</button>
                            </div>
                        </form>
                    </td>
                    <td style="text-align:center;">
                        <a href="?page=taixe&del=<?= $d['ma_tai_xe'] ?>" onclick="return confirm('Xóa hồ sơ này?')" style="color:#ef4444; font-size:16px;">
                            <i class="fa-solid fa-trash-can"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>