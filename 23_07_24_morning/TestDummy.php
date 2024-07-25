<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use carbon\carbon;
use dateTime;

class TestDummy extends Controller
{
    function allsymptom_pathology1_dummy(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['LONGITUDE']) && isset($input['LATITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $data = DB::table('pharmacy')
                    ->select(
                        // 'clinic_testdata.DEPARTMENT',
                        'pharmacy.*',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                        * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                        * SIN(RADIANS('$latt'))))),2) as KM"),
                    )
                    ->join('clinic_testdata', 'pharmacy.PHARMA_ID', '=', 'clinic_testdata.PHARMA_ID')
                    ->where('clinic_testdata.DEPARTMENT', 'PATHOLOGY')
                    ->distinct()
                    ->get();
                $groupedData = [];
                foreach ($data as $row) {
                    $modifiedtest = [
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
                        // "HO_ID" => $row->HO_ID,
                    ];
                    $groupedData[] = $modifiedtest;
                }
                $modifiedphar_bnr = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('PHARMA_ID', '0')
                    ->take(3)
                    ->get();
                $groupedData[] = $modifiedphar_bnr;
                $p['Nearby_Pathology_Centre'] = $groupedData;

                $data1 = DB::table('dashboard')
                    ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard.DASH_ID')
                    ->join(DB::raw('(SELECT DISTINCT TEST_ID,TEST_SL,PHARMA_ID,TEST_NAME,TEST_CODE,TEST_SAMPLE,TEST_CATG,DEPARTMENT,TEST_DESC,KNOWN_AS,FASTING,GENDER_TYPE,AGE_TYPE,REPORT_TIME,PRESCRIPTION,ID_PROOF,QA1,QA2,QA3,QA4,QA5,QA6,HOME_COLLECT, MIN(COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID,TEST_SL,TEST_NAME,TEST_CODE,PHARMA_ID,TEST_SAMPLE,TEST_CATG,DEPARTMENT,TEST_DESC,KNOWN_AS,FASTING,GENDER_TYPE,AGE_TYPE,REPORT_TIME,PRESCRIPTION,ID_PROOF,QA1,QA2,QA3,QA4,QA5,QA6,HOME_COLLECT) as clinic_testdata'), function ($join) {
                        $join->on('sym_organ_test.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                    })
                    // ->join('master_testdata', 'sym_organ_test.TEST_ID', '=', 'master_testdata.TEST_ID')
                    ->select(
                        'dashboard.DASH_ID',
                        'dashboard.DASH_NAME',
                        'dashboard.PHOTO_URL',
                        'clinic_testdata.TEST_ID',
                        'clinic_testdata.TEST_SL',
                        'clinic_testdata.TEST_NAME',
                        'clinic_testdata.TEST_CODE',
                        'clinic_testdata.TEST_SAMPLE',
                        'clinic_testdata.TEST_CATG',
                        'clinic_testdata.DEPARTMENT',
                        'clinic_testdata.TEST_DESC',
                        'clinic_testdata.KNOWN_AS',
                        'clinic_testdata.FASTING',
                        'clinic_testdata.GENDER_TYPE',
                        'clinic_testdata.AGE_TYPE',
                        'clinic_testdata.REPORT_TIME',
                        'clinic_testdata.PRESCRIPTION',
                        'clinic_testdata.ID_PROOF',
                        'clinic_testdata.QA1',
                        'clinic_testdata.QA2',
                        'clinic_testdata.QA3',
                        'clinic_testdata.QA4',
                        'clinic_testdata.QA5',
                        'clinic_testdata.QA6',
                        'clinic_testdata.HOME_COLLECT',
                        'clinic_testdata.MIN_COST'
                    )
                    ->where(['dashboard.DASH_SECTION_ID' => 'S', 'STATUS' => 'Active', 'clinic_testdata.DEPARTMENT' => 'PATHOLOGY'])
                    ->orderby('dashboard.POSITION')
                    ->get();

                $S_DTL = [];
                $collection = collect($data1);
                $distinctValues = $collection->pluck('DASH_ID')->unique();
                foreach ($distinctValues as $row) {
                    $fltr_arr = $data1->filter(function ($item) use ($row) {
                        return $item->DASH_ID === $row;
                    });

                    $T_DTL = $fltr_arr->map(function ($item) {
                        return [
                            "TEST_ID" => $item->TEST_ID,
                            "TEST_SL" => $item->TEST_SL,
                            "TEST_NAME" => $item->TEST_NAME,
                            "TEST_CODE" => $item->TEST_CODE,
                            "TEST_SAMPLE" => $item->TEST_SAMPLE,
                            "TEST_CATG" => $item->TEST_CATG,
                            "DEPARTMENT" => $item->DEPARTMENT,
                            // "TEST_UNIT" => $item->TEST_UNIT,
                            // "NORMAL_RANGE" => $item->NORMAL_RANGE,
                            "TEST_DESC" => $item->TEST_DESC,
                            "MIN_COST" => $item->MIN_COST,
                            "KNOWN_AS" => $item->KNOWN_AS,
                            "FASTING" => $item->FASTING,
                            "GENDER_TYPE" => $item->GENDER_TYPE,
                            "AGE_TYPE" => $item->AGE_TYPE,
                            "REPORT_TIME" => $item->REPORT_TIME,
                            "PRESCRIPTION" => $item->PRESCRIPTION,
                            "ID_PROOF" => $item->ID_PROOF,
                            "QA1" => $item->QA1,
                            "QA2" => $item->QA2,
                            "QA3" => $item->QA3,
                            "QA4" => $item->QA4,
                            "QA5" => $item->QA5,
                            "QA6" => $item->QA6,
                            // "TestDetails" => [
                            //     [
                            //         "TEST_ID" => $item->TEST_ID,
                            //         "TEST_NAME" => $item->TEST_NAME,
                            //     ]
                            // ]
                        ];
                    })->values()->all();
                    $S_DTL[] = [
                        "DASH_ID" => $row,
                        "DASH_NAME" => $fltr_arr->first()->DASH_NAME,
                        "PHOTO_URL" => $fltr_arr->first()->PHOTO_URL,
                        "TOT_TEST" => count($T_DTL),
                        // "TOT_COST" => array_sum(array_column($T_DTL, 'COST')),
                        "DETAILS" => $T_DTL
                    ];
                }
                $modifiedsymto_bnr = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', 'TS')
                    ->take(3)
                    ->get();
                $S_DTL[] = $modifiedsymto_bnr;

                $S["Symptomatic_Pathology_Test"] = array_values($S_DTL);

                // SECTION-#### SINGLE TEST
                $TST_DTL = DB::table('master_testdata')
                    ->join(DB::raw("(SELECT DISTINCT clinic_testdata.TEST_ID,clinic_testdata.HOME_COLLECT, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata
            WHERE clinic_testdata.DEPARTMENT='PATHOLOGY'
            GROUP BY TEST_ID,clinic_testdata.DEPARTMENT,HOME_COLLECT) as clinic_testdata"), function ($join) {
                        $join->on('master_testdata.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                    })
                    ->select('master_testdata.*', 'clinic_testdata.MIN_COST', 'clinic_testdata.HOME_COLLECT AS HOME_COLLECT')
                    ->orderby('master_testdata.TEST_SL', 'asc')
                    ->take(100)->get()->toArray();

                // $TST["Popular_Single_Test"] = array_values($TST_DTL);
                $modifiedResponse = [];
                foreach ($TST_DTL as $test) {
                    $modifiedTest = [
                        "TEST_ID" => $test->TEST_ID,
                        "TEST_SL" => $test->TEST_SL,
                        "TEST_NAME" => $test->TEST_NAME,
                        "TEST_CODE" => $test->TEST_CODE,
                        "TEST_SAMPLE" => $test->TEST_SAMPLE,
                        "TEST_CATG" => $test->TEST_CATG,
                        "MIN_COST" => $test->MIN_COST,
                        "HOME_COLLECT" => $test->HOME_COLLECT,
                        "DEPARTMENT" => $test->DEPARTMENT,
                        "TEST_DESC" => $test->TEST_DESC,
                        "KNOWN_AS" => $test->KNOWN_AS,
                        "FASTING" => $test->FASTING,
                        "GENDER_TYPE" => $test->GENDER_TYPE,
                        "AGE_TYPE" => $test->AGE_TYPE,
                        "REPORT_TIME" => $test->REPORT_TIME,
                        "PRESCRIPTION" => $test->PRESCRIPTION,
                        "ID_PROOF" => $test->ID_PROOF,
                        "QA1" => $test->QA1,
                        "QA2" => $test->QA2,
                        "QA3" => $test->QA3,
                        "QA4" => $test->QA4,
                        "QA5" => $test->QA5,
                        "QA6" => $test->QA6,
                        // "TestDetails" => [
                        //     [
                        //         "TEST_ID" => $test->TEST_ID,
                        //         "TEST_NAME" => $test->TEST_NAME,
                        //     ]
                        // ]
                    ];
                    $modifiedResponse[] = $modifiedTest;
                }
                $modifiedTest_bnr = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', 'TS')
                    ->take(3)
                    ->get();
                $modifiedResponse[] = $modifiedTest_bnr;
                $TST["Popular_Pathology_Test"] = $modifiedResponse;

                // Step 1: Retrieve distinct TEST_SAMPLE values excluding those with '/' or ','
                $distinctSamples = DB::table('master_testdata')
                    ->where('DEPARTMENT', 'PATHOLOGY')
                    ->whereNotNull('TEST_SAMPLE')
                    ->where('TEST_SAMPLE', '!=', '')
                    ->where(function ($query) {
                        $query->where('TEST_SAMPLE', 'not like', '%/%')
                            ->where('TEST_SAMPLE', 'not like', '%,%');
                    })
                    ->select(DB::raw('UPPER(TRIM(TEST_SAMPLE)) as TEST_SAMPLE'))
                    ->pluck('TEST_SAMPLE')
                    ->unique();

                // Step 2: Initialize an empty array to hold the sample details
                $sampleDetails = [];

                // Step 3: Loop through each distinct sample to get the related test data
                foreach ($distinctSamples as $sample) {
                    $filteredSampleData = DB::table('master_testdata')
                        ->join(DB::raw("(SELECT DISTINCT clinic_testdata.TEST_ID, clinic_testdata.HOME_COLLECT, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata
        WHERE clinic_testdata.DEPARTMENT='PATHOLOGY'
        GROUP BY TEST_ID, clinic_testdata.DEPARTMENT, HOME_COLLECT) as clinic_testdata"), function ($join) {
                            $join->on('master_testdata.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                        })
                        ->select('master_testdata.*', 'clinic_testdata.MIN_COST', 'clinic_testdata.HOME_COLLECT AS HOME_COLLECT')
                        ->where('master_testdata.DEPARTMENT', 'PATHOLOGY')
                        ->whereRaw('UPPER(TRIM(master_testdata.TEST_SAMPLE)) like ?', ['%' . strtoupper(trim($sample)) . '%'])
                        ->get();

                    $sampleTests = $filteredSampleData->map(function ($item) {
                        return [
                            "TEST_ID" => $item->TEST_ID,
                            "TEST_SL" => $item->TEST_SL,
                            "TEST_NAME" => $item->TEST_NAME,
                            "TEST_CODE" => $item->TEST_CODE,
                            "TEST_SAMPLE" => $item->TEST_SAMPLE,
                            "TEST_CATG" => $item->TEST_CATG,
                            "DEPARTMENT" => $item->DEPARTMENT,
                            "TEST_DESC" => $item->TEST_DESC,
                            "MIN_COST" => $item->MIN_COST,
                            "KNOWN_AS" => $item->KNOWN_AS,
                            "FASTING" => $item->FASTING,
                            "GENDER_TYPE" => $item->GENDER_TYPE,
                            "AGE_TYPE" => $item->AGE_TYPE,
                            "REPORT_TIME" => $item->REPORT_TIME,
                            "PRESCRIPTION" => $item->PRESCRIPTION,
                            "ID_PROOF" => $item->ID_PROOF,
                            "QA1" => $item->QA1,
                            "QA2" => $item->QA2,
                            "QA3" => $item->QA3,
                            "QA4" => $item->QA4,
                            "QA5" => $item->QA5,
                            "QA6" => $item->QA6,
                            "HOME_COLLECT" => $item->HOME_COLLECT
                        ];
                    })->values()->all();

                    $sampleDetails[] = [
                        "SAMPLE_NAME" => $sample,
                        "TOT_TEST" => count($sampleTests),
                        "DETAILS" => $sampleTests
                    ];
                }

                $s_ts["Sample_Test"] = array_values($sampleDetails);

                // Return or process $s_ts as needed







                $p_bnr["Promo_Banner"] = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTion_ID', 'TS')
                    ->take(3)
                    ->get();


                $data = $p + $S + $TST + $s_ts + $p_bnr;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }
    function symptom_tests_dummy(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['PHARMA_ID']) && isset($input['DASH_ID'])) {
                $pharma_id = $input['PHARMA_ID'];
                $dash_id = $input['DASH_ID'];

                //SECTION-S #### SYMPTOMATIC TEST
                $data1 = DB::table('dashboard')
                    ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard.DASH_ID')
                    ->join(DB::raw('(SELECT DISTINCT TEST_ID,TEST_SL,PHARMA_ID,TEST_NAME,TEST_CODE,TEST_SAMPLE,TEST_CATG,DEPARTMENT,TEST_DESC,DISCOUNT,KNOWN_AS,FASTING,GENDER_TYPE,AGE_TYPE,REPORT_TIME,PRESCRIPTION,ID_PROOF,QA1,QA2,QA3,QA4,QA5,QA6,HOME_COLLECT,COST, MIN(COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID,TEST_SL,TEST_NAME,TEST_CODE,PHARMA_ID,TEST_SAMPLE,COST, DISCOUNT,TEST_CATG,DEPARTMENT,TEST_DESC,KNOWN_AS,FASTING,GENDER_TYPE,AGE_TYPE,REPORT_TIME,PRESCRIPTION,ID_PROOF,QA1,QA2,QA3,QA4,QA5,QA6,HOME_COLLECT) as clinic_testdata'), function ($join) {
                        $join->on('sym_organ_test.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                    })
                    ->select(
                        'dashboard.DASH_ID',
                        'dashboard.DASH_NAME',
                        'dashboard.PHOTO_URL',
                        'clinic_testdata.PHARMA_ID',
                        'clinic_testdata.TEST_ID',
                        'clinic_testdata.TEST_SL',
                        'clinic_testdata.TEST_NAME',
                        'clinic_testdata.TEST_CODE',
                        'clinic_testdata.TEST_SAMPLE',
                        'clinic_testdata.TEST_CATG',
                        'clinic_testdata.DEPARTMENT',
                        'clinic_testdata.TEST_DESC',
                        'clinic_testdata.KNOWN_AS',
                        'clinic_testdata.FASTING',
                        'clinic_testdata.GENDER_TYPE',
                        'clinic_testdata.AGE_TYPE',
                        'clinic_testdata.REPORT_TIME',
                        'clinic_testdata.PRESCRIPTION',
                        'clinic_testdata.ID_PROOF',
                        'clinic_testdata.QA1',
                        'clinic_testdata.QA2',
                        'clinic_testdata.QA3',
                        'clinic_testdata.QA4',
                        'clinic_testdata.QA5',
                        'clinic_testdata.QA6',
                        'clinic_testdata.HOME_COLLECT',
                        'clinic_testdata.MIN_COST',
                        'clinic_testdata.COST',
                        'clinic_testdata.DISCOUNT',
                    )
                    ->where(['dashboard.DASH_SECTION_ID' => 'S', 'STATUS' => 'Active', 'clinic_testdata.PHARMA_ID' => $pharma_id, 'dashboard.DASH_ID' => $dash_id])
                    ->orderby('dashboard.POSITION')
                    ->get();

                $S_DTL = [];
                $collection = collect($data1);
                $distinctValues = $collection->pluck('DASH_ID')->unique();
                foreach ($distinctValues as $row) {
                    $fltr_arr = $data1->filter(function ($item) use ($row) {
                        return $item->DASH_ID === $row;
                    });

                    $T_DTL = $fltr_arr->map(function ($item) {
                        return [
                            "TEST_ID" => $item->TEST_ID,
                            "TEST_SL" => $item->TEST_SL,
                            "TEST_NAME" => $item->TEST_NAME,
                            "TEST_CODE" => $item->TEST_CODE,
                            "TEST_SAMPLE" => $item->TEST_SAMPLE,
                            "TEST_CATG" => $item->TEST_CATG,
                            "DEPARTMENT" => $item->DEPARTMENT,
                            // "TEST_UNIT" => $item->TEST_UNIT,
                            // "NORMAL_RANGE" => $item->NORMAL_RANGE,
                            "TEST_DESC" => $item->TEST_DESC,
                            "MIN_COST" => $item->MIN_COST,
                            "COST" => $item->COST,
                            "DISCOUNT" => $item->DISCOUNT,
                            "KNOWN_AS" => $item->KNOWN_AS,
                            "FASTING" => $item->FASTING,
                            "GENDER_TYPE" => $item->GENDER_TYPE,
                            "AGE_TYPE" => $item->AGE_TYPE,
                            "REPORT_TIME" => $item->REPORT_TIME,
                            "PRESCRIPTION" => $item->PRESCRIPTION,
                            "ID_PROOF" => $item->ID_PROOF,
                            "QA1" => $item->QA1,
                            "QA2" => $item->QA2,
                            "QA3" => $item->QA3,
                            "QA4" => $item->QA4,
                            "QA5" => $item->QA5,
                            "QA6" => $item->QA6,
                            "TestDetails" => [
                                [
                                    "TEST_ID" => $item->TEST_ID,
                                    "TEST_NAME" => $item->TEST_NAME,
                                ]
                            ]
                        ];
                    })->values()->all();
                    $S_DTL[] = [
                        "DASH_ID" => $row,
                        "DASH_NAME" => $fltr_arr->first()->DASH_NAME,
                        "PHOTO_URL" => $fltr_arr->first()->PHOTO_URL,
                        "TOT_TEST" => count($T_DTL),
                        // "TOT_COST" => array_sum(array_column($T_DTL, 'COST')),
                        "DETAILS" => $T_DTL
                    ];
                }

                // If there's only one result, return that single object directly
                if (count($S_DTL) === 1) {
                    $response = ['Success' => true, 'data' => $S_DTL[0], 'code' => 200];
                } else {
                    $response = ['Success' => true, 'data' => $S_DTL, 'code' => 200];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function allsymptom_pathology_dummy(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['LONGITUDE']) && isset($input['LATITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $data = DB::table('pharmacy')
                    ->select(
                        // 'clinic_testdata.DEPARTMENT',
                        'pharmacy.*',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                        * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                        * SIN(RADIANS('$latt'))))),2) as KM"),
                    )
                    ->join('clinic_testdata', 'pharmacy.PHARMA_ID', '=', 'clinic_testdata.PHARMA_ID')
                    ->where('clinic_testdata.DEPT_ID', 'D2')
                    ->distinct()
                    ->get();
                $groupedData = [];
                foreach ($data as $row) {
                    $modifiedtest = [
                        "PHARMA_ID" => $row->PHARMA_ID,
                        "PHARMA_NAME" => $row->ITEM_NAME,
                        "CLINIC_TYPE" => $row->CLINIC_TYPE,
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
                        // "HO_ID" => $row->HO_ID,
                    ];
                    $groupedData[] = $modifiedtest;
                }
                $modifiedphar_bnr = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('PHARMA_ID', '0')
                    ->take(3)
                    ->get();
                $groupedData[] = $modifiedphar_bnr;
                $p['Nearby_Pathology_Centre'] = $groupedData;

                $data1 = DB::table('dashboard_item')
                    ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard_item.DASH_ID')
                    ->join(DB::raw('(SELECT DISTINCT TEST_ID,DEPT_ID,TEST_SL,PHARMA_ID,TEST_NAME,TEST_CODE,TEST_SAMPLE,TEST_CATG,DEPARTMENT,TEST_DESC,KNOWN_AS,FASTING,GENDER_TYPE,AGE_TYPE,REPORT_TIME,PRESCRIPTION,ID_PROOF,QA1,QA2,QA3,QA4,QA5,QA6,HOME_COLLECT, MIN(COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID,DEPT_ID,TEST_SL,TEST_NAME,TEST_CODE,PHARMA_ID,TEST_SAMPLE,TEST_CATG,DEPARTMENT,TEST_DESC,KNOWN_AS,FASTING,GENDER_TYPE,AGE_TYPE,REPORT_TIME,PRESCRIPTION,ID_PROOF,QA1,QA2,QA3,QA4,QA5,QA6,HOME_COLLECT) as clinic_testdata'), function ($join) {
                        $join->on('sym_organ_test.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                    })
                    // ->join('master_testdata', 'sym_organ_test.TEST_ID', '=', 'master_testdata.TEST_ID')
                    ->select(
                        'dashboard_item.DASH_ID',
                        'dashboard_item.DASH_NAME',
                        'dashboard_item.DI_IMG1 as PHOTO_URL1',
                        'dashboard_item.DI_IMG2 as PHOTO_URL2',
                        'dashboard_item.DI_IMG3 as PHOTO_URL3',
                        'dashboard_item.DI_IMG4 as PHOTO_URL4',
                        'dashboard_item.DI_IMG5 as PHOTO_URL5',
                        'dashboard_item.DI_IMG6 as PHOTO_URL6',
                        'dashboard_item.DI_IMG7 as PHOTO_URL7',
                        'dashboard_item.DI_IMG8 as PHOTO_URL8',
                        'dashboard_item.DI_IMG9 as PHOTO_URL9',
                        'dashboard_item.DI_IMG10 as PHOTO_URL10',
                        'clinic_testdata.TEST_ID',
                        'clinic_testdata.TEST_SL',
                        'clinic_testdata.TEST_NAME',
                        'clinic_testdata.TEST_CODE',
                        'clinic_testdata.TEST_SAMPLE',
                        'clinic_testdata.TEST_CATG',
                        'clinic_testdata.DEPARTMENT',
                        'clinic_testdata.TEST_DESC',
                        'clinic_testdata.KNOWN_AS',
                        'clinic_testdata.FASTING',
                        'clinic_testdata.GENDER_TYPE',
                        'clinic_testdata.AGE_TYPE',
                        'clinic_testdata.REPORT_TIME',
                        'clinic_testdata.PRESCRIPTION',
                        'clinic_testdata.ID_PROOF',
                        'clinic_testdata.QA1',
                        'clinic_testdata.QA2',
                        'clinic_testdata.QA3',
                        'clinic_testdata.QA4',
                        'clinic_testdata.QA5',
                        'clinic_testdata.QA6',
                        'clinic_testdata.HOME_COLLECT',
                        'clinic_testdata.MIN_COST'
                    )
                    ->where(['dashboard_item.DASH_SECTION_ID' => 'S', 'DASH_STATUS' => 'Active', 'clinic_testdata.DEPT_ID' => 'D2'])
                    ->orderby('dashboard_item.DASH_POSITION')
                    ->get();

                $S_DTL = [];
                $collection = collect($data1);
                $distinctValues = $collection->pluck('DASH_ID')->unique();
                foreach ($distinctValues as $row) {
                    $fltr_arr = $data1->filter(function ($item) use ($row) {
                        return $item->DASH_ID === $row;
                    });

                    $T_DTL = $fltr_arr->map(function ($item) {
                        return [
                            "TEST_ID" => $item->TEST_ID,
                            "TEST_SL" => $item->TEST_SL,
                            "TEST_NAME" => $item->TEST_NAME,
                            "TEST_CODE" => $item->TEST_CODE,
                            "TEST_SAMPLE" => $item->TEST_SAMPLE,
                            "TEST_CATG" => $item->TEST_CATG,
                            "DEPARTMENT" => $item->DEPARTMENT,
                            // "TEST_UNIT" => $item->TEST_UNIT,
                            // "NORMAL_RANGE" => $item->NORMAL_RANGE,
                            "TEST_DESC" => $item->TEST_DESC,
                            "MIN_COST" => $item->MIN_COST,
                            "KNOWN_AS" => $item->KNOWN_AS,
                            "FASTING" => $item->FASTING,
                            "GENDER_TYPE" => $item->GENDER_TYPE,
                            "AGE_TYPE" => $item->AGE_TYPE,
                            "REPORT_TIME" => $item->REPORT_TIME,
                            "PRESCRIPTION" => $item->PRESCRIPTION,
                            "ID_PROOF" => $item->ID_PROOF,
                            "QA1" => $item->QA1,
                            "QA2" => $item->QA2,
                            "QA3" => $item->QA3,
                            "QA4" => $item->QA4,
                            "QA5" => $item->QA5,
                            "QA6" => $item->QA6,
                            // "TestDetails" => [
                            //     [
                            //         "TEST_ID" => $item->TEST_ID,
                            //         "TEST_NAME" => $item->TEST_NAME,
                            //     ]
                            // ]
                        ];
                    })->values()->all();
                    $S_DTL[] = [
                        "DASH_ID" => $row,
                        "DASH_NAME" => $fltr_arr->first()->DASH_NAME,
                        "PHOTO_URL" => $fltr_arr->first()->PHOTO_URL1,
                        "PHOTO_URL2" => $fltr_arr->first()->PHOTO_URL2,
                        "PHOTO_URL3" => $fltr_arr->first()->PHOTO_URL3,
                        "PHOTO_URL4" => $fltr_arr->first()->PHOTO_URL4,
                        "PHOTO_URL5" => $fltr_arr->first()->PHOTO_URL5,
                        "PHOTO_URL6" => $fltr_arr->first()->PHOTO_URL6,
                        "PHOTO_URL7" => $fltr_arr->first()->PHOTO_URL7,
                        "PHOTO_URL8" => $fltr_arr->first()->PHOTO_URL8,
                        "PHOTO_URL9" => $fltr_arr->first()->PHOTO_URL9,
                        "PHOTO_URL10" => $fltr_arr->first()->PHOTO_URL10,
                        "TOT_TEST" => count($T_DTL),
                        // "TOT_COST" => array_sum(array_column($T_DTL, 'COST')),
                        "DETAILS" => $T_DTL
                    ];
                }
                $modifiedsymto_bnr = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', 'TS')
                    ->take(3)
                    ->get();
                $S_DTL[] = $modifiedsymto_bnr;

                $S["Symptomatic_Pathology_Test"] = array_values($S_DTL);

                // SECTION-#### SINGLE TEST
                $TST_DTL = DB::table('master_test')
                    ->join(DB::raw("(SELECT DISTINCT clinic_testdata.TEST_ID,clinic_testdata.REPORT_TIME,clinic_testdata.PRESCRIPTION,clinic_testdata.HOME_COLLECT,clinic_testdata.TEST_CATG, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata
            WHERE clinic_testdata.DEPARTMENT='PATHOLOGY'
            GROUP BY TEST_ID,clinic_testdata.HOME_COLLECT,clinic_testdata.TEST_CATG,clinic_testdata.REPORT_TIME,clinic_testdata.PRESCRIPTION) as clinic_testdata"), function ($join) {
                        $join->on('master_test.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                    })
                    ->select('master_test.*', 'clinic_testdata.MIN_COST', 'clinic_testdata.TEST_CATG', 'clinic_testdata.HOME_COLLECT AS HOME_COLLECT', 'clinic_testdata.REPORT_TIME', 'clinic_testdata.PRESCRIPTION')
                    ->orderby('master_test.TEST_SL', 'asc')
                    ->take(100)->get()->toArray();

                // $TST["Popular_Single_Test"] = array_values($TST_DTL);
                $modifiedResponse = [];
                foreach ($TST_DTL as $test) {
                    $modifiedTest = [
                        "TEST_ID" => $test->TEST_ID,
                        "TEST_SL" => $test->TEST_SL,
                        "TEST_NAME" => $test->TEST_NAME,
                        "TEST_CODE" => $test->TEST_CODE,
                        "SAMPLE_ID" => $test->SAMPLE_ID,
                        "TEST_CATG" => $test->SUB_DEPARTMENT,
                        "MIN_COST" => $test->MIN_COST,
                        "HOME_COLLECT" => $test->HOME_COLLECT,
                        "DEPARTMENT" => $test->DEPARTMENT,
                        "TEST_DESC" => $test->TEST_DESC,
                        "KNOWN_AS" => $test->KNOWN_AS,
                        "FASTING" => $test->FASTING,
                        "GENDER_TYPE" => $test->GENDER_TYPE,
                        "AGE_TYPE" => $test->AGE_TYPE,
                        "REPORT_TIME" => $test->REPORT_TIME,
                        "PRESCRIPTION" => $test->PRESCRIPTION,
                        "ID_PROOF" => $test->ID_PROOF,
                        "QA1" => $test->TQA1,
                        "QA2" => $test->TQA2,
                        "QA3" => $test->TQA3,
                        "QA4" => $test->TQA4,
                        "QA5" => $test->TQA5,
                        "QA6" => $test->TQA6,
                        // "TestDetails" => [
                        //     [
                        //         "TEST_ID" => $test->TEST_ID,
                        //         "TEST_NAME" => $test->TEST_NAME,
                        //     ]
                        // ]
                    ];
                    $modifiedResponse[] = $modifiedTest;
                }
                $modifiedTest_bnr = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', 'TS')
                    ->take(3)
                    ->get();
                $modifiedResponse[] = $modifiedTest_bnr;
                $TST["Popular_Pathology_Test"] = $modifiedResponse;

                //         $data2 = DB::table('master_testdata')
                //             ->join(DB::raw("(SELECT DISTINCT clinic_testdata.TEST_ID,clinic_testdata.HOME_COLLECT, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata
                // WHERE clinic_testdata.DEPARTMENT='PATHOLOGY'
                // GROUP BY TEST_ID,clinic_testdata.DEPARTMENT,HOME_COLLECT) as clinic_testdata"), function ($join) {
                //                 $join->on('master_testdata.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                //             })
                //             ->select('master_testdata.*', 'clinic_testdata.MIN_COST', 'clinic_testdata.HOME_COLLECT AS HOME_COLLECT')
                //             ->where('master_testdata.DEPARTMENT', 'PATHOLOGY')
                //             ->whereNotNull('master_testdata.TEST_SAMPLE') // Ensure the specific column is not null
                //             ->where('master_testdata.TEST_SAMPLE', '!=', '') // Ensure the specific column is not empty
                //             ->get();

                //         $sampleDetails = [];
                //         $collection = collect($data2);
                //         $distinctSamples = $collection->pluck('TEST_SAMPLE')->unique();

                //         foreach ($distinctSamples as $sample) {
                //             $filteredSample = $data2->filter(function ($item) use ($sample) {
                //                 return $item->TEST_SAMPLE === $sample;
                //             });

                //             $sampleTests = $filteredSample->map(function ($item) {
                //                 return [
                //                     "TEST_ID" => $item->TEST_ID,
                //                     "TEST_SL" => $item->TEST_SL,
                //                     "TEST_NAME" => $item->TEST_NAME,
                //                     "TEST_CODE" => $item->TEST_CODE,
                //                     "TEST_SAMPLE" => $item->TEST_SAMPLE,
                //                     "TEST_CATG" => $item->TEST_CATG,
                //                     "DEPARTMENT" => $item->DEPARTMENT,
                //                     "TEST_DESC" => $item->TEST_DESC,
                //                     "MIN_COST" => $item->MIN_COST,
                //                     "KNOWN_AS" => $item->KNOWN_AS,
                //                     "FASTING" => $item->FASTING,
                //                     "GENDER_TYPE" => $item->GENDER_TYPE,
                //                     "AGE_TYPE" => $item->AGE_TYPE,
                //                     "REPORT_TIME" => $item->REPORT_TIME,
                //                     "PRESCRIPTION" => $item->PRESCRIPTION,
                //                     "ID_PROOF" => $item->ID_PROOF,
                //                     "QA1" => $item->QA1,
                //                     "QA2" => $item->QA2,
                //                     "QA3" => $item->QA3,
                //                     "QA4" => $item->QA4,
                //                     "QA5" => $item->QA5,
                //                     "QA6" => $item->QA6,
                //                     "HOME_COLLECT" => $item->HOME_COLLECT
                //                 ];
                //             })->values()->all();

                //             $sampleDetails[] = [
                //                 "SAMPLE_NAME" => $sample,
                //                 "TOT_TEST" => count($sampleTests),
                //                 "DETAILS" => $sampleTests
                //             ];
                //         }

                //         $s_ts["Sample_Test"] = array_values($sampleDetails);

                // Step 1: Retrieve distinct TEST_SAMPLE values excluding those with '/' or ','
                $distinctSamples = DB::table('master_test')
                    ->where('DEPARTMENT', 'PATHOLOGY')
                    ->whereNotNull('SAMPLE_NAME')
                    ->where('SAMPLE_NAME', '!=', NULL)
                    ->where(function ($query) {
                        $query->where('SAMPLE_NAME', 'not like', '%/%')
                            ->where('SAMPLE_NAME', 'not like', '%,%');
                    })
                    ->select(DB::raw('UPPER(TRIM(SAMPLE_NAME)) as TEST_SAMPLE'), 'SAMPLE_ID')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'TEST_SAMPLE' => $item->TEST_SAMPLE,
                            'SAMPLE_ID' => $item->SAMPLE_ID
                        ];
                    })
                    ->unique('TEST_SAMPLE');

                // Step 2: Initialize an empty array to hold the sample details
                $sampleDetails = [];

                // Step 3: Loop through each distinct sample to get the related test data
                foreach ($distinctSamples as $sample) {
                    $filteredSampleData = DB::table('master_test')
                        ->join(DB::raw("(SELECT DISTINCT clinic_testdata.TEST_ID, clinic_testdata.HOME_COLLECT,clinic_testdata.REPORT_TIME, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata
WHERE clinic_testdata.DEPARTMENT='PATHOLOGY'
GROUP BY TEST_ID, clinic_testdata.DEPARTMENT,clinic_testdata.REPORT_TIME, HOME_COLLECT) as clinic_testdata"), function ($join) {
                            $join->on('master_test.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                        })
                        ->select('master_test.*', 'clinic_testdata.MIN_COST', 'clinic_testdata.HOME_COLLECT AS HOME_COLLECT', 'clinic_testdata.REPORT_TIME')
                        ->where('master_test.DEPARTMENT', 'PATHOLOGY')
                        ->whereRaw('UPPER(TRIM(master_test.SAMPLE_NAME)) like ?', ['%' . strtoupper(trim($sample['TEST_SAMPLE'])) . '%'])
                        ->get();

                    $sampleTests = $filteredSampleData->map(function ($item) {
                        return [
                            "TEST_ID" => $item->TEST_ID,
                            "TEST_SL" => $item->TEST_SL,
                            "TEST_NAME" => $item->TEST_NAME,
                            "TEST_CODE" => $item->TEST_CODE,
                            "TEST_SAMPLE" => $item->SAMPLE_NAME,
                            "TEST_CATG" => $item->SUB_DEPARTMENT,
                            "DEPARTMENT" => $item->DEPARTMENT,
                            "TEST_DESC" => $item->TEST_DESC,
                            "MIN_COST" => $item->MIN_COST,
                            "KNOWN_AS" => $item->KNOWN_AS,
                            "FASTING" => $item->FASTING,
                            "GENDER_TYPE" => $item->GENDER_TYPE,
                            "AGE_TYPE" => $item->AGE_TYPE,
                            "REPORT_TIME" => $item->REPORT_TIME,
                            "PRESCRIPTION" => $item->PRESCRIPTION,
                            "ID_PROOF" => $item->ID_PROOF,
                            "QA1" => $item->TQA1,
                            "QA2" => $item->TQA2,
                            "QA3" => $item->TQA3,
                            "QA4" => $item->TQA4,
                            "QA5" => $item->TQA5,
                            "QA6" => $item->TQA6,
                            "HOME_COLLECT" => $item->HOME_COLLECT
                        ];
                    })->values()->all();

                    $sampleDetails[] = [
                        "SAMPLE_NAME" => $sample['TEST_SAMPLE'],
                        "SAMPLE_ID" => $sample['SAMPLE_ID'],
                        "TOT_TEST" => count($sampleTests),
                        "DETAILS" => $sampleTests
                    ];
                }

                $s_ts["Sample_Test"] = array_values($sampleDetails);





                $p_bnr["Promo_Banner"] = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTion_ID', 'TS')
                    ->take(3)
                    ->get();


                $data = $p + $S + $TST + $s_ts + $p_bnr;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function clinic_pathology_dummy(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['PHARMA_ID'])) {
                $pharmaId = $input['PHARMA_ID'];



                //                 // Step 1: Retrieve distinct TEST_SAMPLE values excluding those with '/' or ','
//                 $distinctSamples = DB::table('master_testdata')
//                     ->where('DEPARTMENT', 'PATHOLOGY')
//                     ->whereNotNull('TEST_SAMPLE')
//                     ->where('TEST_SAMPLE', '!=', '')
//                     ->where(function ($query) {
//                         $query->where('TEST_SAMPLE', 'not like', '%/%')
//                             ->where('TEST_SAMPLE', 'not like', '%,%');
//                     })
//                     ->select(DB::raw('UPPER(TRIM(TEST_SAMPLE)) as TEST_SAMPLE'))
//                     ->pluck('TEST_SAMPLE')
//                     ->unique();

                //                 // Step 2: Initialize an empty array to hold the sample details
//                 $sampleDetails = [];

                //                 // Step 3: Loop through each distinct sample to get the related test data
//                 foreach ($distinctSamples as $sample) {
//                     $filteredSampleData = DB::table('master_testdata')
//                         ->join(DB::raw("(SELECT DISTINCT clinic_testdata.TEST_ID,clinic_testdata.PHARMA_ID, clinic_testdata.HOME_COLLECT, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata
// WHERE clinic_testdata.DEPARTMENT='PATHOLOGY'
// GROUP BY TEST_ID, clinic_testdata.PHARMA_ID, clinic_testdata.DEPARTMENT, HOME_COLLECT) as clinic_testdata"), function ($join) {
//                             $join->on('master_testdata.TEST_ID', '=', 'clinic_testdata.TEST_ID');
//                         })
//                         ->select('master_testdata.*', 'clinic_testdata.MIN_COST', 'clinic_testdata.PHARMA_ID', 'clinic_testdata.HOME_COLLECT AS HOME_COLLECT')
//                         ->where(['master_testdata.DEPARTMENT' => 'PATHOLOGY', 'clinic_testdata.PHARMA_ID' => $pharmaId])
//                         ->whereRaw('UPPER(TRIM(master_testdata.TEST_SAMPLE)) like ?', ['%' . strtoupper(trim($sample)) . '%'])
//                         ->get();

                //                     $sampleTests = $filteredSampleData->map(function ($item) {
//                         return [
//                             "TEST_ID" => $item->TEST_ID,
//                             "TEST_SL" => $item->TEST_SL,
//                             "TEST_NAME" => $item->TEST_NAME,
//                             "TEST_CODE" => $item->TEST_CODE,
//                             "TEST_SAMPLE" => $item->TEST_SAMPLE,
//                             "TEST_CATG" => $item->TEST_CATG,
//                             "DEPARTMENT" => $item->DEPARTMENT,
//                             "TEST_DESC" => $item->TEST_DESC,
//                             "MIN_COST" => $item->MIN_COST,
//                             "KNOWN_AS" => $item->KNOWN_AS,
//                             "FASTING" => $item->FASTING,
//                             "GENDER_TYPE" => $item->GENDER_TYPE,
//                             "AGE_TYPE" => $item->AGE_TYPE,
//                             "REPORT_TIME" => $item->REPORT_TIME,
//                             "PRESCRIPTION" => $item->PRESCRIPTION,
//                             "ID_PROOF" => $item->ID_PROOF,
//                             "QA1" => $item->QA1,
//                             "QA2" => $item->QA2,
//                             "QA3" => $item->QA3,
//                             "QA4" => $item->QA4,
//                             "QA5" => $item->QA5,
//                             "QA6" => $item->QA6,
//                             "HOME_COLLECT" => $item->HOME_COLLECT
//                         ];
//                     })->values()->all();

                //                     $sampleDetails[] = [
//                         "SAMPLE_NAME" => $sample,
//                         "TOT_TEST" => count($sampleTests),
//                         // "DETAILS" => $sampleTests
//                     ];
//                 }

                //                 $s_ts["Sample_Test"] = array_values($sampleDetails);

                $distinctSamples = DB::table('master_test')
                    ->where('DEPARTMENT', 'PATHOLOGY')
                    ->whereNotNull('SAMPLE_NAME')
                    ->where('SAMPLE_NAME', '!=', NULL)
                    ->where(function ($query) {
                        $query->where('SAMPLE_NAME', 'not like', '%/%')
                            ->where('SAMPLE_NAME', 'not like', '%,%');
                    })
                    ->select(DB::raw('UPPER(TRIM(SAMPLE_NAME)) as TEST_SAMPLE'), 'SAMPLE_ID')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'TEST_SAMPLE' => $item->TEST_SAMPLE,
                            'SAMPLE_ID' => $item->SAMPLE_ID
                        ];
                    })
                    ->unique('TEST_SAMPLE');

                // Step 2: Initialize an empty array to hold the sample details
                $sampleDetails = [];

                // Step 3: Loop through each distinct sample to get the related test data
                foreach ($distinctSamples as $sample) {
                    $filteredSampleData = DB::table('master_test')
                        ->join(DB::raw("(SELECT DISTINCT clinic_testdata.TEST_ID, clinic_testdata.HOME_COLLECT,clinic_testdata.PHARMA_ID,clinic_testdata.REPORT_TIME, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata
WHERE clinic_testdata.DEPARTMENT='PATHOLOGY'
GROUP BY TEST_ID, clinic_testdata.DEPARTMENT,clinic_testdata.PHARMA_ID,clinic_testdata.REPORT_TIME, HOME_COLLECT) as clinic_testdata"), function ($join) {
                            $join->on('master_test.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                        })
                        ->select('master_test.*', 'clinic_testdata.MIN_COST', 'clinic_testdata.HOME_COLLECT AS HOME_COLLECT', 'clinic_testdata.REPORT_TIME')
                        ->where(['master_test.DEPT_ID' => 'D1', 'clinic_testdata.PHARMA_ID' => $pharmaId])
                        ->whereRaw('UPPER(TRIM(master_test.SAMPLE_NAME)) like ?', ['%' . strtoupper(trim($sample['TEST_SAMPLE'])) . '%'])
                        ->get();

                    $sampleTests = $filteredSampleData->map(function ($item) {
                        return [
                            "TEST_ID" => $item->TEST_ID,
                            "TEST_SL" => $item->TEST_SL,
                            "TEST_NAME" => $item->TEST_NAME,
                            "TEST_CODE" => $item->TEST_CODE,
                            "TEST_SAMPLE" => $item->SAMPLE_NAME,
                            "TEST_CATG" => $item->SUB_DEPARTMENT,
                            "DEPARTMENT" => $item->DEPARTMENT,
                            "TEST_DESC" => $item->TEST_DESC,
                            "MIN_COST" => $item->MIN_COST,
                            "KNOWN_AS" => $item->KNOWN_AS,
                            "FASTING" => $item->FASTING,
                            "GENDER_TYPE" => $item->GENDER_TYPE,
                            "AGE_TYPE" => $item->AGE_TYPE,
                            "REPORT_TIME" => $item->REPORT_TIME,
                            "PRESCRIPTION" => $item->PRESCRIPTION,
                            "ID_PROOF" => $item->ID_PROOF,
                            "QA1" => $item->TQA1,
                            "QA2" => $item->TQA2,
                            "QA3" => $item->TQA3,
                            "QA4" => $item->TQA4,
                            "QA5" => $item->TQA5,
                            "QA6" => $item->TQA6,
                            "HOME_COLLECT" => $item->HOME_COLLECT
                        ];
                    })->values()->all();

                    $sampleDetails[] = [
                        "SAMPLE_NAME" => $sample['TEST_SAMPLE'],
                        "SAMPLE_ID" => $sample['SAMPLE_ID'],
                        "TOT_TEST" => count($sampleTests),
                        "DETAILS" => $sampleTests
                    ];
                }

                $s_ts["Sample_Test"] = array_values($sampleDetails);


                $data = $s_ts;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }
    function labdashboard1_dummy(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['PHARMA_ID'])) {
                $pharma_id = $input['PHARMA_ID'];

                $dash = DB::table('dashboard')
                    ->join('package', 'package.LAB_PKG_ID', '=', 'dashboard.DASH_ID')
                    ->where('CATEGORY', 'like', '%' . 'L' . '%')->where(['dashboard.STATUS' => 'Active', 'package.PHARMA_ID' => $pharma_id])->get();

                //SECTION-F #### DASHBOARD
                $data1 = DB::table('dashboard')
                    ->join('master_testdata', 'dashboard.DASH_NAME', '=', 'master_testdata.TEST_CATG')
                    ->join('clinic_testdata', 'clinic_testdata.TEST_ID', '=', 'master_testdata.TEST_ID')
                    ->select(
                        'master_testdata.*',
                        'dashboard.DASH_ID',
                        'dashboard.DIS_ID',
                        'dashboard.DASH_SECTION_ID',
                        'dashboard.DASH_SECTION_NAME',
                        'dashboard.DASH_NAME',
                        'dashboard.DASH_TYPE',
                        'dashboard.DASH_DESCRIPTION',
                        'dashboard.PHOTO_URL',
                        'dashboard.PHOTO1_URL',
                        'clinic_testdata.PHARMA_ID'
                    )
                    ->where(['dashboard.DASH_SECTION_ID' => 'F', 'clinic_testdata.PHARMA_ID' => $pharma_id])
                    ->orderby('master_testdata.ORGAN_NAME')
                    ->get();



                $F1_DTL = [];
                foreach ($data1->pluck('DASH_ID')->unique() as $DSI) {
                    $filteredArray = $data1->where('DASH_ID', $DSI);
                    $organDetails = [];
                    foreach ($filteredArray as $item) {
                        $organID = $item->ORGAN_ID;
                        if (!isset($organDetails[$organID])) {
                            $organDetails[$organID] = [
                                "ORGAN_ID" => $organID,
                                "ORGAN_NAME" => $item->ORGAN_NAME,
                                "DASH_NAME" => $filteredArray->first()->DASH_NAME,
                                "ORGAN_URL" => $item->ORGAN_URL,
                                "DASH_PHOTO_URL" => $filteredArray->first()->PHOTO_URL,
                            ];
                        }
                    }
                    $F1_DTL[] = [
                        "DASH_ID" => $filteredArray->first()->DASH_ID,
                        "DASH_SECTION_NAME" => $filteredArray->first()->DASH_SECTION_NAME,
                        "DASH_NAME" => $filteredArray->first()->DASH_NAME,
                        "PHARMA_ID" => $filteredArray->first()->PHARMA_ID,
                        "PHOTO_URL" => $filteredArray->first()->PHOTO_URL,
                        "PHOTO1_URL" => $filteredArray->first()->PHOTO1_URL,
                        "ORGANS" => array_values($organDetails),
                    ];
                }

                $F1["Dashboard"] = array_values($F1_DTL);

                //SECTION-S #### SYMPTOMATIC TEST
                $data1 = DB::table('dashboard')
                    ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard.DASH_ID')
                    ->join(DB::raw('(SELECT DISTINCT TEST_ID,TEST_SL,PHARMA_ID,TEST_NAME,TEST_CODE,TEST_SAMPLE,TEST_CATG,DEPARTMENT,TEST_DESC,KNOWN_AS,FASTING,GENDER_TYPE,AGE_TYPE,REPORT_TIME,PRESCRIPTION,ID_PROOF,QA1,QA2,QA3,QA4,QA5,QA6,HOME_COLLECT, MIN(COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID,TEST_SL,TEST_NAME,TEST_CODE,PHARMA_ID,TEST_SAMPLE,TEST_CATG,DEPARTMENT,TEST_DESC,KNOWN_AS,FASTING,GENDER_TYPE,AGE_TYPE,REPORT_TIME,PRESCRIPTION,ID_PROOF,QA1,QA2,QA3,QA4,QA5,QA6,HOME_COLLECT) as clinic_testdata'), function ($join) {
                        $join->on('sym_organ_test.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                    })
                    // ->join('master_testdata', 'sym_organ_test.TEST_ID', '=', 'master_testdata.TEST_ID')
                    ->select(
                        'dashboard.DASH_ID',
                        'dashboard.DASH_NAME',
                        'dashboard.PHOTO_URL',
                        'clinic_testdata.PHARMA_ID',
                        'clinic_testdata.TEST_ID',
                        'clinic_testdata.TEST_SL',
                        'clinic_testdata.TEST_NAME',
                        'clinic_testdata.TEST_CODE',
                        'clinic_testdata.TEST_SAMPLE',
                        'clinic_testdata.TEST_CATG',
                        'clinic_testdata.DEPARTMENT',
                        'clinic_testdata.TEST_DESC',
                        'clinic_testdata.KNOWN_AS',
                        'clinic_testdata.FASTING',
                        'clinic_testdata.GENDER_TYPE',
                        'clinic_testdata.AGE_TYPE',
                        'clinic_testdata.REPORT_TIME',
                        'clinic_testdata.PRESCRIPTION',
                        'clinic_testdata.ID_PROOF',
                        'clinic_testdata.QA1',
                        'clinic_testdata.QA2',
                        'clinic_testdata.QA3',
                        'clinic_testdata.QA4',
                        'clinic_testdata.QA5',
                        'clinic_testdata.QA6',
                        'clinic_testdata.HOME_COLLECT',
                        'clinic_testdata.MIN_COST'
                    )
                    ->where(['dashboard.DASH_SECTION_ID' => 'S', 'STATUS' => 'Active', 'clinic_testdata.PHARMA_ID' => $pharma_id])
                    ->orderby('dashboard.POSITION')
                    ->get();



                // return $data1;

                $S_DTL = [];
                $collection = collect($data1);
                $distinctValues = $collection->pluck('DASH_ID')->unique();
                foreach ($distinctValues as $row) {
                    $fltr_arr = $data1->filter(function ($item) use ($row) {
                        return $item->DASH_ID === $row;
                    });

                    $T_DTL = $fltr_arr->map(function ($item) {
                        return [
                            "TEST_ID" => $item->TEST_ID,
                            "TEST_SL" => $item->TEST_SL,
                            "TEST_NAME" => $item->TEST_NAME,
                            "TEST_CODE" => $item->TEST_CODE,
                            "TEST_SAMPLE" => $item->TEST_SAMPLE,
                            "TEST_CATG" => $item->TEST_CATG,
                            "DEPARTMENT" => $item->DEPARTMENT,
                            // "TEST_UNIT" => $item->TEST_UNIT,
                            // "NORMAL_RANGE" => $item->NORMAL_RANGE,
                            "TEST_DESC" => $item->TEST_DESC,
                            "MIN_COST" => $item->MIN_COST,
                            "KNOWN_AS" => $item->KNOWN_AS,
                            "FASTING" => $item->FASTING,
                            "GENDER_TYPE" => $item->GENDER_TYPE,
                            "AGE_TYPE" => $item->AGE_TYPE,
                            "REPORT_TIME" => $item->REPORT_TIME,
                            "PRESCRIPTION" => $item->PRESCRIPTION,
                            "ID_PROOF" => $item->ID_PROOF,
                            "QA1" => $item->QA1,
                            "QA2" => $item->QA2,
                            "QA3" => $item->QA3,
                            "QA4" => $item->QA4,
                            "QA5" => $item->QA5,
                            "QA6" => $item->QA6,
                            "TestDetails" => [
                                [
                                    "TEST_ID" => $item->TEST_ID,
                                    "TEST_NAME" => $item->TEST_NAME,
                                ]
                            ]
                        ];
                    })->values()->all();
                    $S_DTL[] = [
                        "DASH_ID" => $row,
                        "DASH_NAME" => $fltr_arr->first()->DASH_NAME,
                        "PHOTO_URL" => $fltr_arr->first()->PHOTO_URL,
                        "TOT_TEST" => count($T_DTL),
                        // "TOT_COST" => array_sum(array_column($T_DTL, 'COST')),
                        // "DETAILS" => $T_DTL
                    ];
                }

                $S["Symptomatic_Test"] = array_values($S_DTL);

                //SECTION-T #### ORGAN TEST
                $data1 = DB::table('dashboard')
                    ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard.DASH_ID')
                    // ->join('master_testdata', 'sym_organ_test.TEST_ID', '=', 'master_testdata.TEST_ID')
                    ->join('clinic_testdata', 'clinic_testdata.TEST_ID', '=', 'sym_organ_test.TEST_ID')
                    ->select(
                        'dashboard.DASH_ID',
                        'dashboard.DASH_NAME',
                        'dashboard.PHOTO_URL',

                        'clinic_testdata.*',
                    )
                    ->where(['dashboard.DASH_SECTION_ID' => 'T', 'dashboard.STATUS' => 'Active', 'clinic_testdata.PHARMA_ID' => $pharma_id])
                    ->orderby('dashboard.POSITION')
                    ->get();

                // RETURN $data1;

                $T_DTL = [];
                $collection = collect($data1);
                $distinctValues = $collection->pluck('DASH_ID')->unique();
                foreach ($distinctValues as $row) {
                    $fltr_arr = $data1->filter(function ($item) use ($row) {
                        return $item->DASH_ID === $row;
                    });

                    $TDTL = $fltr_arr->map(function ($item) {
                        return [
                            "TEST_ID" => $item->TEST_ID,
                            "TEST_SL" => $item->TEST_SL,
                            "TEST_NAME" => $item->TEST_NAME,
                            "TEST_CODE" => $item->TEST_CODE,
                            "TEST_SAMPLE" => $item->TEST_SAMPLE,
                            "TEST_CATG" => $item->TEST_CATG,
                            "DEPARTMENT" => $item->DEPARTMENT,
                            "PHARMA_ID" => $item->PHARMA_ID,
                            // "TEST_UNIT" => $item->TEST_UNIT,
                            // "NORMAL_RANGE" => $item->NORMAL_RANGE,
                            "TEST_DESC" => $item->TEST_DESC,
                            "KNOWN_AS" => $item->KNOWN_AS,
                            "FASTING" => $item->FASTING,
                            "GENDER_TYPE" => $item->GENDER_TYPE,
                            "AGE_TYPE" => $item->AGE_TYPE,
                            "REPORT_TIME" => $item->REPORT_TIME,
                            "PRESCRIPTION" => $item->PRESCRIPTION,
                            "ID_PROOF" => $item->ID_PROOF,
                            "QA1" => $item->QA1,
                            "QA2" => $item->QA2,
                            "QA3" => $item->QA3,
                            "QA4" => $item->QA4,
                            "QA5" => $item->QA5,
                            "QA6" => $item->QA6,
                            "TestDetails" => [
                                [
                                    "TEST_ID" => $item->TEST_ID,
                                    "TEST_NAME" => $item->TEST_NAME,
                                ]
                            ]
                        ];
                    })->values()->all();
                    $T_DTL[] = [
                        "DASH_ID" => $row,
                        "DASH_NAME" => $fltr_arr->first()->DASH_NAME,
                        "PHOTO_URL" => $fltr_arr->first()->PHOTO_URL,
                        "TOT_TEST" => count($TDTL),
                        // "TOT_COST" => array_sum(array_column($T_DTL, 'COST')),
                        "DETAILS" => $TDTL
                    ];
                }
                $T["Organ_Test"] = array_values($T_DTL);



                //SECTION-C #### PROFILE
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'C';
                });
                $C_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO_URL,
                    ];
                })->values()->all();
                $C["Profile_Test"] = array_values($C_DTL);

                //SECTION-G #### PACKAGE
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'G';
                });
                $G_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO_URL,
                    ];
                })->values()->all();

                $G["Popular_Health_Package"] = array_values($G_DTL);


                //SECTION-H #### TOP WELLNESS PACKAGE
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'H';
                });
                $H_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO_URL,
                        "VIEW1_URL" => $item->VIEW1_URL,
                    ];
                })->values()->all();

                $H["Top_Wellness_Package"] = array_values($H_DTL);

                // //SECTION-B #### FAMILY CARE PACKAGES
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'B';
                });
                $B_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO_URL,
                    ];
                })->values()->all();

                $B["Family_Care_Package"] = array_values($B_DTL);




                // SECTION-#### SINGLE TEST
                $TST_DTL = DB::table('master_testdata')
                    ->join(DB::raw("(SELECT DISTINCT clinic_testdata.TEST_ID,clinic_testdata.PHARMA_ID,clinic_testdata.DISCOUNT,clinic_testdata.HOME_COLLECT, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata
                    WHERE clinic_testdata.PHARMA_ID = {$pharma_id}
                    GROUP BY TEST_ID,clinic_testdata.PHARMA_ID,clinic_testdata.DISCOUNT,HOME_COLLECT) as clinic_testdata"), function ($join) {
                        $join->on('master_testdata.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                    })
                    ->select('master_testdata.*', 'clinic_testdata.PHARMA_ID', 'clinic_testdata.MIN_COST', 'clinic_testdata.HOME_COLLECT AS HOME_COLLECT', 'clinic_testdata.DISCOUNT')
                    ->take(100)->get()->toArray();

                // $TST["Popular_Single_Test"] = array_values($TST_DTL);
                $modifiedResponse = [];
                foreach ($TST_DTL as $test) {
                    $modifiedTest = [
                        "TEST_ID" => $test->TEST_ID,
                        "TEST_NAME" => $test->TEST_NAME,
                        "TEST_CODE" => $test->TEST_CODE,
                        "TEST_SAMPLE" => $test->TEST_SAMPLE,
                        "TEST_CATG" => $test->TEST_CATG,
                        "COST" => $test->COST,
                        "DISCOUNT" => $test->DISCOUNT,
                        "HOME_COLLECT" => $test->HOME_COLLECT,
                        "ORGAN_ID" => $test->ORGAN_ID,
                        "ORGAN_NAME" => $test->ORGAN_NAME,
                        "ORGAN_URL" => $test->ORGAN_URL,
                        "DEPARTMENT" => $test->DEPARTMENT,
                        "TEST_DESC" => $test->TEST_DESC,
                        "KNOWN_AS" => $test->KNOWN_AS,
                        "FASTING" => $test->FASTING,
                        "GENDER_TYPE" => $test->GENDER_TYPE,
                        "AGE_TYPE" => $test->AGE_TYPE,
                        "REPORT_TIME" => $test->REPORT_TIME,
                        "PRESCRIPTION" => $test->PRESCRIPTION,
                        "ID_PROOF" => $test->ID_PROOF,
                        "QA1" => $test->QA1,
                        "QA2" => $test->QA2,
                        "QA3" => $test->QA3,
                        "QA4" => $test->QA4,
                        "QA5" => $test->QA5,
                        "QA6" => $test->QA6,
                        "TOT_TEST" => 1,
                        "TestDetails" => [
                            [
                                "TEST_ID" => $test->TEST_ID,
                                "TEST_NAME" => $test->TEST_NAME,
                            ]
                        ]
                    ];
                    $modifiedResponse[] = $modifiedTest;
                }

                $TST["Popular_Single_Test"] = $modifiedResponse;




                // $data =  $A +  $F + $F1 + $DASH_BNR + $S + $T + $DIAG + $C + $G + $H + $B + $U + $V + $W + $TST + $AB;
                $data = $F1 + $S + $T + $C + $G + $H + $B + $TST;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }
    function alstst_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
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
                        "PHARMA_ID" => $row->PHARMA_ID,
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

    function scanorgan_dummy(Request $req)
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

    function clinicitemorgantest_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['DASH_NAME']) && isset($input['ORGAN_ID'])) {
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
                        "TOT_TEST" => 1,
                        "COST" => $row->COST,
                        "QA1" => $row->QA1,
                        "QA2" => $row->QA2,
                        "QA3" => $row->QA3,
                        "QA4" => $row->QA4,
                        "QA5" => $row->QA5,
                        "QA6" => $row->QA6,
                        "TestDetails" => [
                            [
                                "TEST_ID" => $row->TEST_ID,
                                "TEST_NAME" => $row->TEST_NAME,
                            ]
                        ]
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

    function scanorgantest_dummy(Request $req)
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
                    $T_dtl["DETAILS"] = array(
                        [
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
                        ]
                    );

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
                $cl["Diagnostic"] = array_values($data);

                $bnr["Catg_Banner"] = DB::table('master_testdata')
                    ->select('SUB_DEPT_ID AS BANNER_ID', 'SUB_DEPARTMENT AS BANNER_NAME', 'TEST_DESC AS DESCRIPTION', 'BANNER_URL')
                    ->where(['DASH_ID' => 'TS', 'SUB_DEPT_ID' => $row->SUB_DEPT_ID])->take(1)->get();

                $bnr1["Banner"] = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', 'TS')->get();

                $data1 = $cl + $bnr + $bnr1;
                $response = ['Success' => true, 'data' => $data1, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function testsearch_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {

                $pharma_id = $input['PHARMA_ID'];
                $response = array();
                $data = array();

                // Fetch data from clinic_testdata
                $clinicTestData = DB::table('clinic_testdata')
                    ->where('PHARMA_ID', $pharma_id)
                    ->get();

                foreach ($clinicTestData as $row1) {
                    $data[] = [
                        "ID" => $row1->TEST_ID,
                        "ITEM_NAME" => $row1->TEST_NAME,
                        "FIELD_TYPE" => $row1->DEPARTMENT,
                        "TEST_TYPE" => $row1->DEPARTMENT,
                        "PHARMA_ID" => $pharma_id,
                        "COST" => $row1->COST,
                        "DISCOUNT" => $row1->DISCOUNT,
                        "DEPT_ID" => $row1->DEPT_ID,
                        "SUB_DEPT_ID" => $row1->SUB_DEPT_ID,
                        "DETAILS" => [
                            "TEST_ID" => $row1->TEST_ID,
                            "TEST_NAME" => $row1->TEST_NAME,
                            "TEST_CODE" => $row1->TEST_CODE,
                            "TEST_SAMPLE" => $row1->TEST_SAMPLE,
                            "TEST_CATG" => $row1->TEST_CATG,
                            "COST" => $row1->COST,
                            "DISCOUNT" => $row1->DISCOUNT,
                            "HOME_COLLECT" => $row1->HOME_COLLECT,
                            "ORGAN_ID" => $row1->ORGAN_ID,
                            "ORGAN_NAME" => $row1->ORGAN_NAME,
                            "ORGAN_URL" => $row1->ORGAN_URL,
                            "DEPARTMENT" => $row1->DEPARTMENT,
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
                            'TOT_TEST' => 1,
                            "TestDetails" => [
                                [
                                    "TEST_ID" => $row1->TEST_ID,
                                    "TEST_NAME" => $row1->TEST_NAME,
                                ]
                            ]
                        ]
                    ];
                }

                // Fetch data from dashboard and package with eager loading
                $dashboardPackages = DB::table('dashboard_item')
                    ->join('package', 'dashboard_item.DASH_ID', '=', 'package.LAB_PKG_ID')
                    ->leftJoin('package_details as pd', 'package.PKG_ID', '=', 'pd.PKG_ID')
                    ->where('dashboard_item.DASH_STATUS', 'Active')
                    ->whereIn('dashboard_item.DASH_SECTION_ID', ['B', 'C', 'D', 'G', 'H'])
                    ->where('package.PHARMA_ID', $pharma_id)
                    ->select(
                        'dashboard_item.DASH_ID',
                        'dashboard_item.DASH_NAME',
                        'package.PKG_TYPE AS DASH_TYPE',
                        'dashboard_item.DASH_SECTION_ID',
                        'package.DASH_SECTION_NAME',
                        'dashboard_item.DASH_POSITION AS POSITION',
                        'dashboard_item.DASH_STATUS AS STATUS',
                        'package.LAB_PKG_ID',
                        'package.LAB_PKG_NAME',
                        'dashboard_item.DASH_DESC AS DASH_DESCRIPTION',
                        'package.FASTING',
                        'package.GENDER_TYPE',
                        'package.AGE_TYPE',
                        'package.HOME_COLLECT',
                        'package.KNOWN_AS',
                        'package.PKG_COST',
                        'package.PKG_DIS',
                        'dashboard_item.DI_IMG1 AS PHOTO_URL',
                        'package.PRESCRIPTION',
                        'package.QA1',
                        'package.QA2',
                        'package.QA3',
                        'package.QA4',
                        'package.QA5',
                        'package.QA6',
                        'package.REPORT_TIME',
                        'pd.TEST_ID as PD_TEST_ID',
                        'pd.TEST_NAME as PD_TEST_NAME',
                        'pd.TEST_TYPE as PD_TEST_TYPE',
                        'pd.COST as PD_COST'
                    )
                    ->get()
                    ->groupBy('DASH_ID');

                foreach ($dashboardPackages as $dashboardId => $packageGroup) {
                    $row = $packageGroup->first();

                    $details = [
                        "AGE_TYPE" => $row->AGE_TYPE,
                        "TEST_DESC" => $row->DASH_DESCRIPTION,
                        "FASTING" => $row->FASTING,
                        "GENDER_TYPE" => $row->GENDER_TYPE,
                        "DASH_ID" => $row->DASH_ID,
                        "TEST_NAME" => $row->DASH_NAME,
                        "DASH_TYPE" => $row->DASH_TYPE,
                        "COST" => $row->PKG_COST,
                        "DISCOUNT" => $row->PKG_DIS,
                        "PHOTO_URL" => $row->PHOTO_URL,
                        "HOME_COLLECT" => $row->HOME_COLLECT,
                        "PRESCRIPTION" => $row->PRESCRIPTION,
                        "QA1" => $row->QA1,
                        "QA2" => $row->QA2,
                        "QA3" => $row->QA3,
                        "QA4" => $row->QA4,
                        "QA5" => $row->QA5,
                        "QA6" => $row->QA6,
                        "REPORT_TIME" => $row->REPORT_TIME,
                        "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                        "SECTION_SL" => $row->POSITION,
                        "STATUS" => $row->STATUS,
                        'TOT_TEST' => 0,
                        "TestDetails" => []
                    ];

                    foreach ($packageGroup as $packageDetail) {
                        if ($packageDetail->PD_TEST_ID) {
                            $testDetail = [
                                "TEST_ID" => $packageDetail->PD_TEST_ID,
                                "TEST_NAME" => $packageDetail->PD_TEST_NAME,
                                "TEST_TYPE" => $packageDetail->PD_TEST_TYPE,
                                "COST" => $packageDetail->PD_COST
                            ];

                            // If the test type is 'Profile', fetch nested details
                            if ($packageDetail->PD_TEST_TYPE == 'Profile') {
                                $nestedDetails = DB::table('package_details')
                                    ->where(['PKG_ID' => $packageDetail->PD_TEST_ID, 'PKG_STATUS' => 'Active'])
                                    ->select(
                                        'TEST_ID',
                                        'TEST_NAME',
                                        'TEST_TYPE',
                                        'COST'
                                    )
                                    // ->orderby('TEST_TYPE','desc')
                                    ->get();


                                foreach ($nestedDetails as $nestedDetail) {
                                    $testDetail['NestedDetails'][] = [
                                        "TEST_ID" => $nestedDetail->TEST_ID,
                                        "TEST_NAME" => $nestedDetail->TEST_NAME,
                                        "TEST_TYPE" => $nestedDetail->TEST_TYPE,
                                        "COST" => $nestedDetail->COST
                                    ];
                                }
                            }

                            $details['TestDetails'][] = $testDetail;
                        }
                    }

                    // Sort TestDetails array in descending order based on TEST_TYPE
                    usort($details['TestDetails'], function ($a, $b) {
                        return strcmp($b['TEST_TYPE'], $a['TEST_TYPE']);
                    });


                    // Calculate TOT_TEST
                    $totTestCount = 0;
                    foreach ($details['TestDetails'] as $test) {
                        if ($test['TEST_TYPE'] != 'Profile') {
                            $totTestCount++;
                        }
                        if (isset($test['NestedDetails'])) {
                            foreach ($test['NestedDetails'] as $nestedTest) {
                                if ($nestedTest['TEST_TYPE'] != 'Profile') {
                                    $totTestCount++;
                                }
                            }
                        }
                    }

                    $details['TOT_TEST'] = $totTestCount;

                    $pkg = [
                        "ID" => $row->DASH_ID,
                        "ITEM_NAME" => $row->DASH_NAME,
                        "FIELD_TYPE" => $row->DASH_TYPE,
                        "PHARMA_ID" => $pharma_id,
                        "COST" => $row->PKG_COST,
                        "DISCOUNT" => $row->PKG_DIS,
                        "TEST_TYPE" => $row->DASH_TYPE,

                        "DETAILS" => $details
                    ];
                    $data[] = $pkg;
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

    function pathologysearch_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {

                $pharma_id = $input['PHARMA_ID'];
                $response = array();
                $data = array();

                // Fetch data from clinic_testdata
                $clinicTestData = DB::table('clinic_testdata')
                    ->where('PHARMA_ID', $pharma_id)
                    ->where(function ($query) {
                        $query->where('TEST_CATG', 'PATHOLOGY')
                            ->orWhere('DEPARTMENT', 'PATHOLOGY');
                    })
                    ->get();

                foreach ($clinicTestData as $row1) {
                    $data[] = [
                        "ID" => $row1->TEST_ID,
                        "ITEM_NAME" => $row1->TEST_NAME,
                        "FIELD_TYPE" => $row1->DEPARTMENT,
                        "TEST_TYPE" => $row1->DEPARTMENT,
                        "PHARMA_ID" => $pharma_id,
                        "COST" => $row1->COST,
                        "DISCOUNT" => $row1->DISCOUNT,
                        "DETAILS" => [
                            "TEST_ID" => $row1->TEST_ID,
                            "TEST_NAME" => $row1->TEST_NAME,
                            "TEST_CODE" => $row1->TEST_CODE,
                            "TEST_SAMPLE" => $row1->TEST_SAMPLE,
                            "TEST_CATG" => $row1->TEST_CATG,
                            "COST" => $row1->COST,
                            "DISCOUNT" => $row1->DISCOUNT,
                            "HOME_COLLECT" => $row1->HOME_COLLECT,
                            "ORGAN_ID" => $row1->ORGAN_ID,
                            "ORGAN_NAME" => $row1->ORGAN_NAME,
                            "ORGAN_URL" => $row1->ORGAN_URL,
                            "DEPARTMENT" => $row1->DEPARTMENT,
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
                            'TOT_TEST' => 1,
                            "TestDetails" => [
                                [
                                    "TEST_ID" => $row1->TEST_ID,
                                    "TEST_NAME" => $row1->TEST_NAME,
                                ]
                            ]
                        ]
                    ];
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
    function pathology_samplesrch_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID']) && isset($input['SAMPLE_NAME'])) {

                $pharma_id = $input['PHARMA_ID'];
                $sample_name = $input['SAMPLE_NAME'];
                $response = array();
                $data = array();

                // Fetch data from clinic_testdata
                $clinicTestData = DB::table('clinic_testdata')
                    ->where(['PHARMA_ID' => $pharma_id, 'TEST_SAMPLE' => $sample_name])
                    ->where(function ($query) {
                        $query->where(['TEST_CATG' => 'PATHOLOGY'])
                            ->orWhere('DEPARTMENT', 'PATHOLOGY');
                    })
                    ->get();

                foreach ($clinicTestData as $row1) {
                    $data[] = [
                        "ID" => $row1->TEST_ID,
                        "ITEM_NAME" => $row1->TEST_NAME,
                        "FIELD_TYPE" => $row1->DEPARTMENT,
                        "TEST_TYPE" => $row1->DEPARTMENT,
                        "PHARMA_ID" => $pharma_id,
                        "COST" => $row1->COST,
                        "DISCOUNT" => $row1->DISCOUNT,
                        "DETAILS" => [
                            "TEST_ID" => $row1->TEST_ID,
                            "TEST_NAME" => $row1->TEST_NAME,
                            "TEST_CODE" => $row1->TEST_CODE,
                            "TEST_SAMPLE" => $row1->TEST_SAMPLE,
                            "TEST_CATG" => $row1->TEST_CATG,
                            "COST" => $row1->COST,
                            "DISCOUNT" => $row1->DISCOUNT,
                            "HOME_COLLECT" => $row1->HOME_COLLECT,
                            "ORGAN_ID" => $row1->ORGAN_ID,
                            "ORGAN_NAME" => $row1->ORGAN_NAME,
                            "ORGAN_URL" => $row1->ORGAN_URL,
                            "DEPARTMENT" => $row1->DEPARTMENT,
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
                            'TOT_TEST' => 1,
                            "TestDetails" => [
                                [
                                    "TEST_ID" => $row1->TEST_ID,
                                    "TEST_NAME" => $row1->TEST_NAME,
                                ]
                            ]
                        ]
                    ];
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
    function radiologyscan_search_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID']) && isset($input['DASH_NAME'])) {

                $pharma_id = $input['PHARMA_ID'];
                $d_name = $input['DASH_NAME'];
                $response = array();
                $data = array();

                // Fetch data from clinic_testdata
                $clinicTestData = DB::table('clinic_testdata')
                    ->where('PHARMA_ID', $pharma_id)
                    ->where('DEPARTMENT', 'RADIOLOGY')
                    ->where(function ($query) use ($d_name) {
                        $query->where('TEST_CATG', $d_name)
                            ->orWhere('SUB_DEPARTMENT', $d_name);
                    })
                    ->get();

                foreach ($clinicTestData as $row1) {
                    $data[] = [
                        "ID" => $row1->TEST_ID,
                        "ITEM_NAME" => $row1->TEST_NAME,
                        "FIELD_TYPE" => $row1->DEPARTMENT,
                        "TEST_TYPE" => $row1->DEPARTMENT,
                        "PHARMA_ID" => $pharma_id,
                        "COST" => $row1->COST,
                        "DISCOUNT" => $row1->DISCOUNT,
                        "DETAILS" => [
                            "TEST_ID" => $row1->TEST_ID,
                            "TEST_NAME" => $row1->TEST_NAME,
                            "TEST_CODE" => $row1->TEST_CODE,
                            "TEST_SAMPLE" => $row1->TEST_SAMPLE,
                            "TEST_CATG" => $row1->TEST_CATG,
                            "COST" => $row1->COST,
                            "DISCOUNT" => $row1->DISCOUNT,
                            "HOME_COLLECT" => $row1->HOME_COLLECT,
                            "ORGAN_ID" => $row1->ORGAN_ID,
                            "ORGAN_NAME" => $row1->ORGAN_NAME,
                            "ORGAN_URL" => $row1->ORGAN_URL,
                            "DEPARTMENT" => $row1->DEPARTMENT,
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
                            'TOT_TEST' => 1,
                            "TestDetails" => [
                                [
                                    "TEST_ID" => $row1->TEST_ID,
                                    "TEST_NAME" => $row1->TEST_NAME,
                                ]
                            ]
                        ]
                    ];
                }

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }
        return $response;
    }


    function sample_pathology_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID']) && isset($input['SAMPLE_NAME'])) {

                $pharma_id = $input['PHARMA_ID'];
                $sample_name = $input['SAMPLE_NAME'];

                $response = array();
                $data = array();


                $TST_DTL = DB::table('master_testdata')
                    ->join(DB::raw("(SELECT DISTINCT clinic_testdata.TEST_ID,clinic_testdata.PHARMA_ID,clinic_testdata.DISCOUNT,clinic_testdata.HOME_COLLECT FROM clinic_testdata
    WHERE clinic_testdata.PHARMA_ID = {$pharma_id}
    GROUP BY TEST_ID,clinic_testdata.PHARMA_ID,clinic_testdata.DISCOUNT,HOME_COLLECT) as clinic_testdata"), function ($join) {
                        $join->on('master_testdata.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                    })
                    ->where(function ($query) {
                        $query->where('DEPARTMENT', 'PATHOLOGY')
                            ->orWhere('TEST_CATG', 'PATHOLOGY');
                    })
                    ->where('TEST_SAMPLE', 'LIKE', '%' . $sample_name . '%')
                    ->select('master_testdata.*', 'clinic_testdata.PHARMA_ID', 'clinic_testdata.HOME_COLLECT AS HOME_COLLECT', 'clinic_testdata.DISCOUNT')
                    ->orderBy('TEST_ID', 'asc')
                    ->get()->toArray();

                foreach ($TST_DTL as $test) {
                    $modifiedTest = [
                        "TEST_ID" => $test->TEST_ID,
                        "TEST_NAME" => $test->TEST_NAME,
                        "TEST_CODE" => $test->TEST_CODE,
                        "TEST_SAMPLE" => $test->TEST_SAMPLE,
                        "TEST_CATG" => $test->TEST_CATG,
                        "COST" => $test->COST,
                        "DISCOUNT" => $test->DISCOUNT,
                        "HOME_COLLECT" => $test->HOME_COLLECT,
                        "ORGAN_ID" => $test->ORGAN_ID,
                        "ORGAN_NAME" => $test->ORGAN_NAME,
                        "ORGAN_URL" => $test->ORGAN_URL,
                        "DEPARTMENT" => $test->DEPARTMENT,
                        "TEST_DESC" => $test->TEST_DESC,
                        "KNOWN_AS" => $test->KNOWN_AS,
                        "FASTING" => $test->FASTING,
                        "GENDER_TYPE" => $test->GENDER_TYPE,
                        "AGE_TYPE" => $test->AGE_TYPE,
                        "REPORT_TIME" => $test->REPORT_TIME,
                        "PRESCRIPTION" => $test->PRESCRIPTION,
                        "ID_PROOF" => $test->ID_PROOF,
                        "QA1" => $test->QA1,
                        "QA2" => $test->QA2,
                        "QA3" => $test->QA3,
                        "QA4" => $test->QA4,
                        "QA5" => $test->QA5,
                        "QA6" => $test->QA6,
                        "TOT_TEST" => 1,
                        "TestDetails" => [
                            [
                                "TEST_ID" => $test->TEST_ID,
                                "TEST_NAME" => $test->TEST_NAME,
                            ]
                        ]
                    ];
                    $data[] = $modifiedTest;
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


    function labsrch_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $data1 = DB::table('master_testdata')->get();
                foreach ($data1 as $row1) {
                    $tstdtl = [];
                    $tstdtl['DETAILS'] = [
                        "TEST_ID" => $row1->TEST_ID,
                        "TEST_NAME" => $row1->TEST_NAME,
                        "TEST_CODE" => $row1->TEST_CODE,
                        "TEST_SAMPLE" => $row1->TEST_SAMPLE,
                        "TEST_CATG" => $row1->TEST_CATG,
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
                * SIN(RADIANS('$latt'))))),2) as KM"), )
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






    function vuclinicstst_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
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
                    $T_dtl['DETAILS'] = array(
                        [
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
                        ]
                    );

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

    function labpkgdtl_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LAB_PKG_ID'])) {

                // $latt = $input['LATITUDE'];
                // $lont = $input['LONGITUDE'];
                $L_PKG_ID = $input['LAB_PKG_ID'];

                $data1 = DB::table('package')
                    ->join('package_details', 'package.PKG_ID', '=', 'package_details.PKG_ID')
                    // ->join('pharmacy', 'package.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    ->select(
                        'package.*',

                        'package_details.*',
                        //     'pharmacy.*',
                        //     DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                        // * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                        // * SIN(RADIANS('$latt'))))),2) as KM"),
                    )
                    ->where(['package.LAB_PKG_ID' => $L_PKG_ID])
                    ->orderby('package_details.TEST_ID')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->PKG_ID])) {
                        $groupedData[$row->PKG_ID] = [
                            // "PHARMA_ID" => $row->PHARMA_ID,
                            // "PHARMA_NAME" => $row->ITEM_NAME,
                            // "ADDRESS" => $row->ADDRESS,
                            // "CITY" => $row->CITY,
                            // "DIST" => $row->DIST,
                            // "CLINIC_MOBILE" => $row->CLINIC_MOBILE,
                            // "PIN" => $row->PIN,
                            // "EMAIL" => $row->EMAIL,
                            // "STATE" => $row->STATE,
                            // "LATITUDE" => $row->LATITUDE,
                            // "LONGITUDE" => $row->LONGITUDE,
                            // "PHOTO_URL" => $row->PHOTO_URL,
                            // "LOGO_URL" => $row->LOGO_URL,
                            // "KM" => $row->KM,
                            // "PH_RATING" => $row->PH_RATING,
                            // "HO_ID" => $row->HO_ID,
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
                // $bnr["Catg_Banner"] = DB::table('dashboard')
                //     ->select('DASH_ID AS BANNER_ID', 'DASH_NAME AS BANNER_NAME', 'DASH_DESCRIPTION AS DESCRIPTION', 'BANNER_URL', 'COLOR_CODE')
                //     ->where(['DASH_ID' => $L_PKG_ID])->get();
                // // $fltr_fxd_bnr = $fxd_banner->filter(function ($item) use ($sid) {
                // //     return $item->BANNER_TYPE === $sid;
                // // });
                // // $bnr["Catg_Banner"] = $fltr_fxd_bnr->take(1)->values()->all();
                // // } 
                // $bnr1["Banner"] = DB::table('promo_banner')
                //     ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                //     ->where(['DASH_SECTION_ID' => $row->DASH_SECTION_ID, 'PHARMA_ID' => $data1->first()->PHARMA_ID])->get();
                $pkg["Package"] = \array_values($groupedData);
                // $data = $pkg + $bnr1 + $bnr;
                $data = $pkg;
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

    function labpackagesdtl_dummy(Request $req)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = $req->json()->all();
                if (isset($input['DASH_ID']) && isset($input['PHARMA_ID']) && isset($input['DASH_SECTION_ID'])) {

                    $L_PKG_ID = $input['DASH_ID'];
                    $pharma_id = $input['PHARMA_ID'];
                    $dash_section_id = $input['DASH_SECTION_ID'];
                    $valid_sections = ['B', 'D', 'G', 'H', 'S', 'T'];

                    if (in_array($dash_section_id, $valid_sections)) {
                        // Fetch packages based on LAB_PKG_ID, PHARMA_ID, and DASH_SECTION_ID
                        $data1 = DB::table('package')
                            ->select('*')
                            ->where([
                                'LAB_PKG_ID' => $L_PKG_ID,
                                'PHARMA_ID' => $pharma_id,
                                'DASH_SECTION_ID' => $dash_section_id
                            ])
                            ->orderBy('PKG_ID')
                            ->get();

                        $groupedData = [];
                        foreach ($data1 as $row) {
                            // Fetch package details based on PKG_ID
                            $packageDetails = DB::table('package_details')
                                ->select('TEST_ID', 'TEST_NAME', 'TEST_TYPE', 'COST')
                                ->where('PKG_ID', $row->PKG_ID)
                                ->get();

                            // Format package details
                            $details = [];
                            $totalTests = 0;
                            foreach ($packageDetails as $detail) {
                                if ($detail->TEST_TYPE == 'Profile') {
                                    // Fetch profile details if TEST_TYPE is 'Profile'
                                    $profileDetails = DB::table('package_details')
                                        ->select('TEST_ID', 'TEST_NAME', 'TEST_TYPE', 'COST')
                                        ->where(['PKG_ID' => $detail->TEST_ID, 'PKG_STATUS' => 'Active'])
                                        ->get();

                                    $profileDetailsArray = [];
                                    foreach ($profileDetails as $profileDetail) {
                                        $profileDetailsArray[] = [
                                            'TEST_ID' => $profileDetail->TEST_ID,
                                            'TEST_NAME' => $profileDetail->TEST_NAME,
                                            'TEST_TYPE' => $profileDetail->TEST_TYPE,
                                            'COST' => $profileDetail->COST
                                        ];
                                        if ($profileDetail->TEST_TYPE != 'Profile') {
                                            $totalTests++;
                                        }
                                    }

                                    $details[] = [
                                        'TEST_ID' => $detail->TEST_ID,
                                        'TEST_NAME' => $detail->TEST_NAME,
                                        'TEST_TYPE' => $detail->TEST_TYPE,
                                        'COST' => $detail->COST,
                                        'NestedDetails' => $profileDetailsArray
                                    ];
                                } else {
                                    $details[] = [
                                        'TEST_ID' => $detail->TEST_ID,
                                        'TEST_NAME' => $detail->TEST_NAME,
                                        'TEST_TYPE' => $detail->TEST_TYPE,
                                        'COST' => $detail->COST
                                    ];
                                    $totalTests++;
                                }
                            }
                            // Sort details array in descending order based on TEST_TYPE
                            usort($details, function ($a, $b) {
                                return strcmp($b['TEST_TYPE'], $a['TEST_TYPE']);
                            });
                            // Extract necessary fields from $row and create a new array
                            $packageData = [
                                "LAB_PKG_ID" => $row->LAB_PKG_ID,
                                "PKG_ID" => $row->PKG_ID,
                                "LAB_PKG_NAME" => $row->LAB_PKG_NAME,
                                "DASH_NAME" => $row->PKG_NAME,
                                "DASH_TYPE" => $row->PKG_TYPE,
                                "TEST_DESC" => $row->PKG_DESC,
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
                                "COST" => $row->PKG_COST,
                                "DISCOUNT" => $row->PKG_DIS,
                                "HOME_COLLECT" => $row->HOME_COLLECT,
                                "FREE_AREA" => $row->FREE_AREA,
                                "SERV_CONDITION" => $row->SERV_CONDITION,
                                "TOT_TEST" => $totalTests,
                                "TestDetails" => $details
                            ];

                            // Push the new array into $groupedData
                            $groupedData[] = $packageData;
                        }

                        // Prepare the response
                        $response = ['Success' => true, 'data' => $groupedData, 'code' => 200];
                    } else {
                        // Handle invalid DASH_SECTION_ID
                        $response = ['Success' => false, 'Message' => 'Invalid DASH_SECTION_ID', 'code' => 422];
                    }
                } else {
                    // Handle invalid input parameters
                    $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
                }
            } else {
                // Handle unsupported HTTP method
                $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
            }
        } catch (\Exception $e) {
            // Handle exception
            $response = ['Success' => false, 'Message' => 'An error occurred: ' . $e->getMessage(), 'code' => 500];
        }

        return $response;
    }


    function labprofilesdtl_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['DASH_ID']) && isset($input['PHARMA_ID'])) {

                $L_PKG_ID = $input['DASH_ID'];
                $pharma_id = $input['PHARMA_ID'];

                // Fetch packages based on LAB_PKG_ID and PHARMA_ID
                $data1 = DB::table('package')
                    ->select('*')
                    ->where(['LAB_PKG_ID' => $L_PKG_ID, 'PHARMA_ID' => $pharma_id, 'DASH_SECTION_ID' => 'C'])
                    ->orderBy('PKG_ID')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    // Fetch package details based on PKG_ID
                    $packageDetails = DB::table('package_details')
                        ->select('TEST_ID', 'TEST_NAME', 'TEST_TYPE')
                        ->where('PKG_ID', $row->PKG_ID)
                        ->get();

                    // Format package details
                    $details = [];
                    $totalTests = 0;
                    foreach ($packageDetails as $detail) {
                        $details[] = [
                            'TEST_ID' => $detail->TEST_ID,
                            'TEST_NAME' => $detail->TEST_NAME,
                            'TEST_TYPE' => $detail->TEST_TYPE
                        ];
                        $totalTests++;
                    }

                    // Extract necessary fields from $row and create a new array
                    $packageData = [
                        "LAB_PKG_ID" => $row->LAB_PKG_ID,
                        "PKG_ID" => $row->PKG_ID,
                        "LAB_PKG_NAME" => $row->LAB_PKG_NAME,
                        "DASH_NAME" => $row->PKG_NAME,
                        "DASH_TYPE" => $row->PKG_TYPE,
                        "TEST_DESC" => $row->PKG_DESC,
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
                        "COST" => $row->PKG_COST,
                        "DISCOUNT" => $row->PKG_DIS,
                        "HOME_COLLECT" => $row->HOME_COLLECT,
                        "FREE_AREA" => $row->FREE_AREA,
                        "SERV_CONDITION" => $row->SERV_CONDITION,
                        "TOT_TEST" => $totalTests,
                        "TestDetails" => $details
                    ];

                    // Push the new array into $groupedData
                    $groupedData[] = $packageData;
                }

                // Prepare the response
                $response = ['Success' => true, 'data' => $groupedData, 'code' => 200];
            } else {
                // Handle invalid input parameters
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            // Handle unsupported HTTP method
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }
        return $response;
    }



    function all_labpkgdtl_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {
                $PID = $input['PHARMA_ID'];

                $data1 = DB::table('package')
                    ->join('package_details', 'package.PKG_ID', '=', 'package_details.PKG_ID')
                    ->select(
                        'package.*',
                        'package_details.*',
                    )
                    ->orderby('package_details.TEST_ID')
                    ->where(['package.PHARMA_ID' => $PID])
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
                            "PHARMA_ID" => $row->PHARMA_ID,
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
                // $bnr["Catg_Banner"] = DB::table('dashboard')
                //     ->select('DASH_ID AS BANNER_ID', 'DASH_NAME AS BANNER_NAME', 'DASH_DESCRIPTION AS DESCRIPTION', 'BANNER_URL', 'COLOR_CODE')
                //     ->where(['DASH_ID' => $L_PKG_ID])->get();
                // $fltr_fxd_bnr = $fxd_banner->filter(function ($item) use ($sid) {
                //     return $item->BANNER_TYPE === $sid;
                // });
                // $bnr["Catg_Banner"] = $fltr_fxd_bnr->take(1)->values()->all();
                // } 
                $bnr1["Banner"] = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where(['DASH_SECTION_ID' => $row->DASH_SECTION_ID, 'PHARMA_ID' => $PID])->get();
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


    // function booktest_dummy(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         //         Log::info('Request received:', $req->all());
    //         //         $arrayData = $req->input['BOOK_TEST'];
    //         //         Log::info('Array data:', $arrayData);
    //         //     }
    //         // }
    //         date_default_timezone_set('Asia/Kolkata');
    //         $cdt = Carbon::now()->format('ymdHis');

    //         $input = $req->json()->all();

    //         $BT = $input['BOOK_TEST'];
    //         $patientIds = [];
    //         $bookid = null;

    //         foreach ($BT as $row) {
    //             foreach ($row['DETAILS'] as $detail) {
    //                 $token = null;
    //                 $token = strtoupper(substr(md5($detail['PATIENT_ID'] . $cdt . $row['PHARMA_ID']), 0, 10));
    //                 if (isset($detail['PKG_TYPE'])) {
    //                     $fields = [
    //                         "BOOKING_ID" => $token,
    //                         "PKG_ID" => $detail['PKG_ID'],
    //                         "PKG_NAME" => $detail['PKG_NAME'],
    //                         "CATEGORY" => 'OTHERS',
    //                         "PHARMA_ID" => $row['PHARMA_ID'],
    //                         "BOOKING_DT" => $detail['BOOK_DATE'],
    //                         "BOOKING_TM" => $detail['BOOK_TIME'],
    //                         "SLOT_DT" => $detail['SLOT_DATE'],
    //                         "FROM" => $detail['FROM'],
    //                         "TO" => $detail['TO'],
    //                         "PATIENT_ID" => $detail['PATIENT_ID'],
    //                         "Patient_Name" => $detail['PATIENT_NAME'],
    //                         "ADVICED_BY" => $detail['ADVICED_BY'],
    //                         "PRESCRIPTION_URL" => $detail['PRESCRIPTION_URL'],
    //                         "HOME_COLLECT" => $detail['HOME_COLLECT'],
    //                         "TEST_COST" => $detail['PKG_COST']
    //                     ];
    //                 } else {
    //                     $fields = [
    //                         "BOOKING_ID" => $token,
    //                         "PKG_ID" => $detail['TEST_ID'],
    //                         "PKG_NAME" => $detail['TEST_NAME'],
    //                         "CATEGORY" => $detail['CATEGORY'],
    //                         "PHARMA_ID" => $row['PHARMA_ID'],
    //                         "BOOKING_DT" => $detail['BOOK_DATE'],
    //                         "BOOKING_TM" => $detail['BOOK_TIME'],
    //                         "SLOT_DT" => $detail['SLOT_DATE'],
    //                         "FROM" => $detail['FROM'],
    //                         "TO" => $detail['TO'],
    //                         "PATIENT_ID" => $detail['PATIENT_ID'],
    //                         "Patient_Name" => $detail['PATIENT_NAME'],
    //                         "ADVICED_BY" => $detail['ADVICED_BY'],
    //                         "PRESCRIPTION_URL" => $detail['PRESCRIPTION_URL'],
    //                         "HOME_COLLECT" => $detail['HOME_COLLECT'],
    //                         "TEST_COST" => $detail['COST'],

    //                     ];
    //                 }
    //                 DB::table('booktest')->insert($fields);
    //             }
    //             if (count($BT) == 1) {
    //                 foreach ($BT as $row) {
    //                     if (count($row['DETAILS']) == 1) {
    //                         $bookid = $token;
    //                     } else {
    //                         foreach ($row['DETAILS'] as $detail) {
    //                             $patientId = $detail['PATIENT_ID'];
    //                             if (in_array($patientId, $patientIds)) {
    //                                 $bookid = $token;
    //                                 break;
    //                             } else {
    //                                 $patientIds[] = $patientId;
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //             try {
    //                 $response = [
    //                     'Success' => true,
    //                     'Message' => 'Test booking successfully.',
    //                     'BOOK_ID' => $bookid,
    //                     'code' => 200
    //                 ];
    //             } catch (\Throwable $th) {
    //                 $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
    //             }
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
    //     }
    //     return $response;
    // }

    function upprescription(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');

            $response = array();
            $input = $req->all();

            $DR = $input['ADVICE_DR'];
            $P_ID = $input['PATIENT_ID'];
            $MOB = $input['MOBILE'];
            $PH_ID = $input['PHARMA_ID'];
            $MSG = $input['MESSAGE'];
            $cdt = Carbon::now()->format('ymdHis');

            $fileName = strtoupper(substr(md5($P_ID . $cdt . $PH_ID), 0, 5)) . "." . $req->file('file')->getClientOriginalExtension();

            $req->file('file')->storeAs('prescription', $fileName);
            $url = asset(storage::url('app/prescription')) . "/" . $fileName;

            $fields = [
                "PATIENT_ID" => $P_ID,
                "MOBILE_NO" => $MOB,
                "ADVISED_DR" => $DR,
                "PHARMA_ID" => $PH_ID,
                "MESSAGE" => $MSG,
                "PRESCRIPTION_URL" => $url,
                "UPLOAD_DT" => Carbon::now()->format('Ymd')
            ];

            try {
                DB::table('prescription')->insert($fields);
                $response = ['Success' => true, 'Message' => 'Prescription upload successfully.', 'code' => 200];
            } catch (\Throwable $th) {
                $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    // function booktest_dummy(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         date_default_timezone_set('Asia/Kolkata');
    //         $cdt = Carbon::now()->format('ymdHis');

    //         // Retrieve formdata
    //         $input = $req->all();
    //         $bookTest = json_decode($input['BOOK_TEST'], true);
    //         $bookTest = $bookTest[0];
    //         // Extract common data
    //         $pharmaId = $bookTest['PHARMA_ID'];
    //         $commonPatientId = $bookTest['PATIENT_ID']??null;
    //         $commonPatientName = $bookTest['PATIENT_NAME'];
    //         $commonAdvisedBy = $bookTest['ADVICED_BY'];
    //         $commonBookDate = Carbon::now()->format('Ymd');
    //         $commonBookTime = Carbon::now()->format('His');
    //         $bookById = $bookTest['BOOK_BY_ID'];
    //         $bookByName = $bookTest['BOOK_BY_NAME'];
    //         $MOBILE_NO = $bookTest['MOBILE_NO'];
    //         $drmessage = $bookTest['MESSAGE'];
    //         $location = $bookTest['ADDRESS']; //mandatory
    //         $alt_mob = $bookTest['ALT_MOB']; // not mandatory
    //         $sex = $bookTest['SEX'];       //mandatory
    //         $m_sts = $bookTest['M_STS'];   // not mandatory
    //         $age = $bookTest['AGE'];  //mandatory
    //         $familyId= $bookTest['FAMILY_ID'];  
    //         // Generate booking token
    //         $token = strtoupper(substr(md5($commonPatientId . $cdt . $pharmaId), 0, 10));

    //         // Initialize variables
    //         $patientIds = [];
    //         $bookid = null;
    //         $msg = "Test booking";
    //         // $url = "";
    //         // Check if file is present

    //         $insertedUserFamilyId=null;

    //         if($commonPatientId==null || $commonPatientId='') {
    //             $userFamilyData = [
    //                 // "ID" => $commonPatientId,
    //                 "FAMILY_ID" => $familyId,
    //                 "NAME" => $commonPatientName,
    //                 "LOCATION" => $location,
    //                 "MOBILE" => $MOBILE_NO,
    //                 "ALT_MOB" => $alt_mob ?? null,
    //                 "M_STS" => $m_sts ?? null,
    //                 "SEX" => $sex,
    //                 "DOB" => $age,
    //                 "RELATION" => 'Self'
    //             ];
    //             $insertedUserFamilyId = DB::table('user_family')->insertGetId($userFamilyData);
    //         }

    //         if ($req->hasFile('file')) {
    //             $file = $req->file('file');
    //             // Process file as needed (e.g., store, retrieve URL)
    //             $fileName = strtoupper(substr(md5($commonPatientId . $cdt . $pharmaId), 0, 5)) . "." . $req->file('file')->getClientOriginalExtension();
    //             // Logic to store or handle the file goes here
    //             $req->file('file')->storeAs('prescription', $fileName);
    //             $url = asset(storage::url('app/prescription')) . "/" . $fileName;

    //             $fields = [
    //                 "PATIENT_ID" => $commonPatientId??$insertedUserFamilyId,
    //                 "MOBILE_NO" => $MOBILE_NO,
    //                 "ADVISED_DR" => $commonAdvisedBy,
    //                 "PHARMA_ID" => $pharmaId,
    //                 "MESSAGE" => $drmessage,
    //                 "PRESCRIPTION_URL" => $url,
    //                 "UPLOAD_DT" => Carbon::now()->format('Ymd')
    //             ];
    //             DB::table('prescription')->insert($fields);
    //             $msg .= ' and Prescription upload';
    //         }
    //         $patientid=null;
    //         if($commonPatientId!=null){
    //             $patientid=$commonPatientId;
    //         }
    //         else{
    //             $patientid=$insertedUserFamilyId;
    //         }
    //             foreach ($bookTest['DETAILS'] as $detail) {
    //                 // Construct fields array
    //                 $fields1 = [
    //                     "BOOKING_ID" => $token,
    //                     "PKG_ID" => $detail['PKG_ID'] ?? $detail['TEST_ID'],
    //                     "PKG_NAME" => $detail['PKG_NAME'] ?? $detail['TEST_NAME'],
    //                     "CATEGORY" => $detail['CATEGORY'] ?? 'OTHERS',
    //                     "PHARMA_ID" => $pharmaId,
    //                     "BOOKING_DT" => $commonBookDate,
    //                     "BOOKING_TM" => $commonBookTime,
    //                     "SLOT_DT" => $detail['SLOT_DATE'],
    //                     "FROM" => $detail['FROM'],
    //                     "TO" => $detail['TO'],
    //                     "BOOK_BY_NAME" => $bookByName,
    //                     "BOOKED_BY_ID" => $bookById,
    //                     "PATIENT_ID" => $patientid,
    //                     "Patient_Name" => $commonPatientName,
    //                     "ADVICED_BY" => $commonAdvisedBy ?? '',
    //                     "PRESCRIPTION_URL" => $url ?? null, // Add file name or URL here
    //                     "HOME_COLLECT" => $detail['HOME_COLLECT'],
    //                     "TEST_COST" => $detail['PKG_COST'] ?? $detail['COST'],
    //                 ];

    //                 // Insert data into database
    //                 DB::table('booktest')->insert($fields1);

    //                 // Determine bookid
    //                 if (count($bookTest['DETAILS']) == 1) {
    //                     $bookid = $token;
    //                 } else {
    //                     if (in_array($commonPatientId, $patientIds)) {
    //                         $bookid = $token;
    //                     } else {
    //                         $patientIds[] = $commonPatientId;
    //                     }
    //                 }
    //             }
    //             $msg .= ' Successful';
    //             try {
    //                 if($insertedUserFamilyId!=null){
    //                     $response = [
    //                         'Success' => true,
    //                         'Message' => $msg,
    //                         'PATIENT_ID'=>$insertedUserFamilyId,
    //                         'BOOK_ID' => $bookid,
    //                         'code' => 200
    //                     ];
    //                 }else{
    //                     $response = [
    //                         'Success' => true,
    //                         'Message' => $msg,
    //                         'BOOK_ID' => $bookid,
    //                         'code' => 200
    //                     ];
    //                 }


    //             } catch (\Throwable $th) {
    //                 $response = [
    //                     'Success' => false,
    //                     'Message' => $th->getMessage(),
    //                     'code' => 200
    //                 ];
    //             }

    //     } else {
    //         $response = [
    //             'Success' => false,
    //             'Message' => 'Method Not Allowed.',
    //             'code' => 200
    //         ];
    //     }

    //     return response()->json($response);
    // }

    function booktest_dummy(Request $req)
    {
        // Ensure the request method is POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            $cdt = Carbon::now()->format('ymdHis');

            // Retrieve form data
            $input = $req->all();
            $bookTest = json_decode($input['BOOK_TEST'], true);
            $bookTest = $bookTest[0];

            // Extract common data
            $pharmaId = $bookTest['PHARMA_ID'];
            $commonPatientId = $bookTest['PATIENT_ID'] ?? null;
            $commonPatientName = $bookTest['PATIENT_NAME'];
            $commonAdvisedBy = $bookTest['ADVICED_BY'];
            $commonBookDate = Carbon::now()->format('Ymd');
            $commonBookTime = Carbon::now()->format('His');
            $bookById = $bookTest['BOOK_BY_ID'];
            $bookByName = $bookTest['BOOK_BY_NAME'];
            $MOBILE_NO = $bookTest['MOBILE_NO'];
            $drmessage = $bookTest['MESSAGE'];
            $location = $bookTest['ADDRESS']; // mandatory
            $alt_mob = $bookTest['ALT_MOB']; // not mandatory
            $sex = $bookTest['SEX']; // mandatory
            $m_sts = $bookTest['M_STS']; // not mandatory
            $age = $bookTest['AGE']; // mandatory
            $familyId = $bookTest['FAMILY_ID'];

            // Generate booking token
            $token = strtoupper(substr(md5($commonPatientId . $cdt . $pharmaId), 0, 10));

            // Initialize variables
            $patientIds = [];
            $bookid = null;
            $msg = "Test booking";
            $insertedUserFamilyId = null;

            // Check if commonPatientId is null or empty
            if (empty($commonPatientId)) {
                $userFamilyData = [
                    "FAMILY_ID" => $familyId,
                    "NAME" => $commonPatientName,
                    "LOCATION" => $location,
                    "MOBILE" => $MOBILE_NO,
                    "ALT_MOB" => $alt_mob,
                    "M_STS" => $m_sts,
                    "SEX" => $sex,
                    "DOB" => $age,
                    "RELATION" => 'Self'
                ];
                $insertedUserFamilyId = DB::table('user_family')->insertGetId($userFamilyData);
            }
            $patientid = $commonPatientId ?? $insertedUserFamilyId;

            // Handle file upload if present
            if ($req->hasFile('file')) {
                $file = $req->file('file');
                $fileName = strtoupper(substr(md5($commonPatientId . $cdt . $pharmaId), 0, 5)) . "." . $file->getClientOriginalExtension();
                $file->storeAs('prescription', $fileName);
                $url = asset(Storage::url('app/prescription')) . "/" . $fileName;

                $fields = [
                    "PATIENT_ID" => $patientid,
                    "MOBILE_NO" => $MOBILE_NO,
                    "ADVISED_DR" => $commonAdvisedBy,
                    "PHARMA_ID" => $pharmaId,
                    "MESSAGE" => $drmessage,
                    "PRESCRIPTION_URL" => $url,
                    "UPLOAD_DT" => Carbon::now()->format('Ymd')
                ];
                DB::table('prescription')->insert($fields);
                $msg .= ' and Prescription upload';
            }



            // Process booking details
            foreach ($bookTest['DETAILS'] as $detail) {
                $fields1 = [
                    "BOOKING_ID" => $token,
                    "PKG_ID" => $detail['PKG_ID'] ?? $detail['TEST_ID'],
                    "PKG_NAME" => $detail['PKG_NAME'] ?? $detail['TEST_NAME'],
                    "CATEGORY" => $detail['CATEGORY'] ?? 'OTHERS',
                    "PHARMA_ID" => $pharmaId,
                    "BOOKING_DT" => $commonBookDate,
                    "BOOKING_TM" => $commonBookTime,
                    "SLOT_DT" => $detail['SLOT_DATE'],
                    "SCH_ID" => $detail['SCH_ID'],
                    "FROM" => $detail['FROM'],
                    "TO" => $detail['TO'],
                    "BOOK_BY_NAME" => $bookByName,
                    "BOOKED_BY_ID" => $bookById,
                    "PATIENT_ID" => $patientid,
                    "Patient_Name" => $commonPatientName,
                    "ADVICED_BY" => $commonAdvisedBy ?? '',
                    "PRESCRIPTION_URL" => $url ?? null,
                    "HOME_COLLECT" => $detail['HOME_COLLECT'],
                    "TEST_COST" => $detail['PKG_COST'] ?? $detail['COST'],
                ];

                DB::table('booktest')->insert($fields1);

                if (count($bookTest['DETAILS']) == 1) {
                    $bookid = $token;
                } else {
                    if (in_array($commonPatientId, $patientIds)) {
                        $bookid = $token;
                    } else {
                        $patientIds[] = $commonPatientId;
                    }
                }
            }
            $msg .= ' Successful';

            // Prepare response
            try {
                $response = [
                    'Success' => true,
                    'Message' => $msg,
                    'BOOK_ID' => $bookid,
                    'code' => 200
                ];
                if ($insertedUserFamilyId) {
                    $response['PATIENT_ID'] = $insertedUserFamilyId;
                }
            } catch (\Throwable $th) {
                $response = [
                    'Success' => false,
                    'Message' => $th->getMessage(),
                    'code' => 200
                ];
            }
        } else {
            $response = [
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 200
            ];
        }

        return response()->json($response);
    }




    // function testinvoice_dummy(Request $request)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         // $headers = apache_request_headers();
    //         // session_start();
    //         // date_default_timezone_set('Asia/Kolkata');
    //         $input = $request->json()->all();

    //         // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
    //         if (isset($input['PATIENT_ID']) && isset($input['PHARMA_ID']) && isset($input['BOOKING_ID'])) {
    //             $PID = $input['PATIENT_ID'];
    //             $PHID = $input['PHARMA_ID'];
    //             $BID = $input['BOOKING_ID'];

    //             $currentDate = date('Ymd');

    //             $data1 = DB::table('booktest')
    //                 ->join('pharmacy', 'booktest.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
    //                 ->join('user_family', 'booktest.PATIENT_ID', '=', 'user_family.ID')
    //                 ->select(
    //                     'booktest.*',
    //                     'user_family.*',
    //                     'pharmacy.*',
    //                     'booktest.STATUS'
    //                 )
    //                 ->where(['booktest.BOOKING_ID' => $BID, 'booktest.PHARMA_ID' => $PHID, 'booktest.PATIENT_ID' => $PID])
    //                 ->orderby('booktest.SLOT_DT')
    //                 ->get();

    //             $groupedData = [];
    //             foreach ($data1 as $row) {
    //                 if (!isset($groupedData[$row->BOOKING_ID])) {
    //                     if ($row->SLOT_DT < $currentDate) {
    //                         $status = 'Cancelled';
    //                     } else {
    //                         $status = $row->STATUS;
    //                     }
    //                     $groupedData[$row->BOOKING_ID] = [
    //                         "BOOKING_ID" => $row->BOOKING_ID,
    //                         "BOOKING_DT" => $row->BOOKING_DT,
    //                         "BOOKING_TM" => $row->BOOKING_TM,
    //                         "PHARMA_ID" => $row->PHARMA_ID,
    //                         "PHARMA_NAME" => $row->ITEM_NAME,
    //                         "ADDRESS" => $row->ADDRESS,
    //                         "PIN" => $row->PIN,
    //                         "CITY" => $row->CITY,
    //                         "DIST" => $row->DIST,
    //                         "STATE" => $row->STATE,
    //                         "CLINIC_MOBILE" => $row->CLINIC_MOBILE,
    //                         "EMAIL" => $row->EMAIL,
    //                         "LATITUDE" => $row->LATITUDE,
    //                         "LONGITUDE" => $row->LONGITUDE,
    //                         "PHOTO_URL" => $row->PHOTO_URL,
    //                         "LOGO_URL" => $row->LOGO_URL,
    //                         "PATIENT_ID" => $row->PATIENT_ID,
    //                         "Patient_Name" => $row->Patient_Name,
    //                         "PATIENT_ADDRESS" => $row->LOCATION,
    //                         "MOBILE" => $row->MOBILE,
    //                         "SEX" => $row->SEX,
    //                         "AGE" => $row->DOB,
    //                         "STATUS" => $status,
    //                         "ADVICED_BY" => $row->ADVICED_BY,
    //                         "DETAILS" => []
    //                     ];
    //                 }
    //                 $groupedData[$row->BOOKING_ID]['DETAILS'][] = [
    //                     "PKG_ID" => $row->PKG_ID,
    //                     "PKG_NAME" => $row->PKG_NAME,
    //                     "TEST_COST" => $row->TEST_COST,
    //                     "PAY_MODE" => $row->PAY_MODE,
    //                     "HOME_COLLECT" => $row->HOME_COLLECT,
    //                     "TRANS_ID" => $row->TRANS_ID,
    //                     "SLOT_DT" => $row->SLOT_DT,
    //                     "FROM" => $row->FROM,
    //                     "TO" => $row->TO,
    //                 ];
    //             }

    //             $response = ['Success' => true, 'data' => $groupedData, 'code' => 200];
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

    function testinvoice_dummy(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $headers = apache_request_headers();
            // session_start();
            // date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();

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

                $groupedData = null;
                $totalCost = 0; // Initialize total cost
                foreach ($data1 as $row) {
                    if (!$groupedData) {
                        if ($row->SLOT_DT < $currentDate) {
                            $status = 'Cancelled';
                        } else {
                            $status = $row->STATUS;
                        }
                        $groupedData = [
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
                            'TOT_COST' => null,
                            "DETAILS" => []
                        ];
                    }
                    $groupedData['DETAILS'][] = [
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
                    $totalCost += $row->TEST_COST; // Add the test cost to the total cost
                }
                if ($groupedData) {
                    $groupedData['TOT_COST'] = $totalCost; // Add the total cost to grouped data
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

    function savetestinvoice_dummy(Request $req)
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


    function testbookinghistory_dummy(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->json()->all();
            if (isset($input['BOOK_BY_ID']) && isset($input['PHARMA_ID'])) {
                $bookedBy = $input['BOOK_BY_ID'];
                $PHID = $input['PHARMA_ID'];

                // Fetch the total cost for each booking with additional user details
                $bookingHistories = DB::table('booktest')
                    ->join('user_family', 'booktest.PATIENT_ID', '=', 'user_family.ID')
                    ->select(
                        'booktest.BOOKING_ID',
                        'booktest.BOOKING_DT',
                        'booktest.PATIENT_ID',
                        'booktest.PATIENT_NAME',
                        'booktest.ADVICED_BY',
                        'booktest.BOOKING_DT',
                        'booktest.SLOT_DT',
                        'booktest.BOOKING_TM',
                        'booktest.FROM',
                        'booktest.TO',
                        DB::raw('SUM(booktest.TEST_COST) AS TOTAL_COST'),
                        'user_family.LOCATION',
                        'user_family.MOBILE',
                        'user_family.ALT_MOB',
                        'user_family.M_STS',
                        'user_family.DOB as AGE',
                        'user_family.SEX'
                    )
                    ->where(['booktest.BOOKED_BY_ID' => $bookedBy, 'booktest.PHARMA_ID' => $PHID])
                    ->groupBy(
                        'booktest.BOOKING_ID',
                        'booktest.BOOKING_DT',
                        'booktest.PATIENT_ID',
                        'booktest.PATIENT_NAME',
                        'booktest.ADVICED_BY',
                        'booktest.BOOKING_DT',
                        'booktest.SLOT_DT',
                        'booktest.BOOKING_TM',
                        'booktest.FROM',
                        'booktest.TO',
                        'user_family.LOCATION',
                        'user_family.MOBILE',
                        'user_family.ALT_MOB',
                        'user_family.M_STS',
                        'user_family.DOB',
                        'user_family.SEX'
                    )
                    ->get();

                // Convert the collection to an array for sorting
                $bookingHistoriesArray = $bookingHistories->toArray();

                // Get today's date in yyyymmdd format
                $today = date('Ymd');

                // Sort the booking histories
                usort($bookingHistoriesArray, function ($a, $b) use ($today) {
                    // First, compare SLOT_DT
                    if ($a->SLOT_DT == $b->SLOT_DT) {
                        // If SLOT_DT is the same, compare FROM time
                        $aTime = strtotime(date("H:i", strtotime($a->FROM)));
                        $bTime = strtotime(date("H:i", strtotime($b->FROM)));

                        if ($aTime == $bTime) {
                            return 0;
                        }

                        return ($aTime < $bTime) ? -1 : 1;
                    }

                    // If SLOT_DT is different, sort based on SLOT_DT
                    if ($a->SLOT_DT < $today && $b->SLOT_DT < $today) {
                        return $a->SLOT_DT < $b->SLOT_DT ? 1 : -1; // Both less than today, so descending
                    }
                    if ($a->SLOT_DT < $today) {
                        return 1; // $a is less than today, put it at the bottom
                    }
                    if ($b->SLOT_DT < $today) {
                        return -1; // $b is less than today, put it at the bottom
                    }

                    return $a->SLOT_DT < $b->SLOT_DT ? -1 : 1; // Ascending for future dates
                });

                $response = ['Success' => true, 'data' => $bookingHistoriesArray, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }

        return response()->json($response);
    }


    function vustst_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
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

    function diagpkgdtl_dummy(Request $req)
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
                $pkg["packages"] = $data;
                $bnr["Catg_Banner"] = DB::table('dashboard')
                    ->select('DASH_ID AS BANNER_ID', 'DASH_SECTION_NAME AS BANNER_NAME', 'DASH_DESCRIPTION AS DESCRIPTION', 'BANNER_URL')
                    ->where(['DASH_ID' => $row->LAB_PKG_ID])->take(1)->get();

                $bnr1["Banner"] = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', 'TS')->get();
                $data1 = $pkg + $bnr + $bnr1;
                // foreach ($groupedData as $pkgId => &$package) {
                //     $package['TOT_TEST'] = count($package['DETAILS']);
                // }
                // $data = array_values($groupedData);
                $response = ['Success' => true, 'data' => $data1, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function admcatgclinicdr1_dummy(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->json()->all();
            if (isset($input['PHARMA_ID']) && isset($input['DIS_ID'])) {
                $pharmaId = $input['PHARMA_ID'];
                $did = $input['DIS_ID'];
                $sid = $input['SYM_ID'] ?? null;
                $data = collect();

                if ($sid == null) {
                    $fxd_banner = DB::table('dis_catg')->select('DIS_ID AS BANNER_ID', 'DIS_ID', 'DIS_DESC AS BANNER_DESC', 'BANNER_URL')->get();
                    $fltr_fxd_bnr = $fxd_banner->filter(function ($item) use ($did) {
                        return $item->BANNER_ID === $did;
                    });
                    $bnr["Catg_Banner"] = $fltr_fxd_bnr->values()->all();
                } else {
                    if (is_numeric($input['SYM_ID'])) {
                        $fxd_banner = DB::table('symptoms')
                            ->select('SYM_ID AS
                             BANNER_ID', 'DIS_ID', 'SYM_NAME AS BANNER_NAME', 'DESCRIPTION', 'BANNER_URL')
                            ->where('DASH_ID', '=', 'SM')->get();
                        $fltr_fxd_bnr = $fxd_banner->filter(function ($item) use ($sid) {
                            return $item->BANNER_ID === $sid;
                        });
                        $bnr["Catg_Banner"] = $fltr_fxd_bnr->values()->all();
                    } else {
                        $fxd_banner = DB::table('surgery')
                            ->select('SURG_TYPE AS BANNER_TYPE', 'DIS_ID', 'SURG_NAME AS BANNER_NAME', 'TYPE_DESC AS DESCRIPTION', 'BANNER_URL')
                            ->where('DASH_ID', '=', 'SR')->get();
                        $fltr_fxd_bnr = $fxd_banner->filter(function ($item) use ($sid) {
                            return $item->BANNER_TYPE === $sid;
                        });
                        $bnr["Catg_Banner"] = $fltr_fxd_bnr->take(1)->values()->all();
                    }
                }

                $data = $data->merge($this->getCatgDrDt1($pharmaId, $did));

                $data = $data->merge($bnr);
                if ($data == null) {
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

    // private function getCatgDrDt1($pharmaId, $did)
    // {
    //     $totdr = DB::table('drprofile')
    //         ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
    //         ->select(
    //             'drprofile.DR_ID',
    //             'drprofile.DR_NAME',
    //             'drprofile.DR_MOBILE',
    //             'drprofile.SEX',
    //             'drprofile.DESIGNATION',
    //             'drprofile.QUALIFICATION',
    //             'drprofile.UID_NMC',
    //             'drprofile.REGN_NO',
    //             'drprofile.D_CATG',
    //             'drprofile.EXPERIENCE',
    //             'drprofile.LANGUAGE',
    //             'drprofile.PHOTO_URL',
    //             'dr_availablity.CHK_IN_STATUS',
    //             'dr_availablity.DR_ARRIVE',
    //             'dr_availablity.CHEMBER_NO',

    //         )
    //         ->distinct('DR_ID')
    //         ->where(['dr_availablity.PHARMA_ID' => $pharmaId, 'dr_availablity.DIS_ID' => $did])
    //         ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
    //         ->where('drprofile.APPROVE', 'true')
    //         ->get();

    //     $DRSCH = ['Doctors' => []];


    //     foreach ($totdr as $row1) {
    //         $dravail = DB::table('dr_availablity')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pharmaId])->get();
    //         $totapp = DB::table('appointment')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pharmaId])->get();
    //         $data = [];

    //         foreach ($dravail as $row) {
    //             if (is_numeric($row->SCH_DAY)) {
    //                 $currentYear = date("Y");
    //                 $startDate = new DateTime("{$currentYear}-$row->START_MONTH-$row->SCH_DAY");

    //                 for ($i = 0; $i < 6; $i++) {
    //                     $dates = [];
    //                     $dates = $startDate->format('Ymd');
    //                     $schday = $startDate->format('l');
    //                     $cym = date('Ymd');
    //                     $bookingStartDate = Carbon::createFromFormat('Ymd', $dates)->addDays(-$row->BOOK_ST_DT);
    //                     $formattedBookingDate = $bookingStartDate->format('Ymd');

    //                     $startDate->modify('+' . $row->MONTH . 'months');
    //                     if ($dates >= $cym) {
    //                         $apnt_dt = $dates;
    //                         $apnt_id = $row->ID;

    //                         $fltr_apnt = $totapp->filter(function ($item) use ($apnt_dt, $apnt_id) {
    //                             return $item->APPNT_DT == $apnt_dt && $item->APPNT_ID == $apnt_id;
    //                         });
    //                         $totappct = $fltr_apnt->count();
    //                         if ($row->MAX_BOOK - $totappct == 0) {
    //                             $book_sts = "Closed";
    //                         } else {
    //                             $book_sts = "Available";
    //                         }
    //                         $data[] = [
    //                             "ID" => $row->ID,
    //                             "SCH_DT" => $dates,
    //                             "SCH_DAY" => $schday,
    //                             "SLOT" => $row->SLOT,
    //                             "SCHEDULE" => $row->DESCRIPTION,
    //                             "FROM" => $row->CHK_IN_TIME,
    //                             "TO" => $row->CHK_OUT_TIME,
    //                             "BOOK_START_DT" => $formattedBookingDate,
    //                             "BOOK_START_TIME" => $row->BOOK_ST_TM,
    //                             "DR_STATUS" => $row->CHK_IN_STATUS,
    //                             "DR_ARRIVE" => $row->DR_ARRIVE,
    //                             "CHEMBER_NO" => $row->CHEMBER_NO,
    //                             "MAX_BOOK" => $row->MAX_BOOK,
    //                             "SLOT_STATUS" => $book_sts,
    //                         ];
    //                         if (!empty($earliestDates)) {
    //                             break;
    //                         }
    //                     } else {
    //                         continue;
    //                     }
    //                 }
    //             } else {
    //                 $startDate = Carbon::today();
    //                 $endDate = Carbon::today()->addMonths(6);
    //                 $cym = date('Ymd');
    //                 $counter = 0;

    //                 while ($startDate->lte($endDate) && $counter < 6) {
    //                     $dates = [];
    //                     if ($startDate->format('l') === $row->SCH_DAY) {
    //                         if (in_array($startDate->weekOfMonth, explode(',', $row->WEEK))) {
    //                             $dates = $startDate->format('Ymd');
    //                             $bookingStartDate = Carbon::createFromFormat('Ymd', $dates)->addDays(-$row->BOOK_ST_DT);
    //                             $formattedBookingDate = $bookingStartDate->format('Ymd');

    //                             $apnt_dt = $formattedBookingDate;
    //                             $apnt_id = $row->ID;
    //                             $fltr_apnt = $totapp->filter(function ($item) use ($apnt_dt, $apnt_id) {
    //                                 return $item->APPNT_DT == $apnt_dt && $item->APPNT_ID == $apnt_id;
    //                             });
    //                             $totappct = $fltr_apnt->count();
    //                             if ($row->MAX_BOOK - $totappct == 0) {
    //                                 $book_sts = "Closed";
    //                             } else {
    //                                 $book_sts = "Available";
    //                             }
    //                             $data[] = [
    //                                 "ID" => $row->ID,
    //                                 "SCH_DT" => $dates,
    //                                 "SCH_DAY" => $row->SCH_DAY,
    //                                 "SLOT" => $row->SLOT,
    //                                 "SCHEDULE" => $row->DESCRIPTION,
    //                                 "FROM" => $row->CHK_IN_TIME,
    //                                 "TO" => $row->CHK_OUT_TIME,
    //                                 "BOOK_START_DT" => $formattedBookingDate,
    //                                 "BOOK_START_TIME" => $row->BOOK_ST_TM,
    //                                 "DR_STATUS" => $row->CHK_IN_STATUS,
    //                                 "DR_ARRIVE" => $row->DR_ARRIVE,
    //                                 "CHK_IN_TIME" => $row->CHK_IN_TIME,
    //                                 "CHEMBER_NO" => $row->CHEMBER_NO,
    //                                 "MAX_BOOK" => $row->MAX_BOOK,
    //                                 "SLOT_STATUS" => $book_sts,
    //                             ];
    //                             $counter++;
    //                         }
    //                     }
    //                     $startDate->addDay();
    //                 }
    //             }
    //         }
    //         usort($data, function ($item1, $item2) {
    //             return $item1['SCH_DT'] <=> $item2['SCH_DT'];
    //         });

    //         if ($data[0]['SCH_DT'] === $cym) {
    //             $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));
    //             $firstRowTOTime = $data[0]['TO'];

    //             if ($currentTime->greaterThan($firstRowTOTime)) {
    //                 $data[0]['DR_STATUS'] = "OUT";
    //                 $data[0]['SLOT_STATUS'] = "Closed";
    //             }
    //         }

    //         $collection = collect($data);
    //         $firstAvailable = $collection->first(function ($item) {
    //             return $item['DR_STATUS'] === 'IN' || $item['DR_STATUS'] === 'TIMELY';
    //         });
    //         if ($firstAvailable) {
    //             $firstAvailableIndex = $collection->search($firstAvailable);
    //             $sixRows = array_slice($data, $firstAvailableIndex, 6);
    //         }

    //         $DRSCH['Doctors'][$row1->DR_ID] = [
    //             "ID" => $row1->DR_ID,
    //             "ITEM_NAME" => $row1->DR_NAME,
    //             "FIELD_TYPE" => "Doctor",
    //             "DETAILS" => [
    //                 "DR_ID" => $row1->DR_ID,
    //                 "DR_NAME" => $row1->DR_NAME,
    //                 "DR_MOBILE" => $row1->DR_MOBILE,
    //                 "DR_STATUS" => $row1->CHK_IN_STATUS,
    //                 "DR_ARRIVE" => $row1->DR_ARRIVE,
    //                 "CHEMBER_NO" => $row1->CHEMBER_NO,
    //                 "SEX" => $row1->SEX,
    //                 "DESIGNATION" => $row1->DESIGNATION,
    //                 "QUALIFICATION" => $row1->QUALIFICATION,
    //                 "UID_NMC" => $row1->UID_NMC,
    //                 "REGN_NO" => $row1->REGN_NO,
    //                 "D_CATG" => $row1->D_CATG,
    //                 "EXPERIENCE" => $row1->EXPERIENCE,
    //                 "DR_FEES" => $row->DR_FEES,
    //                 "LANGUAGE" => $row1->LANGUAGE,
    //                 "DR_PHOTO" => $row1->PHOTO_URL,
    //                 "AVAILABLE_DT" => $sixRows[0]['SCH_DT'],
    //                 "SLOT_STATUS" => $sixRows[0]['SLOT_STATUS'],
    //                 "CHK_IN_TIME" => $sixRows[0]['CHK_IN_TIME'],
    //                 "SCH_DT" => $sixRows
    //             ]
    //         ];
    //     }
    //     if (empty($DRSCH['Doctors'])) {
    //         $DRSCH['Doctors'] = [];
    //     }
    //     $DRSCH['Doctors'] = array_values($DRSCH['Doctors']);
    //     return $DRSCH;
    // }

    private function getCatgDrDt1($pharmaId, $did)
    {
        $totdr = DB::table('drprofile')
            ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
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
                'dr_availablity.CHK_IN_STATUS',
                'dr_availablity.DR_ARRIVE',
                'dr_availablity.CHEMBER_NO',
            )
            ->distinct('DR_ID')
            ->where(['dr_availablity.PHARMA_ID' => $pharmaId, 'dr_availablity.DIS_ID' => $did])
            ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
            ->where('drprofile.APPROVE', 'true')
            ->get();

        $DRSCH = ['Doctors' => []];

        foreach ($totdr as $row1) {
            $dravail = DB::table('dr_availablity')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pharmaId])->get();
            $totapp = DB::table('appointment')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pharmaId])->get();
            $data = [];

            foreach ($dravail as $row) {
                if (is_numeric($row->SCH_DAY)) {
                    $currentYear = date("Y");
                    $startDate = new DateTime("{$currentYear}-$row->START_MONTH-$row->SCH_DAY");

                    for ($i = 0; $i < 6; $i++) {
                        $dates = $startDate->format('Ymd');
                        $schday = $startDate->format('l');
                        $cym = date('Ymd');
                        $bookingStartDate = Carbon::createFromFormat('Ymd', $dates)->addDays(-$row->BOOK_ST_DT);
                        $formattedBookingDate = $bookingStartDate->format('Ymd');

                        $startDate->modify('+' . $row->MONTH . 'months');
                        if ($dates >= $cym) {
                            $apnt_dt = $dates;
                            $apnt_id = $row->ID;

                            $fltr_apnt = $totapp->filter(function ($item) use ($apnt_dt, $apnt_id) {
                                return $item->APPNT_DT == $apnt_dt && $item->APPNT_ID == $apnt_id;
                            });
                            $totappct = $fltr_apnt->count();
                            $book_sts = $row->MAX_BOOK - $totappct == 0 ? "Closed" : "Available";

                            $data[] = [
                                "ID" => $row->ID,
                                "SCH_DT" => $dates,
                                "SCH_DAY" => $schday,
                                "SLOT" => $row->SLOT,
                                "SCHEDULE" => $row->DESCRIPTION,
                                "FROM" => $row->CHK_IN_TIME,
                                "TO" => $row->CHK_OUT_TIME,
                                "BOOK_START_DT" => $formattedBookingDate,
                                "BOOK_START_TIME" => $row->BOOK_ST_TM,
                                "DR_STATUS" => $row->CHK_IN_STATUS,
                                "DR_ARRIVE" => $row->DR_ARRIVE,
                                "CHEMBER_NO" => $row->CHEMBER_NO,
                                "MAX_BOOK" => $row->MAX_BOOK,
                                "SLOT_STATUS" => $book_sts,
                            ];
                        }
                    }
                } else {
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->addMonths(6);
                    $cym = date('Ymd');
                    $counter = 0;

                    while ($startDate->lte($endDate) && $counter < 6) {
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
                                $book_sts = $row->MAX_BOOK - $totappct == 0 ? "Closed" : "Available";

                                $data[] = [
                                    "ID" => $row->ID,
                                    "SCH_DT" => $dates,
                                    "SCH_DAY" => $row->SCH_DAY,
                                    "SLOT" => $row->SLOT,
                                    "SCHEDULE" => $row->DESCRIPTION,
                                    "FROM" => $row->CHK_IN_TIME,
                                    "TO" => $row->CHK_OUT_TIME,
                                    "BOOK_START_DT" => $formattedBookingDate,
                                    "BOOK_START_TIME" => $row->BOOK_ST_TM,
                                    "DR_STATUS" => $row->CHK_IN_STATUS,
                                    "DR_ARRIVE" => $row->DR_ARRIVE,
                                    "CHK_IN_TIME" => $row->CHK_IN_TIME,
                                    "CHEMBER_NO" => $row->CHEMBER_NO,
                                    "MAX_BOOK" => $row->MAX_BOOK,
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

            if (!empty($data) && $data[0]['SCH_DT'] === $cym) {
                $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));
                $firstRowTOTime = Carbon::createFromFormat('h:i A', $data[0]['TO']);

                if ($currentTime->greaterThan($firstRowTOTime)) {
                    $data[0]['DR_STATUS'] = "OUT";
                    $data[0]['SLOT_STATUS'] = "Closed";
                }
            }

            $collection = collect($data);
            $firstAvailable = $collection->first(function ($item) {
                return $item['DR_STATUS'] === 'IN' || $item['DR_STATUS'] === 'TIMELY';
            });

            $sixRows = [];
            if ($firstAvailable) {
                $firstAvailableIndex = $collection->search($firstAvailable);
                $sixRows = array_slice($data, $firstAvailableIndex, 6);
            }

            $DRSCH['Doctors'][$row1->DR_ID] = [
                "ID" => $row1->DR_ID,
                "ITEM_NAME" => $row1->DR_NAME,
                "FIELD_TYPE" => "Doctor",
                "DETAILS" => [
                    "DR_ID" => $row1->DR_ID,
                    "DR_NAME" => $row1->DR_NAME,
                    "DR_MOBILE" => $row1->DR_MOBILE,
                    "DR_STATUS" => $row1->CHK_IN_STATUS,
                    "DR_ARRIVE" => $row1->DR_ARRIVE,
                    "CHEMBER_NO" => $row1->CHEMBER_NO,
                    "SEX" => $row1->SEX,
                    "DESIGNATION" => $row1->DESIGNATION,
                    "QUALIFICATION" => $row1->QUALIFICATION,
                    "UID_NMC" => $row1->UID_NMC,
                    "REGN_NO" => $row1->REGN_NO,
                    "D_CATG" => $row1->D_CATG,
                    "EXPERIENCE" => $row1->EXPERIENCE,
                    "DR_FEES" => $row->DR_FEES ?? null,
                    "LANGUAGE" => $row1->LANGUAGE,
                    "DR_PHOTO" => $row1->PHOTO_URL,
                    "AVAILABLE_DT" => $sixRows[0]['SCH_DT'] ?? null,
                    "SLOT_STATUS" => $sixRows[0]['SLOT_STATUS'] ?? null,
                    "CHK_IN_TIME" => $sixRows[0]['CHK_IN_TIME'] ?? null,
                    "SCH_DT" => $sixRows,
                ]
            ];
        }

        if (empty($DRSCH['Doctors'])) {
            $DRSCH['Doctors'] = [];
        }

        $DRSCH['Doctors'] = array_values($DRSCH['Doctors']);
        return $DRSCH;
    }

    // public function allSpecialist_dummy(Request $request)
    // {
    //     if ($request->isMethod('post')) {
    //         $response = [];
    //         $input = $request->json()->all();

    //         if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
    //             // try {
    //             $latitude = $input['LATITUDE'];
    //             $longitude = $input['LONGITUDE'];

    //             $specialists['Specialist'] = DB::table('dis_catg')
    //                 ->select(
    //                     'DIS_ID',
    //                     'DASH_SECTION_ID',
    //                     'DIS_TYPE',
    //                     'DIS_CATEGORY',
    //                     'SPECIALIST',
    //                     'SPECIALITY',
    //                     'DIS_DESC',
    //                     // 'PHOTO_URL',
    //                     // 'BANNER_URL',
    //                     // 'PHOTO1_URL',
    //                     // 'BANNER1_URL',
    //                     'PHOTO2_URL AS PHOTO_URL',
    //                     'BANNER2_URL AS BANNER_URL',
    //                 )
    //                 ->where('STATUS', 'Active')
    //                 ->get();

    //             $arr_A['Banner'] = DB::table('promo_banner')
    //                 ->select('PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
    //                 ->where('DASH_SECTION_ID', '=', 'SP')->take(3)->get();
    //             $data = $specialists + $arr_A;

    //             $response = ['Success' => true, 'data' => $data, 'code' => 200];
    //             // } catch (\Exception $e) {
    //             //     $response = ['Success' => false, 'Message' => 'An unexpected error occurred. Please try again later.', 'code' => 500];
    //             // }
    //         } else {
    //             $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
    //     }

    //     return response()->json($response);
    // }

    public function allSpecialist_dummy(Request $request)
    {
        if ($request->isMethod('post')) {
            $response = [];
            $input = $request->json()->all();

            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                // try {
                $latitude = $input['LATITUDE'];
                $longitude = $input['LONGITUDE'];
                $I_DTL = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->where('facility_section.DASH_SECTION_ID', 'SP')
                    ->orderBy('facility_type.DT_POSITION')
                    // ->orderBy('facility.DN_POSITION')
                    ->get();


                $groupedData = [];
                foreach ($I_DTL as $row2) {
                    if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
                        $groupedData[$row2->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DESCRIPTION" => $row2->DS_DESCRIPTION,
                            "PHOTO_URL" => $row2->DSIMG1,
                            "BANNER_URL" => $row2->DSBNR1,
                            "DASH_TYPE" => []
                        ];
                    }

                    if (!isset($groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE])) {
                        $groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DASH_TYPE" => $row2->DASH_TYPE,
                            "DESCRIPTION" => $row2->DT_DESCRIPTION,
                            // "PHOTO_URL" => $row2->URL_IPD_MG,
                            // "PHOTO_URL1" => $row2->DNIMG1,

                            "PHOTO_URL1" => $row2->DTIMG1,

                            "PHOTO_URL2" => $row2->DTIMG2,
                            "PHOTO_URL3" => $row2->DTIMG3,
                            "PHOTO_URL4" => $row2->DTIMG4,
                            "PHOTO_URL5" => $row2->DTIMG5,
                            "PHOTO_URL6" => $row2->DTIMG6,
                            "PHOTO_URL7" => $row2->DTIMG7,
                            "PHOTO_URL8" => $row2->DTIMG8,
                            "PHOTO_URL9" => $row2->DTIMG9,
                            "PHOTO_URL10" => $row2->DTIMG10,

                            "BANNER_URL1" => $row2->DTBNR1,
                            "BANNER_URL2" => $row2->DTBNR2,
                            "BANNER_URL3" => $row2->DTBNR3,
                            "BANNER_URL4" => $row2->DTBNR4,
                            "BANNER_URL5" => $row2->DTBNR5,
                            "BANNER_URL6" => $row2->DTBNR6,
                            "BANNER_URL7" => $row2->DTBNR7,
                            "BANNER_URL8" => $row2->DTBNR8,
                            "BANNER_URL9" => $row2->DTBNR9,
                            "BANNER_URL10" => $row2->DTBNR10,
                            "FACILITY_DETAILS" => []
                        ];
                    }

                    $groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE]['FACILITY_DETAILS'][] = [
                        "DASH_ID" => $row2->DASH_ID,
                        // "DIS_ID" => $row2->DIS_ID,
                        // "SYM_ID" => $row2->SYM_ID,
                        "DASH_NAME" => $row2->DASH_NAME,
                        "DASH_TYPE" => $row2->DASH_TYPE,
                        "DESCRIPTION" => $row2->DN_DESCRIPTION,
                        // "PHOTO_URL" => $row2->URL_IPD_MI,
                        // "BANNER_URL" => $row2->DN_BANNER_URL,
                        "PHOTO_URL1" => $row2->DNIMG1,
                        "PHOTO_URL2" => $row2->DNIMG2,
                        "PHOTO_URL3" => $row2->DNIMG3,
                        "PHOTO_URL4" => $row2->DNIMG4,
                        "PHOTO_URL5" => $row2->DNIMG5,
                        "PHOTO_URL6" => $row2->DNIMG6,
                        "PHOTO_URL7" => $row2->DNIMG7,
                        "PHOTO_URL8" => $row2->DNIMG8,
                        "PHOTO_URL9" => $row2->DNIMG9,
                        "PHOTO_URL10" => $row2->DNIMG10,

                        "BANNER_URL1" => $row2->DNBNR1,
                        "BANNER_URL2" => $row2->DNBNR2,
                        "BANNER_URL3" => $row2->DNBNR3,
                        "BANNER_URL4" => $row2->DNBNR4,
                        "BANNER_URL5" => $row2->DNBNR5,
                        "BANNER_URL6" => $row2->DNBNR6,
                        "BANNER_URL7" => $row2->DNBNR7,
                        "BANNER_URL8" => $row2->DNBNR8,
                        "BANNER_URL9" => $row2->DNBNR9,
                        "BANNER_URL10" => $row2->DNBNR10,

                    ];
                }
                $specialists['Specialist'] = array_values($groupedData);
                // $specialists['Specialist'] = DB::table('dis_catg')
                //     ->select(
                //         'DIS_ID',
                //         'DASH_SECTION_ID',
                //         'DIS_TYPE',
                //         'DIS_CATEGORY',
                //         'SPECIALIST',
                //         'SPECIALITY',
                //         'DIS_DESC',
                //         // 'PHOTO_URL',
                //         // 'BANNER_URL',
                //         // 'PHOTO1_URL',
                //         // 'BANNER1_URL',
                //         'PHOTO2_URL AS PHOTO_URL',
                //         'BANNER2_URL AS BANNER_URL',
                //     )
                //     ->where('STATUS', 'Active')
                //     ->get();

                $arr_A['Banner'] = DB::table('promo_banner')
                    ->select('PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', 'SP')->take(3)->get();
                $data = $specialists + $arr_A;

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
                // } catch (\Exception $e) {
                //     $response = ['Success' => false, 'Message' => 'An unexpected error occurred. Please try again later.', 'code' => 500];
                // }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }

        return response()->json($response);
    }

    public function specialist_pharma_dummy(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->json()->all();
            if (isset($input['DIS_ID']) && isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                try {
                    $disId = $input['DIS_ID'];
                    $latt = $input['LATITUDE'];
                    $lont = $input['LONGITUDE'];

                    // Fetch distinct pharma_id values based on the provided DIS_ID
                    $pharmaIds = DB::table('dr_availablity')
                        ->select('PHARMA_ID')
                        ->where('DIS_ID', $disId)
                        ->distinct()
                        ->pluck('PHARMA_ID');

                    // Fetch pharmacy details including distance calculation
                    $pharmacies['Hospitals'] = DB::table('pharmacy')
                        ->select(
                            'PHARMA_ID',
                            'ITEM_NAME as PHARMA_NAME',
                            'ADDRESS',
                            'CITY',
                            'DIST',
                            'CLINIC_MOBILE',
                            'PIN',
                            'EMAIL',
                            'STATE',
                            'LATITUDE',
                            'LONGITUDE',
                            'PHOTO_URL',
                            'LOGO_URL',
                            DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                            * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                            * SIN(RADIANS('$latt'))))),2) as KM"),
                            'PH_RATING'
                        )
                        ->whereIn('PHARMA_ID', $pharmaIds)
                        ->get();

                    $fxd_banner = DB::table('dis_catg')->select('DIS_ID AS BANNER_ID', 'DIS_ID', 'DIS_DESC AS BANNER_DESC', 'BANNER_URL')->get();
                    $fltr_fxd_bnr = $fxd_banner->filter(function ($item) use ($disId) {
                        return $item->BANNER_ID === $disId;
                    });
                    $bnr["Catg_Banner"] = $fltr_fxd_bnr->values()->all();

                    $modifiedTest_bnr = DB::table('promo_banner')
                        ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                        ->where('DASH_SECTION_ID', 'SP')
                        ->take(3)
                        ->get();
                    $modifiedResponse["Banner"] = $modifiedTest_bnr;

                    $data = $pharmacies + $bnr + $modifiedResponse;
                    $response = ['Success' => true, 'data' => $data, 'code' => 200];
                } catch (\Exception $e) {
                    $response = ['Success' => false, 'Message' => 'An unexpected error occurred. Please try again later.', 'code' => 500];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Missing dis_id, LATITUDE, or LONGITUDE parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }

        return response()->json($response);
    }


    // function clinic_catgdr_dummy(Request $request)
    // {
    //     if ($request->isMethod('post')) {
    //         date_default_timezone_set('Asia/Kolkata');
    //         $input = $request->json()->all();
    //         if (isset($input['DIS_ID']) && isset($input['PHARMA_ID']) && isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {

    //             $pid = $input['PHARMA_ID'];
    //             $did = $input['DIS_ID'];
    //             $latt = $input['LATITUDE'];
    //             $lont = $input['LONGITUDE'];
    //             $data = collect();

    //             $promo_banner = DB::table('promo_banner')
    //                 ->select('PROMO_ID', 'DASH_SECTION_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
    //                 ->whereIn('DASH_SECTION_ID', ['SP', 'CL'])->get();

    //             $fltr_promo_bnr = $promo_banner->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'CL';
    //             });
    //             $BNR = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                 ];
    //             })->values()->take(1)->all();


    //             // Fetch pharmacy details including distance calculation
    //             $pharmacies['Hospital'] = DB::table('pharmacy')
    //                 ->joinSub($BNR, 'banner_cl', function ($join) {
    //                     $join->on('pharmacy.PHARMA_ID', '=', 'banner_cl.PHARMA_ID');
    //                 })
    //                 ->select(
    //                     'pharmacy.PHARMA_ID',
    //                     'pharmacy.ITEM_NAME as PHARMA_NAME',
    //                     'pharmacy.ADDRESS',
    //                     'pharmacy.CITY',
    //                     'pharmacy.DIST',
    //                     'pharmacy.CLINIC_MOBILE',
    //                     'pharmacy.PIN',
    //                     'pharmacy.EMAIL',
    //                     'pharmacy.STATE',
    //                     'pharmacy.LATITUDE',
    //                     'pharmacy.LONGITUDE',
    //                     'pharmacy.PHOTO_URL',
    //                     'pharmacy.LOGO_URL',
    //                     'banner_cl.PROMO_URL',
    //                     DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
    //                  * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
    //                  * SIN(RADIANS('$latt'))))),2) as KM"),
    //                     'PH_RATING'
    //                 )
    //                 ->where('PHARMA_ID', $pid)
    //                 ->get();

    //             $data1 = $data->merge($this->getCatgDrDt($pid, $did));
    //             $data['Doctors'] = $data1['Specialists'];

    //             $fltr_promo_bnr = $promo_banner->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'SP';
    //             });
    //             $arr_A["Banner"] = $fltr_promo_bnr->values()->take(3)->all();


    //             // $arr_A['Banner'] = DB::table('promo_banner')
    //             //     ->select('PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
    //             //     ->where(['DASH_SECTION_ID'=>'SP','PHARMA_ID'=>$pid])->take(3)->get();

    //             $data = $data->merge($arr_A);
    //             $data = $data->merge($pharmacies);
    //             if ($data === null) {
    //                 $response = ['Success' => false, 'Message' => 'Record not found', 'code' => 200];
    //             } else {
    //                 $response = ['Success' => true, 'data' => $data, 'code' => 200];
    //             }
    //         } else {
    //             $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
    //     }
    //     return $response;
    // }

    function clinic_catgdr_dummy(Request $request)
    {
        $response = [];
        if ($request->method() === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();
            if (isset($input['DIS_ID'], $input['PHARMA_ID'], $input['LATITUDE'], $input['LONGITUDE'])) {
                $pid = $input['PHARMA_ID'];
                $did = $input['DIS_ID'];
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $data = collect();

                $promo_banner = DB::table('promo_banner')
                    ->select('PROMO_ID', 'DASH_SECTION_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->whereIn('DASH_SECTION_ID', ['SP', 'CL'])
                    ->where('PHARMA_ID', $pid)
                    ->get();


                // $fltr_promo_bnr = $promo_banner->filter(function ($item) {
                //     return $item->DASH_SECTION_ID === 'CL';
                // })->map(function ($item) {
                //     return ["PROMO_URL" => $item->PROMO_URL];
                // })->values()->take(1);
                $fltr_promo_bnr = DB::table('promo_banner')
                    ->select('PHARMA_ID', 'PROMO_URL')
                    ->whereIn('DASH_SECTION_ID', ['CL'])
                    ->where('PHARMA_ID', $pid)
                    ->limit(1);

                // Fetch hospitals (pharmacies) with distinct PHARMA_ID
                $pharmacies['Hospital'] = DB::table('pharmacy')
                    ->select(
                        'pharmacy.PHARMA_ID',
                        'pharmacy.ITEM_NAME as PHARMA_NAME',
                        'pharmacy.ADDRESS',
                        'pharmacy.CITY',
                        'pharmacy.DIST',
                        'pharmacy.CLINIC_MOBILE',
                        'pharmacy.PIN',
                        'pharmacy.EMAIL',
                        'pharmacy.STATE',
                        'pharmacy.LATITUDE',
                        'pharmacy.LONGITUDE',
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        'dr_availablity.TAG_NOTE',
                        'dr_availablity.DEPT_PH',
                        'banner_cl.PROMO_URL',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
             * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
             * SIN(RADIANS('$latt'))))),2) as KM"),
                        'PH_RATING'
                    )
                    ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
                    ->joinSub($fltr_promo_bnr, 'banner_cl', 'pharmacy.PHARMA_ID', '=', 'banner_cl.PHARMA_ID')
                    ->distinct('pharmacy.PHARMA_ID')

                    ->where(['pharmacy.PHARMA_ID' => $pid, 'dr_availablity.DIS_ID' => $did])
                    ->get();

                $distinctDoctors = DB::table('dr_availablity')
                    ->select(
                        'DR_ID',
                        'TAG_DEPT',
                        'TAG_NOTE',
                        'DEPT_PH',
                        'POSITION'
                    )
                    ->distinct()
                    ->where(['dr_availablity.DIS_ID' => $did, 'dr_availablity.PHARMA_ID' => $pid])
                    ->where('dr_availablity.TAG_DEPT', 'true')
                    ->orderBy('dr_availablity.POSITION');

                // return $distinctDoctors;

                $D_DR = DB::table('drprofile')
                    ->joinSub($distinctDoctors, 'distinct_doctors', function ($join) {
                        $join->on('drprofile.DR_ID', '=', 'distinct_doctors.DR_ID');
                    })
                    ->select(
                        'drprofile.DR_ID',
                        'drprofile.DIS_ID',
                        'drprofile.DR_NAME',
                        'drprofile.DR_MOBILE',
                        'drprofile.SEX',
                        'drprofile.DESIGNATION',
                        'drprofile.QUALIFICATION',
                        'drprofile.D_CATG',
                        'drprofile.EXPERIENCE',
                        'drprofile.LANGUAGE',
                        'drprofile.PHOTO_URL AS DR_PHOTO',
                        'distinct_doctors.TAG_DEPT',
                        // 'distinct_doctors.TAG_NOTE',
                        'distinct_doctors.DEPT_PH',
                    )
                    ->get();


                // $data1 = $data->merge($this->getCatgDrDt($pid, $did));
                $data['Doctors'] = $D_DR;


                $fltr_promo_bnr = $promo_banner->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SP';
                })->values()->take(3);

                $data = $data->merge(['Banner' => $fltr_promo_bnr->all()]);
                $data = $data->merge($pharmacies);

                if ($data->isEmpty()) {
                    $response = ['Success' => false, 'Message' => 'Record not found', 'code' => 200];
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


    // private function getCatgDrDt($pid, $did)
    // {
    //     $distinctDoctors = DB::table('dr_availablity')
    //     ->select('DR_ID',
    //             'TAG_DEPT',
    //             'TAG_NOTE',
    //             'DEPT_PH',)
    //     ->distinct()
    //     ->where(['dr_availablity.DIS_ID' => $did, 'dr_availablity.PHARMA_ID' => $pid])
    //         ->where('dr_availablity.TAG_DEPT', 'true');

    //     // return $distinctDoctors;

    //     $D_DR  = DB::table('drprofile')
    //     ->joinSub($distinctDoctors, 'distinct_doctors', function ($join) {
    //         $join->on('drprofile.DR_ID', '=', 'distinct_doctors.DR_ID');
    //     })
    //     ->select(
    //         'drprofile.DR_ID',
    //         'drprofile.DIS_ID',
    //         'drprofile.DR_NAME',
    //         'drprofile.DR_MOBILE',
    //         'drprofile.SEX',
    //         'drprofile.DESIGNATION',
    //         'drprofile.QUALIFICATION',
    //         'drprofile.D_CATG',
    //         'drprofile.EXPERIENCE',
    //         'drprofile.LANGUAGE',
    //         'drprofile.PHOTO_URL AS DR_PHOTO',
    //         'distinct_doctors.DR_FEES',

    //     )
    //     ->get();



    //     // $distinctdoctor = DB::table('dr_availablity')
    //     //     ->select(
    //     //         'DR_ID',
    //     //         'TAG_DEPT',
    //     //         'TAG_NOTE',
    //     //         'DEPT_PH',
    //     //     )
    //     //     ->distinct()
    //     //     ->where(['dr_availablity.DIS_ID' => $did, 'dr_availablity.PHARMA_ID' => $pid])
    //     //     ->where('dr_availablity.TAG_DEPT', 'true')
    //     //     ->get();

    //     // // RETURN $distinctdoctor;

    //     // $totdr = DB::table('drprofile')
    //     //     ->joinSub($distinctdoctor, 'distinct_doctor', 'drprofile.DR_ID', '=', 'distinct_doctor.DR_ID')
    //     //     ->where('drprofile.DIS_ID', $did)
    //     //     ->select(
    //     //         'drprofile.DR_ID',
    //     //         'drprofile.DR_NAME',
    //     //         'drprofile.DR_MOBILE',
    //     //         'drprofile.SEX',
    //     //         'drprofile.DESIGNATION',
    //     //         'drprofile.QUALIFICATION',
    //     //         'drprofile.UID_NMC',
    //     //         'drprofile.REGN_NO',
    //     //         'drprofile.D_CATG',
    //     //         'drprofile.EXPERIENCE',
    //     //         'drprofile.LANGUAGE',
    //     //         'drprofile.PHOTO_URL',
    //     //     )
    //     //     // ->distinct('DR_ID')
    //     //     // ->where(['dr_availablity.DIS_ID' => $did, 'dr_availablity.PHARMA_ID' => $pid])
    //     //     // ->where('dr_availablity.TAG_DEPT', 'true')
    //     //     // ->where('drprofile.APPROVE', 'true')
    //     //     ->get();

    //     $DRSCH = ['Doctors' => []];

    //     foreach ($totdr as $row1) {
    //         $dravail = DB::table('dr_availablity')
    //             ->where(['DR_ID' => $row1->DR_ID])
    //             ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
    //             ->orderby('dr_availablity.CHK_OUT_TIME')->get();
    //         $totapp = DB::table('appointment')->where(['DR_ID' => $row1->DR_ID])->get();
    //         $data = [];

    //         foreach ($dravail as $row) {
    //             if (is_numeric($row->SCH_DAY)) {
    //                 $currentYear = date("Y");
    //                 $startDate = new DateTime("{$currentYear}-$row->START_MONTH-$row->SCH_DAY");

    //                 for ($i = 0; $i < 12; $i++) {
    //                     $dates = [];
    //                     $dates = $startDate->format('Ymd');
    //                     $schday = $startDate->format('l');
    //                     $cym = date('Ymd');

    //                     $startDate->modify('+' . $row->MONTH . 'months');
    //                     if ($dates >= $cym) {
    //                         $apnt_dt = $dates;
    //                         $apnt_id = $row->ID;

    //                         $fltr_apnt = $totapp->filter(function ($item) use ($apnt_dt, $apnt_id) {
    //                             return $item->APPNT_DT == $apnt_dt && $item->APPNT_ID == $apnt_id;
    //                         });
    //                         $totappct = $fltr_apnt->count();
    //                         if ($row->MAX_BOOK - $totappct == 0) {
    //                             $book_sts = "Closed";
    //                         } else {
    //                             $book_sts = "Available";
    //                         }

    //                         if ($row->ABS_TDT != null) {
    //                             if ($row->ABS_TDT < $dates) {
    //                                 $dr_status = "TIMELY";
    //                             } else {
    //                                 $dr_status = $row->CHK_IN_STATUS;
    //                             }
    //                         } else {
    //                             $dr_status = $row->CHK_IN_STATUS;
    //                         }

    //                         $data[] = [
    //                             "SCH_DT" => $dates,
    //                             "DR_STATUS" => $dr_status,
    //                             "DR_FEES" => $row->DR_FEES,
    //                             "ABS_FDT" => $row->ABS_FDT,
    //                             "ABS_TDT" => $row->ABS_TDT,
    //                             "DR_ARRIVE" => $row->DR_ARRIVE,
    //                             "CHK_IN_TIME" => $row->CHK_IN_TIME,
    //                             "CHEMBER_NO" => $row->CHEMBER_NO,
    //                             "TO" => $row->CHK_OUT_TIME,
    //                             "SLOT_STATUS" => $book_sts,
    //                         ];
    //                         if (!empty($earliestDates)) {
    //                             break;
    //                         }
    //                     } else {
    //                         continue;
    //                     }
    //                 }
    //             } else {
    //                 $startDate = Carbon::today();
    //                 $endDate = Carbon::today()->addMonths(6);
    //                 $cym = date('Ymd');
    //                 $counter = 0;

    //                 while ($startDate->lte($endDate) && $counter < 6) {
    //                     $dates = [];
    //                     if ($startDate->format('l') === $row->SCH_DAY) {
    //                         if (in_array($startDate->weekOfMonth, explode(',', $row->WEEK))) {
    //                             $dates = $startDate->format('Ymd');
    //                             $bookingStartDate = Carbon::createFromFormat('Ymd', $dates)->addDays(-$row->BOOK_ST_DT);
    //                             $formattedBookingDate = $bookingStartDate->format('Ymd');

    //                             $apnt_dt = $formattedBookingDate;
    //                             $apnt_id = $row->ID;
    //                             $fltr_apnt = $totapp->filter(function ($item) use ($apnt_dt, $apnt_id) {
    //                                 return $item->APPNT_DT == $apnt_dt && $item->APPNT_ID == $apnt_id;
    //                             });
    //                             $totappct = $fltr_apnt->count();
    //                             if ($row->MAX_BOOK - $totappct == 0) {
    //                                 $book_sts = "Closed";
    //                             } else {
    //                                 $book_sts = "Available";
    //                             }
    //                             if ($row->ABS_TDT != null) {
    //                                 if ($row->ABS_TDT < $dates) {
    //                                     $dr_status = "TIMELY";
    //                                 } else {
    //                                     $dr_status = $row->CHK_IN_STATUS;
    //                                 }
    //                             } else {
    //                                 $dr_status = $row->CHK_IN_STATUS;
    //                             }
    //                             $data[] = [
    //                                 "SCH_DT" => $dates,
    //                                 "DR_STATUS" => $dr_status,
    //                                 "DR_FEES" => $row->DR_FEES,
    //                                 "ABS_FDT" => $row->ABS_FDT,
    //                                 "ABS_TDT" => $row->ABS_TDT,
    //                                 "DR_ARRIVE" => $row->DR_ARRIVE,
    //                                 "CHK_IN_TIME" => $row->CHK_IN_TIME,
    //                                 "CHEMBER_NO" => $row->CHEMBER_NO,
    //                                 "TO" => $row->CHK_OUT_TIME,
    //                                 "SLOT_STATUS" => $book_sts,
    //                             ];
    //                             $counter++;
    //                         }
    //                     }
    //                     $startDate->addDay();
    //                 }
    //             }
    //         }
    //         usort($data, function ($item1, $item2) {
    //             return $item1['SCH_DT'] <=> $item2['SCH_DT'];
    //         });
    //         if ($data[0]['SCH_DT'] === $cym) {
    //             $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));
    //             $firstRowTOTime = $data[0]['TO'];

    //             if ($currentTime->greaterThan($firstRowTOTime)) {
    //                 $data[0]['DR_STATUS'] = "OUT";
    //                 $data[0]['SLOT_STATUS'] = "Closed";
    //             }
    //         }
    //         $collection = collect($data);
    //         $firstAvailable = $collection->first(function ($item) {
    //             return $item['DR_STATUS'] === 'IN' || $item['DR_STATUS'] === 'TIMELY' || $item['DR_STATUS'] === 'DELAY';
    //         });
    //         if ($firstAvailable) {
    //             $firstAvailableIndex = $collection->search($firstAvailable);
    //             $sixRows = array_slice($data, $firstAvailableIndex, 1);
    //         }

    //         if (!empty($sixRows)) {
    //             $DRSCH['Specialists'][$row1->DR_ID] = [
    //                 "DR_ID" => $row1->DR_ID,
    //                 "DR_NAME" => $row1->DR_NAME,
    //                 "DR_MOBILE" => $row1->DR_MOBILE,
    //                 "SEX" => $row1->SEX,
    //                 "DESIGNATION" => $row1->DESIGNATION,
    //                 "QUALIFICATION" => $row1->QUALIFICATION,
    //                 "UID_NMC" => $row1->UID_NMC,
    //                 "REGN_NO" => $row1->REGN_NO,
    //                 "D_CATG" => $row1->D_CATG,
    //                 "EXPERIENCE" => $row1->EXPERIENCE,
    //                 "LANGUAGE" => $row1->LANGUAGE,
    //                 "DR_PHOTO" => $row1->PHOTO_URL,
    //                 // "KM" => $row1->KM,
    //                 "AVAILABLE_DT" => $sixRows[0]['SCH_DT'],
    //                 "SLOT_STATUS" => $sixRows[0]['SLOT_STATUS'],
    //                 "DR_STATUS" => $sixRows[0]['DR_STATUS'],
    //                 "DR_FEES" => $sixRows[0]['DR_FEES'],
    //                 "CHK_IN_TIME" => $sixRows[0]['CHK_IN_TIME'],
    //                 "DR_ARRIVE" => $sixRows[0]['DR_ARRIVE'],
    //                 "CHEMBER_NO" => $sixRows[0]['CHEMBER_NO'],
    //             ];
    //         }
    //     }
    //     if (empty($DRSCH['Specialists'])) {
    //         $DRSCH['Specialists'] = [];
    //     }
    //     usort($DRSCH['Specialists'], function ($a, $b) {
    //         $statusOrder = ['IN' => 1, 'TIMELY' => 2, 'DELAY' => 3, 'CANCELLED' => 4, 'OUT' => 5, 'LEAVE' => 6];
    //         if ($a['AVAILABLE_DT'] != $b['AVAILABLE_DT']) {
    //             return $a['AVAILABLE_DT'] <=> $b['AVAILABLE_DT'];
    //         }
    //         if ($statusOrder[$a['DR_STATUS']] != $statusOrder[$b['DR_STATUS']]) {
    //             return $statusOrder[$a['DR_STATUS']] <=> $statusOrder[$b['DR_STATUS']];
    //         }
    //         return $a['CHK_IN_TIME'] <=> $b['CHK_IN_TIME'];
    //     });
    //     $DRSCH['Specialists'] = array_values($DRSCH['Specialists']);
    //     return $DRSCH;
    // }




    // function globalscrh_dummy(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $input = $req->json()->all();
    //         if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
    //             $latt = $input['LATITUDE'];
    //             $lont = $input['LONGITUDE'];

    //             $data = collect();

    //             $data1 = DB::table('drprofile')
    //                 ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
    //                 ->where(['drprofile.APPROVE' => 'true'])
    //                 ->where('dr_availablity.SCH_STATUS', '!=', 'NA')
    //                 ->select(
    //                     'drprofile.DR_ID',
    //                     'drprofile.DR_NAME',
    //                     'drprofile.DR_MOBILE',
    //                     'drprofile.SEX',
    //                     'drprofile.DESIGNATION',
    //                     'drprofile.QUALIFICATION',
    //                     'drprofile.D_CATG',
    //                     'drprofile.EXPERIENCE',
    //                     'drprofile.PHOTO_URL',
    //                     // 'dr_availablity.PHARMA_ID',
    //                     // 'dr_availablity.PHARMA_NAME',
    //                     // 'dr_availablity.DR_FEES'
    //                 )
    //                 ->distinct()
    //                 ->get();

    //             foreach ($data1 as $row1) {
    //                 $drdtl = [];
    //                 $drdtl['DETAILS'] = [
    //                     "DR_ID" => $row1->DR_ID,
    //                     "DR_NAME" => $row1->DR_NAME,
    //                     "DR_MOBILE" => $row1->DR_MOBILE,
    //                     "SEX" => $row1->SEX,
    //                     "DESIGNATION" => $row1->DESIGNATION,
    //                     "QUALIFICATION" => $row1->QUALIFICATION,
    //                     "D_CATG" => $row1->D_CATG,
    //                     "EXPERIENCE" => $row1->EXPERIENCE,
    //                     "DR_PHOTO" => $row1->PHOTO_URL,
    //                     // "PHARMA_ID" => $row1->PHARMA_ID,
    //                     // "PHARMA_NAME" => $row1->PHARMA_NAME,
    //                     // "DR_FEES" => $row1->DR_FEES,
    //                 ];

    //                 $data[] = [
    //                     "ID" => $row1->DR_ID,
    //                     "ITEM_NAME" => $row1->DR_NAME,
    //                     "FIELD_TYPE" => "Doctor",
    //                     "DETAILS" => $drdtl['DETAILS']
    //                 ];
    //             }

    //             $data2 = DB::table('dis_catg')->get();
    //             foreach ($data2 as $row3) {
    //                 $spdtl = [];
    //                 $spdtl['DETAILS'] = [
    //                     "DIS_ID" => $row3->DIS_ID,
    //                     "D_CATG" => $row3->DIS_CATEGORY,
    //                 ];

    //                 $data->push([
    //                     "ID" => $row3->DIS_ID,
    //                     "ITEM_NAME" => $row3->SPECIALIST,
    //                     "ITEM_NAME1" => $row3->SPECIALITY,
    //                     "FIELD_TYPE" => "Specialist",
    //                     "DETAILS" => $spdtl['DETAILS']
    //                 ]);
    //             }

    //             $data3 = DB::table('symptoms')->get();
    //             foreach ($data3 as $row4) {
    //                 $sydtl = [];
    //                 $sydtl['DETAILS'] = [
    //                     "DIS_ID" => $row4->DIS_ID,
    //                     "D_CATG" => $row4->DIS_CATEGORY,
    //                     "SYM_ID" => $row4->SYM_ID,
    //                     "DESCRIPTION" => $row4->DESCRIPTION,
    //                 ];

    //                 $data->push([
    //                     "ID" => $row4->DIS_ID,
    //                     "ITEM_NAME" => $row4->SYM_NAME,
    //                     "FIELD_TYPE" => "Symptom",
    //                     "DETAILS" => $sydtl['DETAILS']
    //                 ]);
    //             }

    //             $data5 = DB::table('master_testdata')->get();
    //             foreach ($data5 as $row1) {
    //                 $tstdtl = [];
    //                 $tstdtl['DETAILS'] = [
    //                     "TEST_ID" => $row1->TEST_ID,
    //                     "TEST_NAME" => $row1->TEST_NAME,
    //                     "TEST_CODE" => $row1->TEST_CODE,
    //                     "TEST_SAMPLE" => $row1->TEST_SAMPLE,
    //                     "TEST_CATG" => $row1->TEST_CATG,
    //                     "ORGAN_ID" => $row1->ORGAN_ID,
    //                     "ORGAN_NAME" => $row1->ORGAN_NAME,
    //                     "ORGAN_URL" => $row1->ORGAN_URL,
    //                     "DEPARTMENT" => $row1->DEPARTMENT,
    //                     "TEST_DESC" => $row1->TEST_DESC,
    //                     "KNOWN_AS" => $row1->KNOWN_AS,
    //                     "FASTING" => $row1->FASTING,
    //                     "GENDER_TYPE" => $row1->GENDER_TYPE,
    //                     "AGE_TYPE" => $row1->AGE_TYPE,
    //                     "REPORT_TIME" => $row1->REPORT_TIME,
    //                     "PRESCRIPTION" => $row1->PRESCRIPTION,
    //                     "ID_PROOF" => $row1->ID_PROOF,
    //                     "QA1" => $row1->QA1,
    //                     "QA2" => $row1->QA2,
    //                     "QA3" => $row1->QA3,
    //                     "QA4" => $row1->QA4,
    //                     "QA5" => $row1->QA5,
    //                     "QA6" => $row1->QA6,
    //                 ];
    //                 $data->push([
    //                     "ID" => $row1->TEST_ID,
    //                     "ITEM_NAME" => $row1->TEST_NAME,
    //                     "FIELD_TYPE" => $row1->DEPARTMENT,
    //                     "DETAILS" => $tstdtl['DETAILS']
    //                 ]);
    //             }

    //             $data6 = DB::table('pharmacy')
    //                 ->select('pharmacy.*', DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
    //         * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
    //         * SIN(RADIANS('$latt'))))),2) as KM"),)
    //                 ->take(25)
    //                 ->get();
    //             foreach ($data6 as $row2) {
    //                 $cldtl = [];
    //                 $cldtl['DETAILS'] = [
    //                     "PHARMA_ID" => $row2->PHARMA_ID,
    //                     "PHARMA_NAME" => $row2->ITEM_NAME,
    //                     "ADDRESS" => $row2->ADDRESS,
    //                     "CITY" => $row2->CITY,
    //                     "DIST" => $row2->DIST,
    //                     "STATE" => $row2->STATE,
    //                     "PIN" => $row2->PIN,
    //                     "PHOTO_URL" => $row2->PHOTO_URL,
    //                     "CLINIC_TYPE" => $row2->CLINIC_TYPE,
    //                     "CLINIC_MOBILE" => $row2->CLINIC_MOBILE,
    //                     "LATITUDE" => $row2->LATITUDE,
    //                     "LONGITUDE" => $row2->LONGITUDE,
    //                     "KM" => $row2->KM,
    //                 ];

    //                 $data->push([
    //                     "ID" => $row2->PHARMA_ID,
    //                     "ITEM_NAME" => $row2->ITEM_NAME,
    //                     "FIELD_TYPE" => $row2->CLINIC_TYPE,
    //                     "DETAILS" => $cldtl['DETAILS']
    //                 ]);
    //             }

    //             $data7 = DB::table('dashboard')
    //                 ->whereIn('DASH_SECTION_ID', ['B', 'C', 'D', 'G', 'H'])
    //                 ->where('STATUS', 'Active')
    //                 ->orderby('DASH_TYPE')
    //                 ->get();

    //             foreach ($data7 as $row3) {
    //                 $pkgdtl = [];

    //                 $pkgdtl['DETAILS'] = [
    //                     "AGE_TYPE" => $row3->AGE_TYPE,
    //                     "DESCRIPTION" => $row3->DASH_DESCRIPTION,
    //                     "FASTING" => $row3->FASTING,
    //                     "GENDER_TYPE" => $row3->GENDER_TYPE,
    //                     "DASH_ID" => $row3->DASH_ID,
    //                     "ID_PROOF" => $row3->ID_PROOF,
    //                     "DASH_NAME" => $row3->DASH_NAME,
    //                     "DASH_TYPE" => $row3->DASH_TYPE,
    //                     "KNOWN_AS" => $row3->KNOWN_AS,
    //                     "PHOTO_URL" => $row3->PHOTO_URL,
    //                     "PRESCRIPTION" => $row3->PRESCRIPTION,
    //                     "QA1" => $row3->QA1,
    //                     "QA2" => $row3->QA2,
    //                     "QA3" => $row3->QA3,
    //                     "QA4" => $row3->QA4,
    //                     "QA5" => $row3->QA5,
    //                     "QA6" => $row3->QA6,
    //                     "REPORT_TIME" => $row3->REPORT_TIME,
    //                     "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
    //                     "SECTION_SL" => $row3->POSITION,
    //                     "STATUS" => $row3->STATUS
    //                 ];


    //                 $data->push([
    //                     "ID" => $row3->DASH_ID,
    //                     "ITEM_NAME" => $row3->DASH_NAME,
    //                     "FIELD_TYPE" => $row3->DASH_SECTION_ID,
    //                     "DETAILS" => $pkgdtl['DETAILS']
    //                 ]);
    //             }

    //             $testDetails = [];
    //             $data8 = DB::table('dashboard')
    //                 ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard.DASH_ID')
    //                 ->join('master_testdata', 'sym_organ_test.TEST_ID', '=', 'master_testdata.TEST_ID')
    //                 ->select([
    //                     'dashboard.DASH_ID',
    //                     'dashboard.DASH_NAME',
    //                     'dashboard.PHOTO_URL',
    //                     'dashboard.DASH_TYPE',
    //                     'master_testdata.TEST_ID',
    //                     'master_testdata.TEST_SL',
    //                     'master_testdata.TEST_NAME',
    //                     'master_testdata.TEST_CODE',
    //                     'master_testdata.TEST_SAMPLE',
    //                     'master_testdata.TEST_CATG',
    //                     'master_testdata.DEPARTMENT',
    //                     'master_testdata.TEST_DESC',
    //                     'master_testdata.KNOWN_AS',
    //                     'master_testdata.FASTING',
    //                     'master_testdata.GENDER_TYPE',
    //                     'master_testdata.AGE_TYPE',
    //                     'master_testdata.REPORT_TIME',
    //                     'master_testdata.PRESCRIPTION',
    //                     'master_testdata.ID_PROOF',
    //                     'master_testdata.QA1',
    //                     'master_testdata.QA2',
    //                     'master_testdata.QA3',
    //                     'master_testdata.QA4',
    //                     'master_testdata.QA5',
    //                     'master_testdata.QA6'
    //                 ])
    //                 ->where([
    //                     ['dashboard.DASH_SECTION_ID', '=', 'S'],
    //                     ['dashboard.STATUS', '=', 'Active']
    //                 ])
    //                 ->orderBy('dashboard.POSITION')
    //                 ->get();

    //             foreach ($data8 as $item) {
    //                 if (!isset($testDetails[$item->DASH_ID])) {
    //                     $testDetails[$item->DASH_ID] = [
    //                         'ID' => $item->DASH_ID,
    //                         'ITEM_NAME' => $item->DASH_NAME,
    //                         'FIELD_TYPE' => $item->DASH_TYPE,
    //                         'DETAILS' => []
    //                     ];
    //                 }
    //                 $testDetails[$item->DASH_ID]['DETAILS'][] = [
    //                     "TEST_ID" => $item->TEST_ID,
    //                     "TEST_SL" => $item->TEST_SL,
    //                     "TEST_NAME" => $item->TEST_NAME,
    //                     "TEST_CODE" => $item->TEST_CODE,
    //                     "TEST_SAMPLE" => $item->TEST_SAMPLE,
    //                     "TEST_CATG" => $item->TEST_CATG,
    //                     "DEPARTMENT" => $item->DEPARTMENT,
    //                     "TEST_DESC" => $item->TEST_DESC,
    //                     "KNOWN_AS" => $item->KNOWN_AS,
    //                     "FASTING" => $item->FASTING,
    //                     "GENDER_TYPE" => $item->GENDER_TYPE,
    //                     "AGE_TYPE" => $item->AGE_TYPE,
    //                     "REPORT_TIME" => $item->REPORT_TIME,
    //                     "PRESCRIPTION" => $item->PRESCRIPTION,
    //                     "ID_PROOF" => $item->ID_PROOF,
    //                     "QA1" => $item->QA1,
    //                     "QA2" => $item->QA2,
    //                     "QA3" => $item->QA3,
    //                     "QA4" => $item->QA4,
    //                     "QA5" => $item->QA5,
    //                     "QA6" => $item->QA6
    //                 ];
    //             }

    //             $data = $data->merge(array_values($testDetails));


    //             // Query facility_section
    //             $data10 = DB::table('facility_section')
    //                 ->where('DS_STATUS', 'Active')
    //                 ->orderby('ID')
    //                 ->get();

    //             foreach ($data10 as $row3) {
    //                 $pkgdtl = [
    //                     "DESCRIPTION" => $row3->DS_DESCRIPTION,
    //                     "ID" => $row3->ID,
    //                     "PHOTO_URL" => $row3->DSH_PHOTO_URL,
    //                     "DSM_PHOTO_URL" => $row3->DSM_PHOTO_URL,
    //                     "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
    //                     "POSITION" => $row3->DS_POSITION,
    //                     "STATUS" => $row3->DS_STATUS,
    //                 ];

    //                 if ($row3->DASH_SECTION_ID == 'SR') {
    //                     $pkgdtl['Questions'] = [
    //                         [
    //                             "QA1" => $row3->DSQA1,
    //                             "QA2" => $row3->DSQA2,
    //                             "QA3" => $row3->DSQA3,
    //                             "QA4" => $row3->DSQA4,
    //                             "QA5" => $row3->DSQA5,
    //                             "QA6" => $row3->DSQA6,
    //                             "QA7" => $row3->DSQA7,
    //                             "QA8" => $row3->DSQA8,
    //                             "QA9" => $row3->DSQA9,
    //                         ]
    //                     ];
    //                 } else {
    //                     $pkgdtl['QA1'] = $row3->DSQA1;
    //                     $pkgdtl['QA2'] = $row3->DSQA2;
    //                     $pkgdtl['QA3'] = $row3->DSQA3;
    //                     $pkgdtl['QA4'] = $row3->DSQA4;
    //                     $pkgdtl['QA5'] = $row3->DSQA5;
    //                     $pkgdtl['QA6'] = $row3->DSQA6;
    //                 }

    //                 $data->push(
    //                     [
    //                         "ID" => $row3->ID,
    //                         "ITEM_NAME" => $row3->DASH_SECTION_NAME,
    //                         "FIELD_TYPE" => '',
    //                         "DETAILS" => $pkgdtl
    //                     ]
    //                 );
    //             }

    //             // Query facility_type
    //             $data11 = DB::table('facility_type')
    //                 ->whereIn('DASH_SECTION_ID', ['AG', 'AH', 'AI', 'AL', 'AP', 'SR', 'AM'])
    //                 ->where('DT_STATUS', 'Active')
    //                 ->orderby('DASH_TYPE_ID')
    //                 ->get();

    //             foreach ($data11 as $row3) {
    //                 $pkgdtl = [
    //                     "ID" => $row3->DASH_TYPE_ID,
    //                     "DASH_TYPE" => $row3->DASH_TYPE,
    //                     "DESCRIPTION" => $row3->DT_DESCRIPTION,
    //                     "BANNER_URL" => $row3->DT_BANNER_URL,
    //                     "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
    //                     "POSITION" => $row3->DT_POSITION,
    //                     "STATUS" => $row3->DT_STATUS,
    //                 ];

    //                 if ($row3->DASH_SECTION_ID == 'AG') {
    //                     $row3->DASH_TYPE = '24x7 ' . $row3->DASH_TYPE;
    //                 }

    //                 if ($row3->DASH_SECTION_ID == 'SR') {
    //                     $pkgdtl['Questions'] = [
    //                         [
    //                             "QA1" => $row3->DTQA1,
    //                             "QA2" => $row3->DTQA2,
    //                             "QA3" => $row3->DTQA3,
    //                             "QA4" => $row3->DTQA4,
    //                             "QA5" => $row3->DTQA5,
    //                             "QA6" => $row3->DTQA6,
    //                             "QA7" => $row3->DTQA7,
    //                             "QA8" => $row3->DTQA8,
    //                             "QA9" => $row3->DTQA9,
    //                         ]
    //                     ];
    //                 } else {
    //                     $pkgdtl['QA1'] = $row3->DTQA1;
    //                     $pkgdtl['QA2'] = $row3->DTQA2;
    //                     $pkgdtl['QA3'] = $row3->DTQA3;
    //                     $pkgdtl['QA4'] = $row3->DTQA4;
    //                     $pkgdtl['QA5'] = $row3->DTQA5;
    //                     $pkgdtl['QA6'] = $row3->DTQA6;
    //                 }

    //                 $data->push(
    //                     [
    //                         "ID" => $row3->DASH_TYPE_ID,
    //                         "ITEM_NAME" => $row3->DASH_TYPE,
    //                         "FIELD_TYPE" => $row3->DASH_SECTION_ID,
    //                         "DETAILS" => $pkgdtl
    //                     ]
    //                 );
    //             }

    //             // Query facility
    //             $data12 = DB::table('facility')
    //                 ->where('DN_STATUS', 'Active')
    //                 ->orderby('DASH_ID')
    //                 ->get();

    //             foreach ($data12 as $row3) {
    //                 $pkgdtl = [
    //                     "ID" => $row3->DASH_ID,
    //                     "DASH_NAME" => $row3->DASH_NAME,
    //                     "DESCRIPTION" => $row3->DN_DESCRIPTION,
    //                     "BANNER_URL" => $row3->DN_BANNER_URL,
    //                     "DASH_TYPE_ID" => $row3->DASH_TYPE_ID,
    //                     "DN_TAG_SECTION" => $row3->DN_TAG_SECTION,
    //                     "POSITION" => $row3->DN_POSITION,
    //                     "STATUS" => $row3->DN_STATUS,
    //                 ];

    //                 if ($row3->DN_TAG_SECTION == 'AG') {
    //                     $row3->DASH_NAME = '24x7 ' . $row3->DASH_NAME;
    //                 }

    //                 if (strpos($row3->DN_TAG_SECTION, 'SR') !== false) {
    //                     $pkgdtl['Questions'] = [
    //                         [
    //                             "QA1" => $row3->DNQA1,
    //                             "QA2" => $row3->DNQA2,
    //                             "QA3" => $row3->DNQA3,
    //                             "QA4" => $row3->DNQA4,
    //                             "QA5" => $row3->DNQA5,
    //                             "QA6" => $row3->DNQA6,
    //                             "QA7" => $row3->DNQA7,
    //                             "QA8" => $row3->DNQA8,
    //                             "QA9" => $row3->DNQA9,
    //                         ]
    //                     ];
    //                 } else {
    //                     $pkgdtl['QA1'] = $row3->DNQA1;
    //                     $pkgdtl['QA2'] = $row3->DNQA2;
    //                     $pkgdtl['QA3'] = $row3->DNQA3;
    //                     $pkgdtl['QA4'] = $row3->DNQA4;
    //                     $pkgdtl['QA5'] = $row3->DNQA5;
    //                     $pkgdtl['QA6'] = $row3->DNQA6;
    //                 }

    //                 $data->push(
    //                     [
    //                         "ID" => $row3->DASH_ID,
    //                         "ITEM_NAME" => $row3->DASH_NAME,
    //                         "FIELD_TYPE" => $row3->DASH_TYPE_ID,
    //                         "DETAILS" => $pkgdtl
    //                     ]
    //                 );
    //             }

    //             if ($data->isEmpty()) {
    //                 $response = ['Success' => false, 'Message' => 'No Search data found', 'code' => 200];
    //             } else {
    //                 $response = ['Success' => true, 'data' => $data, 'code' => 200];
    //             }
    //         } else {
    //             $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
    //     }
    //     return response()->json($response);
    // }

    //     function globalscrh_dummy(Request $req)
// {
//     // Simplified request method check
//     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//         return response()->json(['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200]);
//     }

    //     $input = $req->json()->all();
//     if (!isset($input['LATITUDE']) || !isset($input['LONGITUDE'])) {
//         return response()->json(['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422]);
//     }

    //     $latt = $input['LATITUDE'];
//     $lont = $input['LONGITUDE'];

    //     $data = collect();

    //     // Query doctors with selective columns and combined query
//     $data1 = DB::table('drprofile')
//         ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
//         ->where('drprofile.APPROVE', 'true')
//         ->where('dr_availablity.SCH_STATUS', '!=', 'NA')
//         ->select([
//             'drprofile.DR_ID',
//             'drprofile.DR_NAME',
//             'drprofile.DR_MOBILE',
//             'drprofile.SEX',
//             'drprofile.DESIGNATION',
//             'drprofile.QUALIFICATION',
//             'drprofile.D_CATG',
//             'drprofile.EXPERIENCE',
//             'drprofile.PHOTO_URL',
//         ])
//         ->distinct()
//         ->get();

    //     foreach ($data1 as $row1) {
//         $data->push([
//             'ID' => $row1->DR_ID,
//             'ITEM_NAME' => $row1->DR_NAME,
//             'FIELD_TYPE' => 'Doctor',
//             'CHECK_TYPE' => 'Doctor',
//             'DETAILS' => [
//                 'DR_ID' => $row1->DR_ID,
//                 'DR_NAME' => $row1->DR_NAME,
//                 'DR_MOBILE' => $row1->DR_MOBILE,
//                 'SEX' => $row1->SEX,
//                 'DESIGNATION' => $row1->DESIGNATION,
//                 'QUALIFICATION' => $row1->QUALIFICATION,
//                 'D_CATG' => $row1->D_CATG,
//                 'EXPERIENCE' => $row1->EXPERIENCE,
//                 'DR_PHOTO' => $row1->PHOTO_URL,
//             ]
//         ]);
//     }

    //     // Query specialists with selective columns
//     $data2 = DB::table('dis_catg')
//     ->select('DIS_ID', 'SPECIALIST', 'SPECIALITY', 'DIS_CATEGORY')
//     ->get();
//     foreach ($data2 as $row3) {
//         $data->push([
//             'ID' => $row3->DIS_ID,
//             'ITEM_NAME' => $row3->SPECIALIST,
//             'ITEM_NAME1' => $row3->SPECIALITY,
//             'CHECK_TYPE' => 'Specialist',
//             'FIELD_TYPE' => 'Specialist',
//             'DETAILS' => [
//                 'DIS_ID' => $row3->DIS_ID,
//                 'D_CATG' => $row3->DIS_CATEGORY,
//             ]
//         ]);
//     }

    //     // Query symptoms with selective columns
//     $data3 = DB::table('symptoms')
//     ->select('DIS_ID', 'SYM_NAME', 'DIS_CATEGORY', 'SYM_ID', 'DESCRIPTION')
//         ->get();
//     foreach ($data3 as $row4) {
//         $data->push([
//             'ID' => $row4->DIS_ID,
//             'ITEM_NAME' => $row4->SYM_NAME,
//             'FIELD_TYPE' => 'Symptom',
//             'CHECK_TYPE' => 'Symptom',
//             'DETAILS' => [
//                 'DIS_ID' => $row4->DIS_ID,
//                 'D_CATG' => $row4->DIS_CATEGORY,
//                 'SYM_ID' => $row4->SYM_ID,
//                 'DESCRIPTION' => $row4->DESCRIPTION,
//             ]
//         ]);
//     }

    //     // Query master_testdata with selective columns
//     $data5 = DB::table('master_testdata')
//     ->select([
//         'TEST_ID', 'TEST_NAME', 'TEST_CODE', 'TEST_SAMPLE', 'TEST_CATG', 'ORGAN_ID', 
//         'ORGAN_NAME', 'ORGAN_URL', 'DEPARTMENT', 'TEST_DESC', 'KNOWN_AS', 'FASTING', 
//         'GENDER_TYPE', 'AGE_TYPE', 'REPORT_TIME', 'PRESCRIPTION', 'ID_PROOF', 
//         'QA1', 'QA2', 'QA3', 'QA4', 'QA5', 'QA6'
//     ])
//     ->get();
//     foreach ($data5 as $row1) {
//         $data->push([
//             'ID' => $row1->TEST_ID,
//             'ITEM_NAME' => $row1->TEST_NAME,
//             'FIELD_TYPE' => $row1->DEPARTMENT,
//             'CHECK_TYPE' => $row1->DEPARTMENT,
//             'DETAILS' => [
//                 'TEST_ID' => $row1->TEST_ID,
//                 'TEST_NAME' => $row1->TEST_NAME,
//                 'TEST_CODE' => $row1->TEST_CODE,
//                 'TEST_SAMPLE' => $row1->TEST_SAMPLE,
//                 'TEST_CATG' => $row1->TEST_CATG,
//                 'ORGAN_ID' => $row1->ORGAN_ID,
//                 'ORGAN_NAME' => $row1->ORGAN_NAME,
//                 'ORGAN_URL' => $row1->ORGAN_URL,
//                 'DEPARTMENT' => $row1->DEPARTMENT,
//                 'TEST_DESC' => $row1->TEST_DESC,
//                 'KNOWN_AS' => $row1->KNOWN_AS,
//                 'FASTING' => $row1->FASTING,
//                 'GENDER_TYPE' => $row1->GENDER_TYPE,
//                 'AGE_TYPE' => $row1->AGE_TYPE,
//                 'REPORT_TIME' => $row1->REPORT_TIME,
//                 'PRESCRIPTION' => $row1->PRESCRIPTION,
//                 'ID_PROOF' => $row1->ID_PROOF,
//                 'QA1' => $row1->QA1,
//                 'QA2' => $row1->QA2,
//                 'QA3' => $row1->QA3,
//                 'QA4' => $row1->QA4,
//                 'QA5' => $row1->QA5,
//                 'QA6' => $row1->QA6,
//             ]
//         ]);
//     }

    //     // Query nearby pharmacies with selective columns
//     $data6 = DB::table('pharmacy')
//     // ->select([
//     //     'pharmacy.PHARMA_ID', 'pharmacy.ITEM_NAME', 'pharmacy.ADDRESS', 'pharmacy.CITY', 'pharmacy.DIST', 'pharmacy.STATE', 'pharmacy.PIN', 
//     //     'pharmacy.PHOTO_URL', 'pharmacy.CLINIC_TYPE', 'pharmacy.CLINIC_MOBILE', 'pharmacy.LATITUDE', 'pharmacy.LONGITUDE',
//     //     DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
//     //     * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
//     //     * SIN(RADIANS('$latt'))))),2) as KM")
//     // ])
//         ->select([
//             'pharmacy.*',
//             DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
//             * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
//             * SIN(RADIANS('$latt'))))),2) as KM")
//         ])
//         ->take(25)
//         ->get();

    //     foreach ($data6 as $row2) {
//         $data->push([
//             'ID' => $row2->PHARMA_ID,
//             'ITEM_NAME' => $row2->ITEM_NAME,
//             'FIELD_TYPE' => $row2->CLINIC_TYPE,
//             'CHECK_TYPE' => $row2->CLINIC_TYPE,
//             'DETAILS' => [
//                 'PHARMA_ID' => $row2->PHARMA_ID,
//                 'PHARMA_NAME' => $row2->ITEM_NAME,
//                 'ADDRESS' => $row2->ADDRESS,
//                 'CITY' => $row2->CITY,
//                 'DIST' => $row2->DIST,
//                 'STATE' => $row2->STATE,
//                 'PIN' => $row2->PIN,
//                 'PHOTO_URL' => $row2->PHOTO_URL,
//                 'CLINIC_TYPE' => $row2->CLINIC_TYPE,
//                 'CLINIC_MOBILE' => $row2->CLINIC_MOBILE,
//                 'LATITUDE' => $row2->LATITUDE,
//                 'LONGITUDE' => $row2->LONGITUDE,
//                 'KM' => $row2->KM,
//             ]
//         ]);
//     }

    //     // Query dashboard with selective columns
//     $data7 = DB::table('dashboard')
//         ->whereIn('DASH_SECTION_ID', ['B', 'C', 'D', 'G', 'H'])
//         ->where('STATUS', 'Active')
//         ->orderBy('DASH_TYPE')
//         ->select([
//             'DASH_ID', 'DASH_NAME', 'DASH_SECTION_ID','DASH_SECTION_NAME', 'DASH_TYPE', 'DASH_DESCRIPTION', 
//             'AGE_TYPE', 'FASTING', 'GENDER_TYPE', 'ID_PROOF', 'KNOWN_AS', 'PHOTO_URL', 
//             'PRESCRIPTION', 'QA1', 'QA2', 'QA3', 'QA4', 'QA5', 'QA6', 'REPORT_TIME', 
//             'POSITION', 'STATUS'
//         ])
//         ->get();

    //     foreach ($data7 as $row3) {
//         $data->push([
//             'ID' => $row3->DASH_ID,
//             'ITEM_NAME' => $row3->DASH_NAME,
//             'FIELD_TYPE' => $row3->DASH_SECTION_ID,
//             'CHECK_TYPE' => $row3->DASH_SECTION_ID,
//             'DETAILS' => [
//                 'AGE_TYPE' => $row3->AGE_TYPE,
//                 'DESCRIPTION' => $row3->DASH_DESCRIPTION,
//                 'FASTING' => $row3->FASTING,
//                 'GENDER_TYPE' => $row3->GENDER_TYPE,
//                 'DASH_ID' => $row3->DASH_ID,
//                 'ID_PROOF' => $row3->ID_PROOF,
//                 'DASH_NAME' => $row3->DASH_NAME,
//                 'DASH_TYPE' => $row3->DASH_TYPE,
//                 'KNOWN_AS' => $row3->KNOWN_AS,
//                 'PHOTO_URL' => $row3->PHOTO_URL,
//                 'PRESCRIPTION' => $row3->PRESCRIPTION,
//                 'QA1' => $row3->QA1,
//                 'QA2' => $row3->QA2,
//                 'QA3' => $row3->QA3,
//                 'QA4' => $row3->QA4,
//                 'QA5' => $row3->QA5,
//                 'QA6' => $row3->QA6,
//                 'REPORT_TIME' => $row3->REPORT_TIME,
//                 'DASH_SECTION_ID' => $row3->DASH_SECTION_ID,
//                 'DASH_SECTION_NAME' => $row3->DASH_SECTION_NAME,
//                 'SECTION_SL' => $row3->POSITION,
//                 'STATUS' => $row3->STATUS,
//             ]
//         ]);
//     }

    //     // Query sym_organ_test with selective columns
//     $data8 = DB::table('sym_organ_test')
//         ->join('master_testdata', 'master_testdata.TEST_ID', '=', 'sym_organ_test.TEST_ID')
//         ->join('dashboard', 'dashboard.DASH_ID', '=', 'sym_organ_test.DASH_ID')
//         ->select([
//             'dashboard.DASH_ID',
//             'dashboard.DASH_NAME',
//             'dashboard.PHOTO_URL',
//             'dashboard.DASH_TYPE',
//             'master_testdata.TEST_ID',
//             'master_testdata.TEST_SL',
//             'master_testdata.TEST_NAME',
//             'master_testdata.TEST_CODE',
//             'master_testdata.TEST_SAMPLE',
//             'master_testdata.TEST_CATG',
//             'master_testdata.DEPARTMENT',
//             'master_testdata.TEST_DESC',
//             'master_testdata.KNOWN_AS',
//             'master_testdata.FASTING',
//             'master_testdata.GENDER_TYPE',
//             'master_testdata.AGE_TYPE',
//             'master_testdata.REPORT_TIME',
//             'master_testdata.PRESCRIPTION',
//             'master_testdata.ID_PROOF',
//             'master_testdata.QA1',
//             'master_testdata.QA2',
//             'master_testdata.QA3',
//             'master_testdata.QA4',
//             'master_testdata.QA5',
//             'master_testdata.QA6'
//         ])
//         ->where([
//             ['dashboard.DASH_SECTION_ID', '=', 'S'],
//             ['dashboard.STATUS', '=', 'Active']
//         ])
//         ->orderBy('dashboard.POSITION')
//         ->get();

    //     $testDetails = [];
//     foreach ($data8 as $item) {
//         if (!isset($testDetails[$item->DASH_ID])) {
//             $testDetails[$item->DASH_ID] = [
//                 'ID' => $item->DASH_ID,
//                 'ITEM_NAME' => $item->DASH_NAME,
//                 'CHECK_TYPE' => $item->DASH_TYPE,
//                 'FIELD_TYPE' => $item->DASH_TYPE,
//                 'DETAILS' => []
//             ];
//         }
//         $testDetails[$item->DASH_ID]['DETAILS'][] = [
//             "TEST_ID" => $item->TEST_ID,
//             "TEST_SL" => $item->TEST_SL,
//             "TEST_NAME" => $item->TEST_NAME,
//             "TEST_CODE" => $item->TEST_CODE,
//             "TEST_SAMPLE" => $item->TEST_SAMPLE,
//             "TEST_CATG" => $item->TEST_CATG,
//             "DEPARTMENT" => $item->DEPARTMENT,
//             "TEST_DESC" => $item->TEST_DESC,
//             "KNOWN_AS" => $item->KNOWN_AS,
//             "FASTING" => $item->FASTING,
//             "GENDER_TYPE" => $item->GENDER_TYPE,
//             "AGE_TYPE" => $item->AGE_TYPE,
//             "REPORT_TIME" => $item->REPORT_TIME,
//             "PRESCRIPTION" => $item->PRESCRIPTION,
//             "ID_PROOF" => $item->ID_PROOF,
//             "QA1" => $item->QA1,
//             "QA2" => $item->QA2,
//             "QA3" => $item->QA3,
//             "QA4" => $item->QA4,
//             "QA5" => $item->QA5,
//             "QA6" => $item->QA6
//         ];
//     }

    //     $data = $data->merge(array_values($testDetails));

    //     // Query facility_section with selective columns
//     $data10 = DB::table('facility_section')
//         ->where('DS_STATUS', 'Active')
//         ->orderBy('ID')
//         ->select([
//             'DS_DESCRIPTION', 'ID', 'DSIMG1', 'DSBNR1', 'DASH_SECTION_ID', 'DASH_SECTION_NAME', 
//             'DS_POSITION', 'DS_STATUS', 'DSQA1', 'DSQA2', 'DSQA3', 'DSQA4', 'DSQA5', 'DSQA6', 
//             'DSQA7', 'DSQA8', 'DSQA9'
//         ])
//         ->get();

    //     foreach ($data10 as $row3) {
//         $pkgdtl = [
//             "DESCRIPTION" => $row3->DS_DESCRIPTION,
//             "ID" => $row3->ID,
//             "PHOTO_URL" => $row3->DSIMG1,
//             "DSM_PHOTO_URL" => $row3->DSBNR1,
//             "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
//             "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
//             "POSITION" => $row3->DS_POSITION,
//             "STATUS" => $row3->DS_STATUS,
//         ];

    //         if ($row3->DASH_SECTION_ID == 'SR') {
//             $pkgdtl['Questions'] = [
//                 [
//                     "QA1" => $row3->DSQA1,
//                     "QA2" => $row3->DSQA2,
//                     "QA3" => $row3->DSQA3,
//                     "QA4" => $row3->DSQA4,
//                     "QA5" => $row3->DSQA5,
//                     "QA6" => $row3->DSQA6,
//                     "QA7" => $row3->DSQA7,
//                     "QA8" => $row3->DSQA8,
//                     "QA9" => $row3->DSQA9,
//                 ]
//             ];
//         } else {
//             $pkgdtl['QA1'] = $row3->DSQA1;
//             $pkgdtl['QA2'] = $row3->DSQA2;
//             $pkgdtl['QA3'] = $row3->DSQA3;
//             $pkgdtl['QA4'] = $row3->DSQA4;
//             $pkgdtl['QA5'] = $row3->DSQA5;
//             $pkgdtl['QA6'] = $row3->DSQA6;
//         }

    //         $data->push(
//             [
//                 "ID" => $row3->DASH_SECTION_ID,
//                 "ITEM_NAME" => $row3->DASH_SECTION_NAME,
//                 "FIELD_TYPE" => '',
//                 'CHECK_TYPE' => 'Facility_Section',
//                 "DETAILS" => $pkgdtl
//             ]
//         );
//     }

    //     // Query facility_type with selective columns
//     $data11 =  DB::table('facility_type')
//     ->join('facility_section', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
//         ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active'])
//         ->whereIn('facility_type.DASH_SECTION_ID', ['AG', 'AH', 'AI', 'AL', 'AP', 'SR', 'AM'])
//         ->orderby('facility_type.DASH_TYPE_ID')
//         ->select([
//             'facility_type.DASH_TYPE_ID', 'facility_type.DASH_TYPE', 'facility_type.DT_DESCRIPTION', 
//             'facility_type.DT_BANNER_URL', 'facility_type.DASH_SECTION_ID', 'facility_section.DASH_SECTION_NAME', 
//             'facility_type.DT_POSITION', 'facility_type.DT_STATUS', 'facility_type.DTQA1', 'facility_type.DTQA2', 
//             'facility_type.DTQA3', 'facility_type.DTQA4', 'facility_type.DTQA5', 'facility_type.DTQA6', 
//             'facility_type.DTQA7', 'facility_type.DTQA8', 'facility_type.DTQA9'
//         ])
//         ->get();

    //     foreach ($data11 as $row3) {
//         if ($row3->DASH_SECTION_ID == 'AG') {
//             $row3->DASH_TYPE = '24x7 ' . $row3->DASH_TYPE;
//         }
//         $pkgdtl = [
//             "DASH_TYPE_ID" => $row3->DASH_TYPE_ID,
//             "DASH_TYPE" => $row3->DASH_TYPE,
//             "DESCRIPTION" => $row3->DT_DESCRIPTION,
//             "BANNER_URL" => $row3->DT_BANNER_URL,
//             "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
//             "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
//             "POSITION" => $row3->DT_POSITION,
//             "STATUS" => $row3->DT_STATUS,
//         ];


    //             $pkgdtl['Questions'] = [
//                 [
//                     "QA1" => $row3->DTQA1,
//                     "QA2" => $row3->DTQA2,
//                     "QA3" => $row3->DTQA3,
//                     "QA4" => $row3->DTQA4,
//                     "QA5" => $row3->DTQA5,
//                     "QA6" => $row3->DTQA6,
//                     "QA7" => $row3->DTQA7,
//                     "QA8" => $row3->DTQA8,
//                     "QA9" => $row3->DTQA9,
//                 ]
//             ];


    //             $data->push( [
//             "ID" => $row3->DASH_TYPE_ID,
//             "ITEM_NAME" => $row3->DASH_TYPE,
//             "FIELD_TYPE" => $row3->DASH_SECTION_NAME,
//             'CHECK_TYPE' => 'Facility_Type',
//             "DETAILS" => $pkgdtl
//         ]);
//     }

    //     // Query facility with selective columns
//     $data12 = DB::table('facility')
//     ->join('facility_type', 'facility.DASH_TYPE_ID', '=', 'facility_type.DASH_TYPE_ID')
//     ->where(['facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
//         ->orderby('DASH_ID')
//         ->select([
//             'facility.DASH_ID', 'facility.DASH_NAME', 'facility.DN_DESCRIPTION', 'facility.DN_BANNER_URL', 
//             'facility.DASH_TYPE_ID', 'facility_type.DASH_TYPE', 'facility_type.DASH_SECTION_ID', 'facility.DN_TAG_SECTION', 
//             'facility.DN_POSITION', 'facility.DN_STATUS', 'facility.DNQA1', 'facility.DNQA2', 'facility.DNQA3', 
//             'facility.DNQA4', 'facility.DNQA5', 'facility.DNQA6', 'facility.DNQA7', 'facility.DNQA8', 'facility.DNQA9'
//         ])
//         ->get();

    //     foreach ($data12 as $row3) {
//         if ($row3->DASH_SECTION_ID == 'AG') {
//             $row3->DASH_NAME = '24x7 ' . $row3->DASH_NAME;
//             $row3->DASH_TYPE = '24x7 ' . $row3->DASH_TYPE;
//         }
//         $pkgdtl = [
//             "DASH_ID" => $row3->DASH_ID,
//             "DASH_NAME" => $row3->DASH_NAME,
//             "DESCRIPTION" => $row3->DN_DESCRIPTION,
//             "BANNER_URL" => $row3->DN_BANNER_URL,
//             "DASH_TYPE_ID" => $row3->DASH_TYPE_ID,
//             "DASH_TYPE" => $row3->DASH_TYPE,
//             "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
//             "DN_TAG_SECTION" => $row3->DN_TAG_SECTION,
//             "POSITION" => $row3->DN_POSITION,
//             "STATUS" => $row3->DN_STATUS,
//         ];



    //             $pkgdtl['Questions'] = [
//                 [
//                     "QA1" => $row3->DNQA1,
//                     "QA2" => $row3->DNQA2,
//                     "QA3" => $row3->DNQA3,
//                     "QA4" => $row3->DNQA4,
//                     "QA5" => $row3->DNQA5,
//                     "QA6" => $row3->DNQA6,
//                     "QA7" => $row3->DNQA7,
//                     "QA8" => $row3->DNQA8,
//                     "QA9" => $row3->DNQA9,
//                 ]
//             ];

    //             $data->push([
//             "ID" => $row3->DASH_ID,
//             "ITEM_NAME" => $row3->DASH_NAME,
//             "FIELD_TYPE" => $row3->DASH_TYPE,
//             'CHECK_TYPE' => 'Facility',
//             "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
//             "DETAILS" => $pkgdtl
//         ]);
//     }

    //     if ($data->isEmpty()) {
//         $response = ['Success' => false, 'Message' => 'No Search data found', 'code' => 200];
//     } else {
//         $response = ['Success' => true, 'data' => $data, 'code' => 200];
//     }

    //     return response()->json($response);
// }



    function globalscrh_dummy(Request $req)
    {
        // Simplified request method check
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return response()->json(['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200]);
        }

        $input = $req->json()->all();
        if (!isset($input['LATITUDE']) || !isset($input['LONGITUDE'])) {
            return response()->json(['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422]);
        }

        $latt = $input['LATITUDE'];
        $lont = $input['LONGITUDE'];

        // // Generate a unique cache key
        // $cacheKey = "globalscrh_dummy_{$latt}_{$lont}";

        // // Check if data exists in cache
        // $data = Cache::remember($cacheKey, now()->addMinutes(1), function () use ($latt, $lont, $cacheKey) {
        //     Log::info("Cache miss for key: {$cacheKey}");
        //     $data = collect();
        //         // Query doctors with selective columns and combined query
        //     return $data;
        // });

        $data = collect();

        $data1 = DB::table('drprofile')
            ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
            ->where('drprofile.APPROVE', 'true')
            ->where('dr_availablity.SCH_STATUS', '!=', 'NA')
            ->select([
                'drprofile.DR_ID',
                'drprofile.DR_NAME',
                'drprofile.DR_MOBILE',
                'drprofile.SEX',
                'drprofile.DESIGNATION',
                'drprofile.QUALIFICATION',
                'drprofile.D_CATG',
                'drprofile.EXPERIENCE',
                'drprofile.PHOTO_URL',
            ])
            ->distinct()
            ->get();

        foreach ($data1 as $row1) {
            $data->push([
                'ID' => $row1->DR_ID,
                'ITEM_NAME' => $row1->DR_NAME,
                'FIELD_TYPE' => 'Doctor',
                'CHECK_TYPE' => 'Doctor',
                'DETAILS' => [
                    'DR_ID' => $row1->DR_ID,
                    'DR_NAME' => $row1->DR_NAME,
                    'DR_MOBILE' => $row1->DR_MOBILE,
                    'SEX' => $row1->SEX,
                    'DESIGNATION' => $row1->DESIGNATION,
                    'QUALIFICATION' => $row1->QUALIFICATION,
                    'D_CATG' => $row1->D_CATG,
                    'EXPERIENCE' => $row1->EXPERIENCE,
                    'DR_PHOTO' => $row1->PHOTO_URL,
                ]
            ]);
        }

        // Query specialists with selective columns
        $data2 = DB::table('dis_catg')
            ->select('DIS_ID', 'SPECIALIST', 'SPECIALITY', 'DIS_CATEGORY')
            ->get();
        foreach ($data2 as $row3) {
            $data->push([
                'ID' => $row3->DIS_ID,
                'ITEM_NAME' => $row3->SPECIALIST,
                'ITEM_NAME1' => $row3->SPECIALITY,
                'CHECK_TYPE' => 'Specialist',
                'FIELD_TYPE' => 'Specialist',
                'DETAILS' => [
                    'DIS_ID' => $row3->DIS_ID,
                    'D_CATG' => $row3->DIS_CATEGORY,
                ]
            ]);
        }

        // Query symptoms with selective columns
        $data3 = DB::table('symptoms')
            ->select('DIS_ID', 'SYM_NAME', 'DIS_CATEGORY', 'SYM_ID', 'DESCRIPTION')
            ->get();
        foreach ($data3 as $row4) {
            $data->push([
                'ID' => $row4->DIS_ID,
                'ITEM_NAME' => $row4->SYM_NAME,
                'FIELD_TYPE' => 'Symptom',
                'CHECK_TYPE' => 'Symptom',
                'DETAILS' => [
                    'DIS_ID' => $row4->DIS_ID,
                    'D_CATG' => $row4->DIS_CATEGORY,
                    'SYM_ID' => $row4->SYM_ID,
                    'DESCRIPTION' => $row4->DESCRIPTION,
                ]
            ]);
        }

        // Query master_testdata with selective columns
        $data5 = DB::table('master_testdata')
            ->select([
                'TEST_ID',
                'TEST_NAME',
                'TEST_CODE',
                'TEST_SAMPLE',
                'TEST_CATG',
                'ORGAN_ID',
                'ORGAN_NAME',
                'ORGAN_URL',
                'DEPARTMENT',
                'TEST_DESC',
                'KNOWN_AS',
                'FASTING',
                'GENDER_TYPE',
                'AGE_TYPE',
                'REPORT_TIME',
                'PRESCRIPTION',
                'ID_PROOF',
                'QA1',
                'QA2',
                'QA3',
                'QA4',
                'QA5',
                'QA6'
            ])
            ->get();
        foreach ($data5 as $row1) {
            $data->push([
                'ID' => $row1->TEST_ID,
                'ITEM_NAME' => $row1->TEST_NAME,
                'FIELD_TYPE' => $row1->DEPARTMENT,
                'CHECK_TYPE' => $row1->DEPARTMENT,
                'DETAILS' => [
                    'TEST_ID' => $row1->TEST_ID,
                    'TEST_NAME' => $row1->TEST_NAME,
                    'TEST_CODE' => $row1->TEST_CODE,
                    'TEST_SAMPLE' => $row1->TEST_SAMPLE,
                    'TEST_CATG' => $row1->TEST_CATG,
                    'ORGAN_ID' => $row1->ORGAN_ID,
                    'ORGAN_NAME' => $row1->ORGAN_NAME,
                    'ORGAN_URL' => $row1->ORGAN_URL,
                    'DEPARTMENT' => $row1->DEPARTMENT,
                    'TEST_DESC' => $row1->TEST_DESC,
                    'KNOWN_AS' => $row1->KNOWN_AS,
                    'FASTING' => $row1->FASTING,
                    'GENDER_TYPE' => $row1->GENDER_TYPE,
                    'AGE_TYPE' => $row1->AGE_TYPE,
                    'REPORT_TIME' => $row1->REPORT_TIME,
                    'PRESCRIPTION' => $row1->PRESCRIPTION,
                    'ID_PROOF' => $row1->ID_PROOF,
                    'QA1' => $row1->QA1,
                    'QA2' => $row1->QA2,
                    'QA3' => $row1->QA3,
                    'QA4' => $row1->QA4,
                    'QA5' => $row1->QA5,
                    'QA6' => $row1->QA6,
                ]
            ]);
        }

        // Query nearby pharmacies with selective columns
        $data6 = DB::table('pharmacy')
            // ->select([
            //     'pharmacy.PHARMA_ID', 'pharmacy.ITEM_NAME', 'pharmacy.ADDRESS', 'pharmacy.CITY', 'pharmacy.DIST', 'pharmacy.STATE', 'pharmacy.PIN', 
            //     'pharmacy.PHOTO_URL', 'pharmacy.CLINIC_TYPE', 'pharmacy.CLINIC_MOBILE', 'pharmacy.LATITUDE', 'pharmacy.LONGITUDE',
            //     DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
            //     * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
            //     * SIN(RADIANS('$latt'))))),2) as KM")
            // ])
            ->select([
                'pharmacy.*',
                DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
            * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
            * SIN(RADIANS('$latt'))))),2) as KM")
            ])
            ->take(25)
            ->get();

        foreach ($data6 as $row2) {
            $data->push([
                'ID' => $row2->PHARMA_ID,
                'ITEM_NAME' => $row2->ITEM_NAME,
                'FIELD_TYPE' => $row2->CLINIC_TYPE,
                'CHECK_TYPE' => $row2->CLINIC_TYPE,
                'DETAILS' => [
                    'PHARMA_ID' => $row2->PHARMA_ID,
                    'PHARMA_NAME' => $row2->ITEM_NAME,
                    'ADDRESS' => $row2->ADDRESS,
                    'CITY' => $row2->CITY,
                    'DIST' => $row2->DIST,
                    'STATE' => $row2->STATE,
                    'PIN' => $row2->PIN,
                    'PHOTO_URL' => $row2->PHOTO_URL,
                    'CLINIC_TYPE' => $row2->CLINIC_TYPE,
                    'CLINIC_MOBILE' => $row2->CLINIC_MOBILE,
                    'LATITUDE' => $row2->LATITUDE,
                    'LONGITUDE' => $row2->LONGITUDE,
                    'KM' => $row2->KM,
                ]
            ]);
        }

        // Query dashboard with selective columns
        $data7 = DB::table('dashboard')
            ->whereIn('DASH_SECTION_ID', ['B', 'C', 'D', 'G', 'H'])
            ->where('STATUS', 'Active')
            ->orderBy('DASH_TYPE')
            ->select([
                'DASH_ID',
                'DASH_NAME',
                'DASH_SECTION_ID',
                'DASH_SECTION_NAME',
                'DASH_TYPE',
                'DASH_DESCRIPTION',
                'AGE_TYPE',
                'FASTING',
                'GENDER_TYPE',
                'ID_PROOF',
                'KNOWN_AS',
                'PHOTO_URL',
                'PRESCRIPTION',
                'QA1',
                'QA2',
                'QA3',
                'QA4',
                'QA5',
                'QA6',
                'REPORT_TIME',
                'POSITION',
                'STATUS'
            ])
            ->get();

        foreach ($data7 as $row3) {
            $data->push([
                'ID' => $row3->DASH_ID,
                'ITEM_NAME' => $row3->DASH_NAME,
                'FIELD_TYPE' => $row3->DASH_TYPE,
                'CHECK_TYPE' => $row3->DASH_SECTION_ID,
                'DETAILS' => [
                    'AGE_TYPE' => $row3->AGE_TYPE,
                    'DESCRIPTION' => $row3->DASH_DESCRIPTION,
                    'FASTING' => $row3->FASTING,
                    'GENDER_TYPE' => $row3->GENDER_TYPE,
                    'DASH_ID' => $row3->DASH_ID,
                    'ID_PROOF' => $row3->ID_PROOF,
                    'DASH_NAME' => $row3->DASH_NAME,
                    'DASH_TYPE' => $row3->DASH_TYPE,
                    'KNOWN_AS' => $row3->KNOWN_AS,
                    'PHOTO_URL' => $row3->PHOTO_URL,
                    'PRESCRIPTION' => $row3->PRESCRIPTION,
                    'QA1' => $row3->QA1,
                    'QA2' => $row3->QA2,
                    'QA3' => $row3->QA3,
                    'QA4' => $row3->QA4,
                    'QA5' => $row3->QA5,
                    'QA6' => $row3->QA6,
                    'REPORT_TIME' => $row3->REPORT_TIME,
                    'DASH_SECTION_ID' => $row3->DASH_SECTION_ID,
                    'DASH_SECTION_NAME' => $row3->DASH_SECTION_NAME,
                    'SECTION_SL' => $row3->POSITION,
                    'STATUS' => $row3->STATUS,
                ]
            ]);
        }

        // Query sym_organ_test with selective columns
        $data8 = DB::table('sym_organ_test')
            ->join('master_testdata', 'master_testdata.TEST_ID', '=', 'sym_organ_test.TEST_ID')
            ->join('dashboard', 'dashboard.DASH_ID', '=', 'sym_organ_test.DASH_ID')
            ->select([
                'dashboard.DASH_ID',
                'dashboard.DASH_NAME',
                'dashboard.PHOTO_URL',
                'dashboard.DASH_TYPE',
                'master_testdata.TEST_ID',
                'master_testdata.TEST_SL',
                'master_testdata.TEST_NAME',
                'master_testdata.TEST_CODE',
                'master_testdata.TEST_SAMPLE',
                'master_testdata.TEST_CATG',
                'master_testdata.DEPARTMENT',
                'master_testdata.TEST_DESC',
                'master_testdata.KNOWN_AS',
                'master_testdata.FASTING',
                'master_testdata.GENDER_TYPE',
                'master_testdata.AGE_TYPE',
                'master_testdata.REPORT_TIME',
                'master_testdata.PRESCRIPTION',
                'master_testdata.ID_PROOF',
                'master_testdata.QA1',
                'master_testdata.QA2',
                'master_testdata.QA3',
                'master_testdata.QA4',
                'master_testdata.QA5',
                'master_testdata.QA6'
            ])
            ->where([
                ['dashboard.DASH_SECTION_ID', '=', 'S'],
                ['dashboard.STATUS', '=', 'Active']
            ])
            ->orderBy('dashboard.POSITION')
            ->get();

        $testDetails = [];
        foreach ($data8 as $item) {
            if (!isset($testDetails[$item->DASH_ID])) {
                $testDetails[$item->DASH_ID] = [
                    'ID' => $item->DASH_ID,
                    'ITEM_NAME' => $item->DASH_NAME,
                    'CHECK_TYPE' => $item->DASH_TYPE,
                    'FIELD_TYPE' => $item->DASH_TYPE,
                    'DETAILS' => []
                ];
            }
            $testDetails[$item->DASH_ID]['DETAILS'][] = [
                "TEST_ID" => $item->TEST_ID,
                "TEST_SL" => $item->TEST_SL,
                "TEST_NAME" => $item->TEST_NAME,
                "TEST_CODE" => $item->TEST_CODE,
                "TEST_SAMPLE" => $item->TEST_SAMPLE,
                "TEST_CATG" => $item->TEST_CATG,
                "DEPARTMENT" => $item->DEPARTMENT,
                "TEST_DESC" => $item->TEST_DESC,
                "KNOWN_AS" => $item->KNOWN_AS,
                "FASTING" => $item->FASTING,
                "GENDER_TYPE" => $item->GENDER_TYPE,
                "AGE_TYPE" => $item->AGE_TYPE,
                "REPORT_TIME" => $item->REPORT_TIME,
                "PRESCRIPTION" => $item->PRESCRIPTION,
                "ID_PROOF" => $item->ID_PROOF,
                "QA1" => $item->QA1,
                "QA2" => $item->QA2,
                "QA3" => $item->QA3,
                "QA4" => $item->QA4,
                "QA5" => $item->QA5,
                "QA6" => $item->QA6
            ];
        }

        $data = $data->merge(array_values($testDetails));

        // Query facility_section with selective columns
        $data10 = DB::table('facility_section')
            ->where('DS_STATUS', 'Active')
            ->orderBy('ID')
            // ->select([
            //     'DS_DESCRIPTION', 'ID', 'DSIMG1', 'DSBNR1', 'DASH_SECTION_ID', 'DASH_SECTION_NAME', 
            //     'DS_POSITION', 'DS_STATUS', 'DSQA1', 'DSQA2', 'DSQA3', 'DSQA4', 'DSQA5', 'DSQA6', 
            //     'DSQA7', 'DSQA8', 'DSQA9'
            // ])
            ->get();

        foreach ($data10 as $row3) {
            $pkgdtl = [
                "DESCRIPTION" => $row3->DS_DESCRIPTION,
                "ID" => $row3->ID,
                // "PHOTO_URL" => $row3->DSIMG1,
                // "DSM_PHOTO_URL" => $row3->DSBNR1,
                "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
                "POSITION" => $row3->DS_POSITION,
                "STATUS" => $row3->DS_STATUS,
                "PHOTO_URL1" => $row3->DSIMG1,
                "PHOTO_URL2" => $row3->DSIMG2,
                "PHOTO_URL3" => $row3->DSIMG3,
                "PHOTO_URL4" => $row3->DSIMG4,
                "PHOTO_URL5" => $row3->DSIMG5,
                "PHOTO_URL6" => $row3->DSIMG6,
                "PHOTO_URL7" => $row3->DSIMG7,
                "PHOTO_URL8" => $row3->DSIMG8,
                "PHOTO_URL9" => $row3->DSIMG9,
                "PHOTO_URL10" => $row3->DSIMG10,
                "BANNER_URL1" => $row3->DSBNR1,
                "BANNER_URL2" => $row3->DSBNR2,
                "BANNER_URL3" => $row3->DSBNR3,
                "BANNER_URL4" => $row3->DSBNR4,
                "BANNER_URL5" => $row3->DSBNR5,
                "BANNER_URL6" => $row3->DSBNR6,
                "BANNER_URL7" => $row3->DSBNR7,
                "BANNER_URL8" => $row3->DSBNR8,
                "BANNER_URL9" => $row3->DSBNR9,
                "BANNER_URL10" => $row3->DSBNR10,
            ];

            if ($row3->DASH_SECTION_ID == 'SR') {
                $pkgdtl['Questions'] = [
                    [
                        "QA1" => $row3->DSQA1,
                        "QA2" => $row3->DSQA2,
                        "QA3" => $row3->DSQA3,
                        "QA4" => $row3->DSQA4,
                        "QA5" => $row3->DSQA5,
                        "QA6" => $row3->DSQA6,
                        "QA7" => $row3->DSQA7,
                        "QA8" => $row3->DSQA8,
                        "QA9" => $row3->DSQA9,
                    ]
                ];
            } else {
                $pkgdtl['QA1'] = $row3->DSQA1;
                $pkgdtl['QA2'] = $row3->DSQA2;
                $pkgdtl['QA3'] = $row3->DSQA3;
                $pkgdtl['QA4'] = $row3->DSQA4;
                $pkgdtl['QA5'] = $row3->DSQA5;
                $pkgdtl['QA6'] = $row3->DSQA6;
            }

            $data->push(
                [
                    "ID" => $row3->DASH_SECTION_ID,
                    "ITEM_NAME" => $row3->DASH_SECTION_NAME,
                    "FIELD_TYPE" => '',
                    'CHECK_TYPE' => 'Facility_Section',
                    "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                    "DETAILS" => $pkgdtl
                ]
            );
        }

        // // Query facility_type with selective columns
        // $data11 =  DB::table('facility_type')
        // ->join('facility_section', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
        //     ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active'])
        //     ->whereIn('facility_type.DASH_SECTION_ID', ['AG', 'AH', 'AI', 'AL', 'AP', 'SR', 'AM'])
        //     ->orderby('facility_type.DASH_TYPE_ID')
        //     ->select([
        //         'facility_type.DASH_TYPE_ID', 'facility_type.DASH_TYPE', 'facility_type.DT_DESCRIPTION', 
        //         'facility_type.DT_BANNER_URL', 'facility_type.DASH_SECTION_ID', 'facility_section.DASH_SECTION_NAME', 
        //         'facility_type.DT_POSITION', 'facility_type.DT_STATUS', 'facility_type.DTQA1', 'facility_type.DTQA2', 
        //         'facility_type.DTQA3', 'facility_type.DTQA4', 'facility_type.DTQA5', 'facility_type.DTQA6', 
        //         'facility_type.DTQA7', 'facility_type.DTQA8', 'facility_type.DTQA9'
        //     ])
        //     ->get();

        // foreach ($data11 as $row3) {
        //     if ($row3->DASH_SECTION_ID == 'AG') {
        //         $row3->DASH_TYPE = '24x7 ' . $row3->DASH_TYPE;
        //     }
        //     $pkgdtl = [
        //         "DASH_TYPE_ID" => $row3->DASH_TYPE_ID,
        //         "DASH_TYPE" => $row3->DASH_TYPE,
        //         "DESCRIPTION" => $row3->DT_DESCRIPTION,
        //         "BANNER_URL" => $row3->DT_BANNER_URL,
        //         "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
        //         "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
        //         "POSITION" => $row3->DT_POSITION,
        //         "STATUS" => $row3->DT_STATUS,
        //     ];


        //         $pkgdtl['Questions'] = [
        //             [
        //                 "QA1" => $row3->DTQA1,
        //                 "QA2" => $row3->DTQA2,
        //                 "QA3" => $row3->DTQA3,
        //                 "QA4" => $row3->DTQA4,
        //                 "QA5" => $row3->DTQA5,
        //                 "QA6" => $row3->DTQA6,
        //                 "QA7" => $row3->DTQA7,
        //                 "QA8" => $row3->DTQA8,
        //                 "QA9" => $row3->DTQA9,
        //             ]
        //         ];


        //         $data->push( [
        //         "ID" => $row3->DASH_TYPE_ID,
        //         "ITEM_NAME" => $row3->DASH_TYPE,
        //         "FIELD_TYPE" => $row3->DASH_SECTION_NAME,
        //         "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
        //         'CHECK_TYPE' => 'Facility_Type',
        //         "DETAILS" => $pkgdtl
        //     ]);
        // }

        $data11 = DB::table('facility')
            ->join('facility_type', 'facility.DASH_TYPE_ID', '=', 'facility_type.DASH_TYPE_ID')
            ->join('facility_section', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
            ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
            ->orderby('facility_type.DT_POSITION')
            ->orderby('facility.DN_POSITION')
            ->get();

        $sections = [];

        foreach ($data11 as $row3) {
            if ($row3->DASH_SECTION_ID == 'AG') {
                $row3->DASH_TYPE = '24x7 ' . $row3->DASH_TYPE;
                $row3->DASH_NAME = '24x7 ' . $row3->DASH_NAME;
            }
            if ($row3->DASH_SECTION_ID == 'SR') {
                continue;
            }

            if (!isset($sections[$row3->DASH_SECTION_ID])) {
                $sections[$row3->DASH_SECTION_ID] = [
                    "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                    "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
                    "DASH_TYPES" => []
                ];
            }

            if (!isset($sections[$row3->DASH_SECTION_ID]['DASH_TYPES'][$row3->DASH_TYPE_ID])) {
                $sections[$row3->DASH_SECTION_ID]['DASH_TYPES'][$row3->DASH_TYPE_ID] = [
                    "DASH_TYPE_ID" => $row3->DASH_TYPE_ID,
                    "DASH_TYPE" => $row3->DASH_TYPE,
                    "DESCRIPTION" => $row3->DT_DESCRIPTION,
                    "PHOTO_URL1" => $row3->DTIMG1,
                    "PHOTO_URL2" => $row3->DSIMG2,
                    "PHOTO_URL3" => $row3->DTIMG3,
                    "PHOTO_URL4" => $row3->DTIMG4,
                    "PHOTO_URL5" => $row3->DTIMG5,
                    "PHOTO_URL6" => $row3->DTIMG6,
                    "PHOTO_URL7" => $row3->DTIMG7,
                    "PHOTO_URL8" => $row3->DTIMG8,
                    "PHOTO_URL9" => $row3->DTIMG9,
                    "PHOTO_URL10" => $row3->DTIMG10,
                    "BANNER_URL1" => $row3->DTBNR1,
                    "BANNER_URL2" => $row3->DTBNR2,
                    "BANNER_URL3" => $row3->DTBNR3,
                    "BANNER_URL4" => $row3->DTBNR4,
                    "BANNER_URL5" => $row3->DTBNR5,
                    "BANNER_URL6" => $row3->DTBNR6,
                    "BANNER_URL7" => $row3->DTBNR7,
                    "BANNER_URL8" => $row3->DTBNR8,
                    "BANNER_URL9" => $row3->DTBNR9,
                    "BANNER_URL10" => $row3->DTBNR10,
                    "FACILITY_DETAILS" => []
                ];
            }

            $facilityDetail = [
                "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
                "DASH_TYPE_ID" => $row3->DASH_TYPE_ID,
                "DASH_TYPE" => $row3->DASH_TYPE,
                "DASH_ID" => $row3->DASH_ID,
                "DASH_NAME" => $row3->DASH_NAME,
                "DESCRIPTION" => $row3->DN_DESCRIPTION,
                "PHOTO_URL1" => $row3->DNIMG1,
                "PHOTO_URL2" => $row3->DNIMG2,
                "PHOTO_URL3" => $row3->DNIMG3,
                "PHOTO_URL4" => $row3->DNIMG4,
                "PHOTO_URL5" => $row3->DNIMG5,
                "PHOTO_URL6" => $row3->DNIMG6,
                "PHOTO_URL7" => $row3->DNIMG7,
                "PHOTO_URL8" => $row3->DNIMG8,
                "PHOTO_URL9" => $row3->DNIMG9,
                "PHOTO_URL10" => $row3->DNIMG10,
                "BANNER_URL1" => $row3->DNBNR1,
                "BANNER_URL2" => $row3->DNBNR2,
                "BANNER_URL3" => $row3->DNBNR3,
                "BANNER_URL4" => $row3->DNBNR4,
                "BANNER_URL5" => $row3->DNBNR5,
                "BANNER_URL6" => $row3->DNBNR6,
                "BANNER_URL7" => $row3->DNBNR7,
                "BANNER_URL8" => $row3->DNBNR8,
                "BANNER_URL9" => $row3->DNBNR9,
                "BANNER_URL10" => $row3->DNBNR10,
                "Questions" => [
                    [
                        "QA1" => $row3->DNQA1,
                        "QA2" => $row3->DNQA2,
                        "QA3" => $row3->DNQA3,
                        "QA4" => $row3->DNQA4,
                        "QA5" => $row3->DNQA5,
                        "QA6" => $row3->DNQA6,
                        "QA7" => $row3->DNQA7,
                        "QA8" => $row3->DNQA8,
                        "QA9" => $row3->DNQA9
                    ]
                ]
            ];

            $sections[$row3->DASH_SECTION_ID]['DASH_TYPES'][$row3->DASH_TYPE_ID]['FACILITY_DETAILS'][] = $facilityDetail;
        }

        foreach ($sections as $section) {
            foreach ($section['DASH_TYPES'] as $type) {
                $data->push([
                    "ID" => $type['DASH_TYPE_ID'],
                    "ITEM_NAME" => $type['DASH_TYPE'],
                    "FIELD_TYPE" => $section['DASH_SECTION_NAME'],
                    "DASH_SECTION_ID" => $section['DASH_SECTION_ID'],
                    'CHECK_TYPE' => 'Facility_Type',
                    "DETAILS" => $type
                ]);

            }
        }

        // Query facility with selective columns
        // $data12 = DB::table('facility')
        // ->join('facility_type', 'facility.DASH_TYPE_ID', '=', 'facility_type.DASH_TYPE_ID')
        // ->where(['facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
        //     ->orderby('DASH_ID')
        //     ->select([
        //         'facility.DASH_ID', 'facility.DASH_NAME', 'facility.DN_DESCRIPTION', 'facility.DN_BANNER_URL', 
        //         'facility.DASH_TYPE_ID', 'facility_type.DASH_TYPE', 'facility_type.DASH_SECTION_ID', 'facility.DN_TAG_SECTION', 
        //         'facility.DN_POSITION', 'facility.DN_STATUS', 'facility.DNQA1', 'facility.DNQA2', 'facility.DNQA3', 
        //         'facility.DNQA4', 'facility.DNQA5', 'facility.DNQA6', 'facility.DNQA7', 'facility.DNQA8', 'facility.DNQA9'
        //     ])
        //     ->get();

        foreach ($data11 as $row3) {

            $pkgdtl = [
                "DASH_ID" => $row3->DASH_ID,
                "DASH_NAME" => $row3->DASH_NAME,
                "DESCRIPTION" => $row3->DN_DESCRIPTION,
                "BANNER_URL" => $row3->DN_BANNER_URL,
                "DASH_TYPE_ID" => $row3->DASH_TYPE_ID,
                "DASH_TYPE" => $row3->DASH_TYPE,
                "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                "DN_TAG_SECTION" => $row3->DN_TAG_SECTION,
                "POSITION" => $row3->DN_POSITION,
                "STATUS" => $row3->DN_STATUS,
                "PHOTO_URL1" => $row3->DNIMG1,
                "PHOTO_URL2" => $row3->DNIMG2,
                "PHOTO_URL3" => $row3->DNIMG3,
                "PHOTO_URL4" => $row3->DNIMG4,
                "PHOTO_URL5" => $row3->DNIMG5,
                "PHOTO_URL6" => $row3->DNIMG6,
                "PHOTO_URL7" => $row3->DNIMG7,
                "PHOTO_URL8" => $row3->DNIMG8,
                "PHOTO_URL9" => $row3->DNIMG9,
                "PHOTO_URL10" => $row3->DNIMG10,
                "BANNER_URL1" => $row3->DNBNR1,
                "BANNER_URL2" => $row3->DNBNR2,
                "BANNER_URL3" => $row3->DNBNR3,
                "BANNER_URL4" => $row3->DNBNR4,
                "BANNER_URL5" => $row3->DNBNR5,
                "BANNER_URL6" => $row3->DNBNR6,
                "BANNER_URL7" => $row3->DNBNR7,
                "BANNER_URL8" => $row3->DNBNR8,
                "BANNER_URL9" => $row3->DNBNR9,
                "BANNER_URL10" => $row3->DNBNR10,
            ];



            $pkgdtl['Questions'] = [
                [
                    "QA1" => $row3->DNQA1,
                    "QA2" => $row3->DNQA2,
                    "QA3" => $row3->DNQA3,
                    "QA4" => $row3->DNQA4,
                    "QA5" => $row3->DNQA5,
                    "QA6" => $row3->DNQA6,
                    "QA7" => $row3->DNQA7,
                    "QA8" => $row3->DNQA8,
                    "QA9" => $row3->DNQA9,
                ]
            ];

            $data->push([
                "ID" => $row3->DASH_ID,
                "ITEM_NAME" => $row3->DASH_NAME,
                "FIELD_TYPE" => $row3->DASH_TYPE,
                'CHECK_TYPE' => 'Facility',
                "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                "DETAILS" => $pkgdtl
            ]);
        }



        if ($data->isEmpty()) {
            $response = ['Success' => false, 'Message' => 'No Search data found', 'code' => 200];
        } else {
            $response = ['Success' => true, 'data' => $data, 'code' => 200];
        }

        return response()->json($response);
    }

    function doctrscrh_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $data1 = DB::table('drprofile')
                    ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
                    ->where(['drprofile.APPROVE' => 'true'])
                    ->where('dr_availablity.SCH_STATUS', '!=', 'NA')
                    ->select(
                        'drprofile.DR_ID',
                        'drprofile.DR_NAME',
                        'drprofile.DR_MOBILE',
                        'drprofile.SEX',
                        'drprofile.DESIGNATION',
                        'drprofile.QUALIFICATION',
                        'drprofile.D_CATG',
                        'drprofile.EXPERIENCE',
                        'drprofile.PHOTO_URL',
                        // 'dr_availablity.PHARMA_ID',
                        // 'dr_availablity.PHARMA_NAME',
                        // 'dr_availablity.DR_FEES'
                    )
                    ->distinct('drprofile.DR_ID', )
                    ->get();

                foreach ($data1 as $row1) {
                    $drdtl = [];
                    $drdtl['DETAILS'] = [
                        "DR_ID" => $row1->DR_ID,
                        "DR_NAME" => $row1->DR_NAME,
                        "DR_MOBILE" => $row1->DR_MOBILE,
                        "SEX" => $row1->SEX,
                        "DESIGNATION" => $row1->DESIGNATION,
                        "QUALIFICATION" => $row1->QUALIFICATION,
                        "D_CATG" => $row1->D_CATG,
                        "EXPERIENCE" => $row1->EXPERIENCE,
                        "DR_PHOTO" => $row1->PHOTO_URL,
                        // "PHARMA_ID" => $row1->PHARMA_ID,
                        // "PHARMA_NAME" => $row1->PHARMA_NAME,
                        // "DR_FEES" => $row1->DR_FEES,
                    ];

                    $data[] = [
                        "ID" => $row1->DR_ID,
                        "ITEM_NAME" => $row1->DR_NAME,
                        "FIELD_TYPE" => "Doctor",
                        "DETAILS" => $drdtl['DETAILS']
                    ];
                }

                $data2 = DB::table('dis_catg')->get();
                foreach ($data2 as $row3) {
                    $spdtl = [];
                    $spdtl['DETAILS'] = [
                        "DIS_ID" => $row3->DIS_ID,
                        "D_CATG" => $row3->DIS_CATEGORY,
                    ];

                    $sp = [
                        "ID" => $row3->DIS_ID,
                        "ITEM_NAME" => $row3->SPECIALIST,
                        "ITEM_NAME1" => $row3->SPECIALITY,
                        "FIELD_TYPE" => "Specialist",
                        "DETAILS" => $spdtl['DETAILS']
                    ];
                    array_push($data, $sp);
                }

                $data3 = DB::table('symptoms')->get();
                foreach ($data3 as $row4) {
                    $sydtl = [];
                    $sydtl['DETAILS'] = [
                        "DIS_ID" => $row4->DIS_ID,
                        "D_CATG" => $row4->DIS_CATEGORY,
                        "SYM_ID" => $row4->SYM_ID,
                        "DESCRIPTION" => $row4->DESCRIPTION,
                    ];

                    $sy = [
                        "ID" => $row4->DIS_ID,
                        "ITEM_NAME" => $row4->SYM_NAME,
                        "FIELD_TYPE" => "Symptom",
                        "DETAILS" => $sydtl['DETAILS']
                    ];
                    array_push($data, $sy);
                }

                if (empty($data)) {
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


    function testscrh_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = collect();

                $data1 = DB::table('master_testdata')->get();
                foreach ($data1 as $row1) {
                    $tstdtl = [];
                    $details = [
                        "TEST_ID" => $row1->TEST_ID,
                        "TEST_NAME" => $row1->TEST_NAME,
                        "TEST_CODE" => $row1->TEST_CODE,
                        "TEST_SAMPLE" => $row1->TEST_SAMPLE,
                        "TEST_CATG" => $row1->TEST_CATG,
                        "ORGAN_ID" => $row1->ORGAN_ID,
                        "ORGAN_NAME" => $row1->ORGAN_NAME,
                        "ORGAN_URL" => $row1->ORGAN_URL,
                        "DEPARTMENT" => $row1->DEPARTMENT,
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
                    ];
                    $data->push([
                        "ID" => $row1->TEST_ID,
                        "ITEM_NAME" => $row1->TEST_NAME,
                        "FIELD_TYPE" => $row1->DEPARTMENT,
                        "DETAILS" => $details
                    ]);
                }

                $data2 = DB::table('dashboard')
                    ->whereIn('DASH_SECTION_ID', ['B', 'C', 'D', 'G', 'H']) //T
                    ->where('STATUS', 'Active')
                    ->orderby('DASH_TYPE')
                    ->get();

                foreach ($data2 as $row3) {
                    $details = [
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

                    $data->push([
                        "ID" => $row3->DASH_ID,
                        "ITEM_NAME" => $row3->DASH_NAME,
                        "FIELD_TYPE" => $row3->DASH_TYPE,
                        "DETAILS" => $details
                    ]);
                }

                $data3 = DB::table('dashboard')
                    ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard.DASH_ID')
                    ->join('master_testdata', 'sym_organ_test.TEST_ID', '=', 'master_testdata.TEST_ID')
                    ->select([
                        'dashboard.DASH_ID',
                        'dashboard.DASH_NAME',
                        'dashboard.PHOTO_URL',
                        'dashboard.DASH_TYPE',
                        'master_testdata.TEST_ID',
                        'master_testdata.TEST_SL',
                        'master_testdata.TEST_NAME',
                        'master_testdata.TEST_CODE',
                        'master_testdata.TEST_SAMPLE',
                        'master_testdata.TEST_CATG',
                        'master_testdata.DEPARTMENT',
                        'master_testdata.TEST_DESC',
                        'master_testdata.KNOWN_AS',
                        'master_testdata.FASTING',
                        'master_testdata.GENDER_TYPE',
                        'master_testdata.AGE_TYPE',
                        'master_testdata.REPORT_TIME',
                        'master_testdata.PRESCRIPTION',
                        'master_testdata.ID_PROOF',
                        'master_testdata.QA1',
                        'master_testdata.QA2',
                        'master_testdata.QA3',
                        'master_testdata.QA4',
                        'master_testdata.QA5',
                        'master_testdata.QA6'
                    ])
                    ->where('dashboard.DASH_SECTION_ID', '=', 'S')
                    ->where('dashboard.STATUS', '=', 'Active')
                    ->orderBy('dashboard.POSITION')
                    ->get();

                $testDetails = collect();
                $data3 = DB::table('dashboard')
                    ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard.DASH_ID')
                    ->join('master_testdata', 'sym_organ_test.TEST_ID', '=', 'master_testdata.TEST_ID')
                    ->select([
                        'dashboard.DASH_ID',
                        'dashboard.DASH_NAME',
                        'dashboard.PHOTO_URL',
                        'dashboard.DASH_TYPE',
                        'master_testdata.TEST_ID',
                        'master_testdata.TEST_SL',
                        'master_testdata.TEST_NAME',
                        'master_testdata.TEST_CODE',
                        'master_testdata.TEST_SAMPLE',
                        'master_testdata.TEST_CATG',
                        'master_testdata.DEPARTMENT',
                        'master_testdata.TEST_DESC',
                        'master_testdata.KNOWN_AS',
                        'master_testdata.FASTING',
                        'master_testdata.GENDER_TYPE',
                        'master_testdata.AGE_TYPE',
                        'master_testdata.REPORT_TIME',
                        'master_testdata.PRESCRIPTION',
                        'master_testdata.ID_PROOF',
                        'master_testdata.QA1',
                        'master_testdata.QA2',
                        'master_testdata.QA3',
                        'master_testdata.QA4',
                        'master_testdata.QA5',
                        'master_testdata.QA6'
                    ])
                    ->where([
                        ['dashboard.DASH_SECTION_ID', '=', 'S'],
                        ['dashboard.STATUS', '=', 'Active']
                    ])
                    ->orderBy('dashboard.POSITION')
                    ->get();

                $testDetails = [];
                foreach ($data3 as $item) {
                    $testDetails[$item->DASH_ID]['ID'] = $item->DASH_ID;
                    $testDetails[$item->DASH_ID]['ITEM_NAME'] = $item->DASH_NAME;
                    $testDetails[$item->DASH_ID]['FIELD_TYPE'] = $item->DASH_TYPE;
                    $testDetails[$item->DASH_ID]['DETAILS'][] = [
                        "TEST_ID" => $item->TEST_ID,
                        "TEST_SL" => $item->TEST_SL,
                        "TEST_NAME" => $item->TEST_NAME,
                        "TEST_CODE" => $item->TEST_CODE,
                        "TEST_SAMPLE" => $item->TEST_SAMPLE,
                        "TEST_CATG" => $item->TEST_CATG,
                        "DEPARTMENT" => $item->DEPARTMENT,
                        "TEST_DESC" => $item->TEST_DESC,
                        "KNOWN_AS" => $item->KNOWN_AS,
                        "FASTING" => $item->FASTING,
                        "GENDER_TYPE" => $item->GENDER_TYPE,
                        "AGE_TYPE" => $item->AGE_TYPE,
                        "REPORT_TIME" => $item->REPORT_TIME,
                        "PRESCRIPTION" => $item->PRESCRIPTION,
                        "ID_PROOF" => $item->ID_PROOF,
                        "QA1" => $item->QA1,
                        "QA2" => $item->QA2,
                        "QA3" => $item->QA3,
                        "QA4" => $item->QA4,
                        "QA5" => $item->QA5,
                        "QA6" => $item->QA6
                    ];
                }
                $data = $data->merge(\array_values($testDetails));

                if ($data == null) {
                    $response = ['Success' => false, 'Message' => 'Test not found', 'code' => 200];
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

    function servicescrh_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = [];

                // Query facility_section
                $data1 = DB::table('facility_section')
                    // ->whereIn('DASH_SECTION_ID', ['AG', 'AH', 'AI', 'AL', 'AP', 'SR', 'AM'])
                    ->where('DS_STATUS', 'Active')
                    ->orderby('ID')
                    ->get();

                foreach ($data1 as $row3) {
                    $pkgdtl = [
                        "DESCRIPTION" => $row3->DS_DESCRIPTION,
                        "ID" => $row3->ID,
                        // "PHOTO_URL" => $row3->DSH_PHOTO_URL,
                        // "DSM_PHOTO_URL" => $row3->DSM_PHOTO_URL,
                        "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
                        "POSITION" => $row3->DS_POSITION,
                        "STATUS" => $row3->DS_STATUS,
                        "PHOTO_URL1" => $row3->DSIMG1,
                        "PHOTO_URL2" => $row3->DSIMG2,
                        "PHOTO_URL3" => $row3->DSIMG3,
                        "PHOTO_URL4" => $row3->DSIMG4,
                        "PHOTO_URL5" => $row3->DSIMG5,
                        "PHOTO_URL6" => $row3->DSIMG6,
                        "PHOTO_URL7" => $row3->DSIMG7,
                        "PHOTO_URL8" => $row3->DSIMG8,
                        "PHOTO_URL9" => $row3->DSIMG9,
                        "PHOTO_URL10" => $row3->DSIMG10,
                        "BANNER_URL1" => $row3->DSBNR1,
                        "BANNER_URL2" => $row3->DSBNR2,
                        "BANNER_URL3" => $row3->DSBNR3,
                        "BANNER_URL4" => $row3->DSBNR4,
                        "BANNER_URL5" => $row3->DSBNR5,
                        "BANNER_URL6" => $row3->DSBNR6,
                        "BANNER_URL7" => $row3->DSBNR7,
                        "BANNER_URL8" => $row3->DSBNR8,
                        "BANNER_URL9" => $row3->DSBNR9,
                        "BANNER_URL10" => $row3->DSBNR10,

                    ];

                    if ($row3->DASH_SECTION_ID == 'SR') {
                        $pkgdtl['Questions'] = [
                            [
                                "QA1" => $row3->DSQA1,
                                "QA2" => $row3->DSQA2,
                                "QA3" => $row3->DSQA3,
                                "QA4" => $row3->DSQA4,
                                "QA5" => $row3->DSQA5,
                                "QA6" => $row3->DSQA6,
                                "QA7" => $row3->DSQA7,
                                "QA8" => $row3->DSQA8,
                                "QA9" => $row3->DSQA9,
                            ]
                        ];
                    } else {
                        $pkgdtl['QA1'] = $row3->DSQA1;
                        $pkgdtl['QA2'] = $row3->DSQA2;
                        $pkgdtl['QA3'] = $row3->DSQA3;
                        $pkgdtl['QA4'] = $row3->DSQA4;
                        $pkgdtl['QA5'] = $row3->DSQA5;
                        $pkgdtl['QA6'] = $row3->DSQA6;
                    }

                    $pkg = [
                        "ID" => $row3->DASH_SECTION_ID,
                        "ITEM_NAME" => $row3->DASH_SECTION_NAME,
                        "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                        "FIELD_TYPE" => '',
                        'CHECK_TYPE' => 'Facility_Section',
                        "DETAILS" => $pkgdtl
                    ];
                    array_push($data, $pkg);
                }

                // Query facility_type
                // $data2 = DB::table('facility_type')
                // ->join('facility_section', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                //     ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active'])
                //     ->whereIn('facility_type.DASH_SECTION_ID', ['AG', 'AH', 'AI', 'AL', 'AP', 'SR', 'AM'])
                //     ->orderby('facility_type.DASH_TYPE_ID')
                //     ->get();

                // foreach ($data2 as $row3) {
                //     if ($row3->DASH_SECTION_ID == 'AG') {
                //         $row3->DASH_TYPE = '24x7 ' . $row3->DASH_TYPE;
                //     }
                //     $pkgdtl = [
                //         "DASH_TYPE_ID" => $row3->DASH_TYPE_ID,
                //         "DASH_TYPE" => $row3->DASH_TYPE,
                //         "DESCRIPTION" => $row3->DT_DESCRIPTION,
                //         "BANNER_URL" => $row3->DT_BANNER_URL,
                //         "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                //         "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
                //         "POSITION" => $row3->DT_POSITION,
                //         "STATUS" => $row3->DT_STATUS,
                //         "PHOTO_URL1" =>$row3->DTIMG1,
                //         "PHOTO_URL2" =>$row3->DSIMG2,
                //         "PHOTO_URL3" =>$row3->DTIMG3,
                //         "PHOTO_URL4" =>$row3->DTIMG4,
                //         "PHOTO_URL5" =>$row3->DTIMG5,
                //         "PHOTO_URL6" =>$row3->DTIMG6,
                //         "PHOTO_URL7" =>$row3->DTIMG7,
                //         "PHOTO_URL8" =>$row3->DTIMG8,
                //         "PHOTO_URL9" =>$row3->DTIMG9,
                //         "PHOTO_URL10" =>$row3->DTIMG10,
                //         "BANNER_URL1" => $row3->DTBNR1,
                //         "BANNER_URL2" => $row3->DTBNR2,
                //         "BANNER_URL3" => $row3->DTBNR3,
                //         "BANNER_URL4" => $row3->DTBNR4,
                //         "BANNER_URL5" => $row3->DTBNR5,
                //         "BANNER_URL6" => $row3->DTBNR6,
                //         "BANNER_URL7" => $row3->DTBNR7,
                //         "BANNER_URL8" => $row3->DTBNR8,
                //         "BANNER_URL9" => $row3->DTBNR9,
                //         "BANNER_URL10" => $row3->DTBNR10,
                //     ];


                //         $pkgdtl['Questions'] = [
                //             [
                //                 "QA1" => $row3->DTQA1,
                //                 "QA2" => $row3->DTQA2,
                //                 "QA3" => $row3->DTQA3,
                //                 "QA4" => $row3->DTQA4,
                //                 "QA5" => $row3->DTQA5,
                //                 "QA6" => $row3->DTQA6,
                //                 "QA7" => $row3->DTQA7,
                //                 "QA8" => $row3->DTQA8,
                //                 "QA9" => $row3->DTQA9,
                //             ]
                //         ];


                //     $pkg = [
                //         "ID" => $row3->DASH_TYPE_ID,
                //         "ITEM_NAME" => $row3->DASH_TYPE,
                //         "FIELD_TYPE" => $row3->DASH_SECTION_NAME,
                //         "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                //         'CHECK_TYPE' => 'Facility_Type',
                //         "DETAILS" => $pkgdtl
                //     ];
                //     array_push($data, $pkg);
                // }


                $data2 = DB::table('facility')
                    ->join('facility_type', 'facility.DASH_TYPE_ID', '=', 'facility_type.DASH_TYPE_ID')
                    ->join('facility_section', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->orderby('facility_type.DT_POSITION')
                    ->orderby('facility.DN_POSITION')
                    ->get();

                $sections = [];

                foreach ($data2 as $row3) {
                    if ($row3->DASH_SECTION_ID == 'AG') {
                        $row3->DASH_TYPE = '24x7 ' . $row3->DASH_TYPE;
                        $row3->DASH_NAME = '24x7 ' . $row3->DASH_NAME;
                    }
                    if ($row3->DASH_SECTION_ID == 'SR') {
                        continue;
                    }

                    if (!isset($sections[$row3->DASH_SECTION_ID])) {
                        $sections[$row3->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
                            "DASH_TYPES" => []
                        ];
                    }

                    if (!isset($sections[$row3->DASH_SECTION_ID]['DASH_TYPES'][$row3->DASH_TYPE_ID])) {
                        $sections[$row3->DASH_SECTION_ID]['DASH_TYPES'][$row3->DASH_TYPE_ID] = [
                            "DASH_TYPE_ID" => $row3->DASH_TYPE_ID,
                            "DASH_TYPE" => $row3->DASH_TYPE,
                            "DESCRIPTION" => $row3->DT_DESCRIPTION,
                            "PHOTO_URL1" => $row3->DTIMG1,
                            "PHOTO_URL2" => $row3->DSIMG2,
                            "PHOTO_URL3" => $row3->DTIMG3,
                            "PHOTO_URL4" => $row3->DTIMG4,
                            "PHOTO_URL5" => $row3->DTIMG5,
                            "PHOTO_URL6" => $row3->DTIMG6,
                            "PHOTO_URL7" => $row3->DTIMG7,
                            "PHOTO_URL8" => $row3->DTIMG8,
                            "PHOTO_URL9" => $row3->DTIMG9,
                            "PHOTO_URL10" => $row3->DTIMG10,
                            "BANNER_URL1" => $row3->DTBNR1,
                            "BANNER_URL2" => $row3->DTBNR2,
                            "BANNER_URL3" => $row3->DTBNR3,
                            "BANNER_URL4" => $row3->DTBNR4,
                            "BANNER_URL5" => $row3->DTBNR5,
                            "BANNER_URL6" => $row3->DTBNR6,
                            "BANNER_URL7" => $row3->DTBNR7,
                            "BANNER_URL8" => $row3->DTBNR8,
                            "BANNER_URL9" => $row3->DTBNR9,
                            "BANNER_URL10" => $row3->DTBNR10,
                            "FACILITY_DETAILS" => []
                        ];
                    }

                    $facilityDetail = [
                        "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
                        "DASH_TYPE_ID" => $row3->DASH_TYPE_ID,
                        "DASH_TYPE" => $row3->DASH_TYPE,
                        "DASH_ID" => $row3->DASH_ID,
                        "DASH_NAME" => $row3->DASH_NAME,
                        "DESCRIPTION" => $row3->DN_DESCRIPTION,
                        "PHOTO_URL1" => $row3->DNIMG1,
                        "PHOTO_URL2" => $row3->DNIMG2,
                        "PHOTO_URL3" => $row3->DNIMG3,
                        "PHOTO_URL4" => $row3->DNIMG4,
                        "PHOTO_URL5" => $row3->DNIMG5,
                        "PHOTO_URL6" => $row3->DNIMG6,
                        "PHOTO_URL7" => $row3->DNIMG7,
                        "PHOTO_URL8" => $row3->DNIMG8,
                        "PHOTO_URL9" => $row3->DNIMG9,
                        "PHOTO_URL10" => $row3->DNIMG10,
                        "BANNER_URL1" => $row3->DNBNR1,
                        "BANNER_URL2" => $row3->DNBNR2,
                        "BANNER_URL3" => $row3->DNBNR3,
                        "BANNER_URL4" => $row3->DNBNR4,
                        "BANNER_URL5" => $row3->DNBNR5,
                        "BANNER_URL6" => $row3->DNBNR6,
                        "BANNER_URL7" => $row3->DNBNR7,
                        "BANNER_URL8" => $row3->DNBNR8,
                        "BANNER_URL9" => $row3->DNBNR9,
                        "BANNER_URL10" => $row3->DNBNR10,
                        "Questions" => [
                            [
                                "QA1" => $row3->DNQA1,
                                "QA2" => $row3->DNQA2,
                                "QA3" => $row3->DNQA3,
                                "QA4" => $row3->DNQA4,
                                "QA5" => $row3->DNQA5,
                                "QA6" => $row3->DNQA6,
                                "QA7" => $row3->DNQA7,
                                "QA8" => $row3->DNQA8,
                                "QA9" => $row3->DNQA9
                            ]
                        ]
                    ];

                    $sections[$row3->DASH_SECTION_ID]['DASH_TYPES'][$row3->DASH_TYPE_ID]['FACILITY_DETAILS'][] = $facilityDetail;
                }

                $data = [];

                foreach ($sections as $section) {
                    foreach ($section['DASH_TYPES'] as $type) {
                        $pkg = [
                            "ID" => $type['DASH_TYPE_ID'],
                            "ITEM_NAME" => $type['DASH_TYPE'],
                            "FIELD_TYPE" => $section['DASH_SECTION_NAME'],
                            "DASH_SECTION_ID" => $section['DASH_SECTION_ID'],
                            'CHECK_TYPE' => 'Facility_Type',
                            "DETAILS" => $type
                        ];
                        $data[] = $pkg;
                    }
                }

                // // Query facility
                // $data3 = DB::table('facility')
                // ->join('facility_type', 'facility.DASH_TYPE_ID', '=', 'facility_type.DASH_TYPE_ID')
                // ->where(['facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                //     ->orderby('DASH_ID')
                //     ->get();

                foreach ($data2 as $row3) {
                    $pkgdtl = [
                        "DASH_ID" => $row3->DASH_ID,
                        "DASH_NAME" => $row3->DASH_NAME,
                        "DESCRIPTION" => $row3->DN_DESCRIPTION,
                        "BANNER_URL" => $row3->DN_BANNER_URL,
                        "DASH_TYPE_ID" => $row3->DASH_TYPE_ID,
                        "DASH_TYPE" => $row3->DASH_TYPE,
                        "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                        "DN_TAG_SECTION" => $row3->DN_TAG_SECTION,
                        "POSITION" => $row3->DN_POSITION,
                        "STATUS" => $row3->DN_STATUS,
                        "PHOTO_URL1" => $row3->DNIMG1,
                        "PHOTO_URL2" => $row3->DNIMG2,
                        "PHOTO_URL3" => $row3->DNIMG3,
                        "PHOTO_URL4" => $row3->DNIMG4,
                        "PHOTO_URL5" => $row3->DNIMG5,
                        "PHOTO_URL6" => $row3->DNIMG6,
                        "PHOTO_URL7" => $row3->DNIMG7,
                        "PHOTO_URL8" => $row3->DNIMG8,
                        "PHOTO_URL9" => $row3->DNIMG9,
                        "PHOTO_URL10" => $row3->DNIMG10,
                        "BANNER_URL1" => $row3->DNBNR1,
                        "BANNER_URL2" => $row3->DNBNR2,
                        "BANNER_URL3" => $row3->DNBNR3,
                        "BANNER_URL4" => $row3->DNBNR4,
                        "BANNER_URL5" => $row3->DNBNR5,
                        "BANNER_URL6" => $row3->DNBNR6,
                        "BANNER_URL7" => $row3->DNBNR7,
                        "BANNER_URL8" => $row3->DNBNR8,
                        "BANNER_URL9" => $row3->DNBNR9,
                        "BANNER_URL10" => $row3->DNBNR10,
                    ];



                    $pkgdtl['Questions'] = [
                        [
                            "QA1" => $row3->DNQA1,
                            "QA2" => $row3->DNQA2,
                            "QA3" => $row3->DNQA3,
                            "QA4" => $row3->DNQA4,
                            "QA5" => $row3->DNQA5,
                            "QA6" => $row3->DNQA6,
                            "QA7" => $row3->DNQA7,
                            "QA8" => $row3->DNQA8,
                            "QA9" => $row3->DNQA9,
                        ]
                    ];

                    $pkg = [
                        "ID" => $row3->DASH_ID,
                        "ITEM_NAME" => $row3->DASH_NAME,
                        "FIELD_TYPE" => $row3->DASH_TYPE,
                        'CHECK_TYPE' => 'Facility',
                        "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row3->DASH_SECTION_NAME,
                        "DETAILS" => $pkgdtl
                    ];
                    array_push($data, $pkg);
                }

                if ($data == null) {
                    $response = ['Success' => false, 'Message' => 'services not found', 'code' => 200];
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

    function pharmascrh_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $data1 = DB::table('pharmacy')
                    ->select('pharmacy.*', DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                * SIN(RADIANS('$latt'))))),2) as KM"), )
                    //     ->whereRaw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                    // * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                    //  * SIN(RADIANS('$latt'))))),2) as KM" <= 100)
                    ->take(25)
                    ->get();
                foreach ($data1 as $row2) {
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

    public function vu_facilities1(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $dsid = $input['DASH_SECTION_ID'];
            $data1 = DB::table('dashboard')
                ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId, $dsid) {
                    $join->on('dashboard.DASH_ID', '=', 'hospital_facilities_details.DASH_ID')
                        ->where('hospital_facilities_details.PHARMA_ID', '=', $pharmaId);
                })
                ->select(
                    'dashboard.DASH_SECTION_ID',
                    'dashboard.DASH_SECTION_NAME',
                    'dashboard.PHOTO_URL',
                    'dashboard.DASH_TYPE',
                    'dashboard.DASH_DESCRIPTION',
                    'dashboard.GR_DESC',
                    'dashboard.DASH_ID',
                    'dashboard.DIS_ID',
                    'dashboard.SYM_ID',
                    'dashboard.DASH_NAME',
                    'hospital_facilities_details.UID',
                    'hospital_facilities_details.TOT_BED',
                    'hospital_facilities_details.AVAIL_BED',
                    'hospital_facilities_details.PRICE_FROM',
                    'hospital_facilities_details.DEPT_PH',
                    'hospital_facilities_details.SHORT_NOTE',
                    'hospital_facilities_details.FREE_AREA',
                    'hospital_facilities_details.FREE_FROM',
                    'hospital_facilities_details.FREE_TO',
                    'hospital_facilities_details.SERV_AREA',
                    'hospital_facilities_details.SERV_FROM',
                    'hospital_facilities_details.SERV_TO',
                    'hospital_facilities_details.SERV_CRG',
                    'hospital_facilities_details.DLV_TM',
                    'hospital_facilities_details.MIN_ODR',
                    'hospital_facilities_details.SERV_24X7',
                    'hospital_facilities_details.SERV_HOME',
                    'hospital_facilities_details.TREATMENTS',
                    'hospital_facilities_details.DISCOUNT',
                    'hospital_facilities_details.CASH_LESS',
                    'hospital_facilities_details.CASH_PAID',
                    'hospital_facilities_details.IMAGE1_URL',
                    'hospital_facilities_details.IMAGE2_URL',
                    'hospital_facilities_details.IMAGE3_URL',
                    'hospital_facilities_details.REMARK',
                )
                ->where('dashboard.CATEGORY', 'like', '%' . 'H' . '%')
                ->where('dashboard.DASH_SECTION_ID', '<>', 'AK')
                ->where('dashboard.STATUS', 'Active')
                ->where('dashboard.DASH_SECTION_ID', $dsid)
                ->get();

            $groupedData = $this->groupFacilities($data1);

            $data2 = DB::table('surgery')
                ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId, $dsid) {
                    $join->on('surgery.DASH_ID', '=', 'hospital_facilities_details.DASH_ID')
                        ->where('hospital_facilities_details.PHARMA_ID', '=', $pharmaId);
                })
                ->select(
                    'surgery.DASH_SECTION_ID',
                    DB::raw("'SURGERY' as DASH_SECTION_NAME"),
                    'surgery.DASH_ID',
                    'surgery.PHOTO_URL',
                    'surgery.SURG_TYPE AS DASH_TYPE',
                    'surgery.DESCRIPTION AS DASH_DESCRIPTION',
                    'surgery.TYPE_DESC AS GR_DESC',
                    'surgery.DIS_ID',
                    'surgery.SURG_NAME AS DASH_NAME',
                    'hospital_facilities_details.UID',
                    'hospital_facilities_details.TOT_BED',
                    'hospital_facilities_details.AVAIL_BED',
                    'hospital_facilities_details.PRICE_FROM',
                    'hospital_facilities_details.DEPT_PH',
                    'hospital_facilities_details.SHORT_NOTE',
                    'hospital_facilities_details.FREE_AREA',
                    'hospital_facilities_details.FREE_FROM',
                    'hospital_facilities_details.FREE_TO',
                    'hospital_facilities_details.SERV_AREA',
                    'hospital_facilities_details.SERV_FROM',
                    'hospital_facilities_details.SERV_TO',
                    'hospital_facilities_details.SERV_CRG',
                    'hospital_facilities_details.DLV_TM',
                    'hospital_facilities_details.MIN_ODR',
                    'hospital_facilities_details.SERV_24X7',
                    'hospital_facilities_details.SERV_HOME',
                    'hospital_facilities_details.TREATMENTS',
                    'hospital_facilities_details.DISCOUNT',
                    'hospital_facilities_details.CASH_LESS',
                    'hospital_facilities_details.CASH_PAID',
                    'hospital_facilities_details.IMAGE1_URL',
                    'hospital_facilities_details.IMAGE2_URL',
                    'hospital_facilities_details.IMAGE3_URL',
                    'hospital_facilities_details.REMARK',
                )
                ->where('surgery.DASH_SECTION_ID', $dsid)
                ->get();
            $groupedData1 = $this->groupFacilities($data2);

            $data = array_merge(array_values($groupedData), array_values($groupedData1));

            if (empty($data)) {
                return response()->json(['Success' => false, 'Message' => 'Record not found', 'code' => 404], 404);
            } else {
                return response()->json(['Success' => true, 'data' => $data, 'code' => 200], 200);
            }
        } else {
            return response()->json(['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405], 405);
        }
    }


    function hndashboard1(Request $request)
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $promo_bnr = DB::table('promo_banner')->where('STATUS', 'Active')->get();
                $dash = DB::table('dashboard')->where('CATEGORY', 'like', '%' . 'H' . '%')->where('STATUS', 'Active')->orderby('DASH_SL')->get();
                $pharma = DB::table('pharmacy')
                    ->select('PHARMA_ID', 'ITEM_NAME AS PHARMA_NAME', 'CLINIC_TYPE', 'ADDRESS', 'CITY', 'DIST', 'STATE', 'PIN', 'CLINIC_MOBILE', 'PHOTO_URL', 'LOGO_URL', 'LATITUDE', 'LONGITUDE', DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude)) * COS(RADIANS('$latt')) * COS(RADIANS(Longitude - '$lont')) + SIN(RADIANS(Latitude)) * SIN(RADIANS('$latt'))))),2) as KM"))
                    ->where('CLINIC_TYPE', 'Hospital')
                    ->orderby('KM')->take(25)->get()->ToArray();

                //SECTION-A #### SLIDER
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'HA';
                });
                $A["Slider"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "SLIDER_ID" => $item->PROMO_ID,
                        "SLIDER_NAME" => $item->PROMO_NAME,
                        "SLIDER_URL" => $item->PROMO_URL,
                    ];
                })->values()->take(4)->all();

                //SECTION-DASH_A #### DASHBOARD
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'AK';
                });
                $B["Dashboard"] = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        // "FACILITY_ID" => $item->FACILITY_ID,
                        "DASH_SECTION_ID" => $item->FACILITY_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DASH_SECTION_DESC,
                        "PHOTO_URL" => $item->PHOTO_URL,
                        "BANNER_URL" => $item->BANNER_URL,
                    ];
                })->values()->all();

                //SECTION-NEAR BY HOSPITAL                
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'CL';
                });
                $C_BNR["Clinic_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                })->values()->all();
                $C["Hospital"] = array_values($pharma + $C_BNR);

                //SECTION-#### SPECIALIST
                $SPLST_DTL = DB::table('dis_catg')->select('DIS_ID', 'DASH_SECTION_ID', 'DIS_TYPE', 'DIS_CATEGORY', 'SPECIALIST', 'SPECIALITY', 'PHOTO_URL')->take(7)->orderBy('DIS_SL')->get()->toArray();
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SP';
                });

                $SPB["Specialist_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                $D["Specialist"] = array_values($SPLST_DTL + $SPB);



                //SECTION-E #### International Patient
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'AP';
                });
                $E_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO_URL,
                    ];
                })->values()->all();
                // $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                //     return $item->DASH_SECTION_ID === 'AP';
                // });
                // $E_BNR["IP_Banner"] = $fltr_promo_bnr->map(function ($item) {
                //     return [
                //         "PROMO_ID" => $item->PROMO_ID,
                //         "HEADER_NAME" => $item->HEADER_NAME,
                //         "PHARMA_ID" => $item->PHARMA_ID,
                //         "MOBILE_NO" => $item->MOBILE_NO,
                //         "DIS_ID" => $item->DIS_ID,
                //         "SYM_ID" => $item->SYM_ID,
                //         "PKG_ID" => $item->PKG_ID,
                //         "PROMO_NAME" => $item->PROMO_NAME,
                //         "DESCRIPTION" => $item->DESCRIPTION,
                //         "PROMO_URL" => $item->PROMO_URL,
                //         "PROMO_DT" => $item->PROMO_DT,
                //         "PROMO_VALID" => $item->PROMO_VALID,
                //     ];
                // })->values()->all();

                $E["Banner_IP"] = array_values($E_DTL);

                //SECTION-#### SURGERY
                // $SURG_DTL = DB::table('surgery')
                //     ->select(
                //         'DASH_ID',
                //         'DIS_ID',
                //         'DASH_SECTION_ID',
                //         DB::raw("'SURGERY' as DASH_SECTION_NAME"),
                //         'SURG_NAME AS DASH_NAME',
                //         'SURG_TYPE AS DASH_TYPE',
                //         'DESCRIPTION AS DASH_DESCRIPTION',
                //         'DASH_URL as PHOTO_URL'
                //     )
                //     ->where(['STATUS' => 'Active', 'TYPE_STATUS' => 'Active'])
                //     ->orderby('SURG_SL')->take(10)->get()->toArray();

                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SR';
                });
                $SURG_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->VIEW1_URL,
                        "BANNER_URL" => $item->GR_BANNER_URL,
                    ];
                })->values()->all();
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SR';
                });
                $SGB["Surgery_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                $F["Surgery"] = array_values($SURG_DTL + $SGB);

                //SECTION-#### Inserence
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'AL';
                });
                $G_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO_URL,
                    ];
                })->values()->all();
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'AL';
                });
                $G_BNR["Inserence_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                })->values()->all();

                $G["Insurance"] = array_values($G_DTL + $G_BNR);


                //SECTION-H #### IPD Sectikon
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'AH';
                });


                $H_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->URL_IPD_HG,
                        "VIEW1_URL" => $item->VIEW1_URL,
                    ];
                })->values()->all();

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'AH';
                });
                $H_BNR["IPD_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                })->values()->all();
                $H["IPD_Section"] = array_values($H_DTL + $H_BNR);

                //SECTION-I #### Emergency
                // $fltr_dash = $dash->filter(function ($item) {
                //     return $item->DASH_SECTION_ID === 'AG';
                // });
                // $I_DTL = $fltr_dash->map(function ($item) {
                //     return [
                //         "DASH_ID" => $item->DASH_ID,
                //         "DIS_ID" => $item->DIS_ID,
                //         "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                //         "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                //         "DASH_NAME" => $item->DASH_NAME,
                //         "DASH_TYPE" => $item->DASH_TYPE,
                //         "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                //         "PHOTO_URL" => $item->URL_24X7_HG,
                //         "VIEW1_URL" => $item->VIEW1_URL,
                //     ];
                // })->values()->all();

                $data1 = DB::table('dashboard')->where('TAG_SECTION', 'like', '%' . 'AG' . '%')
                    ->where('STATUS', 'Active')
                    ->orderby('GR_POSITION')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->DASH_TYPE])) {
                        $photoGrUrl = $bannerGrUrl = NULL;

                        $tags = explode(',', $row->TAG_SECTION);
                        if (in_array('AG', $tags) || in_array('AH', $tags) || in_array('AI', $tags) || in_array('AM', $tags)) {
                            $photoGrUrl = $row->URL_24X7_MG;
                            $bannerGrUrl = $row->URL_24X7_MGB;
                        }


                        $groupedData[$row->DASH_TYPE] = [
                            "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                            "DASH_TYPE" => $row->DASH_TYPE,
                            "DESCRIPTION" => $row->GR_DESC,
                            "PHOTO_URL" => $photoGrUrl,
                            "BANNER_URL" => $bannerGrUrl,
                            "FACILITY_DETAILS" => []
                        ];
                    }
                    $photoUrl = $row->URL_24X7_MI;
                    $bannerUrl = $row->URL_24X7_MB;

                    $groupedData[$row->DASH_TYPE]['FACILITY_DETAILS'][] = [
                        "DASH_ID" => $row->DASH_ID,
                        "DIS_ID" => $row->DIS_ID,
                        "SYM_ID" => $row->SYM_ID,
                        "DASH_NAME" => $row->DASH_NAME,
                        "DESCRIPTION" => $row->DASH_DESCRIPTION,
                        "PHOTO_URL" => $photoUrl,
                        "BANNER_URL" => $bannerUrl,
                    ];
                }
                $a = \array_values($groupedData);




                // $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                //     return $item->DASH_SECTION_ID === 'AG';
                // });
                // $b["Facility_Banner"] = $fltr_promo_bnr->map(function ($item) {
                //     return [
                //         "PROMO_ID" => $item->PROMO_ID,
                //         "HEADER_NAME" => $item->HEADER_NAME,
                //         "PHARMA_ID" => $item->PHARMA_ID,
                //         "MOBILE_NO" => $item->MOBILE_NO,
                //         "DIS_ID" => $item->DIS_ID,
                //         "SYM_ID" => $item->SYM_ID,
                //         "PKG_ID" => $item->PKG_ID,
                //         "PROMO_NAME" => $item->PROMO_NAME,
                //         "DESCRIPTION" => $item->DESCRIPTION,
                //         "PROMO_URL" => $item->PROMO_URL,
                //         "PROMO_DT" => $item->PROMO_DT,
                //         "PROMO_VALID" => $item->PROMO_VALID,
                //     ];
                // })->values()->all();
                // $I["Emergency"] = array_values($I_DTL + $I_BNR);
                $I["Emergency"] = $a;

                $data = $A + $B + $C + $D + $E + $F + $G + $H + $I;

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    private function groupFacilities($data)
    {
        $groupedData = [];
        foreach ($data as $row) {
            $typeKey = $row->DASH_TYPE;
            if (!isset($groupedData[$typeKey])) {
                $groupedData[$typeKey] = [
                    "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                    "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                    "DASH_TYPE" => $row->DASH_TYPE,
                    "DESCRIPTION" => $row->GR_DESC,
                    "PHOTO_URL" => $row->PHOTO_URL,
                    "FACILITY" => []
                ];
            }
            if ($row->DASH_SECTION_ID === 'AG') {
                $dname = '24x7 ' . $row->DASH_NAME;
                $dstype = '24x7 ' . $row->DASH_TYPE;
            } else {
                $dname = $row->DASH_NAME;
                $dstype = $row->DASH_TYPE;
            }

            $groupedData[$typeKey]['FACILITY'][] = [
                "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                "UID" => $row->UID,
                "DASH_ID" => $row->DASH_ID,
                "DASH_TYPE" => $dstype,
                "DIS_ID" => $row->DIS_ID,
                "SYM_ID" => $row->SYM_ID ?? null,  // Optional property handling
                "DASH_NAME" => $dname,
                "DESCRIPTION" => $row->DASH_DESCRIPTION,
                "TOT_BED" => $row->TOT_BED,
                "AVAIL_BED" => $row->AVAIL_BED,
                "PRICE_FROM" => $row->PRICE_FROM,
                "DEPT_PH" => $row->DEPT_PH,
                "SHORT_NOTE" => $row->SHORT_NOTE,
                "FREE_AREA" => $row->FREE_AREA,
                "FREE_FROM" => $row->FREE_FROM,
                "FREE_TO" => $row->FREE_TO,
                "SERV_AREA" => $row->SERV_AREA,
                "SERV_CRG" => $row->SERV_CRG,
                "SERV_FROM" => $row->SERV_FROM,
                "SERV_TO" => $row->SERV_TO,
                "DLV_TM" => $row->DLV_TM,
                "MIN_ODR" => $row->MIN_ODR,
                "SERV_24X7" => $row->SERV_24X7,
                "SERV_HOME" => $row->SERV_HOME,
                "TREATMENTS" => $row->TREATMENTS,
                "DISCOUNT" => $row->DISCOUNT,
                "CASH_LESS" => $row->CASH_LESS,
                "CASH_PAID" => $row->CASH_PAID,
                "IMAGE1_URL" => $row->IMAGE1_URL,
                "IMAGE2_URL" => $row->IMAGE2_URL,
                "IMAGE3_URL" => $row->IMAGE3_URL,
                "REMARK" => $row->REMARK,
                "PHOTO_URL" => $row->PHOTO_URL,

            ];
        }
        return $groupedData;
    }

    function allsrch()
    {
        $response = array();
        $data = array();
        $sy = [];
        $sp = [];
        $su = [];

        $data1 = DB::table('drprofile')->where('APPROVE', 'true')->get();
        foreach ($data1 as $row1) {
            $drdtl = [];
            $drdtl['DETAILS'] = [
                "DR_ID" => $row1->DR_ID,
                "DR_NAME" => $row1->DR_NAME,
                "DR_MOBILE" => $row1->DR_MOBILE,
                "SEX" => $row1->SEX,
                "DESIGNATION" => $row1->DESIGNATION,
                "QUALIFICATION" => $row1->QUALIFICATION,
                "D_CATG" => $row1->D_CATG,
                "EXPERIENCE" => $row1->EXPERIENCE,
                "DR_PHOTO" => $row1->PHOTO_URL,
            ];

            $data[] = [
                "ID" => $row1->DR_ID,
                "ITEM_NAME" => $row1->DR_NAME,
                "FIELD_TYPE" => "Doctor",
                "DETAILS" => $drdtl['DETAILS']
            ];
        }

        $data3 = DB::table('dis_catg')->get();
        foreach ($data3 as $row3) {
            $spdtl = [];
            $spdtl['DETAILS'] = [
                "DIS_ID" => $row3->DIS_ID,
                "D_CATG" => $row3->DIS_CATEGORY,
            ];

            $sp = [
                "ID" => $row3->DIS_ID,
                "ITEM_NAME" => $row3->SPECIALIST,
                "ITEM_NAME1" => $row3->SPECIALITY,
                "FIELD_TYPE" => "Specialist",
                "DETAILS" => $spdtl['DETAILS']
            ];
            array_push($data, $sp);
        }

        $data4 = DB::table('symptoms')->get();
        foreach ($data4 as $row4) {
            $sydtl = [];
            $sydtl['DETAILS'] = [
                "DIS_ID" => $row4->DIS_ID,
                "D_CATG" => $row4->DIS_CATEGORY,
                "SYM_ID" => $row4->SYM_ID,
                "DESCRIPTION" => $row4->DESCRIPTION,
            ];

            $sy = [
                "ID" => $row4->DIS_ID,
                "ITEM_NAME" => $row4->SYM_NAME,
                "FIELD_TYPE" => "Symptom",
                "DETAILS" => $sydtl['DETAILS']
            ];
            array_push($data, $sy);
        }

        $data5 = DB::table('surgery')->get();
        foreach ($data5 as $row5) {
            $sudtl = [];
            $sudtl['DETAILS'] = [
                "DIS_ID" => $row5->DIS_ID,
                "D_CATG" => $row5->DIS_CATEGORY,
                "SURG_TYPE" => $row5->SURG_TYPE,
                "DESCRIPTION" => $row5->TYPE_DESC,
            ];

            $su = [
                "ID" => $row5->DIS_ID,
                "ITEM_NAME" => $row5->SURG_NAME,
                "FIELD_TYPE" => "Surgery",
                "DETAILS" => $sudtl['DETAILS']
            ];
            array_push($data, $su);
        }
        if ($data == null) {
            $response = ['Success' => false, 'Message' => 'Dr. not found', 'code' => 200];
        } else {
            $response = ['Success' => true, 'data' => $data, 'code' => 200];
        }
        return $response;
    }

    function labsrch(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $data1 = DB::table('master_testdata')->get();
                foreach ($data1 as $row1) {
                    $tstdtl = [];
                    $tstdtl['DETAILS'] = [
                        "TEST_ID" => $row1->TEST_ID,
                        "TEST_NAME" => $row1->TEST_NAME,
                        "TEST_CODE" => $row1->TEST_CODE,
                        "TEST_SAMPLE" => $row1->TEST_SAMPLE,
                        "TEST_CATG" => $row1->TEST_CATG,
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
                * SIN(RADIANS('$latt'))))),2) as KM"), )
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

    function drappointments_dummy(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();

            if (isset($input['DR_ID'])) {

                $doctorId = $input['DR_ID'];

                date_default_timezone_set('Asia/Kolkata');
                $summaries = [];

                $banners = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'PROMO_NAME', 'PROMO_URL', 'PROMO_TYPE', 'MOBILE_NO', 'DESCRIPTION', 'STATUS')
                    ->where('DASH_SECTION_ID', 'PB')
                    ->orWhere('PHARMA_ID', '0')
                    ->get();

                for ($i = 0; $i < 30; $i++) {
                    $currentDate = Carbon::now()->addDays($i)->format('Ymd');
                    $dayOfWeek = Carbon::now()->addDays($i)->format('l');

                    $availabilityData = DB::table('dr_availablity')
                        ->select(
                            'PHARMA_ID',
                            'ID as SCH_ID',
                            'MAX_BOOK',
                            DB::raw("'" . $currentDate . "' as SCH_DT")
                        )
                        ->distinct()
                        ->where(['DR_ID' => $doctorId, 'SCH_DAY' => $dayOfWeek])
                        ->get();

                    $totalChambers = 0;
                    $sumMaxBook = 0;
                    $sumTotalBooked = 0;



                    foreach ($availabilityData as $row) {
                        $appointmentData = DB::table('appointment')
                            ->where('APPNT_DT', $row->SCH_DT)
                            ->where('PHARMA_ID', $row->PHARMA_ID)
                            ->where('DR_ID', $doctorId)
                            ->count();

                        $totalChambers++;
                        $sumMaxBook += $row->MAX_BOOK;
                        $sumTotalBooked += $appointmentData;
                    }

                    if ($totalChambers === 0) {
                        continue;
                    }

                    $dailySummary = [
                        'APPNT_DT' => $currentDate,
                        'Total_Chambers' => $totalChambers,
                        'Maximum_Booking' => $sumMaxBook,
                        'Total_Booked' => $sumTotalBooked,
                        'Total_OT' => 6,
                        'Total_Round_Visit' => 10

                    ];

                    $summaries[] = $dailySummary;
                }

                $A['Doctor'] = DB::table('drprofile')->where(['DR_ID' => $doctorId])->get();

                $response = [
                    'Success' => true,
                    'data' => [
                        'Doctor' => $A['Doctor'],
                        'APPNT_DETAILS' => $summaries,
                        'Promo_Banner' => $banners->filter(fn($item) => $item->DASH_SECTION_ID === 'PB')->values()->all(),
                    ],
                    'code' => 200
                ];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }

        return response()->json($response);
    }

    function drappnt_clinic_dummy(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();

            if (isset($input['DR_ID']) && isset($input['APPNT_DT'])) {
                $doctorId = $input['DR_ID'];
                $schDate = $input['APPNT_DT'];
                $date = Carbon::createFromFormat('Ymd', $schDate);
                $dayOfWeek = $date->format('l');

                $banners = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'PROMO_NAME', 'PROMO_URL', 'PROMO_TYPE', 'MOBILE_NO', 'DESCRIPTION', 'STATUS')
                    ->where('DASH_SECTION_ID', 'PB')
                    ->orWhere('PHARMA_ID', '0')
                    ->get();

                $distinctDoctors = DB::table('dr_availablity')
                    ->select(
                        'PHARMA_ID',
                        'ID as SCH_ID',
                        'CHK_IN_TIME',
                        'CHK_OUT_TIME',
                        'MAX_BOOK',
                        'CHEMBER_NO',
                        'CHK_IN_STATUS',
                        'CHK_IN_TIME1',
                        'CHK_OUT_TIME1',
                        'MAX_BOOK1',
                        'CHEMBER_NO1',
                        'CHK_IN_STATUS1',
                        'CHK_IN_TIME2',
                        'CHK_OUT_TIME2',
                        'MAX_BOOK2',
                        'CHEMBER_NO2',
                        'CHK_IN_STATUS2',
                        'CHK_IN_TIME3',
                        'CHK_OUT_TIME3',
                        'MAX_BOOK3',
                        'CHEMBER_NO3',
                        'CHK_IN_STATUS3',
                        'DR_FEES',
                        DB::raw("'{$schDate}' as SCH_DT")
                    )
                    ->distinct()
                    ->where(['DR_ID' => $doctorId, 'SCH_DAY' => $dayOfWeek]);

                $availabilityData = DB::table('pharmacy')
                    ->joinSub($distinctDoctors, 'distinct_doctors', function ($join) {
                        $join->on('pharmacy.PHARMA_ID', '=', 'distinct_doctors.PHARMA_ID');
                    })
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
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        'distinct_doctors.SCH_ID',
                        'distinct_doctors.SCH_DT',
                        'distinct_doctors.MAX_BOOK',
                        'distinct_doctors.CHK_IN_TIME',
                        'distinct_doctors.CHK_OUT_TIME',
                        'distinct_doctors.CHK_IN_STATUS',
                        'distinct_doctors.CHEMBER_NO',
                        'distinct_doctors.MAX_BOOK1',
                        'distinct_doctors.CHK_IN_TIME1',
                        'distinct_doctors.CHK_OUT_TIME1',
                        'distinct_doctors.CHK_IN_STATUS1',
                        'distinct_doctors.CHEMBER_NO1',
                        'distinct_doctors.MAX_BOOK2',
                        'distinct_doctors.CHK_IN_TIME2',
                        'distinct_doctors.CHK_OUT_TIME2',
                        'distinct_doctors.CHK_IN_STATUS2',
                        'distinct_doctors.CHEMBER_NO2',
                        'distinct_doctors.MAX_BOOK3',
                        'distinct_doctors.CHK_IN_TIME3',
                        'distinct_doctors.CHK_OUT_TIME3',
                        'distinct_doctors.CHK_IN_STATUS3',
                        'distinct_doctors.CHEMBER_NO3',
                        'distinct_doctors.DR_FEES'
                    )
                    ->get();

                $chambers = [];
                $totalChambers = 0;
                $sumMaxBook = 0;
                $sumTotalBooked = 0;

                foreach ($availabilityData as $row) {
                    $chamber = [
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
                        "SCH_ID" => $row->SCH_ID,
                        "SCH_DT" => $row->SCH_DT,
                        "CHK_IN_TIME" => $row->CHK_IN_TIME,
                        "CHK_OUT_TIME" => $row->CHK_OUT_TIME,
                        "DR_STATUS" => $row->CHK_IN_STATUS,
                        "CHEMBER_NO" => $row->CHEMBER_NO,
                        "MAX_BOOK" => $row->MAX_BOOK,
                        "CHK_IN_TIME1" => $row->CHK_IN_TIME1,
                        "CHK_OUT_TIME1" => $row->CHK_OUT_TIME1,
                        "DR_STATUS1" => $row->CHK_IN_STATUS1,
                        "CHEMBER_NO1" => $row->CHEMBER_NO1,
                        "MAX_BOOK1" => $row->MAX_BOOK1,
                        "CHK_IN_TIME2" => $row->CHK_IN_TIME2,
                        "CHK_OUT_TIME2" => $row->CHK_OUT_TIME2,
                        "DR_STATUS2" => $row->CHK_IN_STATUS2,
                        "CHEMBER_NO2" => $row->CHEMBER_NO2,
                        "MAX_BOOK2" => $row->MAX_BOOK2,
                        "CHK_IN_TIME3" => $row->CHK_IN_TIME3,
                        "CHK_OUT_TIME3" => $row->CHK_OUT_TIME3,
                        "DR_STATUS3" => $row->CHK_IN_STATUS3,
                        "CHEMBER_NO3" => $row->CHEMBER_NO3,
                        "MAX_BOOK3" => $row->MAX_BOOK3,
                        "TOTAL_BOOKED" => 0,
                        "DETAILS" => []
                    ];

                    // Fetch patient details
                    $appointmentData = DB::table('appointment')
                        ->join('user_family', 'user_family.ID', '=', 'appointment.PATIENT_ID')
                        ->select(
                            'appointment.BOOKING_ID',
                            'appointment.FAMILY_ID',
                            'appointment.PATIENT_ID',
                            'appointment.PATIENT_NAME',
                            'user_family.DOB',
                            'user_family.SEX',
                            'user_family.MOBILE',
                            'appointment.APPNT_ID',
                            'appointment.APPNT_DT',
                            'appointment.APPNT_TOKEN',
                            'appointment.BOOKING_SL',
                            'appointment.STATUS',
                            'appointment.APPNT_FROM',
                            'appointment.CHEMBER_NO'
                        )
                        ->where('appointment.APPNT_DT', $row->SCH_DT)
                        ->where('appointment.PHARMA_ID', $row->PHARMA_ID)
                        ->where('appointment.DR_ID', $doctorId)
                        ->orderBy('appointment.BOOKING_SL')
                        ->get();

                    $groupedAppointments = [];
                    foreach ($appointmentData as $appointment) {
                        $groupedAppointments[] = [
                            "FAMILY_ID" => $appointment->FAMILY_ID,
                            "PATIENT_ID" => $appointment->PATIENT_ID,
                            "PATIENT_NAME" => $appointment->PATIENT_NAME,
                            "MOBILE" => $appointment->MOBILE,
                            "AGE" => $appointment->DOB, // Assuming DOB is age, but it might be better to calculate age
                            "SEX" => $appointment->SEX,
                            "BOOKING_ID" => $appointment->BOOKING_ID,
                            "APPNT_TOKEN" => $appointment->APPNT_TOKEN,
                            "APPNT_ID" => $appointment->APPNT_ID,
                            "APPNT_DT" => $appointment->APPNT_DT,
                            "APPNT_FROM" => $appointment->APPNT_FROM,
                            "STATUS" => $appointment->STATUS,
                            "BOOKING_SL" => $appointment->BOOKING_SL
                        ];
                    }


                    // usort($groupedAppointments, function ($a, $b) {
                    //     return strtotime($a['APPNT_FROM']) - strtotime($b['APPNT_FROM']);
                    // });

                    $chamber['TOTAL_BOOKED'] = count($groupedAppointments);
                    $chamber['DETAILS'] = array_values($groupedAppointments);

                    $totalChambers++;
                    $sumMaxBook += $row->MAX_BOOK;
                    $sumTotalBooked += $chamber['TOTAL_BOOKED'];

                    $chambers[] = $chamber;
                }

                usort($chambers, function ($a, $b) {
                    return strtotime($a['CHK_IN_TIME']) - strtotime($b['CHK_IN_TIME']);
                });

                $A['Doctor'] = DB::table('drprofile')->where(['DR_ID' => $doctorId])->get();

                $response = [
                    'Success' => true,
                    'data' => [
                        'Doctor' => $A['Doctor'],
                        'Chembers' => $chambers,
                        'Booking_Summary' => [
                            [
                                'APPNT_DT' => $schDate,
                                'Total_Chambers' => $totalChambers,
                                'Maximum_Booking' => $sumMaxBook,
                                'Total_Booked' => $sumTotalBooked,
                                'Total_OT' => 6,
                                'Total_Round_Visit' => 10
                            ]
                        ],
                        'Promo_Banner' => $banners->filter(fn($item) => $item->DASH_SECTION_ID === 'PB')->values()->all(),
                    ],
                    'code' => 200
                ];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }

        return response()->json($response);
    }


    function opinion_depts_dummy(Request $request)
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $headers = apache_request_headers();
            // session_start();
            // date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();
            $promo_bnr = DB::table('promo_banner')
                ->where('STATUS', 'Active')
                ->whereIn('DASH_SECTION_ID', ['AG', 'AH', 'AI', 'AM', 'AL', 'SR'])
                ->get();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
            if (isset($input['FACILITY_ID'])) {
                $f_id = $input['FACILITY_ID'];
                $data1 = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->distinct()
                    // ->select()
                    ->where('facility_type.DT_TAG_SECTION', 'like', '%' . $f_id . '%')
                    // ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    // ->where('facility_section.DASH_SECTION_ID',  $f_id)
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    // ->where('STATUS', 'Active')
                    ->orderby('facility_type.DT_POSITION')
                    ->orderby('facility.DN_POSITION')
                    ->get();


                if ($f_id === 'AL') {
                    $facilityDetails = [];
                    foreach ($data1 as $row) {
                        $facilityDetails[] = [
                            "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                            "DASH_TYPE_ID" => $row->DASH_TYPE_ID,
                            "DASH_TYPE" => $row->DASH_TYPE,
                            "DASH_ID" => $row->DASH_ID,
                            "DASH_NAME" => $row->DASH_NAME,
                            "DESCRIPTION" => $row->DN_DESCRIPTION,
                            "PHOTO_URL1" => $row->DNIMG1,
                            "PHOTO_URL2" => $row->DNIMG2,
                            "PHOTO_URL3" => $row->DNIMG3,
                            "PHOTO_URL4" => $row->DNIMG4,
                            "PHOTO_URL5" => $row->DNIMG5,
                            "PHOTO_URL6" => $row->DNIMG6,
                            "PHOTO_URL7" => $row->DNIMG7,
                            "PHOTO_URL8" => $row->DNIMG8,
                            "PHOTO_URL9" => $row->DNIMG9,
                            "PHOTO_URL10" => $row->DNIMG10,
                            "BANNER_URL1" => $row->DNBNR1,
                            "BANNER_URL2" => $row->DNBNR2,
                            "BANNER_URL3" => $row->DNBNR3,
                            "BANNER_URL4" => $row->DNBNR4,
                            "BANNER_URL5" => $row->DNBNR5,
                            "BANNER_URL6" => $row->DNBNR6,
                            "BANNER_URL7" => $row->DNBNR7,
                            "BANNER_URL8" => $row->DNBNR8,
                            "BANNER_URL9" => $row->DNBNR9,
                            "BANNER_URL10" => $row->DNBNR10,
                            "Questions" => [
                                "QA1" => $row->DNQA1,
                                "QA2" => $row->DNQA2,
                                "QA3" => $row->DNQA3,
                                "QA4" => $row->DNQA4,
                                "QA5" => $row->DNQA5,
                                "QA6" => $row->DNQA6,
                                "QA7" => $row->DNQA7,
                                "QA8" => $row->DNQA8,
                                "QA9" => $row->DNQA9
                            ]
                        ];
                    }
                    $a['Facilities'] = $facilityDetails;
                } else {
                    $groupedData = [];
                    foreach ($data1 as $row) {
                        if (!isset($groupedData[$row->DASH_TYPE])) {
                            $photoGrUrl = $bannerGrUrl = NULL;

                            $groupedData[$row->DASH_TYPE] = [
                                "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                                "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                                "DASH_TYPE_ID" => $row->DASH_TYPE_ID,
                                "DASH_TYPE" => $row->DASH_TYPE,
                                "DESCRIPTION" => $row->DT_DESCRIPTION,
                                "PHOTO_URL1" => $row->DTIMG1,

                                "PHOTO_URL2" => $row->DTIMG2,
                                "PHOTO_URL3" => $row->DTIMG3,
                                "PHOTO_URL4" => $row->DTIMG4,
                                "PHOTO_URL5" => $row->DTIMG5,
                                "PHOTO_URL6" => $row->DTIMG6,
                                "PHOTO_URL7" => $row->DTIMG7,
                                "PHOTO_URL8" => $row->DTIMG8,
                                "PHOTO_URL9" => $row->DTIMG9,
                                "PHOTO_URL10" => $row->DTIMG10,

                                "BANNER_URL1" => $row->DTBNR1,
                                "BANNER_URL2" => $row->DTBNR2,
                                "BANNER_URL3" => $row->DTBNR3,
                                "BANNER_URL4" => $row->DTBNR4,
                                "BANNER_URL5" => $row->DTBNR5,
                                "BANNER_URL6" => $row->DTBNR6,
                                "BANNER_URL7" => $row->DTBNR7,
                                "BANNER_URL8" => $row->DTBNR8,
                                "BANNER_URL9" => $row->DTBNR9,
                                "BANNER_URL10" => $row->DTBNR10,
                                // "BANNER_URL" => $bannerGrUrl,
                                "FACILITY_DETAILS" => []
                            ];
                        }

                        $groupedData[$row->DASH_TYPE]['FACILITY_DETAILS'][] = [
                            "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                            "DASH_TYPE_ID" => $row->DASH_TYPE_ID,
                            "DASH_TYPE" => $row->DASH_TYPE,
                            "DASH_ID" => $row->DASH_ID,
                            // "DIS_ID" => $row->DIS_ID,
                            // "SYM_ID" => $row->SYM_ID,
                            "DASH_NAME" => $row->DASH_NAME,
                            "DESCRIPTION" => $row->DN_DESCRIPTION,
                            // "PHOTO_URL" => $photoUrl,
                            // "BANNER_URL" => $bannerUrl,
                            "PHOTO_URL1" => $row->DNIMG1,
                            "PHOTO_URL2" => $row->DNIMG2,
                            "PHOTO_URL3" => $row->DNIMG3,
                            "PHOTO_URL4" => $row->DNIMG4,
                            "PHOTO_URL5" => $row->DNIMG5,
                            "PHOTO_URL6" => $row->DNIMG6,
                            "PHOTO_URL7" => $row->DNIMG7,
                            "PHOTO_URL8" => $row->DNIMG8,
                            "PHOTO_URL9" => $row->DNIMG9,
                            "PHOTO_URL10" => $row->DNIMG10,

                            "BANNER_URL1" => $row->DNBNR1,
                            "BANNER_URL2" => $row->DNBNR2,
                            "BANNER_URL3" => $row->DNBNR3,
                            "BANNER_URL4" => $row->DNBNR4,
                            "BANNER_URL5" => $row->DNBNR5,
                            "BANNER_URL6" => $row->DNBNR6,
                            "BANNER_URL7" => $row->DNBNR7,
                            "BANNER_URL8" => $row->DNBNR8,
                            "BANNER_URL9" => $row->DNBNR9,
                            "BANNER_URL10" => $row->DNBNR10,

                            "Questions" => [
                                [
                                    "QA1" => $row->DNQA1,
                                    "QA2" => $row->DNQA2,
                                    "QA3" => $row->DNQA3,
                                    "QA4" => $row->DNQA4,
                                    "QA5" => $row->DNQA5,
                                    "QA6" => $row->DNQA6,
                                    "QA7" => $row->DNQA7,
                                    "QA8" => $row->DNQA8,
                                    "QA9" => $row->DNQA9
                                ]
                            ]

                        ];
                    }
                    $a['Facilities'] = \array_values($groupedData);
                }


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

                // if (isset($data['Facilities'])) {
                //     foreach ($data['Facilities'] as &$facility) {
                //         if ($facility['DASH_SECTION_ID'] === $f_id) {
                //             $facility['FACILITY_DETAILS'][] = \array_values($b["Facility_Banner"]);
                //         }
                //     }
                // }

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
    function dash_facility(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $headers = apache_request_headers();
            // session_start();
            // date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();
            $promo_bnr = DB::table('promo_banner')
                ->where('STATUS', 'Active')
                ->whereIn('DASH_SECTION_ID', ['AG', 'AH', 'AI', 'AM'])
                ->get();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
            if (isset($input['FACILITY_ID'])) {
                $f_id = $input['FACILITY_ID'];
                $data1 = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->distinct()
                    // ->select()
                    ->where('facility_type.DT_TAG_SECTION', 'like', '%' . $f_id . '%')
                    // ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    // ->where('facility_section.DASH_SECTION_ID',  $f_id)
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    // ->where('STATUS', 'Active')
                    ->orderby('facility_type.DT_POSITION')
                    ->orderby('facility.DN_POSITION')
                    ->get();


                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->DASH_TYPE])) {
                        $photoGrUrl = $bannerGrUrl = NULL;
                        // $dstype = $row->DASH_TYPE;


                        $groupedData[$row->DASH_TYPE] = [
                            "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                            "DASH_TYPE_ID" => $row->DASH_TYPE_ID,
                            "DASH_TYPE" => $row->DASH_TYPE,
                            "DESCRIPTION" => $row->DT_DESCRIPTION,
                            "PHOTO_URL1" => $row->DTIMG1,

                            "PHOTO_URL2" => $row->DTIMG2,
                            "PHOTO_URL3" => $row->DTIMG3,
                            "PHOTO_URL4" => $row->DTIMG4,
                            "PHOTO_URL5" => $row->DTIMG5,
                            "PHOTO_URL6" => $row->DTIMG6,
                            "PHOTO_URL7" => $row->DTIMG7,
                            "PHOTO_URL8" => $row->DTIMG8,
                            "PHOTO_URL9" => $row->DTIMG9,
                            "PHOTO_URL10" => $row->DTIMG10,

                            "BANNER_URL1" => $row->DTBNR1,
                            "BANNER_URL2" => $row->DTBNR2,
                            "BANNER_URL3" => $row->DTBNR3,
                            "BANNER_URL4" => $row->DTBNR4,
                            "BANNER_URL5" => $row->DTBNR5,
                            "BANNER_URL6" => $row->DTBNR6,
                            "BANNER_URL7" => $row->DTBNR7,
                            "BANNER_URL8" => $row->DTBNR8,
                            "BANNER_URL9" => $row->DTBNR9,
                            "BANNER_URL10" => $row->DTBNR10,
                            // "BANNER_URL" => $bannerGrUrl,
                            "FACILITY_DETAILS" => []
                        ];
                    }

                    // $photoUrl = $bannerUrl = NULL;
                    // switch ($f_id) {

                    //     case 'AG':
                    //         $photoUrl = $row->URL_24X7_MI;
                    //         $bannerUrl = $row->DN_BANNER_URL;
                    //         $dname = '24X7 ' . $row->DASH_NAME;
                    //         break;
                    //     case 'AH':
                    //         $photoUrl = $row->URL_IPD_MI;
                    //         $bannerUrl = $row->DN_BANNER_URL;
                    //         $dname = $row->DASH_NAME;
                    //         break;
                    //     case 'AI':
                    //         $photoUrl = $row->URL_HOME_MI;
                    //         $bannerUrl = $row->DN_BANNER_URL;
                    //         $dname = $row->DASH_NAME;
                    //         break;
                    //     case 'AM':
                    //         $photoUrl = $row->URL_2NDGN_MI;
                    //         $bannerUrl = $row->DN_BANNER_URL;
                    //         $dname = $row->DASH_NAME;
                    //         break;
                    //     default:
                    //         $photoUrl = $row->PHOTO_URL;
                    //         break;
                    // }
                    $groupedData[$row->DASH_TYPE]['FACILITY_DETAILS'][] = [
                        "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                        "DASH_TYPE_ID" => $row->DASH_TYPE_ID,
                        "DASH_TYPE" => $row->DASH_TYPE,
                        "DASH_ID" => $row->DASH_ID,
                        // "DIS_ID" => $row->DIS_ID,
                        // "SYM_ID" => $row->SYM_ID,
                        "DASH_NAME" => $row->DASH_NAME,
                        "DESCRIPTION" => $row->DN_DESCRIPTION,
                        // "PHOTO_URL" => $photoUrl,
                        // "BANNER_URL" => $bannerUrl,
                        "PHOTO_URL1" => $row->DNIMG1,
                        "PHOTO_URL2" => $row->DNIMG2,
                        "PHOTO_URL3" => $row->DNIMG3,
                        "PHOTO_URL4" => $row->DNIMG4,
                        "PHOTO_URL5" => $row->DNIMG5,
                        "PHOTO_URL6" => $row->DNIMG6,
                        "PHOTO_URL7" => $row->DNIMG7,
                        "PHOTO_URL8" => $row->DNIMG8,
                        "PHOTO_URL9" => $row->DNIMG9,
                        "PHOTO_URL10" => $row->DNIMG10,

                        "BANNER_URL1" => $row->DNBNR1,
                        "BANNER_URL2" => $row->DNBNR2,
                        "BANNER_URL3" => $row->DNBNR3,
                        "BANNER_URL4" => $row->DNBNR4,
                        "BANNER_URL5" => $row->DNBNR5,
                        "BANNER_URL6" => $row->DNBNR6,
                        "BANNER_URL7" => $row->DNBNR7,
                        "BANNER_URL8" => $row->DNBNR8,
                        "BANNER_URL9" => $row->DNBNR9,
                        "BANNER_URL10" => $row->DNBNR10,

                        "Questions" => [
                            [
                                "QA1" => $row->DNQA1,
                                "QA2" => $row->DNQA2,
                                "QA3" => $row->DNQA3,
                                "QA4" => $row->DNQA4,
                                "QA5" => $row->DNQA5,
                                "QA6" => $row->DNQA6,
                                "QA7" => $row->DNQA7,
                                "QA8" => $row->DNQA8,
                                "QA9" => $row->DNQA9
                            ]
                        ]

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

                // if (isset($data['Facilities'])) {
                //     foreach ($data['Facilities'] as &$facility) {
                //         if ($facility['DASH_SECTION_ID'] === $f_id) {
                //             $facility['FACILITY_DETAILS'][] = \array_values($b["Facility_Banner"]);
                //         }
                //     }
                // }

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



    public function drdashboard_dummy(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();

            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['DR_ID'])) {
                $latitude = $input['LATITUDE'];
                $longitude = $input['LONGITUDE'];
                $doctorId = $input['DR_ID'];

                date_default_timezone_set('Asia/Kolkata');
                $date = Carbon::now();
                $weekNumber = $date->weekOfMonth;
                $dayOfWeek = date('l');
                $currentDay = date('d');
                $currentDate = date('Ymd');

                $banners = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'PROMO_NAME', 'PROMO_URL', 'PROMO_TYPE', 'MOBILE_NO', 'DESCRIPTION', 'STATUS')
                    ->where('DASH_SECTION_ID', 'PB')
                    ->orWhere('PHARMA_ID', '0')
                    ->get();

                $distinctDoctors = DB::table('dr_availablity')
                    ->select(
                        'PHARMA_ID',
                        'ID as SCH_ID',
                        'CHK_IN_TIME',
                        'CHK_OUT_TIME',
                        'MAX_BOOK',
                        'CHEMBER_NO',
                        'CHK_IN_STATUS',
                        'DR_FEES',
                        DB::raw("'" . Carbon::now()->format('Ymd') . "' as SCH_DT"),
                    )
                    ->distinct()
                    ->where(['DR_ID' => $doctorId, 'SCH_DAY' => $dayOfWeek]);

                $availabilityData = DB::table('pharmacy')
                    ->joinSub($distinctDoctors, 'distinct_doctors', function ($join) {
                        $join->on('pharmacy.PHARMA_ID', '=', 'distinct_doctors.PHARMA_ID');
                    })
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
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) * COS(RADIANS('$latitude')) * COS(RADIANS(pharmacy.Longitude - '$longitude')) + SIN(RADIANS(pharmacy.Latitude)) * SIN(RADIANS('$latitude'))))), 2) as KM"),
                        'distinct_doctors.SCH_ID',
                        'distinct_doctors.SCH_DT',
                        'distinct_doctors.MAX_BOOK',
                        'distinct_doctors.CHK_IN_TIME',
                        'distinct_doctors.CHK_OUT_TIME',
                        'distinct_doctors.CHK_IN_STATUS',
                        'distinct_doctors.CHEMBER_NO',
                        'distinct_doctors.DR_FEES',
                    )
                    ->get();

                $chambers = [];
                $totalChambers = 0;
                $sumMaxBook = 0;
                $sumTotalBooked = 0;

                foreach ($availabilityData as $row) {
                    $chamber = [
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
                        "KM" => $row->KM,
                        "SCH_ID" => $row->SCH_ID,
                        "SCH_DT" => $row->SCH_DT,
                        "DR_STATUS" => $row->MAX_BOOK,
                        "CHK_IN_TIME" => $row->CHK_IN_TIME,
                        "CHK_OUT_TIME" => $row->CHK_OUT_TIME,
                        "DR_STATUS" => $row->CHK_IN_STATUS,
                        "CHEMBER_NO" => $row->CHEMBER_NO,
                        "MAX_BOOK" => $row->MAX_BOOK,
                        "TOTAL_BOOKED" => 0,
                        "DETAILS" => []
                    ];

                    // Fetch patient details
                    $appointmentData = DB::table('appointment')
                        ->join('user_family', 'user_family.ID', '=', 'appointment.PATIENT_ID')
                        ->select(
                            'appointment.BOOKING_ID',
                            'appointment.FAMILY_ID',
                            'appointment.PATIENT_ID',
                            'appointment.PATIENT_NAME',
                            'user_family.DOB',
                            'user_family.SEX',
                            'user_family.MOBILE',
                            'appointment.APPNT_ID',
                            'appointment.APPNT_DT',
                            'appointment.APPNT_TOKEN',
                            'appointment.BOOKING_SL',
                            'appointment.STATUS',
                            'appointment.APPNT_FROM',
                            'appointment.CHEMBER_NO'
                        )
                        ->where('appointment.APPNT_DT', $row->SCH_DT)
                        ->where('appointment.PHARMA_ID', $row->PHARMA_ID)
                        ->where('appointment.DR_ID', $doctorId)
                        ->orderBy('appointment.BOOKING_SL')
                        ->get();

                    $groupedAppointments = [];
                    foreach ($appointmentData as $appointment) {
                        $groupedAppointments[] = [
                            "FAMILY_ID" => $appointment->FAMILY_ID,
                            "PATIENT_ID" => $appointment->PATIENT_ID,
                            "PATIENT_NAME" => $appointment->PATIENT_NAME,
                            "MOBILE" => $appointment->MOBILE,
                            "AGE" => $appointment->DOB, // Assuming DOB is age, but it might be better to calculate age
                            "SEX" => $appointment->SEX,
                            "BOOKING_ID" => $appointment->BOOKING_ID,
                            "APPNT_TOKEN" => $appointment->APPNT_TOKEN,
                            "APPNT_ID" => $appointment->APPNT_ID,
                            "APPNT_DT" => $appointment->APPNT_DT,
                            "APPNT_FROM" => $appointment->APPNT_FROM,
                            "STATUS" => $appointment->STATUS,
                            "BOOKING_SL" => $appointment->BOOKING_SL
                        ];
                    }

                    $chamber['TOTAL_BOOKED'] = count($groupedAppointments);
                    $chamber['DETAILS'] = array_values($groupedAppointments);

                    $totalChambers++;
                    $sumMaxBook += $row->MAX_BOOK;
                    $sumTotalBooked += $chamber['TOTAL_BOOKED'];

                    $chambers[] = $chamber;
                }

                // Sort chambers by CHK_IN_TIME
                usort($chambers, function ($a, $b) {
                    return strtotime($a['CHK_IN_TIME']) - strtotime($b['CHK_IN_TIME']);
                });

                //SECTION-A #### DR_Details
                $A['Doctor'] = DB::table('drprofile')->where(['DR_ID' => $doctorId])->get();

                //SECTION-B ####
                $B['Dashboard'] = DB::table('dr_dashboard_details')->where(['STATUS' => 'Active'])->get();

                $response = [
                    'Success' => true,
                    'data' => [
                        'Doctor' => $A['Doctor'],
                        'Dashboard' => $B['Dashboard'],
                        'Today_Chember' => $chambers,
                        'Promo_Banner' => $banners->filter(fn($item) => $item->DASH_SECTION_ID === 'PB')->values()->all(),
                        'Today_Summary' => [
                            [
                                'Total_Chambers' => $totalChambers,
                                'Maximum_Booking' => $sumMaxBook,
                                'Total_Booked' => $sumTotalBooked,
                                'Total_OT' => 6,
                                'Total_Round_Visit' => 10
                            ]
                        ]
                    ],
                    'code' => 200
                ];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }

        return response()->json($response);
    }
}
