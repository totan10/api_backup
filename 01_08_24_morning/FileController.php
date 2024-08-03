<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function dr_profile_excel1(Request $req)
    {
        if ($req->isMethod('post')) {
            $validatedData = $req->validate([
                'PHARMA_ID' => 'required',
                'file' => 'required|file'
            ]);

            $pid = $validatedData['PHARMA_ID'];
            $file = $req->file('file');
            
            $fileName = $pid . '.' . $file->getClientOriginalExtension();
            $req->file('file')->storeAs('drprofile/excel', $fileName);
            $url = asset(storage::url('app/drprofile/excel')) . "/" . $fileName;

            $fields = [
                "PHARMA_ID" => $pid,
                "EXCEL_URL" => $url
            ];

            try {
                DB::table('profile_excel')->insert($fields);
                $response = ["Success" => true, 'Message' => 'Excel file uploaded successfully', "code" => 200];
            } catch (\Exception $e) {
                $response = ["Success" => false, 'Message' => $e->getMessage(), "code" => 500];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }
        return $response;
    }

    public function calculateDates() {
        date_default_timezone_set('Asia/Kolkata');
        $cdt = date('Ymd');
        $data = [
            ["SCH_DAY" => 19, "START_MONTH" => 1, "MONTH" => 2],
            ["SCH_DAY" => 19, "START_MONTH" => 3, "MONTH" => 2]
        ];
    
        $results = [];
    
        foreach ($data as $item) {
            // Create the DateTime object for the current year
            $date = new \DateTime();
            $date->setDate($date->format('Y'), $item['START_MONTH'], $item['SCH_DAY']);
    
            // Add the month interval
            $date->modify("+{$item['MONTH']} months");
    
            // Format and store the date
            if ($date->format('Ymd')===$cdt){
                $results[] = $date->format('Ymd');
            }
            
        }
    
        // Output the results
        return response()->json($results);
    }
}
