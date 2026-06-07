-- =====================================================
-- 智慧農業系統「田地節能用水對比」
-- 資料庫建置指令碼 (MySQL / XAMPP 版本)
-- 第 13 組：吳濬澤、洪鵬翔、許珺翔、蔡善宇
-- =====================================================

-- 建立並切換資料庫
CREATE DATABASE IF NOT EXISTS agriwater CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agriwater;

-- 清除舊資料表
DROP TABLE IF EXISTS crop;
DROP TABLE IF EXISTS cultivation_record;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS farmland;
DROP TABLE IF EXISTS users;

-- 1. 使用者資料表
CREATE TABLE users (
    u_no       INT          AUTO_INCREMENT PRIMARY KEY,
    u_account  VARCHAR(50)  NOT NULL UNIQUE,
    u_password VARCHAR(255) NOT NULL,
    u_name     VARCHAR(50)  NOT NULL,
    u_address  VARCHAR(200) NULL,
    u_phone    VARCHAR(20)  NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. 農田資料表
CREATE TABLE farmland (
    farmland_id        INT            AUTO_INCREMENT PRIMARY KEY,
    actual_water_usage DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    is_energy_saving   TINYINT(1)     NOT NULL DEFAULT 0,
    member_id          INT            NOT NULL,
    FOREIGN KEY (member_id) REFERENCES users(u_no) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. 留言板資料表
CREATE TABLE messages (
    m_id       INT           AUTO_INCREMENT PRIMARY KEY,
    m_name     VARCHAR(50)   NOT NULL,
    m_content  TEXT          NOT NULL,
    m_date     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    m_feedback TEXT          NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. 耕種記錄資料表
CREATE TABLE cultivation_record (
    record_id   INT           AUTO_INCREMENT PRIMARY KEY,
    farmland_id INT           NOT NULL,
    record_date DATE          NOT NULL,
    water_data  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (farmland_id) REFERENCES farmland(farmland_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. 農作物資料表
CREATE TABLE crop (
    farmland_id INT          NOT NULL,
    sequence_no INT          NOT NULL,
    description VARCHAR(255) NULL,
    PRIMARY KEY (farmland_id, sequence_no),
    FOREIGN KEY (farmland_id) REFERENCES farmland(farmland_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 範例資料（密碼皆為 test1234）
-- =====================================================

INSERT INTO users (u_account, u_password, u_name, u_address, u_phone) VALUES
('farmer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '張大農', '台中市霧峰區中正路100號', '04-22001234'),
('farmer2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '林水田', '彰化縣二水鄉農業路22號',  '04-87651234'),
('admin',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '系統管理員', '台中市西屯區',      '04-00000000');

INSERT INTO farmland (actual_water_usage, is_energy_saving, member_id) VALUES
(450.00, 1, 1),
(380.00, 1, 1),
(620.50, 0, 2),
(510.00, 0, 2);

INSERT INTO crop (farmland_id, sequence_no, description) VALUES
(1, 1, '水稻 — 台梗九號'),
(1, 2, '芭樂 — 珍珠拔'),
(2, 1, '香蕉 — 北蕉'),
(3, 1, '水稻 — 台南11號'),
(3, 2, '玉米 — 台農甜玉米'),
(4, 1, '甘蔗 — 台糖134號');

INSERT INTO cultivation_record (farmland_id, record_date, water_data) VALUES
(1, '2026-05-28', 450.00),
(1, '2026-05-29', 430.00),
(1, '2026-05-30', 410.00),
(1, '2026-05-31', 390.00),
(1, '2026-06-01', 380.00),
(1, '2026-06-02', 360.00),
(1, '2026-06-03', 340.00),
(2, '2026-05-30', 380.00),
(2, '2026-06-01', 360.00),
(2, '2026-06-02', 340.00),
(3, '2026-05-30', 620.50),
(3, '2026-06-01', 610.00),
(3, '2026-06-02', 630.00),
(4, '2026-06-01', 510.00),
(4, '2026-06-02', 495.00);

INSERT INTO messages (m_name, m_content, m_date, m_feedback) VALUES
('張大農',   '請問能否新增釋迦的標準用水量？',     NOW(), NULL),
('林水田',   '系統用起來很方便，謝謝開發團隊！',   NOW(), '感謝您的支持！未來會繼續優化。'),
('訪客農友', '香蕉的建議用水量是多少？',           NOW(), '香蕉每公畝建議用水量約420公升，請參考典藏系統。');
