<?php

namespace App\Http\Controllers\Notifikasi;

use App\Http\Controllers\Controller;

class NotifikasiController extends Controller
{
    public function sendMessage($noHp, $message, $target)
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

        return 'Pengajuan Berhasil Diajukan Ke ' . $target . ($notifGagal ? '. Namun notifikasi tidak berhasil dikirim dikarenakan "' . $alasanNotif . '". Silakan hubungi ' . $target . ' secara langsung atau minta admin memperbarui atau mengecek nomor ' . $target : '');
    }

    public function generateMessageForKaprodi() {}
}
