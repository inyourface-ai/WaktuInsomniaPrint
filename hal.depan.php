<?php
// === KONFIGURASI ===
// Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "printmalam";

// Telegram
$botToken = "ISI_BOT_TOKEN";
$chatId   = "ISI_CHAT_ID_ADMIN";

// Koneksi DB
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$success = null;

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST["name"];
    $phone    = $_POST["phone"];
    $service  = $_POST["service"];
    $pages    = $_POST["pages"];
    $note     = $_POST["note"];
    $address  = $_POST["address"];
    $datetime = $_POST["datetime"];

    // Upload file
    $filePath = null;
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $target = "uploads/" . time() . "-" . basename($_FILES["file"]["name"]);
        if (!is_dir("uploads")) mkdir("uploads");
        move_uploaded_file($_FILES["file"]["tmp_name"], $target);
        $filePath = $target;
    }

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO orders(name, phone, service, pages, note, address, datetime, file) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssissss", $name, $phone, $service, $pages, $note, $address, $datetime, $filePath);
    $stmt->execute();

    // Kirim notifikasi ke Telegram
    $text = "📥 Pesanan Baru PrintMalam24\n".
            "Nama: $name\n".
            "HP: $phone\n".
            "Layanan: $service\n".
            "Halaman: $pages\n".
            "Catatan: $note\n".
            "Alamat: $address\n".
            "Waktu: $datetime";
    file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=".urlencode($text));

    $success = "✅ Pesanan berhasil dikirim!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>PrintMalam24</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; margin: 0; padding: 0; background: #f3f4f6; color: #333; }
    .header { display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: sticky; top:0; z-index:1000; }
    .logo { font-weight: 700; font-size: 20px; }
    .btn-primary { background: linear-gradient(135deg, #4f46e5, #6366f1); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: 0.3s; }
    .btn-primary:hover { opacity: 0.9; }
    .btn-secondary { background: #e5e7eb; padding: 8px 16px; border-radius: 6px; text-decoration: none; color: #333; }
    .hero { display: flex; justify-content: space-between; align-items: center; padding: 80px 10%; background: linear-gradient(135deg, #eef2ff, #e0e7ff); }
    .hero-text { max-width: 50%; }
    .hero-text h1 { font-size: 36px; font-weight: 700; margin-bottom: 20px; }
    .hero-text p { font-size: 18px; margin-bottom: 30px; }
    .hero-img img { width: 280px; }
    .features { display: flex; justify-content: center; gap: 30px; padding: 60px 10%; }
    .card { background: white; padding: 30px; border-radius: 12px; text-align: center; width: 250px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .card:hover { transform: translateY(-5px); }
    .icon { font-size: 40px; margin-bottom: 15px; }
    .footer { text-align: center; padding: 20px; background: white; font-size: 14px; margin-top: 50px; }
    .form-section { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .form-section h2 { text-align: center; margin-bottom: 20px; }
    .order-form { display: flex; flex-direction: column; gap: 15px; }
    .order-form input, .order-form select, .order-form textarea { padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
    .order-form textarea { resize: vertical; }
    .alert-success { background: #dcfce7; color: #166534; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; }
  </style>
</head>
<body>
  <!-- Header -->
  <header class="header">
    <div class="logo">🖨️ PrintMalam24</div>
    <a href="#form" class="btn-primary">Pesan Sekarang</a>
  </header>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-text">
      <h1>Butuh Print/Fotocopy Malam Hari?</h1>
      <p>Kami siap membantu print dokumen kapan saja, bahkan larut malam.</p>
      <a href="#form" class="btn-primary">Pesan Print Sekarang</a>
    </div>
    <div class="hero-img">
      <img src="https://cdn-icons-png.flaticon.com/512/1995/1995574.png" alt="Printer Malam">
    </div>
  </section>

  <!-- Features -->
  <section class="features">
    <div class="card">
      <div class="icon">⏰</div>
      <h3>24 Jam</h3>
      <p>Layanan siap melayani kapan saja, bahkan tengah malam.</p>
    </div>
    <div class="card">
      <div class="icon">⚡</div>
      <h3>Cepat & Praktis</h3>
      <p>Proses cepat, tinggal kirim file & langsung diproses.</p>
    </div>
    <div class="card">
      <div class="icon">📍</div>
      <h3>Antar Jemput</h3>
      <p>Pilih antar-jemput untuk kenyamanan lebih.</p>
    </div>
  </section>

  <!-- Form -->
  <section class="form-section" id="form">
    <h2>FORM PESAN PRINT/FOTOCOPY</h2>
    <?php if($success) echo "<p class='alert-success'>$success</p>"; ?>
    <form method="POST" enctype="multipart/form-data" class="order-form">
      <input type="text" name="name" placeholder="Nama Lengkap" required>
      <input type="text" name="phone" placeholder="Nomor HP / WhatsApp" required>
      <select name="service">
        <option>Print Hitam Putih</option>
        <option>Print Warna</option>
        <option>Fotocopy</option>
      </select>
      <input type="number" name="pages" placeholder="Jumlah Halaman" min="1" required>
      <input type="file" name="file">
      <textarea name="note" placeholder="Catatan Tambahan (jilid, A4/F4, dsb.)"></textarea>
      <input type="text" name="address" placeholder="Alamat (opsional)">
      <input type="datetime-local" name="datetime">
      <button type="submit" class="btn-primary">KIRIM PESANAN</button>
    </form>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <p>Kontak: WA | Telegram | Instagram</p>
    <p>© 2025 PrintMalam24</p>
  </footer>
</body>
</html>
