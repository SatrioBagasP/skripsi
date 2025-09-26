<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Proposal;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{

    public function index()
    {

        $data = DB::table('proposal')
            ->join('unit_kemahasiswaan as uk', 'proposal.unit_id', '=', 'uk.id')
            ->select(
                'proposal.id',
                DB::raw("
                    CONCAT(
                        proposal.name,
                        ' (', uk.name, ')',
                        CASE
                            WHEN proposal.is_harian = 0 THEN
                                CONCAT(
                                    ' (',
                                    DATE_FORMAT(start_date, '%d %b %H:%i'),
                                    ' – ',
                                    DATE_FORMAT(end_date, '%d %b %H:%i'),
                                    ')'
                                )
                            ELSE ''
                        END
                    ) as title
                "),
                DB::raw("
                    CONCAT('fc-event-wrap') as class_name
                "),
                DB::raw("
                    CASE WHEN proposal.is_harian = 1
                        THEN
                            true
                        ELSE
                            false
                        END
                    as allDay"),
                'start_date as start',
                DB::raw("
                    CASE
                        WHEN proposal.is_harian = 1
                            THEN DATE_ADD(end_date, INTERVAL 1 DAY)
                        ELSE end_date
                    END as end
                "),
            )
            ->where('proposal.status', 'Accepted')
            ->get()
            ->toArray();

        return view('Pages.Dashboard.index', compact('data'));
    }
}
