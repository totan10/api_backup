<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use carbon\carbon;
use dateTime;
use Exception;
use Illuminate\Support\Arr;

class EasyHealths_Admin_Controller extends Controller
{
    function allsrchtst(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['SRCH'])) {
                $nm = $input['SRCH'];

                $response = array();
                $data = array();

                $data2 = DB::table('master_testdata')->where('TEST_NAME', 'like', '%' . $nm . '%')->get();
                foreach ($data2 as $row2) {
                    $tstdtl = [];
                    $tstdtl['DETAILS'] = [
                        "TEST_ID" => $row2->TEST_ID,
                        "TEST_NAME" => $row2->TEST_NAME,
                        "TEST_CODE" => $row2->TEST_CODE,
                        "TEST_CATG" => $row2->TEST_CATG,
                        "SUB_CATG" => $row2->SUB_CATG,
                        "TEST_DESC" => $row2->TEST_DESC,
                        "FASTING" => $row2->FASTING,
                        "ELIGIBLE" => $row2->ELIGIBLE,
                        "WHY_TAKE" => $row2->WHY_TAKE,
                        "BENEFITS" => $row2->BENEFITS,
                        "QA1" => $row2->QA1,
                        "QA2" => $row2->QA2,
                        "QA3" => $row2->QA3,
                        "QA4" => $row2->QA4,
                        "COST" => $row2->COST,
                        "CATEGORY" => $row2->CATEGORY
                    ];

                    $data[] = [
                        "ID" => $row2->TEST_ID,
                        "ITEM_NAME" => $row2->TEST_NAME,
                        "FIELD_TYPE" => $row2->TEST_CATG,
                        "DETAILS" => $tstdtl['DETAILS']
                    ];
                }

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

    function addtest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            $td = $input['TEST_DATA'];

            foreach ($td as $row) {
                $fields = [
                    'PHARMA_ID' => $row['PHARMA_ID'],
                    'HO_ID' => $row['HO_CODE'] ?? null,
                    'TEST_ID' => $row['TEST_ID'],
                    'TEST_UC' => $row['PHARMA_ID'] . $row['TEST_ID'],
                    'TEST_TYPE' => $row['TEST_TYPE'],
                    'TEST_NAME' => $row['TEST_NAME'],
                    'TEST_CODE' => $row['TEST_CODE'] ?? null,
                    // 'TEST_SAMPLE' => $row['TEST_SAMPLE'],
                    'TEST_CATG' => $row['TEST_CATG'],
                    'ORGAN_ID' => $row['ORGAN_ID'] ?? NULL,
                    'DEPT_ID' => $row['DEPT_ID'],
                    'SUB_DEPT_ID' => $row['SUB_DEPT_ID'],
                    'DEPARTMENT' => $row['DEPARTMENT'],
                    'ORGAN_NAME' => $row['ORGAN_NAME'] ?? null,
                    'ORGAN_URL' => $row['ORGAN_URL'] ?? null,
                    // 'CATEGORY' => $row['CATEGORY']??null,
                    // 'TEST_UNIT' => $row['TEST_UNIT']??null,
                    'NORMAL_RANGE' => $row['NORMAL_RANGE'] ?? null,
                    'TEST_DESC' => $row['TEST_DESC'],
                    'KNOWN_AS' => $row['KNOWN_AS'],
                    'FASTING' => $row['FASTING'],
                    'GENDER_TYPE' => $row['GENDER_TYPE'] ?? null,
                    'SAMPLE_ID' => $row['SAMPLE_ID'] ?? null,
                    'TEST_SAMPLE' => $row['SAMPLE_NAME'] ?? null,
                    'AGE_TYPE' => $row['AGE_TYPE'],
                    'REPORT_TIME' => $row['REPORT_TIME'],
                    'PRESCRIPTION' => $row['PRESCRIPTION'],
                    'ID_PROOF' => $row['ID_PROOF'],
                    'QA1' => $row['QA1'] ?? NULL,
                    'QA2' => $row['QA2'] ?? NULL,
                    'QA3' => $row['QA3'] ?? NULL,
                    'QA4' => $row['QA4'] ?? NULL,
                    'QA5' => $row['QA5'] ?? NULL,
                    'QA6' => $row['QA6'] ?? NULL,
                    'COST' => $row['COST'],
                    'DISCOUNT' => $row['DISCOUNT'],
                    'HOME_COLLECT' => $row['HOME_COLLECT'],
                    // 'FREE_AREA' => $row['FREE_AREA'],
                    // 'SERV_CONDITION' => $row['SERV_CONDITION'],

                ];
                try {
                    DB::table('clinic_testdata')->insert($fields);
                    $response = ['Success' => true, 'Message' => 'Test added successfully.', 'code' => 200];
                } catch (\Throwable $th) {
                    $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
                }
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }

    // function testsrch(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $input = $req->json()->all();
    //         if (isset($input['PHARMA_ID'])) {
    //             $PID = $input['PHARMA_ID'];
    //             $response = array();
    //             $data = array();

    //             $added = DB::table('clinic_testdata')
    //                 ->where(["REMARK" => 'Added', 'PHARMA_ID' => $PID])
    //                 ->get();
    //             $testIds = array_column($added->toArray(), 'TEST_ID');
    //             $notadded = DB::table('master_test')
    //                 ->leftJoin('test_scanorgan', 'master_test.ORGAN_ID', 'test_scanorgan.ORGAN_ID')
    //                 ->select(
    //                     'master_test.TEST_ID',
    //                     'master_test.TEST_SL',
    //                     'master_test.TEST_NAME',
    //                     'master_test.TEST_TYPE',
    //                     'master_test.TEST_CODE',
    //                     // 'TEST_SAMPLE',
    //                     'master_test.SUB_DEPARTMENT as TEST_CATG',
    //                     'master_test.SUB_DEPT_ID',
    //                     'master_test.SAMPLE_ID',
    //                     'master_test.SAMPLE_NAME',
    //                     'master_test.ORGAN_ID',
    //                     'test_scanorgan.ORGAN_NAME',
    //                     'test_scanorgan.OIMG1 AS ORGAN_URL',
    //                     // 'CATEGORY',
    //                     // 'TEST_UNIT',
    //                     'master_test.NORMAL_RANGE',
    //                     'master_test.TEST_DESC',
    //                     'master_test.DEPT_ID',
    //                     'master_test.DEPARTMENT',
    //                     'master_test.KNOWN_AS',
    //                     'master_test.FASTING',
    //                     'master_test.GENDER_TYPE',
    //                     'master_test.AGE_TYPE',
    //                     'master_test.PRESCRIPTION',
    //                     'master_test.ID_PROOF',
    //                     'master_test.TQA1 AS QA1',
    //                     'master_test.TQA2 AS QA2',
    //                     'master_test.TQA3 AS QA3',
    //                     'master_test.TQA4 AS QA4',
    //                     'master_test.TQA5 AS QA5',
    //                     'master_test.TQA6 AS QA6',
    //                     // 'master_test.REMARK'
    //                 )
    //                 ->whereNotIn('TEST_ID', $testIds)
    //                 ->get();
    //             $data = $notadded->merge($added);

    //             // Sort data so that items with REMARK != 'Added' come first

    //             if ($data == null) {
    //                 $response = ['Success' => false, 'Message' => 'Test not found', 'code' => 200];
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

    function testsrch(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {
                $PID = $input['PHARMA_ID'];
                $response = array();

                // Fetch added tests with specified columns
                $addedTests = DB::table('clinic_testdata')
                    ->where(["REMARK" => 'Added', 'PHARMA_ID' => $PID])
                    ->select(
                        'PHARMA_ID',
                        'HO_ID',
                        'TEST_ID',
                        'TEST_UC',
                        'TEST_SL',
                        'TEST_NAME',
                        'TEST_TYPE',
                        'TEST_CODE',
                        'DEPT_ID',
                        'SUB_DEPT_ID',
                        'DEPARTMENT',
                        // 'SUB_DEPARTMENT',
                        'TEST_CATG',
                        'ORGAN_ID',
                        'ORGAN_NAME',
                        'ORGAN_URL',
                        'TEST_DESC',
                        'KNOWN_AS',
                        'FASTING',
                        'GENDER_TYPE',
                        'AGE_TYPE',
                        'REPORT_TIME',
                        'PRESCRIPTION',
                        'ID_PROOF',
                        'SAMPLE_ID',
                        'TEST_SAMPLE',
                        'NORMAL_RANGE',
                        'QA1',
                        'QA2',
                        'QA3',
                        'QA4',
                        'QA5',
                        'QA6',
                        'COST',
                        'DISCOUNT',
                        'HOME_COLLECT',
                        'SERV_CONDITION',
                        'STATUS',
                        'REMARK'
                    )
                    ->get();

                $testIds = array_column($addedTests->toArray(), 'TEST_ID');

                // return $addedTests;

                // Fetch not added tests with selected columns
                $notAddedTests = DB::table('master_test')
                    ->leftJoin('test_scanorgan', 'master_test.ORGAN_ID', 'test_scanorgan.ORGAN_ID')
                    ->select(
                        'master_test.TEST_ID',
                        'master_test.TEST_SL',
                        'master_test.TEST_NAME',
                        'master_test.TEST_TYPE',
                        'master_test.TEST_CODE',
                        'master_test.SUB_DEPARTMENT as TEST_CATG',
                        'master_test.SUB_DEPT_ID',
                        'master_test.SAMPLE_ID',
                        'master_test.SAMPLE_NAME',
                        'master_test.ORGAN_ID',
                        'test_scanorgan.ORGAN_NAME',
                        'test_scanorgan.OIMG1 AS ORGAN_URL',
                        'master_test.NORMAL_RANGE',
                        'master_test.TEST_DESC',
                        'master_test.DEPT_ID',
                        'master_test.DEPARTMENT',
                        'master_test.KNOWN_AS',
                        'master_test.FASTING',
                        'master_test.GENDER_TYPE',
                        'master_test.AGE_TYPE',
                        'master_test.PRESCRIPTION',
                        'master_test.ID_PROOF',
                        'master_test.TQA1 AS QA1',
                        'master_test.TQA2 AS QA2',
                        'master_test.TQA3 AS QA3',
                        'master_test.TQA4 AS QA4',
                        'master_test.TQA5 AS QA5',
                        'master_test.TQA6 AS QA6'
                    )
                    ->whereNotIn('master_test.TEST_ID', $testIds)
                    ->get();

                // return $notAddedTests;

                // Merge results
                $data = $notAddedTests->merge($addedTests);


                if ($data->isEmpty()) {
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


    function edittest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            $td = $input['UPDATE_DATA'];

            foreach ($td as $row) {
                $fields = [
                    'TEST_SL' => $row['POSITION'],
                    'COST' => $row['COST'],
                    'DISCOUNT' => $row['DISCOUNT'],
                    'HOME_COLLECT' => $row['HOME_COLLECT'],
                    'STATUS' => $row['STATUS'],
                    'REPORT_TIME' => $row['REPORT_TIME'],
                    // 'FREE_AREA' => $row['FREE_AREA'],
                    // 'SERV_CONDITION' => $row['SERV_CONDITION'],
                ];
                $T_ID = $row['TEST_ID'];
                $PHARMA_ID = $row['PHARMA_ID'];
                try {
                    DB::table('clinic_testdata')
                        ->where('PHARMA_ID', $PHARMA_ID)
                        ->where('TEST_ID', $T_ID)
                        ->update($fields);
                    $response = ['Success' => true, 'Message' => 'Test update successfully.', 'code' => 200];
                } catch (\Throwable $th) {
                    $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 500];
                }
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function edit_pkgtest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();

            $input = $req->all();
            $rmv_arr = isset($input['RMV_DATA']) ? json_decode($input['RMV_DATA'], true) : [];
            $pkg_arr = isset($input['PKG_DATA']) ? json_decode($input['PKG_DATA'], true) : [];
            $tst_arr = isset($input['ADD_DATA']) ? json_decode($input['ADD_DATA'], true) : [];
            $edt_arr = isset($input['EDT_DATA']) ? json_decode($input['EDT_DATA'], true) : [];

            if (is_array($rmv_arr) && !empty($rmv_arr)) {
                foreach ($rmv_arr as $row1) {
                    if (isset($row1['TEST_UC'])) {
                        DB::table('package_details')->where('TEST_UC', $row1['TEST_UC'])->delete();
                    }
                }
            }

            if (is_array($tst_arr) && !empty($tst_arr)) {
                foreach ($tst_arr as $row) {
                    $fields = [
                        "PHARMA_ID" => $row['PHARMA_ID'],
                        "LAB_PKG_ID" => $row['LAB_PKG_ID'],
                        "LAB_PKG_NAME" => $row['LAB_PKG_NAME'],
                        "DASH_SECTION_ID" => $row['DASH_SECTION_ID'],
                        "PKG_ID" => $row['PKG_ID'],
                        "PKG_NAME" => $row['PKG_NAME'],
                        "TEST_ID" => $row['TEST_ID'],
                        "TEST_UC" => $row['PKG_ID'] . $row['TEST_ID'],
                        "TEST_NAME" => $row['TEST_NAME'],
                        "TEST_TYPE" => $row['TEST_TYPE'],
                        "COST" => $row['COST']
                    ];
                    DB::table('package_details')->insert($fields);
                }
            }

            if (is_array($pkg_arr) && !empty($pkg_arr)) {
                foreach ($pkg_arr as $row2) {

                    if ($req->file('FILE')) {
                        $PKG_CT = DB::table('package')->where(["PHARMA_ID" => $row2['PHARMA_ID'], "LAB_PKG_ID" => $row2['PKG_ID']])->count();
                        $fileName = $row2['PHARMA_ID'] . $row2['PKG_ID'] . $PKG_CT . "." . $req->file('FILE')->getClientOriginalExtension();
                        $req->file('FILE')->storeAs('diaghomepage', $fileName);
                        $PKG_URL = asset('storage/app/diaghomepage') . "/" . $fileName;
                    }
                    $PKG_URL = $PKG_URL ?? null;

                    $PHARMA_ID = $row2['PHARMA_ID'];
                    $PKG_ID = $row2['PKG_ID'];
                    $PKG_COST = $row2['PKG_COST'];
                    $PKG_DIS = $row2['PKG_DIS'];
                    $PKG_NAME = $row2['PKG_NAME'];
                    $HOME_COLLECT = $row2['HOME_COLLECT'];
                    $STATUS = $row2['STATUS'];
                    $REPORT_TIME = $row2['REPORT_TIME'];
                    $FASTING = $row2['FASTING'];
                    $GENDER_TYPE = $row2['GENDER_TYPE'];
                    $AGE_TYPE = $row2['AGE_TYPE'];

                    DB::table('package')
                        ->where('PKG_ID', $PKG_ID)
                        ->update([
                            'PKG_NAME_UC' => $PHARMA_ID . $PKG_NAME,
                            'PKG_NAME' => $PKG_NAME,
                            'PKG_URL' => $PKG_URL,
                            'PKG_COST' => $PKG_COST,
                            'PKG_DIS' => $PKG_DIS,
                            'HOME_COLLECT' => $HOME_COLLECT,
                            'STATUS' => $STATUS,
                            'REPORT_TIME' => $REPORT_TIME,
                            'FASTING' => $FASTING,
                            'GENDER_TYPE' => $GENDER_TYPE,
                            'AGE_TYPE' => $AGE_TYPE,
                        ]);
                }
            }

            if (is_array($edt_arr) && !empty($edt_arr)) {
                foreach ($edt_arr as $row3) {
                    $TEST_UC = $row3['TEST_UC'];
                    $PKG_STATUS = $row3['PKG_STATUS'];
                    DB::table('package_details')
                        ->where('TEST_UC', $TEST_UC)
                        ->update([
                            'PKG_STATUS' => $PKG_STATUS
                        ]);
                }
            }

            $response = ['Success' => true, 'Message' => 'Records modified successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return response()->json($response);
    }

    function viewitem(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['ITEM_TYPE'])) {
                $item = $input['ITEM_TYPE'];
                $P_ID = $input['PHARMA_ID'];

                $response = array();
                $data = array();

                $data = DB::table('package')
                    ->select(
                        'AGE_TYPE',
                        'CLINIC_SECTION',
                        // 'DASH_SEC_NAME',
                        'FASTING',
                        'GENDER_TYPE',
                        'ID_PROOF',
                        'PKG_TYPE',
                        'KNOWN_AS',
                        'LAB_PKG_ID',
                        'LAB_PKG_NAME',
                        'LAB_SECTION',
                        'PKG_DESC',
                        'PKG_URL',
                        'PRESCRIPTION',
                        'QA1',
                        'QA2',
                        'QA3',
                        'QA4',
                        'QA5',
                        'QA6',
                        'REPORT_TIME',
                        DB::raw('COUNT(LAB_PKG_ID) as TOT_PACKAGE'),
                    )
                    ->where(['PHARMA_ID' => $P_ID, 'PKG_TYPE' => $item])
                    ->GROUPBY(
                        'AGE_TYPE',
                        'CLINIC_SECTION',
                        // 'DASH_SEC_NAME',
                        'FASTING',
                        'GENDER_TYPE',
                        'ID_PROOF',
                        'PKG_TYPE',
                        'KNOWN_AS',
                        'LAB_PKG_ID',
                        'LAB_PKG_NAME',
                        'LAB_SECTION',
                        'PKG_DESC',
                        'PKG_URL',
                        'PRESCRIPTION',
                        'QA1',
                        'QA2',
                        'QA3',
                        'QA4',
                        'QA5',
                        'QA6',
                        'REPORT_TIME',
                    )
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

    function addedtest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {
                $PID = $input['PHARMA_ID'];

                $response = array();
                $data = array();

                $data = DB::table('clinic_testdata')->where(['PHARMA_ID' => $PID])
                    ->orderby('TEST_SL')
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

    function add_app_pkg(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->all();
            $response = array();

            $P_ID = $input['PHARMA_ID'];
            $L_PKG = $input['LAB_PKG_ID'];
            $PKG_CT = DB::table('package')->where(["PHARMA_ID" => $P_ID, "LAB_PKG_ID" => $L_PKG])->count();

            if (empty($input['PKG_URL'])) {
                $fileName = $P_ID . $L_PKG . $PKG_CT . "." . $req->file('FILE')->getClientOriginalExtension();
                $req->file('FILE')->storeAs('diaghomepage', $fileName);
                $PKG_URL = asset(storage::url('app/diaghomepage')) . "/" . $fileName;
            } else {
                $PKG_URL = $input['PKG_URL'];
            }
            $fields = [
                'PHARMA_ID' => $input['PHARMA_ID'],
                'HO_ID' => $input['HO_ID'],
                'PKG_ID' => $P_ID . $L_PKG . $PKG_CT,
                'LAB_PKG_ID' => $input['LAB_PKG_ID'],
                'LAB_PKG_NAME' => $input['LAB_PKG_NAME'],
                'DASH_SECTION_ID' => $input['DASH_SECTION_ID'],
                'DASH_SECTION_NAME' => $input['DASH_SECTION_NAME'],
                // 'DASHBOARD_SECTION_NAME' => $input['DASH_SEC_NAME'],
                'PKG_NAME' => $input['PKG_NAME'],
                'PKG_TYPE' => $input['PKG_TYPE'],
                'PKG_NAME_UC' => $input['PHARMA_ID'] . $input['PKG_NAME'],
                'PKG_DESC' => $input['PKG_DESC'],
                'PKG_URL' => $PKG_URL,
                'KNOWN_AS' => $input['KNOWN_AS'],
                'FASTING' => $input['FASTING'] ?? null,
                'GENDER_TYPE' => $input['GENDER_TYPE'] ?? null,
                'AGE_TYPE' => $input['AGE_TYPE'] ?? null,
                'REPORT_TIME' => $input['REPORT_TIME'] ?? null,
                'PRESCRIPTION' => $input['PRESCRIPTION'],
                'ID_PROOF' => $input['ID_PROOF'],
                'HOME_COLLECT' => $input['HOME_COLLECT'],
                'QA1' => $input['QA1'],
                'QA2' => $input['QA2'],
                'QA3' => $input['QA3'],
                'QA4' => $input['QA4'],
                'QA5' => $input['QA5'],
                'QA6' => $input['QA6']
            ];
            try {
                DB::table('package')->insert($fields);
                $response = ['Success' => true, 'Message' => 'Record added successfully.', 'code' => 200];
            } catch (\Throwable $th) {
                $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 500];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_pkgtest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();

            $input = $req->json()->all();
            $pkgdata = $input['PKG_DATA'];

            foreach ($pkgdata as $row) {
                $fields = [
                    "PHARMA_ID" => $row['PHARMA_ID'],
                    "LAB_PKG_ID" => $row['LAB_PKG_ID'],
                    "LAB_PKG_NAME" => $row['LAB_PKG_NAME'],
                    // "LAB_SECTION" => $row['LAB_SECTION'],
                    "PKG_ID" => $row['PKG_ID'],
                    "PKG_NAME" => $row['PKG_NAME'],
                    "DASH_SECTION_ID" => $row['DASH_SECTION_ID'],
                    "TEST_ID" => $row['TEST_ID'],
                    "TEST_UC" => $row['PKG_ID'] . $row['TEST_ID'],
                    "TEST_NAME" => $row['TEST_NAME'],
                    "TEST_TYPE" => $row['TEST_TYPE'],
                    "COST" => $row['COST']
                ];
                $PKG_ID = $row['PKG_ID'];
                $PKG_COST = $row['PKG_COST'];
                $PKG_DIS = $row['PKG_DIS'];
                DB::table('package_details')->insert($fields);
            }
            DB::table('package')
                ->where('PKG_ID', $PKG_ID)
                ->update([
                    'PKG_COST' => $PKG_COST,
                    'PKG_DIS' => $PKG_DIS
                ]);
            $response = ['Success' => true, 'Message' => 'Test added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }

    function del_pkgtest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();

            $input = $req->json()->all();
            if (isset($input['PKG_ID']) && isset($input['PHARMA_ID'])) {
                DB::table('package_details')->where(['PHARMA_ID' => $input['PHARMA_ID'], 'PKG_ID' => $input['PKG_ID']])->delete();
                DB::table('package')->where(['PHARMA_ID' => $input['PHARMA_ID'], 'PKG_ID' => $input['PKG_ID']])->delete();
                $response = ['Success' => true, 'Message' => 'Records deleted successfully.', 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }

    function vupkgtest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID']) && isset($input['LAB_PKG_ID'])) {

                $PID = $input['PHARMA_ID'];
                $L_PKG_ID = $input['LAB_PKG_ID'];

                $response = array();
                $data = array();

                $data1 = DB::table('package')
                    ->leftjoin('package_details', 'package.PKG_ID', '=', 'package_details.PKG_ID')
                    ->select('package.*', 'package_details.TEST_ID', 'package_details.TEST_NAME', 'package_details.COST', 'package_details.TEST_UC', 'package_details.TEST_TYPE')
                    ->where(['package.PHARMA_ID' => $PID, 'package.LAB_PKG_ID' => $L_PKG_ID])
                    ->orderby('package_details.TEST_ID')
                    ->get();
                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->PKG_ID])) {
                        $groupedData[$row->PKG_ID] = [
                            "PHARMA_ID" => $row->PHARMA_ID,
                            "HO_ID" => $row->HO_ID,
                            "LAB_PKG_ID" => $row->LAB_PKG_ID,
                            "PKG_ID" => $row->PKG_ID,
                            "LAB_PKG_NAME" => $row->LAB_PKG_NAME,
                            "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
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
                            "STATUS" => $row->STATUS,
                            "TEST_DETAILS" => []
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
                                    "TEST_DETAILS" => []
                                ];
                            }
                            $groupedData1['TEST_DETAILS'][] = [
                                "TEST_ID" => $row2->TEST_ID,
                                "TEST_UC" => $row2->TEST_UC,
                                "TEST_NAME" => $row2->TEST_NAME,
                                "TEST_TYPE" => $row2->TEST_TYPE,
                                "COST" => $row2->COST,
                                "PKG_STATUS" => $row2->PKG_STATUS,
                            ];
                        }
                        $groupedData[$row->PKG_ID]['TEST_DETAILS']['PROFILE_DETAILS'][$row->TEST_ID] = $groupedData1;
                    } else {
                        $groupedData[$row->PKG_ID]['TEST_DETAILS'][] = [
                            "TEST_ID" => $row->TEST_ID,
                            "TEST_UC" => $row->TEST_UC,
                            "TEST_NAME" => $row->TEST_NAME,
                            "TEST_TYPE" => $row->TEST_TYPE,
                            "COST" => $row->COST,
                        ];
                    }
                    foreach ($groupedData as $detailId => &$detail) {
                        $totalTests = 0;
                        if (isset($detail['TEST_DETAILS']['PROFILE_DETAILS'])) {
                            foreach ($detail['TEST_DETAILS']['PROFILE_DETAILS'] as $profile) {
                                foreach ($profile['TEST_DETAILS'] as $test) {
                                    if ($test['TEST_TYPE'] == 'Test' && $test['PKG_STATUS'] == 'Active') {
                                        $totalTests++;
                                    }
                                }
                            }
                        }
                        foreach ($detail['TEST_DETAILS'] as $testDetail) {
                            if (!isset($testDetail['PROFILE_DETAILS']) && isset($testDetail['TEST_TYPE']) && $testDetail['TEST_TYPE'] == 'Test') {
                                $totalTests++;
                            }
                        }
                        $detail['TOT_TEST'] = $totalTests;
                    }
                }
                $data = array_values($groupedData);
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function pkgproftestsrch(Request $req)
    {
        if ($req->isMethod('post')) { // Use Laravel's built-in method check
            $input = $req->json()->all();

            if (isset($input['PHARMA_ID'], $input['PKG_ID'])) { // Combined isset for both variables

                $P_ID = $input['PHARMA_ID'];
                $PKG_ID = $input['PKG_ID'];

                $added = DB::table('package_details')
                    ->select('TEST_ID', 'TEST_UC', 'TEST_NAME', 'TEST_TYPE', 'COST', 'PKG_RMK AS REMARK', DB::raw('COUNT(TEST_ID) as TOT_TEST'))
                    ->groupBy('TEST_ID', 'TEST_UC', 'TEST_NAME', 'TEST_TYPE', 'COST', 'PKG_RMK')
                    ->where(['PKG_RMK' => 'Added', 'PHARMA_ID' => $P_ID, 'PKG_ID' => $PKG_ID])
                    ->get();

                $testIds = $added->pluck('TEST_ID')->all();

                // $notadded = DB::table('clinic_testdata')
                //     ->select('TEST_ID', 'TEST_UC', 'TEST_NAME', 'TEST_TYPE', 'COST', 'PKG_RMK AS REMARK', DB::raw('COUNT(TEST_ID) as TOT_TEST'))
                //     ->groupBy('TEST_ID', 'TEST_UC', 'TEST_NAME', 'TEST_TYPE', 'COST', 'PKG_RMK')
                //     ->where('PHARMA_ID', $P_ID)
                //     ->whereNotIn('TEST_ID', $testIds)
                //     ->get();

                // $notadded = DB::table('clinic_testdata')
                //     ->select('TEST_ID', 'TEST_UC', 'TEST_NAME', 'TEST_TYPE', 'COST', 'PKG_RMK AS REMARK', DB::raw('1 as TOT_TEST'))
                //     ->where('PHARMA_ID', $P_ID)
                //     ->whereNotIn('TEST_ID', $testIds)
                //     ->get();

                $notadded = DB::table('clinic_testdata')
                    ->select('TEST_ID', 'TEST_UC', 'TEST_NAME', 'TEST_TYPE', 'COST', 'PKG_RMK AS REMARK', DB::raw('1 as TOT_TEST'))
                    ->where('PHARMA_ID', $P_ID)
                    ->whereNotIn('TEST_ID', $testIds)
                    ->get()
                    ->map(function ($item) {
                        $item->TEST_ID = (string) $item->TEST_ID;
                        return $item;
                    });



                $notaddedperofile = DB::table('package')
                    ->join('package_details', 'package.PKG_ID', '=', 'package_details.PKG_ID')
                    ->select(['package.PKG_ID as TEST_ID', 'package.PKG_NAME_UC AS TEST_UC', 'package.PKG_NAME AS TEST_NAME', 'package.PKG_TYPE AS TEST_TYPE', 'package.PKG_COST AS COST', 'package.PKG_RMK AS REMARK', DB::raw('COUNT(DISTINCT package_details.TEST_ID) as TOT_TEST')])
                    ->groupBy('package.PKG_ID', 'package.PKG_NAME_UC', 'package.PKG_NAME', 'package.PKG_TYPE', 'package.PKG_COST', 'package.PKG_RMK')
                    ->where(['package.PKG_TYPE' => 'Profile', 'package_details.PHARMA_ID' => $P_ID])
                    ->whereNotIn('package.PKG_ID', $testIds)
                    ->get();

                $data = $added->merge($notadded)->merge($notaddedperofile);

                return $data->isEmpty()
                    ? ['Success' => false, 'Message' => 'Test not found', 'code' => 200]
                    : ['Success' => true, 'data' => $data, 'code' => 200];
            }
            return ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
        }
        return ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
    }

    function vuallpkgtest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID']) && isset($input['PKG_TYPE'])) {

                $PID = $input['PHARMA_ID'];
                $ptype = $input['PKG_TYPE'];
                $response = array();
                $data = array();

                $data1 = DB::table('package')
                    ->leftjoin('package_details', 'package.PKG_ID', '=', 'package_details.PKG_ID')
                    ->select(
                        'package.PHARMA_ID',
                        'package.HO_CODE',
                        'package.LAB_PKG_ID',
                        'package.PKG_ID',
                        'package.LAB_PKG_NAME',
                        'package.LAB_SECTION',
                        'package.CLINIC_SECTION',
                        'package.PKG_NAME',
                        'package.PKG_TYPE',
                        'package.PKG_DESC',
                        'package.PKG_URL',
                        'package.KNOWN_AS',
                        'package.FASTING',
                        'package.GENDER_TYPE',
                        'package.AGE_TYPE',
                        'package.REPORT_TIME',
                        'package.PRESCRIPTION',
                        'package.ID_PROOF',
                        'package.QA1',
                        'package.QA2',
                        'package.QA3',
                        'package.QA4',
                        'package.QA5',
                        'package.QA6',
                        'package.PKG_COST',
                        'package.PKG_DIS',
                        'package.HOME_COLLECT',
                        'package.FREE_AREA',
                        'package.SERV_CONDITION',
                        'package.STATUS',
                        'package_details.TEST_ID',
                        'package_details.TEST_NAME',
                        'package_details.COST',
                        'package_details.TEST_UC',
                        'package_details.TEST_TYPE',
                    )
                    ->where(['package.PHARMA_ID' => $PID, 'package.PKG_TYPE' => $ptype])
                    ->orderby('package_details.TEST_ID')
                    ->get();
                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->PKG_ID])) {
                        $groupedData[$row->PKG_ID] = [
                            "PHARMA_ID" => $row->PHARMA_ID,
                            "HO_CODE" => $row->HO_CODE,
                            "LAB_PKG_ID" => $row->LAB_PKG_ID,
                            "PKG_ID" => $row->PKG_ID,
                            "LAB_PKG_NAME" => $row->LAB_PKG_NAME,
                            "LAB_SECTION" => $row->LAB_SECTION,
                            "CLINIC_SECTION" => $row->CLINIC_SECTION,
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
                            "STATUS" => $row->STATUS,
                            "DETAILS" => []
                        ];
                    }
                    if (!is_null($row->TEST_ID)) {
                        $groupedData[$row->PKG_ID]['DETAILS'][] = [
                            "TEST_ID" => $row->TEST_ID,
                            "TEST_UC" => $row->TEST_UC,
                            "TEST_NAME" => $row->TEST_NAME,
                            "COST" => $row->COST,
                            "TEST_TYPE" => $row->TEST_TYPE,
                        ];
                    }
                }
                foreach ($groupedData as $pkgId => &$package) {
                    $package['TOT_TEST'] = count($package['DETAILS']);
                }
                $data = array_values($groupedData);
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function pkg_dtls(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['ITEM_TYPE']) && isset($input['PHARMA_ID'])) {

                $test_catg = $input['ITEM_TYPE'];
                $p_id = $input['PHARMA_ID'];

                $data = array();

                $data1 = DB::table('dashboard_section')
                    ->join('dashboard_item', 'dashboard_section.DASH_SECTION_ID', 'dashboard_item.DASH_SECTION_ID')
                    ->leftJoin('package', function ($join) use ($p_id) {
                        $join->on('dashboard_item.ID', '=', 'package.LAB_PKG_ID')
                            ->where('package.PHARMA_ID', '=', $p_id)
                            ->orderby('package.PKG_ID');
                    })
                    ->leftJoin('package_details', function ($join) use ($p_id) {
                        $join->on('package.PKG_ID', '=', 'package_details.PKG_ID')
                            ->where('package_details.PHARMA_ID', '=', $p_id)
                            ->orderby('package_details.PKG_ID');
                    })
                    ->select(
                        'dashboard_item.ID',
                        'dashboard_item.DASH_SECTION_ID',
                        'dashboard_item.DASH_NAME',
                        'dashboard_section.DASH_SECTION_NAME',
                        'dashboard_item.DASH_DESC',
                        'dashboard_item.DI_IMG1 AS PHOTO_URL1',
                        'dashboard_item.DI_IMG2 AS PHOTO_URL2',
                        'dashboard_item.DI_IMG3 AS PHOTO_URL3',
                        'dashboard_item.DI_IMG4 AS PHOTO_URL4',
                        'dashboard_item.DI_IMG5 AS PHOTO_URL5',
                        'dashboard_item.DI_IMG6 AS PHOTO_URL6',
                        'dashboard_item.DI_IMG7 AS PHOTO_URL7',
                        'dashboard_item.DI_IMG8 AS PHOTO_URL8',
                        'dashboard_item.DI_IMG9 AS PHOTO_URL9',
                        'dashboard_item.DI_IMG10 AS PHOTO_URL10',
                        'package.AGE_TYPE',
                        'package.FASTING',
                        'package.GENDER_TYPE',
                        'package.HO_ID',
                        'package.ID_PROOF',
                        'package.KNOWN_AS',
                        'package.PRESCRIPTION',
                        'dashboard_item.DIQA1',
                        'dashboard_item.DIQA2',
                        'dashboard_item.DIQA3',
                        'dashboard_item.DIQA4',
                        'dashboard_item.DIQA5',
                        'dashboard_item.DIQA6',
                        'dashboard_item.DIQA7',
                        'dashboard_item.DIQA8',
                        'dashboard_item.DIQA9',
                        'package.REPORT_TIME',
                        'dashboard_section.DS_TYPE',
                        'package.PHARMA_ID',
                        'package.PKG_COST',
                        'package.PKG_DIS',
                        'package.PKG_ID',
                        'package.PKG_NAME',
                        'package.PKG_URL',
                        'package.HOME_COLLECT',
                        'package.STATUS',
                        'package_details.TEST_ID',
                        'package_details.TEST_UC',
                        'package_details.TEST_NAME',
                        'package_details.TEST_TYPE',
                        'package_details.COST',
                        'package_details.PKG_STATUS',
                    )
                    ->where(['dashboard_section.DS_TYPE' => $test_catg])
                    ->orderby('dashboard_item.ID')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    $photoUrl = null;
                    for ($i = 1; $i <= 10; $i++) {
                        $photoField = "PHOTO_URL{$i}";
                        if (!empty($row->$photoField)) {
                            $photoUrl = $row->$photoField;
                            break;
                        }
                    }

                    if (!isset($groupedData[$row->ID])) {
                        $groupedData[$row->ID] = [
                            "LAB_PKG_ID" => $row->ID,
                            "LAB_PKG_NAME" => $row->DASH_NAME,
                            "PKG_DESC" => $row->DASH_DESC,
                            "PHOTO_URL" => $photoUrl,
                            "AGE_TYPE" => $row->AGE_TYPE,
                            "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                            "FASTING" => $row->FASTING,
                            "GENDER_TYPE" => $row->GENDER_TYPE,
                            "ID_PROOF" => $row->ID_PROOF,
                            "KNOWN_AS" => $row->KNOWN_AS,
                            "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                            "PRESCRIPTION" => $row->PRESCRIPTION,
                            "QA1" => $row->DIQA1,
                            "QA2" => $row->DIQA2,
                            "QA3" => $row->DIQA3,
                            "QA4" => $row->DIQA4,
                            "QA5" => $row->DIQA5,
                            "QA6" => $row->DIQA6,
                            "REPORT_TIME" => $row->REPORT_TIME,
                            "PKG_TYPE" => $row->DS_TYPE,
                            "PKG_DETAILS" => []
                        ];
                    }
                    if (!isset($groupedData[$row->ID]['PKG_DETAILS'][$row->PKG_ID])) {
                        $groupedData[$row->ID]['PKG_DETAILS'][$row->PKG_ID] = [
                            "LAB_PKG_ID" => $row->ID,
                            "LAB_PKG_NAME" => $row->DASH_NAME,
                            "PKG_DESC" => $row->DASH_DESC,
                            "PKG_URL" => $row->PKG_URL,
                            "AGE_TYPE" => $row->AGE_TYPE,
                            "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                            "FASTING" => $row->FASTING,
                            "GENDER_TYPE" => $row->GENDER_TYPE,
                            "HOME_COLLECT" => $row->HOME_COLLECT,
                            "HO_ID" => $row->HO_ID,
                            "ID_PROOF" => $row->ID_PROOF,
                            "KNOWN_AS" => $row->KNOWN_AS,
                            "PHARMA_ID" => $row->PHARMA_ID,
                            "PKG_COST" => $row->PKG_COST,
                            "PKG_DIS" => $row->PKG_DIS,
                            "PKG_ID" => $row->PKG_ID,
                            "PKG_NAME" => $row->PKG_NAME,
                            "PKG_TYPE" => $row->DS_TYPE,
                            "QA1" => $row->DIQA1,
                            "QA2" => $row->DIQA2,
                            "QA3" => $row->DIQA3,
                            "QA4" => $row->DIQA4,
                            "QA5" => $row->DIQA5,
                            "QA6" => $row->DIQA6,
                            "REPORT_TIME" => $row->REPORT_TIME,
                            "STATUS" => $row->STATUS,
                            "TEST_DETAILS" => []
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
                            $photoUrl2 = null;
                            for ($i = 1; $i <= 10; $i++) {
                                $photoField = "PHOTO_URL{$i}";
                                if (!empty($row2->$photoField)) {
                                    $photoUrl2 = $row2->$photoField;
                                    break;
                                }
                            }

                            if (empty($groupedData1)) {
                                $groupedData1 = [
                                    "ID" => $row2->ID,
                                    "PHARMA_ID" => $row2->PHARMA_ID,
                                    "HO_ID" => $row2->HO_ID,
                                    "PKG_ID" => $row2->PKG_ID,
                                    "TEST_UC" => $row2->TEST_UC,
                                    "PKG_SL" => $row2->PKG_SL,
                                    "LAB_PKG_ID" => $row2->LAB_PKG_ID,
                                    "LAB_PKG_NAME" => $row2->LAB_PKG_NAME,
                                    "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                                    "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                                    "PKG_NAME" => $row2->PKG_NAME,
                                    "PKG_NAME_UC" => $row2->PKG_NAME_UC,
                                    "PKG_TYPE" => $row2->PKG_TYPE,
                                    "PKG_DESC" => $row2->PKG_DESC,
                                    "PKG_URL" => $photoUrl2,
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
                                    "TEST_DETAILS" => []
                                ];
                            }
                            if ($row2->TEST_TYPE == 'Test' || $row2->TEST_TYPE == 'Profile') {
                                $groupedData1['TEST_DETAILS'][] = [
                                    "TEST_ID" => $row2->TEST_ID,
                                    "TEST_UC" => $row2->TEST_UC,
                                    "TEST_NAME" => $row2->TEST_NAME,
                                    "TEST_TYPE" => $row2->TEST_TYPE,
                                    "COST" => $row2->COST,
                                    "PKG_STATUS" => $row2->PKG_STATUS,
                                ];
                            }
                        }

                        $groupedData[$row->ID]['PKG_DETAILS'][$row->PKG_ID]['TEST_DETAILS']['PROFILE_DETAILS'][$row->TEST_ID] = $groupedData1;
                    } else {
                        if ($row->TEST_TYPE == 'Test' || $row->TEST_TYPE == 'Profile') {
                            $groupedData[$row->ID]['PKG_DETAILS'][$row->PKG_ID]['TEST_DETAILS'][] = [
                                "TEST_ID" => $row->TEST_ID,
                                "TEST_UC" => $row->TEST_UC,
                                "TEST_NAME" => $row->TEST_NAME,
                                "TEST_TYPE" => $row->TEST_TYPE,
                                "COST" => $row->COST,
                            ];
                        }
                    }
                    foreach ($groupedData as $pkgId => &$package) {
                        $package['TOT_PACKAGE'] = count($package['PKG_DETAILS']);
                        foreach ($package['PKG_DETAILS'] as $detailId => &$detail) {
                            $totalTests = 0;
                            if (isset($detail['TEST_DETAILS']['PROFILE_DETAILS'])) {
                                foreach ($detail['TEST_DETAILS']['PROFILE_DETAILS'] as $profile) {
                                    if (isset($profile['TEST_DETAILS'])) {
                                        foreach ($profile['TEST_DETAILS'] as $test) {
                                            if ($test['TEST_TYPE'] == 'Test') {
                                                $totalTests++;
                                            }
                                        }
                                    }
                                }
                            }
                            foreach ($detail['TEST_DETAILS'] as $testDetail) {
                                if (!isset($testDetail['PROFILE_DETAILS']) && (isset($testDetail['TEST_TYPE']) && $testDetail['TEST_TYPE'] == 'Test')) {
                                    $totalTests++;
                                }
                            }
                            $detail['TOT_TEST'] = $totalTests;
                        }
                    }
                }
                $data = array_values($groupedData);
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }




    function admtesthis(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $headers = apache_request_headers();
            session_start();
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
            if (isset($input['PHARMA_ID'])) {
                $f_id = $input['PHARMA_ID'];
                $currentDate = date('Ymd');
                $data1 = DB::table('booktest')
                    ->join('pharmacy', 'booktest.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    ->join('user_family', 'booktest.PATIENT_ID', '=', 'user_family.ID')
                    ->select('booktest.*', 'user_family.*', 'pharmacy.*', 'booktest.STATUS')
                    ->where('booktest.PHARMA_ID', '=', $f_id)
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->orderbydesc('booktest.BOOKING_DT')
                    ->orderbydesc('booktest.BOOKING_TM')
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
                            "SLOT_DT" => $row->SLOT_DT,
                            "FROM" => $row->FROM,
                            "TO" => $row->TO,
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
                            "PAY_MODE" => $row->PAY_MODE,
                            "HOME_COLLECT" => $row->HOME_COLLECT,
                            "TRANS_ID" => $row->TRANS_ID,
                            "DETAILS" => []
                        ];
                    }
                    if (!isset($groupedData[$row->BOOKING_ID]['DETAILS'][$row->CATEGORY])) {
                        $groupedData[$row->BOOKING_ID]['DETAILS'][$row->CATEGORY] = [];
                    }
                    $groupedData[$row->BOOKING_ID]['DETAILS'][$row->CATEGORY][] = [
                        "PKG_ID" => $row->PKG_ID,
                        "PKG_NAME" => $row->PKG_NAME,
                        "CATEGORY" => $row->CATEGORY,
                        "TEST_COST" => $row->TEST_COST,
                        "PAY_MODE" => $row->PAY_MODE,
                        "HOME_COLLECT" => $row->HOME_COLLECT,
                        "TRANS_ID" => $row->TRANS_ID,
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

    function preshis(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $headers = apache_request_headers();
            session_start();
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
            if (isset($input['PHARMA_ID'])) {
                $f_id = $input['PHARMA_ID'];

                $data1 = DB::table('prescription')
                    ->join('pharmacy', 'prescription.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    ->join('user_family', 'prescription.PATIENT_ID', '=', 'user_family.ID')
                    ->select('prescription.*', 'user_family.*', 'pharmacy.*')
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->where('prescription.PHARMA_ID', '=', $f_id)
                    ->orderby('prescription.PRESCRIBE_ID')
                    ->get();

                $response = ['Success' => true, 'data' => $data1, 'code' => 200];
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

    function createstaff(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');

            $response = array();
            $row = $req->all();

            $fileName = $row['STAFF_ID'] . "." . $req->file('file')->getClientOriginalExtension();
            $req->file('file')->storeAs('staff', $fileName);
            $url = asset(storage::url('app/staff')) . "/" . $fileName;

            $fields = [
                "STAFF_ID" => $row['STAFF_ID'],
                "STAFF_PWD" => md5(1234),
                "STAFF_NAME" => $row['STAFF_NAME'],
                "STAFF_ADDRESS" => $row['STAFF_ADDRESS'] ?? null,
                "STAFF_DESIGN" => $row['STAFF_DESIGN'],
                "STAFF_MOB" => $row['STAFF_MOB'],
                "STAFF_AGE" => $row['STAFF_AGE'],
                "STAFF_SEX" => $row['STAFF_SEX'],
                "STAFF_PHOTO" => $url,
                "DEPT" => $row['DEPT'],
                "SUB_DEPT" => $row['SUB_DEPT'] ?? null,
                "PHARMA_ID" => $row['PHARMA_ID'],
            ];

            try {
                DB::table('user_staff')->insert($fields);
                $response = ['Success' => true, 'Message' => 'Staff create successfully.', 'code' => 200];
            } catch (\Throwable $th) {
                $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function viewstaff(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {

                $p_id = $input['PHARMA_ID'];
                $data = array();

                $data1 = DB::table('user_staff')->where(['PHARMA_ID' => $p_id])->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->DEPT])) {
                        $groupedData[$row->DEPT] = [
                            "DEPARTMENT" => $row->DEPT,
                            "DEPT_STAFF" => []
                        ];
                    }
                    $groupedData[$row->DEPT]['DEPT_STAFF'][] = [
                        "DEPARTMENT" => $row->DEPT,
                        "STAFF_ID" => $row->STAFF_ID,
                        "STAFF_NAME" => $row->STAFF_NAME,
                        "STAFF_ADDRESS" => $row->STAFF_ADDRESS,
                        "STAFF_MOB" => $row->STAFF_MOB,
                        "STAFF_DESIGN" => $row->STAFF_DESIGN,
                        "STAFF_AGE" => $row->STAFF_AGE,
                        "STAFF_SEX" => $row->STAFF_SEX,
                        "STAFF_PHOTO" => $row->STAFF_PHOTO,
                        "SUB_DEPT" => $row->SUB_DEPT,
                        "CASH_COLLECT" => $row->CASH_COLLECT,
                        "STAFF_STATUS" => $row->STAFF_STATUS,
                        "WALLET" => $row->WALLET,
                        "USER_CREATE" => $row->USER_CREATE,
                        "EDIT_USER" => $row->EDIT_USER,
                        "DELETE_USER" => $row->DELETE_USER,
                        "TRANS_REQ" => $row->TRANS_REQ,
                        "TEST_BOOK" => $row->TEST_BOOK,
                    ];

                    foreach ($groupedData as $deptId => &$dept) {
                        $dept['TOT_STAFF'] = count($dept['DEPT_STAFF']);
                    }
                }

                $data = array_values($groupedData);
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function viewallstaff(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {

                $p_id = $input['PHARMA_ID'];

                $data1 = DB::table('user_staff')->where(['PHARMA_ID' => $p_id])->get();

                $response = ['Success' => true, 'data' => $data1, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function userdashboard(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['PHARMA_ID']) && isset($input['STAFF_ID'])) {

                $sid = $input['STAFF_ID'];
                $pid = $input['PHARMA_ID'];
                $data = array();

                $banner = DB::table('fxd_banner')->select('BANNER_ID', 'BANNER_TYPE', 'BANNER_URL', 'MOBILE_NO', 'STATUS')->get();
                $promo_bnr = DB::table('promo_banner')->where(['STATUS' => 'Active', 'PHARMA_ID' => $pid])->get();
                //SECTION-A ####
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'DA';
                });
                $A["Slider"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "SLIDER_ID" => $item->PROMO_ID,
                        "SLIDER_NAME" => $item->PROMO_NAME,
                        "SLIDER_URL" => $item->PROMO_URL,
                    ];
                })->values()->all();
                //SECTION-B ####
                $B['Pharma'] = DB::table('pharmacy')
                    ->select('PHARMA_ID', 'ITEM_NAME AS PHARMA_NAME', 'CLINIC_TYPE', 'ADDRESS', 'CITY', 'DIST', 'STATE', 'PIN', 'CLINIC_MOBILE', 'PHOTO_URL', 'LOGO_URL')
                    ->where(['PHARMA_ID' => $sid, 'STATUS' => 'Active'])->where('pharmacy.STATUS', '=', 'Active')->first();

                //SECTION-C ####
                $C['Dashboard'] = DB::table('user_staff')
                    ->select('CASH_COLLECT', 'WALLET', 'USER_CREATE', 'EDIT_USER', 'DELETE_USER', 'TRANS_REQ', 'TEST_BOOK')
                    ->where(['PHARMA_ID' => $pid, 'STAFF_ID' => $sid, 'STAFF_STATUS' => 'Active'])->take(1)->get();

                //SECTION-D ####
                $fltr_bnr = $banner->filter(function ($item) {
                    return $item->BANNER_TYPE === 'Call Banner';
                });
                $D["Call_Banner"] = $fltr_bnr->values()->all();

                //SECTION-E ####
                $fltr_bnr = $banner->filter(function ($item) {
                    return $item->BANNER_TYPE === 'IP';
                });
                $E["EasyHealths_Banner1"] = $fltr_bnr->values()->all();

                //SECTION-F ####
                $fltr_bnr = $banner->filter(function ($item) {
                    return $item->BANNER_TYPE === 'Specialist';
                });
                $F["EasyHealths_Banner2"] = $fltr_bnr->values()->all();

                //SECTION-G ####
                $fltr_bnr = $banner->filter(function ($item) {
                    return $item->BANNER_TYPE === 'Specialist';
                });
                $G["EasyHealths_Banner3"] = $fltr_bnr->values()->take(1)->all();

                $data = $A + $B + $C + $D + $E + $F + $G;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function viewdoctors(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {

                $p_id = $input['PHARMA_ID'];
                $data = array();

                $data1 = DB::table('drprofile')
                    ->leftjoin('dr_availablity', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
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
                        'drprofile.PHOTO_URL',
                        'drprofile.APPROVE',
                        'dr_availablity.ID',
                        'dr_availablity.DR_ID',
                        'dr_availablity.PHARMA_ID',
                        'dr_availablity.DR_FEES',
                        'dr_availablity.SCH_DAY',
                        'dr_availablity.SCH_DT',
                        'dr_availablity.SLOT',
                        'dr_availablity.WEEK',
                        'dr_availablity.MONTH',
                        'dr_availablity.START_MONTH',
                        'dr_availablity.SCH_STATUS',
                        'dr_availablity.BOOK_ST_DT',
                        'dr_availablity.BOOK_ST_TM',
                        'dr_availablity.CHK_IN_TIME',
                        'dr_availablity.CHK_OUT_TIME',
                        'dr_availablity.CHK_IN_TIME1',
                        'dr_availablity.CHK_OUT_TIME1',
                        'dr_availablity.CHK_IN_TIME2',
                        'dr_availablity.CHK_OUT_TIME2',
                        'dr_availablity.CHK_IN_TIME3',
                        'dr_availablity.CHK_OUT_TIME3',
                        'dr_availablity.MAX_BOOK',
                        'dr_availablity.MAX_BOOK1',
                        'dr_availablity.MAX_BOOK2',
                        'dr_availablity.MAX_BOOK3',
                        'dr_availablity.SLOT_INTVL',
                        'dr_availablity.SLOT_APPNT',
                        'dr_availablity.AVAIL_STATUS',
                    )
                    ->where(['dr_availablity.PHARMA_ID' => $p_id])
                    ->where('drprofile.APPROVE', 'true')
                    // ->orderBy('dr_availablity.DR_ID')
                    // ->orderbyraw("FIELD(dr_availablity.SCH_DAY,'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saterday')")
                    ->get();

                // RETURN $data1;
                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->DR_ID])) {
                        $groupedData[$row->DR_ID] = [
                            "DR_ID" => $row->DR_ID,
                            "DIS_ID" => $row->DIS_ID,
                            "DR_NAME" => $row->DR_NAME,
                            "DR_MOBILE" => $row->DR_MOBILE,
                            "SEX" => $row->SEX,
                            "DESIGNATION" => $row->DESIGNATION,
                            "QUALIFICATION" => $row->QUALIFICATION,
                            "D_CATG" => $row->D_CATG,
                            "EXPERIENCE" => $row->EXPERIENCE,
                            "LANGUAGE" => $row->LANGUAGE,
                            "DR_PHOTO" => $row->PHOTO_URL,
                            "DR_FEES" => $row->DR_FEES,
                            "APPROVE" => $row->APPROVE,
                            "TOT_SCH" => 0,
                            "SCHEDULE_DETAILS" => []
                        ];
                    }
                    if (!isset($groupedData[$row->DR_ID]['SCHEDULE_DETAILS'][$row->SCH_DAY])) {
                        $groupedData[$row->DR_ID]['SCHEDULE_DETAILS'][$row->SCH_DAY] = [
                            "SCH_ID" => $row->ID,
                            "SCH_DAY" => $row->SCH_DAY,
                            "SCH_DT" => $row->SCH_DT,
                            "WEEK" => $row->WEEK,
                            "MONTH" => $row->MONTH,
                            "START_MONTH" => $row->START_MONTH,
                            "SCH_STATUS" => $row->SCH_STATUS,
                            "AVAIL_STATUS" => $row->AVAIL_STATUS,
                            "SLOT_DETAILS" => []
                        ];
                    }
                    $groupedData[$row->DR_ID]['SCHEDULE_DETAILS'][$row->SCH_DAY]['SLOT_DETAILS'][] = [
                        "SLOT_INTVL" => $row->SLOT_INTVL,
                        "SLOT_APPNT" => $row->SLOT_APPNT,
                        "SLOT_TYPE" => $row->SLOT,
                        "BOOK_ST_DT" => $row->BOOK_ST_DT,
                        "BOOK_ST_TM" => $row->BOOK_ST_TM,
                        "CHK_IN_TIME" => $row->CHK_IN_TIME,
                        "CHK_OUT_TIME" => $row->CHK_OUT_TIME,
                        "CHK_IN_TIME1" => $row->CHK_IN_TIME1,
                        "CHK_OUT_TIME1" => $row->CHK_OUT_TIME1,
                        "CHK_IN_TIME2" => $row->CHK_IN_TIME2,
                        "CHK_OUT_TIME2" => $row->CHK_OUT_TIME2,
                        "CHK_IN_TIME3" => $row->CHK_IN_TIME3,
                        "CHK_OUT_TIME3" => $row->CHK_OUT_TIME3,
                        "MAX_BOOK" => $row->MAX_BOOK,
                        "MAX_BOOK1" => $row->MAX_BOOK1,
                        "MAX_BOOK2" => $row->MAX_BOOK2,
                        "MAX_BOOK3" => $row->MAX_BOOK3,
                        // "BREAK_FROM" => $row->BREAK_FROM,
                        // "BREAK_TO" => $row->BREAK_TO,
                    ];
                    if ($row->SCH_STATUS != 'NA') {
                        $groupedData[$row->DR_ID]['TOT_SCH']++;
                    }
                }
                usort($groupedData, function ($a, $b) {
                    return $a['TOT_SCH'] <=> $b['TOT_SCH'];
                });
                $data = array_values($groupedData);
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function allot(Request $req)
    {
        $response = ['Success' => false, 'Message' => 'An error occurred.', 'code' => 500];
        if ($req->isMethod('post')) {

            $input = $req->json()->all();
            $PDTL = $input['PKG_DETAILS'] ?? [];

            if (is_array($PDTL) && count($PDTL) > 0) {
                foreach ($PDTL as $row) {
                    if (isset($row['PKG_ID'])) {
                        $fields = [
                            'ALLOT_STAFF_ID' => $input['STAFF_ID'],
                            'BOOKING_ID' => $input['BOOKING_ID'],
                            'CASH_COLLECT' => $input['CASH_COLLECT'],
                            'TEST_BOOK' => $input['TEST_BOOK'],
                            'ASSIGN' => 'true',
                        ];
                        try {
                            DB::table('booktest')->where(['BOOKING_ID' => $input['BOOKING_ID'], 'PKG_ID' => $row['PKG_ID']])->update($fields);
                            $response = ['Success' => true, 'Message' => 'Task alloted successfully.', 'code' => 200];
                        } catch (\Throwable $th) {
                            $response = ['Success' => false, 'Message' => 'Already allotyed', 'code' => 500];
                            break;
                        }
                    }
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid Package Details.', 'code' => 400];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function chkallot(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();

            $data = DB::table('booktest')
                ->select('ASSIGN', 'COLLECT', 'COLL_MSG', 'COLLECT_TM', 'SAMPLE_RCV', 'SAM_MSG', 'RPT_GNR', 'RPT_GNR_MSG', 'RPT_AVAIL', 'AVAIL_MSG')
                ->where(['BOOKING_ID' => $input['BOOKING_ID'], 'CATEGORY' => $input['CATEGORY']])->first();
            $response = ['Success' => true, 'data' => $data, 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function vutestreq(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();
            if (isset($input['STAFF_ID'])) {
                $currentDate = date('Ymd');
                $sid = $input['STAFF_ID'];
                $data = array();

                $data1 = DB::table('booktest')
                    ->join('pharmacy', 'booktest.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    ->join('user_family', 'booktest.PATIENT_ID', '=', 'user_family.ID')
                    ->select('booktest.*', 'user_family.*', 'pharmacy.*', 'booktest.STATUS')
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->where('booktest.ALLOT_STAFF_ID', $sid)
                    ->orderbydesc('booktest.BOOKING_DT')
                    ->orderbydesc('booktest.BOOKING_TM')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->BOOKING_DT])) {
                        if ($row->SLOT_DT < $currentDate) {
                            $status = 'Cancelled';
                        } else {
                            $status = $row->STATUS;
                        }
                        $groupedData[$row->BOOKING_DT] = [
                            "BOOKING_DETAILS" => []
                        ];
                    }
                    if (!isset($groupedData[$row->BOOKING_DT]['BOOKING_DETAILS'][$row->BOOKING_ID])) {
                        $groupedData[$row->BOOKING_DT]['BOOKING_DETAILS'][$row->BOOKING_ID] = [
                            "BOOKING_ID" => $row->BOOKING_ID,
                            "BOOKING_DT" => $row->BOOKING_DT,
                            "BOOKING_TM" => $row->BOOKING_TM,
                            "SLOT_DT" => $row->SLOT_DT,
                            "FROM" => $row->FROM,
                            "TO" => $row->TO,
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
                            "PATIENT_NAME" => $row->Patient_Name,
                            "PATIENT_ADDRESS" => $row->LOCATION,
                            "MOBILE" => $row->MOBILE,
                            "SEX" => $row->SEX,
                            "AGE" => $row->DOB,
                            "STATUS" => $status,
                            "ADVICED_BY" => $row->ADVICED_BY,
                            "PRRSCRIPTION" => $row->PRESCRIPTION_URL,
                            "PAY_MODE" => $row->PAY_MODE,
                            "HOME_COLLECT" => $row->HOME_COLLECT,
                            "TRANS_ID" => $row->TRANS_ID,
                            "SAMPLE_COLLECT" => $row->COLLECT,
                            "COLL_MSG" => $row->COLL_MSG,
                            "COLLECT_TM" => $row->COLLECT_TM,
                            "CASH_COLLECT" => $row->CASH_COLLECT,
                            "TEST_BOOK" => $row->TEST_BOOK,
                            "CASH_RCV" => $row->CASH_RCV,
                            "CASH_DEPO" => $row->CASH_DEPO,
                            "TEST_DETAILS" => []
                        ];
                    }
                    if (!isset($groupedData[$row->BOOKING_DT]['BOOKING_DETAILS'][$row->BOOKING_ID]['TEST_DETAILS'][$row->CATEGORY])) {
                        $groupedData[$row->BOOKING_DT]['BOOKING_DETAILS'][$row->BOOKING_ID]['TEST_DETAILS'][$row->CATEGORY] = [];
                    }
                    $groupedData[$row->BOOKING_DT]['BOOKING_DETAILS'][$row->BOOKING_ID]['TEST_DETAILS'][$row->CATEGORY][] = [
                        "PKG_ID" => $row->PKG_ID,
                        "PKG_NAME" => $row->PKG_NAME,
                        "CATEGORY" => $row->CATEGORY,
                        "TEST_COST" => $row->TEST_COST,
                        "PAY_MODE" => $row->PAY_MODE,
                        "HOME_COLLECT" => $row->HOME_COLLECT,
                        "TRANS_ID" => $row->TRANS_ID,
                        "TEST_COST" => $row->TEST_COST,
                        "CASH_COLLECT" => $row->CASH_COLLECT,
                    ];
                }
                foreach ($groupedData as $bookingDate => &$bookingDetails) {
                    $totalBookings = count($bookingDetails['BOOKING_DETAILS']);
                    $bookingDetails['TOTAL_BOOKINGS'] = $totalBookings;

                    foreach ($bookingDetails['BOOKING_DETAILS'] as $bookingID => &$details) {
                        $detailsTotalCount = 0;
                        $detailsTotalTestCost = 0;
                        foreach ($details['TEST_DETAILS'] as $category => $categoryDetails) {
                            foreach ($categoryDetails as $detail) {
                                $detailsTotalCount += 1;
                                $detailsTotalTestCost += $detail['TEST_COST'];
                            }
                        }
                        $details['TOTAL_TEST'] = $detailsTotalCount;
                        $details['TOTAL_TEST_COST'] = $detailsTotalTestCost;
                    }
                    $bookingDetailsTotalCost = array_reduce($bookingDetails['BOOKING_DETAILS'], function ($carry, $item) {
                        return $carry + $item['TOTAL_TEST_COST'];
                    }, 0);
                    $bookingDetails['GRAND_TOTAL_COST'] = $bookingDetailsTotalCost;
                }
                $data = $groupedData;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function chating(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ]);
        }
        date_default_timezone_set('Asia/Kolkata');
        $input = $req->json()->all();

        // return now()->format('Hms');
        $fields = [
            "BOOKING_ID" => $input['BOOKING_ID'],
            "STAFF_ID" => $input['STAFF_ID'],
            "MSG_DT" => Carbon::now()->format('Ymd'),
            "MSG_TM" => Carbon::now()->format('His'),
            "STAFF_MSG" => $input['STAFF_MSG'] ?? '',
            "PATIENT_ID" => $input['PATIENT_ID'],
            "PATIENT_MSG" => $input['PATIENT_MSG'] ?? '',
        ];

        try {
            DB::table('chating')->insert($fields);
            $msg = $input['STAFF_MSG'] ?? $input['PATIENT_MSG'] ?? '';
            $response = ['Success' => true, 'Message' => $msg, 'code' => 200];
        } catch (\Exception $e) {
            $response = ['Success' => true, 'Message' => $e->getMessage(), 'code' => 500];
        }
        return $response;
    }

    function vuchating(Request $req)
    {
        if ($req->isMethod('post')) {
            date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();
            $msg_dt = Carbon::now()->format('Ymd');

            $data = DB::table('chating')
                ->where(['STAFF_ID' => $input['STAFF_ID'], 'PATIENT_ID' => $input['PATIENT_ID'], 'MSG_DT' => $msg_dt])
                ->orderByDesc('MSG_DT')
                ->orderByDesc('MSG_TM')
                ->get();
            $response = ['Success' => true, 'data' => $data, 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function addsch(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            $td = $input['ADD_SCH'];

            foreach ($td as $row) {
                $schId = (substr(md5($row['DR_ID'] . $row['PHARMA_ID'] . $row['SCH_DAY']), 0, 15));
                $fields = [
                    'SCH_ID' => $schId,
                    'DR_ID' => $row['DR_ID'],
                    'DIS_ID' => $row['DIS_ID'],
                    'DR_FEES' => $row['DR_FEES'],
                    'PHARMA_ID' => $row['PHARMA_ID'],
                    'PHARMA_NAME' => $row['PHARMA_NAME'],
                    'SCH_DAY' => $row['SCH_DAY'],
                    'SCH_DT' => $row['SCH_DT'] ?? null,
                    'WEEK' => $row['WEEK'],
                    'MONTH' => $row['MONTH'] ?? null,
                    'START_MONTH' => $row['START_MONTH'],
                    'SCH_STATUS' => $row['SCH_STATUS'],
                    'BOOK_ST_DT' => $row['BOOK_ST_DT'],
                    'BOOK_ST_TM' => $row['BOOK_ST_TM'],
                    'AVAIL_STATUS' => 'Active',
                    'CHK_IN_TIME' => $row['CHK_IN_TIME'],
                    'CHK_OUT_TIME' => $row['CHK_OUT_TIME'] ?? null,
                    'CHK_IN_TIME1' => $row['CHK_IN_TIME1'] ?? null,
                    'CHK_OUT_TIME1' => $row['CHK_OUT_TIME1'] ?? null,
                    'CHK_IN_TIME2' => $row['CHK_IN_TIME2'] ?? null,
                    'CHK_OUT_TIME2' => $row['CHK_OUT_TIME2'] ?? null,
                    'CHK_IN_TIME3' => $row['CHK_IN_TIME3'] ?? null,
                    'CHK_OUT_TIME3' => $row['CHK_OUT_TIME3'] ?? null,
                    'SLOT_INTVL' => $row['SLOT_INTVL'] ?? 0,
                    'SLOT_APPNT' => $row['SLOT_APPNT'] ?? 0,
                    'MAX_BOOK' => $row['MAX_BOOK'] ?? null,
                    'MAX_BOOK1' => $row['MAX_BOOK1'] ?? null,
                    'MAX_BOOK2' => $row['MAX_BOOK2'] ?? null,
                    'MAX_BOOK3' => $row['MAX_BOOK3'] ?? null,
                    'SLOT' => $row['SLOT_TYPE'] ?? null,
                ];

                for ($i = 0; $i < 4; $i++) {
                    $maxBookCol = 'MAX_BOOK' . ($i === 0 ? '' : $i);
                    $chkInTimeCol = 'CHK_IN_TIME' . ($i === 0 ? '' : $i);
                    $chkOutTimeCol = 'CHK_OUT_TIME' . ($i === 0 ? '' : $i);

                    if ($row[$maxBookCol] === null && $row[$chkInTimeCol] != null && $row[$chkOutTimeCol] != null) {
                        $chkinTime = Carbon::createFromFormat('h:i A', $row[$chkInTimeCol]);
                        $chkoutTime = Carbon::createFromFormat('h:i A', $row[$chkOutTimeCol]);
                        $minutesDiff = $chkinTime->diffInMinutes($chkoutTime, false);
                        $maxbook = ($minutesDiff / $row['SLOT_INTVL']) * $row['SLOT_APPNT'];
                        $fields[$maxBookCol] = $maxbook;
                    } elseif ($row[$chkOutTimeCol] === null && $row[$chkInTimeCol] != null && $row[$maxBookCol] != null) {
                        $chkinTime = Carbon::createFromFormat('h:i A', $row[$chkInTimeCol]);
                        $minutesDiff = $row[$maxBookCol] * $row['SLOT_INTVL'];
                        $chkoutTime = $chkinTime->copy()->addMinutes($minutesDiff);
                        $fields[$chkOutTimeCol] = $chkoutTime->format('h:i A');
                    } else {
                        $fields[$maxBookCol] = $row[$maxBookCol];
                    }
                }

                try {
                    $existingSchedule = DB::table('dr_availablity')->where('SCH_ID', $schId)
                        ->where('SCH_STATUS', '!=', 'NA')
                        ->first();
                    if ($existingSchedule) {
                        $response = ['Success' => false, 'Message' => 'Schedule ID already exists.', 'code' => 200];
                    } else {
                        DB::table('dr_availablity')->insert($fields);
                        DB::table('dr_availablity')->where(['DR_ID' => $row['DR_ID'], 'PHARMA_ID' => $row['PHARMA_ID'], 'SCH_STATUS' => 'NA'])->delete();
                        $response = ['Success' => true, 'Message' => 'Doctor schedule added successfully.', 'code' => 200];
                    }
                } catch (\Throwable $th) {
                    $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
                }
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }

    function admtodaydr(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {
                $pid = $input['PHARMA_ID'];
                $date = Carbon::now();
                $weekNumber = $date->weekOfMonth;
                $day1 = date('l');
                $currentDate = Carbon::now();
                $cdy = date('d');
                $cdt = date('Ymd');
                $data = array();

                $data1 = DB::table('drprofile')
                    ->leftjoin('appointment', 'appointment.DR_ID', '=', 'drprofile.DR_ID')
                    ->leftjoin('user_family', 'user_family.ID', '=', 'appointment.PATIENT_ID')
                    ->select(
                        'drprofile.DR_ID',
                        'drprofile.DR_NAME',
                        'drprofile.DR_MOBILE',
                        'drprofile.SEX',
                        'drprofile.DESIGNATION',
                        'drprofile.QUALIFICATION',
                        'drprofile.D_CATG',
                        'drprofile.EXPERIENCE',
                        'drprofile.LANGUAGE',
                        'drprofile.PHOTO_URL',
                        'drprofile.LANGUAGE',
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
                        'appointment.ARRIVE',
                        'appointment.BOOKING_SL',
                        'appointment.STATUS',
                        'appointment.DR_FEES',
                        'appointment.PATIENT_REVIEW',
                        'appointment.APPNT_FROM',
                        'appointment.CHEMBER_NO',
                        'appointment.DR_STATUS',
                        'appointment.DR_DELAY',
                        'appointment.CHK_IN_TIME',
                        'appointment.CHK_OUT_TIME',
                    )
                    ->where('appointment.APPNT_DT', '>=', $cdt)
                    ->where('appointment.PHARMA_ID', $pid)
                    ->where('drprofile.APPROVE', 'true')
                    ->orderByRaw("FIELD(appointment.DR_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
                    ->orderBy('appointment.APPNT_DT')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {

                    if (!isset($groupedData[$row->APPNT_DT])) {
                        $groupedData[$row->APPNT_DT] = [
                            "APPNT_DT" => $row->APPNT_DT,
                            "TOTAL_DOCTORS" => 0,
                            "TOTAL_PATIENTS" => 0,
                        ];
                    }
                    if (!isset($groupedData[$row->APPNT_DT]['DOCTOR'][$row->DR_ID])) {
                        $groupedData[$row->APPNT_DT]['DOCTOR'][$row->DR_ID] = [
                            "DR_ID" => $row->DR_ID,
                            "SCH_ID" => $row->APPNT_ID,
                            "DR_NAME" => $row->DR_NAME,
                            "DR_MOBILE" => $row->DR_MOBILE,
                            "SEX" => $row->SEX,
                            "DESIGNATION" => $row->DESIGNATION,
                            "QUALIFICATION" => $row->QUALIFICATION,
                            "D_CATG" => $row->D_CATG,
                            "EXPERIENCE" => $row->EXPERIENCE,
                            "LANGUAGE" => $row->LANGUAGE,
                            "DR_PHOTO" => $row->PHOTO_URL,
                            "DR_FEES" => $row->DR_FEES,
                            "DR_STATUS" => $row->DR_STATUS,
                            "DR_DELAY" => $row->DR_DELAY,
                            "CHK_IN_TIME" => $row->CHK_IN_TIME,
                            "CHK_OUT_TIME" => $row->CHK_OUT_TIME,
                            "CHEMBER_NO" => $row->CHEMBER_NO,
                            "PATIENT" => [],
                            "TOTAL_PATIENTS" => 0,
                        ];
                        $groupedData[$row->APPNT_DT]['TOTAL_DOCTORS']++;
                    }

                    $groupedData[$row->APPNT_DT]['DOCTOR'][$row->DR_ID]['PATIENT'][] = [
                        "FAMILY_ID" => $row->FAMILY_ID,
                        "PATIENT_ID" => $row->PATIENT_ID,
                        "PATIENT_NAME" => $row->PATIENT_NAME,
                        "AGE" => $row->DOB,
                        "SEX" => $row->SEX,
                        "BOOKING_ID" => $row->BOOKING_ID,
                        "APPNT_TOKEN" => $row->APPNT_TOKEN,
                        "APPNT_ID" => $row->APPNT_ID,
                        "APPNT_DT" => $row->APPNT_DT,
                        "STATUS" => $row->STATUS,
                        "BOOKING_SL" => $row->BOOKING_SL,
                        "PATIENT_REVIEW" => $row->PATIENT_REVIEW,
                    ];
                    $groupedData[$row->APPNT_DT]['DOCTOR'][$row->DR_ID]['TOTAL_PATIENTS']++;
                    $groupedData[$row->APPNT_DT]['TOTAL_PATIENTS']++;
                }

                $sortedBookedDoctors = collect($groupedData)->sortBy(function ($bookeddoctor) {
                    return $bookeddoctor['APPNT_DT'];
                })->all();
                $SD["Booked_Doctors"] = array_values($sortedBookedDoctors);

                $todayDoctors = DB::table('pharmacy')
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
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
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
                        'dr_availablity.ID',
                        'dr_availablity.SCH_DAY',
                        'dr_availablity.START_MONTH',
                        'dr_availablity.MONTH',
                        'dr_availablity.ABS_FDT',
                        'dr_availablity.ABS_TDT',
                        'dr_availablity.CHK_IN_TIME',
                        'dr_availablity.CHK_OUT_TIME',
                        'dr_availablity.CHK_IN_TIME1',
                        'dr_availablity.CHK_OUT_TIME1',
                        'dr_availablity.CHK_IN_TIME2',
                        'dr_availablity.CHK_OUT_TIME2',
                        'dr_availablity.CHK_IN_TIME3',
                        'dr_availablity.CHK_OUT_TIME3',
                        'dr_availablity.CHK_IN_STATUS',
                        'dr_availablity.CHK_IN_STATUS1',
                        'dr_availablity.CHK_IN_STATUS2',
                        'dr_availablity.CHK_IN_STATUS3',
                        'dr_availablity.CHEMBER_NO',
                        'dr_availablity.CHEMBER_NO1',
                        'dr_availablity.CHEMBER_NO2',
                        'dr_availablity.CHEMBER_NO3',
                        'dr_availablity.DR_ARRIVE',
                        'dr_availablity.DR_ARRIVE1',
                        'dr_availablity.DR_ARRIVE2',
                        'dr_availablity.DR_ARRIVE3',
                        'dr_availablity.DR_DELAY',
                        'dr_availablity.DR_DELAY1',
                        'dr_availablity.DR_DELAY2',
                        'dr_availablity.DR_DELAY3',
                        'dr_availablity.MAX_BOOK',
                        'dr_availablity.MAX_BOOK1',
                        'dr_availablity.MAX_BOOK2',
                        'dr_availablity.MAX_BOOK3',
                        'dr_availablity.SLOT_INTVL'
                    )
                    ->where(['dr_availablity.SCH_DAY' => $day1, 'dr_availablity.PHARMA_ID' => $pid])
                    ->where('WEEK', 'like', '%' . $weekNumber . '%')
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->where('drprofile.APPROVE', 'true')
                    ->orWhere('dr_availablity.SCH_DT', $cdy)
                    // ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
                    // ->orderBy('dr_availablity.CHK_IN_TIME')
                    ->get();

                $result = [];
                $chk = array("CHK_IN_TIME", "CHK_IN_TIME1", "CHK_IN_TIME2", "CHK_IN_TIME3");
                $chkout = array("CHK_OUT_TIME", "CHK_OUT_TIME1", "CHK_OUT_TIME2", "CHK_OUT_TIME3");
                $CHKINSTATUS = array("CHK_IN_STATUS", "CHK_IN_STATUS1", "CHK_IN_STATUS2", "CHK_IN_STATUS3");
                $delay = array("DR_DELAY", "DR_DELAY1", "DR_DELAY2", "DR_DELAY3");
                $chamber = array("CHEMBER_NO", "CHEMBER_NO1", "CHEMBER_NO2", "CHEMBER_NO3");

                $currentTime = Carbon::now();

                foreach ($todayDoctors as $key => $doctor) {
                    $maxBookSum = 0;
                    $sch_dt = null; // Initialize $sch_dt here

                    if (is_numeric($doctor->SCH_DAY)) {
                        $date = Carbon::createFromDate(date('Y'), $doctor->START_MONTH, $doctor->SCH_DAY)
                            ->addMonths($doctor->MONTH);
                        if ($date->format('Ymd') === $cdt) {
                            $sch_dt = $date->format('Ymd');
                        }
                    } else {
                        $sch_dt = Carbon::now()->format('Ymd');
                    }

                    $nonNullChkInTimes = array_filter([$doctor->CHK_IN_TIME, $doctor->CHK_IN_TIME1, $doctor->CHK_IN_TIME2, $doctor->CHK_IN_TIME3], function ($time) {
                        return !empty($time);
                    });

                    for ($i = 0; $i < count($nonNullChkInTimes); $i++) {
                        $checkOutTime = Carbon::parse($doctor->{$chkout[$i]});
                        if (!empty($doctor->{$delay[$i]})) {
                            $checkOutTime = $checkOutTime->addMinutes($doctor->{$delay[$i]});
                        }

                        if ($currentTime->lessThanOrEqualTo($checkOutTime)) {
                            if (!in_array($doctor->{$CHKINSTATUS[$i]}, ['OUT', 'CANCELLED', 'LEAVE'])) {
                                $doctor->CHK_IN_TIME = $doctor->{$chk[$i]};
                                $doctor->CHK_OUT_TIME = $doctor->{$chkout[$i]};
                                $doctor->CHK_IN_STATUS = $doctor->{$CHKINSTATUS[$i]};
                                $doctor->CHEMBER_NO = $doctor->{$chamber[$i]};
                                $doctor->DR_DELAY = $doctor->{$delay[$i]} ?? null;
                                break;
                            } else {
                                $nextIndex = $i + 1;
                                if ($nextIndex < count($chk) && !empty($doctor->{$chk[$nextIndex]}) && !in_array($doctor->{$CHKINSTATUS[$nextIndex]}, ['OUT', 'CANCELLED'])) {
                                    $doctor->CHK_IN_TIME = $doctor->{$chk[$nextIndex]};
                                    $doctor->CHK_OUT_TIME = $doctor->{$chkout[$nextIndex]};
                                    $doctor->CHK_IN_STATUS = $doctor->{$CHKINSTATUS[$nextIndex]};
                                    $doctor->CHEMBER_NO = $doctor->{$chamber[$nextIndex]};
                                    $doctor->DR_DELAY = $doctor->{$delay[$nextIndex]} ?? null;
                                } else {
                                    $doctor->CHK_IN_TIME = $doctor->{$chk[$i]};
                                    $doctor->CHK_OUT_TIME = $doctor->{$chkout[$i]};
                                    $doctor->CHK_IN_STATUS = $doctor->{$CHKINSTATUS[$i]};
                                    $doctor->CHEMBER_NO = $doctor->{$chamber[$i]};
                                    $doctor->DR_DELAY = $doctor->{$delay[$i]} ?? null;
                                }
                            }
                        }
                    }

                    // Calculate sum of MAX_BOOK values that are not null for the current doctor
                    $maxBookDoctorSum = 0;
                    foreach (['MAX_BOOK', 'MAX_BOOK1', 'MAX_BOOK2', 'MAX_BOOK3'] as $maxBookField) {
                        if (!is_null($doctor->{$maxBookField})) {
                            $maxBookDoctorSum += $doctor->{$maxBookField};
                        }
                    }

                    $doctor->MAX_BOOK = $maxBookDoctorSum;

                    $result['Today_Doctors'][] = [
                        "PHARMA_ID" => $doctor->PHARMA_ID,
                        "PHARMA_NAME" => $doctor->ITEM_NAME,
                        "ADDRESS" => $doctor->ADDRESS,
                        "CITY" => $doctor->CITY,
                        "PIN" => $doctor->PIN,
                        "DIST" => $doctor->DIST,
                        "STATE" => $doctor->STATE,
                        "LATITUDE" => $doctor->LATITUDE,
                        "LONGITUDE" => $doctor->LONGITUDE,
                        "PHOTO_URL" => $doctor->PHOTO_URL,
                        "LOGO_URL" => $doctor->LOGO_URL,
                        "DR_ID" => $doctor->DR_ID,
                        "DR_NAME" => $doctor->DR_NAME,
                        "DR_MOBILE" => $doctor->DR_MOBILE,
                        "SEX" => $doctor->SEX,
                        "DESIGNATION" => $doctor->DESIGNATION,
                        "QUALIFICATION" => $doctor->QUALIFICATION,
                        "D_CATG" => $doctor->D_CATG,
                        "EXPERIENCE" => $doctor->EXPERIENCE,
                        "LANGUAGE" => $doctor->LANGUAGE,
                        "DR_PHOTO" => $doctor->DR_PHOTO,
                        "DR_FEES" => $doctor->DR_FEES,
                        "SCH_ID" => $doctor->ID,
                        "APPNT_DT" => $sch_dt,
                        "CHK_IN_TIME" => $doctor->CHK_IN_TIME,
                        "CHK_OUT_TIME" => $doctor->CHK_OUT_TIME,
                        "DR_STATUS" => $doctor->CHK_IN_STATUS,
                        // "CHK_IN_STATUS" => $doctor->CHK_IN_STATUS,
                        // "CHK_IN_STATUS1" => $doctor->CHK_IN_STATUS1,
                        // "CHK_IN_STATUS2" => $doctor->CHK_IN_STATUS2,
                        // "CHK_IN_STATUS3" => $doctor->CHK_IN_STATUS3,
                        "CHEMBER_NO" => $doctor->CHEMBER_NO,
                        "MAX_BOOK" => $maxBookDoctorSum,
                    ];
                }


                if (empty($result['Today_Doctors'])) {
                    $result['Today_Doctors'] = [];
                } else {
                    // Convert 12-hour format to 24-hour format for sorting
                    $doctorsCollection = collect($result['Today_Doctors']);
                    $doctorsCollection = $doctorsCollection->sortBy(function ($availableDoctor) {
                        return date("H:i", strtotime($availableDoctor['CHK_IN_TIME']));
                    })->values()->all();

                    $result['Today_Doctors'] = $doctorsCollection;

                    // Remove duplicate doctors based on DR_ID
                    $uniqueDoctors = collect($doctorsCollection)->unique('DR_ID');

                    // Sort the unique doctors by status and appointment date
                    $sortedDoctors = $uniqueDoctors->sortBy(function ($doctor) {
                        $statusOrder = [
                            'IN' => 1,
                            'TIMELY' => 2,
                            'DELAY' => 3,
                            'CANCELLED' => 4,
                            'OUT' => 5,
                            'LEAVE' => 6
                        ];
                        $statusPriority = $statusOrder[$doctor['DR_STATUS']] ?? 999;
                        return $doctor['APPNT_DT'] . '-' . sprintf('%02d', $statusPriority) . '-' . date("H:i", strtotime($doctor['CHK_IN_TIME']));
                    })->values()->all();

                    $result['Today_Doctors'] = $sortedDoctors;
                }



                $data = $SD + $result;

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function adddrcsv(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ], 405);
        }

        $row = $req->all();
        $pid = $row['PHARMA_ID'];
        $pname = $row['PHARMA_NAME'];

        $validator = Validator::make($req->all(), [
            'file' => 'required|mimes:csv',
        ]);

        if ($validator->fails()) {
            $response = ['Success' => false, 'Message' => $validator->errors(), 'code' => 422];
            return response()->json($response, 422);
        }

        $file = $req->file('file');
        $data = array_map('str_getcsv', file($file));




        $data = array_slice($data, 1);

        $failedRows = [];

        foreach ($data as $input) {
            if (array_filter($input)) { // Check if the row is not empty
                $fields_dr = [
                    'DR_ID' => strtoupper(substr(md5($input[3] . $input[4]), 0, 15)),
                    'DR_NAME' => $input[0] ?? null,
                    'DR_MOBILE' => $input[1] ?? null,
                    'SEX' => $input[2] ?? null,
                    'REGN_NO' => $input[3] ?? null,
                    'COUNCIL' => $input[4] ?? null,
                    'DESIGNATION' => $input[5] ?? null,
                    'QUALIFICATION' => $input[6] ?? null,
                    'D_CATG' => $input[7] ?? null,
                    'EXPERIENCE' => $input[8] ?? null,
                    'LANGUAGE' => $input[9] ?? null,
                    'REPORTING_DAY' => $input[10] ?? null,
                    'GOOGLE_REVIEW' => $input[12] ?? null,
                ];

                // return  $fields_dr;
                $fields_sch = [
                    "DR_ID" => strtoupper(substr(md5($input[3] . $input[4]), 0, 15)),
                    "SCH_ID" => strtoupper(substr(md5($input[3] . $input[4] . $pid), 0, 15)),
                    "DR_FEES" => $input[11] ?? 0,
                    "PHARMA_ID" => $pid,
                    "PHARMA_NAME" => $pname,
                ];

                try {
                    DB::table('drprofile')->insert($fields_dr);
                } catch (\Exception $e) {
                }
                try {
                    DB::table('dr_availablity')->insert($fields_sch);
                } catch (\Exception $e) {
                    $failedRows[] = $input;
                }
            }
        }

        if (count($failedRows) > 0) {
            $failedCsvPath = 'app/drprofile/failed_rows.csv';
            $filePath = storage_path("$failedCsvPath");

            $file = fopen($filePath, 'w');
            foreach ($failedRows as $failedRow) {
                fputcsv($file, $failedRow);
            }
            fclose($file);

            return response()->json([
                'Success' => false,
                'Message' => 'Some rows failed to insert',
                'failedRows' => asset(Storage::url($failedCsvPath)),
                'code' => 500
            ], 500);
        }

        return response()->json([
            'Success' => true,
            'Message' => 'All rows inserted successfully',
            'code' => 200
        ], 200);
    }

    function vucatg()
    {
        $data = DB::table('dis_catg')->select('DIS_ID', 'SPECIALITY as D_CATG')->orderby('SPECIALITY')->get();
        $response = ['Success' => true, 'data' => $data, 'code' => 200];
        return $response;
    }

    function vunmc()
    {
        $data = DB::table('council')->select('NMC_ID', 'COUNCIL')->get();
        $response = ['Success' => true, 'data' => $data, 'code' => 200];
        return $response;
    }

    function adddr(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ], 405);
        }

        $input = $req->all();
        $fileName = null;
        $url = 'http://easyhealths.com/storage/app/drprofile/drphoto/doctor_dummy.png';
        if ($req->file('FILE')) {
            $drid = $input['REGN_NO'] ?? null;
            $pid = $input['PHARMA_ID'] ?? null;
            if ($drid && $pid) {
                $fileName = $drid . $pid . "." . $req->file('FILE')->getClientOriginalExtension();
                $req->file('FILE')->storeAs('drprofile/drphoto', $fileName);
                $url = asset(storage::url('app/drprofile/drphoto')) . "/" . $fileName;
            }
        }

        $fields_dr = [
            "DR_ID" => strtoupper(substr(md5($input['REGN_NO'] . $input['COUNCIL']), 0, 15)),
            "DESIGNATION" => $input['DESIGNATION'] ?? null,
            "DIS_ID" => $input['DIS_ID'] ?? null,
            "DR_MOBILE" => $input['DR_MOBILE'] ?? null,
            "DR_NAME" => $input['DR_NAME'] ?? null,
            "D_CATG" => $input['D_CATG'] ?? null,
            "EXPERIENCE" => $input['EXPERIENCE'] ?? null,
            "LANGUAGE" => $input['LANGUAGE'] ?? null,
            "QUALIFICATION" => $input['QUALIFICATION'] ?? null,
            "REGN_NO" => $input['REGN_NO'] ?? null,
            "COUNCIL" => $input['COUNCIL'] ?? null,
            "SEX" => $input['SEX'] ?? null,
            // "UID_NMC" => $input['UID_NMC'] ?? null,
            "REPORTING_DAY" => $input['REPORTING_DAY'] ?? null,
            "PHOTO_URL" => $url,
        ];
        $fields_sch = [
            "DR_ID" => strtoupper(substr(md5($input['REGN_NO'] . $input['COUNCIL']), 0, 15)),
            "DR_FEES" => $input['DR_FEES'] ?? null,
            "SCH_ID" => strtoupper(substr(md5($input['REGN_NO'] . $input['COUNCIL']), 0, 15)),
            "PHARMA_ID" => $input['PHARMA_ID'] ?? null,
            "PHARMA_NAME" => $input['PHARMA_NAME'] ?? null,
        ];

        try {
            DB::table('drprofile')->insert($fields_dr);
            DB::table('dr_availablity')->insert($fields_sch);
            $response = ['Success' => true, 'Message' => 'Doctor added successfully', 'code' => 200];
        } catch (\Exception $e) {
            try {
                DB::table('dr_availablity')->insert($fields_sch);
                $response = ['Success' => true, 'Message' => 'Doctor added successfully', 'code' => 200];
            } catch (\Throwable $th) {
                $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
            }
        }
        return $response;
    }

    function vudrprofile(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();

            $data = DB::table('drprofile')
                ->where(['REGN_NO' => $input['REGN_NO']])
                ->first();
            $response = ['Success' => true, 'data' => $data, 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function drsts(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ]);
        }

        date_default_timezone_set('Asia/Kolkata');
        $input = $req->json()->all();
        $cit = Carbon::now()->format('h:i A');
        $chk_in_time = $input['FROM_TIME'];

        // Define arrays for the update and checking
        $chkInTimes = ['CHK_IN_TIME', 'CHK_IN_TIME1', 'CHK_IN_TIME2', 'CHK_IN_TIME3'];
        $chkInStatusFields = ['CHK_IN_STATUS', 'CHK_IN_STATUS1', 'CHK_IN_STATUS2', 'CHK_IN_STATUS3'];
        $drArriveFields = ['DR_ARRIVE', 'DR_ARRIVE1', 'DR_ARRIVE2', 'DR_ARRIVE3'];
        $chemberFields = ['CHEMBER_NO', 'CHEMBER_NO1', 'CHEMBER_NO2', 'CHEMBER_NO3'];
        $chkOutTimes = ['CHK_OUT_TIME', 'CHK_OUT_TIME1', 'CHK_OUT_TIME2', 'CHK_OUT_TIME3'];
        $maxBookFields = ['MAX_BOOK', 'MAX_BOOK1', 'MAX_BOOK2', 'MAX_BOOK3'];

        $fields = [];
        $selectedStatusField = 'CHK_IN_STATUS'; // Default to CHK_IN_STATUS
        $selectedChemberField = 'CHEMBER_NO'; // Default to CHEMBER_NO
        $selectedChkInTime = 'CHK_IN_TIME'; // Default to CHK_IN_TIME
        $selectedChkOutTime = 'CHK_OUT_TIME'; // Default to CHK_OUT_TIME
        $selectedMaxBookField = 'MAX_BOOK'; // Default to MAX_BOOK
        $selectedArriveField = 'DR_ARRIVE';

        foreach ($chkInTimes as $index => $timeField) {
            $statusField = $chkInStatusFields[$index];
            $arriveField = $drArriveFields[$index];
            $chemberField = $chemberFields[$index];
            $chkOutTimeField = $chkOutTimes[$index];
            $maxBookField = $maxBookFields[$index];
            $arriveField = $drArriveFields[$index];
            $time = DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->value($timeField);
            if ($chk_in_time === $time) {
                $fields[$statusField] = 'IN';
                $fields[$arriveField] = $cit;
                $fields[$chemberField] = $input['CHEMBER'];
                $selectedStatusField = $statusField; // Update the selected status field
                $selectedChemberField = $chemberField; // Update the selected chember field
                $selectedChkInTime = $timeField; // Update the selected CHK_IN_TIME field
                $selectedChkOutTime = $chkOutTimeField; // Update the selected CHK_OUT_TIME field
                $selectedMaxBookField = $maxBookField; // Update the selected MAX_BOOK field
            }
        }

        $fields1 = [
            "CHEMBER_NO" => $input['CHEMBER'],
            "DR_STATUS" => 'IN',
            "DR_ARRIVE" => $cit
        ];

        try {
            DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->update($fields);
            DB::table('appointment')->where(['APPNT_ID' => $input['SCH_ID']])->update($fields1);
            $TD = DB::table('drprofile')
                ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
                ->select(
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
                    'dr_availablity.ID as APPNT_ID',
                    DB::raw("'" . Carbon::now()->format('Ymd') . "' as APPNT_DT"),
                    'dr_availablity.DR_FEES',
                    'dr_availablity.' . $selectedChkInTime . ' AS CHK_IN_TIME', // Include the selected CHK_IN_TIME field
                    'dr_availablity.' . $selectedChkOutTime . ' AS CHK_OUT_TIME', // Include the selected CHK_OUT_TIME field
                    'dr_availablity.' . $selectedStatusField . ' AS DR_STATUS', // Include the selected status field
                    'dr_availablity.' . $selectedChemberField . ' AS CHEMBER_NO', // Include the selected chember field
                    'dr_availablity.' . $selectedMaxBookField . ' AS MAX_BOOK' // Include the selected MAX_BOOK field
                )
                ->where(['dr_availablity.ID' => $input['SCH_ID']])
                ->where('drprofile.APPROVE', 'true')
                ->first();

            $response = [
                'Success' => true,
                'data' => $TD,
                'Message' => 'Doctor arrived',
                'code' => 200
            ];
        } catch (\Exception $e) {
            $response = [
                'Success' => false,
                'Message' => $e->getMessage(),
                'code' => 500
            ];
        }

        return response()->json($response);
    }

    function vuslot(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();
            if (isset($input['DR_ID']) && isset($input['PHARMA_ID']) && isset($input['APPNT_ID']) && isset($input['APPNT_DT'])) {
                $drid = $input['DR_ID'];
                $fid = $input['PHARMA_ID'];
                $apid = $input['APPNT_ID'];
                $apdt = $input['APPNT_DT'];
                $cdt = date('Ymd');

                $data1 = DB::table('dr_availablity')
                    ->where(['DR_ID' => $drid, 'PHARMA_ID' => $fid, 'ID' => $apid])
                    ->first();

                if (!$data1) {
                    return ['Success' => false, 'Message' => 'Availability data not found.', 'code' => 404];
                }

                $slots = [
                    'Morning' => [],
                    'Afternoon' => [],
                    'Evening' => [],
                    'Night' => []
                ];

                $chkInTimes = [
                    $data1->CHK_IN_TIME,
                    $data1->CHK_IN_TIME1,
                    $data1->CHK_IN_TIME2,
                    $data1->CHK_IN_TIME3
                ];
                $chkOutTimes = [
                    $data1->CHK_OUT_TIME,
                    $data1->CHK_OUT_TIME1,
                    $data1->CHK_OUT_TIME2,
                    $data1->CHK_OUT_TIME3
                ];
                $maxbooks = [
                    $data1->MAX_BOOK,
                    $data1->MAX_BOOK1,
                    $data1->MAX_BOOK2,
                    $data1->MAX_BOOK3
                ];
                $intvl = $data1->SLOT_INTVL ?? null;

                foreach ($chkInTimes as $index => $chkin) {
                    $maxbk = $maxbooks[$index];
                    $chkout = $chkOutTimes[$index];
                    if ($chkin === null) {
                        continue;
                    }

                    try {
                        $chkinTime = Carbon::createFromFormat('h:i A', $chkin);
                        $chkoutTime = $chkout !== null ? Carbon::createFromFormat('h:i A', $chkout) : $chkinTime->copy()->addMinutes($intvl * $maxbk);
                    } catch (\Exception $e) {
                        return ["Error" => "Error in time conversion: " . $e->getMessage()];
                    }

                    $slot_sts = null;

                    if ($data1->SLOT == '1') {
                        // Create hourly slots
                        while ($chkinTime->lessThan($chkoutTime)) {
                            $endSlot = $chkinTime->copy()->addHour();
                            if ($endSlot->greaterThan($chkoutTime)) {
                                $endSlot = $chkoutTime;
                            }

                            // Fetch booked slots for the current time slot
                            $bookedCount = DB::table('appointment')
                                ->where('APPNT_FROM', '>=', $chkinTime->format('h:i A'))
                                ->where('APPNT_TO', '<=', $endSlot->format('h:i A'))
                                ->where('APPNT_ID', '=', $apid)
                                ->count();

                            // Log the booked count for debugging
                            Log::info('Booked Count: ' . $bookedCount);

                            $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                            $bookingSerials = range(1, $totalAppointments);
                            $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));
                            $availAppointments = count($availableSerials);

                            if ($cdt === $apdt) {
                                $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
                            } else {
                                $slot_sts = "Available";
                            }

                            $slotString = [
                                "FROM" => $chkinTime->format('h:i A'),
                                "TO" => $endSlot->format('h:i A'),
                                "TOTAL_APNT" => $totalAppointments,
                                "AVAIL_APNT" => $availAppointments,
                                "BOOKING_SERIALS" => $bookingSerials,
                                "AVAILABLE_SERIALS" => $availableSerials,
                                "SLOT_STATUS" => $slot_sts,
                            ];

                            if ($chkinTime->hour < 12) {
                                $slots['Morning'][] = $slotString;
                            } elseif ($chkinTime->hour < 16) {
                                $slots['Afternoon'][] = $slotString;
                            } elseif ($chkinTime->hour < 20) {
                                $slots['Evening'][] = $slotString;
                            } else {
                                $slots['Night'][] = $slotString;
                            }

                            $chkinTime->addHour();
                        }
                    } else if ($data1->SLOT == '2') {
                        while ($chkinTime->lessThan($chkoutTime)) {
                            $endSlot = $chkinTime->copy()->addMinutes($intvl);
                            if ($endSlot->greaterThan($chkoutTime)) {
                                break;
                            }

                            // Fetch booked slots for the current time slot
                            $bookedCount = DB::table('appointment')
                                ->where('APPNT_FROM', '>=', $chkinTime->format('h:i A'))
                                ->where('APPNT_TO', '<=', $endSlot->format('h:i A'))
                                ->where('APPNT_ID', '=', $apid)
                                ->count();

                            // Log the booked count for debugging
                            Log::info('Booked Count: ' . $bookedCount);

                            $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                            $bookingSerials = range(1, $totalAppointments);
                            $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));
                            $availAppointments = count($availableSerials);

                            if ($cdt === $apdt) {
                                $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
                            } else {
                                $slot_sts = "Available";
                            }

                            $slotString = [
                                "FROM" => $chkinTime->format('h:i A'),
                                "TO" => $endSlot->format('h:i A'),
                                "TOTAL_APNT" => $totalAppointments,
                                "AVAIL_APNT" => $availAppointments,
                                "BOOKING_SERIALS" => $bookingSerials,
                                "AVAILABLE_SERIALS" => $availableSerials,
                                "SLOT_STATUS" => $slot_sts,
                            ];

                            if ($chkinTime->hour < 12) {
                                $slots['Morning'][] = $slotString;
                            } elseif ($chkinTime->hour < 16) {
                                $slots['Afternoon'][] = $slotString;
                            } elseif ($chkinTime->hour < 20) {
                                $slots['Evening'][] = $slotString;
                            } else {
                                $slots['Night'][] = $slotString;
                            }

                            $chkinTime->addMinutes($intvl);
                        }
                    }
                }

                $response = ['Success' => true, 'Data' => $slots, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function vuslot1(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();
            if (isset($input['DR_ID']) && isset($input['PHARMA_ID']) && isset($input['APPNT_ID']) && isset($input['APPNT_DT'])) {
                $drid = $input['DR_ID'];
                $fid = $input['PHARMA_ID'];
                $apid = $input['APPNT_ID'];
                $apdt = $input['APPNT_DT'];
                $cdt = date('Ymd');

                $data1 = DB::table('dr_availablity')
                    ->where(['DR_ID' => $drid, 'PHARMA_ID' => $fid, 'ID' => $apid])
                    ->first();

                if (!$data1) {
                    return ['Success' => false, 'Message' => 'Availability data not found.', 'code' => 404];
                }

                $slots = [
                    'Morning' => [],
                    'Afternoon' => [],
                    'Evening' => [],
                    'Night' => []
                ];

                $chkInTimes = [
                    $data1->CHK_IN_TIME,
                    $data1->CHK_IN_TIME1,
                    $data1->CHK_IN_TIME2,
                    $data1->CHK_IN_TIME3
                ];
                $chkOutTimes = [
                    $data1->CHK_OUT_TIME,
                    $data1->CHK_OUT_TIME1,
                    $data1->CHK_OUT_TIME2,
                    $data1->CHK_OUT_TIME3
                ];
                $maxbooks = [
                    $data1->MAX_BOOK,
                    $data1->MAX_BOOK1,
                    $data1->MAX_BOOK2,
                    $data1->MAX_BOOK3
                ];
                $intvl = $data1->SLOT_INTVL ?? null;

                foreach ($chkInTimes as $index => $chkin) {
                    $maxbk = $maxbooks[$index];
                    $chkout = $chkOutTimes[$index];
                    if ($chkin === null) {
                        continue;
                    }

                    try {
                        $chkinTime = Carbon::createFromFormat('h:i A', $chkin);
                        $chkoutTime = $chkout !== null ? Carbon::createFromFormat('h:i A', $chkout) : $chkinTime->copy()->addMinutes($intvl * $maxbk);
                    } catch (\Exception $e) {
                        return ["Error" => "Error in time conversion: " . $e->getMessage()];
                    }

                    $slot_sts = null;
                    $serialCounter = 1;
                    if ($data1->SLOT == '1') {
                        // Create hourly slots

                        while ($chkinTime->lessThan($chkoutTime)) {
                            $endSlot = $chkinTime->copy()->addHour();
                            if ($endSlot->greaterThan($chkoutTime)) {
                                $endSlot = $chkoutTime;
                            }

                            // Fetch booked slots for the current time slot
                            $bookedCount = DB::table('appointment')
                                ->where('APPNT_FROM', '>=', $chkinTime->format('h:i A'))
                                ->where('APPNT_TO', '<=', $endSlot->format('h:i A'))
                                ->where(['APPNT_ID' => $apid, 'APPNT_DT' => $apdt])
                                ->count();

                            // Log the booked count for debugging
                            Log::info($chkinTime . 'Booked Count: ' . $bookedCount);

                            $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                            $bookingSerials = range($serialCounter, $serialCounter + $totalAppointments - 1);
                            $serialCounter += $totalAppointments;

                            $availableSerials = [];
                            if ($bookedCount > 0) {
                                $bookedSerials = range($serialCounter - $totalAppointments, $serialCounter - $totalAppointments + $bookedCount - 1);
                                $availableSerials = array_diff($bookingSerials, $bookedSerials);
                            } else {
                                $availableSerials = $bookingSerials;
                            }
                            $availableSerials = array_values($availableSerials);

                            $availAppointments = count($availableSerials);

                            if ($cdt === $apdt) {
                                $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
                            } else {
                                $slot_sts = "Available";
                            }

                            $slotString = [
                                "FROM" => $chkinTime->format('h:i A'),
                                "TO" => $endSlot->format('h:i A'),
                                "TOTAL_APNT" => $totalAppointments,
                                "AVAIL_APNT" => $availAppointments,
                                "BOOKING_SERIALS" => $bookingSerials,
                                "AVAILABLE_SERIALS" => $availableSerials,
                                "SLOT_STATUS" => $slot_sts,
                            ];

                            if ($chkinTime->hour < 12) {
                                $slots['Morning'][] = $slotString;
                            } elseif ($chkinTime->hour < 16) {
                                $slots['Afternoon'][] = $slotString;
                            } elseif ($chkinTime->hour < 20) {
                                $slots['Evening'][] = $slotString;
                            } else {
                                $slots['Night'][] = $slotString;
                            }

                            $chkinTime->addHour();
                        }
                    } else if ($data1->SLOT == '2') {
                        while ($chkinTime->lessThan($chkoutTime)) {
                            $endSlot = $chkinTime->copy()->addMinutes($intvl);
                            if ($endSlot->greaterThan($chkoutTime)) {
                                break;
                            }

                            // Fetch booked slots for the current time slot
                            $bookedCount = DB::table('appointment')
                                ->where('APPNT_FROM', '>=', $chkinTime->format('h:i A'))
                                ->where('APPNT_TO', '<=', $endSlot->format('h:i A'))
                                ->where('APPNT_ID', '=', $apid)
                                ->count();

                            // Log the booked count for debugging
                            Log::info('Booked Count: ' . $bookedCount);

                            $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                            $bookingSerials = range($serialCounter, $serialCounter + $totalAppointments - 1);
                            $serialCounter += $totalAppointments;

                            $availableSerials = [];
                            if ($bookedCount > 0) {
                                $bookedSerials = range($serialCounter - $totalAppointments, $serialCounter - $totalAppointments + $bookedCount - 1);
                                $availableSerials = array_diff($bookingSerials, $bookedSerials);
                            } else {
                                $availableSerials = $bookingSerials;
                            }
                            $availableSerials = array_values($availableSerials);

                            $availAppointments = count($availableSerials);

                            if ($cdt === $apdt) {
                                $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
                            } else {
                                $slot_sts = "Available";
                            }

                            $slotString = [
                                "FROM" => $chkinTime->format('h:i A'),
                                "TO" => $endSlot->format('h:i A'),
                                "TOTAL_APNT" => $totalAppointments,
                                "AVAIL_APNT" => $availAppointments,
                                "BOOKING_SERIALS" => $bookingSerials,
                                "AVAILABLE_SERIALS" => $availableSerials,
                                "SLOT_STATUS" => $slot_sts,
                            ];

                            if ($chkinTime->hour < 12) {
                                $slots['Morning'][] = $slotString;
                            } elseif ($chkinTime->hour < 16) {
                                $slots['Afternoon'][] = $slotString;
                            } elseif ($chkinTime->hour < 20) {
                                $slots['Evening'][] = $slotString;
                            } else {
                                $slots['Night'][] = $slotString;
                            }

                            $chkinTime->addMinutes($intvl);
                        }
                    }
                }

                $response = ['Success' => true, 'Data' => $slots, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }



    function getsl(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ]);
        }
        date_default_timezone_set('Asia/Kolkata');
        $input = $req->json()->all();

        $fields = [
            "BOOKING_SL" => $input['SL_NO'],
            "ARRIVE" => 'true'
        ];

        try {
            DB::table('appointment')->where(['BOOKING_ID' => $input['BOOKING_ID']])->update($fields);
            $response = [
                'Success' => true,
                'Message' => 'Confirmed Patient Serial No.',
                'Serial No' => $input['SL_NO'],
                'code' => 200
            ];
        } catch (\Exception $e) {
            $response = ['Success' => true, 'Message' => $e->getMessage(), 'code' => 500];
        }
        return $response;
    }

    function drvisit(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ]);
        }
        date_default_timezone_set('Asia/Kolkata');
        $input = $req->json()->all();

        $fields = [
            "STATUS" => 'Visited',
            "PATIENT_REVIEW" => 'Yes',
            "NEXT_VISIT" => $input['NEXT_VISIT'],
            "SHOW_REPORT" => $input['SHOW_REPORT'],
        ];

        try {
            DB::table('appointment')->where(['BOOKING_ID' => $input['BOOKING_ID']])->update($fields);
            $response = [
                'Success' => true,
                'Message' => 'Patient visited',
                'code' => 200
            ];
        } catch (\Exception $e) {
            $response = ['Success' => true, 'Message' => $e->getMessage(), 'code' => 500];
        }
        return $response;
    }

    function patientin(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ]);
        }
        date_default_timezone_set('Asia/Kolkata');
        $input = $req->json()->all();

        $fields = [
            "STATUS" => 'In',
        ];

        try {
            DB::table('appointment')->where(['BOOKING_ID' => $input['BOOKING_ID']])->update($fields);
            $response = [
                'Success' => true,
                'Message' => 'Patient In',
                'code' => 200
            ];
        } catch (\Exception $e) {
            $response = ['Success' => true, 'Message' => $e->getMessage(), 'code' => 500];
        }
        return $response;
    }

    function drleft(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ]);
        }
        date_default_timezone_set('Asia/Kolkata');
        $currentDate = Carbon::now();
        $cit = Carbon::now()->format('h:i A');
        $input = $req->json()->all();
        $tdt = carbon::now()->format('Ymd');
        $apntid = $input['SCH_ID'];


        $chk_in_time = $input['FROM_TIME'];

        // Define arrays for the update and checking
        $chkInTimes = ['CHK_IN_TIME', 'CHK_IN_TIME1', 'CHK_IN_TIME2', 'CHK_IN_TIME3'];
        $chkInStatusFields = ['CHK_IN_STATUS', 'CHK_IN_STATUS1', 'CHK_IN_STATUS2', 'CHK_IN_STATUS3'];
        $drArriveFields = ['DR_ARRIVE', 'DR_ARRIVE1', 'DR_ARRIVE2', 'DR_ARRIVE3'];
        $drLeftFields = ['DR_LEFT', 'DR_LEFT1', 'DR_LEFT2', 'DR_LEFT3'];
        $chemberFields = ['CHEMBER_NO', 'CHEMBER_NO1', 'CHEMBER_NO2', 'CHEMBER_NO3'];
        $chkOutTimes = ['CHK_OUT_TIME', 'CHK_OUT_TIME1', 'CHK_OUT_TIME2', 'CHK_OUT_TIME3'];
        $maxBookFields = ['MAX_BOOK', 'MAX_BOOK1', 'MAX_BOOK2', 'MAX_BOOK3'];

        $fields_d = [];
        $selectedStatusField = 'CHK_IN_STATUS'; // Default to CHK_IN_STATUS
        $selectedChemberField = 'CHEMBER_NO'; // Default to CHEMBER_NO
        $selectedChkInTime = 'CHK_IN_TIME'; // Default to CHK_IN_TIME
        $selectedChkOutTime = 'CHK_OUT_TIME'; // Default to CHK_OUT_TIME
        $selectedMaxBookField = 'MAX_BOOK'; // Default to MAX_BOOK
        $selectedLeftField = 'DR_LEFT'; // Default to DR_LEFT
        $selectedArriveField = 'DR_ARRIVE';

        foreach ($chkInTimes as $index => $timeField) {
            $statusField = $chkInStatusFields[$index];
            $arriveField = $drArriveFields[$index];
            $leftField = $drLeftFields[$index];
            $chemberField = $chemberFields[$index];
            $chkOutTimeField = $chkOutTimes[$index];
            $maxBookField = $maxBookFields[$index];
            $time = DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->value($timeField);

            if ($chk_in_time === $time) {
                $fields_d[$statusField] = 'OUT';
                $fields_d[$chemberField] = null;
                $fields_d[$leftField] = $cit;

                // New logic for selecting the next available index
                $foundNextAvailable = false;
                for ($nextIndex = $index + 1; $nextIndex < count($chkInTimes); $nextIndex++) {
                    $nextTimeField = $chkInTimes[$nextIndex];
                    $nextStatusField = $chkInStatusFields[$nextIndex];
                    $nextArriveField = $drArriveFields[$nextIndex];
                    $nextLeftField = $drLeftFields[$nextIndex];
                    $nextChemberField = $chemberFields[$nextIndex];
                    $nextChkOutTimeField = $chkOutTimes[$nextIndex];
                    $nextMaxBookField = $maxBookFields[$nextIndex];
                    $nextTime = DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->value($nextTimeField);
                    $nextStatus = DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->value($nextStatusField);

                    if (!is_null($nextTime) && $nextStatus !== 'OUT') {
                        $selectedArriveField = $nextArriveField;
                        $selectedLeftField = $nextLeftField;
                        $selectedStatusField = $nextStatusField;
                        $selectedChemberField = $nextChemberField;
                        $selectedChkInTime = $nextTimeField;
                        $selectedChkOutTime = $nextChkOutTimeField;
                        $selectedMaxBookField = $nextMaxBookField;
                        $foundNextAvailable = true;
                        break;
                    }
                }

                // If no next available index found, use the current index
                if (!$foundNextAvailable) {
                    $selectedArriveField = $arriveField;
                    $selectedLeftField = $leftField;
                    $selectedStatusField = $statusField;
                    $selectedChemberField = $chemberField;
                    $selectedChkInTime = $timeField;
                    $selectedChkOutTime = $chkOutTimeField;
                    $selectedMaxBookField = $maxBookField;
                }
            }
        }


        // $fields_d = [
        //     "CHK_IN_STATUS" => 'OUT',
        //     "CHEMBER_NO" => null,
        // ];
        $fields_p1 = [
            "STATUS" => 'Cancelled',
            "REASONS" => 'Clinic',
            "DR_STATUS" => 'OUT',
            "CHEMBER_NO" => null,
        ];
        $fields_p2 = [
            "DR_STATUS" => 'OUT',
            "CHEMBER_NO" => null,
        ];

        try {
            DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->update($fields_d);
            DB::table('appointment')
                ->where(function ($query) use ($tdt, $apntid, $fields_p2, $chk_in_time) {
                    $query->where(['APPNT_ID' => $apntid, 'APPNT_DT' => $tdt, 'APPNT_FROM' => $chk_in_time])->update($fields_p2);
                })
                ->where(['APPNT_ID' => $input['SCH_ID'], 'APPNT_DT' => $input['APPNT_DT']])->update($fields_p1);

            $todayDoctors = DB::table('pharmacy')
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
                    DB::raw("'" . Carbon::now()->format('Ymd') . "' as APPNT_DT"),
                    'dr_availablity.ID',
                    'dr_availablity.SCH_DAY',
                    'dr_availablity.START_MONTH',
                    'dr_availablity.MONTH',
                    'dr_availablity.ABS_FDT',
                    'dr_availablity.ABS_TDT',
                    'dr_availablity.' . $selectedChkInTime . ' AS CHK_IN_TIME', // Include the selected CHK_IN_TIME field
                    'dr_availablity.' . $selectedChkOutTime . ' AS CHK_OUT_TIME', // Include the selected CHK_OUT_TIME field
                    'dr_availablity.' . $selectedStatusField . ' AS CHK_IN_STATUS', // Include the selected status field
                    'dr_availablity.' . $selectedChemberField . ' AS CHEMBER_NO', // Include the selected chember field
                    'dr_availablity.' . $selectedMaxBookField . ' AS MAX_BOOK', // Include the selected MAX_BOOK field
                    'dr_availablity.' . $selectedArriveField . ' AS DR_ARRIVE',
                    'dr_availablity.' . $selectedLeftField . ' AS DR_LEFT'

                )
                ->where('pharmacy.STATUS', '=', 'Active')
                ->where(['dr_availablity.ID' => $input['SCH_ID']])
                ->get();

            foreach ($todayDoctors as $doctor) {
                $result = [
                    "PHARMA_ID" => $doctor->PHARMA_ID,
                    "PHARMA_NAME" => $doctor->ITEM_NAME,
                    "ADDRESS" => $doctor->ADDRESS,
                    "CITY" => $doctor->CITY,
                    "PIN" => $doctor->PIN,
                    "DIST" => $doctor->DIST,
                    "STATE" => $doctor->STATE,
                    "LATITUDE" => $doctor->LATITUDE,
                    "LONGITUDE" => $doctor->LONGITUDE,
                    "PHOTO_URL" => $doctor->PHOTO_URL,
                    "LOGO_URL" => $doctor->LOGO_URL,
                    "DR_ID" => $doctor->DR_ID,
                    "DR_NAME" => $doctor->DR_NAME,
                    "DR_MOBILE" => $doctor->DR_MOBILE,
                    "SEX" => $doctor->SEX,
                    "DESIGNATION" => $doctor->DESIGNATION,
                    "QUALIFICATION" => $doctor->QUALIFICATION,
                    "D_CATG" => $doctor->D_CATG,
                    "EXPERIENCE" => $doctor->EXPERIENCE,
                    "LANGUAGE" => $doctor->LANGUAGE,
                    "DR_PHOTO" => $doctor->DR_PHOTO,
                    "DR_FEES" => $doctor->DR_FEES,
                    "APPNT_ID" => $doctor->ID,
                    "APPNT_DT" => $doctor->APPNT_DT,
                    "CHK_IN_TIME" => $doctor->CHK_IN_TIME,
                    "CHK_OUT_TIME" => $doctor->CHK_OUT_TIME,
                    "DR_STATUS" => $doctor->CHK_IN_STATUS,
                    "DR_ARRIVE" => $doctor->DR_ARRIVE,
                    "DR_LEFT" => $doctor->DR_LEFT,
                    "CHEMBER_NO" => $doctor->CHEMBER_NO,
                    "MAX_BOOK" => $doctor->MAX_BOOK,
                ];
            }

            $response = [
                'Success' => true,
                'Message' => 'Doctor has been left',
                'data' => $result,
                'code' => 200
            ];
        } catch (\Exception $e) {
            $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
        }
        return $response;
    }

    function drdelay(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ]);
        }
        date_default_timezone_set('Asia/Kolkata');
        $input = $req->json()->all();
        $chk_in_time = $input['FROM_TIME'];

        // Define arrays for the update and checking
        $chkInTimes = ['CHK_IN_TIME', 'CHK_IN_TIME1', 'CHK_IN_TIME2', 'CHK_IN_TIME3'];
        $chkInStatusFields = ['CHK_IN_STATUS', 'CHK_IN_STATUS1', 'CHK_IN_STATUS2', 'CHK_IN_STATUS3'];
        $drArriveFields = ['DR_ARRIVE', 'DR_ARRIVE1', 'DR_ARRIVE2', 'DR_ARRIVE3'];
        $drdelays = ['DR_DELAY', 'DR_DELAY1', 'DR_DELAY2', 'DR_DELAY3'];
        $chemberFields = ['CHEMBER_NO', 'CHEMBER_NO1', 'CHEMBER_NO2', 'CHEMBER_NO3'];
        $chkOutTimes = ['CHK_OUT_TIME', 'CHK_OUT_TIME1', 'CHK_OUT_TIME2', 'CHK_OUT_TIME3'];
        $maxBookFields = ['MAX_BOOK', 'MAX_BOOK1', 'MAX_BOOK2', 'MAX_BOOK3'];

        $selectedStatusField = 'CHK_IN_STATUS'; // Default to CHK_IN_STATUS
        $selectedChemberField = 'CHEMBER_NO'; // Default to CHEMBER_NO
        $selectedChkInTime = 'CHK_IN_TIME'; // Default to CHK_IN_TIME
        $selectedChkOutTime = 'CHK_OUT_TIME'; // Default to CHK_OUT_TIME
        $selectedMaxBookField = 'MAX_BOOK'; // Default to MAX_BOOK
        foreach ($chkInTimes as $index => $timeField) {
            $statusField = $chkInStatusFields[$index];
            $arriveField = $drArriveFields[$index];
            $chemberField = $chemberFields[$index];
            $chkOutTimeField = $chkOutTimes[$index];
            $maxBookField = $maxBookFields[$index];
            $lateField = $drdelays[$index];
            $time = DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->value($timeField);
            if ($chk_in_time === $time) {
                $fields_d[$statusField] = 'DELAY';
                $fields_d[$lateField] = $input['DELAY'];
                $selectedStatusField = $statusField; // Update the selected status field
                $selectedDelayField = $lateField; // Update the selected chember field
                $selectedChkInTime = $timeField; // Update the selected CHK_IN_TIME field
                $selectedChkOutTime = $chkOutTimeField; // Update the selected CHK_OUT_TIME field
                $selectedMaxBookField = $maxBookField; // Update the selected MAX_BOOK field
            }
        }
        // $fields_d = [
        //     "DR_DELAY" => $input['DELAY'],
        //     "CHK_IN_STATUS" => 'DELAY',
        // ];
        $fields_p1 = [
            "DR_DELAY" => $input['DELAY'],
            "DR_STATUS" => 'DELAY',
        ];

        try {
            DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->update($fields_d);
            DB::table('appointment')->where(['APPNT_ID' => $input['SCH_ID'], 'APPNT_DT' => $input['APPNT_DT']])->update($fields_p1);
            $todayDoctors = DB::table('pharmacy')
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
                    DB::raw("'" . Carbon::now()->format('Ymd') . "' as APPNT_DT"),
                    'dr_availablity.ID',
                    'dr_availablity.SCH_DAY',
                    'dr_availablity.START_MONTH',
                    'dr_availablity.MONTH',
                    'dr_availablity.ABS_FDT',
                    'dr_availablity.ABS_TDT',
                    'dr_availablity.' . $selectedChkInTime . ' AS CHK_IN_TIME', // Include the selected CHK_IN_TIME field
                    'dr_availablity.' . $selectedChkOutTime . ' AS CHK_OUT_TIME', // Include the selected CHK_OUT_TIME field
                    'dr_availablity.' . $selectedStatusField . ' AS CHK_IN_STATUS', // Include the selected status field
                    'dr_availablity.' . $selectedChemberField . ' AS CHEMBER_NO',
                    'dr_availablity.DR_ARRIVE',
                    'dr_availablity.' . $selectedMaxBookField . ' AS MAX_BOOK', // Include the selected MAX_BOOK field
                    'dr_availablity.' . $selectedDelayField . ' AS DR_DELAY',
                )
                ->where('pharmacy.STATUS', '=', 'Active')
                ->where(['dr_availablity.ID' => $input['SCH_ID']])
                ->get();

            foreach ($todayDoctors as $doctor) {
                $result = [
                    "PHARMA_ID" => $doctor->PHARMA_ID,
                    "PHARMA_NAME" => $doctor->ITEM_NAME,
                    "ADDRESS" => $doctor->ADDRESS,
                    "CITY" => $doctor->CITY,
                    "PIN" => $doctor->PIN,
                    "DIST" => $doctor->DIST,
                    "STATE" => $doctor->STATE,
                    "LATITUDE" => $doctor->LATITUDE,
                    "LONGITUDE" => $doctor->LONGITUDE,
                    "PHOTO_URL" => $doctor->PHOTO_URL,
                    "LOGO_URL" => $doctor->LOGO_URL,
                    "DR_ID" => $doctor->DR_ID,
                    "DR_NAME" => $doctor->DR_NAME,
                    "DR_MOBILE" => $doctor->DR_MOBILE,
                    "SEX" => $doctor->SEX,
                    "DESIGNATION" => $doctor->DESIGNATION,
                    "QUALIFICATION" => $doctor->QUALIFICATION,
                    "D_CATG" => $doctor->D_CATG,
                    "EXPERIENCE" => $doctor->EXPERIENCE,
                    "LANGUAGE" => $doctor->LANGUAGE,
                    "DR_PHOTO" => $doctor->DR_PHOTO,
                    "DR_FEES" => $doctor->DR_FEES,
                    "APPNT_ID" => $doctor->ID,
                    "APPNT_DT" => $doctor->APPNT_DT,
                    "CHK_IN_TIME" => $doctor->CHK_IN_TIME,
                    "CHK_OUT_TIME" => $doctor->CHK_OUT_TIME,
                    "DR_STATUS" => 'DELAY',
                    "DR_ARRIVE" => $doctor->DR_ARRIVE,
                    "CHEMBER_NO" => $doctor->CHEMBER_NO,
                    "MAX_BOOK" => $doctor->MAX_BOOK,
                ];
            }

            $response = [
                'Success' => true,
                'Message' => 'Doctor delay',
                'data' => $result,
                'code' => 200
            ];
        } catch (\Exception $e) {
            $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
        }
        return $response;
    }

    function drleave(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ]);
        }
        date_default_timezone_set('Asia/Kolkata');
        $input = $req->json()->all();

        $fields = [
            "CHK_IN_STATUS" => 'LEAVE',
            "CHK_IN_STATUS1" => 'LEAVE',
            "CHK_IN_STATUS2" => 'LEAVE',
            "CHK_IN_STATUS3" => 'LEAVE',
            "ABS_FDT" => $input['FROM'],
            "ABS_TDT" => $input['TO'],
        ];
        $fields1 = [
            "DR_STATUS" => 'LEAVE',
        ];

        try {
            DB::table('dr_availablity')->where(['PHARMA_ID' => $input['PHARMA_ID'], 'DR_ID' => $input['DR_ID']])->update($fields);
            DB::table('appointment')
                ->where('APPNT_DT', '>=', $input['FROM'])
                ->where('APPNT_DT', '<=', $input['TO'])
                ->update($fields1);
            $response = [
                'Success' => true,
                'Message' => 'Doctor in leave',
                'code' => 200
            ];
        } catch (\Exception $e) {
            $response = ['Success' => true, 'Message' => $e->getMessage(), 'code' => 500];
        }
        return $response;
    }

    function vunonsch(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {
                $p_id = $input['PHARMA_ID'];

                $data = DB::table('dr_availablity')
                    ->join('drprofile', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
                    ->select(
                        'drprofile.DR_ID',
                        'drprofile.DR_NAME',
                        'drprofile.DR_MOBILE',
                        'drprofile.UID_NMC',
                        'drprofile.REGN_NO',
                        'drprofile.SEX',
                        'drprofile.DESIGNATION',
                        'drprofile.QUALIFICATION',
                        'drprofile.D_CATG',
                        'drprofile.EXPERIENCE',
                        'drprofile.LANGUAGE',
                        'drprofile.PHOTO_URL as DR_PHOTO',
                        'drprofile.REPORTING_DAY',
                        'dr_availablity.DR_FEES',
                    )
                    ->where(['dr_availablity.PHARMA_ID' => $p_id, 'dr_availablity.SCH_STATUS' => 'NA'])
                    ->where('drprofile.APPROVE', 'true')
                    ->orderBy('dr_availablity.DR_ID')
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

    function editdr(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ], 405);
        }

        $input = $req->all();
        $drid = $input['DR_ID'];
        $fileName = null;
        if ($req->file('FILE')) {

            if ($drid) {
                $fileName = $drid . "." . $req->file('FILE')->getClientOriginalExtension();
                $req->file('FILE')->storeAs('drprofile/drphoto', $fileName);
                $url = asset(storage::url('app/drprofile/drphoto')) . "/" . $fileName;
            }
        }
        $url = $url ?? null;

        $fields_dr = [
            // "DR_ID" => $input['DR_ID'] ?? null,
            "DESIGNATION" => $input['DESIGNATION'] ?? null,
            "DIS_ID" => $input['DIS_ID'] ?? null,
            "DR_MOBILE" => $input['DR_MOBILE'] ?? null,
            "DR_NAME" => $input['DR_NAME'] ?? null,
            "D_CATG" => $input['D_CATG'] ?? null,
            "EXPERIENCE" => $input['EXPERIENCE'] ?? null,
            "LANGUAGE" => $input['LANGUAGE'] ?? null,
            "QUALIFICATION" => $input['QUALIFICATION'] ?? null,
            "REGN_NO" => $input['REGN_NO'] ?? null,
            "SEX" => $input['SEX'] ?? null,
            "REPORTING_DAY" => $input['REPORTING_DAY'] ?? null,
            // "UID_NMC" => $input['UID_NMC'] ?? null,
            "COUNCIL" => $input['COUNCIL'] ?? null,
            "PHOTO_URL" => $url,
        ];

        $fields_sch = [
            // "DR_ID" => $input['DR_ID'] ?? null,
            "DR_FEES" => $input['DR_FEES'] ?? null,
            // "SCH_ID" => $input['DR_ID'] ?? null,
            "PHARMA_ID" => $input['PHARMA_ID'] ?? null,
            "PHARMA_NAME" => $input['PHARMA_NAME'] ?? null,
        ];

        try {
            DB::table('drprofile')
                ->where(['drprofile.DR_ID' => $drid])
                ->update($fields_dr);
            DB::table('dr_availablity')->where('SCH_ID', $drid)->update($fields_sch);
            $response = ['Success' => true, 'Message' => 'Doctor update successfully', 'code' => 200];
        } catch (\Exception $e) {
            $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
        }

        return $response;
    }

    function patientarrive(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['DR_ID']) && isset($input['PHARMA_ID']) && isset($input['APPNT_ID']) && isset($input['FROM_TIME']) && isset($input['BOOKING_ID'])) {
                $drid = $input['DR_ID'];
                $fid = $input['PHARMA_ID'];
                $apid = $input['APPNT_ID'];
                $fromTime = $input['FROM_TIME'];
                $cdt = date('Ymd');
                $apdt = date('Ymd');

                $data1 = DB::table('dr_availablity')
                    ->where(['DR_ID' => $drid, 'PHARMA_ID' => $fid, 'ID' => $apid])
                    ->first();

                if (!$data1) {
                    return ['Success' => false, 'Message' => 'Availability data not found.', 'code' => 404];
                }

                $slots = [
                    'Morning' => [],
                    'Afternoon' => [],
                    'Evening' => [],
                    'Night' => []
                ];

                $chkInTimes = [
                    $data1->CHK_IN_TIME,
                    $data1->CHK_IN_TIME1,
                    $data1->CHK_IN_TIME2,
                    $data1->CHK_IN_TIME3
                ];
                $chkOutTimes = [
                    $data1->CHK_OUT_TIME,
                    $data1->CHK_OUT_TIME1,
                    $data1->CHK_OUT_TIME2,
                    $data1->CHK_OUT_TIME3
                ];
                $maxbooks = [
                    $data1->MAX_BOOK,
                    $data1->MAX_BOOK1,
                    $data1->MAX_BOOK2,
                    $data1->MAX_BOOK3
                ];
                $intvl = $data1->SLOT_INTVL ?? null;

                foreach ($chkInTimes as $index => $chkin) {
                    $maxbk = $maxbooks[$index];
                    $chkout = $chkOutTimes[$index];
                    if ($chkin === null) {
                        continue;
                    }

                    try {
                        $chkinTime = Carbon::createFromFormat('h:i A', $chkin);
                        $chkoutTime = $chkout !== null ? Carbon::createFromFormat('h:i A', $chkout) : $chkinTime->copy()->addMinutes($intvl * $maxbk);
                    } catch (\Exception $e) {
                        return ["Error" => "Error in time conversion: " . $e->getMessage()];
                    }

                    $slot_sts = null;
                    $serialCounter = 1;
                    if ($data1->SLOT == '1') {

                        while ($chkinTime->lessThan($chkoutTime)) {
                            $endSlot = $chkinTime->copy()->addHour();
                            if ($endSlot->greaterThan($chkoutTime)) {
                                $endSlot = $chkoutTime;
                            }

                            $bookedCount = DB::table('appointment')
                                ->where('APPNT_FROM',  $chkinTime->format('h:i A'))
                                ->where(['APPNT_ID' => $apid, 'APPNT_DT' => $apdt, 'ARRIVE' => 'true'])
                                ->count();

                            // Log::info($chkinTime . 'Booked Count: ' . $bookedCount);

                            $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);

                            $bookingSerials = range($serialCounter, $serialCounter + $totalAppointments - 1);
                            $serialCounter += $totalAppointments;
                            $availableSerials = [];

                            if ($bookedCount > 0) {
                                $bookedSerials = range($serialCounter - $totalAppointments, $serialCounter - $totalAppointments + $bookedCount - 1);
                                $availableSerials = array_diff($bookingSerials, $bookedSerials);
                            } else {
                                $availableSerials = $bookingSerials;
                            }
                            $availableSerials = array_values($availableSerials);

                            $availAppointments = count($availableSerials);

                            if ($cdt === $apdt) {
                                $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
                            } else {
                                $slot_sts = "Available";
                            }

                            $slotString = [
                                "FROM" => $chkinTime->format('h:i A'),
                                "TO" => $endSlot->format('h:i A'),
                                "TOTAL_APNT" => $totalAppointments,
                                "AVAIL_APNT" => $availAppointments,
                                "BOOKING_SERIALS" => $bookingSerials,
                                "AVAILABLE_SERIALS" => $availableSerials,
                                "SLOT_STATUS" => $slot_sts,
                            ];

                            if ($chkinTime->hour < 12) {
                                $slots['Morning'][] = $slotString;
                            } elseif ($chkinTime->hour < 16) {
                                $slots['Afternoon'][] = $slotString;
                            } elseif ($chkinTime->hour < 20) {
                                $slots['Evening'][] = $slotString;
                            } else {
                                $slots['Night'][] = $slotString;
                            }

                            if ($chkinTime->format('h:i A') == $fromTime) {
                                $matchingSlots = $availableSerials[0];
                            }

                            $chkinTime->addHour();
                        }
                    } else if ($data1->SLOT == '2') {
                        while ($chkinTime->lessThan($chkoutTime)) {
                            $endSlot = $chkinTime->copy()->addMinutes($intvl);
                            if ($endSlot->greaterThan($chkoutTime)) {
                                break;
                            }

                            $bookedCount = DB::table('appointment')
                                ->where('APPNT_FROM',  $chkinTime->format('h:i A'))
                                ->where(['APPNT_ID' => $apid, 'APPNT_DT' => $apdt, 'ARRIVE' => 'true'])
                                ->count();

                            // Log::info('Booked Count: ' . $bookedCount);

                            $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                            $bookingSerials = range($serialCounter, $serialCounter + $totalAppointments - 1);
                            $serialCounter += $totalAppointments;
                            $availableSerials = [];

                            $availableSerials = [];
                            if ($bookedCount > 0) {
                                $bookedSerials = range($serialCounter - $totalAppointments, $serialCounter - $totalAppointments + $bookedCount - 1);
                                $availableSerials = array_diff($bookingSerials, $bookedSerials);
                            } else {
                                $availableSerials = $bookingSerials;
                            }
                            $availableSerials = array_values($availableSerials);

                            $availAppointments = count($availableSerials);

                            if ($cdt === $apdt) {
                                $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
                            } else {
                                $slot_sts = "Available";
                            }

                            $slotString = [
                                "FROM" => $chkinTime->format('h:i A'),
                                "TO" => $endSlot->format('h:i A'),
                                "TOTAL_APNT" => $totalAppointments,
                                "AVAIL_APNT" => $availAppointments,
                                "BOOKING_SERIALS" => $bookingSerials,
                                "AVAILABLE_SERIALS" => $availableSerials,
                                "SLOT_STATUS" => $slot_sts,
                            ];


                            if ($chkinTime->hour < 12) {
                                $slots['Morning'][] = $slotString;
                            } elseif ($chkinTime->hour < 16) {
                                $slots['Afternoon'][] = $slotString;
                            } elseif ($chkinTime->hour < 20) {
                                $slots['Evening'][] = $slotString;
                            } else {
                                $slots['Night'][] = $slotString;
                            }

                            if ($chkinTime->format('h:i A') == $fromTime && !empty($availableSerials)) {
                                $matchingSlots = $availableSerials[0];
                            }
                            $chkinTime->addMinutes($intvl);
                        }
                    }
                }

                $fields = [
                    "BOOKING_SL" => $matchingSlots,
                    "ARRIVE" => 'true'
                ];

                try {
                    DB::table('appointment')->where(['BOOKING_ID' => $input['BOOKING_ID']])->update($fields);
                    $response = [
                        'Success' => true,
                        'Message' => 'Confirmed Patient Serial No.',
                        'Serial No' => $matchingSlots,
                        'code' => 200
                    ];
                } catch (\Exception $e) {
                    $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
                }
                return $response;
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }



    function hlthtrk(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ]);
        }
        date_default_timezone_set('Asia/Kolkata');
        $input = $req->json()->all();
        $cdt = Carbon::now()->format('Ymd');
        $fields = [
            "USER_ID" => $input['PATIENT_ID'],
            "TRACK_DT" => $cdt,
            "BP" => $input['BP'],
            "SUGAR_FBS" => $input['SUGAR'],
            "TEMP" => $input['TEMP'],
            "WEIGHT" => $input['WEIGHT'],
        ];
        try {
            DB::table('health_tracker')->insert($fields);
            $response = [
                'Success' => true,
                'Message' => 'Health record saved successfully',
                'code' => 200
            ];
        } catch (\Exception $e) {
            $response = ['Success' => true, 'Message' => $e->getMessage(), 'code' => 500];
        }
        return $response;
    }

    function appntdr(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {
                $pid = $input['PHARMA_ID'];
                $date = Carbon::now();
                $TDAY = $date->format('Ymd');
                $weekNumber = Carbon::now()->weekOfMonth;
                $day1 = date('l'); // Get today's day in full textual representation (Sunday through Saturday)
                $data = array();
                $cdy = date('d');
                $cdt = date('Ymd');

                $todayDoctors = DB::table('pharmacy')
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
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
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
                        'dr_availablity.ID',
                        'dr_availablity.SCH_DAY',
                        'dr_availablity.START_MONTH',
                        'dr_availablity.MONTH',
                        'dr_availablity.ABS_FDT',
                        'dr_availablity.ABS_TDT',
                        'dr_availablity.CHK_IN_TIME',
                        'dr_availablity.CHK_OUT_TIME',
                        'dr_availablity.CHK_IN_TIME1',
                        'dr_availablity.CHK_OUT_TIME1',
                        'dr_availablity.CHK_IN_TIME2',
                        'dr_availablity.CHK_OUT_TIME2',
                        'dr_availablity.CHK_IN_TIME3',
                        'dr_availablity.CHK_OUT_TIME3',
                        'dr_availablity.CHK_IN_STATUS',
                        'dr_availablity.CHK_IN_STATUS1',
                        'dr_availablity.CHK_IN_STATUS2',
                        'dr_availablity.CHK_IN_STATUS3',
                        'dr_availablity.CHEMBER_NO',
                        'dr_availablity.CHEMBER_NO1',
                        'dr_availablity.CHEMBER_NO2',
                        'dr_availablity.CHEMBER_NO3',
                        'dr_availablity.DR_ARRIVE',
                        'dr_availablity.DR_ARRIVE1',
                        'dr_availablity.DR_ARRIVE2',
                        'dr_availablity.DR_ARRIVE3',
                        'dr_availablity.DR_DELAY',
                        'dr_availablity.DR_DELAY1',
                        'dr_availablity.DR_DELAY2',
                        'dr_availablity.DR_DELAY3',
                        'dr_availablity.MAX_BOOK',
                        'dr_availablity.MAX_BOOK1',
                        'dr_availablity.MAX_BOOK2',
                        'dr_availablity.MAX_BOOK3',
                        'dr_availablity.SLOT_INTVL'
                    )
                    ->where(['dr_availablity.SCH_DAY' => $day1, 'dr_availablity.PHARMA_ID' => $pid])
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->where('WEEK', 'like', '%' . $weekNumber . '%')
                    ->where('drprofile.APPROVE', 'true')
                    ->orWhere('dr_availablity.SCH_DT', $cdy)
                    // ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
                    // ->orderBy('dr_availablity.CHK_IN_TIME')
                    ->get();

                $result = [];
                $chk = array("CHK_IN_TIME", "CHK_IN_TIME1", "CHK_IN_TIME2", "CHK_IN_TIME3");
                $chkout = array("CHK_OUT_TIME", "CHK_OUT_TIME1", "CHK_OUT_TIME2", "CHK_OUT_TIME3");
                $CHKINSTATUS = array("CHK_IN_STATUS", "CHK_IN_STATUS1", "CHK_IN_STATUS2", "CHK_IN_STATUS3");
                $delay = array("DR_DELAY", "DR_DELAY1", "DR_DELAY2", "DR_DELAY3");
                $chamber = array("CHEMBER_NO", "CHEMBER_NO1", "CHEMBER_NO2", "CHEMBER_NO3");

                $currentTime = Carbon::now();

                foreach ($todayDoctors as $key => $doctor) {
                    $maxBookSum = 0;
                    $sch_dt = null; // Initialize $sch_dt here

                    if (is_numeric($doctor->SCH_DAY)) {
                        $date = Carbon::createFromDate(date('Y'), $doctor->START_MONTH, $doctor->SCH_DAY)
                            ->addMonths($doctor->MONTH);
                        if ($date->format('Ymd') === $cdt) {
                            $sch_dt = $date->format('Ymd');
                        }
                    } else {
                        $sch_dt = Carbon::now()->format('Ymd');
                    }
                    $nonNullChkInTimes = array_filter([$doctor->CHK_IN_TIME, $doctor->CHK_IN_TIME1, $doctor->CHK_IN_TIME2, $doctor->CHK_IN_TIME3], function ($time) {
                        return !empty($time);
                    });

                    for ($i = 0; $i < count($nonNullChkInTimes); $i++) {
                        $checkOutTime = Carbon::parse($doctor->{$chkout[$i]});
                        if (!empty($doctor->{$delay[$i]})) {
                            $checkOutTime = $checkOutTime->addMinutes($doctor->{$delay[$i]});
                        }

                        if ($currentTime->lessThanOrEqualTo($checkOutTime)) {
                            if (!in_array($doctor->{$CHKINSTATUS[$i]}, ['OUT', 'CANCELLED', 'LEAVE'])) {
                                $doctor->CHK_IN_TIME = $doctor->{$chk[$i]};
                                $doctor->CHK_OUT_TIME = $doctor->{$chkout[$i]};
                                $doctor->CHK_IN_STATUS = $doctor->{$CHKINSTATUS[$i]};
                                $doctor->CHEMBER_NO = $doctor->{$chamber[$i]};
                                $doctor->DR_DELAY = $doctor->{$delay[$i]} ?? null;
                                break;
                            } else {
                                $nextIndex = $i + 1;
                                if ($nextIndex < count($chk) && !empty($doctor->{$chk[$nextIndex]}) && !in_array($doctor->{$CHKINSTATUS[$nextIndex]}, ['OUT', 'CANCELLED'])) {
                                    $doctor->CHK_IN_TIME = $doctor->{$chk[$nextIndex]};
                                    $doctor->CHK_OUT_TIME = $doctor->{$chkout[$nextIndex]};
                                    $doctor->CHK_IN_STATUS = $doctor->{$CHKINSTATUS[$nextIndex]};
                                    $doctor->CHEMBER_NO = $doctor->{$chamber[$nextIndex]};
                                    $doctor->DR_DELAY = $doctor->{$delay[$nextIndex]} ?? null;
                                } else {
                                    $doctor->CHK_IN_TIME = $doctor->{$chk[$i]};
                                    $doctor->CHK_OUT_TIME = $doctor->{$chkout[$i]};
                                    $doctor->CHK_IN_STATUS = $doctor->{$CHKINSTATUS[$i]};
                                    $doctor->CHEMBER_NO = $doctor->{$chamber[$i]};
                                    $doctor->DR_DELAY = $doctor->{$delay[$i]} ?? null;
                                }
                            }
                        }
                    }
                    // Calculate sum of MAX_BOOK values that are not null for the current doctor
                    $maxBookDoctorSum = 0;
                    foreach (['MAX_BOOK', 'MAX_BOOK1', 'MAX_BOOK2', 'MAX_BOOK3'] as $maxBookField) {
                        if (!is_null($doctor->{$maxBookField})) {
                            $maxBookDoctorSum += $doctor->{$maxBookField};
                        }
                    }

                    $doctor->MAX_BOOK = $maxBookDoctorSum;

                    $groupedData[$doctor->DR_ID] = [
                        "PHARMA_ID" => $doctor->PHARMA_ID,
                        "PHARMA_NAME" => $doctor->ITEM_NAME,
                        "ADDRESS" => $doctor->ADDRESS,
                        "CITY" => $doctor->CITY,
                        "PIN" => $doctor->PIN,
                        "DIST" => $doctor->DIST,
                        "STATE" => $doctor->STATE,
                        "LATITUDE" => $doctor->LATITUDE,
                        "LONGITUDE" => $doctor->LONGITUDE,
                        "PHOTO_URL" => $doctor->PHOTO_URL,
                        "LOGO_URL" => $doctor->LOGO_URL,
                        "DR_ID" => $doctor->DR_ID,
                        "DR_NAME" => $doctor->DR_NAME,
                        "DR_MOBILE" => $doctor->DR_MOBILE,
                        "SEX" => $doctor->SEX,
                        "DESIGNATION" => $doctor->DESIGNATION,
                        "QUALIFICATION" => $doctor->QUALIFICATION,
                        "D_CATG" => $doctor->D_CATG,
                        "EXPERIENCE" => $doctor->EXPERIENCE,
                        "LANGUAGE" => $doctor->LANGUAGE,
                        "DR_PHOTO" => $doctor->DR_PHOTO,
                        "DR_FEES" => $doctor->DR_FEES,
                        "SCH_ID" => $doctor->ID,
                        "APPNT_DT" => $sch_dt,
                        "CHK_IN_TIME" => $doctor->CHK_IN_TIME,
                        "CHK_OUT_TIME" => $doctor->CHK_OUT_TIME,
                        "DR_STATUS" => $doctor->CHK_IN_STATUS,
                        // "CHK_IN_STATUS" => $doctor->CHK_IN_STATUS,
                        // "CHK_IN_STATUS1" => $doctor->CHK_IN_STATUS1,
                        // "CHK_IN_STATUS2" => $doctor->CHK_IN_STATUS2,
                        // "CHK_IN_STATUS3" => $doctor->CHK_IN_STATUS3,
                        "CHEMBER_NO" => $doctor->CHEMBER_NO,
                        "MAX_BOOK" => $maxBookDoctorSum,
                        "PATIENTS" => []
                    ];

                    // Fetching patients from appointment table
                    $patients = DB::table('appointment')
                        ->leftjoin('user_family', 'appointment.PATIENT_ID', '=', 'user_family.ID')
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
                            'appointment.ARRIVE',
                            'appointment.BOOKING_SL',
                            'appointment.STATUS',
                            'appointment.DR_FEES',
                            'appointment.PATIENT_REVIEW',
                            'appointment.APPNT_FROM',
                            'appointment.CHEMBER_NO',
                            'appointment.DR_STATUS',
                            'appointment.DR_DELAY',
                            'appointment.CHK_IN_TIME',
                            'appointment.CHK_OUT_TIME'
                        )
                        ->where('appointment.APPNT_DT', '=', $TDAY)
                        ->where('appointment.PHARMA_ID', $pid)
                        ->where('appointment.DR_ID', '=', $doctor->DR_ID)
                        ->get();

                    foreach ($patients as $patient) {
                        $groupedData[$doctor->DR_ID]['PATIENTS'][] = [
                            "FAMILY_ID" => $patient->FAMILY_ID,
                            "PATIENT_ID" => $patient->PATIENT_ID,
                            "PATIENT_NAME" => $patient->PATIENT_NAME,
                            "AGE" => $patient->DOB,
                            "SEX" => $patient->SEX,
                            "BOOKING_ID" => $patient->BOOKING_ID,
                            "APPNT_TOKEN" => $patient->APPNT_TOKEN,
                            "APPNT_ID" => $patient->APPNT_ID,
                            "APPNT_DT" => $patient->APPNT_DT,
                            "APPNT_FROM" => $patient->APPNT_FROM,
                            "STATUS" => $patient->STATUS,
                            "BOOKING_SL" => $patient->BOOKING_SL
                        ];
                    }
                }

                foreach ($groupedData as $pId => &$phid) {
                    $phid['TOT_PATIENT'] = count($phid['PATIENTS']);
                }
                $data = array_values($groupedData);

                // Filter out doctors with zero patients
                $data = array_filter($data, function ($doctor) {
                    return $doctor['TOT_PATIENT'] > 0;
                });



                if (empty($data)) {
                    $data = [];
                } else {
                    $doctorsCollection = collect($data);
                    $doctorsCollection = $doctorsCollection->sortBy(function ($availableDoctor) {
                        return $availableDoctor['CHK_IN_TIME'];
                    })->all();
                    $uniqueDoctors = collect($doctorsCollection)->unique('DR_ID');
                    $data = $uniqueDoctors->sortBy(function ($doctor) {
                        $statusOrder = [
                            'IN' => 1,
                            'TIMELY' => 2,
                            'DELAY' => 3,
                            'CANCELLED' => 4,
                            'OUT' => 5,
                            'LEAVE' => 6
                        ];
                        $statusPriority = $statusOrder[$doctor['DR_STATUS']] ?? 999;
                        return $doctor['APPNT_DT'] . '-' . sprintf('%02d', $statusPriority) . '-' . $doctor['CHK_IN_TIME'];
                    })->values()->all();
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

    function tdpatient(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID']) && isset($input['DR_ID'])) {
                $pid = $input['PHARMA_ID'];
                $did = $input['DR_ID'];
                $date = Carbon::now();

                $TDAY = $date->format('Ymd');
                $data = array();

                $data1 = DB::table('appointment')
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
                        'appointment.CHEMBER_NO',
                    )
                    ->where(['appointment.APPNT_DT' => $TDAY, 'appointment.PHARMA_ID' => $pid, 'appointment.DR_ID' => $did])
                    ->orderby('appointment.APPNT_FROM')
                    ->orderby('appointment.BOOKING_SL')
                    ->get();

                // return $data1;

                $groupedData = [];
                foreach ($data1 as $row) {

                    if (!isset($groupedData[$row->APPNT_FROM])) {
                        $groupedData[$row->APPNT_FROM] = [
                            "APPNT_FROM" => $row->APPNT_FROM,
                            "PATIENTS" => [],
                        ];
                    }
                    $groupedData[$row->APPNT_FROM]['PATIENTS'][] = [
                        "FAMILY_ID" => $row->FAMILY_ID,
                        "PATIENT_ID" => $row->PATIENT_ID,
                        "PATIENT_NAME" => $row->PATIENT_NAME,
                        "MOBILE" => $row->MOBILE,
                        "AGE" => $row->DOB,
                        "SEX" => $row->SEX,
                        "BOOKING_ID" => $row->BOOKING_ID,
                        "APPNT_TOKEN" => $row->APPNT_TOKEN,
                        "APPNT_ID" => $row->APPNT_ID,
                        "APPNT_DT" => $row->APPNT_DT,
                        "STATUS" => $row->STATUS,
                        "BOOKING_SL" => $row->BOOKING_SL,
                    ];
                }
                foreach ($groupedData as $pId => &$phid) {
                    $phid['TOT_PATIENT'] = count($phid['PATIENTS']);
                }
                $data = array_values($groupedData);

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function clalldr(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {
                $p_id = $input['PHARMA_ID'];

                $data = DB::table('drprofile')
                    ->join('dr_availablity', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
                    ->distinct('drprofile.DR_ID')
                    ->select(
                        'drprofile.DR_ID',
                        'drprofile.DR_NAME',
                        'drprofile.DR_MOBILE',
                        'drprofile.UID_NMC',
                        'drprofile.REGN_NO',
                        'drprofile.SEX',
                        'drprofile.DESIGNATION',
                        'drprofile.QUALIFICATION',
                        'drprofile.D_CATG',
                        'drprofile.EXPERIENCE',
                        'drprofile.COUNCIL',
                        'drprofile.LANGUAGE',
                        'drprofile.PHOTO_URL as DR_PHOTO',
                        'drprofile.REPORTING_DAY',
                        'dr_availablity.DR_FEES',
                    )
                    ->where(['dr_availablity.PHARMA_ID' => $p_id, 'APPROVE' => 'true'])
                    ->orderBy('drprofile.DR_ID')
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

    function editstaff(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ], 405);
        }
        $input = $req->all();
        $staffid = $input['STAFF_ID'];
        $fileName = null;
        if ($req->file('file')) {
            if ($staffid) {
                $fileName = $staffid . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('staff', $fileName);
                $url = asset(storage::url('app/staff')) . "/" . $fileName;
            }
        }
        $url = $url ?? null;
        $fields = [
            "STAFF_ID" => $input['STAFF_ID'] ?? null,
            "STAFF_NAME" => $input['STAFF_NAME'] ?? null,
            "STAFF_ADDRESS" => $input['STAFF_ADDRESS'] ?? null,
            "STAFF_DESIGN" => $input['STAFF_DESIGN'] ?? null,
            "STAFF_MOB" => $input['STAFF_MOB'] ?? null,
            "STAFF_AGE" => $input['STAFF_AGE'] ?? null,
            "STAFF_SEX" => $input['STAFF_SEX'] ?? null,
            "DEPT" => $input['DEPT'] ?? null,
            "SUB_DEPT" => $input['SUB_DEPT'] ?? null,
            "STAFF_STATUS" => $input['STAFF_STATUS'] ?? null,
            "STAFF_PHOTO" => $url,
        ];
        try {
            DB::table('user_staff')->where(['STAFF_ID' => $staffid])->update($fields);
            $response = ['Success' => true, 'Message' => 'Staff profile update successfully', 'code' => 200];
        } catch (\Exception $e) {
            $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
        }
        return $response;
    }

    function admcliniclogin(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $response = array();
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();

            if (isset($input['USERID']) && isset($input['PASSWORD'])) {
                $user = DB::table('pharmacy')
                    ->select(
                        'PHARMA_ID',
                        'USERID',
                        'ITEM_NAME',
                        'CLINIC_MOBILE',
                        'PASSWORD',
                        'ADDRESS',
                        'CITY',
                        'DIST',
                        'PIN',
                        'STATE',
                        'PHOTO_URL',
                        'LOGO_URL',
                        'CLINIC_TYPE',
                        'LATITUDE',
                        'LONGITUDE',
                        'CHEMBER_CT',
                        'GLIMG1 AS PHOTO_URL1',
                        'GLIMG2 AS PHOTO_URL2',
                        'GLIMG3 AS PHOTO_URL3',
                        'GLIMG4 AS PHOTO_URL4',
                        'GLIMG5 AS PHOTO_URL5',
                        'GLIMG6 AS PHOTO_URL6',
                        'GLIMG7 AS PHOTO_URL7',
                        'GLIMG8 AS PHOTO_URL8',
                        'GLIMG9 AS PHOTO_URL9',
                        'GLIMG10 AS PHOTO_URL10',
                        'SLDIMG1 AS SLIDER_IMG1',
                        'SLDIMG2 AS SLIDER_IMG2',
                        'SLDIMG3 AS SLIDER_IMG3',
                        'SLDIMG4 AS SLIDER_IMG4'
                    )
                    ->where(['USERID' => $input['USERID'], 'STATUS' => 'Active'])->where('pharmacy.STATUS', '=', 'Active')->first();

                if ($user != null) {
                    $data = [
                        "PHARMA_ID" => $user->PHARMA_ID,
                        "PHARMA_NAME" => $user->ITEM_NAME,
                        "CLINIC_MOBILE" => $user->CLINIC_MOBILE,
                        "ADDRESS" => $user->ADDRESS,
                        "CITY" => $user->CITY,
                        "DIST" => $user->DIST,
                        "PIN" => $user->PIN,
                        "STATE" => $user->STATE,
                        "PHOTO_URL" => $user->PHOTO_URL,
                        "LOGO_URL" => $user->LOGO_URL,
                        // "PHOTO_64" => base64_encode(file_get_contents($user->PHOTO_URL)), 
                        // "LOGO_64" => base64_encode(file_get_contents($user->LOGO_URL)), 
                        "CLINIC_TYPE" => $user->CLINIC_TYPE,
                        "LATITUDE" => $user->LATITUDE,
                        "LONGITUDE" => $user->LONGITUDE,
                        "CHEMBER_CT" => $user->CHEMBER_CT,
                        "USERID" => $user->USERID,
                        'PHOTO_URL1' => $user->PHOTO_URL1,
                        'PHOTO_URL2' => $user->PHOTO_URL2,
                        'PHOTO_URL3' => $user->PHOTO_URL3,
                        'PHOTO_URL4' => $user->PHOTO_URL4,
                        'PHOTO_URL5' => $user->PHOTO_URL5,
                        'PHOTO_URL6' => $user->PHOTO_URL6,
                        'PHOTO_URL7' => $user->PHOTO_URL7,
                        'PHOTO_URL8' => $user->PHOTO_URL8,
                        'PHOTO_URL9' => $user->PHOTO_URL9,
                        'PHOTO_URL10' => $user->PHOTO_URL10,
                        'SLIDER1' => $user->SLIDER_IMG1,
                        'SLIDER2' => $user->SLIDER_IMG2,
                        'SLIDER3' => $user->SLIDER_IMG3,
                        'SLIDER4' => $user->SLIDER_IMG4,
                    ];
                    if (($user->PASSWORD) === md5($request->input('PASSWORD'))) {
                        DB::table('pharmacy')->where(['USERID' => $input['USERID'], 'STATUS' => 'Active'])->update(['LOGIN' => carbon::now()]);
                        $token = base64_encode($request->input('PHARMA_ID') . $user->PASSWORD . $user->CLINIC_TYPE);
                        $_SESSION['TOKEN'] = $token;
                        $response = ['Success' => true, "data" => $data, 'Message' => 'Login Successfully', 'User_Type' => $user->CLINIC_TYPE, 'Token' => $token, 'Code' => 200];
                    } else {
                        $response = ['Success' => false, 'Message' => 'Wrong Password', 'status' => 200];
                    }
                } else {
                    $response = ['Success' => false, 'Message' => 'Account Not Found', 'status' => 200];
                }
            } else {
                $response = ["Success" => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ["Success" => false, 'Message' => 'Method not allowed', 'code' => 200];
        }
        return $response;
    }

    function clientlogin(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $response = array();
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();

            if (isset($input['USER_ID']) && isset($input['PASSWORD'])) {
                $user = DB::table('client')
                    ->where(['USER_ID' => $input['USER_ID'], 'CLIENT_STATUS' => 'Active'])->first();

                if ($user != null) {

                    if ($user->CLIENT_TYPE === 'Doctor') {
                        $result = DB::table('drprofile')->where('DR_ID', $user->CLIENT_ID)->first();


                        // $result = $data->map(function ($user) {
                        //     return [
                        //         "PHARMA_ID" => $user->PHARMA_ID,
                        //         "PHARMA_NAME" => $user->ITEM_NAME,
                        //         "CLINIC_MOBILE" => $user->CLINIC_MOBILE,
                        //         "ADDRESS" => $user->ADDRESS,
                        //         "CITY" => $user->CITY,
                        //         "DIST" => $user->DIST,
                        //         "PIN" => $user->PIN,
                        //         "STATE" => $user->STATE,
                        //         "PHOTO_URL" => $user->PHOTO_URL,
                        //         "LOGO_URL" => $user->LOGO_URL,
                        //         "CLINIC_TYPE" => $user->CLINIC_TYPE,
                        //         "LATITUDE" => $user->LATITUDE,
                        //         "LONGITUDE" => $user->LONGITUDE,
                        //         "CHEMBER_CT" => $user->CHEMBER_CT,
                        //         "USERID" => $user->USERID,
                        //         'PHOTO_URL1' => $user->GLIMG1,
                        //         'PHOTO_URL2' => $user->GLIMG2,
                        //         'PHOTO_URL3' => $user->GLIMG3,
                        //         'PHOTO_URL4' => $user->GLIMG4,
                        //         'PHOTO_URL5' => $user->GLIMG5,
                        //         'PHOTO_URL6' => $user->GLIMG6,
                        //         'PHOTO_URL7' => $user->GLIMG7,
                        //         'PHOTO_URL8' => $user->GLIMG8,
                        //         'PHOTO_URL9' => $user->GLIMG9,
                        //         'PHOTO_URL10' => $user->GLIMG10,
                        //         'SLIDER1' => $user->SLDIMG1,
                        //         'SLIDER2' => $user->SLDIMG2,
                        //         'SLIDER3' => $user->SLDIMG3,
                        //         'SLIDER4' => $user->SLDIMG4,
                        //     ];
                        // })->first();
                    } else {
                        $data = DB::table('pharmacy')->where('USERID', $input['USER_ID'])->get();
                        $result = $data->map(function ($user) {
                            return [
                                "PHARMA_ID" => $user->PHARMA_ID,
                                "PHARMA_NAME" => $user->ITEM_NAME,
                                "CLINIC_MOBILE" => $user->CLINIC_MOBILE,
                                "ADDRESS" => $user->ADDRESS,
                                "CITY" => $user->CITY,
                                "DIST" => $user->DIST,
                                "PIN" => $user->PIN,
                                "STATE" => $user->STATE,
                                "PHOTO_URL" => $user->PHOTO_URL,
                                "LOGO_URL" => $user->LOGO_URL,
                                "CLINIC_TYPE" => $user->CLINIC_TYPE,
                                "LATITUDE" => $user->LATITUDE,
                                "LONGITUDE" => $user->LONGITUDE,
                                "CHEMBER_CT" => $user->CHEMBER_CT,
                                "USERID" => $user->USERID,
                                'PHOTO_URL1' => $user->GLIMG1,
                                'PHOTO_URL2' => $user->GLIMG2,
                                'PHOTO_URL3' => $user->GLIMG3,
                                'PHOTO_URL4' => $user->GLIMG4,
                                'PHOTO_URL5' => $user->GLIMG5,
                                'PHOTO_URL6' => $user->GLIMG6,
                                'PHOTO_URL7' => $user->GLIMG7,
                                'PHOTO_URL8' => $user->GLIMG8,
                                'PHOTO_URL9' => $user->GLIMG9,
                                'PHOTO_URL10' => $user->GLIMG10,
                                'SLIDER1' => $user->SLDIMG1,
                                'SLIDER2' => $user->SLDIMG2,
                                'SLIDER3' => $user->SLDIMG3,
                                'SLIDER4' => $user->SLDIMG4,
                            ];
                        })->first();
                    }




                    if (($user->PASSWORD) === md5($request->input('PASSWORD'))) {
                        DB::table('client')->where(['USER_ID' => $input['USER_ID'], 'CLIENT_STATUS' => 'Active'])->update(['LOGIN' => carbon::now()]);
                        $token = base64_encode($request->input('PHARMA_ID') . $user->PASSWORD . $user->CLIENT_TYPE);
                        $_SESSION['TOKEN'] = $token;
                        $response = ['Success' => true, "data" => $result, 'Message' => 'Login Successfully', 'User_Type' => $user->CLIENT_TYPE, 'Token' => $token, 'Code' => 200];
                    } else {
                        $response = ['Success' => false, 'Message' => 'Wrong Password', 'status' => 200];
                    }
                } else {
                    $response = ['Success' => false, 'Message' => 'Account Not Found', 'status' => 200];
                }
            } else {
                $response = ["Success" => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ["Success" => false, 'Message' => 'Method not allowed', 'code' => 200];
        }
        return $response;
    }

    function logout(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();
            $response = array();
            session_start();
            DB::table('users')->where(['mobile' => $input['MOBILE']])->update(['LOGOUT' => carbon::now()]);
            session_destroy();
            $response = ['Success' => true, 'Message' => 'You are successfully logout.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function editclinicprofile(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ], 405);
        }
        $input = $req->all();
        $PID = $input['PHARMA_ID'];
        $fileName = null;
        if ($req->file('file')) {
            if ($PID) {
                $fileName = $PID . "P." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('clinicphoto', $fileName);
                $url = asset(storage::url('app/clinicphoto')) . "/" . $fileName;
            }
        }
        $url = $url ?? null;
        if ($req->file('file1')) {
            if ($PID) {
                $fileName1 = $PID . "L." . $req->file('file1')->getClientOriginalExtension();
                $req->file('file1')->storeAs('cliniclogo', $fileName1);
                $url1 = asset(storage::url('app/cliniclogo')) . "/" . $fileName1;
            }
        }
        $url1 = $url1 ?? null;
        $fields = [
            // "PHARMA_ID" => $input['PHARMA_ID'] ?? null,
            "ITEM_NAME" => $input['PHARMA_NAME'] ?? null,
            "CLINIC_MOBILE" => $input['CLINIC_MOBILE'] ?? null,
            "ADDRESS" => $input['ADDRESS'] ?? null,
            "CITY" => $input['CITY'] ?? null,
            "DIST" => $input['DIST'] ?? null,
            "PIN" => $input['PIN'] ?? null,
            "STATE" => $input['STATE'] ?? null,
            "PHOTO_URL" => $url,
            "LOGO_URL" => $url1,
            "CHEMBER_CT" => $input['CHEMBER_CT'] ?? null,
            "CLINIC_TYPE" => $input['CLINIC_TYPE'] ?? null,
            "LATITUDE" => $input['LATITUDE'] ?? null,
            "LONGITUDE" => $input['LONGITUDE'] ?? null,
        ];
        $data = [
            // "PHARMA_ID" => $input['PHARMA_ID'] ?? null,
            "PHARMA_NAME" => $input['PHARMA_NAME'] ?? null,
            "CLINIC_MOBILE" => $input['CLINIC_MOBILE'] ?? null,
            "ADDRESS" => $input['ADDRESS'] ?? null,
            "CITY" => $input['CITY'] ?? null,
            "DIST" => $input['DIST'] ?? null,
            "PIN" => $input['PIN'] ?? null,
            "STATE" => $input['STATE'] ?? null,
            "PHOTO_URL" => $url,
            "LOGO_URL" => $url1,
            "CHEMBER_CT" => $input['CHEMBER_CT'] ?? null,
            "CLINIC_TYPE" => $input['CLINIC_TYPE'] ?? null,
            "LATITUDE" => $input['LATITUDE'] ?? null,
            "LONGITUDE" => $input['LONGITUDE'] ?? null,
        ];


        try {
            DB::table('pharmacy')->where(['PHARMA_ID' => $PID])->where('pharmacy.STATUS', '=', 'Active')->update($fields);
            $response = ['Success' => true, 'data' => $data, 'Message' => 'Clinic profile update successfully', 'code' => 200];
        } catch (\Exception $e) {
            $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
        }
        return $response;
    }

    function editsection(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['PHARMA_ID'])) {

                $pid = $input['PHARMA_ID'];
                $data = array();

                $promo_bnr = DB::table('dashboard_section')
                    ->join('dashboard_item', 'dashboard_item.DASH_SECTION_ID', '=', 'dashboard_section.DASH_SECTION_ID')
                    ->leftJoin('promo_banner', function ($join) use ($pid) {
                        $join->on('dashboard_item.ID', '=', 'promo_banner.DASH_ID')
                            ->where('promo_banner.PHARMA_ID', '=', $pid);
                    })
                    ->select(
                        'dashboard_item.ID',
                        'dashboard_item.DASH_SECTION_ID',
                        'dashboard_section.DASH_SECTION_NAME',
                        'dashboard_item.DASH_NAME',
                        'dashboard_item.DASH_DESC',
                        'dashboard_item.DI_IMG5',
                        'promo_banner.REMARK',
                    )
                    ->distinct('dashboard_item.ID')
                    ->where(['dashboard_item.ITEM_TYPE' => 'About Us'])->get();




                $fltr_promo_bnr = $promo_bnr->filter(function ($item) use ($pid) {
                    return $item->DASH_SECTION_ID === 'U';
                });
                $A["Why_Choose_Us"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "DASH_ID" => $item->ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_DESCRIPTION" => $item->DASH_DESC,
                        "PHOTO_URL" => $item->DI_IMG5,
                        "REMARK" => $item->REMARK,
                    ];
                })->values()->all();

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) use ($pid) {
                    return $item->DASH_SECTION_ID === 'V';
                });
                $B["What_Makes_Us_Special"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "DASH_ID" => $item->ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_DESCRIPTION" => $item->DASH_DESC,
                        "PHOTO_URL" => $item->DI_IMG5,
                        "REMARK" => $item->REMARK,
                    ];
                })->values()->all();
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) use ($pid) {
                    return $item->DASH_SECTION_ID === 'W';
                });
                $C["Special_Services"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "DASH_ID" => $item->ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_DESCRIPTION" => $item->DASH_DESC,
                        "PHOTO_URL" => $item->DI_IMG5,
                        "REMARK" => $item->REMARK,
                    ];
                })->values()->all();
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) use ($pid) {
                    return $item->DASH_SECTION_ID === 'X';
                });
                $D["Advance_Equipments"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "DASH_ID" => $item->ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_DESCRIPTION" => $item->DASH_DESC,
                        "PHOTO_URL" => $item->DI_IMG5,
                        "REMARK" => $item->REMARK,
                    ];
                })->values()->all();
                $E["Doctors"] = DB::table('drprofile')
                    ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
                    // ->join('disease_catg', 'drprofile.DIS_ID', '=', 'disease_catg.DIS_ID')
                    ->distinct('drprofile.DR_ID')
                    ->select(
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
                        'dr_availablity.POSITION',
                    )

                    ->where(['dr_availablity.PHARMA_ID' => $pid])
                    ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
                    ->where('drprofile.APPROVE', 'true')
                    ->orderByRaw('CASE WHEN dr_availablity.POSITION = 0 THEN 1 ELSE 0 END, dr_availablity.POSITION')
                    ->distinct()
                    ->get()->toArray();

                $data = $A + $B + $C + $D + $E;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    // function addabout(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $response = array();

    //         $input = $req->json()->all();
    //         $abt_arr = $input['ABOUT_DATA'] ?? [];
    //         $dr_arr = $input['DR_DATA'] ?? [];
    //         $rmv_arr = $input['RMV_DATA'] ?? [];
    //         // $edt_arr = $input['EDT_DATA'] ?? [];

    //         if (is_array($rmv_arr) && !empty($rmv_arr)) {
    //             foreach ($rmv_arr as $row1) {
    //                 if (isset($row1['DASH_ID'])) {
    //                     DB::table('promo_banner')->where(['DASH_ID' => $row1['DASH_ID'], 'PHARMA_ID' => $row1['PHARMA_ID']])->delete();
    //                 }
    //             }
    //         }

    //         if (is_array($abt_arr) && !empty($abt_arr)) {
    //             foreach ($abt_arr as $row) {

    //                 $fields = [
    //                     "DASH_ID" => $row['DASH_ID'],
    //                     "PHARMA_ID" => $row['PHARMA_ID'],
    //                     "DASH_SECTION_ID" => $row['DASH_SECTION_ID'],
    //                     // "HEADER_NAME" => $row['DASH_SECTION_NAME'],
    //                     "PROMO_NAME" => $row['DASH_NAME'],
    //                     "DESCRIPTION" => $row['DASH_DESCRIPTION']??null,
    //                     "PROMO_URL" => $row['PHOTO_URL']??null,
    //                     "PROMO_TYPE" => 'About Us',
    //                     "REMARK" => 'Added'
    //                 ];
    //                 DB::table('promo_banner')->insert($fields);
    //             }
    //         }

    //         if (is_array($dr_arr) && !empty($dr_arr)) {
    //             foreach ($dr_arr as $row2) {
    //                 $PHARMA_ID = $row2['PHARMA_ID'];
    //                 $DR_ID = $row2['DR_ID'];
    //                 $POSITION = $row2['POSITION'];

    //                 DB::table('dr_availablity')
    //                     ->where(['DR_ID' => $DR_ID, 'PHARMA_ID' => $PHARMA_ID])
    //                     ->update(['POSITION' => $POSITION]);
    //             }
    //         }


    //         $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
    //     }
    //     return $response;
    // }

    function addabout(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->json()->all();
            $abt_arr = $input['ABOUT_DATA'] ?? [];
            $dr_arr = $input['DR_DATA'] ?? [];
            $rmv_arr = $input['RMV_DATA'] ?? [];

            DB::beginTransaction(); // Start transaction

            try {
                if (is_array($rmv_arr) && !empty($rmv_arr)) {
                    foreach ($rmv_arr as $row1) {
                        if (isset($row1['DASH_ID'])) {
                            DB::table('promo_banner')->where(['DASH_ID' => $row1['DASH_ID'], 'PHARMA_ID' => $row1['PHARMA_ID']])->delete();
                        }
                    }
                }

                if (is_array($abt_arr) && !empty($abt_arr)) {
                    $grouped_abt_arr = collect($abt_arr)->groupBy('DASH_SECTION_ID');
                    foreach ($grouped_abt_arr as $section_id => $items) {
                        $count = $items->count();
                        if (in_array($count, [2, 4, 6, 8])) {
                            foreach ($items as $row) {
                                $fields = [
                                    "DASH_ID" => $row['DASH_ID'],
                                    "PHARMA_ID" => $row['PHARMA_ID'],
                                    "DASH_SECTION_ID" => $row['DASH_SECTION_ID'],
                                    "PROMO_NAME" => $row['DASH_NAME'],
                                    "DESCRIPTION" => $row['DASH_DESCRIPTION'] ?? null,
                                    "PROMO_URL" => $row['PHOTO_URL'] ?? null,
                                    "PROMO_TYPE" => 'About Us',
                                    "REMARK" => 'Added'
                                ];
                                DB::table('promo_banner')->insert($fields);
                            }
                        } else {
                            throw new Exception("DASH_SECTION_ID $section_id must have exactly 2, 4, 6, or 8 items. Current count: $count");
                        }
                    }
                }

                DB::commit(); // Commit transaction
                $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
            } catch (Exception $e) {
                DB::rollback(); // Rollback transaction
                $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 400];
            }

            // Processing dr_arr outside the transaction
            if (is_array($dr_arr) && !empty($dr_arr)) {
                foreach ($dr_arr as $row2) {
                    $PHARMA_ID = $row2['PHARMA_ID'];
                    $DR_ID = $row2['DR_ID'];
                    $POSITION = $row2['POSITION'];

                    DB::table('dr_availablity')
                        ->where(['DR_ID' => $DR_ID, 'PHARMA_ID' => $PHARMA_ID])
                        ->update(['POSITION' => $POSITION]);
                }
            }

            if ($response['Success'] === false) {
                // Append message to response if insertion to promo_banner failed
                $response['Message'] .= " However, doctor availability is updated.";
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }

        return response()->json($response);
    }


    function staffprivilege(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');

            $response = array();
            $row = $req->json()->all();

            $fields = [
                "CASH_COLLECT" => $row['CASH_COLLECT'],
                "WALLET" => $row['WALLET'],
                "USER_CREATE" => $row['USER_CREATE'],
                "EDIT_USER" => $row['EDIT_USER'],
                "DELETE_USER" => $row['DELETE_USER'],
                "TRANS_REQ" => $row['TRANS_REQ'],
                "TEST_BOOK" => $row['TEST_BOOK'],
            ];

            try {
                DB::table('user_staff')->where(['PHARMA_ID' => $row['PHARMA_ID'], 'STAFF_ID' => $row['STAFF_ID']])->update($fields);
                $response = ['Success' => true, 'Message' => 'Staff privilege successfully.', 'code' => 200];
            } catch (\Throwable $th) {
                $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function staffleave(Request $req)
    {
        if (!$req->isMethod('post')) {
            return ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }
        $input = $req->json()->all();

        $array = $input['STAFF_LEAVE'] ?? [];
        foreach ($array as $row) {
            $sid = $row['STAFF_ID'];
            $pid = $row['PHARMA_ID'];
        }
        $leave = array_map(function ($item) {
            return Arr::except($item, ['PHARMA_ID', 'STAFF_ID']);
        }, $array);

        try {
            if (isset($row['STAFF_ID']) && isset($row['PHARMA_ID'])) {
                DB::table('user_staff')->where(['STAFF_ID' => $sid, 'PHARMA_ID' => $pid])->update(['HD' => json_encode($leave)]);

                return ['Success' => true, 'Message' => 'Staff leave updated successfully', 'code' => 200];
            } else {
                return ['Success' => false, 'Message' => 'Missing STAFF_ID or PHARMA_ID', 'code' => 400];
            }
        } catch (\Exception $e) {
            return ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
        }
    }

    function staffcl(Request $req)
    {
        if (!$req->isMethod('post')) {
            return ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }
        $row = $req->json()->all();
        $leave = $row['CL_DATA'];
        foreach ($leave as $row1) {
            $fields = [
                "STAFF_ID" => $row1['STAFF_ID'],
                "PHARMA_ID" => $row1['PHARMA_ID'],
                "L_DT" => $row1['DT'],
                "L_TYPE" => $row1['REMARKS'],
            ];

            try {
                if (isset($row1['STAFF_ID']) && isset($row1['PHARMA_ID'])) {
                    DB::table('staff_cl')->where(['STAFF_ID' => $row1['STAFF_ID'], 'PHARMA_ID' => $row1['PHARMA_ID']])->insert($fields);
                    $response = ['Success' => true, 'Message' => 'Staff leave approved', 'code' => 200];
                } else {
                    $response = ['Success' => false, 'Message' => 'Missing STAFF_ID or PHARMA_ID', 'code' => 400];
                }
            } catch (\Exception $e) {
                $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
            }
        }
        return $response;
    }

    function vustaffcl(Request $req)
    {
        $response = ['Success' => false, 'Message' => 'An error occurred'];

        if ($req->isMethod('post')) {
            $input = $req->json()->all();
            $data = DB::table('user_staff')
                ->leftJoin('staff_cl', 'user_staff.STAFF_ID', '=', 'staff_cl.STAFF_ID')
                ->select('user_staff.STAFF_ID', 'user_staff.STAFF_NAME', 'user_staff.HD', 'user_staff.PHARMA_ID', 'staff_cl.L_DT', 'staff_cl.L_TYPE', 'staff_cl.APPROVE')
                ->where([
                    'user_staff.STAFF_ID' => $input['STAFF_ID'],
                    'user_staff.PHARMA_ID' => $input['PHARMA_ID']
                ])
                ->get();

            if ($data->isEmpty()) {
                $response = ['Success' => false, 'Message' => 'User not found'];
            } else {
                $holidayData = json_decode($data[0]->HD, true);

                $leaves = [];
                $leaveTypesCount = [];
                foreach ($data as $row) {
                    $leaves[] = [
                        "LEAVE_DT" => $row->L_DT,
                        "REMARKS" => $row->L_TYPE
                    ];
                    if (array_key_exists($row->L_TYPE, $leaveTypesCount)) {
                        $leaveTypesCount[$row->L_TYPE]++;
                    } else {
                        $leaveTypesCount[$row->L_TYPE] = 1;
                    }
                }

                $data1 = [
                    "STAFF_ID" => $data[0]->STAFF_ID,
                    "HOLIDAY" => $holidayData,
                    "LEAVE" => $leaves,
                    "LEAVE_COUNTS" => $leaveTypesCount
                ];

                $response = ['Success' => true, 'data' => $data1];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }

        return $response;
    }

    function phopdadmin(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $data = collect();

            $bnr['slider'] = DB::table('promo_banner')
                ->select('PROMO_ID as SLIDER_ID', 'PROMO_NAME as SLIDER_NAME', 'PROMO_URL as SLIDER_URL')
                ->where(['STATUS' => 'Active', 'PROMO_TYPE' => 'Slider'])
                ->orderby('PROMO_SL')->get();

            $data = $data->merge($bnr);
            $data = $data->merge($this->getSpecialist($pharmaId));
            $data = $data->merge($this->getTodayDoctor($pharmaId));
            $data = $data->merge($this->getSymptoms($pharmaId));
            $data = $data->merge($this->getTotalDoctor($pharmaId));

            if ($data == null) {
                $response = ['Success' => false, 'Message' => 'Record not found', 'code' => 200];
            } else {
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }
    function phopd(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $data = collect();

            // $bnr['slider'] = DB::table('promo_banner')
            //     ->select('PROMO_ID as SLIDER_ID', 'PROMO_NAME as SLIDER_NAME', 'PROMO_URL as SLIDER_URL')
            //     ->where(['PHARMA_ID' => $pharmaId, 'STATUS' => 'Active', 'DASH_SECTION_ID' => 'DA'])
            //     ->orderby('PROMO_SL')->get();
            $bnr['slider'] = DB::table('promo_banner')
                ->select('PROMO_ID as SLIDER_ID', 'PROMO_NAME as SLIDER_NAME', 'PROMO_URL as SLIDER_URL')
                ->where(['STATUS' => 'Active', 'PROMO_TYPE' => 'Slider'])
                ->orderby('PROMO_SL')->get();

            $data = $data->merge($bnr);
            $data = $data->merge($this->getSpecialists($pharmaId));
            $data = $data->merge($this->getTodayDoctor($pharmaId));
            $data = $data->merge($this->getSymptoms($pharmaId));
            $data = $data->merge($this->getTotalDoctor($pharmaId));

            if ($data == null) {
                $response = ['Success' => false, 'Message' => 'Record not found', 'code' => 200];
            } else {
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    private function getSpecialists($pharmaId)
    {
        $data = [];
        $data['specialist'] = DB::table('dis_catg')
            ->join('dr_availablity', 'dis_catg.DIS_ID', '=', 'dr_availablity.DIS_ID')
            ->select(
                'dis_catg.DIS_ID',
                'dis_catg.DIS_CATEGORY',
                'dis_catg.SPECIALIST',
                'dis_catg.SPECIALITY',
                'dis_catg.DISIMG1 AS PHOTO_URL1',
                'dis_catg.DISIMG2 AS PHOTO_URL2',
                'dis_catg.DISIMG3 AS PHOTO_URL3',
                'dis_catg.DISIMG4 AS PHOTO_URL4',
                'dis_catg.DISIMG5 AS PHOTO_URL5',
                'dis_catg.DISIMG6 AS PHOTO_URL6',
                'dis_catg.DISIMG7 AS PHOTO_URL7',
                'dis_catg.DISIMG8 AS PHOTO_URL8',
                'dis_catg.DISIMG9 AS PHOTO_URL9',
                'dis_catg.DISIMG10 AS PHOTO_URL10',
                DB::raw('count(*) as TOTAL')
            )
            ->where('dr_availablity.PHARMA_ID', '=', $pharmaId)
            ->groupBy(
                'dis_catg.DIS_ID',
                'dis_catg.DIS_CATEGORY',
                'dis_catg.SPECIALIST',
                'dis_catg.SPECIALITY',
                'dis_catg.DISIMG1',
                'dis_catg.DISIMG2',
                'dis_catg.DISIMG3',
                'dis_catg.DISIMG4',
                'dis_catg.DISIMG5',
                'dis_catg.DISIMG6',
                'dis_catg.DISIMG7',
                'dis_catg.DISIMG8',
                'dis_catg.DISIMG9',
                'dis_catg.DISIMG10'
            )
            ->orderby('dis_catg.DIS_SL')
            ->get();
        return $data;
    }


    private function getSpecialist($pharmaId)
    {
        $data = [];
        $data['specialist'] = DB::table('dis_catg')
            ->join('dr_availablity', 'dis_catg.DIS_ID', '=', 'dr_availablity.DIS_ID')
            ->select(
                'dis_catg.DIS_ID',
                'dis_catg.DIS_CATEGORY',
                'dis_catg.SPECIALIST',
                'dis_catg.SPECIALITY',
                'dis_catg.DISIMG1 AS PHOTO_URL',
                DB::raw('count(*) as TOTAL')
            )
            ->where('dr_availablity.PHARMA_ID', '=', $pharmaId)
            ->groupBy(
                'dis_catg.DIS_ID',
                'dis_catg.DIS_CATEGORY',
                'dis_catg.SPECIALIST',
                'dis_catg.SPECIALITY',
                'dis_catg.DISIMG1',
            )
            ->orderby('dis_catg.DIS_SL')
            ->get();
        return $data;
    }

    private function getTodayDoctor($pharmaId)
    {

        date_default_timezone_set('Asia/Kolkata');
        $currentDate = Carbon::now();
        $weekNumber = $currentDate->weekOfMonth;
        $day1 = $currentDate->format('l');
        $cdy = date('d');
        $cdt = date('Ymd');
        $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));
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
            ->where(['dr_availablity.PHARMA_ID' => $pharmaId])
            ->where('pharmacy.STATUS', '=', 'Active')
            ->where(['dr_availablity.SCH_DAY' => $day1])
            ->where('WEEK', 'like', '%' . $weekNumber . '%')
            // ->orWhere('dr_availablity.SCH_DT', $cdy)
            ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
            ->orderby('dr_availablity.CHK_IN_TIME')

            // ->orderbyraw('KM')
            ->get();

        $ldr = [];
        foreach ($data1 as $row) {

            if ($currentTime->greaterThan($row->CHK_OUT_TIME)) {
                $drstatus = "OUT";
            } else {
                $drstatus = $row->CHK_IN_STATUS;
            }
            $SCH_DT = $this->getSchDt($row->DR_ID, $row->PHARMA_ID);
            $ldr['today_doctor'][] = [

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
                "SCH_DT" => $SCH_DT,
                "CHK_IN_TIME" => $row->CHK_IN_TIME,
                "CHK_OUT_TIME" => $row->CHK_OUT_TIME,
                "DR_STATUS" => $drstatus,
                "DR_ARRIVE" => $row->DR_ARRIVE,
                "CHEMBER_NO" => $row->CHEMBER_NO,
            ];
        }
        usort($ldr['today_doctor'], function ($item1, $item2) {
            $order = [
                'IN' => 1,
                'TIMELY' => 2,
                'DELAY' => 3,
                'CANCELLED' => 4,
                'OUT' => 5,
                'LEAVE' => 6
            ];
            $status1 = $order[$item1['DR_STATUS']] ?? 999;
            $status2 = $order[$item2['DR_STATUS']] ?? 999;
            if ($status1 == $status2) {
                return 0;
            }
            return ($status1 < $status2) ? -1 : 1;
        });
        $filtered_ldr = array_filter($ldr['today_doctor'], function ($doctor) {
            return $doctor['DR_STATUS'] === "IN" || $doctor['DR_STATUS'] === "TIMELY" || $doctor['DR_STATUS'] === "DELAY" || $doctor['DR_STATUS'] === "OUT" || $doctor['DR_STATUS'] === "CANCELLED" || $doctor['DR_STATUS'] === "LEAVE";
        });
        $ldr['today_doctor'] = array_values($filtered_ldr);
        return $ldr;
    }

    private function getSymptoms($pharmaId)
    {
        $data = [];
        $data['symptoms'] = DB::table('symptoms')
            ->select(
                'SYM_ID',
                'DIS_ID',
                'SYM_NAME',
                'DIS_CATEGORY',
                'SYMIMG1 AS PHOTO_URL',
                'SYMIMG6 AS PHOTO1_URL',
                'DESCRIPTION',
                'STATUS',
                'SYM_SL',
                'SYM_TYPE'
            )
            ->where('STATUS', 'Active')
            ->orderby('SYM_SL')
            ->get();
        return $data;
    }

    private function getTotalDoctor($pharmaId)
    {
        // $doctors = DB::table('drprofile')
        //     ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
        //     ->where('dr_availablity.PHARMA_ID', '=', $pharmaId)
        //     ->where('drprofile.APPROVE', 'true')
        //     ->distinct()
        //     ->select([
        //         'drprofile.DR_ID',
        //         'drprofile.DR_NAME',
        //         'drprofile.DR_MOBILE',
        //         'drprofile.SEX',
        //         'drprofile.DESIGNATION',
        //         'drprofile.QUALIFICATION',
        //         'drprofile.D_CATG',
        //         'drprofile.EXPERIENCE',
        //         'drprofile.LANGUAGE',
        //         'drprofile.PHOTO_URL',
        //         'drprofile.REGN_NO',
        //         'drprofile.UID_NMC',
        //         'drprofile.COUNCIL AS NMC_NAME',
        //         'dr_availablity.PHARMA_ID',
        //         'dr_availablity.DR_FEES',
        //         'dr_availablity.CHK_IN_STATUS',
        //         'dr_availablity.CHEMBER_NO',
        //         'dr_availablity.DR_ARRIVE',
        //     ])
        //     ->get();

        $distinctDoctors = DB::table('dr_availablity')
            ->select('DR_ID', 'DR_FEES', 'POSITION', 'PHARMA_ID')
            ->distinct()
            ->where(['PHARMA_ID' => $pharmaId])
            ->orderByRaw('CASE WHEN POSITION =0 THEN 1 ELSE 0 END, POSITION ASC');
        // ->limit(6);

        $doctors = DB::table('drprofile')
            ->joinSub($distinctDoctors, 'distinct_doctors', function ($join) {
                $join->on('drprofile.DR_ID', '=', 'distinct_doctors.DR_ID');
            })
            ->select([
                'drprofile.DR_ID',
                'drprofile.DR_NAME',
                'drprofile.DR_MOBILE',
                'drprofile.SEX',
                'drprofile.DESIGNATION',
                'drprofile.QUALIFICATION',
                'drprofile.D_CATG',
                'drprofile.EXPERIENCE',
                'drprofile.LANGUAGE',
                'drprofile.PHOTO_URL',
                'drprofile.REGN_NO',
                'drprofile.UID_NMC',
                'drprofile.COUNCIL AS NMC_NAME',
                'distinct_doctors.PHARMA_ID',
                'distinct_doctors.DR_FEES',

            ])
            ->limit(6)
            ->get();

        $data['total_doctor'] = $doctors->map(function ($doctor) {
            // $SCH_DT = $this->getSchDt($doctor->DR_ID, $doctor->PHARMA_ID);
            return [
                "DR_ID" => $doctor->DR_ID,
                "DR_NAME" => $doctor->DR_NAME,
                "DR_MOBILE" => $doctor->DR_MOBILE,
                "SEX" => $doctor->SEX,
                "DESIGNATION" => $doctor->DESIGNATION,
                "QUALIFICATION" => $doctor->QUALIFICATION,
                "D_CATG" => $doctor->D_CATG,
                "EXPERIENCE" => $doctor->EXPERIENCE,
                "LANGUAGE" => $doctor->LANGUAGE,
                "REGN_NO" => $doctor->REGN_NO,
                "UID_NMC" => $doctor->UID_NMC,
                "NMC_NAME" => $doctor->NMC_NAME,
                "DR_PHOTO" => $doctor->PHOTO_URL,
                "DR_FEES" => $doctor->DR_FEES,
                // "SCH_DT" => $SCH_DT,
                // "DR_STATUS" => $doctor->CHK_IN_STATUS,
                // "DR_ARRIVE" => $doctor->DR_ARRIVE,
                // "CHEMBER_NO" => $doctor->CHEMBER_NO,

            ];
        });

        return $data;
    }

    private function getSchDt($drId, $P_ID)
    {
        $sixRows = [];
        $cym = date('Ymd');
        $dravail = DB::table('dr_availablity')->where(['DR_ID' => $drId, 'PHARMA_ID' => $P_ID])->get();
        $totapp = DB::table('appointment')->where(['DR_ID' => $drId, 'PHARMA_ID' => $P_ID])->get();
        foreach ($dravail as $row) {
            if (is_numeric($row->SCH_DAY)) {
                $currentYear = date("Y");
                $startDate = new DateTime("{$currentYear}-$row->START_MONTH-$row->SCH_DAY");

                for ($i = 0; $i < 12; $i++) {
                    $dates = [];
                    $dates = $startDate->format('Ymd');
                    $schday = $startDate->format('l');

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
                        // $slots = $this->getDtSlot($P_ID, $drId, $row->ID);
                        $data[] = [
                            "ID" => $row->ID,
                            "SCH_DT" => $dates,
                            "SCH_DAY" => $schday,
                            "SLOT" => $row->SLOT,
                            "SCHEDULE" => $row->DESCRIPTION,
                            "FROM" => $row->CHK_IN_TIME,
                            "TO" => $row->CHK_OUT_TIME,
                            "BOOK_START_TIME" => $row->BOOK_ST_TM,
                            "CHK_IN_STS" => $row->CHK_IN_STATUS,
                            "MAX_BOOK" => $row->MAX_BOOK,
                            "AVAILABLE" => $row->MAX_BOOK - $totappct,
                            "STATUS" => $book_sts,
                            "BOOK_START_DT" => $dates,
                            "MAX_BOOK" => $row->MAX_BOOK,
                            // "SLOTS" => [$slots]
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
                            // $slots = $this->getDtSlot($P_ID, $drId, $row->ID);
                            $data[] = [
                                "ID" => $row->ID,
                                "SCH_DT" => $dates,
                                "SCH_DAY" => $row->SCH_DAY,
                                "SLOT" => $row->SLOT,
                                "SCHEDULE" => $row->DESCRIPTION,
                                "FROM" => $row->CHK_IN_TIME,
                                "TO" => $row->CHK_OUT_TIME,
                                "BOOK_START_TIME" => $row->BOOK_ST_TM,
                                "CHK_IN_STS" => $row->CHK_IN_STATUS,
                                "MAX_BOOK" => $row->MAX_BOOK,
                                "AVAILABLE" => $row->MAX_BOOK - $totappct,
                                "STATUS" => $book_sts,
                                "BOOK_START_DT" => $formattedBookingDate,
                                "MAX_BOOK" => $row->MAX_BOOK,
                                // "SLOTS" => [$slots]
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
                $data[0]['CHK_IN_STS'] = "OUT";
                $data[0]['STATUS'] = "Closed";
            }
        }
        $collection = collect($data);
        $firstAvailable = $collection->firstWhere('STATUS', 'Available');

        if ($firstAvailable) {
            $firstAvailableIndex = $collection->search($firstAvailable);
            $sixRows = array_slice($data, $firstAvailableIndex, 6);
        }
        return $sixRows;
    }

    function admcatgclinicdr(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $did = $input['DIS_ID'];
            $data = collect();


            $data = $data->merge($this->getCatgDrDt($pharmaId, $did));


            if ($data == null) {
                $response = ['Success' => false, 'Message' => 'Record not found', 'code' => 200];
            } else {
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    private function getCatgDrDt($pharmaId, $did)
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
                'dr_availablity.DR_FEES'
                // DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                //     * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                //     * SIN(RADIANS('$latt'))))),0) as KM"),
            )
            ->distinct('DR_ID')
            ->where(['dr_availablity.PHARMA_ID' => $pharmaId, 'dr_availablity.DIS_ID' => $did])
            ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
            ->where('pharmacy.STATUS', '=', 'Active')
            ->where('drprofile.APPROVE', 'true')
            ->get();

        $DRSCH = ['Doctors' => []];

        foreach ($totdr as $row1) {
            $dravail = DB::table('dr_availablity')
                ->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pharmaId])
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
                                    $dr_status = $row->CHK_IN_STATUS3 ?? $row->CHK_IN_STATUS2 ?? $row->CHK_IN_STATUS1 ?? $row->CHK_IN_STATUS;
                                }
                            } else {
                                $dr_status = $row->CHK_IN_STATUS3 ?? $row->CHK_IN_STATUS2 ?? $row->CHK_IN_STATUS1 ?? $row->CHK_IN_STATUS;
                            }

                            $data[] = [
                                "SCH_DT" => $dates,
                                "DR_STATUS" => $dr_status,
                                "ABS_FDT" => $row->ABS_FDT,
                                "ABS_TDT" => $row->ABS_TDT,
                                "DR_ARRIVE" => $row->DR_ARRIVE,
                                "CHK_IN_TIME" => $row->CHK_IN_TIME,
                                "CHK_IN_TIME1" => $row->CHK_IN_TIME1,
                                "CHK_IN_TIME2" => $row->CHK_IN_TIME2,
                                "CHK_IN_TIME3" => $row->CHK_IN_TIME3,
                                "TO" => $row->CHK_OUT_TIME,
                                "TO1" => $row->CHK_OUT_TIME1,
                                "TO2" => $row->CHK_OUT_TIME2,
                                "TO3" => $row->CHK_OUT_TIME2,
                                "CHEMBER_NO" => $row->CHEMBER_NO,
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
                                $totalMaxBook = collect([$row->MAX_BOOK, $row->MAX_BOOK1, $row->MAX_BOOK2, $row->MAX_BOOK3])->filter()->sum();
                                if ($totalMaxBook - $totappct == 0) {
                                    $book_sts = "Closed";
                                } else {
                                    $book_sts = "Available";
                                }
                                if ($row->ABS_TDT != null) {
                                    if ($row->ABS_TDT < $dates) {
                                        $dr_status = "TIMELY";
                                    } else {
                                        $dr_status = $row->CHK_IN_STATUS3 ?? $row->CHK_IN_STATUS2 ?? $row->CHK_IN_STATUS1 ?? $row->CHK_IN_STATUS;
                                    }
                                } else {
                                    $dr_status = $row->CHK_IN_STATUS3 ?? $row->CHK_IN_STATUS2 ?? $row->CHK_IN_STATUS1 ?? $row->CHK_IN_STATUS;
                                }

                                $data[] = [
                                    "SCH_DT" => $dates,
                                    "DR_STATUS" => $dr_status,
                                    "ABS_FDT" => $row->ABS_FDT,
                                    "ABS_TDT" => $row->ABS_TDT,
                                    "DR_ARRIVE" => $row->DR_ARRIVE,
                                    "CHK_IN_TIME" => $row->CHK_IN_TIME,
                                    "CHK_IN_TIME1" => $row->CHK_IN_TIME1,
                                    "CHK_IN_TIME2" => $row->CHK_IN_TIME2,
                                    "CHK_IN_TIME3" => $row->CHK_IN_TIME3,
                                    "TO" => $row->CHK_OUT_TIME,
                                    "TO1" => $row->CHK_OUT_TIME1,
                                    "TO2" => $row->CHK_OUT_TIME2,
                                    "TO3" => $row->CHK_OUT_TIME2,
                                    "CHEMBER_NO" => $row->CHEMBER_NO,
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

                $firstRowTOTime = $data[0]['TO'] ? Carbon::createFromFormat('h:i A', $data[0]['TO']) : null;
                $firstRowTOTime1 = $data[0]['TO1'] ? Carbon::createFromFormat('h:i A', $data[0]['TO1']) : null;
                $firstRowTOTime2 = $data[0]['TO2'] ? Carbon::createFromFormat('h:i A', $data[0]['TO2']) : null;
                $firstRowTOTime3 = $data[0]['TO3'] ? Carbon::createFromFormat('h:i A', $data[0]['TO3']) : null;

                $allTimesPassed = "true";

                if ($firstRowTOTime && $currentTime->lessThanOrEqualTo($firstRowTOTime)) {
                    $allTimesPassed = "false";
                }
                if ($firstRowTOTime1 && $currentTime->lessThanOrEqualTo($firstRowTOTime1)) {
                    $allTimesPassed = "false";
                }
                if ($firstRowTOTime2 && $currentTime->lessThanOrEqualTo($firstRowTOTime2)) {
                    $allTimesPassed = "false";
                }
                if ($firstRowTOTime3 && $currentTime->lessThanOrEqualTo($firstRowTOTime3)) {
                    $allTimesPassed = "false";
                }

                if ($allTimesPassed === "true") {
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
                    "DR_FEES" => $row1->DR_FEES,
                    "SEX" => $row1->SEX,
                    "DESIGNATION" => $row1->DESIGNATION,
                    "QUALIFICATION" => $row1->QUALIFICATION,
                    "UID_NMC" => $row1->UID_NMC,
                    "REGN_NO" => $row1->REGN_NO,
                    "D_CATG" => $row1->D_CATG,
                    "EXPERIENCE" => $row1->EXPERIENCE,
                    "LANGUAGE" => $row1->LANGUAGE,
                    "DR_PHOTO" => $row1->PHOTO_URL,
                    "AVAILABLE_DT" => $sixRows[0]['SCH_DT'],
                    "SLOT_STATUS" => $sixRows[0]['SLOT_STATUS'],
                    "DR_STATUS" => $sixRows[0]['DR_STATUS'],
                    "CHK_IN_TIME" => $sixRows[0]['CHK_IN_TIME'],
                    "CHK_IN_TIME1" => $sixRows[0]['CHK_IN_TIME1'],
                    "CHK_IN_TIME2" => $sixRows[0]['CHK_IN_TIME2'],
                    "CHK_IN_TIME3" => $sixRows[0]['CHK_IN_TIME3'],
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

    function viewappointment(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $headers = apache_request_headers();
            $input = $req->json()->all();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {

            if (isset($input['APPNT_DT']) && isset($input['APPNT_ID']) && isset($input['APPNT_FROM'])) {

                $response = array();
                $data = array();

                $totapp = DB::table('appointment')
                    ->select()
                    ->where(['APPNT_DT' => $input['APPNT_DT'], 'APPNT_ID' => $input['APPNT_ID'], 'APPNT_FROM' => $input['APPNT_FROM']])
                    ->where('STATUS', '<>', 'Cancelled')
                    ->count();

                $data[] = array(
                    "TOT_APPNT" => $totapp
                );
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid Parameter.', 'code' => 422];
            }
            // } else {
            //     $response = ['Success' => false, 'Message' => 'You are not Authorized.', 'code' => 401];
            // }
        } else {
            $response = ['Success' => false, 'Messege' => 'Method not allowed', 'code' => 405];
        }
        return $response;
    }

    function srchMob(Request $req)
    {
        $response = ['Success' => false, 'Message' => 'An error occurred'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();

            $data = DB::table('user_family')
                ->select('ID', 'FAMILY_ID', 'NAME', 'LOCATION', 'MOBILE', 'ALT_MOB', 'M_STS', 'SEX', 'DOB as AGE')
                ->where('FAMILY_ID', $input['MOBILE'])
                ->orWhere('MOBILE', $input['MOBILE'])
                ->get();

            if ($data->isEmpty()) {
                $response = ['Success' => false, 'Message' => 'User not found'];
            } else {
                $response = ['Success' => true, 'data' => $data];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }

        return $response;
    }

    function admbooking(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // session_start();
            // $headers = apache_request_headers();

            // if (isset($headers['Authorization']) && $headers['Authorization'] == $_SESSION['TOKEN']) {
            date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();
            $response = array();
            $data = array();

            if (isset($input['FAMILY_ID'])) {
                $commonPatientId = $input['PATIENT_ID'] ?? null;
                if (empty($commonPatientId)) {
                    $userFamilyData = [
                        "FAMILY_ID" => $input['FAMILY_ID'],
                        "NAME" => $input['PATIENT_NAME'],
                        "LOCATION" => $input['ADDRESS'],
                        "MOBILE" => $input['MOBILE'],
                        "ALT_MOB" => $input['ALT_MOB'] ?? NULL,
                        "M_STS" => $input['M_STS'] ?? NULL,
                        "SEX" => $input['SEX'] ?? NULL,
                        "DOB" => $input['AGE'] ?? NULL,
                        "RELATION" => 'Self'
                    ];
                    $insertedUserFamilyId = DB::table('user_family')->insertGetId($userFamilyData);
                }
                $patientid = $commonPatientId ?? $insertedUserFamilyId;

                $APNT_TOKEN = strtoupper(substr(md5($input['APNT_DT'] . $input['APNT_ID'] . $input['PATIENT_ID']), 0, 10));
                $fields = [
                    "FAMILY_ID" => $input['FAMILY_ID'],
                    "BOOK_BY_ID" => $input['FAMILY_ID'],
                    "PATIENT_ID" => $patientid,
                    "PATIENT_NAME" => $input['PATIENT_NAME'],
                    "BOOK_BY_NAME" => $input['PATIENT_NAME'],
                    "MOBILE" => $input['MOBILE'],
                    "DR_ID" => $input['DR_ID'],
                    "BOOKING_DT" => date('Ymd'),
                    "BOOKING_TM" => date('h:i A'),
                    "BOOKING_TYPE" => $input['BOOKING_TYPE'],
                    "APPNT_ID" => $input['APNT_ID'],
                    "APPNT_DT" => $input['APNT_DT'],
                    "APPNT_FROM" => $input['FROM'],
                    "APPNT_TO" => $input['TO'],
                    "DR_FEES" => $input['DR_FEES'],
                    "DR_STATUS" => $input['DR_STATUS'],
                    "CHK_IN_TIME" => $input['CHK_IN_TIME'] ?? null,
                    "CHK_OUT_TIME" => $input['CHK_OUT_TIME'] ?? null,
                    "DR_ARRIVE" => $input['DR_ARRIVE'] ?? null,
                    "DR_DELAY" => $input['DR_DELAY'] ?? null,
                    "CHEMBER_NO" => $input['CHEMBER_NO'] ?? null,
                    "PHARMA_ID" => $input['PHARMA_ID'],
                    "APPNT_TOKEN" => $APNT_TOKEN,
                    "STATUS" => 'Upcoming'
                ];

                DB::beginTransaction();
                try {
                    DB::table('appointment')->insert($fields);
                    $data = array(
                        "APPNT_TOKEN" => $APNT_TOKEN,
                        "Family_ID" => $input['FAMILY_ID'],
                        "Patient_Name" => $input['PATIENT_NAME'],
                        "Mobile" => $input['MOBILE'],
                        "Dr_Name:" => $input['DR_NAME'],
                        "Booking_Date" => date('Ymd'),
                        "Appointment_Date" => $input['APNT_DT'],
                        "Booked_By" => $input['BOOK_BY_NAME'],
                        "Clinic_Name" => $input['PHARMA_NAME']
                    );
                    $response = ['Success' => true, 'Message' => 'Booking successfully.', 'data' => $data, 'code' => 200];
                    DB::commit();
                } catch (\Throwable $th) {
                    $chk = DB::table('appointment')->select('APPNT_TOKEN', 'STATUS')->where('APPNT_TOKEN', $APNT_TOKEN)->first();
                    if (!$chk) {
                        return $th;
                        $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 404];
                    } elseif ($chk->STATUS != 'Cancelled') {
                        DB::rollBack();
                        $data = [
                            "APPNT_TOKEN" => $APNT_TOKEN,
                            "Patient_Name" => $input['PATIENT_NAME'],
                        ];
                        $response = ['Success' => false, 'data' => $data, 'Message' => 'Patient already booked', 'code' => 200];
                    } else {
                        try {
                            DB::table('appointment')->where('APPNT_TOKEN', $APNT_TOKEN)->update($fields);
                            $response = ['Success' => true, 'Message' => 'Appointment updated successfully', 'code' => 200];
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            $response = ['Success' => false, 'Message' => 'Error updating appointment', 'code' => 500];
                        }
                    }
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid Parameter', 'code' => 422];
            }
            // } else {
            //     $response = ['Success' => false, 'Message' => 'You are not Authorized,', 'code' => 401];
            // }
        } else {
            $response = ['Success' => false, 'Message' => 'Method not allowed.', 'code' => 405];
        }
        return $response;
    }

    function bookinginvoice(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $headers = apache_request_headers();
            // session_start();
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
            if (isset($input['TOKEN'])) {
                $f_id = $input['TOKEN'];
                $data = DB::table('appointment')
                    ->join('drprofile', 'appointment.DR_ID', '=', 'drprofile.DR_ID')
                    ->join('pharmacy', 'appointment.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    ->join('dr_availablity', 'appointment.APPNT_ID', '=', 'dr_availablity.ID')
                    ->select(
                        'appointment.BOOKING_ID',
                        'appointment.FAMILY_ID',
                        'appointment.PATIENT_NAME as Patient_Name',
                        'appointment.MOBILE',
                        'appointment.BOOKING_DT',
                        'appointment.BOOKING_TM',
                        'appointment.BOOKING_TYPE',
                        'appointment.APPNT_DT',
                        'appointment.APPNT_FROM',
                        'appointment.APPNT_TO',
                        'appointment.DR_FEES',
                        'appointment.APPNT_DT AS AVAILABLE_DT',
                        'appointment.APPNT_TOKEN',
                        'appointment.APPNT_SLOT',
                        'dr_availablity.SCH_DAY AS APPNT_DAY',
                        'dr_availablity.CHK_IN_TIME',
                        'dr_availablity.CHK_OUT_TIME',
                        'dr_availablity.CHK_IN_STATUS AS AVAILABLE',
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
                        'pharmacy.PHARMA_ID',
                        'pharmacy.ITEM_NAME as PHARMA_NAME',
                        'pharmacy.ADDRESS',
                        'pharmacy.PIN',
                        'pharmacy.CITY',
                        'pharmacy.DIST',
                        'pharmacy.STATE',
                        'pharmacy.CLINIC_MOBILE',
                        'pharmacy.EMAIL',
                        'pharmacy.LATITUDE',
                        'pharmacy.LONGITUDE',
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        'appointment.PHOTO_URL AS BOOK_INVOICE',
                        'appointment.STATUS',
                        'appointment.VISIT as REASON'
                    )
                    ->where('appointment.APPNT_TOKEN', '=', $f_id)
                    // ->orderby('appointment.BOOKING_ID')
                    ->get();

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

    function cltestdash(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['PHARMA_ID'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $pid = $input['PHARMA_ID'];
                $date = Carbon::now();
                $weekNumber = $date->weekOfMonth;
                $day1 = date('l');

                $promo_bnr = DB::table('promo_banner')->where('STATUS', 'Active')->get();
                $dash = DB::table('dashboard')->where('CATEGORY', 'like', '%' . 'D' . '%')->where('STATUS', 'Active')->get();

                //SECTION-A #### SLIDER
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) use ($pid) {
                    return $item->DASH_SECTION_ID === 'DA' && $item->PHARMA_ID == $pid;
                });
                $A["Slider"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "SLIDER_ID" => $item->PROMO_ID,
                        "SLIDER_NAME" => $item->PROMO_NAME,
                        "SLIDER_URL" => $item->PROMO_URL,
                    ];
                })->values()->all();

                //SECTION-#### SINGLE TEST
                $TST_DTL = DB::table('clinic_testdata')
                    ->where(['PHARMA_ID' => $pid])
                    ->orderBy('TEST_SL')
                    ->take(100)->get()->toArray();
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'TS';
                });
                $STB["Test_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                $TST["Most_Popular_Test"] = array_values($TST_DTL + $STB);


                //SECTION-Z #### DASHBOARD
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'Z';
                });
                $DASH_Z["Dashboard"] = $fltr_dash->map(function ($item) {
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

                // SECTION-S #### SYMPTOMATIC TEST
                $data1 = DB::table('dashboard')
                    ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard.DASH_ID')
                    ->join('clinic_testdata', 'sym_organ_test.TEST_ID', '=', 'clinic_testdata.TEST_ID')
                    ->select(
                        // 'sym_organ_test.TEST_ID',
                        // 'sym_organ_test.TEST_NAME',
                        // // 'dashboard.DASH_ID',
                        // // 'dashboard.DASH_NAME',
                        'dashboard.PHOTO_URL',
                        'dashboard.POSITION',
                        'sym_organ_test.DASH_ID',
                        'sym_organ_test.DASH_NAME',
                        'clinic_testdata.*',
                    )
                    ->where(['dashboard.DASH_SECTION_ID' => 'S', 'dashboard.STATUS' => 'Active'])
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
                            "COST" => $item->COST,
                            "DEPARTMENT" => $item->DEPARTMENT,
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

                // RETURN $S_DTL;
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'TS';
                });
                $S_BNR["Symtomatic_Test_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                $S["Symptomatic_Test"] = array_values($S_DTL + $S_BNR);

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
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'C';
                });
                $C_BNR["Profile_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                $C["Profile_Test"] = array_values($C_DTL + $C_BNR);

                //SECTION-####ADVERTISEMENT BANNER
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'CL';
                });
                $DASH_BNR["Advertisement_Banner"] = $fltr_promo_bnr->map(function ($item) {
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

                //SECTION-B #### FAMILY CARE PACKAGES
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
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'B';
                });
                $B_BNR["Family_Care_Package_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                $B["Family_Care_Package"] = array_values($B_DTL + $B_BNR);

                //SECTION-AB #### HOW IT WORK
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'AB';
                });
                $AB["How_It_Work"] = $fltr_dash->map(function ($item) {
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

                $data = $A + $DASH_Z + $TST + $S + $C + $DASH_BNR + $B + $AB;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function drcancel(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ]);
        }
        date_default_timezone_set('Asia/Kolkata');
        $input = $req->json()->all();
        $tdt = carbon::now()->format('Ymd');
        $chk_in_time = $input['FROM_TIME'];

        $fields1 = [
            "DR_STATUS" => 'CANCELLED',
        ];
        $chkInTimes = ['CHK_IN_TIME', 'CHK_IN_TIME1', 'CHK_IN_TIME2', 'CHK_IN_TIME3'];
        $chkInStatusFields = ['CHK_IN_STATUS', 'CHK_IN_STATUS1', 'CHK_IN_STATUS2', 'CHK_IN_STATUS3'];
        $drArriveFields = ['DR_ARRIVE', 'DR_ARRIVE1', 'DR_ARRIVE2', 'DR_ARRIVE3'];
        $drLeftFields = ['DR_LEFT', 'DR_LEFT1', 'DR_LEFT2', 'DR_LEFT3'];
        $chemberFields = ['CHEMBER_NO', 'CHEMBER_NO1', 'CHEMBER_NO2', 'CHEMBER_NO3'];
        $chkOutTimes = ['CHK_OUT_TIME', 'CHK_OUT_TIME1', 'CHK_OUT_TIME2', 'CHK_OUT_TIME3'];
        $maxBookFields = ['MAX_BOOK', 'MAX_BOOK1', 'MAX_BOOK2', 'MAX_BOOK3'];

        $fields_d = [];
        $selectedStatusField = 'CHK_IN_STATUS';
        $selectedChemberField = 'CHEMBER_NO';
        $selectedChkInTime = 'CHK_IN_TIME';
        $selectedChkOutTime = 'CHK_OUT_TIME';
        $selectedMaxBookField = 'MAX_BOOK';
        $selectedLeftField = 'DR_LEFT';
        $selectedArriveField = 'DR_ARRIVE';

        foreach ($chkInTimes as $index => $timeField) {
            $statusField = $chkInStatusFields[$index];
            $arriveField = $drArriveFields[$index];
            $leftField = $drLeftFields[$index];
            $chemberField = $chemberFields[$index];
            $chkOutTimeField = $chkOutTimes[$index];
            $maxBookField = $maxBookFields[$index];
            $time = DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->value($timeField);

            if ($chk_in_time === $time) {
                $fields[$statusField] = 'CANCELLED';
                $fields[$chemberField] = null;
                $fields[$leftField] = null;

                $foundNextAvailable = false;
                for ($nextIndex = $index + 1; $nextIndex < count($chkInTimes); $nextIndex++) {
                    $nextTimeField = $chkInTimes[$nextIndex];
                    $nextStatusField = $chkInStatusFields[$nextIndex];
                    $nextArriveField = $drArriveFields[$nextIndex];
                    $nextLeftField = $drLeftFields[$nextIndex];
                    $nextChemberField = $chemberFields[$nextIndex];
                    $nextChkOutTimeField = $chkOutTimes[$nextIndex];
                    $nextMaxBookField = $maxBookFields[$nextIndex];
                    $nextTime = DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->value($nextTimeField);
                    $nextStatus = DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->value($nextStatusField);

                    if (!is_null($nextTime) && $nextStatus !== 'OUT' & $nextStatus !== 'CANCELLED') {
                        $selectedArriveField = $nextArriveField;
                        $selectedLeftField = $nextLeftField;
                        $selectedStatusField = $nextStatusField;
                        $selectedChemberField = $nextChemberField;
                        $selectedChkInTime = $nextTimeField;
                        $selectedChkOutTime = $nextChkOutTimeField;
                        $selectedMaxBookField = $nextMaxBookField;
                        $foundNextAvailable = true;
                        break;
                    }
                }
                if (!$foundNextAvailable) {
                    $selectedArriveField = $arriveField;
                    $selectedLeftField = $leftField;
                    $selectedStatusField = $statusField;
                    $selectedChemberField = $chemberField;
                    $selectedChkInTime = $timeField;
                    $selectedChkOutTime = $chkOutTimeField;
                    $selectedMaxBookField = $maxBookField;
                }
            }
        }

        try {
            DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->update($fields);
            DB::table('appointment')->where(['APPNT_ID' => $input['SCH_ID'], 'APPNT_DT' => $tdt, 'APPNT_FROM' => $chk_in_time])->update($fields1);
            $todayDoctors = DB::table('pharmacy')
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
                    DB::raw("'" . Carbon::now()->format('Ymd') . "' as APPNT_DT"),
                    'dr_availablity.ID',
                    'dr_availablity.SCH_DAY',
                    'dr_availablity.START_MONTH',
                    'dr_availablity.MONTH',
                    'dr_availablity.ABS_FDT',
                    'dr_availablity.ABS_TDT',
                    'dr_availablity.' . $selectedChkInTime . ' AS CHK_IN_TIME',
                    'dr_availablity.' . $selectedChkOutTime . ' AS CHK_OUT_TIME',
                    'dr_availablity.' . $selectedStatusField . ' AS CHK_IN_STATUS',
                    'dr_availablity.' . $selectedChemberField . ' AS CHEMBER_NO',
                    'dr_availablity.' . $selectedMaxBookField . ' AS MAX_BOOK',
                    'dr_availablity.' . $selectedArriveField . ' AS DR_ARRIVE',
                    'dr_availablity.' . $selectedLeftField . ' AS DR_LEFT'

                )
                ->where(['dr_availablity.ID' => $input['SCH_ID']])
                ->get();

            foreach ($todayDoctors as $doctor) {
                $result = [
                    "PHARMA_ID" => $doctor->PHARMA_ID,
                    "PHARMA_NAME" => $doctor->ITEM_NAME,
                    "ADDRESS" => $doctor->ADDRESS,
                    "CITY" => $doctor->CITY,
                    "PIN" => $doctor->PIN,
                    "DIST" => $doctor->DIST,
                    "STATE" => $doctor->STATE,
                    "LATITUDE" => $doctor->LATITUDE,
                    "LONGITUDE" => $doctor->LONGITUDE,
                    "PHOTO_URL" => $doctor->PHOTO_URL,
                    "LOGO_URL" => $doctor->LOGO_URL,
                    "DR_ID" => $doctor->DR_ID,
                    "DR_NAME" => $doctor->DR_NAME,
                    "DR_MOBILE" => $doctor->DR_MOBILE,
                    "SEX" => $doctor->SEX,
                    "DESIGNATION" => $doctor->DESIGNATION,
                    "QUALIFICATION" => $doctor->QUALIFICATION,
                    "D_CATG" => $doctor->D_CATG,
                    "EXPERIENCE" => $doctor->EXPERIENCE,
                    "LANGUAGE" => $doctor->LANGUAGE,
                    "DR_PHOTO" => $doctor->DR_PHOTO,
                    "DR_FEES" => $doctor->DR_FEES,
                    "APPNT_ID" => $doctor->ID,
                    "APPNT_DT" => $doctor->APPNT_DT,
                    "CHK_IN_TIME" => $doctor->CHK_IN_TIME,
                    "CHK_OUT_TIME" => $doctor->CHK_OUT_TIME,
                    "DR_STATUS" => $doctor->CHK_IN_STATUS,
                    "DR_ARRIVE" => $doctor->DR_ARRIVE,
                    "DR_LEFT" => $doctor->DR_LEFT,
                    "CHEMBER_NO" => $doctor->CHEMBER_NO,
                    "MAX_BOOK" => $doctor->MAX_BOOK,
                ];
            }

            $response = [
                'Success' => true,
                'Message' => 'Doctor cancelled',
                'data' => $result,
                'code' => 200
            ];
        } catch (\Exception $e) {
            $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
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
                $dashboardPackages = DB::table('dashboard')
                    ->join('package', 'dashboard.DASH_ID', '=', 'package.LAB_PKG_ID')
                    ->leftJoin('package_details as pd', 'package.PKG_ID', '=', 'pd.PKG_ID')
                    ->where('dashboard.STATUS', 'Active')
                    ->whereIn('dashboard.DASH_SECTION_ID', ['B', 'C', 'D', 'G', 'H'])
                    ->where('package.PHARMA_ID', $pharma_id)
                    ->select(
                        'dashboard.DASH_ID',
                        'dashboard.DASH_NAME',
                        'dashboard.DASH_TYPE',
                        'dashboard.DASH_SECTION_ID',
                        'dashboard.DASH_SECTION_NAME',
                        'dashboard.POSITION',
                        'dashboard.STATUS',
                        'package.LAB_PKG_ID',
                        'package.LAB_PKG_NAME',
                        'dashboard.DASH_DESCRIPTION',
                        'package.FASTING',
                        'package.GENDER_TYPE',
                        'package.AGE_TYPE',
                        'package.HOME_COLLECT',
                        'package.KNOWN_AS',
                        'package.PKG_COST',
                        'package.PKG_DIS',
                        'dashboard.PHOTO_URL',
                        'dashboard.PRESCRIPTION',
                        'dashboard.QA1',
                        'dashboard.QA2',
                        'dashboard.QA3',
                        'dashboard.QA4',
                        'dashboard.QA5',
                        'dashboard.QA6',
                        'dashboard.REPORT_TIME',
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
                    usort($details['TestDetails'], function ($a, $b) {
                        return strcmp($b['TEST_TYPE'], $a['TEST_TYPE']);
                    });
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

    function delsch(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            $fields = [
                'SCH_DAY' => null,
                'SCH_DT' => 0,
                'WEEK' => 0,
                'MONTH' => 0,
                'START_MONTH' => 0,
                'SCH_STATUS' => 'NA',
                'BOOK_ST_DT' => 0,
                'BOOK_ST_TM' => null,
                'CHK_IN_TIME' => null,
                'CHK_OUT_TIME' => null,
                'CHK_IN_TIME1' => null,
                'CHK_OUT_TIME1' => null,
                'CHK_IN_TIME2' => null,
                'CHK_OUT_TIME2' => null,
                'CHK_IN_TIME3' => null,
                'CHK_OUT_TIME3' => null,
                'SLOT_INTVL' => 0,
                'SLOT_APPNT' => 0,
                'MAX_BOOK' => null,
                'MAX_BOOK1' => null,
                'MAX_BOOK2' => null,
                'MAX_BOOK3' => null,
                'AVAIL_STATUS' => 'Deactive',
                'SCH_ID' => strtoupper(substr(md5($input['SCH_ID'] . 'Deactive'), 0, 15)),
            ];
            try {
                DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->update($fields);
                $response = ['Success' => true, 'Message' => 'Doctor schedule delete successfully.', 'code' => 200];
            } catch (\Throwable $th) {
                $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }

    function editsch(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            $td = $input['EDIT_SCH'];

            foreach ($td as $row) {
                $fields = [
                    'SCH_DAY' => $row['SCH_DAY'],
                    'SCH_DT' => $row['SCH_DT'] ?? null,
                    'WEEK' => $row['WEEK'],
                    'MONTH' => $row['MONTH'] ?? null,
                    'START_MONTH' => $row['START_MONTH'],
                    'SCH_STATUS' => $row['SCH_STATUS'],
                    'BOOK_ST_DT' => $row['BOOK_ST_DT'],
                    'BOOK_ST_TM' => $row['BOOK_ST_TM'],
                    'CHK_IN_TIME' => $row['CHK_IN_TIME'],
                    'CHK_OUT_TIME' => $row['CHK_OUT_TIME'] ?? null,
                    'CHK_IN_TIME1' => $row['CHK_IN_TIME1'] ?? null,
                    'CHK_OUT_TIME1' => $row['CHK_OUT_TIME1'] ?? null,
                    'CHK_IN_TIME2' => $row['CHK_IN_TIME2'] ?? null,
                    'CHK_OUT_TIME2' => $row['CHK_OUT_TIME2'] ?? null,
                    'CHK_IN_TIME3' => $row['CHK_IN_TIME3'] ?? null,
                    'CHK_OUT_TIME3' => $row['CHK_OUT_TIME3'] ?? null,
                    'SLOT_INTVL' => $row['SLOT_INTVL'] ?? null,
                    'SLOT_APPNT' => $row['SLOT_APPNT'] ?? null,
                    'MAX_BOOK' => $row['MAX_BOOK'] ?? null,
                    'MAX_BOOK1' => $row['MAX_BOOK1'] ?? null,
                    'MAX_BOOK2' => $row['MAX_BOOK2'] ?? null,
                    'MAX_BOOK3' => $row['MAX_BOOK3'] ?? null,
                    'SLOT' => $row['SLOT_TYPE'] ?? null,
                ];
                for ($i = 0; $i < 4; $i++) {
                    $maxBookCol = 'MAX_BOOK' . ($i === 0 ? '' : $i);
                    $chkInTimeCol = 'CHK_IN_TIME' . ($i === 0 ? '' : $i);
                    $chkOutTimeCol = 'CHK_OUT_TIME' . ($i === 0 ? '' : $i);

                    if ($row[$maxBookCol] === null && $row[$chkInTimeCol] != null && $row[$chkOutTimeCol] != null) {
                        $chkinTime = Carbon::createFromFormat('h:i A', $row[$chkInTimeCol]);
                        $chkoutTime = Carbon::createFromFormat('h:i A', $row[$chkOutTimeCol]);
                        $minutesDiff = $chkinTime->diffInMinutes($chkoutTime, false);
                        $maxbook = ($minutesDiff / $row['SLOT_INTVL']) * $row['SLOT_APPNT'];
                        $fields[$maxBookCol] = $maxbook;
                        // break;
                    } else {
                        $fields[$maxBookCol] = $row[$maxBookCol];
                    }
                }
                try {
                    DB::table('dr_availablity')->where('ID', $row['SCH_ID'])->update($fields);
                    $response = ['Success' => true, 'Message' => 'Doctor schedule mofied successfully.', 'code' => 200];
                } catch (\Throwable $th) {
                    $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
                }
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }



    function booktest_dummy(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            $cdt = Carbon::now()->format('ymdHis');
            $input = $req->all();
            $bookTest = json_decode($input['BOOK_TEST'], true);
            $bookTest = $bookTest[0];
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
            $location = $bookTest['ADDRESS'];
            $alt_mob = $bookTest['ALT_MOB'];
            $sex = $bookTest['SEX'];
            $m_sts = $bookTest['M_STS'];
            $age = $bookTest['AGE'];
            $familyId = $bookTest['FAMILY_ID'];

            $token = strtoupper(substr(md5($commonPatientId . $cdt . $pharmaId), 0, 10));

            $patientIds = [];
            $bookid = null;
            $msg = "Test booking";
            $insertedUserFamilyId = null;

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

    function testbookinghistory_dummy(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->json()->all();
            if (isset($input['BOOK_BY_ID']) && isset($input['PHARMA_ID'])) {
                $bookedBy = $input['BOOK_BY_ID'];
                $PHID = $input['PHARMA_ID'];

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

                $bookingHistoriesArray = $bookingHistories->toArray();

                $today = date('Ymd');

                usort($bookingHistoriesArray, function ($a, $b) use ($today) {
                    if ($a->SLOT_DT == $b->SLOT_DT) {
                        $aTime = strtotime(date("H:i", strtotime($a->FROM)));
                        $bTime = strtotime(date("H:i", strtotime($b->FROM)));

                        if ($aTime == $bTime) {
                            return 0;
                        }

                        return ($aTime < $bTime) ? -1 : 1;
                    }
                    if ($a->SLOT_DT < $today && $b->SLOT_DT < $today) {
                        return $a->SLOT_DT < $b->SLOT_DT ? 1 : -1;
                    }
                    if ($a->SLOT_DT < $today) {
                        return 1;
                    }
                    if ($b->SLOT_DT < $today) {
                        return -1;
                    }

                    return $a->SLOT_DT < $b->SLOT_DT ? -1 : 1;
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
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->orderby('booktest.SLOT_DT')
                    ->get();

                $groupedData = null;
                $totalCost = 0;
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
                    $totalCost += $row->TEST_COST;
                }
                if ($groupedData) {
                    $groupedData['TOT_COST'] = $totalCost;
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

    public function vu_facility(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();

            $data1 = DB::table('facility_section')

                ->select([
                    'facility_section.DASH_SECTION_ID',
                    'facility_section.DASH_SECTION_NAME',
                    'facility_section.DS_DESCRIPTION',
                    'facility_section.DSIMG1',
                    'facility_section.DSIMG2',
                    'facility_section.DSIMG3',
                    'facility_section.DSIMG4',
                    'facility_section.DSIMG5',
                    'facility_section.DSIMG6',
                    'facility_section.DSIMG7',
                    'facility_section.DSIMG8',
                    'facility_section.DSIMG9',
                    'facility_section.DSIMG10',
                ])
                ->get();

            $groupedData = [];
            foreach ($data1 as $row) {
                $sectionId = $row->DASH_SECTION_ID;

                if (!isset($groupedData[$sectionId])) {
                    $groupedData[$sectionId] = [
                        "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                        "DESCRIPTION" => $row->DS_DESCRIPTION,
                        "PHOTO_URL1" => $row->DSIMG1,
                        "PHOTO_URL2" => $row->DSIMG2,
                        "PHOTO_URL3" => $row->DSIMG3,
                        "PHOTO_URL4" => $row->DSIMG4,
                        "PHOTO_URL5" => $row->DSIMG5,
                        "PHOTO_URL6" => $row->DSIMG6,
                        "PHOTO_URL7" => $row->DSIMG7,
                        "PHOTO_URL8" => $row->DSIMG8,
                        "PHOTO_URL9" => $row->DSIMG9,
                        "PHOTO_URL10" => $row->DSIMG10,
                    ];
                }
            }

            $data = \array_values($groupedData);
            $response = $data ?
                ['Success' => true, 'data' => $data, 'code' => 200] :
                ['Success' => false, 'Message' => 'Record not found', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }
        return response()->json($response);
    }

    public function vu_pharma_facility(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $dsid = $input['DASH_SECTION_ID'];

            $query = DB::table('facility_section')
                ->join('facility_type', function ($join) use ($dsid) {
                    $join->on('facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                        ->where('facility_type.DT_TAG_SECTION', 'like', '%' . $dsid . '%');
                })
                ->join('facility', function ($join) use ($dsid) {
                    $join->on('facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                        ->where('facility_type.DT_TAG_SECTION', 'like', '%' . $dsid . '%');
                })
                ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId) {
                    $join->on('facility.DASH_ID', '=', 'hospital_facilities_details.DASH_ID')
                        ->where('hospital_facilities_details.PHARMA_ID', $pharmaId);
                })

                ->where(['facility_type.DT_STATUS' => 'Active', 'facility_section.DS_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                ->select(
                    'facility_section.DASH_SECTION_ID',
                    'facility_section.DASH_SECTION_NAME',
                    'facility_section.DS_DESCRIPTION',

                    'facility_type.DASH_TYPE_ID',
                    'facility_type.DIS_ID',
                    'facility_type.DASH_TYPE',
                    'facility_type.DT_DESCRIPTION',
                    'facility_type.DTIMG1',

                    'facility.DASH_ID',
                    'facility.DASH_NAME',
                    'facility.DN_DESCRIPTION',
                    'facility.DNIMG1',

                    'hospital_facilities_details.UID',
                    'hospital_facilities_details.TOT_BED',
                    'hospital_facilities_details.AVAIL_BED',
                    'hospital_facilities_details.PRICE_FROM',
                    'hospital_facilities_details.DEPT_PH',
                    'hospital_facilities_details.SHORT_NOTE',
                    'hospital_facilities_details.SND_OPINION',
                    'hospital_facilities_details.FREE_AREA',
                    'hospital_facilities_details.FREE_FROM',
                    'hospital_facilities_details.FREE_TO',
                    'hospital_facilities_details.SERV_AREA',
                    'hospital_facilities_details.SERV_FROM',
                    'hospital_facilities_details.SERV_TO',
                    'hospital_facilities_details.DLV_TM',
                    'hospital_facilities_details.MIN_ODR',
                    'hospital_facilities_details.SERV_24X7',
                    'hospital_facilities_details.SERV_HOME',
                    'hospital_facilities_details.SERV_IP',
                    'hospital_facilities_details.TAG_NOTE',
                    'hospital_facilities_details.TREATMENTS',
                    'hospital_facilities_details.SUPER_SPLTY',
                    'hospital_facilities_details.DISCOUNT',
                    'hospital_facilities_details.CASH_LESS',
                    'hospital_facilities_details.CASH_PAID',
                    'hospital_facilities_details.IMAGE1_URL',
                    'hospital_facilities_details.IMAGE2_URL',
                    'hospital_facilities_details.IMAGE3_URL',
                    'hospital_facilities_details.REMARK'
                );
            $data1 = $query->get();
            // Additional query to handle the case where $dsid is 'TV'
            // if ($dsid == 'TV') {
            //     $additionalQuery = DB::table('hospital_facilities_details')
            //         ->leftJoin('facility', 'facility.DASH_ID', '=', 'hospital_facilities_details.DASH_ID')
            //         ->leftJoin('facility_type', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
            //         ->leftJoin('facility_section', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
            //         ->where('hospital_facilities_details.PHARMA_ID', $pharmaId)
            //         ->whereIn('hospital_facilities_details.DASH_SECTION_ID', ['SP', 'SR', 'TU'])
            //         ->select(
            //             'facility_section.DASH_SECTION_ID',
            //             'facility_section.DASH_SECTION_NAME',
            //             'facility_section.DS_DESCRIPTION',

            //             'facility_type.DASH_TYPE_ID',
            //             'facility_type.DIS_ID',
            //             'facility_type.DASH_TYPE',
            //             'facility_type.DT_DESCRIPTION',
            //             'facility_type.DTIMG1',

            //             'facility.DASH_ID',
            //             'facility.DASH_NAME',
            //             'facility.DN_DESCRIPTION',
            //             'facility.DNIMG1',

            //             'hospital_facilities_details.UID',
            //             'hospital_facilities_details.TOT_BED',
            //             'hospital_facilities_details.AVAIL_BED',
            //             'hospital_facilities_details.PRICE_FROM',
            //             'hospital_facilities_details.DEPT_PH',
            //             'hospital_facilities_details.SHORT_NOTE',
            //             'hospital_facilities_details.SND_OPINION',
            //             'hospital_facilities_details.FREE_AREA',
            //             'hospital_facilities_details.FREE_FROM',
            //             'hospital_facilities_details.FREE_TO',
            //             'hospital_facilities_details.SERV_AREA',
            //             'hospital_facilities_details.SERV_FROM',
            //             'hospital_facilities_details.SERV_TO',
            //             'hospital_facilities_details.DLV_TM',
            //             'hospital_facilities_details.MIN_ODR',
            //             'hospital_facilities_details.SERV_24X7',
            //             'hospital_facilities_details.SERV_HOME',
            //             'hospital_facilities_details.SERV_IP',
            //             'hospital_facilities_details.TAG_NOTE',
            //             'hospital_facilities_details.TREATMENTS',
            //             'hospital_facilities_details.SUPER_SPLTY',
            //             'hospital_facilities_details.DISCOUNT',
            //             'hospital_facilities_details.CASH_LESS',
            //             'hospital_facilities_details.CASH_PAID',
            //             'hospital_facilities_details.IMAGE1_URL',
            //             'hospital_facilities_details.IMAGE2_URL',
            //             'hospital_facilities_details.IMAGE3_URL',
            //             'hospital_facilities_details.REMARK'
            //         );

            //     $data1 = $query->union($additionalQuery)->get();
            // } else {
            //     $data1 = $query->get();
            // }

            $groupedData = [];
            foreach ($data1 as $row) {
                $typeKey = $row->DASH_TYPE_ID;

                if (!isset($groupedData[$typeKey])) {
                    if ($dsid === 'AM') {
                        $DSecID = 'AM';
                    } else {
                        $DSecID = $row->DASH_SECTION_ID;
                    }

                    $DSecName = $row->DASH_SECTION_NAME;

                    $groupedData[$typeKey] = [
                        "DASH_SECTION_ID" => $DSecID,
                        "DASH_SECTION_NAME" => $DSecName,
                        "DASH_TYPE_ID" => $row->DASH_TYPE_ID,
                        "DIS_ID" => $row->DIS_ID,
                        "DASH_TYPE" => $row->DASH_TYPE,
                        "DESCRIPTION" => $row->DS_DESCRIPTION,
                        "PHOTO_URL" => $row->DTIMG1,
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

                $facility = [
                    "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                    "DIS_ID" => $row->DIS_ID,
                    "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                    "UID" => $row->UID,
                    "DASH_ID" => $row->DASH_ID,
                    "DASH_TYPE_ID" => $row->DASH_TYPE_ID,
                    "DASH_TYPE" => $dstype,
                    "DASH_NAME" => $dname,
                    "DESCRIPTION" => $row->DN_DESCRIPTION,
                    "TOT_BED" => $row->TOT_BED,
                    "AVAIL_BED" => $row->AVAIL_BED,
                    "PRICE_FROM" => $row->PRICE_FROM,
                    "DEPT_PH" => $row->DEPT_PH,
                    "SHORT_NOTE" => $row->SHORT_NOTE,
                    "SND_OPINION" => $row->SND_OPINION,
                    "FREE_AREA" => $row->FREE_AREA,
                    "FREE_FROM" => $row->FREE_FROM,
                    "FREE_TO" => $row->FREE_TO,
                    "SERV_AREA" => $row->SERV_AREA,
                    "SERV_FROM" => $row->SERV_FROM,
                    "SERV_TO" => $row->SERV_TO,
                    "DLV_TM" => $row->DLV_TM,
                    "MIN_ODR" => $row->MIN_ODR,
                    "SERV_24X7" => $row->SERV_24X7,
                    "SERV_HOME" => $row->SERV_HOME,
                    "SERV_IP" => $row->SERV_IP,
                    "TAG_NOTE" => $row->TAG_NOTE,
                    "TREATMENTS" => $row->TREATMENTS,
                    "SUPER_SPLTY" => $row->SUPER_SPLTY,
                    "DISCOUNT" => $row->DISCOUNT,
                    "CASH_LESS" => $row->CASH_LESS,
                    "CASH_PAID" => $row->CASH_PAID,
                    "IMAGE1_URL" => $row->IMAGE1_URL,
                    "IMAGE2_URL" => $row->IMAGE2_URL,
                    "IMAGE3_URL" => $row->IMAGE3_URL,
                    "REMARK" => $row->REMARK,
                    "PHOTO_URL" => $row->DNIMG1,
                ];

                $groupedData[$typeKey]['FACILITY'][] = $facility;
            }

            $data = array_values($groupedData);

            if (empty($data)) {
                return response()->json(['Success' => false, 'Message' => 'Record not found', 'code' => 404], 404);
            } else {
                return response()->json(['Success' => true, 'data' => $data, 'code' => 200], 200);
            }
        } else {
            return response()->json(['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405], 405);
        }
    }

    public function vuupdt_facility(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $dsid = $input['DASH_SECTION_ID'];
            $dtid = $input['DASH_TYPE_ID'];

            $data = DB::table('facility_section')
                ->join('facility_type', function ($join) use ($dsid) {
                    $join->on('facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                        ->where('facility_type.DT_TAG_SECTION', 'like', '%' . $dsid . '%');
                })
                ->join('facility', function ($join) use ($dsid, $dtid) {
                    $join->on('facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                        ->where('facility.DN_TAG_SECTION', $dtid);
                })
                ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId, $dsid) {
                    $join->on('facility.DASH_ID', '=', 'hospital_facilities_details.DASH_ID')
                        ->where(['hospital_facilities_details.PHARMA_ID' => $pharmaId, 'hospital_facilities_details.DASH_SECTION_ID' => $dsid]);
                })
                ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                ->select(
                    'facility_section.DASH_SECTION_ID',
                    'facility_section.DASH_SECTION_NAME',
                    'facility_section.DS_DESCRIPTION',

                    'facility_type.DASH_TYPE_ID',
                    'facility_type.DASH_TYPE',
                    'facility_type.DT_DESCRIPTION',
                    'facility_type.DTIMG1',

                    'facility.DASH_ID',
                    'facility.DASH_NAME',
                    'facility.DN_DESCRIPTION',
                    'facility.DNIMG1 AS PHOTO_URL',

                    'hospital_facilities_details.UID',
                    'hospital_facilities_details.TOT_BED',
                    'hospital_facilities_details.AVAIL_BED',
                    'hospital_facilities_details.PRICE_FROM',
                    'hospital_facilities_details.DEPT_PH',
                    'hospital_facilities_details.SHORT_NOTE',
                    'hospital_facilities_details.SND_OPINION',
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
                    'hospital_facilities_details.SERV_IP',
                    'hospital_facilities_details.TREATMENTS',
                    'hospital_facilities_details.SUPER_SPLTY',
                    'hospital_facilities_details.DISCOUNT',
                    'hospital_facilities_details.CASH_LESS',
                    'hospital_facilities_details.CASH_PAID',
                    'hospital_facilities_details.CASH_BOTH',
                    'hospital_facilities_details.IMAGE1_URL',
                    'hospital_facilities_details.IMAGE2_URL',
                    'hospital_facilities_details.IMAGE3_URL',
                    'hospital_facilities_details.REMARK'
                )
                ->get();

            if ($input['DASH_SECTION_ID'] == 'AG') {
                $data->transform(function ($item) {
                    $item->DASH_TYPE = '24x7 ' . $item->DASH_TYPE;
                    $item->DASH_NAME = '24x7 ' . $item->DASH_NAME;
                    return $item;
                });
            } elseif ($input['DASH_SECTION_ID'] == 'AM') {
                $data->transform(function ($item) {
                    $item->DASH_SECTION_ID = 'AM';
                    return $item;
                });
            }

            if ($data->isEmpty()) {
                $response = ['Success' => false, 'Message' => 'Record not found', 'code' => 404];
            } else {
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }
        return response()->json($response);
    }

    function add_facility(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ], 405);
        }
        date_default_timezone_set('Asia/Kolkata');
        $cdt = Carbon::now()->format('Ymd');
        $input = $req->all();
        $fileName1 = $fileName2 = $fileName3 = null;
        $url1 = $url2 = $url3 = null;

        $UID = strtoupper(substr(md5($input['PHARMA_ID'] . $input['DASH_ID'] . $input['DASH_SECTION_ID']), 0, 10));
        if ($req->file('IMAGE1_URL')) {
            $fileName1 = $UID . "_1." . $req->file('IMAGE1_URL')->getClientOriginalExtension();
            $req->file('IMAGE1_URL')->storeAs('facilities', $fileName1);
            $url1 = asset(storage::url('app/facilities')) . "/" . $fileName1;
        }

        if ($req->file('IMAGE2_URL')) {
            $fileName2 = $UID . "_2." . $req->file('IMAGE2_URL')->getClientOriginalExtension();
            $req->file('IMAGE2_URL')->storeAs('facilities', $fileName2);
            $url2 = asset(storage::url('app/facilities')) . "/" . $fileName2;
        }

        if ($req->file('IMAGE3_URL')) {
            $fileName3 = $UID . "_3." . $req->file('IMAGE3_URL')->getClientOriginalExtension();
            $req->file('IMAGE3_URL')->storeAs('facilities', $fileName3);
            $url3 = asset(storage::url('app/facilities')) . "/" . $fileName3;
        }

        $fields = [
            'UID' => $UID,
            'PHARMA_ID' => $input['PHARMA_ID'],
            // 'DIS_ID' => $input['DIS_ID'],
            'DASH_SECTION_ID' => $input['DASH_SECTION_ID'],
            'DASH_ID' => $input['DASH_ID'],
            'DASH_NAME' => $input['DASH_NAME'],
            'DEPARTMENT' => $input['DEPARTMENT'],
            'DEPT_PH' => $input['DEPT_PH'],
            'DEPT_PH1' => $input['DEPT_PH1'] ?? null,
            'TOT_BED' => $input['TOT_BED'] ?? null,
            'AVAIL_BED' => $input['AVAIL_BED'] ?? null,
            'PRICE_FROM' => $input['PRICE_FROM'] ?? null,
            'SHORT_NOTE' => $input['SHORT_NOTE'] ?? null,
            'SND_OPINION' => $input['SND_OPINION'] ?? null,
            'FREE_AREA' => $input['FREE_AREA'] ?? null,
            'FREE_FROM' => $input['FREE_FROM'] ?? null,
            'FREE_TO' => $input['FREE_TO'] ?? null,
            'SERV_AREA' => $input['SERV_AREA'] ?? null,
            'SERV_FROM' => $input['SERV_FROM'] ?? null,
            'SERV_TO' => $input['SERV_TO'] ?? null,
            'SERV_CRG' => $input['SERV_CRG'] ?? null,
            'DLV_TM' => $input['DLV_TM'] ?? null,
            'MIN_ODR' => $input['MIN_ODR'] ?? null,
            'SERV_HOME' => $input['SERV_HOME'] ?? null,
            'SERV_IP' => $input['SERV_IP'] ?? null,
            'TREATMENTS' => $input['TREATMENTS'] ?? null,
            'SUPER_SPLTY' => $input['SUPER_SPLTY'] ?? null,
            'DISCOUNT' => $input['DISCOUNT'] ?? null,
            'CASH_LESS' => $input['CASH_LESS'] ?? null,
            'CASH_PAID' => $input['CASH_PAID'] ?? null,
            'CASH_BOTH' => $input['CASH_BOTH'] ?? null,
            'IMAGE1_URL' => $url1,
            'IMAGE2_URL' => $url2,
            'IMAGE3_URL' => $url3,
            'UPDT_DT' => $cdt,
        ];

        try {
            if ($input['REMARK'] === 'true') {
                DB::table('hospital_facilities_details')->where('UID', $UID)->update($fields);
                $response = [
                    'Success' => true,
                    'Message' => 'Facilities update successfully.',
                    'REMARK' => 'true',
                    'code' => 200
                ];
            } else {
                DB::table('hospital_facilities_details')->insert($fields);
                $response = [
                    'Success' => true,
                    'Message' => 'Facilities added successfully.',
                    'REMARK' => 'true',
                    'code' => 200
                ];
            }
        } catch (\Throwable $th) {
            $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
        }
        return $response;
    }

    function vu_dept(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $dId = $input['DIS_ID'];

            $distinctDoctors = DB::table('dr_availablity')
                ->select('DR_ID', 'TAG_DEPT', 'TAG_NOTE', 'DEPT_PH', 'TREATMENTS')
                ->distinct()
                ->where(['PHARMA_ID' => $pharmaId, 'DIS_ID' => $dId]);

            $data = DB::table('drprofile')
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
                    'distinct_doctors.TAG_NOTE',
                    'distinct_doctors.DEPT_PH',
                    'distinct_doctors.TREATMENTS',
                )
                ->get();

            if ($data->isEmpty()) {
                $response = ['Success' => false, 'Message' => 'Record not found', 'code' => 200];
            } else {
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return response()->json($response);
    }

    function add_deptdr(Request $req)
    {
        $response = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            $dptdr = $input['ADD_DEPTDR'];
            $data = [];

            foreach ($dptdr as $row) {
                $fields = [
                    'TAG_DEPT' => $row['TAG_DEPT'],
                    'DEPT_PH' => $input['DEPT_PH'],
                    'TAG_NOTE' => $input['TAG_NOTE'],
                    'TREATMENTS' => $input['TREATMENTS'],
                ];

                $doctorData = [
                    'PHARMA_ID' => $row['PHARMA_ID'],
                    'DR_ID' => $row['DR_ID'],
                    'DIS_ID' => $row['DIS_ID'],
                    'DR_NAME' => $row['DR_NAME'],
                    'SEX' => $row['SEX'],
                    'DESIGNATION' => $row['DESIGNATION'],
                    'QUALIFICATION' => $row['QUALIFICATION'],
                    'D_CATG' => $row['D_CATG'],
                    'EXPERIENCE' => $row['EXPERIENCE'],
                    'LANGUAGE' => $row['LANGUAGE'],
                    'DR_PHOTO' => $row['DR_PHOTO'],
                    'TAG_DEPT' => $row['TAG_DEPT'],
                    'TAG_NOTE' => $input['TAG_NOTE'],
                    'DEPT_PH' => $input['DEPT_PH'],
                    'TREATMENTS' => $input['TREATMENTS'],
                ];

                try {
                    DB::table('dr_availablity')->where(['DR_ID' => $row['DR_ID'], 'PHARMA_ID' => $row['PHARMA_ID']])->update($fields);

                    $data[] = $doctorData;
                } catch (\Throwable $th) {
                    $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
                    return $response;
                }
            }
            $UID = strtoupper(substr(md5($input['PHARMA_ID'] . $input['DASH_ID'] . $input['DASH_SECTION_ID']), 0, 10));
            $fields1 = [
                'UID' => $UID,
                'DIS_ID' => $input['DIS_ID'],
                // 'DR_ID' => $row['DR_ID'],
                'DASH_SECTION_ID' => $input['DASH_SECTION_ID'],
                'DEPARTMENT' => $input['DASH_SECTION_NAME'],
                'DASH_ID' => $input['DASH_ID'],
                'DASH_NAME' => $input['DASH_NAME'],
                'PHARMA_ID' => $input['PHARMA_ID'],
                'DEPT_PH' => $input['DEPT_PH'],
                'TAG_NOTE' => $input['TAG_NOTE'],
                'TREATMENTS' => $input['TREATMENTS'],
                'REMARK' => 'true'
            ];
            try {
                DB::table('hospital_facilities_details')->insert($fields1);
            } catch (\Throwable $th) {
                DB::table('hospital_facilities_details')->where(['DASH_ID' => $input['DASH_ID'], 'PHARMA_ID' => $input['PHARMA_ID']])->update($fields1);
            }
            $doctorData1 = [
                'TAG_NOTE' => $input['TAG_NOTE'],
                'DEPT_PH' => $input['DEPT_PH'],
                'TREATMENTS' => $input['TREATMENTS'],
            ];
            $data1 = $data + $doctorData1;
            $response = [
                'Success' => true,
                'data' => $data1,
                'Message' => 'Doctors added in department successfully.',
                'code' => 200
            ];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }

    public function drdashboard(Request $request)
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
                        'CHK_IN_TIME1',
                        'CHK_OUT_TIME1',
                        'CHK_IN_TIME2',
                        'CHK_OUT_TIME2',
                        'CHK_IN_TIME3',
                        'CHK_OUT_TIME3',
                        'MAX_BOOK',
                        'MAX_BOOK1',
                        'MAX_BOOK2',
                        'MAX_BOOK3',
                        'CHEMBER_NO',
                        'CHK_IN_STATUS',
                        'DR_FEES',
                        DB::raw("'" . Carbon::now()->format('Ymd') . "' as SCH_DT"),
                    )
                    ->distinct()
                    ->where(['DR_ID' => $doctorId, 'SCH_DAY' => $dayOfWeek]);

                $availabilityData = DB::table('pharmacy')
                    ->joinSub($distinctDoctors, 'distinct_doctors', function ($join) {
                        $join->on('pharmacy.PHARMA_ID', '=', 'distinct_doctors.PHARMA_ID')
                            ->where('pharmacy.STATUS', '=', 'Active');
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
                        'distinct_doctors.MAX_BOOK1',
                        'distinct_doctors.MAX_BOOK2',
                        'distinct_doctors.MAX_BOOK3',
                        'distinct_doctors.CHK_IN_TIME',
                        'distinct_doctors.CHK_OUT_TIME',
                        'distinct_doctors.CHK_IN_TIME1',
                        'distinct_doctors.CHK_OUT_TIME1',
                        'distinct_doctors.CHK_IN_TIME2',
                        'distinct_doctors.CHK_OUT_TIME2',
                        'distinct_doctors.CHK_IN_TIME3',
                        'distinct_doctors.CHK_OUT_TIME3',
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
                        "CHK_IN_TIME1" => $row->CHK_IN_TIME1,
                        "CHK_OUT_TIME1" => $row->CHK_OUT_TIME1,
                        "CHK_IN_TIME2" => $row->CHK_IN_TIME2,
                        "CHK_OUT_TIME2" => $row->CHK_OUT_TIME2,
                        "CHK_IN_TIME3" => $row->CHK_IN_TIME3,
                        "CHK_OUT_TIME3" => $row->CHK_OUT_TIME3,
                        "DR_STATUS" => $row->CHK_IN_STATUS,
                        "CHEMBER_NO" => $row->CHEMBER_NO,
                        "MAX_BOOK" => $row->MAX_BOOK,
                        "MAX_BOOK1" => $row->MAX_BOOK1,
                        "MAX_BOOK2" => $row->MAX_BOOK2,
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

                    $chamber['TOTAL_BOOKED'] = count($groupedAppointments);
                    $chamber['DETAILS'] = array_values($groupedAppointments);

                    $totalChambers++;
                    $sumMaxBook += $row->MAX_BOOK;
                    $sumTotalBooked += $chamber['TOTAL_BOOKED'];

                    $chambers[] = $chamber;
                }

                //SECTION-A #### DR_Details
                $A['Doctor'] = DB::table('drprofile')->where(['DR_ID' => $doctorId])->get();

                //SECTION-B ####
                $B['Dashboard'] = DB::table('dr_dashboard_details')->where(['STATUS' => 'Active'])->get();

                //SECTION-C #### DR REVIEW
                $data1 = DB::table('drprofile')
                    ->leftjoin('patient_review', 'drprofile.DR_ID', '=', 'patient_review.DR_ID')
                    ->select(
                        'drprofile.*',
                        'patient_review.FAMILY_ID',
                        'patient_review.FRIENDLY',
                        'patient_review.SATISFY',
                        'patient_review.ECONOMY',
                        'patient_review.RECOMEND',
                        'patient_review.REMARKS',
                        'patient_review.REVIEW_DT',
                    )
                    ->where(['drprofile.DR_ID' => $doctorId])
                    ->where('drprofile.APPROVE', 'true')
                    ->get();

                $disId = $data1[0]->DIS_ID;

                $groupedData = [];
                $uniqueKeys = [];
                foreach ($data1 as $row) {
                    $groupedData[$row->DR_ID] = [
                        "DR_ID" => $row->DR_ID,
                        "DR_NAME" => $row->DR_NAME,
                        'ADDRESS' => $row->ADDRESS,
                        'EMAIL' => $row->EMAIL,
                        'STATE' => $row->STATE,
                        'DIST' => $row->DIST,
                        'CITY' => $row->CITY,
                        'DR_MOBILE' => $row->DR_MOBILE,
                        'SEX' => $row->SEX,
                        'UID_NMC' => $row->UID_NMC,
                        'REGN_NO' => $row->REGN_NO,
                        'REGN_DT' => $row->REGN_DT,
                        'COUNCIL' => $row->COUNCIL,
                        'ACCOUNT_NAME' => $row->ACCOUNT_NAME,
                        'BANK_NAME' => $row->BANK_NAME,
                        'ACCOUNT_NO' => $row->ACCOUNT_NO,
                        'IFSC_CODE' => $row->IFSC_CODE,
                        'ASSOCIATED' => $row->ASSOCIATED,
                        'DESIGNATION' => $row->DESIGNATION,
                        'QUALIFICATION' => $row->QUALIFICATION,
                        'D_CATG' => $row->D_CATG,
                        'EXPERIENCE' => $row->EXPERIENCE,
                        'SERVICES' => $row->SERVICES,
                        'LANGUAGE' => $row->LANGUAGE,
                        'DR_PHOTO' => $row->PHOTO_URL,
                        'DR_ABOUT' => $row->DR_ABOUT,
                        'AWARDS' => $row->AWARDS,
                        "TOT_FRIENDLY" => 0,
                        "TOT_SATISFY" => 0,
                        "TOT_ECONOMY" => 0,
                        "TOT_RECOMEND" => 0,
                        "REVIEW_DETAILS" => [],
                    ];
                }

                $groupedData[$row->DR_ID]['REVIEW_DETAILS'][] = [
                    "DR_ID" => $row->DR_ID,
                    "FAMILY_ID" => $row->FAMILY_ID,
                    "FRIENDLY" => $row->FRIENDLY,
                    "SATISFY" => $row->SATISFY,
                    "ECONOMY" => $row->ECONOMY,
                    "RECOMEND" => $row->RECOMEND,
                    "REMARKS" => $row->REMARKS,
                    "REVIEW_DT" => $row->REVIEW_DT,
                ];

                $groupedData[$row->DR_ID]['TOT_FRIENDLY'] += $row->FRIENDLY;
                $groupedData[$row->DR_ID]['TOT_SATISFY'] += $row->SATISFY;
                $groupedData[$row->DR_ID]['TOT_ECONOMY'] += $row->ECONOMY;
                $groupedData[$row->DR_ID]['TOT_RECOMEND'] += $row->RECOMEND;

                foreach ($groupedData as $drId => &$drids) {
                    if ($row->FAMILY_ID != null) {
                        $totalReviews = count($drids['REVIEW_DETAILS']);
                        $drids['TOT_REVIEW'] = $totalReviews;
                    } else {
                        $totalReviews = 0;
                        $drids['TOT_REVIEW'] = $totalReviews;
                    }
                    if ($totalReviews > 0) {
                        $drids['AVG_FRIENDLY'] = $drids['TOT_FRIENDLY'] / $totalReviews;
                        $drids['AVG_SATISFY'] = $drids['TOT_SATISFY'] / $totalReviews;
                        $drids['AVG_ECONOMY'] = $drids['TOT_ECONOMY'] / $totalReviews;
                        $drids['AVG_RECOMEND'] = $drids['TOT_RECOMEND'] / $totalReviews;
                    } else {
                        $drids['AVG_FRIENDLY'] = 0;
                        $drids['AVG_SATISFY'] = 0;
                        $drids['AVG_ECONOMY'] = 0;
                        $drids['AVG_RECOMEND'] = 0;
                    }
                    $avgzeroCount = 0;
                    if ($drids['AVG_FRIENDLY'] == 0)
                        $avgzeroCount++;
                    if ($drids['AVG_SATISFY'] == 0)
                        $avgzeroCount++;
                    if ($drids['AVG_ECONOMY'] == 0)
                        $avgzeroCount++;
                    if ($drids['AVG_RECOMEND'] == 0)
                        $avgzeroCount++;
                    if ($avgzeroCount != 4) {
                        $drids['AVG_REVIEW'] = ($drids['AVG_FRIENDLY'] + $drids['AVG_SATISFY'] + $drids['AVG_ECONOMY'] + $drids['AVG_RECOMEND']) / (4 - $avgzeroCount);
                    } else {
                        $drids['AVG_REVIEW'] = 0;
                    }

                    if ($drids['AVG_REVIEW'] <= 2) {
                        $drids['REVIEW_REMARKS'] = 'Not Satisfy';
                    } elseif ($drids['AVG_REVIEW'] <= 4) {
                        $drids['REVIEW_REMARKS'] = 'Good';
                    } elseif ($drids['AVG_REVIEW'] <= 6) {
                        $drids['REVIEW_REMARKS'] = 'Very Good';
                    } elseif ($drids['AVG_REVIEW'] <= 8) {
                        $drids['REVIEW_REMARKS'] = 'Excellent';
                    } else {
                        $drids['REVIEW_REMARKS'] = 'Outstanding';
                    }
                }

                $C['Review'] = array_values($groupedData);

                $response = [
                    'Success' => true,
                    'data' => [
                        'Doctor' => $A['Doctor'],
                        'Dashboard' => $B['Dashboard'],
                        'Review' => $C['Review'],
                        'Today_Chember' => $chambers,
                        'Promo_Banner' => $banners->filter(fn ($item) => $item->DASH_SECTION_ID === 'PB')->values()->all(),
                        'Today_Summary' => [
                            [
                                'APPNT_DT' => date('Ymd'),
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
                        'Promo_Banner' => $banners->filter(fn ($item) => $item->DASH_SECTION_ID === 'PB')->values()->all(),
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
                        $join->on('pharmacy.PHARMA_ID', '=', 'distinct_doctors.PHARMA_ID')
                            ->where('pharmacy.STATUS', '=', 'Active');
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
                        'Promo_Banner' => $banners->filter(fn ($item) => $item->DASH_SECTION_ID === 'PB')->values()->all(),
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

    public function avail_chember(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();

            // Validate the input
            $validator = Validator::make($input, [
                'PHARMA_ID' => 'required|integer|exists:pharmacy,PHARMA_ID',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'Success' => false,
                    'Message' => 'Validation Error.',
                    'Errors' => $validator->errors(),
                    'code' => 422,
                ]);
            }

            $pid = $input['PHARMA_ID'];
            $date = Carbon::now();
            $weekNumber = $date->weekOfMonth;
            $day1 = date('l');
            $currentDate = Carbon::now();
            $cdy = date('d');
            $cdt = date('Ymd');

            // Retrieve the CHEMBER_CT value
            $data = DB::table('pharmacy')
                ->select('CHEMBER_CT')
                ->where('PHARMA_ID', $pid)
                ->where('pharmacy.STATUS', '=', 'Active')
                ->first();

            $todaychmbers = DB::table('pharmacy')
                ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
                ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
                ->distinct('drprofile.DR_ID')
                ->select(
                    'dr_availablity.CHEMBER_NO',
                    'dr_availablity.CHEMBER_NO1',
                    'dr_availablity.CHEMBER_NO2',
                    'dr_availablity.CHEMBER_NO3'
                )
                ->where(['dr_availablity.SCH_DAY' => $day1, 'dr_availablity.PHARMA_ID' => $pid])
                ->where('pharmacy.STATUS', '=', 'Active')
                ->where('WEEK', 'like', '%' . $weekNumber . '%')
                ->where('drprofile.APPROVE', 'true')
                ->orWhere('dr_availablity.SCH_DT', $cdy)
                ->get();

            if ($data) {
                // Create an array of serial numbers from 1 to CHEMBER_CT
                $serialNumbers = range(1, $data->CHEMBER_CT);

                // Get all non-null chember numbers
                $bookedSerials = [];
                foreach ($todaychmbers as $chm) {
                    if (!is_null($chm->CHEMBER_NO))
                        $bookedSerials[] = $chm->CHEMBER_NO;
                    if (!is_null($chm->CHEMBER_NO1))
                        $bookedSerials[] = $chm->CHEMBER_NO1;
                    if (!is_null($chm->CHEMBER_NO2))
                        $bookedSerials[] = $chm->CHEMBER_NO2;
                    if (!is_null($chm->CHEMBER_NO3))
                        $bookedSerials[] = $chm->CHEMBER_NO3;
                }

                // Remove duplicates and sort the array in ascending order
                $bookedSerials = array_unique($bookedSerials);
                sort($bookedSerials);

                // Remove booked serials from serialNumbers
                $availableSerials = array_diff($serialNumbers, $bookedSerials);

                // Convert integers to strings
                $availableSerials = array_map(function ($num) {
                    return (string) $num;
                }, $availableSerials);

                $response = [
                    'Success' => true,
                    'data' => [
                        'CHEMBER_CT' => $data->CHEMBER_CT,
                        'AVAIL_SERIALS' => array_values($availableSerials), // Reindex array
                        'BOOKED_SERIALS' => array_values($bookedSerials) // Reindex array
                    ],
                    'code' => 200
                ];
            } else {
                $response = [
                    'Success' => false,
                    'Message' => 'Pharmacy not found.',
                    'code' => 404
                ];
            }
        } else {
            $response = [
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ];
        }
        return response()->json($response);
    }

    function vu_radiology(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {

                $PID = $input['PHARMA_ID'];

                $response = array();
                $data = array();

                $data1 = DB::table('clinic_testdata')
                    // ->join('clinic_testdata', 'master_testdata.TEST_ID', '=', 'clinic_testdata.TEST_ID')
                    ->select(
                        'clinic_testdata.TEST_ID',
                        'clinic_testdata.TEST_NAME',
                        'clinic_testdata.DEPT_ID',
                        'clinic_testdata.DEPARTMENT',
                        'clinic_testdata.SUB_DEPT_ID',
                        'clinic_testdata.TEST_CATG',
                        'clinic_testdata.PHARMA_ID',
                        'clinic_testdata.TEST_UC',
                        'clinic_testdata.TEST_SL',
                        'clinic_testdata.COST',
                    )
                    ->where(['clinic_testdata.PHARMA_ID' => $PID, 'clinic_testdata.DEPARTMENT' => 'RADIOLOGY'])
                    ->orderby('clinic_testdata.TEST_SL')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row2) {
                    if (!isset($groupedData[$row2->TEST_CATG])) {
                        $groupedData[$row2->TEST_CATG] = [
                            "DEPT_ID" => $row2->DEPT_ID,
                            "DEPARTMENT" => $row2->DEPARTMENT,
                            "SUB_DEPT_ID" => $row2->SUB_DEPT_ID,
                            "TEST_CATG" => $row2->TEST_CATG,
                            "TEST_DETAILS" => []
                        ];
                    }

                    $groupedData[$row2->TEST_CATG]['TEST_DETAILS'][] = [
                        "TEST_ID" => $row2->TEST_ID,
                        "TEST_UC" => $row2->TEST_UC,
                        "TEST_NAME" => $row2->TEST_NAME,
                        "TEST_SL" => $row2->TEST_SL,
                    ];
                }
                $data = array_values($groupedData);

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function addtestsch(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();

            $td = $input['ADD_TEST_SCH'];

            foreach ($td as $row) {
                if ($row['SUB_DEPT_ID'] === null) {
                    $schId = strtoupper(substr(md5($row['SCH_STATUS'] . $row['PHARMA_ID'] . $row['SCH_DAY'] . $row['DEPT_ID']), 0, 15));
                } else {
                    $schId = strtoupper(substr(md5($row['PHARMA_ID'] . $row['SCH_DAY'] . $row['DEPT_ID'] . $row['SUB_DEPT_ID']), 0, 15));
                }



                $fields = [
                    'SCH_ID' => $schId,
                    'DEPT_ID' => $row['DEPT_ID'],
                    'DEPARTMENT' => $row['DEPARTMENT'],
                    'SUB_DEPT_ID' => $row['SUB_DEPT_ID'] ?? null,
                    'SUB_DEPARTMENT' => $row['SUB_DEPARTMENT'] ?? null,
                    'PHARMA_ID' => $row['PHARMA_ID'],
                    'SCH_DAY' => $row['SCH_DAY'],
                    'WEEK' => $row['WEEK'],
                    'SCH_START_DAY' => $row['SCH_START_DAY'],
                    'SCH_START_TIME' => $row['SCH_START_TIME'],
                    'SCH_FROM1' => $row['SCH_FROM1'],
                    'SCH_FROM2' => $row['SCH_FROM2'] ?? null,
                    'SCH_FROM3' => $row['SCH_FROM3'] ?? null,
                    'SCH_FROM4' => $row['SCH_FROM4'] ?? null,
                    'SCH_TO1' => $row['SCH_TO1'],
                    'SCH_TO2' => $row['SCH_TO2'] ?? null,
                    'SCH_TO3' => $row['SCH_TO3'] ?? null,
                    'SCH_TO4' => $row['SCH_TO4'] ?? null,
                    'MAX_BOOK1' => $row['MAX_BOOK1'] ?? null,
                    'MAX_BOOK2' => $row['MAX_BOOK2'] ?? null,
                    'MAX_BOOK3' => $row['MAX_BOOK3'] ?? null,
                    'MAX_BOOK4' => $row['MAX_BOOK4'] ?? null,
                    'DURATION' => $row['DURATION'] ?? null,
                    'SLOT_TEST_CT' => $row['SLOT_TEST_CT'] ?? null,
                    'SCH_STATUS' => $row['SCH_STATUS'] ?? null,
                ];

                for ($i = 1; $i <= 4; $i++) {
                    $maxBookCol = 'MAX_BOOK' . $i;
                    $chkInTimeCol = 'SCH_FROM' . $i;
                    $chkOutTimeCol = 'SCH_TO' . $i;

                    if ($row[$maxBookCol] === null && $row[$chkInTimeCol] && $row[$chkOutTimeCol] && $row['DURATION']) {
                        $chkinTime = Carbon::createFromFormat('h:i A', $row[$chkInTimeCol]);
                        $chkoutTime = Carbon::createFromFormat('h:i A', $row[$chkOutTimeCol]);
                        $minutesDiff = $chkinTime->diffInMinutes($chkoutTime, false);
                        $maxbook = ($minutesDiff / $row['DURATION']) * $row['SLOT_TEST_CT'];
                        $fields[$maxBookCol] = $maxbook;
                    }
                }

                // try {
                DB::table('test_schedule')->insert($fields);
                $response = ['Success' => true, 'Message' => 'Pathology schedule added successfully.', 'code' => 200];
                // } catch (\Throwable $th) {
                //     $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
                // }
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }

        return response()->json($response);
    }

    function vutestsch(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID']) && isset($input['DEPT_ID'])) {

                $query = DB::table('test_schedule')
                    ->where('PHARMA_ID', $input['PHARMA_ID'])
                    ->where('DEPT_ID', $input['DEPT_ID']);


                if (isset($input['SUB_DEPT_ID'])) {
                    $query->where('SUB_DEPT_ID', $input['SUB_DEPT_ID']);
                }

                $data = $query->get();

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }
        return response()->json($response);
    }

    function avail_testsch(Request $request)
    {
        if ($request->isMethod('post')) {
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();
            if (isset($input['PHARMA_ID']) && isset($input['SCH_STATUS']) && isset($input['DEPT_ID'])) {
                $sub_dept_id = $input['SUB_DEPT_ID'] ?? null;
                $data = [];

                $data = $this->getTestSchDt($input['PHARMA_ID'], $input['SCH_STATUS'], $input['DEPT_ID'], $sub_dept_id);

                if (empty($data)) {
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
    // private function getTestSchDt($P_ID, $SchSTS, $dept_id, $sub_dept_id)
    // {
    //     $schDT = [];
    //     $cym = date('Ymd');


    //     if ($dept_id === 'D1') {
    //         $TestAvail = DB::table('test_schedule')->where(['PHARMA_ID' => $P_ID, 'DEPT_ID' => $dept_id, 'SUB_DEPT_ID' => $sub_dept_id, 'SCH_STATUS' => $SchSTS])->get();
    //     } else {
    //         $TestAvail = DB::table('test_schedule')->where(['PHARMA_ID' => $P_ID, 'DEPT_ID' => $dept_id, 'SCH_STATUS' => $SchSTS])->get();
    //     }


    //     $totapp = DB::table('booktest')->where('PHARMA_ID', $P_ID)->where('SLOT_DT', '>=', $cym)->get();

    //     $data = [];

    //     foreach ($TestAvail as $row) {
    //         $startDate = Carbon::today();
    //         $endDate = Carbon::today()->addMonths(6);
    //         $counter = 0;

    //         while ($startDate->lte($endDate) && $counter < 15) {
    //             if ($dept_id === 'D3') {
    //                 // Handle department D3 with no MAX_BOOK values and unlimited bookings
    //                 $dates = $startDate->format('Ymd');
    //                 $formattedBookingDate = $startDate->format('Ymd');
    //                 $slots = $this->getSchDtSlot($P_ID, $row->ID, $dates);
    //                 $data[] = [
    //                     "ID" => $row->ID,
    //                     "DEPT_ID" => $row->DEPT_ID,
    //                     "SUB_DEPT_ID" => $row->SUB_DEPT_ID,
    //                     "SCH_DT" => $dates,
    //                     "SCH_DAY" => $row->SCH_DAY,
    //                     "SCH_STATUS" => $row->SCH_STATUS,
    //                     "BOOK_START_DAY" => $formattedBookingDate,
    //                     "BOOK_START_TIME" => $row->SCH_START_TIME,
    //                     "MAX_BOOK1" => null,
    //                     "MAX_BOOK2" => null,
    //                     "MAX_BOOK3" => null,
    //                     "MAX_BOOK4" => null,
    //                     "AVAILABLE1" => null,
    //                     "AVAILABLE2" => null,
    //                     "AVAILABLE3" => null,
    //                     "AVAILABLE4" => null,
    //                     "SCH_FROM1" => $row->SCH_FROM1,
    //                     "SCH_FROM2" => $row->SCH_FROM2,
    //                     "SCH_FROM3" => $row->SCH_FROM3,
    //                     "SCH_FROM4" => $row->SCH_FROM4,
    //                     "SCH_TO1" => $row->SCH_TO1,
    //                     "SCH_TO2" => $row->SCH_TO2,
    //                     "SCH_TO3" => $row->SCH_TO3,
    //                     "SCH_TO4" => $row->SCH_TO4,
    //                     "SLOT_STATUS" => "Available",
    //                     "SLOTS" => [$slots]
    //                 ];
    //                 $counter++;
    //             }else{
    //                 if ($startDate->format('l') === $row->SCH_DAY) {
    //                     if (in_array($startDate->weekOfMonth, explode(',', $row->WEEK))) {
    //                         $dates = $startDate->format('Ymd');
    //                         $bookingStartDate = Carbon::createFromFormat('Ymd', $dates)->subDays($row->SCH_START_DAY);
    //                         $formattedBookingDate = $bookingStartDate->format('Ymd');

    //                         $slot_dt = $formattedBookingDate;
    //                         $sch_id = $row->ID;
    //                         $fltr_apnt = $totapp->filter(function ($item) use ($slot_dt, $sch_id) {
    //                             return $item->SLOT_DT == $slot_dt && $item->SCH_ID == $sch_id;
    //                         });
    //                         $totappct = $fltr_apnt->count();
    //                         $totalMaxBook = collect([$row->MAX_BOOK1, $row->MAX_BOOK2, $row->MAX_BOOK3, $row->MAX_BOOK4])->filter()->sum();
    //                         $book_sts = ($totalMaxBook - $totappct == 0) ? "Closed" : "Available";
    //                         $slots = $this->getSchDtSlot($P_ID, $row->ID, $dates);
    //                         $data[] = [
    //                             "ID" => $row->ID,
    //                             "DEPT_ID" => $row->DEPT_ID,
    //                             "SUB_DEPT_ID" => $row->SUB_DEPT_ID,
    //                             "SCH_DT" => $dates,
    //                             "SCH_DAY" => $row->SCH_DAY,
    //                             "SCH_STATUS" => $row->SCH_STATUS,
    //                             "BOOK_START_DAY" => $formattedBookingDate,
    //                             "BOOK_START_TIME" => $row->SCH_START_TIME,
    //                             "MAX_BOOK1" => $row->MAX_BOOK1,
    //                             "MAX_BOOK2" => $row->MAX_BOOK2,
    //                             "MAX_BOOK3" => $row->MAX_BOOK3,
    //                             "MAX_BOOK4" => $row->MAX_BOOK4,
    //                             "AVAILABLE1" => $row->MAX_BOOK1 - $totappct,
    //                             "AVAILABLE2" => $row->MAX_BOOK2 - $totappct,
    //                             "AVAILABLE3" => $row->MAX_BOOK3 - $totappct,
    //                             "AVAILABLE4" => $row->MAX_BOOK4 - $totappct,
    //                             "SCH_FROM1" => $row->SCH_FROM1,
    //                             "SCH_FROM2" => $row->SCH_FROM2,
    //                             "SCH_FROM3" => $row->SCH_FROM3,
    //                             "SCH_FROM4" => $row->SCH_FROM4,
    //                             "SCH_TO1" => $row->SCH_TO1,
    //                             "SCH_TO2" => $row->SCH_TO2,
    //                             "SCH_TO3" => $row->SCH_TO3,
    //                             "SCH_TO4" => $row->SCH_TO4,
    //                             "SLOT_STATUS" => $book_sts,
    //                             "SLOTS" => [$slots]
    //                         ];
    //                         $counter++;
    //                     }
    //                 }
    //             }

    //             $startDate->addDay();
    //         }
    //     }

    //     usort($data, function ($item1, $item2) {
    //         return $item1['SCH_DT'] <=> $item2['SCH_DT'];
    //     });

    //     if (!empty($data) && $data[0]['SCH_DT'] === $cym) {
    //         $currentTime = Carbon::now();
    //         $times = [
    //             'SCH_TO1' => $data[0]['SCH_TO1'] ? Carbon::createFromFormat('h:i A', $data[0]['SCH_TO1']) : null,
    //             'SCH_TO2' => $data[0]['SCH_TO2'] ? Carbon::createFromFormat('h:i A', $data[0]['SCH_TO2']) : null,
    //             'SCH_TO3' => $data[0]['SCH_TO3'] ? Carbon::createFromFormat('h:i A', $data[0]['SCH_TO3']) : null,
    //             'SCH_TO4' => $data[0]['SCH_TO4'] ? Carbon::createFromFormat('h:i A', $data[0]['SCH_TO4']) : null,
    //         ];

    //         $allTimesPassed = true;

    //         foreach ($times as $time) {
    //             if ($time && $currentTime->lessThanOrEqualTo($time)) {
    //                 $allTimesPassed = false;
    //                 break;
    //             }
    //         }

    //         if ($allTimesPassed) {
    //             $data[0]['SLOT_STATUS'] = "Closed";
    //         }
    //     }

    //     $collection = collect($data);
    //     $firstAvailable = $collection->firstWhere('SLOT_STATUS', 'Available');

    //     if ($firstAvailable) {
    //         $firstAvailableIndex = $collection->search($firstAvailable);
    //         $schDT = array_slice($data, $firstAvailableIndex, 15);
    //     }

    //     return $schDT;
    // }

    private function getTestSchDt($P_ID, $SchSTS, $dept_id, $sub_dept_id)
    {
        $schDT = [];
        $cym = date('Ymd');

        if ($dept_id === 'D1') {
            $TestAvail = DB::table('test_schedule')->where(['PHARMA_ID' => $P_ID, 'DEPT_ID' => $dept_id, 'SUB_DEPT_ID' => $sub_dept_id, 'SCH_STATUS' => $SchSTS])->get();
        } else {
            $TestAvail = DB::table('test_schedule')->where(['PHARMA_ID' => $P_ID, 'DEPT_ID' => $dept_id, 'SCH_STATUS' => $SchSTS])->get();
        }

        $totapp = DB::table('booktest')->where('PHARMA_ID', $P_ID)->where('SLOT_DT', '>=', $cym)->get();

        $data = [];

        foreach ($TestAvail as $row) {
            $startDate = Carbon::today();
            $endDate = Carbon::today()->addMonths(6);
            $counter = 0;

            while ($startDate->lte($endDate) && $counter < 15) {
                if ($startDate->format('l') === $row->SCH_DAY && in_array($startDate->weekOfMonth, explode(',', $row->WEEK))) {
                    if ($dept_id === 'D3') {
                        // Handle department D3 with no MAX_BOOK values and unlimited bookings
                        $dates = $startDate->format('Ymd');
                        $formattedBookingDate = $startDate->format('Ymd');
                        $slots = $this->getSchDtSlot($P_ID, $row->ID, $dates);
                        $data[] = [
                            "ID" => $row->ID,
                            "DEPT_ID" => $row->DEPT_ID,
                            "SUB_DEPT_ID" => $row->SUB_DEPT_ID,
                            "SCH_DT" => $dates,
                            "SCH_DAY" => $startDate->format('l'),
                            "SCH_STATUS" => $row->SCH_STATUS,
                            "BOOK_START_DAY" => $formattedBookingDate,
                            "BOOK_START_TIME" => $row->SCH_START_TIME,
                            "MAX_BOOK1" => null,
                            "MAX_BOOK2" => null,
                            "MAX_BOOK3" => null,
                            "MAX_BOOK4" => null,
                            "AVAILABLE1" => null,
                            "AVAILABLE2" => null,
                            "AVAILABLE3" => null,
                            "AVAILABLE4" => null,
                            "SCH_FROM1" => $row->SCH_FROM1,
                            "SCH_FROM2" => $row->SCH_FROM2,
                            "SCH_FROM3" => $row->SCH_FROM3,
                            "SCH_FROM4" => $row->SCH_FROM4,
                            "SCH_TO1" => $row->SCH_TO1,
                            "SCH_TO2" => $row->SCH_TO2,
                            "SCH_TO3" => $row->SCH_TO3,
                            "SCH_TO4" => $row->SCH_TO4,
                            "SLOT_STATUS" => "Available",
                            "SLOTS" => [$slots]
                        ];
                        $counter++;
                    } else {
                        $dates = $startDate->format('Ymd');
                        $bookingStartDate = Carbon::createFromFormat('Ymd', $dates)->subDays($row->SCH_START_DAY);
                        $formattedBookingDate = $bookingStartDate->format('Ymd');

                        $slot_dt = $formattedBookingDate;
                        $sch_id = $row->ID;
                        $fltr_apnt = $totapp->filter(function ($item) use ($slot_dt, $sch_id) {
                            return $item->SLOT_DT == $slot_dt && $item->SCH_ID == $sch_id;
                        });
                        $totappct = $fltr_apnt->count();
                        $totalMaxBook = collect([$row->MAX_BOOK1, $row->MAX_BOOK2, $row->MAX_BOOK3, $row->MAX_BOOK4])->filter()->sum();
                        $book_sts = ($totalMaxBook - $totappct == 0) ? "Closed" : "Available";
                        $slots = $this->getSchDtSlot($P_ID, $row->ID, $dates);
                        $data[] = [
                            "ID" => $row->ID,
                            "DEPT_ID" => $row->DEPT_ID,
                            "SUB_DEPT_ID" => $row->SUB_DEPT_ID,
                            "SCH_DT" => $dates,
                            "SCH_DAY" => $startDate->format('l'),
                            "SCH_STATUS" => $row->SCH_STATUS,
                            "BOOK_START_DAY" => $formattedBookingDate,
                            "BOOK_START_TIME" => $row->SCH_START_TIME,
                            "MAX_BOOK1" => $row->MAX_BOOK1,
                            "MAX_BOOK2" => $row->MAX_BOOK2,
                            "MAX_BOOK3" => $row->MAX_BOOK3,
                            "MAX_BOOK4" => $row->MAX_BOOK4,
                            "AVAILABLE1" => $row->MAX_BOOK1 - $totappct,
                            "AVAILABLE2" => $row->MAX_BOOK2 - $totappct,
                            "AVAILABLE3" => $row->MAX_BOOK3 - $totappct,
                            "AVAILABLE4" => $row->MAX_BOOK4 - $totappct,
                            "SCH_FROM1" => $row->SCH_FROM1,
                            "SCH_FROM2" => $row->SCH_FROM2,
                            "SCH_FROM3" => $row->SCH_FROM3,
                            "SCH_FROM4" => $row->SCH_FROM4,
                            "SCH_TO1" => $row->SCH_TO1,
                            "SCH_TO2" => $row->SCH_TO2,
                            "SCH_TO3" => $row->SCH_TO3,
                            "SCH_TO4" => $row->SCH_TO4,
                            "SLOT_STATUS" => $book_sts,
                            "SLOTS" => [$slots]
                        ];
                        $counter++;
                    }
                }
                $startDate->addDay();
            }
        }

        usort($data, function ($item1, $item2) {
            return $item1['SCH_DT'] <=> $item2['SCH_DT'];
        });

        if (!empty($data) && $data[0]['SCH_DT'] === $cym) {
            $currentTime = Carbon::now();
            $times = [
                'SCH_TO1' => $data[0]['SCH_TO1'] ? Carbon::createFromFormat('h:i A', $data[0]['SCH_TO1']) : null,
                'SCH_TO2' => $data[0]['SCH_TO2'] ? Carbon::createFromFormat('h:i A', $data[0]['SCH_TO2']) : null,
                'SCH_TO3' => $data[0]['SCH_TO3'] ? Carbon::createFromFormat('h:i A', $data[0]['SCH_TO3']) : null,
                'SCH_TO4' => $data[0]['SCH_TO4'] ? Carbon::createFromFormat('h:i A', $data[0]['SCH_TO4']) : null,
            ];

            $allTimesPassed = true;

            foreach ($times as $time) {
                if ($time && $currentTime->lessThanOrEqualTo($time)) {
                    $allTimesPassed = false;
                    break;
                }
            }

            if ($allTimesPassed) {
                $data[0]['SLOT_STATUS'] = "Closed";
            }
        }

        $collection = collect($data);
        $firstAvailable = $collection->firstWhere('SLOT_STATUS', 'Available');

        if ($firstAvailable) {
            $firstAvailableIndex = $collection->search($firstAvailable);
            $schDT = array_slice($data, $firstAvailableIndex, 15);
        }

        return $schDT;
    }


    private function getSchDtSlot($fid, $apid, $apdt)
    {
        $cdt = date('Ymd');

        $data1 = DB::table('test_schedule')
            ->where(['PHARMA_ID' => $fid, 'ID' => $apid])
            ->get();

        $slots = [
            'Morning' => [],
            'Afternoon' => [],
            'Evening' => [],
            'Night' => []
        ];

        foreach ($data1 as $row) {
            $chkInTimes = [
                $row->SCH_FROM1,
                $row->SCH_FROM2,
                $row->SCH_FROM3,
                $row->SCH_FROM4
            ];
            $chkOutTimes = [
                $row->SCH_TO1,
                $row->SCH_TO2,
                $row->SCH_TO3,
                $row->SCH_TO4
            ];
            $maxBooks = [
                $row->MAX_BOOK1,
                $row->MAX_BOOK2,
                $row->MAX_BOOK3,
                $row->MAX_BOOK4
            ];

            foreach ($chkInTimes as $index => $chkin) {
                $chkout = $chkOutTimes[$index];
                $maxbook = $maxBooks[$index];
                if ($chkin === null) {
                    continue;
                }

                try {
                    $chkinTime = Carbon::createFromFormat('h:i A', $chkin);
                    $chkoutTime = Carbon::createFromFormat('h:i A', $chkout);
                } catch (\Exception $e) {
                    // Log::error("Error in time conversion: " . $e->getMessage());
                    return ["Error" => "Error in time conversion: " . $e->getMessage()];
                }

                $slot_sts = null;
                if ($cdt === $apdt) {
                    $slot_sts = $chkoutTime->lessThan(Carbon::now()) ? "Closed" : "Available";
                } else {
                    $slot_sts = "Available";
                }

                $slotString = [
                    "FROM" => $chkinTime->format('h:i A'),
                    "TO" => $chkoutTime !== null ? $chkoutTime->format('h:i A') : null,
                    "SLOT_STATUS" => $slot_sts
                ];

                if ($chkinTime->hour < 12) {
                    $slots['Morning'][] = $slotString;
                } elseif ($chkinTime->hour < 16) {
                    $slots['Afternoon'][] = $slotString;
                } elseif ($chkinTime->hour < 20) {
                    $slots['Evening'][] = $slotString;
                } else {
                    $slots['Night'][] = $slotString;
                }
            }
        }

        return $slots;
    }

    function vutestslot(Request $req)
    {
        if ($req->isMethod('post')) {
            date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();

            if (isset($input['PHARMA_ID']) && isset($input['SCH_ID']) && isset($input['SLOT_DT'])) {
                $phid = $input['PHARMA_ID'];
                $schid = $input['SCH_ID'];
                $slotdt = $input['SLOT_DT'];
                $cdt = date('Ymd');

                $data1 = DB::table('test_schedule')->where(['PHARMA_ID' => $phid, 'ID' => $schid])->first();

                if (!$data1) {
                    return response()->json(['Success' => false, 'Message' => 'Availability data not found.', 'code' => 404]);
                }



                $chkInTimes = [
                    $data1->SCH_FROM1,
                    $data1->SCH_FROM2,
                    $data1->SCH_FROM3,
                    $data1->SCH_FROM4
                ];
                $chkOutTimes = [
                    $data1->SCH_TO1,
                    $data1->SCH_TO2,
                    $data1->SCH_TO3,
                    $data1->SCH_TO4
                ];
                $maxbooks = [
                    $data1->MAX_BOOK1,
                    $data1->MAX_BOOK2,
                    $data1->MAX_BOOK3,
                    $data1->MAX_BOOK4
                ];
                $intvl = $data1->DURATION ?? null;
                if ($data1->DEPT_ID === 'D3') {
                    // Handle the case for DEPT_ID 'D3'
                    try {
                        $chkinTime = Carbon::createFromFormat('h:i A', $data1->SCH_FROM1);
                        $chkoutTime = Carbon::createFromFormat('h:i A', $data1->SCH_TO1);
                    } catch (\Exception $e) {
                        return response()->json(["Error" => "Error in time conversion: " . $e->getMessage()]);
                    }

                    $slot_sts = $cdt === $slotdt ? ($chkoutTime->lessThan(Carbon::now()) ? "Closed" : "Available") : "Available";

                    $slotString = [
                        "FROM" => $chkinTime->format('h:i A'),
                        "TO" => $chkoutTime->format('h:i A'),
                        "TOTAL" => null,
                        "AVAIL_BOOK" => null,
                        "BOOKING_SERIALS" => null,
                        "AVAILABLE_SERIALS" => null,
                        "SLOT_STATUS" => $slot_sts,
                    ];


                    $slots['SLOTS'][] = $slotString;
                } else {
                    $slots = [
                        'Morning' => [],
                        'Afternoon' => [],
                        'Evening' => [],
                        'Night' => []
                    ];
                    foreach ($chkInTimes as $index => $chkin) {
                        $maxbk = $maxbooks[$index];
                        $chkout = $chkOutTimes[$index];
                        if ($chkin === null) {
                            continue;
                        }

                        try {
                            $chkinTime = Carbon::createFromFormat('h:i A', $chkin);
                            $chkoutTime = $chkout !== null ? Carbon::createFromFormat('h:i A', $chkout) : $chkinTime->copy()->addMinutes($intvl * $maxbk);
                        } catch (\Exception $e) {
                            return response()->json(["Error" => "Error in time conversion: " . $e->getMessage()]);
                        }

                        $slot_sts = null;

                        while ($chkinTime->lessThan($chkoutTime)) {
                            $endSlot = $chkinTime->copy()->addMinutes($intvl);
                            if ($endSlot->greaterThan($chkoutTime)) {
                                break;
                            }

                            $bookedCount = DB::table('booktest')->where(['FROM' => $chkinTime->format('h:i A'), 'SLOT_DT' => $slotdt, 'SCH_ID' => $schid])->count();
                            // Log::info('Booked Count: ' . $bookedCount);

                            $totalAppointments = $data1->SLOT_TEST_CT;
                            $bookingSerials = range(1, $totalAppointments);
                            // $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));

                            if ($bookedCount > 0) {
                                $availableSerials = array_values(array_diff($bookingSerials, range(1, $bookedCount)));
                            } else {
                                $availableSerials = $bookingSerials;
                            }

                            if ($cdt === $slotdt) {
                                $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
                            } else {
                                $slot_sts = "Available";
                            }

                            $slotString = [
                                "FROM" => $chkinTime->format('h:i A'),
                                "TO" => $endSlot->format('h:i A'),
                                "TOTAL" => $totalAppointments,
                                "AVAIL_BOOK" => count($availableSerials),
                                "BOOKING_SERIALS" => $bookingSerials,
                                "AVAILABLE_SERIALS" => $availableSerials,
                                "SLOT_STATUS" => $slot_sts,
                            ];

                            if ($chkinTime->hour < 12) {
                                $slots['Morning'][] = $slotString;
                            } elseif ($chkinTime->hour < 16) {
                                $slots['Afternoon'][] = $slotString;
                            } elseif ($chkinTime->hour < 20) {
                                $slots['Evening'][] = $slotString;
                            } else {
                                $slots['Night'][] = $slotString;
                            }

                            $chkinTime->addMinutes($intvl);
                        }
                    }
                }



                $response = ['Success' => true, 'data' => $slots, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return response()->json($response);
    }

    public function edittestsch(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();
            $td = $input['EDIT_SCH'];

            foreach ($td as $row) {
                $fields = [
                    'DEPT_ID' => $row['DEPT_ID'],
                    'DEPARTMENT' => $row['DEPARTMENT'],
                    'SUB_DEPT_ID' => $row['SUB_DEPT_ID'] ?? null,
                    'SUB_DEPARTMENT' => $row['SUB_DEPARTMENT'] ?? null,
                    'SCH_DAY' => $row['SCH_DAY'],
                    'WEEK' => $row['WEEK'],
                    'SCH_START_DAY' => $row['SCH_START_DAY'],
                    'SCH_START_TIME' => $row['SCH_START_TIME'],
                    'SCH_FROM1' => $row['SCH_FROM1'],
                    'SCH_FROM2' => $row['SCH_FROM2'] ?? null,
                    'SCH_FROM3' => $row['SCH_FROM3'] ?? null,
                    'SCH_FROM4' => $row['SCH_FROM4'] ?? null,
                    'SCH_TO1' => $row['SCH_TO1'],
                    'SCH_TO2' => $row['SCH_TO2'] ?? null,
                    'SCH_TO3' => $row['SCH_TO3'] ?? null,
                    'SCH_TO4' => $row['SCH_TO4'] ?? null,
                    'MAX_BOOK1' => $row['MAX_BOOK1'] ?? null,
                    'MAX_BOOK2' => $row['MAX_BOOK2'] ?? null,
                    'MAX_BOOK3' => $row['MAX_BOOK3'] ?? null,
                    'MAX_BOOK4' => $row['MAX_BOOK4'] ?? null,
                    'DURATION' => $row['DURATION'] ?? null,
                    'SLOT_TEST_CT' => $row['SLOT_TEST_CT'] ?? null,
                    'SCH_STATUS' => $row['SCH_STATUS'] ?? null,
                ];
                for ($i = 1; $i <= 4; $i++) {
                    $maxBookCol = 'MAX_BOOK' . $i;
                    $chkInTimeCol = 'SCH_FROM' . $i;
                    $chkOutTimeCol = 'SCH_TO' . $i;

                    if ($fields[$maxBookCol] === null && !empty($row[$chkInTimeCol]) && !empty($row[$chkOutTimeCol] && $fields['DURATION'])) {
                        $chkinTime = Carbon::createFromFormat('h:i A', $row[$chkInTimeCol]);
                        $chkoutTime = Carbon::createFromFormat('h:i A', $row[$chkOutTimeCol]);
                        $minutesDiff = $chkinTime->diffInMinutes($chkoutTime, false);
                        $maxbook = ($minutesDiff / $fields['DURATION']) * $fields['SLOT_TEST_CT'];
                        $fields[$maxBookCol] = $maxbook;
                    }
                }

                try {
                    DB::table('test_schedule')->where('SCH_ID', $row['SCH_ID'])->update($fields);
                } catch (\Throwable $th) {
                    return response()->json(['Success' => false, 'Message' => $th->getMessage(), 'code' => 500], 500);
                }
            }
            return response()->json(['Success' => true, 'Message' => 'Test schedule modified successfully.', 'code' => 200], 200);
        }
        return response()->json(['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405], 405);
    }

    public function deltestsch(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['SCH_ID'])) {
                $sch_id = $input['SCH_ID'] ?? null;
                try {
                    DB::table('test_schedule')->where('ID', $sch_id)->delete();
                    $response = ['Success' => true, 'Message' => 'Test schedule deleted successfully.', 'code' => 200];
                } catch (\Throwable $th) {
                    $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 500];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return response()->json($response);
    }

    function clinicalldr(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {
                $pid = $input['PHARMA_ID'];

                $data = array();

                $doctors = DB::table('dr_availablity')
                    ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
                    ->distinct('dr_availablity.DR_ID')
                    ->select(
                        'dr_availablity.DR_ID',
                        'drprofile.DR_NAME',
                        'drprofile.DR_MOBILE',
                        'drprofile.SEX',
                        'drprofile.DESIGNATION',
                        'drprofile.QUALIFICATION',
                        'drprofile.D_CATG',
                        'drprofile.EXPERIENCE',
                        'drprofile.LANGUAGE',
                        'drprofile.PHOTO_URL AS DR_PHOTO',
                        'dr_availablity.PHARMA_ID',
                    )
                    ->where(['dr_availablity.PHARMA_ID' => $pid])
                    ->get();

                $approved_doctors = DB::table('dr_availablity')
                    ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
                    ->distinct('drprofile.DR_ID')
                    ->select(
                        'dr_availablity.DR_ID',
                        'drprofile.DR_NAME',
                        'drprofile.DR_MOBILE',
                        'drprofile.SEX',
                        'drprofile.DESIGNATION',
                        'drprofile.QUALIFICATION',
                        'drprofile.D_CATG',
                        'drprofile.EXPERIENCE',
                        'drprofile.LANGUAGE',
                        'drprofile.PHOTO_URL AS DR_PHOTO',
                        'dr_availablity.PHARMA_ID',
                        'drprofile.APPROVE'
                    )
                    ->where(['dr_availablity.PHARMA_ID' => $pid, 'drprofile.APPROVE' => 'true'])
                    ->get();

                $non_approved_doctors = DB::table('dr_availablity')
                    ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
                    ->distinct('drprofile.DR_ID')
                    ->select(
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
                        'dr_availablity.PHARMA_ID',
                        'drprofile.APPROVE'
                    )
                    ->where(['dr_availablity.PHARMA_ID' => $pid, 'drprofile.APPROVE' => 'false'])
                    ->get();

                // Count of doctors
                $doctor_count = $doctors->count();
                $approve_count = $approved_doctors->count();
                $notapprove_count = $non_approved_doctors->count();

                // Create an object with all doctors' data and count
                $total_doctors = [
                    'All_dr' => $doctor_count,
                    'Approve_dr' => $approve_count,
                    'Not_Approved_dr' => $notapprove_count,
                    'Approve' => $approved_doctors,
                    'Not_Approve' => $non_approved_doctors
                ];

                // Add the object to the 0th index of the data array
                $data[0] = $total_doctors;

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return response()->json($response);
    }

    public function clinicsignup(Request $req)
    {
        if ($req->isMethod('post')) {
            date_default_timezone_set('Asia/Kolkata');
            $row = $req->all();
            $cdt = date('Ymd');
            if (
                isset($row['REGIST_NO']) && isset($row['CLINIC_TYPE']) && isset($row['PHARMA_NAME']) && isset($row['ADDRESS']) && isset($row['STATE']) && isset($row['DISTRICT']) &&
                isset($row['CITY']) && isset($row['PIN']) && isset($row['CLINIC_MOBILE'])
            ) {
                $response = array();
                $ho_id = strtoupper(substr(md5($row['REGIST_NO']), 0, 5));

                $brnch_cd = DB::table('pharmacy')->select('ID', 'PHARMA_ID')->where(['HO_ID' => $ho_id, 'STATE' => $row['STATE'], 'DIST' => $row['DISTRICT'], 'CITY' => $row['CITY']])->orderBy('ID', 'desc')->first();
                if (!$brnch_cd) {
                    $brnch_value = '01';
                } else {
                    $last_id = $brnch_cd->PHARMA_ID;
                    $brnch_value = str_pad((int) $last_id + 2, 2, '0', STR_PAD_LEFT);
                }
                // RETURN $brnch_value;
                $pharma_id = "E" . $ho_id . $row['STATE_CODE'] . $row['DIST_CODE'] . $row['CITY_CODE'] . $brnch_value;
                // RETURN $pharma_id ;
                if ($req->file('PHOTO_URL')) {
                    $PhotoL = $pharma_id . "PL." . $req->file('PHOTO_URL')->getClientOriginalExtension();
                    $req->file('PHOTO_URL')->storeAs('clinicprofile/clinicphoto', $PhotoL);
                    $PHOTO_URL = asset('storage/app/clinicprofile/clinicphoto') . "/" . $PhotoL;
                } else {
                    $PHOTO_URL = null;
                }

                if ($req->file('PHOTO_URL1')) {
                    $PhotoP = $pharma_id . "PP." . $req->file('PHOTO_URL1')->getClientOriginalExtension();
                    $req->file('PHOTO_URL1')->storeAs('clinicprofile/clinicphoto', $PhotoP);
                    $PHOTO_URL1 = asset('storage/app/clinicprofile/clinicphoto') . "/" . $PhotoP;
                } else {
                    $PHOTO_URL1 = null;
                }

                if ($req->file('LOGO_URL')) {
                    $logo = $pharma_id . "L." . $req->file('LOGO_URL')->getClientOriginalExtension();
                    $req->file('LOGO_URL')->storeAs('clinicprofile/cliniclogo', $logo);
                    $LOGO_URL = asset('storage/app/clinicprofile/cliniclogo') . "/" . $logo;
                } else {
                    $LOGO_URL = null;
                }

                if ($req->file('GST_DOC_URL')) {
                    $gst = $pharma_id . "GST." . $req->file('GST_DOC_URL')->getClientOriginalExtension();
                    $req->file('GST_DOC_URL')->storeAs('clinicprofile/clinicdoc', $gst);
                    $GST_DOC_URL = asset('storage/app/clinicprofile/clinicdoc') . "/" . $gst;
                } else {
                    $GST_DOC_URL = null;
                }

                if ($req->file('TRADE_DOC_URL')) {
                    $trd = $pharma_id . "TRD." . $req->file('TRADE_DOC_URL')->getClientOriginalExtension();
                    $req->file('TRADE_DOC_URL')->storeAs('clinicprofile/clinicdoc', $trd);
                    $TRADE_DOC_URL = asset('storage/app/clinicprofile/clinicdoc') . "/" . $trd;
                } else {
                    $TRADE_DOC_URL = null;
                }

                $fields = [
                    'PHARMA_ID' => $pharma_id,
                    'HO_ID' => $ho_id,
                    'ITEM_NAME' => $row['PHARMA_NAME'],
                    'CLINIC_TYPE' => $row['CLINIC_TYPE'],
                    'ADDRESS' => $row['ADDRESS'],
                    'CITY' => $row['CITY'],
                    'PIN' => $row['PIN'],
                    'DIST' => $row['DISTRICT'],
                    'EMAIL' => $row['EMAIL'] ?? null,
                    'STATE' => $row['STATE'],
                    'CLINIC_MOBILE' => $row['CLINIC_MOBILE'],
                    'LATITUDE' => $row['LATITUDE'] ?? null,
                    'LONGITUDE' => $row['LONGITUDE'] ?? null,
                    'PHOTO_URL' => $PHOTO_URL,
                    'PHOTO_URL1' => $PHOTO_URL1,
                    'LOGO_URL' => $LOGO_URL,
                    'REGIST_NO' => $row['REGIST_NO'],
                    'GST_NO' => $row['GST_NO'] ?? null,
                    'TRADE_LIS_NO' => $row['TRADE_LIS_NO'] ?? null,
                    'GST_DOC_URL' => $GST_DOC_URL,
                    'TRADE_DOC_URL' => $TRADE_DOC_URL,
                    'USERID' => $row['CLINIC_MOBILE'],
                    'PASSWORD' => md5('1234'),
                    'CHEMBER_CT' => $row['CHEMBER_CT'] ?? null,
                ];

                $fields1 = [
                    'CLIENT_ID' => $pharma_id,
                    'CLIENT_TYPE' => $row['CLINIC_TYPE'],
                    'NAME' => $row['PHARMA_NAME'],
                    'SUBS_DT' => $cdt,
                    'VALID_TO' => '99991231',
                    'USER_ID' => $row['CLINIC_MOBILE'],
                    'PASSWORD' => md5('1234'),
                    // 'TARIFF' => $row['TARIFF'],
                ];

                try {
                    DB::table('client')->insert($fields1);
                    DB::table('pharmacy')->insert($fields);

                    $response = array("Success" => true, "Message" => 'Register successfully', "code" => 200);
                } catch (\Throwable $th) {
                    $response = array("Success" => false, "Message" => 'You are already registered', "code" => 200);
                }
            } else {
                $response = array("Success" => false, "Message" => "Invalid Parameter", "code" => 422);
            }
        } else {
            $response = array("Success" => false, "Message" => "Method not allowed", "code" => 405);
        }
        return response()->json($response);
    }

    public function drsignup(Request $req)
    {
        if ($req->isMethod('post')) {
            date_default_timezone_set('Asia/Kolkata');
            $row = $req->json()->all();
            $cdt = date('Ymd');
            $response = array();

            if (isset($row['DR_ID']) && isset($row['DR_NAME']) && isset($row['DR_MOBILE']) && isset($row['CLIENT_TYPE'])) {
                $fields = [
                    'CLIENT_ID' => $row['DR_ID'],
                    'CLIENT_TYPE' => $row['CLIENT_TYPE'],
                    'NAME' => $row['DR_NAME'],
                    'SUBS_DT' => $cdt,
                    'VALID_TO' => '99991231',
                    'USER_ID' => $row['DR_MOBILE'],
                    'PASSWORD' => md5('1234'),
                    // 'TARIFF' => $row['TARIFF'],
                ];

                try {
                    DB::table('client')->insert($fields);
                    $response = array("Success" => true, "Message" => 'Registered successfully', "code" => 200);
                } catch (\Throwable $th) {
                    $response = array("Success" => false, "Message" => 'You are already registered', "code" => 200);
                }
            } else {
                $response = array("Success" => false, "Message" => "Invalid Parameter", "code" => 422);
            }
        } else {
            $response = array("Success" => false, "Message" => "Method not allowed", "code" => 405);
        }
        return response()->json($response);
    }


    public function uploadgallery(Request $req)
    {
        if ($req->isMethod('POST')) {
            $row = $req->all();

            if (isset($row['PHARMA_ID'])) {
                $response = array();
                $pharma_id = $row['PHARMA_ID'];

                $photo_urls = [];
                // Handle GLIMGs
                for ($i = 1; $i <= 10; $i++) {
                    $file_key = 'GLIMG' . $i;
                    if ($req->file($file_key)) {
                        $photo_name = $pharma_id . "G" . $i . "." . $req->file($file_key)->getClientOriginalExtension();
                        $req->file($file_key)->storeAs('clinicprofile/clinicgallery', $photo_name);
                        $photo_urls[$file_key] = asset('storage/app/clinicprofile/clinicgallery') . "/" . $photo_name;
                    } else {
                        $photo_urls[$file_key] = null;
                    }
                }

                // Handle SLIMGs
                for ($i = 1; $i <= 4; $i++) {
                    $file_key = 'SLDIMG' . $i;
                    if ($req->file($file_key)) {
                        $photo_name = $pharma_id . "SL" . $i . "." . $req->file($file_key)->getClientOriginalExtension();
                        $req->file($file_key)->storeAs('clinicprofile/clinicslider', $photo_name);
                        $photo_urls[$file_key] = asset('storage/app/clinicprofile/clinicslider') . "/" . $photo_name;
                    } else {
                        $photo_urls[$file_key] = null;
                    }
                }

                $fields = [];
                foreach ($photo_urls as $key => $url) {
                    if (!is_null($url)) {
                        $fields[$key] = $url;
                    }
                }

                if (!empty($fields)) {
                    DB::table('pharmacy')->where('PHARMA_ID', $pharma_id)->update($fields);
                    $user = DB::table('pharmacy')
                        ->select(
                            'PHARMA_ID',
                            'USERID',
                            'ITEM_NAME AS PHARMA_NAME',
                            'CLINIC_MOBILE',
                            'ADDRESS',
                            'CITY',
                            'DIST',
                            'PIN',
                            'STATE',
                            'PHOTO_URL',
                            'LOGO_URL',
                            'CLINIC_TYPE',
                            'LATITUDE',
                            'LONGITUDE',
                            'CHEMBER_CT',
                            'GLIMG1 AS PHOTO_URL1',
                            'GLIMG2 AS PHOTO_URL2',
                            'GLIMG3 AS PHOTO_URL3',
                            'GLIMG4 AS PHOTO_URL4',
                            'GLIMG5 AS PHOTO_URL5',
                            'GLIMG6 AS PHOTO_URL6',
                            'GLIMG7 AS PHOTO_URL7',
                            'GLIMG8 AS PHOTO_URL8',
                            'GLIMG9 AS PHOTO_URL9',
                            'GLIMG10 AS PHOTO_URL10',
                            'SLDIMG1 AS SLIDER_IMG1',
                            'SLDIMG2 AS SLIDER_IMG2',
                            'SLDIMG3 AS SLIDER_IMG3',
                            'SLDIMG4 AS SLIDER_IMG4'
                        )
                        ->where('PHARMA_ID', $pharma_id)
                        ->first();

                    if ($user != null) {
                        $data = [
                            "PHARMA_ID" => $user->PHARMA_ID,
                            "PHARMA_NAME" => $user->PHARMA_NAME,
                            "CLINIC_MOBILE" => $user->CLINIC_MOBILE,
                            "ADDRESS" => $user->ADDRESS,
                            "CITY" => $user->CITY,
                            "DIST" => $user->DIST,
                            "PIN" => $user->PIN,
                            "STATE" => $user->STATE,
                            "PHOTO_URL" => $user->PHOTO_URL,
                            "LOGO_URL" => $user->LOGO_URL,
                            "CLINIC_TYPE" => $user->CLINIC_TYPE,
                            "LATITUDE" => $user->LATITUDE,
                            "LONGITUDE" => $user->LONGITUDE,
                            "CHEMBER_CT" => $user->CHEMBER_CT,
                            "USERID" => $user->USERID,
                            'PHOTO_URL1' => $user->PHOTO_URL1,
                            'PHOTO_URL2' => $user->PHOTO_URL2,
                            'PHOTO_URL3' => $user->PHOTO_URL3,
                            'PHOTO_URL4' => $user->PHOTO_URL4,
                            'PHOTO_URL5' => $user->PHOTO_URL5,
                            'PHOTO_URL6' => $user->PHOTO_URL6,
                            'PHOTO_URL7' => $user->PHOTO_URL7,
                            'PHOTO_URL8' => $user->PHOTO_URL8,
                            'PHOTO_URL9' => $user->PHOTO_URL9,
                            'PHOTO_URL10' => $user->PHOTO_URL10,
                            'SLIDER1' => $user->SLIDER_IMG1,
                            'SLIDER2' => $user->SLIDER_IMG2,
                            'SLIDER3' => $user->SLIDER_IMG3,
                            'SLIDER4' => $user->SLIDER_IMG4,
                        ];
                    }
                    $response = array("Success" => true, "data" => $data, "Message" => 'Photos uploaded and updated successfully', "code" => 200);
                } else {
                    $response = array("Success" => false, "Message" => 'No photos uploaded', "code" => 422);
                }
            } else {
                $response = array("Success" => false, "Message" => "Invalid Parameter", "code" => 422);
            }
        } else {
            $response = array("Success" => false, "Message" => "Method not allowed", "code" => 405);
        }
        return response()->json($response);
    }



    function srchcity(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $input = $req->json()->all();
            // $headers = apache_request_headers();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {

            if (isset($input['PIN'])) {
                $pin = $input['PIN'];

                $response = array();
                $data = array();


                $data = DB::table('city')->where('PIN', $pin)->get();
                if ($data != null) {
                    $response = ['Success' => true, 'data' => $data, 'code' => 200];
                } else {
                    $response = ['Success' => false, 'Message' => 'Invalid DR ID', 'code' => 200];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid Parameter', 'code' => 422];
            }
            // } else {
            //     $response = ['Success' => false, 'Message' => 'You are not Authorized', 'code' => 401];
            // }
        } else {
            $response = ['Success' => false, 'Message' => 'Method not allowed', 'code' => 200];
        }
        return $response;
    }

    public function drsummary(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();

            if (isset($input['DR_ID']) && isset($input['SCH_MONTH'])) {
                $doctorId = $input['DR_ID'];
                $ym = $input['SCH_MONTH'];
                $cym = Carbon::now()->format('Ym');
                $today = Carbon::now()->format('Ymd');

                $overallSummary = DB::table('appointment')
                    ->select(
                        DB::raw('COUNT(DISTINCT PATIENT_ID) as TOTAL_PATIENT'),
                        DB::raw('SUM(CASE WHEN STATUS = "Visited" THEN DR_FEES ELSE 0 END) as TOTAL_FEES'),
                        DB::raw('COUNT(CASE WHEN STATUS = "Visited" THEN 1 ELSE NULL END) as TOTAL_VISIT'),
                        DB::raw('COUNT(DISTINCT PHARMA_ID) as TOTAL_CHEMBER')
                    )
                    ->where('DR_ID', $doctorId)
                    ->where(function ($query) use ($ym, $cym, $today) {
                        if ($ym === $cym) {
                            $query->where('APPNT_DT', 'like', '%' . $ym . '%')
                                ->where('APPNT_DT', '<=', $today);
                        } else {
                            $query->where('APPNT_DT', 'like', '%' . $ym . '%');
                        }
                    })
                    ->first();

                $dailySummary = DB::table('appointment')
                    ->select(
                        'APPNT_DT',
                        DB::raw('COUNT(DISTINCT PATIENT_ID) as TOTAL_PATIENT'),
                        DB::raw('SUM(CASE WHEN STATUS = "Visited" THEN DR_FEES ELSE 0 END) as TOTAL_FEES'),
                        DB::raw('COUNT(CASE WHEN STATUS = "Visited" THEN 1 ELSE NULL END) as TOTAL_VISIT'),
                        DB::raw('COUNT(DISTINCT PHARMA_ID) as TOTAL_CHEMBER')
                    )
                    ->where('DR_ID', $doctorId)
                    ->where(function ($query) use ($ym, $cym, $today) {
                        if ($ym === $cym) {
                            $query->where('APPNT_DT', 'like', '%' . $ym . '%')
                                ->where('APPNT_DT', '<=', $today);
                        } else {
                            $query->where('APPNT_DT', 'like', '%' . $ym . '%');
                        }
                    })
                    ->groupBy('APPNT_DT')
                    ->get();

                $response = [
                    'SUMMARY' => [
                        'TOTAL_PATIENT' => $overallSummary->TOTAL_PATIENT,
                        'TOTAL_VISIT' => $overallSummary->TOTAL_VISIT,
                        'TOTAL_FEES' => $overallSummary->TOTAL_FEES,
                        'TOTAL_CHEMBER' => $overallSummary->TOTAL_CHEMBER,
                    ],
                    'DAILY_SUMMARY' => $dailySummary
                ];

                return response()->json($response);
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }

        return response()->json($response);
    }

    public function drdaysummary(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();

            if (isset($input['DR_ID']) && isset($input['APPNT_DT'])) {
                $doctorId = $input['DR_ID'];
                $apntdt = $input['APPNT_DT'];

                $overallSummary = DB::table('appointment')
                    ->select(
                        DB::raw('COUNT(DISTINCT PATIENT_ID) as TOTAL_PATIENT'),
                        DB::raw('SUM(CASE WHEN STATUS = "Visited" THEN DR_FEES ELSE 0 END) as TOTAL_FEES'),
                        DB::raw('COUNT(CASE WHEN STATUS = "Visited" THEN 1 ELSE NULL END) as TOTAL_VISIT'),
                        DB::raw('COUNT(DISTINCT PHARMA_ID) as TOTAL_CHEMBER')
                    )
                    ->where(['DR_ID' => $doctorId, 'APPNT_DT' => $apntdt])
                    ->first();

                $clinicSummary = DB::table('appointment')
                    ->join('pharmacy', 'appointment.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
                    ->select(
                        'appointment.DR_ID',
                        'pharmacy.PHARMA_ID',
                        'pharmacy.ITEM_NAME AS PHARMA_NAME',
                        'pharmacy.ADDRESS',
                        'pharmacy.CITY',
                        'pharmacy.DIST',
                        'pharmacy.STATE',
                        'pharmacy.PIN',
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        DB::raw('(SELECT COUNT(DISTINCT a.PATIENT_ID) FROM appointment a WHERE a.DR_ID = appointment.DR_ID AND a.PHARMA_ID = appointment.PHARMA_ID) as TOTAL_PATIENT'),
                        DB::raw('(SELECT SUM(CASE WHEN a.STATUS = "Visited" THEN a.DR_FEES ELSE 0 END) FROM appointment a WHERE a.DR_ID = appointment.DR_ID AND a.PHARMA_ID = appointment.PHARMA_ID) as TOTAL_FEES'),
                        DB::raw('(SELECT COUNT(CASE WHEN a.STATUS = "Visited" THEN 1 ELSE NULL END) FROM appointment a WHERE a.DR_ID = appointment.DR_ID AND a.PHARMA_ID = appointment.PHARMA_ID) as TOTAL_VISIT'),
                        DB::raw('(SELECT COUNT(DISTINCT a.PHARMA_ID) FROM appointment a WHERE a.DR_ID = appointment.DR_ID AND a.PHARMA_ID = appointment.PHARMA_ID) as TOTAL_CHEMBER')
                    )
                    ->where('appointment.DR_ID', $doctorId)
                    ->distinct()
                    ->get();


                $response = [
                    'SUMMARY' => [
                        'TOTAL_PATIENT' => $overallSummary->TOTAL_PATIENT,
                        'TOTAL_VISIT' => $overallSummary->TOTAL_VISIT,
                        'TOTAL_FEES' => $overallSummary->TOTAL_FEES,
                        'TOTAL_CHEMBER' => $overallSummary->TOTAL_CHEMBER,
                    ],
                    'CLINIC_SUMMARY' => $clinicSummary
                ];

                return response()->json($response);
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }

        return response()->json($response);
    }


    public function vu_superfacility(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();

            if (isset($input['PHARMA_ID'])) {
                $pharmaId = $input['PHARMA_ID'];

                $data1 = DB::table('facility_section')
                    ->join('facility_type', function ($join) {
                        $join->on('facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                            ->whereIn('facility_type.DASH_SECTION_ID', ['SR', 'SP', 'TU']);
                    })
                    ->join('facility', function ($join) {
                        $join->on('facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID');
                    })
                    ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId) {
                        $join->on('facility.DASH_ID', '=', 'hospital_facilities_details.DASH_ID')
                            ->where('hospital_facilities_details.REMARK', 'true')
                            ->where('hospital_facilities_details.PHARMA_ID', $pharmaId);
                    })
                    ->select([
                        'facility_section.DASH_SECTION_ID',
                        'facility_section.DASH_SECTION_NAME',
                        'facility_section.DS_DESCRIPTION',
                        'facility_section.DSIMG1',
                        'facility_section.DSIMG2',
                        'facility_section.DSIMG3',
                        'facility_section.DSIMG4',
                        'facility_section.DSIMG5',
                        'facility_section.DSIMG6',
                        'facility_section.DSIMG7',
                        'facility_section.DSIMG8',
                        'facility_section.DSIMG9',
                        'facility_section.DSIMG10',
                        'hospital_facilities_details.REMARK'
                    ])
                    ->where('hospital_facilities_details.REMARK', 'true')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    $sectionId = $row->DASH_SECTION_ID;

                    if (!isset($groupedData[$sectionId])) {
                        $groupedData[$sectionId] = [
                            "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                            "DESCRIPTION" => $row->DS_DESCRIPTION,
                            "PHOTO_URL1" => $row->DSIMG1,
                            "PHOTO_URL2" => $row->DSIMG2,
                            "PHOTO_URL3" => $row->DSIMG3,
                            "PHOTO_URL4" => $row->DSIMG4,
                            "PHOTO_URL5" => $row->DSIMG5,
                            "PHOTO_URL6" => $row->DSIMG6,
                            "PHOTO_URL7" => $row->DSIMG7,
                            "PHOTO_URL8" => $row->DSIMG8,
                            "PHOTO_URL9" => $row->DSIMG9,
                            "PHOTO_URL10" => $row->DSIMG10,
                        ];
                    }
                }

                $data = \array_values($groupedData);
                $response = $data ?
                    ['Success' => true, 'data' => $data, 'code' => 200] :
                    ['Success' => false, 'Message' => 'Record not found', 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }
        return response()->json($response);
    }

    public function vu_pharma_superfacility(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $dsid = $input['DASH_SECTION_ID'];

            // Initial query to get data from facility_section and related tables
            $query = DB::table('facility_section')
                ->join('facility_type', function ($join) use ($dsid) {
                    $join->on('facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                        ->where('facility_type.DASH_SECTION_ID', $dsid);
                })
                ->join('facility', function ($join) use ($dsid) {
                    $join->on('facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                        ->where('facility_type.DASH_SECTION_ID', $dsid);
                })
                ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId) {
                    $join->on('facility.DASH_ID', '=', 'hospital_facilities_details.DASH_ID')
                        ->where('hospital_facilities_details.REMARK', 'true')
                        ->where('hospital_facilities_details.PHARMA_ID', $pharmaId);
                })

                ->where(['facility_type.DT_STATUS' => 'Active', 'facility_section.DS_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                ->where('hospital_facilities_details.REMARK', 'true')
                ->select(
                    'facility_section.DASH_SECTION_ID',
                    'facility_section.DASH_SECTION_NAME',
                    'facility_section.DS_DESCRIPTION',

                    'facility_type.DASH_TYPE_ID',
                    'facility_type.DIS_ID',
                    'facility_type.DASH_TYPE',
                    'facility_type.DT_DESCRIPTION',
                    'facility_type.DTIMG1',

                    'facility.DASH_ID',
                    'facility.DASH_NAME',
                    'facility.DN_DESCRIPTION',
                    'facility.DNIMG1',

                    'hospital_facilities_details.UID',
                    'hospital_facilities_details.TOT_BED',
                    'hospital_facilities_details.AVAIL_BED',
                    'hospital_facilities_details.PRICE_FROM',
                    'hospital_facilities_details.DEPT_PH',
                    'hospital_facilities_details.SHORT_NOTE',
                    'hospital_facilities_details.SND_OPINION',
                    'hospital_facilities_details.FREE_AREA',
                    'hospital_facilities_details.FREE_FROM',
                    'hospital_facilities_details.FREE_TO',
                    'hospital_facilities_details.SERV_AREA',
                    'hospital_facilities_details.SERV_FROM',
                    'hospital_facilities_details.SERV_TO',
                    'hospital_facilities_details.DLV_TM',
                    'hospital_facilities_details.MIN_ODR',
                    'hospital_facilities_details.SERV_24X7',
                    'hospital_facilities_details.SERV_HOME',
                    'hospital_facilities_details.SERV_IP',
                    'hospital_facilities_details.TAG_NOTE',
                    'hospital_facilities_details.TREATMENTS',
                    'hospital_facilities_details.SUPER_SPLTY',
                    'hospital_facilities_details.DISCOUNT',
                    'hospital_facilities_details.CASH_LESS',
                    'hospital_facilities_details.CASH_PAID',
                    'hospital_facilities_details.IMAGE1_URL',
                    'hospital_facilities_details.IMAGE2_URL',
                    'hospital_facilities_details.IMAGE3_URL',
                    'hospital_facilities_details.REMARK'
                );
            $data1 = $query->get();

            $groupedData = [];
            foreach ($data1 as $row) {
                $typeKey = $row->DASH_TYPE_ID;

                if (!isset($groupedData[$typeKey])) {

                    $groupedData[$typeKey] = [
                        "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                        "DASH_TYPE_ID" => $row->DASH_TYPE_ID,
                        "DIS_ID" => $row->DIS_ID,
                        "DASH_TYPE" => $row->DASH_TYPE,
                        "DESCRIPTION" => $row->DS_DESCRIPTION,
                        "PHOTO_URL" => $row->DTIMG1,
                        "FACILITY" => []
                    ];
                }

                $facility = [
                    "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                    "DIS_ID" => $row->DIS_ID,
                    "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                    "UID" => $row->UID,
                    "DASH_ID" => $row->DASH_ID,
                    "DASH_TYPE_ID" => $row->DASH_TYPE_ID,
                    "DASH_TYPE" => $row->DASH_TYPE,
                    "DASH_NAME" => $row->DASH_NAME,
                    "DESCRIPTION" => $row->DN_DESCRIPTION,
                    "TOT_BED" => $row->TOT_BED,
                    "AVAIL_BED" => $row->AVAIL_BED,
                    "PRICE_FROM" => $row->PRICE_FROM,
                    "DEPT_PH" => $row->DEPT_PH,
                    "SHORT_NOTE" => $row->SHORT_NOTE,
                    "SND_OPINION" => $row->SND_OPINION,
                    "FREE_AREA" => $row->FREE_AREA,
                    "FREE_FROM" => $row->FREE_FROM,
                    "FREE_TO" => $row->FREE_TO,
                    "SERV_AREA" => $row->SERV_AREA,
                    "SERV_FROM" => $row->SERV_FROM,
                    "SERV_TO" => $row->SERV_TO,
                    "DLV_TM" => $row->DLV_TM,
                    "MIN_ODR" => $row->MIN_ODR,
                    "SERV_24X7" => $row->SERV_24X7,
                    "SERV_HOME" => $row->SERV_HOME,
                    "SERV_IP" => $row->SERV_IP,
                    "TAG_NOTE" => $row->TAG_NOTE,
                    "TREATMENTS" => $row->TREATMENTS,
                    "SUPER_SPLTY" => $row->SUPER_SPLTY,
                    "DISCOUNT" => $row->DISCOUNT,
                    "CASH_LESS" => $row->CASH_LESS,
                    "CASH_PAID" => $row->CASH_PAID,
                    "IMAGE1_URL" => $row->IMAGE1_URL,
                    "IMAGE2_URL" => $row->IMAGE2_URL,
                    "IMAGE3_URL" => $row->IMAGE3_URL,
                    "REMARK" => $row->REMARK,
                    "PHOTO_URL" => $row->DNIMG1,
                ];

                $groupedData[$typeKey]['FACILITY'][] = $facility;
            }

            $data = array_values($groupedData);

            if (empty($data)) {
                return response()->json(['Success' => false, 'Message' => 'Record not found', 'code' => 404], 404);
            } else {
                return response()->json(['Success' => true, 'data' => $data, 'code' => 200], 200);
            }
        } else {
            return response()->json(['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405], 405);
        }
    }

    function add_superspeciality(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ], 405);
        }
        $input = $req->json()->all();
        DB::table('hospital_facilities_details')->where('UID', $input['UID'])->update(['SUPER_SPLTY' => $input['SUPER_SPLTY']]);
        $response = [
            'Success' => true,
            'Message' => 'Facilities update successfully.',
            'SUPER_SPLTY' => 'true',
            'code' => 200
        ];

        return $response;
    }
}
