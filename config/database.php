<?php
// Config/database.php - Connects to MySQL using PDO

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3308';
$db   = getenv('DB_NAME') ?: 'learts_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     $db_error_message = $e->getMessage();
     ?>
     <!DOCTYPE html>
     <html lang="vi">
     <head>
         <meta charset="UTF-8">
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <title>Database Connection Required - Learts</title>
         <style>
             body {
                 font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                 background-color: #f4ece1;
                 color: #4a4a4a;
                 display: flex;
                 justify-content: center;
                 align-items: center;
                 min-height: 100vh;
                 margin: 0;
                 padding: 20px;
             }
             .card {
                 background: white;
                 padding: 40px;
                 border-radius: 12px;
                 box-shadow: 0 10px 25px rgba(0,0,0,0.05);
                 max-width: 600px;
                 width: 100%;
                 text-align: center;
                 border-top: 5px solid #d5b85a;
             }
             h1 {
                 color: #111;
                 font-size: 24px;
                 margin-bottom: 20px;
             }
             p {
                 font-size: 16px;
                 line-height: 1.6;
                 margin-bottom: 25px;
             }
             .instructions {
                 text-align: left;
                 font-size: 15px;
                 line-height: 1.8;
                 background: #fff8eb;
                 border-left: 4px solid #d5b85a;
                 padding: 15px 20px;
                 margin-bottom: 25px;
                 border-radius: 0 6px 6px 0;
             }
             .btn {
                 background-color: #d5b85a;
                 color: white;
                 border: none;
                 padding: 12px 30px;
                 font-size: 16px;
                 border-radius: 6px;
                 cursor: pointer;
                 text-decoration: none;
                 display: inline-block;
                 font-weight: bold;
                 transition: background 0.2s;
             }
             .btn:hover {
                 background-color: #c4a547;
             }
             .error-detail {
                 font-size: 12px;
                 color: #999;
                 margin-top: 30px;
                 border-top: 1px solid #eee;
                 padding-top: 15px;
                 text-align: left;
                 word-break: break-all;
             }
         </style>
     </head>
     <body>
         <div class="card">
             <h1>⚠️ Cần Kết Nối Cơ Sở Dữ Liệu</h1>
             <p>Ứng dụng <strong>Learts Handmade Store</strong> đã được triển khai thành công, nhưng hệ thống chưa thể kết nối đến cơ sở dữ liệu MySQL của bạn.</p>
             
             <div class="instructions">
                 <strong>Hướng dẫn cấu hình:</strong>
                 <ol style="margin: 10px 0 0 20px; padding: 0;">
                     <li>Tạo một cơ sở dữ liệu MySQL trực tuyến (ví dụ trên <em>Clever Cloud, Aiven, PlanetScale...</em>).</li>
                     <li>Nhập cấu trúc và dữ liệu mẫu từ tệp <strong>database.sql</strong> trong dự án vào CSDL vừa tạo.</li>
                     <li>Truy cập vào <strong>Vercel Dashboard &gt; Settings &gt; Environment Variables</strong> của dự án.</li>
                     <li>Thêm các biến môi trường sau:
                         <ul style="margin: 5px 0 0 0; padding-left: 20px; list-style-type: circle;">
                             <li><code>DB_HOST</code>: Địa chỉ máy chủ (Host)</li>
                             <li><code>DB_PORT</code>: Cổng kết nối (thường là 3306)</li>
                             <li><code>DB_NAME</code>: Tên cơ sở dữ liệu (Database Name)</li>
                             <li><code>DB_USER</code>: Tên đăng nhập (Username)</li>
                             <li><code>DB_PASS</code>: Mật khẩu kết nối (Password)</li>
                         </ul>
                     </li>
                 </ol>
             </div>

             <a href="https://vercel.com/dashboard" target="_blank" class="btn">Đi tới Vercel Dashboard</a>
             
             <div class="error-detail">
                 <strong>Chi tiết lỗi kết nối (PDOException):</strong><br>
                 <code><?php echo htmlspecialchars($db_error_message); ?></code>
             </div>
         </div>
     </body>
     </html>
     <?php
     exit;
}


