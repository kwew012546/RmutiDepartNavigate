-- ========================================================
-- RMUTI Department Navigate - Database Performance Indexes
-- Run this script on your MySQL database to optimize query speeds.
-- ========================================================

-- 1. Departments table indexes
ALTER TABLE `departments`
    ADD INDEX `idx_departments_names` (`name_th`, `name_en`),
    ADD INDEX `idx_departments_building` (`building`);

-- 2. Services table indexes
ALTER TABLE `services`
    ADD INDEX `idx_services_dept` (`departments_id`),
    ADD INDEX `idx_services_name` (`service_name`);

-- 3. Department Images indexes
ALTER TABLE `department_images`
    ADD INDEX `idx_images_dept` (`departments_id`),
    ADD INDEX `idx_images_name` (`image_name`);

-- 4. Search Logs optimization indexes
ALTER TABLE `search_logs`
    ADD INDEX `idx_logs_query_selected` (`user_input`, `user_selected`),
    ADD INDEX `idx_logs_service` (`suggested_service_id`),
    ADD INDEX `idx_logs_department` (`suggested_department_id`);

-- 5. Users table indexes
ALTER TABLE `users`
    ADD INDEX `idx_users_remember_token` (`remember_token`);
