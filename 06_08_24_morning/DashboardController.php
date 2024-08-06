<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;

class DashboardController extends Controller
{
    function admindashboard(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['PHARMA_ID'])) {

                $pid = $input['PHARMA_ID'];
                $data = array();

                $banner = DB::table('promo_banner')->select('DASH_SECTION_ID', 'PROMO_ID', 'PROMO_NAME', 'PROMO_URL', 'PROMO_TYPE', 'MOBILE_NO', 'DESCRIPTION', 'STATUS')
                    ->where(['DASH_SECTION_ID' => 'PB'])
                    ->orwhere(['PHARMA_ID' => $pid])
                    ->get();

                //SECTION-A #### SLIDER
                $fltr_promo_bnr = $banner->filter(function ($item) {
                    return $item->PROMO_TYPE === 'Slider';
                });
                $A["Slider"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "SLDR_ID" => $item->PROMO_ID,
                        "SLDR_NAME" => $item->PROMO_NAME,
                        "SLDR_URL" => $item->PROMO_URL,
                    ];
                })->values()->all();

                //SECTION-B ####
                $fltr_bnr = $banner->filter(function ($item) {
                    return $item->PROMO_TYPE === 'Call';
                });
                $B["Call_Banner"] = $fltr_bnr->values()->all();

                //SECTION-C ####
                $C['Dashboard'] = DB::table('admin_dashboard_details')->where(['STATUS' => 'Active'])->get();

                //SECTION-D ####
                $fltr_bnr = $banner->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'PB';
                });
                $D["Promo_Banner"] = $fltr_bnr->values()->all();

                $data = $A + $B + $C + $D;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }



    function hndashboard1(Request $request)
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $promo_bnr = DB::table('promo_banner')->where('STATUS', 'Active')->get();
                // $dash = DB::table('dashboard')->where('CATEGORY', 'like', '%' . 'H' . '%')->where('STATUS', 'Active')->orderby('DASH_SL')->get();
                $dash = DB::table('dashboard_section')
                    ->join('dashboard_item', 'dashboard_section.DASH_SECTION_ID', 'dashboard_item.DASH_SECTION_ID')
                    ->select(
                        'dashboard_section.DASH_SECTION_ID',
                        'dashboard_section.DASH_SECTION_NAME',
                        'dashboard_section.DS_DESCRIPTION',
                        'dashboard_section.DS_TYPE',
                        'dashboard_section.DS_POSITION',
                        'dashboard_section.DS_TAGGED',
                        'dashboard_section.DS_STATUS',
                        'dashboard_section.DSIMG1',
                        'dashboard_section.DSIMG2',
                        'dashboard_section.DSIMG3',
                        'dashboard_section.DSIMG4',
                        'dashboard_section.DSIMG5',
                        'dashboard_section.DSIMG6',
                        'dashboard_section.DSIMG7',
                        'dashboard_section.DSIMG8',
                        'dashboard_section.DSIMG9',
                        'dashboard_section.DSIMG10',
                        'dashboard_section.DSBNR1',
                        'dashboard_section.DSBNR2',
                        'dashboard_section.DSBNR3',
                        'dashboard_section.DSBNR4',
                        'dashboard_section.DSBNR5',
                        'dashboard_section.DSBNR6',
                        'dashboard_section.DSBNR7',
                        'dashboard_section.DSBNR8',
                        'dashboard_section.DSBNR9',
                        'dashboard_section.DSBNR10',
                        'dashboard_section.DSQA1',
                        'dashboard_section.DSQA2',
                        'dashboard_section.DSQA3',
                        'dashboard_section.DSQA4',
                        'dashboard_section.DSQA5',
                        'dashboard_section.DSQA6',
                        'dashboard_section.DSQA7',
                        'dashboard_section.DSQA8',
                        'dashboard_section.DSQA9',
                        // 'dashboard_section.DSSL1',
                        // 'dashboard_section.DSSL2',
                        // 'dashboard_section.DSSL3',
                        // 'dashboard_section.DSSL4',
                        // 'dashboard_section.DSSL5',
                        // 'dashboard_section.DSSL6',
                        // 'dashboard_section.DSSL7',
                        // 'dashboard_section.DSSL8',
                        // 'dashboard_section.DSSL9',
                        // 'dashboard_section.DSSL10',
                        'dashboard_item.ID',
                        'dashboard_item.DASH_NAME',
                        'dashboard_item.DASH_DESC',
                        'dashboard_item.DASH_POSITION',
                        'dashboard_item.DASH_ITEM_TAGGED',
                        'dashboard_item.DASH_STATUS',
                        'dashboard_item.DI_IMG1',
                        'dashboard_item.DI_IMG2',
                        'dashboard_item.DI_IMG3',
                        'dashboard_item.DI_IMG4',
                        'dashboard_item.DI_IMG5',
                        'dashboard_item.DI_IMG6',
                        'dashboard_item.DI_IMG7',
                        'dashboard_item.DI_IMG8',
                        'dashboard_item.DI_IMG9',
                        'dashboard_item.DI_IMG10',
                        'dashboard_item.DI_BNR1',
                        'dashboard_item.DI_BNR2',
                        'dashboard_item.DI_BNR3',
                        'dashboard_item.DI_BNR4',
                        'dashboard_item.DI_BNR5',
                        'dashboard_item.DI_BNR6',
                        'dashboard_item.DI_BNR7',
                        'dashboard_item.DI_BNR8',
                        'dashboard_item.DI_BNR9',
                        'dashboard_item.DI_BNR10',
                        'dashboard_item.DIQA1',
                        'dashboard_item.DIQA2',
                        'dashboard_item.DIQA3',
                        'dashboard_item.DIQA4',
                        'dashboard_item.DIQA5',
                        'dashboard_item.DIQA6',
                        'dashboard_item.DIQA7',
                        'dashboard_item.DIQA8',
                        'dashboard_item.DIQA9',
                        // 'dashboard_item.DISL1',
                        // 'dashboard_item.DISL2',
                        // 'dashboard_item.DISL3',
                        // 'dashboard_item.DISL4',
                        // 'dashboard_item.DISL5',
                        // 'dashboard_item.DISL6',
                        // 'dashboard_item.DISL7',
                        // 'dashboard_item.DISL8',
                        // 'dashboard_item.DISL9',
                        // 'dashboard_item.DISL10'
                    )
                    ->where('dashboard_section.DS_TAGGED', 'like', '%' . 'H' . '%')
                    ->where('dashboard_section.DS_STATUS', 'Active')
                    // ->orderby('dashboard_section.DS_POSITION')
                    // ->orderby('dashboard_item.DASH_POSITION')
                    ->orderByRaw('CASE WHEN dashboard_section.DSSL2 IS NULL THEN 1 ELSE 0 END, dashboard_section.DSSL2 ASC')
                    ->orderByRaw('CASE WHEN dashboard_item.DISL2 IS NULL THEN 1 ELSE 0 END, dashboard_item.DISL2 ASC')
                    ->get();
                $pharma = DB::table('pharmacy')
                    ->select('PHARMA_ID', 'ITEM_NAME AS PHARMA_NAME', 'CLINIC_TYPE', 'ADDRESS', 'CITY', 'DIST', 'STATE', 'PIN', 'CLINIC_MOBILE', 'PHOTO_URL', 'LOGO_URL', 'LATITUDE', 'LONGITUDE', DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude)) * COS(RADIANS('$latt')) * COS(RADIANS(Longitude - '$lont')) + SIN(RADIANS(Latitude)) * SIN(RADIANS('$latt'))))),2) as KM"))
                    ->where(['CLINIC_TYPE' => 'Hospital', 'STATUS' => 'Active'])
                    ->orderby('KM')->take(25)->get()->ToArray();

                //SECTION-A #### SLIDER
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'HS';
                });
                $A["Slider"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "SLIDER_ID" => $item->PROMO_ID,
                        "SLIDER_NAME" => $item->PROMO_NAME,
                        "SLIDER_URL" => $item->PROMO_URL,
                    ];
                })->values()->take(4)->all();

                //SECTION-DASH_A #### DASHBOARD
                $dash1 = DB::table('facility_section')
                    // ->orderBy('DSSL2')
                    ->whereIn('DASH_SECTION_ID', ['AH', 'AG', 'SP', 'SR', 'AP', 'AI', 'AL', 'AM'])
                    ->orderByRaw('CASE WHEN facility_section.DSSL2 IS NULL THEN 1 ELSE 0 END, facility_section.DSSL2 ASC')
                    ->get();

                $B["Dashboard"] = $dash1->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        // "PHOTO_URL" => $item->URL_SURGERY_HI,
                        // "BANNER_URL" => $item->DN_BANNER_URL,
                        "PHOTO_URL1" => $item->DSIMG1,
                        "PHOTO_URL2" => $item->DSIMG2,
                        "PHOTO_URL3" => $item->DSIMG3,
                        "PHOTO_URL4" => $item->DSIMG4,
                        "PHOTO_URL5" => $item->DSIMG5,
                        "PHOTO_URL6" => $item->DSIMG6,
                        "PHOTO_URL7" => $item->DSIMG7,
                        "PHOTO_URL8" => $item->DSIMG8,
                        "PHOTO_URL9" => $item->DSIMG9,
                        "PHOTO_URL10" => $item->DSIMG10,

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
                // $SPLST_DTL = DB::table('dis_catg')->select('DIS_ID', 'DASH_SECTION_ID', 'DIS_TYPE', 'DIS_CATEGORY', 'SPECIALIST', 'SPECIALITY', 'PHOTO_URL')->take(7)->orderBy('DIS_SL')->get()->toArray();

                // $SPLST_DTL = DB::table('dis_catg')
                //     ->select(
                //         'DIS_ID',
                //         'DASH_SECTION_ID',
                //         'DIS_TYPE',
                //         'DIS_CATEGORY',
                //         'SPECIALIST',
                //         'SPECIALITY',
                //         'DISIMG1',
                //         'DISIMG2',
                //         'DISIMG3',
                //         'DISIMG4',
                //         'DISIMG5',
                //         'DISIMG6',
                //         'DISIMG7',
                //         'DISIMG8',
                //         'DISIMG9',
                //         'DISIMG10',
                //         'DISBNR1',
                //         'DISBNR2',
                //         'DISBNR3',
                //         'DISBNR4',
                //         'DISBNR5',
                //         'DISBNR6',
                //         'DISBNR7',
                //         'DISBNR8',
                //         'DISBNR9',
                //         'DISBNR10',
                //         'DISQA1',
                //         'DISQA2',
                //         'DISQA3',
                //         'DISQA4',
                //         'DISQA5',
                //         'DISQA6',
                //         'DISQA7',
                //         'DISQA8',
                //         'DISQA9',

                //     )
                //     ->orderByRaw('CASE WHEN dis_catg.SPSL2 IS NULL THEN 1 ELSE 0 END, dis_catg.SPSL2 ASC')->get()->map(function ($item) {
                //         return [
                //             "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                //             "DIS_ID" => $item->DIS_ID,
                //             "DIS_TYPE" => $item->DIS_TYPE,
                //             "DIS_CATEGORY" => $item->DIS_CATEGORY,
                //             "SPECIALIST" => $item->SPECIALIST,
                //             "SPECIALITY" => $item->SPECIALITY,
                //             "PHOTO_URL1" => $item->DISIMG1,
                //             "PHOTO_URL2" => $item->DISIMG2,
                //             "PHOTO_URL3" => $item->DISIMG3,
                //             "PHOTO_URL4" => $item->DISIMG4,
                //             "PHOTO_URL5" => $item->DISIMG5,
                //             "PHOTO_URL6" => $item->DISIMG6,
                //             "PHOTO_URL7" => $item->DISIMG7,
                //             "PHOTO_URL8" => $item->DISIMG8,
                //             "PHOTO_URL9" => $item->DISIMG9,
                //             "PHOTO_URL10" => $item->DISIMG10,
                //             "BANNER_URL1" => $item->DISBNR1,
                //             "BANNER_URL2" => $item->DISBNR2,
                //             "BANNER_URL3" => $item->DISBNR3,
                //             "BANNER_URL4" => $item->DISBNR4,
                //             "BANNER_URL5" => $item->DISBNR5,
                //             "BANNER_URL6" => $item->DISBNR6,
                //             "BANNER_URL7" => $item->DISBNR7,
                //             "BANNER_URL8" => $item->DISBNR8,
                //             "BANNER_URL9" => $item->DISBNR9,
                //             "BANNER_URL10" => $item->DISBNR10,
                //             "Questions" => [
                //                 "QA1" => $item->DISQA1,
                //                 "QA2" => $item->DISQA2,
                //                 "QA3" => $item->DISQA3,
                //                 "QA4" => $item->DISQA4,
                //                 "QA5" => $item->DISQA5,
                //                 "QA6" => $item->DISQA6,
                //                 "QA7" => $item->DISQA7,
                //                 "QA8" => $item->DISQA8,
                //                 "QA9" => $item->DISQA9
                //             ]
                //         ];
                //     })->values()->all();
                $data1 = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->where('facility_section.DASH_SECTION_ID', 'SP')
                    // ->orderBy('facility_type.DT_POSITION')
                    // ->orderBy('facility.DN_POSITION')
                    ->orderByRaw('CASE WHEN facility_type.DTSL2 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL2 ASC')
                    ->orderByRaw('CASE WHEN facility.DNSL2 IS NULL THEN 1 ELSE 0 END, facility.DNSL2 ASC')
                    ->get();

                $SPLST_DTL = [];
                foreach ($data1 as $row2) {
                    if (!isset($SPLST_DTL[$row2->DASH_SECTION_ID])) {
                        $SPLST_DTL[$row2->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DESCRIPTION" => $row2->DS_DESCRIPTION,
                            // "PHOTO_URL" => $row2->DSM_PHOTO_URL,
                            // "BANNER_URL" => $row2->DS_BANNER_URL,
                            "PHOTO_URL1" => $row2->DSIMG1,
                            "PHOTO_URL2" => $row2->DSIMG2,
                            "PHOTO_URL3" => $row2->DSIMG3,
                            "PHOTO_URL4" => $row2->DSIMG4,
                            "PHOTO_URL5" => $row2->DSIMG5,
                            "PHOTO_URL6" => $row2->DSIMG6,
                            "PHOTO_URL7" => $row2->DSIMG7,
                            "PHOTO_URL8" => $row2->DSIMG8,
                            "PHOTO_URL9" => $row2->DSIMG9,
                            "PHOTO_URL10" => $row2->DSIMG10,

                            "BANNER_URL1" => $row2->DSBNR1,
                            "BANNER_URL2" => $row2->DSBNR2,
                            "BANNER_URL3" => $row2->DSBNR3,
                            "BANNER_URL4" => $row2->DSBNR4,
                            "BANNER_URL5" => $row2->DSBNR5,
                            "BANNER_URL6" => $row2->DSBNR6,
                            "BANNER_URL7" => $row2->DSBNR7,
                            "BANNER_URL8" => $row2->DSBNR8,
                            "BANNER_URL9" => $row2->DSBNR9,
                            "BANNER_URL10" => $row2->DSBNR10,
                            "Facilities" => []
                        ];
                    }

                    if (!isset($SPLST_DTL[$row2->DASH_SECTION_ID]['Facilities'][$row2->DASH_TYPE])) {
                        $SPLST_DTL[$row2->DASH_SECTION_ID]['Facilities'][$row2->DASH_TYPE] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DASH_TYPE_ID" => $row2->DASH_TYPE_ID,
                            "DASH_TYPE" => $row2->DASH_TYPE,
                            "DESCRIPTION" => $row2->DT_DESCRIPTION,

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

                    $SPLST_DTL[$row2->DASH_SECTION_ID]['Facilities'][$row2->DASH_TYPE]['FACILITY_DETAILS'][] = [
                        "DASH_ID" => $row2->DASH_ID,
                        "DASH_NAME" => $row2->DASH_NAME,
                        "DASH_TYPE" => $row2->DASH_TYPE,
                        "DASH_TYPE_ID" => $row2->DASH_TYPE_ID,
                        "DESCRIPTION" => $row2->DN_DESCRIPTION,
                        "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
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


                // $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                //     return $item->DASH_SECTION_ID === 'SP';
                // });

                // $SPB["Specialist_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                // })->take(3)->values()->all();

                $D["Department"] = array_values($SPLST_DTL);



                //SECTION-E #### International Patient
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'AP';
                });
                $E_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "ID" => $item->ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DS_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESC,
                        "PHOTO_URL" => $item->DSIMG1,
                    ];
                })->values()->all();
                $E["Banner_IP"] = array_values($E_DTL);

                //SECTION-#### Surgery
                $surg = DB::table('facility')
                    ->join('facility_type', 'facility.DASH_TYPE_ID', '=', 'facility_type.DASH_TYPE_ID')
                    ->join('facility_section', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->where('facility_section.DASH_SECTION_ID', 'like', '%' . 'SR' . '%')
                    // ->orderBy('facility.DN_POSITION')
                    ->orderByRaw('CASE WHEN facility.DNSL2 IS NULL THEN 1 ELSE 0 END, facility.DNSL2 ASC')
                    ->get();

                $SURG_DTL = $surg->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_TYPE_ID" => $item->DASH_TYPE_ID,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_ID" => $item->DASH_ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_SL" => $item->DN_POSITION,
                        "DESCRIPTION" => $item->DN_DESCRIPTION,

                        "PHOTO_URL1" => $item->DNIMG1,
                        "PHOTO_URL2" => $item->DNIMG2,
                        "PHOTO_URL3" => $item->DNIMG3,
                        "PHOTO_URL4" => $item->DNIMG4,
                        "PHOTO_URL5" => $item->DNIMG5,
                        "PHOTO_URL6" => $item->DNIMG6,
                        "PHOTO_URL7" => $item->DNIMG7,
                        "PHOTO_URL8" => $item->DNIMG8,
                        "PHOTO_URL9" => $item->DNIMG9,
                        "PHOTO_URL10" => $item->DNIMG10,

                        "BANNER_URL1" => $item->DNBNR1,
                        "BANNER_URL2" => $item->DNBNR2,
                        "BANNER_URL3" => $item->DNBNR3,
                        "BANNER_URL4" => $item->DNBNR4,
                        "BANNER_URL5" => $item->DNBNR5,
                        "BANNER_URL6" => $item->DNBNR6,
                        "BANNER_URL7" => $item->DNBNR7,
                        "BANNER_URL8" => $item->DNBNR8,
                        "BANNER_URL9" => $item->DNBNR9,
                        "BANNER_URL10" => $item->DNBNR10,
                        "Questions" => [
                            [
                                "QA1" => $item->DNQA1,
                                "QA2" => $item->DNQA2,
                                "QA3" => $item->DNQA3,
                                "QA4" => $item->DNQA4,
                                "QA5" => $item->DNQA5,
                                "QA6" => $item->DNQA6,
                                "QA7" => $item->DNQA7,
                                "QA8" => $item->DNQA8,
                                "QA9" => $item->DNQA9
                            ]
                        ]
                    ];
                })->values()->take(10)->all();

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

                $data2 = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->where('facility_section.DASH_SECTION_ID', 'AL')
                    // ->orderBy('facility_type.DT_POSITION')
                    // ->orderBy('facility.DN_POSITION')
                    ->orderByRaw('CASE WHEN facility_type.DTSL2 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL2 ASC')
                    ->orderByRaw('CASE WHEN facility.DNSL2 IS NULL THEN 1 ELSE 0 END, facility.DNSL2 ASC')
                    ->get();

                $groupedData = [];
                foreach ($data2 as $row2) {
                    if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
                        $groupedData[$row2->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DESCRIPTION" => $row2->DS_DESCRIPTION,
                            "PHOTO_URL1" => $row2->DSIMG1,
                            "PHOTO_URL2" => $row2->DSIMG2,
                            "PHOTO_URL3" => $row2->DSIMG3,
                            "PHOTO_URL4" => $row2->DSIMG4,
                            "PHOTO_URL5" => $row2->DSIMG5,
                            "PHOTO_URL6" => $row2->DSIMG6,
                            "PHOTO_URL7" => $row2->DSIMG7,
                            "PHOTO_URL8" => $row2->DSIMG8,
                            "PHOTO_URL9" => $row2->DSIMG9,
                            "PHOTO_URL10" => $row2->DSIMG10,
                            "BANNER_URL1" => $row2->DSBNR1,
                            "BANNER_URL2" => $row2->DSBNR2,
                            "BANNER_URL3" => $row2->DSBNR3,
                            "BANNER_URL4" => $row2->DSBNR4,
                            "BANNER_URL5" => $row2->DSBNR5,
                            "BANNER_URL6" => $row2->DSBNR6,
                            "BANNER_URL7" => $row2->DSBNR7,
                            "BANNER_URL8" => $row2->DSBNR8,
                            "BANNER_URL9" => $row2->DSBNR9,
                            "BANNER_URL10" => $row2->DSBNR10,
                            "DASH_NAME" => [] // Change to just hold facility details
                        ];
                    }

                    $groupedData[$row2->DASH_SECTION_ID]['DASH_NAME'][] = [
                        "DASH_ID" => $row2->DASH_ID,
                        "DASH_NAME" => $row2->DASH_NAME,
                        "DASH_TYPE" => $row2->DASH_TYPE,
                        "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                        "DESCRIPTION" => $row2->DN_DESCRIPTION,
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
                $G["Insurance"] = array_values($groupedData);



                //SECTION-H #### IPD Section
                $data1 = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->where('facility_section.DASH_SECTION_ID', 'AH')
                    // ->orderBy('facility_type.DT_POSITION')
                    // ->orderBy('facility.DN_POSITION')
                    ->orderByRaw('CASE WHEN facility_type.DTSL2 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL2 ASC')
                    ->orderByRaw('CASE WHEN facility.DNSL2 IS NULL THEN 1 ELSE 0 END, facility.DNSL2 ASC')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row2) {
                    if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
                        $groupedData[$row2->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DESCRIPTION" => $row2->DS_DESCRIPTION,
                            // "PHOTO_URL" => $row2->DSM_PHOTO_URL,
                            // "BANNER_URL" => $row2->DS_BANNER_URL,
                            "PHOTO_URL1" => $row2->DSIMG1,
                            "PHOTO_URL2" => $row2->DSIMG2,
                            "PHOTO_URL3" => $row2->DSIMG3,
                            "PHOTO_URL4" => $row2->DSIMG4,
                            "PHOTO_URL5" => $row2->DSIMG5,
                            "PHOTO_URL6" => $row2->DSIMG6,
                            "PHOTO_URL7" => $row2->DSIMG7,
                            "PHOTO_URL8" => $row2->DSIMG8,
                            "PHOTO_URL9" => $row2->DSIMG9,
                            "PHOTO_URL10" => $row2->DSIMG10,

                            "BANNER_URL1" => $row2->DSBNR1,
                            "BANNER_URL2" => $row2->DSBNR2,
                            "BANNER_URL3" => $row2->DSBNR3,
                            "BANNER_URL4" => $row2->DSBNR4,
                            "BANNER_URL5" => $row2->DSBNR5,
                            "BANNER_URL6" => $row2->DSBNR6,
                            "BANNER_URL7" => $row2->DSBNR7,
                            "BANNER_URL8" => $row2->DSBNR8,
                            "BANNER_URL9" => $row2->DSBNR9,
                            "BANNER_URL10" => $row2->DSBNR10,
                            "DASH_TYPE" => []
                        ];
                    }

                    if (!isset($groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE])) {
                        $groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DASH_TYPE" => $row2->DASH_TYPE,
                            "DESCRIPTION" => $row2->DT_DESCRIPTION,

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
                        "DASH_NAME" => $row2->DASH_NAME,
                        "DASH_TYPE" => $row2->DASH_TYPE,
                        "DESCRIPTION" => $row2->DN_DESCRIPTION,
                        "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
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
                $H["IPD_Section"] = array_values($groupedData);

                //SECTION-I #### Emergency
                $data1 = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->where('facility_section.DASH_SECTION_ID', 'AG')
                    // ->orderBy('facility_type.DT_POSITION')
                    // ->orderBy('facility.DN_POSITION')
                    ->orderByRaw('CASE WHEN facility_type.DTSL2 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL2 ASC')
                    ->orderByRaw('CASE WHEN facility.DNSL2 IS NULL THEN 1 ELSE 0 END, facility.DNSL2 ASC')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row2) {
                    if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
                        $groupedData[$row2->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DESCRIPTION" => $row2->DS_DESCRIPTION,
                            // "PHOTO_URL" => $row2->DSM_PHOTO_URL,
                            // "BANNER_URL" => $row2->DS_BANNER_URL,

                            "PHOTO_URL1" => $row2->DSIMG1,
                            "PHOTO_URL2" => $row2->DSIMG2,
                            "PHOTO_URL3" => $row2->DSIMG3,
                            "PHOTO_URL4" => $row2->DSIMG4,
                            "PHOTO_URL5" => $row2->DSIMG5,
                            "PHOTO_URL6" => $row2->DSIMG6,
                            "PHOTO_URL7" => $row2->DSIMG7,
                            "PHOTO_URL8" => $row2->DSIMG8,
                            "PHOTO_URL9" => $row2->DSIMG9,
                            "PHOTO_URL10" => $row2->DSIMG10,

                            "BANNER_URL1" => $row2->DSBNR1,
                            "BANNER_URL2" => $row2->DSBNR2,
                            "BANNER_URL3" => $row2->DSBNR3,
                            "BANNER_URL4" => $row2->DSBNR4,
                            "BANNER_URL5" => $row2->DSBNR5,
                            "BANNER_URL6" => $row2->DSBNR6,
                            "BANNER_URL7" => $row2->DSBNR7,
                            "BANNER_URL8" => $row2->DSBNR8,
                            "BANNER_URL9" => $row2->DSBNR9,
                            "BANNER_URL10" => $row2->DSBNR10,
                            "DASH_TYPE" => []
                        ];
                    }

                    if (!isset($groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE])) {
                        $groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DASH_TYPE" => $row2->DASH_TYPE,
                            "DESCRIPTION" => $row2->DT_DESCRIPTION,

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
                        "DASH_NAME" => $row2->DASH_NAME,
                        "DASH_TYPE" => $row2->DASH_TYPE,
                        "DESCRIPTION" => $row2->DN_DESCRIPTION,
                        "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
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
                $a = array_values($groupedData);
                $I["Emergency"] = $a;


                //SECTION-AJ #### 2nd Opinion

                $I_DTL = DB::table('facility_type')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->where(['facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->where('facility_type.DT_TAG_SECTION', 'like', '%AM%')
                    // ->orderBy('facility_type.DT_POSITION')
                    ->orderByRaw('CASE WHEN facility_type.DTSL2 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL2 ASC')
                    // ->orderByRaw('CASE WHEN facility.DNSL2 IS NULL THEN 1 ELSE 0 END, facility.DNSL2 ASC')
                    // ->orderBy('facility.DN_POSITION')
                    ->get();
                // return $I_DTL;

                $groupedData = [];
                foreach ($I_DTL as $row2) {
                    $ds_dtl = DB::table('facility_section')->where('DASH_SECTION_ID', 'AM')->first();
                    $DsID = $ds_dtl->DASH_SECTION_ID;
                    $Dsname = $ds_dtl->DASH_SECTION_NAME;

                    if (!isset($groupedData[$ds_dtl->DASH_SECTION_ID])) {
                        $groupedData[$ds_dtl->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $ds_dtl->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $ds_dtl->DASH_SECTION_NAME,
                            "DESCRIPTION" => $ds_dtl->DS_DESCRIPTION,
                            "PHOTO_URL1" => $ds_dtl->DSIMG1,
                            "PHOTO_URL2" => $ds_dtl->DSIMG2,
                            "PHOTO_URL3" => $ds_dtl->DSIMG3,
                            "PHOTO_URL4" => $ds_dtl->DSIMG4,
                            "PHOTO_URL5" => $ds_dtl->DSIMG5,
                            "PHOTO_URL6" => $ds_dtl->DSIMG6,
                            "PHOTO_URL7" => $ds_dtl->DSIMG7,
                            "PHOTO_URL8" => $ds_dtl->DSIMG8,
                            "PHOTO_URL9" => $ds_dtl->DSIMG9,
                            "PHOTO_URL10" => $ds_dtl->DSIMG10,

                            "BANNER_URL1" => $ds_dtl->DSBNR1,
                            "BANNER_URL2" => $ds_dtl->DSBNR2,
                            "BANNER_URL3" => $ds_dtl->DSBNR3,
                            "BANNER_URL4" => $ds_dtl->DSBNR4,
                            "BANNER_URL5" => $ds_dtl->DSBNR5,
                            "BANNER_URL6" => $ds_dtl->DSBNR6,
                            "BANNER_URL7" => $ds_dtl->DSBNR7,
                            "BANNER_URL8" => $ds_dtl->DSBNR8,
                            "BANNER_URL9" => $ds_dtl->DSBNR9,
                            "BANNER_URL10" => $ds_dtl->DSBNR10,
                            "DASH_TYPE" => []
                        ];
                    }

                    if (!isset($groupedData[$ds_dtl->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE])) {
                        $groupedData[$ds_dtl->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE] = [
                            "DASH_SECTION_ID" => $DsID,
                            "DASH_SECTION_NAME" => $Dsname,
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

                    $groupedData[$ds_dtl->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE]['FACILITY_DETAILS'][] = [
                        "DASH_ID" => $row2->DASH_ID,
                        // "DIS_ID" => $row2->DIS_ID,
                        // "SYM_ID" => $row2->SYM_ID,
                        "DASH_NAME" => $row2->DASH_NAME,
                        "DASH_TYPE" => $row2->DASH_TYPE,
                        "DESCRIPTION" => $row2->DN_DESCRIPTION,
                        "DASH_SECTION_ID" => $ds_dtl->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $ds_dtl->DASH_SECTION_NAME,
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


                $AJ["Second_Opinion"] = array_values($groupedData);

                //**************International Patient*****************
                $INTL_P = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->where('facility_section.DASH_SECTION_ID', 'AP')
                    // ->orderBy('facility_type.DT_POSITION')
                    // ->orderBy('facility.DN_POSITION')
                    ->orderByRaw('CASE WHEN facility_type.DTSL2 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL2 ASC')
                    ->orderByRaw('CASE WHEN facility.DNSL2 IS NULL THEN 1 ELSE 0 END, facility.DNSL2 ASC')
                    ->get();
                $groupedData = [];
                foreach ($INTL_P as $row2) {
                    if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
                        $groupedData[$row2->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DESCRIPTION" => $row2->DS_DESCRIPTION,
                            "PHOTO_URL1" => $row2->DSIMG1,
                            "PHOTO_URL2" => $row2->DSIMG2,
                            "PHOTO_URL3" => $row2->DSIMG3,
                            "PHOTO_URL4" => $row2->DSIMG4,
                            "PHOTO_URL5" => $row2->DSIMG5,
                            "PHOTO_URL6" => $row2->DSIMG6,
                            "PHOTO_URL7" => $row2->DSIMG7,
                            "PHOTO_URL8" => $row2->DSIMG8,
                            "PHOTO_URL9" => $row2->DSIMG9,
                            "PHOTO_URL10" => $row2->DSIMG10,
                            "BANNER_URL1" => $row2->DSBNR1,
                            "BANNER_URL2" => $row2->DSBNR2,
                            "BANNER_URL3" => $row2->DSBNR3,
                            "BANNER_URL4" => $row2->DSBNR4,
                            "BANNER_URL5" => $row2->DSBNR5,
                            "BANNER_URL6" => $row2->DSBNR6,
                            "BANNER_URL7" => $row2->DSBNR7,
                            "BANNER_URL8" => $row2->DSBNR8,
                            "BANNER_URL9" => $row2->DSBNR9,
                            "BANNER_URL10" => $row2->DSBNR10,
                            "DASH_NAME" => [] // Change to just hold facility details
                        ];
                    }

                    $groupedData[$row2->DASH_SECTION_ID]['DASH_NAME'][] = [
                        "DASH_ID" => $row2->DASH_ID,
                        "DASH_NAME" => $row2->DASH_NAME,
                        "DASH_TYPE" => $row2->DASH_TYPE,
                        "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                        "DESCRIPTION" => $row2->DN_DESCRIPTION,
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

                $INTL_PAT["International_Patient"] = array_values($groupedData);


                //***********Client 1************/

                $cldata = DB::table('pharmacy')
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
                        'pharmacy.CBNR_URL1',
                        'pharmacy.LOGO_URL',
                        'pharmacy.LATITUDE',
                        'pharmacy.LONGITUDE',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
     * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
      * SIN(RADIANS('$latt'))))),2) as KM")
                    )
                    ->where('pharmacy.PHARMA_ID', '=', 12)
                    ->get();

                $fltr_symp1 = DB::table('symptoms')
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
                    ->whereIn('symptoms.SYM_ID', [111, 115, 113, 116])
                    // ->orderby('SYM_SL')
                    ->get()->map(function ($item) {
                        return [
                            "SYM_ID" => $item->SYM_ID,
                            "DASH_NAME" => $item->SYM_NAME,
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

                $groupedData = [];
                foreach ($cldata as $data) {
                    $fltr_dash = DB::table('dashboard_section')
                        ->join('dashboard_item', 'dashboard_section.DASH_SECTION_ID', 'dashboard_item.DASH_SECTION_ID')
                        // ->leftjoin('dis_catg','dashboard_section.DASH_SECTION_ID','dashboard_item.DASH_SECTION_ID')
                        // ->where('dashboard_section.DS_TAGGED', 'like', '%' . 'M' . '%')
                        ->where(['dashboard_section.DS_STATUS' => 'Active', 'dashboard_item.DASH_SECTION_ID' => 'A'])
                        ->whereIn('dashboard_item.ID', [2])
                        ->orderby('dashboard_section.DS_POSITION')
                        ->orderby('dashboard_item.DASH_POSITION')
                        ->get();

                    $groupedDataItem = [
                        'PHARMA_ID' => $data->PHARMA_ID,
                        'PHARMA_NAME' => $data->PHARMA_NAME,
                        'CLINIC_TYPE' => $data->CLINIC_TYPE,
                        'ADDRESS' => $data->ADDRESS,
                        'CITY' => $data->CITY,
                        'DIST' => $data->DIST,
                        'STATE' => $data->STATE,
                        'PIN' => $data->PIN,
                        'CLINIC_MOBILE' => $data->CLINIC_MOBILE,
                        'PHOTO_URL' => $data->PHOTO_URL,
                        'LOGO_URL' => $data->LOGO_URL,
                        'BANNER_URL' => $data->CBNR_URL1,
                        'LATITUDE' => $data->LATITUDE,
                        'LONGITUDE' => $data->LONGITUDE,
                        'KM' => $data->KM,
                        'DETAILS' => $fltr_dash->map(function ($item) {
                            return [
                                "ID" => $item->ID,
                                "DASH_NAME" => $item->DASH_NAME,
                                "DASH_SECTION_ID" => $item->FACILITY_ID,
                                "DASH_SECTION_NAME" => $item->DASH_NAME,
                                "DESCRIPTION" => $item->DASH_DESC,
                                "PHOTO_URL1" => $item->DI_IMG1,
                                "PHOTO_URL2" => $item->DI_IMG2,
                                "PHOTO_URL3" => $item->DI_IMG3,
                                "PHOTO_URL4" => $item->DI_IMG4,
                                "PHOTO_URL5" => $item->DI_IMG5,
                                "PHOTO_URL6" => $item->DI_IMG6,
                                "PHOTO_URL7" => $item->DI_IMG7,
                                "PHOTO_URL8" => $item->DI_IMG8,
                                "PHOTO_URL9" => $item->DI_IMG9,
                                "PHOTO_URL10" => $item->DI_IMG10,
                                "BANNER_URL1" => $item->DI_BNR1,
                                "BANNER_URL2" => $item->DI_BNR2,
                                "BANNER_URL3" => $item->DI_BNR3,
                                "BANNER_URL4" => $item->DI_BNR4,
                                "BANNER_URL5" => $item->DI_BNR5,
                                "BANNER_URL6" => $item->DI_BNR6,
                                "BANNER_URL7" => $item->DI_BNR7,
                                "BANNER_URL8" => $item->DI_BNR8,
                                "BANNER_URL9" => $item->DI_BNR9,
                                "BANNER_URL10" => $item->DI_BNR10,
                                "Questions" => [
                                    "QA1" => $item->DIQA1,
                                    "QA2" => $item->DIQA2,
                                    "QA3" => $item->DIQA3,
                                    "QA4" => $item->DIQA4,
                                    "QA5" => $item->DIQA5,
                                    "QA6" => $item->DIQA6,
                                    "QA7" => $item->DIQA7,
                                    "QA8" => $item->DIQA8,
                                    "QA9" => $item->DIQA9
                                ]
                            ];
                        })->values()->all(),
                        'details' => $fltr_symp1

                    ];
                    $filr_surg = $surg->filter(function ($item) {
                        return in_array($item->DASH_ID, [227, 228]);
                    });
                    $SURG_DTL1 = $filr_surg->map(function ($item) {
                        return [
                            "DASH_ID" => $item->DASH_ID,
                            "DASH_NAME" => $item->DASH_NAME,
                            "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                            "DASH_TYPE_ID" => $item->DASH_TYPE_ID,
                            "DASH_TYPE" => $item->DASH_TYPE,
                            "DESCRIPTION" => $item->DN_DESCRIPTION,
                            "PHOTO_URL1" => $item->DNIMG1,
                            "PHOTO_URL2" => $item->DNIMG2,
                            "PHOTO_URL3" => $item->DNIMG3,
                            "PHOTO_URL4" => $item->DNIMG4,
                            "PHOTO_URL5" => $item->DNIMG5,
                            "PHOTO_URL6" => $item->DNIMG6,
                            "PHOTO_URL7" => $item->DNIMG7,
                            "PHOTO_URL8" => $item->DNIMG8,
                            "PHOTO_URL9" => $item->DNIMG9,
                            "PHOTO_URL10" => $item->DNIMG10,

                            "BANNER_URL1" => $item->DNBNR1,
                            "BANNER_URL2" => $item->DNBNR2,
                            "BANNER_URL3" => $item->DNBNR3,
                            "BANNER_URL4" => $item->DNBNR4,
                            "BANNER_URL5" => $item->DNBNR5,
                            "BANNER_URL6" => $item->DNBNR6,
                            "BANNER_URL7" => $item->DNBNR7,
                            "BANNER_URL8" => $item->DNBNR8,
                            "BANNER_URL9" => $item->DNBNR9,
                            "BANNER_URL10" => $item->DNBNR10,
                            "Questions" => [
                                [
                                    "QA1" => $item->DNQA1,
                                    "QA2" => $item->DNQA2,
                                    "QA3" => $item->DNQA3,
                                    "QA4" => $item->DNQA4,
                                    "QA5" => $item->DNQA5,
                                    "QA6" => $item->DNQA6,
                                    "QA7" => $item->DNQA7,
                                    "QA8" => $item->DNQA8,
                                    "QA9" => $item->DNQA9
                                ]
                            ]
                        ];
                    })->values()->all();

                    foreach ($SURG_DTL1 as $surgDtlItem) {
                        $groupedDataItem['DETAILS'][] = $surgDtlItem;
                    }

                    $groupedData[] = $groupedDataItem;
                }

                $AG["Client2"] = array_values($groupedData);

                $data = $A + $B + $C + $D + $E + $F + $G + $H + $I + $AJ + $INTL_PAT + $AG;

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function diagdash(Request $request)
    {
        if ($request->isMethod('post')) {
            date_default_timezone_set('Asia/Kolkata');
            $input = $request->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];
                $data = collect();

                $data = $data->merge($this->getLiveDrDt($latt, $lont));
                

               
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
    private function getCatgDrDt($latt, $lont, $did)
    {

        $weekNumber = Carbon::now()->weekOfMonth;
        $day1 = date('l');
        $cdy = date('d');
        $cdt = date('Ymd');

        $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));
        $totdr =  DB::table('pharmacy')
        ->join('dr_availablity', function ($join) use ($day1, $weekNumber, $cdy) {
            $join->on('pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
                ->where(function ($query) use ($day1, $weekNumber, $cdy) {
                    $query->where('dr_availablity.SCH_DAY', $day1)
                        ->where('dr_availablity.WEEK', 'like', '%' . $weekNumber . '%')
                        ->orWhere('dr_availablity.SCH_DT', $cdy);
                });
        })
        ->join('drprofile', 'dr_availablity.DR_ID', '=', 'drprofile.DR_ID')
        ->distinct('drprofile.DR_ID')
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
            ->where(['pharmacy.STATUS' => 'Active'])
            ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
            ->where('drprofile.APPROVE', 'true')
            ->get();

        return $totdr;

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
                            }
                        }      
                   
                }
            }

    
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



    private function getLiveDrDt($latt, $lont)

    {
        // $did = $input['DIS_ID'];
        $data = collect();

        $data = $data->merge($this->getCatgDrDt($latt, $lont, 12));
        return $data;
    }

    function diagdash1(Request $request)
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
                $response = array();
                $data = array();
                $cdy = date('d');
                $cdt = date('Ymd');

                $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));

                $promo_bnr = DB::table('promo_banner')
                    // ->join('dashboard_item', 'dashboard_item.DASH_SECTION_ID', '=', 'dashboard_section.DASH_SECTION_ID')
                    ->leftJoin('dashboard_item', 'dashboard_item.ID', 'promo_banner.DASH_ID')
                    ->where('promo_banner.PHARMA_ID', '=', $pid)
                    ->select(
                        'promo_banner.*',
                        'dashboard_item.DI_IMG1 as PHOTO_URL1',
                        'dashboard_item.DI_IMG2 as PHOTO_URL2',
                        'dashboard_item.DI_IMG3 as PHOTO_URL3',
                        'dashboard_item.DI_IMG4 as PHOTO_URL4',
                        'dashboard_item.DI_IMG5 as PHOTO_URL5',
                        'dashboard_item.DI_IMG6 as PHOTO_URL6',
                        'dashboard_item.DI_IMG7 as PHOTO_URL7',
                        'dashboard_item.DI_IMG8 as PHOTO_URL8',
                        'dashboard_item.DI_IMG9 as PHOTO_URL9',
                        'dashboard_item.DI_IMG10 as PHOTO_URL10'
                    )
                    ->distinct('dashboard_item.ID')
                    ->get();

                function getAvailableDoctors($disId, $pharmaId)
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



                //SECTION-A #### SLIDER

                $promo_bnr1 = DB::table('promo_banner')
                    ->where(['promo_banner.PHARMA_ID' => 0, 'promo_banner.PROMO_TYPE' => 'Slider', 'promo_banner.DASH_SECTION_ID' => 'CL', 'promo_banner.STATUS' => 'Active'])
                    ->select(
                        'promo_banner.*',
                        // 'dashboard_item.DI_IMG1 as PHOTO_URL1',
                        // 'dashboard_item.DI_IMG2 as PHOTO_URL2',
                        // 'dashboard_item.DI_IMG3 as PHOTO_URL3',
                        // 'dashboard_item.DI_IMG4 as PHOTO_URL4',
                        // 'dashboard_item.DI_IMG5 as PHOTO_URL5',
                        // 'dashboard_item.DI_IMG6 as PHOTO_URL6',
                        // 'dashboard_item.DI_IMG7 as PHOTO_URL7',
                        // 'dashboard_item.DI_IMG8 as PHOTO_URL8',
                        // 'dashboard_item.DI_IMG9 as PHOTO_URL9',
                        // 'dashboard_item.DI_IMG10 as PHOTO_URL10'
                    )
                    // ->distinct('dashboard_item.ID')
                    ->get();

                $A["Slider"] = $promo_bnr1->map(function ($item) {
                    return [
                        "SLIDER_ID" => $item->PROMO_ID,
                        "SLIDER_NAME" => $item->PROMO_NAME,
                        "SLIDER_URL" => $item->PROMO_URL,
                    ];
                })->values()->TAKE(4)->all();




                // SECTKON #### CLINIC DATA
                $cldata['Clinic_Data'] = DB::table('pharmacy')
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
                        'pharmacy.GLIMG1 AS PHOTO_URL1',
                        'pharmacy.GLIMG2 AS PHOTO_URL2',
                        'pharmacy.GLIMG3 AS PHOTO_URL3',
                        'pharmacy.GLIMG4 AS PHOTO_URL4',
                        'pharmacy.GLIMG5 AS PHOTO_URL5',
                        'pharmacy.GLIMG6 AS PHOTO_URL6',
                        'pharmacy.GLIMG7 AS PHOTO_URL7',
                        'pharmacy.GLIMG8 AS PHOTO_URL8',
                        'pharmacy.GLIMG9 AS PHOTO_URL9',
                        'pharmacy.GLIMG10 AS PHOTO_URL10',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                 * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                  * SIN(RADIANS('$latt'))))),2) as KM")
                    )
                    ->where('pharmacy.PHARMA_ID', '=', $pid)
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->get();

                // Transform the data
                $cldata['Clinic_Data'] = $cldata['Clinic_Data']->map(function ($item) {
                    $item->GALLERY = [
                        [
                            'PHOTO_URL1' => $item->PHOTO_URL1,
                            'PHOTO_URL2' => $item->PHOTO_URL2,
                            'PHOTO_URL3' => $item->PHOTO_URL3,
                            'PHOTO_URL4' => $item->PHOTO_URL4,
                            'PHOTO_URL5' => $item->PHOTO_URL5,
                            'PHOTO_URL6' => $item->PHOTO_URL6,
                            'PHOTO_URL7' => $item->PHOTO_URL7,
                            'PHOTO_URL8' => $item->PHOTO_URL8,
                            'PHOTO_URL9' => $item->PHOTO_URL9,
                            'PHOTO_URL10' => $item->PHOTO_URL10
                        ]
                    ];

                    // Remove the individual PHOTO_URL fields
                    unset($item->PHOTO_URL1);
                    unset($item->PHOTO_URL2);
                    unset($item->PHOTO_URL3);
                    unset($item->PHOTO_URL4);
                    unset($item->PHOTO_URL5);
                    unset($item->PHOTO_URL6);
                    unset($item->PHOTO_URL7);
                    unset($item->PHOTO_URL8);
                    unset($item->PHOTO_URL9);
                    unset($item->PHOTO_URL10);

                    return $item;
                });


                //SECTION-Z #### DASHBOARD


                $DASH_Z = [];
                // Perform operations based on CLINIC_TYPE
                foreach ($cldata['Clinic_Data'] as $clinic) {
                    if ($clinic->CLINIC_TYPE == 'Hospital') {

                        $dash = DB::table('dashboard_section')
                            ->join('dashboard_item', 'dashboard_section.DASH_SECTION_ID', 'dashboard_item.DASH_SECTION_ID')
                            ->where('dashboard_section.DS_TAGGED', 'like', '%' . 'H' . '%')
                            ->select(
                                'dashboard_section.DASH_SECTION_ID',
                                'dashboard_section.DASH_SECTION_NAME',
                                'dashboard_section.DS_DESCRIPTION',
                                'dashboard_section.DS_TYPE',
                                'dashboard_section.DS_POSITION',
                                'dashboard_section.DS_TAGGED',
                                'dashboard_section.DS_STATUS',
                                'dashboard_section.DSIMG1',
                                'dashboard_section.DSIMG2',
                                'dashboard_section.DSIMG3',
                                'dashboard_section.DSIMG4',
                                'dashboard_section.DSIMG5',
                                'dashboard_section.DSIMG6',
                                'dashboard_section.DSIMG7',
                                'dashboard_section.DSIMG8',
                                'dashboard_section.DSIMG9',
                                'dashboard_section.DSIMG10',
                                'dashboard_section.DSBNR1',
                                'dashboard_section.DSBNR2',
                                'dashboard_section.DSBNR3',
                                'dashboard_section.DSBNR4',
                                'dashboard_section.DSBNR5',
                                'dashboard_section.DSBNR6',
                                'dashboard_section.DSBNR7',
                                'dashboard_section.DSBNR8',
                                'dashboard_section.DSBNR9',
                                'dashboard_section.DSBNR10',
                                'dashboard_section.DSQA1',
                                'dashboard_section.DSQA2',
                                'dashboard_section.DSQA3',
                                'dashboard_section.DSQA4',
                                'dashboard_section.DSQA5',
                                'dashboard_section.DSQA6',
                                'dashboard_section.DSQA7',
                                'dashboard_section.DSQA8',
                                'dashboard_section.DSQA9',
                                'dashboard_item.ID',
                                'dashboard_item.DIS_ID',
                                'dashboard_item.TEST_DEPT_ID',
                                'dashboard_item.DASH_NAME',
                                'dashboard_item.DASH_DESC',
                                'dashboard_item.DASH_POSITION',
                                'dashboard_item.DASH_ITEM_TAGGED',
                                'dashboard_item.FACILITY_ID',
                                'dashboard_item.DASH_STATUS',
                                'dashboard_item.DI_IMG1',
                                'dashboard_item.DI_IMG2',
                                'dashboard_item.DI_IMG3',
                                'dashboard_item.DI_IMG4',
                                'dashboard_item.DI_IMG5',
                                'dashboard_item.DI_IMG6',
                                'dashboard_item.DI_IMG7',
                                'dashboard_item.DI_IMG8',
                                'dashboard_item.DI_IMG9',
                                'dashboard_item.DI_IMG10',
                                'dashboard_item.DI_BNR1',
                                'dashboard_item.DI_BNR2',
                                'dashboard_item.DI_BNR3',
                                'dashboard_item.DI_BNR4',
                                'dashboard_item.DI_BNR5',
                                'dashboard_item.DI_BNR6',
                                'dashboard_item.DI_BNR7',
                                'dashboard_item.DI_BNR8',
                                'dashboard_item.DI_BNR9',
                                'dashboard_item.DI_BNR10',
                                'dashboard_item.DIQA1',
                                'dashboard_item.DIQA2',
                                'dashboard_item.DIQA3',
                                'dashboard_item.DIQA4',
                                'dashboard_item.DIQA5',
                                'dashboard_item.DIQA6',
                                'dashboard_item.DIQA7',
                                'dashboard_item.DIQA8',
                                'dashboard_item.DIQA9'
                            )
                            ->where('dashboard_section.DS_STATUS', 'Active')
                            // ->orderByRaw('CASE WHEN dashboard_section.DSSL4 IS NULL THEN 1 ELSE 0 END, dashboard_section.DSSL4 ASC')
                            ->orderByRaw('CASE WHEN dashboard_item.DISL4 IS NULL THEN 1 ELSE 0 END, dashboard_item.DISL4 ASC')
                            ->get();


                        $fltr_dash = $dash->filter(function ($item) {
                            return $item->DASH_SECTION_ID === 'A' && stripos($item->DASH_ITEM_TAGGED, 'H') !== false;
                        });

                        // Base query with common joins and where conditions
                        $baseQuery = DB::table('facility')
                            ->join('facility_type', 'facility.DASH_TYPE_ID', '=', 'facility_type.DASH_TYPE_ID')
                            ->join('facility_section', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                            ->where('facility_section.DS_STATUS', 'Active')
                            ->where('facility_type.DT_STATUS', 'Active')
                            ->where('facility.DN_STATUS', 'Active');

                        // Surgical facilities query
                        $surg = (clone $baseQuery)
                            ->where('facility_section.DASH_SECTION_ID', 'like', '%SR%')
                            ->orderByRaw('CASE WHEN facility.DNSL4 IS NULL THEN 1 ELSE 0 END, facility.DNSL4 ASC')
                            ->get();

                        // Section - Service from Home query
                        $SFH_DTL = (clone $baseQuery)
                            ->where('facility_section.DASH_SECTION_ID', 'AI')
                            ->orderBy('facility_type.DT_POSITION')
                            ->get();

                        // IPD details query
                        $IPD_DTL = (clone $baseQuery)
                            ->where('facility_section.DASH_SECTION_ID', 'AH')
                            ->orderByRaw('CASE WHEN facility_type.DTSL4 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL4 ASC')
                            ->get();

                        // Emergency details query
                        $EM_DTL = (clone $baseQuery)
                            ->where('facility_section.DASH_SECTION_ID', 'AG')
                            ->orderByRaw('CASE WHEN facility_type.DTSL4 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL4 ASC')
                            ->orderByRaw('CASE WHEN facility.DNSL4 IS NULL THEN 1 ELSE 0 END, facility.DNSL4 ASC')
                            ->get();

                        // SND details query
                        $SND_DTL = (clone $baseQuery)
                            ->where('facility_type.DT_TAG_SECTION', 'like', '%AM%')
                            ->orderByRaw('CASE WHEN facility_type.DTSL4 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL4 ASC')
                            ->get();

                        // INS details query
                        $INS_DTL = (clone $baseQuery)
                            ->where('facility_section.DASH_SECTION_ID', 'AL')
                            ->orderByRaw('CASE WHEN facility_type.DTSL4 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL4 ASC')
                            ->orderByRaw('CASE WHEN facility.DNSL4 IS NULL THEN 1 ELSE 0 END, facility.DNSL4 ASC')
                            ->get();
                        $INTL_P = (clone $baseQuery)
                            ->where('facility_section.DASH_SECTION_ID', 'AP')
                            ->orderByRaw('CASE WHEN facility_type.DTSL4 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL4 ASC')
                            ->orderByRaw('CASE WHEN facility.DNSL4 IS NULL THEN 1 ELSE 0 END, facility.DNSL4 ASC')
                            ->get();

                        $COR_P = (clone $baseQuery)
                            ->where('facility_section.DASH_SECTION_ID', 'TT')
                            ->orderByRaw('CASE WHEN facility_type.DTSL4 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL4 ASC')
                            ->orderByRaw('CASE WHEN facility.DNSL4 IS NULL THEN 1 ELSE 0 END, facility.DNSL4 ASC')
                            ->get();



                        $groupedData = [];
                        foreach ($COR_P as $row2) {
                            if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
                                $groupedData[$row2->DASH_SECTION_ID] = [
                                    "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                                    "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                                    "DESCRIPTION" => $row2->DS_DESCRIPTION,
                                    "PHOTO_URL1" => $row2->DSIMG1,
                                    "PHOTO_URL2" => $row2->DSIMG2,
                                    "PHOTO_URL3" => $row2->DSIMG3,
                                    "PHOTO_URL4" => $row2->DSIMG4,
                                    "PHOTO_URL5" => $row2->DSIMG5,
                                    "PHOTO_URL6" => $row2->DSIMG6,
                                    "PHOTO_URL7" => $row2->DSIMG7,
                                    "PHOTO_URL8" => $row2->DSIMG8,
                                    "PHOTO_URL9" => $row2->DSIMG9,
                                    "PHOTO_URL10" => $row2->DSIMG10,
                                    "BANNER_URL1" => $row2->DSBNR1,
                                    "BANNER_URL2" => $row2->DSBNR2,
                                    "BANNER_URL3" => $row2->DSBNR3,
                                    "BANNER_URL4" => $row2->DSBNR4,
                                    "BANNER_URL5" => $row2->DSBNR5,
                                    "BANNER_URL6" => $row2->DSBNR6,
                                    "BANNER_URL7" => $row2->DSBNR7,
                                    "BANNER_URL8" => $row2->DSBNR8,
                                    "BANNER_URL9" => $row2->DSBNR9,
                                    "BANNER_URL10" => $row2->DSBNR10,
                                    "DASH_NAME" => [] // Change to just hold facility details
                                ];
                            }

                            $groupedData[$row2->DASH_SECTION_ID]['DASH_NAME'][] = [
                                "DASH_ID" => $row2->DASH_ID,
                                "DASH_NAME" => $row2->DASH_NAME,
                                "DASH_TYPE" => $row2->DASH_TYPE,
                                "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                                "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                                "DESCRIPTION" => $row2->DN_DESCRIPTION,
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

                        $COR["Corporate_Partner"] = array_values($groupedData);

                        //SECTION-INTERNATIONAL PATIENT


                        $groupedData = [];
                        foreach ($INTL_P as $row2) {
                            if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
                                $groupedData[$row2->DASH_SECTION_ID] = [
                                    "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                                    "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                                    "DESCRIPTION" => $row2->DS_DESCRIPTION,
                                    "PHOTO_URL1" => $row2->DSIMG1,
                                    "PHOTO_URL2" => $row2->DSIMG2,
                                    "PHOTO_URL3" => $row2->DSIMG3,
                                    "PHOTO_URL4" => $row2->DSIMG4,
                                    "PHOTO_URL5" => $row2->DSIMG5,
                                    "PHOTO_URL6" => $row2->DSIMG6,
                                    "PHOTO_URL7" => $row2->DSIMG7,
                                    "PHOTO_URL8" => $row2->DSIMG8,
                                    "PHOTO_URL9" => $row2->DSIMG9,
                                    "PHOTO_URL10" => $row2->DSIMG10,
                                    "BANNER_URL1" => $row2->DSBNR1,
                                    "BANNER_URL2" => $row2->DSBNR2,
                                    "BANNER_URL3" => $row2->DSBNR3,
                                    "BANNER_URL4" => $row2->DSBNR4,
                                    "BANNER_URL5" => $row2->DSBNR5,
                                    "BANNER_URL6" => $row2->DSBNR6,
                                    "BANNER_URL7" => $row2->DSBNR7,
                                    "BANNER_URL8" => $row2->DSBNR8,
                                    "BANNER_URL9" => $row2->DSBNR9,
                                    "BANNER_URL10" => $row2->DSBNR10,
                                    "DASH_NAME" => [] // Change to just hold facility details
                                ];
                            }

                            $groupedData[$row2->DASH_SECTION_ID]['DASH_NAME'][] = [
                                "DASH_ID" => $row2->DASH_ID,
                                "DASH_NAME" => $row2->DASH_NAME,
                                "DASH_TYPE" => $row2->DASH_TYPE,
                                "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                                "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                                "DESCRIPTION" => $row2->DN_DESCRIPTION,
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

                        $INTL_PAT["International_Patient"] = array_values($groupedData);

                        $groupedData = [];
                        foreach ($SFH_DTL as $row2) {
                            if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
                                $groupedData[$row2->DASH_SECTION_ID] = [
                                    "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                                    "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                                    "DESCRIPTION" => $row2->DS_DESCRIPTION,
                                    "PHOTO_URL1" => $row2->DSIMG1,
                                    "PHOTO_URL2" => $row2->DSIMG2,
                                    "PHOTO_URL3" => $row2->DSIMG3,
                                    "PHOTO_URL4" => $row2->DSIMG4,
                                    "PHOTO_URL5" => $row2->DSIMG5,
                                    "PHOTO_URL6" => $row2->DSIMG6,
                                    "PHOTO_URL7" => $row2->DSIMG7,
                                    "PHOTO_URL8" => $row2->DSIMG8,
                                    "PHOTO_URL9" => $row2->DSIMG9,
                                    "PHOTO_URL10" => $row2->DSIMG10,

                                    "BANNER_URL1" => $row2->DSBNR1,
                                    "BANNER_URL2" => $row2->DSBNR2,
                                    "BANNER_URL3" => $row2->DSBNR3,
                                    "BANNER_URL4" => $row2->DSBNR4,
                                    "BANNER_URL5" => $row2->DSBNR5,
                                    "BANNER_URL6" => $row2->DSBNR6,
                                    "BANNER_URL7" => $row2->DSBNR7,
                                    "BANNER_URL8" => $row2->DSBNR8,
                                    "BANNER_URL9" => $row2->DSBNR9,
                                    "BANNER_URL10" => $row2->DSBNR10,
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
                                "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                                "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
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

                        $SFH["Service_From_Home"] = array_values($groupedData);
                        $DASH_Z["Dashboard"] = $fltr_dash->map(function ($item) {
                            return [
                                "ID" => $item->ID,
                                "DASH_NAME" => $item->DASH_NAME,
                                "DASH_SECTION_ID" => $item->FACILITY_ID,
                                "DASH_SECTION_NAME" => $item->DASH_NAME,
                                "DESCRIPTION" => $item->DASH_DESC,
                                "PHOTO_URL1" => $item->DI_IMG1,

                                "PHOTO_URL2" => $item->DI_IMG2,
                                "PHOTO_URL3" => $item->DI_IMG3,
                                "PHOTO_URL4" => $item->DI_IMG4,
                                "PHOTO_URL5" => $item->DI_IMG5,
                                "PHOTO_URL6" => $item->DI_IMG6,
                                "PHOTO_URL7" => $item->DI_IMG7,
                                "PHOTO_URL8" => $item->DI_IMG8,
                                "PHOTO_URL9" => $item->DI_IMG9,
                                "PHOTO_URL10" => $item->DI_IMG10,
                                "BANNER_URL1" => $item->DI_BNR1,
                                "BANNER_URL2" => $item->DI_BNR2,
                                "BANNER_URL3" => $item->DI_BNR3,
                                "BANNER_URL4" => $item->DI_BNR4,
                                "BANNER_URL5" => $item->DI_BNR5,
                                "BANNER_URL6" => $item->DI_BNR6,
                                "BANNER_URL7" => $item->DI_BNR7,
                                "BANNER_URL8" => $item->DI_BNR8,
                                "BANNER_URL9" => $item->DI_BNR9,
                                "BANNER_URL10" => $item->DI_BNR10,
                                "Questions" => [
                                    [
                                        "QA1" => $item->DIQA1,
                                        "QA2" => $item->DIQA2,
                                        "QA3" => $item->DIQA3,
                                        "QA4" => $item->DIQA4,
                                        "QA5" => $item->DIQA5,
                                        "QA6" => $item->DIQA6,
                                        "QA7" => $item->DIQA7,
                                        "QA8" => $item->DIQA8,
                                        "QA9" => $item->DIQA9
                                    ]
                                ]
                            ];

                        })->values()->all();

                        //***********Client 1************/

                        $cldata1 = DB::table('pharmacy')
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
                                'pharmacy.CBNR_URL1',
                                'pharmacy.LATITUDE',
                                'pharmacy.LONGITUDE',
                                DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
* COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
* SIN(RADIANS('$latt'))))),2) as KM")
                            )
                            ->where('pharmacy.PHARMA_ID', '=', 11)
                            ->get();

                        $filtr_dash = $dash->filter(function ($item) {
                            return in_array($item->ID, [2, 5, 233, 237]);
                        });


                        $fltr_symp = DB::table('symptoms')
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
                            ->whereIn('symptoms.SYM_ID', [73, 75, 76])
                            // ->orderby('SYM_SL')
                            ->get()->map(function ($item) {
                                return [
                                    "SYM_ID" => $item->SYM_ID,
                                    "DASH_NAME" => $item->SYM_NAME,
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


                        // $symDtlCollection = collect($SYM_DTL);
                        $groupedData = [];
                        foreach ($cldata1 as $data) {

                            $groupedDataItem = [
                                'PHARMA_ID' => $data->PHARMA_ID,
                                'PHARMA_NAME' => $data->PHARMA_NAME,
                                'CLINIC_TYPE' => $data->CLINIC_TYPE,
                                'ADDRESS' => $data->ADDRESS,
                                'CITY' => $data->CITY,
                                'DIST' => $data->DIST,
                                'STATE' => $data->STATE,
                                'PIN' => $data->PIN,
                                'CLINIC_MOBILE' => $data->CLINIC_MOBILE,
                                'PHOTO_URL' => $data->PHOTO_URL,
                                'LOGO_URL' => $data->LOGO_URL,
                                'BANNER_URL' => $data->CBNR_URL1,
                                'LATITUDE' => $data->LATITUDE,
                                'LONGITUDE' => $data->LONGITUDE,
                                'KM' => $data->KM,
                                'DETAILS' => $filtr_dash->map(function ($item) {
                                    return [
                                        "ID" => $item->ID,
                                        "DASH_NAME" => $item->DASH_NAME,
                                        "DASH_SECTION_ID" => $item->FACILITY_ID,
                                        "DASH_SECTION_NAME" => $item->DASH_NAME,
                                        "DESCRIPTION" => $item->DASH_DESC,
                                        "PHOTO_URL1" => $item->DI_IMG1,
                                        "PHOTO_URL2" => $item->DI_IMG2,
                                        "PHOTO_URL3" => $item->DI_IMG3,
                                        "PHOTO_URL4" => $item->DI_IMG4,
                                        "PHOTO_URL5" => $item->DI_IMG5,
                                        "PHOTO_URL6" => $item->DI_IMG6,
                                        "PHOTO_URL7" => $item->DI_IMG7,
                                        "PHOTO_URL8" => $item->DI_IMG8,
                                        "PHOTO_URL9" => $item->DI_IMG9,
                                        "PHOTO_URL10" => $item->DI_IMG10,
                                        "BANNER_URL1" => $item->DI_BNR1,
                                        "BANNER_URL2" => $item->DI_BNR2,
                                        "BANNER_URL3" => $item->DI_BNR3,
                                        "BANNER_URL4" => $item->DI_BNR4,
                                        "BANNER_URL5" => $item->DI_BNR5,
                                        "BANNER_URL6" => $item->DI_BNR6,
                                        "BANNER_URL7" => $item->DI_BNR7,
                                        "BANNER_URL8" => $item->DI_BNR8,
                                        "BANNER_URL9" => $item->DI_BNR9,
                                        "BANNER_URL10" => $item->DI_BNR10,
                                        "Questions" => [
                                            "QA1" => $item->DIQA1,
                                            "QA2" => $item->DIQA2,
                                            "QA3" => $item->DIQA3,
                                            "QA4" => $item->DIQA4,
                                            "QA5" => $item->DIQA5,
                                            "QA6" => $item->DIQA6,
                                            "QA7" => $item->DIQA7,
                                            "QA8" => $item->DIQA8,
                                            "QA9" => $item->DIQA9
                                        ]
                                    ];
                                })->values()->all(),
                                'details' => $fltr_symp
                            ];

                            $groupedData[] = $groupedDataItem;
                        }




                        $AS["Client1"] = array_values($groupedData);
                        // //SECTION-#### SPECIALIST

                        $SPLST_DTL = DB::table('dis_catg')
                            ->join('dr_availablity', function ($join) use ($pid) {
                                $join->on('dr_availablity.DIS_ID', '=', 'dis_catg.DIS_ID')
                                    ->where('dr_availablity.PHARMA_ID', '=', $pid);
                            })
                            ->select(
                                'dis_catg.DIS_ID',
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
                                DB::raw('COUNT(DISTINCT CASE WHEN dr_availablity.SCH_STATUS != \'NA\' THEN dr_availablity.DR_ID ELSE NULL END) as TOT_DR'),
                            )
                            ->where('dr_availablity.PHARMA_ID', '=', $pid)
                            ->groupBy(
                                'dis_catg.DIS_ID',
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

                            )
                            // ->orderBy('dis_catg.DIS_SL')
                            ->orderByRaw('CASE WHEN dis_catg.SPSL4 IS NULL THEN 1 ELSE 0 END, dis_catg.SPSL4 ASC')
                            ->get()
                            ->map(function ($item) use ($pid) {
                                $availDr = getAvailableDoctors($item->DIS_ID, $pid);
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
                                    "TOT_DR" => $item->TOT_DR,
                                    "AVAIL_DR" => $availDr,
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
                            ->orderByRaw('CASE WHEN symptoms.SMSL4 IS NULL THEN 1 ELSE 0 END, symptoms.SMSL4 ASC')
                            ->take(10)->get()->map(function ($item) {
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
                                        [
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
                                    ]
                                ];
                            })->take(10)->values()->all();

                        $data = DB::table('dashboard_item')
                            ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard_item.ID')
                            ->join('clinic_testdata', function ($join) use ($pid) {
                                $join->on('sym_organ_test.TEST_ID', '=', 'clinic_testdata.TEST_ID')
                                    ->where('clinic_testdata.PHARMA_ID', $pid);
                            })
                            ->join('test_sub_dept', 'clinic_testdata.SUB_DEPT_ID', '=', 'test_sub_dept.SUB_DEPT_ID')
                            ->whereIn('dashboard_item.DASH_SECTION_ID', ['S', 'T'])
                            ->select(
                                'dashboard_item.ID',
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
                                'clinic_testdata.TEST_NAME',
                                'clinic_testdata.TEST_CATG',
                                'clinic_testdata.DISCOUNT',
                                'clinic_testdata.HOME_COLLECT',
                                'clinic_testdata.ORGAN_ID',
                                'clinic_testdata.ORGAN_NAME',
                                'clinic_testdata.SAMPLE_ID',
                                'clinic_testdata.TEST_SAMPLE',
                                'clinic_testdata.ORGAN_URL',
                                'clinic_testdata.TEST_DESC',
                                'clinic_testdata.DEPARTMENT',
                                'clinic_testdata.COST',
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
                                'test_sub_dept.SDBNR1 as BANNER_URL',
                                'dashboard_item.DASH_SECTION_ID'
                            )
                            ->orderByRaw('CASE WHEN dashboard_item.DISL4 IS NULL THEN 1 ELSE 0 END, dashboard_item.DISL4 ASC')
                            ->get();

                        // Separate the data based on DASH_SECTION_ID
                        $sodata = $data->filter(function ($item) {
                            return $item->DASH_SECTION_ID === 'S';
                        });

                        $datat = $data->filter(function ($item) {
                            return $item->DASH_SECTION_ID === 'T';
                        });

                        $scandata = DB::table('dashboard_section')
                            ->join('dashboard_item', 'dashboard_item.DASH_SECTION_ID', '=', 'dashboard_section.DASH_SECTION_ID')
                            ->join('clinic_testdata', function ($join) {
                                $join->on('dashboard_item.SUB_DEPT_ID', '=', 'clinic_testdata.SUB_DEPT_ID');
                            })
                            ->leftJoin('test_scanorgan', 'clinic_testdata.ORGAN_ID', '=', 'test_scanorgan.ORGAN_ID')
                            ->select(
                                'dashboard_section.DASH_SECTION_NAME',
                                'dashboard_item.ID',
                                'dashboard_item.DASH_SECTION_ID',
                                'dashboard_item.DASH_NAME',
                                'dashboard_item.DASH_DESC',
                                'dashboard_item.DASH_POSITION',
                                'dashboard_item.DASH_STATUS',
                                'dashboard_item.DI_IMG1',
                                'dashboard_item.DI_IMG2',
                                'dashboard_item.DI_IMG3',
                                'dashboard_item.DI_IMG4',
                                'dashboard_item.DI_IMG5',
                                'dashboard_item.DI_IMG6',
                                'dashboard_item.DI_IMG7',
                                'dashboard_item.DI_IMG8',
                                'dashboard_item.DI_IMG9',
                                'dashboard_item.DI_IMG10',
                                'dashboard_item.DI_BNR1',
                                'dashboard_item.DI_BNR2',
                                'dashboard_item.DI_BNR3',
                                'dashboard_item.DI_BNR4',
                                'dashboard_item.DI_BNR5',
                                'dashboard_item.DI_BNR6',
                                'dashboard_item.DI_BNR7',
                                'dashboard_item.DI_BNR8',
                                'dashboard_item.DI_BNR9',
                                'dashboard_item.DI_BNR10',
                                'dashboard_item.DIQA1',
                                'dashboard_item.DIQA2',
                                'dashboard_item.DIQA3',
                                'dashboard_item.DIQA4',
                                'dashboard_item.DIQA5',
                                'dashboard_item.DIQA6',
                                'dashboard_item.DIQA7',
                                'dashboard_item.DIQA8',
                                'dashboard_item.DIQA9',
                                'test_scanorgan.ORGAN_ID',
                                'test_scanorgan.ORGAN_NAME',
                                'test_scanorgan.OIMG1 as ORGAN_URL',
                                'clinic_testdata.DEPT_ID',
                                'clinic_testdata.SUB_DEPT_ID'
                            )
                            ->where(['clinic_testdata.DEPT_ID' => 'D1', 'clinic_testdata.PHARMA_ID' => $pid])
                            ->where('dashboard_item.DASH_SECTION_ID', '=', 'F')
                            // ->orderBy('dashboard_item.DASH_POSITION')
                            ->orderByRaw('CASE WHEN dashboard_item.DISL4 IS NULL THEN 1 ELSE 0 END, dashboard_item.DISL4 ASC')
                            ->get();




                    } elseif ($clinic->CLINIC_TYPE == 'Diagnostic') {

                        $dash = DB::table('dashboard_section')
                            ->join('dashboard_item', 'dashboard_section.DASH_SECTION_ID', 'dashboard_item.DASH_SECTION_ID')
                            ->where('dashboard_section.DS_TAGGED', 'like', '%' . 'C' . '%')
                            ->select(
                                'dashboard_section.DASH_SECTION_ID',
                                'dashboard_section.DASH_SECTION_NAME',
                                'dashboard_section.DS_DESCRIPTION',
                                'dashboard_section.DS_TYPE',
                                'dashboard_section.DS_POSITION',
                                'dashboard_section.DS_TAGGED',
                                'dashboard_section.DS_STATUS',
                                'dashboard_section.DSIMG1',
                                'dashboard_section.DSIMG2',
                                'dashboard_section.DSIMG3',
                                'dashboard_section.DSIMG4',
                                'dashboard_section.DSIMG5',
                                'dashboard_section.DSIMG6',
                                'dashboard_section.DSIMG7',
                                'dashboard_section.DSIMG8',
                                'dashboard_section.DSIMG9',
                                'dashboard_section.DSIMG10',
                                'dashboard_section.DSBNR1',
                                'dashboard_section.DSBNR2',
                                'dashboard_section.DSBNR3',
                                'dashboard_section.DSBNR4',
                                'dashboard_section.DSBNR5',
                                'dashboard_section.DSBNR6',
                                'dashboard_section.DSBNR7',
                                'dashboard_section.DSBNR8',
                                'dashboard_section.DSBNR9',
                                'dashboard_section.DSBNR10',
                                'dashboard_section.DSQA1',
                                'dashboard_section.DSQA2',
                                'dashboard_section.DSQA3',
                                'dashboard_section.DSQA4',
                                'dashboard_section.DSQA5',
                                'dashboard_section.DSQA6',
                                'dashboard_section.DSQA7',
                                'dashboard_section.DSQA8',
                                'dashboard_section.DSQA9',
                                'dashboard_item.ID',
                                'dashboard_item.DIS_ID',
                                'dashboard_item.TEST_DEPT_ID',
                                'dashboard_item.DASH_NAME',
                                'dashboard_item.DASH_DESC',
                                'dashboard_item.DASH_POSITION',
                                'dashboard_item.DASH_ITEM_TAGGED',
                                'dashboard_item.FACILITY_ID',
                                'dashboard_item.DASH_STATUS',
                                'dashboard_item.DI_IMG1',
                                'dashboard_item.DI_IMG2',
                                'dashboard_item.DI_IMG3',
                                'dashboard_item.DI_IMG4',
                                'dashboard_item.DI_IMG5',
                                'dashboard_item.DI_IMG6',
                                'dashboard_item.DI_IMG7',
                                'dashboard_item.DI_IMG8',
                                'dashboard_item.DI_IMG9',
                                'dashboard_item.DI_IMG10',
                                'dashboard_item.DI_BNR1',
                                'dashboard_item.DI_BNR2',
                                'dashboard_item.DI_BNR3',
                                'dashboard_item.DI_BNR4',
                                'dashboard_item.DI_BNR5',
                                'dashboard_item.DI_BNR6',
                                'dashboard_item.DI_BNR7',
                                'dashboard_item.DI_BNR8',
                                'dashboard_item.DI_BNR9',
                                'dashboard_item.DI_BNR10',
                                'dashboard_item.DIQA1',
                                'dashboard_item.DIQA2',
                                'dashboard_item.DIQA3',
                                'dashboard_item.DIQA4',
                                'dashboard_item.DIQA5',
                                'dashboard_item.DIQA6',
                                'dashboard_item.DIQA7',
                                'dashboard_item.DIQA8',
                                'dashboard_item.DIQA9'
                            )
                            ->where('dashboard_section.DS_STATUS', 'Active')
                            ->orderByRaw('CASE WHEN dashboard_section.DSSL5 IS NULL THEN 1 ELSE 0 END, dashboard_section.DSSL5 ASC')
                            ->orderByRaw('CASE WHEN dashboard_item.DISL5 IS NULL THEN 1 ELSE 0 END, dashboard_item.DISL5 ASC')
                            ->get();


                        $fltr_dash = $dash->filter(function ($item) {
                            return $item->DASH_SECTION_ID === 'A' && stripos($item->DASH_ITEM_TAGGED, 'C') !== false;
                        });
                        $DASH_Z["Dashboard"] = $fltr_dash->map(function ($item) {
                            return [
                                "ID" => $item->ID,
                                "DASH_NAME" => $item->DASH_NAME,
                                "DASH_SECTION_ID" => $item->FACILITY_ID,
                                "DASH_SECTION_NAME" => $item->DASH_NAME,
                                "DESCRIPTION" => $item->DASH_DESC,
                                "PHOTO_URL1" => $item->DI_IMG1,
                                "PHOTO_URL2" => $item->DI_IMG2,
                                "PHOTO_URL3" => $item->DI_IMG3,
                                "PHOTO_URL4" => $item->DI_IMG4,
                                "PHOTO_URL5" => $item->DI_IMG5,
                                "PHOTO_URL6" => $item->DI_IMG6,
                                "PHOTO_URL7" => $item->DI_IMG7,
                                "PHOTO_URL8" => $item->DI_IMG8,
                                "PHOTO_URL9" => $item->DI_IMG9,
                                "PHOTO_URL10" => $item->DI_IMG10,
                                "BANNER_URL1" => $item->DI_BNR1,
                                "BANNER_URL2" => $item->DI_BNR2,
                                "BANNER_URL3" => $item->DI_BNR3,
                                "BANNER_URL4" => $item->DI_BNR4,
                                "BANNER_URL5" => $item->DI_BNR5,
                                "BANNER_URL6" => $item->DI_BNR6,
                                "BANNER_URL7" => $item->DI_BNR7,
                                "BANNER_URL8" => $item->DI_BNR8,
                                "BANNER_URL9" => $item->DI_BNR9,
                                "BANNER_URL10" => $item->DI_BNR10,
                                "Questions" => [
                                    [
                                        "QA1" => $item->DIQA1,
                                        "QA2" => $item->DIQA2,
                                        "QA3" => $item->DIQA3,
                                        "QA4" => $item->DIQA4,
                                        "QA5" => $item->DIQA5,
                                        "QA6" => $item->DIQA6,
                                        "QA7" => $item->DIQA7,
                                        "QA8" => $item->DIQA8,
                                        "QA9" => $item->DIQA9
                                    ]
                                ]
                            ];
                        })->values()->all();

                        // Client 1

                        $cldata1 = DB::table('pharmacy')
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
                                'pharmacy.CBNR_URL1',
                                'pharmacy.LOGO_URL',
                                'pharmacy.LATITUDE',
                                'pharmacy.LONGITUDE',
                                DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                                * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                                * SIN(RADIANS('$latt'))))),2) as KM")
                            )
                            ->where('pharmacy.PHARMA_ID', '=', 12)
                            ->get();

                        $fltr_symp1 = DB::table('symptoms')
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
                            ->whereIn('symptoms.SYM_ID', [111, 115, 113, 116])
                            ->get()->map(function ($item) {
                                return [
                                    "SYM_ID" => $item->SYM_ID,
                                    "DASH_NAME" => $item->SYM_NAME,
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

                        $groupedData = [];
                        foreach ($cldata1 as $data) {


                            $fltr_dash = $dash->filter(function ($item) {
                                return in_array($item->ID, [2]);
                            });

                            $groupedDataItem = [
                                'PHARMA_ID' => $data->PHARMA_ID,
                                'PHARMA_NAME' => $data->PHARMA_NAME,
                                'CLINIC_TYPE' => $data->CLINIC_TYPE,
                                'ADDRESS' => $data->ADDRESS,
                                'CITY' => $data->CITY,
                                'DIST' => $data->DIST,
                                'STATE' => $data->STATE,
                                'PIN' => $data->PIN,
                                'CLINIC_MOBILE' => $data->CLINIC_MOBILE,
                                'PHOTO_URL' => $data->PHOTO_URL,
                                'LOGO_URL' => $data->LOGO_URL,
                                'BANNER_URL' => $data->CBNR_URL1,
                                'LATITUDE' => $data->LATITUDE,
                                'LONGITUDE' => $data->LONGITUDE,
                                'KM' => $data->KM,
                                'DETAILS' => $fltr_dash->map(function ($item) {
                                    return [
                                        "ID" => $item->ID,
                                        "DASH_NAME" => $item->DASH_NAME,
                                        "DASH_SECTION_ID" => $item->FACILITY_ID,
                                        "DASH_SECTION_NAME" => $item->DASH_NAME,
                                        "DESCRIPTION" => $item->DASH_DESC,
                                        "PHOTO_URL1" => $item->DI_IMG1,
                                        "PHOTO_URL2" => $item->DI_IMG2,
                                        "PHOTO_URL3" => $item->DI_IMG3,
                                        "PHOTO_URL4" => $item->DI_IMG4,
                                        "PHOTO_URL5" => $item->DI_IMG5,
                                        "PHOTO_URL6" => $item->DI_IMG6,
                                        "PHOTO_URL7" => $item->DI_IMG7,
                                        "PHOTO_URL8" => $item->DI_IMG8,
                                        "PHOTO_URL9" => $item->DI_IMG9,
                                        "PHOTO_URL10" => $item->DI_IMG10,
                                        "BANNER_URL1" => $item->DI_BNR1,
                                        "BANNER_URL2" => $item->DI_BNR2,
                                        "BANNER_URL3" => $item->DI_BNR3,
                                        "BANNER_URL4" => $item->DI_BNR4,
                                        "BANNER_URL5" => $item->DI_BNR5,
                                        "BANNER_URL6" => $item->DI_BNR6,
                                        "BANNER_URL7" => $item->DI_BNR7,
                                        "BANNER_URL8" => $item->DI_BNR8,
                                        "BANNER_URL9" => $item->DI_BNR9,
                                        "BANNER_URL10" => $item->DI_BNR10,
                                        "Questions" => [
                                            "QA1" => $item->DIQA1,
                                            "QA2" => $item->DIQA2,
                                            "QA3" => $item->DIQA3,
                                            "QA4" => $item->DIQA4,
                                            "QA5" => $item->DIQA5,
                                            "QA6" => $item->DIQA6,
                                            "QA7" => $item->DIQA7,
                                            "QA8" => $item->DIQA8,
                                            "QA9" => $item->DIQA9
                                        ]
                                    ];
                                })->values()->all(),
                                'details' => $fltr_symp1
                            ];
                            // Base query
                            $baseQuery = DB::table('facility')
                                ->join('facility_type', 'facility.DASH_TYPE_ID', '=', 'facility_type.DASH_TYPE_ID')
                                ->join('facility_section', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                                ->where('facility_section.DS_STATUS', 'Active')
                                ->where('facility_type.DT_STATUS', 'Active')
                                ->where('facility.DN_STATUS', 'Active')
                                ->orderByRaw('CASE WHEN facility_type.DTSL5 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL5 ASC')
                                ->orderByRaw('CASE WHEN facility.DNSL5 IS NULL THEN 1 ELSE 0 END, facility.DNSL5 ASC');

                            // Surgical facilities query
                            $surg = $baseQuery->clone()
                                ->where('facility_section.DASH_SECTION_ID', 'like', '%SR%')
                                ->get();

                            // IPD details query
                            $IPD_DTL = $baseQuery->clone()
                                ->where('facility_section.DASH_SECTION_ID', 'AH')
                                ->get();

                            // Emergency details query
                            $EM_DTL = $baseQuery->clone()
                                ->where('facility_section.DASH_SECTION_ID', 'AG')
                                ->get();

                            // SND details query
                            $SND_DTL = $baseQuery->clone()
                                ->where('facility_type.DT_TAG_SECTION', 'like', '%AM%')
                                ->orderByRaw('CASE WHEN facility_type.DTSL5 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL5 ASC')
                                ->get();

                            // INS details query
                            $INS_DTL = $baseQuery->clone()
                                ->where('facility_section.DASH_SECTION_ID', 'AL')
                                ->get();

                            $filr_surg = $surg->filter(function ($item) {
                                return in_array($item->DASH_ID, [227, 228]);
                            });
                            $SURG_DTL1 = $filr_surg->map(function ($item) {
                                return [
                                    "DASH_ID" => $item->DASH_ID,
                                    "DASH_NAME" => $item->DASH_NAME,
                                    "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                                    "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                                    "DASH_TYPE_ID" => $item->DASH_TYPE_ID,
                                    "DASH_TYPE" => $item->DASH_TYPE,
                                    "DESCRIPTION" => $item->DN_DESCRIPTION,
                                    "PHOTO_URL1" => $item->DNIMG1,
                                    "PHOTO_URL2" => $item->DNIMG2,
                                    "PHOTO_URL3" => $item->DNIMG3,
                                    "PHOTO_URL4" => $item->DNIMG4,
                                    "PHOTO_URL5" => $item->DNIMG5,
                                    "PHOTO_URL6" => $item->DNIMG6,
                                    "PHOTO_URL7" => $item->DNIMG7,
                                    "PHOTO_URL8" => $item->DNIMG8,
                                    "PHOTO_URL9" => $item->DNIMG9,
                                    "PHOTO_URL10" => $item->DNIMG10,
                                    "BANNER_URL1" => $item->DNBNR1,
                                    "BANNER_URL2" => $item->DNBNR2,
                                    "BANNER_URL3" => $item->DNBNR3,
                                    "BANNER_URL4" => $item->DNBNR4,
                                    "BANNER_URL5" => $item->DNBNR5,
                                    "BANNER_URL6" => $item->DNBNR6,
                                    "BANNER_URL7" => $item->DNBNR7,
                                    "BANNER_URL8" => $item->DNBNR8,
                                    "BANNER_URL9" => $item->DNBNR9,
                                    "BANNER_URL10" => $item->DNBNR10,
                                    "Questions" => [
                                        [
                                            "QA1" => $item->DNQA1,
                                            "QA2" => $item->DNQA2,
                                            "QA3" => $item->DNQA3,
                                            "QA4" => $item->DNQA4,
                                            "QA5" => $item->DNQA5,
                                            "QA6" => $item->DNQA6,
                                            "QA7" => $item->DNQA7,
                                            "QA8" => $item->DNQA8,
                                            "QA9" => $item->DNQA9
                                        ]
                                    ]
                                ];
                            })->values()->all();

                            foreach ($SURG_DTL1 as $surgDtlItem) {
                                $groupedDataItem['DETAILS'][] = $surgDtlItem;
                            }

                            $groupedData[] = $groupedDataItem;
                        }

                        $AS["Client1"] = array_values($groupedData);
                        $AS = is_array($AS) ? $AS : $AS->toArray();

                        // //SECTION-#### SPECIALIST

                        $SPLST_DTL = DB::table('dis_catg')
                            ->join('dr_availablity', function ($join) use ($pid) {
                                $join->on('dr_availablity.DIS_ID', '=', 'dis_catg.DIS_ID')
                                    ->where('dr_availablity.PHARMA_ID', '=', $pid);
                            })
                            ->select(
                                'dis_catg.DIS_ID',
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
                                DB::raw('COUNT(DISTINCT CASE WHEN dr_availablity.SCH_STATUS != \'NA\' THEN dr_availablity.DR_ID ELSE NULL END) as TOT_DR'),
                            )
                            ->where('dr_availablity.PHARMA_ID', '=', $pid)
                            ->groupBy(
                                'dis_catg.DIS_ID',
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

                            )
                            // ->orderBy('dis_catg.DIS_SL')
                            ->orderByRaw('CASE WHEN dis_catg.SPSL5 IS NULL THEN 1 ELSE 0 END, dis_catg.SPSL5 ASC')
                            ->get()
                            ->map(function ($item) use ($pid) {
                                $availDr = getAvailableDoctors($item->DIS_ID, $pid);
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
                                    "TOT_DR" => $item->TOT_DR,
                                    "AVAIL_DR" => $availDr,
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
                            ->orderByRaw('CASE WHEN symptoms.SMSL5 IS NULL THEN 1 ELSE 0 END, symptoms.SMSL5 ASC')
                            ->take(10)->get()->map(function ($item) {
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
                                        [
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
                                    ]
                                ];
                            })->take(10)->values()->all();

                        $data = DB::table('dashboard_item')
                            ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard_item.ID')
                            ->join('clinic_testdata', function ($join) use ($pid) {
                                $join->on('sym_organ_test.TEST_ID', '=', 'clinic_testdata.TEST_ID')
                                    ->where('clinic_testdata.PHARMA_ID', $pid);
                            })
                            ->join('test_sub_dept', 'clinic_testdata.SUB_DEPT_ID', '=', 'test_sub_dept.SUB_DEPT_ID')
                            ->whereIn('dashboard_item.DASH_SECTION_ID', ['S', 'T'])
                            ->select(
                                'dashboard_item.ID',
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
                                'clinic_testdata.TEST_NAME',
                                'clinic_testdata.TEST_CATG',
                                'clinic_testdata.DISCOUNT',
                                'clinic_testdata.HOME_COLLECT',
                                'clinic_testdata.ORGAN_ID',
                                'clinic_testdata.ORGAN_NAME',
                                'clinic_testdata.SAMPLE_ID',
                                'clinic_testdata.TEST_SAMPLE',
                                'clinic_testdata.ORGAN_URL',
                                'clinic_testdata.TEST_DESC',
                                'clinic_testdata.DEPARTMENT',
                                'clinic_testdata.COST',
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
                                'test_sub_dept.SDBNR1 as BANNER_URL',
                                'dashboard_item.DASH_SECTION_ID'
                            )
                            ->orderByRaw('CASE WHEN dashboard_item.DISL5 IS NULL THEN 1 ELSE 0 END, dashboard_item.DISL5 ASC')
                            ->get();

                        // Separate the data based on DASH_SECTION_ID
                        $sodata = $data->filter(function ($item) {
                            return $item->DASH_SECTION_ID === 'S';
                        });

                        $datat = $data->filter(function ($item) {
                            return $item->DASH_SECTION_ID === 'T';
                        });

                        $scandata = DB::table('dashboard_section')
                            ->join('dashboard_item', 'dashboard_item.DASH_SECTION_ID', '=', 'dashboard_section.DASH_SECTION_ID')
                            ->join('clinic_testdata', function ($join) {
                                $join->on('dashboard_item.SUB_DEPT_ID', '=', 'clinic_testdata.SUB_DEPT_ID');
                            })
                            ->leftJoin('test_scanorgan', 'clinic_testdata.ORGAN_ID', '=', 'test_scanorgan.ORGAN_ID')
                            ->select(
                                'dashboard_section.DASH_SECTION_NAME',
                                'dashboard_item.ID',
                                'dashboard_item.DASH_SECTION_ID',
                                'dashboard_item.DASH_NAME',
                                'dashboard_item.DASH_DESC',
                                'dashboard_item.DASH_POSITION',
                                'dashboard_item.DASH_STATUS',
                                'dashboard_item.DI_IMG1',
                                'dashboard_item.DI_IMG2',
                                'dashboard_item.DI_IMG3',
                                'dashboard_item.DI_IMG4',
                                'dashboard_item.DI_IMG5',
                                'dashboard_item.DI_IMG6',
                                'dashboard_item.DI_IMG7',
                                'dashboard_item.DI_IMG8',
                                'dashboard_item.DI_IMG9',
                                'dashboard_item.DI_IMG10',
                                'dashboard_item.DI_BNR1',
                                'dashboard_item.DI_BNR2',
                                'dashboard_item.DI_BNR3',
                                'dashboard_item.DI_BNR4',
                                'dashboard_item.DI_BNR5',
                                'dashboard_item.DI_BNR6',
                                'dashboard_item.DI_BNR7',
                                'dashboard_item.DI_BNR8',
                                'dashboard_item.DI_BNR9',
                                'dashboard_item.DI_BNR10',
                                'dashboard_item.DIQA1',
                                'dashboard_item.DIQA2',
                                'dashboard_item.DIQA3',
                                'dashboard_item.DIQA4',
                                'dashboard_item.DIQA5',
                                'dashboard_item.DIQA6',
                                'dashboard_item.DIQA7',
                                'dashboard_item.DIQA8',
                                'dashboard_item.DIQA9',
                                'test_scanorgan.ORGAN_ID',
                                'test_scanorgan.ORGAN_NAME',
                                'test_scanorgan.OIMG1 as ORGAN_URL',
                                'clinic_testdata.DEPT_ID',
                                'clinic_testdata.SUB_DEPT_ID'
                            )
                            ->where(['clinic_testdata.DEPT_ID' => 'D1', 'clinic_testdata.PHARMA_ID' => $pid])
                            ->where('dashboard_item.DASH_SECTION_ID', '=', 'F')
                            // ->orderBy('dashboard_item.DASH_POSITION')
                            ->orderByRaw('CASE WHEN dashboard_item.DISL5 IS NULL THEN 1 ELSE 0 END, dashboard_item.DISL5 ASC')
                            ->get();



                    } else {

                    }
                }


                //SECTION-U #### WHY CHOOSE US?
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'U';
                });
                $U["Why_Chhose_Us"] = $fltr_promo_bnr->map(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->values()->all();


                $fltr_promo_bnr = $promo_bnr1->filter(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->take(3)->values()->all();
                $SPLST["Specialist"] = array_values($SPLST_DTL + $SPB);

                //SECTION-#### SINGLE TEST

                $TST_DTL = DB::table('clinic_testdata')
                    ->join('test_sub_dept', 'clinic_testdata.SUB_DEPT_ID', '=', 'test_sub_dept.SUB_DEPT_ID')
                    ->select(
                        'TEST_ID',
                        'TEST_NAME',
                        'TEST_CATG',
                        'DISCOUNT',
                        'HOME_COLLECT',
                        'ORGAN_ID',
                        'ORGAN_NAME',
                        'SAMPLE_ID',
                        'TEST_SAMPLE',
                        'ORGAN_URL',
                        'TEST_DESC',
                        'DEPARTMENT as CATEGORY',
                        'COST',
                        'KNOWN_AS',
                        'FASTING',
                        'GENDER_TYPE',
                        'AGE_TYPE',
                        'REPORT_TIME',
                        'PRESCRIPTION',
                        'ID_PROOF',
                        'test_sub_dept.SDBNR1 as BANNER_URL',
                        'QA1',
                        'QA2',
                        'QA3',
                        'QA4',
                        'QA5',
                        'QA6'
                    )
                    ->where(['PHARMA_ID' => $pid])
                    ->orderBy('TEST_SL')
                    ->take(100)
                    ->get()
                    ->map(function ($item) {
                        $item = (array) $item; // Convert stdClass object to array
                        $item['Questions'][] = [
                            'QA1' => $item['QA1'],
                            'QA2' => $item['QA2'],
                            'QA3' => $item['QA3'],
                            'QA4' => $item['QA4'],
                            'QA5' => $item['QA5'],
                            'QA6' => $item['QA6'],
                        ];
                        unset($item['QA1'], $item['QA2'], $item['QA3'], $item['QA4'], $item['QA5'], $item['QA6']);
                        return $item;
                    })
                    ->toArray();

                // $TST_DTL now contains the restructured data


                $fltr_promo_bnr = $promo_bnr1->filter(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->take(3)->values()->all();
                $TST["Most_Popular_Test"] = array_values($TST_DTL + $STB);

                //SECTION-#### SYMPTOMS



                $fltr_promo_bnr = $promo_bnr1->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SM';
                });
                $SMB["Symptoms_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->take(3)->values()->all();
                $SYM["Symptoms"] = array_values($SYM_DTL + $SMB);

                //SECTION-C #### PROFILE
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'C';
                });

                $C_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
                        "DEPT_ID" => $item->TEST_DEPT_ID,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                        "BANNER_URL1" => $item->DI_BNR1,
                        "BANNER_URL2" => $item->DI_BNR2,
                        "BANNER_URL3" => $item->DI_BNR3,
                        "BANNER_URL4" => $item->DI_BNR4,
                        "BANNER_URL5" => $item->DI_BNR5,
                        "BANNER_URL6" => $item->DI_BNR6,
                        "BANNER_URL7" => $item->DI_BNR7,
                        "BANNER_URL8" => $item->DI_BNR8,
                        "BANNER_URL9" => $item->DI_BNR9,
                        "BANNER_URL10" => $item->DI_BNR10,
                        "Questions" => [
                            "QA1" => $item->DIQA1,
                            "QA2" => $item->DIQA2,
                            "QA3" => $item->DIQA3,
                            "QA4" => $item->DIQA4,
                            "QA5" => $item->DIQA5,
                            "QA6" => $item->DIQA6,
                            "QA7" => $item->DIQA7,
                            "QA8" => $item->DIQA8,
                            "QA9" => $item->DIQA9
                        ]
                    ];
                })->values()->all();
                $fltr_promo_bnr = $promo_bnr1->filter(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->values()->all();

                //SECTION-B #### FAMILY CARE PACKAGES
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'B';
                });
                // $B_DTL = $fltr_dash->map(function ($item) {
                //     return [
                //         "DASH_ID" => $item->DASH_ID,
                //         "DIS_ID" => $item->DIS_ID,
                //         "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                //         "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                //         "DASH_NAME" => $item->DASH_NAME,
                //         "DASH_TYPE" => $item->DASH_TYPE,
                //         "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                //         "PHOTO_URL" => $item->PHOTO1_URL,
                //     ];
                // })->values()->all();

                $B_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
                        "DEPT_ID" => $item->TEST_DEPT_ID,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                        "BANNER_URL1" => $item->DI_BNR1,
                        "BANNER_URL2" => $item->DI_BNR2,
                        "BANNER_URL3" => $item->DI_BNR3,
                        "BANNER_URL4" => $item->DI_BNR4,
                        "BANNER_URL5" => $item->DI_BNR5,
                        "BANNER_URL6" => $item->DI_BNR6,
                        "BANNER_URL7" => $item->DI_BNR7,
                        "BANNER_URL8" => $item->DI_BNR8,
                        "BANNER_URL9" => $item->DI_BNR9,
                        "BANNER_URL10" => $item->DI_BNR10,
                        "Questions" => [
                            "QA1" => $item->DIQA1,
                            "QA2" => $item->DIQA2,
                            "QA3" => $item->DIQA3,
                            "QA4" => $item->DIQA4,
                            "QA5" => $item->DIQA5,
                            "QA6" => $item->DIQA6,
                            "QA7" => $item->DIQA7,
                            "QA8" => $item->DIQA8,
                            "QA9" => $item->DIQA9
                        ]
                    ];
                })->values()->all();
                $fltr_promo_bnr = $promo_bnr1->filter(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->values()->all();
                $B["Family_Care_Package"] = array_values($B_DTL + $B_BNR);

                //SECTION-G #### POPULAR HEALTH CHECKUP PACKAGES
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'G';
                });


                $G_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
                        "DEPT_ID" => $item->TEST_DEPT_ID,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                        "BANNER_URL1" => $item->DI_BNR1,
                        "BANNER_URL2" => $item->DI_BNR2,
                        "BANNER_URL3" => $item->DI_BNR3,
                        "BANNER_URL4" => $item->DI_BNR4,
                        "BANNER_URL5" => $item->DI_BNR5,
                        "BANNER_URL6" => $item->DI_BNR6,
                        "BANNER_URL7" => $item->DI_BNR7,
                        "BANNER_URL8" => $item->DI_BNR8,
                        "BANNER_URL9" => $item->DI_BNR9,
                        "BANNER_URL10" => $item->DI_BNR10,
                        "Questions" => [
                            "QA1" => $item->DIQA1,
                            "QA2" => $item->DIQA2,
                            "QA3" => $item->DIQA3,
                            "QA4" => $item->DIQA4,
                            "QA5" => $item->DIQA5,
                            "QA6" => $item->DIQA6,
                            "QA7" => $item->DIQA7,
                            "QA8" => $item->DIQA8,
                            "QA9" => $item->DIQA9
                        ]
                    ];
                })->values()->all();



                $fltr_promo_bnr = $promo_bnr1->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'G';
                });
                $G_BNR["Popular_Health_Packages_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->values()->all();
                $G["Popular_Health_Packages"] = array_values($G_DTL + $G_BNR);

                // // SECTION-S #### SYMPTOMATIC TEST



                $S_DTL = [];
                $collection = collect($sodata);
                $distinctValues = $collection->pluck('ID')->unique();
                foreach ($distinctValues as $row) {
                    $fltr_arr = $sodata->filter(function ($item) use ($row) {
                        return $item->ID === $row;
                    });


                    $T_DTL = $fltr_arr->map(function ($item) {
                        $homecol = "---";
                        return [
                            "TEST_ID" => $item->TEST_ID,
                            // "TEST_SL" => $item->TEST_SL,
                            "TEST_NAME" => $item->TEST_NAME,
                            // "TEST_CODE" => $item->TEST_CODE,
                            // "TEST_SAMPLE" => $item->TEST_SAMPLE,
                            "TEST_CATG" => $item->TEST_CATG,
                            "COST" => $item->COST,
                            "CATEGORY" => $item->DEPARTMENT,
                            // "TEST_UNIT" => $item->TEST_UNIT,
                            // "NORMAL_RANGE" => $item->NORMAL_RANGE,
                            "TEST_DESC" => $item->TEST_DESC,
                            "ORGAN_ID" => $item->ORGAN_ID,
                            "SAMPLE_ID" => $item->SAMPLE_ID,
                            "TEST_SAMPLE" => $item->TEST_SAMPLE,
                            "ORGAN_NAME" => $item->ORGAN_NAME,
                            "KNOWN_AS" => $item->KNOWN_AS,
                            "FASTING" => $item->FASTING,
                            "GENDER_TYPE" => $item->GENDER_TYPE,
                            "AGE_TYPE" => $item->AGE_TYPE,
                            "REPORT_TIME" => $item->REPORT_TIME,
                            "HOME_COLLECT" => $item->HOME_COLLECT,
                            "PRESCRIPTION" => $item->PRESCRIPTION,
                            "ID_PROOF" => $item->ID_PROOF,
                            "BANNER_URL" => $item->BANNER_URL,
                            "Questions" => [
                                [
                                    "QA1" => $item->QA1,
                                    "QA2" => $item->QA2,
                                    "QA3" => $item->QA3,
                                    "QA4" => $item->QA4,
                                    "QA5" => $item->QA5,
                                    "QA6" => $item->QA6,
                                ]
                            ]

                        ];
                    })->values()->all();
                    $S_DTL[] = [
                        "DASH_ID" => $row,
                        "DASH_NAME" => $fltr_arr->first()->DASH_NAME,
                        "PHOTO_URL1" => $fltr_arr->first()->PHOTO_URL1,
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

                // RETURN $S_DTL;
                $fltr_promo_bnr = $promo_bnr1->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'S';
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->values()->all();
                $S["Symptomatic_Test"] = array_values($S_DTL + $S_BNR);

                //SECTION-T #### ORGAN TEST


                $S1_DTL = [];
                $collection = collect($datat);
                $distinctValues = $collection->pluck('ID')->unique();
                foreach ($distinctValues as $row) {
                    $fltr_arr = $datat->filter(function ($item) use ($row) {
                        return $item->ID === $row;
                    });


                    $T_DTL = $fltr_arr->map(function ($item) {
                        $homecol = "---";
                        return [
                            "TEST_ID" => $item->TEST_ID,
                            // "TEST_SL" => $item->TEST_SL,
                            "TEST_NAME" => $item->TEST_NAME,
                            // "TEST_CODE" => $item->TEST_CODE,
                            // "TEST_SAMPLE" => $item->TEST_SAMPLE,
                            "TEST_CATG" => $item->TEST_CATG,
                            "COST" => $item->COST,
                            "CATEGORY" => $item->DEPARTMENT,
                            // "TEST_UNIT" => $item->TEST_UNIT,
                            // "NORMAL_RANGE" => $item->NORMAL_RANGE,
                            "TEST_DESC" => $item->TEST_DESC,
                            "ORGAN_ID" => $item->ORGAN_ID,
                            "SAMPLE_ID" => $item->SAMPLE_ID,
                            "TEST_SAMPLE" => $item->TEST_SAMPLE,
                            "ORGAN_NAME" => $item->ORGAN_NAME,
                            "KNOWN_AS" => $item->KNOWN_AS,
                            "FASTING" => $item->FASTING,
                            "GENDER_TYPE" => $item->GENDER_TYPE,
                            "AGE_TYPE" => $item->AGE_TYPE,
                            "REPORT_TIME" => $item->REPORT_TIME,
                            "HOME_COLLECT" => $item->HOME_COLLECT,
                            "PRESCRIPTION" => $item->PRESCRIPTION,
                            "ID_PROOF" => $item->ID_PROOF,
                            "BANNER_URL" => $item->BANNER_URL,
                            "Questions" => [
                                [
                                    "QA1" => $item->QA1,
                                    "QA2" => $item->QA2,
                                    "QA3" => $item->QA3,
                                    "QA4" => $item->QA4,
                                    "QA5" => $item->QA5,
                                    "QA6" => $item->QA6,
                                ]
                            ]

                        ];
                    })->values()->all();
                    $S1_DTL[] = [
                        "DASH_ID" => $row,
                        "DASH_NAME" => $fltr_arr->first()->DASH_NAME,
                        "PHOTO_URL1" => $fltr_arr->first()->PHOTO_URL1,
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
                $fltr_promo_bnr = $promo_bnr1->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'T';
                });
                $T_BNR["Organ_Test_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->values()->all();
                $T["Organ_Test"] = array_values($S1_DTL + $T_BNR);

                //SECTION-H #### TOP WELLNESS PACKAGE
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'H';
                });
                $H_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
                        "DEPT_ID" => $item->TEST_DEPT_ID,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                        "BANNER_URL1" => $item->DI_BNR1,
                        "BANNER_URL2" => $item->DI_BNR2,
                        "BANNER_URL3" => $item->DI_BNR3,
                        "BANNER_URL4" => $item->DI_BNR4,
                        "BANNER_URL5" => $item->DI_BNR5,
                        "BANNER_URL6" => $item->DI_BNR6,
                        "BANNER_URL7" => $item->DI_BNR7,
                        "BANNER_URL8" => $item->DI_BNR8,
                        "BANNER_URL9" => $item->DI_BNR9,
                        "BANNER_URL10" => $item->DI_BNR10,
                        "Questions" => [
                            "QA1" => $item->DIQA1,
                            "QA2" => $item->DIQA2,
                            "QA3" => $item->DIQA3,
                            "QA4" => $item->DIQA4,
                            "QA5" => $item->DIQA5,
                            "QA6" => $item->DIQA6,
                            "QA7" => $item->DIQA7,
                            "QA8" => $item->DIQA8,
                            "QA9" => $item->DIQA9
                        ]
                    ];
                })->values()->all();
                $fltr_promo_bnr = $promo_bnr1->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'H';
                });
                $H_BNR["Top_Wellness_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->values()->all();
                $H["Top_Wellness_Package"] = array_values($H_DTL + $H_BNR);

                //SECTION-AA #### CHILD VACCINE                
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'AA';
                });
                $AE["Child_Vaccine"] = $fltr_promo_bnr->map(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->values()->all();


                //SECTION-V #### WHY MAKES US SPECIAL
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'V';
                });
                $V["What_Makes_Us_Special"] = $fltr_promo_bnr->map(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->values()->all();



                //   SECTION-W #### SPECIAL SERVICES
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'W';
                });

                $W["Special_Services"] = $fltr_promo_bnr->map(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->values()->all();

                //SECTION-X #### ADVANCE EQUIPMENT
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'X';
                });
                $X["Advance_Equipments"] = $fltr_promo_bnr->map(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->values()->all();

                //SECTION-Y #### CUSTOMER REVIEW
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'Y';
                });
                $Y["Customer_Review"] = $fltr_promo_bnr->map(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->values()->all();

                //SECTION-#### MET OUR DOCTORS

                $distinctDoctors = DB::table('dr_availablity')
                    ->select('DR_ID', 'DR_FEES', 'POSITION')
                    ->distinct()
                    ->where(['PHARMA_ID' => $pid])
                    ->where('POSITION', '>', 0)
                    ->orderby('POSITION')
                    ->limit(6);
                // ->get();

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
                        'distinct_doctors.DR_FEES',

                    )
                    ->get()->toArray();


                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SP';
                });
                $DR_BNR["Dr_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->values()->take(3)->all();
                $DD["Meet_Our_Doctors"] = array_values($D_DR + $DR_BNR);

                // SECTION-TDR #### TODAY DOCTOR
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
                    ->where('pharmacy.STATUS', '=', 'Active')
                    ->where('WEEK', 'like', '%' . $weekNumber . '%')
                    ->orWhere('dr_availablity.SCH_DT', $cdy)
                    ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
                    ->orderby('dr_availablity.CHK_OUT_TIME')

                    ->orderbyraw('KM')
                    ->get();



                if ($data1 = []) {

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
                        $ldr['Today_Doctors'][] = [
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
                    usort($ldr['Today_Doctors'], function ($item1, $item2) {
                        $order = ['IN' => 1, 'TIMELY' => 2];
                        $status1 = $order[$item1['DR_STATUS']] ?? 999;
                        $status2 = $order[$item2['DR_STATUS']] ?? 999;
                        if ($status1 == $status2) {
                            return 0;
                        }
                        return ($status1 < $status2) ? -1 : 1;
                    });
                    $filtered_ldr = array_filter($ldr['Today_Doctors'], function ($doctor) {
                        return $doctor['DR_STATUS'] === "IN" || $doctor['DR_STATUS'] === "TIMELY" || $doctor['DR_STATUS'] === "DELAY" || $doctor['DR_STATUS'] === "OUT" || $doctor['DR_STATUS'] === "CANCELLED" || $doctor['DR_STATUS'] === "LEAVE";
                    });
                    $ldr['Today_Doctors'] = array_values($filtered_ldr);
                } else {
                    $ldr['Today_Doctors'] = [];
                }



                ## SECTION Surgery


                $SURG_DTL = $surg->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_TYPE_ID" => $item->DASH_TYPE_ID,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_ID" => $item->DASH_ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_SL" => $item->DN_POSITION,
                        "DESCRIPTION" => $item->DN_DESCRIPTION,

                        "PHOTO_URL1" => $item->DNIMG1,
                        "PHOTO_URL2" => $item->DNIMG2,
                        "PHOTO_URL3" => $item->DNIMG3,
                        "PHOTO_URL4" => $item->DNIMG4,
                        "PHOTO_URL5" => $item->DNIMG5,
                        "PHOTO_URL6" => $item->DNIMG6,
                        "PHOTO_URL7" => $item->DNIMG7,
                        "PHOTO_URL8" => $item->DNIMG8,
                        "PHOTO_URL9" => $item->DNIMG9,
                        "PHOTO_URL10" => $item->DNIMG10,

                        "BANNER_URL1" => $item->DNBNR1,
                        "BANNER_URL2" => $item->DNBNR2,
                        "BANNER_URL3" => $item->DNBNR3,
                        "BANNER_URL4" => $item->DNBNR4,
                        "BANNER_URL5" => $item->DNBNR5,
                        "BANNER_URL6" => $item->DNBNR6,
                        "BANNER_URL7" => $item->DNBNR7,
                        "BANNER_URL8" => $item->DNBNR8,
                        "BANNER_URL9" => $item->DNBNR9,
                        "BANNER_URL10" => $item->DNBNR10,
                        "Questions" => [
                            [
                                "QA1" => $item->DNQA1,
                                "QA2" => $item->DNQA2,
                                "QA3" => $item->DNQA3,
                                "QA4" => $item->DNQA4,
                                "QA5" => $item->DNQA5,
                                "QA6" => $item->DNQA6,
                                "QA7" => $item->DNQA7,
                                "QA8" => $item->DNQA8,
                                "QA9" => $item->DNQA9
                            ]
                        ]
                    ];
                })->values()->take(10)->all();

                $fltr_promo_bnr = $promo_bnr1->filter(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->take(3)->values()->all();
                $SURG["Surgery"] = array_values($SURG_DTL + $SGB);




                // Initialize an array to hold the final structure
                $F1_DTL = [];
                foreach ($scandata as $item) {
                    $dashID = $item->ID;
                    // Search for existing index
                    $index = array_search($dashID, array_column($F1_DTL, 'ID'));

                    if ($index === false) {
                        $index = count($F1_DTL);
                        $F1_DTL[$index] = [
                            "ID" => $dashID,
                            "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                            "DASH_NAME" => $item->DASH_NAME,
                            "DESCRIPTION" => $item->DASH_DESC,
                            "PHOTO_URL1" => $item->DI_IMG1,
                            "PHOTO_URL2" => $item->DI_IMG2,
                            "PHOTO_URL3" => $item->DI_IMG3,
                            "PHOTO_URL4" => $item->DI_IMG4,
                            "PHOTO_URL5" => $item->DI_IMG5,
                            "PHOTO_URL6" => $item->DI_IMG6,
                            "PHOTO_URL7" => $item->DI_IMG7,
                            "PHOTO_URL8" => $item->DI_IMG8,
                            "PHOTO_URL9" => $item->DI_IMG9,
                            "PHOTO_URL10" => $item->DI_IMG10,
                            "BANNER_URL1" => $item->DI_BNR1,
                            "BANNER_URL2" => $item->DI_BNR2,
                            "BANNER_URL3" => $item->DI_BNR3,
                            "BANNER_URL4" => $item->DI_BNR4,
                            "BANNER_URL5" => $item->DI_BNR5,
                            "BANNER_URL6" => $item->DI_BNR6,
                            "BANNER_URL7" => $item->DI_BNR7,
                            "BANNER_URL8" => $item->DI_BNR8,
                            "BANNER_URL9" => $item->DI_BNR9,
                            "BANNER_URL10" => $item->DI_BNR10,
                            "ORGANS" => []
                        ];
                    }

                    // Check if the organ is already in the list
                    $organExists = false;
                    foreach ($F1_DTL[$index]['ORGANS'] as $organ) {
                        if ($organ['ORGAN_ID'] == $item->ORGAN_ID) {
                            $organExists = true;
                            break;
                        }
                    }

                    // Add the organ if it doesn't exist
                    if (!$organExists) {
                        $F1_DTL[$index]['ORGANS'][] = [
                            "ORGAN_ID" => $item->ORGAN_ID,
                            "ORGAN_NAME" => $item->ORGAN_NAME,
                            "ORGAN_URL" => $item->ORGAN_URL,
                        ];
                    }
                }

                $fltr_promo_bnr = $promo_bnr1->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'TS';
                });

                $F_BNR["Popular_Scan_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                        "PHOTO_URL1" => $item->PHOTO_URL1,
                        "PHOTO_URL2" => $item->PHOTO_URL2,
                        "PHOTO_URL3" => $item->PHOTO_URL3,
                        "PHOTO_URL4" => $item->PHOTO_URL4,
                        "PHOTO_URL5" => $item->PHOTO_URL5,
                        "PHOTO_URL6" => $item->PHOTO_URL6,
                        "PHOTO_URL7" => $item->PHOTO_URL7,
                        "PHOTO_URL8" => $item->PHOTO_URL8,
                        "PHOTO_URL9" => $item->PHOTO_URL9,
                        "PHOTO_URL10" => $item->PHOTO_URL10,
                    ];
                })->take(3)->values()->all();

                $F_DTL["Popular_Scan"] = array_values($F1_DTL + $F_BNR);


                //SECTION-AQ #### IPD Section




                $groupedData = [];
                foreach ($IPD_DTL as $row2) {
                    if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
                        $groupedData[$row2->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DESCRIPTION" => $row2->DS_DESCRIPTION,
                            "PHOTO_URL1" => $row2->DSIMG1,
                            "PHOTO_URL2" => $row2->DSIMG2,
                            "PHOTO_URL3" => $row2->DSIMG3,
                            "PHOTO_URL4" => $row2->DSIMG4,
                            "PHOTO_URL5" => $row2->DSIMG5,
                            "PHOTO_URL6" => $row2->DSIMG6,
                            "PHOTO_URL7" => $row2->DSIMG7,
                            "PHOTO_URL8" => $row2->DSIMG8,
                            "PHOTO_URL9" => $row2->DSIMG9,
                            "PHOTO_URL10" => $row2->DSIMG10,

                            "BANNER_URL1" => $row2->DSBNR1,
                            "BANNER_URL2" => $row2->DSBNR2,
                            "BANNER_URL3" => $row2->DSBNR3,
                            "BANNER_URL4" => $row2->DSBNR4,
                            "BANNER_URL5" => $row2->DSBNR5,
                            "BANNER_URL6" => $row2->DSBNR6,
                            "BANNER_URL7" => $row2->DSBNR7,
                            "BANNER_URL8" => $row2->DSBNR8,
                            "BANNER_URL9" => $row2->DSBNR9,
                            "BANNER_URL10" => $row2->DSBNR10,
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
                        "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
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

                $IPD["IPD_Facilities"] = array_values($groupedData);

                //SECTION-I #### Emergency


                $groupedData = [];
                foreach ($EM_DTL as $row2) {
                    if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
                        $groupedData[$row2->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DESCRIPTION" => $row2->DS_DESCRIPTION,
                            // "PHOTO_URL" => $row2->DSM_PHOTO_URL,
                            // "BANNER_URL" => $row2->DS_BANNER_URL,

                            "PHOTO_URL1" => $row2->DSIMG1,
                            "PHOTO_URL2" => $row2->DSIMG2,
                            "PHOTO_URL3" => $row2->DSIMG3,
                            "PHOTO_URL4" => $row2->DSIMG4,
                            "PHOTO_URL5" => $row2->DSIMG5,
                            "PHOTO_URL6" => $row2->DSIMG6,
                            "PHOTO_URL7" => $row2->DSIMG7,
                            "PHOTO_URL8" => $row2->DSIMG8,
                            "PHOTO_URL9" => $row2->DSIMG9,
                            "PHOTO_URL10" => $row2->DSIMG10,

                            "BANNER_URL1" => $row2->DSBNR1,
                            "BANNER_URL2" => $row2->DSBNR2,
                            "BANNER_URL3" => $row2->DSBNR3,
                            "BANNER_URL4" => $row2->DSBNR4,
                            "BANNER_URL5" => $row2->DSBNR5,
                            "BANNER_URL6" => $row2->DSBNR6,
                            "BANNER_URL7" => $row2->DSBNR7,
                            "BANNER_URL8" => $row2->DSBNR8,
                            "BANNER_URL9" => $row2->DSBNR9,
                            "BANNER_URL10" => $row2->DSBNR10,
                            "DASH_TYPE" => []
                        ];
                    }

                    if (!isset($groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE])) {
                        $groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DASH_TYPE" => $row2->DASH_TYPE,
                            "DESCRIPTION" => $row2->DT_DESCRIPTION,

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
                        "DASH_NAME" => $row2->DASH_NAME,
                        "DASH_TYPE" => $row2->DASH_TYPE,
                        "DESCRIPTION" => $row2->DN_DESCRIPTION,
                        "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
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
                $a = array_values($groupedData);
                $I["Emergency"] = $a;

                //SECTION-AQ #### Service from Home



                //SECTION-AJ #### 2nd Opinion


                // return $I_DTL;

                $groupedData = [];
                foreach ($SND_DTL as $row2) {
                    $ds_dtl = DB::table('facility_section')->where('DASH_SECTION_ID', 'AM')->first();
                    $DsID = $ds_dtl->DASH_SECTION_ID;
                    $Dsname = $ds_dtl->DASH_SECTION_NAME;

                    if (!isset($groupedData[$ds_dtl->DASH_SECTION_ID])) {
                        $groupedData[$ds_dtl->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $ds_dtl->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $ds_dtl->DASH_SECTION_NAME,
                            "DESCRIPTION" => $ds_dtl->DS_DESCRIPTION,
                            "PHOTO_URL1" => $ds_dtl->DSIMG1,
                            "PHOTO_URL2" => $ds_dtl->DSIMG2,
                            "PHOTO_URL3" => $ds_dtl->DSIMG3,
                            "PHOTO_URL4" => $ds_dtl->DSIMG4,
                            "PHOTO_URL5" => $ds_dtl->DSIMG5,
                            "PHOTO_URL6" => $ds_dtl->DSIMG6,
                            "PHOTO_URL7" => $ds_dtl->DSIMG7,
                            "PHOTO_URL8" => $ds_dtl->DSIMG8,
                            "PHOTO_URL9" => $ds_dtl->DSIMG9,
                            "PHOTO_URL10" => $ds_dtl->DSIMG10,

                            "BANNER_URL1" => $ds_dtl->DSBNR1,
                            "BANNER_URL2" => $ds_dtl->DSBNR2,
                            "BANNER_URL3" => $ds_dtl->DSBNR3,
                            "BANNER_URL4" => $ds_dtl->DSBNR4,
                            "BANNER_URL5" => $ds_dtl->DSBNR5,
                            "BANNER_URL6" => $ds_dtl->DSBNR6,
                            "BANNER_URL7" => $ds_dtl->DSBNR7,
                            "BANNER_URL8" => $ds_dtl->DSBNR8,
                            "BANNER_URL9" => $ds_dtl->DSBNR9,
                            "BANNER_URL10" => $ds_dtl->DSBNR10,
                            "DASH_TYPE" => []
                        ];
                    }

                    if (!isset($groupedData[$ds_dtl->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE])) {
                        $groupedData[$ds_dtl->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE] = [
                            "DASH_SECTION_ID" => $DsID,
                            "DASH_SECTION_NAME" => $Dsname,
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

                    $groupedData[$ds_dtl->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE]['FACILITY_DETAILS'][] = [
                        "DASH_ID" => $row2->DASH_ID,
                        // "DIS_ID" => $row2->DIS_ID,
                        // "SYM_ID" => $row2->SYM_ID,
                        "DASH_NAME" => $row2->DASH_NAME,
                        "DASH_TYPE" => $row2->DASH_TYPE,
                        "DESCRIPTION" => $row2->DN_DESCRIPTION,
                        "DASH_SECTION_ID" => $DsID,
                        "DASH_SECTION_NAME" => $Dsname,
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
                        "Questions" => [
                            [
                                "QA1" => $row2->DNQA1,
                                "QA2" => $row2->DNQA2,
                                "QA3" => $row2->DNQA3,
                                "QA4" => $row2->DNQA4,
                                "QA5" => $row2->DNQA5,
                                "QA6" => $row2->DNQA6,
                                "QA7" => $row2->DNQA7,
                                "QA8" => $row2->DNQA8,
                                "QA9" => $row2->DNQA9
                            ]
                        ]

                    ];
                }


                $AJ["Second_Opinion"] = array_values($groupedData);



                //SECTION-#### Inserence



                $groupedData = [];
                foreach ($INS_DTL as $row2) {
                    if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
                        $groupedData[$row2->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DESCRIPTION" => $row2->DS_DESCRIPTION,
                            "PHOTO_URL1" => $row2->DSIMG1,
                            "PHOTO_URL2" => $row2->DSIMG2,
                            "PHOTO_URL3" => $row2->DSIMG3,
                            "PHOTO_URL4" => $row2->DSIMG4,
                            "PHOTO_URL5" => $row2->DSIMG5,
                            "PHOTO_URL6" => $row2->DSIMG6,
                            "PHOTO_URL7" => $row2->DSIMG7,
                            "PHOTO_URL8" => $row2->DSIMG8,
                            "PHOTO_URL9" => $row2->DSIMG9,
                            "PHOTO_URL10" => $row2->DSIMG10,
                            "BANNER_URL1" => $row2->DSBNR1,
                            "BANNER_URL2" => $row2->DSBNR2,
                            "BANNER_URL3" => $row2->DSBNR3,
                            "BANNER_URL4" => $row2->DSBNR4,
                            "BANNER_URL5" => $row2->DSBNR5,
                            "BANNER_URL6" => $row2->DSBNR6,
                            "BANNER_URL7" => $row2->DSBNR7,
                            "BANNER_URL8" => $row2->DSBNR8,
                            "BANNER_URL9" => $row2->DSBNR9,
                            "BANNER_URL10" => $row2->DSBNR10,
                            "DASH_NAME" => [] // Change to just hold facility details
                        ];
                    }

                    $groupedData[$row2->DASH_SECTION_ID]['DASH_NAME'][] = [
                        "DASH_ID" => $row2->DASH_ID,
                        "DASH_NAME" => $row2->DASH_NAME,
                        "DASH_TYPE" => $row2->DASH_TYPE,
                        "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                        "DESCRIPTION" => $row2->DN_DESCRIPTION,
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
                $INS["Insurance"] = array_values($groupedData);


                // $data = $A + $cldata + $DASH_Z + $U + $SPLST + $TST + $F_DTL + $AE + $SYM + $C + $DASH_BNR + $S + $T + $B + $G + $H + $V + $W + $X + $Y + $DD + $ldr + $IPD + $SURG + $I + $AJ + $INS + $AS; // + $S + $T
                // Ensure all collections are converted to arrays before merging
                $data = [];
                $data = array_merge(
                    is_array($A) ? $A : $A->toArray(),
                    is_array($cldata) ? $cldata : $cldata->toArray(),
                    is_array($DASH_Z) ? $DASH_Z : $DASH_Z->toArray(),
                    is_array($U) ? $U : $U->toArray(),
                    is_array($SPLST) ? $SPLST : $SPLST->toArray(),
                    is_array($TST) ? $TST : $TST->toArray(),
                    is_array($F_DTL) ? $F_DTL : $F_DTL->toArray(),
                    is_array($AE) ? $AE : $AE->toArray(),
                    is_array($SYM) ? $SYM : $SYM->toArray(),
                    is_array($C) ? $C : $C->toArray(),
                    is_array($DASH_BNR) ? $DASH_BNR : $DASH_BNR->toArray(),
                    is_array($S) ? $S : $S->toArray(),
                    is_array($T) ? $T : $T->toArray(),
                    is_array($B) ? $B : $B->toArray(),
                    is_array($G) ? $G : $G->toArray(),
                    is_array($H) ? $H : $H->toArray(),
                    is_array($V) ? $V : $V->toArray(),
                    is_array($W) ? $W : $W->toArray(),
                    is_array($X) ? $X : $X->toArray(),
                    is_array($Y) ? $Y : $Y->toArray(),
                    is_array($DD) ? $DD : $DD->toArray(),
                    is_array($ldr) ? $ldr : $ldr->toArray(),
                    is_array($IPD) ? $IPD : $IPD->toArray(),
                    is_array($SURG) ? $SURG : $SURG->toArray(),
                    is_array($I) ? $I : $I->toArray(),
                    is_array($AJ) ? $AJ : $AJ->toArray(),
                    is_array($INS) ? $INS : $INS->toArray(),
                    is_array($AS) ? $AS : $AS->toArray() // Convert $AS to array
                );
                if (isset($SFH)) {
                    $data = array_merge($data, is_array($SFH) ? $SFH : $SFH->toArray());
                }
                if (isset($COR)) {
                    $data = array_merge($data, is_array($COR) ? $COR : $COR->toArray());
                }
                if (isset($INTL_PAT)) {
                    $data = array_merge($data, is_array($INTL_PAT) ? $INTL_PAT : $INTL_PAT->toArray());
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



    function labdashboard1(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $promo_bnr = DB::table('promo_banner')->where('STATUS', 'Active')->get();
                // $dash = DB::table('dashboard')->where('CATEGORY', 'like', '%' . 'L' . '%')->where('STATUS', 'Active')->get();

                $dash = DB::table('dashboard_section')
                    ->join('dashboard_item', 'dashboard_section.DASH_SECTION_ID', 'dashboard_item.DASH_SECTION_ID')
                    ->where('dashboard_section.DS_STATUS', 'Active')
                    ->orderByRaw('CASE WHEN dashboard_item.DISL3 IS NULL THEN 1 ELSE 0 END, dashboard_item.DISL3 ASC')
                    ->get();
                // return $dash;

                //SECTION-A #### SLIDER
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'LD';
                });
                $A["Slider"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "SLIDER_ID" => $item->PROMO_ID,
                        "SLIDER_NAME" => $item->PROMO_NAME,
                        "SLIDER_URL" => $item->PROMO_URL,
                    ];
                })->values()->all();

                //SECTION-F #### DASHBOARD

                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'A' && stripos($item->DASH_ITEM_TAGGED, 'L') !== false;
                });
                $F["Dashboard"] = $fltr_dash->map(function ($item) {
                    return [
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_SECTION_ID" => $item->FACILITY_ID,
                        "DASH_SECTION_NAME" => $item->DASH_NAME,
                        "DESCRIPTION" => $item->DASH_DESC,
                        "PHOTO_URL1" => $item->DI_IMG1,

                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                        "BANNER_URL1" => $item->DI_BNR1,
                        "BANNER_URL2" => $item->DI_BNR2,
                        "BANNER_URL3" => $item->DI_BNR3,
                        "BANNER_URL4" => $item->DI_BNR4,
                        "BANNER_URL5" => $item->DI_BNR5,
                        "BANNER_URL6" => $item->DI_BNR6,
                        "BANNER_URL7" => $item->DI_BNR7,
                        "BANNER_URL8" => $item->DI_BNR8,
                        "BANNER_URL9" => $item->DI_BNR9,
                        "BANNER_URL10" => $item->DI_BNR10,
                        "Questions" => [
                            "QA1" => $item->DIQA1,
                            "QA2" => $item->DIQA2,
                            "QA3" => $item->DIQA3,
                            "QA4" => $item->DIQA4,
                            "QA5" => $item->DIQA5,
                            "QA6" => $item->DIQA6,
                            "QA7" => $item->DIQA7,
                            "QA8" => $item->DIQA8,
                            "QA9" => $item->DIQA9
                        ]
                    ];

                })->values()->all();


                $data1 = DB::table('dashboard_section')
                    ->join('dashboard_item', 'dashboard_item.DASH_SECTION_ID', '=', 'dashboard_section.DASH_SECTION_ID')
                    ->join('master_test', function ($join) {
                        $join->on('dashboard_item.SUB_DEPT_ID', '=', 'master_test.SUB_DEPT_ID')
                            ->where('master_test.DEPT_ID', '=', 'D1')
                            ->where('dashboard_item.DASH_SECTION_ID', '=', 'F');
                    })
                    ->leftJoin('test_scanorgan', 'master_test.ORGAN_ID', '=', 'test_scanorgan.ORGAN_ID')
                    ->select(
                        'dashboard_section.DASH_SECTION_NAME',
                        'dashboard_item.ID',
                        'dashboard_item.DASH_SECTION_ID',
                        'dashboard_item.DASH_NAME',
                        'dashboard_item.SUB_DEPT_ID',
                        'dashboard_item.DASH_DESC',
                        'dashboard_item.DASH_POSITION',
                        'dashboard_item.DASH_STATUS',
                        'dashboard_item.DI_IMG1',
                        'dashboard_item.DI_IMG2',
                        'dashboard_item.DI_IMG3',
                        'dashboard_item.DI_IMG4',
                        'dashboard_item.DI_IMG5',
                        'dashboard_item.DI_IMG6',
                        'dashboard_item.DI_IMG7',
                        'dashboard_item.DI_IMG8',
                        'dashboard_item.DI_IMG9',
                        'dashboard_item.DI_IMG10',
                        'dashboard_item.DI_BNR1',
                        'dashboard_item.DI_BNR2',
                        'dashboard_item.DI_BNR3',
                        'dashboard_item.DI_BNR4',
                        'dashboard_item.DI_BNR5',
                        'dashboard_item.DI_BNR6',
                        'dashboard_item.DI_BNR7',
                        'dashboard_item.DI_BNR8',
                        'dashboard_item.DI_BNR9',
                        'dashboard_item.DI_BNR10',
                        'dashboard_item.DIQA1',
                        'dashboard_item.DIQA2',
                        'dashboard_item.DIQA3',
                        'dashboard_item.DIQA4',
                        'dashboard_item.DIQA5',
                        'dashboard_item.DIQA6',
                        'dashboard_item.DIQA7',
                        'dashboard_item.DIQA8',
                        'dashboard_item.DIQA9',
                        'test_scanorgan.ORGAN_ID',
                        'test_scanorgan.ORGAN_NAME',
                        'test_scanorgan.OIMG1 as ORGAN_URL',
                        'master_test.DEPT_ID',
                        'master_test.SUB_DEPT_ID',
                        // 'test_sub_dept.SUB_DEPT_NAME'
                    )
                    // ->orderBy('dashboard_item.DASH_POSITION')
                    ->orderByRaw('CASE WHEN dashboard_item.DISL3 IS NULL THEN 1 ELSE 0 END, dashboard_item.DISL3 ASC')
                    ->get();

                // Initialize an array to hold the final structure
                $F1_DTL = [];
                foreach ($data1 as $item) {
                    $dashID = $item->ID;
                    if (!isset($F1_DTL[$dashID])) {
                        $F1_DTL[$dashID] = [
                            "ID" => $dashID,
                            "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                            "DESCRIPTION" => $item->DASH_DESC,
                            "DASH_NAME" => $item->DASH_NAME,
                            "SUB_DEPT_ID" => $item->SUB_DEPT_ID,
                            "PHOTO_URL1" => $item->DI_IMG1,
                            "PHOTO_URL2" => $item->DI_IMG2,
                            "PHOTO_URL3" => $item->DI_IMG3,
                            "PHOTO_URL4" => $item->DI_IMG4,
                            "PHOTO_URL5" => $item->DI_IMG5,
                            "PHOTO_URL6" => $item->DI_IMG6,
                            "PHOTO_URL7" => $item->DI_IMG7,
                            "PHOTO_URL8" => $item->DI_IMG8,
                            "PHOTO_URL9" => $item->DI_IMG9,
                            "PHOTO_URL10" => $item->DI_IMG10,
                            "BANNER_URL1" => $item->DI_BNR1,
                            "BANNER_URL2" => $item->DI_BNR2,
                            "BANNER_URL3" => $item->DI_BNR3,
                            "BANNER_URL4" => $item->DI_BNR4,
                            "BANNER_URL5" => $item->DI_BNR5,
                            "BANNER_URL6" => $item->DI_BNR6,
                            "BANNER_URL7" => $item->DI_BNR7,
                            "BANNER_URL8" => $item->DI_BNR8,
                            "BANNER_URL9" => $item->DI_BNR9,
                            "BANNER_URL10" => $item->DI_BNR10,
                            "ORGANS" => []
                        ];
                    }

                    // Check if the organ is already in the list
                    $organExists = false;
                    foreach ($F1_DTL[$dashID]['ORGANS'] as $organ) {
                        if ($organ['ORGAN_ID'] == $item->ORGAN_ID) {
                            $organExists = true;
                            break;
                        }
                    }

                    // Add the organ if it doesn't exist
                    if (!$organExists) {
                        $F1_DTL[$dashID]['ORGANS'][] = [
                            "ORGAN_ID" => $item->ORGAN_ID,
                            "ORGAN_NAME" => $item->ORGAN_NAME,
                            "ORGAN_URL" => $item->ORGAN_URL,
                        ];
                    }
                }

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'F';
                });
                $F1_BNR["Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                $F1["Popular_Scan"] = array_values($F1_DTL + $F1_BNR);



                //SECTION-####LABDASHBOARD BANNER
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'LD' && $item->PROMO_TYPE === 'Package';
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

                // Retrieve data from database
                // $data = DB::table('dashboard_item')
                //     ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard_item.ID')
                //     ->join(DB::raw('(SELECT DISTINCT TEST_ID, TEST_SL, TEST_NAME, TEST_CODE, TEST_SAMPLE, SUB_DEPT_ID, TEST_CATG, DEPARTMENT, TEST_DESC, KNOWN_AS, FASTING, GENDER_TYPE, AGE_TYPE, PRESCRIPTION, ID_PROOF, QA1, QA2, QA3, QA4, QA5, QA6, MIN(COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID, TEST_SL, TEST_NAME, SUB_DEPT_ID, TEST_CODE, TEST_SAMPLE, TEST_CATG, DEPARTMENT, TEST_DESC, KNOWN_AS, FASTING, GENDER_TYPE, AGE_TYPE, PRESCRIPTION, ID_PROOF, QA1, QA2, QA3, QA4, QA5, QA6) as clinic_testdata'), function ($join) {
                //         $join->on('sym_organ_test.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                //     })
                //     ->join('test_sub_dept', 'clinic_testdata.SUB_DEPT_ID', '=', 'test_sub_dept.SUB_DEPT_ID')
                //     ->select(
                //         'dashboard_item.ID AS DASH_ID',
                //         'dashboard_item.DASH_NAME',
                //         'dashboard_item.DASH_SECTION_ID',
                //         'dashboard_item.DI_IMG1 as PHOTO_URL1',
                //         'dashboard_item.DI_IMG2 as PHOTO_URL2',
                //         'dashboard_item.DI_IMG3 as PHOTO_URL3',
                //         'dashboard_item.DI_IMG4 as PHOTO_URL4',
                //         'dashboard_item.DI_IMG5 as PHOTO_URL5',
                //         'dashboard_item.DI_IMG6 as PHOTO_URL6',
                //         'dashboard_item.DI_IMG7 as PHOTO_URL7',
                //         'dashboard_item.DI_IMG8 as PHOTO_URL8',
                //         'dashboard_item.DI_IMG9 as PHOTO_URL9',
                //         'dashboard_item.DI_IMG10 as PHOTO_URL10',
                //         'clinic_testdata.TEST_ID',
                //         'clinic_testdata.TEST_SL',
                //         'clinic_testdata.TEST_NAME',
                //         'clinic_testdata.TEST_CODE',
                //         'clinic_testdata.TEST_SAMPLE',
                //         'clinic_testdata.TEST_CATG',
                //         'clinic_testdata.DEPARTMENT',
                //         'clinic_testdata.TEST_DESC',
                //         'clinic_testdata.KNOWN_AS',
                //         'clinic_testdata.FASTING',
                //         'clinic_testdata.GENDER_TYPE',
                //         'clinic_testdata.AGE_TYPE',
                //         'clinic_testdata.PRESCRIPTION',
                //         'clinic_testdata.ID_PROOF',
                //         'clinic_testdata.QA1',
                //         'clinic_testdata.QA2',
                //         'clinic_testdata.QA3',
                //         'clinic_testdata.QA4',
                //         'clinic_testdata.QA5',
                //         'clinic_testdata.QA6',
                //         'clinic_testdata.MIN_COST',
                //         'test_sub_dept.SDBNR1 as BANNER_URL'
                //     )
                //     ->where('dashboard_item.DASH_STATUS', 'Active')
                //     // ->orderby('dashboard_item.DASH_POSITION')
                //     ->orderByRaw('CASE WHEN dashboard_item.DISL3 IS NULL THEN 1 ELSE 0 END, dashboard_item.DISL3 ASC')
                //     ->get();

                // // Filter for Symptomatic Tests
                // $symptomaticData = $data->filter(function ($item) {
                //     return $item->DASH_SECTION_ID === 'S';
                // });

                

                // // Filter for Organ Tests
                // $organData = $data->filter(function ($item) {
                //     return $item->DASH_SECTION_ID === 'T';
                // });

                // $T_DTL = $organData->groupBy('DASH_ID')->map(function ($group) {
                //     $testDetails = $group->map(function ($item) {
                //         return [
                //             "TEST_ID" => $item->TEST_ID,
                //             "TEST_SL" => $item->TEST_SL,
                //             "TEST_NAME" => $item->TEST_NAME,
                //             "TEST_CODE" => $item->TEST_CODE,
                //             "TEST_SAMPLE" => $item->TEST_SAMPLE,
                //             "TEST_CATG" => $item->TEST_CATG,
                //             "DEPARTMENT" => $item->DEPARTMENT,
                //             "TEST_DESC" => $item->TEST_DESC,
                //             "MIN_COST" => $item->MIN_COST,
                //             "KNOWN_AS" => $item->KNOWN_AS,
                //             "FASTING" => $item->FASTING,
                //             "GENDER_TYPE" => $item->GENDER_TYPE,
                //             "AGE_TYPE" => $item->AGE_TYPE,
                //             "REPORT_TIME" => '---',
                //             "PRESCRIPTION" => $item->PRESCRIPTION,
                //             "ID_PROOF" => $item->ID_PROOF,
                //             "BANNER_URL" => $item->BANNER_URL,
                //             "Questions" => [
                //                 "QA1" => $item->QA1,
                //                 "QA2" => $item->QA2,
                //                 "QA3" => $item->QA3,
                //                 "QA4" => $item->QA4,
                //                 "QA5" => $item->QA5,
                //                 "QA6" => $item->QA6
                //             ]
                //         ];
                //     });

                //     return [
                //         "ID" => $group->first()->DASH_ID,
                //         "DASH_NAME" => $group->first()->DASH_NAME,
                //         "DASH_SECTION_ID" => $group->first()->DASH_SECTION_ID,
                //         "PHOTO_URL1" => $group->first()->PHOTO_URL1,
                //         "PHOTO_URL2" => $group->first()->PHOTO_URL2,
                //         "PHOTO_URL3" => $group->first()->PHOTO_URL3,
                //         "PHOTO_URL4" => $group->first()->PHOTO_URL4,
                //         "PHOTO_URL5" => $group->first()->PHOTO_URL5,
                //         "PHOTO_URL6" => $group->first()->PHOTO_URL6,
                //         "PHOTO_URL7" => $group->first()->PHOTO_URL7,
                //         "PHOTO_URL8" => $group->first()->PHOTO_URL8,
                //         "PHOTO_URL9" => $group->first()->PHOTO_URL9,
                //         "PHOTO_URL10" => $group->first()->PHOTO_URL10,
                //         "TOT_TEST" => $testDetails->count(),
                //         "DETAILS" => $testDetails->values()->all()
                //     ];
                // })->values()->all();

                // foreach ($symptomaticData as $row) {
                //     $fltr_arr = $data1->filter(function ($item) use ($row) {
                //         return $item->DASH_ID === $row;
                //     });

                //     $T_DTL = $fltr_arr->map(function ($item) {
                //         $home = "---";
                //         return [
                //             "TEST_ID" => $item->TEST_ID,
                //             "TEST_SL" => $item->TEST_SL,
                //             "TEST_NAME" => $item->TEST_NAME,
                //             "TEST_CODE" => $item->TEST_CODE,
                //             "TEST_SAMPLE" => $item->TEST_SAMPLE,
                //             "TEST_CATG" => $item->TEST_CATG,
                //             "DEPARTMENT" => $item->DEPARTMENT,
                //             // "TEST_UNIT" => $item->TEST_UNIT,
                //             // "NORMAL_RANGE" => $item->NORMAL_RANGE,
                //             "TEST_DESC" => $item->TEST_DESC,
                //             "MIN_COST" => $item->MIN_COST,
                //             "KNOWN_AS" => $item->KNOWN_AS,
                //             "FASTING" => $item->FASTING,
                //             "GENDER_TYPE" => $item->GENDER_TYPE,
                //             "AGE_TYPE" => $item->AGE_TYPE,
                //             "REPORT_TIME" => $home,
                //             "HOME_COLLECT" => $home,
                //             "PRESCRIPTION" => $item->PRESCRIPTION,
                //             "ID_PROOF" => $item->ID_PROOF,
                //             "BANNER_URL" => $item->BANNER_URL,
                //             "Questions" => [
                //                 [
                //                     "QA1" => $item->QA1,
                //                     "QA2" => $item->QA2,
                //                     "QA3" => $item->QA3,
                //                     "QA4" => $item->QA4,
                //                     "QA5" => $item->QA5,
                //                     "QA6" => $item->QA6,
                //                 ]
                //             ]

                //         ];
                //     })->values()->all();
                //     $S_DTL[] = [
                //         "ID" => $row,
                //         "DASH_NAME" => $fltr_arr->first()->DASH_NAME,
                //         "PHOTO_URL" => $fltr_arr->first()->PHOTO_URL1,
                //         "PHOTO_URL2" => $fltr_arr->first()->PHOTO_URL2,
                //         "PHOTO_URL3" => $fltr_arr->first()->PHOTO_URL3,
                //         "PHOTO_URL4" => $fltr_arr->first()->PHOTO_URL4,
                //         "PHOTO_URL5" => $fltr_arr->first()->PHOTO_URL5,
                //         "PHOTO_URL6" => $fltr_arr->first()->PHOTO_URL6,
                //         "PHOTO_URL7" => $fltr_arr->first()->PHOTO_URL7,
                //         "PHOTO_URL8" => $fltr_arr->first()->PHOTO_URL8,
                //         "PHOTO_URL9" => $fltr_arr->first()->PHOTO_URL9,
                //         "PHOTO_URL10" => $fltr_arr->first()->PHOTO_URL10,
                //         "TOT_TEST" => count($T_DTL),
                //         // "TOT_COST" => array_sum(array_column($T_DTL, 'COST')),
                //         "DETAILS" => $T_DTL
                //     ];
                // }

                // // Add promotional banners
                // $symptomaticBanners = $promo_bnr->filter(function ($item) {
                //     return $item->DASH_SECTION_ID === 'S';
                // })->map(function ($item) {
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

                // $S_DTL[] = $modifiedsymto_bnr;
                $data1 = DB::table('dashboard_item')
                ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard_item.ID')
                ->join(DB::raw('
    (
        SELECT 
            TEST_ID,
            TEST_SL,
            TEST_NAME,
            TEST_CODE,
            TEST_SAMPLE,
            TEST_CATG,
            DEPARTMENT,
            TEST_DESC,
            KNOWN_AS,
            FASTING,
            GENDER_TYPE,
            AGE_TYPE,
            PRESCRIPTION,
            SUB_DEPT_ID,
            ID_PROOF,
            QA1,
            QA2,
            QA3,
            QA4,
            QA5,
            QA6,
            MIN(COST) as MIN_COST
        FROM 
            clinic_testdata
        GROUP BY
            TEST_ID,
            TEST_SL,
            TEST_NAME,
            TEST_CODE,
            TEST_SAMPLE,
            TEST_CATG,
            DEPARTMENT,
            SUB_DEPT_ID,
            TEST_DESC,
            KNOWN_AS,
            FASTING,
            GENDER_TYPE,
            AGE_TYPE,
            PRESCRIPTION,
            ID_PROOF,
            QA1,
            QA2,
            QA3,
            QA4,
            QA5,
            QA6
    ) as clinic_testdata
'), function ($join) {
                    $join->on('sym_organ_test.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                })
                ->join('test_sub_dept', 'clinic_testdata.SUB_DEPT_ID', '=', 'test_sub_dept.SUB_DEPT_ID')
                ->select(
                    'dashboard_item.ID',
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
                    'clinic_testdata.PRESCRIPTION',
                    'clinic_testdata.ID_PROOF',
                    'clinic_testdata.QA1',
                    'clinic_testdata.QA2',
                    'clinic_testdata.QA3',
                    'clinic_testdata.QA4',
                    'clinic_testdata.QA5',
                    'clinic_testdata.QA6',
                    'clinic_testdata.MIN_COST',
                    'test_sub_dept.SDBNR1 as BANNER_URL'
                )
                ->where([
                    'dashboard_item.DASH_SECTION_ID' => 'S',
                    'dashboard_item.DASH_STATUS' => 'Active',
                    'clinic_testdata.DEPARTMENT' => 'PATHOLOGY'
                ])
                ->orderBy('dashboard_item.DASH_POSITION')
                ->get();

            $S_DTL = [];
            $collection = collect($data1);
            $distinctValues = $collection->pluck('ID')->unique();
            foreach ($distinctValues as $row) {
                $fltr_arr = $data1->filter(function ($item) use ($row) {
                    return $item->ID === $row;
                });

                $T_DTL = $fltr_arr->map(function ($item) {
                    $home = "---";
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
                        "REPORT_TIME" => $home,
                        "HOME_COLLECT" => $home,
                        "PRESCRIPTION" => $item->PRESCRIPTION,
                        "ID_PROOF" => $item->ID_PROOF,
                        "BANNER_URL" => $item->BANNER_URL,
                        "Questions" => [
                            [
                                "QA1" => $item->QA1,
                                "QA2" => $item->QA2,
                                "QA3" => $item->QA3,
                                "QA4" => $item->QA4,
                                "QA5" => $item->QA5,
                                "QA6" => $item->QA6,
                            ]
                        ]

                    ];
                })->values()->all();
                $S_DTL[] = [
                    "ID" => $row,
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

            // $S["Symptomatic_Pathology_Test"] = array_values($S_DTL);
                $S["Symptomatic_Test"] = array_values($S_DTL);
                // Prepare final data
                // $S["Symptomatic_Test"][] = array_merge($S_DTL, ["Symptomatic_Test_Banner" => $symptomaticBanners]);

                // $organBanners = $promo_bnr->filter(function ($item) {
                //     return $item->DASH_SECTION_ID === 'T';
                // })->map(function ($item) {
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

                
                // $T["Organ_Test"][] = array_merge($T_DTL, ["Organ_Test_Banner" => $organBanners]);


                //SECTION-#### DIAGNOSTIC
                $DIAG_DTL = DB::table('pharmacy')
                    ->leftjoin('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
                    ->distinct('pharmacy.PHARMA_ID')
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
                        // DB::raw('COUNT(distinct dr_availablity.DR_ID) as TOT_DR'),
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                 * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                  * SIN(RADIANS('$latt'))))),2) as KM")
                    )
                    ->where('pharmacy.CLINIC_TYPE', '<>', 'Clinic')
                    ->where(['STATUS' => 'Active'])
                    ->orderby('KM')
                    ->take(25)
                    ->get()->toArray();
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'CL';
                });
                $DIAG_BNR["Diagnostic_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                $DIAG["Popular_Lab"] = array_values($DIAG_DTL + $DIAG_BNR);

                //SECTION-C #### PROFILE


                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'C';
                });
                $C_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
                        "DEPT_ID" => $item->TEST_DEPT_ID,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                        "BANNER_URL1" => $item->DI_BNR1,
                        "BANNER_URL2" => $item->DI_BNR2,
                        "BANNER_URL3" => $item->DI_BNR3,
                        "BANNER_URL4" => $item->DI_BNR4,
                        "BANNER_URL5" => $item->DI_BNR5,
                        "BANNER_URL6" => $item->DI_BNR6,
                        "BANNER_URL7" => $item->DI_BNR7,
                        "BANNER_URL8" => $item->DI_BNR8,
                        "BANNER_URL9" => $item->DI_BNR9,
                        "BANNER_URL10" => $item->DI_BNR10,
                        "Questions" => [
                            [
                                "QA1" => $item->DIQA1,
                                "QA2" => $item->DIQA2,
                                "QA3" => $item->DIQA3,
                                "QA4" => $item->DIQA4,
                                "QA5" => $item->DIQA5,
                                "QA6" => $item->DIQA6,
                                "QA7" => $item->DIQA7,
                                "QA8" => $item->DIQA8,
                                "QA9" => $item->DIQA9
                            ]
                        ]
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

                //SECTION-G #### PACKAGE

                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'G';
                });
                $G_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
                        "DEPT_ID" => $item->TEST_DEPT_ID,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                        "BANNER_URL1" => $item->DI_BNR1,
                        "BANNER_URL2" => $item->DI_BNR2,
                        "BANNER_URL3" => $item->DI_BNR3,
                        "BANNER_URL4" => $item->DI_BNR4,
                        "BANNER_URL5" => $item->DI_BNR5,
                        "BANNER_URL6" => $item->DI_BNR6,
                        "BANNER_URL7" => $item->DI_BNR7,
                        "BANNER_URL8" => $item->DI_BNR8,
                        "BANNER_URL9" => $item->DI_BNR9,
                        "BANNER_URL10" => $item->DI_BNR10,
                        "Questions" => [
                            [
                                "QA1" => $item->DIQA1,
                                "QA2" => $item->DIQA2,
                                "QA3" => $item->DIQA3,
                                "QA4" => $item->DIQA4,
                                "QA5" => $item->DIQA5,
                                "QA6" => $item->DIQA6,
                                "QA7" => $item->DIQA7,
                                "QA8" => $item->DIQA8,
                                "QA9" => $item->DIQA9
                            ]
                        ]
                    ];
                })->values()->all();

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'G';
                });
                $G_BNR["Popular_Health_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                $G["Popular_Health_Package"] = array_values($G_DTL + $G_BNR);

                //SECTION-H #### TOP WELLNESS PACKAGE
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'H';
                });

                $H_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
                        "DEPT_ID" => $item->TEST_DEPT_ID,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                        "BANNER_URL1" => $item->DI_BNR1,
                        "BANNER_URL2" => $item->DI_BNR2,
                        "BANNER_URL3" => $item->DI_BNR3,
                        "BANNER_URL4" => $item->DI_BNR4,
                        "BANNER_URL5" => $item->DI_BNR5,
                        "BANNER_URL6" => $item->DI_BNR6,
                        "BANNER_URL7" => $item->DI_BNR7,
                        "BANNER_URL8" => $item->DI_BNR8,
                        "BANNER_URL9" => $item->DI_BNR9,
                        "BANNER_URL10" => $item->DI_BNR10,
                        "Questions" => [
                            [
                                "QA1" => $item->DIQA1,
                                "QA2" => $item->DIQA2,
                                "QA3" => $item->DIQA3,
                                "QA4" => $item->DIQA4,
                                "QA5" => $item->DIQA5,
                                "QA6" => $item->DIQA6,
                                "QA7" => $item->DIQA7,
                                "QA8" => $item->DIQA8,
                                "QA9" => $item->DIQA9
                            ]
                        ]
                    ];
                })->values()->all();
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'H';
                });
                $H_BNR["Top_Wellness_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                $H["Top_Wellness_Package"] = array_values($H_DTL + $H_BNR);

                // //SECTION-B #### FAMILY CARE PACKAGES
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'B';
                });

                $B_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
                        "DEPT_ID" => $item->TEST_DEPT_ID,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                        "BANNER_URL1" => $item->DI_BNR1,
                        "BANNER_URL2" => $item->DI_BNR2,
                        "BANNER_URL3" => $item->DI_BNR3,
                        "BANNER_URL4" => $item->DI_BNR4,
                        "BANNER_URL5" => $item->DI_BNR5,
                        "BANNER_URL6" => $item->DI_BNR6,
                        "BANNER_URL7" => $item->DI_BNR7,
                        "BANNER_URL8" => $item->DI_BNR8,
                        "BANNER_URL9" => $item->DI_BNR9,
                        "BANNER_URL10" => $item->DI_BNR10,
                        "Questions" => [
                            [
                                "QA1" => $item->DIQA1,
                                "QA2" => $item->DIQA2,
                                "QA3" => $item->DIQA3,
                                "QA4" => $item->DIQA4,
                                "QA5" => $item->DIQA5,
                                "QA6" => $item->DIQA6,
                                "QA7" => $item->DIQA7,
                                "QA8" => $item->DIQA8,
                                "QA9" => $item->DIQA9
                            ]
                        ]
                    ];
                })->values()->all();
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'B';
                });
                $B_BNR["Family_Care_Banner"] = $fltr_promo_bnr->map(function ($item) {
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

                //SECTION-U #### WHY CHOOSE US?
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'U';
                });
                $U["Why_Choose_Us"] = $fltr_dash->map(function ($item) {
                    return [
                        "ID" => $item->ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESC,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                    ];
                })->values()->all();

                //SECTION-V #### WHY MAKES US SPECIAL
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'V';
                });
                $V["Why_Makes_Us_Special"] = $fltr_dash->map(function ($item) {
                    return [
                        "ID" => $item->ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESC,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                    ];
                })->values()->all();

                //SECTION-W #### SPECIAL SERVICES
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'W';
                });
                $W["Special_Services"] = $fltr_dash->map(function ($item) {
                    return [
                        "ID" => $item->ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESC,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                    ];
                })->values()->all();

                //SECTION-#### SINGLE TEST
                // $TST_DTL = DB::table('master_test')
                // ->join(DB::raw('(SELECT DISTINCT clinic_testdata.TEST_ID,clinic_testdata.HOME_COLLECT, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID,HOME_COLLECT) as clinic_testdata'), function ($join) {
                //     $join->on('master_test.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                // })
                // ->select('master_test.*', 'clinic_testdata.MIN_COST', 'clinic_testdata.HOME_COLLECT')
                // ->take(100)->get()->toArray();

                $TST_DTL = DB::table('master_test')
                    ->join(DB::raw('(SELECT DISTINCT clinic_testdata.TEST_ID, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID) as clinic_testdata'), function ($join) {
                        $join->on('master_test.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                    })
                    ->join('test_sub_dept', 'master_test.SUB_DEPT_ID', '=', 'test_sub_dept.SUB_DEPT_ID')
                    ->select(
                        'master_test.TEST_ID',
                        'master_test.TEST_SL',
                        'master_test.DEPT_ID',
                        'master_test.SUB_DEPT_ID',
                        'master_test.TEST_CODE',
                        'master_test.TEST_NAME',
                        'master_test.KNOWN_AS',
                        'master_test.TEST_DESC',
                        'master_test.FASTING',
                        'master_test.GENDER_TYPE',
                        'master_test.AGE_TYPE',
                        'master_test.PRESCRIPTION',
                        'master_test.ID_PROOF',
                        'master_test.TSTATUS',
                        'master_test.ORGAN_ID',
                        'master_test.SAMPLE_ID',
                        'master_test.SAMPLE_NAME',
                        'master_test.METHOD_ID',
                        'master_test.TEST_TYPE',
                        'master_test.TIMG1',
                        'master_test.TIMG2',
                        'master_test.TIMG3',
                        'master_test.TIMG4',
                        'master_test.TIMG5',
                        'master_test.TIMG6',
                        'master_test.TIMG7',
                        'master_test.TIMG8',
                        'master_test.TIMG9',
                        'master_test.TIMG10',
                        // 'master_test.TBNR1',
                        // 'master_test.TBNR2',
                        // 'master_test.TBNR3',
                        // 'master_test.TBNR4',
                        // 'master_test.TBNR5',
                        // 'master_test.TBNR6',
                        // 'master_test.TBNR7',
                        // 'master_test.TBNR8',
                        // 'master_test.TBNR9',
                        // 'master_test.TBNR10',
                        'master_test.TQA1',
                        'master_test.TQA2',
                        'master_test.TQA3',
                        'master_test.TQA4',
                        'master_test.TQA5',
                        'master_test.TQA6',
                        'clinic_testdata.MIN_COST',
                        // 'clinic_testdata.HOME_COLLECT',
                        'test_sub_dept.SDBNR1 as BANNER_URL'
                    )
                    ->orderByRaw('CASE WHEN master_test.TSSL3 IS NULL THEN 1 ELSE 0 END, master_test.TSSL3 ASC')
                    ->take(100)
                    ->get()
                    ->toArray();

                // Transform the result to rename fields and nest QAs
                $transformed_TST_DTL = array_map(function ($item) {
                    $homecol = "---";
                    $newItem = [
                        "TEST_ID" => $item->TEST_ID,
                        "TEST_SL" => $item->TEST_SL,
                        "DEPT_ID" => $item->DEPT_ID,
                        "SUB_DEPT_ID" => $item->SUB_DEPT_ID,
                        "TEST_CODE" => $item->TEST_CODE,
                        "TEST_NAME" => $item->TEST_NAME,
                        "TEST_SAMPLE" => $item->SAMPLE_NAME,
                        "KNOWN_AS" => $item->KNOWN_AS,
                        "TEST_DESC" => $item->TEST_DESC,
                        "FASTING" => $item->FASTING,
                        "GENDER_TYPE" => $item->GENDER_TYPE,
                        "AGE_TYPE" => $item->AGE_TYPE,
                        "PRESCRIPTION" => $item->PRESCRIPTION,
                        "ID_PROOF" => $item->ID_PROOF,
                        "TSTATUS" => $item->TSTATUS,
                        "ORGAN_ID" => $item->ORGAN_ID,
                        "SPECIMEN_ID" => $item->SAMPLE_ID,
                        "METHOD_ID" => $item->METHOD_ID,
                        "TEST_TYPE" => $item->TEST_TYPE,
                        "MIN_COST" => $item->MIN_COST,
                        "HOME_COLLECT" => $homecol,
                        "BANNER_URL" => $item->BANNER_URL,
                    ];

                    // Map TIMG fields to PHOTO_URL
                    for ($i = 1; $i <= 10; $i++) {
                        $newItem["PHOTO_URL{$i}"] = $item->{"TIMG{$i}"};
                    }

                    // // Map TBNR fields to BANNER_URL
                    // for ($i = 1; $i <= 10; $i++) {
                    //     $newItem["BANNER_URL{$i}"] = $item->{"TBNR{$i}"};
                    // }

                    // Map TQA fields to Questions
                    $newQues = [];
                    for ($i = 1; $i <= 6; $i++) {
                        $newQues["QA{$i}"] = $item->{"TQA{$i}"};
                    }
                    $newItem["Questions"][] = $newQues;

                    return $newItem;
                }, $TST_DTL);

                // Convert the transformed result to an array
                $TST_DTL = json_decode(json_encode($transformed_TST_DTL), true);

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
                $TST["Popular_Single_Test"] = array_values($TST_DTL + $STB);

                //SECTION-AB #### HOW IT WORK
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'AB';
                });
                $AB["How_It_Work"] = $fltr_dash->map(function ($item) {
                    return [
                        "ID" => $item->ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DS_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESC,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                    ];
                })->values()->all();

                $cldata1 = DB::table('pharmacy')
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
                        'pharmacy.CBNR_URL1',
                        'pharmacy.LATITUDE',
                        'pharmacy.LONGITUDE',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
     * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
      * SIN(RADIANS('$latt'))))),2) as KM")
                    )
                    ->where(['pharmacy.PHARMA_ID' => 6, 'STATUS' => 'Active'])
                    ->get();

                $data1 = DB::table('dashboard_section')
                    ->join('dashboard_item', 'dashboard_item.DASH_SECTION_ID', '=', 'dashboard_section.DASH_SECTION_ID')
                    // ->leftJoin('test_sub_dept', 'dashboard_item.DASH_NAME', '=', 'test_sub_dept.SUB_DEPT_NAME')
                    ->join('master_test', function ($join) {
                        $join->on('dashboard_item.SUB_DEPT_ID', '=', 'master_test.SUB_DEPT_ID');
                    })
                    ->leftJoin('test_scanorgan', 'master_test.ORGAN_ID', '=', 'test_scanorgan.ORGAN_ID')
                    ->select(
                        'dashboard_section.DASH_SECTION_NAME',
                        'dashboard_item.ID',
                        'dashboard_item.DASH_SECTION_ID',
                        'dashboard_item.DASH_NAME',
                        'dashboard_item.DASH_DESC',
                        'dashboard_item.DASH_POSITION',
                        'dashboard_item.DASH_STATUS',
                        'dashboard_item.DI_IMG1',
                        'dashboard_item.DI_IMG2',
                        'dashboard_item.DI_IMG3',
                        'dashboard_item.DI_IMG4',
                        'dashboard_item.DI_IMG5',
                        'dashboard_item.DI_IMG6',
                        'dashboard_item.DI_IMG7',
                        'dashboard_item.DI_IMG8',
                        'dashboard_item.DI_IMG9',
                        'dashboard_item.DI_IMG10',
                        'dashboard_item.DI_BNR1',
                        'dashboard_item.DI_BNR2',
                        'dashboard_item.DI_BNR3',
                        'dashboard_item.DI_BNR4',
                        'dashboard_item.DI_BNR5',
                        'dashboard_item.DI_BNR6',
                        'dashboard_item.DI_BNR7',
                        'dashboard_item.DI_BNR8',
                        'dashboard_item.DI_BNR9',
                        'dashboard_item.DI_BNR10',
                        'dashboard_item.DIQA1',
                        'dashboard_item.DIQA2',
                        'dashboard_item.DIQA3',
                        'dashboard_item.DIQA4',
                        'dashboard_item.DIQA5',
                        'dashboard_item.DIQA6',
                        'dashboard_item.DIQA7',
                        'dashboard_item.DIQA8',
                        'dashboard_item.DIQA9',
                        'test_scanorgan.ORGAN_ID',
                        'test_scanorgan.ORGAN_NAME',
                        'test_scanorgan.OIMG1 as ORGAN_URL',
                        'master_test.DEPT_ID',
                        'master_test.SUB_DEPT_ID',
                        // 'test_sub_dept.SUB_DEPT_NAME'
                    )
                    ->where('master_test.DEPT_ID', '=', 'D1')
                    ->whereIn('dashboard_item.ID', [54, 53, 51, 50])
                    ->where('dashboard_item.DASH_SECTION_ID', '=', 'F')
                    ->orderBy('dashboard_item.ID', 'desc')
                    ->get();

                // Initialize an array to hold the final structure
                $F1_DTL = [];
                foreach ($data1 as $item) {
                    $dashID = $item->ID;
                    if (!isset($F1_DTL[$dashID])) {
                        $F1_DTL[$dashID] = [
                            "ID" => $dashID,
                            "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                            "DASH_NAME" => $item->DASH_NAME,
                            "DESCRIPTION" => $item->DASH_DESC,
                            "PHOTO_URL1" => $item->DI_IMG1,
                            "PHOTO_URL2" => $item->DI_IMG2,
                            "PHOTO_URL3" => $item->DI_IMG3,
                            "PHOTO_URL4" => $item->DI_IMG4,
                            "PHOTO_URL5" => $item->DI_IMG5,
                            "PHOTO_URL6" => $item->DI_IMG6,
                            "PHOTO_URL7" => $item->DI_IMG7,
                            "PHOTO_URL8" => $item->DI_IMG8,
                            "PHOTO_URL9" => $item->DI_IMG9,
                            "PHOTO_URL10" => $item->DI_IMG10,
                            "BANNER_URL1" => $item->DI_BNR1,
                            "BANNER_URL2" => $item->DI_BNR2,
                            "BANNER_URL3" => $item->DI_BNR3,
                            "BANNER_URL4" => $item->DI_BNR4,
                            "BANNER_URL5" => $item->DI_BNR5,
                            "BANNER_URL6" => $item->DI_BNR6,
                            "BANNER_URL7" => $item->DI_BNR7,
                            "BANNER_URL8" => $item->DI_BNR8,
                            "BANNER_URL9" => $item->DI_BNR9,
                            "BANNER_URL10" => $item->DI_BNR10,
                            "ORGANS" => []
                        ];
                    }

                    // Check if the organ is already in the list
                    $organExists = false;
                    foreach ($F1_DTL[$dashID]['ORGANS'] as $organ) {
                        if ($organ['ORGAN_ID'] == $item->ORGAN_ID) {
                            $organExists = true;
                            break;
                        }
                    }

                    // Add the organ if it doesn't exist
                    if (!$organExists) {
                        $F1_DTL[$dashID]['ORGANS'][] = [
                            "ORGAN_ID" => $item->ORGAN_ID,
                            "ORGAN_NAME" => $item->ORGAN_NAME,
                            "ORGAN_URL" => $item->ORGAN_URL,
                        ];
                    }
                }
                $F11 = array_values($F1_DTL);

                $groupedData = [];
                foreach ($cldata1 as $data) {
                    $fltr_dash = $dash->filter(function ($item) {
                        $validIds = [2, 210, 211, 228];
                        return in_array($item->ID, $validIds);
                    });

                    $groupedDataItem = [
                        'PHARMA_ID' => $data->PHARMA_ID,
                        'PHARMA_NAME' => $data->PHARMA_NAME,
                        'CLINIC_TYPE' => $data->CLINIC_TYPE,
                        'ADDRESS' => $data->ADDRESS,
                        'CITY' => $data->CITY,
                        'DIST' => $data->DIST,
                        'STATE' => $data->STATE,
                        'PIN' => $data->PIN,
                        'CLINIC_MOBILE' => $data->CLINIC_MOBILE,
                        'PHOTO_URL' => $data->PHOTO_URL,
                        'BANNER_URL' => $data->CBNR_URL1,
                        'LOGO_URL' => $data->LOGO_URL,
                        'LATITUDE' => $data->LATITUDE,
                        'LONGITUDE' => $data->LONGITUDE,
                        'KM' => $data->KM,
                        'DETAILS' => $fltr_dash->map(function ($item) {
                            return [
                                "ID" => $item->ID,
                                "DASH_NAME" => $item->DASH_NAME,
                                "DASH_SECTION_ID" => $item->FACILITY_ID,
                                "DASH_SECTION_NAME" => $item->DASH_NAME,
                                "DESCRIPTION" => $item->DASH_DESC,
                                "PHOTO_URL1" => $item->DI_IMG1,
                                "PHOTO_URL2" => $item->DI_IMG2,
                                "PHOTO_URL3" => $item->DI_IMG3,
                                "PHOTO_URL4" => $item->DI_IMG4,
                                "PHOTO_URL5" => $item->DI_IMG5,
                                "PHOTO_URL6" => $item->DI_IMG6,
                                "PHOTO_URL7" => $item->DI_IMG7,
                                "PHOTO_URL8" => $item->DI_IMG8,
                                "PHOTO_URL9" => $item->DI_IMG9,
                                "PHOTO_URL10" => $item->DI_IMG10,
                                "BANNER_URL1" => $item->DI_BNR1,
                                "BANNER_URL2" => $item->DI_BNR2,
                                "BANNER_URL3" => $item->DI_BNR3,
                                "BANNER_URL4" => $item->DI_BNR4,
                                "BANNER_URL5" => $item->DI_BNR5,
                                "BANNER_URL6" => $item->DI_BNR6,
                                "BANNER_URL7" => $item->DI_BNR7,
                                "BANNER_URL8" => $item->DI_BNR8,
                                "BANNER_URL9" => $item->DI_BNR9,
                                "BANNER_URL10" => $item->DI_BNR10,
                                "Questions" => [
                                    "QA1" => $item->DIQA1,
                                    "QA2" => $item->DIQA2,
                                    "QA3" => $item->DIQA3,
                                    "QA4" => $item->DIQA4,
                                    "QA5" => $item->DIQA5,
                                    "QA6" => $item->DIQA6,
                                    "QA7" => $item->DIQA7,
                                    "QA8" => $item->DIQA8,
                                    "QA9" => $item->DIQA9
                                ]
                            ];
                        })->values()->all(),
                        "details" => $F11
                    ];

                    $groupedData[] = $groupedDataItem;
                }

                $AV["Client1"] = array_values($groupedData);

                // $data = $A + $F + $F1 + $DASH_BNR + $S + $T + $DIAG + $C + $G + $H + $B + $U + $V + $W + $TST + $AB + $AV;
                $data = $A + $F + $F1 + $DASH_BNR + $S  + $DIAG + $C + $G + $H + $B + $U + $V + $W + $TST + $AB + $AV;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
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
                    ->where(['PHARMA_ID' => $sid, 'STATUS' => 'Active'])->first();

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
                $dash = DB::table('dashboard_section')
                    ->join('dashboard_item', 'dashboard_section.DASH_SECTION_ID', 'dashboard_item.DASH_SECTION_ID')
                    // ->leftjoin('dis_catg','dashboard_section.DASH_SECTION_ID','dashboard_item.DASH_SECTION_ID')
                    // ->where('dashboard_section.DS_TAGGED', 'like', '%' . 'M' . '%')
                    ->where('dashboard_section.DS_STATUS', 'Active')
                    ->orderby('dashboard_section.DS_POSITION')
                    ->orderby('dashboard_item.DASH_POSITION')
                    ->get();

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
                    ->join('test_sub_dept', 'clinic_testdata.SUB_DEPT_ID', '=', 'test_sub_dept.SUB_DEPT_ID')
                    ->select(
                        'TEST_ID',
                        'TEST_NAME',
                        'TEST_CATG',
                        'clinic_testdata.DEPT_ID',
                        'clinic_testdata.SUB_DEPT_ID',
                        'DISCOUNT',
                        'HOME_COLLECT',
                        'TEST_SAMPLE',
                        'ORGAN_ID',
                        'ORGAN_NAME',
                        'ORGAN_URL',
                        'TEST_DESC',
                        'DEPARTMENT as CATEGORY',
                        'COST',
                        'KNOWN_AS',
                        'FASTING',
                        'GENDER_TYPE',
                        'AGE_TYPE',
                        'REPORT_TIME',
                        'PRESCRIPTION',
                        'ID_PROOF',
                        'test_sub_dept.SDBNR1 as BANNER_URL',
                        'QA1',
                        'QA2',
                        'QA3',
                        'QA4',
                        'QA5',
                        'QA6'
                    )
                    ->where(['PHARMA_ID' => $pid])
                    ->orderBy('TEST_SL')
                    ->take(100)
                    ->get()
                    ->map(function ($item) {
                        $item = (array) $item; // Convert stdClass object to array
                        $item['Questions'][] = [
                            'QA1' => $item['QA1'],
                            'QA2' => $item['QA2'],
                            'QA3' => $item['QA3'],
                            'QA4' => $item['QA4'],
                            'QA5' => $item['QA5'],
                            'QA6' => $item['QA6'],
                        ];
                        unset($item['QA1'], $item['QA2'], $item['QA3'], $item['QA4'], $item['QA5'], $item['QA6']);
                        return $item;
                    })
                    ->toArray();
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
                    return $item->DASH_SECTION_ID === 'A' && in_array($item->ID, [208, 209, 210, 211, 212, 213]);
                });

                $DASH_Z["Dashboard"] = $fltr_dash->map(function ($item) {
                    return [
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_SECTION_ID" => $item->FACILITY_ID,
                        "DASH_SECTION_NAME" => $item->DASH_NAME,
                        "DESCRIPTION" => $item->DASH_DESC,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                        "BANNER_URL1" => $item->DI_BNR1,
                        "BANNER_URL2" => $item->DI_BNR2,
                        "BANNER_URL3" => $item->DI_BNR3,
                        "BANNER_URL4" => $item->DI_BNR4,
                        "BANNER_URL5" => $item->DI_BNR5,
                        "BANNER_URL6" => $item->DI_BNR6,
                        "BANNER_URL7" => $item->DI_BNR7,
                        "BANNER_URL8" => $item->DI_BNR8,
                        "BANNER_URL9" => $item->DI_BNR9,
                        "BANNER_URL10" => $item->DI_BNR10,
                        "Questions" => [
                            [
                                "QA1" => $item->DIQA1,
                                "QA2" => $item->DIQA2,
                                "QA3" => $item->DIQA3,
                                "QA4" => $item->DIQA4,
                                "QA5" => $item->DIQA5,
                                "QA6" => $item->DIQA6,
                                "QA7" => $item->DIQA7,
                                "QA8" => $item->DIQA8,
                                "QA9" => $item->DIQA9
                            ]
                        ]
                    ];
                })->values()->all();

                // SECTION-S #### SYMPTOMATIC TEST
                $data1 = DB::table('dashboard_item')
                    ->join('sym_organ_test', function ($join) {
                        $join->on('sym_organ_test.DASH_ID', '=', 'dashboard_item.ID')
                            ->where([
                                'dashboard_item.DASH_SECTION_ID' => 'S',
                                'dashboard_item.DASH_STATUS' => 'Active'
                            ]);
                    })
                    ->join('clinic_testdata', function ($join) use ($pid) {
                        $join->on('sym_organ_test.TEST_ID', '=', 'clinic_testdata.TEST_ID')
                            ->where('clinic_testdata.PHARMA_ID', $pid);
                    })
                    ->join('test_sub_dept', 'clinic_testdata.SUB_DEPT_ID', '=', 'test_sub_dept.SUB_DEPT_ID')
                    ->select(
                        'dashboard_item.ID',
                        'dashboard_item.DASH_NAME',
                        'dashboard_item.DASH_DESC',
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
                        'clinic_testdata.DEPT_ID',
                        'clinic_testdata.SUB_DEPT_ID',
                        'clinic_testdata.TEST_NAME',
                        'clinic_testdata.TEST_CATG',
                        'clinic_testdata.DISCOUNT',
                        'clinic_testdata.HOME_COLLECT',
                        'clinic_testdata.ORGAN_ID',
                        'clinic_testdata.ORGAN_NAME',
                        'clinic_testdata.TEST_SAMPLE',
                        'clinic_testdata.ORGAN_URL',
                        'clinic_testdata.TEST_DESC',
                        'clinic_testdata.DEPARTMENT',
                        'clinic_testdata.COST',
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
                        'test_sub_dept.SDBNR1 as BANNER_URL'
                    )
                    ->orderby('dashboard_item.DASH_POSITION')
                    ->get();


                $S_DTL = [];
                $collection = collect($data1);
                $distinctValues = $collection->pluck('ID')->unique();
                foreach ($distinctValues as $row) {
                    $fltr_arr = $data1->filter(function ($item) use ($row) {
                        return $item->ID === $row;
                    });


                    $T_DTL = $fltr_arr->map(function ($item) {
                        $homecol = "---";
                        return [
                            "TEST_ID" => $item->TEST_ID,
                            // "TEST_SL" => $item->TEST_SL,
                            "TEST_NAME" => $item->TEST_NAME,
                            // "TEST_CODE" => $item->TEST_CODE,
                            "TEST_SAMPLE" => $item->TEST_SAMPLE,
                            "HOME_COLLECT" => $item->HOME_COLLECT,
                            "TEST_CATG" => $item->TEST_CATG,
                            "DEPT_ID" => $item->DEPT_ID,
                            "SUB_DEPT_ID" => $item->SUB_DEPT_ID,
                            "COST" => $item->COST,
                            "DISCOUNT" => $item->DISCOUNT,
                            "ORGAN_ID" => $item->ORGAN_ID,
                            "ORGAN_NAME" => $item->ORGAN_NAME,
                            "CATEGORY" => $item->DEPARTMENT,
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
                            "BANNER_URL" => $item->BANNER_URL,
                            "Questions" => [
                                [
                                    "QA1" => $item->QA1,
                                    "QA2" => $item->QA2,
                                    "QA3" => $item->QA3,
                                    "QA4" => $item->QA4,
                                    "QA5" => $item->QA5,
                                    "QA6" => $item->QA6,
                                ]
                            ]

                        ];
                    })->values()->all();
                    $S_DTL[] = [
                        "ID" => $row,
                        "DASH_NAME" => $fltr_arr->first()->DASH_NAME,
                        "DESCRIPTION" => $fltr_arr->first()->DASH_DESC,
                        "PHOTO_URL1" => $fltr_arr->first()->PHOTO_URL1,
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


                // RETURN $S_DTL;
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'SM';
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
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
                        "DEPT_ID" => $item->TEST_DEPT_ID,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                        "BANNER_URL1" => $item->DI_BNR1,
                        "BANNER_URL2" => $item->DI_BNR2,
                        "BANNER_URL3" => $item->DI_BNR3,
                        "BANNER_URL4" => $item->DI_BNR4,
                        "BANNER_URL5" => $item->DI_BNR5,
                        "BANNER_URL6" => $item->DI_BNR6,
                        "BANNER_URL7" => $item->DI_BNR7,
                        "BANNER_URL8" => $item->DI_BNR8,
                        "BANNER_URL9" => $item->DI_BNR9,
                        "BANNER_URL10" => $item->DI_BNR10,
                        "Questions" => [
                            "QA1" => $item->DIQA1,
                            "QA2" => $item->DIQA2,
                            "QA3" => $item->DIQA3,
                            "QA4" => $item->DIQA4,
                            "QA5" => $item->DIQA5,
                            "QA6" => $item->DIQA6,
                            "QA7" => $item->DIQA7,
                            "QA8" => $item->DIQA8,
                            "QA9" => $item->DIQA9
                        ]
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
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
                        "DEPT_ID" => $item->TEST_DEPT_ID,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "PHOTO_URL1" => $item->DI_IMG1,
                        "PHOTO_URL2" => $item->DI_IMG2,
                        "PHOTO_URL3" => $item->DI_IMG3,
                        "PHOTO_URL4" => $item->DI_IMG4,
                        "PHOTO_URL5" => $item->DI_IMG5,
                        "PHOTO_URL6" => $item->DI_IMG6,
                        "PHOTO_URL7" => $item->DI_IMG7,
                        "PHOTO_URL8" => $item->DI_IMG8,
                        "PHOTO_URL9" => $item->DI_IMG9,
                        "PHOTO_URL10" => $item->DI_IMG10,
                        "BANNER_URL1" => $item->DI_BNR1,
                        "BANNER_URL2" => $item->DI_BNR2,
                        "BANNER_URL3" => $item->DI_BNR3,
                        "BANNER_URL4" => $item->DI_BNR4,
                        "BANNER_URL5" => $item->DI_BNR5,
                        "BANNER_URL6" => $item->DI_BNR6,
                        "BANNER_URL7" => $item->DI_BNR7,
                        "BANNER_URL8" => $item->DI_BNR8,
                        "BANNER_URL9" => $item->DI_BNR9,
                        "BANNER_URL10" => $item->DI_BNR10,
                        "Questions" => [
                            "QA1" => $item->DIQA1,
                            "QA2" => $item->DIQA2,
                            "QA3" => $item->DIQA3,
                            "QA4" => $item->DIQA4,
                            "QA5" => $item->DIQA5,
                            "QA6" => $item->DIQA6,
                            "QA7" => $item->DIQA7,
                            "QA8" => $item->DIQA8,
                            "QA9" => $item->DIQA9
                        ]
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
                        "ID" => $item->ID,
                        "DIS_ID" => $item->DIS_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DS_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESC,
                        "PHOTO_URL" => $item->DI_IMG1,
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

    // function drdashboard(Request $request)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $input = $request->json()->all();

    //         if (isset($input['LATITUDE']) && isset($input['LONGITUDE']) && isset($input['DR_ID'])) {
    //             $latitude = $input['LATITUDE'];
    //             $longitude = $input['LONGITUDE'];
    //             $doctorId = $input['DR_ID'];

    //             date_default_timezone_set('Asia/Kolkata');
    //             $date = Carbon::now();
    //             $weekNumber = $date->weekOfMonth;
    //             $dayOfWeek = date('l');
    //             $currentDay = date('d');
    //             $currentDate = date('Ymd');

    //             $banners = DB::table('promo_banner')
    //                 ->select('DASH_SECTION_ID', 'PROMO_ID', 'PROMO_NAME', 'PROMO_URL', 'PROMO_TYPE', 'MOBILE_NO', 'DESCRIPTION', 'STATUS')
    //                 ->where('DASH_SECTION_ID', 'PB')
    //                 ->orWhere('PHARMA_ID', '0')
    //                 ->get();

    //             $distinctDoctors = DB::table('dr_availablity')
    //                 ->select(
    //                     'PHARMA_ID',
    //                     'ID as SCH_ID',
    //                     'CHK_IN_TIME',
    //                     'CHK_OUT_TIME',
    //                     'MAX_BOOK',
    //                     'CHEMBER_NO',
    //                     'CHK_IN_STATUS',
    //                     'DR_FEES',
    //                     DB::raw("'" . Carbon::now()->format('Ymd') . "' as SCH_DT"),

    //                 )
    //                 ->distinct()

    //                 ->where(['DR_ID' => $doctorId, 'SCH_DAY' => $dayOfWeek]);

    //             $availabilityData  = DB::table('pharmacy')
    //                 ->joinSub($distinctDoctors, 'distinct_doctors', function ($join) {
    //                     $join->on('pharmacy.PHARMA_ID', '=', 'distinct_doctors.PHARMA_ID');
    //                 })
    //                 ->select(
    //                     'pharmacy.PHARMA_ID',
    //                     'pharmacy.ITEM_NAME',
    //                     'pharmacy.ADDRESS',
    //                     'pharmacy.CITY',
    //                     'pharmacy.PIN',
    //                     'pharmacy.DIST',
    //                     'pharmacy.STATE',
    //                     'pharmacy.LATITUDE',
    //                     'pharmacy.LONGITUDE',
    //                     'pharmacy.PHOTO_URL',
    //                     'pharmacy.LOGO_URL',
    //                     DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) * COS(RADIANS('$latitude')) * COS(RADIANS(pharmacy.Longitude - '$longitude')) + SIN(RADIANS(pharmacy.Latitude)) * SIN(RADIANS('$latitude'))))), 2) as KM"),
    //                     'distinct_doctors.SCH_ID',
    //                     'distinct_doctors.SCH_DT',
    //                     'distinct_doctors.MAX_BOOK',
    //                     'distinct_doctors.CHK_IN_TIME',
    //                     'distinct_doctors.CHK_OUT_TIME',
    //                     'distinct_doctors.CHK_IN_STATUS',
    //                     'distinct_doctors.CHEMBER_NO',
    //                     'distinct_doctors.DR_FEES',
    //                     // 'distinct_doctors.TOT_CLINIC',
    //                 )
    //                 ->get();

    //             $chambers = [];
    //             foreach ($availabilityData as $row) {
    //                 $chamber = [
    //                     "PHARMA_ID" => $row->PHARMA_ID,
    //                     "PHARMA_NAME" => $row->ITEM_NAME,
    //                     "ADDRESS" => $row->ADDRESS,
    //                     "CITY" => $row->CITY,
    //                     "PIN" => $row->PIN,
    //                     "DIST" => $row->DIST,
    //                     "STATE" => $row->STATE,
    //                     "LATITUDE" => $row->LATITUDE,
    //                     "LONGITUDE" => $row->LONGITUDE,
    //                     "PHOTO_URL" => $row->PHOTO_URL,
    //                     "LOGO_URL" => $row->LOGO_URL,
    //                     "KM" => $row->KM,
    //                     "SCH_ID" => $row->SCH_ID,
    //                     "SCH_DT" => $row->SCH_DT,
    //                     "MAX_BOOK" => $row->MAX_BOOK,
    //                     "DR_STATUS" => $row->MAX_BOOK,
    //                     "CHK_IN_TIME" => $row->CHK_IN_TIME,
    //                     "CHK_OUT_TIME" => $row->CHK_OUT_TIME,
    //                     "DR_STATUS" => $row->CHK_IN_STATUS,
    //                     "CHEMBER_NO" => $row->CHEMBER_NO,
    //                     "TOTAL_CHEMBER"=>0,
    //                     "TODAY_BOOKED"=>10,
    //                     "TOTAL_BOOKED" => 0,
    //                     "TOTAL_OT"=>6,
    //                     "TOTAL_IPD_VISIT"=>10,
    //                     "DETAILS" => []
    //                 ];

    //                 // Fetch patient details
    //                 $appointmentData = DB::table('appointment')
    //                     ->join('user_family', 'user_family.ID', '=', 'appointment.PATIENT_ID')
    //                     ->select(
    //                         'appointment.BOOKING_ID',
    //                         'appointment.FAMILY_ID',
    //                         'appointment.PATIENT_ID',
    //                         'appointment.PATIENT_NAME',
    //                         'user_family.DOB',
    //                         'user_family.SEX',
    //                         'user_family.MOBILE',
    //                         'appointment.APPNT_ID',
    //                         'appointment.APPNT_DT',
    //                         'appointment.APPNT_TOKEN',
    //                         'appointment.BOOKING_SL',
    //                         'appointment.STATUS',
    //                         'appointment.APPNT_FROM',
    //                         'appointment.CHEMBER_NO'
    //                     )
    //                     ->where('appointment.APPNT_DT', $row->SCH_DT)
    //                     ->where('appointment.PHARMA_ID', $row->PHARMA_ID)
    //                     ->where('appointment.DR_ID', $doctorId)
    //                     ->orderBy('appointment.BOOKING_SL')
    //                     ->get();

    //                 $groupedAppointments = [];
    //                 foreach ($appointmentData as $appointment) {

    //                     $groupedAppointments[] = [
    //                         "FAMILY_ID" => $appointment->FAMILY_ID,
    //                         "PATIENT_ID" => $appointment->PATIENT_ID,
    //                         "PATIENT_NAME" => $appointment->PATIENT_NAME,
    //                         "MOBILE" => $appointment->MOBILE,
    //                         "AGE" => $appointment->DOB, // Assuming DOB is age, but it might be better to calculate age
    //                         "SEX" => $appointment->SEX,
    //                         "BOOKING_ID" => $appointment->BOOKING_ID,
    //                         "APPNT_TOKEN" => $appointment->APPNT_TOKEN,
    //                         "APPNT_ID" => $appointment->APPNT_ID,
    //                         "APPNT_DT" => $appointment->APPNT_DT,
    //                         "APPNT_FROM" => $appointment->APPNT_FROM,
    //                         "STATUS" => $appointment->STATUS,
    //                         "BOOKING_SL" => $appointment->BOOKING_SL
    //                     ];
    //                 }
    //                 $chamber['TOTAL_CHEMBER'] = count($availabilityData);
    //                 $chamber['TOTAL_BOOKED'] = count($groupedAppointments);
    //                 $chamber['DETAILS'] = array_values($groupedAppointments);
    //                 $chambers[] = $chamber;
    //             }

    //             //SECTION-A #### DR_Details
    //             $A['Doctor'] = DB::table('drprofile')->where(['DR_ID' => $doctorId])->get();

    //             //SECTION-B ####
    //             $B['Dashboard'] = DB::table('dr_dashboard_details')->where(['STATUS' => 'Active'])->get();

    //             $response = [
    //                 'Success' => true,
    //                 'data' => [
    //                     'Doctor' => $A['Doctor'],
    //                     'Dashboard' => $B['Dashboard'],
    //                     'Today_Chember' => $chambers,
    //                     'Promo_Banner' => $banners->filter(fn ($item) => $item->DASH_SECTION_ID === 'PB')->values()->all()
    //                 ],
    //                 'code' => 200
    //             ];
    //         } else {
    //             $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
    //     }

    //     return response()->json($response);
    // }

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

                $response = [
                    'Success' => true,
                    'data' => [
                        'Doctor' => $A['Doctor'],
                        'Dashboard' => $B['Dashboard'],
                        'Today_Chember' => $chambers,
                        'Promo_Banner' => $banners->filter(fn($item) => $item->DASH_SECTION_ID === 'PB')->values()->all(),
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
}
