<?php
if (!isset($conn)) { if (file_exists('db.php')) require_once 'db.php'; }
$user = $_SESSION['full_name'] ?? 'Admin';

// 1. LẤY DỮ LIỆU SẢN PHẨM & DANH MỤC
try {
    $products = $conn->query("SELECT * FROM sanpham WHERE so_luong > 0 ORDER BY ten_sp")->fetchAll(PDO::FETCH_ASSOC);
    $types = $conn->query("SELECT DISTINCT loai_hang FROM sanpham WHERE so_luong > 0 AND loai_hang != '' ORDER BY loai_hang")->fetchAll(PDO::FETCH_COLUMN);
    $units = $conn->query("SELECT DISTINCT don_vi FROM sanpham WHERE so_luong > 0 AND don_vi != '' ORDER BY don_vi")->fetchAll(PDO::FETCH_COLUMN);
} catch(Exception $e) { $products = []; $types = []; $units = []; }

// --- A. XỬ LÝ LƯU PHIẾU XUẤT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['act']) && $_POST['act'] == 'save_export') {
    try {
        $conn->beginTransaction();
        $ma_phieu = "PX-" . date("YmdHis");
        
        // Header
        $stmt = $conn->prepare("INSERT INTO phieu_xuat (ma_phieu, nguoi_nhan, nguoi_tao, ghi_chu) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ma_phieu, $_POST['nguoi_nhan'], $user, $_POST['ghi_chu']]);
        $id_phieu = $conn->lastInsertId();

        // Detail
        $stmtLine = $conn->prepare("INSERT INTO ct_phieu_xuat (phieu_xuat_id, ma_sp, so_luong) VALUES (?, ?, ?)");
        $stmtUp = $conn->prepare("UPDATE sanpham SET so_luong = so_luong - ? WHERE ma_sp = ?");
        
        $exportData = []; 
        if (!empty($_POST['items'])) {
            foreach ($_POST['items'] as $ma_sp => $sl) {
                if($sl > 0) {
                    $stmtLine->execute([$id_phieu, $ma_sp, $sl]);
                    $stmtUp->execute([$sl, $ma_sp]);
                    // Lấy thêm tên sản phẩm để in Excel cho đẹp
                    $ten_sp = "";
                    foreach($products as $p) { if($p['ma_sp'] == $ma_sp) { $ten_sp = $p['ten_sp']; break; } }
                    
                    $exportData[] = ['ma' => $ma_sp, 'ten' => $ten_sp, 'sl' => $sl];
                }
            }
        }
        $conn->commit();
        
        // Dữ liệu JSON gửi xuống JS
        $jsonExport = json_encode($exportData);
        $jsonInfo = json_encode([
            'ma' => $ma_phieu, 
            'kh' => $_POST['nguoi_nhan'], 
            'ngay' => date('d/m/Y'),
            'nguoi_tao' => $user
        ]);

        echo "<script>
            alert('✅ Tạo phiếu xuất $ma_phieu thành công!');
            setTimeout(function() { exportWithExcelJS($jsonInfo, $jsonExport); }, 500);
        </script>";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>alert('❌ Lỗi: " . $e->getMessage() . "');</script>";
    }
}

// --- B. LẤY LỊCH SỬ ---
// (Giữ nguyên logic cũ)
$sqlBase = "SELECT p.*, COUNT(c.id) as sl_mat_hang, SUM(c.so_luong) as tong_so_luong 
            FROM phieu_xuat p LEFT JOIN ct_phieu_xuat c ON p.id = c.phieu_xuat_id WHERE 1=1";
$params = [];
if (!empty($_GET['k_hist'])) { $sqlBase .= " AND (p.ma_phieu LIKE ? OR p.nguoi_nhan LIKE ?)"; $params[] = "%".$_GET['k_hist']."%"; $params[] = "%".$_GET['k_hist']."%"; }
if (!empty($_GET['d_from'])) { $sqlBase .= " AND p.ngay_xuat >= ?"; $params[] = $_GET['d_from'] . " 00:00:00"; }
if (!empty($_GET['d_to'])) { $sqlBase .= " AND p.ngay_xuat <= ?"; $params[] = $_GET['d_to'] . " 23:59:59"; }
$sqlBase .= " GROUP BY p.id ORDER BY p.ngay_xuat DESC LIMIT 50";
try { $stmtHist = $conn->prepare($sqlBase); $stmtHist->execute($params); $history = $stmtHist->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) { $history = []; }
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<style>
    /* (CSS GIỮ NGUYÊN NHƯ CŨ) */
    .page-wrapper { display: flex; flex-direction: column; gap: 30px; height: 100%; overflow-y: auto; padding-right: 5px; }
    .export-container { display: flex; gap: 20px; height: 600px; }
    .list-panel { width: 40%; background: #fff; border: 1px solid #e2e8f0; display: flex; flex-direction: column; border-radius: 8px; }
    .quick-filter { padding: 10px; background: #f8fafc; border-bottom: 1px solid #eee; display: flex; flex-direction: column; gap: 8px; }
    .q-search { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .q-row { display: flex; gap: 5px; }
    .q-select { flex: 1; padding: 5px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px; }
    .prod-list { flex: 1; overflow-y: auto; padding: 0; }
    .prod-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: 0.1s; }
    .prod-item:hover { background: #f0fdf4; border-left: 4px solid #16a34a; padding-left: 11px; }
    .prod-info b { color: #334155; font-size: 13px; }
    .prod-info div { font-size: 12px; color: #64748b; }
    .prod-stock { text-align: right; }
    .cart-panel { flex: 1; background: #fff; border: 1px solid #e2e8f0; display: flex; flex-direction: column; padding: 20px; border-radius: 8px; border-top: 4px solid #16a34a; }
    .cart-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .cart-table th { background: #dcfce7; padding: 10px; text-align: left; font-size: 13px; }
    .cart-table td { padding: 8px; border-bottom: 1px solid #eee; font-size: 13px; }
    .btn-submit { margin-top: auto; padding: 15px; background: #16a34a; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 5px; font-size: 16px; width: 100%; }
    .kiot-input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
    .history-section { background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 20px; }
    .hist-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    .hist-title { margin: 0; color: #166534; font-size: 18px; }
    .btn-filter { background: #fff; border: 1px solid #ccc; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 5px; }
    .tbl-hist { width: 100%; border-collapse: collapse; }
    .tbl-hist th { background: #f8fafc; padding: 12px; text-align: left; font-size: 13px; font-weight: bold; color: #475569; }
    .tbl-hist td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
    .tag-ok { background: #dcfce7; color: #166534; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: bold; }
    .modal-filter { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-content { background: #fff; width: 400px; padding: 25px; border-radius: 8px; animation: slideDown 0.3s; }
    @keyframes slideDown { from{transform:translateY(-20px);opacity:0} to{transform:translateY(0);opacity:1} }
    .btn-apply { background: #16a34a; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; cursor: pointer; }
</style>

<div class="page-wrapper">
    <form method="POST" class="export-container" id="exportForm">
        <input type="hidden" name="act" value="save_export">
        <div class="list-panel">
            <div class="quick-filter">
                <input type="text" id="qSearch" class="q-search" placeholder="🔍 Gõ mã hoặc tên hàng..." onkeyup="filterClientSide()">
                <div class="q-row">
                    <select id="qLoai" class="q-select" onchange="filterClientSide()"><option value="">-- Nhóm hàng --</option><?php foreach($types as $t) echo "<option value='$t'>$t</option>"; ?></select>
                    <select id="qUnit" class="q-select" onchange="filterClientSide()"><option value="">-- Đơn vị --</option><?php foreach($units as $u) echo "<option value='$u'>$u</option>"; ?></select>
                </div>
            </div>
            <div class="prod-list" id="sourceList">
                <?php foreach($products as $p): ?>
                <div class="prod-item" data-name="<?= strtolower($p['ten_sp']) ?> <?= strtolower($p['ma_sp']) ?>" data-loai="<?= $p['loai_hang'] ?>" data-unit="<?= $p['don_vi'] ?>" onclick="addToCart('<?= $p['ma_sp'] ?>', '<?= htmlspecialchars($p['ten_sp'], ENT_QUOTES) ?>', <?= $p['so_luong'] ?>)">
                    <div class="prod-info"><b><?= $p['ma_sp'] ?></b><div><?= $p['ten_sp'] ?></div><div style="font-size:11px; color:#999"><?= $p['loai_hang'] ?> - <?= $p['don_vi'] ?></div></div>
                    <div class="prod-stock"><span class="tag-ok">Tồn: <?= number_format($p['so_luong']) ?></span></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="cart-panel">
            <h3 style="margin:0 0 15px 0; color:#166534">📤 Phiếu Xuất Kho</h3>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom: 10px;">
                <div><label style="font-size:12px; font-weight:bold">Khách hàng / Người nhận</label><input type="text" name="nguoi_nhan" class="kiot-input" required></div>
                <div><label style="font-size:12px; font-weight:bold">Ghi chú</label><input type="text" name="ghi_chu" class="kiot-input"></div>
            </div>
            <div style="flex:1; overflow-y:auto; border:1px solid #eee; border-radius:5px;">
                <table class="cart-table">
                    <thead><tr><th>Mã SP</th><th>Tên hàng</th><th width="80">SL Xuất</th><th width="30"></th></tr></thead>
                    <tbody id="cartBody"></tbody>
                </table>
            </div>
            <button type="submit" class="btn-submit"><i class="fa-solid fa-file-export"></i> LƯU & XUẤT EXCEL</button>
        </div>
    </form>

    <div class="history-section">
        <div class="hist-header">
            <h3 class="hist-title"><i class="fa-solid fa-clock-rotate-left"></i> Lịch Sử Xuất Kho</h3>
            <button class="btn-filter" onclick="document.getElementById('filterModal').style.display='flex'">
                <i class="fa-solid fa-filter"></i> Bộ lọc lịch sử
                <?php if(!empty($_GET['k_hist']) || !empty($_GET['d_from'])) echo "<span style='width:8px; height:8px; background:red; border-radius:50%;'></span>"; ?>
            </button>
        </div>
        <table class="tbl-hist">
            <thead><tr><th>Mã Phiếu</th><th>Ngày Xuất</th><th>Người Nhận</th><th>Người Tạo</th><th>SL M.Hàng</th><th>Tổng SL</th><th>Ghi Chú</th></tr></thead>
            <tbody>
                <?php if(count($history) > 0): foreach($history as $h): ?>
                <tr>
                    <td><b style="color:#e11d48"><?= $h['ma_phieu'] ?></b></td>
                    <td><?= date('d/m/Y H:i', strtotime($h['ngay_xuat'])) ?></td>
                    <td><?= htmlspecialchars($h['nguoi_nhan']) ?></td>
                    <td><span style="background:#e0f2fe; color:#0284c7; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:bold;"><?= $h['nguoi_tao'] ?></span></td>
                    <td style="text-align:center"><?= number_format($h['sl_mat_hang']) ?></td>
                    <td style="text-align:center; font-weight:bold"><?= number_format($h['tong_so_luong']) ?></td>
                    <td style="color:#666; font-style:italic"><?= htmlspecialchars($h['ghi_chu']) ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7" style="text-align:center; padding:20px; color:#999">Không tìm thấy phiếu xuất nào!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="filterModal" class="modal-filter">
    <div class="modal-content">
        <h3 style="margin-top:0; color:#166534">🔍 Lọc Lịch Sử</h3>
        <form method="GET" action="index.php">
            <input type="hidden" name="page" value="phieuxuat">
            <div style="margin-bottom:10px"><label style="font-size:12px; font-weight:bold">Từ khóa</label><input type="text" name="k_hist" class="kiot-input" value="<?= htmlspecialchars($_GET['k_hist']??'') ?>" placeholder="Mã phiếu..."></div>
            <div style="display:flex; gap:10px; margin-bottom:15px">
                <div style="flex:1"><label style="font-size:12px; font-weight:bold">Từ ngày</label><input type="date" name="d_from" class="kiot-input" value="<?= $_GET['d_from']??'' ?>"></div>
                <div style="flex:1"><label style="font-size:12px; font-weight:bold">Đến ngày</label><input type="date" name="d_to" class="kiot-input" value="<?= $_GET['d_to']??'' ?>"></div>
            </div>
            <div style="text-align:right">
                <button type="button" onclick="document.getElementById('filterModal').style.display='none'" style="background:#eee; border:none; padding:10px; cursor:pointer; border-radius:5px; margin-right:5px;">Đóng</button>
                <button type="submit" class="btn-apply">Áp dụng</button>
            </div>
        </form>
    </div>
</div>

<script>
    // 1. LỌC CLIENT SIDE
    function filterClientSide() {
        let txt = document.getElementById('qSearch').value.toLowerCase();
        let loai = document.getElementById('qLoai').value;
        let unit = document.getElementById('qUnit').value;
        let items = document.getElementsByClassName('prod-item');
        for (let i = 0; i < items.length; i++) {
            let item = items[i];
            let name = item.getAttribute('data-name');
            let l = item.getAttribute('data-loai');
            let u = item.getAttribute('data-unit');
            if (name.includes(txt) && (loai===""||l===loai) && (unit===""||u===unit)) item.style.display = ""; else item.style.display = "none";
        }
    }

    // 2. THÊM VÀO GIỎ
    function addToCart(ma, ten, ton) {
        if(document.getElementById('row_'+ma)) { alert('Sản phẩm đã chọn!'); return; }
        const tr = document.createElement('tr');
        tr.id = 'row_' + ma;
        tr.innerHTML = `<td>${ma}</td><td style="font-size:12px">${ten}</td><td><input type="number" name="items[${ma}]" class="kiot-input" value="1" min="1" max="${ton}" style="padding:5px; font-weight:bold; color:red"></td><td style="color:red; cursor:pointer" onclick="this.parentElement.remove()">×</td>`;
        document.getElementById('cartBody').appendChild(tr);
    }

    // 3. XUẤT EXCEL CHUYÊN NGHIỆP (CÓ LOGO VÀ CHỮ KÝ)
    async function exportWithExcelJS(info, items) {
        const workbook = new ExcelJS.Workbook();
        const sheet = workbook.addWorksheet('PhieuXuatKho');

        // A. CẤU HÌNH CỘT
        sheet.columns = [
            { header: '', key: 'A', width: 5 },
            { header: '', key: 'B', width: 20 },
            { header: '', key: 'C', width: 35 },
            { header: '', key: 'D', width: 15 },
        ];

        // B. THÊM LOGO (Thay chuỗi base64 này bằng logo công ty của bạn)
        // Đây là hình icon cái hộp demo, bạn hãy vào trang 'base64-image.de' để lấy mã logo của bạn
        const logoBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAABmJLR0QA/wD/AP+gvaeTAAAAbklEQVRoge3QwQ2AMAwEwUzs0F6k0A2kQAu0QAn8E0Vw5Cw+ST57tqZ5ZmYfF8hI1s7Wzla2drd2tna2srW7tbO1s5Wt3a2drZ2tbO1u7WztbGVrd2tna2crW7tbO1s7W9na3drZ2tnK1u7WztbO1g8+bwcx4588uAAAAABJRU5ErkJggg==';
        
        const imageId = workbook.addImage({
            base64: logoBase64,
            extension: 'png',
        });
        // Chèn logo vào góc trái trên cùng
        sheet.addImage(imageId, {
            tl: { col: 0, row: 0 },
            ext: { width: 50, height: 50 }
        });

        // C. HEADER THÔNG TIN CÔNG TY (Gộp ô)
        sheet.mergeCells('B1:D1'); 
        sheet.getCell('B1').value = "CÔNG TY TNHH GREENWAY LOGISTICS";
        sheet.getCell('B1').font = { bold: true, size: 14, color: { argb: '14532d' } };

        sheet.mergeCells('B2:D2'); 
        sheet.getCell('B2').value = "Đ/c: 12/10/83 Nguyễn Bình, Đổng Quốc Bình, Ngô Quyền, Hải Phòng";
        
        // Tiêu đề phiếu
        sheet.mergeCells('A4:D4');
        sheet.getCell('A4').value = "PHIẾU XUẤT KHO";
        sheet.getCell('A4').alignment = { horizontal: 'center' };
        sheet.getCell('A4').font = { bold: true, size: 18 };

        // Thông tin phiếu
        sheet.addRow([]); // Dòng trống
        sheet.addRow(['', 'Mã Phiếu:', info.ma]);
        sheet.addRow(['', 'Ngày Xuất:', info.ngay]);
        sheet.addRow(['', 'Khách Hàng:', info.kh]);
        sheet.addRow(['', 'Người Lập:', info.nguoi_tao]);

        sheet.addRow([]); // Dòng trống

        // D. BẢNG HÀNG HÓA
        // Header bảng
        sheet.addRow(['STT', 'Mã Hàng', 'Tên Sản Phẩm', 'Số Lượng']);
        const headerRow = sheet.lastRow;
        headerRow.eachCell((cell) => {
            cell.font = { bold: true, color: { argb: 'FFFFFF' } };
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: '16a34a' } };
            cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
        });

        // Dữ liệu hàng hóa
        let stt = 1;
        items.forEach(item => {
            sheet.addRow([stt++, item.ma, item.ten, Number(item.sl)]);
            // Kẻ khung cho dòng vừa thêm
            sheet.lastRow.eachCell((cell) => {
                cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
            });
        });

        // E. PHẦN CHỮ KÝ (FOOTER)
        sheet.addRow([]);
        sheet.addRow([]);
        
        const signRowIndex = sheet.rowCount + 1;
        sheet.addRow(['', 'Người Lập Phiếu', 'Thủ Kho', 'Người Nhận Hàng']);
        sheet.lastRow.font = { bold: true };
        sheet.lastRow.alignment = { horizontal: 'center' };
        
        sheet.addRow(['', '(Ký, họ tên)', '(Ký, họ tên)', '(Ký, họ tên)']);
        sheet.lastRow.font = { italic: true, size: 10 };
        sheet.lastRow.alignment = { horizontal: 'center' };

        // Xuất file
        const buffer = await workbook.xlsx.writeBuffer();
        saveAs(new Blob([buffer]), info.ma + ".xlsx");
        
        // Reload trang
        setTimeout(() => { window.location.href = '?page=phieuxuat'; }, 2000);
    }
</script>