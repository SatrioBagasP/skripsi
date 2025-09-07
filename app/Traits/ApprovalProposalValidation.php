<?php

namespace App\Traits;

use Exception;
use Carbon\Carbon;
use App\Models\Proposal;
use App\Models\UnitKemahasiswaan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

trait ApprovalProposalValidation
{
    use UserValidation;

    public function validateUserCanApprove($proposal, $abort = null)
    {
        $user = Auth::user();

        // 1. Admin selalu bisa approve
        if (Gate::allows('admin')) {
            return true;
        }

        // 2. Dosen penanggung jawab
        if ($this->validateUserIsDosen($user)) {
            // cek apakah dosen penanggung jawab itu user yang login
            if ($proposal->dosen_id == $user->userable_id) {
                return true;
            }
        }

        // 3. Kaprodi
        if ($this->validateUserIsKaprodi($user)) {
            // cek apakah jurusan pengusul itu merupakan jurusan dari user yang login
            if ($proposal->pengusul->jurusan_id == $user->userable->jurusan_id) {
                return true;
            }
        }

        // 4. Ketua minat dan bakat
        if ($this->validateUserIsKetuaMinatBakat($user)) {
            return true;
        }

        // 5. Unit layanan mahasiswa
        if ($this->validateUserIsLayananMahasiswa($user)) {
            return true;
        }

        // 6. Wakil Rektor 1
        if ($this->validateUserIsWakilRektor1($user)) {
            return true;
        }

        $message = 'Anda tidak berhak melakukan approval pada proposal ini!';
        if ($abort) {
            abort(403, $message);
        } else {
            throw new Exception($message);
        }
    }

    public function validateProposalIsApproveable($data, $abort = null)
    {
        if (in_array($data->status, ['Draft', 'Rejected'])) {
            $message = 'Tidak bisa melakukan approval pada proposal ini dikarenakan masih belum diajukan oleh unit kemahasiswaan!';
            if ($abort) {
                abort(403, $message);
            } else {
                throw new Exception($message);
            }
        }
    }

    public function validateApprovalDosen($proposal)
    {
        $user = Auth::user();
        $admin = Gate::allows('admin');
        $dosen = $this->validateUserIsDosen($user);
        $dosen = $dosen ? $proposal->dosen_id == $user->userable_id : null;

        if ($dosen || $admin) {
            if ($proposal->status == 'Pending Dosen') {
                return true;
            } else {
                throw new Exception('Data Tidak valid untuk disetujui atau ditolak, silahkan refresh halaman ini!');
            }
        } else {
            throw new Exception('Anda tidak berhak untuk melakukan approval ini!');
        }
    }

    public function validateApprovalKaprodi($proposal)
    {
        $user = Auth::user();
        $admin = Gate::allows('admin');
        $kaprodi = $this->validateUserIsKaprodi($user);
        $kaprodi = $kaprodi ? $proposal->pengusul->jurusan_id == $user->userable->jurusan_id : null;

        if ($kaprodi || $admin) {
            if ($proposal->status == 'Pending Kaprodi') {
                return true;
            } else {
                throw new Exception('Data Tidak valid untuk disetujui atau ditolak, silahkan refresh halaman ini!');
            }
        } else {
            throw new Exception('Anda tidak berhak untuk melakukan approval ini!');
        }
    }

    public function validateApprovalKetuaMinatBakat($proposal)
    {
        $user = Auth::user();
        $admin = Gate::allows('admin');
        $minatBakat = $this->validateUserIsKetuaMinatBakat($user);

        if ($minatBakat || $admin) {
            if ($proposal->status == 'Pending Minat dan Bakat') {
                return true;
            } else {
                throw new Exception('Data Tidak valid untuk disetujui atau ditolak, silahkan refresh halaman ini!');
            }
        } else {
            throw new Exception('Anda tidak berhak untuk melakukan approval ini!');
        }
    }

    public function validateApprovalLayananMahasiswa($proposal)
    {
        $user = Auth::user();
        $admin = Gate::allows('admin');
        $layananMahasiswa = $this->validateUserIsLayananMahasiswa($user);

        if ($layananMahasiswa || $admin) {
            if ($proposal->status == 'Pending Layanan Mahasiswa') {
                return true;
            } else {
                throw new Exception('Data Tidak valid untuk disetujui atau ditolak, silahkan refresh halaman ini!');
            }
        } else {
            throw new Exception('Anda tidak berhak untuk melakukan approval ini!');
        }
    }

    public function validateApprovalWakilRektor($proposal)
    {
        $user = Auth::user();
        $admin = Gate::allows('admin');
        $wakilRektor1 = $this->validateUserIsWakilRektor1($user);

        if ($wakilRektor1 || $admin) {
            if ($proposal->status == 'Pending Wakil Rektor 1') {
                return true;
            } else {
                throw new Exception('Data Tidak valid untuk disetujui atau ditolak, silahkan refresh halaman ini!');
            }
        } else {
            throw new Exception('Anda tidak berhak untuk melakukan approval ini!');
        }
    }

    public function validateApprovalRequest($request)
    {
        $request->validate([
            'reason' => [
                Rule::requiredIf($request->boolean('approve') == false),
            ],
        ], [
            'reason.required' => 'Alasan penolakan harus diisi.',
        ]);
    }

    public function validateActionApproval($proposal)
    {

        $admin = Gate::allows('admin');
        $user = Auth::user()->load('userable.jurusan');

        $dosen = $this->validateUserIsDosen($user);
        $ketuaMinatDanBakat = $this->validateUserIsKetuaMinatBakat($user);
        $layananMahasiswa = $this->validateUserIsLayananMahasiswa($user);
        $wakilRektor = $this->validateUserIsWakilRektor1($user);
        $kaprodi = $this->validateUserIsKaprodi($user);



        $admin = Gate::allows('admin');
        $dosenPj = $admin ? false : $proposal->dosen_id == Auth::user()->userable_id;
        $jurusanId = $proposal->user->userable->jurusan  ?  $proposal->user->userable->jurusan_id : $proposal->ketua->jurusan_id;
        $kaprodiJurusan = $admin ? false : Auth::user()->userable->jurusan_id == $jurusanId;
        if (($proposal->status == 'Rejected' || $proposal->status == 'Draft' || $proposal->status == 'Accepted') && ($dosenPj || $admin)) {
            return false;
        } elseif ($proposal->status == 'Pending Dosen' && ((Gate::allows('approval') && $dosenPj) || $admin)) {
            return true;
        } elseif ($proposal->status == 'Pending Kaprodi' && ((Gate::allows('kaprodi') && $kaprodiJurusan) || $admin)) {
            return true;
        } elseif ($proposal->status == 'Pending Minat dan Bakat' && (Gate::allows('minat-bakat') || $admin)) {
            return true;
        } elseif ($proposal->status == 'Pending Layanan Mahasiswa' && (Gate::allows('layanan-mahasiswa') || $admin)) {
            return true;
        } elseif ($proposal->status == 'Pending Wakil Rektor' && (Gate::allows('wakil-rektor') || $admin)) {
            return true;
        } else {
            return false;
        }
    }
}
