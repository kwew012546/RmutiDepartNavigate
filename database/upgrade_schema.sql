-- ========================================================
-- RMUTI Department Navigate - Schema Upgrade Script
-- Run this script if you have an existing database and want to add
-- the structured business hours columns without losing data.
-- ========================================================

ALTER TABLE `departments`
    ADD COLUMN `workday` VARCHAR(50) DEFAULT 'Monday-Friday' AFTER `weekend_business_hours`,
    ADD COLUMN `weekday_open` VARCHAR(10) DEFAULT '08:30' AFTER `workday`,
    ADD COLUMN `weekday_close` VARCHAR(10) DEFAULT '16:30' AFTER `weekday_open`,
    ADD COLUMN `weekend_open` VARCHAR(10) DEFAULT NULL AFTER `weekday_close`,
    ADD COLUMN `weekend_close` VARCHAR(10) DEFAULT NULL AFTER `weekend_open`;

-- Populate structured columns for existing rows based on default office hours
UPDATE `departments`
SET
    `workday` = 'Monday-Friday',
    `weekday_open` = '08:30',
    `weekday_close` = '16:30'
WHERE `workday` IS NULL;
