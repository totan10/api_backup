<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Arr;

class AdminController extends Controller
{
    function admlogin(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            date_default_timezone_set('Asia/Kolkata');
            // session_start();
            $response = array();
            $login = array();

            $input = $request->json()->all();
            if (isset($input['Mobile']) && isset($input['Password'])) {
                $user = DB::table('users')
                    ->where('mobile', '=', $request->input('Mobile'))
                    ->where('user_type', '!=', 'User')
                    ->where('user_type', '!=', 'Clinic')
                    ->first();
                if ($user != null) {
                    if (($user->password) === md5($request->input('Password'))) {
                        $m = $request->input('Mobile');
                        $fname = explode(" ", $user->name);
                        $login = array('mobile' => $m, 'name' => $fname[0], 'currdt' => date('d/m/Y'), 'User_Type' => $user->user_type); //DB::select($SQL);
                        $token = base64_encode($request->input('Mobile') . $user->password . $user->name . $user->user_type);
                        $_SESSION['TOKEN'] = $token;

                        $response = ['Success' => true, "login" => $login, 'Message' => 'Login Successfully', 'User_Type' => $user->user_type, 'Token' => $token, 'status' => 200];
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

    function homescreen()
    {
        $response = array();
        $data = array();
        $data = DB::table('dashboard_header')->get();
        $response = ['Success' => true, 'data' => $data, 'code' => 200];
        return $response;
    }

    function add_slider(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
            $req->file('file')->storeAs('homepage', $fileName);
            $url = asset(storage::url('app/homepage')) . "/" . $fileName;
            $sql1 = "INSERT INTO `slider`(`SECTION_ID`, `SECTION_NAME`, `ITEM_ID`, `ITEM_NAME`, `DESCRIPTION`, `PHOTO_URL`, `STATUS`, `POSITION`) 
            VALUES ('$section','$h_name','$item_id','$item_name','$i_desc','$url','$i_status','$i_position')";
            DB::insert($sql1);
            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_slider(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            if ($req->file('file') !== null) {
                $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('homepage', $fileName);
                $url = asset(storage::url('app/homepage')) . "/" . $fileName;
                $sql1 = "UPDATE `slider` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`PHOTO_URL`='$url',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            } else {
                $sql1 = "UPDATE `slider` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            }
            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_appdash(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();



            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
            $req->file('file')->storeAs('homepage', $fileName);
            $url = asset(storage::url('app/homepage')) . "/" . $fileName;
            $sql1 = "INSERT INTO `appdashboard`(`SECTION_ID`, `SECTION_NAME`, `ITEM_ID`, `ITEM_NAME`, `DESCRIPTION`, `PHOTO_URL`, `STATUS`, `POSITION`) 
            VALUES ('$section','$h_name','$item_id','$item_name','$i_desc','$url','$i_status','$i_position')";
            DB::insert($sql1);
            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }



    function add_bestoffer(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
            $req->file('file')->storeAs('homepage', $fileName);
            $url = asset(storage::url('app/homepage')) . "/" . $fileName;
            $sql1 = "INSERT INTO `best_offer`(`SECTION_ID`, `SECTION_NAME`, `ITEM_ID`, `ITEM_NAME`, `DESCRIPTION`, `PHOTO_URL`, `STATUS`, `POSITION`) 
            VALUES ('$section','$h_name','$item_id','$item_name','$i_desc','$url','$i_status','$i_position')";
            DB::insert($sql1);
            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_bestoffer(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            if ($req->file('file') !== null) {
                $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('homepage', $fileName);
                $url = asset(storage::url('app/homepage')) . "/" . $fileName;
                $sql1 = "UPDATE `best_offer` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`PHOTO_URL`='$url',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            } else {
                $sql1 = "UPDATE `best_offer` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            }
            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_expertcare_little(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
            $req->file('file')->storeAs('homepage', $fileName);
            $url = asset(storage::url('app/homepage')) . "/" . $fileName;
            $sql1 = "INSERT INTO `expart_care_little`(`SECTION_ID`, `SECTION_NAME`, `ITEM_ID`, `ITEM_NAME`, `DESCRIPTION`, `PHOTO_URL`, `STATUS`, `POSITION`) 
            VALUES ('$section','$h_name','$item_id','$item_name','$i_desc','$url','$i_status','$i_position')";
            DB::insert($sql1);
            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_expertcare_little(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            if ($req->file('file') !== null) {
                $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('homepage', $fileName);
                $url = asset(storage::url('app/homepage')) . "/" . $fileName;
                $sql1 = "UPDATE `expart_care_little` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`PHOTO_URL`='$url',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            } else {
                $sql1 = "UPDATE `expart_care_little` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            }
            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_expertcare_woman(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
            $req->file('file')->storeAs('homepage', $fileName);
            $url = asset(storage::url('app/homepage')) . "/" . $fileName;
            $sql1 = "INSERT INTO `expart_care_women`(`SECTION_ID`, `SECTION_NAME`, `ITEM_ID`, `ITEM_NAME`, `DESCRIPTION`, `PHOTO_URL`, `STATUS`, `POSITION`) 
            VALUES ('$section','$h_name','$item_id','$item_name','$i_desc','$url','$i_status','$i_position')";
            DB::insert($sql1);
            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_expertcare_woman(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            if ($req->file('file') !== null) {
                $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('homepage', $fileName);
                $url = asset(storage::url('app/homepage')) . "/" . $fileName;
                $sql1 = "UPDATE `expart_care_women` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`PHOTO_URL`='$url',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            } else {
                $sql1 = "UPDATE `expart_care_women` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            }
            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_health_tool(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
            $req->file('file')->storeAs('homepage', $fileName);
            $url = asset(storage::url('app/homepage')) . "/" . $fileName;
            $sql1 = "INSERT INTO `health_tool`(`SECTION_ID`, `SECTION_NAME`, `ITEM_ID`, `ITEM_NAME`, `DESCRIPTION`, `PHOTO_URL`, `STATUS`, `POSITION`) 
            VALUES ('$section','$h_name','$item_id','$item_name','$i_desc','$url','$i_status','$i_position')";
            DB::insert($sql1);
            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_health_tool(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            if ($req->file('file') !== null) {
                $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('homepage', $fileName);
                $url = asset(storage::url('app/homepage')) . "/" . $fileName;
                $sql1 = "UPDATE `health_tool` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`PHOTO_URL`='$url',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            } else {
                $sql1 = "UPDATE `health_tool` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            }
            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_health_check(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
            $req->file('file')->storeAs('homepage', $fileName);
            $url = asset(storage::url('app/homepage')) . "/" . $fileName;
            $sql1 = "INSERT INTO `health_check`(`SECTION_ID`, `SECTION_NAME`, `ITEM_ID`, `ITEM_NAME`, `DESCRIPTION`, `PHOTO_URL`, `STATUS`, `POSITION`) 
            VALUES ('$section','$h_name','$item_id','$item_name','$i_desc','$url','$i_status','$i_position')";
            DB::insert($sql1);
            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_health_check(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            if ($req->file('file') !== null) {
                $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('homepage', $fileName);
                $url = asset(storage::url('app/homepage')) . "/" . $fileName;
                $sql1 = "UPDATE `health_check` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`PHOTO_URL`='$url',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            } else {
                $sql1 = "UPDATE `health_check` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            }
            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_consult_home(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
            $req->file('file')->storeAs('homepage', $fileName);
            $url = asset(storage::url('app/homepage')) . "/" . $fileName;
            $sql1 = "INSERT INTO `consult_home`(`SECTION_ID`, `SECTION_NAME`, `ITEM_ID`, `ITEM_NAME`, `DESCRIPTION`, `PHOTO_URL`, `STATUS`, `POSITION`) 
            VALUES ('$section','$h_name','$item_id','$item_name','$i_desc','$url','$i_status','$i_position')";
            DB::insert($sql1);
            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_consult_home(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            if ($req->file('file') !== null) {
                $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('homepage', $fileName);
                $url = asset(storage::url('app/homepage')) . "/" . $fileName;
                $sql1 = "UPDATE `consult_home` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`PHOTO_URL`='$url',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            } else {
                $sql1 = "UPDATE `consult_home` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            }
            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_fittrack(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
            $req->file('file')->storeAs('homepage', $fileName);
            $url = asset(storage::url('app/homepage')) . "/" . $fileName;
            $sql1 = "INSERT INTO `fitness_tracker`(`SECTION_ID`, `SECTION_NAME`, `ITEM_ID`, `ITEM_NAME`, `DESCRIPTION`, `PHOTO_URL`, `STATUS`, `POSITION`) 
            VALUES ('$section','$h_name','$item_id','$item_name','$i_desc','$url','$i_status','$i_position')";
            DB::insert($sql1);
            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_fittrack(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            if ($req->file('file') !== null) {
                $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('homepage', $fileName);
                $url = asset(storage::url('app/homepage')) . "/" . $fileName;
                $sql1 = "UPDATE `fitness_tracker` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`PHOTO_URL`='$url',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            } else {
                $sql1 = "UPDATE `fitness_tracker` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            }
            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_spotlight(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
            $req->file('file')->storeAs('homepage', $fileName);
            $url = asset(storage::url('app/homepage')) . "/" . $fileName;

            $sql1 = "
             INTO `spotlight`(`SECTION_ID`, `SECTION_NAME`, `ITEM_ID`, `ITEM_NAME`, `DESCRIPTION`, `PHOTO_URL`, `STATUS`, `POSITION`) 
            VALUES ('$section','$h_name','$item_id','$item_name','$i_desc','$url','$i_status','$i_position')";
            DB::insert($sql1);
            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_spotlight(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();

            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            if ($req->file('file') !== null) {
                $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('homepage', $fileName);
                $url = asset(storage::url('app/homepage')) . "/" . $fileName;

                $sql1 = "UPDATE `spotlight` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`PHOTO_URL`='$url',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            } else {
                $sql1 = "UPDATE `spotlight` SET `ITEM_NAME`='$item_name',`DESCRIPTION`='$i_desc',`STATUS`='$i_status',`POSITION`='$i_position' WHERE ITEM_ID='$item_id'";
                DB::update($sql1);
            }
            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_catg(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();

            $input = $req->all();
            $section = $input['SECTION'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $item_catg = $input['DIS_CATG'];
            $item_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            try {
                $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('homepage', $fileName);
                $url = asset(storage::url('app/homepage')) . "/" . $fileName;

                $sql2 = "INSERT INTO `disease_catg`(`ITEM_ID`,`ITEM_NAME`, `DIS_CATG`, `PHOTO_URL`,`DESCRIPTION`,`STATUS`, `POSITION`) VALUES ('$item_id','$item_name','$item_catg','$url','$item_desc','$i_status','$i_position')";
                DB::insert($sql2);
                $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
            } catch (\Throwable $response) {
                $response = ['Success' => false, 'Message' => 'Disease Category already exists.', 'code' => 405];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_catg(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();
            $section = $input['SECTION'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];
            $item_catg = $input['DIS_CATG'];
            $item_desc = $input['DESCRIPTION'];
            $item_name1 = $input['ITEM_NAME1'];

            if ($req->file('file') !== null) {
                $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('homepage', $fileName);
                $url = asset(storage::url('app/homepage')) . "/" . $fileName;
                $sql1 = "UPDATE `disease_catg` SET `PHOTO_URL`='$url',`STATUS`='$i_status',`DIS_CATG`='$item_catg',`DESCRIPTION`='$item_desc',`ITEM_NAME`='$item_name',`POSITION`='$i_position' WHERE `ITEM_ID`='$item_id'";
                DB::update($sql1);
            } else {
                $sql1 = "UPDATE `disease_catg` SET `STATUS`='$i_status',`DIS_CATG`='$item_catg',`DESCRIPTION`='$item_desc',`ITEM_NAME`='$item_name',`POSITION`='$i_position' WHERE `ITEM_ID`='$item_id'";
                DB::update($sql1);
            }
            $sql2 = "UPDATE `drprofile` SET `D_CATG`='$item_name' WHERE `D_CATG`='$item_name1'";
            DB::update($sql2);

            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    // function add_symp(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $response = array();

    //         $input   = $req->all();
    //         $section = $input['SECTION'];
    //         $dis_id = $input['DIS_ID'];
    //         $item_name = $input['ITEM_NAME'];
    //         $item_catg = $input['DIS_CATG'];
    //         $item_desc = $input['DESCRIPTION'];
    //         $i_status = $input['I_STATUS'];
    //         $i_position = $input['I_POSITION'];

    //         try {
    //             $fileName = $section . $dis_id . "." . $req->file('file')->getClientOriginalExtension();
    //             $req->file('file')->storeAs('homepage', $fileName);
    //             $url = asset(storage::url('app/homepage')) . "/" . $fileName;

    //             $sql2 = "INSERT INTO `symptom`(`DIS_ID`,`ITEM_NAME`, `DIS_CATG`, `PHOTO_URL`,`DESCRIPTION`,`STATUS`, `POSITION`) VALUES ('$dis_id','$item_name','$item_catg','$url','$item_desc','$i_status','$i_position')";
    //             DB::insert($sql2);
    //             $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
    //         } catch (\Throwable $response) {
    //             $response = ['Success' => false, 'Message' => 'Disease Category already exists.', 'code' => 405];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
    //     }
    //     return $response;
    // }

    // function updt_symp(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $response = array();
    //         $input   = $req->all();
    //         $section = $input['SECTION'];
    //         $item_id = $input['ITEM_ID'];
    //         $dis_id = $input['DIS_ID'];
    //         $item_name = $input['ITEM_NAME'];
    //         $i_status = $input['I_STATUS'];
    //         $i_position = $input['I_POSITION'];
    //         $item_catg = $input['DIS_CATG'];
    //         $item_desc = $input['DESCRIPTION'];
    //         $item_name1 = $input['ITEM_NAME1'];

    //         if ($req->file('file') !== null) {
    //             $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
    //             $req->file('file')->storeAs('homepage', $fileName);
    //             $url = asset(storage::url('app/homepage')) . "/" . $fileName;
    //             $sql1 = "UPDATE `symptom` SET `PHOTO_URL`='$url',`STATUS`='$i_status',`DIS_ID`='$dis_id',`DIS_CATG`='$item_catg',`DESCRIPTION`='$item_desc',`ITEM_NAME`='$item_name',`POSITION`='$i_position' WHERE `ITEM_ID`='$item_id'";
    //             DB::update($sql1);
    //         } else {
    //             $sql1 = "UPDATE `symptom` SET `STATUS`='$i_status',`DIS_ID`='$dis_id',`DIS_CATG`='$item_catg',`DESCRIPTION`='$item_desc',`ITEM_NAME`='$item_name',`POSITION`='$i_position' WHERE `ITEM_ID`='$item_id'";
    //             DB::update($sql1);
    //         }
    //         $sql2 = "UPDATE `drprofile` SET `D_CATG`='$item_name' WHERE `D_CATG`='$item_name1'";
    //         DB::update($sql2);

    //         $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
    //     }
    //     return $response;
    // }

    // function add_surgery(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $response = array();

    //         $input   = $req->all();
    //         $section = $input['SECTION'];
    //         $dis_id = $input['DIS_ID'];
    //         $item_name = $input['ITEM_NAME'];
    //         $item_catg = $input['DIS_CATG'];
    //         $item_desc = $input['DESCRIPTION'];
    //         $i_status = $input['I_STATUS'];
    //         $i_position = $input['I_POSITION'];

    //         try {
    //             $fileName = $section . $dis_id . "." . $req->file('file')->getClientOriginalExtension();
    //             $req->file('file')->storeAs('homepage', $fileName);
    //             $url = asset(storage::url('app/homepage')) . "/" . $fileName;

    //             $sql2 = "INSERT INTO `surgeries`(`DIS_ID`,`SECTION_ID`,`ITEM_NAME`, `DIS_CATG`, `PHOTO_URL`,`DESCRIPTION`,`STATUS`, `POSITION`) VALUES ('$dis_id','$section','$item_name','$item_catg','$url','$item_desc','$i_status','$i_position')";
    //             DB::insert($sql2);
    //             $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
    //         } catch (\Throwable $response) {
    //             $response = ['Success' => false, 'Message' => 'Disease Category already exists.', 'code' => 405];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
    //     }
    //     return $response;
    // }

    // function updt_surgery(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $response = array();
    //         $input   = $req->all();

    //         $section = $input['SECTION'];
    //         $item_id = $input['ITEM_ID'];
    //         $dis_id = $input['DIS_ID'];
    //         $item_name = $input['ITEM_NAME'];
    //         $i_status = $input['I_STATUS'];
    //         $i_position = $input['I_POSITION'];
    //         $item_catg = $input['DIS_CATG'];
    //         $item_desc = $input['DESCRIPTION'];
    //         $item_name1 = $input['ITEM_NAME1'];

    //         if ($req->file('file') !== null) {
    //             $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
    //             $req->file('file')->storeAs('homepage', $fileName);
    //             $url = asset(storage::url('app/homepage')) . "/" . $fileName;
    //             $sql1 = "UPDATE `surgeries` SET `PHOTO_URL`='$url',`STATUS`='$i_status',`DIS_ID`='$dis_id',`DIS_CATG`='$item_catg',`DESCRIPTION`='$item_desc',`ITEM_NAME`='$item_name',`POSITION`='$i_position' WHERE `ITEM_ID`='$item_id'";
    //             DB::update($sql1);
    //         } else {
    //             $sql1 = "UPDATE `surgeries` SET `STATUS`='$i_status',`DIS_ID`='$dis_id',`DIS_CATG`='$item_catg',`DESCRIPTION`='$item_desc',`ITEM_NAME`='$item_name',`POSITION`='$i_position' WHERE `ITEM_ID`='$item_id'";
    //             DB::update($sql1);
    //         }
    //         $sql2 = "UPDATE `drprofile` SET `D_CATG`='$item_name' WHERE `D_CATG`='$item_name1'";
    //         DB::update($sql2);
    //         $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
    //         // } else {
    //         //     $response = ['Success' => false, 'Message' => 'Invalid parameter.', 'code' => 200];
    //         // }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
    //     }
    //     return $response;
    // }

    function add_video_clip(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();

            $input = $req->all();
            $section = $input['SECTION'];
            $dis_id = $input['DIS_ID'];
            $item_name = $input['ITEM_NAME'];
            $item_catg = $input['DIS_CATG'];
            $item_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            $fileName = $section . $dis_id . "." . $req->file('file')->getClientOriginalExtension();
            $req->file('file')->storeAs('homepage', $fileName);
            $url = asset(storage::url('app/homepage')) . "/" . $fileName;

            $sql2 = "INSERT INTO `video_consult`(`DIS_ID`,`ITEM_NAME`, `DIS_CATG`, `PHOTO_URL`,`DESCRIPTION`,`STATUS`, `POSITION`) VALUES ('$dis_id','$item_name','$item_catg','$url','$item_desc','$i_status','$i_position')";
            DB::insert($sql2);
            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_video_clip(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();
            $section = $input['SECTION'];
            $item_id = $input['ITEM_ID'];
            $dis_id = $input['DIS_ID'];
            $item_name = $input['ITEM_NAME'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];
            $item_catg = $input['DIS_CATG'];
            $item_desc = $input['DESCRIPTION'];
            $item_name1 = $input['ITEM_NAME1'];

            if ($req->file('file') !== null) {
                $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('homepage', $fileName);
                $url = asset(storage::url('app/homepage')) . "/" . $fileName;
                $sql1 = "UPDATE `video_consult` SET `PHOTO_URL`='$url',`STATUS`='$i_status',`DIS_ID`='$dis_id',`DIS_CATG`='$item_catg',`DESCRIPTION`='$item_desc',`ITEM_NAME`='$item_name',`POSITION`='$i_position' WHERE `ITEM_ID`='$item_id'";
                DB::update($sql1);
            } else {
                $sql1 = "UPDATE `video_consult` SET `STATUS`='$i_status',`DIS_ID`='$dis_id',`DIS_CATG`='$item_catg',`DESCRIPTION`='$item_desc',`ITEM_NAME`='$item_name',`POSITION`='$i_position' WHERE `ITEM_ID`='$item_id'";
                DB::update($sql1);
            }
            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_caption(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();

            $input = $req->all();
            $header_id = $input['HEADER_ID'];
            $h_name = $input['HEADER_NAME'];
            $h_desc = $input['HEADER_DESCRIPTION'];
            $h_status = $input['H_STATUS'];

            $sql1 = "UPDATE `dashboard_header` SET `NAME`='$h_name',`DESCRIPTION`='$h_desc',`STATUS`='$h_status' WHERE `ID`='$header_id'";
            DB::update($sql1);

            $response = ['Success' => true, 'Message' => 'Record update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_promo(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();

            $input = $req->all();
            $cl_id = $input['ITEM_ID'];
            $i_promo = $input['PROMO_DT'];
            $i_status = $input['PROMO_STS'];

            if ($i_status !== "Deactive") {
                $sql1 = "UPDATE `pharmacy` SET `VALID_DT`='$i_promo',`STATUS`='$i_status' WHERE `PHARMA_ID`='$cl_id'";
            } else {
                $sql1 = "UPDATE `pharmacy` SET `STATUS`='$i_status' WHERE `PHARMA_ID`='$cl_id'";
            }
            DB::update($sql1);

            $response = ['Success' => true, 'Message' => 'Record update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_header(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();

            $input = $req->all();
            $header_id = $input['HEADER_ID'];
            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $h_desc = $input['HEADER_DESCRIPTION'];
            $h_status = $input['H_STATUS'];

            $sql1 = "INSERT INTO `dashboard_header`(`ID`, `SECTION`, `NAME`, `DESCRIPTION`, `STATUS`) VALUES ('$header_id','$section','$h_name','$h_desc','$h_status')";
            DB::insert($sql1);
            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function delssn(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();

            $input = $req->all();
            $header_id = $input['HEADER_ID'];
            $item_id = $input['ITEM_ID'];
            $url = $input['PHOTO_URL'];
            $filePath = storage::url('app/homepage') . '/C1.jpg';
            if (File::exists($filePath)) {
                DB::delete('dashboard_details')
                    ->where('SECTION_ID', $header_id)
                    ->where('SUB_SL', $item_id);

                File::delete($filePath);
                $response = ['Success' => true, 'Message' => 'Record successfully deleted.', 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'File not found.', 'code' => 404];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function getdata()
    {
        $admin = Auth::user()->is_admin;
        if ($admin == 1) {
            $data = User::paginate(10);
        } else {
            $data = User::where('id', Auth::user()->id)->paginate(10);
        }

        return view('admin.user_list', ['user1' => $data]);
    }
    function edit($id)
    {
        $user = User::find($id);
        return view('admin.edit_user', compact('user'));
    }
    function add(request $req)
    {
        $user = new User;
        $user->name = $req->name;
        $user->email = $req->email;

        if (!empty($req->password)) {
            $user->password = Hash::make($req->password);
        }
        $user->save();
        Session::flash('message', 'Successfully Created a new Author.');
        return redirect()->back();
        return view('admin.add_user');
    }

    function update(request $req)
    {
        $user = User::find($req->id);
        $user->name = $req->name;
        $user->email = $req->email;

        if (!empty($req->password)) {
            $user->password = Hash::make($req->password);
        }
        $user->save();
        Session::flash('message', 'Successfully updated.');
        return redirect()->back();
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

    function testsrch(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMA_ID'])) {
                $PID = $input['PHARMA_ID'];
                $response = array();
                $data = array();

                $added = DB::table('clinic_testdata')
                    // ->select(
                    //     'TEST_ID',
                    //     'SCH_DAY',
                    //     'TEST_NAME',
                    //     'TEST_CODE',
                    //     'TEST_SAMPLE',
                    //     'TEST_CATG',
                    //     'ORGAN_ID',
                    //     'ORGAN_NAME',
                    //     'ORGAN_URL',
                    //     'CATEGORY',
                    //     'TEST_UNIT',
                    //     'NORMAL_RANGE',
                    //     'TEST_DESC',
                    //     'KNOWN_AS',
                    //     'FASTING',
                    //     'GENDER_TYPE',
                    //     'AGE_TYPE',
                    //     'REPORT_TIME',
                    //     'PRESCRIPTION',
                    //     'ID_PROOF',
                    //     'QA1',
                    //     'QA2',
                    //     'QA3',
                    //     'QA4',
                    //     'QA5',
                    //     'QA6',
                    //     'REMARK'
                    // )
                    ->where(["REMARK" => 'Added', 'PHARMA_ID' => $PID])
                    ->get();
                $testIds = array_column($added->toArray(), 'TEST_ID');
                $notadded = DB::table('master_testdata')
                    // ->select('TEST_ID',
                    //     'TEST_NAME',
                    //     'TEST_CODE',
                    //     'TEST_SAMPLE',
                    //     'TEST_CATG',
                    //     'ORGAN_ID',
                    //     'ORGAN_NAME',
                    //     'ORGAN_URL',
                    //     'CATEGORY',
                    //     'TEST_UNIT',
                    //     'NORMAL_RANGE',
                    //     'TEST_DESC',
                    //     'KNOWN_AS',
                    //     'FASTING',
                    //     'GENDER_TYPE',
                    //     'AGE_TYPE',
                    //     'REPORT_TIME',
                    //     'PRESCRIPTION',
                    //     'ID_PROOF',
                    //     'QA1',
                    //     'QA2',
                    //     'QA3',
                    //     'QA4',
                    //     'QA5',
                    //     'QA6',
                    //     'REMARK'
                    // )
                    ->whereNotIn('TEST_ID', $testIds)
                    ->get();
                $data = $added->merge($notadded);

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
                    'HO_CODE' => $row['HO_CODE'],
                    'TEST_ID' => $row['TEST_ID'],
                    'TEST_UC' => $row['PHARMA_ID'] . $row['TEST_ID'],
                    //'TEST_SL' => $row['TEST_SL'],
                    'TEST_NAME' => $row['TEST_NAME'],
                    'TEST_CODE' => $row['TEST_CODE'],
                    'TEST_SAMPLE' => $row['TEST_SAMPLE'],
                    'TEST_CATG' => $row['TEST_CATG'],
                    'ORGAN_ID' => $row['ORGAN_ID'],
                    'ORGAN_NAME' => $row['ORGAN_NAME'],
                    'ORGAN_URL' => $row['ORGAN_URL'],
                    'CATEGORY' => $row['CATEGORY'],
                    'TEST_UNIT' => $row['TEST_UNIT'],
                    'NORMAL_RANGE' => $row['NORMAL_RANGE'],
                    'TEST_DESC' => $row['TEST_DESC'],
                    'KNOWN_AS' => $row['KNOWN_AS'],
                    'FASTING' => $row['FASTING'],
                    'GENDER_TYPE' => $row['GENDER_TYPE'],
                    'AGE_TYPE' => $row['AGE_TYPE'],
                    'REPORT_TIME' => $row['REPORT_TIME'],
                    'PRESCRIPTION' => $row['PRESCRIPTION'],
                    'ID_PROOF' => $row['ID_PROOF'],
                    'QA1' => $row['QA1'],
                    'QA2' => $row['QA2'],
                    'QA3' => $row['QA3'],
                    'QA4' => $row['QA4'],
                    'QA5' => $row['QA5'],
                    'QA6' => $row['QA6'],
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
                'FASTING' => $input['FASTING'],
                'GENDER_TYPE' => $input['GENDER_TYPE'],
                'AGE_TYPE' => $input['AGE_TYPE'],
                'REPORT_TIME' => $input['REPORT_TIME'],
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

                $notadded = DB::table('clinic_testdata')
                    ->select('TEST_ID', 'TEST_UC', 'TEST_NAME', 'TEST_TYPE', 'COST', 'PKG_RMK AS REMARK', DB::raw('COUNT(TEST_ID) as TOT_TEST'))
                    ->groupBy('TEST_ID', 'TEST_UC', 'TEST_NAME', 'TEST_TYPE', 'COST', 'PKG_RMK')
                    ->where('PHARMA_ID', $P_ID)
                    ->whereNotIn('TEST_ID', $testIds)
                    ->get();

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

            $input = $req->json()->all();
            $rmv_arr = $input['RMV_DATA'] ?? [];
            $pkg_arr = $input['PKG_DATA'] ?? [];
            $tst_arr = $input['ADD_DATA'] ?? [];
            $edt_arr = $input['EDT_DATA'] ?? [];

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
                        // "CLINIC_SECTION" => $row['CLINIC_SECTION'],
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
                    $PHARMA_ID = $row2['PHARMA_ID'];
                    $PKG_ID = $row2['PKG_ID'];
                    $PKG_COST = $row2['PKG_COST'];
                    $PKG_DIS = $row2['PKG_DIS'];
                    $PKG_NAME = $row2['PKG_NAME'];
                    $HOME_COLLECT = $row2['HOME_COLLECT'];
                    $STATUS = $row2['STATUS'];
                    DB::table('package')
                        ->where('PKG_ID', $PKG_ID)
                        ->update([
                            'PKG_NAME_UC' => $PHARMA_ID . $PKG_NAME,
                            'PKG_NAME' => $PKG_NAME,
                            'PKG_COST' => $PKG_COST,
                            'PKG_DIS' => $PKG_DIS,
                            'HOME_COLLECT' => $HOME_COLLECT,
                            'STATUS' => $STATUS,
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

    function booktest(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //         Log::info('Request received:', $req->all());
            //         $arrayData = $req->input['BOOK_TEST'];
            //         Log::info('Array data:', $arrayData);
            //     }
            // }
            date_default_timezone_set('Asia/Kolkata');
            $cdt = Carbon::now()->format('ymdHis');

            $input = $req->json()->all();

            $BT = $input['BOOK_TEST'];
            $patientIds = [];
            $bookid = null;

            foreach ($BT as $row) {
                foreach ($row['DETAILS'] as $detail) {
                    $token = null;
                    $token = strtoupper(substr(md5($detail['PATIENT_ID'] . $cdt . $row['PHARMA_ID']), 0, 10));
                    if (isset($detail['PKG_TYPE'])) {
                        $fields = [
                            "BOOKING_ID" => $token,
                            "PKG_ID" => $detail['PKG_ID'],
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
                    }
                    DB::table('booktest')->insert($fields);
                }
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
                try {
                    $response = [
                        'Success' => true,
                        'Message' => 'Test booking successfully.',
                        'BOOK_ID' => $bookid,
                        'code' => 200
                    ];
                } catch (\Throwable $th) {
                    $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
                }
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }

    function pkg_dtls1(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['ITEM_TYPE']) && isset($input['PHARMA_ID'])) {

                $test_catg = $input['ITEM_TYPE'];
                $p_id = $input['PHARMA_ID'];

                $data = array();

                $data1 = DB::table('l_dashboard_details')
                    ->leftjoin('dc_dashboard_header', function ($join) {
                        $join->on('dc_dashboard_header.LAB_SECTION', '=', 'l_dashboard_details.SECTION_ID');
                    })
                    ->leftJoin('package', function ($join) use ($p_id) {
                        $join->on('l_dashboard_details.ID', '=', 'package.LAB_PKG_ID')
                            ->where('package.PHARMA_ID', '=', $p_id)
                            ->orderby('package.PKG_ID');
                    })
                    ->leftJoin('package_details', function ($join) use ($p_id) {
                        $join->on('package.PKG_ID', '=', 'package_details.PKG_ID')
                            ->where('package_details.PHARMA_ID', '=', $p_id)
                            ->orderby('package_details.PKG_ID');
                    })
                    ->select(
                        'l_dashboard_details.ID AS LAB_PKG_ID',
                        'l_dashboard_details.ITEM_NAME',
                        'l_dashboard_details.SECTION_NAME',
                        'l_dashboard_details.DESCRIPTION AS PKG_DESC',
                        'l_dashboard_details.PHOTO_URL AS PKG_URL',
                        'l_dashboard_details.AGE_TYPE',
                        'dc_dashboard_header.ID AS CLINIC_SECTION',
                        'l_dashboard_details.FASTING',
                        'l_dashboard_details.GENDER_TYPE',
                        'package.HO_CODE',
                        'l_dashboard_details.ID_PROOF',
                        'l_dashboard_details.KNOWN_AS',
                        'dc_dashboard_header.LAB_SECTION',
                        'l_dashboard_details.PRESCRIPTION',
                        'l_dashboard_details.QA1',
                        'l_dashboard_details.QA2',
                        'l_dashboard_details.QA3',
                        'l_dashboard_details.QA4',
                        'l_dashboard_details.QA5',
                        'l_dashboard_details.QA6',
                        'l_dashboard_details.REPORT_TIME',
                        'l_dashboard_details.ITEM_TYPE AS PKG_TYPE',
                        'package.PHARMA_ID',
                        'package.PKG_COST',
                        'package.PKG_DIS',
                        'package.PKG_ID',
                        'package.PKG_NAME',
                        'package.HOME_COLLECT',
                        'package.STATUS',
                        'package_details.TEST_ID',
                        'package_details.TEST_UC',
                        'package_details.TEST_NAME',
                        'package_details.TEST_TYPE',
                        'package_details.COST',
                        'package_details.PKG_STATUS',
                    )
                    ->where(['l_dashboard_details.ITEM_TYPE' => $test_catg])
                    ->orderby('l_dashboard_details.ID')
                    ->get();
                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->LAB_PKG_ID])) {
                        $groupedData[$row->LAB_PKG_ID] = [
                            "LAB_PKG_ID" => $row->LAB_PKG_ID,
                            "LAB_PKG_NAME" => $row->ITEM_NAME,
                            "PKG_DESC" => $row->PKG_DESC,
                            "PKG_URL" => $row->PKG_URL,
                            "AGE_TYPE" => $row->AGE_TYPE,
                            "CLINIC_SECTION" => $row->CLINIC_SECTION,
                            "FASTING" => $row->FASTING,
                            "GENDER_TYPE" => $row->GENDER_TYPE,
                            "ID_PROOF" => $row->ID_PROOF,
                            "KNOWN_AS" => $row->KNOWN_AS,
                            "LAB_SECTION" => $row->LAB_SECTION,
                            "DASH_SEC_NAME" => $row->SECTION_NAME,
                            "PRESCRIPTION" => $row->PRESCRIPTION,
                            "QA1" => $row->QA1,
                            "QA2" => $row->QA2,
                            "QA3" => $row->QA3,
                            "QA4" => $row->QA4,
                            "QA5" => $row->QA5,
                            "QA6" => $row->QA6,
                            "REPORT_TIME" => $row->REPORT_TIME,
                            "PKG_TYPE" => $row->PKG_TYPE,
                            "PKG_DETAILS" => []
                        ];
                    }
                    if (!isset($groupedData[$row->LAB_PKG_ID]['PKG_DETAILS'][$row->PKG_ID])) {
                        $groupedData[$row->LAB_PKG_ID]['PKG_DETAILS'][$row->PKG_ID] = [
                            "LAB_PKG_ID" => $row->LAB_PKG_ID,
                            "LAB_PKG_NAME" => $row->ITEM_NAME,
                            "PKG_DESC" => $row->PKG_DESC,
                            "PKG_URL" => $row->PKG_URL,
                            "AGE_TYPE" => $row->AGE_TYPE,
                            "CLINIC_SECTION" => $row->CLINIC_SECTION,
                            "FASTING" => $row->FASTING,
                            "GENDER_TYPE" => $row->GENDER_TYPE,
                            "HOME_COLLECT" => $row->HOME_COLLECT,
                            "HO_CODE" => $row->HO_CODE,
                            "ID_PROOF" => $row->ID_PROOF,
                            "KNOWN_AS" => $row->KNOWN_AS,
                            "LAB_SECTION" => $row->LAB_SECTION,
                            "PHARMA_ID" => $row->PHARMA_ID,
                            "PKG_COST" => $row->PKG_COST,
                            "PKG_DIS" => $row->PKG_DIS,
                            "PKG_ID" => $row->PKG_ID,
                            "PKG_NAME" => $row->PKG_NAME,
                            "PKG_TYPE" => $row->PKG_TYPE,
                            "PRESCRIPTION" => $row->PRESCRIPTION,
                            "QA1" => $row->QA1,
                            "QA2" => $row->QA2,
                            "QA3" => $row->QA3,
                            "QA4" => $row->QA4,
                            "QA5" => $row->QA5,
                            "QA6" => $row->QA6,
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
                            if (empty($groupedData1)) {
                                $groupedData1 = [
                                    "ID" => $row2->ID,
                                    "PHARMA_ID" => $row2->PHARMA_ID,
                                    "HO_CODE" => $row2->HO_CODE,
                                    "PKG_ID" => $row2->PKG_ID,
                                    "TEST_UC" => $row->TEST_UC,
                                    "PKG_SL" => $row2->PKG_SL,
                                    "LAB_PKG_ID" => $row2->LAB_PKG_ID,
                                    "LAB_PKG_NAME" => $row2->LAB_PKG_NAME,
                                    "LAB_SECTION" => $row2->LAB_SECTION,
                                    "CLINIC_SECTION" => $row2->CLINIC_SECTION,
                                    "DASHBOARD_SECTION_NAME" => $row2->DASHBOARD_SECTION_NAME,
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
                                    "PROMO_TYPE" => $row2->PROMO_TYPE,
                                    "PROMO_VALID" => $row2->PROMO_VALID,
                                    "PROMO_URL" => $row2->PROMO_URL,
                                    "PROMO_REQUEST" => $row2->PROMO_REQUEST,
                                    "NOTIFICATION" => $row2->NOTIFICATION,
                                    "PROMO_APPROVE" => $row2->PROMO_APPROVE,
                                    "PROMO_STATUS" => $row2->PROMO_STATUS,
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

                        $groupedData[$row->LAB_PKG_ID]['PKG_DETAILS'][$row->PKG_ID]['TEST_DETAILS']['PROFILE_DETAILS'][$row->TEST_ID] = $groupedData1;
                    } else {
                        $groupedData[$row->LAB_PKG_ID]['PKG_DETAILS'][$row->PKG_ID]['TEST_DETAILS'][] = [
                            "TEST_ID" => $row->TEST_ID,
                            "TEST_UC" => $row->TEST_UC,
                            "TEST_NAME" => $row->TEST_NAME,
                            "TEST_TYPE" => $row->TEST_TYPE,
                            "COST" => $row->COST,
                        ];
                    }
                    foreach ($groupedData as $pkgId => &$package) {
                        $package['TOT_PACKAGE'] = count($package['PKG_DETAILS']);
                        foreach ($package['PKG_DETAILS'] as $detailId => &$detail) {
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

                $data1 = DB::table('dashboard')
                    // ->leftjoin('dc_dashboard_header', function ($join) {
                    //     $join->on('dc_dashboard_header.LAB_SECTION', '=', 'l_dashboard_details.SECTION_ID');
                    // })
                    ->leftJoin('package', function ($join) use ($p_id) {
                        $join->on('dashboard.DASH_ID', '=', 'package.LAB_PKG_ID')
                            ->where('package.PHARMA_ID', '=', $p_id)
                            ->orderby('package.PKG_ID');
                    })
                    ->leftJoin('package_details', function ($join) use ($p_id) {
                        $join->on('package.PKG_ID', '=', 'package_details.PKG_ID')
                            ->where('package_details.PHARMA_ID', '=', $p_id)
                            ->orderby('package_details.PKG_ID');
                    })
                    ->select(
                        'dashboard.DASH_ID',
                        'dashboard.DASH_SECTION_ID',
                        'dashboard.DASH_NAME',
                        'dashboard.DASH_SECTION_NAME',
                        'dashboard.DASH_DESCRIPTION',
                        'dashboard.PHOTO_URL',
                        'dashboard.AGE_TYPE',
                        'dashboard.FASTING',
                        'dashboard.GENDER_TYPE',
                        'package.HO_ID',
                        'dashboard.ID_PROOF',
                        'dashboard.KNOWN_AS',
                        'dashboard.PRESCRIPTION',
                        'dashboard.QA1',
                        'dashboard.QA2',
                        'dashboard.QA3',
                        'dashboard.QA4',
                        'dashboard.QA5',
                        'dashboard.QA6',
                        'dashboard.REPORT_TIME',
                        'dashboard.DASH_TYPE',
                        'package.PHARMA_ID',
                        'package.PKG_COST',
                        'package.PKG_DIS',
                        'package.PKG_ID',
                        'package.PKG_NAME',
                        'package.HOME_COLLECT',
                        'package.STATUS',
                        'package_details.TEST_ID',
                        'package_details.TEST_UC',
                        'package_details.TEST_NAME',
                        'package_details.TEST_TYPE',
                        'package_details.COST',
                        'package_details.PKG_STATUS',
                    )
                    ->where(['dashboard.DASH_TYPE' => $test_catg])
                    ->orderby('dashboard.DASH_ID')
                    ->get();
                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->DASH_ID])) {
                        $groupedData[$row->DASH_ID] = [
                            "LAB_PKG_ID" => $row->DASH_ID,
                            "LAB_PKG_NAME" => $row->DASH_NAME,
                            "PKG_DESC" => $row->DASH_DESCRIPTION,
                            "PKG_URL" => $row->PHOTO_URL,
                            "AGE_TYPE" => $row->AGE_TYPE,
                            "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                            "FASTING" => $row->FASTING,
                            "GENDER_TYPE" => $row->GENDER_TYPE,
                            "ID_PROOF" => $row->ID_PROOF,
                            "KNOWN_AS" => $row->KNOWN_AS,
                            // "LAB_SECTION" => $row->LAB_SECTION,
                            "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                            "PRESCRIPTION" => $row->PRESCRIPTION,
                            "QA1" => $row->QA1,
                            "QA2" => $row->QA2,
                            "QA3" => $row->QA3,
                            "QA4" => $row->QA4,
                            "QA5" => $row->QA5,
                            "QA6" => $row->QA6,
                            "REPORT_TIME" => $row->REPORT_TIME,
                            "PKG_TYPE" => $row->DASH_TYPE,
                            "PKG_DETAILS" => []
                        ];
                    }
                    if (!isset($groupedData[$row->DASH_ID]['PKG_DETAILS'][$row->PKG_ID])) {
                        $groupedData[$row->DASH_ID]['PKG_DETAILS'][$row->PKG_ID] = [
                            "LAB_PKG_ID" => $row->DASH_ID,
                            "LAB_PKG_NAME" => $row->DASH_NAME,
                            "PKG_DESC" => $row->DASH_DESCRIPTION,
                            "PKG_URL" => $row->PHOTO_URL,
                            "AGE_TYPE" => $row->AGE_TYPE,
                            "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                            "FASTING" => $row->FASTING,
                            "GENDER_TYPE" => $row->GENDER_TYPE,
                            "HOME_COLLECT" => $row->HOME_COLLECT,
                            "HO_ID" => $row->HO_ID,
                            "ID_PROOF" => $row->ID_PROOF,
                            "KNOWN_AS" => $row->KNOWN_AS,
                            // "LAB_SECTION" => $row->LAB_SECTION,
                            "PHARMA_ID" => $row->PHARMA_ID,
                            "PKG_COST" => $row->PKG_COST,
                            "PKG_DIS" => $row->PKG_DIS,
                            "PKG_ID" => $row->PKG_ID,
                            "PKG_NAME" => $row->PKG_NAME,
                            "PKG_TYPE" => $row->DASH_TYPE,
                            "PRESCRIPTION" => $row->PRESCRIPTION,
                            "QA1" => $row->QA1,
                            "QA2" => $row->QA2,
                            "QA3" => $row->QA3,
                            "QA4" => $row->QA4,
                            "QA5" => $row->QA5,
                            "QA6" => $row->QA6,
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

                        $groupedData[$row->DASH_ID]['PKG_DETAILS'][$row->PKG_ID]['TEST_DETAILS']['PROFILE_DETAILS'][$row->TEST_ID] = $groupedData1;
                    } else {
                        $groupedData[$row->DASH_ID]['PKG_DETAILS'][$row->PKG_ID]['TEST_DETAILS'][] = [
                            "TEST_ID" => $row->TEST_ID,
                            "TEST_UC" => $row->TEST_UC,
                            "TEST_NAME" => $row->TEST_NAME,
                            "TEST_TYPE" => $row->TEST_TYPE,
                            "COST" => $row->COST,
                        ];
                    }
                    foreach ($groupedData as $pkgId => &$package) {
                        $package['TOT_PACKAGE'] = count($package['PKG_DETAILS']);
                        foreach ($package['PKG_DETAILS'] as $detailId => &$detail) {
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
                "STAFF_ADDRESS" => $row['STAFF_ADDRESS'],
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

    // function viewdoctors(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $input = $req->json()->all();
    //         if (isset($input['PHARMA_ID'])) {

    //             $p_id = $input['PHARMA_ID'];
    //             $data = array();

    //             $data = DB::table('disease_catg')
    //                 ->join('drprofile', 'drprofile.DIS_ID', '=', 'disease_catg.DIS_ID')
    //                 ->join('dr_availablity', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
    //                 ->select(
    //                     'drprofile.DR_ID',
    //                     'drprofile.DR_NAME',
    //                     'drprofile.DR_MOBILE',
    //                     'drprofile.SEX',
    //                     'drprofile.DESIGNATION',
    //                     'drprofile.QUALIFICATION',
    //                     'drprofile.D_CATG',
    //                     'drprofile.EXPERIENCE',
    //                     'drprofile.LANGUAGE',
    //                     'drprofile.PHOTO_URL AS DR_PHOTO',
    //                     'dr_availablity.DR_FEES',
    //                 )
    //                 ->where(['dr_availablity.PHARMA_ID' => $p_id])->get();

    //             $response = ['Success' => true, 'data' => $data, 'code' => 200];
    //         } else {
    //             $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
    //     }
    //     return $response;
    // }

    // function addsch(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $input = $req->json()->all();
    //         $td = $input['ADD_SCH'];

    //         foreach ($td as $row) {
    //             $fields = [
    //                 'SCH_ID' => strtoupper(substr(md5($row['DR_ID'] . $row['PHARMA_ID'] . $row['SCH_DAY']), 0, 15)),
    //                 'DR_ID' => $row['DR_ID'],
    //                 'DIS_ID' => $row['DIS_ID'],
    //                 'DR_FEES' => $row['DR_FEES'],
    //                 'PHARMA_ID' => $row['PHARMA_ID'],
    //                 'PHARMA_NAME' => $row['PHARMA_NAME'],
    //                 'SCH_DAY' => $row['SCH_DAY'],
    //                 'SCH_DT' => $row['SCH_DT'] ?? null,
    //                 'WEEK' => $row['WEEK'],
    //                 'MONTH' => $row['MONTH'] ?? null,
    //                 'START_MONTH' => $row['START_MONTH'],
    //                 'SCH_STATUS' => $row['SCH_STATUS'],
    //                 'BOOK_ST_DT' => $row['BOOK_ST_DT'],
    //                 'BOOK_ST_TM' => $row['BOOK_ST_TM'],
    //                 'AVAIL_STATUS' => 'Active',
    //                 'CHK_IN_TIME' => $row['CHK_IN_TIME'],
    //                 'CHK_OUT_TIME' => $row['CHK_OUT_TIME'] ?? null,
    //                 'CHK_IN_TIME1' => $row['CHK_IN_TIME1'] ?? null,
    //                 'CHK_OUT_TIME1' => $row['CHK_OUT_TIME1'] ?? null,
    //                 'CHK_IN_TIME2' => $row['CHK_IN_TIME2'] ?? null,
    //                 'CHK_OUT_TIME2' => $row['CHK_OUT_TIME2'] ?? null,
    //                 'CHK_IN_TIME3' => $row['CHK_IN_TIME3'] ?? null,
    //                 'CHK_OUT_TIME3' => $row['CHK_OUT_TIME3'] ?? null,
    //                 'SLOT_INTVL' => $row['SLOT_INTVL'] ?? 0,
    //                 'SLOT_APPNT' => $row['SLOT_APPNT'] ?? 0,
    //                 'MAX_BOOK' => $row['MAX_BOOK'] ?? null,
    //                 'MAX_BOOK1' => $row['MAX_BOOK1'] ?? null,
    //                 'MAX_BOOK2' => $row['MAX_BOOK2'] ?? null,
    //                 'MAX_BOOK3' => $row['MAX_BOOK3'] ?? null,
    //                 'SLOT' => $row['SLOT_TYPE'] ?? null,
    //             ];
    //             for ($i = 0; $i < 4; $i++) {
    //                 $maxBookCol = 'MAX_BOOK' . ($i === 0 ? '' : $i);
    //                 $chkInTimeCol = 'CHK_IN_TIME' . ($i === 0 ? '' : $i);
    //                 $chkOutTimeCol = 'CHK_OUT_TIME' . ($i === 0 ? '' : $i);

    //                 if ($row[$maxBookCol] === null && $row[$chkInTimeCol] != null && $row[$chkOutTimeCol] != null) {
    //                     $chkinTime = Carbon::createFromFormat('h:i A', $row[$chkInTimeCol]);
    //                     $chkoutTime = Carbon::createFromFormat('h:i A', $row[$chkOutTimeCol]);
    //                     $minutesDiff = $chkinTime->diffInMinutes($chkoutTime, false);
    //                     $maxbook = ($minutesDiff / $row['SLOT_INTVL']) * $row['SLOT_APPNT'];
    //                     $fields[$maxBookCol] = $maxbook;
    //                     //break;
    //                 } else {
    //                     $fields[$maxBookCol] = $row[$maxBookCol];
    //                 }
    //             }

    //             // try {                    
    //             DB::table('dr_availablity')->insert($fields);
    //             DB::table('dr_availablity')->where(['SCH_ID' => strtoupper(substr(md5($row['DR_ID'] . $row['PHARMA_ID']), 0, 15)), 'SCH_STATUS' => 'NA'])->delete();
    //             $response = ['Success' => true, 'Message' => 'Doctor schedule added successfully.', 'code' => 200];
    //             // } catch (\Throwable $th) {
    //             //     $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
    //             // }
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
    //     }
    //     return $response;
    // }

    function addsch(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            $td = $input['ADD_SCH'];

            foreach ($td as $row) {
                $schId = strtoupper(substr(md5($row['DR_ID'] . $row['PHARMA_ID'] . $row['SCH_DAY']), 0, 15));
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
                    } else {
                        $fields[$maxBookCol] = $row[$maxBookCol];
                    }
                }

                try {
                    $existingSchedule = DB::table('dr_availablity')->where('SCH_ID', $schId)->first();
                    if ($existingSchedule) {
                        $response = ['Success' => false, 'Message' => 'Schedule ID already exists.', 'code' => 200];
                    } else {
                        DB::table('dr_availablity')->insert($fields);
                        // DB::table('dr_availablity')->where(['SCH_ID' => strtoupper(substr(md5($row['DR_ID'] . $row['PHARMA_ID']), 0, 15)), 'SCH_STATUS' => 'NA'])->delete();
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


                // $todayDoctors = DB::table('pharmacy')
                //     ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
                //     ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
                //     ->distinct('drprofile.DR_ID')
                //     ->select(
                //         'pharmacy.PHARMA_ID',
                //         'pharmacy.ITEM_NAME',
                //         'pharmacy.ADDRESS',
                //         'pharmacy.CITY',
                //         'pharmacy.PIN',
                //         'pharmacy.DIST',
                //         'pharmacy.STATE',
                //         'pharmacy.LATITUDE',
                //         'pharmacy.LONGITUDE',
                //         'pharmacy.LONGITUDE',
                //         'pharmacy.PHOTO_URL',
                //         'pharmacy.LOGO_URL',
                //         // DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                //         // * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                //         // * SIN(RADIANS('$latt'))))),2) as KM"),
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
                //         // DB::raw("'" . Carbon::now()->format('Ymd') . "' as SCH_DT"),
                //         'dr_availablity.ID',
                //         'dr_availablity.SCH_DAY',
                //         'dr_availablity.START_MONTH',
                //         'dr_availablity.MONTH',
                //         'dr_availablity.ABS_FDT',
                //         'dr_availablity.ABS_TDT',
                //         'dr_availablity.CHK_IN_TIME',
                //         'dr_availablity.CHK_OUT_TIME',
                //         'dr_availablity.CHK_IN_TIME1',
                //         'dr_availablity.CHK_OUT_TIME1',
                //         'dr_availablity.CHK_IN_TIME2',
                //         'dr_availablity.CHK_OUT_TIME2',
                //         'dr_availablity.CHK_IN_TIME3',
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
                //         'dr_availablity.MAX_BOOK3'
                //     )
                //     ->where(['dr_availablity.SCH_DAY' => $day1, 'dr_availablity.PHARMA_ID' => $pid])
                //     ->where('WEEK', 'like', '%' . $weekNumber . '%')
                //     // ->orWhere(['dr_availablity.SCH_DT' => $cdy, 'dr_availablity.PHARMA_ID' => $pid])
                //     ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
                //     ->orderby('dr_availablity.CHK_IN_TIME')
                //     ->get();

                // // return $todayDoctors;

                // $result = [];
                // foreach ($todayDoctors as $doctor) {
                //     if (is_numeric($doctor->SCH_DAY)) {
                //         $date = Carbon::createFromDate(date('Y'), $doctor->START_MONTH, $doctor->SCH_DAY)
                //             ->addMonths($doctor->MONTH);
                //         if ($date->format('Ymd') === $cdt) {
                //             $sch_dt = $date->format('Ymd');
                //         }
                //     } else {
                //         $sch_dt = Carbon::now()->format('Ymd');
                //     }
                //     if ($currentDate->greaterThan($doctor->CHK_OUT_TIME)) {
                //         $doctorStatus = "OUT";
                //     } else {
                //         $doctorStatus = $doctor->CHK_IN_STATUS;
                //     }

                //     $result['Today_Doctors'][] = [
                //         "PHARMA_ID" => $doctor->PHARMA_ID,
                //         "PHARMA_NAME" => $doctor->ITEM_NAME,
                //         "ADDRESS" => $doctor->ADDRESS,
                //         "CITY" => $doctor->CITY,
                //         "PIN" => $doctor->PIN,
                //         "DIST" => $doctor->DIST,
                //         "STATE" => $doctor->STATE,
                //         "LATITUDE" => $doctor->LATITUDE,
                //         "LONGITUDE" => $doctor->LONGITUDE,
                //         "PHOTO_URL" => $doctor->PHOTO_URL,
                //         "LOGO_URL" => $doctor->LOGO_URL,
                //         "DR_ID" => $doctor->DR_ID,
                //         "DR_NAME" => $doctor->DR_NAME,
                //         "DR_MOBILE" => $doctor->DR_MOBILE,
                //         "SEX" => $doctor->SEX,
                //         "DESIGNATION" => $doctor->DESIGNATION,
                //         "QUALIFICATION" => $doctor->QUALIFICATION,
                //         "D_CATG" => $doctor->D_CATG,
                //         "EXPERIENCE" => $doctor->EXPERIENCE,
                //         "LANGUAGE" => $doctor->LANGUAGE,
                //         "DR_PHOTO" => $doctor->DR_PHOTO,
                //         "DR_FEES" => $doctor->DR_FEES,
                //         "APPNT_ID" => $doctor->ID,
                //         "APPNT_DT" => $sch_dt,
                //         "CHK_IN_TIME" => $doctor->CHK_IN_TIME,
                //         "CHK_OUT_TIME" => $doctor->CHK_OUT_TIME,
                //         "CHK_IN_TIME1" => $doctor->CHK_IN_TIME1,
                //         "CHK_OUT_TIME1" => $doctor->CHK_OUT_TIME1,
                //         "CHK_IN_TIME2" => $doctor->CHK_IN_TIME2,
                //         "CHK_OUT_TIME2" => $doctor->CHK_OUT_TIME2,
                //         "CHK_IN_TIME3" => $doctor->CHK_IN_TIME3,
                //         "CHK_OUT_TIME3" => $doctor->CHK_OUT_TIME3,
                //         "DR_STATUS" => $doctorStatus,
                //         "DR_ARRIVE" => $doctor->DR_ARRIVE,
                //         "CHEMBER_NO" => $doctor->CHEMBER_NO,
                //         "MAX_BOOK" => $doctor->MAX_BOOK,
                //         "MAX_BOOK1" => $doctor->MAX_BOOK1,
                //         "MAX_BOOK2" => $doctor->MAX_BOOK2,
                //         "MAX_BOOK3" => $doctor->MAX_BOOK3,

                //     ];
                // }

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
                    ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
                    ->orderBy('dr_availablity.CHK_IN_TIME')
                    ->get();

                $result = [];
                $currentDate = Carbon::now();

                foreach ($todayDoctors as $doctor) {
                    if (is_numeric($doctor->SCH_DAY)) {
                        $date = Carbon::createFromDate(date('Y'), $doctor->START_MONTH, $doctor->SCH_DAY)
                            ->addMonths($doctor->MONTH);
                        if ($date->format('Ymd') === $cdt) {
                            $sch_dt = $date->format('Ymd');
                        }
                    } else {
                        $sch_dt = Carbon::now()->format('Ymd');
                    }

                    $doctorStatus = "OUT"; // Default to OUT
                    $chamberNo = null;
                    $drArrive = null;
                    $totalMaxBook = 0;

                    // Check each check-in time and calculate the corresponding check-out time
                    for ($i = 0; $i <= 3; $i++) {
                        $chkInTime = "CHK_IN_TIME" . ($i == 0 ? "" : $i);
                        $chkOutTime = "CHK_OUT_TIME" . ($i == 0 ? "" : $i);
                        $drDelay = "DR_DELAY" . ($i == 0 ? "" : $i);
                        $chkInStatus = "CHK_IN_STATUS" . ($i == 0 ? "" : $i);
                        $chamberNoField = "CHEMBER_NO" . ($i == 0 ? "" : $i);
                        $drArriveField = "DR_ARRIVE" . ($i == 0 ? "" : $i);
                        $maxBook = "MAX_BOOK" . ($i == 0 ? "" : $i);
                        $slotIntvl = "SLOT_INTVL";

                        if (!is_null($doctor->$chkInTime)) {
                            $chkIn = Carbon::parse($doctor->$chkInTime);
                            $chkOut = $chkIn->copy()->addMinutes($doctor->$maxBook * $doctor->$slotIntvl);

                            // Add delay if present
                            if (!is_null($doctor->$drDelay)) {
                                [$delayHours, $delayMinutes] = explode(':', $doctor->$drDelay);
                                $chkOut->addHours($delayHours)->addMinutes($delayMinutes);
                            }

                            // Sum up the max books
                            $totalMaxBook += $doctor->$maxBook;

                            // Check if the current time is between the calculated check-in and check-out times
                            if ($currentDate->between($chkIn, $chkOut)) {
                                $doctorStatus = $doctor->$chkInStatus;
                                $chamberNo = $doctor->$chamberNoField;
                                $drArrive = $doctor->$drArriveField;
                                break;
                            }
                        }
                    }

                    if ($doctorStatus == "OUT") {
                        $chamberNo = null;
                    }

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
                        "APPNT_ID" => $doctor->ID,
                        "APPNT_DT" => $sch_dt,
                        "CHK_IN_TIME" => $doctor->CHK_IN_TIME,
                        "CHK_OUT_TIME" => $doctor->CHK_OUT_TIME,
                        "CHK_IN_TIME1" => $doctor->CHK_IN_TIME1,
                        "CHK_OUT_TIME1" => $doctor->CHK_OUT_TIME1,
                        "CHK_IN_TIME2" => $doctor->CHK_IN_TIME2,
                        "CHK_OUT_TIME2" => $doctor->CHK_OUT_TIME2,
                        "CHK_IN_TIME3" => $doctor->CHK_IN_TIME3,
                        "CHK_OUT_TIME3" => $doctor->CHK_OUT_TIME3,
                        "DR_STATUS" => $doctorStatus,
                        "DR_ARRIVE" => $drArrive,
                        "CHEMBER_NO" => $chamberNo,
                        "MAX_BOOK" => $totalMaxBook,
                    ];
                }




                if (empty($result['Today_Doctors'])) {
                    $result['Today_Doctors'] = [];
                }
                $doctorsCollection = collect($result['Today_Doctors']);
                $uniqueDoctors = $doctorsCollection->unique('DR_ID');
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
                    return $doctor['APPNT_DT'] . '-' . sprintf('%02d', $statusPriority) . '-' . $doctor['CHK_IN_TIME'];
                })->values()->all();
                $result['Today_Doctors'] = $sortedDoctors;

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

    function admtodaydr1(Request $req)
    {
        if ($req->isMethod('post')) {
            date_default_timezone_set('Asia/Kolkata');
            $currentDate = Carbon::now();
            $weekNumber = $currentDate->weekOfMonth;
            $currentDay = $currentDate->format('l');
            $cdy = date('d');
            $cdt = date('Ymd');
            $date = Carbon::now();
            $TDAY = $date->format('Ymd');
            $weekNumber = $date->weekOfMonth;
            $day1 = date('l');
            $TDAY = $date->format('Ymd');
            $data = array();
            $input = $req->json()->all();

            if (isset($input['PHARMA_ID'])) {
                $pid = $input['PHARMA_ID'];

                $data1 = DB::table('drprofile')
                    ->leftJoin('appointment', 'appointment.DR_ID', '=', 'drprofile.DR_ID')
                    ->leftJoin('user_family', 'user_family.ID', '=', 'appointment.PATIENT_ID')
                    ->select([
                        'drprofile.*',
                        'appointment.*',
                        'user_family.DOB',
                        'user_family.SEX as FAMILY_SEX',
                        'user_family.MOBILE as FAMILY_MOBILE',
                    ])
                    ->where('appointment.APPNT_DT', '>=', $TDAY)
                    ->where('appointment.PHARMA_ID', $pid)
                    ->where('drprofile.APPROVE', 'true')
                    ->orderByRaw("FIELD(appointment.DR_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
                    ->orderBy('appointment.APPNT_DT')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row) {
                    $appntDt = $row->APPNT_DT;
                    $drId = $row->DR_ID;

                    if (!isset($groupedData[$appntDt]['DOCTOR'][$drId])) {
                        $groupedData[$appntDt]['DOCTOR'][$drId] = [
                            "DR_ID" => $drId,
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
                            "CHEMBER_NO" => $row->CHEMBER_NO,
                            "PATIENT" => [],
                        ];
                    }

                    $groupedData[$appntDt]['DOCTOR'][$drId]['PATIENT'][] = [
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
                }

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
                        'dr_availablity.ID',
                        'dr_availablity.SCH_DAY',
                        'dr_availablity.START_MONTH',
                        'dr_availablity.MONTH',
                        'dr_availablity.ABS_FDT',
                        'dr_availablity.ABS_TDT',
                        'dr_availablity.CHK_IN_TIME',
                        'dr_availablity.CHK_OUT_TIME',
                        'dr_availablity.CHK_IN_STATUS',
                        'dr_availablity.CHEMBER_NO',
                        'dr_availablity.DR_ARRIVE',
                        'dr_availablity.MAX_BOOK'
                    )
                    ->where(['dr_availablity.SCH_DAY' => $day1])
                    ->where('WEEK', 'like', '%' . $weekNumber . '%')
                    ->orWhere('dr_availablity.SCH_DT', $cdy)
                    ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
                    ->orderby('dr_availablity.CHK_IN_TIME')
                    ->get();

                $result = [];
                foreach ($todayDoctors as $doctor) {
                    if (is_numeric($doctor->SCH_DAY)) {
                        $date = Carbon::createFromDate(date('Y'), $doctor->START_MONTH, $doctor->SCH_DAY)
                            ->addMonths($doctor->MONTH);
                        if ($date->format('Ymd') === $cdt) {
                            $sch_dt = $date->format('Ymd');
                        }
                    } else {
                        $sch_dt = Carbon::now()->format('Ymd');
                    }
                    if ($currentDate->greaterThan($doctor->CHK_OUT_TIME)) {
                        $doctorStatus = "OUT";
                    } else {
                        $doctorStatus = $doctor->CHK_IN_STATUS;
                    }

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
                        "APPNT_ID" => $doctor->ID,
                        "APPNT_DT" => $sch_dt,
                        "CHK_IN_TIME" => $doctor->CHK_IN_TIME,
                        "CHK_OUT_TIME" => $doctor->CHK_OUT_TIME,
                        "DR_STATUS" => $doctorStatus,
                        "DR_ARRIVE" => $doctor->DR_ARRIVE,
                        "CHEMBER_NO" => $doctor->CHEMBER_NO,
                        "MAX_BOOK" => $doctor->MAX_BOOK,

                    ];
                }
                if (empty($result['Today_Doctors'])) {
                    $result['Today_Doctors'] = [];
                }
                $sortedDoctors = collect($result['Today_Doctors'])->sortBy(function ($doctor) {
                    return $doctor['APPNT_DT'] . '-' . $doctor['DR_STATUS'] . '-' . $doctor['CHK_IN_TIME'];
                })->all();
                $result['Today_Doctors'] = array_values($sortedDoctors);

                $sortedBookedDoctors = collect($groupedData)->sortBy(function ($bookeddoctor) {
                    return $bookeddoctor['APPNT_DT'];
                })->all();
                $SD["Booked_Doctors"] = array_values($sortedBookedDoctors);

                $data = $SD + $result;

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
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

    // function drsts(Request $req)
    // {
    //     if (!$req->isMethod('post')) {
    //         return response()->json([
    //             'Success' => false,
    //             'Message' => 'Method Not Allowed.',
    //             'code' => 405
    //         ]);
    //     }
    //     date_default_timezone_set('Asia/Kolkata');
    //     $input = $req->json()->all();
    //     $cit = Carbon::now()->format('h:i A');
    //     $fields = [
    //         "CHK_IN_STATUS" => 'IN',
    //         "DR_ARRIVE" => $cit,
    //         "CHEMBER_NO" => $input['CHEMBER']
    //     ];
    //     $fields1 = [
    //         "CHEMBER_NO" => $input['CHEMBER'],
    //         "DR_STATUS" => 'IN',
    //         "DR_ARRIVE" => $cit
    //     ];



    //     try {
    //         DB::table('dr_availablity')->where(['ID' => $input['SCH_ID']])->update($fields);
    //         DB::table('appointment')->where(['APPNT_ID' => $input['SCH_ID']])->update($fields1);
    //         $TD = DB::table('drprofile')
    //             ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
    //             ->distinct('dr_availablity.DR_ID')
    //             ->select(
    //                 'drprofile.DR_ID',
    //                 'drprofile.DR_NAME',
    //                 'drprofile.DR_MOBILE',
    //                 'drprofile.SEX',
    //                 'drprofile.DESIGNATION',
    //                 'drprofile.QUALIFICATION',
    //                 'drprofile.D_CATG',
    //                 'drprofile.EXPERIENCE',
    //                 'drprofile.LANGUAGE',
    //                 'drprofile.PHOTO_URL AS DR_PHOTO',
    //                 'dr_availablity.ID as APPNT_ID',
    //                 DB::raw("'" . Carbon::now()->format('Ymd') . "' as APPNT_DT"),
    //                 'dr_availablity.DR_FEES',
    //                 'dr_availablity.CHK_IN_TIME',
    //                 'dr_availablity.CHK_OUT_TIME',
    //                 'dr_availablity.CHK_IN_STATUS AS DR_STATUS',
    //                 'dr_availablity.CHEMBER_NO',
    //                 'dr_availablity.MAX_BOOK',
    //             )
    //             ->where(['dr_availablity.ID' => $input['SCH_ID']])
    //             ->where('drprofile.APPROVE', 'true')
    //             ->first();
    //         $response = [
    //             'Success' => true,
    //             'data' => $TD,
    //             'Message' => 'Doctor arrived',
    //             'Chember No' => $input['CHEMBER'],
    //             'code' => 200
    //         ];
    //     } catch (\Exception $e) {
    //         $response = ['Success' => true, 'Message' => $e->getMessage(), 'code' => 500];
    //     }
    //     return $response;
    // }

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
        $apntid = $input['APPNT_ID'];


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
                $selectedArriveField = $arriveField;
                $selectedLeftField = $leftField;
                $selectedStatusField = $statusField; // Update the selected status field
                $selectedChemberField = $chemberField; // Update the selected chember field
                $selectedChkInTime = $timeField; // Update the selected CHK_IN_TIME field
                $selectedChkOutTime = $chkOutTimeField; // Update the selected CHK_OUT_TIME field
                $selectedMaxBookField = $maxBookField; // Update the selected MAX_BOOK field
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
            DB::table('dr_availablity')->where(['ID' => $input['APPNT_ID']])->update($fields_d);
            DB::table('appointment')
                ->where(function ($query) use ($tdt, $apntid, $fields_p2) {
                    $query->where(['APPNT_ID' => $apntid, 'APPNT_DT' => $tdt])->update($fields_p2);
                })
                ->where(['APPNT_ID' => $input['APPNT_ID'], 'APPNT_DT' => $input['APPNT_DT']])->update($fields_p1);

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
                    'dr_availablity' . $selectedArriveField . 'AS DR_ARRIVE',
                    'dr_availablity' . $selectedLeftField . 'AS DR_LEFT'
                )
                ->where(['dr_availablity.ID' => $input['APPNT_ID']])
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
                    "DR_STATUS" => 'OUT',
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
            $response = ['Success' => true, 'Message' => $e->getMessage(), 'code' => 500];
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
                    'dr_availablity.' . $selectedDelayField . 'DR_DELAY',
                )
                ->where(['dr_availablity.ID' => $input['APPNT_ID']])
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
            $response = ['Success' => true, 'Message' => $e->getMessage(), 'code' => 500];
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

    // function drcancel(Request $req)
    // {
    //     if (!$req->isMethod('post')) {
    //         return response()->json([
    //             'Success' => false,
    //             'Message' => 'Method Not Allowed.',
    //             'code' => 405
    //         ]);
    //     }
    //     date_default_timezone_set('Asia/Kolkata');
    //     $input = $req->json()->all();
    //     $tdt = carbon::now()->format('Ymd');

    //     $fields = [
    //         "CHK_IN_STATUS" => 'CANCELLED',
    //         "ABS_FDT" => $tdt,
    //         "ABS_TDT" => $tdt,
    //     ];
    //     $fields1 = [
    //         "DR_STATUS" => 'CANCELLED',
    //     ];

    //     try {
    //         DB::table('dr_availablity')->where(['ID' => $input['APPNT_ID']])->update($fields);
    //         DB::table('appointment')->where(['APPNT_ID' => $input['APPNT_ID'], 'APPNT_DT' => $tdt])->update($fields1);
    //         $todayDoctors = DB::table('pharmacy')
    //             ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
    //             ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
    //             ->distinct('drprofile.DR_ID')
    //             ->select(
    //                 'pharmacy.PHARMA_ID',
    //                 'pharmacy.ITEM_NAME',
    //                 'pharmacy.ADDRESS',
    //                 'pharmacy.CITY',
    //                 'pharmacy.PIN',
    //                 'pharmacy.DIST',
    //                 'pharmacy.STATE',
    //                 'pharmacy.LATITUDE',
    //                 'pharmacy.LONGITUDE',
    //                 'pharmacy.LONGITUDE',
    //                 'pharmacy.PHOTO_URL',
    //                 'pharmacy.LOGO_URL',
    //                 'drprofile.DR_ID',
    //                 'drprofile.DR_NAME',
    //                 'drprofile.DR_MOBILE',
    //                 'drprofile.SEX',
    //                 'drprofile.DESIGNATION',
    //                 'drprofile.QUALIFICATION',
    //                 'drprofile.D_CATG',
    //                 'drprofile.EXPERIENCE',
    //                 'drprofile.LANGUAGE',
    //                 'drprofile.PHOTO_URL AS DR_PHOTO',
    //                 'dr_availablity.DR_FEES',
    //                 DB::raw("'" . Carbon::now()->format('Ymd') . "' as APPNT_DT"),
    //                 'dr_availablity.ID',
    //                 'dr_availablity.SCH_DAY',
    //                 'dr_availablity.START_MONTH',
    //                 'dr_availablity.MONTH',
    //                 'dr_availablity.ABS_FDT',
    //                 'dr_availablity.ABS_TDT',
    //                 'dr_availablity.CHK_IN_TIME',
    //                 'dr_availablity.CHK_OUT_TIME',
    //                 'dr_availablity.CHK_IN_STATUS',
    //                 'dr_availablity.CHEMBER_NO',
    //                 'dr_availablity.DR_ARRIVE',
    //                 'dr_availablity.MAX_BOOK'
    //             )
    //             ->where(['dr_availablity.ID' => $input['APPNT_ID']])
    //             ->get();

    //         foreach ($todayDoctors as $doctor) {
    //             $result = [
    //                 "PHARMA_ID" => $doctor->PHARMA_ID,
    //                 "PHARMA_NAME" => $doctor->ITEM_NAME,
    //                 "ADDRESS" => $doctor->ADDRESS,
    //                 "CITY" => $doctor->CITY,
    //                 "PIN" => $doctor->PIN,
    //                 "DIST" => $doctor->DIST,
    //                 "STATE" => $doctor->STATE,
    //                 "LATITUDE" => $doctor->LATITUDE,
    //                 "LONGITUDE" => $doctor->LONGITUDE,
    //                 "PHOTO_URL" => $doctor->PHOTO_URL,
    //                 "LOGO_URL" => $doctor->LOGO_URL,
    //                 "DR_ID" => $doctor->DR_ID,
    //                 "DR_NAME" => $doctor->DR_NAME,
    //                 "DR_MOBILE" => $doctor->DR_MOBILE,
    //                 "SEX" => $doctor->SEX,
    //                 "DESIGNATION" => $doctor->DESIGNATION,
    //                 "QUALIFICATION" => $doctor->QUALIFICATION,
    //                 "D_CATG" => $doctor->D_CATG,
    //                 "EXPERIENCE" => $doctor->EXPERIENCE,
    //                 "LANGUAGE" => $doctor->LANGUAGE,
    //                 "DR_PHOTO" => $doctor->DR_PHOTO,
    //                 "DR_FEES" => $doctor->DR_FEES,
    //                 "APPNT_ID" => $doctor->ID,
    //                 "APPNT_DT" => $doctor->APPNT_DT,
    //                 "CHK_IN_TIME" => $doctor->CHK_IN_TIME,
    //                 "CHK_OUT_TIME" => $doctor->CHK_OUT_TIME,
    //                 "DR_STATUS" => 'CANCELLED',
    //                 "DR_ARRIVE" => $doctor->DR_ARRIVE,
    //                 "CHEMBER_NO" => $doctor->CHEMBER_NO,
    //                 "MAX_BOOK" => $doctor->MAX_BOOK,
    //             ];
    //         }

    //         $response = [
    //             'Success' => true,
    //             'Message' => 'Doctor cancelled',
    //             'data' => $result,
    //             'code' => 200
    //         ];
    //     } catch (\Exception $e) {
    //         $response = ['Success' => true, 'Message' => $e->getMessage(), 'code' => 500];
    //     }
    //     return $response;
    // }

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

        $fields = [
            "CHK_IN_STATUS" => 'CANCELLED',
            "CHK_IN_STATUS1" => 'CANCELLED',
            "CHK_IN_STATUS2" => 'CANCELLED',
            "CHK_IN_STATUS3" => 'CANCELLED',
            "ABS_FDT" => $tdt,
            "ABS_TDT" => $tdt,
        ];
        $fields1 = [
            "DR_STATUS" => 'CANCELLED',
        ];

        try {
            DB::table('dr_availablity')->where(['ID' => $input['APPNT_ID']])->update($fields);
            DB::table('appointment')->where(['APPNT_ID' => $input['APPNT_ID'], 'APPNT_DT' => $tdt])->update($fields1);
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
                    'dr_availablity.CHK_IN_TIME',
                    'dr_availablity.CHK_OUT_TIME',
                    'dr_availablity.CHK_IN_STATUS',
                    'dr_availablity.CHEMBER_NO',
                    'dr_availablity.DR_ARRIVE',
                    'dr_availablity.MAX_BOOK'
                )
                ->where(['dr_availablity.ID' => $input['APPNT_ID']])
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
                    "DR_STATUS" => 'CANCELLED',
                    "DR_ARRIVE" => $doctor->DR_ARRIVE,
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

    // function appntdr(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         date_default_timezone_set('Asia/Kolkata');
    //         $input = $req->json()->all();
    //         if (isset($input['PHARMA_ID'])) {
    //             $pid = $input['PHARMA_ID'];
    //             $date = Carbon::now();

    //             $TDAY = $date->format('Ymd');
    //             $data = array();

    //             $data1 = DB::table('drprofile')
    //                 ->leftjoin('appointment', 'appointment.DR_ID', '=', 'drprofile.DR_ID')
    //                 ->leftjoin('user_family', 'appointment.PATIENT_ID', '=', 'user_family.ID')
    //                 ->select(
    //                     'drprofile.DR_ID',
    //                     'drprofile.DR_NAME',
    //                     'drprofile.DR_MOBILE',
    //                     'drprofile.SEX',
    //                     'drprofile.DESIGNATION',
    //                     'drprofile.QUALIFICATION',
    //                     'drprofile.D_CATG',
    //                     'drprofile.EXPERIENCE',
    //                     'drprofile.LANGUAGE',
    //                     'drprofile.PHOTO_URL',
    //                     'drprofile.LANGUAGE',
    //                     'appointment.BOOKING_ID',
    //                     'appointment.FAMILY_ID',
    //                     'appointment.PATIENT_ID',
    //                     'appointment.PATIENT_NAME',
    //                     'user_family.DOB',
    //                     'user_family.SEX',
    //                     'user_family.MOBILE',
    //                     'appointment.APPNT_ID',
    //                     'appointment.APPNT_DT',
    //                     'appointment.APPNT_TOKEN',
    //                     'appointment.ARRIVE',
    //                     'appointment.BOOKING_SL',
    //                     'appointment.STATUS',
    //                     'appointment.DR_FEES',
    //                     'appointment.PATIENT_REVIEW',
    //                     'appointment.APPNT_FROM',
    //                     'appointment.CHEMBER_NO',
    //                     'appointment.DR_STATUS',
    //                     'appointment.DR_DELAY',
    //                     'appointment.CHK_IN_TIME',
    //                     'appointment.CHK_OUT_TIME',

    //                 )
    //                 ->where('appointment.APPNT_DT', '=', $TDAY)
    //                 ->where('appointment.PHARMA_ID', $pid)
    //                 ->where('drprofile.APPROVE', 'true')
    //                 ->orderBy('appointment.APPNT_DT')
    //                 ->get();

    //             $groupedData = [];
    //             foreach ($data1 as $row) {

    //                 if (!isset($groupedData[$row->DR_ID])) {
    //                     $groupedData[$row->DR_ID] = [
    //                         "APPNT_DT" => $row->APPNT_DT,
    //                         "DR_ID" => $row->DR_ID,
    //                         "APPNT_ID" => $row->APPNT_ID,
    //                         "DR_NAME" => $row->DR_NAME,
    //                         "DR_MOBILE" => $row->DR_MOBILE,
    //                         "SEX" => $row->SEX,
    //                         "DESIGNATION" => $row->DESIGNATION,
    //                         "QUALIFICATION" => $row->QUALIFICATION,
    //                         "D_CATG" => $row->D_CATG,
    //                         "EXPERIENCE" => $row->EXPERIENCE,
    //                         "LANGUAGE" => $row->LANGUAGE,
    //                         "DR_PHOTO" => $row->PHOTO_URL,
    //                         "DR_FEES" => $row->DR_FEES,
    //                         "CHEMBER_NO" => $row->CHEMBER_NO,
    //                         "DR_STATUS" => $row->DR_STATUS,
    //                         "DR_DELAY" => $row->DR_DELAY,
    //                         "CHK_IN_TIME" => $row->CHK_IN_TIME,
    //                         "CHK_OUT_TIME" => $row->CHK_OUT_TIME,
    //                         "PATIENTS" => [],
    //                     ];
    //                 }
    //                 $groupedData[$row->DR_ID]['PATIENTS'][] = [
    //                     "FAMILY_ID" => $row->FAMILY_ID,
    //                     "PATIENT_ID" => $row->PATIENT_ID,
    //                     "PATIENT_NAME" => $row->PATIENT_NAME,
    //                     "AGE" => $row->DOB,
    //                     "SEX" => $row->SEX,
    //                     "BOOKING_ID" => $row->BOOKING_ID,
    //                     "APPNT_TOKEN" => $row->APPNT_TOKEN,
    //                     "APPNT_ID" => $row->APPNT_ID,
    //                     "APPNT_DT" => $row->APPNT_DT,
    //                     "APPNT_FROM" => $row->APPNT_FROM,
    //                     "STATUS" => $row->STATUS,
    //                     "BOOKING_SL" => $row->BOOKING_SL,
    //                 ];
    //             }
    //             foreach ($groupedData as $pId => &$phid) {
    //                 $phid['TOT_PATIENT'] = count($phid['PATIENTS']);
    //             }
    //             $data = array_values($groupedData);

    //             usort($data, function ($item1, $item2) {
    //                 $order = [
    //                     'IN' => 1,
    //                     'TIMELY' => 2,
    //                     'DELAY' => 3,
    //                     'CANCELLED' => 4,
    //                     'OUT' => 5,
    //                     'LEAVE' => 6
    //                 ];
    //                 $status1 = $order[$item1['DR_STATUS']] ?? 999;
    //                 $status2 = $order[$item2['DR_STATUS']] ?? 999;
    //                 if ($status1 == $status2) {
    //                     return 0;
    //                 }
    //                 return ($status1 < $status2) ? -1 : 1;
    //             });
    //             $response = ['Success' => true, 'data' => $data, 'code' => 200];
    //         } else {
    //             $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
    //     }
    //     return $response;
    // }


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

                // Join with dr_availability table
                $data1 = DB::table('drprofile')
                    ->leftjoin('dr_availablity', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
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
                        'dr_availablity.ID',
                        'dr_availablity.DR_FEES',
                        'dr_availablity.PHARMA_ID',
                        'dr_availablity.SCH_DT',
                        'dr_availablity.SCH_STATUS',
                        'dr_availablity.CHEMBER_NO',
                        'dr_availablity.CHK_IN_TIME',
                        'dr_availablity.CHK_OUT_TIME',
                        'dr_availablity.CHK_IN_TIME1',
                        'dr_availablity.CHK_OUT_TIME1',
                        'dr_availablity.CHK_IN_TIME2',
                        'dr_availablity.CHK_OUT_TIME2',
                        'dr_availablity.CHK_IN_TIME3',
                        'dr_availablity.CHK_OUT_TIME3',
                        'dr_availablity.DR_DELAY',
                        'dr_availablity.DR_DELAY1',
                        'dr_availablity.DR_DELAY2',
                        'dr_availablity.DR_DELAY3',
                        'dr_availablity.CHEMBER_NO1',
                        'dr_availablity.CHEMBER_NO2',
                        'dr_availablity.CHEMBER_NO3',
                        'dr_availablity.CHK_IN_STATUS',
                        'dr_availablity.CHK_IN_STATUS1',
                        'dr_availablity.CHK_IN_STATUS2',
                        'dr_availablity.CHK_IN_STATUS3'
                    )
                    // ->where('dr_availablity.SCH_DT', '=', $TDAY)
                    ->where('dr_availablity.PHARMA_ID', $pid)
                    ->where('drprofile.APPROVE', 'true')
                    ->where(['dr_availablity.SCH_DAY' => $day1])
                    ->where('WEEK', 'like', '%' . $weekNumber . '%')
                    ->orWhere('dr_availablity.SCH_DT', $cdy)
                    ->orderBy('dr_availablity.SCH_DT')
                    ->get();


                $groupedData = [];
                foreach ($data1 as $row) {
                    if (!isset($groupedData[$row->DR_ID])) {

                        $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));

                        $firstRowTOTime = $row->CHK_OUT_TIME ? Carbon::createFromFormat('h:i A', $row->CHK_OUT_TIME) : null;
                        $firstRowTOTime1 = $row->CHK_OUT_TIME1 ? Carbon::createFromFormat('h:i A', $row->CHK_OUT_TIME1) : null;
                        $firstRowTOTime2 = $row->CHK_OUT_TIME2 ? Carbon::createFromFormat('h:i A', $row->CHK_OUT_TIME2) : null;
                        $firstRowTOTime3 = $row->CHK_OUT_TIME3 ? Carbon::createFromFormat('h:i A', $row->CHK_OUT_TIME3) : null;

                        $firstRowTOChember = $row->CHEMBER_NO ? $row->CHEMBER_NO : null;
                        $firstRowTOChember1 = $row->CHEMBER_NO1 ? $row->CHEMBER_NO1 : null;
                        $firstRowTOChember2 = $row->CHEMBER_NO2 ? $row->CHEMBER_NO2 : null;
                        $firstRowTOChember3 = $row->CHEMBER_NO3 ? $row->CHEMBER_NO3 : null;

                        $allTimesPassed = true;

                        if ($firstRowTOTime && $currentTime->lessThanOrEqualTo($firstRowTOTime)) {
                            $drchember = $firstRowTOChember;
                            $drstatus = $row->CHK_IN_STATUS;
                            $allTimesPassed = false;
                        }
                        if ($firstRowTOTime1 && $currentTime->lessThanOrEqualTo($firstRowTOTime1)) {
                            $drchember = $firstRowTOChember1;
                            $drstatus = $row->CHK_IN_STATUS1;
                            $allTimesPassed = false;
                        }
                        if ($firstRowTOTime2 && $currentTime->lessThanOrEqualTo($firstRowTOTime2)) {
                            $drchember = $firstRowTOChember2;
                            $drstatus = $row->CHK_IN_STATUS2;
                            $allTimesPassed = false;
                        }
                        if ($firstRowTOTime3 && $currentTime->lessThanOrEqualTo($firstRowTOTime3)) {
                            $drchember = $firstRowTOChember3;
                            $drstatus = $row->CHK_IN_STATUS3;
                            $allTimesPassed = false;
                        }

                        if ($allTimesPassed) {
                            $drstatus = "OUT";
                            $drchember = null;
                        }
                        // else {
                        //     $drstatus = $row->CHK_IN_STATUS;
                        // }

                        $groupedData[$row->DR_ID] = [
                            "SCH_ID" => $row->ID,
                            "APPNT_DT" => $TDAY,
                            "DR_ID" => $row->DR_ID,
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
                            "CHEMBER_NO" => $drchember,
                            // "CHEMBER_NO1" => $row->CHEMBER_NO1,
                            // "CHEMBER_NO2" => $row->CHEMBER_NO2,
                            // "CHEMBER_NO3" => $row->CHEMBER_NO3,
                            "CHK_IN_TIME" => $row->CHK_IN_TIME,
                            "CHK_OUT_TIME" => $row->CHK_OUT_TIME,
                            "CHK_IN_TIME1" => $row->CHK_IN_TIME1,
                            "CHK_OUT_TIME1" => $row->CHK_OUT_TIME1,
                            "CHK_IN_TIME2" => $row->CHK_IN_TIME2,
                            "CHK_OUT_TIME2" => $row->CHK_OUT_TIME2,
                            "CHK_IN_TIME3" => $row->CHK_IN_TIME3,
                            "CHK_OUT_TIME3" => $row->CHK_OUT_TIME3,
                            "DR_DELAY" => $row->DR_DELAY,
                            "DR_DELAY1" => $row->DR_DELAY1,
                            "DR_DELAY2" => $row->DR_DELAY2,
                            "DR_DELAY3" => $row->DR_DELAY3,
                            "DR_STATUS" => $drstatus,
                            "CHK_IN_STATUS" => $row->CHK_IN_STATUS,
                            "CHK_IN_STATUS1" => $row->CHK_IN_STATUS1,
                            "CHK_IN_STATUS2" => $row->CHK_IN_STATUS2,
                            "CHK_IN_STATUS3" => $row->CHK_IN_STATUS3,
                            "PATIENTS" => []
                        ];
                    }

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
                        ->where('appointment.DR_ID', '=', $row->DR_ID)
                        ->get();

                    foreach ($patients as $patient) {
                        $groupedData[$row->DR_ID]['PATIENTS'][] = [
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
            $data = array_filter($data, function($doctor) {
                return $doctor['TOT_PATIENT'] > 0;
            });

                usort($data, function ($item1, $item2) {
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
                    ->orderby('appointment.BOOKING_SL')->get();

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
            "CLINIC_TYPE" => $input['CLINIC_TYPE'] ?? null,
            "LATITUDE" => $input['LATITUDE'] ?? null,
            "LONGITUDE" => $input['LONGITUDE'] ?? null,
        ];


        try {
            DB::table('pharmacy')->where(['PHARMA_ID' => $PID])->update($fields);
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

                $promo_bnr = DB::table('dashboard')
                    ->leftJoin('promo_banner', function ($join) use ($pid) {
                        $join->on('dashboard.DASH_ID', '=', 'promo_banner.DASH_ID')
                            ->where('promo_banner.PHARMA_ID', '=', $pid);
                    })
                    ->select(
                        'dashboard.DASH_ID',
                        'dashboard.DASH_SECTION_ID',
                        'dashboard.DASH_SECTION_NAME',
                        'dashboard.DASH_NAME',
                        'dashboard.DASH_DESCRIPTION',
                        'dashboard.PHOTO_URL',
                        'promo_banner.REMARK',
                    )
                    ->distinct('dashboard.DASH_ID')
                    ->where(['dashboard.DASH_TYPE' => 'About Us'])->get();

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) use ($pid) {
                    return $item->DASH_SECTION_ID === 'U';
                });
                $A["Why_Choose_Us"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO_URL,
                        "REMARK" => $item->REMARK,
                    ];
                })->values()->all();

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) use ($pid) {
                    return $item->DASH_SECTION_ID === 'V';
                });
                $B["What_Makes_Us_Special"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO_URL,
                        "REMARK" => $item->REMARK,
                    ];
                })->values()->all();
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) use ($pid) {
                    return $item->DASH_SECTION_ID === 'W';
                });
                $C["Special_Services"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO_URL,
                        "REMARK" => $item->REMARK,
                    ];
                })->values()->all();
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) use ($pid) {
                    return $item->DASH_SECTION_ID === 'X';
                });
                $D["Advance_Equipments"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO_URL,
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

    function addabout(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();

            $input = $req->json()->all();
            $abt_arr = $input['ABOUT_DATA'] ?? [];
            $dr_arr = $input['DR_DATA'] ?? [];
            $rmv_arr = $input['RMV_DATA'] ?? [];
            // $edt_arr = $input['EDT_DATA'] ?? [];

            if (is_array($rmv_arr) && !empty($rmv_arr)) {
                foreach ($rmv_arr as $row1) {
                    if (isset($row1['DASH_ID'])) {
                        DB::table('promo_banner')->where(['DASH_ID' => $row1['DASH_ID'], 'PHARMA_ID' => $row1['PHARMA_ID']])->delete();
                    }
                }
            }

            if (is_array($abt_arr) && !empty($abt_arr)) {
                foreach ($abt_arr as $row) {

                    $fields = [
                        "DASH_ID" => $row['DASH_ID'],
                        "PHARMA_ID" => $row['PHARMA_ID'],
                        "DASH_SECTION_ID" => $row['DASH_SECTION_ID'],
                        "HEADER_NAME" => $row['DASH_SECTION_NAME'],
                        "PROMO_NAME" => $row['DASH_NAME'],
                        "DESCRIPTION" => $row['DASH_DESCRIPTION'],
                        "PROMO_URL" => $row['PHOTO_URL'],
                        "PROMO_TYPE" => 'About Us',
                        "REMARK" => 'Added'
                    ];
                    DB::table('promo_banner')->insert($fields);
                }
            }

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


            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 200];
        }
        return $response;
    }

    function searchDoctorTest(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $data = collect();

            $data = $data->merge($this->getSpecialist1($pharmaId));
            $data = $data->merge($this->getTestDetails());
            $data = $data->merge($this->getDoctorAvailability($pharmaId));
            $data = $data->merge($this->getDashboardItems());
            $data = $data->merge($this->getSymptomsDetails());

            if ($data == null) {
                $response = ['Success' => false, 'Message' => 'Test/Clinic not found', 'code' => 200];
            } else {
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }
    function admsearchtest(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $data = collect();

            // $data = $data->merge($this->getSpecialist1($pharmaId));
            $data = $data->merge($this->getTestDetails());
            // $data = $data->merge($this->getDoctorAvailability($pharmaId));
            $data = $data->merge($this->getDashboardItems());
            // $data = $data->merge($this->getSymptomsDetails());

            if ($data == null) {
                $response = ['Success' => false, 'Message' => 'Test/Clinic not found', 'code' => 200];
            } else {
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }
    function admcatgclinicdr1(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $did = $input['DIS_ID'];
            $data = collect();


            $data = $data->merge($this->getCatgDrDt1($pharmaId, $did));


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



    private function getTestDetails()
    {
        return DB::table('master_testdata')->get()->map(function ($item) {
            return [
                "ID" => $item->TEST_ID,
                "ITEM_NAME" => $item->TEST_NAME,
                "FIELD_TYPE" => $item->DEPARTMENT,
                "DETAILS" => [
                    "TEST_ID" => $item->TEST_ID,
                    "TEST_NAME" => $item->TEST_NAME,
                ]
            ];
        });
    }

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

        $DRSCH = [];


        foreach ($totdr as $row1) {
            $dravail = DB::table('dr_availablity')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pharmaId])->get();
            $totapp = DB::table('appointment')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pharmaId])->get();
            $data = [];

            foreach ($dravail as $row) {
                if (is_numeric($row->SCH_DAY)) {
                    $currentYear = date("Y");
                    $startDate = new DateTime("{$currentYear}-$row->START_MONTH-$row->SCH_DAY");

                    for ($i = 0; $i < 6; $i++) {
                        $dates = [];
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
                            if ($row->MAX_BOOK - $totappct == 0) {
                                $book_sts = "Closed";
                            } else {
                                $book_sts = "Available";
                            }
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
                return $item['DR_STATUS'] === 'IN' || $item['DR_STATUS'] === 'TIMELY';
            });
            if ($firstAvailable) {
                $firstAvailableIndex = $collection->search($firstAvailable);
                $sixRows = array_slice($data, $firstAvailableIndex, 6);
            }

            $DRSCH[] = [
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
                    "DR_FEES" => $row->DR_FEES,
                    "LANGUAGE" => $row1->LANGUAGE,
                    "DR_PHOTO" => $row1->PHOTO_URL,
                    "SCH_DT" => $sixRows
                ]
            ];
        }
        return $DRSCH;
    }

    private function getDoctorAvailability($pharmaId)
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
            )
            ->distinct('DR_ID')
            ->where(['dr_availablity.PHARMA_ID' => $pharmaId])
            ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
            ->where('drprofile.APPROVE', 'true')
            ->get();

        $DRSCH = [];
        $cym = date('Ymd');

        foreach ($totdr as $row1) {
            $dravail = DB::table('dr_availablity')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pharmaId])->get();
            $totapp = DB::table('appointment')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pharmaId])->get();
            $data = [];

            // return $dravail;
            foreach ($dravail as $row) {
                if (is_numeric($row->SCH_DAY)) {
                    $currentYear = date("Y");
                    $startDate = new DateTime("{$currentYear}-$row->START_MONTH-$row->SCH_DAY");

                    for ($i = 0; $i < 6; $i++) {
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
                            if ($row->MAX_BOOK - $totappct == 0) {
                                $book_sts = "Closed";
                            } else {
                                $book_sts = "Available";
                            }
                            $data[] = [
                                "ID" => $row->ID,
                                "SCH_DT" => $dates,
                                "SCH_DAY" => $schday,
                                "SLOT" => $row->SLOT,
                                "SCHEDULE" => $row->DESCRIPTION,
                                "FROM" => $row->CHK_IN_TIME,
                                "TO" => $row->CHK_OUT_TIME,
                                "BOOK_START_TIME" => $row->BOOK_ST_TM,
                                "DR_STATUS" => $row->CHK_IN_STATUS,
                                "CHEMBER_NO" => $row->CHEMBER_NO,
                                "DR_ARRIVE" => $row->DR_ARRIVE,
                                "MAX_BOOK" => $row->MAX_BOOK,
                                "SLOT_STATUS" => $book_sts,
                                "BOOK_START_DT" => $formattedBookingDate,
                                "MAX_BOOK" => $row->MAX_BOOK
                            ];
                            // if (!empty($earliestDates)) {
                            //     break;
                            // }
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
                                $data[] = [
                                    "ID" => $row->ID,
                                    "SCH_DT" => $dates,
                                    "SCH_DAY" => $row->SCH_DAY,
                                    "SLOT" => $row->SLOT,
                                    "SCHEDULE" => $row->DESCRIPTION,
                                    "FROM" => $row->CHK_IN_TIME,
                                    "TO" => $row->CHK_OUT_TIME,
                                    "BOOK_START_TIME" => $row->BOOK_ST_TM,
                                    "DR_STATUS" => $row->CHK_IN_STATUS,
                                    "DR_ARRIVE" => $row->DR_ARRIVE,
                                    "CHEMBER_NO" => $row->CHEMBER_NO,
                                    "MAX_BOOK" => $row->MAX_BOOK,
                                    "SLOT_STATUS" => $book_sts,
                                    "BOOK_START_DT" => $formattedBookingDate,
                                    "MAX_BOOK" => $row->MAX_BOOK
                                ];
                                $counter++;
                            }
                        }
                        $startDate->addDay();
                    }
                }
            }


            // return $data;
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
            $firstAvailable = $collection->first(function ($item) {
                return $item['DR_STATUS'] === 'IN' || $item['DR_STATUS'] === 'TIMELY';
            });
            if ($firstAvailable) {
                $firstAvailableIndex = $collection->search($firstAvailable);
                $sixRows = array_slice($data, $firstAvailableIndex, 6);
            }


            $DRSCH[] = [
                "ID" => $row1->DR_ID,
                "ITEM_NAME" => $row1->DR_NAME,
                "FIELD_TYPE" => "Doctor",
                "DETAILS" => [
                    "DR_ID" => $row1->DR_ID,
                    "DR_NAME" => $row1->DR_NAME,
                    "DR_MOBILE" => $row1->DR_MOBILE,
                    "DR_STATUS" => $row->CHK_IN_STATUS,
                    "DR_ARRIVE" => $row->DR_ARRIVE,
                    "CHEMBER_NO" => $row->CHEMBER_NO,
                    "SEX" => $row1->SEX,
                    "DESIGNATION" => $row1->DESIGNATION,
                    "QUALIFICATION" => $row1->QUALIFICATION,
                    "UID_NMC" => $row1->UID_NMC,
                    "REGN_NO" => $row1->REGN_NO,
                    "D_CATG" => $row1->D_CATG,
                    "EXPERIENCE" => $row1->EXPERIENCE,
                    "DR_FEES" => $row->DR_FEES,
                    "LANGUAGE" => $row1->LANGUAGE,
                    "DR_PHOTO" => $row1->PHOTO_URL,
                    "SCH_DT" => $sixRows
                ]
            ];
        }

        return $DRSCH;
    }


    private function getDashboardItems()
    {
        $data = [];
        $data3 = DB::table('dashboard')
            ->whereIn('DASH_SECTION_ID', ['B', 'C', 'D', 'G', 'H', 'S', 'T'])
            ->where('STATUS', 'Active')
            ->orderby('DASH_TYPE')
            ->get();

        foreach ($data3 as $row3) {
            $pkgdtl = [];
            $pkgdtl['DETAILS'] = [
                "DASH_ID" => $row3->DASH_ID,
                "DASH_NAME" => $row3->DASH_NAME,
                "DASH_TYPE" => $row3->DASH_TYPE,
                "DESCRIPTION" => $row3->DASH_DESCRIPTION
            ];

            $pkg = [
                "ID" => $row3->DASH_ID,
                "ITEM_NAME" => $row3->DASH_NAME,
                "FIELD_TYPE" => $row3->DASH_TYPE,
                "DETAILS" => $pkgdtl['DETAILS']
            ];
            array_push($data, $pkg);
        }
        return $data;
    }

    private function getSymptomsDetails()
    {
        $data = [];
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
        return $data;
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

    function vucatgdrdt(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $DisId = $input['DIS_ID'];

            $data = collect();

            $data = $data->merge($this->catgdtldt($pharmaId, $DisId));

            if ($data == null) {
                $response = ['Success' => false, 'Message' => 'Test/Clinic not found', 'code' => 200];
            } else {
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    private function catgdtldt($pharmaId, $DisId)
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
            )
            ->distinct('DR_ID')
            ->where(['dr_availablity.PHARMA_ID' => $pharmaId, 'drprofile.DIS_ID' => $DisId])
            ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
            ->where('drprofile.APPROVE', 'true')
            ->get();

        // return $totdr;

        $DRSCH = [];

        foreach ($totdr as $row1) {
            $dravail = DB::table('dr_availablity')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pharmaId])->get();
            $totapp = DB::table('appointment')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pharmaId])->get();
            $data = [];
            // return $dravail;
            foreach ($dravail as $row) {
                if (is_numeric($row->SCH_DAY)) {
                    $currentYear = date("Y");
                    $startDate = new DateTime("{$currentYear}-$row->START_MONTH-$row->SCH_DAY");

                    for ($i = 0; $i < 6; $i++) {
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
                                $book_sts = "Booking Closed";
                            } else {
                                $book_sts = "Booking Available";
                            }
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
                                "STATUS" => $book_sts,
                                "BOOK_START_DT" => $dates,
                                "MAX_BOOK" => $row->MAX_BOOK
                            ];
                            // if (!empty($earliestDates)) {
                            //     break;
                            // }
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
                                    $book_sts = "Booking Closed";
                                } else {
                                    $book_sts = "Booking Available";
                                }
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
                                    "STATUS" => $book_sts,
                                    "BOOK_START_DT" => $formattedBookingDate,
                                    "MAX_BOOK" => $row->MAX_BOOK
                                ];
                                $counter++;
                            }
                        }
                        $startDate->addDay();
                    }
                }
            }


            // return $data;
            usort($data, function ($item1, $item2) {
                return $item1['SCH_DT'] <=> $item2['SCH_DT'];
            });

            $sixRows['SCH_DT'] = array_slice($data, 0, 6);


            $DRSCH[] = [
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
                "DR_FEES" => $row->DR_FEES,
                "LANGUAGE" => $row1->LANGUAGE,
                "DR_PHOTO" => $row1->PHOTO_URL,
                "SCH_DT" => $sixRows['SCH_DT']
            ];
        }

        return $DRSCH;
    }

    // function adddrcsv(Request $req)
    // {
    //     if (!$req->isMethod('post')) {
    //         return response()->json([
    //             'Success' => false,
    //             'Message' => 'Method Not Allowed.',
    //             'code' => 405
    //         ], 405);
    //     }

    //     $row = $req->all();
    //     $pid = $row['PHARMA_ID'];
    //     $pname = $row['PHARMA_NAME'];

    //     $validator = Validator::make($req->all(), [
    //         'file' => 'required|mimes:csv',
    //     ]);

    //     if ($validator->fails()) {
    //         $response = ['Success' => false, 'Message' => $validator->errors(), 'code' => 422];
    //         return $response;
    //     }

    //     $file = $req->file('file');
    //     $data = array_map('str_getcsv', file($file));
    //     $data = array_slice($data, 1);

    //     // return $data;

    //     foreach ($data as $input) {
    //         $fields_dr = [
    //             'DR_ID' => strtoupper(substr(md5($input[3] . $input[4]), 0, 15)),
    //             'DR_NAME' => $input[0] ?? null,
    //             'DR_MOBILE' => $input[1] ?? null,
    //             'SEX' => $input[2] ?? null,
    //             'REGN_NO' => $input[3] ?? null,
    //             'COUNCIL' => $input[4] ?? null,
    //             'ASSOCIATED' => $input[5] ?? null,
    //             'DESIGNATION' => $input[6] ?? null,
    //             'QUALIFICATION' => $input[7] ?? null,
    //             'D_CATG' => $input[8] ?? null,
    //             'EXPERIENCE' => $input[9] ?? null,
    //             'LANGUAGE' => $input[10] ?? null,
    //             'REPORTING_DAY' => $input[11] ?? null,
    //         ];
    //         $fields_sch = [
    //             "DR_ID" => strtoupper(substr(md5($input[3] . $input[4]), 0, 15)),
    //             "SCH_ID" => strtoupper(substr(md5($input[3] . $input[4]), 0, 15)),
    //             "DR_FEES" => $input[12] ?? 0,
    //             "PHARMA_ID" => $pid,
    //             "PHARMA_NAME" => $pname,
    //         ];

    //         try {
    //             DB::table('drprofile')->insert($fields_dr);
    //             DB::table('dr_availablity')->insert($fields_sch);
    //             $response = ['Success' => true, 'Message' => 'Doctor added successfully', 'code' => 200];
    //         } catch (\Exception $e) {
    //             // try {
    //                 DB::table('dr_availablity')->insert($fields_sch);
    //                 $response = ['Success' => true, 'Message' => 'Doctor schedule added successfully', 'code' => 200];
    //             // } catch (\Throwable $th) {
    //             //     $response = ['Success' => false, 'Message' => $e->getMessage(), 'code' => 500];
    //             // }
    //         }
    //     }
    //     return $response;
    // }

    //commented sir's code
    // function adddrcsv(Request $req)
    // {
    //     if (!$req->isMethod('post')) {
    //         return response()->json([
    //             'Success' => false,
    //             'Message' => 'Method Not Allowed.',
    //             'code' => 405
    //         ], 405);
    //     }

    //     $row = $req->all();
    //     $pid = $row['PHARMA_ID'];
    //     $pname = $row['PHARMA_NAME'];

    //     $validator = Validator::make($req->all(), [
    //         'file' => 'required|mimes:csv',
    //     ]);

    //     if ($validator->fails()) {
    //         $response = ['Success' => false, 'Message' => $validator->errors(), 'code' => 422];
    //         return response()->json($response, 422);
    //     }

    //     $file = $req->file('file');
    //     $data = array_map('str_getcsv', file($file));
    //     $data = array_slice($data, 1);

    //     $failedRows = [];

    //     foreach ($data as $input) {
    //         $fields_dr = [
    //             'DR_ID' => strtoupper(substr(md5($input[3] . $input[4]), 0, 15)),
    //             'DR_NAME' => $input[0] ?? null,
    //             'DR_MOBILE' => $input[1] ?? null,
    //             'SEX' => $input[2] ?? null,
    //             'REGN_NO' => $input[3] ?? null,
    //             'COUNCIL' => $input[4] ?? null,
    //             // 'ASSOCIATED' => $input[5] ?? null,
    //             'DESIGNATION' => $input[5] ?? null,
    //             'QUALIFICATION' => $input[6] ?? null,
    //             'D_CATG' => $input[7] ?? null,
    //             'EXPERIENCE' => $input[8] ?? null,
    //             'LANGUAGE' => $input[9] ?? null,
    //             'REPORTING_DAY' => $input[10] ?? null,
    //             'GOOGLE_REVIEW' => $input[12] ?? null,
    //         ];
    //         $fields_sch = [
    //             "DR_ID" => strtoupper(substr(md5($input[3] . $input[4]), 0, 15)),
    //             "SCH_ID" => strtoupper(substr(md5($input[3] . $input[4] . $pid), 0, 15)),
    //             "DR_FEES" => $input[11] ?? 0,
    //             "PHARMA_ID" => $pid,
    //             "PHARMA_NAME" => $pname,
    //         ];


    //         try {
    //             DB::table('drprofile')->insert($fields_dr);
    //         } catch (\Exception $e) {
    //         }
    //         try {
    //             DB::table('dr_availablity')->insert($fields_sch);
    //         } catch (\Exception $e) {
    //             $failedRows[] = $input;
    //         }
    //     }

    //     if (count($failedRows) > 0) {
    //         $failedCsvPath = 'app/drprofile/failed_rows.csv';
    //         $filePath = storage_path("$failedCsvPath");

    //         $file = fopen($filePath, 'w');
    //         foreach ($failedRows as $failedRow) {
    //             fputcsv($file, $failedRow);
    //         }
    //         fclose($file);

    //         return response()->json([
    //             'Success' => false,
    //             'Message' => 'Some rows failed to insert',
    //             'failedRows' => asset(Storage::url($failedCsvPath)),
    //             'code' => 500
    //         ], 500);
    //     }

    //     return response()->json([
    //         'Success' => true,
    //         'Message' => 'All rows inserted successfully',
    //         'code' => 200
    //     ], 200);
    // }

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


    // function adddrcsv(Request $req)
    // {
    //     if (!$req->isMethod('post')) {
    //         return response()->json([
    //             'Success' => false,
    //             'Message' => 'Method Not Allowed.',
    //             'code' => 405
    //         ], 405);
    //     }

    //     $row = $req->all();
    //     $pid = $row['PHARMA_ID'];
    //     $pname = $row['PHARMA_NAME'];

    //     $validator = Validator::make($req->all(), [
    //         'file' => 'required|mimes:csv',
    //     ]);

    //     if ($validator->fails()) {
    //         $response = ['Success' => false, 'Message' => $validator->errors(), 'code' => 422];
    //         return response()->json($response, 422);
    //     }

    //     $file = $req->file('file');
    //     $data = array_map('str_getcsv', file($file));
    //     $data = array_slice($data, 1);

    //     $failedRows = [];

    //     foreach ($data as $input) {
    //         $fields_dr = [
    //             'DR_ID' => strtoupper(substr(md5($input[3] . $input[4]), 0, 15)),
    //             'DR_NAME' => $input[0] ?? null,
    //             'DR_MOBILE' => $input[1] ?? null,
    //             'SEX' => $input[2] ?? null,
    //             'REGN_NO' => $input[3] ?? null,
    //             'COUNCIL' => $input[4] ?? null,
    //             'ASSOCIATED' => $input[5] ?? null,
    //             'DESIGNATION' => $input[6] ?? null,
    //             'QUALIFICATION' => $input[7] ?? null,
    //             'D_CATG' => $input[8] ?? null,
    //             'EXPERIENCE' => $input[9] ?? null,
    //             'LANGUAGE' => $input[10] ?? null,
    //             'REPORTING_DAY' => $input[11] ?? null,
    //         ];
    //         $fields_sch = [
    //             "DR_ID" => strtoupper(substr(md5($input[3] . $input[4]), 0, 15)),
    //             "SCH_ID" => strtoupper(substr(md5($input[3] . $input[4]), 0, 15)),
    //             "DR_FEES" => $input[12] ?? 0,
    //             "PHARMA_ID" => $pid,
    //             "PHARMA_NAME" => $pname,
    //         ];

    //         try {
    //             DB::table('drprofile')->insert($fields_dr);

    //         } catch (\Exception $e) {
    //             $failedRows[] = $input;
    //         }
    //     }

    //     if (count($failedRows) > 0) {
    //         // Save failed rows to a CSV file

    //         $failedCsvPath = asset(storage::url('app/drprofile/failed_rows.csv'));
    //         $file = fopen(storage_path("app/$failedCsvPath"), 'w');
    //         foreach ($failedRows as $failedRow) {
    //             fputcsv($file, $failedRow);
    //         }
    //         fclose($file);

    //         return response()->json([
    //             'Success' => false,
    //             'Message' => 'Some rows failed to insert',
    //             'failedRows' => Storage::url($failedCsvPath),
    //             'code' => 500
    //         ], 500);
    //     }

    //     return response()->json([
    //         'Success' => true,
    //         'Message' => 'All rows inserted successfully',
    //         'code' => 200
    //     ], 200);
    // }

    function admopd(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $req->json()->all();
            if (isset($input['PHARMAID'])) {
                $pharmaid = $input['PHARMAID'];
                $date = Carbon::now();
                $weekNumber = $date->weekOfMonth;
                $day1 = date('l');

                $response = array();
                $data = array();

                $dcat['specialist'] = DB::table('dis_catg')
                    ->join('dr_availablity', 'dis_catg.DIS_ID', '=', 'dr_availablity.DIS_ID')
                    ->select(
                        'dis_catg.DIS_ID',
                        'dis_catg.DIS_CATEGORY',
                        'dis_catg.SPECIALIST',
                        'dis_catg.SPECIALITY',
                        'dis_catg.PHOTO_URL',
                        DB::raw('count(*) as TOTAL')
                    )
                    ->where('dr_availablity.PHARMA_ID', '=', $pharmaid)
                    ->groupBy(
                        'dis_catg.DIS_ID',
                        'dis_catg.DIS_CATEGORY',
                        'dis_catg.SPECIALIST',
                        'dis_catg.SPECIALITY',
                        'dis_catg.PHOTO_URL',
                    )
                    ->orderby('dis_catg.DIS_SL')
                    ->get();

                $arr['total_doctor'] = DB::table('drprofile')
                    ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
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
                    )
                    ->where('dr_availablity.PHARMA_ID', '=', $pharmaid)
                    ->where('drprofile.APPROVE', 'true')
                    ->get();

                $symp['symptoms'] = DB::table('symptoms')
                    ->select('SYM_ID', 'DIS_ID', 'SYM_NAME', 'DIS_CATEGORY', 'DASH_PHOTO AS PHOTO_URL', 'DESCRIPTION', 'STATUS', 'SYM_SL', 'SYM_TYPE')->where('STATUS', 'Active')->orderby('SYM_SL')->take(10)->get();

                $data1 = DB::table('drprofile')
                    ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
                    ->distinct('dr_availablity.DR_ID')
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
                        'dr_availablity.DR_FEES',
                        'dr_availablity.CHK_IN_STATUS',
                        'dr_availablity.CHK_IN_TIME',
                        'dr_availablity.CHK_OUT_TIME',
                        'dr_availablity.MAX_BOOK',
                        'dr_availablity.ID',
                        'dr_availablity.CHEMBER_NO'
                    )
                    ->where(['dr_availablity.SCH_DAY' => $day1, 'dr_availablity.PHARMA_ID' => $pharmaid])
                    ->where('WEEK', 'like', '%' . $weekNumber . '%')
                    ->where('drprofile.APPROVE', 'true')
                    ->distinct()
                    ->get();

                $tddr = [];
                foreach ($data1 as $row) {
                    $ct = DB::table('appointment')->where('APPNT_ID', $row->ID)->count();
                    $tddr['today_doctor'][] = [
                        "DR_ID" => $row->DR_ID,
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
                        "CHK_IN_STATUS" => $row->CHK_IN_STATUS,
                        "CHK_IN_TIME" => $row->CHK_IN_TIME,
                        "CHK_OUT_TIME" => $row->CHK_OUT_TIME,
                        "AVAILABLE" => $row->MAX_BOOK - $ct,
                        "CHEMBER_NO" => $row->CHEMBER_NO
                    ];
                }
                $data = $dcat + $tddr + $symp + $arr;

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

    // function admcatgclinicdr(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         date_default_timezone_set('Asia/Kolkata');
    //         $input   = $req->json()->all();
    //         if (isset($input['DIS_ID']) && isset($input['PHARMA_ID'])) {
    //             $did = $input['DIS_ID'];
    //             $pid = $input['PHARMA_ID'];

    //             $arr_A['Banner'] = DB::table('promo_banner')
    //                 ->select('PROMO_ID', 'HEADER_NAME', 'PHARMA_ID', 'MOBILE_NO', 'DIS_ID', 'SYM_ID', 'PKG_ID', 'PROMO_NAME', 'DESCRIPTION', 'PROMO_URL', 'PROMO_DT', 'PROMO_VALID')
    //                 ->where('DASH_SECTION_ID', '=', 'SP')->get();

    //             $drcl = DB::table('dr_availablity')
    //                 ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
    //                 ->distinct('drprofile.DR_ID')
    //                 ->select(
    //                     'drprofile.DR_ID',
    //                     'drprofile.DR_NAME',
    //                     'drprofile.DR_MOBILE',
    //                     'drprofile.SEX',
    //                     'drprofile.DESIGNATION',
    //                     'drprofile.QUALIFICATION',
    //                     'drprofile.D_CATG',
    //                     'drprofile.EXPERIENCE',
    //                     'drprofile.LANGUAGE',
    //                     'drprofile.PHOTO_URL AS DR_PHOTO',
    //                     'dr_availablity.PHARMA_ID',
    //                     'dr_availablity.DR_FEES',
    //                     'dr_availablity.WEEK',
    //                     'dr_availablity.SCH_DAY',
    //                     'dr_availablity.START_MONTH',
    //                     'dr_availablity.MONTH',
    //                     'dr_availablity.SCH_STATUS',
    //                     'dr_availablity.CHK_IN_STATUS',
    //                 )
    //                 ->where(['dr_availablity.PHARMA_ID' => $pid, 'drprofile.DIS_ID' => $did])
    //                 ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
    //                 ->where('drprofile.APPROVE', 'true')
    //                 ->get();

    //             // RETURN $drcl;

    //             $earliestDates = [];
    //             foreach ($drcl as $row) {
    //                 if (is_numeric($row->SCH_DAY)) {
    //                     $currentYear = date("Y");
    //                     $startDate = new DateTime("{$currentYear}-$row->START_MONTH-$row->SCH_DAY");
    //                     for ($i = 0; $i < 6; $i++) {
    //                         $dates = [];
    //                         $dates = $startDate->format('Ymd');
    //                         $cym = date('Ymd');

    //                         $startDate->modify('+' . $row->MONTH . 'months');
    //                         if ($dates >= $cym) {
    //                             $earliestDates[] = [
    //                                 "DR_ID" => $row->DR_ID,
    //                                 "DR_NAME" => $row->DR_NAME,
    //                                 "DR_MOBILE" => $row->DR_MOBILE,
    //                                 "SEX" => $row->SEX,
    //                                 "DESIGNATION" => $row->DESIGNATION,
    //                                 "QUALIFICATION" => $row->QUALIFICATION,
    //                                 "D_CATG" => $row->D_CATG,
    //                                 "EXPERIENCE" => $row->EXPERIENCE,
    //                                 "DR_PHOTO" => $row->DR_PHOTO,
    //                                 "DR_FEES" => $row->DR_FEES,
    //                                 "AVAILABLE_DT" =>  $dates,
    //                                 "AVAILABLE_STATUS" =>  $row->CHK_IN_STATUS
    //                             ];
    //                             if (!empty($earliestDates)) {
    //                                 break;
    //                             }
    //                         } else {
    //                             continue;
    //                         }
    //                     }
    //                 } else {
    //                     $data1 = [];
    //                     $dayOrder = ['Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6];
    //                     $fltr_arr = $drcl->filter(function ($item) use ($row) {
    //                         return $item->DR_ID === $row->DR_ID && $item->PHARMA_ID === $row->PHARMA_ID && $item->SCH_STATUS === 'Regular';
    //                     });
    //                     $dravail = $fltr_arr->map(function ($item) use ($dayOrder) {
    //                         $dayNum = $dayOrder[$item->SCH_DAY] ?? -1;
    //                         return [
    //                             "SCH_DAY_NUM" => $dayNum,
    //                             "SCH_DAY" => $item->SCH_DAY,
    //                             "WEEK" => $item->WEEK,
    //                             "CHK_IN_STATUS" => $item->CHK_IN_STATUS,
    //                         ];
    //                     });
    //                     $sortedDravail = $dravail->sortBy('SCH_DAY_NUM');
    //                     $firstItem = $sortedDravail->first();

    //                     $startDate = carbon::now();
    //                     $endDate = $startDate->copy()->addMonths(1)->endOfMonth();
    //                     $currentDate = $startDate;

    //                     while ($currentDate->lte($endDate)) {
    //                         $schday = "is" . $firstItem['SCH_DAY'];
    //                         $string = $firstItem['WEEK'];
    //                         $array = explode(",", $string);
    //                         $avl = $firstItem['CHK_IN_STATUS'];
    //                         if ($currentDate->$schday()) {
    //                             $dateString = $currentDate->toDateString();
    //                             $date = Carbon::createFromFormat('Y-m-d', $dateString);
    //                             $formattedDate = $date->format('Ymd');
    //                             foreach ($array as $value) {
    //                                 if ($date->weekOfMonth == $value) {
    //                                     $data1 = [
    //                                         "DR_ID" => $row->DR_ID,
    //                                         "DR_NAME" => $row->DR_NAME,
    //                                         "DR_MOBILE" => $row->DR_MOBILE,
    //                                         "SEX" => $row->SEX,
    //                                         "DESIGNATION" => $row->DESIGNATION,
    //                                         "QUALIFICATION" => $row->QUALIFICATION,
    //                                         "D_CATG" => $row->D_CATG,
    //                                         "EXPERIENCE" => $row->EXPERIENCE,
    //                                         "DR_PHOTO" => $row->DR_PHOTO,
    //                                         "DR_FEES" => $row->DR_FEES,
    //                                         "AVAILABLE_DT" =>  $formattedDate,
    //                                         "AVAILABLE_STATUS" =>  $avl
    //                                     ];
    //                                 }
    //                             }
    //                         }
    //                         $currentDate->addDay();
    //                         if (!empty($data1)) {
    //                             $doctorId = $data1['DR_ID'];
    //                             $availableDt = $data1['AVAILABLE_DT'];
    //                             if (!isset($earliestDates[$doctorId]) || $availableDt < $earliestDates[$doctorId]['AVAILABLE_DT']) {
    //                                 $earliestDates[$doctorId] = $data1;
    //                             }
    //                             break;
    //                         }
    //                     }
    //                     $distinctArray = array_unique($data1);
    //                     if (!empty($distinctArray)) {
    //                         $data[] = $distinctArray;
    //                     }
    //                 }
    //             }
    //             $arr_dr['doctors'] = array_values($earliestDates);
    //             usort($arr_dr['doctors'], function ($a, $b) {
    //                 return $a['AVAILABLE_DT'] <=> $b['AVAILABLE_DT'];
    //             });

    //             $data = $arr_dr  + $arr_A;
    //             $response = ['Success' => true, 'data' => $data, 'code' => 200];
    //         } else {
    //             $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
    //     }
    //     return $response;
    // }

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

    // private function getCatgDrDt($pharmaId, $did)
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
    //             'drprofile.COUNCIL AS NMC_NAME',
    //             'drprofile.D_CATG',
    //             'drprofile.EXPERIENCE',
    //             'drprofile.LANGUAGE',
    //             'drprofile.PHOTO_URL',
    //             'dr_availablity.CHEMBER_NO'
    //         )
    //         ->distinct('DR_ID')
    //         ->where(['dr_availablity.PHARMA_ID' => $pharmaId, 'dr_availablity.DIS_ID' => $did])
    //         ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
    //         ->where('drprofile.APPROVE', 'true')
    //         ->get();

    //     $DRSCH = [];
    //     $cym = date('Ymd');

    //     foreach ($totdr as $row1) {
    //         $dravail = DB::table('dr_availablity')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pharmaId])->get();
    //         $totapp = DB::table('appointment')->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pharmaId])->get();
    //         $data = [];

    //         foreach ($dravail as $row) {
    //             if (is_numeric($row->SCH_DAY)) {
    //                 $currentYear = date("Y");
    //                 $startDate = new DateTime("{$currentYear}-$row->START_MONTH-$row->SCH_DAY");
    //                 for ($i = 0; $i < 12; $i++) {
    //                     $dates = [];
    //                     $bookingStartDate = Carbon::createFromFormat('Ymd', $dates)->addDays(-$row->BOOK_ST_DT);
    //                     $formattedBookingDate = $bookingStartDate->format('Ymd');
    //                     $dates = $startDate->format('Ymd');
    //                     $schday = $startDate->format('l');

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
    //         $firstAvailable = $collection->firstWhere('SLOT_STATUS', 'Available');
    //         if ($firstAvailable) {
    //             $firstAvailableIndex = $collection->search($firstAvailable);
    //             $sixRows = array_slice($data, $firstAvailableIndex, 1);
    //         }

    //         $DRSCH[] = [
    //             "DR_ID" => $row1->DR_ID,
    //             "DR_NAME" => $row1->DR_NAME,
    //             "DR_MOBILE" => $row1->DR_MOBILE,
    //             "SEX" => $row1->SEX,
    //             "DESIGNATION" => $row1->DESIGNATION,
    //             "QUALIFICATION" => $row1->QUALIFICATION,
    //             "UID_NMC" => $row1->UID_NMC,
    //             "REGN_NO" => $row1->REGN_NO,
    //             "NMC_NAME" => $row1->NMC_NAME,
    //             "D_CATG" => $row1->D_CATG,
    //             "EXPERIENCE" => $row1->EXPERIENCE,
    //             "DR_FEES" => $row->DR_FEES,
    //             "LANGUAGE" => $row1->LANGUAGE,
    //             "DR_PHOTO" => $row1->PHOTO_URL,
    //             "SCH_DT" => $sixRows[0]['SCH_DT'],
    //             "DR_STATUS" => $sixRows[0]['DR_STATUS'],
    //             "CHEMBER_NO" => $row1->CHEMBER_NO,
    //         ];
    //     }
    //     return $DRSCH;
    // }
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
                // DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                //     * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                //     * SIN(RADIANS('$latt'))))),0) as KM"),
            )
            ->distinct('DR_ID')
            ->where(['dr_availablity.PHARMA_ID' => $pharmaId, 'dr_availablity.DIS_ID' => $did])
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
                    "DR_FEES" => $row->DR_FEES,
                    // "KM" => $row1->KM,
                    "AVAILABLE_DT" => $sixRows[0]['SCH_DT'],
                    "SLOT_STATUS" => $sixRows[0]['SLOT_STATUS'],
                    "DR_STATUS" => $sixRows[0]['DR_STATUS'],
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

    function phopdweb(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $data = collect();

            $data = $data->merge($this->getSpecialist($pharmaId));
            $data = $data->merge($this->getTodayDoctor($pharmaId));
            $data = $data->merge($this->getTotalDoctor($pharmaId));
            $data = $data->merge($this->getSymptoms($pharmaId));

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
        $data['specialist'] = DB::table('dis_catg')
            ->join('dr_availablity', 'dis_catg.DIS_ID', '=', 'dr_availablity.DIS_ID')
            ->select(
                'dis_catg.DIS_ID',
                'dis_catg.DIS_CATEGORY',
                'dis_catg.SPECIALIST',
                'dis_catg.SPECIALITY',
                'dis_catg.PHOTO_URL',
                DB::raw('count(*) as TOTAL')
            )
            ->where('dr_availablity.PHARMA_ID', '=', $pharmaId)
            ->groupBy(
                'dis_catg.DIS_ID',
                'dis_catg.DIS_CATEGORY',
                'dis_catg.SPECIALIST',
                'dis_catg.SPECIALITY',
                'dis_catg.PHOTO_URL',
            )
            ->orderby('dis_catg.DIS_SL')
            ->get();
        return $data;
    }

    private function getSpecialist1($pharmaId)
    {
        $data = [];
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
                "FIELD_TYPE" => "Specialist",
                "DETAILS" => $spdtl['DETAILS']
            ];
            array_push($data, $sp);
        }
        return $data;
    }

    // private function getTodayDoctor($pharmaId)
    // {
    //     $data = [];
    //     $date = Carbon::now();
    //     $weekNumber = $date->weekOfMonth;
    //     $day1 = date('l');

    //     $doctors = DB::table('drprofile')
    //         ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
    //         ->distinct('dr_availablity.DR_ID')
    //         ->select(
    //             'drprofile.DR_ID',
    //             'drprofile.DR_NAME',
    //             'drprofile.DR_MOBILE',
    //             'drprofile.SEX',
    //             'drprofile.DESIGNATION',
    //             'drprofile.QUALIFICATION',
    //             'drprofile.D_CATG',
    //             'drprofile.EXPERIENCE',
    //             'drprofile.REGN_NO',
    //             'drprofile.UID_NMC',
    //             'drprofile.COUNCIL AS NMC_NAME',
    //             'drprofile.LANGUAGE',
    //             'drprofile.PHOTO_URL',
    //             'dr_availablity.PHARMA_ID',
    //             'dr_availablity.DR_FEES',
    //             'dr_availablity.CHK_IN_STATUS',
    //             'dr_availablity.CHK_IN_TIME',
    //             'dr_availablity.CHK_OUT_TIME',
    //             'dr_availablity.CHEMBER_NO',
    //             'dr_availablity.DR_ARRIVE',

    //         )
    //         ->where(['dr_availablity.SCH_DAY' => $day1, 'dr_availablity.PHARMA_ID' => $pharmaId])
    //         ->where('WEEK', 'like', '%' . $weekNumber . '%')
    //         ->where('dr_availablity.CHK_IN_STATUS', '<>', 'OUT')
    //         ->where('drprofile.APPROVE', 'true')

    //         ->get();

    //     $data['today_doctors'] = $doctors->map(function ($doctor) {
    //         $SCH_DT = $this->getSchDt($doctor->DR_ID, $doctor->PHARMA_ID);
    //         return [
    //             "DR_ID" => $doctor->DR_ID,
    //             "DR_NAME" => $doctor->DR_NAME,
    //             "DR_MOBILE" => $doctor->DR_MOBILE,
    //             "SEX" => $doctor->SEX,
    //             "DESIGNATION" => $doctor->DESIGNATION,
    //             "QUALIFICATION" => $doctor->QUALIFICATION,
    //             "D_CATG" => $doctor->D_CATG,
    //             "EXPERIENCE" => $doctor->EXPERIENCE,
    //             "REGN_NO" => $doctor->REGN_NO,
    //             "UID_NMC" => $doctor->UID_NMC,
    //             "NMC_NAME" => $doctor->NMC_NAME,
    //             "LANGUAGE" => $doctor->LANGUAGE,
    //             "DR_PHOTO" => $doctor->PHOTO_URL,
    //             "DR_FEES" => $doctor->DR_FEES,
    //             "DR_STATUS" => $doctor->CHK_IN_STATUS,
    //             "DR_ARRIVE" => $doctor->DR_ARRIVE,
    //             "CHEMBER_NO" => $doctor->CHEMBER_NO,
    //             "SCH_DT" => $SCH_DT
    //         ];
    //     });

    //     return $data;
    // }

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
            ->where(['dr_availablity.SCH_DAY' => $day1])
            ->where('WEEK', 'like', '%' . $weekNumber . '%')
            // ->orWhere('dr_availablity.SCH_DT', $cdy)
            ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
            ->orderby('dr_availablity.CHK_IN_TIME')

            // ->orderbyraw('KM')
            ->get();

        $ldr = [];
        foreach ($data1 as $row) {
            // if (is_numeric($row->SCH_DAY)) {
            //     $date = Carbon::createFromDate(date('Y'), $row->START_MONTH, $row->SCH_DAY)
            //         ->addMonths($row->MONTH);
            //     if ($date->format('Ymd') === $cdt) {
            //         $sch_dt = $date->format('Ymd');
            //     }
            // } else {
            //     $sch_dt = Carbon::now()->format('Ymd');
            // }
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
            $SCH_DT = $this->getSchDt($doctor->DR_ID, $doctor->PHARMA_ID);
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
                "SCH_DT" => $SCH_DT,
                "DR_STATUS" => $doctor->CHK_IN_STATUS,
                "DR_ARRIVE" => $doctor->DR_ARRIVE,
                "CHEMBER_NO" => $doctor->CHEMBER_NO,

            ];
        });

        return $data;
    }

    // private function getTotalDoctor($pharmaId)
    // {
    //     $cday = now()->format('l');
    //     $doctors = DB::table('drprofile')
    //         ->leftJoin('dr_availablity', function ($join) use ($cday) {
    //             $join->on('dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
    //                 ->where('dr_availablity.SCH_DAY', '>=', $cday);
    //         })
    //         ->select([
    //             'drprofile.DR_ID',
    //             'drprofile.DR_NAME',
    //             'drprofile.DR_MOBILE',
    //             'drprofile.SEX',
    //             'drprofile.DESIGNATION',
    //             'drprofile.QUALIFICATION',
    //             'drprofile.D_CATG',
    //             'drprofile.EXPERIENCE',
    //             'drprofile.LANGUAGE',
    //             'drprofile.PHOTO_URL',
    //             'drprofile.REGN_NO',
    //             'drprofile.UID_NMC',
    //             'drprofile.COUNCIL AS NMC_NAME',
    //             'dr_availablity.PHARMA_ID',
    //             'dr_availablity.SCH_DAY',
    //             'dr_availablity.DR_FEES',
    //             'dr_availablity.CHK_IN_STATUS',
    //             'dr_availablity.CHEMBER_NO',
    //             'dr_availablity.DR_ARRIVE',
    //         ])
    //         ->where('dr_availablity.PHARMA_ID', $pharmaId)
    //         ->where('drprofile.APPROVE', 'true')
    //         ->distinct('drprofile.DR_ID')
    //         ->orderBy('dr_availablity.SCH_DAY', 'asc')
    //         ->get();

    //     // return $doctors;


    //     $data['total_doctors'] = $doctors->map(function ($doctor) {
    //         $SCH_DT = $this->getSchDt($doctor->DR_ID, $doctor->PHARMA_ID);
    //         return [
    //             "DR_ID" => $doctor->DR_ID,
    //             "DR_NAME" => $doctor->DR_NAME,
    //             "DR_MOBILE" => $doctor->DR_MOBILE,
    //             "SEX" => $doctor->SEX,
    //             "DESIGNATION" => $doctor->DESIGNATION,
    //             "QUALIFICATION" => $doctor->QUALIFICATION,
    //             "D_CATG" => $doctor->D_CATG,
    //             "EXPERIENCE" => $doctor->EXPERIENCE,
    //             "LANGUAGE" => $doctor->LANGUAGE,
    //             "REGN_NO" => $doctor->REGN_NO,
    //             "UID_NMC" => $doctor->UID_NMC,
    //             "NMC_NAME" => $doctor->NMC_NAME,
    //             "DR_PHOTO" => $doctor->PHOTO_URL,
    //             "DR_FEES" => $doctor->DR_FEES,
    //             "SCH_DT" => $SCH_DT,
    //             "DR_STATUS" => $doctor->CHK_IN_STATUS,
    //             "DR_ARRIVE" => $doctor->DR_ARRIVE,
    //             "CHEMBER_NO" => $doctor->CHEMBER_NO,

    //         ];
    //     });

    //     return $data;
    // }

    private function getSchDt($drId, $pid)
    {
        $sixRows = [];
        $dravail = DB::table('dr_availablity')->where(['DR_ID' => $drId, 'PHARMA_ID' => $pid])->get();
        $totapp = DB::table('appointment')->where(['DR_ID' => $drId, 'PHARMA_ID' => $pid])->get();
        foreach ($dravail as $row) {
            if (is_numeric($row->SCH_DAY)) {
                $currentYear = date("Y");
                $startDate = new DateTime("{$currentYear}-$row->START_MONTH-$row->SCH_DAY");

                for ($i = 0; $i < 12; $i++) {
                    $dates = [];
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
                        if ($row->MAX_BOOK - $totappct == 0) {
                            $book_sts = "Closed";
                        } else {
                            $book_sts = "Available";
                        }
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
                            "AVAILABLE" => $row->MAX_BOOK - $totappct,
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
                                "CHEMBER_NO" => $row->CHEMBER_NO,
                                "MAX_BOOK" => $row->MAX_BOOK,
                                "AVAILABLE" => $row->MAX_BOOK - $totappct,
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
            return $item['DR_STATUS'] === 'IN' || $item['DR_STATUS'] === 'TIMELY';
        });
        if ($firstAvailable) {
            $firstAvailableIndex = $collection->search($firstAvailable);
            $sixRows = array_slice($data, $firstAvailableIndex, 6);
        }
        return $sixRows;
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

    // function vu_facilities2(Request $req)
    // {
    //     // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {           
    //     if ($req->isMethod('post')) {
    //         $input = $req->json()->all();

    //         $pharmaId = $input['PHARMA_ID'];
    //         $data1 = DB::table('dashboard')
    //             ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId) {
    //                 $join->on('dashboard.DASH_ID', '=', 'hospital_facilities_details.DASH_ID')
    //                     ->where('hospital_facilities_details.PHARMA_ID', '=', $pharmaId);
    //             })
    //             ->select(
    //                 'dashboard.DASH_SECTION_ID',
    //                 'dashboard.DASH_SECTION_NAME',
    //                 'dashboard.PHOTO_URL',
    //                 'dashboard.DASH_TYPE',
    //                 'dashboard.DASH_DESCRIPTION',
    //                 'dashboard.GR_DESC',
    //                 'dashboard.DASH_ID',
    //                 'dashboard.DIS_ID',
    //                 'dashboard.SYM_ID',
    //                 'dashboard.DASH_NAME',
    //                 'hospital_facilities_details.UID',
    //                 'hospital_facilities_details.TOT_BED',
    //                 'hospital_facilities_details.AVAIL_BED',
    //                 'hospital_facilities_details.PRICE_FROM',
    //                 'hospital_facilities_details.DEPT_PH',
    //                 'hospital_facilities_details.SHORT_NOTE',
    //                 'hospital_facilities_details.IMAGE1_URL',
    //                 'hospital_facilities_details.IMAGE2_URL',
    //                 'hospital_facilities_details.IMAGE3_URL',
    //                 'hospital_facilities_details.REMARK',
    //                 'hospital_facilities_details.FREE_AREA',
    //                 'hospital_facilities_details.SERV_CRG',
    //             )
    //             ->where('dashboard.CATEGORY', 'like', '%' . 'H' . '%')
    //             ->where('dashboard.DASH_SECTION_ID', '<>', 'AK')
    //             ->where('dashboard.STATUS', 'Active')
    //             ->get();


    //         // return $data1;

    //         $groupedData = [];
    //         foreach ($data1 as $row2) {
    //             if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
    //                 $groupedData[$row2->DASH_SECTION_ID] = [
    //                     "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
    //                     "DESCRIPTION" => $row2->GR_DESC,
    //                     "PHOTO_URL" => $row2->PHOTO_URL,
    //                     "DASH_TYPE" => []
    //                 ];
    //             }

    //             if (!isset($groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE])) {
    //                 $groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE] = [
    //                     "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
    //                     "DASH_TYPE" => $row2->DASH_TYPE,
    //                     "DESCRIPTION" => $row2->GR_DESC,
    //                     "PHOTO_URL" => $row2->PHOTO_URL,
    //                     "FACILITY" => []
    //                 ];
    //             }

    //             $groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE]['FACILITY'][] = [
    //                 "UID" => $row2->UID,
    //                 "DASH_ID" => $row2->DASH_ID,
    //                 "DIS_ID" => $row2->DIS_ID,
    //                 "SYM_ID" => $row2->SYM_ID,
    //                 "DASH_NAME" => $row2->DASH_NAME,
    //                 "DESCRIPTION" => $row2->DASH_DESCRIPTION,
    //                 "TOT_BED" => $row2->TOT_BED,
    //                 "AVAIL_BED" => $row2->AVAIL_BED,
    //                 "PRICE_FROM" => $row2->PRICE_FROM,
    //                 "DEPT_PH" => $row2->DEPT_PH,
    //                 "SHORT_NOTE" => $row2->SHORT_NOTE,
    //                 "IMAGE1_URL" => $row2->IMAGE1_URL,
    //                 "IMAGE2_URL" => $row2->IMAGE2_URL,
    //                 "IMAGE3_URL" => $row2->IMAGE3_URL,
    //                 "REMARK" => $row2->REMARK,
    //                 "FREE_AREA" => $row2->FREE_AREA,
    //                 "SERV_CRG" => $row2->SERV_CRG,
    //                 "PHOTO_URL" => $row2->PHOTO_URL,
    //             ];
    //         }

    //         $data2 = DB::table('surgery')
    //             ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId) {
    //                 $join->on('surgery.DASH_ID', '=', 'hospital_facilities_details.DASH_SECTION_ID')
    //                     ->where('hospital_facilities_details.PHARMA_ID', '=', $pharmaId);
    //             })
    //             ->select(
    //                 'surgery.DASH_ID as DASH_SECTION_ID',
    //                 // 'dashboard.DASH_SECTION_NAME',
    //                 'surgery.PHOTO_URL',
    //                 'surgery.SURG_TYPE AS DASH_TYPE',
    //                 'surgery.DESCRIPTION AS DASH_DESCRIPTION',
    //                 'surgery.TYPE_DESC AS GR_DESC',
    //                 'surgery.SURG_ID AS DASH_ID',
    //                 'surgery.DIS_ID',
    //                 // 'dashboard.SYM_ID',
    //                 'surgery.SURG_NAME AS DASH_NAME',
    //                 'hospital_facilities_details.UID',
    //                 'hospital_facilities_details.TOT_BED',
    //                 'hospital_facilities_details.AVAIL_BED',
    //                 'hospital_facilities_details.PRICE_FROM',
    //                 'hospital_facilities_details.DEPT_PH',
    //                 'hospital_facilities_details.SHORT_NOTE',
    //                 'hospital_facilities_details.IMAGE1_URL',
    //                 'hospital_facilities_details.IMAGE2_URL',
    //                 'hospital_facilities_details.IMAGE3_URL',
    //                 'hospital_facilities_details.REMARK',
    //                 'hospital_facilities_details.FREE_AREA',
    //                 'hospital_facilities_details.SERV_CRG',
    //             )
    //             ->get();


    //         // return $data2;

    //         $groupedData1 = [];
    //         foreach ($data2 as $row3) {
    //             if (!isset($groupedData1[$row3->DASH_SECTION_ID])) {
    //                 $groupedData1[$row3->DASH_SECTION_ID] = [
    //                     "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => 'SURGERY',
    //                     "DESCRIPTION" => $row3->GR_DESC,
    //                     "PHOTO_URL" => $row3->PHOTO_URL,
    //                     "DASH_TYPE" => []
    //                 ];
    //             }

    //             if (!isset($groupedData1[$row3->DASH_SECTION_ID]['DASH_TYPE'][$row3->DASH_TYPE])) {
    //                 $groupedData1[$row3->DASH_SECTION_ID]['DASH_TYPE'][$row3->DASH_TYPE] = [
    //                     "DASH_SECTION_ID" => $row3->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => 'SURGERY',
    //                     "DASH_TYPE" => $row3->DASH_TYPE,
    //                     "DESCRIPTION" => $row3->GR_DESC,
    //                     "PHOTO_URL" => $row3->PHOTO_URL,
    //                     "FACILITY" => []
    //                 ];
    //             }

    //             $groupedData1[$row3->DASH_SECTION_ID]['DASH_TYPE'][$row3->DASH_TYPE]['FACILITY'][] = [
    //                 "UID" => $row3->UID,
    //                 "DASH_ID" => $row3->DASH_ID,
    //                 "DIS_ID" => $row3->DIS_ID,
    //                 // "SYM_ID" => $row3->SYM_ID,
    //                 "DASH_NAME" => $row3->DASH_NAME,
    //                 "DESCRIPTION" => $row3->DASH_DESCRIPTION,
    //                 "TOT_BED" => $row3->TOT_BED,
    //                 "AVAIL_BED" => $row3->AVAIL_BED,
    //                 "PRICE_FROM" => $row3->PRICE_FROM,
    //                 "DEPT_PH" => $row3->DEPT_PH,
    //                 "SHORT_NOTE" => $row3->SHORT_NOTE,
    //                 "IMAGE1_URL" => $row3->IMAGE1_URL,
    //                 "IMAGE2_URL" => $row3->IMAGE2_URL,
    //                 "IMAGE3_URL" => $row3->IMAGE3_URL,
    //                 "REMARK" => $row3->REMARK,
    //                 "FREE_AREA" => $row3->FREE_AREA,
    //                 "SERV_CRG" => $row3->SERV_CRG,
    //                 "PHOTO_URL" => $row3->PHOTO_URL,
    //             ];
    //         }

    //         $data = array_merge(array_values($groupedData), array_values($groupedData1));
    //         if ($data == null) {
    //             $response = ['Success' => false, 'Message' => 'Record not found', 'code' => 200];
    //         } else {
    //             $response = ['Success' => true, 'data' => $data, 'code' => 200];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
    //     }
    //     return $response;
    // }


    // function vu_facilities1(Request $req)
    // {
    //     if ($req->isMethod('post')) {
    //         $input = $req->json()->all();

    //         $pharmaId = $input['PHARMA_ID'];
    //         $dsid = $input['DASH_SECTION_ID'];
    //         $data1 = DB::table('dashboard')
    //             ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId, $dsid) {
    //                 $join->on('dashboard.DASH_ID', '=', 'hospital_facilities_details.DASH_ID')
    //                     ->where(['hospital_facilities_details.PHARMA_ID' => $pharmaId]);
    //             })
    //             ->select(
    //                 'dashboard.DASH_SECTION_ID',
    //                 'dashboard.DASH_SECTION_NAME',
    //                 'dashboard.PHOTO_URL',
    //                 'dashboard.DASH_TYPE',
    //                 'dashboard.DASH_DESCRIPTION',
    //                 'dashboard.GR_DESC',
    //                 'dashboard.DASH_ID',
    //                 'dashboard.DIS_ID',
    //                 'dashboard.SYM_ID',
    //                 'dashboard.DASH_NAME',
    //                 'hospital_facilities_details.UID',
    //                 'hospital_facilities_details.TOT_BED',
    //                 'hospital_facilities_details.AVAIL_BED',
    //                 'hospital_facilities_details.PRICE_FROM',
    //                 'hospital_facilities_details.DEPT_PH',
    //                 'hospital_facilities_details.SHORT_NOTE',
    //                 'hospital_facilities_details.FREE_AREA',
    //                 'hospital_facilities_details.FREE_FROM',
    //                 'hospital_facilities_details.FREE_TO',
    //                 'hospital_facilities_details.SERV_AREA',
    //                 'hospital_facilities_details.SERV_FROM',
    //                 'hospital_facilities_details.SERV_TO',
    //                 'hospital_facilities_details.SERV_CRG',
    //                 'hospital_facilities_details.DLV_TM',
    //                 'hospital_facilities_details.MIN_ODR',
    //                 'hospital_facilities_details.SERV_24X7',
    //                 'hospital_facilities_details.SERV_HOME',
    //                 'hospital_facilities_details.DISCOUNT',
    //                 'hospital_facilities_details.CASH_LESS',
    //                 'hospital_facilities_details.CASH_PAID',
    //                 'hospital_facilities_details.IMAGE1_URL',
    //                 'hospital_facilities_details.IMAGE2_URL',
    //                 'hospital_facilities_details.IMAGE3_URL',
    //                 'hospital_facilities_details.REMARK',
    //             )
    //             ->where('dashboard.CATEGORY', 'like', '%' . 'H' . '%')
    //             ->where('dashboard.DASH_SECTION_ID', '<>', 'AK')
    //             ->where('dashboard.STATUS', 'Active')
    //             ->where('dashboard.DASH_SECTION_ID', $dsid)
    //             ->get();

    //         $groupedData = [];
    //         foreach ($data1 as $row) {
    //             $typeKey = $row->DASH_TYPE;
    //             if (!isset($groupedData[$typeKey])) {
    //                 $groupedData[$typeKey] = [
    //                     "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
    //                     "DASH_TYPE" => $row->DASH_TYPE,
    //                     "DESCRIPTION" => $row->GR_DESC,
    //                     "PHOTO_URL" => $row->PHOTO_URL,
    //                     "FACILITY" => []
    //                 ];
    //             }

    //             $groupedData[$typeKey]['FACILITY'][] = [
    //                 "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
    //                 "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
    //                 "DASH_TYPE" => $row->DASH_TYPE,
    //                 "UID" => $row->UID,
    //                 "DASH_ID" => $row->DASH_ID,
    //                 "DIS_ID" => $row->DIS_ID,
    //                 "SYM_ID" => $row->SYM_ID,
    //                 "DASH_NAME" => $row->DASH_NAME,
    //                 "DESCRIPTION" => $row->DASH_DESCRIPTION,
    //                 "TOT_BED" => $row->TOT_BED,
    //                 "AVAIL_BED" => $row->AVAIL_BED,
    //                 "PRICE_FROM" => $row->PRICE_FROM,
    //                 "DEPT_PH" => $row->DEPT_PH,
    //                 "SHORT_NOTE" => $row->SHORT_NOTE,
    //                 "FREE_AREA" => $row->FREE_AREA,
    //                 "FREE_FROM" => $row->FREE_FROM,
    //                 "FREE_TO" => $row->FREE_TO,
    //                 "SERV_AREA" => $row->SERV_AREA,
    //                 "SERV_CRG" => $row->SERV_CRG,
    //                 "SERV_FROM" => $row->SERV_FROM,
    //                 "SERV_TO" => $row->SERV_TO,
    //                 "DLV_TM" => $row->DLV_TM,
    //                 "MIN_ODR" => $row->MIN_ODR,
    //                 "SERV_24X7" => $row->SERV_24X7,
    //                 "SERV_HOME" => $row->SERV_HOME,
    //                 "DISCOUNT" => $row->DISCOUNT,
    //                 "CASH_LESS" => $row->CASH_LESS,
    //                 "CASH_PAID" => $row->CASH_PAID,
    //                 "IMAGE1_URL" => $row->IMAGE1_URL,
    //                 "IMAGE2_URL" => $row->IMAGE2_URL,
    //                 "IMAGE3_URL" => $row->IMAGE3_URL,
    //                 "REMARK" => $row->REMARK,
    //                 "PHOTO_URL" => $row->PHOTO_URL,
    //             ];
    //         }

    //         $data2 = DB::table('surgery')
    //             ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId) {
    //                 $join->on('surgery.DASH_ID', '=', 'hospital_facilities_details.DASH_SECTION_ID')
    //                     ->where('hospital_facilities_details.PHARMA_ID', '=', $pharmaId);
    //             })
    //             ->select(
    //                 'surgery.DASH_ID as DASH_SECTION_ID',
    //                 // 'dashboard.DASH_SECTION_NAME',
    //                 'surgery.PHOTO_URL',
    //                 'surgery.SURG_TYPE AS DASH_TYPE',
    //                 'surgery.DESCRIPTION AS DASH_DESCRIPTION',
    //                 'surgery.TYPE_DESC AS GR_DESC',
    //                 'surgery.SURG_ID AS DASH_ID',
    //                 'surgery.DIS_ID',
    //                 // 'dashboard.SYM_ID',
    //                 'surgery.SURG_NAME AS DASH_NAME',
    //                 'hospital_facilities_details.UID',
    //                 'hospital_facilities_details.TOT_BED',
    //                 'hospital_facilities_details.AVAIL_BED',
    //                 'hospital_facilities_details.PRICE_FROM',
    //                 'hospital_facilities_details.DEPT_PH',
    //                 'hospital_facilities_details.SHORT_NOTE',
    //                 'hospital_facilities_details.FREE_AREA',
    //                 'hospital_facilities_details.FREE_FROM',
    //                 'hospital_facilities_details.FREE_TO',
    //                 'hospital_facilities_details.SERV_AREA',
    //                 'hospital_facilities_details.SERV_FROM',
    //                 'hospital_facilities_details.SERV_TO',
    //                 'hospital_facilities_details.SERV_CRG',
    //                 'hospital_facilities_details.DLV_TM',
    //                 'hospital_facilities_details.MIN_ODR',
    //                 'hospital_facilities_details.SERV_24X7',
    //                 'hospital_facilities_details.SERV_HOME',
    //                 'hospital_facilities_details.DISCOUNT',
    //                 'hospital_facilities_details.CASH_LESS',
    //                 'hospital_facilities_details.CASH_PAID',
    //                 'hospital_facilities_details.IMAGE1_URL',
    //                 'hospital_facilities_details.IMAGE2_URL',
    //                 'hospital_facilities_details.IMAGE3_URL',
    //                 'hospital_facilities_details.REMARK',
    //             )
    //             ->where('surgery.DASH_ID', $dsid)
    //             ->get();

    //         $groupedData1 = [];
    //         foreach ($data2 as $row1) {
    //             $typeKey = $row1->DASH_TYPE;
    //             if (!isset($groupedData1[$typeKey])) {
    //                 $groupedData1[$typeKey] = [
    //                     "DASH_SECTION_ID" => $row1->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => 'SURGERY',
    //                     "DASH_TYPE" => $row1->DASH_TYPE,
    //                     "DESCRIPTION" => $row1->GR_DESC,
    //                     "PHOTO_URL" => $row1->PHOTO_URL,
    //                     "FACILITY" => []
    //                 ];
    //             }

    //             $groupedData1[$typeKey]['FACILITY'][] = [
    //                 "DASH_SECTION_ID" => $row1->DASH_SECTION_ID,
    //                 "DASH_SECTION_NAME" => 'SURGERY',
    //                 "DASH_TYPE" => $row1->DASH_TYPE,
    //                 "UID" => $row1->UID,
    //                 "DASH_ID" => $row1->DASH_ID,
    //                 "DIS_ID" => $row1->DIS_ID,
    //                 // "SYM_ID" => $row1->SYM_ID,
    //                 "DASH_NAME" => $row1->DASH_NAME,
    //                 "DESCRIPTION" => $row1->DASH_DESCRIPTION,
    //                 "TOT_BED" => $row1->TOT_BED,
    //                 "AVAIL_BED" => $row1->AVAIL_BED,
    //                 "PRICE_FROM" => $row1->PRICE_FROM,
    //                 "DEPT_PH" => $row1->DEPT_PH,
    //                 "SHORT_NOTE" => $row1->SHORT_NOTE,
    //                 "FREE_AREA" => $row1->FREE_AREA,
    //                 "FREE_FROM" => $row1->FREE_FROM,
    //                 "FREE_TO" => $row1->FREE_TO,
    //                 "SERV_AREA" => $row1->SERV_AREA,
    //                 "SERV_CRG" => $row1->SERV_CRG,
    //                 "SERV_FROM" => $row1->SERV_FROM,
    //                 "SERV_TO" => $row1->SERV_TO,
    //                 "DLV_TM" => $row1->DLV_TM,
    //                 "MIN_ODR" => $row1->MIN_ODR,
    //                 "SERV_24X7" => $row1->SERV_24X7,
    //                 "SERV_HOME" => $row1->SERV_HOME,
    //                 "DISCOUNT" => $row1->DISCOUNT,
    //                 "CASH_LESS" => $row1->CASH_LESS,
    //                 "CASH_PAID" => $row1->CASH_PAID,
    //                 "IMAGE1_URL" => $row1->IMAGE1_URL,
    //                 "IMAGE2_URL" => $row1->IMAGE2_URL,
    //                 "IMAGE3_URL" => $row1->IMAGE3_URL,
    //                 "REMARK" => $row1->REMARK,
    //                 "PHOTO_URL" => $row1->PHOTO_URL,
    //             ];
    //         }

    //         $data = array_merge(array_values($groupedData), array_values($groupedData1));

    //         if (empty($data)) {
    //             $response = ['Success' => false, 'Message' => 'Record not found', 'code' => 200];
    //         } else {
    //             $response = ['Success' => true, 'data' => $data, 'code' => 200];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
    //     }
    //     return response()->json($response);
    // }


    function vuupdt_facilities(Request $req)
    {
        // if (isset($headers['Authorization']) && $headers['Authorization'] === $_SESSION['TOKEN']) {           
        if ($req->isMethod('post')) {
            $input = $req->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $DsID = $input['DASH_SECTION_ID'];
            if ($DsID === 'AM') {
                $DsID = 'SR';
            }
            $data = DB::table('dashboard')
                ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId) {
                    $join->on('dashboard.DASH_ID', '=', 'hospital_facilities_details.DASH_ID')
                        ->where('hospital_facilities_details.PHARMA_ID', '=', $pharmaId);
                })
                ->select(
                    'dashboard.DASH_SECTION_ID',
                    'dashboard.DASH_SECTION_NAME',
                    'dashboard.PHOTO_URL',
                    'dashboard.DASH_TYPE',
                    'dashboard.DASH_DESCRIPTION as DESCRIPTION',
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
                    'hospital_facilities_details.DEPT_PH1',
                    'hospital_facilities_details.SHORT_NOTE',
                    'hospital_facilities_details.SEC_SHORT_NOTE',
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
                    'hospital_facilities_details.DISCOUNT',
                    'hospital_facilities_details.CASH_LESS',
                    'hospital_facilities_details.CASH_PAID',
                    'hospital_facilities_details.CASH_BOTH',
                    'hospital_facilities_details.IMAGE1_URL',
                    'hospital_facilities_details.IMAGE2_URL',
                    'hospital_facilities_details.IMAGE3_URL',
                    'hospital_facilities_details.REMARK',
                )
                ->where([
                    'dashboard.DASH_SECTION_ID' => $DsID,
                    'dashboard.STATUS' => 'Active',
                    'dashboard.DASH_TYPE' => $input['DASH_TYPE']
                ])
                ->get();
            if ($input['DASH_SECTION_ID'] == 'AG') {
                $data->transform(function ($item) {
                    $item->DASH_TYPE = '24x7 ' . $item->DASH_TYPE;
                    $item->DASH_NAME = '24x7 ' . $item->DASH_NAME;
                    return $item;
                });
            }

            if ($data->isEmpty()) {
                $response = ['Success' => false, 'Message' => 'Record not found', 'code' => 404]; // Use appropriate HTTP status code
            } else {
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
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
                    // 'SCH_ID' => strtoupper(substr(md5($row['DR_ID'] . $row['PHARMA_ID'] . $row['SCH_DAY']), 0, 15)),
                    // 'DR_ID' => $row['DR_ID'] ?? NULL,
                    // 'DIS_ID' => $row['DIS_ID'],
                    // 'DR_FEES' => $row['DR_FEES'],
                    // 'PHARMA_ID' => $row['PHARMA_ID'],
                    // 'PHARMA_NAME' => $row['PHARMA_NAME'],
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

    function add_facilities(Request $req)
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

        $UID = strtoupper(substr(md5($input['PHARMA_ID'] . $input['DASH_ID']), 0, 10));
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
            'DIS_ID' => $input['DIS_ID'],
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
            'SEC_SHORT_NOTE' => $input['SEC_SHORT_NOTE'] ?? null,
            'FREE_AREA' => $input['FREE_AREA'] ?? null,
            'FREE_FROM' => $input['FREE_FROM'] ?? null,
            'FREE_TO' => $input['FREE_TO'] ?? null,
            'SERV_AREA' => $input['SERV_AREA'] ?? null,
            'SERV_FROM' => $input['SERV_FROM'] ?? null,
            'SERV_TO' => $input['SERV_TO'] ?? null,
            'SERV_CRG' => $input['SERV_CRG'] ?? null,
            'DLV_TM' => $input['DLV_TM'] ?? null,
            'MIN_ODR' => $input['MIN_ODR'] ?? null,
            'SERV_24X7' => $input['SERV_24X7'] ?? null,
            'SERV_HOME' => $input['SERV_HOME'] ?? null,
            'SERV_IP' => $input['SERV_IP'] ?? null,
            'TREATMENTS' => $input['TREATMENTS'] ?? null,
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
                    'Message' => 'Facilities added successfully.',
                    // 'data' =>  $fields,
                    'REMARK' => 'true',
                    'code' => 200
                ];
            } else {
                DB::table('hospital_facilities_details')->insert($fields);
                $response = [
                    'Success' => true,
                    'Message' => 'Facilities update successfully.',
                    // 'data' =>  $fields,
                    'REMARK' => 'true',
                    'code' => 200
                ];
            }
        } catch (\Throwable $th) {
            $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
        }
        return $response;
    }

    // function edit_facilities(Request $req)
    // {
    //     if (!$req->isMethod('post')) {
    //         return response()->json([
    //             'Success' => false,
    //             'Message' => 'Method Not Allowed.',
    //             'code' => 405
    //         ], 405);
    //     }

    //     date_default_timezone_set('Asia/Kolkata');
    //     $cdt = Carbon::now()->format('Ymd');
    //     $input = $req->all();
    //     $fileName1 = $fileName2 = $fileName3 = null;
    //     $url1 = $url2 = $url3 = null;

    //     $UID = strtoupper(substr(md5($input['PHARMA_ID'] . $input['DASH_ID']), 0, 10));
    //     if ($req->file('IMAGE1_URL')) {
    //         $fileName1 = $UID . "_1." . $req->file('IMAGE1_URL')->getClientOriginalExtension();
    //         $req->file('IMAGE1_URL')->storeAs('facilities', $fileName1);
    //         $url1 = asset(storage::url('app/facilities')) . "/" . $fileName1;
    //     }

    //     if ($req->file('IMAGE2_URL')) {
    //         $fileName2 = $UID . "_2." . $req->file('IMAGE2_URL')->getClientOriginalExtension();
    //         $req->file('IMAGE2_URL')->storeAs('facilities', $fileName2);
    //         $url2 = asset(storage::url('app/facilities')) . "/" . $fileName2;
    //     }

    //     if ($req->file('IMAGE3_URL')) {
    //         $fileName3 = $UID . "_3." . $req->file('IMAGE3_URL')->getClientOriginalExtension();
    //         $req->file('IMAGE3_URL')->storeAs('facilities', $fileName3);
    //         $url3 = asset(storage::url('app/facilities')) . "/" . $fileName3;
    //     }

    //     $fields = [
    //         'PHARMA_ID' => $input['PHARMA_ID'],
    //         'DIS_ID' => $input['DIS_ID'],
    //         'DASH_SECTION_ID' => $input['DASH_SECTION_ID'],
    //         'DASH_ID' => $input['DASH_ID'],
    //         'DASH_NAME' => $input['DASH_NAME'],
    //         'DEPARTMENT' => $input['DEPARTMENT'],
    //         'DEPT_PH' => $input['DEPT_PH'],
    //         'TOT_BED' => $input['TOT_BED'] ?? null,
    //         'AVAIL_BED' => $input['AVAIL_BED'] ?? null,
    //         'PRICE_FROM' => $input['PRICE_FROM'] ?? null,
    //         'SHORT_NOTE' => $input['SHORT_NOTE'] ?? null,
    //         'FREE_AREA' => $input['FREE_AREA'] ?? null,
    //         'SERV_CRG' => $input['SERV_CRG'] ?? null,
    //         'IMAGE1_URL' => $url1,
    //         'IMAGE2_URL' => $url2,
    //         'IMAGE3_URL' => $url3,
    //         'UPDT_DT' => $cdt,
    //     ];

    //     $data = [
    //         "DASH_SECTION_ID" => $input['DASH_SECTION_ID'],
    //         'DASH_SECTION_NAME' => $input['DEPARTMENT'],
    //         "FACILITY" => []
    //     ];
    //     $data["FACILITY"][] = [
    //         'PHARMA_ID' => $input['PHARMA_ID'],
    //         'DIS_ID' => $input['DIS_ID'],
    //         'DASH_SECTION_ID' => $input['DASH_SECTION_ID'],
    //         'DASH_ID' => $input['DASH_ID'],
    //         'DASH_NAME' => $input['DASH_NAME'],
    //         'DEPARTMENT' => $input['DEPARTMENT'],
    //         'DEPT_PH' => $input['DEPT_PH'],
    //         'TOT_BED' => $input['TOT_BED'] ?? null,
    //         'AVAIL_BED' => $input['AVAIL_BED'] ?? null,
    //         'PRICE_FROM' => $input['PRICE_FROM'] ?? null,
    //         'SHORT_NOTE' => $input['SHORT_NOTE'] ?? null,
    //         'FREE_AREA' => $input['FREE_AREA'] ?? null,
    //         'SERV_CRG' => $input['SERV_CRG'] ?? null,
    //         'IMAGE1_URL' => $url1,
    //         'IMAGE2_URL' => $url2,
    //         'IMAGE3_URL' => $url3,
    //         'UPDT_DT' => $cdt,
    //     ];

    //     try {
    //         DB::table('hospital_facilities_details')->where('UID', $UID)->update($fields);
    //         $response = [
    //             'Success' => true,
    //             'Message' => 'Facilities added successfully.',
    //             'data' =>  $data,
    //             'REMARK' => 'true',
    //             'code' => 200
    //         ];
    //     } catch (\Throwable $th) {
    //         $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
    //     }
    //     return $response;
    // }

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

                ];

                try {
                    DB::table('dr_availablity')->where(['DR_ID' => $row['DR_ID'], 'PHARMA_ID' => $row['PHARMA_ID']])->update($fields);

                    $data[] = $doctorData;
                } catch (\Throwable $th) {
                    $response = ['Success' => false, 'Message' => $th->getMessage(), 'code' => 200];
                    return $response;
                }
            }
            $fields1 = [
                'TAG_NOTE' => $input['TAG_NOTE'],
                'DEPT_PH' => $input['DEPT_PH'],
                'TREATMENTS' => $input['TREATMENTS'],
            ];
            DB::table('dr_availablity')->where(['DIS_ID' => $input['DIS_ID'], 'PHARMA_ID' => $input['PHARMA_ID']])->update($fields1);
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

    // public function vu_facilities(Request $req)
    // {
    //     if ($req->isMethod('post')) {
    //         $input = $req->json()->all();
    //         $pharmaId = $input['PHARMA_ID'];

    //         $data1 = DB::table('dashboard')
    //             ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId) {
    //                 $join->on('dashboard.DASH_ID', '=', 'hospital_facilities_details.DASH_ID')
    //                     ->where('hospital_facilities_details.PHARMA_ID', '=', $pharmaId);
    //             })
    //             ->select(
    //                 'dashboard.DASH_SECTION_ID',
    //                 'dashboard.DASH_SECTION_NAME',
    //                 'dashboard.PHOTO_URL',
    //                 'dashboard.DASH_TYPE',
    //                 'dashboard.DASH_DESCRIPTION',
    //                 'dashboard.GR_DESC',
    //                 'dashboard.DASH_ID',
    //                 'dashboard.DIS_ID',
    //                 'dashboard.SYM_ID',
    //                 'dashboard.DASH_NAME',
    //                 'hospital_facilities_details.UID',
    //                 'hospital_facilities_details.TOT_BED',
    //                 'hospital_facilities_details.AVAIL_BED',
    //                 'hospital_facilities_details.PRICE_FROM',
    //                 'hospital_facilities_details.DEPT_PH',
    //                 'hospital_facilities_details.SHORT_NOTE',
    //                 'hospital_facilities_details.FREE_AREA',
    //                 'hospital_facilities_details.FREE_FROM',
    //                 'hospital_facilities_details.FREE_TO',
    //                 'hospital_facilities_details.SERV_AREA',
    //                 'hospital_facilities_details.SERV_FROM',
    //                 'hospital_facilities_details.SERV_TO',
    //                 'hospital_facilities_details.SERV_CRG',
    //                 'hospital_facilities_details.DLV_TM',
    //                 'hospital_facilities_details.MIN_ODR',
    //                 'hospital_facilities_details.SERV_24X7',
    //                 'hospital_facilities_details.SERV_HOME',
    //                 'hospital_facilities_details.TREATMENTS',
    //                 'hospital_facilities_details.DISCOUNT',
    //                 'hospital_facilities_details.CASH_LESS',
    //                 'hospital_facilities_details.CASH_PAID',
    //                 'hospital_facilities_details.IMAGE1_URL',
    //                 'hospital_facilities_details.IMAGE2_URL',
    //                 'hospital_facilities_details.IMAGE3_URL',
    //                 'hospital_facilities_details.REMARK',
    //             )
    //             ->where('dashboard.CATEGORY', 'like', '%' . 'H' . '%')
    //             ->where('dashboard.DASH_SECTION_ID', '<>', 'AK')
    //             // ->where('dashboard.DASH_SECTION_ID', '<>', 'AM')
    //             ->where('dashboard.STATUS', 'Active')
    //             ->get();

    //         $groupedData = [];
    //         foreach ($data1 as $row) {
    //             $sectionId = $row->DASH_SECTION_ID;
    //             $type = $row->DASH_TYPE;

    //             if (!isset($groupedData[$sectionId])) {
    //                 $groupedData[$sectionId] = [
    //                     "DASH_SECTION_ID" => $sectionId,
    //                     "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
    //                     "DESCRIPTION" => $row->GR_DESC,
    //                     "PHOTO_URL" => $row->PHOTO_URL,
    //                     "DASH_TYPE" => []
    //                 ];
    //             }
    //             if (!isset($groupedData[$sectionId]['DASH_TYPE'][$type])) {
    //                 $groupedData[$sectionId]['DASH_TYPE'][$type] = [
    //                     "DASH_SECTION_ID" => $sectionId,
    //                     "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
    //                     "DESCRIPTION" => $row->GR_DESC,
    //                     "PHOTO_URL" => $row->PHOTO_URL,
    //                     "DASH_TYPE" => $type,
    //                     "FACILITY" => []
    //                 ];
    //             }
    //             $groupedData[$sectionId]['DASH_TYPE'][$type]['FACILITY'][] = [
    //                 "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
    //                 "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
    //                 "UID" => $row->UID,
    //                 "DASH_ID" => $row->DASH_ID,
    //                 "DIS_ID" => $row->DIS_ID,
    //                 "SYM_ID" => $row->SYM_ID ?? null,
    //                 "DASH_NAME" => $row->DASH_NAME,
    //                 "DESCRIPTION" => $row->DASH_DESCRIPTION,
    //                 "TOT_BED" => $row->TOT_BED,
    //                 "AVAIL_BED" => $row->AVAIL_BED,
    //                 "PRICE_FROM" => $row->PRICE_FROM,
    //                 "DEPT_PH" => $row->DEPT_PH,
    //                 "SHORT_NOTE" => $row->SHORT_NOTE,
    //                 "FREE_AREA" => $row->FREE_AREA,
    //                 "FREE_FROM" => $row->FREE_FROM,
    //                 "FREE_TO" => $row->FREE_TO,
    //                 "SERV_AREA" => $row->SERV_AREA,
    //                 "SERV_CRG" => $row->SERV_CRG,
    //                 "SERV_FROM" => $row->SERV_FROM,
    //                 "SERV_TO" => $row->SERV_TO,
    //                 "DLV_TM" => $row->DLV_TM,
    //                 "MIN_ODR" => $row->MIN_ODR,
    //                 "SERV_24X7" => $row->SERV_24X7,
    //                 "SERV_HOME" => $row->SERV_HOME,
    //                 "TREATMENTS" => $row->TREATMENTS,
    //                 "DISCOUNT" => $row->DISCOUNT,
    //                 "CASH_LESS" => $row->CASH_LESS,
    //                 "CASH_PAID" => $row->CASH_PAID,
    //                 "IMAGE1_URL" => $row->IMAGE1_URL,
    //                 "IMAGE2_URL" => $row->IMAGE2_URL,
    //                 "IMAGE3_URL" => $row->IMAGE3_URL,
    //                 "REMARK" => $row->REMARK,
    //                 "PHOTO_URL" => $row->PHOTO_URL,
    //             ];
    //         }


    //         $data = \array_values($groupedData);
    //         $response = $data ?
    //             ['Success' => true, 'data' => $data, 'code' => 200] :
    //             ['Success' => false, 'Message' => 'Record not found', 'code' => 200];
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405];
    //     }
    //     return response()->json($response);
    // }

    public function vu_facilities(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();
            // $pharmaId = $input['PHARMA_ID'];

            $data1 = DB::table('facility_section')
                // ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                // ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                // ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId) {
                //     $join->on('facility.DASH_ID', '=', 'hospital_facilities_details.DASH_ID')
                //         ->where('hospital_facilities_details.PHARMA_ID', '=', $pharmaId);
                // })
                ->select([
                    'facility_section.DASH_SECTION_ID',
                    'facility_section.DASH_SECTION_NAME',
                    'facility_section.DS_DESCRIPTION',
                    'facility_section.DSH_PHOTO_URL',
                    // 'facility_type.DASH_TYPE',
                    // 'facility.DASH_ID',

                ])
                ->get();

            $groupedData = [];
            foreach ($data1 as $row) {
                $sectionId = $row->DASH_SECTION_ID;
                // $type = $row->DASH_TYPE;

                if (!isset($groupedData[$sectionId])) {
                    $groupedData[$sectionId] = [
                        "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                        "DESCRIPTION" => $row->DS_DESCRIPTION,
                        "PHOTO_URL" => $row->DSH_PHOTO_URL,
                        // "DASH_TYPE" => []
                    ];
                }
                // switch ($sectionId) {

                //     case 'AG':
                //         $photoGrUrl = $row->URL_24X7_MG;
                //         $photoUrl = $row->URL_24X7_MI;
                //         break;
                //     case 'AH':
                //         $photoGrUrl = $row->URL_IPD_MG;
                //         $photoUrl = $row->URL_IPD_MI;
                //         break;
                //     case 'AI':
                //         $photoGrUrl = $row->URL_HOME_MG;
                //         $photoUrl = $row->URL_HOME_MI;
                //         break;
                //     case 'AM':
                //         $photoGrUrl = $row->URL_2NDOPIN_MG;
                //         $photoUrl = $row->URL_2NDOPIN_MI;
                //         break;

                //     default:
                //         $photoGrUrl = $row->PHOTO_URL;
                //         $photoUrl = $row->PHOTO_URL;
                //         break;
                // }
                // if (!isset($groupedData[$sectionId]['DASH_TYPE'][$type])) {
                //     $groupedData[$sectionId]['DASH_TYPE'][$type] = [
                //         "DASH_SECTION_ID" => $sectionId,
                //         "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                //         "DESCRIPTION" => $row->DT_DESCRIPTION,
                //         "PHOTO_URL" =>$row->DTIMG1,
                //         "DASH_TYPE" => $row->DASH_TYPE,
                //         "FACILITY" => []
                //     ];
                // }
                // $groupedData[$sectionId]['DASH_TYPE'][$type]['FACILITY'][] = [
                //     "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
                //     "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
                //     "UID" => $row->UID,
                //     "DASH_ID" => $row->DASH_ID,
                //     // "DIS_ID" => $row->DIS_ID,
                //     // "SYM_ID" => $row->SYM_ID ?? null,
                //     "DASH_NAME" => $row->DASH_NAME,
                //     "DESCRIPTION" => $row->DN_DESCRIPTION,
                //     "TOT_BED" => $row->TOT_BED,
                //     "AVAIL_BED" => $row->AVAIL_BED,
                //     "PRICE_FROM" => $row->PRICE_FROM,
                //     "DEPT_PH" => $row->DEPT_PH,
                //     "SHORT_NOTE" => $row->SHORT_NOTE,
                //     "FREE_AREA" => $row->FREE_AREA,
                //     "FREE_FROM" => $row->FREE_FROM,
                //     "FREE_TO" => $row->FREE_TO,
                //     "SERV_AREA" => $row->SERV_AREA,
                //     "SERV_CRG" => $row->SERV_CRG,
                //     "SERV_FROM" => $row->SERV_FROM,
                //     "SERV_TO" => $row->SERV_TO,
                //     "DLV_TM" => $row->DLV_TM,
                //     "MIN_ODR" => $row->MIN_ODR,
                //     "SERV_24X7" => $row->SERV_24X7,
                //     "SERV_HOME" => $row->SERV_HOME,
                //     "TREATMENTS" => $row->TREATMENTS,
                //     "DISCOUNT" => $row->DISCOUNT,
                //     "CASH_LESS" => $row->CASH_LESS,
                //     "CASH_PAID" => $row->CASH_PAID,
                //     // "IMAGE1_URL" => $row->IMAGE1_URL,
                //     // "IMAGE2_URL" => $row->IMAGE2_URL,
                //     // "IMAGE3_URL" => $row->IMAGE3_URL,
                //     "REMARK" => $row->REMARK,
                //     "PHOTO_URL" => $$row->DNIMG1,
                // ];

                // Check if DASH_SECTION_ID is 'SR' and copy to 'AM'
                // if ($row->DASH_SECTION_ID === 'SR') {
                //     $newRow = clone $row;
                //     $newRow->DASH_SECTION_ID = 'AM';
                //     $newRow->DASH_SECTION_NAME = 'Second Opinion (Treatment)'; // Modify as needed

                //     if (!isset($groupedData['AM'])) {
                //         $groupedData['AM'] = [
                //             "DASH_SECTION_ID" => 'AM',
                //             "DASH_SECTION_NAME" => $newRow->DASH_SECTION_NAME,
                //             "DESCRIPTION" => $newRow->DASH_SECTION_DESC,
                //             "PHOTO_URL" => $newRow->PHOTO_URL,
                //             "DASH_TYPE" => []
                //         ];
                //     }
                //     if (!isset($groupedData['AM']['DASH_TYPE'][$type])) {
                //         $groupedData['AM']['DASH_TYPE'][$type] = [
                //             "DASH_SECTION_ID" => 'AM',
                //             "DASH_SECTION_NAME" => $newRow->DASH_SECTION_NAME,
                //             "DESCRIPTION" => $newRow->DASH_SECTION_DESC,
                //             "PHOTO_URL" => $newRow->PHOTO_URL,
                //             "DASH_TYPE" => $type,
                //             "FACILITY" => []
                //         ];
                //     }
                //     $groupedData['AM']['DASH_TYPE'][$type]['FACILITY'][] = [
                //         "DASH_SECTION_ID" => $newRow->DASH_SECTION_ID,
                //         "DASH_SECTION_NAME" => $newRow->DASH_SECTION_NAME,
                //         "UID" => $newRow->UID,
                //         "DASH_ID" => $newRow->DASH_ID,
                //         // "DIS_ID" => $newRow->DIS_ID,
                //         // "SYM_ID" => $newRow->SYM_ID ?? null,
                //         "DASH_NAME" => $newRow->DASH_NAME,
                //         "DESCRIPTION" => $newRow->DN_DESCRIPTION,
                //         "TOT_BED" => $newRow->TOT_BED,
                //         "AVAIL_BED" => $newRow->AVAIL_BED,
                //         "PRICE_FROM" => $newRow->PRICE_FROM,
                //         "DEPT_PH" => $newRow->DEPT_PH,
                //         "SHORT_NOTE" => $newRow->SHORT_NOTE,
                //         "FREE_AREA" => $newRow->FREE_AREA,
                //         "FREE_FROM" => $newRow->FREE_FROM,
                //         "FREE_TO" => $newRow->FREE_TO,
                //         "SERV_AREA" => $newRow->SERV_AREA,
                //         "SERV_CRG" => $newRow->SERV_CRG,
                //         "SERV_FROM" => $newRow->SERV_FROM,
                //         "SERV_TO" => $newRow->SERV_TO,
                //         "DLV_TM" => $newRow->DLV_TM,
                //         "MIN_ODR" => $newRow->MIN_ODR,
                //         "SERV_24X7" => $newRow->SERV_24X7,
                //         "SERV_HOME" => $newRow->SERV_HOME,
                //         "TREATMENTS" => $newRow->TREATMENTS,
                //         "DISCOUNT" => $newRow->DISCOUNT,
                //         "CASH_LESS" => $newRow->CASH_LESS,
                //         "CASH_PAID" => $newRow->CASH_PAID,
                //         "IMAGE1_URL" => $newRow->IMAGE1_URL,
                //         "IMAGE2_URL" => $newRow->IMAGE2_URL,
                //         "IMAGE3_URL" => $newRow->IMAGE3_URL,
                //         "REMARK" => $newRow->REMARK,
                //         "PHOTO_URL" => $newRow->PHOTO_URL,
                //     ];
                // }
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


    // private function groupData($data)
    // {
    //     $groupedData = [];
    //     foreach ($data as $row) {
    //         $sectionId = $row->DASH_SECTION_ID;
    //         $type = $row->DASH_TYPE;

    //         if (!isset($groupedData[$sectionId])) {
    //             $groupedData[$sectionId] = [
    //                 "DASH_SECTION_ID" => $sectionId,
    //                 "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
    //                 "DESCRIPTION" => $row->GR_DESC,
    //                 "PHOTO_URL" => $row->PHOTO_URL,
    //                 "DASH_TYPE" => []
    //             ];
    //         }

    //         if (!isset($groupedData[$sectionId]['DASH_TYPE'][$type])) {
    //             $groupedData[$sectionId]['DASH_TYPE'][$type] = [
    //                 "DASH_SECTION_ID" => $sectionId,
    //                 "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
    //                 "DESCRIPTION" => $row->GR_DESC,
    //                 "PHOTO_URL" => $row->PHOTO_URL,
    //                 "DASH_TYPE" => $type,
    //                 "FACILITY" => []
    //             ];
    //         }

    //         $groupedData[$sectionId]['DASH_TYPE'][$type]['FACILITY'][] = [
    //             "DASH_SECTION_ID"=>$row->DASH_SECTION_ID,
    //             "DASH_SECTION_NAME"=>$row->DASH_SECTION_NAME,
    //             "UID" => $row->UID,
    //             "DASH_ID" => $row->DASH_ID,
    //             "DIS_ID" => $row->DIS_ID,
    //             "SYM_ID" => $row->SYM_ID ?? null,  // Optional property handling
    //             "DASH_NAME" => $row->DASH_NAME,
    //             "DESCRIPTION" => $row->DASH_DESCRIPTION,
    //             "TOT_BED" => $row->TOT_BED,
    //             "AVAIL_BED" => $row->AVAIL_BED,
    //             "PRICE_FROM" => $row->PRICE_FROM,
    //             "DEPT_PH" => $row->DEPT_PH,
    //             "SHORT_NOTE" => $row->SHORT_NOTE,
    //             "FREE_AREA" => $row->FREE_AREA,
    //             "FREE_FROM" => $row->FREE_FROM,
    //             "FREE_TO" => $row->FREE_TO,
    //             "SERV_AREA" => $row->SERV_AREA,
    //             "SERV_CRG" => $row->SERV_CRG,
    //             "SERV_FROM" => $row->SERV_FROM,
    //             "SERV_TO" => $row->SERV_TO,
    //             "DLV_TM" => $row->DLV_TM,
    //             "MIN_ODR" => $row->MIN_ODR,
    //             "SERV_24X7" => $row->SERV_24X7,
    //             "SERV_HOME" => $row->SERV_HOME,
    //             "TREATMENTS" => $row->TREATMENTS,
    //             "DISCOUNT" => $row->DISCOUNT,
    //             "CASH_LESS" => $row->CASH_LESS,
    //             "CASH_PAID" => $row->CASH_PAID,
    //             "IMAGE1_URL" => $row->IMAGE1_URL,
    //             "IMAGE2_URL" => $row->IMAGE2_URL,
    //             "IMAGE3_URL" => $row->IMAGE3_URL,
    //             "REMARK" => $row->REMARK,
    //             "PHOTO_URL" => $row->PHOTO_URL,
    //         ];
    //     }
    //     return $groupedData;
    // }

    // public function vu_facilities1(Request $req)
    // {
    //     if ($req->isMethod('post')) {
    //         $input = $req->json()->all();

    //         $pharmaId = $input['PHARMA_ID'];
    //         $dsid = $input['DASH_SECTION_ID'];
    //         if ($dsid === 'AM') {
    //             $dsid = 'SR';
    //         }
    //         $data1 = DB::table('dashboard')
    //             ->leftJoin('hospital_facilities_details', function ($join) use ($pharmaId, $dsid) {
    //                 $join->on('dashboard.DASH_ID', '=', 'hospital_facilities_details.DASH_ID')
    //                     ->where('hospital_facilities_details.PHARMA_ID', '=', $pharmaId);
    //             })
    //             ->select(
    //                 'dashboard.DASH_SECTION_ID',
    //                 'dashboard.DASH_SECTION_NAME',
    //                 'dashboard.PHOTO_URL',
    //                 'dashboard.DASH_TYPE',
    //                 'dashboard.DASH_DESCRIPTION',
    //                 'dashboard.GR_DESC',
    //                 'dashboard.DASH_SECTION_DESC',
    //                 'dashboard.DASH_ID',
    //                 'dashboard.DIS_ID',
    //                 'dashboard.SYM_ID',
    //                 'dashboard.DASH_NAME',
    //                 'hospital_facilities_details.UID',
    //                 'hospital_facilities_details.TOT_BED',
    //                 'hospital_facilities_details.AVAIL_BED',
    //                 'hospital_facilities_details.PRICE_FROM',
    //                 'hospital_facilities_details.DEPT_PH',
    //                 'hospital_facilities_details.SHORT_NOTE',
    //                 'hospital_facilities_details.SEC_SHORT_NOTE',
    //                 'hospital_facilities_details.FREE_AREA',
    //                 'hospital_facilities_details.FREE_FROM',
    //                 'hospital_facilities_details.FREE_TO',
    //                 'hospital_facilities_details.SERV_AREA',
    //                 'hospital_facilities_details.SERV_FROM',
    //                 'hospital_facilities_details.SERV_TO',
    //                 'hospital_facilities_details.SERV_CRG',
    //                 'hospital_facilities_details.DLV_TM',
    //                 'hospital_facilities_details.MIN_ODR',
    //                 'hospital_facilities_details.SERV_24X7',
    //                 'hospital_facilities_details.SERV_HOME',
    //                 'hospital_facilities_details.SERV_IP',
    //                 'hospital_facilities_details.TREATMENTS',
    //                 'hospital_facilities_details.DISCOUNT',
    //                 'hospital_facilities_details.CASH_LESS',
    //                 'hospital_facilities_details.CASH_PAID',
    //                 'hospital_facilities_details.IMAGE1_URL',
    //                 'hospital_facilities_details.IMAGE2_URL',
    //                 'hospital_facilities_details.IMAGE3_URL',
    //                 'hospital_facilities_details.REMARK',
    //             )
    //             ->where('dashboard.CATEGORY', 'like', '%' . 'H' . '%')
    //             ->where('dashboard.DASH_SECTION_ID', '<>', 'AK')
    //             ->where('dashboard.STATUS', 'Active')
    //             ->where('dashboard.DASH_SECTION_ID', $dsid)
    //             ->get();

    //         $groupedData = [];
    //         foreach ($data1 as $row) {
    //             $typeKey = $row->DASH_TYPE;
    //             if ($dsid === 'AM') {
    //                 $DSecID = 'AM';
    //                 $DSecName = 'Second Opinion (Treatment)';
    //             } else {
    //                 $DSecID = $row->DASH_SECTION_ID;
    //                 $DSecName = $row->DASH_SECTION_NAME;
    //             }
    //             if (!isset($groupedData[$typeKey])) {
    //                 $groupedData[$typeKey] = [
    //                     "DASH_SECTION_ID" => $DSecID,
    //                     "DASH_SECTION_NAME" => $DSecName,
    //                     "DASH_TYPE" => $row->DASH_TYPE,
    //                     "DESCRIPTION" => $row->DASH_SECTION_DESC,
    //                     "PHOTO_URL" => $row->PHOTO_URL,
    //                     "FACILITY" => []
    //                 ];
    //             }
    //             if ($row->DASH_SECTION_ID === 'AG') {
    //                 $dname = '24x7 ' . $row->DASH_NAME;
    //                 $dstype = '24x7 ' . $row->DASH_TYPE;
    //             } else {
    //                 $dname = $row->DASH_NAME;
    //                 $dstype = $row->DASH_TYPE;
    //             }

    //             $groupedData[$typeKey]['FACILITY'][] = [
    //                 "DASH_SECTION_ID" => $DSecID,
    //                 "DASH_SECTION_NAME" => $DSecName,
    //                 "UID" => $row->UID,
    //                 "DASH_ID" => $row->DASH_ID,
    //                 "DASH_TYPE" => $dstype,
    //                 "DIS_ID" => $row->DIS_ID,
    //                 "SYM_ID" => $row->SYM_ID ?? null,  // Optional property handling
    //                 "DASH_NAME" => $dname,
    //                 "DESCRIPTION" => $row->DASH_DESCRIPTION,
    //                 "TOT_BED" => $row->TOT_BED,
    //                 "AVAIL_BED" => $row->AVAIL_BED,
    //                 "PRICE_FROM" => $row->PRICE_FROM,
    //                 "DEPT_PH" => $row->DEPT_PH,
    //                 "SHORT_NOTE" => $row->SHORT_NOTE,
    //                 "SEC_SHORT_NOTE" => $row->SEC_SHORT_NOTE,
    //                 "FREE_AREA" => $row->FREE_AREA,
    //                 "FREE_FROM" => $row->FREE_FROM,
    //                 "FREE_TO" => $row->FREE_TO,
    //                 "SERV_AREA" => $row->SERV_AREA,
    //                 "SERV_CRG" => $row->SERV_CRG,
    //                 "SERV_FROM" => $row->SERV_FROM,
    //                 "SERV_TO" => $row->SERV_TO,
    //                 "DLV_TM" => $row->DLV_TM,
    //                 "MIN_ODR" => $row->MIN_ODR,
    //                 "SERV_24X7" => $row->SERV_24X7,
    //                 "SERV_HOME" => $row->SERV_HOME,
    //                 "SERV_IP" => $row->SERV_IP,
    //                 "TREATMENTS" => $row->TREATMENTS,
    //                 "DISCOUNT" => $row->DISCOUNT,
    //                 "CASH_LESS" => $row->CASH_LESS,
    //                 "CASH_PAID" => $row->CASH_PAID,
    //                 "IMAGE1_URL" => $row->IMAGE1_URL,
    //                 "IMAGE2_URL" => $row->IMAGE2_URL,
    //                 "IMAGE3_URL" => $row->IMAGE3_URL,
    //                 "REMARK" => $row->REMARK,
    //                 "PHOTO_URL" => $row->PHOTO_URL,
    //             ];
    //         }
    //         $data = array_values($groupedData);

    //         if (empty($data)) {
    //             return response()->json(['Success' => false, 'Message' => 'Record not found', 'code' => 404], 404);
    //         } else {
    //             return response()->json(['Success' => true, 'data' => $data, 'code' => 200], 200);
    //         }
    //     } else {
    //         return response()->json(['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 405], 405);
    //     }
    // }

    public function vu_facilities1(Request $req)
    {
        if ($req->isMethod('post')) {
            $input = $req->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $dsid = $input['DASH_SECTION_ID'];

            // If DASH_SECTION_ID is 'AM', set it to 'SR' for the query
            if ($dsid === 'AM') {
                $dsid = 'SR';
            }

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
                    'dashboard.DASH_SECTION_DESC',
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
                    'hospital_facilities_details.SEC_SHORT_NOTE',
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
                    'hospital_facilities_details.DISCOUNT',
                    'hospital_facilities_details.CASH_LESS',
                    'hospital_facilities_details.CASH_PAID',
                    'hospital_facilities_details.IMAGE1_URL',
                    'hospital_facilities_details.IMAGE2_URL',
                    'hospital_facilities_details.IMAGE3_URL',
                    'hospital_facilities_details.REMARK'
                )
                ->where('dashboard.CATEGORY', 'like', '%' . 'H' . '%')
                ->where('dashboard.DASH_SECTION_ID', '<>', 'AK')
                ->where('dashboard.STATUS', 'Active')
                ->where('dashboard.DASH_SECTION_ID', $dsid)
                ->get();

            $groupedData = [];
            foreach ($data1 as $row) {
                $typeKey = $row->DASH_TYPE;

                // Determine the section ID and name


                if (!isset($groupedData[$typeKey])) {
                    // if ($dsid === 'AM') {
                    //     $DSecID = 'AM';
                    //     $DSecName = 'Second Opinion (Treatment)';
                    // } else {
                    $DSecID = $row->DASH_SECTION_ID;
                    $DSecName = $row->DASH_SECTION_NAME;
                    // }
                    $groupedData[$typeKey] = [
                        "DASH_SECTION_ID" => $DSecID,
                        "DASH_SECTION_NAME" => $DSecName,
                        "DASH_TYPE" => $row->DASH_TYPE,
                        "DESCRIPTION" => $row->DASH_SECTION_DESC,
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

                $facility = [
                    "DASH_SECTION_ID" => $DSecID,
                    "DASH_SECTION_NAME" => $DSecName,
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
                    "SEC_SHORT_NOTE" => $row->SEC_SHORT_NOTE,
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
                    "SERV_IP" => $row->SERV_IP,
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

                $groupedData[$typeKey]['FACILITY'][] = $facility;

                // If DASH_SECTION_ID is 'SR', copy the facility and add it as DASH_SECTION_ID 'AM'
                // if ($row->DASH_SECTION_ID === 'SR') {
                //     $newFacility = $facility;
                //     $newFacility['DASH_SECTION_ID'] = 'AM';
                //     $newFacility['DASH_SECTION_NAME'] = 'Second Opinion (Treatment)';

                //     if (!isset($groupedData['AM'])) {
                //         $groupedData['AM'] = [
                //             "DASH_SECTION_ID" => 'AM',
                //             "DASH_SECTION_NAME" => 'Second Opinion (Treatment)',
                //             "DASH_TYPE" => $row->DASH_TYPE,
                //             "DESCRIPTION" => $row->DASH_SECTION_DESC,
                //             "PHOTO_URL" => $row->PHOTO_URL,
                //             "FACILITY" => []
                //         ];
                //     }
                //     $groupedData['AM']['FACILITY'][] = $newFacility;
                // }
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


    // private function groupFacilities($data)
    // {
    //     $groupedData = [];
    //     foreach ($data as $row) {
    //         $typeKey = $row->DASH_TYPE;
    //         if (!isset($groupedData[$typeKey])) {
    //             $groupedData[$typeKey] = [
    //                 "DASH_SECTION_ID" => $row->DASH_SECTION_ID,
    //                 "DASH_SECTION_NAME" => $row->DASH_SECTION_NAME,
    //                 "DASH_TYPE" => $row->DASH_TYPE,
    //                 "DESCRIPTION" => $row->GR_DESC,
    //                 "PHOTO_URL" => $row->PHOTO_URL,
    //                 "FACILITY" => []
    //             ];
    //         }
    //         if ($row->DASH_SECTION_ID==='AG'){
    //             $dname='24x7 '.$row->DASH_NAME;
    //             $dstype='24x7 '.$row->DASH_TYPE;

    //         }else{
    //             $dname=$row->DASH_NAME;
    //             $dstype=$row->DASH_TYPE;

    //         }

    //         $groupedData[$typeKey]['FACILITY'][] = [
    //             "DASH_SECTION_ID"=>$row->DASH_SECTION_ID,
    //             "DASH_SECTION_NAME"=>$row->DASH_SECTION_NAME,
    //             "UID" => $row->UID,
    //             "DASH_ID" => $row->DASH_ID,
    //             "DASH_TYPE" => $dstype,
    //             "DIS_ID" => $row->DIS_ID,
    //             "SYM_ID" => $row->SYM_ID ?? null,  // Optional property handling
    //             "DASH_NAME" => $dname,
    //             "DESCRIPTION" => $row->DASH_DESCRIPTION,
    //             "TOT_BED" => $row->TOT_BED,
    //             "AVAIL_BED" => $row->AVAIL_BED,
    //             "PRICE_FROM" => $row->PRICE_FROM,
    //             "DEPT_PH" => $row->DEPT_PH,
    //             "SHORT_NOTE" => $row->SHORT_NOTE,
    //             "FREE_AREA" => $row->FREE_AREA,
    //             "FREE_FROM" => $row->FREE_FROM,
    //             "FREE_TO" => $row->FREE_TO,
    //             "SERV_AREA" => $row->SERV_AREA,
    //             "SERV_CRG" => $row->SERV_CRG,
    //             "SERV_FROM" => $row->SERV_FROM,
    //             "SERV_TO" => $row->SERV_TO,
    //             "DLV_TM" => $row->DLV_TM,
    //             "MIN_ODR" => $row->MIN_ODR,
    //             "SERV_24X7" => $row->SERV_24X7,
    //             "SERV_HOME" => $row->SERV_HOME,
    //             "TREATMENTS" => $row->TREATMENTS,
    //             "DISCOUNT" => $row->DISCOUNT,
    //             "CASH_LESS" => $row->CASH_LESS,
    //             "CASH_PAID" => $row->CASH_PAID,
    //             "IMAGE1_URL" => $row->IMAGE1_URL,
    //             "IMAGE2_URL" => $row->IMAGE2_URL,
    //             "IMAGE3_URL" => $row->IMAGE3_URL,
    //             "REMARK" => $row->REMARK,
    //             "PHOTO_URL" => $row->PHOTO_URL,

    //         ];
    //     }
    //     return $groupedData;
    // }
}
