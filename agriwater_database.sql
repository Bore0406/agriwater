-- =====================================================
-- 智慧農業系統「田地節能用水對比」
-- 資料庫建置完整指令碼 (DDL + DML)
-- 適用環境：Microsoft SQL Server / SSMS 20.2.1
-- 第 13 組：吳濬澤、洪鵬翔、許珺翔、蔡善宇
-- =====================================================

-- 【執行說明】
-- 1. 開啟 SSMS 20.2.1，連線至 SQL Server
-- 2. 新建資料庫（建議命名 DB2_49），切換至該資料庫後執行
-- 3. 全選本檔案內容，按 F5 執行

-- =====================================================
-- ① 清除舊資料表（重新建立時先執行）
-- =====================================================
IF OBJECT_ID('dbo.crop',                'U') IS NOT NULL DROP TABLE dbo.crop;
IF OBJECT_ID('dbo.cultivation_record',  'U') IS NOT NULL DROP TABLE dbo.cultivation_record;
IF OBJECT_ID('dbo.messages',            'U') IS NOT NULL DROP TABLE dbo.messages;
IF OBJECT_ID('dbo.farmland',            'U') IS NOT NULL DROP TABLE dbo.farmland;
IF OBJECT_ID('dbo.users',               'U') IS NOT NULL DROP TABLE dbo.users;
GO

-- =====================================================
-- ② 建立資料表
-- =====================================================

-- 1. 使用者帳號資料表 (users) ← 網站登入用，對應 member
CREATE TABLE dbo.users (
    u_no       INT            IDENTITY(1,1) NOT NULL,  -- 流水號 (= member_id)
    u_account  NVARCHAR(50)   NOT NULL UNIQUE,          -- 登入帳號（唯一）
    u_password NVARCHAR(255)  NOT NULL,                 -- bcrypt 雜湊密碼
    u_name     NVARCHAR(50)   NOT NULL,                 -- 真實姓名（支援中文）
    u_address  NVARCHAR(200)  NULL,                     -- 聯絡地址
    u_phone    VARCHAR(20)    NULL,                     -- 聯絡電話
    CONSTRAINT pk_users PRIMARY KEY (u_no)
);
GO

-- 2. 農田資料表 (farmland) ← 依賴 users
CREATE TABLE dbo.farmland (
    farmland_id        INT           IDENTITY(1,1) NOT NULL,
    actual_water_usage DECIMAL(10,2) NOT NULL DEFAULT 0.00,  -- 實際用水量（公升）
    is_energy_saving   BIT           NOT NULL DEFAULT 0,     -- 0:未節能 / 1:節能
    member_id          INT           NOT NULL,               -- FK → users.u_no
    CONSTRAINT pk_farmland PRIMARY KEY (farmland_id),
    CONSTRAINT fk_farmland_member
        FOREIGN KEY (member_id) REFERENCES dbo.users(u_no)
        ON UPDATE CASCADE
);
GO

-- 3. 留言板資料表 (messages) ← 獨立，不綁定登入帳號（訪客也可留言）
CREATE TABLE dbo.messages (
    m_id       INT            IDENTITY(1,1) NOT NULL,
    m_name     NVARCHAR(50)   NOT NULL,                -- 留言者暱稱
    m_content  NVARCHAR(MAX)  NOT NULL,                -- 留言內容
    m_date     DATETIME       NOT NULL DEFAULT GETDATE(), -- 發布時間
    m_feedback NVARCHAR(MAX)  NULL,                    -- 管理員回覆
    CONSTRAINT pk_messages PRIMARY KEY (m_id)
);
GO

-- 4. 耕種記錄資料表 (cultivation_record) ← 依賴 farmland
CREATE TABLE dbo.cultivation_record (
    record_id   INT           IDENTITY(1,1) NOT NULL,
    farmland_id INT           NOT NULL,
    record_date DATE          NOT NULL,
    water_data  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT pk_cultivation_record PRIMARY KEY (record_id),
    CONSTRAINT fk_record_farmland
        FOREIGN KEY (farmland_id) REFERENCES dbo.farmland(farmland_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);
GO

-- 5. 農作物資料表 (crop) ← 複合主鍵，依賴 farmland
CREATE TABLE dbo.crop (
    farmland_id INT           NOT NULL,
    sequence_no INT           NOT NULL,
    description NVARCHAR(255) NULL,
    CONSTRAINT pk_crop PRIMARY KEY (farmland_id, sequence_no),
    CONSTRAINT fk_crop_farmland
        FOREIGN KEY (farmland_id) REFERENCES dbo.farmland(farmland_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);
GO

-- =====================================================
-- ③ 效能優化索引
-- =====================================================
CREATE NONCLUSTERED INDEX IX_cultivation_record_date
    ON dbo.cultivation_record (record_date);
GO
CREATE NONCLUSTERED INDEX IX_farmland_member
    ON dbo.farmland (member_id);
GO

-- =====================================================
-- ④ 範例資料 (DML)
-- 密碼皆為 test1234（bcrypt 雜湊）
-- =====================================================

INSERT INTO dbo.users (u_account, u_password, u_name, u_address, u_phone) VALUES
    ('farmer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', N'張大農', N'台中市霧峰區中正路100號', '04-22001234'),
    ('farmer2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', N'林水田', N'彰化縣二水鄉農業路22號',  '04-87651234'),
    ('admin',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', N'系統管理員', N'台中市西屯區',          '04-00000000');
GO

INSERT INTO dbo.farmland (actual_water_usage, is_energy_saving, member_id) VALUES
    (450.00, 1, 1),
    (380.00, 1, 1),
    (620.50, 0, 2),
    (510.00, 0, 2);
GO

INSERT INTO dbo.crop (farmland_id, sequence_no, description) VALUES
    (1, 1, N'水稻 — 台梗九號'),
    (1, 2, N'芭樂 — 珍珠拔'),
    (2, 1, N'香蕉 — 北蕉'),
    (3, 1, N'水稻 — 台南11號'),
    (3, 2, N'玉米 — 台農甜玉米'),
    (4, 1, N'甘蔗 — 台糖134號');
GO

INSERT INTO dbo.cultivation_record (farmland_id, record_date, water_data) VALUES
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
GO

INSERT INTO dbo.messages (m_name, m_content, m_date, m_feedback) VALUES
    (N'張大農',   N'請問能否新增釋迦的標準用水量？',     GETDATE(), NULL),
    (N'林水田',   N'系統用起來很方便，謝謝開發團隊！',   GETDATE(), N'感謝您的支持！未來會繼續優化。'),
    (N'訪客農友', N'香蕉的建議用水量是多少？',           GETDATE(), N'香蕉每公畝建議用水量約420公升，請參考典藏系統。');
GO

-- =====================================================
-- ⑤ 驗證查詢
-- =====================================================

-- 查看所有使用者
SELECT u_no, u_account, u_name, u_address, u_phone FROM dbo.users;

-- 查看各農田及所屬會員
SELECT f.farmland_id,
       u.u_name AS 會員姓名,
       f.actual_water_usage AS 實際用水量,
       CASE f.is_energy_saving WHEN 1 THEN N'節能' ELSE N'超用' END AS 節能狀態
FROM dbo.farmland f
JOIN dbo.users u ON f.member_id = u.u_no;

-- 數據分析：各農田節能量（理論標準 500 公升/筆）
SELECT f.farmland_id,
       u.u_name AS 農田所有人,
       SUM(cr.water_data)                          AS 實際總用水,
       COUNT(cr.record_id) * 500.00               AS 理論標準量,
       COUNT(cr.record_id) * 500.00 - SUM(cr.water_data) AS 節省量
FROM dbo.cultivation_record cr
JOIN dbo.farmland f ON cr.farmland_id = f.farmland_id
JOIN dbo.users u    ON f.member_id    = u.u_no
GROUP BY f.farmland_id, u.u_name
ORDER BY 節省量 DESC;

-- 查看留言板
SELECT m_id, m_name, m_content, m_date,
       ISNULL(m_feedback, N'（尚未回覆）') AS 管理員回覆
FROM dbo.messages
ORDER BY m_date DESC;
GO
