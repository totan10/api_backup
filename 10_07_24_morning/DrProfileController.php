<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DrProfileController extends Controller
{
    function drlogin(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $response = array();
            $data = array();
            $input   = $request->json()->all();
            if (isset($input['Mobile']) && isset($input['Password'])) {
                $user = DB::table('users')
                    ->where('mobile', $request->input('Mobile'))
                    ->where('user_type', 'Doctor')
                    ->first();
                if ($user != null) {
                    if (($user->password) === md5($request->input('Password'))) {

                        $m = $request->input('Mobile');
                        $data = DB::select("select * from drprofile where DR_MOBILE='$m'");
                        $token = base64_encode($request->input('Mobile') . $user->password . $user->user_type);
                        $_SESSION['TOKEN'] = $token;
                        $response = ['Success' => true, "data" => $data, 'Message' => 'Login Successfully', 'User_Type' => $user->user_type, 'Token' => $token, 'status' => 200];
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

    function dcatg(Request $req)
    {
        $response = array();
        $data = array();
        $key = $req->ct;
        if ($key == null) {
            $data = DB::table('disease_catg')
                ->get();
        } else {
            $data = DB::table('disease_catg')
                ->take($key)
                ->get();
        }
        $response = ['Success' => true, 'data' => $data, 'code' => 200];
        return $response;
    }

    function adcatg()
    {
        $response = array();
        $data = array();
        $data = DB::table('disease_catg')
            ->get();
        $response = ['Success' => true, 'data' => $data, 'code' => 200];
        return $response;
    }

    function pincode(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input   = $req->json()->all();

            $response = array();
            $data = array();

            $pincode = $input['PIN'];
            if (strlen($pincode) === 6) {
                $data = DB::select("select * from city where PIN='$pincode' order by CITY");
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid Pincode.', 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method not allowed', 'code' => 405];
        }
        return $response;
    }

    function states()
    {
        $response = array();
        $data = array();
        $dcatg = array();
        $city = array();
        $stat = array();
        $dist = array();

        $q1 = DB::select("select DIS_CATG from disease_catg");
        foreach ($q1 as $row1) {
            $catg = $row1->DIS_CATG;
            array_push($dcatg, $catg);
        };
        $q2 = DB::select("select distinct STATE from city order by STATE");
        foreach ($q2 as $row2) {
            $s2 = $row2->STATE;
            $q3 = DB::select("select distinct DIST from city where STATE='$s2' order by DIST");
            foreach ($q3 as $row3) {
                $d1 = array();
                $d2 = $row3->DIST;
                $q4 = DB::select("select CITY from city where STATE='$s2' and DIST='$d2' order by CITY");
                foreach ($q4 as $row4) {
                    $c2 = $row4->CITY;
                    array_push($city, $c2);
                };
                $d1[$d2] = $city;
                unset($city);
                $city = array();
                array_push($dist, $d1);
            };
            $stat[$s2] = $dist;

            unset($dist);
            $dist = array();
        };
        $data = array("STATE" => $stat);
        $response = array("Success" => true, "data" => $data, "code" => 200);
        http_response_code(200);
        return $response;
    }

    function state()
    {
        $response = array();
        $data = array();

        $data = DB::select("select distinct STATE from city order by STATE");
        $response = array("Success" => true, "data" => $data, "code" => 200);
        http_response_code(200);
        return $response;
    }

    function district(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input   = $req->json()->all();

            $response = array();
            $data = array();
            $state = $input['STATE'];
            $data = DB::select("select distinct DIST from city where STATE='$state' order by DIST");
            $response = array("Success" => true, "data" => $data, "code" => 200);
            http_response_code(200);
        } else {
            $data = array(
                "Message" => "Method not allowed"
            );
            $response = array("Success" => false, "data" => $data, "code" => 405);
            http_response_code(405);
        }
        return $response;
    }
    function city(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input   = $req->json()->all();
            if (isset($input['STATE']) && isset($input['DISTRICT'])) {

                $response = array();
                $data = array();

                $data = DB::table('city')
                    ->where(['STATE' => $input['STATE'], 'DIST' => $input['DISTRICT']])
                    ->get('CITY');

                $response = array("Success" => true, "data" => $data, "code" => 200);
                http_response_code(200);
            } else {
                $data = array(
                    "Message" => "Invalid Parameter"
                );
                $response = array("Success" => false, "data" => $data, "code" => 422);
                http_response_code(422);
            }
        } else {
            $data = array(
                "Message" => "Method not allowed"
            );
            $response = array("Success" => false, "data" => $data, "code" => 405);
            http_response_code(405);
        }
        return $response;
    }

    function drsignup(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //$input = $req->json()->all();
            $input = $req->all();
            if (
                isset($input['DR_NAME']) && isset($input['GENDER']) && isset($input['EXPERIENCE']) && isset($input['SPECIALIZATION']) && isset($input['QUALIFICATION']) && isset($input['REGN_NO']) && isset($input['DR_MOBILE']) && isset($input['PASSWORD']) && isset($input['STATE']) && isset($input['DISTRICT']) && isset($input['CITY'])
            ) {
                $response = array();
                
                $dr_name = "Dr. " . $input['DR_NAME'];
                $sex = $input['GENDER'];
                $experience = $input['EXPERIENCE'];
                $d_catg = $input['SPECIALIZATION'];
                $qualification = $input['QUALIFICATION'];
                //$designation = $input['DESIGNATION'];
                $state = $input['STATE'];
                $dist = $input['DISTRICT'];
                $city = $input['CITY'];
                $regn_no = $input['REGN_NO'];
                $dr_mobile = $input['DR_MOBILE'];
                $password = md5($input['PASSWORD']);

                try {
                    if ($req->file('DRPROFILEPHOTO') !== null) {
                        $drprofilephoto = "DR_PROFILE_" . $dr_mobile . "." . $req->file('DRPROFILEPHOTO')->getClientOriginalExtension();
                        $updr_photo = $req->file('DRPROFILEPHOTO')->storeAs('drprofile/drphoto', $drprofilephoto);
                        $updr_photo_url = "http://healthezy.easytechitsolutions.com/healthezyapi/storage/app/drprofile/drphoto/" . $drprofilephoto;
                    } else {
                        $updr_photo_url = "";
                    }
                    if ($req->file('DRDOCUMENT') !== null) {
                        $drdoc = "DR_DOC_" . $regn_no . "." . $req->file('DRDOCUMENT')->getClientOriginalExtension();
                        $updr_doc = $req->file('DRDOCUMENT')->storeAs('drprofile/drdoc', $drdoc);
                        $updr_doc_url = "http://healthezy.easytechitsolutions.com/healthezyapi/storage/app/drprofile/drdoc/" . $drdoc;
                    } else {
                        $updr_doc_url = "";
                    }
                    $sql1 = "INSERT INTO `users`(`name`, `mobile`,`password`, `user_type`) VALUES ('$dr_name','$dr_mobile','$password','DOCTOR')";
                    DB::insert($sql1);
                } catch (\Throwable $th) {
                    $response = ['Success' => false, 'Message' => 'You are already registered.', 'code' => 200];
                    return $response;
                }
                $sql = "INSERT INTO `drprofile`(`DR_NAME`, `STATE`, `DIST`, `CITY`, `DR_MOBILE`, `SEX`, `REGN_NO`, `REGN_PROOF`, `QUALIFICATION`, `D_CATG`, `EXPERIENCE`, `PHOTO_URL`) 
                VALUES ('$dr_name','$state','$dist','$city','$dr_mobile','$sex','$regn_no','$updr_doc_url','$qualification','$d_catg','$experience','$updr_photo_url')";
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

    function adddr(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $input   = $request->json()->all();
            $headers = apache_request_headers();

            if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {

                if (
                    isset($input['DR_ID']) && isset($input['DR_FEES']) && isset($input['SCH_DAY']) && isset($input['WEEK']) && isset($input['BOOK_FROM'])
                    && isset($input['CHK_IN_TIME']) && isset($input['CHK_OUT_TIME']) && isset($input['SLOT'])
                ) {
                    $dr_id = $input['DR_ID'];
                    $pharma_id = $input['PHARMA_ID'];
                    $dr_fees = $input['DR_FEES'];
                    $sch_day = $input['SCH_DAY'];
                    $week = $input['WEEK'];
                    $book_from = $input['BOOK_FROM'];
                    $chk_in_time = $input['CHK_IN_TIME'];
                    $chk_out_time = $input['CHK_OUT_TIME'];
                    $slot = $input['SLOT'];
                    if (!empty($input['MAX_BOOK'])) {
                        $max_book = $input['MAX_BOOK'];
                    } else {
                        $max_book = "";
                    }

                    $response = array();
                    $data = array();

                    $SQL = "INSERT INTO `dr_availablity`(`DR_ID`, `DR_FEES`, `PHARMA_ID`, `SCH_DAY`, `WEEK`, `BOOK_FROM`, `CHK_IN_TIME`, `CHK_OUT_TIME`, `SLOT`, `MAX_BOOK`, `AVAIL_STATUS`) VALUES 
                    ('$dr_id','$dr_fees','$pharma_id','$sch_day','$week ','$book_from','$chk_in_time','$chk_out_time','$slot','$max_book','ACTIVE')";
                    $data = DB::insert($SQL);
                    $response = ['Success' => true, 'Message' => 'Doctor attached successfully', 'code' => 200];
                } else {
                    $response = ['Success' => false, 'Message' => 'Invalid Parameter', 'code' => 422];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'You are not Authorized', 'code' => 401];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }
}
