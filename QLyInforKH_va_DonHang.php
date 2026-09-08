<?php
// QLyInfoKH_va_DonHang.php
require_once 'qlyKH-db.php';

// Mảng chứa các lỗi phát sinh
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- Xử lý Thêm Khách hàng ---
    if ($action === 'add_customer') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name === '') $errors[] = 'Tên khách hàng không được để trống.';

        if (empty($errors)) {
            if (add_customer($name, $email, $phone, $address)) {
                // Redirect để tránh gửi lại form
                header('Location: ' . $_SERVER['PHP_SELF']); 
                exit;
            } else {
                $errors[] = 'Lỗi khi thêm khách hàng vào CSDL.';
            }
        }
    }

    // --- Xử lý Thêm Đơn hàng ---
    if ($action === 'add_order') {
        $customer_id = intval($_POST['customer_id'] ?? 0);
        $route_from = trim($_POST['route_from'] ?? '');
        $route_to = trim($_POST['route_to'] ?? '');
        $product_name = trim($_POST['product_name'] ?? '');
        $weight = $_POST['weight'] ?? 0;
        $cargo_type = trim($_POST['cargo_type'] ?? '');
        $status = trim($_POST['status'] ?? 'Pending');
        $price = $_POST['price'] ?? 0;

        if ($customer_id <= 0) $errors[] = 'Vui lòng chọn khách hàng.';
        if ($route_from === '' || $route_to === '') $errors[] = 'Nhập đầy đủ tuyến lộ trình.';
        if ($product_name === '') $errors[] = 'Tên sản phẩm không được rỗng.';

        if (empty($errors)) {
            if (add_order($customer_id, $route_from, $route_to, $product_name, $weight, $cargo_type, $status, $price)) {
                // Redirect để tránh gửi lại form
                header('Location: ' . $_SERVER['PHP_SELF']); 
                exit;
            } else {
                 $errors[] = 'Lỗi khi thêm đơn hàng vào CSDL.';
            }
        }
    }

    // --- Xử lý Cập nhật trạng thái ---
    if ($action === 'update_status') {
        $order_id = intval($_POST['order_id'] ?? 0);
        $new_status = trim($_POST['new_status'] ?? '');
        
        if (update_order_status($order_id, $new_status)) {
            header('Location: ' . $_SERVER['PHP_SELF']); 
            exit;
        } else {
            $errors[] = 'Lỗi khi cập nhật trạng thái đơn hàng.';
        }
    }
}

// Lấy danh sách khách hàng và đơn hàng
$customers = get_all_customers();
$orders = get_orders_with_customer_name();



?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý khách hàng & đơn hàng vận tải</title>
    <style>
    /* CSS Native - Giữ nguyên như file gốc */
    body {
        font-family: Arial, Helvetica, sans-serif;
        margin: 20px;
        background: #f5f7fb
    }

    a {
        text-decoration: none;
    }

    h1 {
        margin-bottom: 10px
    }

    .container {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 20px
    }

    @media (max-width: 1024px) {
        .container {
            grid-template-columns: 1fr;
        }
    }

    .card {
        background: white;
        padding: 16px;
        border-radius: 8px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08)
    }

    label {
        display: block;
        margin-top: 8px;
        font-weight: 600
    }

    input[type=text],
    input[type=email],
    input[type=number],
    input[type=date],
    select,
    textarea {
        width: 100%;
        padding: 8px;
        margin-top: 6px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px
    }

    th,
    td {
        padding: 8px;
        border-bottom: 1px solid #e8eef5;
        text-align: left
    }

    .table-scroll {
        overflow-x: auto;
    }

    th {
        background: #f0f4fa;
        position: sticky;
        top: 0;
    }

    .small {
        font-size: 0.9em;
        color: #666
    }

    .status-Pending {
        color: #b8860b;
        font-weight: bold;
    }

    .status-InTransit {
        color: #1e90ff;
        font-weight: bold;
    }

    .status-Delivered {
        color: #2e8b57;
        font-weight: bold;
    }

    .status-Cancelled {
        color: #b22222;
        font-weight: bold;
    }

    .error {
        color: white;
        background: #ef4444;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .actions {
        display: flex;
        gap: 8px
    }

    .btn {
        padding: 8px 12px;
        border-radius: 6px;
        background: #4f46e5;
        color: white;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn:hover {
        background: #4338ca;
    }

    .back-btn {
        display: inline-block;
        margin-bottom: 15px;
        padding: 8px 12px;
        background: #374151;
        color: white;
        border-radius: 6px;
    }
    </style>
</head>

<body>
   
    

    <?php if (!empty($errors)): ?>
    <div class="error">
        <?php foreach ($errors as $err) echo '<div>⚠️ '.e($err).'</div>'; ?>
    </div>
    <?php endif; ?>

    <div class="container">
        <div class="card">
            <h2>Thêm khách hàng</h2>
            <form method="post" action="">
                <input type="hidden" name="action" value="add_customer">
                <label>Tên</label>
                <input type="text" name="name" required>

                <label>Email</label>
                <input type="email" name="email">

                <label>Điện thoại</label>
                <input type="text" name="phone">

                <label>Địa chỉ</label>
                <textarea name="address" rows="3"></textarea>

                <div style="margin-top:15px">
                    <button class="btn" type="submit">Lưu khách hàng</button>
                </div>
            </form>

            <h3 style="margin-top:25px">Danh sách khách hàng</h3>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tên</th>
                            <th>Điện thoại</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; color:#9ca3af">Chưa có khách hàng nào được thêm.
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><?php echo e($c['id']); ?></td>
                            <td><?php echo e($c['name']); ?><div class="small"><?php echo e($c['address']); ?></div>
                            </td>
                            <td><?php echo e($c['phone']); ?></td>
                            <td><?php echo e($c['email']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2>Thêm đơn hàng vận tải</h2>
            <form method="post" action="">
                <input type="hidden" name="action" value="add_order">

                <label>Khách hàng</label>
                <select name="customer_id" required>
                    <option value="">-- Chọn khách hàng --</option>
                    <?php foreach ($customers as $c): ?>
                    <option value="<?php echo e($c['id']); ?>"><?php echo e($c['name']); ?> -
                        <?php echo e($c['phone']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Tuyến - Từ</label>
                <input type="text" name="route_from" required placeholder="Ví dụ: Hà Nội">

                <label>Tuyến - Đến</label>
                <input type="text" name="route_to" required placeholder="Ví dụ: Hồ Chí Minh">

                <label>Tên sản phẩm</label>
                <input type="text" name="product_name" required>

                <label>Khối lượng (kg)</label>
                <input type="number" step="0.01" name="weight" value="0">

                <label>Loại hàng</label>
                <input type="text" name="cargo_type" placeholder="Ví dụ: Hàng xi măng, Hàng lạnh...">

                <label>Tình trạng</label>
                <select name="status">
                    <option value="Pending">Pending</option>
                    <option value="InTransit">InTransit</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancelled">Cancelled</option>
                </select>

                <label>Giá tiền (VND)</label>
                <input type="number" step="0.01" name="price" value="0">

                <div style="margin-top:15px"><button class="btn" type="submit">Thêm đơn hàng</button></div>
            </form>

            <h3 style="margin-top:25px">Theo dõi đơn hàng</h3>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Khách hàng</th>
                            <th>Tuyến</th>
                            <th>Sản phẩm</th>
                            <th>Khối lượng(kg)</th>
                            <th>Loại hàng</th>
                            <th>Giá</th>
                            <th>Tình trạng</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="9" style="text-align:center; color:#9ca3af">Chưa có đơn hàng nào được tạo.</td>
                        </tr>
                        <?php endif; ?>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><?php echo e($o['id']); ?></td>
                            <td><?php echo e($o['customer_name']); ?></td>
                            <td><?php echo e($o['route_from']); ?> → <?php echo e($o['route_to']); ?></td>
                            <td><?php echo e($o['product_name']); ?></td>
                            <td><?php echo e($o['weight']); ?></td>
                            <td><?php echo e($o['cargo_type']); ?></td>
                            <td><?php echo e(number_format($o['price'], 0, ',', '.')); ?></td>
                            <td class="<?php echo get_status_class($o['status']); ?>">
                                <?php echo e($o['status']); ?></td>
                            <td>
                                <form method="post" style="display:flex; align-items:center; gap:5px;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?php echo e($o['id']); ?>">
                                    <select name="new_status">
                                        <?php 
                                            $statuses = ['Pending', 'InTransit', 'Delivered', 'Cancelled'];
                                            foreach($statuses as $s) {
                                                $selected = ($s === $o['status']) ? 'selected' : '';
                                                echo "<option value='".e($s)."' $selected>".e($s)."</option>";
                                            }
                                        ?>
                                    </select>
                                    <button class="btn" type="submit" style="padding: 4px 8px; font-size: 0.85em;">Cập
                                        nhật</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>