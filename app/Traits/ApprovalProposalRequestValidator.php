<?php

namespace App\Traits;

use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

trait ApprovalProposalRequestValidator
{
    public function validateApprovalProposalEligible($request, $show = false)
    {
        $data = Proposal::with('mahasiswa')
            ->where('id', decrypt($request->id))
            ->lockForUpdate()
            ->first();

        if (!$data) {
            if ($request->ajax()) {
                throw new \Exception('Data proposal tidak ada atau telah dihapus, silahkan refresh halaman ini atau kembali ke halaman proposal');
            } else {
                return abort(404);
            }
        } elseif (in_array($data->status, ['Draft', 'Rejected']) && !$show) {
            if ($request->ajax()) {
                throw new \Exception('Proposal masih dalam status draft / revisi, tidak bisa memvalidasi proposal ini');
            } else {
                return abort(404);
            }
        }

        return $data;
    }

    public function checkNonJurusan($proposal)
    {
        return $proposal->user->userable->is_non_jurusan == true;
    }

    public function urlProposalEligible($proposal)
    {
        if ($proposal->status == 'Pending Dosen') {
            return route('approval-proposal.approvalDosen');
        } elseif ($proposal->status == 'Pending Kaprodi') {
            return route('approval-proposal.approvalKaprodi');
        } elseif ($proposal->status == 'Pending Minat dan Bakat') {
            return route('approval-proposal.approvalMinatBakat');
        } elseif ($proposal->status == 'Pending Layanan Mahasiswa') {
            return route('approval-proposal.approvalLayananMahasiswa');
        } elseif ($proposal->status == 'Pending Wakil Rektor') {
            return route('approval-proposal.approvalWakilRektor');
        } else {
            return null;
        }
    }
    public function approvalBtnEligible($proposal)
    {
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

    public function approvalDosenEligible($proposal)
    {
        $admin = Gate::allows('admin');
        $dosenPj = Gate::allows('approval') && $proposal->dosen_id == Auth::user()->userable_id;
        $canAcc = $admin || $dosenPj;
        if ($proposal->status != 'Pending Dosen' || !$canAcc) {
            throw new \Exception("Data Tidak valid untuk di acc atau ditolak, silahkan refresh halaman ini!");
        } else {
            return true;
        }
    }

    public function approvalKaprodiEligible($proposal)
    {
        $admin = Gate::allows('admin');
        $jurusanId = $proposal->user->userable->jurusan  ?  $proposal->user->userable->jurusan_id : $proposal->ketua->jurusan_id;
        $kaprodiJurusan = Gate::allows('kaprodi') && Auth::user()->userable->jurusan_id == $jurusanId;
        $canAcc = $admin || $kaprodiJurusan;
        if ($proposal->status != 'Pending Kaprodi' || !$canAcc) {
            throw new \Exception("Data Tidak valid untuk di acc atau ditolak, silahkan refresh halaman ini!");
        } else {
            return true;
        }
    }

    public function approvalMinatBakatEligible($proposal)
    {
        if ($proposal->status != 'Pending Minat dan Bakat' || !(Gate::allows('minat-bakat') || Gate::allows('admin'))) {
            throw new \Exception("Data Tidak valid untuk di acc atau ditolak, silahkan refresh halaman ini!");
        } else {
            return true;
        }
    }

    public function approvalLayananMahasiswaEligible($proposal)
    {
        if ($proposal->status != 'Pending Layanan Mahasiswa' || !(Gate::allows('layanan-mahasiswa') || Gate::allows('admin'))) {
            throw new \Exception("Data Tidak valid untuk di acc atau ditolak, silahkan refresh halaman ini!");
        } else {
            return true;
        }
    }

    public function approvalWakilRektorEligible($proposal)
    {
        if ($proposal->status != 'Pending Wakil Rektor' || !(Gate::allows('wakil-rektor') || Gate::allows('admin'))) {
            throw new \Exception("Data Tidak valid untuk di acc atau ditolak, silahkan refresh halaman ini!");
        } else {
            return true;
        }
    }

    public function showApprovalEligible($proposal)
    {
        $admin = Gate::allows('admin');
        $dosenPj = $admin ? '' : $proposal->dosen_id == Auth::user()->userable_id;
        $jurusanId = $proposal->user->userable->jurusan  ?  $proposal->user->userable->jurusan_id : $proposal->ketua->jurusan_id;
        $kaprodiJurusan = $admin ? '' : Auth::user()->userable->jurusan_id == $jurusanId;
        if (($proposal->status == 'Rejected' || $proposal->status == 'Draft' || $proposal->status == 'Accepted') && ($dosenPj || $admin)) {
            return true;
        } elseif ($proposal->status == 'Pending Dosen' && ((Gate::allows('approval') && $dosenPj) || $admin)) {
            return true;
        } elseif ($proposal->status == 'Pending Kaprodi' && ((Gate::allows('kaprodi') && $kaprodiJurusan) || $dosenPj || $admin)) {
            return true;
        } elseif ($proposal->status == 'Pending Minat dan Bakat' && (Gate::allows('minat-bakat') || $dosenPj || $admin)) {
            return true;
        } elseif ($proposal->status == 'Pending Layanan Mahasiswa' && (Gate::allows('layanan-mahasiswa') || $dosenPj || $admin)) {
            return true;
        } elseif ($proposal->status == 'Pending Wakil Rektor' && (Gate::allows('wakil-rektor') || $dosenPj || $admin)) {
            return true;
        } else {
            return false;
        }
    }
}
