<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Session;
use DateTime;

class UserController extends Controller
{
    function usersignup(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (
                isset($input['NAME']) && isset($input['MOBILE']) && isset($input['PASSWORD'])
            ) {
                $response = array();

                $name = $input['NAME'];
                $mpin = md5($input['PASSWORD']);
                $mobile = $input['MOBILE'];

                try {
                    $sql1 = "INSERT INTO `users`(`name`, `mobile`,`password`, `user_type`) VALUES ('$name','$mobile','$mpin','User')";
                    DB::insert($sql1);
                } catch (\Throwable $th) {
                    $response = ['Success' => false, 'Message' => 'You are already registered.', 'code' => 200];
                    return $response;
                }
                $sql = "INSERT INTO `user_family`(`FAMILY_ID`, `NAME`,`MOBILE`,`RELATION`) VALUES ('$mobile','$name','$mobile','Self')";
                DB::insert($sql);
                $response = ['Success' => true, 'Message' => 'Registered successfgully.', 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid parameter.', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }

    function login(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            session_start();
            $response = array();
            $data = array();
            $input = $request->json()->all();
            if (isset($input['Mobile']) && isset($input['Password'])) {
                $user = DB::table('users')
                    ->join('user_family', 'users.mobile', '=', 'user_family.FAMILY_ID')
                    ->select('users.mobile', 'user_family.ID', 'users.name', 'users.user_type', 'users.password')
                    ->where('users.mobile', $request->input('Mobile'))
                    ->whereIn('users.user_type', ['User', 'Admin'])
                    ->where('user_family.RELATION', 'Self')
                    ->first();

                if ($user != null) {
                    if (($user->password) === md5($request->input('Password'))) {

                        //$fname = explode(" ", $user->name);
                        $data = [
                            'mobile' => $request->input('Mobile'),
                            'id' => $user->ID,
                            'name' => $user->name,
                            'currdt' => date('d/m/Y')
                        ];
                        $token = base64_encode($request->input('Mobile') . $user->password . $user->name . $user->user_type);
                        // $token = $request->input('androidId');
                        // if ($user->token != $token) {
                        //     DB::update("update users set token='$token' where mobile='$m'");
                        // }
                        // Session::put('key', $token);
                        //$token = $request->input('androidId');
                        $_SESSION['TOKEN'] = $token;

                        $response = [
                            'Success' => true,
                            "data" => $data,
                            'Message' => 'Login Successfully',
                            'User_Type' => $user->user_type,
                            'Token' => $token,
                            'status' => 200
                        ];
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

    function srchdr(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $data = DB::table('pharmacy')
                    ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
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
                        'drprofile.PHOTO_URL as DR_PHOTO',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                    * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                    * SIN(RADIANS('$latt'))))),2) as KM"),
                    )
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->orderbyraw('KM')
                    ->orderby('drprofile.EXPERIENCE')
                    ->take(25)
                    ->get();

                if ($data == null) {
                    $response = ['Success' => true, 'Message' => 'Doctor not found', 'data' => $data];
                } else {
                    $response = ['Success' => true, 'data' => $data];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function catgdr(Request $request)
    {
        if ($request->isMethod('post')) {
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();
            if (isset($input['DIS_ID']) && isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $did = $input['DIS_ID'];
                $data = collect();

                $data = $data->merge($this->getCatgDrDt($latt, $lont, $did));
                $data = $data->merge($this->getClinic($latt, $lont, $did));

                $fxd_banner = DB::table('dis_catg')->select(
                    'DIS_ID AS BANNER_ID',
                    'SPECIALITY AS BANNER_NAME',
                    'DIS_ID',
                    'DIS_DESC AS DESCRIPTION',
                    'DISBNR1 AS BANNER_URL1',
                    'DISBNR2 AS BANNER_URL2',
                    'DISBNR3 AS BANNER_URL3',
                    'DISBNR4 AS BANNER_URL4',
                    'DISBNR5 AS BANNER_URL5',
                    'DISBNR6 AS BANNER_URL6',
                    'DISBNR7 AS BANNER_URL7',
                    'DISBNR8 AS BANNER_URL8',
                    'DISBNR9 AS BANNER_URL9',
                    'DISBNR10 AS BANNER_URL10',
                    'DISQA1 AS QA1',
                    'DISQA2 AS QA2',
                    'DISQA3 AS QA3',
                    'DISQA4 AS QA4'
                )->get();

                // Check if the fetched data is not empty and is an array of objects
                if ($fxd_banner->isEmpty()) {
                    throw new \Exception("No data found in dis_catg table.");
                }

                $fxd_banner = $fxd_banner->map(function ($item) {
                    return [
                        'BANNER_ID' => $item->BANNER_ID,
                        'BANNER_NAME' => $item->BANNER_NAME,
                        'DIS_ID' => $item->DIS_ID,
                        'DESCRIPTION' => $item->DESCRIPTION,
                        'BANNER_URL1' => $item->BANNER_URL1,
                        'BANNER_URL2' => $item->BANNER_URL2,
                        'BANNER_URL3' => $item->BANNER_URL3,
                        'BANNER_URL4' => $item->BANNER_URL4,
                        'BANNER_URL5' => $item->BANNER_URL5,
                        'BANNER_URL6' => $item->BANNER_URL6,
                        'BANNER_URL7' => $item->BANNER_URL7,
                        'BANNER_URL8' => $item->BANNER_URL8,
                        'BANNER_URL9' => $item->BANNER_URL9,
                        'BANNER_URL10' => $item->BANNER_URL10,
                        'Questions' => [
                            [
                                'QA1' => $item->QA1,
                                'QA2' => $item->QA2,
                                'QA3' => $item->QA3,
                                'QA4' => $item->QA4
                            ]
                        ]
                    ];
                });

                $promo_banner = DB::table('promo_banner')
                    ->select('PROMO_ID', 'DASH_SECTION_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', 'SP')->get();

                // Debugging check
                if ($promo_banner->isEmpty()) {
                    throw new \Exception("No data found in promo_banner table.");
                }

                // Ensure $did is defined and has a value
                if (!isset($did)) {
                    throw new \Exception("Variable \$did is not defined.");
                }

                $fltr_fxd_bnr = $fxd_banner->filter(function ($item) use ($did) {
                    // Ensure $item is an object
                    if (is_array($item)) {
                        $item = (object) $item;
                    }
                    return $item->BANNER_ID === $did;
                });

                $bnr["Catg_Banner"] = $fltr_fxd_bnr->values()->all();

                $fltr_promo_bnr = $promo_banner->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SP';
                });
                $bnr1["Banner"] = $fltr_promo_bnr->values()->take(3)->all();
                $data = $data->merge($bnr + $bnr1);
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

    function allsrchdr()
    {
        $SQL = "SELECT DISTINCT DR_ID,DR_NAME,DR_MOBILE,SEX,PHOTO_URL,REGN_NO,DESIGNATION,QUALIFICATION,D_CATG,EXPERIENCE FROM drprofile";
        $data = DB::select($SQL);

        if ($data == []) {
            $response = ['Success' => false, 'Message' => 'Doctor not found.', 'code' => 200];
        } else {
            $response = ['Success' => true, 'data' => $data, 'code' => 200];
        }
        return $response;
    }

    function nearbydr(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $data = DB::table('drprofile')
                    ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
                    ->join('pharmacy', 'dr_availablity.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
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
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                 * SIN(RADIANS('$latt'))))),2) as KM")
                    )
                    ->where('drprofile.APPROVE', 'true')
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->orderbyraw('KM')
                    ->take(25)
                    ->get();

                if ($data == null) {
                    $response = ['Success' => false, 'Message' => 'Dr. not found', 'code' => 200];
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

    function nearbyclinic(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();
                $promo_banner = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', 'CL')->get();
                $pharma = DB::table('pharmacy')
                    // ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
                    // ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
                    ->distinct('pharmacy.PHARMA_ID')
                    ->select(
                        'pharmacy.PHARMA_ID',
                        'pharmacy.ITEM_NAME AS PHARMA_NAME',
                        'pharmacy.ADDRESS',
                        'pharmacy.CITY',
                        'pharmacy.DIST',
                        'pharmacy.STATE',
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        'pharmacy.CLINIC_TYPE',
                        'pharmacy.OPD',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                 * SIN(RADIANS('$latt'))))),0) as KM")
                    )
                    ->where(['pharmacy.STATUS' => 'Active'])
                    ->where('pharmacy.CLINIC_TYPE', '<>', 'Clinic')
                    ->orderbyraw('KM')
                    ->take(25)
                    ->get();
                $arr_cl["Diagnostic"] = $pharma;

                $fltr_promo_bnr = $promo_banner->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'CL';
                });
                $bnr1["Banner"] = $fltr_promo_bnr->take(3)->values()->all();

                $data = $arr_cl + $bnr1;

                if ($data == null) {
                    $response = ['Success' => false, 'Message' => 'Dr. not found', 'code' => 200];
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

    function srchlivedr(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $data = DB::table('drprofile')
                    ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
                    ->join('pharmacy', 'dr_availablity.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
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
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                        * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                         * SIN(RADIANS('$latt'))))),2) as KM")
                    )
                    ->where('dr_availablity.CHK_IN_STATUS', 'IN')
                    ->where('drprofile.APPROVE', 'true')
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->orderbyraw('KM')
                    ->take(25)
                    ->get();

                if ($data == []) {
                    $response = ['Success' => false, 'Message' => 'Dr. not live now', 'code' => 200];
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

    // function srchcityclinic(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $input   = $req->json()->all();
    //         if (isset($input['CITY']) && isset($input['DRID'])) {
    //             $city = $input['CITY'];
    //             $drid = $input['DRID'];

    //             $response = array();
    //             $data = array();

    //             $day1 = date('l');
    //             $day2 = Date('l', strtotime('+1 days'));
    //             $day3 = Date('l', strtotime('+2 days'));
    //             $day4 = Date('l', strtotime('+3 days'));
    //             $day5 = Date('l', strtotime('+4 days'));
    //             $day6 = Date('l', strtotime('+5 days'));
    //             $day7 = Date('l', strtotime('+6 days'));

    //             $SQL = "SELECT distinct C.PHARMA_ID,C.DR_ID,A.DR_NAME,C.WEEK,C.DR_FEES,B.ITEM_NAME AS PHARMA_NAME,B.ADDRESS,B.CITY,B.DIST,B.CLINIC_MOBILE,B.PIN,B.EMAIL,B.STATE,B.LATITUDE,B.LONGITUDE,B.PHOTO_URL FROM drprofile A,pharmacy B,dr_availablity C WHERE A.DR_ID=C.DR_ID AND B.PHARMA_ID=C.PHARMA_ID AND C.DR_ID='$drid' AND B.CITY='$city' order by FIELD(C.SCH_DAY,'$day1','$day2','$day3','$day4','$day5','$day6','$day7'),C.CHK_IN_TIME";
    //             $data = DB::select($SQL);
    //             $response = ['Success' => true, 'data' => $data, 'code' => 200];
    //         } else {
    //             $response = ['Success' => false, 'Message' => 'Invalid input parameter.', 'code' => 422];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
    //     }
    //     return $response;
    // }

    function viewfamily(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $input = $req->json()->all();
            $headers = apache_request_headers();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {

            if (isset($input['FAMILYID'])) {
                $f_id = $input['FAMILYID'];

                $response = array();
                $data = array();

                $data = DB::table('user_family')->where('family_id', '=', $f_id)->orderby('ID')->get();
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter.', 'code' => 422];
            }
            // } else {
            //     $response = ['Success' => false, 'Message' => 'You are not Authorized', 'code' => 401];
            // }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }

    function addfamily(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $input = $req->all();
            // $headers = apache_request_headers();
            // if (isset($headers['Authorization']) && $headers['Authorization'] == $_SESSION['TOKEN']) {

            $input = $req->all();
            $fileName = null;
            $url = 'http://easyhealths.com/storage/app/user/dummy.png';
            if ($req->file('PHOTO')) {
                $fid = $input['FAMILYID'] ?? null;

                if ($fid) {
                    $fileName = $fid . time() . "." . $req->file('PHOTO')->getClientOriginalExtension();
                    $req->file('PHOTO')->storeAs('user', $fileName);
                    $url = asset(storage::url('app/user')) . "/" . $fileName;
                }
            }
            if (isset($input['NAME']) && isset($input['MOBILE']) && isset($input['RELATION']) && isset($input['FAMILYID']) && isset($input['SEX']) && isset($input['DOB'])) {
                $fields = [
                    "FAMILY_ID" => $input['FAMILYID'] ?? null,
                    "NAME" => $input['NAME'] ?? null,
                    "LOCATION" => $input['LOCATION'] ?? null,
                    "MOBILE" => $input['MOBILE'] ?? null,
                    "SEX" => $input['SEX'] ?? null,
                    "DOB" => $input['DOB'] ?? null,
                    "RELATION" => $input['RELATION'] ?? null,
                    "PHOTO_URL" => $url,
                ];

                try {
                    DB::table('user_family')->insert($fields);
                    $response = ['Success' => true, 'Message' => 'Family add successfully', 'code' => 200];
                } catch (\Throwable $th) {
                    $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 500];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter.', 'code' => 422];
            }
            // } else {
            //     $response = array("Success" => false, 'Message' => 'You are not Authorized', 'code' => 401);
            // }
        } else {
            $response = ['Success' => false, 'Message' => 'Method not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function editfamily(Request $req)
    {
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ], 405);
        }
        $input = $req->all();
        // $fid = $input['FAMILY_ID'];
        $pid = $input['PATIENT_ID'];
        $fileName = null;
        $url = 'http://easyhealths.com/storage/app/user/dummy.png';
        if ($req->file('file')) {
            if ($pid) {
                $fileName = $pid . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('user', $fileName);
                $url = asset(storage::url('app/user')) . "/" . $fileName;
            }
        }
        $fields_patient = [
            "PHOTO_URL" => $url,
        ];
        try {
            DB::table('user_family')->where(['user_family.ID' => $pid])->update($fields_patient);
            $response = ['Success' => true, 'Message' => 'Photo insert successfully', 'code' => 200];
        } catch (\Exception $e) {
            $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
        }
        return $response;
    }

    function delfamily(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $input = $req->json()->all();
            $headers = apache_request_headers();
            // if (isset($headers['Authorization']) && $headers['Authorization'] == $_SESSION['TOKEN']) {

            if (isset($input['PATIENT_ID'])) {

                $ID = $input['PATIENT_ID'];
                $response = array();
                $data = array();

                $SQL = "DELETE FROM `user_family` WHERE ID='$ID'";
                $data = DB::delete($SQL);

                if ($data) {
                    $response = ['Success' => true, 'Message' => 'Family member delete successfully', 'code' => 200];
                } else {
                    $response = ['Success' => false, 'Message' => 'Invalid family member ID.', 'code' => 200];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter.', 'code' => 422];
            }
            // } else {
            //     $response = array("Success" => false, 'Message' => 'You are not Authorized', 'code' => 401);
            // }
        } else {
            $response = ['Success' => false, 'Message' => 'Method not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updatefamily(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // session_start();
            $input = $req->json()->all();
            $headers = apache_request_headers();
            // if (isset($headers['Authorization']) && $headers['Authorization'] == $_SESSION['TOKEN']) {

            if (isset($input['ID'])) {
                $ID = $input['ID'];
                $FAMILYID = $input['FAMILYID'];
                $NAME = $input['NAME'];
                $LOCATION = $input['LOCATION'];
                $MOBILE = $input['MOBILE'];
                $A_MOB = $input['ALT_MOB'];
                $M_STS = $input['M_STS'];
                $SEX = $input['SEX'];
                $DOB = $input['DOB'];
                $HEIGHT = $input['HEIGHT'];
                $WEIGHT = $input['WEIGHT'];
                $BLOODGR = $input['BLOODGR'];
                $RELATION = $input['RELATION'];

                $ALG = $input['ALLERGIES'];
                $C_MD = $input['CURR_MED'];
                $P_MD = $input['PAST_MED'];
                $CHRONIC = $input['CHRONIC'];
                $INJURY = $input['INJURY'];
                $SURGERY = $input['SURGERY'];

                $SMK = $input['SMOKE'];
                $ALC = $input['ALCOHOL'];
                $ACT_LBL = $input['ACT_LBL'];
                $FOOD = $input['FOOD'];
                $OCC = $input['OCCUPATION'];

                $response = array();
                $data = array();

                $SQL = "UPDATE `user_family` SET `FAMILY_ID`='$FAMILYID',`NAME`='$NAME,`LOCATION`='$LOCATION',`MOBILE`='$MOBILE',`ALT_MOB`='$A_MOB',
                `M_STS`='$M_STS',`SEX`='$SEX',`DOB`='$DOB',`HEIGHT`='$HEIGHT',`WEIGHT`='$WEIGHT',`BLOOD_GR`='$BLOODGR',`RELATION`='$RELATION',
                `ALLERGIES`='$ALG',`CURR_MED`='$C_MD',`PAST_MED`='$P_MD',`CHRONIC`='$CHRONIC',`INJURY`='$INJURY',`SURGERY`='$SURGERY',
                `SMOKE_HABIT`='$SMK',`ALCOHOL_HABIT`='$ALC',`ACTIVITY_LBL`='$ACT_LBL',`FOOD_PERFORM`='$FOOD',`OCCUPATION`='$OCC' WHERE ID='$ID'";
                $data = DB::update($SQL);

                $response = ['Success' => true, 'Message' => 'Family modified successfully', 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter.', 'code' => 422];
            }
            // } else {
            //     $response = array("Success" => false, 'Message' => 'You are not Authorized', 'code' => 401);
            // }
        } else {
            $response = ['Success' => false, 'Message' => 'Method not Allowed.', 'code' => 405];
        }
        return $response;
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

    // function booktest(Request $req)
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
    //                         "SCH_ID" => $detail['SCH_ID'],
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
    //                         "SCH_ID" => $detail['SCH_ID'],
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

    //                 $cdt = date('Ymd');
    //                 $phid = $row['PHARMA_ID'];
    //                 $schid = $detail['SCH_ID'];
    //                 $slotdt =$detail['SLOT_DATE'];

    //             $data1 = DB::table('test_schedule')->where(['PHARMA_ID' => $phid, 'ID' => $schid])->first();

    //             $slots = [];

    //             $chkInTimes = [
    //                 $data1->SCH_FROM1,
    //                 $data1->SCH_FROM2,
    //                 $data1->SCH_FROM3,
    //                 $data1->SCH_FROM4
    //             ];
    //             $chkOutTimes = [
    //                 $data1->SCH_TO1,
    //                 $data1->SCH_TO2,
    //                 $data1->SCH_TO3,
    //                 $data1->SCH_TO4
    //             ];
    //             $maxbooks = [
    //                 $data1->MAX_BOOK1,
    //                 $data1->MAX_BOOK2,
    //                 $data1->MAX_BOOK3,
    //                 $data1->MAX_BOOK4
    //             ];
    //             $intvl = $data1->DURATION ?? null;

    //             foreach ($chkInTimes as $index => $chkin) {
    //                 $maxbk = $maxbooks[$index];
    //                 $chkout = $chkOutTimes[$index];
    //                 if ($chkin === null) {
    //                     continue;
    //                 }

    //                 try {
    //                     $chkinTime = Carbon::createFromFormat('h:i A', $chkin);
    //                     $chkoutTime = $chkout !== null ? Carbon::createFromFormat('h:i A', $chkout) : $chkinTime->copy()->addMinutes($intvl * $maxbk);
    //                 } catch (\Exception $e) {
    //                     return response()->json(["Error" => "Error in time conversion: " . $e->getMessage()]);
    //                 }

    //                 $slot_sts = null;

    //                 while ($chkinTime->lessThan($chkoutTime)) {
    //                     $endSlot = $chkinTime->copy()->addMinutes($intvl);
    //                     if ($endSlot->greaterThan($chkoutTime)) {
    //                         break;
    //                     }

    //                     $bookedCount = DB::table('booktest')->where(['FROM' => $chkinTime->format('h:i A'), 'SCH_ID' => $schid])->count();
    //                     // Log::info('Booked Count: ' . $bookedCount);

    //                     $totalAppointments = $data1->SLOT_TEST_CT;
    //                     $bookingSerials = range(1, $totalAppointments);
    //                     $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));

    //                     if ($cdt === $slotdt) {
    //                         $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
    //                     } else {
    //                         $slot_sts = "Available";
    //                     }

    //                     $slotString = [
    //                         "FROM" => $chkinTime->format('h:i A'),
    //                         "TO" => $endSlot->format('h:i A'),
    //                         "TOTAL" => $totalAppointments,
    //                         "AVAIL_BOOK" => count($availableSerials),
    //                         "BOOKING_SERIALS" => $bookingSerials,
    //                         "AVAILABLE_SERIALS" => $availableSerials,
    //                         "SLOT_STATUS" => $slot_sts,
    //                     ];


    //                         $slots[] = $slotString;


    //                     $chkinTime->addMinutes($intvl);
    //                 }
    //             }

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

    // function booktest(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         date_default_timezone_set('Asia/Kolkata');
    //         $cdt = Carbon::now()->format('ymdHis');

    //         $input = $req->json()->all();
    //         $BT = $input['BOOK_TEST'];
    //         $patientIds = [];
    //         $bookid = null;

    //         DB::beginTransaction(); // Start the transaction

    //         try {
    //             foreach ($BT as $row) {
    //                 foreach ($row['DETAILS'] as $detail) {
    //                     $token = strtoupper(substr(md5($detail['PATIENT_ID'] . $cdt . $row['PHARMA_ID']), 0, 10));

    //                     // Prepare fields based on PKG_TYPE
    //                     $fields = [
    //                         "BOOKING_ID" => $token,
    //                         "PKG_ID" => $detail['PKG_TYPE'] ? $detail['PKG_ID'] : $detail['TEST_ID'],
    //                         "SCH_ID" => $detail['SCH_ID'],
    //                         "PKG_NAME" => $detail['PKG_TYPE'] ? $detail['PKG_NAME'] : $detail['TEST_NAME'],
    //                         "CATEGORY" => $detail['PKG_TYPE'] ? 'OTHERS' : $detail['CATEGORY'],
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
    //                         "TEST_COST" => $detail['PKG_TYPE'] ? $detail['PKG_COST'] : $detail['COST']
    //                     ];

    //                     $cdt = date('Ymd');
    //                     $phid = $row['PHARMA_ID'];
    //                     $schid = $detail['SCH_ID'];
    //                     $slotdt = $detail['SLOT_DATE'];

    //                     $data1 = DB::table('test_schedule')->where(['PHARMA_ID' => $phid, 'ID' => $schid])->first();
    //                     if (!$data1) {
    //                         throw new \Exception('Schedule data not found.');
    //                     }

    //                     $slots = [];
    //                     $chkInTimes = [$data1->SCH_FROM1, $data1->SCH_FROM2, $data1->SCH_FROM3, $data1->SCH_FROM4];
    //                     $chkOutTimes = [$data1->SCH_TO1, $data1->SCH_TO2, $data1->SCH_TO3, $data1->SCH_TO4];
    //                     $maxbooks = [$data1->MAX_BOOK1, $data1->MAX_BOOK2, $data1->MAX_BOOK3, $data1->MAX_BOOK4];
    //                     $intvl = $data1->DURATION ?? null;

    //                     foreach ($chkInTimes as $index => $chkin) {
    //                         $maxbk = $maxbooks[$index];
    //                         $chkout = $chkOutTimes[$index];
    //                         if ($chkin === null)
    //                             continue;

    //                         try {
    //                             $chkinTime = Carbon::createFromFormat('h:i A', $chkin);
    //                             $chkoutTime = $chkout !== null ? Carbon::createFromFormat('h:i A', $chkout) : $chkinTime->copy()->addMinutes($intvl * $maxbk);
    //                         } catch (\Exception $e) {
    //                             throw new \Exception("Error in time conversion: " . $e->getMessage());
    //                         }

    //                         while ($chkinTime->lessThan($chkoutTime)) {
    //                             $endSlot = $chkinTime->copy()->addMinutes($intvl);
    //                             if ($endSlot->greaterThan($chkoutTime))
    //                                 break;

    //                             $bookedCount = DB::table('booktest')->where(['FROM' => $chkinTime->format('h:i A'), 'SCH_ID' => $schid])->count();
    //                             $totalAppointments = $data1->SLOT_TEST_CT;
    //                             $bookingSerials = range(1, $totalAppointments);
    //                             $availableSerials = array_values(array_diff($bookingSerials, range(1, $bookedCount)));

    //                             $slot_sts = ($cdt === $slotdt && $endSlot->lessThan(Carbon::now())) ? "Closed" : "Available";

    //                             $slotString = [
    //                                 "FROM" => $chkinTime->format('h:i A'),
    //                                 "TO" => $endSlot->format('h:i A'),
    //                                 "TOTAL" => $totalAppointments,
    //                                 "AVAIL_BOOK" => count($availableSerials),
    //                                 "BOOKING_SERIALS" => $bookingSerials,
    //                                 "AVAILABLE_SERIALS" => $availableSerials,
    //                                 "SLOT_STATUS" => $slot_sts,
    //                             ];

    //                             $slots[] = $slotString;
    //                             $chkinTime->addMinutes($intvl);
    //                         }
    //                     }

    //                     // Filter slots based on details['FROM']
    //                     $filteredSlots = array_filter($slots, function ($slot) use ($detail) {
    //                         return $slot['FROM'] === $detail['FROM'];
    //                     });

    //                     if (empty($filteredSlots) || $filteredSlots[0]['AVAIL_BOOK'] <= 0) {
    //                         throw new \Exception('No available slots or slots are fully booked.');
    //                     }

    //                     DB::table('booktest')->insert($fields);
    //                 }

    //                 // Handling bookid logic
    //                 if (count($BT) == 1) {
    //                     foreach ($BT as $row) {
    //                         if (count($row['DETAILS']) == 1) {
    //                             $bookid = $token;
    //                         } else {
    //                             foreach ($row['DETAILS'] as $detail) {
    //                                 $patientId = $detail['PATIENT_ID'];
    //                                 if (in_array($patientId, $patientIds)) {
    //                                     $bookid = $token;
    //                                     break;
    //                                 } else {
    //                                     $patientIds[] = $patientId;
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             }

    //             DB::commit(); // Commit the transaction

    //             $response = [
    //                 'Success' => true,
    //                 'Message' => 'Test booking successfully.',
    //                 'BOOK_ID' => $bookid,
    //                 'code' => 200
    //             ];
    //         } catch (\Throwable $th) {
    //             DB::rollBack(); // Rollback the transaction
    //             $response = [
    //                 'Success' => false,
    //                 'Message' => $th->getMessage(),
    //                 'code' => 200
    //             ];
    //         }
    //     } else {
    //         $response = [
    //             'Success' => false,
    //             'Message' => 'Method Not Allowed.',
    //             'code' => 200
    //         ];
    //     }
    //     return response()->json($response);
    // }

    function booktest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            $cdt = Carbon::now()->format('ymdHis');

            $input = $req->json()->all();
            $BT = $input['BOOK_TEST'];
            $patientIds = [];
            $bookid = null;

            Log::info($BT);

            DB::beginTransaction(); // Start the transaction


            try {
                foreach ($BT as $row) {
                    foreach ($row['DETAILS'] as $detail) {
                        $token = strtoupper(substr(md5($detail['PATIENT_ID'] . $cdt . $row['PHARMA_ID']), 0, 10));
                        // Check if PKG_TYPE exists and prepare fields based on its presence

                        if (isset($detail['PKG_TYPE'])) {
                            $fields = [
                                "BOOKING_ID" => $token,
                                "PKG_ID" => $detail['PKG_ID'],
                                "SCH_ID" => $detail['SCH_ID'],
                                "PKG_NAME" => $detail['PKG_NAME'],
                                "CATEGORY" => 'OTHERS',
                                "PHARMA_ID" => $row['PHARMA_ID'],
                                "BOOKING_DT" => $detail['BOOK_DATE'],
                                "BOOKING_TM" => $detail['BOOK_TIME'],
                                "SLOT_DT" => $detail['SLOT_DATE'],
                                "FROM" => $detail['FROM'],
                                "TO" => $detail['TO'],
                                "PATIENT_ID" => $detail['PATIENT_ID'],
                                "Patient_Name" => $detail['PATIENT_NAME'],
                                "ADVICED_BY" => $detail['ADVICED_BY'],
                                "PRESCRIPTION_URL" => $detail['PRESCRIPTION_URL'],
                                "HOME_COLLECT" => $detail['HOME_COLLECT'],
                                "TEST_COST" => $detail['PKG_COST']
                            ];



                        } else {
                            $fields = [
                                "BOOKING_ID" => $token,
                                "PKG_ID" => $detail['TEST_ID'],
                                "SCH_ID" => $detail['SCH_ID'],
                                "PKG_NAME" => $detail['TEST_NAME'],
                                "CATEGORY" => $detail['CATEGORY'],
                                "PHARMA_ID" => $row['PHARMA_ID'],
                                "BOOKING_DT" => $detail['BOOK_DATE'],
                                "BOOKING_TM" => $detail['BOOK_TIME'],
                                "SLOT_DT" => $detail['SLOT_DATE'],
                                "FROM" => $detail['FROM'],
                                "TO" => $detail['TO'],
                                "PATIENT_ID" => $detail['PATIENT_ID'],
                                "Patient_Name" => $detail['PATIENT_NAME'],
                                "ADVICED_BY" => $detail['ADVICED_BY'],
                                "PRESCRIPTION_URL" => $detail['PRESCRIPTION_URL'],
                                "HOME_COLLECT" => $detail['HOME_COLLECT'],
                                "TEST_COST" => $detail['COST'],

                            ];

                            $cdt = date('Ymd');
                            $phid = $row['PHARMA_ID'];
                            $schid = $detail['SCH_ID'];
                            $slotdt = $detail['SLOT_DATE'];

                            $data1 = DB::table('test_schedule')->where(['PHARMA_ID' => $phid, 'ID' => $schid])->first();
                            Log::info($schid);
                            if (!$data1) {
                                throw new \Exception('Schedule data not found.');
                            }

                            $slots = [];
                            $chkInTimes = [$data1->SCH_FROM1, $data1->SCH_FROM2, $data1->SCH_FROM3, $data1->SCH_FROM4];
                            $chkOutTimes = [$data1->SCH_TO1, $data1->SCH_TO2, $data1->SCH_TO3, $data1->SCH_TO4];
                            $maxbooks = [$data1->MAX_BOOK1, $data1->MAX_BOOK2, $data1->MAX_BOOK3, $data1->MAX_BOOK4];
                            $intvl = $data1->DURATION ?? null;

                            foreach ($chkInTimes as $index => $chkin) {
                                $maxbk = $maxbooks[$index];
                                $chkout = $chkOutTimes[$index];
                                if ($chkin === null)
                                    continue;

                                try {
                                    $chkinTime = Carbon::createFromFormat('h:i A', $chkin);
                                    $chkoutTime = $chkout !== null ? Carbon::createFromFormat('h:i A', $chkout) : $chkinTime->copy()->addMinutes($intvl * $maxbk);
                                } catch (\Exception $e) {
                                    throw new \Exception("Error in time conversion: " . $e->getMessage());
                                }
                                $slot_sts = null;

                                while ($chkinTime->lessThan($chkoutTime)) {
                                    $endSlot = $chkinTime->copy()->addMinutes($intvl);
                                    if ($endSlot->greaterThan($chkoutTime))
                                        break;

                                    $bookedCount = DB::table('booktest')->where(['FROM' => $chkinTime->format('h:i A'), 'SLOT_DT' => $slotdt, 'SCH_ID' => $schid])->count();
                                    Log::info($bookedCount);
                                    $totalAppointments = $data1->SLOT_TEST_CT;
                                    $bookingSerials = range(1, $totalAppointments);
                                    if ($bookedCount > 0) {
                                        $availableSerials = array_values(array_diff($bookingSerials, range(1, $bookedCount)));
                                    } else {
                                        $availableSerials = $bookingSerials;
                                    }


                                    $slot_sts = ($cdt === $slotdt && $endSlot->lessThan(Carbon::now())) ? "Closed" : "Available";

                                    $slotString = [
                                        "FROM" => $chkinTime->format('h:i A'),
                                        "TO" => $endSlot->format('h:i A'),
                                        "TOTAL" => $totalAppointments,
                                        "AVAIL_BOOK" => count($availableSerials),
                                        "BOOKING_SERIALS" => $bookingSerials,
                                        "AVAILABLE_SERIALS" => $availableSerials,
                                        "SLOT_STATUS" => $slot_sts,
                                    ];

                                    $slots[] = $slotString;
                                    $chkinTime->addMinutes($intvl);
                                }
                            }

                            Log::info($slots);

                            // Filter slots based on details['FROM']
                            $filteredSlots = array_filter($slots, function ($slot) use ($detail) {
                                return $slot['FROM'] === $detail['FROM'];
                            });

                            Log::info($filteredSlots);

                            if (empty($filteredSlots)) {
                                // Since there are no slots, manually create a default slot entry to handle this case
                                $defaultSlot = [
                                    'FROM' => $detail['FROM'], // Assuming FROM time from $detail for consistency
                                    'TO' => '', // No TO time available
                                    'TOTAL' => 0, // No total slots available
                                    'AVAIL_BOOK' => 0, // No slots available to book
                                    'BOOKING_SERIALS' => [], // No booking serials
                                    'AVAILABLE_SERIALS' => [], // No available serials
                                    'SLOT_STATUS' => 'Closed', // Default status as closed or unavailable
                                ];

                                // Use the default slot for further logic
                                $filteredSlots = [$defaultSlot];

                                // Since there are no available bookings, you might want to handle the logic accordingly
                                throw new \Exception('No slots available for the selected time.');
                            }


                            $firstFilteredSlot = reset($filteredSlots); // reset() gets the first element of the array

                            // Check available bookings
                            if ($firstFilteredSlot['AVAIL_BOOK'] <= 0) {
                                throw new \Exception('Booking Failed! It looks like you are trying to book more than one slot for your selected time.');
                            }
                        }

                        DB::table('booktest')->insert($fields);
                    }

                    // Handling bookid logic
                    if (count($BT) == 1) {
                        foreach ($BT as $row) {
                            if (count($row['DETAILS']) == 1) {
                                $bookid = $token;
                            } else {
                                foreach ($row['DETAILS'] as $detail) {
                                    $patientId = $detail['PATIENT_ID'];
                                    if (in_array($patientId, $patientIds)) {
                                        $bookid = $token;
                                        break;
                                    } else {
                                        $patientIds[] = $patientId;
                                    }
                                }
                            }
                        }
                    }
                }

                DB::commit(); // Commit the transaction

                $response = [
                    'Success' => true,
                    'Message' => 'Test booking successfully.',
                    'BOOK_ID' => $bookid,
                    'code' => 200
                ];
                Log::info('Booking success response: ', $response);
            } catch (\Throwable $th) {
                DB::rollBack(); // Rollback the transaction
                $response = [
                    'Success' => false,
                    'Message' => $th->getMessage(),
                    'code' => 400
                ];
                Log::error('Booking failure response: ', $response);
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


    function booking(Request $req)
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
                $APNT_TOKEN = strtoupper(substr(md5($input['APNT_DT'] . $input['APNT_ID'] . $input['PATIENT_ID']), 0, 10));
                $fields = [
                    "FAMILY_ID" => $input['FAMILY_ID'],
                    "BOOK_BY_ID" => $input['FAMILY_ID'],
                    "PATIENT_ID" => $input['PATIENT_ID'],
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
                        // "Slot" => $input['APNT_SLOT'],
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

    function bookcancel(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $input = $req->json()->all();
            $headers = apache_request_headers();
            // if (isset($headers['Authorization']) && $headers['Authorization'] == $_SESSION['TOKEN']) {
            if (isset($input['BOOKID'])) {

                $fields = [
                    "STATUS" => 'Cancelled',
                    "REASON" => $input['REASON'],
                ];
                $response = array();
                try {
                    DB::table('appointment')->where('BOOKING_ID', $input['BOOKID'])->update($fields);
                    $response = ['Success' => true, 'Message' => 'Booking cancelled successfully', 'code' => 200];
                } catch (\Exception $e) {
                    $response = ['Success' => false, 'Message' => $e, 'code' => 200];
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

    function symptoms(Request $request)
    {
        if ($request->isMethod('post')) {
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();
            if (isset($input['DIS_ID']) && isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $did = $input['DIS_ID'];
                $sid = $input['SYM_ID'];

                $data = collect();

                $data = $data->merge($this->getCatgDrDt($latt, $lont, $did));
                $data = $data->merge($this->getClinic($latt, $lont, $did));

                if (is_numeric($input['SYM_ID'])) {
                    // $fxd_banner = DB::table('symptoms')
                    //     ->select('SYM_ID AS BANNER_ID', 'DIS_ID', 'SYM_NAME AS BANNER_NAME', 'DESCRIPTION', 'BANNER_URL', 'QA1', 'QA2', 'QA3', 'QA4', 'QA5')
                    //     ->where('DASH_SECTION_ID', '=', 'SM')->get();

                    $fxd_banner = DB::table('symptoms')
                        ->select(
                            'SYM_ID AS BANNER_ID',
                            'DIS_ID',
                            'SYM_NAME AS BANNER_NAME',
                            'DESCRIPTION',
                            'SYMBNR1 AS BANNER_URL1',
                            'SYMBNR2 AS BANNER_URL2',
                            'SYMBNR3 AS BANNER_URL3',
                            'SYMBNR4 AS BANNER_URL4',
                            'SYMBNR5 AS BANNER_URL5',
                            'SYMBNR6 AS BANNER_URL6',
                            'SYMBNR7 AS BANNER_URL7',
                            'SYMBNR8 AS BANNER_URL8',
                            'SYMBNR9 AS BANNER_URL9',
                            'SYMBNR10 AS BANNER_URL10',
                            'SYMQA1 AS QA1',
                            'SYMQA2 AS QA2',
                            'SYMQA3 AS QA3',
                            'SYMQA4 AS QA4',
                            'SYMQA4 AS QA5'
                        )
                        ->where(['DASH_SECTION_ID' => 'SM', 'DIS_ID' => $did, 'SYM_ID' => $sid])
                        ->get();



                    $fltr_fxd_bnr = $fxd_banner->filter(function ($item) use ($sid) {
                        return $item->BANNER_ID === $sid;
                    });
                    $bnr["Catg_Banner"] = $fltr_fxd_bnr->map(function ($item) {
                        return [
                            "BANNER_ID" => $item->BANNER_ID,
                            "DIS_ID" => $item->DIS_ID,
                            "BANNER_NAME" => $item->BANNER_NAME,
                            "DESCRIPTION" => $item->DESCRIPTION,
                            "BANNER_URL1" => $item->BANNER_URL1,
                            "BANNER_URL2" => $item->BANNER_URL2,
                            "BANNER_URL3" => $item->BANNER_URL3,
                            "BANNER_URL4" => $item->BANNER_URL4,
                            "BANNER_URL5" => $item->BANNER_URL5,
                            "BANNER_URL6" => $item->BANNER_URL6,
                            "BANNER_URL7" => $item->BANNER_URL7,
                            "BANNER_URL8" => $item->BANNER_URL8,
                            "BANNER_URL9" => $item->BANNER_URL9,
                            "BANNER_URL10" => $item->BANNER_URL10,
                            "Questions" => [
                                [
                                    "QA1" => $item->QA1,
                                    "QA2" => $item->QA2,
                                    "QA3" => $item->QA3,
                                    "QA4" => $item->QA4,
                                    "QA5" => $item->QA5
                                ]
                            ]

                        ];
                    })->values()->all();
                    // $bnr["Catg_Banner"] = $fltr_fxd_bnr->values()->all();
                } else {
                    $fxd_banner = DB::table('surgery')
                        ->select('SURG_TYPE AS BANNER_TYPE', 'DIS_ID', 'SURG_NAME AS BANNER_NAME', 'TYPE_DESC AS DESCRIPTION', 'BANNER_URL')
                        ->where('DASH_ID', '=', 'SR')->get();
                    $fltr_fxd_bnr = $fxd_banner->filter(function ($item) use ($sid) {
                        return $item->BANNER_TYPE === $sid;
                    });
                    $bnr["Catg_Banner"] = $fltr_fxd_bnr->take(1)->values()->all();
                }

                $promo_banner = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', 'SM')->get();
                $fltr_promo_bnr = $promo_banner->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SM';
                });
                $bnr1["Banner"] = $fltr_promo_bnr->values()->take(3)->all();
                $data = $data->merge($bnr + $bnr1);
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

    function srccldr(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['PHARMAID']) && isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $pharmaid = $input['PHARMAID'];

                $drs = DB::table('pharmacy')
                    ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
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
                        'drprofile.PHOTO_URL as DR_PHOTO',
                        'dr_availablity.DR_FEES',
                        'dr_availablity.DESCRIPTION',
                        'pharmacy.PHARMA_ID',
                        'pharmacy.ITEM_NAME AS PHARMA_NAME',
                        'pharmacy.ADDRESS',
                        'pharmacy.CITY',
                        'pharmacy.DIST',
                        'pharmacy.STATE',
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        'pharmacy.CLINIC_TYPE',
                        'pharmacy.OPD',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                    * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                     * SIN(RADIANS('$latt'))))),2) as KM")
                    )
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->where('pharmacy.PHARMA_ID', '=', $pharmaid)
                    ->orderby('drprofile.EXPERIENCE', 'desc')
                    ->get();

                foreach ($drs as $entry) {
                    $sch = DB::table('dr_availablity')
                        ->distinct('SCH_DAY')
                        ->select('SCH_DAY')
                        ->where(['PHARMA_ID' => $entry->PHARMA_ID, 'DR_ID' => $entry->DR_ID])
                        ->get();

                    foreach ($sch as $dd) {
                        $scheduleDays[] = $dd->SCH_DAY;
                    }

                    $scheduleDaysString = implode(', ', $scheduleDays);
                    $data[] = array(
                        "DR_ID" => $entry->DR_ID,
                        "DR_NAME" => $entry->DR_NAME,
                        "DR_MOBILE" => $entry->DR_MOBILE,
                        "SEX" => $entry->SEX,
                        "DESIGNATION" => $entry->DESIGNATION,
                        "QUALIFICATION" => $entry->QUALIFICATION,
                        "D_CATG" => $entry->D_CATG,
                        "EXPERIENCE" => $entry->EXPERIENCE,
                        "DR_PHOTO" => $entry->DR_PHOTO,
                        "DR_FEES" => $entry->DR_FEES,
                        "SCH_WEEK" => $entry->DESCRIPTION,
                        "SCH_DAY" => $scheduleDaysString,
                        "PHARMA_ID" => $entry->PHARMA_ID,
                        "PHARMA_NAME" => $entry->PHARMA_NAME,
                        "ADDRESS" => $entry->ADDRESS,
                        "CITY" => $entry->CITY,
                        "DIST" => $entry->DIST,
                        "STATE" => $entry->STATE,
                        "PHOTO_URL" => $entry->PHOTO_URL,
                        "CLINIC_TYPE" => $entry->CLINIC_TYPE,
                        "KM" => $entry->KM
                    );
                    $scheduleDays = [];
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

    function srcclinic(Request $request)
    {
        if ($request->isMethod('post')) {
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();
            if (isset($input['DRID']) && isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $drid = $input['DRID'];

                $clinics = collect($this->getClinicDrDt($latt, $lont, $drid));

                if (empty($clinics)) {
                    $response = ['Success' => false, 'Message' => 'Record not found', 'code' => 200];
                } else {
                    // $uniqueClinics = $clinics->unique('clinic_id')->values();
                    $uniqueClinics = $clinics->unique('PHARMA_ID')->values();
                    $response = ['Success' => true, 'data' => $uniqueClinics, 'code' => 200];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function bookhis(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $headers = apache_request_headers();
            session_start();
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
            if (isset($input['FAMILY_ID'])) {
                $f_id = $input['FAMILY_ID'];
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
                        'appointment.APPNT_DT AS AVAILABLE_DT',
                        'appointment.APPNT_TOKEN',
                        'appointment.BOOKING_SL',
                        'appointment.APPNT_SLOT',
                        'dr_availablity.SCH_DAY AS APPNT_DAY',
                        'dr_availablity.CHK_IN_TIME AS FROM',
                        'dr_availablity.CHK_OUT_TIME AS TO',
                        'dr_availablity.CHK_IN_STATUS AS DR_STATUS',
                        'dr_availablity.DR_DELAY',
                        'dr_availablity.DR_ARRIVE',
                        'dr_availablity.CHEMBER_NO',
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
                        // 'appointment.VISIT AS REASON'
                    )
                    ->where('appointment.FAMILY_ID', '=', $f_id)
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->orderbydesc('appointment.BOOKING_DT')
                    ->orderbydesc('appointment.BOOKING_TM')
                    ->orderby('appointment.BOOKING_ID')
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

    function saveinvoice(Request $req)
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

    function cldrct(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $headers = apache_request_headers();
            session_start();
            date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']){

            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['PHARMAID'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $pharmaid = $input['PHARMAID'];

                $data = DB::table('pharmacy')
                    ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
                    ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
                    ->join('dis_catg', 'drprofile.DIS_ID', '=', 'dis_catg.DIS_ID')
                    ->select('drprofile.D_CATG', 'dis_catg.PHOTO_URL', DB::raw('count(*) as total'))
                    ->where(DB::raw("111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                    * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                    * SIN(RADIANS('$latt')))))"), '<=', '100')
                    ->where('pharmacy.PHARMA_ID', '=', $pharmaid)
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->groupBy('drprofile.D_CATG', 'dis_catg.PHOTO_URL')
                    ->get();
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid parameter.', 'code' => 422];
            }
            // }else {
            //     $response = ['Success' => false, 'Message' => 'You are not Authorized,', 'code' => 401];
            // }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }

    function cldr(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $headers = apache_request_headers();
            session_start();
            date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']){

            if (isset($input['PHARMAID']) && isset($input['DRID'])) {
                $pharmaid = $input['PHARMAID'];
                $dcatg = $input['DCATG'];

                $data = DB::table('drprofile')
                    ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
                    // ->join('pharmacy', 'dr_availablity.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
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
                        'dr_availablity.DESCRIPTION as SCH_WEEK',
                        'dr_availablity.SCH_DAY'
                    )
                    ->where(['drprofile.D_CATG' => $dcatg, 'dr_availablity.PHARMA_ID' => $pharmaid])
                    ->where('drprofile.APPROVE', 'true')
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->get();
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid parameter.', 'code' => 422];
            }
            // }else {
            //     $response = ['Success' => false, 'Message' => 'You are not Authorized,', 'code' => 401];
            // }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }
    //     public function nearlivedr(Request $req)
// {
//     if ($req->isMethod('post')) {
//         $input = $req->json()->all();
//         if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
//             date_default_timezone_set('Asia/Kolkata');
//             $latt = $input['LATITUDE'];
//             $lont = $input['LONGITUDE'];
//             $weekNumber = Carbon::now()->weekOfMonth;
//             $day1 = date('l');
//             $cdy = date('d');

    //             try {
//                 $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));

    //                 // Fetch promo banners
//                 $promo_bnr = DB::table('promo_banner')
//                     ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
//                     ->whereIn('DASH_SECTION_ID', ['SP', 'SM'])
//                     ->get();

    //                 $bnr['Banner'] = $promo_bnr->where('DASH_SECTION_ID', 'SP')->take(3)->values()->all();

    //                 // Fetch specialist categories
//                 $dcat['specialist'] = DB::table('dis_catg')
//                     ->select(
//                         'DIS_ID', 'DASH_SECTION_ID', 'DIS_TYPE', 'DIS_CATEGORY', 'SPECIALIST', 'SPECIALITY',
//                         'DISIMG1', 'DISIMG2', 'DISIMG3', 'DISIMG4', 'DISIMG5', 'DISIMG6', 'DISIMG7', 'DISIMG8',
//                         'DISIMG9', 'DISIMG10', 'DISBNR1', 'DISBNR2', 'DISBNR3', 'DISBNR4', 'DISBNR5', 'DISBNR6',
//                         'DISBNR7', 'DISBNR8', 'DISBNR9', 'DISBNR10', 'DISQA1', 'DISQA2', 'DISQA3', 'DISQA4',
//                         'DISQA5', 'DISQA6', 'DISQA7', 'DISQA8', 'DISQA9'
//                     )
//                     ->orderBy('DIS_SL')
//                     ->get()
//                     ->map(function ($item) {
//                         return [
//                             "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
//                             "DIS_ID" => $item->DIS_ID,
//                             "DIS_TYPE" => $item->DIS_TYPE,
//                             "DIS_CATEGORY" => $item->DIS_CATEGORY,
//                             "SPECIALIST" => $item->SPECIALIST,
//                             "SPECIALITY" => $item->SPECIALITY,
//                             "PHOTO_URL1" => $item->DISIMG1,
//                             "PHOTO_URL2" => $item->DISIMG2,
//                             "PHOTO_URL3" => $item->DISIMG3,
//                             "PHOTO_URL4" => $item->DISIMG4,
//                             "PHOTO_URL5" => $item->DISIMG5,
//                             "PHOTO_URL6" => $item->DISIMG6,
//                             "PHOTO_URL7" => $item->DISIMG7,
//                             "PHOTO_URL8" => $item->DISIMG8,
//                             "PHOTO_URL9" => $item->DISIMG9,
//                             "PHOTO_URL10" => $item->DISIMG10,
//                             "BANNER_URL1" => $item->DISBNR1,
//                             "BANNER_URL2" => $item->DISBNR2,
//                             "BANNER_URL3" => $item->DISBNR3,
//                             "BANNER_URL4" => $item->DISBNR4,
//                             "BANNER_URL5" => $item->DISBNR5,
//                             "BANNER_URL6" => $item->DISBNR6,
//                             "BANNER_URL7" => $item->DISBNR7,
//                             "BANNER_URL8" => $item->DISBNR8,
//                             "BANNER_URL9" => $item->DISBNR9,
//                             "BANNER_URL10" => $item->DISBNR10,
//                             "Questions" => [
//                                 "QA1" => $item->DISQA1,
//                                 "QA2" => $item->DISQA2,
//                                 "QA3" => $item->DISQA3,
//                                 "QA4" => $item->DISQA4,
//                                 "QA5" => $item->DISQA5,
//                                 "QA6" => $item->DISQA6,
//                                 "QA7" => $item->DISQA7,
//                                 "QA8" => $item->DISQA8,
//                                 "QA9" => $item->DISQA9
//                             ]
//                         ];
//                     })
//                     ->values()
//                     ->all();

    //                 // Fetch nearby doctors
//                 $data1 = DB::table('pharmacy')
//                     ->join('dr_availablity', function ($join) use ($day1, $weekNumber, $cdy) {
//                         $join->on('pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
//                             ->where(function ($query) use ($day1, $weekNumber, $cdy) {
//                                 $query->where('dr_availablity.SCH_DAY', $day1)
//                                     ->where('dr_availablity.WEEK', 'like', '%' . $weekNumber . '%')
//                                     ->orWhere('dr_availablity.SCH_DT', $cdy);
//                             });
//                     })
//                     ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
//                     ->distinct('drprofile.DR_ID')
//                     ->select(
//                         'pharmacy.PHARMA_ID',
//                         'pharmacy.ITEM_NAME',
//                         'pharmacy.ADDRESS',
//                         'pharmacy.CITY',
//                         'pharmacy.CLINIC_TYPE',
//                         'pharmacy.PIN',
//                         'pharmacy.DIST',
//                         'pharmacy.STATE',
//                         'pharmacy.LATITUDE',
//                         'pharmacy.LONGITUDE',
//                         'pharmacy.PHOTO_URL',
//                         'pharmacy.LOGO_URL',
//                         DB::raw("ROUND(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.LATITUDE)) 
//                             * COS(RADIANS(?)) * COS(RADIANS(pharmacy.LONGITUDE - ?)) + SIN(RADIANS(pharmacy.LATITUDE)) 
//                             * SIN(RADIANS(?)))))), 2) as KM", [$latt, $lont, $latt]),
//                         'drprofile.DR_ID',
//                         'drprofile.DR_NAME',
//                         'drprofile.DR_MOBILE',
//                         'drprofile.SEX',
//                         'drprofile.DESIGNATION',
//                         'drprofile.QUALIFICATION',
//                         'drprofile.D_CATG',
//                         'drprofile.EXPERIENCE',
//                         'drprofile.LANGUAGE',
//                         'drprofile.PHOTO_URL AS DR_PHOTO',
//                         'dr_availablity.DR_FEES',
//                         'dr_availablity.ID',
//                         'dr_availablity.SCH_DAY',
//                         'dr_availablity.START_MONTH',
//                         'dr_availablity.MONTH',
//                         'dr_availablity.ABS_FDT',
//                         'dr_availablity.ABS_TDT',
//                         'dr_availablity.CHK_IN_TIME',
//                         'dr_availablity.CHK_IN_TIME1',
//                         'dr_availablity.CHK_IN_TIME2',
//                         'dr_availablity.CHK_IN_TIME3',
//                         'dr_availablity.CHK_OUT_TIME',
//                         'dr_availablity.CHK_OUT_TIME1',
//                         'dr_availablity.CHK_OUT_TIME2',
//                         'dr_availablity.CHK_OUT_TIME3',
//                         'dr_availablity.CHK_IN_STATUS',
//                         'dr_availablity.CHK_IN_STATUS1',
//                         'dr_availablity.CHK_IN_STATUS2',
//                         'dr_availablity.CHK_IN_STATUS3',
//                         'dr_availablity.CHEMBER_NO',
//                         'dr_availablity.CHEMBER_NO1',
//                         'dr_availablity.CHEMBER_NO2',
//                         'dr_availablity.CHEMBER_NO3',
//                         'dr_availablity.DR_ARRIVE',
//                         'dr_availablity.DR_ARRIVE1',
//                         'dr_availablity.DR_ARRIVE2',
//                         'dr_availablity.DR_ARRIVE3',
//                         'dr_availablity.DR_DELAY',
//                         'dr_availablity.DR_DELAY1',
//                         'dr_availablity.DR_DELAY2',
//                         'dr_availablity.DR_DELAY3',
//                         'dr_availablity.MAX_BOOK',
//                         'dr_availablity.MAX_BOOK1',
//                         'dr_availablity.MAX_BOOK2',
//                         'dr_availablity.MAX_BOOK3',
//                         'dr_availablity.SLOT_INTVL'
//                     )
//                     ->where('pharmacy.STATUS', '=', 'Active')
//                     ->get();

    //                 // Fetch promo clinics
//                 $pha['promoclinic'] = DB::table('pharmacy')
//                     ->select(
//                         'pharmacy.PHARMA_ID',
//                         'pharmacy.ITEM_NAME AS PHARMA_NAME',
//                         'pharmacy.ADDRESS',
//                         'pharmacy.CITY',
//                         'pharmacy.CLINIC_TYPE',
//                         'pharmacy.PIN',
//                         'pharmacy.DIST',
//                         'pharmacy.STATE',
//                         'pharmacy.LATITUDE',
//                         'pharmacy.LONGITUDE',
//                         'pharmacy.PHOTO_URL',
//                         'pharmacy.LOGO_URL',
//                         DB::raw("ROUND(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.LATITUDE)) 
//                             * COS(RADIANS(?)) * COS(RADIANS(pharmacy.LONGITUDE - ?)) + SIN(RADIANS(pharmacy.LATITUDE)) 
//                             * SIN(RADIANS(?)))))), 2) as KM", [$latt, $lont, $latt])
//                     )
//                     ->whereIn('CLINIC_TYPE', ['Clinic', 'Hospital', 'Diagnostic'])
//                     ->where('pharmacy.STATUS', '=', 'Active')
//                     ->orderBy('pharmacy.VALID_DT', 'desc')
//                     ->orderByRaw('KM')
//                     ->take(25)
//                     ->get();

    //                 // Fetch symptoms details
//                 $SYM_DTL = DB::table('symptoms')
//                     ->select(
//                         'SYM_ID', 'SYM_NAME', 'DIS_ID', 'DIS_CATEGORY', 'SYMIMG1', 'SYMIMG2', 'SYMIMG3', 'SYMIMG4',
//                         'SYMIMG5', 'SYMIMG6', 'SYMIMG7', 'SYMIMG8', 'SYMIMG9', 'SYMIMG10', 'SYMBNR1', 'SYMBNR2',
//                         'SYMBNR3', 'SYMBNR4', 'SYMBNR5', 'SYMBNR6', 'SYMBNR7', 'SYMBNR8', 'SYMBNR9', 'SYMBNR10',
//                         'SYMQA1', 'SYMQA2', 'SYMQA3', 'SYMQA4', 'SYMQA5', 'SYMQA6', 'SYMQA7', 'SYMQA8', 'SYMQA9'
//                     )
//                     ->orderBy('SYM_SL')
//                     ->take(10)
//                     ->get()
//                     ->map(function ($item) {
//                         return [
//                             "SYM_ID" => $item->SYM_ID,
//                             "SYM_NAME" => $item->SYM_NAME,
//                             "DIS_ID" => $item->DIS_ID,
//                             "DIS_CATEGORY" => $item->DIS_CATEGORY,
//                             "PHOTO_URL1" => $item->SYMIMG1,
//                             "PHOTO_URL2" => $item->SYMIMG2,
//                             "PHOTO_URL3" => $item->SYMIMG3,
//                             "PHOTO_URL4" => $item->SYMIMG4,
//                             "PHOTO_URL5" => $item->SYMIMG5,
//                             "PHOTO_URL6" => $item->SYMIMG6,
//                             "PHOTO_URL7" => $item->SYMIMG7,
//                             "PHOTO_URL8" => $item->SYMIMG8,
//                             "PHOTO_URL9" => $item->SYMIMG9,
//                             "PHOTO_URL10" => $item->SYMIMG10,
//                             "BANNER_URL1" => $item->SYMBNR1,
//                             "BANNER_URL2" => $item->SYMBNR2,
//                             "BANNER_URL3" => $item->SYMBNR3,
//                             "BANNER_URL4" => $item->SYMBNR4,
//                             "BANNER_URL5" => $item->SYMBNR5,
//                             "BANNER_URL6" => $item->SYMBNR6,
//                             "BANNER_URL7" => $item->SYMBNR7,
//                             "BANNER_URL8" => $item->SYMBNR8,
//                             "BANNER_URL9" => $item->SYMBNR9,
//                             "BANNER_URL10" => $item->SYMBNR10,
//                             "Questions" => [
//                                 "QA1" => $item->SYMQA1,
//                                 "QA2" => $item->SYMQA2,
//                                 "QA3" => $item->SYMQA3,
//                                 "QA4" => $item->SYMQA4,
//                                 "QA5" => $item->SYMQA5,
//                                 "QA6" => $item->SYMQA6,
//                                 "QA7" => $item->SYMQA7,
//                                 "QA8" => $item->SYMQA8,
//                                 "QA9" => $item->SYMQA9
//                             ]
//                         ];
//                     })
//                     ->values()
//                     ->all();

    //                 $SMB["Symptoms_Banner"] = $promo_bnr->where('DASH_SECTION_ID', 'SM')->take(3)->values()->all();
//                 $SYM["Symptoms"] = array_merge($SYM_DTL, $SMB);

    //                 $data = array_merge($bnr, $dcat, $pha, $SYM);

    //                 if (empty($data)) {
//                     $response = ['Success' => false, 'Message' => 'Dr. not found', 'code' => 200];
//                 } else {
//                     $response = ['Success' => true, 'data' => $data, 'code' => 200];
//                 }
//             } catch (\Exception $e) {
//                 $response = ['Success' => false, 'Message' => 'An error occurred', 'code' => 500, 'error' => $e->getMessage()];
//             }
//         } else {
//             $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
//         }
//     } else {
//         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
//     }

    //     return response()->json($response);
// }


    // public function nearlivedr(Request $req)
// {
//     // Check the request method
//     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//         return response()->json(['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405]);
//     }

    //     $input = $req->json()->all();

    //     if (!isset($input['LATITUDE']) || !isset($input['LONGITUDE'])) {
//         return response()->json(['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422]);
//     }

    //     // Set timezone
//     date_default_timezone_set('Asia/Kolkata');
//     $latt = $input['LATITUDE'];
//     $lont = $input['LONGITUDE'];
//     $currentDate = date('Ymd');
//     $weekNumber = Carbon::now()->weekOfMonth;
//                 $day1 = date('l');
//                 $response = array();
//                 $data = array();
//                 $cdy = date('d');
//                 $cdt = date('Ymd');
//     $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));

    //     // Initialize response data
//     $response = [];
//     $data = [];

    //     try {
//         // Open a new database connection
//         DB::beginTransaction();

    //         // Perform database operations
//         $promo_bnr = DB::table('promo_banner')
//             ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
//             ->whereIn('DASH_SECTION_ID', ['SP', 'SM'])
//             ->get();

    //         $fltr_promo_bnr = $promo_bnr->filter(fn($item) => $item->DASH_SECTION_ID === 'SP');
//         $bnr['Banner'] = $fltr_promo_bnr->take(3)->values()->all();

    //         $dcat['specialist'] = DB::table('dis_catg')
//             ->select(
//                 'DIS_ID', 'DASH_SECTION_ID', 'DIS_TYPE', 'DIS_CATEGORY', 'SPECIALIST', 'SPECIALITY',
//                 'DISIMG1', 'DISIMG2', 'DISIMG3', 'DISIMG4', 'DISIMG5', 'DISIMG6', 'DISIMG7', 'DISIMG8',
//                 'DISIMG9', 'DISIMG10', 'DISBNR1', 'DISBNR2', 'DISBNR3', 'DISBNR4', 'DISBNR5', 'DISBNR6',
//                 'DISBNR7', 'DISBNR8', 'DISBNR9', 'DISBNR10', 'DISQA1', 'DISQA2', 'DISQA3', 'DISQA4', 
//                 'DISQA5', 'DISQA6', 'DISQA7', 'DISQA8', 'DISQA9'
//             )
//             ->orderBy('DIS_SL')
//             ->get()
//             ->map(function ($item) {
//                 return [
//                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
//                     "DIS_ID" => $item->DIS_ID,
//                     "DIS_TYPE" => $item->DIS_TYPE,
//                     "DIS_CATEGORY" => $item->DIS_CATEGORY,
//                     "SPECIALIST" => $item->SPECIALIST,
//                     "SPECIALITY" => $item->SPECIALITY,
//                     "PHOTO_URL1" => $item->DISIMG1,
//                     "PHOTO_URL2" => $item->DISIMG2,
//                     "PHOTO_URL3" => $item->DISIMG3,
//                     "PHOTO_URL4" => $item->DISIMG4,
//                     "PHOTO_URL5" => $item->DISIMG5,
//                     "PHOTO_URL6" => $item->DISIMG6,
//                     "PHOTO_URL7" => $item->DISIMG7,
//                     "PHOTO_URL8" => $item->DISIMG8,
//                     "PHOTO_URL9" => $item->DISIMG9,
//                     "PHOTO_URL10" => $item->DISIMG10,
//                     "BANNER_URL1" => $item->DISBNR1,
//                     "BANNER_URL2" => $item->DISBNR2,
//                     "BANNER_URL3" => $item->DISBNR3,
//                     "BANNER_URL4" => $item->DISBNR4,
//                     "BANNER_URL5" => $item->DISBNR5,
//                     "BANNER_URL6" => $item->DISBNR6,
//                     "BANNER_URL7" => $item->DISBNR7,
//                     "BANNER_URL8" => $item->DISBNR8,
//                     "BANNER_URL9" => $item->DISBNR9,
//                     "BANNER_URL10" => $item->DISBNR10,
//                     "Questions" => [
//                         "QA1" => $item->DISQA1,
//                         "QA2" => $item->DISQA2,
//                         "QA3" => $item->DISQA3,
//                         "QA4" => $item->DISQA4,
//                         "QA5" => $item->DISQA5,
//                         "QA6" => $item->DISQA6,
//                         "QA7" => $item->DISQA7,
//                         "QA8" => $item->DISQA8,
//                         "QA9" => $item->DISQA9
//                     ]
//                 ];
//             })->values()->all();

    //         $pha['promoclinic'] = DB::table('pharmacy')
//             ->select(
//                 'pharmacy.PHARMA_ID', 'pharmacy.ITEM_NAME AS PHARMA_NAME', 'pharmacy.ADDRESS', 
//                 'pharmacy.CITY', 'pharmacy.CLINIC_TYPE', 'pharmacy.PIN', 'pharmacy.DIST', 
//                 'pharmacy.STATE', 'pharmacy.LATITUDE', 'pharmacy.LONGITUDE', 'pharmacy.PHOTO_URL', 
//                 'pharmacy.LOGO_URL', DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
//                 * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
//                 * SIN(RADIANS('$latt'))))),2) as KM")
//             )
//             ->whereIn('CLINIC_TYPE', ['Clinic', 'Hospital', 'Diagnostic'])
//             ->where('pharmacy.STATUS', '=', 'Active')
//             ->orderBy('pharmacy.VALID_DT', 'desc')
//             ->orderByRaw('KM')
//             ->take(25)
//             ->get();

    //         $SYM_DTL = DB::table('symptoms')
//             ->select(
//                 'SYM_ID', 'SYM_NAME', 'DIS_ID', 'DIS_CATEGORY', 'SYMIMG1', 'SYMIMG2', 'SYMIMG3',
//                 'SYMIMG4', 'SYMIMG5', 'SYMIMG6', 'SYMIMG7', 'SYMIMG8', 'SYMIMG9', 'SYMIMG10',
//                 'SYMBNR1', 'SYMBNR2', 'SYMBNR3', 'SYMBNR4', 'SYMBNR5', 'SYMBNR6', 'SYMBNR7',
//                 'SYMBNR8', 'SYMBNR9', 'SYMBNR10', 'SYMQA1', 'SYMQA2', 'SYMQA3', 'SYMQA4', 'SYMQA5',
//                 'SYMQA6', 'SYMQA7', 'SYMQA8', 'SYMQA9'
//             )
//             ->orderBy('SYM_SL')
//             ->take(10)
//             ->get()
//             ->map(function ($item) {
//                 return [
//                     "SYM_ID" => $item->SYM_ID,
//                     "SYM_NAME" => $item->SYM_NAME,
//                     "DIS_ID" => $item->DIS_ID,
//                     "DIS_CATEGORY" => $item->DIS_CATEGORY,
//                     "PHOTO_URL1" => $item->SYMIMG1,
//                     "PHOTO_URL2" => $item->SYMIMG2,
//                     "PHOTO_URL3" => $item->SYMIMG3,
//                     "PHOTO_URL4" => $item->SYMIMG4,
//                     "PHOTO_URL5" => $item->SYMIMG5,
//                     "PHOTO_URL6" => $item->SYMIMG6,
//                     "PHOTO_URL7" => $item->SYMIMG7,
//                     "PHOTO_URL8" => $item->SYMIMG8,
//                     "PHOTO_URL9" => $item->SYMIMG9,
//                     "PHOTO_URL10" => $item->SYMIMG10,
//                     "BANNER_URL1" => $item->SYMBNR1,
//                     "BANNER_URL2" => $item->SYMBNR2,
//                     "BANNER_URL3" => $item->SYMBNR3,
//                     "BANNER_URL4" => $item->SYMBNR4,
//                     "BANNER_URL5" => $item->SYMBNR5,
//                     "BANNER_URL6" => $item->SYMBNR6,
//                     "BANNER_URL7" => $item->SYMBNR7,
//                     "BANNER_URL8" => $item->SYMBNR8,
//                     "BANNER_URL9" => $item->SYMBNR9,
//                     "BANNER_URL10" => $item->SYMBNR10,
//                     "Questions" => [
//                         "QA1" => $item->SYMQA1,
//                         "QA2" => $item->SYMQA2,
//                         "QA3" => $item->SYMQA3,
//                         "QA4" => $item->SYMQA4,
//                         "QA5" => $item->SYMQA5,
//                         "QA6" => $item->SYMQA6,
//                         "QA7" => $item->SYMQA7,
//                         "QA8" => $item->SYMQA8,
//                         "QA9" => $item->SYMQA9
//                     ]
//                 ];
//             })->values()->all();

    //         $fltr_promo_bnr = $promo_bnr->filter(fn($item) => $item->DASH_SECTION_ID === 'SM');
//         $SMB["Symptoms_Banner"] = $fltr_promo_bnr->take(3)->values()->all();
//         $SYM["Symptoms"] = array_values($SYM_DTL + $SMB);

    //         $data1 = DB::table('pharmacy')
//     ->join('dr_availablity', function ($join) use ($day1, $weekNumber, $currentDate) {
//         $join->on('pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
//             ->where(function ($query) use ($day1, $weekNumber, $currentDate) {
//                 $query->where('dr_availablity.SCH_DAY', $day1)
//                     ->where('dr_availablity.WEEK', 'like', '%' . $weekNumber . '%')
//                     ->orWhere('dr_availablity.SCH_DT', $currentDate);
//             });
//     })
//     ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
//     ->select(
//         'pharmacy.PHARMA_ID',
//         'pharmacy.ITEM_NAME',
//         'pharmacy.ADDRESS',
//         'pharmacy.CITY',
//         'pharmacy.CLINIC_TYPE',
//         'pharmacy.PIN',
//         'pharmacy.DIST',
//         'pharmacy.STATE',
//         'pharmacy.LATITUDE',
//         'pharmacy.LONGITUDE',
//         'pharmacy.PHOTO_URL',
//         'pharmacy.LOGO_URL',
//         DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
//         * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
//         * SIN(RADIANS('$latt'))))),2) as KM"),
//         'drprofile.DR_ID',
//         'drprofile.DR_NAME',
//         'drprofile.DR_MOBILE',
//         'drprofile.SEX',
//         'drprofile.DESIGNATION',
//         'drprofile.QUALIFICATION',
//         'drprofile.D_CATG',
//         'drprofile.EXPERIENCE',
//         'drprofile.LANGUAGE',
//         'drprofile.PHOTO_URL AS DR_PHOTO',
//         'dr_availablity.DR_FEES',
//         'dr_availablity.ID',
//         'dr_availablity.SCH_DAY',
//         'dr_availablity.START_MONTH',
//         'dr_availablity.MONTH',
//         'dr_availablity.ABS_FDT',
//         'dr_availablity.ABS_TDT',
//         'dr_availablity.CHK_IN_TIME',
//         'dr_availablity.CHK_IN_TIME1',
//         'dr_availablity.CHK_IN_TIME2',
//         'dr_availablity.CHK_IN_TIME3',
//         'dr_availablity.CHK_OUT_TIME',
//         'dr_availablity.CHK_OUT_TIME1',
//         'dr_availablity.CHK_OUT_TIME2',
//         'dr_availablity.CHK_OUT_TIME3',
//         'dr_availablity.CHK_IN_STATUS',
//         'dr_availablity.CHK_IN_STATUS1',
//         'dr_availablity.CHK_IN_STATUS2',
//         'dr_availablity.CHK_IN_STATUS3',
//         'dr_availablity.CHEMBER_NO',
//         'dr_availablity.CHEMBER_NO1',
//         'dr_availablity.CHEMBER_NO2',
//         'dr_availablity.CHEMBER_NO3',
//         'dr_availablity.DR_ARRIVE',
//         'dr_availablity.DR_ARRIVE1',
//         'dr_availablity.DR_ARRIVE2',
//         'dr_availablity.DR_ARRIVE3',
//         'dr_availablity.DR_DELAY',
//         'dr_availablity.DR_DELAY1',
//         'dr_availablity.DR_DELAY2',
//         'dr_availablity.DR_DELAY3',
//         'dr_availablity.MAX_BOOK',
//         'dr_availablity.MAX_BOOK1',
//         'dr_availablity.MAX_BOOK2',
//         'dr_availablity.MAX_BOOK3',
//         'dr_availablity.SLOT_INTVL'
//     )
//     ->where('pharmacy.STATUS', '=', 'Active')
//     ->get();

    // $ldr = [];
// $currentDate = Carbon::now();
// $currentTime = Carbon::createFromFormat('h:i A', $currentDate->format('h:i A'));

    // foreach ($data1 as $doctor) {
//     if (is_numeric($doctor->SCH_DAY)) {
//         $date = Carbon::createFromDate(date('Y'), $doctor->START_MONTH, $doctor->SCH_DAY)
//             ->addMonths($doctor->MONTH);
//         $sch_dt = $date->format('Ymd');
//     } else {
//         $sch_dt = Carbon::now()->format('Ymd');
//     }

    //     // Handle non-null check-in times and statuses
//     $chkInTimes = [
//         $doctor->CHK_IN_TIME,
//         $doctor->CHK_IN_TIME1,
//         $doctor->CHK_IN_TIME2,
//         $doctor->CHK_IN_TIME3
//     ];
//     $chkOutTimes = [
//         $doctor->CHK_OUT_TIME,
//         $doctor->CHK_OUT_TIME1,
//         $doctor->CHK_OUT_TIME2,
//         $doctor->CHK_OUT_TIME3
//     ];
//     $chkInStatuses = [
//         $doctor->CHK_IN_STATUS,
//         $doctor->CHK_IN_STATUS1,
//         $doctor->CHK_IN_STATUS2,
//         $doctor->CHK_IN_STATUS3
//     ];
//     $chambers = [
//         $doctor->CHEMBER_NO,
//         $doctor->CHEMBER_NO1,
//         $doctor->CHEMBER_NO2,
//         $doctor->CHEMBER_NO3
//     ];
//     $delays = [
//         $doctor->DR_DELAY,
//         $doctor->DR_DELAY1,
//         $doctor->DR_DELAY2,
//         $doctor->DR_DELAY3
//     ];

    //     $nonNullChkInTimes = array_filter($chkInTimes, function ($time) {
//         return !empty($time);
//     });

    //     foreach ($nonNullChkInTimes as $i => $chkin) {
//         $checkOutTime = Carbon::parse($chkOutTimes[$i]);
//         if (!empty($delays[$i])) {
//             $checkOutTime = $checkOutTime->addMinutes($delays[$i]);
//         }

    //         if ($currentTime->lessThanOrEqualTo($checkOutTime)) {
//             if (!in_array($chkInStatuses[$i], ['OUT', 'CANCELLED', 'LEAVE'])) {
//                 $doctor->CHK_IN_TIME = $chkin;
//                 $doctor->CHK_OUT_TIME = $chkOutTimes[$i];
//                 $doctor->CHK_IN_STATUS = $chkInStatuses[$i];
//                 $doctor->CHEMBER_NO = $chambers[$i];
//                 $doctor->DR_DELAY = $delays[$i] ?? null;
//                 break;
//             }
//        else {
//             $nextIndex = $i + 1;
//             if ($nextIndex < count($chkInTimes) && !empty($chkInTimes[$nextIndex]) && !in_array($chkInStatuses[$nextIndex], ['OUT', 'CANCELLED'])) {
//                 $doctor->CHK_IN_TIME = $chkInTimes[$nextIndex];
//                 $doctor->CHK_OUT_TIME = $chkOutTimes[$nextIndex];
//                 $doctor->CHK_IN_STATUS = $chkInStatuses[$nextIndex];
//                 $doctor->CHEMBER_NO = $chambers[$nextIndex];
//                 $doctor->DR_DELAY = $delays[$nextIndex] ?? null;
//             } else {
//                 $doctor->CHK_IN_TIME = $chkin;
//                 $doctor->CHK_OUT_TIME = $chkOutTimes[$i];
//                 $doctor->CHK_IN_STATUS = $chkInStatuses[$i];
//                 $doctor->CHEMBER_NO = $chambers[$i];
//                 $doctor->DR_DELAY = $delays[$i] ?? null;
//             }
//         }
//     }else {
//                 $doctor->CHK_IN_TIME = $chkin;
//                 $doctor->CHK_OUT_TIME = $chkOutTimes[$i];
//                 $doctor->CHK_IN_STATUS = 'OUT';
//                 $doctor->CHEMBER_NO = NULL;
//             }
//     }

    //     $maxBookDoctorSum = array_sum(array_filter([
//         $doctor->MAX_BOOK,
//         $doctor->MAX_BOOK1,
//         $doctor->MAX_BOOK2,
//         $doctor->MAX_BOOK3
//     ]));

    //     $doctor->MAX_BOOK = $maxBookDoctorSum;

    //     $drid = $doctor->DR_ID;
//     $fid = $doctor->PHARMA_ID;
//     $apid = $doctor->ID;
//     $apdt = $sch_dt;
//     $cdt = date('Ymd');

    //     $data1 = DB::table('dr_availablity')
//         ->where(['DR_ID' => $drid, 'PHARMA_ID' => $fid, 'ID' => $apid])
//         ->first();

    //     $chkInTimes = [
//         $data1->CHK_IN_TIME,
//         $data1->CHK_IN_TIME1,
//         $data1->CHK_IN_TIME2,
//         $data1->CHK_IN_TIME3
//     ];
//     $chkOutTimes = [
//         $data1->CHK_OUT_TIME,
//         $data1->CHK_OUT_TIME1,
//         $data1->CHK_OUT_TIME2,
//         $data1->CHK_OUT_TIME3
//     ];
//     $maxbooks = [
//         $data1->MAX_BOOK,
//         $data1->MAX_BOOK1,
//         $data1->MAX_BOOK2,
//         $data1->MAX_BOOK3
//     ];
//     $chkInSts = [
//         $data1->CHK_IN_STATUS,
//         $data1->CHK_IN_STATUS1,
//         $data1->CHK_IN_STATUS2,
//         $data1->CHK_IN_STATUS3
//     ];
//     $intvl = $data1->SLOT_INTVL ?? null;
//     $matchingSlot = null;
//     $nextAvailableSlot = null;

    //     foreach ($chkInTimes as $index => $chkin) {
//         $maxbk = $maxbooks[$index];
//         $chkout = $chkOutTimes[$index];
//         $ckinsts = $chkInSts[$index];
//         if ($chkin === null) {
//             continue;
//         }

    //         try {
//             $chkinTime = Carbon::createFromFormat('h:i A', $chkin);
//             $chkoutTime = $chkout !== null ? Carbon::createFromFormat('h:i A', $chkout) : $chkinTime->copy()->addMinutes($intvl * $maxbk);
//         } catch (\Exception $e) {
//             return ["Error" => "Error in time conversion: " . $e->getMessage()];
//         }

    //         $slot_sts = null;

    //         if ($data1->SLOT == '1') {
//             while ($chkinTime->lessThan($chkoutTime)) {
//                 $endSlot = $chkinTime->copy()->addHour();
//                 if ($endSlot->greaterThan($chkoutTime)) {
//                     $endSlot = $chkoutTime;
//                 }

    //                 $bookedCount = DB::table('appointment')
//                     ->where('APPNT_FROM', '>=', $chkinTime->format('h:i A'))
//                     ->where('APPNT_TO', '<=', $endSlot->format('h:i A'))
//                     ->where(['APPNT_ID' => $apid, 'APPNT_DT' => $apdt])
//                     ->count();

    //                 Log::info('Booked Count: ' . $bookedCount);

    //                 $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
//                 $bookingSerials = range(1, $totalAppointments);
//                 $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));
//                 $availAppointments = count($availableSerials);

    //                 if ($cdt === $apdt) {
//                     $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
//                 } else {
//                     $slot_sts = "Available";
//                 }
//                 if (in_array($ckinsts, ['CANCELLED', 'OUT', 'LEAVE'])) {
//                     $slot_sts = "Closed";
//                 }

    //                 $slotString = [
//                     "FROM" => $chkinTime->format('h:i A'),
//                     "TO" => $endSlot->format('h:i A'),
//                     "TOTAL_APNT" => $totalAppointments,
//                     "AVAIL_APNT" => $availAppointments,
//                     "BOOKING_SERIALS" => $bookingSerials,
//                     "AVAILABLE_SERIALS" => $availableSerials,
//                     "SLOT_STATUS" => $slot_sts,
//                 ];

    //                 $slots[][] = $slotString;
//                 if ($currentTime->between($chkinTime, $endSlot) && $slot_sts === 'Available' && $availAppointments > 0) {
//                     $matchingSlot = $slotString;
//                     break 2;
//                 } elseif ($slot_sts === 'Available' && $nextAvailableSlot === null && $availAppointments > 0) {
//                     $nextAvailableSlot = $slotString;
//                 }

    //                 $chkinTime->addHour();
//             }
//         } else if ($data1->SLOT == '2') {
//             while ($chkinTime->lessThan($chkoutTime)) {
//                 $endSlot = $chkinTime->copy()->addMinutes($intvl);
//                 if ($endSlot->greaterThan($chkoutTime)) {
//                     break;
//                 }

    //                 $bookedCount = DB::table('appointment')
//                     ->where('APPNT_FROM', '>=', $chkinTime->format('h:i A'))
//                     ->where('APPNT_TO', '<=', $endSlot->format('h:i A'))
//                     ->where(['APPNT_ID' => $apid, 'APPNT_DT' => $apdt])
//                     ->count();

    //                 $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
//                 $bookingSerials = range(1, $totalAppointments);
//                 $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));
//                 $availAppointments = count($availableSerials);

    //                 if ($cdt === $apdt) {
//                     $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
//                 } else {
//                     $slot_sts = "Available";
//                 }
//                 if (in_array($ckinsts, ['CANCELLED', 'OUT', 'LEAVE'])) {
//                     $slot_sts = "Closed";
//                 }

    //                 $slotString = [
//                     "FROM" => $chkinTime->format('h:i A'),
//                     "TO" => $endSlot->format('h:i A'),
//                     "TOTAL_APNT" => $totalAppointments,
//                     "AVAIL_APNT" => $availAppointments,
//                     "BOOKING_SERIALS" => $bookingSerials,
//                     "AVAILABLE_SERIALS" => $availableSerials,
//                     "SLOT_STATUS" => $slot_sts,
//                 ];

    //                 $slots[][] = $slotString;

    //                 if ($currentTime->between($chkinTime, $endSlot) && $slot_sts === 'Available' && $availAppointments > 0) {
//                     $matchingSlot = $slotString;
//                     break 2;
//                 } elseif ($slot_sts === 'Available' && $nextAvailableSlot === null && $availAppointments > 0) {
//                     $nextAvailableSlot = $slotString;
//                 }

    //                 $chkinTime->addMinutes($intvl);
//             }
//         }
//     }

    //     if ($matchingSlot) {
//         $doctor->FROM = $matchingSlot['FROM'];
//     } elseif ($nextAvailableSlot) {
//         $doctor->FROM = $nextAvailableSlot['FROM'];
//     } else {
//         $doctor->FROM = null;
//     }

    //     $ldr['livedoctor'][] = [
//                                 "PHARMA_ID" => $doctor->PHARMA_ID,
//                                 "PHARMA_NAME" => $doctor->ITEM_NAME,
//                                 "ADDRESS" => $doctor->ADDRESS,
//                                 "CITY" => $doctor->CITY,
//                                 "PIN" => $doctor->PIN,
//                                 "DIST" => $doctor->DIST,
//                                 "STATE" => $doctor->STATE,
//                                 "LATITUDE" => $doctor->LATITUDE,
//                                 "LONGITUDE" => $doctor->LONGITUDE,
//                                 "PHOTO_URL" => $doctor->PHOTO_URL,
//                                 "LOGO_URL" => $doctor->LOGO_URL,
//                                 "DR_ID" => $doctor->DR_ID,
//                                 "DR_NAME" => $doctor->DR_NAME,
//                                 "DR_MOBILE" => $doctor->DR_MOBILE,
//                                 "SEX" => $doctor->SEX,
//                                 "DESIGNATION" => $doctor->DESIGNATION,
//                                 "QUALIFICATION" => $doctor->QUALIFICATION,
//                                 "D_CATG" => $doctor->D_CATG,
//                                 "EXPERIENCE" => $doctor->EXPERIENCE,
//                                 'KM' => $doctor->KM,
//                                 "LANGUAGE" => $doctor->LANGUAGE,
//                                 "DR_PHOTO" => $doctor->DR_PHOTO,
//                                 "DR_FEES" => $doctor->DR_FEES,
//                                 "SCH_ID" => $doctor->ID,
//                                 "SCH_DT" => $sch_dt,
//                                 "FROM" => $doctor->FROM,
//                                 "CHK_IN_TIME" => $doctor->CHK_IN_TIME,
//                                 "CHK_OUT_TIME" => $doctor->CHK_OUT_TIME,
//                                 "DR_STATUS" => $doctor->CHK_IN_STATUS,
//                                 "CHEMBER_NO" => $doctor->CHEMBER_NO,
//                                 "MAX_BOOK" => $maxBookDoctorSum,
//                             ];


    // }
// if (!empty($ldr['livedoctor'])) {
//                     usort($ldr['livedoctor'], function ($a, $b) {
//                         $statusOrder = ['IN' => 1, 'TIMELY' => 2, 'DELAY' => 3, 'CANCELLED' => 4, 'OUT' => 5, 'LEAVE' => 6];

    //                         // Compare by DR_STATUS first
//                         if ($statusOrder[$a['DR_STATUS']] != $statusOrder[$b['DR_STATUS']]) {
//                             return $statusOrder[$a['DR_STATUS']] <=> $statusOrder[$b['DR_STATUS']];
//                         }

    //                         // If DR_STATUS is the same, compare by FROM time
//                         if (strtotime($a['FROM']) != strtotime($b['FROM'])) {
//                             return strtotime($a['FROM']) <=> strtotime($b['FROM']);
//                         }

    //                         // If FROM time is the same, compare by CHK_IN_TIME
//                         if (strtotime($a['CHK_IN_TIME']) != strtotime($b['CHK_IN_TIME'])) {
//                             return strtotime($a['CHK_IN_TIME']) <=> strtotime($b['CHK_IN_TIME']);
//                         }

    //                         // If CHK_IN_TIME is the same, compare by DR_NAME
//                         return strcmp($a['DR_NAME'], $b['DR_NAME']);
//                     });
// }

    //         $data = $bnr + $dcat + $pha + $SYM + $ldr;

    //         // Commit the transaction
//         DB::commit();

    //         if ($data == null) {
//             $response = ['Success' => false, 'Message' => 'Dr. not found', 'code' => 200];
//         } else {
//             $response = ['Success' => true, 'data' => $data, 'code' => 200];
//         }
//     } catch (Exception $e) {
//         // Rollback the transaction in case of an error
//         DB::rollBack();
//         $response = ['Success' => false, 'Message' => 'An error occurred', 'code' => 500];
//     } finally {
//         // Ensure the database connection is closed
//         DB::disconnect();
//     }

    //     return response()->json($response);
// }




    public function nearlivedr(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                date_default_timezone_set('Asia/Kolkata');
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $weekNumber = Carbon::now()->weekOfMonth;
                $day1 = date('l');
                $response = array();
                $data = array();
                $cdy = date('d');
                $cdt = date('Ymd');
                $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));

                $promo_bnr = DB::table('promo_banner')
                    ->select('DASH_SECTION_ID', 'PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', 'SP')
                    ->orWhere('DASH_SECTION_ID', '=', 'SM')
                    ->get();

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SP';
                });
                $bnr['Banner'] = $fltr_promo_bnr->take(3)->values()->all();



                $dcat['specialist'] = DB::table('dis_catg')
                    ->select(
                        'DIS_ID',
                        'DASH_SECTION_ID',
                        'DIS_TYPE',
                        'DIS_CATEGORY',
                        'SPECIALIST',
                        'SPECIALITY',
                        'DISIMG1',
                        'DISIMG2',
                        'DISIMG3',
                        'DISIMG4',
                        'DISIMG5',
                        'DISIMG6',
                        'DISIMG7',
                        'DISIMG8',
                        'DISIMG9',
                        'DISIMG10',
                        'DISBNR1',
                        'DISBNR2',
                        'DISBNR3',
                        'DISBNR4',
                        'DISBNR5',
                        'DISBNR6',
                        'DISBNR7',
                        'DISBNR8',
                        'DISBNR9',
                        'DISBNR10',
                        'DISQA1',
                        'DISQA2',
                        'DISQA3',
                        'DISQA4',
                        'DISQA5',
                        'DISQA6',
                        'DISQA7',
                        'DISQA8',
                        'DISQA9',
                    )
                    ->orderBy('DIS_SL')->get()->map(function ($item) {
                        return [
                            "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                            "DIS_ID" => $item->DIS_ID,
                            "DIS_TYPE" => $item->DIS_TYPE,
                            "DIS_CATEGORY" => $item->DIS_CATEGORY,
                            "SPECIALIST" => $item->SPECIALIST,
                            "SPECIALITY" => $item->SPECIALITY,
                            "PHOTO_URL1" => $item->DISIMG1,
                            "PHOTO_URL2" => $item->DISIMG2,
                            "PHOTO_URL3" => $item->DISIMG3,
                            "PHOTO_URL4" => $item->DISIMG4,
                            "PHOTO_URL5" => $item->DISIMG5,
                            "PHOTO_URL6" => $item->DISIMG6,
                            "PHOTO_URL7" => $item->DISIMG7,
                            "PHOTO_URL8" => $item->DISIMG8,
                            "PHOTO_URL9" => $item->DISIMG9,
                            "PHOTO_URL10" => $item->DISIMG10,
                            "BANNER_URL1" => $item->DISBNR1,
                            "BANNER_URL2" => $item->DISBNR2,
                            "BANNER_URL3" => $item->DISBNR3,
                            "BANNER_URL4" => $item->DISBNR4,
                            "BANNER_URL5" => $item->DISBNR5,
                            "BANNER_URL6" => $item->DISBNR6,
                            "BANNER_URL7" => $item->DISBNR7,
                            "BANNER_URL8" => $item->DISBNR8,
                            "BANNER_URL9" => $item->DISBNR9,
                            "BANNER_URL10" => $item->DISBNR10,
                            "Questions" => [
                                "QA1" => $item->DISQA1,
                                "QA2" => $item->DISQA2,
                                "QA3" => $item->DISQA3,
                                "QA4" => $item->DISQA4,
                                "QA5" => $item->DISQA5,
                                "QA6" => $item->DISQA6,
                                "QA7" => $item->DISQA7,
                                "QA8" => $item->DISQA8,
                                "QA9" => $item->DISQA9
                            ]
                        ];
                    })->values()->all();

                
                 
            
                    $data1 = DB::table('pharmacy')
                        ->join('dr_availablity', function ($join) use ($day1, $weekNumber, $cdy) {
                            $join->on('pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
                            // ->where('dr_availablity.DR_ID','E87CE46B582ADC5')
                                ->where(function ($query) use ($day1, $weekNumber, $cdy) {
                                    $query->where('dr_availablity.SCH_DAY', $day1)
                                        ->where('dr_availablity.WEEK', 'like', '%' . $weekNumber . '%')
                                        ->orWhere('dr_availablity.SCH_DT', $cdy);
                                });
                        })
                        ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
                        ->select(
                            'pharmacy.PHARMA_ID',
                            'pharmacy.ITEM_NAME',
                            'pharmacy.ADDRESS',
                            'pharmacy.CITY',
                            'pharmacy.CLINIC_TYPE',
                            'pharmacy.PIN',
                            'pharmacy.DIST',
                            'pharmacy.STATE',
                            'pharmacy.LATITUDE',
                            'pharmacy.LONGITUDE',
                            'pharmacy.PHOTO_URL',
                            'pharmacy.LOGO_URL',
                            DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                        * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                        * SIN(RADIANS('$latt'))))),2) as KM"),
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
                            'dr_availablity.SLOT',
                            'dr_availablity.CHK_IN_TIME',
                            'dr_availablity.CHK_IN_TIME1',
                            'dr_availablity.CHK_IN_TIME2',
                            'dr_availablity.CHK_IN_TIME3',
                            'dr_availablity.CHK_OUT_TIME',
                            'dr_availablity.CHK_OUT_TIME1',
                            'dr_availablity.CHK_OUT_TIME2',
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
                        ->where('pharmacy.STATUS', '=', 'Active')
                        ->get();
                    
                    // RETURN $data1;
            
                    $ldr['livedoctor'] = [];
            
                    $appointments = DB::table('appointment')
                    ->select(
                        'APPNT_ID','APPNT_FROM','APPNT_TO'
                    )
                    // ->where('APPNT_ID', $doctor->ID)
                    ->where('APPNT_DT', $cdt)
                    ->get();
                
            
                    foreach ($data1 as $doctor) {
                        $chk = ["CHK_IN_TIME", "CHK_IN_TIME1", "CHK_IN_TIME2", "CHK_IN_TIME3"];
                        $chkout = ["CHK_OUT_TIME", "CHK_OUT_TIME1", "CHK_OUT_TIME2", "CHK_OUT_TIME3"];
                        $CHKINSTATUS = ["CHK_IN_STATUS", "CHK_IN_STATUS1", "CHK_IN_STATUS2", "CHK_IN_STATUS3"];
                        $delay = ["DR_DELAY", "DR_DELAY1", "DR_DELAY2", "DR_DELAY3"];
                        $chamber = ["CHEMBER_NO", "CHEMBER_NO1", "CHEMBER_NO2", "CHEMBER_NO3"];
                
                        if (is_numeric($doctor->SCH_DAY)) {
                            $date = Carbon::createFromDate(date('Y'), $doctor->START_MONTH, $doctor->SCH_DAY)
                                ->addMonths($doctor->MONTH);
                            if ($date->format('Ymd') === $cdt) {
                                $sch_dt = $date->format('Ymd');
                            }
                        } else {
                            $sch_dt = Carbon::now()->format('Ymd');
                        }
                
                        $intvl = $doctor->SLOT_INTVL ?? null;
                        $matchingSlot = null;
                        $nextAvailableSlot = null;
                
                        $nonNullChkInTimes = array_filter($chk, function ($time) use ($doctor) {
                            return !empty($doctor->{$time});
                        });
                        // log::info($doctor->ITEM_NAME);
                
                        foreach ($nonNullChkInTimes as $i => $chkin) {
                            $checkOutTime = Carbon::parse($doctor->{$chkout[$i]});
                            if (!empty($doctor->{$delay[$i]})) {
                                $checkOutTime = $checkOutTime->addMinutes($doctor->{$delay[$i]});
                            }
                
                            if ($currentTime->lessThanOrEqualTo($checkOutTime)) {
                                if (!in_array($doctor->{$CHKINSTATUS[$i]}, ['OUT', 'CANCELLED', 'LEAVE'])) {
                                    $doctor->DR_IN_TIME = $doctor->{$chkin};
                                    $doctor->DR_OUT_TIME = $doctor->{$chkout[$i]};
                                    $doctor->DR_IN_STATUS = $doctor->{$CHKINSTATUS[$i]};
                                    $doctor->CHEMBER_NO = $doctor->{$chamber[$i]};
                                } else {
                                    $nextIndex = $i + 1;
                                    if ($nextIndex < count($chk) && !empty($doctor->{$chk[$nextIndex]}) && !in_array($doctor->{$CHKINSTATUS[$nextIndex]}, ['OUT', 'CANCELLED'])) {
                                        $doctor->DR_IN_TIME = $doctor->{$chk[$nextIndex]};
                                        $doctor->DR_OUT_TIME = $doctor->{$chkout[$nextIndex]};
                                        $doctor->DR_IN_STATUS = $doctor->{$CHKINSTATUS[$nextIndex]};
                                        $doctor->CHEMBER_NO = $doctor->{$chamber[$nextIndex]};
                                    } else {
                                        $doctor->DR_IN_TIME = $doctor->{$chkin};
                                        $doctor->DR_OUT_TIME = $doctor->{$chkout[$i]};
                                        $doctor->DR_IN_STATUS = $doctor->{$CHKINSTATUS[$i]};
                                        $doctor->CHEMBER_NO = $doctor->{$chamber[$i]};
                                    }
                                }
                                // Calculate next available slot in the same loop
                             $chkinTime = Carbon::createFromFormat('h:i A', $doctor->{$chkin});
                             $chkoutTime1 = Carbon::createFromFormat('h:i A', $doctor->{$chkout[$i]});
                             if ($doctor->SLOT == '2'){
                                while ($chkinTime->lessThan($chkoutTime1) && $currentTime->lessThanOrEqualTo($checkOutTime)) {
                                    $endSlot = $chkinTime->copy()->addMinutes($intvl);
                                    if ($endSlot->greaterThan($chkoutTime1)) {
                                        break;
                                    }
                                    $schid=$doctor->ID;
                                    // Calculate the count of booked slots based on chkinTime and endslot
                                    $bookedCount = $appointments->filter(function ($appointment) use ($schid, $chkinTime, $endSlot) {
                                        return $appointment->APPNT_ID===$schid &&
                                        Carbon::createFromFormat('h:i A', $appointment->APPNT_TO)->lessThanOrEqualTo($endSlot) &&
                                               Carbon::createFromFormat('h:i A', $appointment->APPNT_FROM)->greaterThanOrEqualTo($chkinTime);
                                    })->count();
                
                                    $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                                    $bookingSerials = range(1, $totalAppointments);
                                    $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));
                                    $availAppointments = count($availableSerials);
                
                                    $slot_sts = ($cdt === $sch_dt && $endSlot->lessThan(Carbon::now())) ? "Closed" : "Available";
                                    if ($doctor->{$CHKINSTATUS[$i]} === 'CANCELLED' || $doctor->{$CHKINSTATUS[$i]} === 'OUT' || $doctor->{$CHKINSTATUS[$i]} === 'LEAVE') {
                                        $slot_sts = "Closed";
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
                
                                    if ($currentTime->between($chkinTime, $endSlot) && $slot_sts === 'Available' && $availAppointments > 0) {
                                        $matchingSlot = $slotString;
                                        break;
                                    } elseif ($slot_sts === 'Available' && $nextAvailableSlot === null && $availAppointments > 0) {
                                        $nextAvailableSlot = $slotString;
                                    }
                                    $chkinTime->addMinutes($intvl);
                                }
                             }else if ($doctor->SLOT == '1'){
                                while ($chkinTime->lessThan($chkoutTime1)) {
                                    $endSlot = $chkinTime->copy()->addHour();
                                    if ($endSlot->greaterThan($chkoutTime1)) {
                                        $endSlot = $chkoutTime1;
                                    }
                
                                    // Calculate the count of booked slots based on chkinTime and endslot
                                    $bookedCount = $appointments->filter(function ($appointment) use ($chkinTime, $endSlot) {
                                        return Carbon::createFromFormat('h:i A', $appointment->APPNT_TO)->lessThanOrEqualTo($endSlot) &&
                                               Carbon::createFromFormat('h:i A', $appointment->APPNT_FROM)->greaterThanOrEqualTo($chkinTime);
                                    })->count();
                
                                    $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                                    $bookingSerials = range(1, $totalAppointments);
                                    $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));
                                    $availAppointments = count($availableSerials);
                
                                    $slot_sts = ($cdt === $sch_dt && $endSlot->lessThan(Carbon::now())) ? "Closed" : "Available";
                                    if ($doctor->{$CHKINSTATUS[$i]} === 'CANCELLED' || $doctor->{$CHKINSTATUS[$i]} === 'OUT' || $doctor->{$CHKINSTATUS[$i]} === 'LEAVE') {
                                        $slot_sts = "Closed";
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
                
                                    if ($currentTime->between($chkinTime, $endSlot) && $slot_sts === 'Available' && $availAppointments > 0) {
                                        $matchingSlot = $slotString;
                                        break;
                                    } elseif ($slot_sts === 'Available' && $nextAvailableSlot === null && $availAppointments > 0) {
                                        $nextAvailableSlot = $slotString;
                                    }
                                    $chkinTime->addHour();
                                }
                             }
                             
                                
                            } else {
                                $doctor->DR_IN_TIME = $doctor->{$chkin};
                                $doctor->DR_OUT_TIME = $doctor->{$chkout[$i]};
                                $doctor->DR_IN_STATUS = 'OUT';
                                $doctor->CHEMBER_NO = NULL;
                            }
            
                            if ($matchingSlot) {
                                $doctor->FROM = $matchingSlot['FROM'];
                                break;
                            } elseif ($nextAvailableSlot) {
                                $doctor->FROM = $nextAvailableSlot['FROM'];
                                break;
                            } else {
                                $doctor->FROM = null;
                            }
            
                             // Exit loop once matching slot or next available slot is found
                        }
                
                        $maxBookDoctorSum = array_sum(array_filter([$doctor->MAX_BOOK, $doctor->MAX_BOOK1, $doctor->MAX_BOOK2, $doctor->MAX_BOOK3]));
                
                        $ldr['livedoctor'][] = [
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
                            'KM' => $doctor->KM,
                            "LANGUAGE" => $doctor->LANGUAGE,
                            "DR_PHOTO" => $doctor->DR_PHOTO,
                            "DR_FEES" => $doctor->DR_FEES,
                            "SCH_ID" => $doctor->ID,
                            "SCH_DT" => $sch_dt,
                            "FROM" => $doctor->FROM,
                            "CHK_IN_TIME" => $doctor->DR_IN_TIME,
                            "CHK_OUT_TIME" => $doctor->DR_OUT_TIME,
                            "DR_STATUS" => $doctor->DR_IN_STATUS,
                            "CHEMBER_NO" => $doctor->CHEMBER_NO,
                            "MAX_BOOK" => $maxBookDoctorSum,
                        ];
                    }
                    if (empty($ldr['livedoctor'])) {
                        $ldr['livedoctor'] = [];
                    }else{
                        usort($ldr['livedoctor'], function ($a, $b) {
                            $statusOrder = ['IN' => 1, 'TIMELY' => 2, 'DELAY' => 3, 'OUT' => 4, 'CANCELLED' => 5, 'LEAVE' => 6];
                        
                            $convertTo24HourFormat = function($time) {
                                return date("H:i", strtotime($time));
                            };
                        
                            $fromA = $convertTo24HourFormat($a['FROM']);
                            $fromB = $convertTo24HourFormat($b['FROM']);
                            $checkInTimeA = $convertTo24HourFormat($a['CHK_IN_TIME']);
                            $checkInTimeB = $convertTo24HourFormat($b['CHK_IN_TIME']);
                        
                            if ($statusOrder[$a['DR_STATUS']] != $statusOrder[$b['DR_STATUS']]) {
                                return $statusOrder[$a['DR_STATUS']] <=> $statusOrder[$b['DR_STATUS']];
                            }
                        
                            // if ($fromA != $fromB) {
                            //     return $fromA <=> $fromB;
                            // }
                        
                            // return $checkInTimeA <=> $checkInTimeB;
                            return $fromA <=> $fromB;
                        });
                    }

                
                
               



                $pha['promoclinic'] = DB::table('pharmacy')
                    ->select(
                        'pharmacy.PHARMA_ID',
                        'pharmacy.ITEM_NAME AS PHARMA_NAME',
                        'pharmacy.ADDRESS',
                        'pharmacy.CITY',
                        'pharmacy.CLINIC_TYPE',
                        'pharmacy.PIN',
                        'pharmacy.DIST',
                        'pharmacy.STATE',
                        'pharmacy.LATITUDE',
                        'pharmacy.LONGITUDE',
                        'pharmacy.LONGITUDE',
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                * SIN(RADIANS('$latt'))))),2) as KM"),
                    )
                    // ->where('CLINIC_TYPE', 'Clinic')
                    ->whereIn('CLINIC_TYPE', ['Clinic', 'Hospital', 'Diagnostic'])
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->orderby('pharmacy.VALID_DT', 'desc')
                    ->orderbyraw('KM')
                    ->take(25)
                    ->get();

                // $SYM_DTL = DB::table('symptoms')
                //     ->select('SYM_ID', 'SYM_NAME', 'DIS_ID', 'DIS_CATEGORY', 'DASH_PHOTO as PHOTO_URL', 'BANNER_URL')
                //     ->orderby('SYM_SL')->take(10)->get()->toArray();

                $SYM_DTL = DB::table('symptoms')
                    ->select(
                        'SYM_ID',
                        'SYM_NAME',
                        'DIS_ID',
                        'DIS_CATEGORY',
                        'SYMIMG1',
                        'SYMIMG2',
                        'SYMIMG3',
                        'SYMIMG4',
                        'SYMIMG5',
                        'SYMIMG6',
                        'SYMIMG7',
                        'SYMIMG8',
                        'SYMIMG9',
                        'SYMIMG10',
                        'SYMBNR1',
                        'SYMBNR2',
                        'SYMBNR3',
                        'SYMBNR4',
                        'SYMBNR5',
                        'SYMBNR6',
                        'SYMBNR7',
                        'SYMBNR8',
                        'SYMBNR9',
                        'SYMBNR10',
                        'SYMQA1',
                        'SYMQA2',
                        'SYMQA3',
                        'SYMQA4',
                        'SYMQA5',
                        'SYMQA6',
                        'SYMQA7',
                        'SYMQA8',
                        'SYMQA9'
                    )
                    ->orderby('SYM_SL')->take(10)->get()->map(function ($item) {
                        return [
                            "SYM_ID" => $item->SYM_ID,
                            "SYM_NAME" => $item->SYM_NAME,
                            "DIS_ID" => $item->DIS_ID,
                            "DIS_CATEGORY" => $item->DIS_CATEGORY,
                            "PHOTO_URL1" => $item->SYMIMG1,
                            "PHOTO_URL2" => $item->SYMIMG2,
                            "PHOTO_URL3" => $item->SYMIMG3,
                            "PHOTO_URL4" => $item->SYMIMG4,
                            "PHOTO_URL5" => $item->SYMIMG5,
                            "PHOTO_URL6" => $item->SYMIMG6,
                            "PHOTO_URL7" => $item->SYMIMG7,
                            "PHOTO_URL8" => $item->SYMIMG8,
                            "PHOTO_URL9" => $item->SYMIMG9,
                            "PHOTO_URL10" => $item->SYMIMG10,
                            "BANNER_URL1" => $item->SYMBNR1,
                            "BANNER_URL2" => $item->SYMBNR2,
                            "BANNER_URL3" => $item->SYMBNR3,
                            "BANNER_URL4" => $item->SYMBNR4,
                            "BANNER_URL5" => $item->SYMBNR5,
                            "BANNER_URL6" => $item->SYMBNR6,
                            "BANNER_URL7" => $item->SYMBNR7,
                            "BANNER_URL8" => $item->SYMBNR8,
                            "BANNER_URL9" => $item->SYMBNR9,
                            "BANNER_URL10" => $item->SYMBNR10,
                            "Questions" => [
                                "QA1" => $item->SYMQA1,
                                "QA2" => $item->SYMQA2,
                                "QA3" => $item->SYMQA3,
                                "QA4" => $item->SYMQA4,
                                "QA5" => $item->SYMQA5,
                                "QA6" => $item->SYMQA6,
                                "QA7" => $item->SYMQA7,
                                "QA8" => $item->SYMQA8,
                                "QA9" => $item->SYMQA9
                            ]
                        ];
                    })->values()->all();

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SM';
                });
                $SMB["Symptoms_Banner"] = $fltr_promo_bnr->take(3)->values()->all();
                $SYM["Symptoms"] = array_values($SYM_DTL + $SMB);

                $data = $bnr + $dcat + $ldr + $pha + $SYM;

                if ($data == null) {
                    $response = ['Success' => false, 'Message' => 'Dr. not found', 'code' => 200];
                } else {
                    $response = ['Success' => true, 'data' => $data, 'code' => 200];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }

        return response()->json($response);
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

    private function getSchDtSlot($drId, $P_ID)
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

                        $slots = $this->getDtSlot($P_ID, $drId, $row->ID, $dates);
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
                            "MAX_BOOK" => $row->MAX_BOOK,
                            "MAX_BOOK1" => $row->MAX_BOOK1,
                            "MAX_BOOK2" => $row->MAX_BOOK2,
                            "MAX_BOOK3" => $row->MAX_BOOK3,
                            "AVAILABLE" => $row->MAX_BOOK - $totappct,
                            "CHK_IN_TIME" => $row->CHK_IN_TIME,
                            "CHK_IN_TIME1" => $row->CHK_IN_TIME1,
                            "CHK_IN_TIME2" => $row->CHK_IN_TIME2,
                            "CHK_IN_TIME3" => $row->CHK_IN_TIME3,
                            "CHK_OUT_TIME" => $row->CHK_OUT_TIME,
                            "CHK_OUT_TIME1" => $row->CHK_OUT_TIME1,
                            "CHK_OUT_TIME2" => $row->CHK_OUT_TIME2,
                            "CHK_OUT_TIME3" => $row->CHK_OUT_TIME3,
                            "DR_STATUS" => $dr_status,
                            "DR_DELAY" => $row->DR_DELAY,
                            "DR_ARRIVE" => $row->DR_ARRIVE,
                            "CHEMBER_NO" => $row->CHEMBER_NO,
                            "SLOT_STATUS" => $book_sts,
                            "SLOTS" => [$slots]
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
                            $slots = $this->getDtSlot($P_ID, $drId, $row->ID, $dates);
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
                                "MAX_BOOK" => $row->MAX_BOOK,
                                "MAX_BOOK1" => $row->MAX_BOOK1,
                                "MAX_BOOK2" => $row->MAX_BOOK2,
                                "MAX_BOOK3" => $row->MAX_BOOK3,
                                "AVAILABLE" => $row->MAX_BOOK - $totappct,
                                "CHK_IN_TIME" => $row->CHK_IN_TIME,
                                "CHK_IN_TIME1" => $row->CHK_IN_TIME1,
                                "CHK_IN_TIME2" => $row->CHK_IN_TIME2,
                                "CHK_IN_TIME3" => $row->CHK_IN_TIME3,
                                "CHK_OUT_TIME" => $row->CHK_OUT_TIME,
                                "CHK_OUT_TIME1" => $row->CHK_OUT_TIME1,
                                "CHK_OUT_TIME2" => $row->CHK_OUT_TIME2,
                                "CHK_OUT_TIME3" => $row->CHK_OUT_TIME3,
                                "DR_STATUS" => $dr_status,
                                "DR_DELAY" => $row->DR_DELAY,
                                "DR_ARRIVE" => $row->DR_ARRIVE,
                                "CHEMBER_NO" => $row->CHEMBER_NO,
                                "SLOT_STATUS" => $book_sts,
                                "SLOTS" => [$slots]
                            ];
                            $counter++;
                        }
                    }
                    $startDate->addDay();
                }
            }
        }
        if (!is_null($data)) {
            usort($data, function ($item1, $item2) {
                return $item1['SCH_DT'] <=> $item2['SCH_DT'];
            });
        }

        if ($data[0]['SCH_DT'] === $cym) {
            $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));

            $firstRowTOTime = $data[0]['CHK_OUT_TIME'] ? Carbon::createFromFormat('h:i A', $data[0]['CHK_OUT_TIME']) : null;
            $firstRowTOTime1 = $data[0]['CHK_OUT_TIME1'] ? Carbon::createFromFormat('h:i A', $data[0]['CHK_OUT_TIME1']) : null;
            $firstRowTOTime2 = $data[0]['CHK_OUT_TIME2'] ? Carbon::createFromFormat('h:i A', $data[0]['CHK_OUT_TIME2']) : null;
            $firstRowTOTime3 = $data[0]['CHK_OUT_TIME3'] ? Carbon::createFromFormat('h:i A', $data[0]['CHK_OUT_TIME3']) : null;

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
            $sixRows = array_slice($data, $firstAvailableIndex, 6);
        }
        return $sixRows;
    }

    function vuallsurg(Request $req)
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $headers = apache_request_headers();
            // session_start();
            // date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();
            $promo_bnr = DB::table('promo_banner')
                ->where('STATUS', 'Active')
                ->whereIn('DASH_SECTION_ID', ['SR'])
                ->get();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
            if (isset($input['FACILITY_ID'])) {
                $f_id = $input['FACILITY_ID'];
                $data1 = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    // ->where('facility.DN_TAG_SECTION', 'like', '%' . $f_id . '%')
                    ->where('facility_section.DASH_SECTION_ID', $f_id)
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->orderby('facility.DN_POSITION')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->DASH_TYPE])) {
                        $groupedData[$row->DASH_TYPE] = [
                            "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                            "DASH_TYPE" => $row->DASH_TYPE,
                            "DESCRIPTION" => $row->DT_DESCRIPTION,
                            // "PHOTO_URL" =>  $row->VIEW1_URL,
                            // "BANNER_URL" => $row->GR_BANNER_URL,
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
                            "FACILITY_DETAILS" => []
                        ];
                    }

                    $groupedData[$row->DASH_TYPE]['FACILITY_DETAILS'][] = [
                        "DASH_ID" => $row->DASH_ID,
                        // "DIS_ID" => $row->DIS_ID,
                        "DASH_NAME" => $row->DASH_NAME,
                        "DASH_TYPE" => $row->DASH_TYPE,
                        "DESCRIPTION" => $row->DN_DESCRIPTION,
                        "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,

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
                $a['Facilities'] = $groupedData;

                $fltr_bnr = $data1->filter(function ($item) use ($f_id) {
                    return $item->DASH_SECTION_ID === $f_id;
                });

                $b["Facility_Banner"] = $fltr_bnr->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        // "PHOTO_URL" =>  $item->VIEW1_URL,
                        "BANNER_URL" => $item->DSBNR1,
                    ];
                })->values()->take(1)->all();
                $data = $a + $b;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter.', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }


    function vuhnallsurg(Request $req)
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $headers = apache_request_headers();
            // session_start();
            // date_default_timezone_set('Asia/Kolkata');
            $input = $req->json()->all();
            $promo_bnr = DB::table('promo_banner')
                ->where('STATUS', 'Active')
                ->whereIn('DASH_SECTION_ID', ['SR'])
                ->get();

            // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {
            if (isset($input['FACILITY_ID'])) {
                $f_id = $input['FACILITY_ID'];
                $data1 = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->where('facility_section.DASH_SECTION_ID', $f_id)
                    // ->where('facility.DN_TAG_SECTION', 'like', '%' . $f_id . '%')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->orderby('facility.DN_POSITION')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->DASH_TYPE])) {
                        $groupedData[$row->DASH_TYPE] = [
                            "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                            "DASH_TYPE" => $row->DASH_TYPE,
                            "DESCRIPTION" => $row->DT_DESCRIPTION,
                            // "PHOTO_URL" =>  $row->VIEW1_URL,
                            // "BANNER_URL" => $row->GR_BANNER_URL,
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
                            "FACILITY_DETAILS" => []
                        ];
                    }

                    $groupedData[$row->DASH_TYPE]['FACILITY_DETAILS'][] = [
                        "DASH_ID" => $row->DASH_ID,
                        // "DIS_ID" => $row->DIS_ID,
                        "DASH_NAME" => $row->DASH_NAME,
                        "DASH_TYPE" => $row->DASH_TYPE,
                        "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
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
                $a['Facilities'] = $groupedData;

                $fltr_bnr = $data1->filter(function ($item) use ($f_id) {
                    return $item->DASH_SECTION_ID === $f_id;
                });

                $b["Facility_Banner"] = $fltr_bnr->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        // "PHOTO_URL" =>  $item->VIEW1_URL,
                        "BANNER_URL" => $item->DSBNR1,
                    ];
                })->values()->take(1)->all();
                $data = $a + $b;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter.', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function vuallsymp(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $sym = DB::table('symptoms')
                    ->distinct('SYMPTOM_TYPE')
                    ->select(
                        'SYM_ID',
                        'SYM_NAME',
                        'DIS_ID',
                        'SYM_TYPE',
                        'DIS_CATEGORY',
                        'TYPE_SL',
                        'SYM_SL',
                        'SYMIMG1',
                        'SYMIMG2',
                        'SYMIMG3',
                        'SYMIMG4',
                        'SYMIMG5',
                        'SYMIMG6',
                        'SYMIMG7',
                        'SYMIMG8',
                        'SYMIMG9',
                        'SYMIMG10',
                        'SYMBNR1',
                        'SYMBNR2',
                        'SYMBNR3',
                        'SYMBNR4',
                        'SYMBNR5',
                        'SYMBNR6',
                        'SYMBNR7',
                        'SYMBNR8',
                        'SYMBNR9',
                        'SYMBNR10',
                        'SYMQA1',
                        'SYMQA2',
                        'SYMQA3',
                        'SYMQA4',
                        'SYMQA5',
                        'SYMQA6',
                        'SYMQA7',
                        'SYMQA8',
                        'SYMQA9'
                    )
                    ->where('STATUS', 'Active')
                    ->orderby('SYM_SL')
                    ->orderby('TYPE_SL')
                    ->get()
                    ->map(function ($item) {
                        return [
                            "SYM_ID" => $item->SYM_ID,
                            "SYM_NAME" => $item->SYM_NAME,
                            "DIS_ID" => $item->DIS_ID,
                            "SYM_TYPE" => $item->SYM_TYPE,
                            "DIS_CATEGORY" => $item->DIS_CATEGORY,
                            "PHOTO_URL1" => $item->SYMIMG1,
                            "PHOTO_URL2" => $item->SYMIMG2,
                            "PHOTO_URL3" => $item->SYMIMG3,
                            "PHOTO_URL4" => $item->SYMIMG4,
                            "PHOTO_URL5" => $item->SYMIMG5,
                            "PHOTO_URL6" => $item->SYMIMG6,
                            "PHOTO_URL7" => $item->SYMIMG7,
                            "PHOTO_URL8" => $item->SYMIMG8,
                            "PHOTO_URL9" => $item->SYMIMG9,
                            "PHOTO_URL10" => $item->SYMIMG10,
                            "BANNER_URL1" => $item->SYMBNR1,
                            "BANNER_URL2" => $item->SYMBNR2,
                            "BANNER_URL3" => $item->SYMBNR3,
                            "BANNER_URL4" => $item->SYMBNR4,
                            "BANNER_URL5" => $item->SYMBNR5,
                            "BANNER_URL6" => $item->SYMBNR6,
                            "BANNER_URL7" => $item->SYMBNR7,
                            "BANNER_URL8" => $item->SYMBNR8,
                            "BANNER_URL9" => $item->SYMBNR9,
                            "BANNER_URL10" => $item->SYMBNR10,
                            "Questions" => [
                                "QA1" => $item->SYMQA1,
                                "QA2" => $item->SYMQA2,
                                "QA3" => $item->SYMQA3,
                                "QA4" => $item->SYMQA4,
                                "QA5" => $item->SYMQA5,
                                "QA6" => $item->SYMQA6,
                                "QA7" => $item->SYMQA7,
                                "QA8" => $item->SYMQA8,
                                "QA9" => $item->SYMQA9
                            ]
                        ];
                    });

                $data = array();
                $i = 1;
                $distinctValues = $sym->pluck('SYM_TYPE')->unique();

                foreach ($distinctValues as $row) {
                    $fltr_arr = $sym->filter(function ($item) use ($row) {
                        return $item['SYM_TYPE'] === $row;
                    });
                    $Symp_DTL = $fltr_arr->values()->all();
                    $tarr["symptom_" . $i . "_details"] = $Symp_DTL;
                    $data = $data + $tarr;
                    $tarr = [];
                    $i++;
                }

                $bnr['Banner'] = DB::table('promo_banner')
                    ->select('PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', 'SM')
                    ->get();

                $data = $data + $bnr;

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter.', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
        }
        return $response;
    }



    function vuclallsplst(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['PHARMA_ID'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $p_id = $input['PHARMA_ID'];

                $date = Carbon::now();
                $weekNumber = $date->weekOfMonth;
                $day1 = date('l');
                $response = array();
                $data = array();
                $cdy = date('d');
                $cdt = date('Ymd');

                $data = array();

                // $bnr['Banner'] = DB::table('promo_banner')
                //     ->select('PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                //     ->where(['DASH_SECTION_ID' => 'SP', 'PHARMA_ID' => $p_id])
                //     ->get();
                $bnr['Banner'] = DB::table('promo_banner')
                    ->select('PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', 'SD')
                    ->get();
                $specialists = DB::table('drprofile')
                    ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
                    ->join('dis_catg', 'drprofile.DIS_ID', '=', 'dis_catg.DIS_ID')
                    ->select(
                        'dis_catg.DIS_ID',
                        'dis_catg.SPECIALIST',
                        'dis_catg.DIS_CATEGORY',
                        'dis_catg.DISIMG1 as PHOTO_URL1',
                        'dis_catg.DISIMG2 as PHOTO_URL2',
                        'dis_catg.DISIMG3 as PHOTO_URL3',
                        'dis_catg.DISIMG4 as PHOTO_URL4',
                        'dis_catg.DISIMG5 as PHOTO_URL5',
                        'dis_catg.DISIMG6 as PHOTO_URL6',
                        'dis_catg.DISIMG7 as PHOTO_URL7',
                        'dis_catg.DISIMG8 as PHOTO_URL8',
                        'dis_catg.DISIMG9 as PHOTO_URL9',
                        'dis_catg.DISIMG10 as PHOTO_URL10',
                        'dis_catg.DIS_TYPE',
                        'dis_catg.SPECIALITY',
                        'dis_catg.DIS_SL',
                        DB::raw('COUNT(DISTINCT CASE WHEN dr_availablity.SCH_STATUS != \'NA\' THEN dr_availablity.DR_ID ELSE NULL END) as TOT_DR'),

                    )
                    ->distinct('dis_catg.DIS_ID')
                    ->where(['dis_catg.STATUS' => 'Active', 'dr_availablity.PHARMA_ID' => $p_id])
                    ->where('drprofile.APPROVE', 'true')
                    ->groupBy(
                        'dis_catg.DIS_ID',
                        'dis_catg.SPECIALIST',
                        'dis_catg.DIS_CATEGORY',
                        'dis_catg.DISIMG1',
                        'dis_catg.DISIMG2',
                        'dis_catg.DISIMG3',
                        'dis_catg.DISIMG4',
                        'dis_catg.DISIMG5',
                        'dis_catg.DISIMG6',
                        'dis_catg.DISIMG7',
                        'dis_catg.DISIMG8',
                        'dis_catg.DISIMG9',
                        'dis_catg.DISIMG10',
                        'dis_catg.DIS_TYPE',
                        'dis_catg.SPECIALITY',
                        'dis_catg.DIS_SL'
                    )
                    ->orderBy('dis_catg.DIS_SL')
                    ->get();

                $data['specialist'] = $specialists->map(function ($specialist) use ($p_id) {
                    $specialist->AVAIL_DR = $this->getAvailableDoctors($specialist->DIS_ID, $p_id);
                    return $specialist;
                });


                $data = [];
                $i = 1;
                $collection = collect($specialists);
                $distinctValues = $collection->pluck('DIS_TYPE')->unique();

                foreach ($distinctValues as $row) {
                    $fltr_arr = $specialists->filter(function ($item) use ($row) {
                        return $item->DIS_TYPE === $row;
                    });
                    $Splst_DTL = $fltr_arr->values()->all();
                    $tarr["specialist_" . $i . "_details"] = $Splst_DTL;
                    $data = array_merge($data, $tarr);
                    $tarr = [];
                    $i++;
                }

                $data1 = DB::table('dashboard_section')
                    ->where('DASH_SECTION_ID', '=', 'SD')
                    ->get();

                $bnr1["Catg_Banner"] = $data1->map(function ($item) {
                    return [
                        "BANNER_ID" => $item->ID,
                        // "DIS_ID" => $item->DIS_ID,
                        "BANNER_NAME" => $item->DASH_SECTION_NAME,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "BANNER_URL1" => $item->DSBNR1,
                        "BANNER_URL2" => $item->DSBNR2,
                        "BANNER_URL3" => $item->DSBNR3,
                        "BANNER_URL4" => $item->DSBNR4,
                        "BANNER_URL5" => $item->DSBNR5,
                        "BANNER_URL6" => $item->DSBNR6,
                        "BANNER_URL7" => $item->DSBNR7,
                        "BANNER_URL8" => $item->DSBNR8,
                        "BANNER_URL9" => $item->DSBNR9,
                        "BANNER_URL10" => $item->DSBNR10,
                        "Questions" => [
                            [
                                "QA1" => $item->DSQA1,
                                "QA2" => $item->DSQA2,
                                "QA3" => $item->DSQA3,
                                "QA4" => $item->DSQA4,
                                "QA5" => $item->DSQA5,
                                "QA6" => $item->DSQA6,
                                "QA7" => $item->DSQA7,
                                "QA8" => $item->DSQA8,
                                "QA9" => $item->DSQA9
                            ]
                        ]

                    ];
                })->values()->all();
                $data = $data + $bnr + $bnr1;

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter.', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    private function getAvailableDoctors($disId, $pharmaId)
    {
        date_default_timezone_set('Asia/Kolkata');
        $currentDate = Carbon::now();
        $weekNumber = $currentDate->weekOfMonth;
        $day1 = $currentDate->format('l');
        $cdy = date('d');
        $cdt = date('Ymd');
        $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));
        $data1 = DB::table('dr_availablity')
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
                'dr_availablity.DR_FEES',
                // DB::raw("'" . Carbon::now()->format('Ymd') . "' as SCH_DT"),
                'dr_availablity.SCH_DAY',
                'dr_availablity.ID',
                'dr_availablity.START_MONTH',
                'dr_availablity.MONTH',
                'dr_availablity.ABS_FDT',
                'dr_availablity.ABS_TDT',
                'dr_availablity.CHK_IN_TIME',
                'dr_availablity.CHK_IN_TIME1',
                'dr_availablity.CHK_IN_TIME2',
                'dr_availablity.CHK_IN_TIME3',
                'dr_availablity.CHK_OUT_TIME',
                'dr_availablity.CHK_OUT_TIME1',
                'dr_availablity.CHK_OUT_TIME2',
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
            ->where(['dr_availablity.PHARMA_ID' => $pharmaId, 'dr_availablity.DIS_ID' => $disId])
            ->where(['dr_availablity.SCH_DAY' => $day1])
            ->where('WEEK', 'like', '%' . $weekNumber . '%')
            ->orWhere('dr_availablity.SCH_DT', $cdy)
            ->get();

        $chk = array("CHK_IN_TIME", "CHK_IN_TIME1", "CHK_IN_TIME2", "CHK_IN_TIME3");
        $chkout = array("CHK_OUT_TIME", "CHK_OUT_TIME1", "CHK_OUT_TIME2", "CHK_OUT_TIME3");
        $CHKINSTATUS = array("CHK_IN_STATUS", "CHK_IN_STATUS1", "CHK_IN_STATUS2", "CHK_IN_STATUS3");
        $delay = array("DR_DELAY", "DR_DELAY1", "DR_DELAY2", "DR_DELAY3");
        $chamber = array("CHEMBER_NO", "CHEMBER_NO1", "CHEMBER_NO2", "CHEMBER_NO3");

        $currentTime = Carbon::now();

        $ldr = [];
        foreach ($data1 as $doctor) {
            if (is_numeric($doctor->SCH_DAY)) {
                $date = Carbon::createFromDate(date('Y'), $doctor->START_MONTH, $doctor->SCH_DAY)
                    ->addMonths($doctor->MONTH);
                if ($date->format('Ymd') === $cdt) {
                    $sch_dt = $date->format('Ymd');
                }
            } else {
                $sch_dt = Carbon::now()->format('Ymd');
            }
            $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));

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
                } else {
                    $doctor->CHK_IN_TIME = $doctor->{$chk[$i]};
                    $doctor->CHK_OUT_TIME = $doctor->{$chkout[$i]};
                    $doctor->CHK_IN_STATUS = 'OUT';
                    $doctor->CHEMBER_NO = NULL;
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

            $ldr['today_doctor'][] = [
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
                "SCH_DT" => $sch_dt,
                "CHK_IN_TIME" => $doctor->CHK_IN_TIME,
                "CHK_OUT_TIME" => $doctor->CHK_OUT_TIME,
                "DR_STATUS" => $doctor->CHK_IN_STATUS,
                "CHEMBER_NO" => $doctor->CHEMBER_NO,
                "MAX_BOOK" => $maxBookDoctorSum,
            ];

        }
        if (empty($ldr['today_doctor'])) {
            $ldr['today_doctor'] = [];
        } else {
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
                return $doctor['DR_STATUS'] === "IN" || $doctor['DR_STATUS'] === "TIMELY" || $doctor['DR_STATUS'] === "DELAY";
            });
            $ldr['today_doctor'] = array_values($filtered_ldr);
        }

        return count($ldr['today_doctor']);
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

    function vuallsplst()
    {
        $bnr['Banner'] = DB::table('promo_banner')
            ->select('PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
            ->where('DASH_SECTION_ID', '=', 'SD')
            ->get();

        $spl = DB::table('dis_catg')
            ->distinct('DIS_TYPE')
            ->select(
                'DIS_ID',
                'DASH_SECTION_ID',
                'DIS_TYPE',
                'TYPE_SL',
                'DIS_SL',
                'DIS_CATEGORY',
                'SPECIALIST',
                'SPECIALITY',
                'DISIMG1',
                'DISIMG2',
                'DISIMG3',
                'DISIMG4',
                'DISIMG5',
                'DISIMG6',
                'DISIMG7',
                'DISIMG8',
                'DISIMG9',
                'DISIMG10',
                // 'DISBNR1',
                // 'DISBNR2',
                // 'DISBNR3',
                // 'DISBNR4',
                // 'DISBNR5',
                // 'DISBNR6',
                // 'DISBNR7',
                // 'DISBNR8',
                // 'DISBNR9',
                // 'DISBNR10',
                // 'DISQA1',
                // 'DISQA2',
                // 'DISQA3',
                // 'DISQA4',
                // 'DISQA5',
                // 'DISQA6',
                // 'DISQA7',
                // 'DISQA8',
                // 'DISQA9',

            )
            ->distinct('SPECIALIST')
            ->where('STATUS', 'Active')
            ->orderby('TYPE_SL')
            ->orderby('DIS_SL')
            ->get()->map(function ($item) {
                return [
                    "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                    "DIS_ID" => $item->DIS_ID,
                    "DIS_TYPE" => $item->DIS_TYPE,
                    "DIS_CATEGORY" => $item->DIS_CATEGORY,
                    "SPECIALIST" => $item->SPECIALIST,
                    "SPECIALITY" => $item->SPECIALITY,
                    "PHOTO_URL1" => $item->DISIMG1,
                    "PHOTO_URL2" => $item->DISIMG2,
                    "PHOTO_URL3" => $item->DISIMG3,
                    "PHOTO_URL4" => $item->DISIMG4,
                    "PHOTO_URL5" => $item->DISIMG5,
                    "PHOTO_URL6" => $item->DISIMG6,
                    "PHOTO_URL7" => $item->DISIMG7,
                    "PHOTO_URL8" => $item->DISIMG8,
                    "PHOTO_URL9" => $item->DISIMG9,
                    "PHOTO_URL10" => $item->DISIMG10,
                    // "BANNER_URL1" => $item->DISBNR1,
                    // "BANNER_URL2" => $item->DISBNR2,
                    // "BANNER_URL3" => $item->DISBNR3,
                    // "BANNER_URL4" => $item->DISBNR4,
                    // "BANNER_URL5" => $item->DISBNR5,
                    // "BANNER_URL6" => $item->DISBNR6,
                    // "BANNER_URL7" => $item->DISBNR7,
                    // "BANNER_URL8" => $item->DISBNR8,
                    // "BANNER_URL9" => $item->DISBNR9,
                    // "BANNER_URL10" => $item->DISBNR10,
                    // "Questions" => [
                    //     "QA1" => $item->DISQA1,
                    //     "QA2" => $item->DISQA2,
                    //     "QA3" => $item->DISQA3,
                    //     "QA4" => $item->DISQA4,
                    //     "QA5" => $item->DISQA5,
                    //     "QA6" => $item->DISQA6,
                    //     "QA7" => $item->DISQA7,
                    //     "QA8" => $item->DISQA8,
                    //     "QA9" => $item->DISQA9
                    // ]
                ];
            });

        $data = array();
        $i = 1;
        $distinctValues = $spl->pluck('DIS_TYPE')->unique();

        foreach ($distinctValues as $row) {
            $fltr_arr = $spl->filter(function ($item) use ($row) {
                return $item['DIS_TYPE'] === $row;
            });
            $Splst_DTL = $fltr_arr->values()->all();
            $tarr["specialist_" . $i . "_details"] = $Splst_DTL;
            $data = $data + $tarr;
            $tarr = [];
            $i++;
        }
        $data1 = DB::table('dashboard_section')
            ->where('DASH_SECTION_ID', '=', 'SD')
            ->get();

        $bnr1["Catg_Banner"] = $data1->map(function ($item) {
            return [
                "BANNER_ID" => $item->ID,
                // "DIS_ID" => $item->DIS_ID,
                "BANNER_NAME" => $item->DASH_SECTION_NAME,
                "DESCRIPTION" => $item->DS_DESCRIPTION,
                "BANNER_URL1" => $item->DSBNR1,
                "BANNER_URL2" => $item->DSBNR2,
                "BANNER_URL3" => $item->DSBNR3,
                "BANNER_URL4" => $item->DSBNR4,
                "BANNER_URL5" => $item->DSBNR5,
                "BANNER_URL6" => $item->DSBNR6,
                "BANNER_URL7" => $item->DSBNR7,
                "BANNER_URL8" => $item->DSBNR8,
                "BANNER_URL9" => $item->DSBNR9,
                "BANNER_URL10" => $item->DSBNR10,
                "Questions" => [
                    [
                        "QA1" => $item->DSQA1,
                        "QA2" => $item->DSQA2,
                        "QA3" => $item->DSQA3,
                        "QA4" => $item->DSQA4,
                        "QA5" => $item->DSQA5,
                        "QA6" => $item->DSQA6,
                        "QA7" => $item->DSQA7,
                        "QA8" => $item->DSQA8,
                        "QA9" => $item->DSQA9
                    ]
                ]

            ];
        })->values()->all();

        $data = $data + $bnr + $bnr1;
        $response = ['Success' => true, 'data' => $data, 'code' => 200];
        return $response;
    }


    function allsrchcl(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $data2 = DB::table('pharmacy')
                    ->select('pharmacy.*', DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                * SIN(RADIANS('$latt'))))),2) as KM"), )
                    //     ->whereRaw("KM" <= 100)
                    ->where('pharmacy.STATUS', '=', 'Active')
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

                    $data[] = [
                        "ID" => $row2->PHARMA_ID,
                        "ITEM_NAME" => $row2->ITEM_NAME,
                        "FIELD_TYPE" => $row2->CLINIC_TYPE,
                        "DETAILS" => $cldtl['DETAILS']
                    ];
                }

                if ($data == null) {
                    $response = ['Success' => false, 'Message' => 'Clinic not found', 'code' => 200];
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

    function alldiag(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $data = DB::table('pharmacy')
                    ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
                    ->join('clinic_testdata', 'pharmacy.PHARMA_ID', '=', 'clinic_testdata.PHARMA_ID')
                    ->select(
                        'pharmacy.PHARMA_ID',
                        'pharmacy.ITEM_NAME AS PHARMA_NAME',
                        'pharmacy.CLINIC_TYPE',
                        'pharmacy.ADDRESS',
                        'pharmacy.CITY',
                        'pharmacy.DIST',
                        'pharmacy.STATE',
                        'pharmacy.PIN',
                        'pharmacy.CLINIC_MOBILE',
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        'pharmacy.LOGO_URL',
                        'pharmacy.LATITUDE',
                        'pharmacy.LONGITUDE',
                        'pharmacy.OPD',
                        DB::raw('COUNT(distinct dr_availablity.DR_ID) as TOT_DR'),
                        DB::raw('COUNT(distinct clinic_testdata.TEST_ID) as TOT_TEST'),
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                 * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                  * SIN(RADIANS('$latt'))))),2) as KM")
                    )
                    ->groupBy(
                        'pharmacy.PHARMA_ID',
                        'pharmacy.ITEM_NAME',
                        'pharmacy.ADDRESS',
                        'pharmacy.CLINIC_TYPE',
                        'pharmacy.CITY',
                        'pharmacy.DIST',
                        'pharmacy.STATE',
                        'pharmacy.PIN',
                        'pharmacy.CLINIC_MOBILE',
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        'pharmacy.LATITUDE',
                        'pharmacy.LONGITUDE',
                        'pharmacy.OPD'
                    )
                    ->where('pharmacy.CLINIC_TYPE', '<>', 'Clinic')
                    ->where('pharmacy.CLINIC_TYPE', '<>', 'Hospital')
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->orderby('KM')
                    ->take(25)
                    ->get();

                if ($data == null) {
                    $response = ['Success' => true, 'Message' => 'Diagnostic not found', 'data' => $data];
                } else {
                    $response = ['Success' => true, 'data' => $data];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function allclinic(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $response = array();
                $data = array();

                $data1["Diagnostic"] = DB::table('pharmacy')
                    ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
                    ->select(
                        'pharmacy.PHARMA_ID',
                        'pharmacy.ITEM_NAME AS PHARMA_NAME',
                        'pharmacy.CLINIC_TYPE',
                        'pharmacy.ADDRESS',
                        'pharmacy.CITY',
                        'pharmacy.DIST',
                        'pharmacy.STATE',
                        'pharmacy.PIN',
                        'pharmacy.CLINIC_MOBILE',
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        'pharmacy.LATITUDE',
                        'pharmacy.LONGITUDE',
                        'pharmacy.OPD',
                        DB::raw('COUNT(distinct dr_availablity.DR_ID) as TOT_DR'),
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                 * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                  * SIN(RADIANS('$latt'))))),2) as KM")
                    )
                    ->groupBy(
                        'pharmacy.PHARMA_ID',
                        'pharmacy.ITEM_NAME',
                        'pharmacy.ADDRESS',
                        'pharmacy.CLINIC_TYPE',
                        'pharmacy.CITY',
                        'pharmacy.DIST',
                        'pharmacy.STATE',
                        'pharmacy.PIN',
                        'pharmacy.CLINIC_MOBILE',
                        'pharmacy.PHOTO_URL',
                        'pharmacy.LOGO_URL',
                        'pharmacy.LATITUDE',
                        'pharmacy.LONGITUDE',
                        'pharmacy.OPD'
                    )
                    ->where('pharmacy.STATUS', '=', 'Active')
                    // ->where('pharmacy.CLINIC_TYPE', '=', 'Diagnostic')
                    ->orderby('KM')
                    ->take(25)
                    ->get()->toArray();
                $bnr["Banner"] = DB::table('promo_banner')
                    ->select('PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
                    ->where('DASH_SECTION_ID', '=', 'CL')
                    ->get()->ToArray();
                $data = $data1 + $bnr;

                if ($data == null) {
                    $response = ['Success' => true, 'Message' => 'Clinic not found', 'data' => $data];
                } else {
                    $response = ['Success' => true, 'data' => $data];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
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

            $pharmaId = $input['PHARMAID'];
            $data = collect();

            $bnr['slider'] = DB::table('promo_banner')
                ->select('PROMO_ID as SLIDER_ID', 'PROMO_NAME as SLIDER_NAME', 'PROMO_URL as SLIDER_URL')
                ->where(['PHARMA_ID' => $pharmaId, 'STATUS' => 'Active', 'DASH_SECTION_ID' => 'DA'])
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

    private function getSpecialist($pharmaId)
    {
        $data = [];
        $specialists = DB::table('dis_catg')
            ->Join('dr_availablity', function ($join) use ($pharmaId) {
                $join->on('dr_availablity.DIS_ID', '=', 'dis_catg.DIS_ID')
                    ->where('dr_availablity.PHARMA_ID', '=', $pharmaId);
            })
            ->select(
                'dis_catg.DIS_ID',
                'dis_catg.DIS_CATEGORY',
                'dis_catg.SPECIALITY',
                'dis_catg.SPECIALIST',
                'dis_catg.DIS_TYPE',
                'dis_catg.PHOTO1_URL AS PHOTO_URL',
                DB::raw('COUNT(DISTINCT CASE WHEN dr_availablity.SCH_STATUS != \'NA\' THEN dr_availablity.DR_ID ELSE NULL END) as TOT_DR'),
            )
            ->where('dr_availablity.PHARMA_ID', '=', $pharmaId)
            ->groupBy(
                'dis_catg.DIS_ID',
                'dis_catg.DIS_CATEGORY',
                'dis_catg.SPECIALITY',
                'dis_catg.SPECIALIST',
                'dis_catg.DIS_TYPE',
                'dis_catg.PHOTO1_URL',
            )
            ->orderby('dis_catg.DIS_SL')
            ->get();

        // Add AVAIL_DR to each specialist
        $data['specialist'] = $specialists->map(function ($specialist) use ($pharmaId) {
            $specialist->AVAIL_DR = $this->getAvailableDoctors($specialist->DIS_ID, $pharmaId);
            return $specialist;
        });

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
                'dr_availablity.ID',
                'dr_availablity.START_MONTH',
                'dr_availablity.MONTH',
                'dr_availablity.ABS_FDT',
                'dr_availablity.ABS_TDT',
                'dr_availablity.CHK_IN_TIME',
                'dr_availablity.CHK_IN_TIME1',
                'dr_availablity.CHK_IN_TIME2',
                'dr_availablity.CHK_IN_TIME3',
                'dr_availablity.CHK_OUT_TIME',
                'dr_availablity.CHK_OUT_TIME1',
                'dr_availablity.CHK_OUT_TIME2',
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
            ->where(['dr_availablity.PHARMA_ID' => $pharmaId])
            ->where(['dr_availablity.SCH_DAY' => $day1])
            ->where('pharmacy.STATUS', '=', 'Active')
            ->where('WEEK', 'like', '%' . $weekNumber . '%')
            ->orWhere('dr_availablity.SCH_DT', $cdy)
            ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
            ->orderby('dr_availablity.CHK_IN_TIME')

            // ->orderbyraw('KM')
            ->get();

        $chk = array("CHK_IN_TIME", "CHK_IN_TIME1", "CHK_IN_TIME2", "CHK_IN_TIME3");
        $chkout = array("CHK_OUT_TIME", "CHK_OUT_TIME1", "CHK_OUT_TIME2", "CHK_OUT_TIME3");
        $CHKINSTATUS = array("CHK_IN_STATUS", "CHK_IN_STATUS1", "CHK_IN_STATUS2", "CHK_IN_STATUS3");
        $delay = array("DR_DELAY", "DR_DELAY1", "DR_DELAY2", "DR_DELAY3");
        $chamber = array("CHEMBER_NO", "CHEMBER_NO1", "CHEMBER_NO2", "CHEMBER_NO3");

        $currentTime = Carbon::now();

        $ldr = [];
        foreach ($data1 as $doctor) {
            if (is_numeric($doctor->SCH_DAY)) {
                $date = Carbon::createFromDate(date('Y'), $doctor->START_MONTH, $doctor->SCH_DAY)
                    ->addMonths($doctor->MONTH);
                if ($date->format('Ymd') === $cdt) {
                    $sch_dt = $date->format('Ymd');
                }
            } else {
                $sch_dt = Carbon::now()->format('Ymd');
            }
            $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));

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

            $ldr['today_doctor'][] = [
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
                "SCH_DT" => $sch_dt,
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
        if (empty($ldr['today_doctor'])) {
            $ldr['today_doctor'] = [];
        } else {
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
        }


        return $ldr;
    }

    private function getTotalDoctor($pharmaId)
    {
        $doctors = DB::table('drprofile')
            ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
            ->where('dr_availablity.PHARMA_ID', '=', $pharmaId)
            ->where('drprofile.APPROVE', 'true')
            ->distinct()
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
                'dr_availablity.PHARMA_ID',
                'dr_availablity.DR_FEES',
                'dr_availablity.CHK_IN_STATUS',
                'dr_availablity.CHEMBER_NO',
                'dr_availablity.DR_ARRIVE',
            ])
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
                "DR_STATUS" => $doctor->CHK_IN_STATUS,
                "DR_ARRIVE" => $doctor->DR_ARRIVE,
                "CHEMBER_NO" => $doctor->CHEMBER_NO,

            ];
        });

        return $data;
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
                'DASH_PHOTO AS PHOTO_URL',
                'PHOTO_URL AS PHOTO1_URL',
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
                    ->where('pharmacy.STATUS', '=', 'Active')
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

    function patientReview(Request $req)
    {
        date_default_timezone_set('Asia/Kolkata');
        $cdt = Carbon::now()->format('Ymd');
        $response = array();
        $input = $req->json()->all();
        DB::beginTransaction();

        $value1 = ['Not Satisfied' => 2, 'Good' => 4, 'Very Good' => 6, 'Excellent' => 8, 'Outstanding' => 10];
        $value2 = ['Not recommended' => 2.5, 'Yes sometimes' => 5, 'Yes, Strongly recommend' => 10, 'Yes, recommend cautiously' => 7.5];

        try {
            $rv = $input['DR_ID'] . $cdt . $input['FAMILY_ID'];
            $reviews = strtoupper(substr(md5($rv), 0, 10));
            $fields = [
                "DR_ID" => $input['DR_ID'],
                "FAMILY_ID" => $input['FAMILY_ID'],
                "REVIEW_ID" => $reviews,
                "REVIEW_DT" => $cdt,
                "FRIENDLY" => $value1[$input['FRIENDLY']] ?? 0,
                "SATISFY" => $value1[$input['SATISFY']] ?? 0,
                "ECONOMY" => $value1[$input['ECONOMY']] ?? 0,
                "RECOMEND" => $value2[$input['RECOMEND']] ?? 0,
                "REMARKS" => $input['REMARKS'],
            ];
            if (isset($input['DR_ID']) && isset($input['FAMILY_ID'])) {
                DB::table('patient_review')->insert($fields);
                DB::table('appointment')
                    ->where([
                        'DR_ID' => $input['DR_ID'],
                        'FAMILY_ID' => $input['FAMILY_ID']
                    ])
                    ->update(['PATIENT_REVIEW' => 'Done']);
            } else {
                return ['Success' => false, 'Message' => 'Doctor ID or Family ID missing.', 'code' => 400];
            }
            $response[] = ['Success' => true, 'Message' => 'Review inserted successfully.', 'code' => 200];
            DB::commit();
        } catch (\Throwable $th) {
            $chk = DB::table('appointment')->select('DR_ID', 'PATIENT_REVIEW')->where(['DR_ID' => $input['DR_ID'], 'FAMILY_ID' => $input['FAMILY_ID']])->first();
            if ($chk->PATIENT_REVIEW == 'Done') {
                DB::table('patient_review')->where('REVIEW_ID', $reviews)->update($fields);
                $response = ['Success' => true, 'Message' => 'Review updated successfully', 'code' => 200];
                DB::commit();
            } else {
                DB::rollBack();
                $response = ['Success' => false, 'Message' => 'Error updating review', 'code' => 500];
            }
        }
        return $response;
    }

    function vudrvisit(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->json()->all();
            if (isset($input['DR_ID']) && isset($input['FAMILY_ID'])) {
                $drid = $input['DR_ID'];
                $fid = $input['FAMILY_ID'];

                $data = DB::table('appointment')->where(['DR_ID' => $drid, 'FAMILY_ID' => $fid, 'STATUS' => 'Visited'])->first();

                if (empty($data)) {
                    $response = ['Success' => true, 'Review' => 'No', 'code' => 200];
                } else {
                    $response = ['Success' => true, 'Review' => $data->PATIENT_REVIEW, 'code' => 200];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    // function vusamedr(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $response = array();
    //         $input   = $req->json()->all();
    //         if (isset($input['DR_ID']) && isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
    //             $latt = $input['LATITUDE'];
    //             $lont = $input['LONGITUDE'];
    //             $drid = $input['DR_ID'];

    //             $data1 = DB::table('drprofile')
    //                 ->leftjoin('patient_review', 'drprofile.DR_ID', '=', 'patient_review.DR_ID')
    //                 ->select(
    //                     'drprofile.*',
    //                     'drprofile.ADDRESS as DR_ADDRESS',
    //                     'drprofile.EMAIL AS DR_EMAIL',
    //                     'drprofile.STATE AS DR_STATE',
    //                     'drprofile.DIST AS DR_DIST',
    //                     'drprofile.CITY AS DR_CITY',
    //                     'patient_review.FAMILY_ID',
    //                     'patient_review.FRIENDLY',
    //                     'patient_review.SATISFY',
    //                     'patient_review.ECONOMY',
    //                     'patient_review.RECOMEND',
    //                     'patient_review.REMARKS',
    //                     'patient_review.REVIEW_DT',
    //                 )
    //                 ->where(['drprofile.DR_ID' => $drid])
    //                 ->where('drprofile.APPROVE', 'true')
    //                 ->get();

    //             $groupedData = [];
    //             $uniqueKeys = [];
    //             foreach ($data1 as $row) {
    //                 if (!isset($groupedData[$row->DR_ID])) {
    //                     $othdr = DB::table('drprofile')
    //                         ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
    //                         ->join('pharmacy', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
    //                         ->select(
    //                             'drprofile.*',
    //                             'drprofile.ADDRESS as DR_ADDRESS',
    //                             'drprofile.EMAIL AS DR_EMAIL',
    //                             'drprofile.STATE AS DR_STATE',
    //                             'drprofile.DIST AS DR_DIST',
    //                             'drprofile.CITY AS DR_CITY',
    //                             'drprofile.PHOTO_URL AS DR_PHOTO',
    //                             'pharmacy.PHARMA_ID',
    //                             'pharmacy.ITEM_NAME as PHARMA_NAME',
    //                             'pharmacy.ADDRESS',
    //                             'pharmacy.PIN',
    //                             'pharmacy.CITY',
    //                             'pharmacy.DIST',
    //                             'pharmacy.STATE',
    //                             'pharmacy.CLINIC_MOBILE',
    //                             'pharmacy.EMAIL',
    //                             'pharmacy.LATITUDE',
    //                             'pharmacy.LONGITUDE',
    //                             'pharmacy.PHOTO_URL',
    //                             'pharmacy.LOGO_URL',
    //                             DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) * SIN(RADIANS('$latt'))))),2) as KM"),
    //                             'dr_availablity.SCH_DAY',
    //                             'dr_availablity.WEEK',
    //                             'dr_availablity.START_MONTH',
    //                             'dr_availablity.MONTH',
    //                             'dr_availablity.SCH_STATUS',
    //                             'dr_availablity.CHK_IN_STATUS',
    //                             'dr_availablity.DR_FEES',
    //                         )
    //                         ->where('drprofile.DIS_ID', $row->DIS_ID)
    //                         ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
    //                         ->where('drprofile.APPROVE', 'true')
    //                         ->orderByRaw('KM')
    //                         ->orderbyraw("FIELD(SCH_DAY,'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saterday')")
    //                         ->orderby('CHK_IN_STATUS')
    //                         ->get();

    //                     $doctors = [];
    //                     $pharmacies = [];

    //                     $startDate = carbon::now();
    //                     $endDate = $startDate->copy()->addMonths(1)->endOfMonth();
    //                     $currentDate = $startDate;

    //                     foreach ($othdr as $item) {
    //                         if ($item->DR_ID != $drid) {
    //                             $doctor = [
    //                                 'DR_ID' => $item->DR_ID,
    //                                 'DR_NAME' => $item->DR_NAME,
    //                                 'ADDRESS' => $item->DR_ADDRESS,
    //                                 'EMAIL' => $item->DR_EMAIL,
    //                                 'STATE' => $item->DR_STATE,
    //                                 'DIST' => $item->DR_DIST,
    //                                 'CITY' => $item->DR_CITY,
    //                                 'DR_MOBILE' => $item->DR_MOBILE,
    //                                 'SEX' => $item->SEX,
    //                                 'UID_NMC' => $item->UID_NMC,
    //                                 'REGN_NO' => $item->REGN_NO,
    //                                 'REGN_DT' => $item->REGN_DT,
    //                                 'COUNCIL' => $item->COUNCIL,
    //                                 'ACCOUNT_NAME' => $item->ACCOUNT_NAME,
    //                                 'BANK_NAME' => $item->BANK_NAME,
    //                                 'ACCOUNT_NO' => $item->ACCOUNT_NO,
    //                                 'IFSC_CODE' => $item->IFSC_CODE,
    //                                 'ASSOCIATED' => $item->ASSOCIATED,
    //                                 'DESIGNATION' => $item->DESIGNATION,
    //                                 'QUALIFICATION' => $item->QUALIFICATION,
    //                                 'DIS_ID' => $item->DIS_ID,
    //                                 'D_CATG' => $item->D_CATG,
    //                                 'EXPERIENCE' => $item->EXPERIENCE,
    //                                 'SERVICES' => $item->SERVICES,
    //                                 'LANGUAGE' => $item->LANGUAGE,
    //                                 'DR_PHOTO' => $item->DR_PHOTO,
    //                                 'DR_ABOUT' => $item->DR_ABOUT,
    //                                 'AWARDS' => $item->AWARDS,
    //                             ];
    //                             $doctors[] = $doctor;
    //                             continue;
    //                         }
    //                         if ($item->CHK_IN_STATUS === 'IN' || $item->CHK_IN_STATUS === 'TIMELY') {
    //                             if (is_numeric($item->SCH_DAY)) {
    //                                 $currentYear = date("Y");
    //                                 $startDate = new DateTime("{$currentYear}-$item->START_MONTH-$item->SCH_DAY");

    //                                 for ($i = 0; $i < 12; $i++) {
    //                                     $dates = [];
    //                                     $dates = $startDate->format('Ymd');
    //                                     $cym = date('Ymd');

    //                                     $startDate->modify('+' . $item->MONTH . 'months');
    //                                     if ($dates >= $cym) {
    //                                         $data1 = [
    //                                             "PHARMA_ID" => $item->PHARMA_ID,
    //                                             "DR_ID" => $item->DR_ID,
    //                                             "DR_NAME" => $item->DR_NAME,
    //                                             "DR_FEES" => $item->DR_FEES,
    //                                             "PHARMA_NAME" => $item->PHARMA_NAME,
    //                                             "ADDRESS" => $item->ADDRESS,
    //                                             "CITY" => $item->CITY,
    //                                             "DIST" => $item->DIST,
    //                                             "CLINIC_MOBILE" => $item->CLINIC_MOBILE,
    //                                             "PIN" => $item->PIN,
    //                                             "EMAIL" => $item->EMAIL,
    //                                             "STATE" => $item->STATE,
    //                                             "LATITUDE" => $item->LATITUDE,
    //                                             "LONGITUDE" => $item->LONGITUDE,
    //                                             "PHOTO_URL" => $item->PHOTO_URL,
    //                                             "KM" => $item->KM,
    //                                             "AVAILABLE_DT" =>  $dates,
    //                                             "DR_STATUS" => $item->CHK_IN_STATUS
    //                                         ];
    //                                         if (!empty($data1)) {
    //                                             break;
    //                                         }
    //                                     } else {
    //                                         continue;
    //                                     }
    //                                 }
    //                                 if (!empty($data1)) {
    //                                     $uniqueKey = $item->PHARMA_ID;
    //                                     if (!in_array($uniqueKey, $uniqueKeys)) {
    //                                         $uniqueKeys[] = $uniqueKey;
    //                                         $pharmacies[] = $data1;
    //                                     }
    //                                 }
    //                             } else {
    //                                 $data1 = [];
    //                                 $dayOrder = ['Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6];

    //                                 $currentDay = date('l');
    //                                 $currentDayNum = $dayOrder[$currentDay];

    //                                 $fltr_arr = $othdr->filter(function ($items) use ($item) {
    //                                     return $items->DR_ID === $item->DR_ID && $items->PHARMA_ID === $item->PHARMA_ID && $items->SCH_STATUS === 'Regular';
    //                                 });

    //                                 $dravail = $fltr_arr->map(function ($items) use ($dayOrder) {
    //                                     $dayNum = $dayOrder[$items->SCH_DAY] ?? -1;
    //                                     return [
    //                                         "SCH_DAY_NUM" => $dayNum,
    //                                         "SCH_DAY" => $items->SCH_DAY,
    //                                         "WEEK" => $items->WEEK,
    //                                         "CHK_IN_STATUS" => $items->CHK_IN_STATUS,
    //                                     ];
    //                                 });
    //                                 $sortedDravail = $dravail->sort(function ($a, $b) use ($currentDayNum) {
    //                                     $a_diff = ($a['SCH_DAY_NUM'] - $currentDayNum + 7) % 7;
    //                                     $b_diff = ($b['SCH_DAY_NUM'] - $currentDayNum + 7) % 7;
    //                                     return $a_diff - $b_diff;
    //                                 });

    //                                 $firstItem = $sortedDravail->first();

    //                                 // $firstItem = $sortedDravail->first(function ($item) {
    //                                 //     return $item['CHK_IN_STATUS'] === 'Cancelled';
    //                                 // });

    //                                 $startDate = carbon::now();
    //                                 $endDate = $startDate->copy()->addMonths(1)->endOfMonth();
    //                                 $currentDate = $startDate;

    //                                 while ($currentDate->lte($endDate)) {
    //                                     $schday = "is" . $firstItem['SCH_DAY'];
    //                                     $string = $firstItem['WEEK'];
    //                                     $array = explode(",", $string);
    //                                     $avl = $firstItem['CHK_IN_STATUS'];

    //                                     if ($currentDate->$schday()) {
    //                                         $dateString = $currentDate->toDateString();
    //                                         $date = Carbon::createFromFormat('Y-m-d', $dateString);
    //                                         $formattedDate = $date->format('Ymd');
    //                                         foreach ($array as $value) {
    //                                             if ($date->weekOfMonth == $value) {
    //                                                 $data1 = [
    //                                                     "PHARMA_ID" => $item->PHARMA_ID,
    //                                                     "DR_ID" => $item->DR_ID,
    //                                                     "DR_NAME" => $item->DR_NAME,
    //                                                     "DR_FEES" => $item->DR_FEES,
    //                                                     "PHARMA_NAME" => $item->PHARMA_NAME,
    //                                                     "ADDRESS" => $item->ADDRESS,
    //                                                     "CITY" => $item->CITY,
    //                                                     "DIST" => $item->DIST,
    //                                                     "CLINIC_MOBILE" => $item->CLINIC_MOBILE,
    //                                                     "PIN" => $item->PIN,
    //                                                     "EMAIL" => $item->EMAIL,
    //                                                     "STATE" => $item->STATE,
    //                                                     "LATITUDE" => $item->LATITUDE,
    //                                                     "LONGITUDE" => $item->LONGITUDE,
    //                                                     "PHOTO_URL" => $item->PHOTO_URL,
    //                                                     "KM" => $item->KM,
    //                                                     "AVAILABLE_DT" =>  $formattedDate,
    //                                                     "DR_STATUS" => $avl
    //                                                 ];
    //                                             }
    //                                         }
    //                                     }


    //                                     $currentDate->addDay();
    //                                     if (!empty($data1)) {
    //                                         $uniqueKey = $item->PHARMA_ID;
    //                                         if (!in_array($uniqueKey, $uniqueKeys)) {
    //                                             $uniqueKeys[] = $uniqueKey;
    //                                             $pharmacies[] = $data1;
    //                                         }
    //                                     }
    //                                 }
    //                             }
    //                         } else {
    //                             continue;
    //                         }
    //                     }

    //                     $groupedData[$row->DR_ID] = [
    //                         "DR_ID" => $row->DR_ID,
    //                         "DR_NAME" => $row->DR_NAME,
    //                         'ADDRESS' => $row->DR_ADDRESS,
    //                         'EMAIL' => $row->DR_EMAIL,
    //                         'STATE' => $row->DR_STATE,
    //                         'DIST' => $row->DR_DIST,
    //                         'CITY' => $row->DR_CITY,
    //                         'DR_MOBILE' => $row->DR_MOBILE,
    //                         'SEX' => $row->SEX,
    //                         'UID_NMC' => $row->UID_NMC,
    //                         'REGN_NO' => $row->REGN_NO,
    //                         'REGN_DT' => $row->REGN_DT,
    //                         'COUNCIL' => $row->COUNCIL,
    //                         'ACCOUNT_NAME' => $row->ACCOUNT_NAME,
    //                         'BANK_NAME' => $row->BANK_NAME,
    //                         'ACCOUNT_NO' => $row->ACCOUNT_NO,
    //                         'IFSC_CODE' => $row->IFSC_CODE,
    //                         'ASSOCIATED' => $row->ASSOCIATED,
    //                         'DESIGNATION' => $row->DESIGNATION,
    //                         'QUALIFICATION' => $row->QUALIFICATION,
    //                         'D_CATG' => $row->D_CATG,
    //                         'EXPERIENCE' => $row->EXPERIENCE,
    //                         'SERVICES' => $row->SERVICES,
    //                         'LANGUAGE' => $row->LANGUAGE,
    //                         'DR_PHOTO' => $row->PHOTO_URL,
    //                         'DR_ABOUT' => $row->DR_ABOUT,
    //                         'AWARDS' => $row->AWARDS,
    //                         "TOT_FRIENDLY" => 0,
    //                         "TOT_SATISFY" => 0,
    //                         "TOT_ECONOMY" => 0,
    //                         "TOT_RECOMEND" => 0,
    //                         "REVIEW_DETAILS" => [],
    //                         "OTHER_DOCTORS" => $doctors,
    //                         "CLINIC" => $pharmacies,
    //                     ];
    //                 }

    //                 $groupedData[$row->DR_ID]['REVIEW_DETAILS'][] = [
    //                     "DR_ID" => $row->DR_ID,
    //                     "FAMILY_ID" => $row->FAMILY_ID,
    //                     "FRIENDLY" => $row->FRIENDLY,
    //                     "SATISFY" => $row->SATISFY,
    //                     "ECONOMY" => $row->ECONOMY,
    //                     "RECOMEND" => $row->RECOMEND,
    //                     "REMARKS" => $row->REMARKS,
    //                     "REVIEW_DT" => $row->REVIEW_DT,
    //                 ];

    //                 $groupedData[$row->DR_ID]['TOT_FRIENDLY'] += $row->FRIENDLY;
    //                 $groupedData[$row->DR_ID]['TOT_SATISFY'] += $row->SATISFY;
    //                 $groupedData[$row->DR_ID]['TOT_ECONOMY'] += $row->ECONOMY;
    //                 $groupedData[$row->DR_ID]['TOT_RECOMEND'] += $row->RECOMEND;

    //                 foreach ($groupedData as $drId => &$drids) {
    //                     if ($row->FAMILY_ID != null) {
    //                         $totalReviews = count($drids['REVIEW_DETAILS']);
    //                         $drids['TOT_REVIEW'] = $totalReviews;
    //                     } else {
    //                         $totalReviews = 0;
    //                         $drids['TOT_REVIEW'] = $totalReviews;
    //                     }
    //                     if ($totalReviews > 0) {
    //                         $drids['AVG_FRIENDLY'] = $drids['TOT_FRIENDLY'] / $totalReviews;
    //                         $drids['AVG_SATISFY'] = $drids['TOT_SATISFY'] / $totalReviews;
    //                         $drids['AVG_ECONOMY'] = $drids['TOT_ECONOMY'] / $totalReviews;
    //                         $drids['AVG_RECOMEND'] = $drids['TOT_RECOMEND'] / $totalReviews;
    //                     } else {
    //                         $drids['AVG_FRIENDLY'] = 0;
    //                         $drids['AVG_SATISFY'] = 0;
    //                         $drids['AVG_ECONOMY'] = 0;
    //                         $drids['AVG_RECOMEND'] = 0;
    //                     }
    //                     $avgzeroCount = 0;
    //                     if ($drids['AVG_FRIENDLY'] == 0) $avgzeroCount++;
    //                     if ($drids['AVG_SATISFY'] == 0) $avgzeroCount++;
    //                     if ($drids['AVG_ECONOMY'] == 0) $avgzeroCount++;
    //                     if ($drids['AVG_RECOMEND'] == 0) $avgzeroCount++;
    //                     if ($avgzeroCount != 4) {
    //                         $drids['AVG_REVIEW'] = ($drids['AVG_FRIENDLY'] + $drids['AVG_SATISFY'] + $drids['AVG_ECONOMY'] + $drids['AVG_RECOMEND']) / (4 - $avgzeroCount);
    //                     } else {
    //                         $drids['AVG_REVIEW'] = 0;
    //                     }

    //                     if ($drids['AVG_REVIEW'] <= 2) {
    //                         $drids['REVIEW_REMARKS'] = 'Not Satisfy';
    //                     } elseif ($drids['AVG_REVIEW'] <= 4) {
    //                         $drids['REVIEW_REMARKS'] = 'Good';
    //                     } elseif ($drids['AVG_REVIEW'] <= 6) {
    //                         $drids['REVIEW_REMARKS'] = 'Very Good';
    //                     } elseif ($drids['AVG_REVIEW'] <= 8) {
    //                         $drids['REVIEW_REMARKS'] = 'Excellent';
    //                     } else {
    //                         $drids['REVIEW_REMARKS'] = 'Outstanding';
    //                     }
    //                 }
    //             }
    //             $data = array_values($groupedData);

    //             $response = ['Success' => true, 'Data' => $data, 'code' => 200];
    //         } else {
    //             $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
    //     }
    //     return $response;
    // }

    function vusamedr(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->json()->all();
            if (isset($input['DR_ID']) && isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $drid = $input['DR_ID'];

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
                    ->where(['drprofile.DR_ID' => $drid])
                    ->where('drprofile.APPROVE', 'true')
                    ->get();

                $disId = $data1[0]->DIS_ID;
                $clinics = $this->getClinicDrDt($latt, $lont, $drid);
                $othdr = $this->getOthrDr($latt, $lont, $drid, $disId);
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
                        "OTHER_DOCTORS" => $othdr,
                        "CLINIC" => $clinics,
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

                $data = array_values($groupedData);

                $response = ['Success' => true, 'Data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    private function getOthrDr($latt, $lont, $drid, $disId)
    {
        $data = [];
        $distinctDoctors = DB::table('dr_availablity')
            ->select('DR_ID', 'DR_FEES')
            ->distinct()
            ->where('DIS_ID', $disId)
            ->where('DR_ID', '<>', $drid)
            ->where('SCH_STATUS', '<>', 'NA');

        $data = DB::table('drprofile')
            ->joinSub($distinctDoctors, 'distinct_doctors', function ($join) {
                $join->on('drprofile.DR_ID', '=', 'distinct_doctors.DR_ID');
            })
            ->select(
                'drprofile.*',
                'drprofile.PHOTO_URL AS DR_PHOTO',
                'distinct_doctors.DR_ID',
            )
            ->get();

        return $data;
    }

    function vuallreview(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->json()->all();
            if (isset($input['DR_ID'])) {
                $drid = $input['DR_ID'];

                $data1 = DB::table('drprofile')
                    ->leftjoin('patient_review', 'drprofile.DR_ID', '=', 'patient_review.DR_ID')
                    ->leftjoin('users', 'users.mobile', '=', 'patient_review.FAMILY_ID')
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
                        'patient_review.FAMILY_ID',
                        'users.name',
                        'patient_review.FRIENDLY',
                        'patient_review.SATISFY',
                        'patient_review.ECONOMY',
                        'patient_review.RECOMEND',
                        'patient_review.REMARKS',
                        'patient_review.REVIEW_DT',
                    )
                    ->where(['drprofile.DR_ID' => $drid])
                    ->where('drprofile.APPROVE', 'true')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    $zeroCount = 0;
                    if ($row->FRIENDLY == 0)
                        $zeroCount++;
                    if ($row->SATISFY == 0)
                        $zeroCount++;
                    if ($row->ECONOMY == 0)
                        $zeroCount++;
                    if ($row->RECOMEND == 0)
                        $zeroCount++;
                    if ($zeroCount != 4) {
                        $avgReview = ($row->FRIENDLY + $row->SATISFY + $row->RECOMEND + $row->ECONOMY) / (4 - $zeroCount);
                    } else {
                        $avgReview = 0;
                    }

                    if ($avgReview <= 2) {
                        $reviewRemarks = 'Not Satisfy';
                    } elseif ($avgReview <= 4) {
                        $reviewRemarks = 'Good';
                    } elseif ($avgReview <= 6) {
                        $reviewRemarks = 'Very Good';
                    } elseif ($avgReview <= 8) {
                        $reviewRemarks = 'Excellent';
                    } else {
                        $reviewRemarks = 'Outstanding';
                    }
                    if (!isset($groupedData[$row->DR_ID])) {
                        $groupedData[$row->DR_ID] = [
                            "DR_ID" => $row->DR_ID,
                            "DR_NAME" => $row->DR_NAME,
                            "DR_MOBILE" => $row->DR_MOBILE,
                            "SEX" => $row->SEX,
                            "DESIGNATION" => $row->DESIGNATION,
                            "QUALIFICATION" => $row->QUALIFICATION,
                            "D_CATG" => $row->D_CATG,
                            "EXPERIENCE" => $row->EXPERIENCE,
                            "DR_PHOTO" => $row->PHOTO_URL,
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
                        "NAME" => $row->name,
                        "FRIENDLY" => $row->FRIENDLY,
                        "SATISFY" => $row->SATISFY,
                        "ECONOMY" => $row->ECONOMY,
                        "RECOMEND" => $row->RECOMEND,
                        "REMARKS" => $row->REMARKS,
                        "REVIEW_DT" => $row->REVIEW_DT,
                        "AVG_REVIEW" => $avgReview,
                        "REVIEW_REMARKS" => $reviewRemarks,
                    ];

                    $groupedData[$row->DR_ID]['TOT_FRIENDLY'] += $row->FRIENDLY;
                    $groupedData[$row->DR_ID]['TOT_SATISFY'] += $row->SATISFY;
                    $groupedData[$row->DR_ID]['TOT_ECONOMY'] += $row->ECONOMY;
                    $groupedData[$row->DR_ID]['TOT_RECOMEND'] += $row->RECOMEND;
                }

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

                $data = array_values($groupedData);

                $response = ['Success' => true, 'Data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
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
                $chkInSts = [
                    $data1->CHK_IN_STATUS,
                    $data1->CHK_IN_STATUS1,
                    $data1->CHK_IN_STATUS2,
                    $data1->CHK_IN_STATUS3
                ];
                $intvl = $data1->SLOT_INTVL ?? null;

                foreach ($chkInTimes as $index => $chkin) {
                    $maxbk = $maxbooks[$index];
                    $chkout = $chkOutTimes[$index];
                    $ckinsts = $chkInSts[$index];
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
                                ->where(['APPNT_ID' => $apid, 'APPNT_DT' => $apdt])
                                ->count();

                            // Log the booked count for debugging
                            Log::info('Booked Count: ' . $bookedCount);

                            $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                            $bookingSerials = range(1, $totalAppointments);
                            $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));
                            $availAppointments = count($availableSerials);
                            // $availAppointments= $totalAppointments-$bookedCount;

                            if ($cdt === $apdt) {
                                $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
                            } else {
                                $slot_sts = "Available";
                            }
                            if ($ckinsts === 'CANCELLED' || $ckinsts === 'OUT' || $ckinsts === 'LEAVE') {
                                $slot_sts = "Closed";
                                $availAppointments = 0;
                                $availableSerials = [];
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
                                ->where(['APPNT_ID' => $apid, 'APPNT_DT' => $apdt])
                                ->count();

                            // Log the booked count for debugging
                            Log::info('Booked Count: ' . $bookedCount);

                            $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                            $bookingSerials = range(1, $totalAppointments);
                            $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));
                            $availAppointments = count($availableSerials);

                            // $availAppointments= $totalAppointments-$bookedCount;

                            if ($cdt === $apdt) {
                                $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
                            } else {
                                $slot_sts = "Available";
                            }

                            if ($ckinsts === 'CANCELLED' || $ckinsts === 'OUT' || $ckinsts === 'LEAVE') {
                                $slot_sts = "Closed";
                                $availAppointments = 0;
                                $availableSerials = [];
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
                            Log::info('Booked Count: ' . $bookedCount);

                            $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                            $bookingSerials = range(1, $totalAppointments);
                            $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));
                            $availAppointments = count($availableSerials);
                            // $availAppointments= $totalAppointments-$bookedCount;

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

                            // $availAppointments= $totalAppointments-$bookedCount;

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




    private function createSlot($chkinTime, $endSlot, $intvl, $row, $apid, $cdt, $apdt)
    {
        return [
            "FROM" => $chkinTime->format('h:i A'),
            "TO" => $endSlot->format('h:i A'),
            // "TOTAL_APNT" => $totalAppointments,
            // "AVAIL_APNT" => $availableAppointments,
            // "BOOKING_SERIALS" => $bookingSerials,
            // "AVAILABLE_SERIALS" => $availableSerialsArray,
            // "SLOT_STATUS" => $slot_sts,
        ];
    }

    private function assignSlotToPeriod(&$slots, $slotString, $chkinTime)
    {
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


    // function patientarrive(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $input = $req->json()->all();
    //         if (isset($input['DR_ID']) && isset($input['PHARMA_ID']) && isset($input['APPNT_ID']) && isset($input['BOOKING_ID'])) {
    //             $drid = $input['DR_ID'];
    //             $fid = $input['PHARMA_ID'];
    //             $apid = $input['APPNT_ID'];
    //             $fromTime = $input['FROM_TIME'];


    //             $data1 = DB::table('dr_availablity')
    //                 ->where(['DR_ID' => $drid, 'PHARMA_ID' => $fid, 'ID' => $apid])
    //                 ->get();

    //             $slots = [
    //                 'Morning' => [],
    //                 'Afternoon' => [],
    //                 'Evening' => []
    //             ];
    //             $matchingSlots = [];
    //             $cumulativeAppntCount = 1;
    //             foreach ($data1 as $row) {
    //                 $chkin = $row->CHK_IN_TIME;
    //                 $chkout = $row->CHK_OUT_TIME;
    //                 $brkin = $row->BREAK_FROM;
    //                 $brkout = $row->BREAK_TO;
    //                 $intvl = $row->SLOT_INTVL;

    //                 $chkinTime = Carbon::createFromFormat('h:i A', $chkin);
    //                 $chkoutTime = Carbon::createFromFormat('h:i A', $chkout);

    //                 if ($chkinTime && $chkoutTime) {
    //                     while ($chkinTime->lessThan($chkoutTime)) {
    //                         $endSlot = $chkinTime->copy()->addMinutes($intvl);
    //                         if ($endSlot->greaterThan($chkoutTime)) {
    //                             break;
    //                         }
    //                         $data2 = DB::table('appointment')
    //                             ->select('APPNT_ID', 'BOOKING_SL')
    //                             ->where('APPNT_FROM', '=', $chkinTime->format('h:i A'))
    //                             ->where('APPNT_ID', '=', $apid)
    //                             ->get();

    //                         if (!$data2->isEmpty()) {
    //                             $totalAppointments = $data2->count();
    //                             $BookedSl = $data2->pluck('BOOKING_SL')->map(function ($item) {
    //                                 return $item === null ? 0 : (int) $item;
    //                             })->toArray();
    //                             $availableAppointments = $row->SLOT_APPNT - $totalAppointments;
    //                         } else {
    //                             $availableAppointments = $row->SLOT_APPNT;
    //                             $totalAppointments = 0;
    //                             $BookedSl = [];
    //                         }

    //                         if (isset($brkin) && isset($brkout)) {
    //                             $brkinTime = Carbon::createFromFormat('h:i A', $brkin);
    //                             $brkoutTime = Carbon::createFromFormat('h:i A', $brkout);
    //                             if ($chkinTime->greaterThanOrEqualTo($brkinTime) && $chkinTime->lessThan($brkoutTime)) {
    //                                 $totalAppointments = 0;
    //                                 $availableAppointments = 0;
    //                             }
    //                         }

    //                         if ($availableAppointments === 0 && $totalAppointments === 0) {
    //                             $bookingSerials = [];
    //                         } else {
    //                             $bookingSerials = range($cumulativeAppntCount, $cumulativeAppntCount + $row->SLOT_APPNT - 1);
    //                             $cumulativeAppntCount += $row->SLOT_APPNT;
    //                         }

    //                         $bookingSerialsCollection = collect($bookingSerials);
    //                         $BookedSlArray = is_array($BookedSl) ? $BookedSl : $BookedSl->toArray();
    //                         $availableSerials = $bookingSerialsCollection->diff($BookedSlArray);
    //                         $availableSerialsArray = \array_values($availableSerials->all());

    //                         $slotString = array(
    //                             "FROM" => $chkinTime->format('h:i A'),
    //                             "TO" => $endSlot->format('h:i A'),
    //                             "TOTAL_APNT" => $totalAppointments,
    //                             "AVAIL_APNT" => $availableAppointments,
    //                             "BOOKING_SERIALS" => $bookingSerials,
    //                             // "BOOKED_SERIALS" => $BookedSl
    //                             "AVAILABLE_SERIALS" => $availableSerialsArray,

    //                         );
    //                         if ($chkinTime->hour < 12) {
    //                             $slots['Morning'][] = $slotString;
    //                         } elseif ($chkinTime->hour < 16) {
    //                             $slots['Afternoon'][] = $slotString;
    //                         } else {
    //                             $slots['Evening'][] = $slotString;
    //                         }

    //                         if ($chkinTime->format('h:i A') == $fromTime) {
    //                             $bookingSerialsCollection = collect($bookingSerials);
    //                             $BookedSlArray = is_array($BookedSl) ? $BookedSl : $BookedSl->toArray();
    //                             $availableSerials = $bookingSerialsCollection->diff($BookedSlArray);
    //                             $availableSerialsArray = \array_values($availableSerials->all());
    //                             $matchingSlots = $availableSerialsArray[0];
    //                         }
    //                         $chkinTime->addMinutes($intvl);
    //                     }
    //                 } else {
    //                     return ["Error" => "Error in time conversion"];
    //                 }
    //             }

    //             $fields = [
    //                 "BOOKING_SL" => $matchingSlots,
    //                 "ARRIVE" => 'true'
    //             ];

    //             try {
    //                 DB::table('appointment')->where(['BOOKING_ID' => $input['BOOKING_ID']])->update($fields);
    //                 $response = [
    //                     'Success' => true,
    //                     'Message' => 'Confirmed Patient Serial No.',
    //                     'Serial No' => $matchingSlots,
    //                     'code' => 200
    //                 ];
    //             } catch (\Exception $e) {
    //                 $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
    //             }
    //             return $response;
    //         } else {
    //             $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
    //     }
    //     return $response;
    // }

    function generateDatesForSpecificDay($dayName, $startDate, $endDate)
    {
        $dates = [];
        $currentDate = Carbon::createFromFormat('Y-m-d', $startDate);

        while ($currentDate->lte(Carbon::createFromFormat('Y-m-d', $endDate))) {
            if ($currentDate->format('l') === $dayName) {
                $dates[] = $currentDate->toDateString();
            }
            $currentDate->addDay();
        }
        return $dates;
    }

    function vurptday(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->json()->all();
            date_default_timezone_set('Asia/Kolkata');
            if (isset($input['DR_ID']) && isset($input['PATIENT_ID'])) {
                $drid = $input['DR_ID'];
                $pid = $input['PATIENT_ID'];

                $data = DB::table('drprofile')
                    ->leftJoin('appointment', function ($join) use ($pid) {
                        $join->on('appointment.DR_ID', '=', 'drprofile.DR_ID')
                            ->where('appointment.PATIENT_ID', '=', $pid);
                    })
                    ->select('drprofile.REPORTING_DAY', 'appointment.APPNT_DT')
                    ->where(['drprofile.DR_ID' => $drid])
                    ->where('drprofile.APPROVE', 'true')
                    ->orderbydesc('appointment.APPNT_DT')
                    ->first();


                if (is_null($data->APPNT_DT)) {
                    $response = [
                        'Success' => true,
                        'Last_Visit' => null,
                        'Report_Day' => $data->REPORTING_DAY,
                        'Free_Visit' => 'true',
                        'code' => 200
                    ];
                } else {
                    $appntdt = $data->APPNT_DT;
                    $endDate = Carbon::createFromFormat('Ymd', $appntdt)->addDays($data->REPORTING_DAY)->format('Ymd');

                    if (Carbon::now()->format('Ymd') > $endDate) {
                        $response = [
                            'Success' => true,
                            'Last_Visit' => $data->APPNT_DT,
                            'Report_Day' => $data->REPORTING_DAY,
                            'Free_Visit' => 'false',
                            'code' => 200
                        ];
                    } else {
                        $response = [
                            'Success' => true,
                            'Last_Visit' => $data->APPNT_DT,
                            'Report_Day' => $data->REPORTING_DAY,
                            'Free_Visit' => 'true',
                            'code' => 200
                        ];
                    }
                }
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function del_user(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->json()->all();

            DB::table('user_family')->where('ID', '=', $input['PATIENT_ID'])->delete();

            $response = ['Success' => true, 'Message' => 'Records delete successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
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


                if ($f_id === 'AL' || $f_id == 'TT' || $f_id == 'AP') {
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

    function availdr(Request $request)
    {
        if ($request->isMethod('post')) {
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();
            if (isset($input['DRID']) && isset($input['PHARMAID'])) {

                $data = [];

                $data = $this->getSchDtSlot($input['DRID'], $input['PHARMAID']);

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

    private function getCatgDrDt($latt, $lont, $did)
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
            ->where(['dr_availablity.DIS_ID' => $did, 'pharmacy.STATUS' => 'Active'])
            ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
            ->where('drprofile.APPROVE', 'true')
            ->get();

        // return $totdr;

        $DRSCH = ['Doctors' => []];

        foreach ($totdr as $row1) {
            $dravail = DB::table('dr_availablity')
                ->where(['DR_ID' => $row1->DR_ID])
                ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
                ->orderby('dr_availablity.CHK_OUT_TIME')->get();
            $totapp = DB::table('appointment')->where(['DR_ID' => $row1->DR_ID])->get();
            $data = [];

            $chk = array("CHK_IN_TIME", "CHK_IN_TIME1", "CHK_IN_TIME2", "CHK_IN_TIME3");
            $chkout = array("CHK_OUT_TIME", "CHK_OUT_TIME1", "CHK_OUT_TIME2", "CHK_OUT_TIME3");
            $CHKINSTATUS = array("CHK_IN_STATUS", "CHK_IN_STATUS1", "CHK_IN_STATUS2", "CHK_IN_STATUS3");
            $delay = array("DR_DELAY", "DR_DELAY1", "DR_DELAY2", "DR_DELAY3");
            $chamber = array("CHEMBER_NO", "CHEMBER_NO1", "CHEMBER_NO2", "CHEMBER_NO3");

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
                                "PHARMA_ID" => $row->PHARMA_ID,
                                "ID" => $row->ID,
                                "DR_STATUS" => $dr_status,
                                "ABS_FDT" => $row->ABS_FDT,
                                "ABS_TDT" => $row->ABS_TDT,
                                "DR_ARRIVE" => $row->DR_ARRIVE,
                                "FROM" => $row->CHK_IN_TIME,
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

                                $nonNullChkInTimes = array_filter([$row->CHK_IN_TIME, $row->CHK_IN_TIME1, $row->CHK_IN_TIME2, $row->CHK_IN_TIME3], function ($time) {
                                    return !empty($time);
                                });
                                $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));
                                for ($i = 0; $i < count($nonNullChkInTimes); $i++) {
                                    $checkOutTime = Carbon::parse($row->{$chkout[$i]});
                                    if (!empty($row->{$delay[$i]})) {
                                        $checkOutTime = $checkOutTime->addMinutes($row->{$delay[$i]});
                                    }

                                    if ($currentTime->lessThanOrEqualTo($checkOutTime)) {
                                        if (!in_array($row->{$CHKINSTATUS[$i]}, ['OUT', 'CANCELLED', 'LEAVE'])) {
                                            $row->CHK_IN_TIME = $row->{$chk[$i]};
                                            $row->CHK_OUT_TIME = $row->{$chkout[$i]};
                                            $row->CHK_IN_STATUS = $row->{$CHKINSTATUS[$i]};
                                            $row->CHEMBER_NO = $row->{$chamber[$i]};
                                            $row->DR_DELAY = $row->{$delay[$i]} ?? null;
                                            break;
                                        } else {
                                            $nextIndex = $i + 1;
                                            if ($nextIndex < count($chk) && !empty($row->{$chk[$nextIndex]}) && !in_array($row->{$CHKINSTATUS[$nextIndex]}, ['OUT', 'CANCELLED', 'LEAVE'])) {
                                                $row->CHK_IN_TIME = $row->{$chk[$nextIndex]};
                                                $row->CHK_OUT_TIME = $row->{$chkout[$nextIndex]};
                                                $row->CHK_IN_STATUS = $row->{$CHKINSTATUS[$nextIndex]};
                                                $row->CHEMBER_NO = $row->{$chamber[$nextIndex]};
                                                $row->DR_DELAY = $row->{$delay[$nextIndex]} ?? null;
                                            } else {
                                                $row->CHK_IN_TIME = $row->{$chk[$i]};
                                                $row->CHK_OUT_TIME = $row->{$chkout[$i]};
                                                $row->CHK_IN_STATUS = $row->{$CHKINSTATUS[$i]};
                                                $row->CHEMBER_NO = $row->{$chamber[$i]};
                                                $row->DR_DELAY = $row->{$delay[$i]} ?? null;
                                            }
                                        }
                                    }
                                }
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
                                    "PHARMA_ID" => $row->PHARMA_ID,
                                    "ID" => $row->ID,
                                    "FROM" => $row->CHK_IN_TIME,
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

                $slots = [];
                $drid = $row1->DR_ID;
                $fid = $data[0]['PHARMA_ID'];
                $apid = $data[0]['ID'];
                $apdt = $data[0]['SCH_DT'];
                $cdt = date('Ymd');

                $data1 = DB::table('dr_availablity')
                    ->where(['DR_ID' => $drid, 'PHARMA_ID' => $fid, 'ID' => $apid])
                    ->first();

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
                $chkInSts = [
                    $data1->CHK_IN_STATUS,
                    $data1->CHK_IN_STATUS1,
                    $data1->CHK_IN_STATUS2,
                    $data1->CHK_IN_STATUS3
                ];
                $intvl = $data1->SLOT_INTVL ?? null;
                $matchingSlot = null;
                $nextAvailableSlot = null;

                foreach ($chkInTimes as $index => $chkin) {
                    $maxbk = $maxbooks[$index];
                    $chkout = $chkOutTimes[$index];
                    $ckinsts = $chkInSts[$index];
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
                                ->where(['APPNT_ID' => $apid, 'APPNT_DT' => $apdt])
                                ->count();

                            // Log the booked count for debugging
                            Log::info('Booked Count: ' . $bookedCount);

                            $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                            $bookingSerials = range(1, $totalAppointments);
                            $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));
                            $availAppointments = count($availableSerials);
                            // $availAppointments= $totalAppointments-$bookedCount;

                            if ($cdt === $apdt) {
                                $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
                            } else {
                                $slot_sts = "Available";
                            }
                            if ($ckinsts === 'CANCELLED' || $ckinsts === 'OUT' || $ckinsts === 'LEAVE') {
                                $slot_sts = "Closed";
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

                            $slots[][] = $slotString;
                            if ($currentTime->between($chkinTime, $endSlot) && $slot_sts === 'Available' && $availAppointments > 0) {
                                $matchingSlot = $slotString;
                                break 2; // Exit both loops
                            } elseif ($slot_sts === 'Available' && $nextAvailableSlot === null && $availAppointments > 0) {
                                $nextAvailableSlot = $slotString;
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
                                ->where(['APPNT_ID' => $apid, 'APPNT_DT' => $apdt])
                                ->count();

                            $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                            $bookingSerials = range(1, $totalAppointments);
                            $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));
                            $availAppointments = count($availableSerials);

                            // $availAppointments= $totalAppointments-$bookedCount;

                            if ($cdt === $apdt) {
                                $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
                            } else {
                                $slot_sts = "Available";
                            }
                            if ($ckinsts === 'CANCELLED' || $ckinsts === 'OUT' || $ckinsts === 'LEAVE') {
                                $slot_sts = "Closed";
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

                            $slots[][] = $slotString;

                            if ($currentTime->between($chkinTime, $endSlot) && $slot_sts === 'Available' && $availAppointments > 0) {
                                $matchingSlot = $slotString;
                                break 2; // Exit both loops
                            } elseif ($slot_sts === 'Available' && $nextAvailableSlot === null && $availAppointments > 0) {
                                $nextAvailableSlot = $slotString;
                            }

                            $chkinTime->addMinutes($intvl);
                        }
                    }
                }


                if ($matchingSlot) {
                    $data[0]['FROM'] = $matchingSlot['FROM'];
                } elseif ($nextAvailableSlot) {
                    $data[0]['FROM'] = $nextAvailableSlot['FROM'];
                } else {
                    $data[0]['FROM'] = null;
                }


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
                    "FROM" => $sixRows[0]['FROM'],
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



    // private function getDtSlot($fid, $drid, $apid, $apdt)
    // {
    //     $cdt = date('Ymd');

    //     $data1 = DB::table('dr_availablity')
    //         ->where(['DR_ID' => $drid, 'PHARMA_ID' => $fid, 'ID' => $apid])
    //         ->get();

    //     $slots = [
    //         'Morning' => [],
    //         'Afternoon' => [],
    //         'Evening' => [],
    //         'Night' => []
    //     ];
    //     $cumulativeAppntCount = 1;
    //     foreach ($data1 as $row) {
    //         $chkInTimes = [
    //             $row->CHK_IN_TIME,
    //             $row->CHK_IN_TIME1,
    //             $row->CHK_IN_TIME2,
    //             $row->CHK_IN_TIME3
    //         ];
    //         $chkOutTimes = [
    //             $row->CHK_OUT_TIME,
    //             $row->CHK_OUT_TIME1,
    //             $row->CHK_OUT_TIME2,
    //             $row->CHK_OUT_TIME3
    //         ];
    //         $maxbooks = [
    //             $row->MAX_BOOK,
    //             $row->MAX_BOOK1,
    //             $row->MAX_BOOK2,
    //             $row->MAX_BOOK3
    //         ];
    //         $intvl = $row->SLOT_INTVL ?? null;

    //         foreach ($chkInTimes as $index => $chkin) {
    //             $maxbk = $maxbooks[$index];
    //             $chkout = $chkOutTimes[$index];
    //             if ($chkin === null) {
    //                 continue;
    //             }

    //             try {
    //                 $chkinTime = Carbon::createFromFormat('h:i A', $chkin);
    //                 $chkoutTime = $chkout !== null ? Carbon::createFromFormat('h:i A', $chkout) : $chkinTime->copy()->addMinutes($intvl * $maxbk);
    //             } catch (\Exception $e) {
    //                 return ["Error" => "Error in time conversion: " . $e->getMessage()];
    //             }

    //             $slot_sts = null;

    //             if ($row->SLOT == '1') {
    //                 // Create hourly slots
    //                 while ($chkinTime->lessThan($chkoutTime)) {
    //                     $endSlot = $chkinTime->copy()->addHour();
    //                     if ($endSlot->greaterThan($chkoutTime)) {
    //                         $endSlot = $chkoutTime;
    //                     }

    //                     $totalAppointments = 1;
    //                     $availableAppointments = $endSlot->diffInMinutes($chkinTime) / $intvl;
    //                     $bookingSerials = range($cumulativeAppntCount, $cumulativeAppntCount + $availableAppointments - 1);
    //                     $cumulativeAppntCount += $availableAppointments;

    //                     // Fetch booked slots for the current time slot
    //                     $data2 = DB::table('appointment')
    //                         ->select('APPNT_ID', 'BOOKING_SL')
    //                         ->where('APPNT_FROM', '>=', $chkinTime->format('h:i A'))
    //                         ->where('APPNT_TO', '<=', $endSlot->format('h:i A'))
    //                         ->where('APPNT_ID', '=', $apid)
    //                         ->get();

    //                     $BookedSl = $data2->pluck('BOOKING_SL')->map(function ($item) {
    //                         return $item === null ? 0 : (int) $item;
    //                     })->toArray();
    //                     $BookedSlArray = is_array($BookedSl) ? $BookedSl : $BookedSl->toArray();

    //                     $bookingSerialsCollection = collect($bookingSerials);
    //                     $availableSerials = $bookingSerialsCollection->diff($BookedSlArray);
    //                     $availableSerialsArray = array_values($availableSerials->all());

    //                     if ($cdt === $apdt) {
    //                         $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
    //                     } else {
    //                         $slot_sts = "Available";
    //                     }

    //                     $slotString = [
    //                         "FROM" => $chkinTime->format('h:i A'),
    //                         "TO" => $endSlot->format('h:i A'),
    //                         "TOTAL_APNT" => $availableAppointments,
    //                         "AVAIL_APNT" => count($availableSerialsArray),
    //                         "BOOKING_SERIALS" => $bookingSerials,
    //                         "AVAILABLE_SERIALS" => $availableSerialsArray,
    //                         "SLOT_STATUS" => $slot_sts,
    //                     ];

    //                     if ($chkinTime->hour < 12) {
    //                         $slots['Morning'][] = $slotString;
    //                     } elseif ($chkinTime->hour < 16) {
    //                         $slots['Afternoon'][] = $slotString;
    //                     } elseif ($chkinTime->hour < 20) {
    //                         $slots['Evening'][] = $slotString;
    //                     } else {
    //                         $slots['Night'][] = $slotString;
    //                     }

    //                     $chkinTime->addHour();
    //                 }
    //             } else if ($row->SLOT == '2') {
    //                 while ($chkinTime->lessThan($chkoutTime)) {
    //                     $endSlot = $chkinTime->copy()->addMinutes($intvl);
    //                     if ($endSlot->greaterThan($chkoutTime)) {
    //                         break;
    //                     }

    //                     // Fetch booked slots for the current time slot
    //                     $data2 = DB::table('appointment')
    //                         ->select('APPNT_ID', 'BOOKING_SL')
    //                         ->where('APPNT_FROM', '=', $chkinTime->format('h:i A'))
    //                         ->where('APPNT_TO', '=', $endSlot->format('h:i A'))
    //                         ->where('APPNT_ID', '=', $apid)
    //                         ->get();

    //                     $totalAppointments = 1;
    //                     $BookedSl = $data2->pluck('BOOKING_SL')->map(function ($item) {
    //                         return $item === null ? 0 : (int) $item;
    //                     })->toArray();
    //                     $BookedSlArray = is_array($BookedSl) ? $BookedSl : $BookedSl->toArray();

    //                     $bookingSerials = [$cumulativeAppntCount];
    //                     $cumulativeAppntCount += 1;

    //                     $bookingSerialsCollection = collect($bookingSerials);
    //                     $BookedSlArray = [];
    //                     $availableSerials = $bookingSerialsCollection->diff($BookedSlArray);
    //                     $availableSerialsArray = array_values($availableSerials->all());

    //                     if ($cdt === $apdt) {
    //                         $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
    //                     } else {
    //                         $slot_sts = "Available";
    //                     }

    //                     $slotString = [
    //                         "FROM" => $chkinTime->format('h:i A'),
    //                         "TO" => $endSlot->format('h:i A'),
    //                         "TOTAL_APNT" => $totalAppointments,
    //                         "AVAIL_APNT" => count($availableSerialsArray),
    //                         "BOOKING_SERIALS" => $bookingSerials,
    //                         "AVAILABLE_SERIALS" => $availableSerialsArray,
    //                         "SLOT_STATUS" => $slot_sts,
    //                     ];

    //                     if ($chkinTime->hour < 12) {
    //                         $slots['Morning'][] = $slotString;
    //                     } elseif ($chkinTime->hour < 16) {
    //                         $slots['Afternoon'][] = $slotString;
    //                     } elseif ($chkinTime->hour < 20) {
    //                         $slots['Evening'][] = $slotString;
    //                     } else {
    //                         $slots['Night'][] = $slotString;
    //                     }
    //                     $chkinTime->addMinutes($intvl);
    //                 }
    //             }
    //         }
    //     }
    //     return $slots;
    // }

    private function getDtSlot($fid, $drid, $apid, $apdt)
    {
        $cdt = date('Ymd');

        $data1 = DB::table('dr_availablity')
            ->where(['DR_ID' => $drid, 'PHARMA_ID' => $fid, 'ID' => $apid])
            ->get();

        $slots = [
            'Morning' => [],
            'Afternoon' => [],
            'Evening' => [],
            'Night' => []
        ];

        foreach ($data1 as $row) {
            $chkInTimes = [
                $row->CHK_IN_TIME,
                $row->CHK_IN_TIME1,
                $row->CHK_IN_TIME2,
                $row->CHK_IN_TIME3
            ];
            $chkOutTimes = [
                $row->CHK_OUT_TIME,
                $row->CHK_OUT_TIME1,
                $row->CHK_OUT_TIME2,
                $row->CHK_OUT_TIME3
            ];
            $maxBooks = [
                $row->MAX_BOOK,
                $row->MAX_BOOK1,
                $row->MAX_BOOK2,
                $row->MAX_BOOK3
            ];

            foreach ($chkInTimes as $index => $chkin) {
                $chkout = $chkOutTimes[$index];
                $maxbook = $maxBooks[$index];
                if ($chkin === null) {
                    continue;
                }


                // try {
                //     $chkinTime = Carbon::createFromFormat('h:i A', $chkin);
                //     if ($chkout === null) {
                //         $minutesDiff = $maxbook * $row->SLOT_INTVL;
                //         $chkout = $chkinTime->copy()->addMinutes($minutesDiff);
                //     }
                //     $chkoutTime = $chkout !== null ? Carbon::createFromFormat('h:i A', $chkout) : null;
                // } catch (\Exception $e) {
                //     return ["Error" => "Error in time conversion: " . $e->getMessage()];
                // }


                try {

                    $chkinTime = Carbon::createFromFormat('h:i A', $chkin);
                    // Calculate check-out time if null
                    if ($chkout === null) {

                        $minutesDiff = $maxbook * $row->SLOT_INTVL;
                        $chkout = $chkinTime->copy()->addMinutes($minutesDiff)->format('h:i A');
                    }

                    $chkoutTime = Carbon::createFromFormat('h:i A', $chkout);


                } catch (\Exception $e) {
                    Log::error("Error in time conversion: " . $e->getMessage());
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





    private function getClinic($latt, $lont, $did)
    {
        $data = [];
        $data['Clinic'] = DB::table('pharmacy')
            ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
            ->distinct('pharmacy.PHARMA_ID')
            ->select(
                'pharmacy.PHARMA_ID',
                'pharmacy.ITEM_NAME AS PHARMA_NAME',
                'pharmacy.ADDRESS',
                'pharmacy.CITY',
                'pharmacy.DIST',
                'pharmacy.STATE',
                'pharmacy.PHOTO_URL',
                'pharmacy.LOGO_URL',
                'pharmacy.CLINIC_TYPE',
                'pharmacy.OPD',
                DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                    * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                    * SIN(RADIANS('$latt'))))),0) as KM"),
            )
            ->where('pharmacy.STATUS', '=', 'Active')
            ->where('dr_availablity.DIS_ID', '=', $did)
            ->orderbyraw('KM')
            ->take(25)
            ->get();
        return $data;
    }

    private function getClinicDrDt($latt, $lont, $drid)
    {
        $drcl = DB::table('pharmacy')
            ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
            ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
            ->distinct('dr_availablity.DR_ID')
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
                'drprofile.PHOTO_URL AS DR_PHOTO',
                'dr_availablity.DR_FEES',
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
                'pharmacy.OPD',
                'pharmacy.CLINIC_MOBILE',
                'pharmacy.LATITUDE',
                'pharmacy.LONGITUDE',
                'dr_availablity.SCH_DAY',
                'dr_availablity.CHK_IN_STATUS',
                'dr_availablity.WEEK',
                'dr_availablity.START_MONTH',
                'dr_availablity.MONTH',
                'dr_availablity.SCH_STATUS',
                'dr_availablity.CHEMBER_NO',
                'dr_availablity.DR_DELAY',
                'dr_availablity.DR_ARRIVE',
                DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                        * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                        * SIN(RADIANS('$latt'))))),2) as KM"),
            ])
            ->where('pharmacy.STATUS', '=', 'Active')
            ->where('drprofile.DR_ID', '=', $drid)
            ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
            // ->orderbyraw("FIELD(dr_availablity.SCH_DAY,'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saterday')")
            // ->orderbyraw('KM')
            // ->orderby('drprofile.EXPERIENCE')
            ->take(25)
            ->get();

        // return $drcl;

        $DRSCH = [];

        foreach ($drcl as $row1) {
            $dravail = DB::table('dr_availablity')->distinct('DR_ID')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $row1->PHARMA_ID])->get();
            $totapp = DB::table('appointment')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $row1->PHARMA_ID])->get();
            $data = [];
            $chk = array("CHK_IN_TIME", "CHK_IN_TIME1", "CHK_IN_TIME2", "CHK_IN_TIME3");
            $chkout = array("CHK_OUT_TIME", "CHK_OUT_TIME1", "CHK_OUT_TIME2", "CHK_OUT_TIME3");
            $CHKINSTATUS = array("CHK_IN_STATUS", "CHK_IN_STATUS1", "CHK_IN_STATUS2", "CHK_IN_STATUS3");
            $delay = array("DR_DELAY", "DR_DELAY1", "DR_DELAY2", "DR_DELAY3");
            $chamber = array("CHEMBER_NO", "CHEMBER_NO1", "CHEMBER_NO2", "CHEMBER_NO3");

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

                                $nonNullChkInTimes = array_filter([$row->CHK_IN_TIME, $row->CHK_IN_TIME1, $row->CHK_IN_TIME2, $row->CHK_IN_TIME3], function ($time) {
                                    return !empty($time);
                                });
                                $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));
                                for ($i = 0; $i < count($nonNullChkInTimes); $i++) {
                                    $checkOutTime = Carbon::parse($row->{$chkout[$i]});
                                    if (!empty($row->{$delay[$i]})) {
                                        $checkOutTime = $checkOutTime->addMinutes($row->{$delay[$i]});
                                    }

                                    if ($currentTime->lessThanOrEqualTo($checkOutTime)) {
                                        if (!in_array($row->{$CHKINSTATUS[$i]}, ['OUT', 'CANCELLED', 'LEAVE'])) {
                                            $row->CHK_IN_TIME = $row->{$chk[$i]};
                                            $row->CHK_OUT_TIME = $row->{$chkout[$i]};
                                            $row->CHK_IN_STATUS = $row->{$CHKINSTATUS[$i]};
                                            $row->CHEMBER_NO = $row->{$chamber[$i]};
                                            $row->DR_DELAY = $row->{$delay[$i]} ?? null;
                                            break;
                                        } else {
                                            $nextIndex = $i + 1;
                                            if ($nextIndex < count($chk) && !empty($row->{$chk[$nextIndex]}) && !in_array($row->{$CHKINSTATUS[$nextIndex]}, ['OUT', 'CANCELLED', 'LEAVE'])) {
                                                $row->CHK_IN_TIME = $row->{$chk[$nextIndex]};
                                                $row->CHK_OUT_TIME = $row->{$chkout[$nextIndex]};
                                                $row->CHK_IN_STATUS = $row->{$CHKINSTATUS[$nextIndex]};
                                                $row->CHEMBER_NO = $row->{$chamber[$nextIndex]};
                                                $row->DR_DELAY = $row->{$delay[$nextIndex]} ?? null;
                                            } else {
                                                $row->CHK_IN_TIME = $row->{$chk[$i]};
                                                $row->CHK_OUT_TIME = $row->{$chkout[$i]};
                                                $row->CHK_IN_STATUS = $row->{$CHKINSTATUS[$i]};
                                                $row->CHEMBER_NO = $row->{$chamber[$i]};
                                                $row->DR_DELAY = $row->{$delay[$i]} ?? null;
                                            }
                                        }
                                    } else {
                                        $row->CHK_IN_TIME = $row->{$chk[$i]};
                                        $row->CHK_OUT_TIME = $row->{$chkout[$i]};
                                        $row->CHK_IN_STATUS = 'OUT';
                                        $row->CHEMBER_NO = NULL;
                                    }
                                }
                                if ($totalMaxBook - $totappct == 0) {
                                    $book_sts = "Closed";
                                } else {
                                    $book_sts = "Available";
                                }


                                $data[] = [
                                    "ID" => $row->ID,
                                    "SCH_DT" => $dates,
                                    "DR_STATUS" => $row->CHK_IN_STATUS,
                                    "ABS_FDT" => $row->ABS_FDT,
                                    "ABS_TDT" => $row->ABS_TDT,
                                    "DR_ARRIVE" => $row->DR_ARRIVE,
                                    "FROM" => $row->CHK_IN_TIME,
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
                $slots = [];
                $drid = $row1->DR_ID;
                $fid = $row1->PHARMA_ID;
                $apid = $data[0]['ID'];
                $apdt = $data[0]['SCH_DT'];
                $cdt = date('Ymd');

                $data1 = DB::table('dr_availablity')
                    ->where(['DR_ID' => $drid, 'PHARMA_ID' => $fid, 'ID' => $apid])
                    ->first();

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
                $chkInSts = [
                    $data1->CHK_IN_STATUS,
                    $data1->CHK_IN_STATUS1,
                    $data1->CHK_IN_STATUS2,
                    $data1->CHK_IN_STATUS3
                ];
                $intvl = $data1->SLOT_INTVL ?? null;
                $matchingSlot = null;
                $nextAvailableSlot = null;

                foreach ($chkInTimes as $index => $chkin) {
                    $maxbk = $maxbooks[$index];
                    $chkout = $chkOutTimes[$index];
                    $ckinsts = $chkInSts[$index];
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
                                ->where(['APPNT_ID' => $apid, 'APPNT_DT' => $apdt])
                                ->count();

                            // Log the booked count for debugging
                            Log::info('Booked Count: ' . $bookedCount);

                            $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                            $bookingSerials = range(1, $totalAppointments);
                            $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));
                            $availAppointments = count($availableSerials);
                            // $availAppointments= $totalAppointments-$bookedCount;

                            if ($cdt === $apdt) {
                                $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
                            } else {
                                $slot_sts = "Available";
                            }
                            if ($ckinsts === 'CANCELLED' || $ckinsts === 'OUT' || $ckinsts === 'LEAVE') {
                                $slot_sts = "Closed";
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

                            $slots[][] = $slotString;
                            if ($currentTime->between($chkinTime, $endSlot) && $slot_sts === 'Available' && $availAppointments > 0) {
                                $matchingSlot = $slotString;
                                break 2; // Exit both loops
                            } elseif ($slot_sts === 'Available' && $nextAvailableSlot === null && $availAppointments > 0) {
                                $nextAvailableSlot = $slotString;
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
                                ->where(['APPNT_ID' => $apid, 'APPNT_DT' => $apdt])
                                ->count();

                            $totalAppointments = ceil($endSlot->diffInMinutes($chkinTime) / $intvl);
                            $bookingSerials = range(1, $totalAppointments);
                            $availableSerials = array_values(array_diff($bookingSerials, range(0, $bookedCount)));
                            $availAppointments = count($availableSerials);

                            // $availAppointments= $totalAppointments-$bookedCount;

                            if ($cdt === $apdt) {
                                $slot_sts = $endSlot->lessThan(Carbon::now()) ? "Closed" : "Available";
                            } else {
                                $slot_sts = "Available";
                            }
                            if ($ckinsts === 'CANCELLED' || $ckinsts === 'OUT' || $ckinsts === 'LEAVE') {
                                $slot_sts = "Closed";
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

                            $slots[][] = $slotString;

                            if ($currentTime->between($chkinTime, $endSlot) && $slot_sts === 'Available' && $availAppointments > 0) {
                                $matchingSlot = $slotString;
                                break 2; // Exit both loops
                            } elseif ($slot_sts === 'Available' && $nextAvailableSlot === null && $availAppointments > 0) {
                                $nextAvailableSlot = $slotString;
                            }

                            $chkinTime->addMinutes($intvl);
                        }
                    }
                }

                if ($matchingSlot) {
                    $data[0]['FROM'] = $matchingSlot['FROM'];
                } elseif ($nextAvailableSlot) {
                    $data[0]['FROM'] = $nextAvailableSlot['FROM'];
                } else {
                    $data[0]['FROM'] = null;
                }


                $firstRowTOTime = $data[0]['TO'] ? Carbon::createFromFormat('h:i A', $data[0]['TO']) : null;
                $firstRowTOTime1 = $data[0]['TO1'] ? Carbon::createFromFormat('h:i A', $data[0]['TO1']) : null;
                $firstRowTOTime2 = $data[0]['TO2'] ? Carbon::createFromFormat('h:i A', $data[0]['TO2']) : null;
                $firstRowTOTime3 = $data[0]['TO3'] ? Carbon::createFromFormat('h:i A', $data[0]['TO3']) : null;

                $firstRowFromTime = $data[0]['CHK_IN_TIME'] ? Carbon::createFromFormat('h:i A', $data[0]['CHK_IN_TIME']) : null;
                $firstRowFromTime1 = $data[0]['CHK_IN_TIME1'] ? Carbon::createFromFormat('h:i A', $data[0]['CHK_IN_TIME1']) : null;
                $firstRowFromTime2 = $data[0]['CHK_IN_TIME2'] ? Carbon::createFromFormat('h:i A', $data[0]['CHK_IN_TIME2']) : null;
                $firstRowFromTime3 = $data[0]['CHK_IN_TIME3'] ? Carbon::createFromFormat('h:i A', $data[0]['CHK_IN_TIME3']) : null;

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
            $DRSCH[] = [
                "PHARMA_ID" => $row1->PHARMA_ID,
                "DR_ID" => $row1->DR_ID,
                "DR_NAME" => $row1->DR_NAME,
                "DR_FEES" => $row1->DR_FEES,
                "PHARMA_NAME" => $row1->PHARMA_NAME,
                "CLINIC_TYPE" => $row1->CLINIC_TYPE,
                "ADDRESS" => $row1->ADDRESS,
                "CITY" => $row1->CITY,
                "DIST" => $row1->DIST,
                "CLINIC_MOBILE" => $row1->CLINIC_MOBILE,
                "PIN" => $row1->PIN,
                "EMAIL" => $row1->EMAIL,
                "STATE" => $row1->STATE,
                "LATITUDE" => $row1->LATITUDE,
                "LONGITUDE" => $row1->LONGITUDE,
                "PHOTO_URL" => $row1->PHOTO_URL,
                "KM" => $row1->KM,
                "AVAILABLE_DT" => $sixRows[0]['SCH_DT'],
                "FROM" => $sixRows[0]['FROM'],
                "CHK_IN_TIME" => $sixRows[0]['CHK_IN_TIME'],
                "SLOT_STATUS" => $sixRows[0]['SLOT_STATUS'],
                "DR_STATUS" => $sixRows[0]['DR_STATUS'],
                "DR_ARRIVE" => $sixRows[0]['DR_ARRIVE'],
                "CHEMBER_NO" => $sixRows[0]['CHEMBER_NO'],
            ];
        }
        $uniqueDRSCH = array_values(array_unique(array_map('json_encode', $DRSCH), SORT_REGULAR));

        // Decode JSON into associative arrays
        $uniqueDRSCH = array_map(function ($item) {
            return json_decode($item, true);
        }, $uniqueDRSCH);

        if (empty($uniqueDRSCH)) {
            $uniqueDRSCH = [];
        } else {
            usort($uniqueDRSCH, function ($a, $b) {
                $statusOrder = ['IN' => 1, 'TIMELY' => 2, 'DELAY' => 3, 'CANCELLED' => 4, 'OUT' => 5, 'LEAVE' => 6];
                if ($a['AVAILABLE_DT'] != $b['AVAILABLE_DT']) {
                    return $a['AVAILABLE_DT'] <=> $b['AVAILABLE_DT'];
                }
                if ($statusOrder[$a['DR_STATUS']] != $statusOrder[$b['DR_STATUS']]) {
                    return $statusOrder[$a['DR_STATUS']] <=> $statusOrder[$b['DR_STATUS']];
                }
                return $a['FROM'] <=> $b['FROM'];
            });
            $uniqueDRSCH = array_values($uniqueDRSCH);
        }


        // Return the unique array
        return $uniqueDRSCH;
        // return $DRSCH;
    }


    // function book_facilities(Request $req)
    // {
    //     if (!$req->isMethod('post')) {
    //         return response()->json([
    //             'Success' => false,
    //             'Message' => 'Method Not Allowed.',
    //             'code' => 405
    //         ], 405);
    //     }
    //     $input = $req->all();
    //     $ctm = Carbon::now()->format('h:i A');
    //     $cdt = Carbon::now()->format('Ymd');

    //     $fileName = null;
    //     if ($req->file('FILE')) {
    //         $fac_id = $input['FACILITY_ID'] ?? null;
    //         $pat_id = $input['PATIENT_ID'] ?? null;
    //         $ph_id = $input['PHARMA_ID'] ?? null;
    //         // $fam_id = $input['FAMILY_ID'] ?? null;
    //         $book_id = strtoupper(substr(md5($fac_id . $cdt . $ph_id . $pat_id), 0, 10));


    //         $fileName = $book_id . "." . $req->file('FILE')->getClientOriginalExtension();
    //         $req->file('FILE')->storeAs('drprofile/drphoto', $fileName);
    //         $url = asset(storage::url('app/drprofile/drphoto')) . "/" . $fileName;
    //     }
    //             $url = $url ?? null;
    //     $fields_facility = [
    //         "FAMILY_ID" => $input['FAMILY_ID'] ?? null,
    //         "PATIENT_ID" => $input['PATIENT_ID'] ?? null,
    //         "FACILITY_ID" => $input['FACILITY_ID'] ?? null,
    //         "PHARMA_ID" => $input['PHARMA_ID'] ?? null,
    //         "PATIENT_NAME" => $input['PATIENT_NAME'] ?? null,
    //         "CONTACT_NAME" => $input['CONTACT_NAME'] ?? null,
    //         "RELATION" => $input['RELATION'] ?? null,
    //         "PATIENT_MOBILE" => $input['PATIENT_MOBILE'] ?? null,
    //         "CONTACT_MOBILE" => $input['CONTACT_MOBILE'] ?? null,
    //         "PATIENT_ADDRESS" => $input['PATIENT_ADDRESS'] ?? null,
    //         "CONTACT_ADDRESS" => $input['CONTACT_ADDRESS'] ?? null,
    //         "PRESCRIPTION" => $url,
    //         // "PHOTO_URL"=>$input['PHOTO_URL'] ?? null, 
    //         "BOOKING_ID" => $book_id,
    //         "BOOKING_TM" => $ctm,
    //         "BOOKING_DT" => $cdt,
    //         // "APPNT_DT"=>$input['APPNT_DT'] ?? null, 
    //         // "STATUS"=>$input['STATUS'] ?? null, 
    //         "SHORT_NOTE" => $input['SHORT_NOTE'] ?? null,
    //     ];

    //     try {
    //         DB::table('book_facilities')->insert($fields_facility);
    //         $response = ['Success' => true, 'Message' => 'Facility booked successfully', 'code' => 200];
    //     } catch (\Exception $e) {
    //         $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
    //     }
    //     return $response;
    // }

    function book_facilities(Request $req)
    {
        date_default_timezone_set('Asia/Kolkata');
        if (!$req->isMethod('post')) {
            return response()->json([
                'Success' => false,
                'Message' => 'Method Not Allowed.',
                'code' => 405
            ], 405);
        }
        $input = $req->all();
        $currentTime = Carbon::now()->format('h:i A');
        $currentDate = Carbon::now()->format('Ymd');

        $fileName = null;
        $url = null;
        $book_id = null;
        $fac_id = $input['FACILITY_ID'] ?? null;
        $pat_id = $input['PATIENT_ID'] ?? null;
        $ph_id = $input['PHARMA_ID'] ?? null;

        $book_id = strtoupper(substr(md5($fac_id . $currentDate . $ph_id . $pat_id), 0, 10));

        if ($req->file('FILE')) {

            $fileName = $book_id . "." . $req->file('FILE')->getClientOriginalExtension();
            $req->file('FILE')->storeAs('drprofile/drphoto', $fileName);

            $url = asset(Storage::url('app/drprofile/drphoto/' . $fileName));
        }


        $fields_facility = [
            "FAMILY_ID" => $input['FAMILY_ID'] ?? null,
            "PATIENT_ID" => $input['PATIENT_ID'] ?? null,
            "FACILITY_ID" => $input['FACILITY_ID'] ?? null,
            "PHARMA_ID" => $input['PHARMA_ID'] ?? null,
            "PATIENT_NAME" => $input['PATIENT_NAME'] ?? null,
            "CONTACT_NAME" => $input['CONTACT_NAME'] ?? null,
            "RELATION" => $input['RELATION'] ?? null,
            "PATIENT_MOBILE" => $input['PATIENT_MOBILE'] ?? null,
            "CONTACT_MOBILE" => $input['CONTACT_MOBILE'] ?? null,
            "PATIENT_ADDRESS" => $input['PATIENT_ADDRESS'] ?? null,
            "CONTACT_ADDRESS" => $input['CONTACT_ADDRESS'] ?? null,
            "PRESCRIPTION" => $url,
            "BOOKING_ID" => $book_id,
            "BOOKING_TM" => $currentTime,
            "BOOKING_DT" => $currentDate,
            "SHORT_NOTE" => $input['SHORT_NOTE'] ?? null,
        ];

        try {
            DB::table('book_facilities')->insert($fields_facility);
            $response = [
                'Success' => true,
                'Message' => 'Facility booked successfully',
                'data' => $fields_facility,
                'code' => 200
            ];
        } catch (\Exception $e) {
            $response = ['Success' => false, 'Message' => 'Facility already exists', 'code' => 500];
        }

        return $response;
    }


}
