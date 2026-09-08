<?php
// 1. KẾT NỐI DB
if (!isset($conn)) { 
    if (file_exists('db.php')) require_once 'db.php'; 
    else die("Lỗi: Không tìm thấy file kết nối db.php!"); 
}
$user = $_SESSION['full_name'] ?? 'Admin';

// 2. XỬ LÝ LƯU (PHP)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['act']) && $_POST['act'] == 'save') {
    try {
        if (empty($_POST['items'])) throw new Exception("Chưa có hàng hóa nào!");

        $conn->beginTransaction();
        
        // A. Tạo Header
        $ma_phieu = "PN-" . date("ymdHis");
        $stmt = $conn->prepare("INSERT INTO phieu_nhap (ma_phieu, nguoi_giao, nguoi_tao, ghi_chu) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ma_phieu, $_POST['nguoi_giao'], $user, $_POST['ghi_chu']]);
        $id_phieu = $conn->lastInsertId();

        // B. SQL Chi tiết & Kho
        $stmtDetail = $conn->prepare("INSERT INTO ct_phieu_nhap (phieu_nhap_id, ma_sp, so_luong, don_gia) VALUES (?, ?, ?, ?)");
        $updateSP = $conn->prepare("UPDATE sanpham SET so_luong = so_luong + ? WHERE ma_sp = ?");
        $insertSP = $conn->prepare("INSERT INTO sanpham (ma_sp, ten_sp, loai_hang, don_vi, so_luong, ngay_tao) VALUES (?, ?, 'Hàng Mới', 'Cái', ?, NOW())");

        // C. Duyệt hàng
        foreach ($_POST['items'] as $item) {
            $ma = trim($item['ma']); $ten = trim($item['ten']);
            $sl = (int)$item['sl']; $gia = (float)$item['gia'];

            if ($sl > 0 && !empty($ma)) {
                $stmtDetail->execute([$id_phieu, $ma, $sl, $gia]);
                
                // Update Kho (Cộng dồn hoặc Tạo mới)
                $updateSP->execute([$sl, $ma]);
                if ($updateSP->rowCount() == 0) $insertSP->execute([$ma, $ten, $sl]);
            }
        }
        
        $conn->commit();
        
        // --- XỬ LÝ CHUYỂN HƯỚNG (QUAN TRỌNG) ---
        // Chuyển hướng về chính trang này kèm tham số ?status=success
        // Để trình duyệt biết là đã xong, không hỏi lại khi F5
        echo "<script>window.location.href = '?page=phieunhap&status=success';</script>";
        exit;
        
    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        echo "<script>alert('❌ Lỗi: " . $e->getMessage() . "');</script>";
    }
}

// 3. LẤY LỊCH SỬ
try {
    $sql = "SELECT p.*, COUNT(c.id) as sl_mat_hang, SUM(c.so_luong) as tong_so_luong 
            FROM phieu_nhap p LEFT JOIN ct_phieu_nhap c ON p.id = c.phieu_nhap_id
            GROUP BY p.id ORDER BY p.ngay_nhap DESC LIMIT 10";
    $history = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $history = []; }
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<style>
    /* CSS Giao diện */
    .wrap { display: flex; gap: 20px; font-family: 'Segoe UI', sans-serif; }
    .col-left { width: 30%; background: #fff; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; height: fit-content; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .col-right { width: 70%; background: #fff; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    
    label { font-weight: 600; font-size: 13px; color: #374151; display: block; margin-bottom: 5px; }
    input, textarea { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; margin-bottom: 15px; font-size: 14px; }
    input:focus, textarea:focus { outline: none; border-color: #10b981; box-shadow : 2px solid #10b981; }

    #drop-area { border: 2px dashed #3b82f6; background: #eff6ff; padding: 30px; text-align: center; cursor: pointer; margin-bottom: 15px; border-radius: 8px; transition: 0.2s; }
    #drop-area:hover { background: #dbeafe; border-color: #2563eb; }

    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th { background: #f3f4f6; text-align: left; padding: 10px; border-bottom: 2px solid #e5e7eb; color: #4b5563; }
    td { padding: 8px; border-bottom: 1px solid #f3f4f6; color: #1f2937; }
    
    .inp-table { border: 1px solid transparent; width: 100%; padding: 6px; border-radius: 4px; }
    .inp-table:focus { background: #fff; border-color: #3b82f6; outline: none; }

    .btn { width: 100%; padding: 12px; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; transition: 0.2s; }
    .btn:hover { background: #059669; }

    /* CSS CHO TOAST NOTIFICATION (THÔNG BÁO TỰ BAY) */
    #toast {
        visibility: hidden; /* Mặc định ẩn */
        min-width: 300px;
        background-color: #10b981; /* Màu xanh thành công */
        color: #fff;
        text-align: center;
        border-radius: 8px;
        padding: 16px;
        position: fixed;
        z-index: 9999;
        right: 30px;
        bottom: 30px;
        font-size: 16px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        opacity: 0;
        transition: opacity 0.5s, bottom 0.5s;
    }

    #toast.show {
        visibility: visible;
        opacity: 1;
        bottom: 50px; /* Hiệu ứng trượt lên */
    }
</style>

<div id="toast"><i class="fa-solid fa-circle-check"></i> Nhập kho thành công!</div>

<form method="POST" class="wrap" action="">
    <input type="hidden" name="act" value="save">

    <div class="col-left">
        <h3 style="margin-top:0; color:#111827">1. Thông Tin Phiếu</h3>
        <label>Người giao hàng</label>
        <input type="text" name="nguoi_giao" required placeholder="Tên tài xế / Đơn vị giao...">
        
        <label>Ghi chú nhập</label>
        <textarea name="ghi_chu" rows="4"></textarea>
        
        <hr style="border:0; border-top:1px solid #e5e7eb; margin:20px 0">
        <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> LƯU & CẬP NHẬT KHO</button>
    </div>

    <div class="col-right">
        <h3 style="margin-top:0; color:#111827">2. Chi Tiết Hàng Hóa</h3>
        
        <div id="drop-area">
            <i class="fa-solid fa-file-excel" style="font-size:30px; color:#3b82f6; margin-bottom:10px"></i><br>
            <strong>Kéo thả file Excel vào đây</strong><br>
            <small style="color:#6b7280">(Cột A: Mã, Cột B: Tên, Cột C: Số lượng, Cột D: Giá)</small>
            <input type="file" id="fileInput" style="display:none" accept=".xlsx, .xls">
        </div>

        <div style="height: 350px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius:6px;">
            <table>
                <thead>
                    <tr>
                        <th width="120">Mã SP</th>
                        <th>Tên SP</th>
                        <th width="80">Số lượng</th>
                        <th width="100">Đơn giá</th>
                        <th width="30"></th>
                    </tr>
                </thead>
                <tbody id="tblBody"></tbody>
            </table>
        </div>
        
        <div style="margin-top:10px; display:flex; justify-content:space-between">
            <button type="button" onclick="addRow('', '', 1, 0)" style="width:auto; cursor:pointer; background:#fff; border:1px solid #d1d5db; color:#374151; padding:8px 15px; border-radius:6px;">+ Thêm dòng</button>
            <span style="font-size:12px; color:#ef4444; font-style:italic; align-self:center">* Tự động tạo mã mới nếu chưa có.</span>
        </div>
    </div>
</form>

<div style="background:#fff; padding:20px; border:1px solid #e5e7eb; margin-top:20px; border-radius:8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <h3 style="margin-top:0">Lịch Sử Nhập Gần Đây</h3>
    <table>
        <thead>
            <tr>
                <th>Mã Phiếu</th>
                <th>Ngày Nhập</th>
                <th>Người Giao</th>
                <th>Người Tạo</th>
                <th>Tổng SL</th>
                <th>Ghi Chú</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($history as $h): ?>
            <tr>
                <td><b style="color:#10b981"><?= $h['ma_phieu'] ?></b></td>
                <td><?= date('d/m/Y H:i', strtotime($h['ngay_nhap'])) ?></td>
                <td><?= $h['nguoi_giao'] ?></td>
                <td><span style="background:#eff6ff; color:#2563eb; padding:2px 8px; border-radius:10px; font-size:12px; font-weight:bold"><?= $h['nguoi_tao'] ?></span></td>
                <td style="font-weight:bold"><?= number_format($h['tong_so_luong']) ?></td>
                <td style="color:#6b7280"><?= $h['ghi_chu'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    // --- 1. XỬ LÝ TOAST NOTIFICATION (THÔNG BÁO TỰ BAY) ---
    // Kiểm tra URL xem có ?status=success không
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status') && urlParams.get('status') === 'success') {
        showToast();
        // Xóa param trên URL để F5 không hiện lại
        window.history.replaceState({}, document.title, window.location.pathname + "?page=phieunhap");
    }

    function showToast() {
        var x = document.getElementById("toast");
        x.className = "show"; // Hiện lên
        setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000); // Ẩn sau 3s
    }

    // --- 2. XỬ LÝ KÉO THẢ EXCEL (GIỮ NGUYÊN) ---
    const dropArea = document.getElementById('drop-area');
    const fileInp = document.getElementById('fileInput');

    dropArea.onclick = () => fileInp.click();
    dropArea.ondragover = (e) => { e.preventDefault(); dropArea.style.background = '#dbeafe'; dropArea.style.borderColor = '#2563eb'; };
    dropArea.ondragleave = () => { dropArea.style.background = '#eff6ff'; dropArea.style.borderColor = '#3b82f6'; };
    dropArea.ondrop = (e) => {
        e.preventDefault(); dropArea.style.background = '#eff6ff';
        if(e.dataTransfer.files.length) readExcel(e.dataTransfer.files[0]);
    };
    fileInp.onchange = (e) => { if(fileInp.files.length) readExcel(fileInp.files[0]); };

    function readExcel(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, {type: 'array'});
            const sheet = workbook.Sheets[workbook.SheetNames[0]];
            const json = XLSX.utils.sheet_to_json(sheet, {header: 1});
            
            document.getElementById('tblBody').innerHTML = '';
            for(let i=1; i<json.length; i++) {
                let row = json[i];
                if(row[0]) addRow(row[0], row[1]||'', row[2]||0, row[3]||0);
            }
        };
        reader.readAsArrayBuffer(file);
    }

    let idx = 0;
    function addRow(ma, ten, sl, gia) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input name="items[${idx}][ma]" value="${ma}" class="inp-table" placeholder="Mã" required style="font-weight:bold"></td>
            <td><input name="items[${idx}][ten]" value="${ten}" class="inp-table" placeholder="Tên"></td>
            <td><input type="number" name="items[${idx}][sl]" value="${sl}" class="inp-table" style="color:#10b981; font-weight:bold"></td>
            <td><input type="number" name="items[${idx}][gia]" value="${gia}" class="inp-table"></td>
            <td onclick="this.parentElement.remove()" style="color:#ef4444; cursor:pointer; text-align:center; font-size:18px">×</td>
        `;
        document.getElementById('tblBody').appendChild(tr);
        idx++;
    }
</script>
