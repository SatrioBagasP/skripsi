<?php

namespace App\Http\Controllers\Notifikasi;

use App\Http\Controllers\Controller;

class NotifikasiController extends Controller
{
    public function sendMessage($noHp, $message, $nextVerifikator, $messageFor)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $noHp,
                'message' => $message,
                'countryCode' => '62', //optional
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Rgpt2U1tVrXx7NsRTNDa'
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
        }
        curl_close($curl);

        if (isset($error_msg)) {
            $response = json_decode($error_msg, true);
        }
        $response = json_decode($response, true);

        $notifGagal = false;
        $alasanNotif = '';
        if ($response['status'] == false) {
            $notifGagal = true;
            $alasanNotif = $response['reason'] ?? 'Tidak diketahui';
        }
        if ($messageFor == 'Pengajuan') {
            return 'Pengajuan Disetujui dan Berhasil Diajukan ke Verifikator ' . $nextVerifikator . ($notifGagal ? '. Namun notifikasi tidak berhasil dikirim dikarenakan "' . $alasanNotif . '". Silakan hubungi ' . $nextVerifikator . ' secara langsung atau minta admin mengecek data ' . $nextVerifikator : '');
        } elseif ($messageFor == 'Ditolak') {
            return 'Pengajuan Berhasil Ditolak' . ($notifGagal ? '. Namun notifikasi tidak berhasil dikirim dikarenakan "' . $alasanNotif . '". Silakan hubungi ' . $nextVerifikator . ' secara langsung atau minta admin mengecek data ' . $nextVerifikator : '');
        } elseif ($messageFor == 'Diterima') {
            return 'Pengajuan Berhasil Diterima' . ($notifGagal ? '. Namun notifikasi tidak berhasil dikirim dikarenakan "' . $alasanNotif . '". Silakan hubungi ' . $nextVerifikator . ' secara langsung atau minta admin mengecek data ' . $nextVerifikator : '');
        }
    }

    public function generateMessageForVerifikator($nama = 'dummy', $jenisPengajuan = 'dummy', $judulKegiatan = 'dummy', $unitKemahasiswaan = 'dummy', $route = 'dummy')
    {
        $pesan = "Yth. Bapak/Ibu, " . $nama . ",\n\nTerdapat pengajuan baru yang memerlukan verifikasi dari Anda dalam sistem informasi kegiatan kemahasiswaan, dengan detail sebagai berikut:\n\n"
            . "- Jenis Pengajuan: " . $jenisPengajuan . "\n"
            . "- Judul Kegiatan: " . $judulKegiatan . "\n"
            . "- Unit Kemahasiswaan: " . $unitKemahasiswaan . "\n";

        $pesan .= "\nMohon untuk dapat segera melakukan verifikasi agar proses selanjutnya dapat berjalan dengan lancar.\nAnda dapat memeriksa dan memproses pengajuan melalui tautan berikut:\n"  . $route .  "\n\nTerima kasih atas perhatian dan kerjasamanya\n\nHormat kami,\nTim Sistem Informasi Kegiatan Kemahasiswaan";

        return $pesan;
    }

    public function generateMessageForRejected($jenisPengajuan = 'dummy', $judulKegiatan = 'dummy', $unitKemahasiswaan = 'dummy', $alasanTolak = 'dummy', $route = 'dummy')
    {
        $pesan = "Yth. " . $unitKemahasiswaan . ",\n\nPengajuan " . $jenisPengajuan . " dengan judul kegiatan " . $judulKegiatan . " yang Anda ajukan melalui sistem informasi kegiatan kemahasiswaan telah ditolak oleh verifikator dengan alasan sebagai berikut:\n\n'*_" . $alasanTolak . "_*'\n\nMohon untuk melakukan revisi sesuai catatan di atas, dan ajukan kembali melalui sistem agar dapat diproses lebih lanjut.Anda dapat melihat dan memperbaiki pengajuan Anda melalui tautan berikut:\n" . $route . "\n\nTerima kasih atas perhatian dan kerjasamanya\n\nHormat kami,\nTim Sistem Informasi Kegiatan Kemahasiswaan";

        return $pesan;
    }

    public function generateMessageForAccepted($jenisPengajuan = 'dummy', $judulKegiatan = 'dummy', $ketua = 'dummy')
    {
        $pesan = "Yth. " . $ketua . ",\n\nPengajuan " . $jenisPengajuan . " dengan judul kegiatan " . $judulKegiatan . " yang Anda ajukan melalui sistem informasi kegiatan kemahasiswaan telah disetujui sepenuhnya (ACC Final)\n\nTerima kasih atas perhatian dan kerjasamanya\n\nHormat kami,\nTim Sistem Informasi Kegiatan Kemahasiswaan";

        return $pesan;
    }
}
