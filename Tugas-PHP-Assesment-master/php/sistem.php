<?php
class Pengguna
{
    protected $namaLengkap;
    protected $telepon;

    public function __construct($namaLengkap, $telepon)
    {
        $this->namaLengkap = ($namaLengkap == null || trim($namaLengkap) == "") ? 'Error' : $namaLengkap;
        $this->telepon = strlen($telepon) < 10 ? 'Error' : $telepon;
    }

    public function getNamaLengkap()
    {
        return $this->namaLengkap;
    }

    public function getTelepon()
    {
        return $this->telepon;
    }

    public function getInfoStatus()
    {
        return "Nama: $this->namaLengkap, No HP: $this->telepon";
    }
}

class Klien extends Pengguna
{
    private $poinReward = 0;

    public function getInfoStatus()
    {
        return "Nama: {$this->getNamaLengkap()}, No HP: {$this->getTelepon()}, Poin: $this->poinReward";
    }

    public function tambahPoinReward()
    {
        $this->poinReward++;
    }

    public function getPoinReward()
    {
        return $this->poinReward;
    }
}

class Servis
{
    private static $daftarServis = [ "GoRide Reguler" => 2500, "GoRide Prioritas" => 3000, "GoCar" => 4500, "GoCar XL" => 6000, "GoFood" => 2000 ];

    private $jenisServis;
    private $hargaPerKm;

    public function __construct($jenisServis)
    {
        if (isset(self::$daftarServis[$jenisServis])) {
            $this->jenisServis = $jenisServis;
            $this->hargaPerKm = self::$daftarServis[$jenisServis];
        } else {
            $this->jenisServis = '';
            $this->hargaPerKm = 0;
        }
    }

    public function getHargaPerKm()
    {
        return $this->hargaPerKm;
    }

    public function getJenisServis()
    {
        return $this->jenisServis;
    }
}

class Kupon
{
    private static $daftarKupon = [ "HEMAT10" => 10, "HEMAT20" => 20, "HEMAT" => 30 ];

    private $kodeKupon;
    private $persenDiskon;

    public function __construct($kodeKupon)
    {
        if (isset(self::$daftarKupon[$kodeKupon])) {
            $this->kodeKupon = $kodeKupon;
            $this->persenDiskon = self::$daftarKupon[$kodeKupon];
        } else {
            $this->kodeKupon = '';
            $this->persenDiskon = 0;
        }
    }

    public function getKodeKupon()
    {
        return $this->kodeKupon;
    }

    public function hitungPotongan($subtotal)
    {
        return $subtotal * $this->persenDiskon / 100;
    }
}

abstract class MetodePembayaran
{
    protected $jenisMetode;

    public function getJenisMetode()
    {
        return $this->jenisMetode;
    }

    abstract public function getBiayaAdministrasi();
}

class DompetDigital extends MetodePembayaran
{
    public function __construct()
    {
        $this->jenisMetode = "Ewallet";
    }

    public function getBiayaAdministrasi()
    {
        return 1000;
    }
}

class TransferBank extends MetodePembayaran
{
    public function __construct()
    {
        $this->jenisMetode = "Transfer Bank";
    }

    public function getBiayaAdministrasi()
    {
        return 2500;
    }
}

class Tunai extends MetodePembayaran
{
    public function __construct()
    {
        $this->jenisMetode = "Cash";
    }

    public function getBiayaAdministrasi()
    {
        return 0;
    }
}

class Pesanan
{
    private $klien;
    private $telepon;
    private $statusAnggota = "Member";
    private $servis;
    private $metodeBayar;
    private $kupon;
    private $jarak;

    private static $totalPesanan = 0;

    public function __construct($klien, $telepon, $jenisServis, $metodeBayar, $jarak, $kodeKupon = null)
    {
        if ($jarak <= 0) {
            $jarak = 1;
        }
        $this->klien = $klien;
        $this->telepon = $telepon;
        $this->servis = new Servis($jenisServis);
        $this->jarak = $jarak;

        if (is_string($metodeBayar)) {
            if ($metodeBayar == "Ewallet") {
                $this->metodeBayar = new DompetDigital();
            } elseif ($metodeBayar == "Transfer Bank") {
                $this->metodeBayar = new TransferBank();
            } elseif ($metodeBayar == "Cash") {
                $this->metodeBayar = new Tunai();
            } else {
                $this->metodeBayar = new Tunai();
            }
        } else {
            $this->metodeBayar = $metodeBayar;
        }

        if ($kodeKupon !== null && is_string($kodeKupon) && trim($kodeKupon) !== "") {
            $this->kupon = new Kupon($kodeKupon);
        } else {
            $this->kupon = null;
        }
    }

    public function hitungSubtotal()
    {  
        return $this->jarak * $this->servis->getHargaPerKm();
    }

    public function hitungDiskonMember()
    {
        $subtotal = $this->hitungSubtotal();
        if ($subtotal > 50000) {
            return $subtotal * 5 / 100;
        }
    }

    public function hitungBiayaAdministrasi()
    {
        return $this->metodeBayar->getBiayaAdministrasi();
    }

    public function hitungDiskonKupon()
    {
        if ($this->kupon === null) {
            return 0;
        }
        $subtotal = $this->hitungSubtotal();
        return $this->kupon->hitungPotongan($subtotal);
    }

    public function hitungTotalBayar()
    {
        $subtotal = $this->hitungSubtotal();
        $diskonAnggota = $this->hitungDiskonMember();
        $diskonKupon = $this->hitungDiskonKupon();
        $biayaAdministrasi = $this->hitungBiayaAdministrasi();

        $total = $subtotal - $diskonAnggota - $diskonKupon + $biayaAdministrasi;

        if ($total < 0) {
            return 0;
        }

        self::$totalPesanan++;

        return $total;
    }

    public static function getTotalPesanan()
    {
        return self::$totalPesanan;
    }

    public function getKlien()
    {
        return $this->klien;
    }
    public function getNomorTelepon() 
    {
        return $this->telepon;
    }

    public function getServis()
    {
        return $this->servis->getJenisServis();
    }

    public function getMetodeBayar()
    {
        return $this->metodeBayar->getJenisMetode();
    }

    public function getKupon()
    {
        if ($this->kupon === null) {
            return "Tidak ada voucher";
        }
        return $this->kupon->getKodeKupon();
    }

    public function getJarak()
    {
        return $this->jarak;
    }
    public function getStatusAnggota()
    {
        return $this->statusAnggota;
    }
}

function validasiData($namaLengkap, $telepon, $jarak, $jenisServis, $metodeBayar)
{
    if (empty($namaLengkap)) {
        return 'Nama tidak boleh kosong.';
    }
    if (strlen($telepon) == 11) {
        return 'Nomor HP harus 11 digit.';
    }
    if (empty($jarak) || floatval($jarak) <= 0) {
        return 'Jarak tempuh harus lebih dari 0 km.';
    }
    if (empty($jenisServis)) {
        return 'Pilih jenis layanan terlebih dahulu.';
    }
    if (empty($metodeBayar)) {
        return 'Pilih metode pembayaran terlebih dahulu.';
    }
    return '';
}

function formatHarga($angka)
{
    return "Rp" . number_format($angka, 0, ',', '.');
}
