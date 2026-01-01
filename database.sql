-- =============================================
-- LOGOS CMS: CLEAN CONTENT ONLY
-- Save as: logos_clean.sql
-- =============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `logos_db` DEFAULT CHARACTER SET utf8mb4;
USE `logos_db`;

-- Видаляємо все старе
DROP TABLE IF EXISTS `post_tags`;
DROP TABLE IF EXISTS `tags`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `settings`;
-- Видаляємо таблиці Rose, щоб бібліотека створила їх сама
DROP TABLE IF EXISTS `rose_toc`;
DROP TABLE IF EXISTS `rose_content`;
DROP TABLE IF EXISTS `rose_fulltext_index`;
DROP TABLE IF EXISTS `rose_keyword_index`;
DROP TABLE IF EXISTS `rose_metadata`;
DROP TABLE IF EXISTS `rose_snippet`;
DROP TABLE IF EXISTS `rose_word`;

-- 1. СТРУКТУРА КОНТЕНТУ
CREATE TABLE `settings` (
  `key` varchar(50) NOT NULL,
  `value` text,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB;

INSERT INTO `settings` (`key`, `value`) VALUES
('blog_title', '/\\ogos'),
('posts_per_page', '5');

CREATE TABLE `posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(128) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `type` enum('text','image','link','quote','code') DEFAULT 'text',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_published` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB;

CREATE TABLE `comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(11) unsigned NOT NULL,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `author_name` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB;

-- 2. ТЕСТОВИЙ ПОСТ
INSERT INTO `posts` (`title`, `slug`, `content`, `type`) VALUES
('Hello World', 'hello-world', '<p>Привіт, це тестовий пост.</p>', 'text');

SET FOREIGN_KEY_CHECKS = 1;