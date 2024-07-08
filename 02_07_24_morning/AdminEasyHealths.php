<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AdminEasyHealths extends Controller
{
    function add_maindash(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }

        $response = array();
        $input = $req->all();

        if ($req->hasFile('file')) {
            $suffix = '';
            switch ($input['IMAGE_KEY']) {
                case 'true':
                    $suffix = '_1';
                    break;
                case 'view1':
                    $suffix = '_v1';
                    break;
                case 'view2':
                    $suffix = '_v2';
                    break;
            }

            $fileExtension = $req->file('file')->getClientOriginalExtension();
            $fileName = $input['DASH_SECTION_ID'] . $input['DASH_ID'] . $suffix . "." . $fileExtension;
            $req->file('file')->storeAs('dashboard', $fileName);
            $url = asset(storage::url('app/dashboard')) . "/" . $fileName;

            if (isset($input['IMAGE_KEY'])) {
                $fields = [
                    'DASH_SECTION_ID' => $input['DASH_SECTION_ID'],
                    'DASH_SECTION_NAME' => $input['DASH_SECTION_NAME'],
                    'DASH_SECTION_DESC' => $input['DASH_SECTION_DESC'],
                    'DIS_ID' => $input['DIS_ID'],
                    'SYM_ID' => $input['SYM_ID'],
                    'DASH_NAME' => $input['DASH_NAME'],
                    'DASH_DESCRIPTION' => $input['DASH_DESCRIPTION'],
                    'CATEGORY' => $input['CATEGORY'],
                    'STATUS' => $input['STATUS'],
                    'POSITION' => $input['POSITION'],
                    'GR_POSITION' => $input['GR_POSITION'],
                    'DASH_TYPE' => $input['DASH_TYPE'],
                    'COLOR_CODE' => $input['COLOR_CODE'],
                    'INDASH' => $input['INDASH'] ?? null,
                    'TAG_SECTION' => $input['TAG_SECTION'] ?? null,
                ];

                if (in_array($input['DASH_SECTION_ID'], ['AG', 'AH', 'AI', 'AM'])) {
                    switch ($input['DASH_SECTION_ID']) {
                        case 'AG':
                            switch ($input['DCAT']) {
                                case 'M':
                                    $fields['URL_24X7_MI'] = $url;
                                    break;
                                case 'D':
                                    $fields['URL_24X7_DI'] = $url;
                                    break;
                                case 'H':
                                    $fields['URL_24X7_HI'] = $url;
                                    break;
                                default:
                                    $fields['PHOTO_URL'] = $url;
                                    break;
                            }
                            break;
                        case 'AH':
                            switch ($input['DCAT']) {
                                case 'M':
                                    $fields['URL_IPD_MI'] = $url;
                                    break;
                                case 'D':
                                    $fields['URL_IPD_DI'] = $url;
                                    break;
                                case 'H':
                                    $fields['URL_IPD_HI'] = $url;
                                    break;
                                default:
                                    $fields['PHOTO_URL'] = $url;
                                    break;
                            }
                            break;
                        case 'AI':
                            switch ($input['DCAT']) {
                                case 'M':
                                    $fields['URL_HOME_MI'] = $url;
                                    break;
                                case 'D':
                                    $fields['URL_HOME_DI'] = $url;
                                    break;
                                case 'H':
                                    $fields['URL_HOME_HI'] = $url;
                                    break;
                                default:
                                    $fields['PHOTO_URL'] = $url;
                                    break;
                            }
                            break;
                        case 'AM':
                            switch ($input['DCAT']) {
                                case 'M':
                                    $fields['URL_2NDGN_MI'] = $url;
                                    break;
                                case 'D':
                                    $fields['URL_2NDGN_DI'] = $url;
                                    break;
                                case 'H':
                                    $fields['URL_2NDGN_HI'] = $url;
                                    break;
                                default:
                                    $fields['PHOTO_URL'] = $url;
                                    break;
                            }
                            break;
                    }
                } else {
                    switch ($input['IMAGE_KEY']) {
                        case 'true':
                            $fields['PHOTO1_URL'] = $url;
                            break;
                        case 'view1':
                            $fields['VIEW1_URL'] = $url;
                            break;
                        case 'view2':
                            $fields['VIEW2_URL'] = $url;
                            break;
                        default:
                            $fields['PHOTO_URL'] = $url;
                            break;
                    }
                }


                $lastId = DB::table('dashboard')->insertGetId($fields);

                $response = [
                    'Success' => true,
                    'Message' => 'Records added successfully.',
                    'PHOTO_URL' => $url,
                    'LAST_ID' => $lastId,
                    'code' => 200
                ];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'No file uploaded.', 'code' => 400];
        }
        return $response;
    }


    function updt_maindash(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input   = $req->all();

            if ($req->file('file') !== null) {
                $suffix = '';
                switch ($input['IMAGE_KEY']) {
                    case 'true':
                        $suffix = '_1';
                        break;
                    case 'view1':
                        $suffix = '_v1';
                        break;
                    case 'view2':
                        $suffix = '_v2';
                        break;
                    case 'bnr':
                        $suffix = 'B';
                        break;
                    case 'dash':
                        $suffix = '_Dsh';
                        break;
                    case 'dbak':
                        $suffix = '_Dbk';
                        break;
                    case 'SRMI':
                        $suffix = 'MI';
                        break;
                    case 'SRMB':
                        $suffix = 'MB';
                        break;
                    case 'SRDI':
                        $suffix = 'DI';
                        break;
                    case 'SRDB':
                        $suffix = 'DB';
                        break;
                    case 'SRHI':
                        $suffix = 'HI';
                        break;
                    case 'SRHB':
                        $suffix = 'HB';
                        break;
                }

                $fileExtension = $req->file('file')->getClientOriginalExtension();
                $fileName = $input['DASH_SECTION_ID'] . $input['DASH_ID'] . $suffix . "." . $fileExtension;
                $req->file('file')->storeAs('dashboard', $fileName);
                $url = asset(storage::url('app/dashboard')) . "/" . $fileName;

                if (isset($input['IMAGE_KEY'])) {
                    $fields = [
                        'DIS_ID' => $input['DIS_ID'],
                        'SYM_ID' => $input['SYM_ID'],
                        'DASH_NAME' => $input['DASH_NAME'],
                        'DASH_TYPE' => $input['DASH_TYPE'],
                        'DASH_DESCRIPTION' => $input['DASH_DESCRIPTION'],
                        'STATUS' => $input['STATUS'],
                        'POSITION' => $input['POSITION'],
                        'GR_POSITION' => $input['GR_POSITION'],
                        // 'MERGE_24X7' => $input['MERGE_24X7'] ?? null,
                        'INDASH' => $input['INDASH'] ?? null,
                    ];

                    switch ($input['IMAGE_KEY']) {
                        case 'true':
                            $fields['PHOTO1_URL'] = $url;
                            break;
                        case 'view1':
                            $fields['VIEW1_URL'] = $url;
                            break;
                        case 'view2':
                            $fields['VIEW2_URL'] = $url;
                            break;
                        case 'bnr':
                            $fields['BANNER_URL'] = $url;
                            break;
                        case 'dash':
                            $fields['DASH_URL'] = $url;
                            break;
                        case 'dbak':
                            $fields['DASH_BAK'] = $url;
                            break;
                        case 'SRMI':
                            $fields['PHOTO_URL'] = $url;
                            break;
                        case 'SRMB':
                            $fields['VIEW2_URL'] = $url;
                            break;
                        case 'SRDI':
                            $fields['PHOTO1_URL'] = $url;
                            break;
                        case 'SRDB':
                            $fields['BANNER_URL'] = $url;
                            break;
                        case 'SRHI':
                            $fields['VIEW1_URL'] = $url;
                            break;
                        case 'SRHB':
                            $fields['GR_BANNER_URL'] = $url;
                            break;
                        default:
                            $fields['PHOTO_URL'] = $url;
                            break;
                    }
                }
                if ($input['IMAGE_KEY'] === 'dbak') {
                    DB::table('dashboard')
                        ->where(['INDASH' => 'true', 'DASH_SECTION_ID' => $input['DASH_SECTION_ID']])
                        ->update(['DASH_BAK' => $url]);
                } elseif ($input['IMAGE_KEY'] === 'dash') {
                    if ($input['DASH_SECTION_ID'] === 'AH' || $input['DASH_SECTION_ID'] === 'AM') {
                        DB::table('dashboard')
                            ->where(['DASH_TYPE' => $input['DASH_TYPE'], 'DASH_SECTION_ID' => $input['DASH_SECTION_ID']])
                            ->update(['DASH_URL' => $url]);
                    } else {
                        DB::table('dashboard')
                            ->where(['DASH_ID' => $input['DASH_ID'], 'DASH_SECTION_ID' => $input['DASH_SECTION_ID']])
                            ->update(['DASH_URL' => $url]);
                    }
                } else {
                    DB::table('dashboard')
                        ->where(['DASH_ID' => $input['DASH_ID'], 'DASH_SECTION_ID' => $input['DASH_SECTION_ID']])
                        ->update($fields);
                }
                $response = [
                    'Success' => true,
                    'Message' => 'Records update successfully.',
                    'PHOTO_URL' => $url,
                    'code' => 200
                ];
            } else {
                $fields = [
                    'DIS_ID' => $input['DIS_ID'],
                    'SYM_ID' => $input['SYM_ID'],
                    'DASH_NAME' => $input['DASH_NAME'],
                    'DASH_TYPE' => $input['DASH_TYPE'],
                    'DASH_DESCRIPTION' => $input['DASH_DESCRIPTION'],
                    'STATUS' => $input['STATUS'],
                    'POSITION' => $input['POSITION'],
                    'GR_POSITION' => $input['GR_POSITION'],
                    // 'MERGE_24X7' => $input['MERGE_24X7'] ?? null,
                    'INDASH' => $input['INDASH'] ?? null,
                ];
                DB::table('dashboard')
                    ->where(['DASH_ID' => $input['DASH_ID'], 'DASH_SECTION_ID' => $input['DASH_SECTION_ID']])
                    ->update($fields);
                $response = [
                    'Success' => true,
                    'Message' => 'Records update successfully.',
                    'code' => 200
                ];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function del_maindash(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input   = $req->all();

            DB::table('dashboard')->where(['DASH_ID' => $input['DASH_ID'], 'DASH_SECTION_ID' => $input['DASH_SECTION_ID']])->delete();
            $filesToDelete = ['PHOTO_URL', 'PHOTO1_URL', 'VIEW1_URL', 'VIEW2_URL', 'BANNER_URL', 'GR_BANNER_URL'];
            foreach ($filesToDelete as $fileKey) {
                if ($req->has($fileKey)) {
                    Storage::delete($req->input($fileKey));
                } else {
                    Log::info('File does not exist: ' . $req->input($fileKey));
                }
            }
            $response = [
                'Success' => true,
                'Message' => 'Records delete successfully.',
                'code' => 200
            ];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_splst(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input   = $req->all();

            if ($req->file('file') !== null) {
                $suffix = '';
                switch ($input['IMAGE_KEY']) {
                    case 'true':
                        $suffix = '_1';
                        break;
                    case 'bnr':
                        $suffix = 'B';
                        break;
                    case 'bnr1':
                        $suffix = 'B1';
                        break;
                    case 'bnr2':
                        $suffix = 'B2';
                        break;
                    case 'hn':
                        $suffix = 'hn';
                        break;
                }

                $fileExtension = $req->file('file')->getClientOriginalExtension();
                $fileName = $input['DASH_ID'] . $input['DIS_ID'] . $suffix . "." . $fileExtension;
                $req->file('file')->storeAs('specialist', $fileName);
                $url = asset(storage::url('app/specialist')) . "/" . $fileName;

                if (isset($input['IMAGE_KEY'])) {
                    $fields = [
                        'DIS_SL' => $input['DIS_SL'],
                        'DIS_TYPE' => $input['DIS_TYPE'],
                        'DIS_DESC' => $input['DIS_DESC'],
                        'TYPE_SL' => $input['TYPE_SL'],
                        'DIS_CATEGORY' => $input['DIS_CATEGORY'],
                        'SPECIALIST' => $input['SPECIALIST'],
                        'SPECIALITY' => $input['SPECIALITY'],
                        'STATUS' => $input['STATUS'],

                    ];
                    // $fields1 = [
                    //     'DASH_SECTION_ID' => 'SP',
                    //     'DASH_SECTION_NAME' => 'Speciality/Department',
                    //     'DIS_ID' => $input['DIS_ID'],
                    //     'DASH_SL' => $input['DIS_SL'],
                    //     'POSITION' => $input['DIS_SL'],
                    //     'DASH_NAME' => $input['DIS_CATEGORY'],
                    //     'DASH_TYPE' => 'Item',
                    //     'CATEGORY' => 'H',
                    //     'STATUS' => $input['STATUS'],
                    // ];
                    switch ($input['IMAGE_KEY']) {
                        case 'true':
                            $fields['PHOTO1_URL'] = $url;
                            break;
                        case 'bnr':
                            $fields['BANNER_URL'] = $url;
                            break;
                        case 'bnr1':
                            $fields['BANNER1_URL'] = $url;
                            break;
                        case 'hn':
                            $fields['PHOTO2_URL'] = $url;
                            break;
                        case 'bnr2':
                            $fields['BANNER2_URL'] = $url;
                            break;
                        default:
                            $fields['PHOTO_URL'] = $url;
                            break;
                    }
                }
                DB::table('dis_catg')
                    ->where(['DIS_ID' => $input['DIS_ID']])
                    ->update($fields);
                // DB::table('dashboard')
                //     ->where(['DIS_ID' => $input['DIS_ID'], 'CATEGORY' => 'H'])
                //     ->update($fields1);
                $response = [
                    'Success' => true,
                    'Message' => 'Records update successfully.',
                    'PHOTO_URL' => $url,
                    'code' => 200
                ];
            } else {
                $fields = [
                    'DIS_SL' => $input['DIS_SL'],
                    'DIS_TYPE' => $input['DIS_TYPE'],
                    'DIS_DESC' => $input['DIS_DESC'],
                    'TYPE_SL' => $input['TYPE_SL'],
                    'DIS_CATEGORY' => $input['DIS_CATEGORY'],
                    'SPECIALIST' => $input['SPECIALIST'],
                    'SPECIALITY' => $input['SPECIALITY'],
                    'STATUS' => $input['STATUS'],
                ];
                // $fields1 = [
                //     'DASH_SECTION_ID' => 'SP',
                //     'DASH_SECTION_NAME' => 'Speciality/Department',
                //     'DIS_ID' => $input['DIS_ID'],
                //     'DASH_SL' => $input['DIS_SL'],
                //     'POSITION' => $input['DIS_SL'],
                //     'DASH_NAME' => $input['DIS_CATEGORY'],
                //     'DASH_TYPE' => 'Item',
                //     'STATUS' => $input['STATUS'],
                // ];
                DB::table('dis_catg')
                    ->where(['DIS_ID' => $input['DIS_ID']])
                    ->update($fields);
                // DB::table('dashboard')
                //     ->where(['DIS_ID' => $input['DIS_ID'], 'CATEGORY' => 'H'])
                //     ->update($fields1);

                $response = [
                    'Success' => true,
                    'Message' => 'Records update successfully.',
                    'code' => 200
                ];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    // function updt_header(Request $req)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $response = array();
    //         $input   = $req->all();
    //         $url = null;
    //         if ($req->file('file') !== null) {
    //             $fileExtension = $req->file('file')->getClientOriginalExtension();
    //             $fileName = $input['DASH_SECTION_ID'] . "B." . $fileExtension;
    //             $req->file('file')->storeAs('header_banner', $fileName);
    //             $url = asset(storage::url('app/header_banner')) . "/" . $fileName;
    //         }
            
    //         if ($input['DASH_TYPE'] != $input['DASH_TYPE1']) {
    //             $fields = [
    //                 'DASH_SECTION_NAME' => $input['DASH_SECTION_NAME'],
    //                 'CATEGORY' => $input['CATEGORY'],
    //                 'COLOR_CODE' => $input['COLOR_CODE'],
    //                 'GR_BANNER_URL' => $url,
    //                 'DASH_TYPE' => $input['DASH_TYPE'],
    //                 'GR_POSITION' => $input['GR_POSITION'],
    //                 'STATUS' => $input['STATUS'
    //                 ]];
    //                 if ($input['DASH_SECTION_ID'] === 'SR') {
    //                     $fields = ['DASH_SECTION_DESC_SR' => $input['DASH_SECTION_DESC_SR']];
    //                 } else {
    //                     $fields = ['DASH_SECTION_DESC' => $input['DASH_SECTION_DESC']];
    //                 }
    //                 DB::table('dashboard')
    //                     ->where(['DASH_SECTION_ID' => $input['DASH_SECTION_ID'], 'DASH_TYPE' => $input['DASH_TYPE1']])
    //                     // ->Orwhere(['FACILITY_ID' => $input['DASH_SECTION_ID'], 'DASH_TYPE' => $input['DASH_TYPE1']])
    //                     ->update($fields);  
    //             // DB::table('dashboard')
    //             // ->where(['DASH_SECTION_ID' => $input['DASH_SECTION_ID'], 'DASH_TYPE' => $input['DASH_TYPE1']])
    //             // ->update($fields);
    //         } elseif ($input['GR_POSITION'] != $input['GR_POSITION1']) {
    //             $fields = [
    //                 'DASH_SECTION_NAME' => $input['DASH_SECTION_NAME'],
    //                 'CATEGORY' => $input['CATEGORY'],
    //                 'COLOR_CODE' => $input['COLOR_CODE'],
    //                 'GR_BANNER_URL' => $url,
    //                 'DASH_TYPE' => $input['DASH_TYPE'],
    //                 'GR_POSITION' => $input['GR_POSITION'],
    //                 'STATUS' => $input['STATUS'
    //                 ]];
    //                 if ($input['DASH_SECTION_ID'] === 'SR') {
    //                     $fields = ['DASH_SECTION_DESC_SR' => $input['DASH_SECTION_DESC_SR']];
    //                 } else {
    //                     $fields = ['DASH_SECTION_DESC' => $input['DASH_SECTION_DESC']];
    //                 }
    //                 DB::table('dashboard')
    //                     ->where(['DASH_SECTION_ID' => $input['DASH_SECTION_ID'], 'GR_POSITION' => $input['GR_POSITION1']])
    //                     // ->Orwhere(['FACILITY_ID' => $input['DASH_SECTION_ID'], 'GR_POSITION' => $input['GR_POSITION1']])
    //                     ->update($fields);  
    //             // $fields = ['GR_POSITION' => $input['GR_POSITION'], 'STATUS' => $input['STATUS']];
    //             // DB::table('dashboard')->where(['DASH_SECTION_ID' => $input['DASH_SECTION_ID'], 'DASH_TYPE' => $input['DASH_TYPE1']])->update($fields);
    //         } elseif ($input['STATUS'] != $input['STATUS1']) {
    //             $fields = [
    //                 'DASH_SECTION_NAME' => $input['DASH_SECTION_NAME'],
    //                 'CATEGORY' => $input['CATEGORY'],
    //                 'COLOR_CODE' => $input['COLOR_CODE'],
    //                 'GR_BANNER_URL' => $url,
    //                 'DASH_TYPE' => $input['DASH_TYPE'],
    //                 'GR_POSITION' => $input['GR_POSITION'],
    //                 'STATUS' => $input['STATUS'
    //                 ]];
    //                 if ($input['DASH_SECTION_ID'] === 'SR') {
    //                     $fields = ['DASH_SECTION_DESC_SR' => $input['DASH_SECTION_DESC_SR']];
    //                 } else {
    //                     $fields = ['DASH_SECTION_DESC' => $input['DASH_SECTION_DESC']];
    //                 }
    //                 DB::table('dashboard')
    //                     ->where(['DASH_SECTION_ID' => $input['DASH_SECTION_ID'], 'STATUS' => $input['STATUS1']])
    //                     // ->Orwhere(['FACILITY_ID' => $input['DASH_SECTION_ID'], 'STATUS' => $input['STATUS1']])
    //                     ->update($fields);  

    //             // $fields = ['STATUS' => $input['STATUS']];
    //             // DB::table('dashboard')->where(['DASH_SECTION_ID' => $input['DASH_SECTION_ID'], 'DASH_TYPE' => $input['DASH_TYPE1']])->update($fields);
    //         } else {
    //             $fields = [
    //                 'DASH_SECTION_NAME' => $input['DASH_SECTION_NAME'],
    //                 'CATEGORY' => $input['CATEGORY'],
    //                 'COLOR_CODE' => $input['COLOR_CODE'],
    //                 'GR_BANNER_URL' => $url,
    //                 'DASH_TYPE' => $input['DASH_TYPE'],
    //                 'GR_POSITION' => $input['GR_POSITION'],
    //                 'STATUS' => $input['STATUS']
    //             ];

    //             return $url;
    //             if ($input['DASH_SECTION_ID'] === 'SR') {
    //                 $fields = ['DASH_SECTION_DESC_SR' => $input['DASH_SECTION_DESC_SR']];
    //             } else {
    //                 $fields = ['DASH_SECTION_DESC' => $input['DASH_SECTION_DESC']];
    //             }
    //             DB::table('dashboard')
    //                 ->where(['DASH_SECTION_ID' => $input['DASH_SECTION_ID']])
    //                 ->Orwhere(['FACILITY_ID' => $input['DASH_SECTION_ID']])
    //                 ->update($fields);
    //         }
    //         $response = ['Success' => true, 'Message' => 'Records update successfully.','PHOTO_URL' => $url, 'code' => 200];
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
    //     }
    //     return $response;
    // }

    function updt_header(Request $req)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response = array();
        $input = $req->all();
        $url = null;

        if ($req->file('file') !== null) {
            $fileExtension = $req->file('file')->getClientOriginalExtension();
            $fileName = $input['DASH_SECTION_ID'] . "B." . $fileExtension;
            $req->file('file')->storeAs('header_banner', $fileName);
            $url = asset(Storage::url('app/header_banner/' . $fileName));
        }

        $fields = [
            'DASH_SECTION_NAME' => $input['DASH_SECTION_NAME'],
            'CATEGORY' => $input['CATEGORY'],
            'COLOR_CODE' => $input['COLOR_CODE'],
            'GR_BANNER_URL' => $url,
            'DASH_TYPE' => $input['DASH_TYPE'],
            'GR_POSITION' => $input['GR_POSITION'],
            'STATUS' => $input['STATUS']
        ];

        if ($input['DASH_SECTION_ID'] === 'SR') {
            $fields['DASH_SECTION_DESC_SR'] = $input['DASH_SECTION_DESC_SR'];
        } else {
            $fields['DASH_SECTION_DESC'] = $input['DASH_SECTION_DESC'];
        }

        if ($input['DASH_TYPE'] != $input['DASH_TYPE1']) {
            DB::table('dashboard')
                ->where(['DASH_SECTION_ID' => $input['DASH_SECTION_ID'], 'DASH_TYPE' => $input['DASH_TYPE1']])
                ->update($fields);
        } elseif ($input['GR_POSITION'] != $input['GR_POSITION1']) {
            DB::table('dashboard')
                ->where(['DASH_SECTION_ID' => $input['DASH_SECTION_ID'], 'GR_POSITION' => $input['GR_POSITION1']])
                ->update($fields);
        } elseif ($input['STATUS'] != $input['STATUS1']) {
            DB::table('dashboard')
                ->where(['DASH_SECTION_ID' => $input['DASH_SECTION_ID'], 'STATUS' => $input['STATUS1']])
                ->update($fields);
        } else {
            DB::table('dashboard')
                ->where(['DASH_SECTION_ID' => $input['DASH_SECTION_ID']])
                ->orWhere(['FACILITY_ID' => $input['DASH_SECTION_ID']])
                ->update($fields);
        }

        $response = ['Success' => true, 'Message' => 'Records updated successfully.', 'PHOTO_URL' => $url, 'code' => 200];
    } else {
        $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
    }
    return $response;
}


    function updt_splsthdr(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input   = $req->all();
            $fields = ['DASH_SECTION_DESC' => $input['DASH_SECTION_DESC']];
            DB::table('dis_catg')->update($fields);
            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_splst(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input   = $req->all();

            $fileExtension = $req->file('file')->getClientOriginalExtension();
            $fileName = $input['DASH_ID'] . $input['DIS_ID'] . "." . $fileExtension;
            $req->file('file')->storeAs('specialist', $fileName);
            $url = asset(storage::url('app/specialist')) . "/" . $fileName;

            $fields = [
                'DIS_ID' => $input['DIS_ID'],
                'DIS_SL' => $input['DIS_SL'],
                'DIS_TYPE' => $input['DIS_TYPE'],
                'DIS_DESC' => $input['DIS_DESC'],
                'TYPE_SL' => $input['TYPE_SL'],
                'DIS_CATEGORY' => $input['DIS_CATEGORY'],
                'SPECIALIST' => $input['SPECIALIST'],
                'SPECIALITY' => $input['SPECIALITY'],
                'STATUS' => $input['STATUS'],
                'PHOTO_URL' => $url,
            ];
            DB::table('dis_catg')->insert($fields);

            // $fields1 = [
            //     'DASH_SECTION_ID' => 'SP',
            //     'DASH_SECTION_NAME' => 'Speciality/Department',
            //     'DIS_ID' => $input['DIS_ID'],
            //     'DASH_SL' => $input['DIS_SL'],
            //     'POSITION' => $input['DIS_SL'],
            //     'DASH_NAME' => $input['DIS_CATEGORY'],
            //     'DASH_TYPE' => 'Item',
            //     'STATUS' => $input['STATUS'],
            //     'CATEGORY' => 'H',
            //     'PHOTO_URL' => $url,
            // ];
            // DB::table('dashboard')->insert($fields1);

            $response = [
                'Success' => true,
                'Message' => 'Records add successfully.',
                'PHOTO_URL' => $url,
                'code' => 200
            ];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_symp(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input   = $req->all();

            $fileExtension = $req->file('file')->getClientOriginalExtension();
            $fileName = $input['DASH_ID'] . $input['SYM_ID'] . "." . $fileExtension;
            $req->file('file')->storeAs('symptoms', $fileName);
            $url = asset(storage::url('app/symptoms')) . "/" . $fileName;

            $fields = [
                'SYM_ID' => $input['SYM_ID'],
                'SYM_SL' => $input['SYM_SL'],
                'DASH_ID' => $input['DASH_ID'],
                'DIS_ID' => $input['DIS_ID'],
                'SYM_NAME' => $input['SYM_NAME'],
                'DIS_CATEGORY' => $input['DIS_CATEGORY'],
                'DESCRIPTION' => $input['DESCRIPTION'],
                'SYM_TYPE' => $input['SYM_TYPE'],
                'SYM_SL' => $input['SYM_SL'],
                'STATUS' => $input['STATUS'],
                'PHOTO_URL' => $url,
            ];

            DB::table('symptoms')->insert($fields);

            $response = [
                'Success' => true,
                'Message' => 'Records add successfully.',
                'PHOTO_URL' => $url,
                'code' => 200
            ];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_symp(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $response = array();
            $input = $req->all();

            if ($req->file('file') !== null) {
                $suffix = '';
                switch ($input['IMAGE_KEY']) {
                    case 'Diag':
                        $suffix = '_2';
                        break;
                    case 'Dash':
                        $suffix = '_1';
                        break;
                    case 'bnr':
                        $suffix = 'B';
                        break;
                }

                $fileExtension = $req->file('file')->getClientOriginalExtension();
                $fileName = $input['DASH_ID'] . $input['SYM_ID'] . $suffix . "." . $fileExtension;
                $req->file('file')->storeAs('symptoms', $fileName);
                $url = asset(storage::url('app/symptoms')) . "/" . $fileName;

                if (isset($input['IMAGE_KEY'])) {
                    $fields = [
                        'SYM_SL' => $input['SYM_SL'],
                        'DASH_ID' => $input['DASH_ID'],
                        'DIS_ID' => $input['DIS_ID'],
                        'SYM_NAME' => $input['SYM_NAME'],
                        'DIS_CATEGORY' => $input['DIS_CATEGORY'],
                        'DESCRIPTION' => $input['DESCRIPTION'],
                        'SYM_TYPE' => $input['SYM_TYPE'],
                        'SYM_SL' => $input['SYM_SL'],
                        'STATUS' => $input['STATUS'],
                    ];
                    switch ($input['IMAGE_KEY']) {
                        case 'Diag':
                            $fields['DIAG_PHOTO'] = $url;
                            break;
                        case 'Dash':
                            $fields['DASH_PHOTO'] = $url;
                            break;
                        case 'bnr':
                            $fields['BANNER_URL'] = $url;
                            break;
                        default:
                            $fields['PHOTO_URL'] = $url;
                            break;
                    }
                }
                DB::table('symptoms')->where(['SYM_ID' => $input['SYM_ID']])->update($fields);
                $response = ['Success' => true, 'Message' => 'Records update successfully.', 'PHOTO_URL' => $url, 'code' => 200];
            } else {
                $fields = [
                    'SYM_SL' => $input['SYM_SL'],
                    'DASH_ID' => $input['DASH_ID'],
                    'DIS_ID' => $input['DIS_ID'],
                    'SYM_NAME' => $input['SYM_NAME'],
                    'DIS_CATEGORY' => $input['DIS_CATEGORY'],
                    'DESCRIPTION' => $input['DESCRIPTION'],
                    'SYM_TYPE' => $input['SYM_TYPE'],
                    'SYM_SL' => $input['SYM_SL'],
                    'STATUS' => $input['STATUS'],
                ];
                DB::table('symptoms')->where(['SYM_ID' => $input['SYM_ID']])->update($fields);
                $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_surg(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input   = $req->all();

            $fileExtension = $req->file('file')->getClientOriginalExtension();
            $fileName = $input['DASH_ID'] . $input['SURG_ID'] . "." . $fileExtension;
            $req->file('file')->storeAs('surgery', $fileName);
            $url = asset(storage::url('app/surgery')) . "/" . $fileName;

            $fields = [
                'SURG_ID' => $input['SURG_ID'],
                'SURG_SL' => $input['SURG_SL'],
                'DASH_ID' => $input['DASH_ID'],
                'DIS_ID' => $input['DIS_ID'],
                'SURG_NAME' => $input['SURG_NAME'],
                'DIS_CATEGORY' => $input['DIS_CATEGORY'],
                'DESCRIPTION' => $input['DESCRIPTION'],
                'SURG_TYPE' => $input['SURG_TYPE'],
                'SURG_SL' => $input['SURG_SL'],
                'STATUS' => $input['STATUS'],
                'PHOTO_URL' => $url,
            ];

            DB::table('surgery')->insert($fields);

            $response = [
                'Success' => true,
                'Message' => 'Records add successfully.',
                'PHOTO_URL' => $url,
                'code' => 200
            ];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_surg(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $response = array();
            $input = $req->all();

            if ($req->file('file') !== null) {
                $suffix = '';
                switch ($input['IMAGE_KEY']) {
                    case 'Dash':
                        $suffix = '_1';
                        break;
                    case 'bnr':
                        $suffix = '_B';
                        break;
                }

                $fileExtension = $req->file('file')->getClientOriginalExtension();
                $fileName = $input['DASH_ID'] . $input['SURG_ID'] . $suffix . "." . $fileExtension;
                $req->file('file')->storeAs('surgery', $fileName);
                $url = asset(storage::url('app/surgery')) . "/" . $fileName;

                if (isset($input['IMAGE_KEY'])) {
                    $fields = [
                        'SURG_SL' => $input['SURG_SL'],
                        'DASH_ID' => $input['DASH_ID'],
                        'DIS_ID' => $input['DIS_ID'],
                        'SURG_NAME' => $input['SURG_NAME'],
                        'DIS_CATEGORY' => $input['DIS_CATEGORY'],
                        'DESCRIPTION' => $input['DESCRIPTION'],
                        'SURG_TYPE' => $input['SURG_TYPE'],
                        'SURG_SL' => $input['SURG_SL'],
                        'STATUS' => $input['STATUS'],
                    ];
                    switch ($input['IMAGE_KEY']) {
                        case 'Dash':
                            $fields['DASH_PHOTO'] = $url;
                            break;
                        case 'bnr':
                            $fields['BANNER1_URL'] = $url;
                            break;
                        default:
                            $fields['PHOTO_URL'] = $url;
                            break;
                    }
                }
                DB::table('surgery')->where(['SURG_ID' => $input['SURG_ID']])->update($fields);
                $response = ['Success' => true, 'Message' => 'Records update successfully.', 'PHOTO_URL' => $url, 'code' => 200];
            } else {
                $fields = [
                    'SURG_SL' => $input['SURG_SL'],
                    'DASH_ID' => $input['DASH_ID'],
                    'DIS_ID' => $input['DIS_ID'],
                    'SURG_NAME' => $input['SURG_NAME'],
                    'DIS_CATEGORY' => $input['DIS_CATEGORY'],
                    'DESCRIPTION' => $input['DESCRIPTION'],
                    'SURG_TYPE' => $input['SURG_TYPE'],
                    'SURG_SL' => $input['SURG_SL'],
                    'STATUS' => $input['STATUS'],
                ];
                DB::table('surgery')->where(['SURG_ID' => $input['SURG_ID']])->update($fields);
                $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_surgbnr(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $response = array();
            $input = $req->all();

            if ($req->file('file') !== null) {

                $fileExtension = $req->file('file')->getClientOriginalExtension();
                $fileName = $input['DASH_ID'] . $input['TYPE_SL'] .  "B." . $fileExtension;
                $req->file('file')->storeAs('surgery', $fileName);
                $url = asset(storage::url('app/surgery')) . "/" . $fileName;

                if ($req->file('file')) {
                    $fields = [
                        'TYPE_SL' => $input['TYPE_SL'],
                        'SURG_TYPE' => $input['SURG_TYPE'],
                        'TYPE_DESC' => $input['TYPE_DESC'],
                        'BANNER_URL' =>  $url,
                    ];
                }
                DB::table('surgery')->where(['SURG_TYPE' => $input['BNR_NAME']])->update($fields);
                $response = ['Success' => true, 'Message' => 'Records update successfully.', 'PHOTO_URL' => $url, 'code' => 200];
            } else {
                $fields = [
                    'TYPE_SL' => $input['TYPE_SL'],
                    'SURG_TYPE' => $input['SURG_TYPE'],
                    'TYPE_DESC' => $input['TYPE_DESC'],
                ];
                DB::table('surgery')->where(['SURG_TYPE' => $input['BNR_NAME']])->update($fields);
                $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_slider(Request $request)
    {
        $response = ['Success' => false, 'Message' => 'Failed to update record.', 'code' => 400]; // Default response

        if ($request->isMethod('post')) {
            $input = $request->all();

            $fields = [
                'PROMO_NAME' => $input['PROMO_NAME'],
                'DESCRIPTION' => $input['DESCRIPTION'],
                'PROMO_SL' => $input['PROMO_SL'],
                'PROMO_TYPE' => $input['PROMO_TYPE'],
                'STATUS' => $input['STATUS'],
                'DIS_ID' => $input['DIS_ID'],
                'PKG_ID' => $input['PKG_ID'],
                'DASH_SECTION_ID' => $input['DASH_SECTION_ID'],
                'MOBILE_NO' => $input['MOBILE_NO'] ?? [],
                'PHARMA_ID' => $input['PHARMA_ID'],
                'PROMO_DT' => $input['PROMO_DT'],
                'PROMO_VALID' => $input['PROMO_VALID'],
            ];

            if ($request->hasFile('file')) {
                try {
                    $fileExtension = $request->file('file')->getClientOriginalExtension();
                    $fileName = $input['DASH_SECTION_ID'] . $input['PROMO_ID'] .  "." . $fileExtension;
                    $request->file('file')->storeAs('slider', $fileName);
                    $url = asset(storage::url('app/slider')) . "/" . $fileName;
                    $fields['PROMO_URL'] = $url;
                } catch (\Exception $e) {
                    return ['Success' => false, 'Message' => 'Error uploading file: ' . $e->getMessage(), 'code' => 500];
                }
            }

            DB::table('promo_banner')->where(['PHARMA_ID' => $input['PHARMA_ID'], 'PROMO_ID' => $input['PROMO_ID']])->update($fields);
            $response = ['Success' => true, 'Message' => 'Record updated successfully.', 'PHOTO_URL' => $url ?? null, 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function add_slider(Request $request)
    {
        $response = ['Success' => false, 'Message' => 'Failed to update record.', 'code' => 400]; // Default response

        if ($request->isMethod('post')) {
            $input = $request->all();

            $fields = [
                'PROMO_NAME' => $input['PROMO_NAME'],
                'DESCRIPTION' => $input['DESCRIPTION'],
                'PROMO_SL' => $input['PROMO_SL'],
                'PROMO_TYPE' => $input['PROMO_TYPE'],
                'STATUS' => $input['STATUS'],
                'DIS_ID' => $input['DIS_ID'],
                'PKG_ID' => $input['PKG_ID'],
                'DASH_SECTION_ID' => $input['DASH_SECTION_ID'],
                'HEADER_NAME' => $input['HEADER_NAME'],
                'PHARMA_ID' => $input['PHARMA_ID'],
                'PROMO_DT' => $input['PROMO_DT'],
                'PROMO_VALID' => $input['PROMO_VALID'],
                'MOBILE_NO' => $input['MOBILE_NO'],
            ];

            if ($request->hasFile('file')) {
                try {
                    $fileExtension = $request->file('file')->getClientOriginalExtension();
                    $fileName = $input['DASH_SECTION_ID'] . $input['PROMO_ID'] .  "." . $fileExtension;
                    $request->file('file')->storeAs('slider', $fileName);
                    $url = asset(storage::url('app/slider')) . "/" . $fileName;
                    $fields['PROMO_URL'] = $url;
                } catch (\Exception $e) {
                    return ['Success' => false, 'Message' => 'Error uploading file: ' . $e->getMessage(), 'code' => 500];
                }
            }

            DB::table('promo_banner')->insert($fields);
            $response = ['Success' => true, 'Message' => 'Record added successfully.', 'PHOTO_URL' => $url ?? null, 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_test(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $response = array();
            $input = $req->all();

            if ($req->file('file') !== null) {

                $fileExtension = $req->file('file')->getClientOriginalExtension();
                $fileName = $input['DASH_ID'] . $input['SUB_DEPT_ID'] .  "B." . $fileExtension;
                $req->file('file')->storeAs('test', $fileName);
                $url = asset(storage::url('app/test')) . "/" . $fileName;

                if ($req->file('file')) {
                    $fields = [
                        'SUB_DEPT_ID' => $input['SUB_DEPT_ID'],
                        'SUB_DEPARTMENT' => $input['SUB_DEPARTMENT'],
                        'DEPT_DESC' => $input['DEPT_DESC'],
                        'BANNER_URL' =>  $url,
                    ];
                }
                DB::table('master_testdata')->where(['SUB_DEPT_ID' => $input['SUB_DEPT_ID']])->update($fields);
                $response = ['Success' => true, 'Message' => 'Records update successfully.', 'PHOTO_URL' => $url, 'code' => 200];
            } else {
                $fields = [
                    'SUB_DEPT_ID' => $input['SUB_DEPT_ID'],
                    'SUB_DEPARTMENT' => $input['SUB_DEPARTMENT'],
                    'DEPT_DESC' => $input['DEPT_DESC'],
                ];
                DB::table('master_testdata')->where(['SUB_DEPT_ID' => $input['SUB_DEPT_ID']])->update($fields);
                $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }



    function updt_organ(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $response = array();
            $input = $req->all();

            if ($req->file('file') !== null) {

                $fileExtension = $req->file('file')->getClientOriginalExtension();
                $fileName = $input['ORGAN_ID'] . "." . $fileExtension;
                $req->file('file')->storeAs('organ', $fileName);
                $url = asset(storage::url('app/organ')) . "/" . $fileName;

                if ($req->file('file')) {
                    $fields = [
                        'ORGAN_ID' => $input['ORGAN_ID'],
                        'ORGAN_NAME' => $input['ORGAN_NAME'],
                        'TEST_CATG' => $input['TEST_CATG'],
                        'ORGAN_URL' =>  $url,
                    ];
                }
                DB::table('master_testdata')->where(['ORGAN_ID' => $input['ORGAN_ID']])->update($fields);
                $response = ['Success' => true, 'Message' => 'Records update successfully.', 'PHOTO_URL' => $url, 'code' => 200];
            } else {
                $fields = [
                    'ORGAN_ID' => $input['ORGAN_ID'],
                    'ORGAN_NAME' => $input['ORGAN_NAME'],
                    'TEST_CATG' => $input['TEST_CATG'],
                ];
                DB::table('master_testdata')->where(['ORGAN_ID' => $input['ORGAN_ID']])->update($fields);
                $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }

    function updt_drprofile(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array();
            $input = $req->all();
            $fields = [
                // 'DR_ID' => $input['DR_ID'],
                'DIS_ID' => $input['DIS_ID'],
                'ADDRESS' => $input['ADDRESS'],
                'APPROVE' => 'true'
            ];
            $fields1 = [
                // 'DR_ID' => $input['DR_ID'],
                'DIS_ID' => $input['DIS_ID'],

            ];
            DB::table('drprofile')->where(['DR_ID' => $input['ID']])->update($fields);
            DB::table('dr_availablity')->where(['DR_ID' => $input['ID']])->update($fields1);

            $response = ['Success' => true, 'Message' => 'Records update successfully.', 'code' => 200];
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }
        return $response;
    }



    function updt_service(Request $req)
    {
        if (!$req->isMethod('post')) {
            return ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }

        $input = $req->all();
        $response = array();
        $url = null;

        if ($req->hasFile('file')) {
            $suffix = $input['DASH_SECTION_ID'] . ($input['IMAGE_KEY'] ?? '');
            $fileExtension = $req->file('file')->getClientOriginalExtension();
            $fileName = $input['DASH_SECTION_ID'] . $input['DASH_ID'] . $suffix . "." . $fileExtension;
            $req->file('file')->storeAs('service', $fileName);
            $url = asset(storage::url('app/service/' . $fileName));
        }

        $fields = [
            'DIS_ID' => $input['DIS_ID'],
            'SYM_ID' => $input['SYM_ID'],
            'DASH_NAME' => $input['DASH_NAME'],
            'DASH_TYPE' => $input['DASH_TYPE'],
            'DASH_DESCRIPTION' => $input['DASH_DESCRIPTION'],
            'STATUS' => $input['STATUS'],
            'POSITION' => $input['POSITION'],
            'GR_POSITION' => $input['GR_POSITION'],
            'TAG_SECTION' => $input['TAG_SECTION'] ?? null,
            'INDASH' => $input['INDASH'] ?? null,
        ];

        $urlFieldMap = ['AG' => 'URL_24X7_', 'AH' => 'URL_IPD_', 'AI' => 'URL_HOME_', 'AM' => 'URL_2NDGN_'];

        if ($url !== null) {
            $keyPrefix = $urlFieldMap[$input['DASH_SECTION_ID']] ?? '';
            if (!empty($keyPrefix)) {
                $fields[$keyPrefix . $input['IMAGE_KEY']] = $url;
            } elseif ($input['DASH_SECTION_ID'] === 'SR') {
                if ($input['IMAGE_KEY'] === 'MI') {
                    $fields['PHOTO_URL'] = $url;
                } elseif ($input['IMAGE_KEY'] === 'MB') {
                    $fields['VIEW1_URL'] = $url;
                } elseif ($input['IMAGE_KEY'] === 'DI' || $input['IMAGE_KEY'] === 'HI') {
                    $fields['PHOTO1_URL'] = $url;
                } elseif ($input['IMAGE_KEY'] === 'DB' || $input['IMAGE_KEY'] === 'HB') {
                    $fields['VIEW2_URL'] = $url;
                }
            }
        }

        DB::table('dashboard')
            ->where(['DASH_ID' => $input['DASH_ID']])
            ->update($fields);

        $response = [
            'Success' => True,
            'Message' => 'Records update successfully.',
            'PHOTO_URL' => $url ?? 'No file uploaded',
            'code' => 200
        ];

        return $response;
    }

    function updt_grservice(Request $req)
    {
        if (!$req->isMethod('post')) {
            return ['Success' => false, 'Message' => 'Method Not Allowed.', 'code' => 405];
        }

        $input = $req->all();
        $response = array();
        $url = null;

        if ($req->hasFile('file')) {
            $suffix = $suffix = strtoupper(substr(md5($input['DASH_SECTION_ID'] . $input['DASH_TYPE'] . $input['IMAGE_KEY']), 0, 10));
            $fileExtension = $req->file('file')->getClientOriginalExtension();
            $fileName = $suffix . "." . $fileExtension;
            $req->file('file')->storeAs('service', $fileName);
            $url = asset(storage::url('app/service/' . $fileName));
        }

        $fields = [
            'GR_POSITION' => $input['GR_POSITION'],
            'DASH_TYPE' => $input['DASH_TYPE'],
            'GR_DESC' => $input['GR_DESC'],
            'STATUS' => $input['STATUS'],
        ];

        if ($input['DASH_SECTION_ID'] == 'AM') {
            $fields['INDASH'] = $input['INDASH'];
        } else if ($input['DASH_SECTION_ID'] == 'SR') {
            $fields['INDASHSR'] = $input['INDASH'];
        }

        $urlFieldMap = ['AG' => 'URL_24X7_', 'AH' => 'URL_IPD_', 'AI' => 'URL_HOME_', 'AM' => 'URL_2NDGN_'];

        if ($url !== null) {
            $keyPrefix = $urlFieldMap[$input['DASH_SECTION_ID']] ?? '';
            if (!empty($keyPrefix)) {
                $fields[$keyPrefix . $input['IMAGE_KEY']] = $url;
            }
        }
        // RETURN $fields;
        try {
            DB::table('dashboard')
                ->where('DASH_TYPE', $input['SERVICE_NAME'])
                ->where('TAG_SECTION', 'LIKE', '%' . $input['DASH_SECTION_ID'] . '%')
                ->update($fields);
            $response = [
                'Success' => True,
                'Message' => 'Records updated successfully.',
                'PHOTO_URL' => $url ?? 'No file uploaded',
                'code' => 200
            ];
        } catch (\Illuminate\Database\QueryException $ex) {
            $response = [
                'Success' => False,
                'Message' => 'Failed to update records. Error: ' . $ex->getMessage(),
                'code' => 500
            ];
        }

        return $response;
    }
}
