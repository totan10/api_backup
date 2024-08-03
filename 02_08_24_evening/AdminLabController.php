<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AdminLabController extends Controller
{
    function add_l_dash_dtl(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input   = $req->all();
            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            // $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $item_type = $input['ITEM_TYPE'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            $fileName = $section . $i_position . "." . $req->file('file')->getClientOriginalExtension();
            $req->file('file')->storeAs('labhomepage', $fileName);
            $url = asset(storage::url('app/labhomepage')) . "/" . $fileName;

            $sql1 = "INSERT INTO `l_dashboard_details`(`SECTION_ID`, `SECTION_NAME`, `SECTION_SL`, `ITEM_NAME`,`ITEM_TYPE`, `DESCRIPTION`, `PHOTO_URL`, `STATUS`) 
            VALUES ('$section','$h_name','$i_position','$item_name','$item_type','$i_desc','$url','$i_status')";
            DB::insert($sql1);

            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_l_dash_dtl(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input   = $req->all();
            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $item_id = $input['ITEM_ID'];
            $item_name = $input['ITEM_NAME'];
            $item_type = $input['ITEM_TYPE'];
            $i_desc = $input['DESCRIPTION'];
            $i_status = $input['I_STATUS'];
            $i_position = $input['I_POSITION'];

            if ($req->file('file') !== null) {
                $fileName = $section . $item_id . "." . $req->file('file')->getClientOriginalExtension();
                $req->file('file')->storeAs('labhomepage', $fileName);
                $url = asset(storage::url('app/labhomepage')) . "/" . $fileName;
                DB::update("UPDATE `l_dashboard_details` SET `SECTION_NAME`='$h_name',`ITEM_NAME`='$item_name',`ITEM_TYPE`='$item_type',`DESCRIPTION`='$i_desc',`PHOTO_URL`='$url',`STATUS`='$i_status',`SECTION_SL`='$i_position' WHERE ID='$item_id' AND `SECTION_ID`='$section'");
            } else {
                DB::update("UPDATE `l_dashboard_details` SET `SECTION_NAME`='$h_name',`ITEM_NAME`='$item_name',`ITEM_TYPE`='$item_type',`DESCRIPTION`='$i_desc',`STATUS`='$i_status',`SECTION_SL`='$i_position' WHERE ID='$item_id' AND `SECTION_ID`='$section'");
            }

            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_l_caption(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();

            $input   = $req->all();
            $header_id = $input['HEADER_ID'];
            $h_name = $input['HEADER_NAME'];
            $h_desc = $input['HEADER_DESCRIPTION'];
            $h_status = $input['H_STATUS'];

            DB::update("UPDATE `l_dashboard_header` SET `NAME`='$h_name',`DESCRIPTION`='$h_desc',`STATUS`='$h_status' WHERE `ID`='$header_id'");
            DB::update("UPDATE `l_dashboard_details` SET `SECTION_NAME`='$h_name' WHERE `SECTION_ID`='$header_id'");

            $response = ['Success' => true, 'Message' => 'Record update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_l_header(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();

            $input   = $req->all();
            $header_id = $input['HEADER_ID'];
            $section = $input['SECTION'];
            $h_name = $input['HEADER_NAME'];
            $h_desc = $input['HEADER_DESCRIPTION'];
            $h_status = $input['H_STATUS'];

            DB::insert("INSERT INTO `l_dashboard_header`(`ID`, `SECTION`, `NAME`, `DESCRIPTION`, `STATUS`) VALUES 
            ('$header_id','$section','$h_name','$h_desc','$h_status')");
            
            $response = ['Success' => true, 'Message' => 'Records added successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }
}
