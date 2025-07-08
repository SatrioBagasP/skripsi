<?php

namespace App\Traits;

use App\Models\Proposal;

trait ApprovalProposalRequestValidator
{
    public function validateApprovalProposalStatus($request)
    {
        $data = Proposal::findOrFail(decrypt($request->id));

        if (in_array($data->status, ['Draft', 'Tolak'])) {
            if ($request->ajax()) {
                throw new \Exception('Proposal masih dalam status draft / revisi, tidak bisa memvalidasi proposal ini');
            } else {
                return abort(404);
            }
        }

        return $data;
    }
}
