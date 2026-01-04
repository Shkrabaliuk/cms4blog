-- Migration: Add parent_id column to comments table for nested comments support
-- Run this SQL on your production database

ALTER TABLE `comments` 
ADD COLUMN `parent_id` int(11) UNSIGNED NULL AFTER `post_id`,
ADD KEY `parent_id` (`parent_id`),
ADD CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;
