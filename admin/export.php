<?php
session_start();
require "../session.php";
require "../functions.php";

if ($role !== 'Admin') {
  header("location:../login.php");
}

$pesan = query("SELECT sewa_212279.212279_id_sewa,user_212279.212279_nama_lengkap,sewa_212279.212279_tanggal_pesan,sewa_212279.212279_jam_mulai,sewa_212279.212279_lama_sewa,sewa_212279.212279_total,bayar_212279.212279_bukti,bayar_212279.212279_konfirmasi
FROM sewa_212279
JOIN user_212279 ON sewa_212279.212279_id_user = user_212279.212279_id_user
JOIN bayar_212279 ON sewa_212279.212279_id_sewa = bayar_212279.212279_id_sewa
");

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <title>Data Pesanan</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f8f9fa;
    }
    .judul {
      color: #28a745;
      font-weight: bold;
      margin-bottom: 30px;
    }
    .table-inti {
      background-color: #343a40;
      color: white;
    }
    .table {
      background-color: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .container-fluid {
      padding: 20px;
    }
    .btn-screenshot {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
    }
    @media print {
      .btn-screenshot {
        display: none;
      }
    }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row min-vh-100">
      <div class="col-12 p-5 mt-5">
        <!-- Tombol untuk kembali atau aksi lainnya -->
        <div class="btn-screenshot">
          <a href="pesan.php" class="btn btn-secondary">Kembali</a>
          <button onclick="window.print()" class="btn btn-primary">Print</button>
        </div>

        <!-- Konten -->
        <h3 class="judul text-center">Data Pesanan</h3>
        <hr>
        <div class="table-responsive">
          <table class="table table-hover mt-3">
            <thead class="table-inti">
              <tr>
                <th scope="col">Id</th>
                <th scope="col">Nama Customer</th>
                <th scope="col">Tanggal Pesan</th>
                <th scope="col">Tanggal Main</th>
                <th scope="col">Lama (Jam)</th>
                <th scope="col">Total (Rp)</th>
                <th scope="col">Bukti Bayar</th>
                <th scope="col">Status</th>
              </tr>
            </thead>
            <tbody class="text">
              <?php $i = 1; ?>
              <?php foreach ($pesan as $row) : ?>
                <tr>
                  <td><?= $i++; ?></td>
                  <td><?= $row["212279_nama_lengkap"]; ?></td>
                  <td><?= $row["212279_tanggal_pesan"]; ?></td>
                  <td><?= $row["212279_jam_mulai"]; ?></td>
                  <td><?= $row["212279_lama_sewa"]; ?> Jam</td>
                  <td>Rp <?= number_format($row["212279_total"], 0, ',', '.'); ?></td>
                  <td>
                    <?php if (!empty($row["212279_bukti"])): ?>
                      <img src="../img/<?= $row["212279_bukti"]; ?>" width="80" height="80" class="img-thumbnail" alt="Bukti Bayar">
                    <?php else: ?>
                      <span class="text-muted">Tidak ada bukti</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge <?= $row["212279_konfirmasi"] == 'Terkonfirmasi' ? 'bg-success' : ($row["212279_konfirmasi"] == 'Sudah Bayar' ? 'bg-warning' : 'bg-secondary'); ?>">
                      <?= $row["212279_konfirmasi"]; ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Summary atau informasi tambahan -->
        <div class="mt-4">
          <div class="row">
            <div class="col-md-6">
              <h5>Total Pesanan: <?= count($pesan); ?></h5>
            </div>
            <div class="col-md-6 text-end">
              <small class="text-muted">Data diambil pada: <?= date('d-m-Y H:i:s'); ?></small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>

</html>