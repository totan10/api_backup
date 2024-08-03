<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;
class EasyHealths_User_Controller extends Controller
{
    function apphome(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $promo_bnr = DB::table('promo_banner')->where('STATUS', 'Active')->get();
                $dash = DB::table('dashboard_section')
                    ->join('dashboard_item', 'dashboard_section.DASH_SECTION_ID', 'dashboard_item.DASH_SECTION_ID')
                    ->where('dashboard_section.DS_TAGGED', 'like', '%' . 'M' . '%')
                    ->where('dashboard_section.DS_STATUS', 'Active')
                    // ->orderby('dashboard_section.DS_POSITION')
                    ->orderby('dashboard_section.DSSL1')
                    // ->where('dashboard_section.DSSL1','>',0)
                    // ->orderby('dashboard_item.DASH_POSITION')
                    ->orderby('dashboard_item.DISL1')
                    ->get();
                $pharma = DB::table('pharmacy')
                    ->leftjoin('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
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
                        DB::raw('COUNT(distinct dr_availablity.DR_ID) as TOT_DR'),
                        // DB::raw('COUNT(distinct clinic_testdata.TEST_ID) as TOT_TEST'),
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                 * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                  * SIN(RADIANS('$latt'))))),2) as KM")
                    )
                    ->where(['pharmacy.STATUS' => 'Active'])
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
                        'pharmacy.LONGITUDE'
                    )
                    ->orderby('KM')->take(25)->get();

                //SECTION-A #### SLIDER
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'MA';
                });
                $A["Slider"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "SLIDER_ID" => $item->PROMO_ID,
                        "SLIDER_NAME" => $item->PROMO_NAME,
                        "SLIDER_URL" => $item->PROMO_URL,
                    ];
                })->values()->all();

                //SECTION-DASH_A #### DASHBOARD
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'A' && stripos($item->DASH_ITEM_TAGGED, 'M') !== false;
                });
                $DASH_A["Dashboard"] = $fltr_dash->map(function ($item) {
                    return [
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_SECTION_ID" => $item->FACILITY_ID,
                        "DASH_SECTION_NAME" => $item->DASH_NAME,
                        "DESCRIPTION" => $item->DASH_DESC,
                        "PHOTO_URL" => $item->DI_IMG1,
                        "BANNER_URL" => $item->DI_BNR1,
                    ];
                })->values()->all();

                //SECTION-#### SPECIALIST
                $SPLST_DTL = DB::table('dis_catg')
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
                        'DISQA9'
                    )
                    ->take(7)->orderBy('DIS_SL')->get()->map(function ($item) {
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
                                [
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
                            ]
                        ];
                    })->values()->all();

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
                $SPLST["Specialist"] = array_values($SPLST_DTL);

                //SECTION-#### SYMPTOMS               

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
                    ];
                })->take(3)->values()->all();
                $SYM["Symptoms"] = array_values($SYM_DTL + $SMB);


                //SECTION-NEAR BY POLYCLINIC
                $fltr_pharma = $pharma->filter(function ($item) {
                    return $item->CLINIC_TYPE === 'Clinic';
                });
                $CLINIC_DTL = $fltr_pharma->values()->take(25)->all();

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'CL';
                });
                $CL_BNR["Clinic_Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                $CLINIC["Clinics"] = array_values($CLINIC_DTL + $CL_BNR);

                //SECTION-E #### EXPERT CARE FOR WOMEN
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'E';
                });
                $E_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
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
                    return $item->DASH_SECTION_ID === 'E';
                });
                $E_BNR["Expert_Care_Women_Banner"] = $fltr_promo_bnr->map(function ($item) {
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

                $E["Women_Health_Care"] = array_values($E_DTL + $E_BNR);

                //SECTION-D #### EXPERT CARE CHILD (0 TO 12)
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'D';
                });
                $D_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
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
                    return $item->DASH_SECTION_ID === 'D';
                });
                $D_BNR["Expert_Care_Child_Banner"] = $fltr_promo_bnr->map(function ($item) {
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

                $D["Expert_Care_Child"] = array_values($D_DTL + $D_BNR);

                //SECTION-M #### AMBULANCE SERVICE
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'M';
                });
                $M["Ambulance_Service"] = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
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

                //SECTION-N #### LOOKING FOR HEALTH TEST
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'N';
                });
                $N["Health_Test"] = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
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

                //SECTION-O #### CONSULT FROM HOME
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'O';
                });
                $O["Consult_From_Home"] = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
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

                //SECTION-P #### FITNESS TRACKER
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'P';
                });
                $P["Fitness_Tracker"] = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
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

                //SECTION-Q #### UNITING EXPART FOR YOUR HEALTH
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'Q';
                });
                $Q["Uniting_Expert"] = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
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

                //SECTION-#### SURGERY

                $surg = DB::table('facility')
                    ->join('facility_type', 'facility.DASH_TYPE_ID', '=', 'facility_type.DASH_TYPE_ID')
                    // ->where('facility.DN_TAG_SECTION', 'like', '%' . 'SR' . '%')
                    ->join('facility_section', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->where('facility_section.DASH_SECTION_ID', 'like', '%' . 'SR' . '%')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    // ->where('facility.DNSL1', '>', 0)
                    ->orderByRaw('CASE WHEN facility.DNSL1 IS NULL THEN 1 ELSE 0 END, facility.DNSL1 ASC')
                    // ->orderBy('facility_type.DT_POSITION')
                    // ->orderBy('facility.DN_POSITION')
                    ->select(
                        'facility.DASH_ID',
                        'facility.DN_POSITION',
                        'facility.DASH_NAME',
                        'facility_type.DASH_TYPE',
                        'facility.DASH_TYPE_ID',
                        'facility.DN_DESCRIPTION',
                        'facility_section.DASH_SECTION_ID',
                        // 'facility.URL_SURGERY_MI',
                        'facility.DN_BANNER_URL',
                        'facility.DNQA1',
                        'facility.DNQA2',
                        'facility.DNQA3',
                        'facility.DNQA4',
                        'facility.DNQA5',
                        'facility.DNQA6',
                        'facility.DNQA7',
                        'facility.DNQA8',
                        'facility.DNQA9',

                        'facility.DNIMG1',
                        'facility.DNIMG2',
                        'facility.DNIMG3',
                        'facility.DNIMG4',
                        'facility.DNIMG5',
                        'facility.DNIMG6',
                        'facility.DNIMG7',
                        'facility.DNIMG8',
                        'facility.DNIMG9',
                        'facility.DNIMG10',

                        'facility.DNBNR1',
                        'facility.DNBNR2',
                        'facility.DNBNR3',
                        'facility.DNBNR4',
                        'facility.DNBNR5',
                        'facility.DNBNR6',
                        'facility.DNBNR7',
                        'facility.DNBNR8',
                        'facility.DNBNR9',
                        'facility.DNBNR10',
                    )
                    // ->take(10)
                    ->get();

                $SURG_DTL = $surg->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DASH_SL" => $item->DN_POSITION,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        // "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_TYPE_ID" => $item->DASH_TYPE_ID,
                        "DESCRIPTION" => $item->DN_DESCRIPTION,
                        // "PHOTO_URL" => $item->URL_SURGERY_MI,
                        // "BANNER_URL" => $item->DN_BANNER_URL,
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
                $SURG["Surgery"] = array_values($SURG_DTL + $SGB);

                //SECTION-R #### HEALTH ZONE
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'R';
                });
                $R["Health_Zone"] = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DIS_ID" => $item->DIS_ID,
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

                //SECTION-#### NEAR BY DIAGNOSTIC
                $fltr_pharma = $pharma->filter(function ($item) {
                    return $item->CLINIC_TYPE === 'Diagnostic';
                });
                $DDTL = $fltr_pharma->values()->take(25)->all();
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

                $DIAG["Diagnostic"] = array_values($DDTL + $DIAG_BNR);

                //SECTION-#### SINGLE TEST
                // $TST_DTL = DB::table('master_test')
                //     ->join(DB::raw('(SELECT DISTINCT clinic_testdata.TEST_ID,clinic_testdata.HOME_COLLECT, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID,HOME_COLLECT) as clinic_testdata'), function ($join) {
                //         $join->on('master_test.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                //     })
                //     ->select('master_test.*', 'clinic_testdata.MIN_COST', 'clinic_testdata.HOME_COLLECT')
                //     ->take(100)->get()->toArray();

                $TST_DTL = DB::table('master_test')
                    ->join(DB::raw('(SELECT DISTINCT clinic_testdata.TEST_ID,  MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID) as clinic_testdata'), function ($join) {
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
                        'master_test.TBNR1',
                        'master_test.TBNR2',
                        'master_test.TBNR3',
                        'master_test.TBNR4',
                        'master_test.TBNR5',
                        'master_test.TBNR6',
                        'master_test.TBNR7',
                        'master_test.TBNR8',
                        'master_test.TBNR9',
                        'master_test.TBNR10',
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
                $transformed_TST_DTL = json_decode(json_encode($transformed_TST_DTL), true);

                // return 
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
                $TST["Top_Single_Test"] = array_values($transformed_TST_DTL + $STB);

                //SECTION-B #### FAMILY CARE PACKAGE
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
                $B_BNR["Expert_Care_Women_Banner"] = $fltr_promo_bnr->map(function ($item) {
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

                // //SECTION-F #### SCAN
                // $data1 = DB::table('dashboard_section')
                //     ->join('dashboard_item', 'dashboard_section.DASH_SECTION_ID', '=', 'dashboard_item.DASH_SECTION_ID')
                //     ->join('master_testdata', 'dashboard_item.DASH_NAME', '=', 'master_testdata.TEST_CATG')
                //     ->select(
                //         'master_testdata.*',
                //         // 'dashboard.DASH_ID',
                //         // 'dashboard.DIS_ID',
                //         'dashboard_section.DASH_SECTION_ID',
                //         'dashboard_section.DASH_SECTION_NAME',
                //         'dashboard_item.*',


                //     )
                //     ->where('dashboard_section.DASH_SECTION_ID', '=', 'F')
                //     ->orderby('dashboard_item.DASH_POSITION')
                //     ->get();

                // // return $data1;

                // $F_DTL = [];
                // foreach ($data1->pluck('DASH_ID')->unique() as $catg) {
                //     $filteredArray = $data1->where('DASH_ID', $catg);
                //     $organDetails = [];
                //     foreach ($filteredArray as $item) {
                //         $organID = $item->ORGAN_ID;
                //         if (!isset($organDetails[$organID])) {
                //             $organDetails[$organID] = [
                //                 "ORGAN_ID" => $organID,
                //                 "ORGAN_NAME" => $item->ORGAN_NAME,
                //                 "ORGAN_URL" => $item->ORGAN_URL,
                //             ];
                //         }
                //     }
                //     $F_DTL[] = [
                //         "DASH_SECTION_NAME" => $filteredArray->first()->DASH_SECTION_NAME,
                //         "DASH_ID" => $filteredArray->first()->DASH_ID,
                //         "DASH_NAME" => $filteredArray->first()->DASH_NAME,

                //         "PHOTO_URL1" => $filteredArray->first()->DI_IMG1,
                //         "PHOTO_URL2" => $filteredArray->first()->DI_IMG2,
                //         "PHOTO_URL3" => $filteredArray->first()->DI_IMG3,
                //         "PHOTO_URL4" => $filteredArray->first()->DI_IMG4,
                //         "PHOTO_URL5" => $filteredArray->first()->DI_IMG5,
                //         "PHOTO_URL6" => $filteredArray->first()->DI_IMG6,
                //         "PHOTO_URL7" => $filteredArray->first()->DI_IMG7,
                //         "PHOTO_URL8" => $filteredArray->first()->DI_IMG8,
                //         "PHOTO_URL9" => $filteredArray->first()->DI_IMG9,
                //         "PHOTO_URL10" => $filteredArray->first()->DI_IMG10,

                //         "BANNER_URL1" => $filteredArray->first()->DI_BNR1,
                //         "BANNER_URL2" => $filteredArray->first()->DI_BNR2,
                //         "BANNER_URL3" => $filteredArray->first()->DI_BNR3,
                //         "BANNER_URL4" => $filteredArray->first()->DI_BNR4,
                //         "BANNER_URL5" => $filteredArray->first()->DI_BNR5,
                //         "BANNER_URL6" => $filteredArray->first()->DI_BNR6,
                //         "BANNER_URL7" => $filteredArray->first()->DI_BNR7,
                //         "BANNER_URL8" => $filteredArray->first()->DI_BNR8,
                //         "BANNER_URL9" => $filteredArray->first()->DI_BNR9,
                //         "BANNER_URL10" => $filteredArray->first()->DI_BNR10,
                //         "Questions" => [
                //             "QA1" => $item->DIQA1,
                //             "QA2" => $item->DIQA2,
                //             "QA3" => $item->DIQA3,
                //             "QA4" => $item->DIQA4,
                //             "QA5" => $item->DIQA5,
                //             "QA6" => $item->DIQA6,
                //             "QA7" => $item->DIQA7,
                //             "QA8" => $item->DIQA8,
                //             "QA9" => $item->DIQA9
                //         ],
                //         "ORGANS" => array_values($organDetails),
                //     ];
                // }

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
                        'dashboard_item.SUB_DEPT_ID',
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
                    ->where('dashboard_item.DASH_SECTION_ID', '=', 'F')
                    // ->where('dashboard_item.DISL1','>',0)
                    ->orderByRaw('CASE WHEN dashboard_item.DISL1 = NULL THEN 1 ELSE 0 END, dashboard_item.DISL1')
                    // ->orderBy('dashboard_item.DISL1')

                    ->get();

                // Initialize an array to hold the final structure
                $F_DTL = [];
                foreach ($data1 as $item) {
                    $dashID = $item->ID;
                    if (!isset($F_DTL[$dashID])) {
                        $F_DTL[$dashID] = [
                            "ID" => $dashID,
                            "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                            "DASH_NAME" => $item->DASH_NAME,
                            "DESCRIPTION" => $item->DASH_DESC,
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
                    foreach ($F_DTL[$dashID]['ORGANS'] as $organ) {
                        if ($organ['ORGAN_ID'] == $item->ORGAN_ID) {
                            $organExists = true;
                            break;
                        }
                    }

                    // Add the organ if it doesn't exist
                    if (!$organExists) {
                        $F_DTL[$dashID]['ORGANS'][] = [
                            "ORGAN_ID" => $item->ORGAN_ID,
                            "ORGAN_NAME" => $item->ORGAN_NAME,
                            "ORGAN_URL" => $item->ORGAN_URL,
                        ];
                    }
                }

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'F';
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
                    ];
                })->values()->all();
                $F["Popular_Scan"] = array_values($F_DTL + $F_BNR);

                // return $F;

                //SECTION-#### DASHBOARD BANNER
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'MD';
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

                // //SECTION-#### NEAR BY HOSPITAL/NURSHING HOME
                $fltr_pharma = $pharma->filter(function ($item) {
                    return $item->CLINIC_TYPE === 'Hospital';
                });
                $HDTL = $fltr_pharma->values()->take(25)->all();
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'HS';
                });
                $HOSP_BNR["Hospital_Banner"] = $fltr_promo_bnr->map(function ($item) {
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

                $HOSP["Hospital"] = array_values($HDTL + $HOSP_BNR);

                //SECTION-I #### DERMATOLOGY
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'I';
                });
                $I_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "DIS_ID" => $item->DIS_ID,
                        "SYM_ID" => $item->SYM_ID,
                        "PHOTO_URL" => $item->DSIMG1,
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
                        "QA1" => $item->DIQA1,
                        "QA2" => $item->DIQA2,
                        "QA3" => $item->DIQA3,
                        "QA4" => $item->DIQA4,
                        "QA5" => $item->DIQA5,
                        "QA6" => $item->DIQA6,
                        "QA7" => $item->DIQA7,
                        "QA8" => $item->DIQA8,
                        "QA9" => $item->DIQA9

                    ];
                })->values()->all();
                $groupedData = [];
                foreach ($I_DTL as $row2) {
                    if (!isset($groupedData[$row2['DASH_SECTION_ID']])) {
                        $groupedData[$row2['DASH_SECTION_ID']] = [
                            "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
                            "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
                            "PHOTO_URL1" => $row2['PHOTO_URL'],
                            "DETAILS" => []
                        ];
                    }
                    $groupedData[$row2['DASH_SECTION_ID']]['DETAILS'][] = [
                        "ID" => $row2['ID'],
                        "DASH_NAME" => $row2['DASH_NAME'],
                        "DIS_ID" => $row2['DIS_ID'],
                        "SYM_ID" => $row2['SYM_ID'],
                        "DESCRIPTION" => $row2['DESCRIPTION'],

                        "PHOTO_URL1" => $row2['PHOTO_URL1'],
                        "PHOTO_URL2" => $row2['PHOTO_URL2'],
                        "PHOTO_URL3" => $row2['PHOTO_URL3'],
                        "PHOTO_URL4" => $row2['PHOTO_URL4'],
                        "PHOTO_URL5" => $row2['PHOTO_URL5'],
                        "PHOTO_URL6" => $row2['PHOTO_URL6'],
                        "PHOTO_URL7" => $row2['PHOTO_URL7'],
                        "PHOTO_URL8" => $row2['PHOTO_URL8'],
                        "PHOTO_URL9" => $row2['PHOTO_URL9'],
                        "PHOTO_URL10" => $row2['PHOTO_URL10'],
                        "BANNER_URL1" => $row2['BANNER_URL1'],
                        "BANNER_URL2" => $row2['BANNER_URL2'],
                        "BANNER_URL3" => $row2['BANNER_URL3'],
                        "BANNER_URL4" => $row2['BANNER_URL4'],
                        "BANNER_URL5" => $row2['BANNER_URL5'],
                        "BANNER_URL6" => $row2['BANNER_URL6'],
                        "BANNER_URL7" => $row2['BANNER_URL7'],
                        "BANNER_URL8" => $row2['BANNER_URL8'],
                        "BANNER_URL9" => $row2['BANNER_URL9'],
                        "BANNER_URL10" => $row2['BANNER_URL10'],
                        "Questions" => [
                            [
                                "QA1" => $row2['QA1'],
                                "QA2" => $row2['QA2'],
                                "QA3" => $row2['QA3'],
                                "QA4" => $row2['QA4'],
                                "QA5" => $row2['QA5'],
                                "QA6" => $row2['QA6'],
                                "QA7" => $row2['QA7'],
                                "QA8" => $row2['QA8'],
                                "QA9" => $row2['QA9']
                            ]
                        ]

                    ];
                }
                $I["Dermatology"] = array_values($groupedData);

                //SECTION-J #### DENTAL
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'J';
                });
                $J_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "DIS_ID" => $item->DIS_ID,
                        "SYM_ID" => $item->SYM_ID,
                        "PHOTO_URL" => $item->DSIMG1,
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
                        "QA1" => $item->DIQA1,
                        "QA2" => $item->DIQA2,
                        "QA3" => $item->DIQA3,
                        "QA4" => $item->DIQA4,
                        "QA5" => $item->DIQA5,
                        "QA6" => $item->DIQA6,
                        "QA7" => $item->DIQA7,
                        "QA8" => $item->DIQA8,
                        "QA9" => $item->DIQA9

                    ];
                })->values()->all();
                $groupedData = [];
                foreach ($J_DTL as $row2) {
                    if (!isset($groupedData[$row2['DASH_SECTION_ID']])) {
                        $groupedData[$row2['DASH_SECTION_ID']] = [
                            "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
                            "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
                            "PHOTO_URL1" => $row2['PHOTO_URL'],
                            "DETAILS" => []
                        ];
                    }
                    $groupedData[$row2['DASH_SECTION_ID']]['DETAILS'][] = [
                        "ID" => $row2['ID'],
                        "DASH_NAME" => $row2['DASH_NAME'],
                        "DIS_ID" => $row2['DIS_ID'],
                        "SYM_ID" => $row2['SYM_ID'],
                        "DESCRIPTION" => $row2['DESCRIPTION'],

                        "PHOTO_URL1" => $row2['PHOTO_URL1'],
                        "PHOTO_URL2" => $row2['PHOTO_URL2'],
                        "PHOTO_URL3" => $row2['PHOTO_URL3'],
                        "PHOTO_URL4" => $row2['PHOTO_URL4'],
                        "PHOTO_URL5" => $row2['PHOTO_URL5'],
                        "PHOTO_URL6" => $row2['PHOTO_URL6'],
                        "PHOTO_URL7" => $row2['PHOTO_URL7'],
                        "PHOTO_URL8" => $row2['PHOTO_URL8'],
                        "PHOTO_URL9" => $row2['PHOTO_URL9'],
                        "PHOTO_URL10" => $row2['PHOTO_URL10'],
                        "BANNER_URL1" => $row2['BANNER_URL1'],
                        "BANNER_URL2" => $row2['BANNER_URL2'],
                        "BANNER_URL3" => $row2['BANNER_URL3'],
                        "BANNER_URL4" => $row2['BANNER_URL4'],
                        "BANNER_URL5" => $row2['BANNER_URL5'],
                        "BANNER_URL6" => $row2['BANNER_URL6'],
                        "BANNER_URL7" => $row2['BANNER_URL7'],
                        "BANNER_URL8" => $row2['BANNER_URL8'],
                        "BANNER_URL9" => $row2['BANNER_URL9'],
                        "BANNER_URL10" => $row2['BANNER_URL10'],
                        "Questions" => [
                            [
                                "QA1" => $row2['QA1'],
                                "QA2" => $row2['QA2'],
                                "QA3" => $row2['QA3'],
                                "QA4" => $row2['QA4'],
                                "QA5" => $row2['QA5'],
                                "QA6" => $row2['QA6'],
                                "QA7" => $row2['QA7'],
                                "QA8" => $row2['QA8'],
                                "QA9" => $row2['QA9']
                            ]
                        ]

                    ];
                }
                $J["Dental"] = array_values($groupedData);

                //SECTION-K #### EYE CARE
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'K';
                });
                $K_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "DIS_ID" => $item->DIS_ID,
                        "SYM_ID" => $item->SYM_ID,
                        "PHOTO_URL" => $item->DSIMG1,
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
                        "QA1" => $item->DIQA1,
                        "QA2" => $item->DIQA2,
                        "QA3" => $item->DIQA3,
                        "QA4" => $item->DIQA4,
                        "QA5" => $item->DIQA5,
                        "QA6" => $item->DIQA6,
                        "QA7" => $item->DIQA7,
                        "QA8" => $item->DIQA8,
                        "QA9" => $item->DIQA9

                    ];
                })->values()->all();
                $groupedData = [];
                foreach ($K_DTL as $row2) {
                    if (!isset($groupedData[$row2['DASH_SECTION_ID']])) {
                        $groupedData[$row2['DASH_SECTION_ID']] = [
                            "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
                            "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
                            "PHOTO_URL1" => $row2['PHOTO_URL'],
                            "DETAILS" => []
                        ];
                    }
                    $groupedData[$row2['DASH_SECTION_ID']]['DETAILS'][] = [
                        "ID" => $row2['ID'],
                        "DASH_NAME" => $row2['DASH_NAME'],
                        "DIS_ID" => $row2['DIS_ID'],
                        "SYM_ID" => $row2['SYM_ID'],
                        "DESCRIPTION" => $row2['DESCRIPTION'],

                        "PHOTO_URL1" => $row2['PHOTO_URL1'],
                        "PHOTO_URL2" => $row2['PHOTO_URL2'],
                        "PHOTO_URL3" => $row2['PHOTO_URL3'],
                        "PHOTO_URL4" => $row2['PHOTO_URL4'],
                        "PHOTO_URL5" => $row2['PHOTO_URL5'],
                        "PHOTO_URL6" => $row2['PHOTO_URL6'],
                        "PHOTO_URL7" => $row2['PHOTO_URL7'],
                        "PHOTO_URL8" => $row2['PHOTO_URL8'],
                        "PHOTO_URL9" => $row2['PHOTO_URL9'],
                        "PHOTO_URL10" => $row2['PHOTO_URL10'],
                        "BANNER_URL1" => $row2['BANNER_URL1'],
                        "BANNER_URL2" => $row2['BANNER_URL2'],
                        "BANNER_URL3" => $row2['BANNER_URL3'],
                        "BANNER_URL4" => $row2['BANNER_URL4'],
                        "BANNER_URL5" => $row2['BANNER_URL5'],
                        "BANNER_URL6" => $row2['BANNER_URL6'],
                        "BANNER_URL7" => $row2['BANNER_URL7'],
                        "BANNER_URL8" => $row2['BANNER_URL8'],
                        "BANNER_URL9" => $row2['BANNER_URL9'],
                        "BANNER_URL10" => $row2['BANNER_URL10'],
                        "Questions" => [
                            [
                                "QA1" => $row2['QA1'],
                                "QA2" => $row2['QA2'],
                                "QA3" => $row2['QA3'],
                                "QA4" => $row2['QA4'],
                                "QA5" => $row2['QA5'],
                                "QA6" => $row2['QA6'],
                                "QA7" => $row2['QA7'],
                                "QA8" => $row2['QA8'],
                                "QA9" => $row2['QA9']
                            ]
                        ]

                    ];
                }
                $K["Eye_Care"] = array_values($groupedData);

                //SECTION-L #### BONE AND JOINT CARE
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'L';
                });
                $L_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "ID" => $item->ID,
                        "DASH_NAME" => $item->DASH_NAME,
                        // "DASH_TYPE" => $item->DASH_TYPE,
                        "DESCRIPTION" => $item->DS_DESCRIPTION,
                        "DIS_ID" => $item->DIS_ID,
                        "SYM_ID" => $item->SYM_ID,
                        "PHOTO_URL" => $item->DSIMG1,
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
                        "QA1" => $item->DIQA1,
                        "QA2" => $item->DIQA2,
                        "QA3" => $item->DIQA3,
                        "QA4" => $item->DIQA4,
                        "QA5" => $item->DIQA5,
                        "QA6" => $item->DIQA6,
                        "QA7" => $item->DIQA7,
                        "QA8" => $item->DIQA8,
                        "QA9" => $item->DIQA9

                    ];
                })->values()->all();
                $groupedData = [];
                foreach ($L_DTL as $row2) {
                    if (!isset($groupedData[$row2['DASH_SECTION_ID']])) {
                        $groupedData[$row2['DASH_SECTION_ID']] = [
                            "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
                            "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
                            "PHOTO_URL1" => $row2['PHOTO_URL'],
                            "DETAILS" => []
                        ];
                    }
                    $groupedData[$row2['DASH_SECTION_ID']]['DETAILS'][] = [
                        "ID" => $row2['ID'],
                        "DASH_NAME" => $row2['DASH_NAME'],
                        "DIS_ID" => $row2['DIS_ID'],
                        "SYM_ID" => $row2['SYM_ID'],
                        "DESCRIPTION" => $row2['DESCRIPTION'],

                        "PHOTO_URL1" => $row2['PHOTO_URL1'],
                        "PHOTO_URL2" => $row2['PHOTO_URL2'],
                        "PHOTO_URL3" => $row2['PHOTO_URL3'],
                        "PHOTO_URL4" => $row2['PHOTO_URL4'],
                        "PHOTO_URL5" => $row2['PHOTO_URL5'],
                        "PHOTO_URL6" => $row2['PHOTO_URL6'],
                        "PHOTO_URL7" => $row2['PHOTO_URL7'],
                        "PHOTO_URL8" => $row2['PHOTO_URL8'],
                        "PHOTO_URL9" => $row2['PHOTO_URL9'],
                        "PHOTO_URL10" => $row2['PHOTO_URL10'],
                        "BANNER_URL1" => $row2['BANNER_URL1'],
                        "BANNER_URL2" => $row2['BANNER_URL2'],
                        "BANNER_URL3" => $row2['BANNER_URL3'],
                        "BANNER_URL4" => $row2['BANNER_URL4'],
                        "BANNER_URL5" => $row2['BANNER_URL5'],
                        "BANNER_URL6" => $row2['BANNER_URL6'],
                        "BANNER_URL7" => $row2['BANNER_URL7'],
                        "BANNER_URL8" => $row2['BANNER_URL8'],
                        "BANNER_URL9" => $row2['BANNER_URL9'],
                        "BANNER_URL10" => $row2['BANNER_URL10'],
                        "Questions" => [
                            [
                                "QA1" => $row2['QA1'],
                                "QA2" => $row2['QA2'],
                                "QA3" => $row2['QA3'],
                                "QA4" => $row2['QA4'],
                                "QA5" => $row2['QA5'],
                                "QA6" => $row2['QA6'],
                                "QA7" => $row2['QA7'],
                                "QA8" => $row2['QA8'],
                                "QA9" => $row2['QA9']
                            ]
                        ]

                    ];
                }
                $L["Bone_Joint_Care"] = array_values($groupedData);

                //SECTION-AJ #### 2nd Opinion

                $I_DTL = DB::table('facility_type')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->where(['facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->where('facility_type.DT_TAG_SECTION', 'like', '%AM%')
                    ->where('facility_type.DTSL1', '>', 0)
                    // ->orderBy('facility_type.DT_POSITION')
                    ->orderBy('facility_type.DTSL1')
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
                            "Questions" =>[[
                                "QA1" => $ds_dtl->DSQA1,
                                "QA2" => $ds_dtl->DSQA2,
                                "QA3" => $ds_dtl->DSQA3,
                                "QA4" => $ds_dtl->DSQA4,
                                "QA5" => $ds_dtl->DSQA5,
                                "QA6" => $ds_dtl->DSQA6,
                                "QA7" => $ds_dtl->DSQA7,
                                "QA8" => $ds_dtl->DSQA8,
                                "QA9" => $ds_dtl->DSQA9
                            ]],
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
                            // "Questions" =>[[
                            //     "QA1" => $row2->DTQA1,
                            //     "QA2" => $row2->DTQA2,
                            //     "QA3" => $row2->DTQA3,
                            //     "QA4" => $row2->DTQA4,
                            //     "QA5" => $row2->DTQA5,
                            //     "QA6" => $row2->DTQA6,
                            //     "QA7" => $row2->DTQA7,
                            //     "QA8" => $row2->DTQA8,
                            //     "QA9" => $row2->DTQA9
                            // ]],
                            "FACILITY_DETAILS" => []
                        ];
                    }

                    $groupedData[$ds_dtl->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE]['FACILITY_DETAILS'][] = [
                        "DASH_ID" => $row2->DASH_ID,
                        // "DIS_ID" => $row2->DIS_ID,
                        // "SYM_ID" => $row2->SYM_ID,
                        "DASH_NAME" => $row2->DASH_NAME,
                        "DASH_TYPE" => $row2->DASH_TYPE,
                        "DASH_SECTION_ID" => $ds_dtl->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $ds_dtl->DASH_SECTION_NAME,
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
                        "Questions" =>[[
                                "QA1" => $row2->DNQA1,
                                "QA2" => $row2->DNQA2,
                                "QA3" => $row2->DNQA3,
                                "QA4" => $row2->DNQA4,
                                "QA5" => $row2->DNQA5,
                                "QA6" => $row2->DNQA6,
                                "QA7" => $row2->DNQA7,
                                "QA8" => $row2->DNQA8,
                                "QA9" => $row2->DNQA9
                            ]],

                    ];
                }


                $AJ["Second_Opinion"] = array_values($groupedData);


                //SECTION-AQ #### IPD Section

                $I_DTL = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->where('facility_section.DASH_SECTION_ID', 'AH')
                    ->orderByRaw('CASE WHEN facility_type.DTSL6 IS NULL THEN 1 ELSE 0 END, facility_type.DTSL6 ASC')
                    ->orderBy('facility_type.DTSL6')
                    ->get();


                $groupedData = [];
                foreach ($I_DTL as $row2) {
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
                        "Questions" =>[[
                            "QA1" => $row2->DNQA1,
                            "QA2" => $row2->DNQA2,
                            "QA3" => $row2->DNQA3,
                            "QA4" => $row2->DNQA4,
                            "QA5" => $row2->DNQA5,
                            "QA6" => $row2->DNQA6,
                            "QA7" => $row2->DNQA7,
                            "QA8" => $row2->DNQA8,
                            "QA9" => $row2->DNQA9
                        ]],
                    ];
                }

                $AH["IPD_Facilities"] = array_values($groupedData);

                // ***** CLIENT-1

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
                        'pharmacy.LOGO_URL',
                        'pharmacy.CBNR_URL1',
                        'pharmacy.LATITUDE',
                        'pharmacy.LONGITUDE',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
         * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
          * SIN(RADIANS('$latt'))))),2) as KM")
                    )
                    ->where('pharmacy.PHARMA_ID', '=', 1)
                    ->get();

                $dash = DB::table('dashboard_section')
                    ->join('dashboard_item', 'dashboard_section.DASH_SECTION_ID', 'dashboard_item.DASH_SECTION_ID')
                    // ->leftjoin('dis_catg','dashboard_section.DASH_SECTION_ID','dashboard_item.DASH_SECTION_ID')
                    // ->where('dashboard_section.DS_TAGGED', 'like', '%' . 'M' . '%')
                    ->where(['dashboard_section.DS_STATUS' => 'Active', 'dashboard_item.DASH_SECTION_ID' => 'A'])
                    // ->orderby('dashboard_section.DS_POSITION')
                    // ->orderby('dashboard_item.DASH_POSITION')
                    ->orderby('dashboard_section.DSSL1')
                    ->orderby('dashboard_item.DISL1')
                    ->get();

                $I_DTL = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->where([
                        'facility_section.DS_STATUS' => 'Active',
                        'facility_type.DT_STATUS' => 'Active',
                        'facility.DN_STATUS' => 'Active'
                    ])
                    ->whereIn('facility.DASH_ID', [6, 23, 13])
                    // ->orderBy('facility_type.DT_POSITION')
                    ->orderBy('facility_type.DTSL1')
                    ->get()
                    ->map(function ($row2) {
                        return [
                            "ID" => $row2->ID,
                            "DASH_NAME" => $row2->DASH_NAME,
                            "DASH_TYPE" => $row2->DASH_TYPE,
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
                    });


                $groupedData = [];
                foreach ($cldata as $data) {
                    $fltr_dash = $dash->filter(function ($item) {
                        $validIds = [2, 5, 233, 234, 237];
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
                        "details" => $I_DTL
                    ];

                    $groupedData[] = $groupedDataItem;
                }

                $AD["Client1"] = array_values($groupedData);

                //*** CLIENT-2

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
                    ->where('pharmacy.PHARMA_ID', '=', 6)
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
                    ->orderBy('dashboard_item.DISL1')
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
                $F1 = array_values($F1_DTL);

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
                        "details" => $F1
                    ];

                    $groupedData[] = $groupedDataItem;
                }

                $AV["Client2"] = array_values($groupedData);


                $data = $A + $DASH_A + $SPLST + $SYM + $CLINIC + $E + $D + $M + $N + $O + $P + $Q + $SURG + $R + $DIAG + $TST + $B + $C + $F + $DASH_BNR + $HOSP + $I + $J + $K + $L + $AJ + $AH + $AD + $AV;

                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    function vucldoctrs(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->json()->all();

            $pharmaId = $input['PHARMA_ID'];
            $data = collect();

            $promo_bnr = DB::table('promo_banner')
                    // ->where(['PHARMA_ID' => $pharmaId, 'STATUS' => 'Active'])
                    ->whereIn('DASH_SECTION_ID', ['CL', 'SP'])
                    ->get();

                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'CL' && $item->PROMO_TYPE==='Slider';
                });
                $bnr["slider"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "SLIDER_ID" => $item->PROMO_ID,
                        "SLIDER_NAME" => $item->PROMO_NAME,
                        "SLIDER_URL" => $item->PROMO_URL,
                    ];
                })->values()->all();
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
            $data = $data->merge($this->getCatgDrDt($pharmaId));
            $data = $data->merge($bnr);
            $data = $data->merge($D_bnr);

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

    // private function geAllDrDt($pid)
    // {
    //     $totdr = DB::table('drprofile')
    //         ->join('dr_availablity', 'drprofile.DR_ID', '=', 'dr_availablity.DR_ID')
    //         ->join('pharmacy', 'dr_availablity.PHARMA_ID', '=', 'pharmacy.PHARMA_ID')
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
    //             'dr_availablity.DR_FEES'
    //         )
    //         ->distinct('DR_ID')
    //         ->where(['dr_availablity.PHARMA_ID' => $pid])
    //         ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
    //         ->where('drprofile.APPROVE', 'true')
    //         ->get();
    //     $DRSCH = ['Doctors' => []];

    //     foreach ($totdr as $row1) {
    //         $dravail = DB::table('dr_availablity')
    //             ->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pid])
    //             ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
    //             ->orderby('dr_availablity.CHK_OUT_TIME')->get();
    //         $totapp = DB::table('appointment')->where(['DR_ID' => $row1->DR_ID])->get();
    //         $data = [];

    //         $chk = array("CHK_IN_TIME", "CHK_IN_TIME1", "CHK_IN_TIME2", "CHK_IN_TIME3");
    //         $chkout = array("CHK_OUT_TIME", "CHK_OUT_TIME1", "CHK_OUT_TIME2", "CHK_OUT_TIME3");
    //         $CHKINSTATUS = array("CHK_IN_STATUS", "CHK_IN_STATUS1", "CHK_IN_STATUS2", "CHK_IN_STATUS3");
    //         $delay = array("DR_DELAY", "DR_DELAY1", "DR_DELAY2", "DR_DELAY3");

    //         foreach ($dravail as $row) {
    //             if (is_numeric($row->SCH_DAY)) {
    //                 $currentYear = date("Y");
    //                 $startDate = new DateTime("{$currentYear}-$row->START_MONTH-$row->SCH_DAY");

    //                 for ($i = 0; $i < 12; $i++) {
    //                     $dates = [];
    //                     $dates = $startDate->format('Ymd');
    //                     $schday = $startDate->format('l');
    //                     $cym = date('Ymd');
    //                     $dr_status =null;
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
    //                                 $dr_status = $row->CHK_IN_STATUS3 ?? $row->CHK_IN_STATUS2 ?? $row->CHK_IN_STATUS1 ?? $row->CHK_IN_STATUS;
    //                             }
    //                         } else {
    //                             $dr_status = $row->CHK_IN_STATUS3 ?? $row->CHK_IN_STATUS2 ?? $row->CHK_IN_STATUS1 ?? $row->CHK_IN_STATUS;
    //                         }

    //                         $data[] = [
    //                             "SCH_DT" => $dates,
    //                             "DR_STATUS" => $dr_status,
    //                             "ABS_FDT" => $row->ABS_FDT,
    //                             "ABS_TDT" => $row->ABS_TDT,
    //                             "DR_ARRIVE" => $row->DR_ARRIVE,
    //                             "FROM" => $row->CHK_IN_TIME,
    //                             "CHK_IN_TIME" => $row->CHK_IN_TIME,
    //                             "CHK_IN_TIME1" => $row->CHK_IN_TIME1,
    //                             "CHK_IN_TIME2" => $row->CHK_IN_TIME2,
    //                             "CHK_IN_TIME3" => $row->CHK_IN_TIME3,
    //                             "TO" => $row->CHK_OUT_TIME,
    //                             "TO1" => $row->CHK_OUT_TIME1,
    //                             "TO2" => $row->CHK_OUT_TIME2,
    //                             "TO3" => $row->CHK_OUT_TIME2,
    //                             "CHEMBER_NO" => $row->CHEMBER_NO,
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
    //                             $totalMaxBook = collect([$row->MAX_BOOK, $row->MAX_BOOK1, $row->MAX_BOOK2, $row->MAX_BOOK3])->filter()->sum();
    //                             if ($totalMaxBook - $totappct == 0) {
    //                                 $book_sts = "Closed";
    //                             } else {
    //                                 $book_sts = "Available";
    //                             }
    //                             if ($row->ABS_TDT != null) {
    //                                 if ($row->ABS_TDT < $dates) {
    //                                     $dr_status = "TIMELY";
    //                                 } else {
    //                                     $dr_status = $row->CHK_IN_STATUS3 ?? $row->CHK_IN_STATUS2 ?? $row->CHK_IN_STATUS1 ?? $row->CHK_IN_STATUS;
    //                                 }
    //                             } else {
    //                                 $dr_status = $row->CHK_IN_STATUS3 ?? $row->CHK_IN_STATUS2 ?? $row->CHK_IN_STATUS1 ?? $row->CHK_IN_STATUS;
    //                             }

    //                             $nonNullChkInTimes = array_filter([$row->CHK_IN_TIME, $row->CHK_IN_TIME1, $row->CHK_IN_TIME2, $row->CHK_IN_TIME3], function ($time) {
    //                                 return !empty($time);
    //                             });



    //                             $data[] = [
    //                                 "SCH_DT" => $dates,
    //                                 "DR_STATUS" => $dr_status,
    //                                 "ABS_FDT" => $row->ABS_FDT,
    //                                 "ABS_TDT" => $row->ABS_TDT,
    //                                 "DR_ARRIVE" => $row->DR_ARRIVE,
    //                                 "FROM" => $row->CHK_IN_TIME,
    //                                 "CHK_IN_TIME" => $row->CHK_IN_TIME,
    //                                 "CHK_IN_TIME1" => $row->CHK_IN_TIME1,
    //                                 "CHK_IN_TIME2" => $row->CHK_IN_TIME2,
    //                                 "CHK_IN_TIME3" => $row->CHK_IN_TIME3,
    //                                 "TO" => $row->CHK_OUT_TIME,
    //                                 "TO1" => $row->CHK_OUT_TIME1,
    //                                 "TO2" => $row->CHK_OUT_TIME2,
    //                                 "TO3" => $row->CHK_OUT_TIME3,
    //                                 "CHEMBER_NO" => $row->CHEMBER_NO,
    //                                 "SLOT_STATUS" => $book_sts,
    //                             ];
    //                             $counter++;
    //                         }
    //                     }
    //                     $startDate->addDay();
    //                 }
    //             }
    //         }
    //         log::info($data);
    //         usort($data, function ($item1, $item2) {
    //             return $item1['SCH_DT'] <=> $item2['SCH_DT'];
    //         });
    //         log::info('Sorted data');
    //         log::info($data);
    //         if ($data[0]['SCH_DT'] === $cym) {
    //             $currentTime = Carbon::createFromFormat('h:i A', Carbon::now()->format('h:i A'));

    //             $firstRowTOTime = $data[0]['TO'] ? Carbon::createFromFormat('h:i A', $data[0]['TO']) : null;
    //             $firstRowTOTime1 = $data[0]['TO1'] ? Carbon::createFromFormat('h:i A', $data[0]['TO1']) : null;
    //             $firstRowTOTime2 = $data[0]['TO2'] ? Carbon::createFromFormat('h:i A', $data[0]['TO2']) : null;
    //             $firstRowTOTime3 = $data[0]['TO3'] ? Carbon::createFromFormat('h:i A', $data[0]['TO3']) : null;

           

    //             $allTimesPassed = "true";

    //             if ($firstRowTOTime && $currentTime->lessThanOrEqualTo($firstRowTOTime)) {
    //                 $allTimesPassed = "false";
    //                 $data[0]['FROM']=$data[0]['CHK_IN_TIME'];
    //             }
    //             if ($firstRowTOTime1 && $currentTime->lessThanOrEqualTo($firstRowTOTime1)) {
    //                 $allTimesPassed = "false";
    //                 $data[0]['FROM']=$data[0]['CHK_IN_TIME1'];
    //             }
    //             if ($firstRowTOTime2 && $currentTime->lessThanOrEqualTo($firstRowTOTime2)) {
    //                 $allTimesPassed = "false";
    //                 $data[0]['FROM']=$data[0]['CHK_IN_TIME2'];
    //             }
    //             if ($firstRowTOTime3 && $currentTime->lessThanOrEqualTo($firstRowTOTime3)) {
    //                 $allTimesPassed = "false";
    //                 $data[0]['FROM']=$data[0]['CHK_IN_TIME3'];
    //             }

    //             if ($allTimesPassed === "true") {
    //                 $data[0]['DR_STATUS'] = "OUT";
    //                 $data[0]['SLOT_STATUS'] = "Closed";
    //             }
    //         }

    //         $collection = collect($data);
    //         $firstAvailable = $collection->first(function ($item) {
    //             $dr_status = is_array($item) ? $item['DR_STATUS'] : (is_object($item) ? $item->DR_STATUS : null);
    //             return $dr_status === 'IN' || $dr_status === 'TIMELY' || $dr_status === 'DELAY';
    //         });
            


    //         if ($firstAvailable) {
    //             $firstAvailableIndex = $collection->search($firstAvailable);
    //             $sixRows = array_slice($data, $firstAvailableIndex, 1);
    //         }
    //         log::info('First available data');
    //         log::info($sixRows);
    //         if (!empty($sixRows)) {
    //             $DRSCH['Doctors'][$row1->DR_ID] = [
    //                 "DR_ID" => $row1->DR_ID,
    //                 "DR_NAME" => $row1->DR_NAME,
    //                 "DR_MOBILE" => $row1->DR_MOBILE,
    //                 "DR_FEES" => $row1->DR_FEES,
    //                 "SEX" => $row1->SEX,
    //                 "DESIGNATION" => $row1->DESIGNATION,
    //                 "QUALIFICATION" => $row1->QUALIFICATION,
    //                 "UID_NMC" => $row1->UID_NMC,
    //                 "REGN_NO" => $row1->REGN_NO,
    //                 "D_CATG" => $row1->D_CATG,
    //                 "EXPERIENCE" => $row1->EXPERIENCE,
    //                 "LANGUAGE" => $row1->LANGUAGE,
    //                 "DR_PHOTO" => $row1->PHOTO_URL,
    //                 "AVAILABLE_DT" => $sixRows[0]['SCH_DT'],
    //                 "SLOT_STATUS" => $sixRows[0]['SLOT_STATUS'],
    //                 "DR_STATUS" => $sixRows[0]['DR_STATUS'],
    //                 "FROM" => $sixRows[0]['FROM'],
    //                 // "CHK_IN_TIME" => $sixRows[0]['CHK_IN_TIME'],
    //                 // "CHK_IN_TIME1" => $sixRows[0]['CHK_IN_TIME1'],
    //                 // "CHK_IN_TIME2" => $sixRows[0]['CHK_IN_TIME2'],
    //                 // "CHK_IN_TIME3" => $sixRows[0]['CHK_IN_TIME3'],
    //                 // "DR_ARRIVE" => $sixRows[0]['DR_ARRIVE'],
    //                 // "CHEMBER_NO" => $sixRows[0]['CHEMBER_NO'],
    //             ];
    //         }
    //     }

    //     if (empty($DRSCH['Doctors'])) {
    //         $DRSCH['Doctors'] = [];
    //     }
    //     usort($DRSCH['Doctors'], function ($a, $b) {
    //         $statusOrder = ['IN' => 1, 'TIMELY' => 2, 'DELAY' => 3, 'CANCELLED' => 4, 'OUT' => 5, 'LEAVE' => 6];
    //         if ($a['AVAILABLE_DT'] != $b['AVAILABLE_DT']) {
    //             return $a['AVAILABLE_DT'] <=> $b['AVAILABLE_DT'];
    //         }
    //         if ($statusOrder[$a['DR_STATUS']] != $statusOrder[$b['DR_STATUS']]) {
    //             return $statusOrder[$a['DR_STATUS']] <=> $statusOrder[$b['DR_STATUS']];
    //         }
    //         return $a['FROM'] <=> $b['FROM'];
    //     });
    //     $DRSCH['Doctors'] = array_values($DRSCH['Doctors']);
    //     return $DRSCH;
    // }

    private function getCatgDrDt($pid)
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
                'dr_availablity.DR_FEES',   
            )
            ->distinct('DR_ID')
            ->where(['dr_availablity.PHARMA_ID' => $pid])
            ->where('dr_availablity.SCH_STATUS', '<>', 'NA')
            ->where('drprofile.APPROVE', 'true')
            ->get();
        $DRSCH = ['Doctors' => []];

        foreach ($totdr as $row1) {
            $dravail = DB::table('dr_availablity')
                ->where(['DR_ID' => $row1->DR_ID, 'PHARMA_ID' => $pid])
                ->orderByRaw("FIELD(dr_availablity.CHK_IN_STATUS,'IN','TIMELY','DELAY','CANCELLED','OUT','LEAVE')")
                ->orderby('dr_availablity.CHK_OUT_TIME')->get();
            $totapp = DB::table('appointment')->where(['DR_ID' => $row1->DR_ID])->get();
            $data = [];

            $chk = array("CHK_IN_TIME", "CHK_IN_TIME1", "CHK_IN_TIME2", "CHK_IN_TIME3");
            $chkout = array("CHK_OUT_TIME", "CHK_OUT_TIME1", "CHK_OUT_TIME2", "CHK_OUT_TIME3");
            $CHKINSTATUS = array("CHK_IN_STATUS", "CHK_IN_STATUS1", "CHK_IN_STATUS2", "CHK_IN_STATUS3");
            $delay = array("DR_DELAY", "DR_DELAY1", "DR_DELAY2", "DR_DELAY3");

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

                                $nonNullChkInTimes = array_filter([$row->CHK_IN_TIME, $row->CHK_IN_TIME1, $row->CHK_IN_TIME2, $row->CHK_IN_TIME3], function ($time) {
                                    return !empty($time);
                                });



                                $data[] = [
                                    "SCH_DT" => $dates,
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



}
