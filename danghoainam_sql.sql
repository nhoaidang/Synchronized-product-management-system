-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 12, 2024 lúc 10:43 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `danghoainam_sql`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(398, 'Plugins');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `feature_image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_category`
--

CREATE TABLE `product_category` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_tag`
--

CREATE TABLE `product_tag` (
  `product_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tags`
--

INSERT INTO `tags` (`id`, `name`) VALUES
(9870, 'abandoned cart pro for woocommerce'),
(9878, 'add to cart'),
(9968, 'add to cart popup woocommerce'),
(9763, 'advanced dynamic pricing for woocommerce'),
(9884, 'advanced shipment tracking for woocommerce'),
(9879, 'ajax add to cart'),
(9844, 'ali dropshipping plugin'),
(9845, 'ali2woo'),
(9846, 'alidropship'),
(9847, 'alidropship plugin'),
(9848, 'alidropship plugin free'),
(9849, 'alidropship reviews'),
(9850, 'alidropship woo'),
(9851, 'aliexpress dropshipping for woocommerce'),
(9852, 'aliexpress dropshipping plugin'),
(9853, 'aliexpress plugin'),
(9675, 'apartment'),
(9689, 'autoptimize'),
(9994, 'best email subscription plugin wordpress'),
(9986, 'best woocommerce currency switcher'),
(9956, 'boost sales'),
(9957, 'boots sales'),
(9773, 'bulk edit'),
(9774, 'bulk edit post type'),
(9813, 'bulk edit products'),
(9788, 'bundle'),
(9789, 'Bundle products On Woocommerce'),
(9880, 'cart'),
(9910, 'checkout countdown for woocommerce'),
(9714, 'checkout field editor for woocommerce'),
(9881, 'checkout one page'),
(9690, 'clear autoptimize cache automatically'),
(9931, 'contact form 7 spinning wheel'),
(9921, 'cookie banner wordpress'),
(9922, 'cookie wordpress'),
(9911, 'countdown timer woocommerce'),
(9858, 'coupon'),
(9995, 'coupon box for woocommerce'),
(9935, 'coupon wheel for woocommerce'),
(9691, 'cronjob'),
(9836, 'custom email template'),
(9722, 'custom email wordpress'),
(9704, 'custom product options woocommerce'),
(9906, 'custom thank you page woocommerce'),
(9944, 'customer reviews for woocommerce'),
(9715, 'customize woocommerce checkout page'),
(9837, 'customize woocommerce emails'),
(9662, 'depart'),
(9663, 'deposit and part payment for woo'),
(9664, 'deposit and part payments for woocommerce'),
(9665, 'deposit woocommerce'),
(9666, 'deposits &amp; partial payments for woocommerce'),
(9764, 'discount rules for woocommerce'),
(9730, 'dropshipping'),
(9765, 'dynamic pricing woocommerce'),
(9667, 'ecommerce'),
(9775, 'edit custom metadata'),
(9907, 'edit order received page woocommerce'),
(9723, 'edit wordpress email templates'),
(9743, 'elementor gallery with links'),
(9744, 'elementor image carousel link'),
(9859, 'email'),
(9996, 'email popup wordpress'),
(9997, 'email subscribers wordpress plugin'),
(9998, 'email subscription popup'),
(9867, 'engine'),
(9705, 'epow custom product options for woocommerce'),
(9706, 'extra product options'),
(9707, 'extra product options for woocommerce'),
(9825, 'facebook api'),
(9826, 'facebook chatbot'),
(9827, 'facebook sales channel'),
(9899, 'fake google reviews'),
(9900, 'fake reviews'),
(9981, 'fake sales notification wordpress'),
(9780, 'free gifts for woocommerce'),
(9975, 'free shipping bar woocommerce'),
(9923, 'gdpr cookie consent plugin'),
(9924, 'gdpr wordpress plugin'),
(9781, 'gift wrap woocommerce'),
(9782, 'gift wrapper for woocommerce'),
(9790, 'Grouped Product'),
(9699, 'help desk'),
(9700, 'helpdesk support'),
(9791, 'How To Bundle Products On Woocommerce'),
(9892, 'import'),
(9945, 'import aliexpress reviews to woocommerce'),
(9901, 'import reviews to woocommerce'),
(9938, 'instagram shop woocommerce'),
(9939, 'instagram shopping woocommerce'),
(9838, 'kadence woocommerce email designer'),
(9676, 'land'),
(9932, 'lucky wheel spin and win'),
(9871, 'mailchimp abandoned cart woocommerce'),
(9809, 'make an offer woocommerce'),
(9958, 'marketing plugin'),
(9776, 'migrate woocommerce to shopify'),
(9893, 'migration'),
(9987, 'multi currency for woo'),
(9988, 'multi currency in woocommerce'),
(9810, 'name your price woocommerce'),
(9959, 'notification'),
(9960, 'notify'),
(9862, 'order bump woocommerce'),
(9885, 'orders tracking for woocommerce'),
(9668, 'partial payments woocommerce'),
(9946, 'photo reviews for woocommerce'),
(9860, 'plugin'),
(9754, 'point of sale for woocommerce'),
(9735, 'points and rewards for woocommerce'),
(9961, 'popup notification'),
(9755, 'pos woocommerce'),
(9814, 'pre order plugin woocommerce'),
(9792, 'product'),
(9793, 'product bundle'),
(9750, 'product comparison plugin wordpress'),
(9963, 'product configurator for woocommerce'),
(9912, 'product countdown wordpress plugin'),
(9798, 'product filter for woocommerce'),
(9819, 'product size charts plugin for woocommerce'),
(9913, 'product time countdown for woocommerce'),
(9982, 'push notification woocommerce'),
(9677, 'real estate for woocommerce'),
(9678, 'REES'),
(9861, 'reminder'),
(9914, 'sales countdown timer for woocommerce'),
(9868, 'search'),
(9724, 'send custom email in wordpress'),
(9794, 'setting up a grouped product'),
(9894, 'SHOPIFY'),
(9882, 'sidebar cart'),
(9820, 'size guide woocommerce'),
(9895, 'smart coupons for woocommerce'),
(9933, 'spin wheel wordpress plugin'),
(9883, 'sticky add to cart'),
(9736, 'sumo reward points'),
(9701, 'support desk'),
(9702, 'support ticket system'),
(9731, 'taobao'),
(9732, 'taobao drop shipping'),
(9733, 'taobao dropshipping'),
(9734, 'taobao dropshipping woocommerce'),
(9999, 'the newsletter plugin'),
(9777, 'transfer woocommerce to shopify'),
(9962, 'up sells'),
(9863, 'upsell order bump offer for woocommerce'),
(9829, 'variation swatches for woocommerce'),
(9869, 'villatheme'),
(9902, 'virtual reviews'),
(9903, 'virtual reviews for woocommerce'),
(9964, 'woo commerce builder'),
(9940, 'woo commerce instagram'),
(9766, 'woo discount rules'),
(9681, 'woo subscription'),
(9830, 'woo variation swatches'),
(9669, 'woocommerce'),
(9872, 'woocommerce abandoned cart'),
(9873, 'woocommerce abandoned cart email'),
(9874, 'woocommerce abandoned cart emails'),
(9875, 'woocommerce abandoned cart plugin'),
(9876, 'woocommerce abandoned cart recovery'),
(9969, 'woocommerce add to cart popup'),
(9970, 'woocommerce added to cart popup'),
(9976, 'woocommerce advanced free shipping'),
(9799, 'woocommerce ajax filter'),
(9854, 'woocommerce aliexpress'),
(9855, 'woocommerce aliexpress dropshipping extension'),
(9989, 'woocommerce auto currency switcher'),
(9971, 'woocommerce boost sales'),
(9767, 'woocommerce bulk discount'),
(9795, 'woocommerce bundle products'),
(9877, 'woocommerce cart abandonment recovery'),
(9972, 'woocommerce cart popup'),
(9716, 'woocommerce checkout'),
(9717, 'woocommerce checkout custom fields'),
(9718, 'woocommerce checkout field editor'),
(9719, 'woocommerce checkout manager'),
(9720, 'woocommerce checkout page editor'),
(9831, 'woocommerce color swatches'),
(9815, 'woocommerce coming soon product'),
(9915, 'woocommerce coming soon product with countdown'),
(9751, 'woocommerce compare'),
(9752, 'woocommerce compare products'),
(9753, 'woocommerce compare products plugin'),
(9916, 'woocommerce countdown'),
(9917, 'woocommerce countdown timer'),
(10000, 'woocommerce coupon box'),
(9896, 'woocommerce coupon code'),
(9897, 'woocommerce coupons'),
(9973, 'woocommerce cross sell'),
(9990, 'woocommerce currency switcher'),
(9708, 'WooCommerce Custom Product Options'),
(9908, 'woocommerce customize thank you page'),
(9670, 'woocommerce deposit'),
(9671, 'woocommerce deposits and part payments'),
(9768, 'woocommerce discount'),
(9769, 'woocommerce discount plugin'),
(9770, 'woocommerce dynamic pricing &amp; discounts'),
(9839, 'woocommerce email'),
(9840, 'woocommerce email customizer'),
(9841, 'woocommerce email template'),
(9842, 'WooCommerce Email Template Customizer'),
(9991, 'woocommerce exchange rate'),
(9721, 'WooCommerce Extra Checkout Fields'),
(9709, 'woocommerce extra product data fields'),
(9710, 'woocommerce extra product options'),
(9828, 'woocommerce facebook chatbot'),
(9904, 'woocommerce fake reviews'),
(9800, 'woocommerce filter by category'),
(9801, 'woocommerce filter plugin'),
(9802, 'woocommerce filters'),
(9679, 'woocommerce for real estate'),
(9783, 'woocommerce free gift'),
(9977, 'woocommerce free shipping'),
(9978, 'woocommerce free shipping bar'),
(9979, 'woocommerce free shipping over amount'),
(9980, 'WooCommerce Free shipping plugin'),
(9864, 'woocommerce funnel'),
(9692, 'woocommerce gift'),
(9784, 'woocommerce gift box plugin'),
(9693, 'woocommerce gift card'),
(9694, 'woocommerce gift card plugin'),
(9695, 'woocommerce gift cards all in one'),
(9696, 'woocommerce gift certificate'),
(9785, 'woocommerce gift product'),
(9697, 'woocommerce gift vouchers'),
(9786, 'woocommerce gift wrap'),
(9796, 'WooCommerce Grouped Product'),
(9941, 'woocommerce instagram shop'),
(9942, 'woocommerce instagram shopping'),
(9943, 'woocommerce lookbook'),
(9936, 'woocommerce lucky wheel'),
(9811, 'woocommerce make an offer'),
(9682, 'woocommerce membership plugin'),
(9683, 'woocommerce monthly subscription'),
(9992, 'woocommerce multi currency'),
(9993, 'woocommerce multi currency switcher'),
(9983, 'woocommerce notification'),
(9812, 'woocommerce open pricing'),
(9865, 'woocommerce order bump'),
(9886, 'woocommerce order tracker plugin'),
(9887, 'woocommerce order tracking plugin'),
(9888, 'woocommerce orders tracking'),
(9672, 'woocommerce partial payment'),
(9673, 'woocommerce payments deposits'),
(9947, 'woocommerce photo reviews'),
(9756, 'woocommerce point of sale'),
(9737, 'woocommerce points and rewards'),
(9974, 'woocommerce popup after add to cart'),
(9757, 'woocommerce pos'),
(9758, 'woocommerce pos integration'),
(9759, 'woocommerce pos plugin'),
(9760, 'woocommerce pos system'),
(9816, 'woocommerce pre order plugin'),
(9817, 'woocommerce pre orders'),
(9818, 'woocommerce preorders'),
(9803, 'woocommerce price filter'),
(9965, 'woocommerce product builder'),
(9797, 'woocommerce product bundle'),
(9966, 'woocommerce product configurator'),
(9711, 'woocommerce product custom options'),
(9821, 'woocommerce product dimensions'),
(9712, 'woocommerce product extra options'),
(9804, 'woocommerce product filter plugin'),
(9805, 'woocommerce product filters'),
(9787, 'woocommerce product gift wrap'),
(9745, 'woocommerce product image external url'),
(9713, 'woocommerce product options'),
(9948, 'woocommerce product reviews'),
(9949, 'woocommerce product reviews pro'),
(9950, 'woocommerce product reviews shortcode'),
(9684, 'woocommerce product subscription'),
(9918, 'woocommerce product timer'),
(9832, 'woocommerce product variations'),
(9984, 'woocommerce push notification'),
(9771, 'woocommerce quantity based pricing'),
(9772, 'woocommerce quantity discounts'),
(9680, 'woocommerce real estate'),
(9685, 'woocommerce recurring payments'),
(9951, 'woocommerce review for discount'),
(9952, 'woocommerce review reminder'),
(9953, 'woocommerce reviews'),
(9954, 'woocommerce reviews plugin'),
(9955, 'woocommerce reviews shortcode'),
(9738, 'woocommerce reward points'),
(9739, 'woocommerce rewards'),
(9919, 'woocommerce sales countdown'),
(9920, 'woocommerce sales counter'),
(9866, 'woocommerce sales funnel'),
(9985, 'woocommerce sales notification'),
(9889, 'woocommerce shipment tracking'),
(9822, 'woocommerce size chart'),
(9823, 'woocommerce size chart plugin'),
(9824, 'woocommerce size guide'),
(9898, 'woocommerce smart coupons'),
(9937, 'woocommerce spin wheel'),
(9686, 'woocommerce subscription plugin'),
(9687, 'woocommerce subscriptions'),
(9833, 'woocommerce swatches'),
(9909, 'woocommerce thank you page'),
(9778, 'woocommerce to shopify'),
(9779, 'woocommerce to shopify migration'),
(9890, 'woocommerce tracking'),
(9891, 'woocommerce tracking number'),
(9698, 'woocommerce ultimate gift card'),
(9834, 'woocommerce variation swatches'),
(9835, 'woocommerce variation swatches and photos'),
(9905, 'woocommerce virtual reviews'),
(9843, 'woocommerce_email_order_details'),
(9806, 'woof product filter'),
(9807, 'woof woocommerce product filter'),
(9761, 'woopos'),
(9856, 'wooshark'),
(9674, 'wordpress'),
(9925, 'wordpress cookie consent'),
(9926, 'wordpress cookie notice'),
(9927, 'wordpress cookie plugin'),
(9928, 'wordpress cookie policy'),
(9725, 'wordpress customize email template'),
(9857, 'wordpress dropship plugin'),
(9726, 'wordpress email customizer'),
(10001, 'wordpress email newsletter plugin'),
(10002, 'wordpress email subscription plugin'),
(9727, 'wordpress email template builder'),
(9728, 'wordpress email templates designer'),
(9746, 'wordpress gallery custom links'),
(9747, 'wordpress gallery link'),
(9748, 'wordpress gallery with links'),
(9929, 'wordpress gdpr'),
(9930, 'wordpress gdpr plugin'),
(9703, 'WordPress Helpdesk Support Ticket System'),
(9749, 'wordpress image links'),
(9740, 'wordpress points and rewards'),
(9762, 'wordpress pos'),
(9967, 'wordpress product configurator'),
(9688, 'wordpress recurring payments'),
(9741, 'wordpress rewards plugin'),
(9934, 'wordpress spin wheel plugin'),
(9729, 'wp email template'),
(9808, 'yith woocommerce ajax product filter'),
(9742, 'yith woocommerce points and rewards');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `product_name` (`product_name`);

--
-- Chỉ mục cho bảng `product_category`
--
ALTER TABLE `product_category`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `product_tag`
--
ALTER TABLE `product_tag`
  ADD PRIMARY KEY (`product_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Chỉ mục cho bảng `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=399;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9258;

--
-- AUTO_INCREMENT cho bảng `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10003;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `product_category`
--
ALTER TABLE `product_category`
  ADD CONSTRAINT `product_category_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_category_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_tag`
--
ALTER TABLE `product_tag`
  ADD CONSTRAINT `product_tag_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_tag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
