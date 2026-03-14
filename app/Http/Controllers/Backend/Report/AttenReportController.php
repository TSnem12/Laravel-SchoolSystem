<?php

namespace App\Http\Controllers\Backend\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\EmployeeAttendance;
use App\Models\User;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class AttenReportController extends Controller
{
    public function AttenReportView(){
    	$data['employees'] = User::where('usertype','employee')->get();
    	return view('backend.report.attend_report.attend_report_view',$data);
    }


    public function AttenReportGet(Request $request){

    	$employee_id = $request->employee_id;
    	if ($employee_id != '') {
    		$where[] = ['employee_id', $employee_id];
    	}
    	
        $date = date('Y-m', strtotime($request->date));
    	if ($date != '') {
    		$where[] = ['date','like', $date.'%'];
    	}

        $singleAttendance = EmployeeAttendance::with(['user'])->where($where)->get();

        if ($singleAttendance->isNotEmpty()) {
            $data['allData'] = $singleAttendance;
        
            $data['absents'] = $singleAttendance->where('attend_status','Absent')->count();

            $data['leaves'] = $singleAttendance->where('attend_status','Leave')->count();

            $data['month'] = date('m-Y', strtotime($request->date));

            $pdf = PDF::loadView('backend.report.attend_report.attend_report_pdf', $data);
            $pdf->SetProtection(['copy', 'print'], '', 'pass');
            return $pdf->stream('document.pdf');

        } else {
            
            $notification = array(
                'message' => 'Sorry These Criteria Dose not match',
                'alert-type' => 'error'
            );

            return redirect()->back()->with($notification);
        }


    } // end Method 

}
