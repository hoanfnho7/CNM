-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 27, 2025 lúc 09:02 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `chodocu`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-folder',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `created_at`) VALUES
(1, 'Điện tử', 'electronics', 'fas fa-laptop', 'Điện thoại, máy tính, thiết bị điện tử và phụ kiện', '2025-05-14 17:17:39'),
(2, 'Nội thất', 'furniture', 'fas fa-couch', 'Bàn ghế, tủ, giường và các đồ nội thất khác', '2025-05-14 17:17:39'),
(3, 'Quần áo', 'clothing', 'fas fa-tshirt', 'Quần áo, giày dép và phụ kiện thời trang', '2025-05-14 17:17:39'),
(4, 'Sách', 'books', 'fas fa-book', 'Sách, truyện, tạp chí và tài liệu học tập', '2025-05-14 17:17:39'),
(5, 'Đồ chơi', 'toys', 'fas fa-gamepad', 'Đồ chơi, trò chơi và thiết bị giải trí', '2025-05-14 17:17:39'),
(6, 'Khác', 'others', 'fas fa-ellipsis-h', 'Các sản phẩm khác không thuộc các danh mục trên', '2025-05-14 17:17:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied') NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'Lê Hoàng Nhớ', 'lhoangnhoo@gmail.com', 'Cần hỗ trợ', 'aaaaaaaaa', 'new', '2025-05-27 10:29:07');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(9, 8, 8, '2025-05-27 12:18:37');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `product_id`, `message`, `is_read`, `created_at`) VALUES
(10, 6, 7, NULL, 'Chào', 1, '2025-05-27 00:06:53'),
(11, 6, 7, NULL, 'Mình muốn mua điện thoại này', 1, '2025-05-27 00:07:15'),
(12, 7, 6, NULL, 'Giá 8 triệu nhe', 1, '2025-05-27 00:07:52'),
(13, 6, 7, NULL, 'ok', 1, '2025-05-27 00:08:04'),
(14, 8, 6, 18, 'hello', 1, '2025-05-27 11:53:14'),
(15, 8, 6, 18, 'rrr', 1, '2025-05-27 12:14:32'),
(16, 8, 7, 17, 'chào', 1, '2025-05-27 12:19:12'),
(17, 7, 8, NULL, 'hi', 1, '2025-05-27 12:19:32'),
(18, 7, 8, NULL, 'bạn muốn mua à', 1, '2025-05-27 12:19:41'),
(19, 8, 7, 17, 'uh', 1, '2025-05-27 12:20:19');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `used`) VALUES
(1, 8, 'mc7qQ9rh6NwgkEF6BLFo0eDFqslgZDOJ', '2025-05-28 14:00:05', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `category` enum('electronics','furniture','clothing','books','toys','others') NOT NULL,
  `condition` enum('new','like_new','good','fair','poor') NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected','sold') NOT NULL DEFAULT 'pending',
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `user_id`, `name`, `description`, `price`, `category_id`, `category`, `condition`, `image`, `status`, `views`, `created_at`, `updated_at`) VALUES
(7, 6, 'XIAOMI REDMI NOTE 11 PRO 8GB.128GB HELIO G96', '🔥XIAOMI REDMI NOTE 11 PRO 8GB.128GB MÁY ĐẸP  CHỨC NĂNG ĐẦY ĐỦ \r\n🔋PIN 5000 ĐỦ DÙNG TRONG 1 NGÀY THOẢI MÁI Ạ \r\n🔥CHÍP HELIO G96 MẠNH MẼ CHƠI GAME NGON LẮM \r\n📸CAMERA  108MP CHỤP ẢNH NÉT LẮM \r\n🔥MÀN HÌNH 6.67 INCH SẮC NÉT MÀN HÌNH ĐẸP HÔNG ÁM Ố HÔNG TRẦY XƯỚC \r\n🔥EM BÁN GIÁ CHỈ 2.400.000 💰\r\n\r\n🌈HỖ TRỢ QUẸT THẺ / TRẢ GÓP 0%\r\n\r\n🚛EM CÓ SHIP TRONG VÀ NGOÀI SÀI GÒN Ạ \r\n🔥ANH CHỊ CẦN XEM MÁY THÌ GỌI EM QUA ☎️SDT HOẶC ZALO PHÍA DƯỚI BÀI VIẾT NHA\r\n❤️EM CẢM ƠN ANH CHỊ ĐÃ XEM TIN Ạ❤️\r\n\r\n🏠346 PHAN VĂN TRỊ BÌNH THẠNH', 2400000.00, 1, 'electronics', 'like_new', 'gAbskTKX7g_1748305192.jpg', 'approved', 2, '2025-05-27 07:19:52', NULL),
(8, 6, 'Bán macbook air xác còn đẹp', 'Dọn cty còn cái macbook air bán dùm Anh bạn không có sạc nên ko biết thế nào?! Bán tù mù ko lên ., Ae cân nhắc đọc kỹ trc khi mua nhé!\r\nvỏ máy màn nhìn còn đẹp . Bán cho Ae lấy linh kiện tks. Không sạc không có gì test cả. Tks sms mình nha hcm q2. ***.', 868000.00, 1, 'electronics', 'good', '1njpuklLte_1748305244.jpg', 'approved', 12, '2025-05-27 07:20:44', NULL),
(9, 6, 'Nvx 11/2023 v2 ngay chủ sang tên ngay bán nhanh', '✔️Nvx 11/2023 abs 155cc V2 Smartkey nguyên zin xe đẹp leng keng k 1 vết\r\n📞 xe chuẩn mới từ trong ra ngoài như new\r\n\r\n✔Xe đẹp leng keng không một vết trầy mua về chỉ việc đổ xăng vào là chạy vọt. \r\n✔Fixxxx chút đỉnh cho ai thiện chí qua xem xe\r\n☎️☎️☎️📞📞📞***\r\nĐỊA CHỈ :468/2/21 phan văn trị phường 7 gò vấp\r\n########Google maps xe máy bita', 42800000.00, 6, 'others', 'like_new', 'NY4kSDtpnI_1748305310.jpg', 'approved', 4, '2025-05-27 07:21:50', NULL),
(10, 7, 'Cần pass lỗ gấp tủ quần áo mới mua. TL trực tiếp', 'Do cần chuyển việc nên pass gấp tủ quần áo phù hợp cho các bạn sinh viên, anh chị văn phòng,…\r\nKích thước: 1m7 x 1m05\r\nChất liệu: Tủ nhựa Đài Loan chống thấm nước, chống mói mọt,..\r\nGiá: 1tr450 (còn thương lượng)\r\nLưu ý: Hình thật, ae thiện chưa liên hệ trực tiếp mình qua Sđt: *** gặp Huân', 1450000.00, 2, 'furniture', 'like_new', '1LOYk4GLEV_1748305402.jpg', 'approved', 10, '2025-05-27 07:23:22', NULL),
(11, 7, 'Bếp Rinnai năm 2021', 'Bếp gas Rinnai phím xoay mặt men sản xuất tháng 1 năm 2021\r\nChức năng tự động khóa gas khi quên tắt bếp \r\nCảm biến nước tràn khóa gas an toàn \r\nĐộ mới trên 95%\r\nBên hong có vài vết xước do quá trình vận chuyển xa \r\nĐã chỉnh lửa đẹp \r\nBao lưới chống chuột đầy đủ\r\nTặng cặp pin mới Panasonic \r\nBảo hành 1 năm \r\nGiá 2tr200k ai quan tâm liên hệ \r\nCó ship toàn quốc', 2200000.00, 2, 'furniture', 'like_new', 'BoB6y7ZdHA_1748305449.jpg', 'approved', 1, '2025-05-27 07:24:09', NULL),
(12, 7, 'Truyện tranh Shin - Cậu bé bút chì', 'Cần bán 10 cuốn truyện tranh Shin - Cậu bé bút chì cho ai có nhu cầu đọc / sưu tầm / trang trí.', 70000.00, 4, 'books', 'new', 'l8kEAACsXr_1748309921.jpg', 'approved', 0, '2025-05-27 07:24:53', '2025-05-27 08:38:41'),
(13, 7, 'Truyện tranh Conan', 'Cần bán bộ truyện tranh Conan như hình cho bạn nào có nhu cầu đọc / sưu tầm.', 500000.00, 4, 'books', 'good', '01WuahEJ23_1748309978.jpg', 'approved', 5, '2025-05-27 07:25:35', '2025-05-27 08:39:38'),
(14, 7, 'Tay cầm chơi game Nitendo , Laptop PC, PS4', 'Tay cầm ít sử dụng, full chức năng, còn như mới, về là chơi không lăng tăng gì cả.', 490000.00, 5, 'toys', 'new', 'u83YrdOPAx_1748309965.jpg', 'approved', 3, '2025-05-27 07:26:23', '2025-05-27 08:41:06'),
(17, 7, 'Bán đồng hồ Citizen máy Quartz chính hãng', 'Bán đồng hồ Citizen máy Quartz chính hãng. Ngoại hình đẹp keng như hình chụp. Kính shaphia lên không trầy xước nhé .viền si vàng đẹp nét mới tinh không phai màu. Máy chạy chuẩn giờ. Pin mới thay được 2 tháng. Bán rẻ các bác xài.', 800000.00, 6, 'others', 'like_new', 'kCIqc93GoM_1748310772.jpg', 'approved', 17, '2025-05-27 08:52:52', NULL),
(18, 6, 'Áo Vario vàng cát 2017', 'Áo zin theo xe .Sơn mới.chân Pass đã spa ngon lành. Bán hoặc giao lưu áo cũ. Nhận rả ráp luôn', 2100000.00, 6, 'others', 'good', 'nzdPiIuipx_1748311379.jpg', 'approved', 11, '2025-05-27 09:02:59', NULL),
(21, 6, 'Chim Cánh Đen', 'Mọi thắc mắc xin vui lòng liên hệ trực tiếp nha , có ship', 1000000.00, NULL, 'others', 'like_new', '9sPG97bjaU_1748316146.jpg', 'approved', 0, '2025-05-27 10:22:26', NULL),
(22, 10, '33333', '333333', 20000.00, NULL, 'books', 'fair', 'H3LEeITZhD_1748323919.png', 'pending', 0, '2025-05-27 12:31:59', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `reason` enum('fake','inappropriate','scam','other') NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','resolved') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `product_id`, `reason`, `description`, `status`, `created_at`) VALUES
(3, 6, 10, 'fake', '', 'resolved', '2025-05-27 09:13:24'),
(4, 8, 8, 'fake', '', 'resolved', '2025-05-27 12:07:42'),
(5, 8, 17, 'scam', '', 'resolved', '2025-05-27 12:20:32');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('admin_email', 'lhoangnhon09@gmail.com'),
('allow_registrations', '1'),
('auto_approve_products', '0'),
('enable_favorites', '1'),
('enable_messaging', '1'),
('enable_reports', '1'),
('footer_text', '&amp;copy; 2025 2HandShop. All rights reserved.'),
('items_per_page', '12'),
('maintenance_mode', '0'),
('site_description', 'Nền tảng mua bán đồ cũ uy tín, an toàn và tiện lợi'),
('site_name', '2HandShop');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `log_type` enum('info','warning','error','security','user','product') NOT NULL,
  `message` text NOT NULL,
  `user_info` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `system_logs`
--

INSERT INTO `system_logs` (`id`, `log_type`, `message`, `user_info`, `ip_address`, `created_at`) VALUES
(1, 'info', 'System database updated with new tables and settings', 'System', '127.0.0.1', '2025-05-15 00:17:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `status` enum('active','blocked') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `google_id`, `password`, `phone`, `address`, `avatar`, `role`, `status`, `created_at`, `last_login`) VALUES
(1, 'admin', 'admin@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 'admin', 'active', '2025-05-15 00:17:27', '2025-05-20 12:11:26'),
(6, 'lhoangnhol@gmail.com', 'lhoangnhol@gmail.com', '114026851762673066217', '$2y$10$8iRPYpw3178UmzbHgZsjmuSzYkSDgXYXbdWJbqkOe2SqLy7FzULvG', '0939027936', '', 'GCs3Fk0Aqo_1748328639.jpg', 'user', 'active', '2025-05-27 00:01:20', '2025-05-27 13:50:07'),
(7, 'lhoangnhoo@gmail.com', 'lhoangnhoo@gmail.com', '101178425697444504212', '$2y$10$ZoLj54QBMD3DyXSU6DrTi.EKhW55Cs7hGHGJGKuSciAdNRAl5f5QK', '0839027936', '12 Nguyễn Văn Bảo, p10, q Gò Vấp, HCM', 'https://lh3.googleusercontent.com/a/ACg8ocKmi8OkBfF7RRGj2e8UCY5gGb-a-4x8-KFZm70JWj9rOH9O6LI=s96-c', 'user', 'active', '2025-05-27 00:02:36', '2025-05-27 13:27:11'),
(8, 'lhoangnhon09', 'lhoangnhon09@gmail.com', '100633445471644402136', '$2y$10$5nhjTOVU2TYbq6e6veXwVuxXRBRAz3kveIfE9xuK60AZOq0BjJaQi', '', '', 'https://lh3.googleusercontent.com/a/ACg8ocJOJZxSLDEoF-b5xk3Tnx6Crp15IPlLpilfDy5z-p1JRPxunGpd=s96-c', 'admin', 'active', '2025-05-27 00:05:30', '2025-05-27 13:49:52'),
(10, 'lhoan@gmail.com', 'lhoan@gmail.com', NULL, '$2y$10$m0aoXzc/eU2OC4YZ0btxu.xA8dXcKinIzC5an1on.IBWE4h1hWvTW', '0939027936', NULL, NULL, 'user', 'active', '2025-05-27 12:31:01', '2025-05-27 12:31:10');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_products_category` (`category_id`);

--
-- Chỉ mục cho bảng `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Chỉ mục cho bảng `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_google_id` (`google_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
