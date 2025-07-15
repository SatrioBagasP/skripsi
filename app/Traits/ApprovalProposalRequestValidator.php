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

    public function urlProposalEligible($proposal)
    {
        return match ($proposal->status) {
            'Pending Dosen' => route('approval-proposal.approvalDosen'),
            'Pending Kaprodi' => route('approval-proposal.approvalKaprodi'),
            'Pending Minat dan Bakat' => route('approval-proposal.approvalMinatBakat'),
            'Pending Layanan Mahasiswa' => route('approval-proposal.approvalLayananMahasiswa'),
            'Pending Wakil Rektor' => route('approval-proposal.approvalWakilRektor'),
            default => null,
        };
    }

    public function approvalEligible($proposal)
    {
        $admin = Gate::allows('admin');
        $dosenPj = $admin ? '' : $proposal->dosen_id == Auth::user()->userable_id;
        $jurusanId = $proposal->user->userable->jurusan  ?  $proposal->user->userable->jurusan_id : $proposal->ketua->jurusan_id;
        $kaprodiJurusan = $admin ? '' : Auth::user()->userable->jurusan_id == $jurusanId;
        if (($proposal->status == 'Rejected' || $proposal->status == 'Draft' || $proposal->status == 'Accepted') && ($dosenPj || $admin)) {
            return false;
        } elseif (($proposal->is_acc_dosen == false && Gate::allows('approval') && $dosenPj)) {
            return true;
        } elseif (($proposal->is_acc_kaprodi == false && Gate::allows('kaprodi') && $kaprodiJurusan) || $admin) {
            return true;
        } elseif (($proposal->is_acc_minat_bakat == false && Gate::allows('minat-bakat') || $admin)) {
            return true;
        } elseif (($proposal->is_acc_layanan == false && Gate::allows('layanan-mahasiswa') || $admin)) {
            return true;
        } elseif (($proposal->is_acc_wakil_rektor == false && Gate::allows('wakil-rektor') || $admin)) {
            return true;
        } else {
            return false;
        }
    }

    public function showApprovalEligible($proposal)
    {
        $admin = Gate::allows('admin');
        $dosenPj = $admin ? '' : $proposal->dosen_id == Auth::user()->userable_id;
        $jurusanId = $proposal->user->userable->jurusan  ?  $proposal->user->userable->jurusan_id : $proposal->ketua->jurusan_id;
        $kaprodiJurusan = $admin ? '' : Auth::user()->userable->jurusan_id == $jurusanId;
        if (($proposal->status == 'Rejected' || $proposal->status == 'Draft' || $proposal->status == 'Accepted') && $dosenPj) {
            return true;
        } elseif (($proposal->is_acc_dosen == false && Gate::allows('approval') && $dosenPj)) {
            return true;
        } elseif (($proposal->is_acc_kaprodi == false && Gate::allows('kaprodi') && $kaprodiJurusan) || $dosenPj || $admin) {
            return true;
        } elseif (($proposal->is_acc_minat_bakat == false && Gate::allows('minat-bakat') || $dosenPj || $admin)) {
            return true;
        } elseif (($proposal->is_acc_layanan == false && Gate::allows('layanan-mahasiswa') || $dosenPj || $admin)) {
            return true;
        } elseif (($proposal->is_acc_wakil_rektor == false && Gate::allows('wakil-rektor') || $dosenPj || $admin)) {
            return true;
        } else {
            return false;
        }
    }
}
