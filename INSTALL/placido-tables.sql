SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `mail` varchar(150) NOT NULL,
  `passw` varchar(500) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

INSERT INTO `admins` (`id`, `mail`, `passw`, `name`) VALUES
(0, 'user@placido-shop.com', '$2y$10$RX8Wvx//G0AEqoDcZpe.Xu/VpZypaSMKhzDOkOtb5U8c7WO/xFN3y', 'Admin');

CREATE TABLE `archived_sales` (
  `sale_number` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `customer` mediumtext NOT NULL,
  `customer_sup` mediumtext NOT NULL,
  `payed` tinyint(1) NOT NULL,
  `processed` tinyint(1) NOT NULL,
  `date_sale` date NOT NULL,
  `id_payment` varchar(200) NOT NULL,
  `id_card` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `categories` (
  `cat_id` int(11) NOT NULL,
  `title` mediumtext NOT NULL,
  `bl` int(11) NOT NULL,
  `br` int(11) NOT NULL,
  `level` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

INSERT INTO `categories` (`cat_id`, `title`, `bl`, `br`, `level`) VALUES
(54, 'Decorative Moroccan terracotta blue bowl', 8, 9, 3),
(22, 'Terracotta bowl', 1, 16, 0),
(50, 'Ethnic terracotta bowl', 2, 11, 1),
(51, 'Moroccan terracotta bowl', 5, 10, 2),
(52, 'Decorative style terracotta bowl', 12, 13, 1),
(53, 'Decorative Moroccan terracotta yellow bowl', 6, 7, 3),
(49, 'Natural color', 14, 15, 1),
(55, 'Rare ethnic terracotta bowl', 3, 4, 2),
(56, 'Terracotta jug', 17, 20, 0),
(57, 'Ethnic terracotta jug', 18, 19, 1);

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `firstname` varchar(150) NOT NULL,
  `lastname` varchar(150) NOT NULL,
  `tel` varchar(30) NOT NULL,
  `mail` varchar(150) NOT NULL,
  `address` varchar(500) NOT NULL,
  `post_code` varchar(50) NOT NULL,
  `city` varchar(150) NOT NULL,
  `country` varchar(100) NOT NULL,
  `firstname_sup` varchar(150) NOT NULL,
  `lastname_sup` varchar(150) NOT NULL,
  `address_sup` varchar(500) NOT NULL,
  `post_code_sup` varchar(50) NOT NULL,
  `city_sup` varchar(150) NOT NULL,
  `country_sup` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `featured_products` (
  `order_prod` int(11) NOT NULL,
  `featured_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

INSERT INTO `featured_products` (`order_prod`, `featured_id`) VALUES
(0, 48),
(1, 35),
(2, 38),
(3, 40),
(4, 39),
(5, 37);

CREATE TABLE `ip_rejected` (
  `ip` varchar(50) NOT NULL,
  `stamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `messages` (
  `mess_id` int(11) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `date_mess` datetime NOT NULL,
  `readed` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

INSERT INTO `messages` (`mess_id`, `mail`, `name`, `message`, `date_mess`, `readed`) VALUES
(0, 'user@placido-shop.com', 'Raf - dev. Placido-Shop', 'Welcome to Placido-Shop software !', '2024-11-21 15:00:00', 0);

CREATE TABLE `new_sales` (
  `sale_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `payed` tinyint(1) NOT NULL,
  `processed` tinyint(1) NOT NULL,
  `date_sale` datetime NOT NULL,
  `id_payment` varchar(600) NOT NULL,
  `id_card` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `title` varchar(500) NOT NULL,
  `text` mediumtext NOT NULL,
  `ref` varchar(500) NOT NULL,
  `quant` smallint(6) NOT NULL,
  `price` int(11) NOT NULL,
  `tax` float NOT NULL,
  `date_prod` bigint(20) NOT NULL,
  `url` mediumtext NOT NULL,
  `on_line` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

INSERT INTO `products` (`id`, `cat_id`, `title`, `text`, `ref`, `quant`, `price`, `tax`, `date_prod`, `url`, `on_line`) VALUES
(48, 57, 'Ethnic motif terracotta jug', 'Yellow terracotta jug.\r\n\r\nBeautiful decorative jug with ethnic motifs.', '', 6, 5500, 0, 1664002800, 'Ethnic-motif-terracotta-jug', 1),
(43, 53, 'Decorative Moroccan yellow bowl', 'Decorative Moroccan yellow bowl\r\nDecorative Moroccan yellow bowl\r\nDecorative Moroccan yellow bowl\r\nDecorative Moroccan yellow bowl\r\n\r\nDecorative Moroccan yellow bowl\r\nDecorative Moroccan yellow bowl', '', 7, 1940, 0, 1664089200, 'Decorative-Moroccan-yellow-bowl', 1),
(44, 54, 'Decorative Moroccan blue bowl', 'Decorative Moroccan blue bowl\r\nDecorative Moroccan blue bowl\r\n\r\nDecorative Moroccan blue bowl', '', 10, 1500, 0, 1664060400, 'Decorative-Moroccan-blue-bowl', 1),
(49, 57, 'Natural color terracotta jug', 'Natural color terracotta jug.\r\n\r\nBeautiful jug with its natural color.', '', 9, 7500, 0, 1664089200, 'Natural-color-terracotta-jug', 1),
(41, 55, 'Large ethnic style terracotta dish', 'Large ethnic style terracotta dish.\r\nPerfectly decorate your interior as well as your exterior.', '', 3, 32000, 0, 1664002800, 'Large-ethnic-style-terracotta-dish', 1),
(42, 22, 'Green terracotta bowl', 'Green terracotta bowl.\r\nWonderfully decorated your interior.', '3 2581', 10, 850, 0, 1763625600, 'Green-terracotta-bowl', 1),
(35, 52, 'Terracotta bowl shimmering colors', 'Terracotta bowl set with pretty colors.\r\nWill decorate your interior as well as your exterior perfectly !', '', 12, 2600, 0, 1664143200, 'Terracotta-bowl-shimmering-colors', 1),
(36, 49, 'Natural color terracotta bowl', 'Natural color terracotta bowl.\r\n\r\nWill perfectly decorate your interior as well as your exterior', '', 9, 1500, 0, 1664143200, 'Natural-color-terracotta-bowl', 1),
(37, 55, 'Large natural color terracotta dish', 'Large natural color terracotta dish.\r\n\r\nWill perfectly decorate your interior as well as your exterior.', '', 8, 4200, 0, 1664089200, 'Large-natural-color-terracotta-dish', 1),
(38, 51, 'Moroccan style terracotta bowl', 'Moroccan style terracotta bowl.\r\nPerfectly decorate your interior as well as your exterior !', '', 4, 1890, 0, 1664089200, 'Moroccan-style-terracotta-bowl', 1),
(39, 52, 'Moroccan style terracotta dish', 'Moroccan style terracotta dish.\r\nPerfectly decorate your interior as well as your exterior.', '', 10, 8500, 0, 1664143200, 'Moroccan-style-terracotta-dish', 1),
(40, 50, 'Ethnic style terracotta dish', 'Ethnic style terracotta dish.\r\nPerfectly decorate your interior as well as your exterior.', '', 8, 3700, 0, 1664143200, 'Ethnic-style-terracotta-dish', 1);

CREATE TABLE `products_imgs` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `order_img` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

INSERT INTO `products_imgs` (`id`, `parent_id`, `name`, `order_img`) VALUES
(6, 35, 'hylcdjaibqguxwopzkrsventmf-0-1664221879.jpg', 0),
(7, 35, 'opjdkbgtnazhmwrxycviqefsul-1-1664221879.jpg', 1),
(8, 35, 'tulexzfdhawkmiqngscojybrvp-2-1664221879.jpg', 2),
(9, 35, 'murwvyhsitngpfoqbzkdleaxcj-3-1664221879.jpg', 3),
(10, 35, 'asxfueoptirkwlgjbmynhczdvq-4-1664221880.jpg', 4),
(11, 36, 'plkexnjgcdwbavtziosyrqufhm-0-1664221889.jpg', 0),
(12, 36, 'ftvmibszqyajuwhnkprxceodgl-1-1664221889.jpg', 1),
(13, 36, 'nycdmfkrolsgwhapvjitxquzeb-2-1664221889.jpg', 2),
(14, 36, 'pugtvshamdnlqfekrxbijywzoc-3-1664221889.jpg', 3),
(27, 39, 'wviogljynhabmueszxdqfktcrp-0-1664221941.jpg', 0),
(28, 39, 'nxqrckedsomauzlhwybjpfigvt-1-1664221941.jpg', 1),
(29, 39, 'dmqtxgszijlpnbeuocyakwrvhf-2-1664221941.jpg', 2),
(30, 40, 'vjalmygfkiuwrsbozqhdntcxep-0-1664221956.jpg', 0),
(31, 40, 'rlhvksduaenoyjctgzwmqfxpbi-1-1664221956.jpg', 1),
(32, 40, 'syjgoexrqncupmflkbztviadwh-2-1664221957.jpg', 2),
(85, 44, 'hrxtepavomgcdjknwslzifquby-0-1664277833.jpg', 0),
(86, 44, 'kpyenvrhxiszjfbdmtoaqgwclu-1-1664277833.jpg', 1),
(87, 44, 'opkaejdwfblscrhqgivmuxtnyz-2-1664277833.jpg', 2),
(135, 48, 'fmlvwusahnkxpocqgdtbyerjiz-0-1668482447.webp', 0),
(136, 48, 'qbreijthzkumxvglswpcdafyon-1-1668482447.webp', 1),
(137, 48, 'fshtyqgxuopeckzndiwjrlmvab-2-1668482447.webp', 2),
(138, 48, 'awcrqnxkoziujvdtfhmbplsgey-3-1668482448.webp', 3),
(139, 41, 'lbjnsqgdeimxpcrtuofkzhyavw-0-1668488785.jpg', 0),
(140, 41, 'oncyftjmihaqdvxzureswlbpkg-1-1668488786.jpg', 1),
(141, 41, 'grmhotqijkesxdnuylavczfbpw-2-1668488786.jpg', 2),
(146, 49, 'ilveczyqjsdomrahgfnutwkbxp-0-1675091683.webp', 0),
(147, 49, 'wgktfvqxcsazbempioyjrulhnd-1-1675091683.webp', 1),
(148, 49, 'vcfrqjseimtudlxbyzwhkngaop-2-1675091684.webp', 2),
(149, 49, 'wnycvlofepgzadrjsbkhitxqum-3-1675091684.webp', 3),
(150, 43, 'iupljqcerzvwmxkgyonhtfbads-0-1721415544.jpg', 0),
(151, 43, 'kgmseopxrdhwzcvlntqujfibay-1-1721415544.jpg', 1),
(152, 43, 'lhycwbnpqveojuimxrztskdfga-2-1721415544.jpg', 2),
(153, 38, 'yhprbwdaoxsuqkemlzjcntigvf-0-1721415615.jpg', 0),
(154, 38, 'etsnzaclwxjivgkfodbmuqrpyh-1-1721415615.jpg', 1),
(155, 38, 'pzrhbeqoisjkalnuvxmgdwcfyt-2-1721415615.jpg', 2),
(156, 38, 'znyixbtdqhvlacwmespkrugfjo-3-1721415615.jpg', 3),
(157, 38, 'qygxhwtendckrjizmpvslfauob-4-1721415615.jpg', 4),
(158, 38, 'ghvkurfdjbmcsxltoawpeyqniz-5-1721415615.jpg', 5),
(159, 38, 'airuhsxbtekdgvoymjlnzqfpcw-6-1721415615.jpg', 6),
(171, 37, 'glqkonmdeurtwyfabzjxhicpvs-0-1731070023.jpg', 0),
(172, 37, 'xlpiqbctkysurmzoefahdgnjwv-1-1731070023.jpg', 1),
(173, 37, 'nqzwchsfiboxymprejkaugldtv-2-1731070023.jpg', 2),
(174, 37, 'wkeqstznarughcyvlfmjxobdip-3-1731070024.jpg', 3),
(175, 37, 'owcibxuflvetanypqrkdshjzgm-4-1731070024.jpg', 4),
(227, 42, 'umbnoregzpjdsxcwqhylktavif-0-1732127727.jpg', 0),
(228, 42, 'edbgplvroajmyqwkxzushiftcn-1-1732127727.jpg', 1);

CREATE TABLE `sold_products` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `img_name` varchar(250) NOT NULL,
  `title` varchar(300) NOT NULL,
  `ref` varchar(500) NOT NULL,
  `quant` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `tax` float NOT NULL,
  `refounded` tinyint(1) NOT NULL,
  `refounded_date` date NOT NULL,
  `refounded_amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `static_pages` (
  `page_id` int(11) NOT NULL,
  `page_title` varchar(500) NOT NULL,
  `page_url` varchar(530) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

INSERT INTO `static_pages` (`page_id`, `page_title`, `page_url`) VALUES
(1, 'Contact us by email', 'contact-us'),
(2, 'Right to retract', 'right-to-retract'),
(3, 'Terms of sale', 'terms-of-sale'),
(4, 'Know more about us', 'about-us'),
(43, 'Open-source libraries and languages ​​used', 'libraries-and-languages');

CREATE TABLE `stats_cart` (
  `day` date NOT NULL,
  `in_cart` mediumtext NOT NULL,
  `purchased` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `stats_loca` (
  `day` date NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `day_nb` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `stats_prods` (
  `day` date NOT NULL,
  `products` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `stats_token` (
  `id` tinyint(4) NOT NULL,
  `token` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `tokens` (
  `user_id` int(11) NOT NULL,
  `token` varchar(250) NOT NULL,
  `stamp` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `token_Placido` (
  `id` tinyint(4) NOT NULL,
  `token` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_shop` (
  `id` int(11) NOT NULL,
  `addr` varchar(530) NOT NULL,
  `tel` varchar(20) NOT NULL,
  `legal_addr` varchar(300) NOT NULL,
  `legal_mention` mediumtext NOT NULL,
  `mode` tinyint(1) NOT NULL,
  `by_money` int(11) NOT NULL,
  `test_pub_key` varchar(530) NOT NULL,
  `test_priv_key` varchar(530) NOT NULL,
  `prod_pub_key` varchar(530) NOT NULL,
  `prod_priv_key` varchar(530) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

INSERT INTO `user_shop` (`id`, `addr`, `tel`, `legal_addr`, `legal_mention`, `mode`, `by_money`, `test_pub_key`, `test_priv_key`, `prod_pub_key`, `prod_priv_key`) VALUES
(0, 'My shop\r\n1, road name\r\n75000 Paris', '+33 6 01 23 45 67', 'My shop\r\n1, road name\r\n75000 Paris', 'Legal notices invoice', 1, 1, '', '', '', '');

ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `archived_sales`
  ADD UNIQUE KEY `sale_number` (`sale_number`);

ALTER TABLE `categories`
  ADD PRIMARY KEY (`cat_id`);

ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `featured_products`
  ADD UNIQUE KEY `order_prod` (`order_prod`);

ALTER TABLE `ip_rejected`
  ADD UNIQUE KEY `ip` (`ip`) USING BTREE;

ALTER TABLE `messages`
  ADD PRIMARY KEY (`mess_id`);

ALTER TABLE `new_sales`
  ADD PRIMARY KEY (`sale_id`);

ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `products_imgs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sold_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`);

ALTER TABLE `static_pages`
  ADD PRIMARY KEY (`page_id`);

ALTER TABLE `stats_cart`
  ADD PRIMARY KEY (`day`);

ALTER TABLE `stats_loca`
  ADD PRIMARY KEY (`day`);

ALTER TABLE `stats_prods`
  ADD UNIQUE KEY `day` (`day`);

ALTER TABLE `stats_token`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tokens`
  ADD UNIQUE KEY `user_id` (`user_id`);

ALTER TABLE `token_Placido`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `user_shop`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `categories`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `messages`
  MODIFY `mess_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

ALTER TABLE `new_sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

ALTER TABLE `products_imgs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

ALTER TABLE `sold_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `static_pages`
  MODIFY `page_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
