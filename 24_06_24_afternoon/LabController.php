<?php

namespace App\Http\Controllers;

use App\Models\singletst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;

class LabController extends Controller
{
    function singaltest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input   = $req->json()->all();
            if (isset($input['SECTION'])) {
                $sec = $input['SECTION'];

                $response = array();
                $data = array();

                $data1 = DB::table('l_dashboard_details')
                    ->join('package', 'package.LAB_PKG_ID', '=', 'l_dashboard_details.ID')
                    ->join('package_details', 'package.PKG_ID', '=', 'package_details.PKG_ID')

                    ->select(
                        'package.*',
                        'l_dashboard_details.PHOTO_URL',
                        'package_details.TEST_ID',
                        'package_details.TEST_NAME',
                        'package_details.COST'
                    )
                    ->where('l_dashboard_details.SECTION_ID', '=', 'D')
                    ->orderby('l_dashboard_details.SECTION_SL')
                    ->get();

                $D_DTL = [];
                $collection = collect($data1);
                $distinctValues = $collection->pluck('LAB_PKG_ID')->unique();
                //$lowestPrice = min($collection->pluck('COST')->unique());
                foreach ($distinctValues as $row) {
                    $fltr_arr = $data1->filter(function ($item) use ($row) {
                        return $item->LAB_PKG_ID === $row;
                    });

                    $T_dtl = $fltr_arr->map(function ($item) {
                        return [
                            "AGE_TYPE" => $item->AGE_TYPE,
                            // "CATEGORY" => $item->CATEGORY,
                            "FASTING" => $item->FASTING,
                            "GENDER_TYPE" => $item->GENDER_TYPE,
                            "ID_PROOF" => $item->ID_PROOF,
                            "KNOWN_AS" => $item->KNOWN_AS,
                            "NORMAL_RANGE" => $item->NORMAL_RANGE,
                            "PRESCRIPTION" => $item->PRESCRIPTION,
                            "QA1" => $item->QA1,
                            "QA2" => $item->QA2,
                            "QA3" => $item->QA3,
                            "QA4" => $item->QA4,
                            "QA5" => $item->QA5,
                            "QA6" => $item->QA6,
                            "REMARK" => $item->PKG_RMK,
                            "REPORT_TIME" => $item->REPORT_TIME,
                            "SUB_CATG" => $item->SUB_CATG,
                            "TEST_CATG" => $item->TEST_CATG,
                            "TEST_CODE" => $item->TEST_CODE,
                            "TEST_DESC" => $item->TEST_DESC,
                            "TEST_ID" => $item->TEST_ID,
                            "TEST_NAME" => $item->TEST_NAME,
                            "TEST_SAMPLE" => $item->TEST_SAMPLE,
                            "TEST_SL" => $item->TEST_SL,
                            "TEST_UNIT" => $item->TEST_UNIT,
                            "COST" => $item->PKG_COST,
                        ];
                    })->values()->all();
                    $D_DTL[]  = [
                        "PKG_ID" => $row,
                        "PKG_NAME" => $fltr_arr->first()->PKG_NAME,
                        "PKG_URL" => $fltr_arr->first()->PHOTO_URL,
                        "TOT_TEST" => count($T_dtl),
                        "TOT_COST" => array_sum(array_column($T_dtl, 'COST')),
                        "DETAILS" => $T_dtl
                    ];
                }
                $data = array_values($D_DTL);

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function alstst(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input   = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = [];
                $data = [];
                $testData = [];
                $testCosts = [];
                $banner["Banner"] = DB::table('promo_banner')
                    ->select('PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', 'TS')->get();

                $data1 = DB::table('clinic_testdata')->orderBy('TEST_ID')->get();

                foreach ($data1 as $row) {
                    $testData[$row->TEST_ID] = [
                        "TEST_ID" => $row->TEST_ID,
                        // "DASH_ID" => $row->DASH_ID,
                        "TEST_NAME" => $row->TEST_NAME,
                        "TEST_CATG" => $row->TEST_CATG,
                        "DISCOUNT" => $row->DISCOUNT,
                        "HOME_COLLECT" => $row->HOME_COLLECT,
                        "ORGAN_ID" => $row->ORGAN_ID,
                        "ORGAN_NAME" => $row->ORGAN_NAME,
                        "ORGAN_URL" => $row->ORGAN_URL,
                        "TEST_DESC" => $row->TEST_DESC,
                        "DEPARTMENT" => $row->DEPARTMENT,
                        "KNOWN_AS" => $row->KNOWN_AS,
                        "FASTING" => $row->FASTING,
                        "GENDER_TYPE" => $row->GENDER_TYPE,
                        "AGE_TYPE" => $row->AGE_TYPE,
                        "REPORT_TIME" => $row->REPORT_TIME,
                        "PRESCRIPTION" => $row->PRESCRIPTION,
                        "ID_PROOF" => $row->ID_PROOF,
                        "QA1" => $row->QA1,
                        "QA2" => $row->QA2,
                        "QA3" => $row->QA3,
                        "QA4" => $row->QA4,
                        "QA5" => $row->QA5,
                        "QA6" => $row->QA6,
                    ];
                    $testCosts[$row->TEST_ID][] = $row->COST;
                }
                foreach ($testData as $tId => &$testInfo) {
                    if (isset($testCosts[$tId])) {
                        $testInfo['MIN_COST'] = min($testCosts[$tId]);
                    }
                }

                $data = array_values($testData + $banner);

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function labsrch(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input   = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $data1 = DB::table('master_testdata')->get();
                foreach ($data1 as $row1) {
                    $tstdtl = [];
                    $tstdtl['DETAILS'] = [
                        "TEST_ID" =>   $row1->TEST_ID,
                        "TEST_NAME" => $row1->TEST_NAME,
                        "TEST_CODE" => $row1->TEST_CODE,
                        "TEST_SAMPLE" => $row1->TEST_SAMPLE,
                        "TEST_CATG" =>  $row1->TEST_CATG,
                        "ORGAN_ID" => $row1->ORGAN_ID,
                        "ORGAN_NAME" => $row1->ORGAN_NAME,
                        "ORGAN_URL" => $row1->ORGAN_URL,
                        "DEPARTMENT" => $row1->DEPARTMENT,
                        // "TEST_UNIT" => $row1->TEST_UNIT,
                        // "NORMAL_RANGE" => $row1->NORMAL_RANGE,
                        "TEST_DESC" => $row1->TEST_DESC,
                        "KNOWN_AS" => $row1->KNOWN_AS,
                        "FASTING" =>   $row1->FASTING,
                        "GENDER_TYPE" =>  $row1->GENDER_TYPE,
                        "AGE_TYPE" =>  $row1->AGE_TYPE,
                        "REPORT_TIME" =>  $row1->REPORT_TIME,
                        "PRESCRIPTION" =>  $row1->PRESCRIPTION,
                        "ID_PROOF" =>  $row1->ID_PROOF,
                        "QA1" =>       $row1->QA1,
                        "QA2" =>       $row1->QA2,
                        "QA3" =>       $row1->QA3,
                        "QA4" =>       $row1->QA4,
                        "QA5" =>       $row1->QA5,
                        "QA6" =>       $row1->QA6,
                    ];
                    $data[] = [
                        "ID" => $row1->TEST_ID,
                        "ITEM_NAME" => $row1->TEST_NAME,
                        "FIELD_TYPE" => $row1->DEPARTMENT,
                        "DETAILS" => $tstdtl['DETAILS']
                    ];
                }
                $data2 = DB::table('pharmacy')
                    ->select('pharmacy.*', DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                * SIN(RADIANS('$latt'))))),2) as KM"),)
                    //     ->whereRaw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                    // * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                    //  * SIN(RADIANS('$latt'))))),2) as KM" <= 100)
                    ->take(25)
                    ->get();
                foreach ($data2 as $row2) {
                    $cldtl = [];
                    $cldtl['DETAILS'] = [
                        "PHARMA_ID" => $row2->PHARMA_ID,
                        "PHARMA_NAME" => $row2->ITEM_NAME,
                        "ADDRESS" => $row2->ADDRESS,
                        "CITY" => $row2->CITY,
                        "DIST" => $row2->DIST,
                        "STATE" => $row2->STATE,
                        "PIN" => $row2->PIN,
                        "PHOTO_URL" => $row2->PHOTO_URL,
                        "CLINIC_TYPE" => $row2->CLINIC_TYPE,
                        "CLINIC_MOBILE" => $row2->CLINIC_MOBILE,
                        "LATITUDE" => $row2->LATITUDE,
                        "LONGITUDE" => $row2->LONGITUDE,
                        "KM" => $row2->KM,
                    ];

                    $cl = [
                        "ID" => $row2->PHARMA_ID,
                        "ITEM_NAME" => $row2->ITEM_NAME,
                        "FIELD_TYPE" => $row2->CLINIC_TYPE,
                        "DETAILS" => $cldtl['DETAILS']
                    ];
                    array_push($data, $cl);
                }

                $data3 = DB::table('dashboard')
                    ->whereIn('DASH_SECTION_ID', ['B', 'C', 'D', 'G', 'H', 'S', 'T', 'SR'])
                    ->where('STATUS', 'Active')
                    ->orderby('DASH_TYPE')
                    ->get();

                foreach ($data3 as $row3) {
                    $pkgdtl = [];
                    $pkgdtl['DETAILS'] = [
                        "AGE_TYPE" => $row3->AGE_TYPE,
                        "DESCRIPTION" => $row3->DASH_DESCRIPTION,
                        "FASTING" => $row3->FASTING,
                        "GENDER_TYPE" => $row3->GENDER_TYPE,
                        "DASH_ID" => $row3->DASH_ID,
                        "ID_PROOF" => $row3->ID_PROOF,
                        "DASH_NAME" => $row3->DASH_NAME,
                        "DASH_TYPE" => $row3->DASH_TYPE,
                        "KNOWN_AS" => $row3->KNOWN_AS,
                        "PHOTO_URL" => $row3->PHOTO_URL,
                        "PRESCRIPTION" => $row3->PRESCRIPTION,
                        "QA1" => $row3->QA1,
                        "QA2" => $row3->QA2,
                        "QA3" => $row3->QA3,
                        "QA4" => $row3->QA4,
                        "QA5" => $row3->QA5,
                        "QA6" => $row3->QA6,
                        "REPORT_TIME" => $row3->REPORT_TIME,
                        "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
                        "SECTION_SL" => $row3->POSITION,
                        "STATUS" => $row3->STATUS
                    ];

                    $pkg = [
                        "ID" => $row3->DASH_ID,
                        "ITEM_NAME" => $row3->DASH_NAME,
                        "FIELD_TYPE" => $row3->DASH_TYPE,
                        "DETAILS" => $pkgdtl['DETAILS']
                    ];
                    array_push($data, $pkg);
                }

                if ($data == null) {
                    $response = ['Success' => false, 'Message' => 'Test/Clinic not found', 'code' => 200];
                } else {
                    $response = ['Success' => true, 'data' => $data, 'code' => 200];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function dcsplst(Request $req)
    {
        if ($req->isMethod('post')) {
            date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {
                $p_id = $input['PHARMA_ID'];
                $date = Carbon::now();
                $weekNumber = $date->weekOfMonth;
                $day1 = $date->format('l');

                $doctors = DB::table('drprofile')
                    ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
                    ->where('dr_availablity.PHARMA_ID', $p_id)
                    ->where('drprofile.APPROVE', 'true')
                    ->distinct('dr_availablity.DR_ID')
                    ->select([
                        'drprofile.DR_ID', 'drprofile.DR_NAME', 'drprofile.DR_MOBILE', 'drprofile.SEX',
                        'drprofile.DESIGNATION', 'drprofile.QUALIFICATION', 'drprofile.D_CATG',
                        'drprofile.EXPERIENCE', 'drprofile.LANGUAGE', 'drprofile.PHOTO_URL AS DR_PHOTO',
                        'dr_availablity.SCH_DAY', 'dr_availablity.WEEK', 'dr_availablity.DR_FEES',
                        'dr_availablity.CHK_IN_STATUS'
                    ])
                    ->orderBy('drprofile.DR_ID')
                    ->get();

                $todayLiveDoctors = $doctors->filter(function ($item) use ($day1, $weekNumber) {
                    return $item->SCH_DAY === $day1 && strpos((string) $item->WEEK, (string) $weekNumber) !== false;
                })->values();

                $data = [
                    'Today/Live' => $todayLiveDoctors,
                    'Doctors' => $doctors
                ];

                if ($data['Doctors']->isEmpty() && $data['Today/Live']->isEmpty()) {
                    $response = ['Success' => false, 'Message' => 'No data found', 'code' => 404];
                } else {
                    $response = ['Success' => true, 'data' => $data, 'code' => 200];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }
        return $response;
    }

    function vuclinicstst(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input   = $req->json()->all();
            if (isset($input['TEST_ID']) && isset($input['PHARMA_ID'])) {

                $TID = $input['TEST_ID'];
                $PID = $input['PHARMA_ID'];
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $data1 = DB::table('clinic_testdata')
                    ->join('pharmacy', 'clinic_testdata.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    // ->join('master_testdata', 'clinic_testdata.TEST_ID', '=', 'master_testdata.TEST_ID')
                    ->distinct('pharmacy.PHARMA_ID')
                    ->select(
                        'pharmacy.PHARMA_ID',
                        'pharmacy.ITEM_NAME AS PHARMA_NAME',
                        'pharmacy.ADDRESS',
                        'pharmacy.CITY',
                        'pharmacy.DIST',
                        'pharmacy.STATE',
                        'pharmacy.PIN',
                        'pharmacy.EMAIL',
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        'pharmacy.CLINIC_TYPE',
                        'pharmacy.CLINIC_MOBILE',
                        'pharmacy.LATITUDE',
                        'pharmacy.LONGITUDE',
                        'pharmacy.PH_RATING',
                        'clinic_testdata.TEST_ID',
                        'clinic_testdata.TEST_NAME',
                        'clinic_testdata.DEPARTMENT',
                        'clinic_testdata.DISCOUNT',
                        'clinic_testdata.COST',
                        'clinic_testdata.HOME_COLLECT',
                        'clinic_testdata.FREE_AREA',
                        'clinic_testdata.SERV_CONDITION',
                        'clinic_testdata.TEST_DESC',
                        'clinic_testdata.FASTING',
                        'clinic_testdata.GENDER_TYPE',
                        'clinic_testdata.AGE_TYPE',
                        'clinic_testdata.REPORT_TIME',
                        'clinic_testdata.QA1',
                        'clinic_testdata.QA2',
                        'clinic_testdata.QA3',
                        'clinic_testdata.QA4',
                        'clinic_testdata.QA5',
                        'clinic_testdata.QA6',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                        * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                        * SIN(RADIANS('$latt'))))),2) as KM"),
                    )
                    ->where(['clinic_testdata.TEST_ID' => $TID, 'clinic_testdata.PHARMA_ID' => $PID])
                    ->get();

                foreach ($data1 as $row) {
                    $T_dtl['DETAILS'] = array([
                        "TEST_ID" => $row->TEST_ID,
                        "TEST_NAME" => $row->TEST_NAME,
                        "CATEGORY" => $row->DEPARTMENT,
                        "PHARMA_ID" => $row->PHARMA_ID,
                        "DISCOUNT" => $row->DISCOUNT,
                        "COST" => $row->COST,
                        "HOME_COLLECT" => $row->HOME_COLLECT,
                        "FREE_AREA" => $row->FREE_AREA,
                        "SERV_CONDITION" => $row->SERV_CONDITION,
                        "TEST_DESC" => $row->TEST_DESC,
                        "FASTING" => $row->FASTING,
                        "GENDER_TYPE" => $row->GENDER_TYPE,
                        "AGE_TYPE" => $row->AGE_TYPE,
                        "REPORT_TIME" => $row->REPORT_TIME,
                        "QA1" => $row->QA1,
                        "QA2" => $row->QA2,
                        "QA3" => $row->QA3,
                        "QA4" => $row->QA4,
                        "QA5" => $row->QA5,
                        "QA6" => $row->QA6,
                    ]);

                    $P_dt = [
                        "PHARMA_ID" => $row->PHARMA_ID,
                        "PHARMA_NAME" => $row->PHARMA_NAME,
                        "ADDRESS" => $row->ADDRESS,
                        "CITY" => $row->CITY,
                        "DIST" => $row->DIST,
                        "CLINIC_MOBILE" => $row->CLINIC_MOBILE,
                        "PIN" => $row->PIN,
                        "EMAIL" => $row->EMAIL,
                        "STATE" => $row->STATE,
                        "LATITUDE" => $row->LATITUDE,
                        "LONGITUDE" => $row->LONGITUDE,
                        "PHOTO_URL" => $row->PHOTO_URL,
                        "LOGO_URL" => $row->LOGO_URL,
                        "KM" => $row->KM,
                        "PH_RATING" => $row->PH_RATING,
                        "TOT_TEST" => 1,
                        "TOT_COST" => $row->COST,
                        "DETAILS" => $T_dtl['DETAILS']
                    ];
                    array_push($data, $P_dt);
                }
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function catgclinicdr(Request $request)
    {
        if ($request->isMethod('post')) {
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();
            if (isset($input['DIS_ID']) && isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $pid = $input['PHARMA_ID'];
                $did = $input['DIS_ID'];
                $data = collect();

                $data = $data->merge($this->getCatgDrDt($latt, $lont, $pid, $did));
                // $data = $data->merge($this->getClinic($latt, $lont, $did));
                $arr_A['Banner'] = DB::table('promo_banner')
                    ->select('PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', 'SP')->get();

                $data = $data->merge($arr_A);
                if ($data === null) {
                    $response = ['Success' => false, 'Message' => 'Record not found', 'code' => 200];
                } else {
                    $response = ['Success' => true, 'data' => $data, 'code' => 200];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    private function getCatgDrDt($latt, $lont, $pid, $did)
    {
        $totdr = DB::table('drprofile')
            ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
            ->join('pharmacy', 'dr_availablity.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
            ->select(
                'drprofile.DR_ID',
                'drprofile.DR_NAME',
                'drprofile.DR_MOBILE',
                'drprofile.SEX',
                'drprofile.DESIGNATION',
                'drprofile.QUALIFICATION',
                'drprofile.UID_NMC',
                'drprofile.REGN_NO',
                'drprofile.D_CATG',
                'drprofile.EXPERIENCE',
                'drprofile.LANGUAGE',
                'drprofile.PHOTO_URL',
                DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                    * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                    * SIN(RADIANS('$latt'))))),0) as KM"),
            )
            ->distinct('DR_ID')
            ->where(['dr_availablity.DIS_ID' => $did, 'dr_availablity.PHARMA_ID' => $pid])
            ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
            ->where('drprofile.APPROVE', 'true')
            ->get();

        $DRSCH = ['Doctors' => []];

        foreach ($totdr as $row1) {
            $dravail = DB::table('dr_availablity')
                ->where(['DR_ID' => $row1->DR_ID])
                ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
                ->orderby('dr_availablity.CHK_OUT_TIME')->get();
            $totapp = DB::table('appointment')->where(['DR_ID' => $row1->DR_ID])->get();
            $data = [];

            foreach ($dravail as $row) {
                if (is_numeric($row->SCH_DAY)) {
                    $currentYear = date("Y");
                    $startDate = new DateTime("{$currentYear}-$row->START_MONTH-$row->SCH_DAY");

                    for ($i = 0; $i < 12; $i++) {
                        $dates = [];
                        $dates = $startDate->format('Ymd');
                        $schday = $startDate->format('l');
                        $cym = date('Ymd');

                        $startDate->modify('+' . $row->MONTH . 'months');
                        if ($dates >= $cym) {
                            $apnt_dt = $dates;
                            $apnt_id = $row->ID;

                            $fltr_apnt = $totapp->filter(function ($item) use ($apnt_dt, $apnt_id) {
                                return $item->APPNT_DT == $apnt_dt && $item->APPNT_ID == $apnt_id;
                            });
                            $totappct = $fltr_apnt->count();
                            if ($row->MAX_BOOK - $totappct == 0) {
                                $book_sts = "Closed";
                            } else {
                                $book_sts = "Available";
                            }

                            if ($row->ABS_TDT != null) {
                                if ($row->ABS_TDT < $dates) {
                                    $dr_status = "TIMELY";
                                } else {
                                    $dr_status = $row->CHK_IN_STATUS;
                                }
                            } else {
                                $dr_status = $row->CHK_IN_STATUS;
                            }

                            $data[] = [
                                "SCH_DT" => $dates,
                                "DR_STATUS" => $dr_status,
                                "DR_FEES" => $row->DR_FEES,
                                "ABS_FDT" => $row->ABS_FDT,
                                "ABS_TDT" => $row->ABS_TDT,
                                "DR_ARRIVE" => $row->DR_ARRIVE,
                                "CHK_IN_TIME" => $row->CHK_IN_TIME,
                                "CHEMBER_NO" => $row->CHEMBER_NO,
                                "TO" => $row->CHK_OUT_TIME,
                                "SLOT_STATUS" => $book_sts,
                            ];
                            if (!empty($earliestDates)) {
                                break;
                            }
                        } else {
                            continue;
                        }
                    }
                } else {
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->addMonths(6);
                    $cym = date('Ymd');
                    $counter = 0;

                    while ($startDate->lte($endDate) && $counter < 6) {
                        $dates = [];
                        if ($startDate->format('l') === $row->SCH_DAY) {
                            if (in_array($startDate->weekOfMonth, explode(',', $row->WEEK))) {
                                $dates = $startDate->format('Ymd');
                                $bookingStartDate = Carbon::createFromFormat('Ymd', $dates)->addDays(-$row->BOOK_ST_DT);
                                $formattedBookingDate = $bookingStartDate->format('Ymd');

                                $apnt_dt = $formattedBookingDate;
                                $apnt_id = $row->ID;
                                $fltr_apnt = $totapp->filter(function ($item) use ($apnt_dt, $apnt_id) {
                                    return $item->APPNT_DT == $apnt_dt && $item->APPNT_ID == $apnt_id;
                                });
                                $totappct = $fltr_apnt->count();
                                if ($row->MAX_BOOK - $totappct == 0) {
                                    $book_sts = "Closed";
                                } else {
                                    $book_sts = "Available";
                                }
                                if ($row->ABS_TDT != null) {
                                    if ($row->ABS_TDT < $dates) {
                                        $dr_status = "TIMELY";
                                    } else {
                                        $dr_status = $row->CHK_IN_STATUS;
                                    }
                                } else {
                                    $dr_status = $row->CHK_IN_STATUS;
                                }
                                $data[] = [
                                    "SCH_DT" => $dates,
                                    "DR_STATUS" => $dr_status,
                                    "DR_FEES" => $row->DR_FEES,
                                    "ABS_FDT" => $row->ABS_FDT,
                                    "ABS_TDT" => $row->ABS_TDT,
                                    "DR_ARRIVE" => $row->DR_ARRIVE,
                                    "CHK_IN_TIME" => $row->CHK_IN_TIME,
                                    "CHEMBER_NO" => $row->CHEMBER_NO,
                                    "TO" => $row->CHK_OUT_TIME,
                                    "SLOT_STATUS" => $book_sts,
                                ];
                                $counter++;
                            }
                        }
                        $startDate->addDay();
                    }
                }
            }
            usort($data, function ($item1, $item2) {
                return $item1['SCH_DT'] <=> $item2['SCH_DT'];
            });
            if ($data[0]['SCH_DT'] === $cym) {
                $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));
                $firstRowTOTime = $data[0]['TO'];

                if ($currentTime->greaterThan($firstRowTOTime)) {
                    $data[0]['DR_STATUS'] = "OUT";
                    $data[0]['SLOT_STATUS'] = "Closed";
                }
            }
            $collection = collect($data);
            $firstAvailable = $collection->first(function ($item) {
                return $item['DR_STATUS'] === 'IN' || $item['DR_STATUS'] === 'TIMELY' || $item['DR_STATUS'] === 'DELAY';
            });
            if ($firstAvailable) {
                $firstAvailableIndex = $collection->search($firstAvailable);
                $sixRows = array_slice($data, $firstAvailableIndex, 1);
            }

            if (!empty($sixRows)) {
                $DRSCH['Doctors'][$row1->DR_ID] = [
                    "DR_ID" => $row1->DR_ID,
                    "DR_NAME" => $row1->DR_NAME,
                    "DR_MOBILE" => $row1->DR_MOBILE,
                    "SEX" => $row1->SEX,
                    "DESIGNATION" => $row1->DESIGNATION,
                    "QUALIFICATION" => $row1->QUALIFICATION,
                    "UID_NMC" => $row1->UID_NMC,
                    "REGN_NO" => $row1->REGN_NO,
                    "D_CATG" => $row1->D_CATG,
                    "EXPERIENCE" => $row1->EXPERIENCE,
                    "LANGUAGE" => $row1->LANGUAGE,
                    "DR_PHOTO" => $row1->PHOTO_URL,
                    "KM" => $row1->KM,
                    "AVAILABLE_DT" => $sixRows[0]['SCH_DT'],
                    "SLOT_STATUS" => $sixRows[0]['SLOT_STATUS'],
                    "DR_STATUS" => $sixRows[0]['DR_STATUS'],
                    "DR_FEES" => $sixRows[0]['DR_FEES'],
                    "CHK_IN_TIME" => $sixRows[0]['CHK_IN_TIME'],
                    "DR_ARRIVE" => $sixRows[0]['DR_ARRIVE'],
                    "CHEMBER_NO" => $sixRows[0]['CHEMBER_NO'],
                ];
            }
        }
        if (empty($DRSCH['Doctors'])) {
            $DRSCH['Doctors'] = [];
        }
        usort($DRSCH['Doctors'], function ($a, $b) {
            $statusOrder = ['IN' => 1, 'TIMELY' => 2, 'DELAY' => 3, 'CANCELLED' => 4, 'OUT' => 5, 'LEAVE' => 6];
            if ($a['AVAILABLE_DT'] != $b['AVAILABLE_DT']) {
                return $a['AVAILABLE_DT'] <=> $b['AVAILABLE_DT'];
            }
            if ($statusOrder[$a['DR_STATUS']] != $statusOrder[$b['DR_STATUS']]) {
                return $statusOrder[$a['DR_STATUS']] <=> $statusOrder[$b['DR_STATUS']];
            }
            return $a['CHK_IN_TIME'] <=> $b['CHK_IN_TIME'];
        });
        $DRSCH['Doctors'] = array_values($DRSCH['Doctors']);
        return $DRSCH;
    }

    function labpkgdtl(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['LAB_PKG_ID'])) {

                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $L_PKG_ID = $input['LAB_PKG_ID'];

                $data1 = DB::table('package')
                    ->join('package_details', 'package.PKG_ID', '=', 'package_details.PKG_ID')
                    ->join('pharmacy', 'package.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    ->select(
                        'package.*',
                        'package_details.*',
                        'pharmacy.*',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                    * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                    * SIN(RADIANS('$latt'))))),2) as KM"),
                    )
                    ->where(['package.LAB_PKG_ID' => $L_PKG_ID])
                    ->orderby('package_details.TEST_ID')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->PKG_ID])) {
                        $groupedData[$row->PKG_ID] = [
                            "PHARMA_ID" => $row->PHARMA_ID,
                            "PHARMA_NAME" => $row->ITEM_NAME,
                            "ADDRESS" => $row->ADDRESS,
                            "CITY" => $row->CITY,
                            "DIST" => $row->DIST,
                            "CLINIC_MOBILE" => $row->CLINIC_MOBILE,
                            "PIN" => $row->PIN,
                            "EMAIL" => $row->EMAIL,
                            "STATE" => $row->STATE,
                            "LATITUDE" => $row->LATITUDE,
                            "LONGITUDE" => $row->LONGITUDE,
                            "PHOTO_URL" => $row->PHOTO_URL,
                            "LOGO_URL" => $row->LOGO_URL,
                            "KM" => $row->KM,
                            "PH_RATING" => $row->PH_RATING,
                            "HO_ID" => $row->HO_ID,
                            "LAB_PKG_ID" => $row->LAB_PKG_ID,
                            "PKG_ID" => $row->PKG_ID,
                            "LAB_PKG_NAME" => $row->LAB_PKG_NAME,
                            "PKG_NAME" => $row->PKG_NAME,
                            "PKG_TYPE" => $row->PKG_TYPE,
                            "PKG_DESC" => $row->PKG_DESC,
                            "PKG_URL" => $row->PKG_URL,
                            "KNOWN_AS" => $row->KNOWN_AS,
                            "FASTING" => $row->FASTING,
                            "GENDER_TYPE" => $row->GENDER_TYPE,
                            "AGE_TYPE" => $row->AGE_TYPE,
                            "REPORT_TIME" => $row->REPORT_TIME,
                            "PRESCRIPTION" => $row->PRESCRIPTION,
                            "ID_PROOF" => $row->ID_PROOF,
                            "QA1" => $row->QA1,
                            "QA2" => $row->QA2,
                            "QA3" => $row->QA3,
                            "QA4" => $row->QA4,
                            "QA5" => $row->QA5,
                            "QA6" => $row->QA6,
                            "PKG_COST" => $row->PKG_COST,
                            "PKG_DIS" => $row->PKG_DIS,
                            "HOME_COLLECT" => $row->HOME_COLLECT,
                            "FREE_AREA" => $row->FREE_AREA,
                            "SERV_CONDITION" => $row->SERV_CONDITION,
                            "DETAILS" => []
                        ];
                    }
                    if ($row->TEST_TYPE == 'Profile') {
                        $data2 = DB::table('package')
                            ->join('package_details', 'package_details.PKG_ID', '=', 'package.PKG_ID')
                            ->select(
                                'package.*',
                                'package_details.TEST_ID',
                                'package_details.TEST_UC',
                                'package_details.TEST_NAME',
                                'package_details.TEST_TYPE',
                                'package_details.COST',
                                'package_details.PKG_STATUS',
                            )
                            ->where(['package.PKG_ID' => $row->TEST_ID])
                            ->get();
                        $groupedData1 = [];
                        foreach ($data2 as $row2) {
                            if (empty($groupedData1)) {
                                $groupedData1 = [
                                    "ID" => $row2->ID,
                                    "PHARMA_ID" => $row2->PHARMA_ID,
                                    "HO_ID" => $row2->HO_ID,
                                    "PKG_ID" => $row2->PKG_ID,
                                    "TEST_UC" => $row->TEST_UC,
                                    "PKG_SL" => $row2->PKG_SL,
                                    "LAB_PKG_ID" => $row2->LAB_PKG_ID,
                                    "LAB_PKG_NAME" => $row2->LAB_PKG_NAME,
                                    "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                                    // "CLINIC_SECTION" => $row2->CLINIC_SECTION,
                                    "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                                    "PKG_NAME" => $row2->PKG_NAME,
                                    "PKG_NAME_UC" => $row2->PKG_NAME_UC,
                                    "PKG_TYPE" => $row2->PKG_TYPE,
                                    "PKG_DESC" => $row2->PKG_DESC,
                                    "PKG_URL" => $row2->PKG_URL,
                                    "KNOWN_AS" => $row2->KNOWN_AS,
                                    "FASTING" => $row2->FASTING,
                                    "GENDER_TYPE" => $row2->GENDER_TYPE,
                                    "AGE_TYPE" => $row2->AGE_TYPE,
                                    "REPORT_TIME" => $row2->REPORT_TIME,
                                    "PRESCRIPTION" => $row2->PRESCRIPTION,
                                    "ID_PROOF" => $row2->ID_PROOF,
                                    "QA1" => $row2->QA1,
                                    "QA2" => $row2->QA2,
                                    "QA3" => $row2->QA3,
                                    "QA4" => $row2->QA4,
                                    "QA5" => $row2->QA5,
                                    "QA6" => $row2->QA6,
                                    "PKG_COST" => $row2->PKG_COST,
                                    "PKG_DIS" => $row2->PKG_DIS,
                                    "PKG_RMK" => $row2->PKG_RMK,
                                    "STATUS" => $row2->STATUS,
                                    "HOME_COLLECT" => $row2->HOME_COLLECT,
                                    "FREE_AREA" => $row2->FREE_AREA,
                                    "SERV_CONDITION" => $row2->SERV_CONDITION,
                                    // "PROMO_TYPE" => $row2->PROMO_TYPE,
                                    // "PROMO_VALID" => $row2->PROMO_VALID,
                                    // "PROMO_URL" => $row2->PROMO_URL,
                                    // "PROMO_REQUEST" => $row2->PROMO_REQUEST,
                                    // "NOTIFICATION" => $row2->NOTIFICATION,
                                    // "PROMO_APPROVE" => $row2->PROMO_APPROVE,
                                    // "PROMO_STATUS" => $row2->PROMO_STATUS,
                                    "DETAILS" => []
                                ];
                            }
                            $groupedData1['DETAILS'][] = [
                                "TEST_ID" => $row2->TEST_ID,
                                "TEST_UC" => $row2->TEST_UC,
                                "TEST_NAME" => $row2->TEST_NAME,
                                "TEST_TYPE" => $row2->TEST_TYPE,
                                "COST" => $row2->COST,
                                "PKG_STATUS" => $row2->PKG_STATUS,
                            ];
                        }
                        $groupedData[$row->PKG_ID]['DETAILS']['PROFILE_DETAILS'][$row->TEST_ID] = $groupedData1;
                    } else {
                        $groupedData[$row->PKG_ID]['DETAILS'][] = [
                            "TEST_ID" => $row->TEST_ID,
                            "TEST_UC" => $row->TEST_UC,
                            "TEST_NAME" => $row->TEST_NAME,
                            "TEST_TYPE" => $row->TEST_TYPE,
                            "COST" => $row->COST,
                        ];
                    }
                    foreach ($groupedData as $detailId => &$detail) {
                        $totalTests = 0;
                        if (isset($detail['DETAILS']['PROFILE_DETAILS'])) {
                            foreach ($detail['DETAILS']['PROFILE_DETAILS'] as $profile) {
                                foreach ($profile['DETAILS'] as $test) {
                                    if ($test['TEST_TYPE'] == 'Test' && $test['PKG_STATUS'] == 'Active') {
                                        $totalTests++;
                                    }
                                }
                            }
                        }
                        foreach ($detail['DETAILS'] as $testDetail) {
                            if (!isset($testDetail['PROFILE_DETAILS']) && isset($testDetail['TEST_TYPE']) && $testDetail['TEST_TYPE'] == 'Test') {
                                $totalTests++;
                            }
                        }
                        $detail['TOT_TEST'] = $totalTests;
                    }
                }
                $bnr["Catg_Banner"] = DB::table('dashboard')
                    ->select('DASH_ID AS BANNER_ID', 'DASH_NAME AS BANNER_NAME', 'DASH_DESCRIPTION AS DESCRIPTION', 'BANNER_URL', 'COLOR_CODE')
                    ->where(['DASH_ID' => $L_PKG_ID])->get();
                // $fltr_fxd_bnr = $fxd_banner->filter(function ($item) use ($sid) {
                //     return $item->BANNER_TYPE === $sid;
                // });
                // $bnr["Catg_Banner"] = $fltr_fxd_bnr->take(1)->values()->all();
                // } 
                $bnr1["Banner"] = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where(['DASH_SECTION_ID' => $row->DASH_SECTION_ID, 'PHARMA_ID' => $data1->first()->PHARMA_ID])->get();
                $pkg["Package"] = \array_values($groupedData);
                $data = $pkg + $bnr1 + $bnr;
                // $data = array_values($groupedData);

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function scanorgan(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['ITEM_NAME'])) {

                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $test_catg = $input['ITEM_NAME'];

                $data = DB::table('clinic_testdata')
                    ->join('pharmacy', 'clinic_testdata.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    ->distinct('pharmacy.PHARMA_ID')
                    ->select(
                        'pharmacy.PHARMA_ID',
                        // 'pharmacy.HO_CODE',
                        'pharmacy.ITEM_NAME AS PHARMA_NAME',
                        'pharmacy.ADDRESS',
                        'pharmacy.CITY',
                        'pharmacy.DIST',
                        'pharmacy.STATE',
                        'pharmacy.PIN',
                        'pharmacy.EMAIL',
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        'pharmacy.CLINIC_TYPE',
                        'pharmacy.CLINIC_MOBILE',
                        'pharmacy.LATITUDE',
                        'pharmacy.LONGITUDE',
                        'pharmacy.PH_RATING',
                        'clinic_testdata.TEST_NAME',
                        'clinic_testdata.TEST_CATG',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                    * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                    * SIN(RADIANS('$latt'))))),2) as KM"),
                    )
                    ->where(['clinic_testdata.TEST_CATG' => $test_catg])
                    ->orderby('clinic_testdata.TEST_NAME')
                    ->get();

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function scanorgantest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['TEST_ID'])) {

                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $test_id = $input['TEST_ID'];

                $data = array();

                $data1 = DB::table('clinic_testdata')
                    ->join('pharmacy', 'clinic_testdata.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    ->select(
                        'pharmacy.*',
                        'clinic_testdata.*',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                    * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                    * SIN(RADIANS('$latt'))))),2) as KM"),
                    )
                    ->where(['clinic_testdata.TEST_ID' => $test_id])
                    ->orderby('clinic_testdata.TEST_NAME')
                    ->get();

                foreach ($data1 as $row) {
                    $T_dtl["DETAILS"] = array([
                        "PHARMA_ID" => $row->PHARMA_ID,
                        "AGE_TYPE" => $row->AGE_TYPE,
                        "FASTING" => $row->FASTING,
                        "GENDER_TYPE" => $row->GENDER_TYPE,
                        "ID_PROOF" => $row->ID_PROOF,
                        "KNOWN_AS" => $row->KNOWN_AS,
                        // "NORMAL_RANGE" => $row->NORMAL_RANGE,
                        "PRESCRIPTION" => $row->PRESCRIPTION,
                        "QA1" => $row->QA1,
                        "QA2" => $row->QA2,
                        "QA3" => $row->QA3,
                        "QA4" => $row->QA4,
                        "QA5" => $row->QA5,
                        "QA6" => $row->QA6,
                        "REPORT_TIME" => $row->REPORT_TIME,
                        "ORGAN_ID" => $row->ORGAN_ID,
                        "ORGAN_NAME" => $row->ORGAN_NAME,
                        "ORGAN_URL" => $row->ORGAN_URL,
                        "TEST_CATG" => $row->TEST_CATG,
                        "TEST_CODE" => $row->TEST_CODE,
                        "TEST_DESC" => $row->TEST_DESC,
                        "TEST_ID" => $row->TEST_ID,
                        "TEST_NAME" => $row->TEST_NAME,
                        "CATEGORY" => $row->DEPARTMENT,
                        "HOME_COLLECT" => $row->HOME_COLLECT,
                        "FREE_AREA" => $row->FREE_AREA,
                        "SERV_CONDITION" => $row->SERV_CONDITION,
                        // "TEST_UNIT" => $row->TEST_UNIT,
                        "COST" => $row->COST,
                        "DISCOUNT" => $row->DISCOUNT,
                        "DEPARTMENT" => $row->DEPARTMENT,
                    ]);

                    $D_DTL = [
                        "PHARMA_ID" => $row->PHARMA_ID,
                        "PHARMA_NAME" => $row->ITEM_NAME,
                        "ADDRESS" => $row->ADDRESS,
                        "CITY" => $row->CITY,
                        "DIST" => $row->DIST,
                        "CLINIC_MOBILE" => $row->CLINIC_MOBILE,
                        "PIN" => $row->PIN,
                        "EMAIL" => $row->EMAIL,
                        "STATE" => $row->STATE,
                        "LATITUDE" => $row->LATITUDE,
                        "LONGITUDE" => $row->LONGITUDE,
                        "PHOTO_URL" => $row->PHOTO_URL,
                        "LOGO_URL" => $row->LOGO_URL,
                        "KM" => $row->KM,
                        "PH_RATING" => $row->PH_RATING,
                        "TEST_CATG" => $row->TEST_CATG,
                        "ORGAN_ID" => $row->ORGAN_ID,
                        "ORGAN_NAME" => $row->ORGAN_NAME,
                        "ORGAN_URL" => $row->ORGAN_URL,
                        "TOT_COST" => $row->COST,
                        "TOT_TEST" => count($T_dtl),
                        "DETAILS" => $T_dtl['DETAILS']
                    ];
                    array_push($data, $D_DTL);
                }
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function diagpkgdtl(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['LAB_PKG_ID']) && isset($input['PHARMA_ID'])) {

                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $L_PKG_ID = $input['LAB_PKG_ID'];
                $P_ID = $input['PHARMA_ID'];


                $data1 = DB::table('package')
                    ->join('package_details', 'package.PKG_ID', '=', 'package_details.PKG_ID')
                    ->join('pharmacy', 'package.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    ->select(
                        'pharmacy.*',
                        'package.*',
                        'package_details.*',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                    * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                    * SIN(RADIANS('$latt'))))),2) as KM"),
                    )
                    ->where(['package.LAB_PKG_ID' => $L_PKG_ID, 'pharmacy.PHARMA_ID' => $P_ID])
                    ->orderby('package_details.TEST_ID')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->PKG_ID])) {
                        $groupedData[$row->PKG_ID] = [
                            "PHARMA_ID" => $row->PHARMA_ID,
                            "PHARMA_NAME" => $row->ITEM_NAME,
                            "ADDRESS" => $row->ADDRESS,
                            "CITY" => $row->CITY,
                            "DIST" => $row->DIST,
                            "CLINIC_MOBILE" => $row->CLINIC_MOBILE,
                            "PIN" => $row->PIN,
                            "EMAIL" => $row->EMAIL,
                            "STATE" => $row->STATE,
                            "LATITUDE" => $row->LATITUDE,
                            "LONGITUDE" => $row->LONGITUDE,
                            "PHOTO_URL" => $row->PHOTO_URL,
                            "LOGO_URL" => $row->LOGO_URL,
                            "KM" => $row->KM,
                            "PH_RATING" => $row->PH_RATING,
                            "HO_ID" => $row->HO_ID,
                            "LAB_PKG_ID" => $row->LAB_PKG_ID,
                            "PKG_ID" => $row->PKG_ID,
                            "LAB_PKG_NAME" => $row->LAB_PKG_NAME,
                            "PKG_NAME" => $row->PKG_NAME,
                            "PKG_TYPE" => $row->PKG_TYPE,
                            "PKG_DESC" => $row->PKG_DESC,
                            "PKG_URL" => $row->PKG_URL,
                            "KNOWN_AS" => $row->KNOWN_AS,
                            "FASTING" => $row->FASTING,
                            "GENDER_TYPE" => $row->GENDER_TYPE,
                            "AGE_TYPE" => $row->AGE_TYPE,
                            "REPORT_TIME" => $row->REPORT_TIME,
                            "PRESCRIPTION" => $row->PRESCRIPTION,
                            "ID_PROOF" => $row->ID_PROOF,
                            "QA1" => $row->QA1,
                            "QA2" => $row->QA2,
                            "QA3" => $row->QA3,
                            "QA4" => $row->QA4,
                            "QA5" => $row->QA5,
                            "QA6" => $row->QA6,
                            "PKG_COST" => $row->PKG_COST,
                            "PKG_DIS" => $row->PKG_DIS,
                            "HOME_COLLECT" => $row->HOME_COLLECT,
                            "FREE_AREA" => $row->FREE_AREA,
                            "SERV_CONDITION" => $row->SERV_CONDITION,
                            "DETAILS" => []
                        ];
                    }
                    if ($row->TEST_TYPE == 'Profile') {
                        $data2 = DB::table('package')
                            ->join('package_details', 'package_details.PKG_ID', '=', 'package.PKG_ID')
                            ->select(
                                'package.*',
                                'package_details.TEST_ID',
                                'package_details.TEST_UC',
                                'package_details.TEST_NAME',
                                'package_details.TEST_TYPE',
                                'package_details.COST',
                                'package_details.PKG_STATUS',
                            )
                            ->where(['package.PKG_ID' => $row->TEST_ID])
                            ->get();
                        $groupedData1 = [];
                        foreach ($data2 as $row2) {
                            if (empty($groupedData1)) {
                                $groupedData1 = [
                                    "ID" => $row2->ID,
                                    "PHARMA_ID" => $row2->PHARMA_ID,
                                    "HO_ID" => $row2->HO_ID,
                                    "PKG_ID" => $row2->PKG_ID,
                                    "TEST_UC" => $row->TEST_UC,
                                    "PKG_SL" => $row2->PKG_SL,
                                    "LAB_PKG_ID" => $row2->LAB_PKG_ID,
                                    "LAB_PKG_NAME" => $row2->LAB_PKG_NAME,
                                    // "LAB_SECTION" => $row2->LAB_SECTION,
                                    "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                                    "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                                    "PKG_NAME" => $row2->PKG_NAME,
                                    "PKG_NAME_UC" => $row2->PKG_NAME_UC,
                                    "PKG_TYPE" => $row2->PKG_TYPE,
                                    "PKG_DESC" => $row2->PKG_DESC,
                                    "PKG_URL" => $row2->PKG_URL,
                                    "KNOWN_AS" => $row2->KNOWN_AS,
                                    "FASTING" => $row2->FASTING,
                                    "GENDER_TYPE" => $row2->GENDER_TYPE,
                                    "AGE_TYPE" => $row2->AGE_TYPE,
                                    "REPORT_TIME" => $row2->REPORT_TIME,
                                    "PRESCRIPTION" => $row2->PRESCRIPTION,
                                    "ID_PROOF" => $row2->ID_PROOF,
                                    "QA1" => $row2->QA1,
                                    "QA2" => $row2->QA2,
                                    "QA3" => $row2->QA3,
                                    "QA4" => $row2->QA4,
                                    "QA5" => $row2->QA5,
                                    "QA6" => $row2->QA6,
                                    "PKG_COST" => $row2->PKG_COST,
                                    "PKG_DIS" => $row2->PKG_DIS,
                                    "PKG_RMK" => $row2->PKG_RMK,
                                    "STATUS" => $row2->STATUS,
                                    "HOME_COLLECT" => $row2->HOME_COLLECT,
                                    "FREE_AREA" => $row2->FREE_AREA,
                                    "SERV_CONDITION" => $row2->SERV_CONDITION,
                                    // "PROMO_TYPE" => $row2->PROMO_TYPE,
                                    // "PROMO_VALID" => $row2->PROMO_VALID,
                                    // "PROMO_URL" => $row2->PROMO_URL,
                                    // "PROMO_REQUEST" => $row2->PROMO_REQUEST,
                                    // "NOTIFICATION" => $row2->NOTIFICATION,
                                    // "PROMO_APPROVE" => $row2->PROMO_APPROVE,
                                    // "PROMO_STATUS" => $row2->PROMO_STATUS,
                                    "DETAILS" => []
                                ];
                            }
                            $groupedData1['DETAILS'][] = [
                                "TEST_ID" => $row2->TEST_ID,
                                "TEST_UC" => $row2->TEST_UC,
                                "TEST_NAME" => $row2->TEST_NAME,
                                "TEST_TYPE" => $row2->TEST_TYPE,
                                "COST" => $row2->COST,
                                "PKG_STATUS" => $row2->PKG_STATUS,
                            ];
                        }
                        $groupedData[$row->PKG_ID]['DETAILS']['PROFILE_DETAILS'][$row->TEST_ID] = $groupedData1;
                    } else {
                        $groupedData[$row->PKG_ID]['DETAILS'][] = [
                            "TEST_ID" => $row->TEST_ID,
                            "TEST_UC" => $row->TEST_UC,
                            "TEST_NAME" => $row->TEST_NAME,
                            "TEST_TYPE" => $row->TEST_TYPE,
                            "COST" => $row->COST,
                        ];
                    }
                    foreach ($groupedData as $detailId => &$detail) {
                        $totalTests = 0;
                        if (isset($detail['DETAILS']['PROFILE_DETAILS'])) {
                            foreach ($detail['DETAILS']['PROFILE_DETAILS'] as $profile) {
                                foreach ($profile['DETAILS'] as $test) {
                                    if ($test['TEST_TYPE'] == 'Test' && $test['PKG_STATUS'] == 'Active') {
                                        $totalTests++;
                                    }
                                }
                            }
                        }
                        foreach ($detail['DETAILS'] as $testDetail) {
                            if (!isset($testDetail['PROFILE_DETAILS']) && isset($testDetail['TEST_TYPE']) && $testDetail['TEST_TYPE'] == 'Test') {
                                $totalTests++;
                            }
                        }
                        $detail['TOT_TEST'] = $totalTests;
                    }
                }
                $data = array_values($groupedData);

                // foreach ($groupedData as $pkgId => &$package) {
                //     $package['TOT_TEST'] = count($package['DETAILS']);
                // }
                // $data = array_values($groupedData);
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function diagsrch(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input   = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['PHARMA_ID'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $P_ID = $input['PHARMA_ID'];

                $response = array();
                $data = array();

                $data1 = DB::table('clinic_testdata')->where(['PHARMA_ID' => $P_ID])->get();
                foreach ($data1 as $row1) {
                    $tstdtl = [];
                    $tstdtl['DETAILS'] = [
                        "TEST_ID" => $row1->TEST_ID,
                        "TEST_NAME" => $row1->TEST_NAME,
                        "TEST_CODE" => $row1->TEST_CODE,
                        "TEST_SAMPLE" => $row1->TEST_SAMPLE,
                        "TEST_CATG" =>  $row1->TEST_CATG,
                        "ORGAN_ID" => $row1->ORGAN_ID,
                        "ORGAN_NAME" => $row1->ORGAN_NAME,
                        "ORGAN_URL" => $row1->ORGAN_URL,
                        "DEPARTMENT" => $row1->DEPARTMENT,
                        // "TEST_UNIT" => $row1->TEST_UNIT,
                        // "NORMAL_RANGE" => $row1->NORMAL_RANGE,
                        "TEST_DESC" => $row1->TEST_DESC,
                        "KNOWN_AS" => $row1->KNOWN_AS,
                        "FASTING" => $row1->FASTING,
                        "GENDER_TYPE" => $row1->GENDER_TYPE,
                        "AGE_TYPE" => $row1->AGE_TYPE,
                        "REPORT_TIME" => $row1->REPORT_TIME,
                        "PRESCRIPTION" => $row1->PRESCRIPTION,
                        "ID_PROOF" => $row1->ID_PROOF,
                        "QA1" => $row1->QA1,
                        "QA2" => $row1->QA2,
                        "QA3" => $row1->QA3,
                        "QA4" => $row1->QA4,
                        "QA5" => $row1->QA5,
                        "QA6" => $row1->QA6,
                        "COST" => $row1->COST,
                        "DISCOUNT" => $row1->DISCOUNT,
                    ];
                    $data[] = [
                        "ID" => $row1->TEST_ID,
                        "ITEM_NAME" => $row1->TEST_NAME,
                        "FIELD_TYPE" => $row1->DEPARTMENT,
                        "DETAILS" => $tstdtl['DETAILS']
                    ];
                }
                $data2 = DB::table('pharmacy')
                    ->select('pharmacy.*', DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                * SIN(RADIANS('$latt'))))),2) as KM"),)
                    //     ->whereRaw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                    // * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                    //  * SIN(RADIANS('$latt'))))),2) as KM" <= 100)
                    ->where(['PHARMA_ID' => $P_ID])
                    ->take(25)
                    ->get();
                foreach ($data2 as $row2) {
                    $cldtl = [];
                    $cldtl['DETAILS'] = [
                        "PHARMA_ID" => $row2->PHARMA_ID,
                        "PHARMA_NAME" => $row2->ITEM_NAME,
                        "ADDRESS" => $row2->ADDRESS,
                        "CITY" => $row2->CITY,
                        "DIST" => $row2->DIST,
                        "STATE" => $row2->STATE,
                        "PIN" => $row2->PIN,
                        "PHOTO_URL" => $row2->PHOTO_URL,
                        "CLINIC_TYPE" => $row2->CLINIC_TYPE,
                        "CLINIC_MOBILE" => $row2->CLINIC_MOBILE,
                        "LATITUDE" => $row2->LATITUDE,
                        "LONGITUDE" => $row2->LONGITUDE,
                        "KM" => $row2->KM,
                    ];

                    $cl = [
                        "ID" => $row2->PHARMA_ID,
                        "ITEM_NAME" => $row2->ITEM_NAME,
                        "FIELD_TYPE" => $row2->CLINIC_TYPE,
                        "DETAILS" => $cldtl['DETAILS']
                    ];
                    array_push($data, $cl);
                }

                $data3 = DB::table('dashboard')
                    ->whereIn('DASH_SECTION_ID', ['B', 'C', 'D', 'G', 'H', 'S', 'T'])
                    ->where('STATUS', 'Active')
                    ->orderby('DASH_TYPE')
                    ->get();

                foreach ($data3 as $row3) {
                    $pkgdtl = [];
                    $pkgdtl['DETAILS'] = [
                        "AGE_TYPE" => $row3->AGE_TYPE,
                        "DESCRIPTION" => $row3->DASH_DESCRIPTION,
                        "FASTING" => $row3->FASTING,
                        "GENDER_TYPE" => $row3->GENDER_TYPE,
                        "DASH_ID" => $row3->DASH_ID,
                        "ID_PROOF" => $row3->ID_PROOF,
                        "DASH_NAME" => $row3->DASH_NAME,
                        "DASH_TYPE" => $row3->DASH_TYPE,
                        "KNOWN_AS" => $row3->KNOWN_AS,
                        "PHOTO_URL" => $row3->PHOTO_URL,
                        "PRESCRIPTION" => $row3->PRESCRIPTION,
                        "QA1" => $row3->QA1,
                        "QA2" => $row3->QA2,
                        "QA3" => $row3->QA3,
                        "QA4" => $row3->QA4,
                        "QA5" => $row3->QA5,
                        "QA6" => $row3->QA6,
                        "REPORT_TIME" => $row3->REPORT_TIME,
                        "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
                        "SECTION_SL" => $row3->POSITION,
                        "STATUS" => $row3->STATUS
                    ];

                    $pkg = [
                        "ID" => $row3->DASH_ID,
                        "ITEM_NAME" => $row3->DASH_NAME,
                        "FIELD_TYPE" => $row3->DASH_TYPE,
                        "DETAILS" => $pkgdtl['DETAILS']
                    ];
                    array_push($data, $pkg);
                }

                $data4 = DB::table('drprofile')->where('drprofile.APPROVE', 'true')->get();
                foreach ($data4 as $row4) {
                    $drdtl = [];
                    $drdtl['DETAILS'] = [
                        "DR_ID" => $row4->DR_ID,
                        "DR_NAME" => $row4->DR_NAME,
                        "DR_MOBILE" => $row4->DR_MOBILE,
                        "SEX" => $row4->SEX,
                        "DESIGNATION" => $row4->DESIGNATION,
                        "QUALIFICATION" => $row4->QUALIFICATION,
                        "D_CATG" => $row4->D_CATG,
                        "EXPERIENCE" => $row4->EXPERIENCE,
                        "DR_PHOTO" => $row4->PHOTO_URL,
                    ];

                    $dr = [
                        "ID" => $row4->DR_ID,
                        "ITEM_NAME" => $row4->DR_NAME,
                        "FIELD_TYPE" => "Doctor",
                        "DETAILS" => $drdtl['DETAILS']
                    ];
                    array_push($data, $dr);
                }

                $data5 = DB::table('dis_catg')->get();
                foreach ($data5 as $row5) {
                    $spdtl = [];
                    $spdtl['DETAILS'] = [
                        "DIS_ID" => $row5->DIS_ID,
                        "D_CATG" => $row5->DIS_CATEGORY,
                    ];

                    $sp = [
                        "ID" => $row5->DIS_ID,
                        "ITEM_NAME" => $row5->SPECIALIST,
                        "FIELD_TYPE" => "Specialist",
                        "DETAILS" => $spdtl['DETAILS']
                    ];
                    array_push($data, $sp);
                }

                $data6 = DB::table('symptoms')->get();
                foreach ($data6 as $row6) {
                    $sydtl = [];
                    $sydtl['DETAILS'] = [
                        "DIS_ID" => $row6->DIS_ID,
                        "D_CATG" => $row6->DIS_CATEGORY,
                        "SYM_ID" => $row6->SYM_ID,
                        "DESCRIPTION" => $row6->DESCRIPTION,
                    ];

                    $sy = [
                        "ID" => $row6->DIS_ID,
                        "ITEM_NAME" => $row6->SYM_NAME,
                        "FIELD_TYPE" => "Symptom",
                        "DETAILS" => $sydtl['DETAILS']
                    ];
                    array_push($data, $sy);
                }

                $data7 = DB::table('surgery')->get();
                foreach ($data7 as $row7) {
                    $sudtl = [];
                    $sudtl['DETAILS'] = [
                        "DIS_ID" => $row7->DIS_ID,
                        "D_CATG" => $row7->DIS_CATEGORY,
                        "SURG_TYPE" => $row7->SURG_TYPE,
                        "DESCRIPTION" => $row7->TYPE_DESC,
                    ];

                    $su = [
                        "ID" => $row7->DIS_ID,
                        "ITEM_NAME" => $row7->SURG_NAME,
                        "FIELD_TYPE" => "Surgery",
                        "DETAILS" => $sudtl['DETAILS']
                    ];
                    array_push($data, $su);
                }

                if ($data == null) {
                    $response = ['Success' => false, 'Message' => 'Test/Clinic not found', 'code' => 200];
                } else {
                    $response = ['Success' => true, 'data' => $data, 'code' => 200];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function vutodaydr(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            $input   = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {
                $p_id = $input['PHARMA_ID'];
                $date = Carbon::now();
                $weekNumber = $date->weekOfMonth;
                $day1 = date('l');
                $cdy = date('d');
                $cdt = date('Ymd');
                $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));

                $promo_bnr = DB::table('promo_banner')
                    ->where(['PHARMA_ID' => $p_id, 'STATUS' => 'Active'])
                    ->whereIn('DASH_SECTION_ID', ['DA', 'SP'])
                    ->get();

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'DA';
                });
                $bnr["slider"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "SLIDER_ID" => $item->PROMO_ID,
                        "SLIDER_NAME" => $item->PROMO_NAME,
                        "SLIDER_URL" => $item->PROMO_URL,
                    ];
                })->values()->all();


                $data1 = DB::table('pharmacy')
                    ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
                    ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
                    ->distinct('drprofile.DR_ID')
                    ->select(
                        'pharmacy.PHARMA_ID',
                        'pharmacy.ITEM_NAME',
                        'pharmacy.ADDRESS',
                        'pharmacy.CITY',
                        'pharmacy.PIN',
                        'pharmacy.DIST',
                        'pharmacy.STATE',
                        'pharmacy.LATITUDE',
                        'pharmacy.LONGITUDE',
                        'pharmacy.LONGITUDE',
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        // DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                        // * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                        // * SIN(RADIANS('$latt'))))),2) as KM"),
                        'drprofile.DR_ID',
                        'drprofile.DR_NAME',
                        'drprofile.DR_MOBILE',
                        'drprofile.SEX',
                        'drprofile.DESIGNATION',
                        'drprofile.QUALIFICATION',
                        'drprofile.D_CATG',
                        'drprofile.EXPERIENCE',
                        'drprofile.LANGUAGE',
                        'drprofile.PHOTO_URL AS DR_PHOTO',
                        'dr_availablity.DR_FEES',
                        // DB::raw("'" . Carbon::now()->format('Ymd') . "' as SCH_DT"),
                        'dr_availablity.SCH_DAY',
                        'dr_availablity.START_MONTH',
                        'dr_availablity.MONTH',
                        'dr_availablity.ABS_FDT',
                        'dr_availablity.ABS_TDT',
                        'dr_availablity.CHK_IN_TIME',
                        'dr_availablity.CHK_OUT_TIME',
                        'dr_availablity.CHK_IN_STATUS',
                        'dr_availablity.CHEMBER_NO',
                        'dr_availablity.DR_ARRIVE'
                    )
                    ->where(['dr_availablity.SCH_DAY' => $day1])
                    ->where('WEEK', 'like', '%' . $weekNumber . '%')
                    ->orWhere('dr_availablity.SCH_DT', $cdy)
                    ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
                    ->orderby('dr_availablity.CHK_OUT_TIME')

                    // ->orderbyraw('KM')
                    ->get();

                $ldr = [];
                foreach ($data1 as $row) {
                    if (is_numeric($row->SCH_DAY)) {
                        $date = Carbon::createFromDate(date('Y'), $row->START_MONTH, $row->SCH_DAY)
                            ->addMonths($row->MONTH);
                        if ($date->format('Ymd') === $cdt) {
                            $sch_dt = $date->format('Ymd');
                        }
                    } else {
                        $sch_dt = Carbon::now()->format('Ymd');
                    }
                    if ($currentTime->greaterThan($row->CHK_OUT_TIME)) {
                        $drstatus = "OUT";
                    } else {
                        $drstatus = $row->CHK_IN_STATUS;
                    }
                    $ldr['Doctor'][] = [
                        "PHARMA_ID" => $row->PHARMA_ID,
                        "PHARMA_NAME" => $row->ITEM_NAME,
                        "ADDRESS" => $row->ADDRESS,
                        "CITY" => $row->CITY,
                        "PIN" => $row->PIN,
                        "DIST" => $row->DIST,
                        "STATE" => $row->STATE,
                        "LATITUDE" => $row->LATITUDE,
                        "LONGITUDE" => $row->LONGITUDE,
                        "PHOTO_URL" => $row->PHOTO_URL,
                        "LOGO_URL" => $row->LOGO_URL,
                        // "KM" => $row->KM,
                        "DR_ID" => $row->DR_ID,
                        "DR_NAME" => $row->DR_NAME,
                        "DR_MOBILE" => $row->DR_MOBILE,
                        "SEX" => $row->SEX,
                        "DESIGNATION" => $row->DESIGNATION,
                        "QUALIFICATION" => $row->QUALIFICATION,
                        "D_CATG" => $row->D_CATG,
                        "EXPERIENCE" => $row->EXPERIENCE,
                        "LANGUAGE" => $row->LANGUAGE,
                        "DR_PHOTO" => $row->DR_PHOTO,
                        "DR_FEES" => $row->DR_FEES,
                        "SCH_DT" => $sch_dt,
                        "CHK_IN_TIME" => $row->CHK_IN_TIME,
                        "CHK_OUT_TIME" => $row->CHK_OUT_TIME,
                        "DR_STATUS" => $drstatus,
                        "DR_ARRIVE" => $row->DR_ARRIVE,
                        "CHEMBER_NO" => $row->CHEMBER_NO,
                    ];
                }
                usort($ldr['Doctor'], function ($item1, $item2) {
                    $order = [
                        'IN' => 1,
                        'TIMELY' => 2,
                        'DELAY' => 3,
                        'CANCELLED' => 4,
                        'OUT' => 5,
                        'LEAVE' => 6,
                    ];
                    $status1 = $order[$item1['DR_STATUS']] ?? 999;
                    $status2 = $order[$item2['DR_STATUS']] ?? 999;
                    if ($status1 == $status2) {
                        return 0;
                    }
                    return ($status1 < $status2) ? -1 : 1;
                });
                $filtered_ldr = array_filter($ldr['Doctor'], function ($doctor) {
                    return $doctor['DR_STATUS'] === "IN" || $doctor['DR_STATUS'] === "TIMELY" || $doctor['DR_STATUS'] === "DELAY" || $doctor['DR_STATUS'] === "OUT" || $doctor['DR_STATUS'] === "CANCELLED" || $doctor['DR_STATUS'] === "LEAVE";
                });
                $ldr['Doctor'] = array_values($filtered_ldr);

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SP';
                });
                $D_bnr["Banner"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "PROMO_ID" => $item->PROMO_ID,
                        "HEADER_NAME" => $item->HEADER_NAME,
                        "PHARMA_ID" => $item->PHARMA_ID,
                        "MOBILE_NO" => $item->MOBILE_NO,
                        "DIS_ID" => $item->DIS_ID,
                        "SYM_ID" => $item->SYM_ID,
                        "PKG_ID" => $item->PKG_ID,
                        "PROMO_NAME" => $item->PROMO_NAME,
                        "DESCRIPTION" => $item->DESCRIPTION,
                        "PROMO_URL" => $item->PROMO_URL,
                        "PROMO_DT" => $item->PROMO_DT,
                        "PROMO_VALID" => $item->PROMO_VALID,
                    ];
                })->take(3)->values()->all();
                $data = $ldr + $D_bnr + $bnr;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function usertesthis(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $headers = apache_request_headers();
            // session_start();
            // date_default_timezone_set('Asia/Kolkata');
            $input   = $request->json()->all();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
            if (isset($input['FAMILY_ID']) && isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $f_id = $input['FAMILY_ID'];
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $currentDate = date('Ymd');

                $data1 = DB::table('booktest')
                    ->join('pharmacy', 'booktest.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    ->join('user_family', 'booktest.PATIENT_ID', '=', 'user_family.ID')
                    ->select(
                        'booktest.*',
                        'user_family.*',
                        'pharmacy.*',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                        * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                        * SIN(RADIANS('$latt'))))),2) as KM"),
                        'booktest.STATUS'
                    )
                    ->where('user_family.FAMILY_ID', '=', $f_id)
                    ->orderbydesc('booktest.BOOKING_DT')
                    ->orderByDesc('booktest.BOOKING_TM')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->BOOKING_ID])) {
                        if ($row->SLOT_DT < $currentDate) {
                            $status = 'Cancelled';
                        } else {
                            $status = $row->STATUS;
                        }
                        $groupedData[$row->BOOKING_ID] = [
                            "BOOKING_ID" => $row->BOOKING_ID,
                            "BOOKING_DT" => $row->BOOKING_DT,
                            "BOOKING_TM" => $row->BOOKING_TM,
                            "PHARMA_ID" => $row->PHARMA_ID,
                            "PHARMA_NAME" => $row->ITEM_NAME,
                            "ADDRESS" => $row->ADDRESS,
                            "PIN" => $row->PIN,
                            "CITY" => $row->CITY,
                            "DIST" => $row->DIST,
                            "STATE" => $row->STATE,
                            "CLINIC_MOBILE" => $row->CLINIC_MOBILE,
                            "EMAIL" => $row->EMAIL,
                            "LATITUDE" => $row->LATITUDE,
                            "LONGITUDE" => $row->LONGITUDE,
                            "KM" => $row->KM,
                            "PHOTO_URL" => $row->PHOTO_URL,
                            "LOGO_URL" => $row->LOGO_URL,
                            "PATIENT_ID" => $row->PATIENT_ID,
                            "Patient_Name" => $row->Patient_Name,
                            "PATIENT_ADDRESS" => $row->LOCATION,
                            "MOBILE" => $row->MOBILE,
                            "SEX" => $row->SEX,
                            "AGE" => $row->DOB,
                            "STATUS" => $status,
                            "ADVICED_BY" => $row->ADVICED_BY,
                            "DETAILS" => []
                        ];
                    }
                    $groupedData[$row->BOOKING_ID]['DETAILS'][] = [
                        "PKG_ID" => $row->PKG_ID,
                        "PKG_NAME" => $row->PKG_NAME,
                        "TEST_COST" => $row->TEST_COST,
                        "PAY_MODE" => $row->PAY_MODE,
                        "HOME_COLLECT" => $row->HOME_COLLECT,
                        "TRANS_ID" => $row->TRANS_ID,
                        "SLOT_DT" => $row->SLOT_DT,
                        "FROM" => $row->FROM,
                        "TO" => $row->TO,
                    ];
                }
                $response = ['Success' => true, 'data' => $groupedData, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
            // } else {
            //     $response = ['Success' => false, 'Message' => 'You are not Authorized,', 'code' => 401];
            // }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    // function hosp_facility(Request $request)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         // $headers = apache_request_headers();
    //         // session_start();
    //         // date_default_timezone_set('Asia/Kolkata');
    //         $input   = $request->json()->all();

    //         // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
    //         if (isset($input['DASH_ID'])) {
    //             $f_id = $input['DASH_ID'];
    //             $data1 = DB::table('hospital_facilities')
    //                 ->where('DASH_ID', '=', $f_id)
    //                 ->get();

    //             $groupedData = [];
    //             foreach ($data1 as $row) {
    //                 if (!isset($groupedData[$row->DEPARTMENT])) {
    //                     $groupedData[$row->DEPARTMENT] = [
    //                         "DASH_ID" => $row->DASH_ID,
    //                         "SERVICE_NAME" => $row->SERVICE_NAME,
    //                         "DEPARTMENT" => $row->DEPARTMENT,
    //                         "DESCRIPTION" => $row->DESCRIPTION,
    //                         "FACILITY_DETAILS" => []
    //                     ];
    //                 }
    //                 $groupedData[$row->DEPARTMENT]['FACILITY_DETAILS'][] = [
    //                     "ID" => $row->ID,
    //                     "DIS_ID" => $row->DIS_ID,
    //                     "FACILITY_NAME" => $row->FACILITY_NAME,
    //                 ];
    //             }
    //             $a['Facilities'] = $groupedData;
    //             $b["Banner"] = DB::table('banner')->where('BANNER_TYPE', 'Profile')->get();

    //             $data = $a + $b;

    //             $response = ['Success' => true, 'data' => $data, 'code' => 200];
    //         } else {
    //             $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
    //         }
    //         // } else {
    //         //     $response = ['Success' => false, 'Message' => 'You are not Authorized,', 'code' => 401];
    //         // }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
    //     }
    //     return $response;
    // }

    function hosp_facility(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $headers = apache_request_headers();
            // session_start();
            // date_default_timezone_set('Asia/Kolkata');
            $input   = $request->json()->all();
            $promo_bnr = DB::table('promo_banner')
                ->where('STATUS', 'Active')
                ->whereIn('DASH_SECTION_ID', ['AG', 'AH', 'AI', 'AK', 'AL', 'AM', 'SP', 'SR'])
                ->get();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
            if (isset($input['FACILITY_ID'])) {
                $f_id = $input['FACILITY_ID'];
                $data1 = DB::table('dashboard')->where('TAG_SECTION', 'like', '%' . $f_id . '%')
                    ->where('STATUS', 'Active')
                    ->orderby('GR_POSITION')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->DASH_TYPE])) {
                        $photoUrl = $photoGrUrl = $bannerUrl = $bannerGrUrl = NULL;

                        $tags = explode(',', $row->TAG_SECTION);

                        switch ($f_id) {

                            case 'AG':
                                if (in_array('AG', $tags) || in_array('AH', $tags) || in_array('AI', $tags) || in_array('AM', $tags)) {
                                    $photoUrl = $row->URL_24X7_MI;
                                    $photoGrUrl = $row->URL_24X7_MG;
                                    $bannerUrl = $row->URL_24X7_MB;
                                    $bannerGrUrl = $row->URL_24X7_MGB;
                                }
                                break;
                            case 'AH':
                                if (in_array('AG', $tags) || in_array('AH', $tags) || in_array('AI', $tags) || in_array('AM', $tags)) {
                                    $photoUrl = $row->URL_IPD_MI;
                                    $photoGrUrl = $row->URL_IPD_MG;
                                    $bannerUrl = $row->URL_IPD_MB;
                                    $bannerGrUrl = $row->URL_IPD_MGB;
                                }
                                break;
                            case 'AI':
                                if (in_array('AG', $tags) || in_array('AH', $tags) || in_array('AI', $tags) || in_array('AM', $tags)) {
                                    $photoUrl = $row->URL_HOME_MI;
                                    $photoGrUrl = $row->URL_HOME_MG;
                                    $bannerUrl = $row->URL_HOME_MB;
                                    $bannerGrUrl = $row->URL_HOME_MGB;
                                }
                                break;
                            case 'AM':
                                if (in_array('AG', $tags) || in_array('AH', $tags) || in_array('AI', $tags) || in_array('AM', $tags)) {
                                    $photoUrl = $row->URL_2NDGN_MI;
                                    $photoGrUrl = $row->URL_2NDGN_MG;
                                    $bannerUrl = $row->URL_2NDGN_MB;
                                    $bannerGrUrl = $row->URL_2NDGN_MGB;
                                }
                                break;
                            default:
                                $photoUrl = $row->PHOTO_URL;
                                break;
                        }
                        $groupedData[$row->DASH_TYPE] = [
                            "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                            "DASH_TYPE" => $row->DASH_TYPE,
                            "DESCRIPTION" => $row->GR_DESC,
                            "PHOTO_URL" =>  $photoGrUrl,
                            "BANNER_URL" => $bannerGrUrl,
                            "FACILITY_DETAILS" => []
                        ];
                    }
                    $groupedData[$row->DASH_TYPE]['FACILITY_DETAILS'][] = [
                        "DASH_ID" => $row->DASH_ID,
                        "DIS_ID" => $row->DIS_ID,
                        "SYM_ID" => $row->SYM_ID,
                        "DASH_NAME" => $row->DASH_NAME,
                        "DESCRIPTION" => $row->DASH_DESCRIPTION,
                        "PHOTO_URL" => $photoUrl,
                    ];
                }
                $a['Facilities'] = \array_values($groupedData);

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) use ($f_id) {
                    return $item->DASH_SECTION_ID === $f_id;
                });

                $b["Facility_Banner"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "PROMO_ID" => $item->PROMO_ID,
                        "HEADER_NAME" => $item->HEADER_NAME,
                        "PHARMA_ID" => $item->PHARMA_ID,
                        "MOBILE_NO" => $item->MOBILE_NO,
                        "DIS_ID" => $item->DIS_ID,
                        "SYM_ID" => $item->SYM_ID,
                        "PKG_ID" => $item->PKG_ID,
                        "PROMO_NAME" => $item->PROMO_NAME,
                        "DESCRIPTION" => $item->DESCRIPTION,
                        "PROMO_URL" => $item->PROMO_URL,
                        "PROMO_DT" => $item->PROMO_DT,
                        "PROMO_VALID" => $item->PROMO_VALID,
                    ];
                })->take(3)->values()->all();

                $data = $a + $b;

                if (isset($data['Facilities'])) {
                    foreach ($data['Facilities'] as &$facility) {
                        if ($facility['DASH_SECTION_ID'] === $f_id) {
                            $facility['FACILITY_DETAILS'][] = \array_values($b["Facility_Banner"]);
                        }
                    }
                }

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
            // } else {
            //     $response = ['Success' => false, 'Message' => 'You are not Authorized,', 'code' => 401];
            // }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function hnsec_opinion1(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $headers = apache_request_headers();
            // session_start();
            // date_default_timezone_set('Asia/Kolkata');
            $input   = $request->json()->all();
            $promo_bnr = DB::table('promo_banner')
                ->where('STATUS', 'Active')
                ->whereIn('DASH_SECTION_ID', ['AM', 'SR'])
                ->get();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
            if (isset($input['FACILITY_ID'])) {
                $f_id = $input['FACILITY_ID'];
                $data1 = DB::table('dashboard')
                    ->wherein('DASH_SECTION_ID', ['AM', 'SP'])
                    ->where('STATUS', 'Active')
                    ->orderby('DASH_ID')
                    ->get();

                // RETURN $data1;

                $fltr_dash = $data1->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SP';
                });
                $departmentArray["Departments"] = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_NAME" => $item->DASH_TYPE,
                        "DASH_ID" => $item->DASH_ID,
                        "DASH_SL" => $item->DASH_SL,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DESCRIPTION" => $item->DASH_SECTION_DESC,
                        "BANNER_URL" => $item->BANNER_URL
                    ];
                })->values()->all();

                // $departments = [];
                // foreach ($data1 as $item) {
                //     $dashType = $item->DASH_TYPE;

                //     if (!isset($departments[$dashType])) {
                //         $departments[$dashType] = [
                //             "DASH_NAME" => $dashType,
                //             "DASH_ID" => $item->DASH_ID,
                //             "DASH_SL" => $item->DASH_SL,
                //             "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                //             "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                //             "DESCRIPTION" => $item->DASH_SECTION_DESC,
                //             "BANNER_URL" => $item->BANNER_URL
                //         ];
                //     }
                // }                

                // $departmentArray["Departments"] = array_values($departments);

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) use ($f_id) {
                    return $item->DASH_SECTION_ID === $f_id;
                });

                $b["Banner"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "PROMO_ID" => $item->PROMO_ID,
                        "HEADER_NAME" => $item->HEADER_NAME,
                        "PHARMA_ID" => $item->PHARMA_ID,
                        "MOBILE_NO" => $item->MOBILE_NO,
                        "DIS_ID" => $item->DIS_ID,
                        "SYM_ID" => $item->SYM_ID,
                        "PKG_ID" => $item->PKG_ID,
                        "PROMO_NAME" => $item->PROMO_NAME,
                        "DESCRIPTION" => $item->DESCRIPTION,
                        "PROMO_URL" => $item->PROMO_URL,
                        "PROMO_DT" => $item->PROMO_DT,
                        "PROMO_VALID" => $item->PROMO_VALID,
                    ];
                })->take(3)->values()->all();

                $fltr_dash = $data1->filter(function ($item) use ($f_id) {
                    return $item->DASH_SECTION_ID === $f_id;
                });
                $q["Questions"] = $fltr_dash->map(function ($item) {
                    return [
                        "QA1" => $item->QA1,
                        "QA2" => $item->QA2,
                        "QA3" => $item->QA3,
                        "QA4" => $item->QA4,
                        "QA5" => $item->QA5,
                        "QA6" => $item->QA6,
                        "QA7" => $item->QA7,
                        "QA8" => $item->QA8,
                        "QA9" => $item->QA9
                    ];
                })->values()->take(1)->all();

                $data = $departmentArray + $b + $q;

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
            // } else {
            //     $response = ['Success' => false, 'Message' => 'You are not Authorized,', 'code' => 401];
            // }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function hnsec_opinion(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $headers = apache_request_headers();
            // session_start();
            // date_default_timezone_set('Asia/Kolkata');
            $input   = $request->json()->all();
            $promo_bnr = DB::table('promo_banner')
                ->where('STATUS', 'Active')
                ->whereIn('DASH_SECTION_ID', ['AM', 'SP'])
                ->get();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
            if (isset($input['FACILITY_ID'])) {
                $f_id = $input['FACILITY_ID'];
                $data1 = DB::table('dashboard')
                    ->whereIn('DASH_SECTION_ID', ['AM', 'SR'])
                    ->orderby('DASH_ID')
                    ->get();

                $S_DTL = [];
                $collection = collect($data1);
                $distinctValues = $collection->pluck('DASH_TYPE')->unique();
                foreach ($distinctValues as $row) {
                    $fltr_arr = $data1->filter(function ($item) use ($row) {
                        return $item->DASH_TYPE === $row;
                    });

                    $T_DTL = $fltr_arr->map(function ($item) {
                        return [
                            "DASH_ID" => $item->DASH_ID,
                            "DASH_SL" => $item->DASH_SL,
                            "DASH_NAME" => $item->DASH_NAME,
                            "DASH_TYPE" => $item->DASH_TYPE,
                            "DESCRIPTION" => $item->DASH_DESCRIPTION,
                            "PHOTO_URL" => $item->PHOTO_URL,
                            "BANNER_URL" => $item->BANNER_URL,
                            // "QA1" => $item->QA1,
                            // "QA2" => $item->QA2,
                            // "QA3" => $item->QA3,
                            // "QA4" => $item->QA4,
                            // "QA5" => $item->QA5,
                            // "QA6" => $item->QA6,
                            // "QA7" => $item->QA7,
                            // "QA8" => $item->QA8,
                            // "QA9" => $item->QA9
                            "Questions" => [[
                                "QA1" => $item->QA1,
                                "QA2" => $item->QA2,
                                "QA3" => $item->QA3,
                                "QA4" => $item->QA4,
                                "QA5" => $item->QA5,
                                "QA6" => $item->QA6,
                                "QA7" => $item->QA7,
                                "QA8" => $item->QA8,
                                "QA9" => $item->QA9
                            ]]

                        ];
                    })->values()->all();
                    $S_DTL['Departments'][]  = [
                        "DASH_SECTION_ID" => $fltr_arr->first()->DASH_SECTION_ID,
                        "TAG_SECTION" => $fltr_arr->first()->TAG_SECTION,
                        "DASH_TYPE" => $row,
                        "DESCRIPTION" => $fltr_arr->first()->GR_DESC,
                        "BANNER_URL" => $fltr_arr->first()->PHOTO_URL,
                        "TOT_SURGERY" => count($T_DTL),
                        "SURGERY_DETAILS" => $T_DTL
                    ];
                }

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) use ($f_id) {
                    return $item->DASH_SECTION_ID === $f_id;
                });

                $b["Banner"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "PROMO_ID" => $item->PROMO_ID,
                        "HEADER_NAME" => $item->HEADER_NAME,
                        "PHARMA_ID" => $item->PHARMA_ID,
                        "MOBILE_NO" => $item->MOBILE_NO,
                        "DIS_ID" => $item->DIS_ID,
                        "SYM_ID" => $item->SYM_ID,
                        "PKG_ID" => $item->PKG_ID,
                        "PROMO_NAME" => $item->PROMO_NAME,
                        "DESCRIPTION" => $item->DESCRIPTION,
                        "PROMO_URL" => $item->PROMO_URL,
                        "PROMO_DT" => $item->PROMO_DT,
                        "PROMO_VALID" => $item->PROMO_VALID,
                    ];
                })->take(3)->values()->all();

                $fltr_dash = $data1->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SR';
                });
                $q["Questions"] = $fltr_dash->map(function ($item) {
                    return [
                        "QA1" => $item->FQA1,
                        "QA2" => $item->FQA2,
                        "QA3" => $item->FQA3,
                        "QA4" => $item->FQA4,
                        "QA5" => $item->FQA5,
                        "QA6" => $item->FQA6,
                        "QA7" => $item->FQA7,
                        "QA8" => $item->FQA8,
                        "QA9" => $item->FQA9
                    ];
                })->values()->take(1)->all();

                $data = $S_DTL + $b + $q;

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
            // } else {
            //     $response = ['Success' => false, 'Message' => 'You are not Authorized,', 'code' => 401];
            // }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function hosp_lst(Request $request)
    {
        if ($request->method() === 'POST') {
            $input = $request->json()->all();

            if (isset($input['DASH_ID'], $input['LATITUDE'], $input['LONGITUDE'])) {
                $f_id = $input['DASH_ID'];
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];



                //SECTION-A #### SLIDER


                try {
                    $data1 = DB::table('hospital_facilities_details')
                        ->join('dashboard', 'hospital_facilities_details.DASH_ID', '=', 'dashboard.DASH_ID')
                        ->join('pharmacy', 'hospital_facilities_details.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                        // ->join('promo_banner', 'pharmacy.PHARMA_ID', '=', 'promo_banner.PHARMA_ID')
                        ->select(
                            'hospital_facilities_details.DASH_ID',
                            'hospital_facilities_details.DASH_NAME',
                            'hospital_facilities_details.DEPT_PH',
                            'hospital_facilities_details.TOT_BED',
                            'hospital_facilities_details.AVAIL_BED',
                            'hospital_facilities_details.PRICE_FROM',
                            'hospital_facilities_details.UPDT_DT',
                            'hospital_facilities_details.SHORT_NOTE',
                            'hospital_facilities_details.IMAGE1_URL',
                            'hospital_facilities_details.IMAGE2_URL',
                            'hospital_facilities_details.IMAGE3_URL',
                            'hospital_facilities_details.FREE_AREA',
                            'hospital_facilities_details.SERV_CRG',
                            'hospital_facilities_details.DEPARTMENT',
                            'pharmacy.PHARMA_ID',
                            'pharmacy.ITEM_NAME AS PHARMA_NAME',
                            'pharmacy.ADDRESS',
                            'pharmacy.CITY',
                            'pharmacy.DIST',
                            'pharmacy.STATE',
                            'pharmacy.PIN',
                            'pharmacy.EMAIL',
                            'pharmacy.PHOTO_URL',
                            'pharmacy.LOGO_URL',
                            'pharmacy.CLINIC_TYPE',
                            'pharmacy.CLINIC_MOBILE',
                            'pharmacy.LATITUDE',
                            'pharmacy.LONGITUDE',
                            'pharmacy.PH_RATING',
                            'dashboard.DASH_SECTION_ID',
                            'dashboard.DASH_DESCRIPTION',
                            'dashboard.BANNER_URL',
                            'dashboard.GR_BANNER_URL',
                            // 'promo_banner.DASH_SECTION_ID as PROMO_SECTION',
                            // 'promo_banner.PROMO_ID',
                            // 'promo_banner.PROMO_NAME',
                            // 'promo_banner.PROMO_URL',

                            DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.LATITUDE)) 
                    * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.LONGITUDE - '$lont')) + SIN(RADIANS(pharmacy.LATITUDE)) 
                    * SIN(RADIANS('$latt'))))),2) as KM"),
                        )
                        ->where('hospital_facilities_details.DASH_ID', '=', $f_id)
                        ->get();


                    // $fltr_promo_bnr = $data1->filter(function ($item) {
                    //     return $item->PROMO_SECTION === 'HA';
                    // });
                    // $A = $fltr_promo_bnr->map(function ($item) {
                    //     return [
                    //         "SLIDER_ID" => $item->PROMO_ID,
                    //         "SLIDER_NAME" => $item->PROMO_NAME,
                    //         "SLIDER_URL" => $item->PROMO_URL,
                    //     ];
                    // })->values();

                    $fltr_fxd_bnr = $data1->where('DASH_ID', $f_id);
                    $bnr = $fltr_fxd_bnr->map(function ($item) {
                        return [
                            "DASH_ID" => $item->DASH_ID,
                            "DASH_NAME" => $item->DASH_NAME,
                            "DESCRIPTION" => $item->DASH_DESCRIPTION,
                            "BANNER_URL" => $item->BANNER_URL,
                            "GR_BANNER_URL" => $item->GR_BANNER_URL,
                        ];
                    })->values();


                    $data = [
                        // 'Slider' => $A->toArray(),
                        'Hospital' => $data1->toArray(),
                        'Catg_Banner' => $bnr->toArray()
                    ];

                    $response = ['Success' => true, 'data' => $data, 'code' => 200];
                } catch (Exception $e) {
                    $response = ['Success' => false, 'Message' => 'Database error: ' . $e->getMessage(), 'code' => 500];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }
        return $response;
    }


    function testinvoice(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $headers = apache_request_headers();
            // session_start();
            // date_default_timezone_set('Asia/Kolkata');
            $input   = $request->json()->all();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
            if (isset($input['PATIENT_ID']) && isset($input['PHARMA_ID']) && isset($input['BOOKING_ID'])) {
                $PID = $input['PATIENT_ID'];
                $PHID = $input['PHARMA_ID'];
                $BID = $input['BOOKING_ID'];

                $currentDate = date('Ymd');

                $data1 = DB::table('booktest')
                    ->join('pharmacy', 'booktest.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    ->join('user_family', 'booktest.PATIENT_ID', '=', 'user_family.ID')
                    ->select(
                        'booktest.*',
                        'user_family.*',
                        'pharmacy.*',
                        'booktest.STATUS'
                    )
                    ->where(['booktest.BOOKING_ID' => $BID, 'booktest.PHARMA_ID' => $PHID, 'booktest.PATIENT_ID' => $PID])
                    ->orderby('booktest.SLOT_DT')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->BOOKING_ID])) {
                        if ($row->SLOT_DT < $currentDate) {
                            $status = 'Cancelled';
                        } else {
                            $status = $row->STATUS;
                        }
                        $groupedData[$row->BOOKING_ID] = [
                            "BOOKING_ID" => $row->BOOKING_ID,
                            "BOOKING_DT" => $row->BOOKING_DT,
                            "BOOKING_TM" => $row->BOOKING_TM,
                            "PHARMA_ID" => $row->PHARMA_ID,
                            "PHARMA_NAME" => $row->ITEM_NAME,
                            "ADDRESS" => $row->ADDRESS,
                            "PIN" => $row->PIN,
                            "CITY" => $row->CITY,
                            "DIST" => $row->DIST,
                            "STATE" => $row->STATE,
                            "CLINIC_MOBILE" => $row->CLINIC_MOBILE,
                            "EMAIL" => $row->EMAIL,
                            "LATITUDE" => $row->LATITUDE,
                            "LONGITUDE" => $row->LONGITUDE,
                            "PHOTO_URL" => $row->PHOTO_URL,
                            "LOGO_URL" => $row->LOGO_URL,
                            "PATIENT_ID" => $row->PATIENT_ID,
                            "Patient_Name" => $row->Patient_Name,
                            "PATIENT_ADDRESS" => $row->LOCATION,
                            "MOBILE" => $row->MOBILE,
                            "SEX" => $row->SEX,
                            "AGE" => $row->DOB,
                            "STATUS" => $status,
                            "ADVICED_BY" => $row->ADVICED_BY,
                            "DETAILS" => []
                        ];
                    }
                    $groupedData[$row->BOOKING_ID]['DETAILS'][] = [
                        "PKG_ID" => $row->PKG_ID,
                        "PKG_NAME" => $row->PKG_NAME,
                        "TEST_COST" => $row->TEST_COST,
                        "PAY_MODE" => $row->PAY_MODE,
                        "HOME_COLLECT" => $row->HOME_COLLECT,
                        "TRANS_ID" => $row->TRANS_ID,
                        "SLOT_DT" => $row->SLOT_DT,
                        "FROM" => $row->FROM,
                        "TO" => $row->TO,
                    ];
                }

                $response = ['Success' => true, 'data' => $groupedData, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
            // } else {
            //     $response = ['Success' => false, 'Message' => 'You are not Authorized,', 'code' => 401];
            // }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function savetestinvoice(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->all();
            if (
                isset($input['BOOKING_ID'])
            ) {
                $response = array();
                $book_id = $input['BOOKING_ID'];
                try {
                    if ($req->file('BOOKINVOICE') !== null) {
                        $book_inv = $book_id . "." . $req->file('BOOKINVOICE')->getClientOriginalExtension();
                        $updt_inv = $req->file('BOOKINVOICE')->storeAs('bookinvoice', $book_inv);
                        $inv_photo_url = "http://healthezy.easytechitsolutions.com/healthezyapi/storage/app/bookinvoice/" . $book_inv;
                    } else {
                        $inv_photo_url = "Missing Image file!!";
                    }
                    DB::update("UPDATE `appointment` SET `PHOTO_URL`='$inv_photo_url' WHERE BOOKING_ID='$book_id'");
                } catch (\Throwable $th) {
                    $response = ['Success' => false, 'Message' => $th, 'code' => 200];
                    return $response;
                }
                $response = ['Success' => true, 'Message' => 'Booking invoice save successfgully.', 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid parameter.', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }

    function vustst(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input   = $req->json()->all();
            if (isset($input['TEST_ID'])) {
                $TID = $input['TEST_ID'];
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $data1 = DB::table('clinic_testdata')
                    ->join('pharmacy', 'clinic_testdata.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    ->distinct('pharmacy.PHARMA_ID')
                    ->select(
                        'clinic_testdata.*',
                        'pharmacy.*',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                        * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                        * SIN(RADIANS('$latt'))))),2) as KM"),
                    )
                    ->where('clinic_testdata.TEST_ID', '=', $TID)
                    ->get();
                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->PHARMA_ID])) {
                        $groupedData[$row->PHARMA_ID] = [
                            "PHARMA_ID" => $row->PHARMA_ID,
                            "PHARMA_NAME" => $row->ITEM_NAME,
                            "ADDRESS" => $row->ADDRESS,
                            "CITY" => $row->CITY,
                            "DIST" => $row->DIST,
                            "STATE" => $row->STATE,
                            "PIN" => $row->PIN,
                            "EMAIL" => $row->EMAIL,
                            "PHOTO_URL" => $row->PHOTO_URL,
                            "LOGO_URL" => $row->LOGO_URL,
                            "CLINIC_TYPE" => $row->CLINIC_TYPE,
                            "CLINIC_MOBILE" => $row->CLINIC_MOBILE,
                            "LATITUDE" => $row->LATITUDE,
                            "LONGITUDE" => $row->LONGITUDE,
                            "PH_RATING" => $row->PH_RATING,
                            "KM" => $row->KM
                        ];
                    }
                    if (!is_null($row->PHARMA_ID)) {
                        $groupedData[$row->PHARMA_ID]['DETAILS'][] = [
                            "TEST_ID" => $row->TEST_ID,
                            "TEST_NAME" => $row->TEST_NAME,
                            "PHARMA_ID" => $row->PHARMA_ID,
                            "DISCOUNT" => $row->DISCOUNT,
                            "COST" => $row->COST,
                            "HOME_COLLECT" => $row->HOME_COLLECT,
                            "FREE_AREA" => $row->FREE_AREA,
                            "SERV_CONDITION" => $row->SERV_CONDITION,
                            "TEST_DESC" => $row->TEST_DESC,
                            "CATEGORY" => $row->DEPARTMENT,
                            "KNOWN_AS" => $row->KNOWN_AS,
                            "FASTING" => $row->FASTING,
                            "GENDER_TYPE" => $row->GENDER_TYPE,
                            "AGE_TYPE" => $row->AGE_TYPE,
                            "REPORT_TIME" => $row->REPORT_TIME,
                            "QA1" => $row->QA1,
                            "QA2" => $row->QA2,
                            "QA3" => $row->QA3,
                            "QA4" => $row->QA4,
                            "QA5" => $row->QA5,
                            "QA6" => $row->QA6,
                        ];
                    }
                }
                foreach ($groupedData as $pId => &$phid) {
                    $phid['TOT_TEST'] = count($phid['DETAILS']);
                    $phid['TOT_COST'] = array_sum(array_column($phid['DETAILS'], 'COST'));
                }
                $bnr["Catg_Banner"] = DB::table('master_testdata')
                    ->select('SUB_DEPT_ID AS BANNER_ID', 'SUB_DEPARTMENT AS BANNER_NAME', 'TEST_DESC AS DESCRIPTION', 'BANNER_URL')
                    ->where(['DASH_ID' => 'TS', 'SUB_DEPT_ID' => $row->SUB_DEPT_ID])->take(1)->get();

                $bnr1["Banner"] = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', 'TS')->get();

                $cl["Diagnostic"] = array_values($groupedData);

                $data = $cl + $bnr + $bnr1;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function itemorgantest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['DASH_NAME']) && isset($input['ORGAN_ID'])) {

                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $test_catg = $input['DASH_NAME'];
                $org_id = $input['ORGAN_ID'];

                $pharmacyData = [];
                $testData = [];
                $testCosts = [];

                $data1 = DB::table('pharmacy')
                    ->join('clinic_testdata', 'pharmacy.PHARMA_ID', '=', 'clinic_testdata.PHARMA_ID')
                    ->distinct('pharmacy.PHARMA_ID')
                    ->select(
                        'pharmacy.*',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude))
                         * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                         * SIN(RADIANS('$latt'))))),2) as KM"),
                        'clinic_testdata.*',
                        'clinic_testdata.HOME_COLLECT'
                    )
                    ->where(['clinic_testdata.TEST_CATG' => $test_catg, 'clinic_testdata.ORGAN_ID' => $org_id])
                    ->orderby('clinic_testdata.TEST_NAME')
                    ->get();

                foreach ($data1 as $row) {
                    $pharmacyData[$row->PHARMA_ID] = [
                        "PHARMA_ID" => $row->PHARMA_ID,
                        "PHARMA_NAME" => $row->ITEM_NAME,
                        "ADDRESS" => $row->ADDRESS,
                        "CITY" => $row->CITY,
                        "DIST" => $row->DIST,
                        "STATE" => $row->STATE,
                        "PIN" => $row->PIN,
                        "EMAIL" => $row->EMAIL,
                        "PHOTO_URL" => $row->PHOTO_URL,
                        "LOGO_URL" => $row->LOGO_URL,
                        "CLINIC_TYPE" => $row->CLINIC_TYPE,
                        "CLINIC_MOBILE" => $row->CLINIC_MOBILE,
                        "LATITUDE" => $row->LATITUDE,
                        "LONGITUDE" => $row->LONGITUDE,
                        "PH_RATING" => $row->PH_RATING,
                        "KM" => $row->KM
                    ];
                    $testData[$row->TEST_ID] = [
                        "TEST_ID" => $row->TEST_ID,
                        "TEST_NAME" => $row->TEST_NAME,
                        "TEST_CATG" => $row->TEST_CATG,
                        "DISCOUNT" => $row->DISCOUNT,
                        "HOME_COLLECT" => $row->HOME_COLLECT,
                        "ORGAN_ID" => $row->ORGAN_ID,
                        "ORGAN_NAME" => $row->ORGAN_NAME,
                        "ORGAN_URL" => $row->ORGAN_URL,
                        "TEST_DESC" => $row->TEST_DESC,
                        "DEPARTMENT" => $row->DEPARTMENT,
                        "KNOWN_AS" => $row->KNOWN_AS,
                        "FASTING" => $row->FASTING,
                        "GENDER_TYPE" => $row->GENDER_TYPE,
                        "AGE_TYPE" => $row->AGE_TYPE,
                        "REPORT_TIME" => $row->REPORT_TIME,
                        "PRESCRIPTION" => $row->PRESCRIPTION,
                        "ID_PROOF" => $row->ID_PROOF,
                        "QA1" => $row->QA1,
                        "QA2" => $row->QA2,
                        "QA3" => $row->QA3,
                        "QA4" => $row->QA4,
                        "QA5" => $row->QA5,
                        "QA6" => $row->QA6,
                    ];
                    $testCosts[$row->TEST_ID][] = $row->COST;
                }
                foreach ($testData as $tId => &$testInfo) {
                    if (isset($testCosts[$tId])) {
                        $testInfo['MIN_COST'] = min($testCosts[$tId]);
                    }
                }
                $data = [
                    'PHARMACY' => array_values($pharmacyData),
                    'TEST' => array_values($testData)
                ];

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function clinicitemorgantest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['DASH_NAME']) && isset($input['ORGAN_ID'])) {

                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $test_catg = $input['DASH_NAME'];
                $org_id = $input['ORGAN_ID'];
                $pid = $input['PHARMA_ID'];
                $testData = [];

                $data1 = DB::table('clinic_testdata')
                    ->where(['TEST_CATG' => $test_catg, 'ORGAN_ID' => $org_id, 'PHARMA_ID' => $pid])
                    ->orderby('TEST_NAME')
                    ->get();

                foreach ($data1 as $row) {
                    $testData[$row->TEST_ID] = [
                        "TEST_ID" => $row->TEST_ID,
                        "TEST_NAME" => $row->TEST_NAME,
                        "TEST_CATG" => $row->TEST_CATG,
                        "DISCOUNT" => $row->DISCOUNT,
                        "HOME_COLLECT" => $row->HOME_COLLECT,
                        "ORGAN_ID" => $row->ORGAN_ID,
                        "ORGAN_NAME" => $row->ORGAN_NAME,
                        "ORGAN_URL" => $row->ORGAN_URL,
                        "TEST_DESC" => $row->TEST_DESC,
                        "DEPARTMENT" => $row->DEPARTMENT,
                        "KNOWN_AS" => $row->KNOWN_AS,
                        "FASTING" => $row->FASTING,
                        "GENDER_TYPE" => $row->GENDER_TYPE,
                        "AGE_TYPE" => $row->AGE_TYPE,
                        "REPORT_TIME" => $row->REPORT_TIME,
                        "PRESCRIPTION" => $row->PRESCRIPTION,
                        "ID_PROOF" => $row->ID_PROOF,
                        "COST" => $row->COST,
                        "QA1" => $row->QA1,
                        "QA2" => $row->QA2,
                        "QA3" => $row->QA3,
                        "QA4" => $row->QA4,
                        "QA5" => $row->QA5,
                        "QA6" => $row->QA6,
                    ];
                }

                $data = ['TEST' => array_values($testData)];

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function diagallpkg(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['PHARMA_ID'])) {

                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $pid = $input['PHARMA_ID'];

                $data1 = DB::table('package')
                    ->join('package_details', 'package.PKG_ID', '=', 'package_details.PKG_ID')
                    ->select(
                        'package.*',
                        'package_details.*',
                    )
                    ->where(['package.PHARMA_ID' => $pid])
                    ->orderby('package_details.TEST_ID')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->PKG_ID])) {
                        $groupedData[$row->PKG_ID] = [

                            "LAB_PKG_ID" => $row->LAB_PKG_ID,
                            "PKG_ID" => $row->PKG_ID,
                            "LAB_PKG_NAME" => $row->LAB_PKG_NAME,
                            "PKG_NAME" => $row->PKG_NAME,
                            "PKG_TYPE" => $row->PKG_TYPE,
                            "PKG_DESC" => $row->PKG_DESC,
                            "PKG_URL" => $row->PKG_URL,
                            "KNOWN_AS" => $row->KNOWN_AS,
                            "FASTING" => $row->FASTING,
                            "GENDER_TYPE" => $row->GENDER_TYPE,
                            "AGE_TYPE" => $row->AGE_TYPE,
                            "REPORT_TIME" => $row->REPORT_TIME,
                            "PRESCRIPTION" => $row->PRESCRIPTION,
                            "ID_PROOF" => $row->ID_PROOF,
                            "QA1" => $row->QA1,
                            "QA2" => $row->QA2,
                            "QA3" => $row->QA3,
                            "QA4" => $row->QA4,
                            "QA5" => $row->QA5,
                            "QA6" => $row->QA6,
                            "PKG_COST" => $row->PKG_COST,
                            "PKG_DIS" => $row->PKG_DIS,
                            "HOME_COLLECT" => $row->HOME_COLLECT,
                            "FREE_AREA" => $row->FREE_AREA,
                            "SERV_CONDITION" => $row->SERV_CONDITION,
                            "DETAILS" => []
                        ];
                    }
                    if ($row->TEST_TYPE == 'Profile') {
                        $data2 = DB::table('package')
                            ->join('package_details', 'package_details.PKG_ID', '=', 'package.PKG_ID')
                            ->select(
                                'package.*',
                                'package_details.TEST_ID',
                                'package_details.TEST_UC',
                                'package_details.TEST_NAME',
                                'package_details.TEST_TYPE',
                                'package_details.COST',
                                'package_details.PKG_STATUS',
                            )
                            ->where(['package.PKG_ID' => $row->TEST_ID])
                            ->get();
                        $groupedData1 = [];
                        foreach ($data2 as $row2) {
                            if (empty($groupedData1)) {
                                $groupedData1 = [
                                    "ID" => $row2->ID,
                                    "PKG_ID" => $row2->PKG_ID,
                                    "TEST_UC" => $row->TEST_UC,
                                    "PKG_SL" => $row2->PKG_SL,
                                    "LAB_PKG_ID" => $row2->LAB_PKG_ID,
                                    "LAB_PKG_NAME" => $row2->LAB_PKG_NAME,
                                    "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,

                                    "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                                    "PKG_NAME" => $row2->PKG_NAME,
                                    "PKG_NAME_UC" => $row2->PKG_NAME_UC,
                                    "PKG_TYPE" => $row2->PKG_TYPE,
                                    "PKG_DESC" => $row2->PKG_DESC,
                                    "PKG_URL" => $row2->PKG_URL,
                                    "KNOWN_AS" => $row2->KNOWN_AS,
                                    "FASTING" => $row2->FASTING,
                                    "GENDER_TYPE" => $row2->GENDER_TYPE,
                                    "AGE_TYPE" => $row2->AGE_TYPE,
                                    "REPORT_TIME" => $row2->REPORT_TIME,
                                    "PRESCRIPTION" => $row2->PRESCRIPTION,
                                    "ID_PROOF" => $row2->ID_PROOF,
                                    "QA1" => $row2->QA1,
                                    "QA2" => $row2->QA2,
                                    "QA3" => $row2->QA3,
                                    "QA4" => $row2->QA4,
                                    "QA5" => $row2->QA5,
                                    "QA6" => $row2->QA6,
                                    "PKG_COST" => $row2->PKG_COST,
                                    "PKG_DIS" => $row2->PKG_DIS,
                                    "PKG_RMK" => $row2->PKG_RMK,
                                    "STATUS" => $row2->STATUS,
                                    "HOME_COLLECT" => $row2->HOME_COLLECT,
                                    "FREE_AREA" => $row2->FREE_AREA,
                                    "SERV_CONDITION" => $row2->SERV_CONDITION,

                                    "DETAILS" => []
                                ];
                            }
                            $groupedData1['DETAILS'][] = [
                                "TEST_ID" => $row2->TEST_ID,
                                "TEST_UC" => $row2->TEST_UC,
                                "TEST_NAME" => $row2->TEST_NAME,
                                "TEST_TYPE" => $row2->TEST_TYPE,
                                "COST" => $row2->COST,
                                "PKG_STATUS" => $row2->PKG_STATUS,
                            ];
                        }
                        $groupedData[$row->PKG_ID]['DETAILS']['PROFILE_DETAILS'][$row->TEST_ID] = $groupedData1;
                    } else {
                        $groupedData[$row->PKG_ID]['DETAILS'][] = [
                            "TEST_ID" => $row->TEST_ID,
                            "TEST_UC" => $row->TEST_UC,
                            "TEST_NAME" => $row->TEST_NAME,
                            "TEST_TYPE" => $row->TEST_TYPE,
                            "COST" => $row->COST,
                        ];
                    }
                    foreach ($groupedData as $detailId => &$detail) {
                        $totalTests = 0;
                        if (isset($detail['DETAILS']['PROFILE_DETAILS'])) {
                            foreach ($detail['DETAILS']['PROFILE_DETAILS'] as $profile) {
                                foreach ($profile['DETAILS'] as $test) {
                                    if ($test['TEST_TYPE'] == 'Test' && $test['PKG_STATUS'] == 'Active') {
                                        $totalTests++;
                                    }
                                }
                            }
                        }
                        foreach ($detail['DETAILS'] as $testDetail) {
                            if (!isset($testDetail['PROFILE_DETAILS']) && isset($testDetail['TEST_TYPE']) && $testDetail['TEST_TYPE'] == 'Test') {
                                $totalTests++;
                            }
                        }
                        $detail['TOT_TEST'] = $totalTests;
                    }
                }
                // $bnr["Catg_Banner"] = DB::table('dashboard')
                //     ->select('DASH_ID AS BANNER_ID', 'DASH_NAME AS BANNER_NAME', 'DASH_DESCRIPTION AS DESCRIPTION', 'BANNER_URL','COLOR_CODE')
                //     ->where(['DASH_ID' => $L_PKG_ID])->get();
                // $fltr_fxd_bnr = $fxd_banner->filter(function ($item) use ($sid) {
                //     return $item->BANNER_TYPE === $sid;
                // });
                // $bnr["Catg_Banner"] = $fltr_fxd_bnr->take(1)->values()->all();
                // } 
                $bnr1["Banner"] = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', $row->DASH_SECTION_ID)->get();
                $pkg["Package"] = \array_values($groupedData);
                $data = $pkg + $bnr1;
                // $data = array_values($groupedData);

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function diagallscan(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['PHARMA_ID'])) {

                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $pid = $input['PHARMA_ID'];

                $data = array();

                $data1 = DB::table('dashboard')
                    ->join('clinic_testdata', 'dashboard.DASH_NAME', '=', 'clinic_testdata.TEST_CATG')
                    ->select(
                        'clinic_testdata.*',
                        'dashboard.DASH_ID',
                        // 'dashboard.DIS_ID',
                        'dashboard.DASH_SECTION_ID',
                        'dashboard.DASH_SECTION_NAME',
                        'dashboard.DASH_NAME',
                        // 'dashboard.DASH_TYPE',
                        // 'dashboard.DASH_DESCRIPTION',
                        'dashboard.PHOTO1_URL',

                    )
                    ->where(['dashboard.DASH_SECTION_ID' => 'F', 'clinic_testdata.PHARMA_ID' => $pid])
                    ->orderby('dashboard.POSITION')
                    ->get();

                // return $data1;

                $F_DTL = [];
                foreach ($data1->pluck('DASH_ID')->unique() as $catg) {
                    $filteredArray = $data1->where('DASH_ID', $catg);
                    $organDetails = [];
                    foreach ($filteredArray as $item) {
                        $organID = $item->ORGAN_ID;
                        if (!isset($organDetails[$organID])) {
                            $organDetails[$organID] = [
                                "ORGAN_ID" => $organID,
                                "ORGAN_NAME" => $item->ORGAN_NAME,
                                "ORGAN_URL" => $item->ORGAN_URL,
                            ];
                        }
                    }
                    $F_DTL["Scan_Test"][] = [
                        "DASH_SECTION_NAME" => $filteredArray->first()->DASH_SECTION_NAME,
                        "DASH_ID" => $filteredArray->first()->DASH_ID,
                        "DASH_NAME" => $filteredArray->first()->DASH_NAME,
                        "PHOTO_URL" => $filteredArray->first()->PHOTO1_URL,
                        "ORGANS" => array_values($organDetails),
                    ];
                }

                $F_BNR["Banner"] = DB::table('promo_banner')
                    ->select('PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where(['DASH_SECTION_ID' => 'TS', 'PHARMA_ID' => $pid])->get();

                $data = $F_DTL + $F_BNR;

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function diagalltest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['PHARMA_ID'])) {

                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $pid = $input['PHARMA_ID'];

                $TST_DTL["Test"]  = DB::table('clinic_testdata')
                    ->select('TEST_ID', 'TEST_NAME', 'TEST_CATG', 'DISCOUNT', 'HOME_COLLECT', 'ORGAN_ID', 'ORGAN_NAME', 'ORGAN_URL', 'TEST_DESC', 'DEPARTMENT as CATEGORY', 'COST', 'KNOWN_AS', 'FASTING', 'GENDER_TYPE', 'AGE_TYPE', 'REPORT_TIME', 'PRESCRIPTION', 'ID_PROOF', 'QA1', 'QA2', 'QA3', 'QA4', 'QA5', 'QA6')
                    ->take(100)->get();
                // return $TST_DTL;
                $TST_BNR["Banner"] = DB::table('promo_banner')
                    ->select('PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where(['DASH_SECTION_ID' => 'TS', 'PHARMA_ID' => $pid])->get();

                $data = $TST_DTL + $TST_BNR;

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }
}
