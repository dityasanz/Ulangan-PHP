<?php
session_start();
require 'php/sistem.php';

if (isset($_GET['hapus'])) {
  unset($_SESSION['result']);
  header('Location: index.php');
  exit;
}

$kesalahan = '';
$result = null;

$metodeRequest = $_SERVER['REQUEST_METHOD'];
$sumber = ($metodeRequest === 'POST') ? $_POST : $_GET;

$namaLengkap = trim(isset($sumber['namaLengkap']) ? $sumber['namaLengkap'] : '');
$nomorTelepon = trim(isset($sumber['nomorTelepon']) ? $sumber['nomorTelepon'] : '');
$jarak = trim(isset($sumber['jarak']) ? $sumber['jarak'] : '');
$jenisServis = isset($sumber['jenisServis']) ? $sumber['jenisServis'] : '';
$metodeBayar = isset($sumber['metodeBayar']) ? $sumber['metodeBayar'] : '';
$kodeKupon = trim(isset($sumber['kodeKupon']) ? $sumber['kodeKupon'] : '');

if ($metodeRequest === 'POST') {
  $kesalahan = validasiData($namaLengkap, $nomorTelepon, $jarak, $jenisServis, $metodeBayar);

  if ($kesalahan === '') {
    $pesanan = new Pesanan($namaLengkap, $nomorTelepon, $jenisServis, $metodeBayar, floatval($jarak), $kodeKupon ?: null);
    $result = [
      'namaLengkap' => $pesanan->getKlien(),
      'statusAnggota' => $pesanan->getStatusAnggota(),
      'servis' => $pesanan->getServis(),
      'metodeBayar' => $pesanan->getMetodeBayar(),
      'jarakTempuh' => $pesanan->getJarak(),
      'kupon' => $pesanan->getKupon(),
      'subTotal' => formatHarga($pesanan->hitungSubtotal()),
      'diskonAnggota' => formatHarga($pesanan->hitungDiskonMember()),
      'diskonKupon' => formatHarga($pesanan->hitungDiskonKupon()),
      'biayaAdministrasi' => formatHarga($pesanan->hitungBiayaAdministrasi()),
      'totalBayar' => formatHarga($pesanan->hitungTotalBayar()),
      'nomorTelepon' => $pesanan->getNomorTelepon()
    ];
    $_SESSION['result'] = $result;
  }
}

$result = isset($result) ? $result : (isset($_SESSION['result']) ? $_SESSION['result'] : null);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Ojek Online</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="js/main.js"></script>
</head>

<body>
  <h1 class="header-form">Sistem Ojek Online</h1>
  <div class="box-form">
    <form method="POST" action="">

      <div class="group-input">
        <label>Nama</label>
        <input type="text" id="namaLengkap" name="namaLengkap" placeholder="Masukkan nama lengkap" value="<?= $namaLengkap ?>">
      </div>

      <div class="group-input">
        <label>Nomor HP</label>
        <input type="text" id="nomorTelepon" name="nomorTelepon" placeholder="Minimal 10 digit" value="<?= $nomorTelepon ?>">
      </div>

      <div class="group-input">
        <label>Jarak Tempuh (km)</label>
        <input type="text" id="jarak" name="jarak" placeholder="Contoh: 5" value="<?= $jarak ?>">
      </div>

      <div class="group-input">
        <label>Jenis Layanan</label>
        <select id="jenisServis" name="jenisServis">
          <option value="">Pilih Layanan</option>
          <option value="GoRide Reguler">GoRide Reguler</option>
          <option value="GoRide Prioritas">GoRide Prioritas</option>
          <option value="GoCar">GoCar</option>
          <option value="GoCar XL">GoCar XL</option>
          <option value="GoFood">GoFood</option>
        </select>
      </div>

      <div class="group-input">
        <label>Metode Pembayaran</label>
        <select id="metodeBayar" name="metodeBayar">
          <option value="">Pilih Pembayaran</option>
          <option value="Ewallet">Ewallet</option>
          <option value="Transfer Bank">Transfer Bank
          </option>
          <option value="Cash" >Cash</option>
        </select>
      </div>

      <div class="group-input">
        <label>Kode Voucher</label>
        <input type="text" id="kodeKupon" name="kodeKupon">
      </div>

      <input type="submit" value="Hitung Biaya" class="btn-hitung">
        <div class="group-input">
        <label >Error</label>
        <input type="text" value="<?= $kesalahan ?>">
      </div>
    </form>

    <?php if ($result): ?>
      <div class="box-hasil">
        <table>
          <tr>
            <td>Nama</td>
            <td><?= $result['namaLengkap'] ?></td>
          </tr>
          <tr>
            <td>Status</td>
            <td><?= $result['statusAnggota'] ?></td>
          </tr>
          <tr>
            <td>Layanan</td>
            <td><?= $result['servis'] ?></td>
          </tr>
          <tr>
            <td>Pembayaran</td>
            <td><?= $result['metodeBayar'] ?></td>
          </tr>
          <tr>
            <td>Jarak Tempuh</td>
            <td><?= $result['jarakTempuh'] ?> km</td>
          </tr>
                    <tr>
            <td>Nomor HP</td>
            <td><?= $result['nomorTelepon'] ?> </td>
          </tr>
          <tr>
            <td>Voucher</td>
            <td><?= $result['kupon'] ?></td>
          </tr>
          <tr>
            <td>Subtotal</td>
            <td><?= $result['subTotal'] ?></td>
          </tr>
          <tr>
            <td>Diskon Member</td>
            <td>- <?= $result['diskonAnggota'] ?></td>
          </tr>
          <tr>
            <td>Diskon Voucher</td>
            <td>- <?= $result['diskonKupon'] ?></td>
          </tr>
          <tr>
            <td>Biaya Admin</td>
            <td>+ <?= $result['biayaAdministrasi'] ?></td>
          </tr>
          <tr class="row-total">
            <td><strong>Total Bayar</strong></td>
            <td><strong><?= $result['totalBayar'] ?></strong></td>
          </tr>
        </table>
        <div class="wrapper-hapus">
          <a href="?hapus=1" class="btn-hapus">Hapus</a>

        </div>
      <?php endif; ?>
    </div>

</body>

</html>
