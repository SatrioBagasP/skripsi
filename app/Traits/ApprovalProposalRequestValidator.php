<?php

namespace App\Traits;

use App\Models\Proposal;
use Illuminate\Support\Facades\Gate;

trait ApprovalProposalRequestValidator
{
    public function validateApprovalProposalEligible($request, $show = false)
    {
        $data = Proposal::where('id', decrypt($request->id))
            ->lockForUpdate()
            ->first();

        if (!$data) {
            if ($request->ajax()) {
                throw new \Exception('Data proposal tidak ada atau telah dihapus, silahkan refresh halaman ini atau kembali ke halaman proposal');
            } else {
                return abort(404);
            }
        } elseif (in_array($data->status, ['Draft', 'Tolak']) && !$show) {
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
            'Pending Kaprodi' => route('approval-proposal.approvalDosen'),
            'Pending Minat dan Bakat' => 'is_acc_minat_bakat',
            'Pending Layanan Mahasiswa' => 'is_acc_layanan',
            'Pending Wakil Rektor' => 'is_acc_wakil_rektor',
            default => null,
        };
    }

    public function approvalEligible($proposal)
    {

        if (($proposal->status == 'Pending Dosen' && Gate::allows('dosen')) || Gate::allows('admin')) {
            return true;
        } elseif (($proposal->status == 'Pending Kaprodi' && Gate::allows('kaprodi')) || Gate::allows('admin')) {
            return true;
        } else {
            return false;
        }
    }
}
