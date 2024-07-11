<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
// use Validator;
// use Illuminate\Contracts\Validation\Validator;

class ClinicController extends Controller
{
    function cliniclogin(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $response = array();
            date_default_timezone_set('Asia/Kolkata');
            $input   = $request->json()->all();

            if (isset($input['MOBILE']) && isset($input['PASSWORD'])) {
                $user = DB::table('pharmacy')
                ->select('PHARMA_ID','ITEM_NAME','CLINIC_MOBILE','PASSWORD','ADDRESS','CITY','DIST','PIN','STATE','PHOTO_URL','LOGO_URL','CLINIC_TYPE','LATITUDE','LONGITUDE')
                ->where(['CLINIC_MOBILE' => $input['MOBILE'], 'STATUS' => 'Active'])->first();
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
                        "PHOTO_64" => base64_encode(file_get_contents($user->PHOTO_URL)), 
                        "LOGO_64" => base64_encode(file_get_contents($user->LOGO_URL)), 
                        "CLINIC_TYPE" => $user->CLINIC_TYPE,
                        "LATITUDE" => $user->LATITUDE,
                        "LONGITUDE" => $user->LONGITUDE,
                    ];
                    if (($user->PASSWORD) === md5($request->input('PASSWORD'))) {
                        DB::table('pharmacy')->where(['CLINIC_MOBILE' => $input['MOBILE'], 'STATUS' => 'Active'])->update(['LOGIN'=>carbon::now()]);
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

    function admcliniclogin(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $response = array();
            date_default_timezone_set('Asia/Kolkata');
            $input   = $request->json()->all();

            if (isset($input['MOBILE']) && isset($input['PASSWORD'])) {
                $user = DB::table('pharmacy')
                ->select('PHARMA_ID','ITEM_NAME','CLINIC_MOBILE','PASSWORD','ADDRESS','CITY','DIST','PIN','STATE','PHOTO_URL','LOGO_URL','CLINIC_TYPE','LATITUDE','LONGITUDE','CHEMBER_CT')
                ->where(['CLINIC_MOBILE' => $input['MOBILE'], 'STATUS' => 'Active'])->first();
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
                    ];
                    if (($user->PASSWORD) === md5($request->input('PASSWORD'))) {
                        DB::table('pharmacy')->where(['CLINIC_MOBILE' => $input['MOBILE'], 'STATUS' => 'Active'])->update(['LOGIN'=>carbon::now()]);
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

    function cliniclogin1(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            session_start();
            $response = array();
            $data = array();
            $input   = $request->json()->all();
            if (isset($input['MOBILE']) && isset($input['PASSWORD'])) {
                $user = DB::table('users')->where('mobile', $request->input('MOBILE'))->first();
                if ($user != null) {
                    if (($user->password) === md5($input['PASSWORD'])) {

                        $data1 = DB::table('pharmacy')
                            ->select('PHARMA_ID', 'ITEM_NAME', 'CLINIC_MOBILE', 'ADDRESS', 'CITY', 'DIST', 'PIN', 'STATE', 'PHOTO_URL', 'LOGO_URL', 'CLINIC_TYPE', 'LATITUDE', 'LONGITUDE')
                            ->where(['CLINIC_MOBILE' => $input['MOBILE'], 'STATUS' => 'Active'])->first();

                        DB::table('users')->where(['mobile' => $input['MOBILE']])->update(['LOGIN' => carbon::now()]);
                        if ($data1) {
                            $data = [
                                "PHARMA_ID" => $data1->PHARMA_ID,
                                "PHARMA_NAME" => $data1->ITEM_NAME,
                                "CLINIC_MOBILE" => $data1->CLINIC_MOBILE,
                                "ADDRESS" => $data1->ADDRESS,
                                "CITY" => $data1->CITY,
                                "DIST" => $data1->DIST,
                                "PIN" => $data1->PIN,
                                "STATE" => $data1->STATE,
                                "PHOTO_URL" => $data1->PHOTO_URL,
                                "LOGO_URL" => $data1->LOGO_URL,
                                "PHOTO_64" => base64_encode(file_get_contents($data1->PHOTO_URL)), 
                                "LOGO_64" => base64_encode(file_get_contents($data1->LOGO_URL)), 
                                "CLINIC_TYPE" => $data1->CLINIC_TYPE,
                                "LATITUDE" => $data1->LATITUDE,
                                "LONGITUDE" => $data1->LONGITUDE,
                            ];
                        }

                        $token = base64_encode($request->input('MOBILE') . $user->password . $user->user_type);
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
    function clinicsignup(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //$input = $req->json()->all();
            $input = $req->all();
            if (
                isset($input['CLINIC_TYPE']) && isset($input['PHARMA_NAME']) && isset($input['ADDRESS']) && isset($input['STATE']) && isset($input['DISTRICT']) &&
                isset($input['CITY']) && isset($input['PIN']) && isset($input['CLINIC_MOBILE']) && isset($input['PASSWORD'])
            ) {

                $response = array();

                $clinic_name = $input['PHARMA_NAME'];
                $clinic_type = $input['CLINIC_TYPE'];
                $add = $input['ADDRESS'];
                $state = $input['STATE'];
                $dist = $input['DISTRICT'];
                $city = $input['CITY'];
                $pin = $input['PIN'];
                $email = $input['EMAIL'];
                $lat = $input['LATITUDE'];
                $lon = $input['LONGITUDE'];
                $doc_type = $input['DOC_TYPE'];
                $clinic_mobile = $input['CLINIC_MOBILE'];
                $password = md5($input['PASSWORD']);

                try {
                    if ($req->file('CLINICPHOTO') !== null) {
                        $clinicphoto = "CLINIC_PHOTO_" . $clinic_mobile . "." . $req->file('CLINICPHOTO')->getClientOriginalExtension();
                        $upclinic_photo = $req->file('CLINICPHOTO')->storeAs('clinicprofile/clinicphoto', $clinicphoto);
                        $clinic_photo_url = "http://healthezy.easytechitsolutions.com/healthezyapi/storage/app/clinicprofile/clinicphoto/" . $clinicphoto;
                    } else {
                        $clinic_photo_url = "";
                    }
                    if ($req->file('CLINICDOC') !== null) {
                        $clinicdoc = "CLINIC_DOC_" . $clinic_mobile . "." . $req->file('CLINICDOC')->getClientOriginalExtension();
                        $upclinic_doc = $req->file('CLINICDOC')->storeAs('clinicprofile/clinicdoc', $clinicdoc);
                        $clinic_doc_url = "http://healthezy.easytechitsolutions.com/healthezyapi/storage/app/clinicprofile/clinicdoc/" . $clinicdoc;
                    } else {
                        $clinic_doc_url = "";
                    }
                    $sql1 = "INSERT INTO `users`(`name`, `mobile`,`password`, `user_type`) VALUES ('$clinic_name','$clinic_mobile','$password','Clinic')";
                    DB::insert($sql1);
                } catch (\Throwable $th) {
                    $response = array("Success" => false, "Message" => 'You are already registered', "code" => 200);
                    return $response;
                }
                $sql = "INSERT INTO `pharmacy`(`CLINIC_TYPE`,`PHARMA_NAME`, `ADDRESS`, `CITY`, `DIST`, `PIN`, `EMAIL`, `STATE`, `CLINIC_MOBILE`, `LATITUDE`, `LONGITUDE`, `PHOTO_URL`, `DOC_TYPE`, `DOC_URL`) 
                    VALUES ('$clinic_type','$clinic_name','$add','$city','$dist','$pin','$email','$state','$clinic_mobile','$lat','$lon','$clinic_photo_url','$doc_type','$clinic_doc_url')";
                DB::insert($sql);
                $response = array("Success" => true, "Message" => 'Data insert successfully', "code" => 200);
            } else {
                $response = array("Success" => false, "Message" => "Invalid Parameter", "code" => 422);
            }
        } else {
            $response = array("Success" => false, "Message" => "Method not allowed", "code" => 405);
        }
        return $response;
    }

    function serdr(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $input   = $request->json()->all();
            $headers = apache_request_headers();

            if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {

                if (isset($input['DR_ID'])) {
                    $dr_id = $input['DR_ID'];

                    $response = array();
                    $data = array();

                    $SQL = "SELECT * FROM drprofile where DR_ID='$dr_id'";
                    $data = DB::select($SQL);
                    if ($data != null) {
                        $response = ['Success' => true, 'data' => $data, 'code' => 200];
                    } else {
                        $response = ['Success' => false, 'Message' => 'Invalid DR ID', 'code' => 200];
                    }
                } else {
                    $response = ['Success' => false, 'Message' => 'Invalid Parameter', 'code' => 422];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'You are not Authorized', 'code' => 401];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method not allowed', 'code' => 200];
        }
        return $response;
    }

    function sndreq(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $input   = $request->json()->all();
            $headers = apache_request_headers();

            if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {

                if (isset($input['DR_ID']) && isset($input['PHARMA_ID'])) {
                    $dr_id = $input['DR_ID'];
                    $pharma_id = $input['PHARMA_ID'];
                    $cdt = date('Ymd');
                    $reqid = $dr_id . $pharma_id . $cdt;

                    $response = array();
                    $data = array();

                    // $validator = \Validator::make($request->all(), [
                    //     'name' => 'required',
                    //     'email' => 'required|email',
                    //     'phone'=>'required|min:11|numeric|unique:patients',
                    //     'birth_date' => 'required',
                    //     'gender' => 'required',
                    //     'address' => 'required',
                    // ]);

                    // if ($validator->fails()) {}

                    $SQL = "INSERT INTO `clinic_req`(`REQ_ID`,`PHARMA_ID`, `DR_ID`, `REQ_DT`, `STATUS`) VALUES ('$reqid','$pharma_id','$dr_id','$cdt','Pending')";
                    $data = DB::insert($SQL);

                    $response = ['Success' => true, 'Message' => 'Request send successfully', 'code' => 200];
                } else {
                    $response = ['Success' => false, 'Message' => 'Invalid Parameter', 'code' => 422];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'You are not Authorized', 'code' => 401];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method not allowed', 'code' => 200];
        }
        return $response;
    }

    function attchdr(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $input   = $request->json()->all();
            $headers = apache_request_headers();

            if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {

                if (isset($input['PHARMA_ID'])) {
                    $pharma_id = $input['PHARMA_ID'];

                    $response = array();
                    $data = array();

                    $SQL = "select * from dr_availablity where PHARMA_ID='$pharma_id' and AVAIL_STATUS='ACTIVE'";
                    $data = DB::select($SQL);

                    $response = ['Success' => true, 'data' => $data, 'code' => 200];
                } else {
                    $response = ['Success' => false, 'Message' => 'Invalid Parameter', 'code' => 422];
                }
            } else {
                $response = ['Success' => false, 'Message' => 'You are not Authorized', 'code' => 401];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method not allowed', 'code' => 200];
        }
        return $response;
    }
}
