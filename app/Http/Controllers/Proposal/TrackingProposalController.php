<?php

namespace App\Http\Controllers\Proposal;

use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LaporanKegiatan;
use Spatie\Activitylog\Models\Activity;

class TrackingProposalController extends Controller
{
    public function index()
    {
        return view('Pages.Proposal.tracking-index');
    }
    public function search(Request $request)
    {
        $request->validate(['keyword' => 'required|string']);

        $keyword = $request->keyword;

        $proposal = Proposal::where('no_proposal', $keyword)
            ->first();


        $logs = Activity::where(function ($q) {
            $q->where('log_name', 'Proposal')
                ->orWhere('log_name', 'Laporan Kegiatan');
        })
            ->where(function ($q) {
                $q->where('subject_type', Proposal::class)
                    ->orWhere('subject_type', LaporanKegiatan::class);
            })
            ->where('subject_id', $proposal->id ?? 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Pages.Proposal.tracking', compact('logs'))->render();
    }
}
