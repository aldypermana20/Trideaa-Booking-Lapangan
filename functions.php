<?php

$conn = mysqli_connect("localhost", "root", "", "dbfutsal");

function query($query)
{
  global $conn;
  $result = mysqli_query($conn, $query);
  $rows = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
  }
  return $rows;
}

function getCount($query)
{
  global $conn;
  $result = mysqli_query($conn, $query);
  if ($row = mysqli_fetch_assoc($result)) {
    return (int) $row['total'];
  }
  return 0;
}

function hapusMember($id)
{
  global $conn;
  mysqli_query($conn, "DELETE FROM user_212279 WHERE 212279_id_user = $id");

  return mysqli_affected_rows($conn);
}

function hapusLpg($id)
{
  global $conn;
  mysqli_query($conn, "DELETE FROM lapangan_212279 WHERE 212279_id_lapangan = $id");

  return mysqli_affected_rows($conn);
}

function hapusAdmin($id)
{
  global $conn;
  mysqli_query($conn, "DELETE FROM admin_212279 WHERE 212279_id_user = $id");

  return mysqli_affected_rows($conn);
}

function hapusPesan($id)
{
  global $conn;
  mysqli_query($conn, "DELETE FROM sewa_212279 WHERE 212279_id_sewa = $id");

  return mysqli_affected_rows($conn);
}

function daftar($data)
{
  global $conn;

  $username = strtolower(stripslashes($data["email"] ?? ''));
  $password = $data["password"] ?? '';
  $nama = $data["nama"] ?? '';
  $no_handphone = $data["hp"] ?? '';
  $alamat = $data["alamat"] ?? '';
  $gender = $data["gender"] ?? '';

  $upload = upload();
  if (!$upload) {
    return false;
  }

  $result = mysqli_query($conn, "SELECT 212279_email FROM user_212279 WHERE 212279_email = '$username'");

  if (mysqli_fetch_assoc($result)) {
    echo "<script>alert('Username sudah terdaftar!');</script>";
    return false;
  }

  mysqli_query($conn, "INSERT INTO user_212279 (212279_email,212279_password,212279_no_handphone,212279_jenis_kelamin,212279_nama_lengkap,212279_alamat,212279_foto) VALUES ('$username','$password','$no_handphone','$gender','$nama','$alamat','$upload')");
  return mysqli_affected_rows($conn);
}

function edit($data)
{
  global $conn;

  // Validasi session
  if (!isset($_SESSION["id_user"])) {
    die("Session tidak ditemukan. Harap login ulang.");
  }

  $userid = $_SESSION["id_user"];
  $username = strtolower(stripslashes($data["email"] ?? ''));
  $nama = $data["nama_lengkap"] ?? '';
  $no_handphone = $data["hp"] ?? '';
  $gender = $data["jenis_kelamin"] ?? '';
  $gambarLama = $data["fotoLama"] ?? '';

  // Cek apakah user memilih gambar baru
  if (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] === 4) {
    $gambar = $gambarLama;
  } else {
    $gambar = upload();
    if (!$gambar) {
      $gambar = $gambarLama;
    }
  }

  // Query dengan prepared statement
  $query = "UPDATE user_212279 SET 
    212279_email = ?, 
    212279_nama_lengkap = ?,
    212279_no_handphone = ?,
    212279_jenis_kelamin = ?,
    212279_foto = ?
    WHERE 212279_id_user = ?";

  $stmt = mysqli_prepare($conn, $query);
  if (!$stmt) {
    die("Gagal menyiapkan statement: " . mysqli_error($conn));
  }

  mysqli_stmt_bind_param($stmt, "sssssi", $username, $nama, $no_handphone, $gender, $gambar, $userid);
  mysqli_stmt_execute($stmt);

  if (mysqli_stmt_errno($stmt)) {
    echo "Error saat update: " . mysqli_stmt_error($stmt);
  }

  return mysqli_stmt_affected_rows($stmt);
}


function pesan($data)
{
  global $conn;

  $userid = $_SESSION["id_user"];
  $idlpg = $data["id_lpg"] ?? '';
  $tanggal_pesan = date('Y-m-d H:i:s');
  $lama = intval($data["jam_mulai"] ?? 0);
  $mulai = $data["tgl_main"] ?? '';
  $mulai_waktu = strtotime($mulai);
  $habis_waktu = $mulai_waktu + ($lama * 3600);
  $habis = date('Y-m-d\TH:i:s', $habis_waktu);
  $harga = $data["harga"] ?? 0;

  $query = "SELECT * FROM sewa_212279 WHERE 212279_id_lapangan = '$idlpg' 
            AND ((212279_jam_mulai < '$habis' AND 212279_jam_habis > '$mulai') 
            OR (212279_jam_mulai < '$mulai' AND 212279_jam_habis > '$habis'))";
  $result = mysqli_query($conn, $query);

  if (mysqli_num_rows($result) > 0) {
    return false;
  } else {
    $total = $lama * $harga;
    mysqli_query($conn, "INSERT INTO sewa_212279 (212279_id_user, 212279_id_lapangan, 212279_tanggal_pesan, 212279_lama_sewa, 212279_jam_mulai, 212279_jam_habis, 212279_harga, 212279_total) 
                         VALUES ('$userid', '$idlpg', '$tanggal_pesan', '$lama', '$mulai', '$habis', '$harga', '$total')");

    return mysqli_affected_rows($conn);
  }
}

function bayar($data)
{
  global $conn;

  $id_sewa = $data["id_sewa"] ?? '';
  $tanggal_upload = date('Y-m-d H:i:s');

  $upload = upload();
  if (!$upload) {
    return false;
  }

  mysqli_query($conn, "INSERT INTO bayar_212279 (212279_id_sewa, 212279_bukti, 212279_tanggal_upload, 212279_konfirmasi) 
                       VALUES ('$id_sewa', '$upload', '$tanggal_upload', 'Sudah Bayar')");

  return mysqli_affected_rows($conn);
}

function tambahLpg($data)
{
  global $conn;

  $lapangan_212279 = $data["lapangan"] ?? '';
  $harga = $data["harga"] ?? '';
  $ket = $data["ket"] ?? '';

  $upload = upload();
  if (!$upload) {
    return false;
  }

  $query = "INSERT INTO lapangan_212279 (212279_nama,212279_harga,212279_foto,212279_keterangan) 
            VALUES ('$lapangan_212279','$harga','$upload','$ket')";

  mysqli_query($conn, $query);
  return mysqli_affected_rows($conn);
}

function upload()
{
  if (!isset($_FILES['foto'])) {
    echo "<script>alert('File tidak ditemukan');</script>";
    return false;
  }

  $namaFile = $_FILES['foto']['name'];
  $ukuranFile = $_FILES['foto']['size'];
  $error = $_FILES['foto']['error'];
  $tmpName = $_FILES['foto']['tmp_name'];

  if ($error === 4) {
    echo "<script>alert('Pilih gambar terlebih dahulu');</script>";
    return false;
  }

  $extensiValid = ['jpg', 'png', 'jpeg'];
  $extensiGambar = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

  if (!in_array($extensiGambar, $extensiValid)) {
    echo "<script>alert('Yang anda upload bukan gambar!');</script>";
    return false;
  }

  if ($ukuranFile > 1000000) {
    echo "<script>alert('Ukuran Gambar Terlalu Besar!');</script>";
    return false;
  }

  // Folder tujuan upload
  $folderTujuan = '../img/';
  
  // Buat folder jika belum ada
  if (!is_dir($folderTujuan)) {
    mkdir($folderTujuan, 0755, true);
  }

  $namaFileBaru = uniqid() . '.' . $extensiGambar;

  // Cek hasil move_uploaded_file
  if (move_uploaded_file($tmpName, $folderTujuan . $namaFileBaru)) {
    return $namaFileBaru;
  } else {
    echo "<script>alert('Gagal mengunggah file.');</script>";
    return false;
  }
}


function editLpg($data)
{
  global $conn;

  $id = mysqli_real_escape_string($conn, $data["idlap"] ?? '');
  $lapangan_212279 = mysqli_real_escape_string($conn, $data["lapangan"] ?? '');
  $ket = mysqli_real_escape_string($conn, $data["ket"] ?? '');
  $harga = mysqli_real_escape_string($conn, $data["harga"] ?? '');
  $gambarLama = mysqli_real_escape_string($conn, $data["fotoLama"] ?? '');

  // Cek apakah ada upload foto baru
  if (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] === 4) {
    $gambar = $gambarLama;
  } else {
    $gambar = upload();
    if (!$gambar) {
      $gambar = $gambarLama; // fallback ke gambar lama jika upload gagal
    }
  }

  // Query update data lapangan
  $query = "UPDATE lapangan_212279 SET 
              212279_nama = '$lapangan_212279',
              212279_keterangan = '$ket',
              212279_harga = '$harga',
              212279_foto = '$gambar' 
            WHERE 212279_id_lapangan = '$id'";

  mysqli_query($conn, $query);

  return mysqli_affected_rows($conn);
}

function tambahAdmin($data)
{
  global $conn;

  $username = $data["username"] ?? '';
  $password = $data["password"] ?? '';
  $nama = $data["nama"] ?? '';
  $no_handphone = $data["hp"] ?? '';
  $email = $data["email"] ?? '';

  $query = "INSERT INTO admin_212279 (212279_username,212279_password,212279_nama,212279_no_handphone,212279_email) 
            VALUES ('$username','$password','$nama','$no_handphone','$email')";

  mysqli_query($conn, $query);
  return mysqli_affected_rows($conn);
}

function editAdmin($data)
{
  global $conn;

  $id = $data["id"] ?? '';
  $username = $data["username"] ?? '';
  $password = $data["password"] ?? '';
  $nama = $data["nama"] ?? '';
  $no_handphone = $data["hp"] ?? '';
  $email = $data["email"] ?? '';

  $query = "UPDATE admin_212279 SET 
    212279_username = '$username',
    212279_password = '$password',
    212279_nama = '$nama',
    212279_no_handphone = '$no_handphone',
    212279_email  = '$email' 
    WHERE 212279_id_user = '$id'";

  mysqli_query($conn, $query);
  return mysqli_affected_rows($conn);
}

function konfirmasi($id_sewa)
{
  global $conn;

  $id = $id_sewa;

  mysqli_query($conn, "UPDATE bayar_212279 SET 212279_konfirmasi = 'Terkonfirmasi' WHERE 212279_id_sewa = '$id'");
  return mysqli_affected_rows($conn);
}
