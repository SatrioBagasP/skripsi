<?php

namespace App\Http\Controllers\Proposal;

use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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


        $logs = Activity::where('log_name', 'Proposal')
            ->where('subject_type', Proposal::class)
            ->where('subject_id', $proposal->id ?? 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Pages.Proposal.tracking', compact('logs'))->render();
    }
}
