-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2026 at 03:24 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
 
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
 
START TRANSACTION;
 
SET time_zone = "+00:00";
 
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;
 
--
-- Database: `web_qlvt`
--
 
-- --------------------------------------------------------
 
--
-- Table structure for table `chitietdonhang`
--
 
DROP TABLE IF EXISTS `chitietdonhang`;
 
CREATE TABLE `chitietdonhang` (
    `ctdh_id` int(11) NOT NULL,
    `dh_id` int(11) NOT NULL,
    `ten_san_pham` varchar(150) NOT NULL,
    `khoi_luong` int(11) NOT NULL,
    `gia_don_vi` decimal(10, 2) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
 
-- --------------------------------------------------------
 
--
-- Table structure for table `ct_phieu_nhap`
--
 
DROP TABLE IF EXISTS `ct_phieu_nhap`;
 
CREATE TABLE `ct_phieu_nhap` (
    `id` int(11) NOT NULL,
    `phieu_nhap_id` int(11) NOT NULL,
    `ma_sp` varchar(50) NOT NULL,
    `so_luong` int(11) NOT NULL,
    `don_gia` decimal(15, 2) DEFAULT 0.00
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
 
--
-- Dumping data for table `ct_phieu_nhap`
--
 
INSERT INTO
    `ct_phieu_nhap` (
        `id`,
        `phieu_nhap_id`,
        `ma_sp`,
        `so_luong`,
        `don_gia`
    )
VALUES (1, 1, 'SH92', 200, 0.00);
 
-- --------------------------------------------------------
 
--
-- Table structure for table `ct_phieu_xuat`
--
 
DROP TABLE IF EXISTS `ct_phieu_xuat`;
 
CREATE TABLE `ct_phieu_xuat` (
    `id` int(11) NOT NULL,
    `phieu_xuat_id` int(11) NOT NULL,
    `ma_sp` varchar(50) NOT NULL,
    `so_luong` int(11) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
 
--
-- Dumping data for table `ct_phieu_xuat`
--
 
INSERT INTO
    `ct_phieu_xuat` (
        `id`,
        `phieu_xuat_id`,
        `ma_sp`,
        `so_luong`
    )
VALUES (1, 1, 'SH92', 10),
    (2, 2, 'SH92', 6);
 
-- --------------------------------------------------------
 
--
-- Table structure for table `customers`
--
 
DROP TABLE IF EXISTS `customers`;
 
CREATE TABLE `customers` (
    `id` int(11) NOT NULL,
    `name` varchar(200) NOT NULL,
    `email` varchar(200) DEFAULT NULL,
    `phone` varchar(50) DEFAULT NULL,
    `address` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
 
--
-- Dumping data for table `customers`
--
 
INSERT INTO
    `customers` (
        `id`,
        `name`,
        `email`,
        `phone`,
        `address`,
        `created_at`
    )
VALUES (
        1,
        'Hoàng Phú Hải',
        'hello2005@gmail.com',
        '0987539959',
        '',
        '2025-12-24 22:01:17'
    ),
    (
        2,
        'long',
        'hello2005@gmail.com',
        '0987539959',
        'á',
        '2026-01-03 04:08:11'
    ),
    (
        5,
        'Khánh',
        'khan90@gmail.com',
        '0987543278',
        'Sài Gòn,an',
        '2026-01-03 04:16:23'
    ),
    (
        6,
        'Bình',
        'BinhGold@gmail.com',
        '0972481325',
        'Họ hỌ',
        '2026-01-03 04:19:56'
    ),
    (
        7,
        'Sơn Tùng',
        'MTP123@gmail.com',
        '0978618293',
        'Hà Đông , Hà Nội',
        '2026-01-03 04:20:43'
    ),
    (
        8,
        'Công ty TNHH thể thao EZB',
        'ezb1234@gmail.com',
        '098123848',
        '12/333b/ văn cao',
        '2026-01-04 08:56:08'
    ),
    (
        9,
        'Công ty TNHH thể thao EZB',
        'ezb1234@gmail.com',
        '098123848',
        '12/333b/ văn cao',
        '2026-01-04 12:45:47'
    );
 
-- --------------------------------------------------------
 
--
-- Table structure for table `donhang`
--
 
DROP TABLE IF EXISTS `donhang`;
 
CREATE TABLE `donhang` (
    `dh_id` int(11) NOT NULL,
    `kh_id` int(11) NOT NULL,
    `ngay_dat` date NOT NULL,
    `trang_thai` varchar(50) NOT NULL,
    `tong_tien` decimal(10, 2) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
 
-- --------------------------------------------------------
 
--
-- Table structure for table `hopdong_chi_tiet`
--
 
DROP TABLE IF EXISTS `hopdong_chi_tiet`;
 
CREATE TABLE `hopdong_chi_tiet` (
    `id` int(11) NOT NULL,
    `ma_hd` varchar(50) DEFAULT NULL,
    `ben_ban_info` text DEFAULT NULL,
    `ben_mua_ten` varchar(255) DEFAULT NULL,
    `ben_mua_mst_cccd` varchar(50) DEFAULT NULL,
    `ben_mua_diachi` text DEFAULT NULL,
    `ten_hang_hoa` varchar(255) DEFAULT NULL,
    `khoi_luong_kthuoc` varchar(100) DEFAULT NULL,
    `diem_boc_hang` text DEFAULT NULL,
    `diem_do_hang` text DEFAULT NULL,
    `thoi_gian_du_kien` datetime DEFAULT NULL,
    `bien_so_xe` varchar(20) DEFAULT NULL,
    `cuoc_phi` decimal(15, 2) DEFAULT NULL,
    `phuong_thuc_tt` varchar(50) DEFAULT NULL,
    `trang_thai` enum(
        'Moi_Tao',
        'Da_Ky',
        'Dang_Thuc_Hien',
        'Hoan_Thanh',
        'Huy'
    ) DEFAULT 'Moi_Tao',
    `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
 
--
-- Dumping data for table `hopdong_chi_tiet`
--
 
INSERT INTO
    `hopdong_chi_tiet` (
        `id`,
        `ma_hd`,
        `ben_ban_info`,
        `ben_mua_ten`,
        `ben_mua_mst_cccd`,
        `ben_mua_diachi`,
        `ten_hang_hoa`,
        `khoi_luong_kthuoc`,
        `diem_boc_hang`,
        `diem_do_hang`,
        `thoi_gian_du_kien`,
        `bien_so_xe`,
        `cuoc_phi`,
        `phuong_thuc_tt`,
        `trang_thai`,
        `ngay_tao`
    )
VALUES (
        1,
        'HĐ-20260103-043650',
        NULL,
        'Hoàng Phú Hải',
        '03728917382',
        '12/12/12 Mây này long và lanh',
        'Thiết bị điện tử',
        '100',
        'A',
        'B',
        '2026-01-05 10:37:00',
        '29C-102323',
        10000000.00,
        'Chuyển khoản',
        'Moi_Tao',
        '2026-01-03 03:37:55'
    );
 
-- --------------------------------------------------------
 
--
-- Table structure for table `khachhang`
--
 
DROP TABLE IF EXISTS `khachhang`;
 
CREATE TABLE `khachhang` (
    `kh_id` int(11) NOT NULL,
    `ten_khach_hang` varchar(100) NOT NULL,
    `dia_chi` varchar(255) DEFAULT NULL,
    `sdt` varchar(15) DEFAULT NULL,
    `email` varchar(100) NOT NULL,
    `username` varchar(50) NOT NULL,
    `password_hash` varchar(255) NOT NULL,
    `ngay_dang_ky` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
 
--
-- Dumping data for table `khachhang`
--
 
INSERT INTO
    `khachhang` (
        `kh_id`,
        `ten_khach_hang`,
        `dia_chi`,
        `sdt`,
        `email`,
        `username`,
        `password_hash`,
        `ngay_dang_ky`
    )
VALUES (
        1,
        'Long',
        NULL,
        '09876539959',
        'hello2005@gmail.com',
        'meomeo',
        '$2y$10$k.k8vIGuNEQNFV06iXmOtuu.wEHz/NxXrFomBpxNV9XqxdKHsTZC6',
        '2025-12-24 22:52:23'
    ),
    (
        13,
        'Công ty TNHH Logistics Trường Thành',
        'Số 12, Duy Tân, Hà Nội',
        '0243123456',
        'logistics@truongthanh.vn',
        'kh_truongthanh',
        '',
        '2026-01-03 04:13:46'
    ),
    (
        14,
        'Cửa hàng Nội thất Minh Khôi',
        '456 Lê Trọng Tấn, TP. HCM',
        '0912345678',
        'noithatminhkhoi@gmail.com',
        'kh_minhkhoi',
        '',
        '2026-01-03 04:13:46'
    ),
    (
        15,
        'Công ty CP Xuất Nhập khẩu Hải Nam',
        'KCN Đình Vũ, Hải Phòng',
        '02253888999',
        'info@hainam-jsc.com',
        'kh_hainam',
        '',
        '2026-01-03 04:13:46'
    ),
    (
        16,
        'Vật liệu Xây dựng Miền Trung',
        '123 Hùng Vương, Đà Nẵng',
        '02363555666',
        'vlxdmientrung@gmail.com',
        'kh_mientrung',
        '',
        '2026-01-03 04:13:46'
    ),
    (
        17,
        'Kho vận thực phẩm sạch EcoFood',
        'KCN Sóng Thần, Bình Dương',
        '0909888777',
        'kho@ecofood.com.vn',
        'kh_ecofood',
        '',
        '2026-01-03 04:13:46'
    ),
    (
        18,
        'Đại lý Phân phối Sữa Vinamilk',
        'Số 8, Bến Nghé, TP. Huế',
        '02343111222',
        'vinamilk-hue@gmail.com',
        'kh_vinamilkhue',
        '',
        '2026-01-03 04:13:46'
    ),
    (
        19,
        'Cơ sở May mặc Thành Phát',
        '68 Trần Phú, TP. Nam Định',
        '0987654321',
        'maymacthanhphat@gmail.com',
        'kh_thanhphat',
        '',
        '2026-01-03 04:13:46'
    ),
    (
        20,
        'Nông sản Đà Lạt Sương Mù',
        'Trần Hưng Đạo, TP. Đà Lạt',
        '02633444555',
        'nongsandatlat@gmail.com',
        'kh_dalat',
        '',
        '2026-01-03 04:13:46'
    ),
    (
        21,
        'Thủy sản Mekong Delta',
        'KCN Trà Nóc, TP. Cần Thơ',
        '02923777888',
        'info@mekong-seafood.com',
        'kh_mekong',
        '',
        '2026-01-03 04:13:46'
    ),
    (
        22,
        'Công ty Điện máy Gia Bảo',
        'Lê Hồng Phong, TP. Quy Nhơn',
        '0933222111',
        'giabaotb@gmail.com',
        'kh_giabao',
        '',
        '2026-01-03 04:13:46'
    );
 
-- --------------------------------------------------------
 
--
-- Table structure for table `lenh_van_chuyen`
--
 
DROP TABLE IF EXISTS `lenh_van_chuyen`;
 
CREATE TABLE `lenh_van_chuyen` (
    `ma_lenh` int(11) NOT NULL,
    `ten_khach_hang` varchar(255) NOT NULL,
    `loai_xe_yeu_cau` varchar(100) DEFAULT NULL,
    `noi_di` varchar(255) DEFAULT NULL,
    `noi_den` varchar(255) DEFAULT NULL,
    `cuoc_phi` decimal(15, 0) DEFAULT 0,
    `phi_khac` decimal(15, 0) DEFAULT 0,
    `tong_cong` decimal(15, 0) DEFAULT 0,
    `ghi_chu` text DEFAULT NULL,
    `trang_thai_lenh` enum(
        'Cho_Xu_Ly',
        'Da_Phan_Cong',
        'Dang_Van_Chuyen',
        'Da_Giao_Hang',
        'Su_Co',
        'Hoan_Thanh'
    ) DEFAULT 'Cho_Xu_Ly',
    `chi_tiet_su_co` text DEFAULT NULL,
    `ma_tai_xe_phu_trach` int(11) DEFAULT NULL,
    `ngay_tao` datetime DEFAULT current_timestamp(),
    `ngay_tiep_nhan` datetime DEFAULT NULL,
    `loai_su_co` varchar(50) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
 
--
-- Dumping data for table `lenh_van_chuyen`
--
 
INSERT INTO
    `lenh_van_chuyen` (
        `ma_lenh`,
        `ten_khach_hang`,
        `loai_xe_yeu_cau`,
        `noi_di`,
        `noi_den`,
        `cuoc_phi`,
        `phi_khac`,
        `tong_cong`,
        `ghi_chu`,
        `trang_thai_lenh`,
        `chi_tiet_su_co`,
        `ma_tai_xe_phu_trach`,
        `ngay_tao`,
        `ngay_tiep_nhan`,
        `loai_su_co`
    )
VALUES (
        1,
        'Vinamilk Hà Nội',
        'Container',
        'Kho Tiên Sơn',
        'Cảng Hải Phòng',
        3500000,
        200000,
        3700000,
        'Giao gấp trước 18h',
        'Su_Co',
        'kẹt xe',
        2,
        '2025-12-17 16:38:52',
        '2025-12-25 04:48:29',
        'Loi_Tai_Xe'
    ),
    (
        2,
        'Shopee Express',
        'Xe tải nhẹ',
        'Kho Gia Lâm',
        'Kho Hoàng Mai',
        500000,
        0,
        500000,
        'Hàng dễ vỡ',
        'Su_Co',
        'Sạt lở',
        4,
        '2025-12-17 16:38:52',
        '2025-12-18 07:05:46',
        'Loi_Kho'
    ),
    (
        3,
        'Samsung Electronics',
        'Container',
        'KCN Yên Phong',
        'Sân bay Nội Bài',
        2200000,
        100000,
        2300000,
        'Đi cổng 3 nhà máy',
        'Hoan_Thanh',
        NULL,
        3,
        '2025-12-17 16:38:52',
        '2025-12-17 16:38:52',
        NULL
    ),
    (
        4,
        'Nhôm Kính Hoa Sen',
        'Xe tải trung',
        'Nhà máy Hải Dương',
        'Đại lý Nam Định',
        1800000,
        50000,
        1850000,
        'Thu tiền mặt',
        'Hoan_Thanh',
        NULL,
        4,
        '2025-12-17 16:38:52',
        '2025-12-17 16:38:52',
        NULL
    ),
    (
        5,
        'Bánh kẹo Kinh Đô',
        'Xe tải trung',
        'Hưng Yên',
        'Thanh Hóa',
        2500000,
        0,
        2500000,
        'Hàng thực phẩm',
        'Hoan_Thanh',
        'Hỏng lốp xe tại Phủ Lý',
        4,
        '2023-12-15 08:00:00',
        '2023-12-15 09:00:00',
        NULL
    ),
    (
        6,
        'Siêu thị BigC',
        'Xe đông lạnh',
        'Kho Lạnh Hà Nội',
        'BigC Thăng Long',
        800000,
        0,
        800000,
        'Nhiệt độ < 5 độ C',
        'Hoan_Thanh',
        NULL,
        10,
        '2023-12-16 07:00:00',
        '2023-12-16 07:30:00',
        NULL
    ),
    (
        7,
        'Thép Hòa Phát',
        'Container',
        'Cảng Dung Quất',
        'Đà Nẵng',
        5000000,
        0,
        5000000,
        '',
        'Hoan_Thanh',
        NULL,
        1,
        '2023-12-01 10:00:00',
        '2023-12-01 10:30:00',
        NULL
    ),
    (
        8,
        'Tiki Trading',
        'Xe tải nhẹ',
        'Kho Bắc Ninh',
        'Hà Đông',
        600000,
        50000,
        650000,
        'Giao giờ hành chính',
        'Hoan_Thanh',
        NULL,
        2,
        '2025-12-17 16:38:52',
        '2025-12-18 07:05:37',
        NULL
    ),
    (
        9,
        'Lazada Logistics',
        'Xe tải trung',
        'Kho Long Biên',
        'Kho Từ Liêm',
        450000,
        0,
        450000,
        '',
        'Hoan_Thanh',
        NULL,
        2,
        '2025-12-17 16:38:52',
        '2025-12-17 16:39:59',
        NULL
    ),
    (
        10,
        'Nội thất Nhà Xinh',
        'Xe tải trung',
        'Showroom Hà Nội',
        'Biệt thự Ecopark',
        1200000,
        200000,
        1400000,
        'Bốc xếp nhẹ tay',
        'Hoan_Thanh',
        NULL,
        2,
        '2023-12-10 14:00:00',
        '2023-12-10 14:15:00',
        NULL
    ),
    (
        11,
        'MEO',
        'Container',
        'HP',
        'DAD',
        150000,
        0,
        150000,
        '',
        'Dang_Van_Chuyen',
        '',
        8,
        '2025-12-25 04:59:45',
        '2025-12-25 04:59:51',
        NULL
    ),
    (
        12,
        'A',
        'Container',
        'AD',
        'DA',
        11000000,
        0,
        11000000,
        '',
        'Cho_Xu_Ly',
        NULL,
        NULL,
        '2026-01-03 10:33:29',
        NULL,
        NULL
    ),
    (
        13,
        'Hoàng Phú Hải',
        'Xe tải nhẹ',
        'Hải Phòng',
        'Đà Nẵng',
        180,
        0,
        180,
        'Hàng: Vợt cầu lông () -  kg',
        'Cho_Xu_Ly',
        NULL,
        NULL,
        '2026-01-03 11:01:50',
        NULL,
        NULL
    ),
    (
        14,
        'long',
        'Xe tải nhẹ',
        'Hải Phòng',
        'Đà Nẵng',
        2132321,
        0,
        2132321,
        'Hàng: dsf. Loại: A. KL: 01213131 kg',
        'Hoan_Thanh',
        '',
        9,
        '2026-01-03 11:10:42',
        '2026-01-03 18:56:34',
        NULL
    ),
    (
        15,
        'Tuán',
        'Xe tải nhẹ',
        'Hải Phòng',
        'Hà Nội',
        10000000,
        0,
        10000000,
        'Hàng: SQDP. Loại: Hóa chất. KL: 1000 kg',
        'Hoan_Thanh',
        '',
        9,
        '2026-01-03 11:15:52',
        '2026-01-03 18:56:49',
        NULL
    ),
    (
        16,
        'Sơn Tùng',
        'Xe tải nhẹ',
        'An Dương',
        'Hà Nội',
        270,
        0,
        270,
        'Hàng: Hóa chất. Mã ĐH: 0',
        'Cho_Xu_Ly',
        NULL,
        NULL,
        '2026-01-04 15:47:34',
        NULL,
        NULL
    ),
    (
        17,
        'Sơn Tùng',
        'Xe tải nhẹ',
        'An Dương',
        'Đà Nẵng',
        1000000,
        0,
        1000000,
        'Hàng: Hóa chất. Mã ĐH: 0',
        'Cho_Xu_Ly',
        NULL,
        NULL,
        '2026-01-04 15:52:02',
        NULL,
        NULL
    ),
    (
        18,
        'Sơn Tùng',
        'Xe tải nhẹ',
        'An Dương',
        'Hà Nội',
        689217,
        0,
        689217,
        'Hàng: SQDP. Mã ĐH: 0',
        'Cho_Xu_Ly',
        NULL,
        NULL,
        '2026-01-04 16:48:11',
        NULL,
        NULL
    );
 
-- --------------------------------------------------------
 
--
-- Table structure for table `nhanvien`
--
 
DROP TABLE IF EXISTS `nhanvien`;
 
CREATE TABLE `nhanvien` (
    `nv_id` int(11) NOT NULL,
    `ten_nhan_vien` varchar(100) NOT NULL,
    `dia_chi` varchar(255) DEFAULT NULL,
    `sdt` varchar(15) DEFAULT NULL,
    `email` varchar(100) NOT NULL,
    `username` varchar(50) NOT NULL,
    `password_hash` varchar(255) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
 
--
-- Dumping data for table `nhanvien`
--
 
INSERT INTO
    `nhanvien` (
        `nv_id`,
        `ten_nhan_vien`,
        `dia_chi`,
        `sdt`,
        `email`,
        `username`,
        `password_hash`
    )
VALUES (
        3,
        'Lam Thần An',
        'Hải Phòng',
        '0901234567',
        'lamthanan03@gmail.com',
        'nhipham',
        '$2y$10$XijECvovSTnQtCE4KL/II.S.xr4MRtiZX45Xt.oK.m.ob5vENtih2'
    );
 
-- --------------------------------------------------------
 
--
-- Table structure for table `orders`
--
 
DROP TABLE IF EXISTS `orders`;
 
CREATE TABLE `orders` (
    `id` int(11) NOT NULL,
    `customer_id` int(11) NOT NULL,
    `route_from` varchar(200) NOT NULL,
    `route_to` varchar(200) NOT NULL,
    `product_name` varchar(255) NOT NULL,
    `weight` decimal(10, 2) DEFAULT 0.00,
    `cargo_type` varchar(100) DEFAULT NULL,
    `status` varchar(50) DEFAULT 'Pending',
    `price` decimal(12, 2) DEFAULT 0.00,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
 
--
-- Dumping data for table `orders`
--
 
INSERT INTO
    `orders` (
        `id`,
        `customer_id`,
        `route_from`,
        `route_to`,
        `product_name`,
        `weight`,
        `cargo_type`,
        `status`,
        `price`,
        `created_at`
    )
VALUES     (
        1,
        1,
        'Hải Phòng',
        'Đà Nẵng',
        'Vợt cầu lông',
        10.00,
        'Vợt',
        'Pending',
        1990000.00,
        '2026-01-02 08:21:13'
    ),
        (
        2,
        1,
        'Hải Phòng',
        'Đà Nẵng',
        'Vợt cầu lông',
        10.00,
        'Vợt',
        'Pending',
        1990000.00,
        '2026-01-02 08:22:28'
    ),
        (
        3,
        1,
        'Hải Phòng',
        'Đà Nẵng',
        'dsf',
        23.00,
        '',
        'InTransit',
        233223.00,
        '2026-01-02 08:24:22'
    ),
        (
        4,
        1,
        'Hải Phòng',
        'ewr',
        'ưer',
        324.00,
        'ewr',
        'Delivered',
        0.00,
        '2026-01-02 08:55:41'
    ),
        (
        5,
        1,
        'Hải Phòng',
        'Đà Nẵng',
        'Vợt cầu lông',
        NULL,
        NULL,
        NULL,
        180.00,
        '2026-01-03 04:01:50'
    ),
        (
        6,
        3,
        'Hải Phòng',
        'Đà Nẵng',
        'dsf',
        1213131.00,
        'A',
        'Delivered',
        2132321.00,
        '2026-01-03 04:10:42'
    ),
        (
        7,
        4,
        'Hải Phòng',
        'Hà Nội',
        'SQDP',
        1000.00,
        'Hóa chất',
        'Delivered',
        10000000.00,
        '2026-01-03 04:15:52'
    ),
        (
        8,
        7,
        'An Dương',
        'Hà Nội',
        'Hóa chất',
        100.00,
        'Vợt',
        'Pending',
        270.00,
        '2026-01-04 08:47:34'
    ),
        (
        9,
        7,
        'An Dương',
        'Đà Nẵng',
        'Hóa chất',
        122.00,
        'Hóa chất',
        'Pending',
        1000000.00,
        '2026-01-04 08:52:02'
    ),
        (
        10,
        7,
        'An Dương',
        'Hà Nội',
        'SQDP',
        12.00,
        'A',
        'Pending',
        689217.00,
        '2026-01-04 09:48:11'
    );
 
-- --------------------------------------------------------
 
--
-- Table structure for table `phieu_nhap`
--
 
DROP TABLE IF EXISTS `phieu_nhap`;
 
CREATE TABLE `phieu_nhap` (
    `id` int(11) NOT NULL,
    `ma_phieu` varchar(50) NOT NULL,
    `ngay_nhap` datetime DEFAULT current_timestamp(),
    `nguoi_giao` varchar(255) DEFAULT NULL,
    `nguoi_tao` varchar(255) DEFAULT NULL,
    `ghi_chu` text DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
 
--
-- Dumping data for table `phieu_nhap`
--
 
INSERT INTO
    `phieu_nhap` (
        `id`,
        `ma_phieu`,
        `ngay_nhap`,
        `nguoi_giao`,
        `nguoi_tao`,
        `ghi_chu`
    )
VALUES (
        1,
        'PN-260104090745',
        '2026-01-04 15:07:45',
        'Long',
        'Lam Thần An',
        ''
    );
 
-- --------------------------------------------------------
 
--
-- Table structure for table `phieu_xuat`
--
 
DROP TABLE IF EXISTS `phieu_xuat`;
 
CREATE TABLE `phieu_xuat` (
    `id` int(11) NOT NULL,
    `ma_phieu` varchar(50) NOT NULL,
    `nguoi_nhan` varchar(255) DEFAULT NULL,
    `nguoi_tao` varchar(255) DEFAULT NULL,
    `ghi_chu` text DEFAULT NULL,
    `ngay_xuat` datetime DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
 
--
-- Dumping data for table `phieu_xuat`
--
 
INSERT INTO
    `phieu_xuat` (
        `id`,
        `ma_phieu`,
        `nguoi_nhan`,
        `nguoi_tao`,
        `ghi_chu`,
        `ngay_xuat`
    )
VALUES (
        1,
        'PX-20260104092308',
        'LongCu',
        'Lam Thần An',
        '',
        '2026-01-04 15:23:08'
    ),
    (
        2,
        'PX-20260104095335',
        'Hải',
        'Lam Thần An',
        '',
        '2026-01-04 15:53:35'
    );
 
-- --------------------------------------------------------
 
--
-- Table structure for table `sanpham`
--
 
DROP TABLE IF EXISTS `sanpham`;
 
CREATE TABLE `sanpham` (
    `id` int(11) NOT NULL,
    `ma_sp` varchar(50) NOT NULL,
    `ten_sp` varchar(255) NOT NULL,
    `loai_hang` varchar(100) DEFAULT NULL,
    `don_vi` varchar(50) DEFAULT NULL,
    `so_luong` int(11) DEFAULT 0,
    `mo_ta` text DEFAULT NULL,
    `ngay_tao` date DEFAULT curdate()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
 
--
-- Dumping data for table `sanpham`
--
 
INSERT INTO
    `sanpham` (
        `id`,
        `ma_sp`,
        `ten_sp`,
        `loai_hang`,
        `don_vi`,
        `so_luong`,
        `mo_ta`,
        `ngay_tao`
    )
VALUES (
        1,
        'PT-2020-001',
        'Lốp xe đầu kéo Bridgestone 12R22.5',
        'Phụ tùng',
        'Cái',
        40,
        NULL,
        '2020-01-15'
    ),
    (
        2,
        'PT-2020-002',
        'Bố thắng xe tải Hino (Trước)',
        'Phụ tùng',
        'Bộ',
        25,
        NULL,
        '2020-02-20'
    ),
    (
        91,
        'YNJP',
        'Yonex',
        'Hàng Mới',
        'Cái',
        13,
        NULL,
        '2025-12-17'
    ),
    (
        92,
        'SH92',
        'Giày',
        'Hàng Mới',
        'Cái',
        409,
        NULL,
        '2025-12-17'
    ),
    (
        93,
        'SP00242',
        'Tất',
        'Hàng Mới',
        'Cái',
        1,
        NULL,
        '2025-12-17'
    ),
    (
        94,
        'Cl-VOT',
        'Vợt cầu lông YN',
        'Hàng Mới',
        'Cái',
        112,
        NULL,
        '2025-12-17'
    );
 
-- --------------------------------------------------------
 
--
-- Table structure for table `taixe`
--
 
DROP TABLE IF EXISTS `taixe`;
 
CREATE TABLE `taixe` (
    `ma_tai_xe` int(11) NOT NULL,
    `ho_ten` varchar(100) NOT NULL,
    `ngay_sinh` date DEFAULT NULL,
    `cccd` varchar(20) DEFAULT NULL,
    `sdt` varchar(20) NOT NULL,
    `dia_chi` text DEFAULT NULL,
    `bang_lai` varchar(10) NOT NULL,
    `ngay_hethan_banglai` date NOT NULL,
    `suc_khoe` enum('Dat', 'Khong_Dat') DEFAULT 'Dat',
    `ngay_kham_sk` date DEFAULT NULL,
    `diem_tin_nhiem` int(11) DEFAULT 100,
    `lich_su_vi_pham` text DEFAULT NULL,
    `trang_thai` enum(
        'San_sang',
        'Dang_ban',
        'Nghi_phep',
        'Bi_khoa'
    ) DEFAULT 'San_sang',
    `ly_do_khoa` varchar(255) DEFAULT NULL,
    `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
 
--
-- Dumping data for table `taixe`
--
 
INSERT INTO
    `taixe` (
        `ma_tai_xe`,
        `ho_ten`,
        `ngay_sinh`,
        `cccd`,
        `sdt`,
        `dia_chi`,
        `bang_lai`,
        `ngay_hethan_banglai`,
        `suc_khoe`,
        `ngay_kham_sk`,
        `diem_tin_nhiem`,
        `lich_su_vi_pham`,
        `trang_thai`,
        `ly_do_khoa`,
        `ngay_tao`
    )
VALUES (
        1,
        'Phạm Văn Dũng',
        '1985-05-12',
        '034085000123',
        '0912000001',
        'Hà Nội',
        'FC',
        '2028-05-12',
        'Dat',
        '2024-01-10',
        100,
        '',
        'Nghi_phep',
        '',
        '2025-12-17 16:38:52'
    ),
    (
        2,
        'Lê Thị Mai',
        '1990-08-20',
        '034090000456',
        '0912000002',
        'Hải Phòng',
        'C',
        '2027-08-20',
        'Dat',
        '2024-02-15',
        85,
        NULL,
        'Dang_ban',
        NULL,
        '2025-12-17 16:38:52'
    ),
    (
        3,
        'Trương Thế Vinh',
        '1988-11-11',
        '034088000789',
        '0912000003',
        'Đà Nẵng',
        'FC',
        '2026-11-11',
        'Dat',
        '2024-03-01',
        98,
        NULL,
        'San_sang',
        NULL,
        '2025-12-17 16:38:52'
    ),
    (
        4,
        'Kiều Minh Tuấn',
        '1982-02-02',
        '034082000111',
        '0912000004',
        'TP.HCM',
        'C',
        '2026-02-02',
        'Dat',
        '2024-01-20',
        90,
        NULL,
        'Dang_ban',
        NULL,
        '2025-12-17 16:38:52'
    ),
    (
        5,
        'Mạc Văn Khoa',
        '1992-06-06',
        '034092000222',
        '0912000005',
        'Hải Dương',
        'C',
        '2023-01-01',
        'Dat',
        '2023-01-01',
        80,
        NULL,
        'Bi_khoa',
        'GPLX hết hạn',
        '2025-12-17 16:38:52'
    ),
    (
        6,
        'Trấn Thành',
        '1987-02-05',
        '034087000333',
        '0912000006',
        'TP.HCM',
        'FC',
        '2029-02-05',
        'Khong_Dat',
        '2024-12-10',
        100,
        NULL,
        'Bi_khoa',
        'Sức khỏe không đạt',
        '2025-12-17 16:38:52'
    ),
    (
        7,
        'Trường Giang',
        '1983-04-20',
        '034083000444',
        '0912000007',
        'Quảng Nam',
        'C',
        '2026-04-20',
        'Dat',
        '2024-05-05',
        99,
        NULL,
        'Nghi_phep',
        NULL,
        '2025-12-17 16:38:52'
    ),
    (
        8,
        'Ninh Dương Lan Ngọc',
        '1995-10-10',
        '034095000555',
        '0912000008',
        'TP.HCM',
        'B2',
        '2030-10-10',
        'Dat',
        '2024-06-01',
        100,
        NULL,
        'Dang_ban',
        NULL,
        '2025-12-17 16:38:52'
    ),
    (
        9,
        'Ngô Kiến Huy',
        '1991-09-09',
        '034091000666',
        '0912000009',
        'TP.HCM',
        'FC',
        '2027-09-09',
        'Dat',
        '2024-07-07',
        75,
        NULL,
        'San_sang',
        NULL,
        '2025-12-17 16:38:52'
    ),
    (
        10,
        'Lâm Vỹ Dạ',
        '1989-12-12',
        '034089000777',
        '0912000010',
        'Huế',
        'C',
        '2026-12-12',
        'Dat',
        '2024-08-08',
        96,
        NULL,
        'San_sang',
        NULL,
        '2025-12-17 16:38:52'
    );
 
--
-- Indexes for dumped tables
--
 
--
-- Indexes for table `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
ADD PRIMARY KEY (`ctdh_id`),
ADD KEY `dh_id` (`dh_id`);
 
--
-- Indexes for table `ct_phieu_nhap`
--
ALTER TABLE `ct_phieu_nhap`
ADD PRIMARY KEY (`id`),
ADD KEY `fk_ct_phieu_nhap_master` (`phieu_nhap_id`);
 
--
-- Indexes for table `ct_phieu_xuat`
--
ALTER TABLE `ct_phieu_xuat`
ADD PRIMARY KEY (`id`),
ADD KEY `fk_ct_phieu_xuat_master` (`phieu_xuat_id`);
 
--
-- Indexes for table `customers`
--
ALTER TABLE `customers` ADD PRIMARY KEY (`id`);
 
--
-- Indexes for table `orders`
--
ALTER TABLE `orders` ADD PRIMARY KEY (`id`), ADD KEY `customer_id` (`customer_id`);
 
--
-- Indexes for table `donhang`
--
ALTER TABLE `donhang`
ADD PRIMARY KEY (`dh_id`),
ADD KEY `kh_id` (`kh_id`);
 
--
-- Indexes for table `hopdong_chi_tiet`
--
ALTER TABLE `hopdong_chi_tiet`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `ma_hd` (`ma_hd`);
 
--
-- Indexes for table `khachhang`
--
ALTER TABLE `khachhang`
ADD PRIMARY KEY (`kh_id`),
ADD UNIQUE KEY `email` (`email`),
ADD UNIQUE KEY `username` (`username`);
 
--
-- Indexes for table `lenh_van_chuyen`
--
ALTER TABLE `lenh_van_chuyen` ADD PRIMARY KEY (`ma_lenh`);
 
--
-- Indexes for table `nhanvien`
--
ALTER TABLE `nhanvien`
ADD PRIMARY KEY (`nv_id`),
ADD UNIQUE KEY `email` (`email`),
ADD UNIQUE KEY `username` (`username`);
 
--
-- Indexes for table `phieu_nhap`
--
ALTER TABLE `phieu_nhap`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `ma_phieu` (`ma_phieu`);
 
--
-- Indexes for table `phieu_xuat`
--
ALTER TABLE `phieu_xuat`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `ma_phieu` (`ma_phieu`);
 
--
-- Indexes for table `sanpham`
--
ALTER TABLE `sanpham`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `ma_sp` (`ma_sp`);
 
--
-- Indexes for table `taixe`
--
ALTER TABLE `taixe` ADD PRIMARY KEY (`ma_tai_xe`);
 
--
-- AUTO_INCREMENT for dumped tables
--
 
--
-- AUTO_INCREMENT for table `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
MODIFY `ctdh_id` int(11) NOT NULL AUTO_INCREMENT;
 
--
-- AUTO_INCREMENT for table `ct_phieu_nhap`
--
ALTER TABLE `ct_phieu_nhap`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 2;
 
--
-- AUTO_INCREMENT for table `ct_phieu_xuat`
--
ALTER TABLE `ct_phieu_xuat`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 3;
 
--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 11;
 
--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 11;
 
--
-- AUTO_INCREMENT for table `donhang`
--
ALTER TABLE `donhang`
MODIFY `dh_id` int(11) NOT NULL AUTO_INCREMENT;
 
--
-- AUTO_INCREMENT for table `hopdong_chi_tiet`
--
ALTER TABLE `hopdong_chi_tiet`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 2;
 
--
-- AUTO_INCREMENT for table `khachhang`
--
ALTER TABLE `khachhang`
MODIFY `kh_id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 23;
 
--
-- AUTO_INCREMENT for table `lenh_van_chuyen`
--
ALTER TABLE `lenh_van_chuyen`
MODIFY `ma_lenh` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 19;
 
--
-- AUTO_INCREMENT for table `nhanvien`
--
ALTER TABLE `nhanvien`
MODIFY `nv_id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;
 
--
-- AUTO_INCREMENT for table `phieu_nhap`
--
ALTER TABLE `phieu_nhap`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 2;
 
--
-- AUTO_INCREMENT for table `phieu_xuat`
--
ALTER TABLE `phieu_xuat`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 3;
 
--
-- AUTO_INCREMENT for table `sanpham`
--
ALTER TABLE `sanpham`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 96;
 
--
-- AUTO_INCREMENT for table `taixe`
--
ALTER TABLE `taixe`
MODIFY `ma_tai_xe` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 11;
 
--
-- Constraints for dumped tables
--
 
--
-- Constraints for table `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
ADD CONSTRAINT `chitietdonhang_ibfk_1` FOREIGN KEY (`dh_id`) REFERENCES `donhang` (`dh_id`);
 
--
-- Constraints for table `ct_phieu_nhap`
--
ALTER TABLE `ct_phieu_nhap`
ADD CONSTRAINT `fk_ct_phieu_nhap_master` FOREIGN KEY (`phieu_nhap_id`) REFERENCES `phieu_nhap` (`id`) ON DELETE CASCADE;
 
--
-- Constraints for table `ct_phieu_xuat`
--
ALTER TABLE `ct_phieu_xuat`
ADD CONSTRAINT `fk_ct_phieu_xuat_master` FOREIGN KEY (`phieu_xuat_id`) REFERENCES `phieu_xuat` (`id`) ON DELETE CASCADE;
 
--
-- Constraints for table `donhang`
--
ALTER TABLE `donhang`
ADD CONSTRAINT `donhang_ibfk_1` FOREIGN KEY (`kh_id`) REFERENCES `khachhang` (`kh_id`);
 
COMMIT;
 
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;
