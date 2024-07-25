<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    function hndashboard(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $hn_hdr_dtl = DB::table('h_dashboard_details')->where('STATUS', 'Active')->orderby('SECTION_SL')->get();
                $hn_hdr = DB::table('h_dashboard_header')->where('STATUS', 'Active')->get();
                //$dash_dtl = DB::table('dashboard_details')->where('STATUS', 'Active')->get();
                $banner = DB::table('banner')->get();

                //SECTION-A #### SLIDER
                $fltr_arr = $hn_hdr_dtl->filter(function ($item) {
                    return $item->SECTION_ID === 'A';
                });

                $AD["Slider"] = $fltr_arr->map(function ($item) {
                    return [
                        "ID" => $item->ID,
                        "SLIDER_URL" => $item->PHOTO_URL,
                    ];
                })->values()->all();



                //SECTION-B #### DASHBOARD
                $fltr_hdr = $hn_hdr_dtl->filter(function ($item) {
                    return $item->SECTION_ID === 'B';
                });
                $BD["Dashboard"] = $fltr_hdr->map(function ($item) {
                    return [
                        "ID" => $item->ID,
                        "SECTION_SL" => $item->SECTION_SL,
                        "SECTION_NAME" => $item->SECTION_NAME,
                        "ITEM_NAME" => $item->ITEM_NAME,
                        "PHOTO_URL" => $item->PHOTO_URL,
                    ];
                })->values()->all();
                // $BD["Dashboard"] = $fltr_hdr->values()->all();

                //SECTION-C #### HOISPITAL
                $C_DTL = DB::table('pharmacy')
                    ->select(
                        'PHARMA_ID',
                        'ITEM_NAME AS PHARMA_NAME',
                        'CLINIC_TYPE',
                        'ADDRESS',
                        'CITY',
                        'DIST',
                        'STATE',
                        'PIN',
                        'CLINIC_MOBILE',
                        'PHOTO_URL',
                        'LOGO_URL',
                        'LATITUDE',
                        'LONGITUDE',
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                 * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                  * SIN(RADIANS('$latt'))))),2) as KM")
                    )
                    // ->whereRaw("ROUND(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(LATITUDE)) * COS(RADIANS(?)) * COS(RADIANS(LONGITUDE - ?)) + SIN(RADIANS(LATITUDE)) * SIN(RADIANS(?))))), 2) <= 200", [$latt, $lont, $latt])
                    ->where([
                        'CLINIC_TYPE' => 'Hospital',
                        'STATUS' => 'Active'
                    ])
                    ->take(25)
                    ->get()
                    ->toArray();


                $fltr_bnr = $banner->filter(function ($item) {
                    return $item->BANNER_TYPE === 'Package';
                });
                $C_bnr["Banner"] = $fltr_bnr->values()->all();
                $CD["Hospital"] = array_values($C_DTL + $C_bnr);

                //SECTION-D #### SPECIALIST
                $D_DTL = DB::table('disease_catg')
                    ->join('hospital_facilities', 'disease_catg.DIS_CATG', '=', 'hospital_facilities.FACILITY_NAME')
                    ->select(
                        'hospital_facilities.ID',
                        'disease_catg.ITEM_NAME as SPECIALIST',
                        'disease_catg.PHOTO_URL as SPECIALIST_PHOTO',
                        'disease_catg.DESCRIPTION AS SPLST_DESC'
                    )
                    ->where('SERVICE_NAME', 'Speciality/Department')
                    ->orderBy('POSITION')
                    ->get()
                    ->toArray();

                $fltr_bnr = $banner->filter(function ($item) {
                    return $item->BANNER_TYPE === 'Package';
                });
                $D_bnr["Banner"] = $fltr_bnr->values()->all();
                $DD["Specialist"] = array_values($D_DTL + $D_bnr);

                //SECTION-E #### BANNER
                $fltr_bnr = $banner->filter(function ($item) {
                    return $item->BANNER_TYPE === 'IP';
                });
                $ED["Banner_IP"][] = $fltr_bnr->values()->first();



                //SECTION-F #### SURGERIES
                $F_DTL = DB::table('surgeries')
                    ->join('hospital_facilities', 'surgeries.ITEM_NAME', '=', 'hospital_facilities.FACILITY_NAME')
                    ->select(
                        'hospital_facilities.ID',
                        'surgeries.ITEM_NAME AS SURGERY',
                        'surgeries.DIS_ID',
                        'surgeries.DIS_CATG',
                        'surgeries.PHOTO_H_URL AS SURGERY_PHOTO',
                        'surgeries.SURGERY_TYPE'
                    )
                    ->where(['surgeries.SECTION_ID' => 'N', 'surgeries.STATUS' => 'Active', 'hospital_facilities.SERVICE_NAME' => 'Surgeries'])
                    ->orderBy('surgeries.POSITION')
                    ->get()->toArray();

                // return $F_DTL;

                $fltr_bnr = $banner->filter(function ($item) {
                    return $item->BANNER_TYPE === 'Package';
                });
                $F_bnr["Banner"] = $fltr_bnr->values()->all();
                $FD["Surgeries"] = array_values($F_DTL + $F_bnr);

                //SECTION-G #### INSURENCE PARTNER
                $fltr_hdr = $hn_hdr_dtl->filter(function ($item) {
                    return $item->SECTION_ID === 'G';
                });
                $G_DTL = $fltr_hdr->values()->all();
                $fltr_bnr = $banner->filter(function ($item) {
                    return $item->BANNER_TYPE === 'Package';
                });
                $G_bnr["Banner"] = $fltr_bnr->values()->all();
                $GD["Insurence"] = array_values($G_DTL + $G_bnr);

                //SECTION-H #### IPD SECTION
                $fltr_hdr = $hn_hdr_dtl->filter(function ($item) {
                    return $item->SECTION_ID === 'H';
                });
                $H_DTL = $fltr_hdr->values()->all();
                $fltr_bnr = $banner->filter(function ($item) {
                    return $item->BANNER_TYPE === 'Package';
                });
                $H_bnr["Banner"] = $fltr_bnr->values()->all();
                $HD["IPD_Section"] = array_values($H_DTL + $H_bnr);

                //SECTION-I #### 24/7 EMERGANCY
                $fltr_hdr = $hn_hdr_dtl->filter(function ($item) {
                    return $item->SECTION_ID === 'I';
                });
                $I_DTL = $fltr_hdr->values()->all();
                $fltr_bnr = $banner->filter(function ($item) {
                    return $item->BANNER_TYPE === 'Package';
                });
                $I_bnr["Banner"] = $fltr_bnr->values()->all();
                $ID["Emergancy"] = array_values($I_DTL + $I_bnr);


                $data = $AD + $BD + $CD + $DD + $ED + $FD + $GD + $HD + $ID;
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
                $dash1 = DB::table('facility_section')->orderBy('DS_POSITION')->get();

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
                // $fltr_dash = $dash->filter(function ($item) {
                //     return $item->DASH_SECTION_ID === 'AK';
                // });
                // $B["Dashboard"] = $fltr_dash->map(function ($item) {
                //     $description = $item->DASH_SECTION_ID === 'SR' ? $item->DASH_SECTION_DESC_SR : $item->DASH_SECTION_DESC;
                //     return [
                //         "DASH_SECTION_ID" => $item->FACILITY_ID,
                //         "DASH_SECTION_NAME" => $item->DASH_NAME,
                //         "DESCRIPTION" => $description,
                //         "PHOTO_URL" => $item->DSIMG2,
                //         "BANNER_URL" => $item->DSBNR2,
                //     ];
                // })->values()->all();

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
                $E["Banner_IP"] = array_values($E_DTL);

                //SECTION-#### Surgery
                $surg = DB::table('facility')
                    ->join('facility_type', 'facility.DASH_TYPE_ID', '=', 'facility_type.DASH_TYPE_ID')
                    ->join('facility_section', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->where('facility_section.DASH_SECTION_ID', 'like', '%' . 'SR' . '%')
                    ->orderBy('facility.DN_POSITION')
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


                //SECTION-H #### IPD Section
                $data1 = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->where('facility_section.DASH_SECTION_ID', 'AH')
                    ->orderBy('facility_type.DT_POSITION')
                    ->orderBy('facility.DN_POSITION')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row2) {
                    if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
                        $groupedData[$row2->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DESCRIPTION" => $row2->DS_DESCRIPTION,
                            "PHOTO_URL" => $row2->DSM_PHOTO_URL,
                            "BANNER_URL" => $row2->DS_BANNER_URL,
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
                    ->orderBy('facility_type.DT_POSITION')
                    ->orderBy('facility.DN_POSITION')
                    ->get();

                $groupedData = [];
                foreach ($data1 as $row2) {
                    if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
                        $groupedData[$row2->DASH_SECTION_ID] = [
                            "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
                            "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
                            "DESCRIPTION" => $row2->DS_DESCRIPTION,
                            "PHOTO_URL" => $row2->DSM_PHOTO_URL,
                            "BANNER_URL" => $row2->DS_BANNER_URL,

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
                    ->where('STATUS', 'Active')
                    ->where(function ($query) use ($pid) {
                        $query->where('PHARMA_ID', $pid)
                            ->orWhere('PHARMA_ID', 0);
                    })
                    ->orderBy('PHARMA_ID', 'DESC')
                    ->get();

                $dash = DB::table('dashboard')->where('CATEGORY', 'like', '%' . 'D' . '%')->where('STATUS', 'Active')->get();

                //SECTION-A #### SLIDER
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'DA';
                });
                $A["Slider"] = $fltr_promo_bnr->map(function ($item) {
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
                        DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
                 * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
                  * SIN(RADIANS('$latt'))))),2) as KM")
                    )
                    ->where('pharmacy.PHARMA_ID', '=', $pid)
                    ->get();

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
                    ];
                })->values()->all();

                // //SECTION-#### SPECIALIST
                $SPLST_DTL = DB::table('dis_catg')
                    ->join('dr_availablity', function ($join) use ($pid) {
                        $join->on('dr_availablity.DIS_ID', '=', 'dis_catg.DIS_ID')
                            ->where('dr_availablity.PHARMA_ID', '=', $pid);
                    })
                    ->select(
                        'dis_catg.DIS_ID',
                        'dis_catg.DIS_CATEGORY',
                        'dis_catg.SPECIALITY',
                        'dis_catg.SPECIALIST',
                        'dis_catg.DIS_TYPE',
                        'dis_catg.PHOTO1_URL AS PHOTO_URL',
                        DB::raw('COUNT(DISTINCT dr_availablity.DR_ID) as TOT_DR'),
                        DB::raw("CAST(SUM(CASE 
        WHEN dr_availablity.SCH_DAY = '$day1' 
            AND (dr_availablity.WEEK LIKE '%$weekNumber%' 
                OR dr_availablity.SCH_DT = '$cdt') 
            AND dr_availablity.CHK_IN_STATUS IN ('IN', 'TIMELY', 'DELAY') 
        THEN 1 
        ELSE 0 
    END) AS UNSIGNED) as AVAIL_DR")
                    )
                    ->where('dr_availablity.PHARMA_ID', '=', $pid)
                    ->groupBy(
                        'dis_catg.DIS_ID',
                        'dis_catg.DIS_CATEGORY',
                        'dis_catg.SPECIALITY',
                        'dis_catg.SPECIALIST',
                        'dis_catg.DIS_TYPE',
                        'dis_catg.PHOTO1_URL'
                    )
                    ->orderBy('dis_catg.DIS_SL')
                    ->get()
                    ->toArray();

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
                $SPLST["Specialist"] = array_values($SPLST_DTL + $SPB);

                //SECTION-#### SINGLE TEST
                $TST_DTL = DB::table('clinic_testdata')
                    ->select('TEST_ID', 'TEST_NAME', 'TEST_CATG', 'DISCOUNT', 'HOME_COLLECT', 'ORGAN_ID', 'ORGAN_NAME', 'ORGAN_URL', 'TEST_DESC', 'DEPARTMENT as CATEGORY', 'COST', 'KNOWN_AS', 'FASTING', 'GENDER_TYPE', 'AGE_TYPE', 'REPORT_TIME', 'PRESCRIPTION', 'ID_PROOF', 'QA1', 'QA2', 'QA3', 'QA4', 'QA5', 'QA6')
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

                //SECTION-#### SYMPTOMS
                $SYM_DTL = DB::table('symptoms')->select('SYM_ID', 'SYM_NAME', 'DIS_ID', 'DIS_CATEGORY', 'PHOTO_URL', 'DASH_PHOTO')->orderby('SYM_SL')->take(10)->get()->toArray();
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
                        "PHOTO_URL" => $item->PHOTO1_URL,
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
                        "PHOTO_URL" => $item->PHOTO1_URL,
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

                //SECTION-G #### POPULAR HEALTH CHECKUP PACKAGES
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
                        "PHOTO_URL" => $item->PHOTO1_URL,
                    ];
                })->values()->all();
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
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
                    ];
                })->values()->all();
                $G["Popular_Health_Packages"] = array_values($G_DTL + $G_BNR);

                // SECTION-S #### SYMPTOMATIC TEST
                $data1 = DB::table('dashboard')
                    ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard.DASH_ID')
                    ->join('clinic_testdata', 'sym_organ_test.TEST_ID', '=', 'clinic_testdata.TEST_ID')
                    ->select(
                        'clinic_testdata.TEST_ID',
                        'clinic_testdata.TEST_NAME',
                        'clinic_testdata.TEST_CATG',
                        'clinic_testdata.DISCOUNT',
                        'clinic_testdata.HOME_COLLECT',
                        'clinic_testdata.ORGAN_ID',
                        'clinic_testdata.ORGAN_NAME',
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
                        'dashboard.PHOTO_URL',
                        'dashboard.POSITION',
                        'sym_organ_test.DASH_ID',
                        'sym_organ_test.DASH_NAME',
                    )
                    ->where(['dashboard.DASH_SECTION_ID' => 'S', 'dashboard.STATUS' => 'Active'])
                    ->orderby('dashboard.POSITION')
                    ->get();


                // RETURN $data1;
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
                    ];
                })->values()->all();
                $S["Symptomatic_Test"] = array_values($S_DTL + $S_BNR);

                //SECTION-T #### ORGAN TEST
                $data1 = DB::table('dashboard')
                    ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard.DASH_ID')
                    // ->join('clinic_testdata', 'sym_organ_test.TEST_ID', '=', 'clinic_testdata.TEST_ID')
                    ->select(
                        'sym_organ_test.TEST_ID',
                        'sym_organ_test.TEST_NAME',
                        'dashboard.DASH_ID',
                        'dashboard.DASH_NAME',
                        'dashboard.PHOTO_URL',
                        // 'clinic_testdata.*',
                    )
                    ->where(['dashboard.DASH_SECTION_ID' => 'T', 'STATUS' => 'Active'])
                    ->orderby('dashboard.POSITION')
                    ->get();
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
                            // "TEST_SL" => $item->TEST_SL,
                            "TEST_NAME" => $item->TEST_NAME,
                            // "TEST_CODE" => $item->TEST_CODE,
                            // "TEST_SAMPLE" => $item->TEST_SAMPLE,
                            // "TEST_CATG" => $item->TEST_CATG,
                            // "COST" => $item->COST,
                            // "CATEGORY" => $item->CATEGORY,
                            // "TEST_UNIT" => $item->TEST_UNIT,
                            // "NORMAL_RANGE" => $item->NORMAL_RANGE,
                            // "TEST_DESC" => $item->TEST_DESC,
                            // "KNOWN_AS" => $item->KNOWN_AS,
                            // "FASTING" => $item->FASTING,
                            // "GENDER_TYPE" => $item->GENDER_TYPE,
                            // "AGE_TYPE" => $item->AGE_TYPE,
                            // "REPORT_TIME" => $item->REPORT_TIME,
                            // "PRESCRIPTION" => $item->PRESCRIPTION,
                            // "ID_PROOF" => $item->ID_PROOF,
                            // "QA1" => $item->QA1,
                            // "QA2" => $item->QA2,
                            // "QA3" => $item->QA3,
                            // "QA4" => $item->QA4,
                            // "QA5" => $item->QA5,
                            // "QA6" => $item->QA6,
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
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
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
                    ];
                })->values()->all();
                $T["Organ_Test"] = array_values($T_DTL + $T_BNR);

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
                    ];
                })->values()->all();

                //SECTION-W #### SPECIAL SERVICES
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
                    ];
                })->values()->all();

                //SECTION-#### MET OUR DOCTORS

                $distinctDoctors = DB::table('dr_availablity')
                    ->select('DR_ID', 'DR_FEES')
                    ->distinct()
                    ->where(['PHARMA_ID' => $pid, 'TAG_DEPT' => 'true']);

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
                $fltr_dash = $dash->filter(function ($item) {
                    return strpos($item->TAG_SECTION, 'SR') !== false && $item->INDASH === 'true';
                });

                $I_DTL = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DIS_ID" => $item->DIS_ID,
                        "SYM_ID" => $item->SYM_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_SECTION_DESC" => $item->DASH_SECTION_DESC,
                        "CATEGORY" => $item->CATEGORY,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "GR_DESC" => $item->GR_DESC,
                        "DASH_URL" => $item->DASH_URL,
                        "DASH_BAK" => $item->DASH_BAK,
                        "URL_IPD_MI" => $item->URL_IPD_MI,
                        "URL_IPD_MG" => $item->URL_IPD_MG,
                        "URL_IPD_MB" => $item->URL_IPD_MB,
                        "URL_IPD_MGB" => $item->URL_IPD_MGB,
                        "PHOTO_URL" => $item->PHOTO_URL,
                        "BANNER_URL" => $item->BANNER_URL,
                        "STATUS" => $item->STATUS,
                        "QA1" => $item->QA1,
                        "QA2" => $item->QA2,
                        "QA3" => $item->QA3,
                        "QA4" => $item->QA4,
                        "QA5" => $item->QA5,
                        "QA6" => $item->QA6,
                        "QA7" => $item->QA7,
                        "QA8" => $item->QA8,
                        "QA9" => $item->QA9,
                    ];
                })->values()->all();

                $groupedData = [];
                foreach ($I_DTL as $row2) {
                    if (!isset($groupedData[$row2['CATEGORY']])) {
                        $groupedData[$row2['CATEGORY']] = [
                            "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
                            "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
                            "DESCRIPTION" => $row2['DASH_SECTION_DESC'],
                            // "PHOTO_URL" => $row2['PHOTO_URL'],
                            "PHOTO_URL" => $row2['DASH_BAK'],
                            "BANNER_URL" => $row2['BANNER_URL'],
                            "DASH_TYPE" => []
                        ];
                    }

                    if (!isset($groupedData[$row2['CATEGORY']]['DASH_TYPE'][$row2['DASH_TYPE']])) {
                        $groupedData[$row2['CATEGORY']]['DASH_TYPE'][$row2['DASH_TYPE']] = [
                            "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
                            "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
                            "DASH_TYPE" => $row2['DASH_TYPE'],
                            "DESCRIPTION" => $row2['GR_DESC'],
                            "PHOTO_URL" => $row2['URL_IPD_MG'],
                            "BANNER_URL" => $row2['URL_IPD_MGB'],
                            "FACILITY_DETAILS" => []
                        ];
                    }

                    $groupedData[$row2['CATEGORY']]['DASH_TYPE'][$row2['DASH_TYPE']]['FACILITY_DETAILS'][] = [
                        "DASH_ID" => $row2['DASH_ID'],
                        "DIS_ID" => $row2['DIS_ID'],
                        "SYM_ID" => $row2['SYM_ID'],
                        "DASH_NAME" => $row2['DASH_NAME'],
                        "DESCRIPTION" => $row2['DASH_DESCRIPTION'],
                        "PHOTO_URL" => $row2['DASH_URL'],
                        "BANNER_URL" => $row2['URL_IPD_MB'],
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

                $SURG["Surgery"] = array_values($groupedData);


                $data = $A + $cldata + $DASH_Z + $U + $SPLST + $TST + $AE + $SYM + $C + $DASH_BNR + $S + $T + $B + $G + $H + $V + $W + $X + $Y + $DD + $ldr + $SURG; // + $S + $T
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    // function labdashboard(Request $request)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $input   = $request->json()->all();
    //         if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
    //             $latt = $input['LATITUDE'];
    //             $lont = $input['LONGITUDE'];

    //             $lab_hdr_dtl = DB::table('l_dashboard_details')->where('STATUS', 'Active')->orderby('SECTION_SL')->get();
    //             $banner = DB::table('banner')->get();
    //             //SECTION-A #### SLIDER
    //             $fltr_arr = $lab_hdr_dtl->filter(function ($item) {
    //                 return $item->SECTION_ID === 'A';
    //             });
    //             $AD["slider"] = $fltr_arr->map(function ($item) {
    //                 return [
    //                     "ID" => $item->ID,
    //                     "SLIDER_URL" => $item->PHOTO_URL,
    //                 ];
    //             })->values()->all();

    //             //SECTION-B #### DASHBOARD
    //             $data1 = DB::table('l_dashboard_details')
    //                 ->join('master_testdata', 'l_dashboard_details.ITEM_NAME', '=', 'master_testdata.TEST_CATG')
    //                 ->select(
    //                     'l_dashboard_details.ID as SECTION_ID',
    //                     'l_dashboard_details.ITEM_NAME',
    //                     'l_dashboard_details.PHOTO_URL',
    //                     'master_testdata.*',
    //                 )
    //                 ->where('l_dashboard_details.SECTION_ID', '=', 'B')
    //                 ->orderby('l_dashboard_details.SECTION_SL')
    //                 ->get();

    //             $B_DTL = [];
    //             foreach ($data1->pluck('SECTION_ID')->unique() as $catg) {
    //                 $filteredArray = $data1->where('SECTION_ID', $catg);
    //                 $organDetails = [];
    //                 foreach ($filteredArray as $item) {
    //                     $organID = $item->ORGAN_ID;
    //                     if (!isset($organDetails[$organID])) {
    //                         $organDetails[$organID] = [
    //                             "ORGAN_ID" => $organID,
    //                             "ORGAN_NAME" => $item->ORGAN_NAME,
    //                             "ORGAN_URL" => $item->ORGAN_URL,
    //                         ];
    //                     }
    //                 }
    //                 $B_DTL[] = [
    //                     "ITEM_ID" => $filteredArray->first()->SECTION_ID,
    //                     "ITEM_NAME" => $filteredArray->first()->ITEM_NAME,
    //                     "PHOTO_URL" => $filteredArray->first()->PHOTO_URL,
    //                     "ORGANS" => array_values($organDetails),
    //                 ];
    //             }
    //             $fltr_hdr = $lab_hdr_dtl->filter(function ($item) {
    //                 return $item->SECTION_ID === 'B';
    //             });
    //             $fltr_bnr = $banner->filter(function ($item) {
    //                 return $item->BANNER_TYPE === 'Profile';
    //             });
    //             $B_bnr["Banner"] = $fltr_bnr->values()->all();
    //             $BD["dashboard"] = array_values($B_DTL + $B_bnr);

    //             //SECTION-C #### BANER
    //             $fltr_hdr = $lab_hdr_dtl->filter(function ($item) {
    //                 return $item->SECTION_ID === 'C';
    //             });
    //             $CD["banner_1"] = $fltr_hdr->values()->all();

    //             //SECTION-D #### SYMPTOMATIC TEST
    //             $data1 = DB::table('l_dashboard_details')
    //                 ->join('package', 'package.LAB_PKG_ID', '=', 'l_dashboard_details.ID')
    //                 ->join('package_details', 'package.PKG_ID', '=', 'package_details.PKG_ID')
    //                 ->join('clinic_testdata', 'package_details.TEST_ID', '=', 'clinic_testdata.TEST_UC')
    //                 ->select(
    //                     'package.LAB_PKG_ID',
    //                     'package.PKG_NAME',
    //                     'l_dashboard_details.PHOTO_URL',
    //                     'clinic_testdata.*',
    //                 )
    //                 ->where('l_dashboard_details.SECTION_ID', '=', 'D')
    //                 ->orderby('l_dashboard_details.SECTION_SL')
    //                 ->get();
    //             $D_DTL = [];
    //             $collection = collect($data1);
    //             $distinctValues = $collection->pluck('LAB_PKG_ID')->unique();
    //             foreach ($distinctValues as $row) {
    //                 $fltr_arr = $data1->filter(function ($item) use ($row) {
    //                     return $item->LAB_PKG_ID === $row;
    //                 });

    //                 $T_dtl = $fltr_arr->map(function ($item) {
    //                     return [
    //                         "TEST_ID" => $item->TEST_ID,
    //                         "TEST_SL" => $item->TEST_SL,
    //                         "TEST_NAME" => $item->TEST_NAME,
    //                         "TEST_TYPE" => $item->TEST_TYPE,
    //                         "TEST_CODE" => $item->TEST_CODE,
    //                         "TEST_SAMPLE" => $item->TEST_SAMPLE,
    //                         "TEST_CATG" => $item->TEST_CATG,
    //                         "CATEGORY" => $item->CATEGORY,
    //                         // "TEST_UNIT" => $item->TEST_UNIT,
    //                         // "NORMAL_RANGE" => $item->NORMAL_RANGE,
    //                         "TEST_DESC" => $item->TEST_DESC,
    //                         "KNOWN_AS" => $item->KNOWN_AS,
    //                         "FASTING" => $item->FASTING,
    //                         "GENDER_TYPE" => $item->GENDER_TYPE,
    //                         "AGE_TYPE" => $item->AGE_TYPE,
    //                         "REPORT_TIME" => $item->REPORT_TIME,
    //                         "PRESCRIPTION" => $item->PRESCRIPTION,
    //                         "ID_PROOF" => $item->ID_PROOF,
    //                         "QA1" => $item->QA1,
    //                         "QA2" => $item->QA2,
    //                         "QA3" => $item->QA3,
    //                         "QA4" => $item->QA4,
    //                         "QA5" => $item->QA5,
    //                         "QA6" => $item->QA6,
    //                         "COST" => $item->COST,
    //                         "DISCOUNT" => $item->DISCOUNT,
    //                         "ID_PROOF" => $item->ID_PROOF,
    //                         "HOME_COLLECT" => $item->HOME_COLLECT,
    //                         "FREE_AREA" => $item->FREE_AREA,
    //                         "SERV_CONDITION" => $item->SERV_CONDITION,
    //                     ];
    //                 })->values()->all();
    //                 $D_DTL[]  = [
    //                     "PKG_ID" => $row,
    //                     "PKG_NAME" => $fltr_arr->first()->PKG_NAME,
    //                     "PKG_URL" => $fltr_arr->first()->PHOTO_URL,
    //                     "TOT_TEST" => count($T_dtl),
    //                     "TOT_COST" => array_sum(array_column($T_dtl, 'COST')),
    //                     "DETAILS" => $T_dtl
    //                 ];
    //             }
    //             $fltr_bnr = $banner->filter(function ($item) {
    //                 return $item->BANNER_TYPE === 'Package';
    //             });

    //             $D_bnr["Banner"] = $fltr_bnr->values()->all();
    //             $DD["popular_singal_test"] = array_values($D_DTL + $D_bnr);

    //             //SECTION-E #### DIAGNOSTIC
    //             $E_DTL = DB::table('pharmacy')
    //                 ->join('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
    //                 ->distinct('pharmacy.PHARMA_ID')
    //                 ->select(
    //                     'pharmacy.PHARMA_ID',
    //                     'pharmacy.ITEM_NAME AS PHARMA_NAME',
    //                     'pharmacy.CLINIC_TYPE',
    //                     'pharmacy.ADDRESS',
    //                     'pharmacy.CITY',
    //                     'pharmacy.DIST',
    //                     'pharmacy.STATE',
    //                     'pharmacy.PIN',
    //                     'pharmacy.CLINIC_MOBILE',
    //                     'pharmacy.PHOTO_URL',
    //                     'pharmacy.LOGO_URL',
    //                     'pharmacy.LATITUDE',
    //                     'pharmacy.LONGITUDE',
    //                     // DB::raw('COUNT(distinct dr_availablity.DR_ID) as TOT_DR'),
    //                     DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
    //              * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
    //               * SIN(RADIANS('$latt'))))),2) as KM")
    //                 )
    //                 ->where('pharmacy.CLINIC_TYPE', '<>', 'Clinic')
    //                 ->orderby('KM')
    //                 ->take(5)
    //                 ->get()->toArray();
    //             $fltr_bnr = $banner->filter(function ($item) {
    //                 return $item->BANNER_TYPE === 'Package';
    //             });
    //             $E_bnr["Banner"] = $fltr_bnr->values()->all();
    //             $ED["popular_lab"] = array_values($E_DTL + $E_bnr);

    //             //SECTION-F #### PROFILE
    //             $fltr_hdr = $lab_hdr_dtl->filter(function ($item) {
    //                 return $item->SECTION_ID === 'F';
    //             });
    //             $F_DTL = $fltr_hdr->values()->all();
    //             $fltr_bnr = $banner->filter(function ($item) {
    //                 return $item->BANNER_TYPE === 'Package';
    //             });
    //             $F_bnr["Banner"] = $fltr_bnr->values()->all();
    //             $FD["profile_test"] = array_values($F_DTL + $F_bnr);

    //             //SECTION-G #### PACKAGE
    //             $fltr_hdr = $lab_hdr_dtl->filter(function ($item) {
    //                 return $item->SECTION_ID === 'P' && $item->DASH_ID != 'G';
    //             });
    //             $G_DTL = $fltr_hdr->values()->all();
    //             $fltr_bnr = $banner->filter(function ($item) {
    //                 return $item->BANNER_TYPE === 'Package';
    //             });
    //             $G_bnr["Banner"] = $fltr_bnr->values()->all();
    //             $GD["popular_health_package"] = array_values($G_DTL + $G_bnr);

    //             //SECTION-H #### BANNER-2
    //             $fltr_hdr = $lab_hdr_dtl->filter(function ($item) {
    //                 return $item->SECTION_ID === 'H';
    //             });
    //             $HD["banner_2"] = $fltr_hdr->values()->all();

    //             //SECTION-I #### RADIOLOGY
    //             $data1 = DB::table('l_dashboard_details')
    //                 ->join('master_testdata', 'l_dashboard_details.ITEM_NAME', '=', 'master_testdata.TEST_CATG')
    //                 ->select(
    //                     'l_dashboard_details.SECTION_NAME',
    //                     'l_dashboard_details.ID as SECTION_ID',
    //                     'l_dashboard_details.ITEM_NAME',
    //                     'l_dashboard_details.PHOTO_URL',
    //                     'master_testdata.*',
    //                 )
    //                 ->where('l_dashboard_details.SECTION_ID', '=', 'I')
    //                 ->orderby('l_dashboard_details.SECTION_SL')
    //                 ->get();

    //             $I_DTL = [];
    //             foreach ($data1->pluck('SECTION_ID')->unique() as $catg) {
    //                 $filteredArray = $data1->where('SECTION_ID', $catg);
    //                 $organDetails = [];
    //                 foreach ($filteredArray as $item) {
    //                     $organID = $item->ORGAN_ID;
    //                     if (!isset($organDetails[$organID])) {
    //                         $organDetails[$organID] = [
    //                             "ORGAN_ID" => $organID,
    //                             "ORGAN_NAME" => $item->ORGAN_NAME,
    //                             "ORGAN_URL" => $item->ORGAN_URL,
    //                             // "TOT_TEST"=>0,
    //                             // "TEST_DETAILS" => []
    //                         ];
    //                     }
    //                 }
    //                 $I_DTL[] = [
    //                     "SECTION_NAME" => $filteredArray->first()->SECTION_NAME,
    //                     "ITEM_ID" => $filteredArray->first()->SECTION_ID,
    //                     "ITEM_NAME" => $filteredArray->first()->ITEM_NAME,
    //                     "PHOTO_URL" => $filteredArray->first()->PHOTO_URL,
    //                     "ORGANS" => array_values($organDetails),
    //                 ];
    //             }
    //             $fltr_bnr = $banner->filter(function ($item) {
    //                 return $item->BANNER_TYPE === 'Package';
    //             });

    //             $I_bnr["Banner"] = $fltr_bnr->values()->all();
    //             $ID["popular_scan"] = array_values($I_DTL + $I_bnr);

    //             //SECTION-J #### WOMEN HEALTH PACKAGE BANNER
    //             $fltr_hdr = $lab_hdr_dtl->filter(function ($item) {
    //                 return $item->SECTION_ID === 'J';
    //             });
    //             $J_DTL = $fltr_hdr->values()->all();

    //             $fltr_bnr = $banner->filter(function ($item) {
    //                 return $item->BANNER_TYPE === 'Package';
    //             });

    //             $J_bnr["Banner"] = $fltr_bnr->values()->all();
    //             $JD["women_health_package"] = array_values($J_DTL + $J_bnr);


    //             // $JD["women_health_package"] = $fltr_hdr->values()->all();

    //             //SECTION-K #### FAMILY HEALTH PACKAGE
    //             $fltr_hdr = $lab_hdr_dtl->filter(function ($item) {
    //                 return $item->SECTION_ID === 'K';
    //             });
    //             $K_DTL = $fltr_hdr->values()->all();

    //             $fltr_bnr = $banner->filter(function ($item) {
    //                 return $item->BANNER_TYPE === 'Package';
    //             });

    //             $K_bnr["Banner"] = $fltr_bnr->values()->all();
    //             $KD["family_health_package"] = array_values($K_DTL + $K_bnr);



    //             // $KD["family_health_package"] = $fltr_hdr->values()->all();

    //             //SECTION-L #### HOW IT WORKS FIXED BANNER
    //             $fltr_hdr = $lab_hdr_dtl->filter(function ($item) {
    //                 return $item->SECTION_ID === 'L';
    //             });
    //             $LD["how_it_work"] = $fltr_hdr->values()->all();

    //             //SECTION-M #### MAKES US SPECIAL FIXED BANNER
    //             $fltr_hdr = $lab_hdr_dtl->filter(function ($item) {
    //                 return $item->SECTION_ID === 'M';
    //             });
    //             $MD["what_makes_us"] = $fltr_hdr->values()->all();

    //             //SECTION-N #### OFFER BANNER
    //             $fltr_hdr = $lab_hdr_dtl->filter(function ($item) {
    //                 return $item->SECTION_ID === 'N';
    //             });
    //             $ND["banner_3"] = $fltr_hdr->values()->all();

    //             //SECTION-O #### SINGLE TEST
    //             $O_DTL  = DB::table('master_testdata')
    //                 ->join(DB::raw('(SELECT DISTINCT clinic_testdata.TEST_ID,clinic_testdata.HOME_COLLECT, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID,HOME_COLLECT) as clinic_testdata'), function ($join) {
    //                     $join->on('master_testdata.TEST_ID', '=', 'clinic_testdata.TEST_ID');
    //                 })
    //                 ->select('master_testdata.*', 'clinic_testdata.MIN_COST', 'clinic_testdata.HOME_COLLECT AS HOME_COLLECT')
    //                 ->get()->toArray();

    //             // $fltr_hdr = $lab_hdr_dtl->filter(function ($item) {
    //             //     return $item->SECTION_ID === 'N';
    //             // });
    //             // $OBD= $fltr_hdr->first();
    //             // array_push($O_DTL,$OBD);

    //             $fltr_bnr = $banner->filter(function ($item) {
    //                 return $item->BANNER_TYPE === 'Package';
    //             });

    //             $O_bnr["Banner"] = $fltr_bnr->values()->all();
    //             $OD["symptomatic_test"] = array_values($O_DTL + $O_bnr);

    //             // $OD["symptomatic_test"] = DB::table('master_testdata')

    //             //     ->join(DB::raw('(SELECT clinic_testdata.TEST_ID, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID) as clinic_testdata'), function ($join) {
    //             //         $join->on('master_testdata.TEST_ID', '=', 'clinic_testdata.TEST_ID');
    //             //     })
    //             //     ->select('master_testdata.*', 'clinic_testdata.MIN_COST')
    //             //     ->get();


    //             //SECTION-P #### EXPERT CARE FOR WOMEN
    //             $fltr_hdr = $lab_hdr_dtl->filter(function ($item) {
    //                 return $item->SECTION_ID === 'P';
    //             });
    //             $P_DTL = $fltr_hdr->values()->all();

    //             $fltr_bnr = $banner->filter(function ($item) {
    //                 return $item->BANNER_TYPE === 'Package';
    //             });

    //             $P_bnr["Banner"] = $fltr_bnr->values()->all();
    //             $PD["expert_care_women"] = array_values($P_DTL + $P_bnr);


    //             // $PD["expert_care_women"] = $fltr_hdr->values()->all();

    //             //SECTION-Q #### CHILD CVARE OF LITTLE
    //             $fltr_hdr = $lab_hdr_dtl->filter(function ($item) {
    //                 return $item->SECTION_ID === 'Q';
    //             });
    //             $Q_DTL = $fltr_hdr->values()->all();

    //             $fltr_bnr = $banner->filter(function ($item) {
    //                 return $item->BANNER_TYPE === 'Package';
    //             });

    //             $Q_bnr["Banner"] = $fltr_bnr->values()->all();
    //             $QD["expert_care_little"] = array_values($Q_DTL + $Q_bnr);



    //             // $QD["expert_care_little"] = $fltr_hdr->values()->all();

    //             $data =  $AD +  $BD + $CD + $DD + $ED + $FD + $GD + $HD + $ID + $JD + $KD + $LD + $MD + $ND + $OD + $PD + $QD;
    //             $response = ['Success' => true, 'data' => $data, 'code' => 200];
    //         } else {
    //             $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
    //     }
    //     return $response;
    // }

    function labdashboard1(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $promo_bnr = DB::table('promo_banner')->where('STATUS', 'Active')->get();
                $dash = DB::table('dashboard')->where('CATEGORY', 'like', '%' . 'L' . '%')->where('STATUS', 'Active')->get();

                //SECTION-A #### SLIDER
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'LA';
                });
                $A["Slider"] = $fltr_promo_bnr->map(function ($item) {
                    return [
                        "SLIDER_ID" => $item->PROMO_ID,
                        "SLIDER_NAME" => $item->PROMO_NAME,
                        "SLIDER_URL" => $item->PROMO_URL,
                    ];
                })->values()->all();

                //SECTION-F #### DASHBOARD
                $data1 = DB::table('dashboard')
                    ->join('master_testdata', 'dashboard.DASH_NAME', '=', 'master_testdata.TEST_CATG')
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
                        'dashboard.BANNER_URL',
                    )
                    ->where('dashboard.DASH_SECTION_ID', '=', 'F')
                    ->orderby('master_testdata.ORGAN_NAME')
                    ->orderby('dashboard.POSITION')
                    ->get();

                // return $data1;

                $F_DTL = [];
                foreach ($data1->pluck('DASH_ID')->unique() as $DSI) {
                    $filteredArray = $data1->where('DASH_ID', $DSI);
                    $organDetails = [];
                    foreach ($filteredArray as $item) {
                        $organID = $item->ORGAN_ID;
                        if (!isset($organDetails[$organID])) {
                            $organDetails[$organID] = [
                                "ORGAN_ID" => $organID,
                                "ORGAN_NAME" => $item->ORGAN_NAME,
                                "ORGAN_URL" => $item->ORGAN_URL,
                            ];
                        }
                    }
                    $F_DTL[] = [
                        "DASH_ID" => $filteredArray->first()->DASH_ID,
                        "DASH_SECTION_NAME" => $filteredArray->first()->DASH_SECTION_NAME,
                        "DASH_NAME" => $filteredArray->first()->DASH_NAME,
                        "PHOTO_URL" => $filteredArray->first()->PHOTO_URL,
                        "PHOTO1_URL" => $filteredArray->first()->PHOTO1_URL,
                        "BANNER_URL" => $filteredArray->first()->BANNER_URL,
                        "ORGANS" => array_values($organDetails),
                    ];
                }
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'F';
                });
                $F_BNR["Banner"] = $fltr_promo_bnr->map(function ($item) {
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
                $F["Dashboard"] = array_values($F_DTL + $F_BNR);

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
                                "ORGAN_URL" => $item->ORGAN_URL,
                            ];
                        }
                    }
                    $F1_DTL[] = [
                        "DASH_ID" => $filteredArray->first()->DASH_ID,
                        "DASH_SECTION_NAME" => $filteredArray->first()->DASH_SECTION_NAME,
                        "DASH_NAME" => $filteredArray->first()->DASH_NAME,
                        "PHOTO_URL" => $filteredArray->first()->PHOTO_URL,
                        "PHOTO1_URL" => $filteredArray->first()->PHOTO1_URL,
                        "BANNER_URL" => $filteredArray->first()->BANNER_URL,
                        "ORGANS" => array_values($organDetails),
                    ];
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
                $F1["Dashboard1"] = array_values($F1_DTL + $F1_BNR);

                //SECTION-####LABDASHBOARD BANNER
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'LD';
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

                //SECTION-S #### SYMPTOMATIC TEST
                $data1 = DB::table('dashboard')
                    ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard.DASH_ID')
                    ->join(DB::raw('(SELECT DISTINCT TEST_ID,TEST_SL,TEST_NAME,TEST_CODE,TEST_SAMPLE,TEST_CATG,DEPARTMENT,TEST_DESC,KNOWN_AS,FASTING,GENDER_TYPE,AGE_TYPE,REPORT_TIME,PRESCRIPTION,ID_PROOF,QA1,QA2,QA3,QA4,QA5,QA6,HOME_COLLECT, MIN(COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID,TEST_SL,TEST_NAME,TEST_CODE,TEST_SAMPLE,TEST_CATG,DEPARTMENT,TEST_DESC,KNOWN_AS,FASTING,GENDER_TYPE,AGE_TYPE,REPORT_TIME,PRESCRIPTION,ID_PROOF,QA1,QA2,QA3,QA4,QA5,QA6,HOME_COLLECT) as clinic_testdata'), function ($join) {
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
                    ->where(['dashboard.DASH_SECTION_ID' => 'S', 'STATUS' => 'Active'])
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

                //SECTION-T #### ORGAN TEST
                $data1 = DB::table('dashboard')
                    ->join('sym_organ_test', 'sym_organ_test.DASH_ID', '=', 'dashboard.DASH_ID')
                    ->join('master_testdata', 'sym_organ_test.TEST_ID', '=', 'master_testdata.TEST_ID')
                    ->select(
                        'dashboard.DASH_ID',
                        'dashboard.DASH_NAME',
                        'dashboard.PHOTO_URL',
                        'master_testdata.*',
                    )
                    ->where(['dashboard.DASH_SECTION_ID' => 'T', 'STATUS' => 'Active'])
                    ->orderby('dashboard.POSITION')
                    ->get();
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
                    $T_DTL[] = [
                        "DASH_ID" => $row,
                        "DASH_NAME" => $fltr_arr->first()->DASH_NAME,
                        "PHOTO_URL" => $fltr_arr->first()->PHOTO_URL,
                        "TOT_TEST" => count($TDTL),
                        // "TOT_COST" => array_sum(array_column($T_DTL, 'COST')),
                        "DETAILS" => $TDTL
                    ];
                }
                $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
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
                    ];
                })->values()->all();
                $T["Organ_Test"] = array_values($T_DTL + $T_BNR);

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

                //SECTION-V #### WHY MAKES US SPECIAL
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'V';
                });
                $V["Why_Makes_Us_Special"] = $fltr_dash->map(function ($item) {
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

                //SECTION-W #### SPECIAL SERVICES
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'W';
                });
                $W["Special_Services"] = $fltr_dash->map(function ($item) {
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

                //SECTION-#### SINGLE TEST
                $TST_DTL = DB::table('master_testdata')
                    ->join(DB::raw('(SELECT DISTINCT clinic_testdata.TEST_ID,clinic_testdata.HOME_COLLECT, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID,HOME_COLLECT) as clinic_testdata'), function ($join) {
                        $join->on('master_testdata.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                    })
                    ->select('master_testdata.*', 'clinic_testdata.MIN_COST', 'clinic_testdata.HOME_COLLECT AS HOME_COLLECT')
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
                $TST["Popular_Single_Test"] = array_values($TST_DTL + $STB);

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

                $data = $A + $F + $F1 + $DASH_BNR + $S + $T + $DIAG + $C + $G + $H + $B + $U + $V + $W + $TST + $AB;
                $response = ['Success' => true, 'data' => $data, 'code' => 200];
            } else {
                $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
            }
        } else {
            $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
        }
        return $response;
    }

    // function dashboard(Request $request)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $input = $request->json()->all();
    //         if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
    //             $latt = $input['LATITUDE'];
    //             $lont = $input['LONGITUDE'];

    //             $promo_bnr = DB::table('promo_banner')->where('STATUS', 'Active')->get();
    //             $dash = DB::table('dashboard')->where('CATEGORY', 'like', '%' . 'M' . '%')->where('STATUS', 'Active')->orderby('DASH_SL')->orderby('GR_POSITION')->get();
    //             $pharma = DB::table('pharmacy')
    //                 ->leftjoin('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
    //                 // ->leftjoin('clinic_testdata', 'pharmacy.PHARMA_ID', '=', 'clinic_testdata.PHARMA_ID')
    //                 ->select(
    //                     'pharmacy.PHARMA_ID',
    //                     'pharmacy.ITEM_NAME AS PHARMA_NAME',
    //                     'pharmacy.CLINIC_TYPE',
    //                     'pharmacy.ADDRESS',
    //                     'pharmacy.CITY',
    //                     'pharmacy.DIST',
    //                     'pharmacy.STATE',
    //                     'pharmacy.PIN',
    //                     'pharmacy.CLINIC_MOBILE',
    //                     'pharmacy.PHOTO_URL',
    //                     'pharmacy.LOGO_URL',
    //                     'pharmacy.LATITUDE',
    //                     'pharmacy.LONGITUDE',
    //                     DB::raw('COUNT(distinct dr_availablity.DR_ID) as TOT_DR'),
    //                     // DB::raw('COUNT(distinct clinic_testdata.TEST_ID) as TOT_TEST'),
    //                     DB::raw("round(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(pharmacy.Latitude)) 
    //              * COS(RADIANS('$latt')) * COS(RADIANS(pharmacy.Longitude - '$lont')) + SIN(RADIANS(pharmacy.Latitude)) 
    //               * SIN(RADIANS('$latt'))))),2) as KM")
    //                 )
    //                 ->groupBy(
    //                     'pharmacy.PHARMA_ID',
    //                     'pharmacy.ITEM_NAME',
    //                     'pharmacy.ADDRESS',
    //                     'pharmacy.CLINIC_TYPE',
    //                     'pharmacy.CITY',
    //                     'pharmacy.DIST',
    //                     'pharmacy.STATE',
    //                     'pharmacy.PIN',
    //                     'pharmacy.CLINIC_MOBILE',
    //                     'pharmacy.PHOTO_URL',
    //                     'pharmacy.LOGO_URL',
    //                     'pharmacy.LATITUDE',
    //                     'pharmacy.LONGITUDE'
    //                 )
    //                 ->orderby('KM')->take(25)->get();

    //             //SECTION-A #### SLIDER
    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'MA';
    //             });
    //             $A["Slider"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "SLIDER_ID" => $item->PROMO_ID,
    //                     "SLIDER_NAME" => $item->PROMO_NAME,
    //                     "SLIDER_URL" => $item->PROMO_URL,
    //                 ];
    //             })->values()->all();

    //             //SECTION-DASH_A #### DASHBOARD
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'A';
    //             });
    //             $DASH_A["Dashboard"] = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DASH_SECTION_ID" => $item->FACILITY_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_NAME,
    //                     "DESCRIPTION" => $item->DASH_SECTION_DESC,
    //                     "PHOTO_URL" => $item->DSIMG1,
    //                     "BANNER_URL" => $item->DSBNR1,
    //                 ];
    //             })->values()->all();

    //             //SECTION-#### SPECIALIST
    //             $SPLST_DTL = DB::table('dis_catg')->select('DIS_ID', 'DASH_SECTION_ID', 'DIS_TYPE', 'DIS_CATEGORY', 'SPECIALIST', 'SPECIALITY', 'PHOTO_URL')->take(7)->orderBy('DIS_SL')->get()->toArray();
    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'SP';
    //             });

    //             $SPB["Specialist_Banner"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_ID" => $item->PROMO_ID,
    //                     "HEADER_NAME" => $item->HEADER_NAME,
    //                     "PHARMA_ID" => $item->PHARMA_ID,
    //                     "MOBILE_NO" => $item->MOBILE_NO,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "PKG_ID" => $item->PKG_ID,
    //                     "PROMO_NAME" => $item->PROMO_NAME,
    //                     "DESCRIPTION" => $item->DESCRIPTION,
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                     "PROMO_DT" => $item->PROMO_DT,
    //                     "PROMO_VALID" => $item->PROMO_VALID,
    //                 ];
    //             })->take(3)->values()->all();
    //             $SPLST["Specialist"] = array_values($SPLST_DTL + $SPB);

    //             //SECTION-#### SYMPTOMS
    //             $SYM_DTL = DB::table('symptoms')->select('SYM_ID', 'SYM_NAME', 'DIS_ID', 'DIS_CATEGORY', 'DASH_PHOTO as PHOTO_URL')->orderby('SYM_SL')->take(10)->get()->toArray();
    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'SM';
    //             });
    //             $SMB["Symptoms_Banner"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_ID" => $item->PROMO_ID,
    //                     "HEADER_NAME" => $item->HEADER_NAME,
    //                     "PHARMA_ID" => $item->PHARMA_ID,
    //                     "MOBILE_NO" => $item->MOBILE_NO,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "PKG_ID" => $item->PKG_ID,
    //                     "PROMO_NAME" => $item->PROMO_NAME,
    //                     "DESCRIPTION" => $item->DESCRIPTION,
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                     "PROMO_DT" => $item->PROMO_DT,
    //                     "PROMO_VALID" => $item->PROMO_VALID,
    //                 ];
    //             })->take(3)->values()->all();
    //             $SYM["Symptoms"] = array_values($SYM_DTL + $SMB);

    //             //SECTION-NEAR BY POLYCLINIC
    //             $fltr_pharma = $pharma->filter(function ($item) {
    //                 return $item->CLINIC_TYPE === 'Clinic';
    //             });
    //             $CLINIC_DTL = $fltr_pharma->values()->take(25)->all();

    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'CL';
    //             });
    //             $CL_BNR["Clinic_Banner"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_ID" => $item->PROMO_ID,
    //                     "HEADER_NAME" => $item->HEADER_NAME,
    //                     "PHARMA_ID" => $item->PHARMA_ID,
    //                     "MOBILE_NO" => $item->MOBILE_NO,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "PKG_ID" => $item->PKG_ID,
    //                     "PROMO_NAME" => $item->PROMO_NAME,
    //                     "DESCRIPTION" => $item->DESCRIPTION,
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                     "PROMO_DT" => $item->PROMO_DT,
    //                     "PROMO_VALID" => $item->PROMO_VALID,
    //                 ];
    //             })->values()->all();
    //             $CLINIC["Clinics"] = array_values($CLINIC_DTL + $CL_BNR);

    //             //SECTION-E #### EXPERT CARE FOR WOMEN
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'E';
    //             });
    //             $E_DTL = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO_URL,
    //                 ];
    //             })->values()->all();
    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'E';
    //             });
    //             $E_BNR["Expert_Care_Women_Banner"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_ID" => $item->PROMO_ID,
    //                     "HEADER_NAME" => $item->HEADER_NAME,
    //                     "PHARMA_ID" => $item->PHARMA_ID,
    //                     "MOBILE_NO" => $item->MOBILE_NO,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "PKG_ID" => $item->PKG_ID,
    //                     "PROMO_NAME" => $item->PROMO_NAME,
    //                     "DESCRIPTION" => $item->DESCRIPTION,
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                     "PROMO_DT" => $item->PROMO_DT,
    //                     "PROMO_VALID" => $item->PROMO_VALID,
    //                 ];
    //             })->values()->all();

    //             $E["Women_Health_Care"] = array_values($E_DTL + $E_BNR);

    //             //SECTION-D #### EXPERT CARE CHILD (0 TO 12)
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'D';
    //             });
    //             $D_DTL = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO_URL,
    //                     "VIEW1_URL" => $item->VIEW1_URL,
    //                 ];
    //             })->values()->all();
    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'D';
    //             });
    //             $D_BNR["Expert_Care_Child_Banner"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_ID" => $item->PROMO_ID,
    //                     "HEADER_NAME" => $item->HEADER_NAME,
    //                     "PHARMA_ID" => $item->PHARMA_ID,
    //                     "MOBILE_NO" => $item->MOBILE_NO,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "PKG_ID" => $item->PKG_ID,
    //                     "PROMO_NAME" => $item->PROMO_NAME,
    //                     "DESCRIPTION" => $item->DESCRIPTION,
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                     "PROMO_DT" => $item->PROMO_DT,
    //                     "PROMO_VALID" => $item->PROMO_VALID,
    //                 ];
    //             })->values()->all();

    //             $D["Expert_Care_Child"] = array_values($D_DTL + $D_BNR);

    //             //SECTION-M #### AMBULANCE SERVICE
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'M';
    //             });
    //             $M["Ambulance_Service"] = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO_URL,
    //                 ];
    //             })->values()->all();

    //             //SECTION-N #### LOOKING FOR HEALTH TEST
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'N';
    //             });
    //             $N["Health_Test"] = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO_URL,
    //                 ];
    //             })->values()->all();

    //             //SECTION-O #### CONSULT FROM HOME
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'O';
    //             });
    //             $O["Consult_From_Home"] = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO_URL,
    //                 ];
    //             })->values()->all();

    //             //SECTION-P #### FITNESS TRACKER
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'P';
    //             });
    //             $P["Fitness_Tracker"] = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO_URL,
    //                 ];
    //             })->values()->all();

    //             //SECTION-Q #### UNITING EXPART FOR YOUR HEALTH
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'Q';
    //             });
    //             $Q["Uniting_Expert"] = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO_URL,
    //                 ];
    //             })->values()->all();

    //             //SECTION-#### SURGERY

    //             $surg = DB::table('facility')
    //                 ->join('facility_type', 'facility.DASH_TYPE_ID', '=', 'facility_type.DASH_TYPE_ID')
    //                 // ->where('facility.DN_TAG_SECTION', 'like', '%' . 'SR' . '%')
    //                 ->join('facility_section', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
    //                 ->where('facility_section.DASH_SECTION_ID', 'like', '%' . 'SR' . '%')
    //                 ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
    //                 // ->orderBy('facility_type.DT_POSITION')
    //                 ->orderBy('facility.DN_POSITION')
    //                 ->select(
    //                     'facility.DASH_ID',
    //                     'facility.DN_POSITION',
    //                     'facility.DASH_NAME',
    //                     'facility_type.DASH_TYPE',
    //                     'facility.DASH_TYPE_ID',
    //                     'facility.DN_DESCRIPTION',
    //                     // 'facility.URL_SURGERY_MI',
    //                     'facility.DN_BANNER_URL',
    //                     'facility.DNQA1',
    //                     'facility.DNQA2',
    //                     'facility.DNQA3',
    //                     'facility.DNQA4',
    //                     'facility.DNQA5',
    //                     'facility.DNQA6',
    //                     'facility.DNQA7',
    //                     'facility.DNQA8',
    //                     'facility.DNQA9',

    //                     'facility.DNIMG1',
    //                     'facility.DNIMG2',
    //                     'facility.DNIMG3',
    //                     'facility.DNIMG4',
    //                     'facility.DNIMG5',
    //                     'facility.DNIMG6',
    //                     'facility.DNIMG7',
    //                     'facility.DNIMG8',
    //                     'facility.DNIMG9',
    //                     'facility.DNIMG10',

    //                     'facility.DNBNR1',
    //                     'facility.DNBNR2',
    //                     'facility.DNBNR3',
    //                     'facility.DNBNR4',
    //                     'facility.DNBNR5',
    //                     'facility.DNBNR6',
    //                     'facility.DNBNR7',
    //                     'facility.DNBNR8',
    //                     'facility.DNBNR9',
    //                     'facility.DNBNR10',
    //                 )
    //                 // ->take(10)
    //                 ->get();

    //             $SURG_DTL = $surg->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DASH_SL" => $item->DN_POSITION,
    //                     // "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     // "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_TYPE_ID" => $item->DASH_TYPE_ID,
    //                     "DESCRIPTION" => $item->DN_DESCRIPTION,
    //                     // "PHOTO_URL" => $item->URL_SURGERY_MI,
    //                     // "BANNER_URL" => $item->DN_BANNER_URL,
    //                     "PHOTO_URL1" => $item->DNIMG1,
    //                     "PHOTO_URL2" => $item->DNIMG2,
    //                     "PHOTO_URL3" => $item->DNIMG3,
    //                     "PHOTO_URL4" => $item->DNIMG4,
    //                     "PHOTO_URL5" => $item->DNIMG5,
    //                     "PHOTO_URL6" => $item->DNIMG6,
    //                     "PHOTO_URL7" => $item->DNIMG7,
    //                     "PHOTO_URL8" => $item->DNIMG8,
    //                     "PHOTO_URL9" => $item->DNIMG9,
    //                     "PHOTO_URL10" => $item->DNIMG10,

    //                     "BANNER_URL1" => $item->DNBNR1,
    //                     "BANNER_URL2" => $item->DNBNR2,
    //                     "BANNER_URL3" => $item->DNBNR3,
    //                     "BANNER_URL4" => $item->DNBNR4,
    //                     "BANNER_URL5" => $item->DNBNR5,
    //                     "BANNER_URL6" => $item->DNBNR6,
    //                     "BANNER_URL7" => $item->DNBNR7,
    //                     "BANNER_URL8" => $item->DNBNR8,
    //                     "BANNER_URL9" => $item->DNBNR9,
    //                     "BANNER_URL10" => $item->DNBNR10,

    //                     "Questions" => [
    //                         [
    //                             "QA1" => $item->DNQA1,
    //                             "QA2" => $item->DNQA2,
    //                             "QA3" => $item->DNQA3,
    //                             "QA4" => $item->DNQA4,
    //                             "QA5" => $item->DNQA5,
    //                             "QA6" => $item->DNQA6,
    //                             "QA7" => $item->DNQA7,
    //                             "QA8" => $item->DNQA8,
    //                             "QA9" => $item->DNQA9
    //                         ]
    //                     ]
    //                 ];
    //             })->values()->take(10)->all();




    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'SR';
    //             });
    //             $SGB["Surgery_Banner"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_ID" => $item->PROMO_ID,
    //                     "HEADER_NAME" => $item->HEADER_NAME,
    //                     "PHARMA_ID" => $item->PHARMA_ID,
    //                     "MOBILE_NO" => $item->MOBILE_NO,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "PKG_ID" => $item->PKG_ID,
    //                     "PROMO_NAME" => $item->PROMO_NAME,
    //                     "DESCRIPTION" => $item->DESCRIPTION,
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                     "PROMO_DT" => $item->PROMO_DT,
    //                     "PROMO_VALID" => $item->PROMO_VALID,
    //                 ];
    //             })->take(3)->values()->all();
    //             $SURG["Surgery"] = array_values($SURG_DTL + $SGB);

    //             //SECTION-R #### HEALTH ZONE
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'R';
    //             });
    //             $R["Health_Zone"] = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO_URL,
    //                 ];
    //             })->values()->all();

    //             //SECTION-#### NEAR BY DIAGNOSTIC
    //             $fltr_pharma = $pharma->filter(function ($item) {
    //                 return $item->CLINIC_TYPE === 'Diagnostic';
    //             });
    //             $DDTL = $fltr_pharma->values()->take(25)->all();
    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'CL';
    //             });
    //             $DIAG_BNR["Diagnostic_Banner"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_ID" => $item->PROMO_ID,
    //                     "HEADER_NAME" => $item->HEADER_NAME,
    //                     "PHARMA_ID" => $item->PHARMA_ID,
    //                     "MOBILE_NO" => $item->MOBILE_NO,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "PKG_ID" => $item->PKG_ID,
    //                     "PROMO_NAME" => $item->PROMO_NAME,
    //                     "DESCRIPTION" => $item->DESCRIPTION,
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                     "PROMO_DT" => $item->PROMO_DT,
    //                     "PROMO_VALID" => $item->PROMO_VALID,
    //                 ];
    //             })->values()->all();

    //             $DIAG["Diagnostic"] = array_values($DDTL + $DIAG_BNR);

    //             //SECTION-#### SINGLE TEST
    //             $TST_DTL = DB::table('master_testdata')
    //                 ->join(DB::raw('(SELECT DISTINCT clinic_testdata.TEST_ID,clinic_testdata.HOME_COLLECT, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID,HOME_COLLECT) as clinic_testdata'), function ($join) {
    //                     $join->on('master_testdata.TEST_ID', '=', 'clinic_testdata.TEST_ID');
    //                 })
    //                 ->select('master_testdata.*', 'clinic_testdata.MIN_COST', 'clinic_testdata.HOME_COLLECT')
    //                 ->take(100)->get()->toArray();
    //             // return 
    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'TS';
    //             });
    //             $STB["Test_Banner"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_ID" => $item->PROMO_ID,
    //                     "HEADER_NAME" => $item->HEADER_NAME,
    //                     "PHARMA_ID" => $item->PHARMA_ID,
    //                     "MOBILE_NO" => $item->MOBILE_NO,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "PKG_ID" => $item->PKG_ID,
    //                     "PROMO_NAME" => $item->PROMO_NAME,
    //                     "DESCRIPTION" => $item->DESCRIPTION,
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                     "PROMO_DT" => $item->PROMO_DT,
    //                     "PROMO_VALID" => $item->PROMO_VALID,
    //                 ];
    //             })->take(3)->values()->all();
    //             $TST["Top_Single_Test"] = array_values($TST_DTL + $STB);

    //             //SECTION-B #### FAMILY CARE PACKAGE
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'B';
    //             });
    //             $B_DTL = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO_URL,
    //                     "VIEW1_URL" => $item->VIEW1_URL,
    //                 ];
    //             })->values()->all();
    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'B';
    //             });
    //             $B_BNR["Expert_Care_Women_Banner"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_ID" => $item->PROMO_ID,
    //                     "HEADER_NAME" => $item->HEADER_NAME,
    //                     "PHARMA_ID" => $item->PHARMA_ID,
    //                     "MOBILE_NO" => $item->MOBILE_NO,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "PKG_ID" => $item->PKG_ID,
    //                     "PROMO_NAME" => $item->PROMO_NAME,
    //                     "DESCRIPTION" => $item->DESCRIPTION,
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                     "PROMO_DT" => $item->PROMO_DT,
    //                     "PROMO_VALID" => $item->PROMO_VALID,
    //                 ];
    //             })->values()->all();
    //             $B["Family_Care_Package"] = array_values($B_DTL + $B_BNR);

    //             //SECTION-C #### PROFILE
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'C';
    //             });
    //             $C_DTL = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO_URL,
    //                 ];
    //             })->values()->all();
    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'C';
    //             });
    //             $C_BNR["Profile_Banner"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_ID" => $item->PROMO_ID,
    //                     "HEADER_NAME" => $item->HEADER_NAME,
    //                     "PHARMA_ID" => $item->PHARMA_ID,
    //                     "MOBILE_NO" => $item->MOBILE_NO,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "PKG_ID" => $item->PKG_ID,
    //                     "PROMO_NAME" => $item->PROMO_NAME,
    //                     "DESCRIPTION" => $item->DESCRIPTION,
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                     "PROMO_DT" => $item->PROMO_DT,
    //                     "PROMO_VALID" => $item->PROMO_VALID,
    //                 ];
    //             })->values()->all();
    //             $C["Profile_Test"] = array_values($C_DTL + $C_BNR);

    //             // //SECTION-F #### SCAN
    //             $data1 = DB::table('dashboard')
    //                 ->join('master_testdata', 'dashboard.DASH_NAME', '=', 'master_testdata.TEST_CATG')
    //                 ->select(
    //                     'master_testdata.*',
    //                     'dashboard.DASH_ID',
    //                     // 'dashboard.DIS_ID',
    //                     'dashboard.DASH_SECTION_ID',
    //                     'dashboard.DASH_SECTION_NAME',
    //                     'dashboard.DASH_NAME',
    //                     // 'dashboard.DASH_TYPE',
    //                     // 'dashboard.DASH_DESCRIPTION',
    //                     'dashboard.PHOTO1_URL',
    //                     'dashboard.BANNER_URL',

    //                 )
    //                 ->where('dashboard.DASH_SECTION_ID', '=', 'F')
    //                 ->orderby('dashboard.POSITION')
    //                 ->get();

    //             // return $data1;

    //             $F_DTL = [];
    //             foreach ($data1->pluck('DASH_ID')->unique() as $catg) {
    //                 $filteredArray = $data1->where('DASH_ID', $catg);
    //                 $organDetails = [];
    //                 foreach ($filteredArray as $item) {
    //                     $organID = $item->ORGAN_ID;
    //                     if (!isset($organDetails[$organID])) {
    //                         $organDetails[$organID] = [
    //                             "ORGAN_ID" => $organID,
    //                             "ORGAN_NAME" => $item->ORGAN_NAME,
    //                             "ORGAN_URL" => $item->ORGAN_URL,
    //                         ];
    //                     }
    //                 }
    //                 $F_DTL[] = [
    //                     "DASH_SECTION_NAME" => $filteredArray->first()->DASH_SECTION_NAME,
    //                     "DASH_ID" => $filteredArray->first()->DASH_ID,
    //                     "DASH_NAME" => $filteredArray->first()->DASH_NAME,
    //                     "PHOTO_URL" => $filteredArray->first()->PHOTO1_URL,
    //                     "BANNER_URL" => $filteredArray->first()->BANNER_URL,
    //                     "ORGANS" => array_values($organDetails),
    //                 ];
    //             }

    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'F';
    //             });
    //             $F_BNR["Popular_Scan_Banner"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_ID" => $item->PROMO_ID,
    //                     "HEADER_NAME" => $item->HEADER_NAME,
    //                     "PHARMA_ID" => $item->PHARMA_ID,
    //                     "MOBILE_NO" => $item->MOBILE_NO,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "PKG_ID" => $item->PKG_ID,
    //                     "PROMO_NAME" => $item->PROMO_NAME,
    //                     "DESCRIPTION" => $item->DESCRIPTION,
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                     "PROMO_DT" => $item->PROMO_DT,
    //                     "PROMO_VALID" => $item->PROMO_VALID,
    //                 ];
    //             })->values()->all();
    //             $F["Popular_Scan"] = array_values($F_DTL + $F_BNR);

    //             //SECTION-#### DASHBOARD BANNER
    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'MD';
    //             });
    //             $DASH_BNR["Advertisement_Banner"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_ID" => $item->PROMO_ID,
    //                     "HEADER_NAME" => $item->HEADER_NAME,
    //                     "PHARMA_ID" => $item->PHARMA_ID,
    //                     "MOBILE_NO" => $item->MOBILE_NO,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "PKG_ID" => $item->PKG_ID,
    //                     "PROMO_NAME" => $item->PROMO_NAME,
    //                     "DESCRIPTION" => $item->DESCRIPTION,
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                     "PROMO_DT" => $item->PROMO_DT,
    //                     "PROMO_VALID" => $item->PROMO_VALID,
    //                 ];
    //             })->values()->all();

    //             // //SECTION-#### NEAR BY HOSPITAL/NURSHING HOME
    //             $fltr_pharma = $pharma->filter(function ($item) {
    //                 return $item->CLINIC_TYPE === 'Hospital';
    //             });
    //             $HDTL = $fltr_pharma->values()->take(25)->all();
    //             $fltr_promo_bnr = $promo_bnr->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'HS';
    //             });
    //             $HOSP_BNR["Hospital_Banner"] = $fltr_promo_bnr->map(function ($item) {
    //                 return [
    //                     "PROMO_ID" => $item->PROMO_ID,
    //                     "HEADER_NAME" => $item->HEADER_NAME,
    //                     "PHARMA_ID" => $item->PHARMA_ID,
    //                     "MOBILE_NO" => $item->MOBILE_NO,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "PKG_ID" => $item->PKG_ID,
    //                     "PROMO_NAME" => $item->PROMO_NAME,
    //                     "DESCRIPTION" => $item->DESCRIPTION,
    //                     "PROMO_URL" => $item->PROMO_URL,
    //                     "PROMO_DT" => $item->PROMO_DT,
    //                     "PROMO_VALID" => $item->PROMO_VALID,
    //                 ];
    //             })->values()->all();

    //             $HOSP["Hospital"] = array_values($HDTL + $HOSP_BNR);

    //             //SECTION-I #### DERMATOLOGY
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'I';
    //             });
    //             $I_DTL = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO1_URL,
    //                     "PHOTO1_URL" => $item->PHOTO_URL,
    //                     "QA1" => $item->QA1,
    //                     "QA2" => $item->QA2,
    //                     "QA3" => $item->QA3,
    //                     "QA4" => $item->QA4,
    //                     "QA5" => $item->QA5,
    //                     "QA6" => $item->QA6,
    //                     "QA7" => $item->QA7,
    //                     "QA8" => $item->QA8,
    //                     "QA9" => $item->QA9,

    //                 ];
    //             })->values()->all();
    //             $groupedData = [];
    //             foreach ($I_DTL as $row2) {
    //                 if (!isset($groupedData[$row2['DASH_SECTION_ID']])) {
    //                     $groupedData[$row2['DASH_SECTION_ID']] = [
    //                         "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
    //                         "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
    //                         "PHOTO_URL" => $row2['PHOTO_URL'],
    //                         "DETAILS" => []
    //                     ];
    //                 }
    //                 $groupedData[$row2['DASH_SECTION_ID']]['DETAILS'][] = [
    //                     "DASH_ID" => $row2['DASH_ID'],
    //                     "DIS_ID" => $row2['DIS_ID'],
    //                     "SYM_ID" => $row2['SYM_ID'],
    //                     "DASH_NAME" => $row2['DASH_NAME'],
    //                     "DASH_DESCRIPTION" => $row2['DASH_DESCRIPTION'],
    //                     "PHOTO_URL" => $row2['PHOTO1_URL'],
    //                     "Questions" => [
    //                         [
    //                             "QA1" => $row2['QA1'],
    //                             "QA2" => $row2['QA2'],
    //                             "QA3" => $row2['QA3'],
    //                             "QA4" => $row2['QA4'],
    //                             "QA5" => $row2['QA5'],
    //                             "QA6" => $row2['QA6'],
    //                             "QA7" => $row2['QA7'],
    //                             "QA8" => $row2['QA8'],
    //                             "QA9" => $row2['QA9']
    //                         ]
    //                     ]

    //                 ];
    //             }
    //             $I["Dermatology"] = array_values($groupedData);

    //             //SECTION-J #### DENTAL
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'J';
    //             });
    //             $J_DTL = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO1_URL,
    //                     "PHOTO1_URL" => $item->PHOTO_URL,
    //                     "QA1" => $item->QA1,
    //                     "QA2" => $item->QA2,
    //                     "QA3" => $item->QA3,
    //                     "QA4" => $item->QA4,
    //                     "QA5" => $item->QA5,
    //                     "QA6" => $item->QA6,
    //                     "QA7" => $item->QA7,
    //                     "QA8" => $item->QA8,
    //                     "QA9" => $item->QA9,
    //                 ];
    //             })->values()->all();
    //             $groupedData = [];
    //             foreach ($J_DTL as $row2) {
    //                 if (!isset($groupedData[$row2['DASH_SECTION_ID']])) {
    //                     $groupedData[$row2['DASH_SECTION_ID']] = [
    //                         "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
    //                         "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
    //                         "PHOTO_URL" => $row2['PHOTO_URL'],
    //                         "DETAILS" => []
    //                     ];
    //                 }
    //                 $groupedData[$row2['DASH_SECTION_ID']]['DETAILS'][] = [
    //                     "DASH_ID" => $row2['DASH_ID'],
    //                     "DIS_ID" => $row2['DIS_ID'],
    //                     "SYM_ID" => $row2['SYM_ID'],
    //                     "DASH_NAME" => $row2['DASH_NAME'],
    //                     "DASH_DESCRIPTION" => $row2['DASH_DESCRIPTION'],
    //                     "PHOTO_URL" => $row2['PHOTO1_URL'],
    //                     "Questions" => [
    //                         [
    //                             "QA1" => $row2['QA1'],
    //                             "QA2" => $row2['QA2'],
    //                             "QA3" => $row2['QA3'],
    //                             "QA4" => $row2['QA4'],
    //                             "QA5" => $row2['QA5'],
    //                             "QA6" => $row2['QA6'],
    //                             "QA7" => $row2['QA7'],
    //                             "QA8" => $row2['QA8'],
    //                             "QA9" => $row2['QA9']
    //                         ]
    //                     ]
    //                 ];
    //             }
    //             $J["Dental"] = array_values($groupedData);

    //             //SECTION-K #### EYE CARE
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'K';
    //             });
    //             $K_DTL = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO1_URL,
    //                     "PHOTO1_URL" => $item->PHOTO_URL,
    //                     "QA1" => $item->QA1,
    //                     "QA2" => $item->QA2,
    //                     "QA3" => $item->QA3,
    //                     "QA4" => $item->QA4,
    //                     "QA5" => $item->QA5,
    //                     "QA6" => $item->QA6,
    //                     "QA7" => $item->QA7,
    //                     "QA8" => $item->QA8,
    //                     "QA9" => $item->QA9,
    //                 ];
    //             })->values()->all();
    //             $groupedData = [];
    //             foreach ($K_DTL as $row2) {
    //                 if (!isset($groupedData[$row2['DASH_SECTION_ID']])) {
    //                     $groupedData[$row2['DASH_SECTION_ID']] = [
    //                         "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
    //                         "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
    //                         "PHOTO_URL" => $row2['PHOTO_URL'],
    //                         "DETAILS" => []
    //                     ];
    //                 }
    //                 $groupedData[$row2['DASH_SECTION_ID']]['DETAILS'][] = [
    //                     "DASH_ID" => $row2['DASH_ID'],
    //                     "DIS_ID" => $row2['DIS_ID'],
    //                     "SYM_ID" => $row2['SYM_ID'],
    //                     "DASH_NAME" => $row2['DASH_NAME'],
    //                     "DASH_DESCRIPTION" => $row2['DASH_DESCRIPTION'],
    //                     "PHOTO_URL" => $row2['PHOTO1_URL'],
    //                     "Questions" => [
    //                         [
    //                             "QA1" => $row2['QA1'],
    //                             "QA2" => $row2['QA2'],
    //                             "QA3" => $row2['QA3'],
    //                             "QA4" => $row2['QA4'],
    //                             "QA5" => $row2['QA5'],
    //                             "QA6" => $row2['QA6'],
    //                             "QA7" => $row2['QA7'],
    //                             "QA8" => $row2['QA8'],
    //                             "QA9" => $row2['QA9']
    //                         ]
    //                     ]
    //                 ];
    //             }
    //             $K["Eye_Care"] = array_values($groupedData);

    //             //SECTION-L #### BONE AND JOINT CARE
    //             $fltr_dash = $dash->filter(function ($item) {
    //                 return $item->DASH_SECTION_ID === 'L';
    //             });
    //             $L_DTL = $fltr_dash->map(function ($item) {
    //                 return [
    //                     "DASH_ID" => $item->DASH_ID,
    //                     "DIS_ID" => $item->DIS_ID,
    //                     "SYM_ID" => $item->SYM_ID,
    //                     "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
    //                     "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
    //                     "DASH_NAME" => $item->DASH_NAME,
    //                     "DASH_TYPE" => $item->DASH_TYPE,
    //                     "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
    //                     "PHOTO_URL" => $item->PHOTO1_URL,
    //                     "PHOTO1_URL" => $item->PHOTO_URL,
    //                     "QA1" => $item->QA1,
    //                     "QA2" => $item->QA2,
    //                     "QA3" => $item->QA3,
    //                     "QA4" => $item->QA4,
    //                     "QA5" => $item->QA5,
    //                     "QA6" => $item->QA6,
    //                     "QA7" => $item->QA7,
    //                     "QA8" => $item->QA8,
    //                     "QA9" => $item->QA9,
    //                 ];
    //             })->values()->all();
    //             $groupedData = [];
    //             foreach ($L_DTL as $row2) {
    //                 if (!isset($groupedData[$row2['DASH_SECTION_ID']])) {
    //                     $groupedData[$row2['DASH_SECTION_ID']] = [
    //                         "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
    //                         "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
    //                         "PHOTO_URL" => $row2['PHOTO_URL'],
    //                         "DETAILS" => []
    //                     ];
    //                 }
    //                 $groupedData[$row2['DASH_SECTION_ID']]['DETAILS'][] = [
    //                     "DASH_ID" => $row2['DASH_ID'],
    //                     "DIS_ID" => $row2['DIS_ID'],
    //                     "SYM_ID" => $row2['SYM_ID'],
    //                     "DASH_NAME" => $row2['DASH_NAME'],
    //                     "DASH_DESCRIPTION" => $row2['DASH_DESCRIPTION'],
    //                     "PHOTO_URL" => $row2['PHOTO1_URL'],
    //                     "Questions" => [
    //                         [
    //                             "QA1" => $row2['QA1'],
    //                             "QA2" => $row2['QA2'],
    //                             "QA3" => $row2['QA3'],
    //                             "QA4" => $row2['QA4'],
    //                             "QA5" => $row2['QA5'],
    //                             "QA6" => $row2['QA6'],
    //                             "QA7" => $row2['QA7'],
    //                             "QA8" => $row2['QA8'],
    //                             "QA9" => $row2['QA9']
    //                         ]
    //                     ]
    //                 ];
    //             }
    //             $L["Bone_Joint_Care"] = array_values($groupedData);

    //             //SECTION-AJ #### 2nd Opinion

    //             $I_DTL = DB::table('facility_section')
    //                 ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
    //                 ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
    //                 ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
    //                 ->where('facility_type.DT_TAG_SECTION', 'like', '%AM%')
    //                 ->orderBy('facility_type.DT_POSITION')
    //                 // ->orderBy('facility.DN_POSITION')
    //                 ->get();
    //             // return $I_DTL;

    //             $groupedData = [];
    //             foreach ($I_DTL as $row2) {
    //                 $DsID = 'AM';
    //                 $Dsname = '2nd Opinion';

    //                 if (!isset($groupedData[$DsID])) {
    //                     $groupedData[$DsID] = [
    //                         "DASH_SECTION_ID" => $DsID,
    //                         "DASH_SECTION_NAME" => $Dsname,
    //                         "DESCRIPTION" => $row2->DS_DESCRIPTION,
    //                         "PHOTO_URL1" => $row2->DSIMG1,
    //                         "PHOTO_URL2" => $row2->DSIMG2,
    //                         "PHOTO_URL3" => $row2->DSIMG3,
    //                         "PHOTO_URL4" => $row2->DSIMG4,
    //                         "PHOTO_URL5" => $row2->DSIMG5,
    //                         "PHOTO_URL6" => $row2->DSIMG6,
    //                         "PHOTO_URL7" => $row2->DSIMG7,
    //                         "PHOTO_URL8" => $row2->DSIMG8,
    //                         "PHOTO_URL9" => $row2->DSIMG9,
    //                         "PHOTO_URL10" => $row2->DSIMG10,

    //                         "BANNER_URL1" => $row2->DSBNR1,
    //                         "BANNER_URL2" => $row2->DSBNR2,
    //                         "BANNER_URL3" => $row2->DSBNR3,
    //                         "BANNER_URL4" => $row2->DSBNR4,
    //                         "BANNER_URL5" => $row2->DSBNR5,
    //                         "BANNER_URL6" => $row2->DSBNR6,
    //                         "BANNER_URL7" => $row2->DSBNR7,
    //                         "BANNER_URL8" => $row2->DSBNR8,
    //                         "BANNER_URL9" => $row2->DSBNR9,
    //                         "BANNER_URL10" => $row2->DSBNR10,
    //                         "DASH_TYPE" => []
    //                     ];
    //                 }

    //                 if (!isset($groupedData[$DsID]['DASH_TYPE'][$row2->DASH_TYPE])) {
    //                     $groupedData[$DsID]['DASH_TYPE'][$row2->DASH_TYPE] = [
    //                         "DASH_SECTION_ID" => $DsID,
    //                         "DASH_SECTION_NAME" => $Dsname,
    //                         "DASH_TYPE" => $row2->DASH_TYPE,
    //                         "DESCRIPTION" => $row2->DT_DESCRIPTION,
    //                         // "PHOTO_URL" => $row2->URL_IPD_MG,
    //                         // "PHOTO_URL1" => $row2->DNIMG1,

    //                         "PHOTO_URL1" => $row2->DTIMG1,
    //                         "PHOTO_URL2" => $row2->DTIMG2,
    //                         "PHOTO_URL3" => $row2->DTIMG3,
    //                         "PHOTO_URL4" => $row2->DTIMG4,
    //                         "PHOTO_URL5" => $row2->DTIMG5,
    //                         "PHOTO_URL6" => $row2->DTIMG6,
    //                         "PHOTO_URL7" => $row2->DTIMG7,
    //                         "PHOTO_URL8" => $row2->DTIMG8,
    //                         "PHOTO_URL9" => $row2->DTIMG9,
    //                         "PHOTO_URL10" => $row2->DTIMG10,

    //                         "BANNER_URL1" => $row2->DTBNR1,
    //                         "BANNER_URL2" => $row2->DTBNR2,
    //                         "BANNER_URL3" => $row2->DTBNR3,
    //                         "BANNER_URL4" => $row2->DTBNR4,
    //                         "BANNER_URL5" => $row2->DTBNR5,
    //                         "BANNER_URL6" => $row2->DTBNR6,
    //                         "BANNER_URL7" => $row2->DTBNR7,
    //                         "BANNER_URL8" => $row2->DTBNR8,
    //                         "BANNER_URL9" => $row2->DTBNR9,
    //                         "BANNER_URL10" => $row2->DTBNR10,
    //                         "FACILITY_DETAILS" => []
    //                     ];
    //                 }

    //                 $groupedData[$DsID]['DASH_TYPE'][$row2->DASH_TYPE]['FACILITY_DETAILS'][] = [
    //                     "DASH_ID" => $row2->DASH_ID,
    //                     // "DIS_ID" => $row2->DIS_ID,
    //                     // "SYM_ID" => $row2->SYM_ID,
    //                     "DASH_NAME" => $row2->DASH_NAME,
    //                     "DASH_TYPE" => $row2->DASH_TYPE,
    //                     "DESCRIPTION" => $row2->DN_DESCRIPTION,
    //                     // "PHOTO_URL" => $row2->URL_IPD_MI,
    //                     // "BANNER_URL" => $row2->DN_BANNER_URL,
    //                     "PHOTO_URL1" => $row2->DNIMG1,
    //                     "PHOTO_URL2" => $row2->DNIMG2,
    //                     "PHOTO_URL3" => $row2->DNIMG3,
    //                     "PHOTO_URL4" => $row2->DNIMG4,
    //                     "PHOTO_URL5" => $row2->DNIMG5,
    //                     "PHOTO_URL6" => $row2->DNIMG6,
    //                     "PHOTO_URL7" => $row2->DNIMG7,
    //                     "PHOTO_URL8" => $row2->DNIMG8,
    //                     "PHOTO_URL9" => $row2->DNIMG9,
    //                     "PHOTO_URL10" => $row2->DNIMG10,

    //                     "BANNER_URL1" => $row2->DNBNR1,
    //                     "BANNER_URL2" => $row2->DNBNR2,
    //                     "BANNER_URL3" => $row2->DNBNR3,
    //                     "BANNER_URL4" => $row2->DNBNR4,
    //                     "BANNER_URL5" => $row2->DNBNR5,
    //                     "BANNER_URL6" => $row2->DNBNR6,
    //                     "BANNER_URL7" => $row2->DNBNR7,
    //                     "BANNER_URL8" => $row2->DNBNR8,
    //                     "BANNER_URL9" => $row2->DNBNR9,
    //                     "BANNER_URL10" => $row2->DNBNR10,

    //                 ];
    //             }

    //             // $AH["IPD_Facilities"] = array_values($groupedData);

    //             $AJ["Second_Opinion"] = array_values($groupedData);


    //             //SECTION-AQ #### IPD Section

    //             $I_DTL = DB::table('facility_section')
    //                 ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
    //                 ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
    //                 ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
    //                 ->where('facility_section.DASH_SECTION_ID', 'AH')
    //                 ->orderBy('facility_type.DT_POSITION')
    //                 // ->orderBy('facility.DN_POSITION')
    //                 ->get();


    //             $groupedData = [];
    //             foreach ($I_DTL as $row2) {
    //                 if (!isset($groupedData[$row2->DASH_SECTION_ID])) {
    //                     $groupedData[$row2->DASH_SECTION_ID] = [
    //                         "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
    //                         "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
    //                         "DESCRIPTION" => $row2->DS_DESCRIPTION,
    //                         "PHOTO_URL1" => $row2->DSIMG1,
    //                         "PHOTO_URL2" => $row2->DSIMG2,
    //                         "PHOTO_URL3" => $row2->DSIMG3,
    //                         "PHOTO_URL4" => $row2->DSIMG4,
    //                         "PHOTO_URL5" => $row2->DSIMG5,
    //                         "PHOTO_URL6" => $row2->DSIMG6,
    //                         "PHOTO_URL7" => $row2->DSIMG7,
    //                         "PHOTO_URL8" => $row2->DSIMG8,
    //                         "PHOTO_URL9" => $row2->DSIMG9,
    //                         "PHOTO_URL10" => $row2->DSIMG10,

    //                         "BANNER_URL1" => $row2->DSBNR1,
    //                         "BANNER_URL2" => $row2->DSBNR2,
    //                         "BANNER_URL3" => $row2->DSBNR3,
    //                         "BANNER_URL4" => $row2->DSBNR4,
    //                         "BANNER_URL5" => $row2->DSBNR5,
    //                         "BANNER_URL6" => $row2->DSBNR6,
    //                         "BANNER_URL7" => $row2->DSBNR7,
    //                         "BANNER_URL8" => $row2->DSBNR8,
    //                         "BANNER_URL9" => $row2->DSBNR9,
    //                         "BANNER_URL10" => $row2->DSBNR10,
    //                         "DASH_TYPE" => []
    //                     ];
    //                 }

    //                 if (!isset($groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE])) {
    //                     $groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE] = [
    //                         "DASH_SECTION_ID" => $row2->DASH_SECTION_ID,
    //                         "DASH_SECTION_NAME" => $row2->DASH_SECTION_NAME,
    //                         "DASH_TYPE" => $row2->DASH_TYPE,
    //                         "DESCRIPTION" => $row2->DT_DESCRIPTION,
    //                         // "PHOTO_URL" => $row2->URL_IPD_MG,
    //                         // "PHOTO_URL1" => $row2->DNIMG1,

    //                         "PHOTO_URL1" => $row2->DTIMG1,

    //                         "PHOTO_URL2" => $row2->DTIMG2,
    //                         "PHOTO_URL3" => $row2->DTIMG3,
    //                         "PHOTO_URL4" => $row2->DTIMG4,
    //                         "PHOTO_URL5" => $row2->DTIMG5,
    //                         "PHOTO_URL6" => $row2->DTIMG6,
    //                         "PHOTO_URL7" => $row2->DTIMG7,
    //                         "PHOTO_URL8" => $row2->DTIMG8,
    //                         "PHOTO_URL9" => $row2->DTIMG9,
    //                         "PHOTO_URL10" => $row2->DTIMG10,

    //                         "BANNER_URL1" => $row2->DTBNR1,
    //                         "BANNER_URL2" => $row2->DTBNR2,
    //                         "BANNER_URL3" => $row2->DTBNR3,
    //                         "BANNER_URL4" => $row2->DTBNR4,
    //                         "BANNER_URL5" => $row2->DTBNR5,
    //                         "BANNER_URL6" => $row2->DTBNR6,
    //                         "BANNER_URL7" => $row2->DTBNR7,
    //                         "BANNER_URL8" => $row2->DTBNR8,
    //                         "BANNER_URL9" => $row2->DTBNR9,
    //                         "BANNER_URL10" => $row2->DTBNR10,
    //                         "FACILITY_DETAILS" => []
    //                     ];
    //                 }

    //                 $groupedData[$row2->DASH_SECTION_ID]['DASH_TYPE'][$row2->DASH_TYPE]['FACILITY_DETAILS'][] = [
    //                     "DASH_ID" => $row2->DASH_ID,
    //                     // "DIS_ID" => $row2->DIS_ID,
    //                     // "SYM_ID" => $row2->SYM_ID,
    //                     "DASH_NAME" => $row2->DASH_NAME,
    //                     "DASH_TYPE" => $row2->DASH_TYPE,
    //                     "DESCRIPTION" => $row2->DN_DESCRIPTION,
    //                     // "PHOTO_URL" => $row2->URL_IPD_MI,
    //                     // "BANNER_URL" => $row2->DN_BANNER_URL,
    //                     "PHOTO_URL1" => $row2->DNIMG1,
    //                     "PHOTO_URL2" => $row2->DNIMG2,
    //                     "PHOTO_URL3" => $row2->DNIMG3,
    //                     "PHOTO_URL4" => $row2->DNIMG4,
    //                     "PHOTO_URL5" => $row2->DNIMG5,
    //                     "PHOTO_URL6" => $row2->DNIMG6,
    //                     "PHOTO_URL7" => $row2->DNIMG7,
    //                     "PHOTO_URL8" => $row2->DNIMG8,
    //                     "PHOTO_URL9" => $row2->DNIMG9,
    //                     "PHOTO_URL10" => $row2->DNIMG10,

    //                     "BANNER_URL1" => $row2->DNBNR1,
    //                     "BANNER_URL2" => $row2->DNBNR2,
    //                     "BANNER_URL3" => $row2->DNBNR3,
    //                     "BANNER_URL4" => $row2->DNBNR4,
    //                     "BANNER_URL5" => $row2->DNBNR5,
    //                     "BANNER_URL6" => $row2->DNBNR6,
    //                     "BANNER_URL7" => $row2->DNBNR7,
    //                     "BANNER_URL8" => $row2->DNBNR8,
    //                     "BANNER_URL9" => $row2->DNBNR9,
    //                     "BANNER_URL10" => $row2->DNBNR10,

    //                 ];
    //             }

    //             $AH["IPD_Facilities"] = array_values($groupedData);


    //             $data = $A + $DASH_A + $SPLST + $SYM + $CLINIC + $E + $D + $M + $N + $O + $P + $Q + $SURG + $R + $DIAG + $TST + $B + $C + $F + $DASH_BNR + $HOSP + $I + $J + $K + $L + $AJ + $AH;

    //             $response = ['Success' => true, 'data' => $data, 'code' => 200];
    //         } else {
    //             $response = ['Success' => false, 'Message' => 'Invalid input parameter', 'code' => 422];
    //         }
    //     } else {
    //         $response = ['Success' => false, 'Message' => 'Method Not Allowed', 'code' => 200];
    //     }
    //     return $response;
    // }



    function dashboard(Request $request)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $request->json()->all();
            if (isset($input['LATITUDE']) && isset($input['LONGITUDE'])) {
                $latt = $input['LATITUDE'];
                $lont = $input['LONGITUDE'];

                $promo_bnr = DB::table('promo_banner')->where('STATUS', 'Active')->get();
                $dash = DB::table('dashboard')->where('CATEGORY', 'like', '%' . 'M' . '%')->where('STATUS', 'Active')->orderby('DASH_SL')->orderby('GR_POSITION')->get();
                $pharma = DB::table('pharmacy')
                    ->leftjoin('dr_availablity', 'pharmacy.PHARMA_ID', '=', 'dr_availablity.PHARMA_ID')
                    // ->leftjoin('clinic_testdata', 'pharmacy.PHARMA_ID', '=', 'clinic_testdata.PHARMA_ID')
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
                    return $item->DASH_SECTION_ID === 'A';
                });
                $DASH_A["Dashboard"] = $fltr_dash->map(function ($item) {
                    return [
                        "DASH_ID" => $item->DASH_ID,
                        "DASH_SECTION_ID" => $item->FACILITY_ID,
                        "DASH_SECTION_NAME" => $item->DASH_NAME,
                        "DESCRIPTION" => $item->DASH_SECTION_DESC,
                        "PHOTO_URL" => $item->DSIMG1,
                        "BANNER_URL" => $item->DSBNR1,
                    ];
                })->values()->all();

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
                $SPLST["Specialist"] = array_values($SPLST_DTL + $SPB);

                //SECTION-#### SYMPTOMS
                $SYM_DTL = DB::table('symptoms')->select('SYM_ID', 'SYM_NAME', 'DIS_ID', 'DIS_CATEGORY', 'DASH_PHOTO as PHOTO_URL')->orderby('SYM_SL')->take(10)->get()->toArray();
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

                //SECTION-N #### LOOKING FOR HEALTH TEST
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'N';
                });
                $N["Health_Test"] = $fltr_dash->map(function ($item) {
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

                //SECTION-O #### CONSULT FROM HOME
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'O';
                });
                $O["Consult_From_Home"] = $fltr_dash->map(function ($item) {
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

                //SECTION-P #### FITNESS TRACKER
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'P';
                });
                $P["Fitness_Tracker"] = $fltr_dash->map(function ($item) {
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

                //SECTION-Q #### UNITING EXPART FOR YOUR HEALTH
                $fltr_dash = $dash->filter(function ($item) {
                    return $item->DASH_SECTION_ID === 'Q';
                });
                $Q["Uniting_Expert"] = $fltr_dash->map(function ($item) {
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

                //SECTION-#### SURGERY

                $surg = DB::table('facility')
                    ->join('facility_type', 'facility.DASH_TYPE_ID', '=', 'facility_type.DASH_TYPE_ID')
                    // ->where('facility.DN_TAG_SECTION', 'like', '%' . 'SR' . '%')
                    ->join('facility_section', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->where('facility_section.DASH_SECTION_ID', 'like', '%' . 'SR' . '%')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    // ->orderBy('facility_type.DT_POSITION')
                    ->orderBy('facility.DN_POSITION')
                    ->select(
                        'facility.DASH_ID',
                        'facility.DN_POSITION',
                        'facility.DASH_NAME',
                        'facility_type.DASH_TYPE',
                        'facility.DASH_TYPE_ID',
                        'facility.DN_DESCRIPTION',
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
                        // "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
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
                $TST_DTL = DB::table('master_testdata')
                    ->join(DB::raw('(SELECT DISTINCT clinic_testdata.TEST_ID,clinic_testdata.HOME_COLLECT, MIN(clinic_testdata.COST) as MIN_COST FROM clinic_testdata GROUP BY TEST_ID,HOME_COLLECT) as clinic_testdata'), function ($join) {
                        $join->on('master_testdata.TEST_ID', '=', 'clinic_testdata.TEST_ID');
                    })
                    ->select('master_testdata.*', 'clinic_testdata.MIN_COST', 'clinic_testdata.HOME_COLLECT')
                    ->take(100)->get()->toArray();
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
                $TST["Top_Single_Test"] = array_values($TST_DTL + $STB);

                //SECTION-B #### FAMILY CARE PACKAGE
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
                        "VIEW1_URL" => $item->VIEW1_URL,
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

                // //SECTION-F #### SCAN
                $data1 = DB::table('dashboard')
                    ->join('master_testdata', 'dashboard.DASH_NAME', '=', 'master_testdata.TEST_CATG')
                    ->select(
                        'master_testdata.*',
                        'dashboard.DASH_ID',
                        // 'dashboard.DIS_ID',
                        'dashboard.DASH_SECTION_ID',
                        'dashboard.DASH_SECTION_NAME',
                        'dashboard.DASH_NAME',
                        // 'dashboard.DASH_TYPE',
                        // 'dashboard.DASH_DESCRIPTION',
                        'dashboard.PHOTO1_URL',
                        'dashboard.BANNER_URL',

                    )
                    ->where('dashboard.DASH_SECTION_ID', '=', 'F')
                    ->orderby('dashboard.POSITION')
                    ->get();

                // return $data1;

                $F_DTL = [];
                foreach ($data1->pluck('DASH_ID')->unique() as $catg) {
                    $filteredArray = $data1->where('DASH_ID', $catg);
                    $organDetails = [];
                    foreach ($filteredArray as $item) {
                        $organID = $item->ORGAN_ID;
                        if (!isset($organDetails[$organID])) {
                            $organDetails[$organID] = [
                                "ORGAN_ID" => $organID,
                                "ORGAN_NAME" => $item->ORGAN_NAME,
                                "ORGAN_URL" => $item->ORGAN_URL,
                            ];
                        }
                    }
                    $F_DTL[] = [
                        "DASH_SECTION_NAME" => $filteredArray->first()->DASH_SECTION_NAME,
                        "DASH_ID" => $filteredArray->first()->DASH_ID,
                        "DASH_NAME" => $filteredArray->first()->DASH_NAME,
                        "PHOTO_URL" => $filteredArray->first()->PHOTO1_URL,
                        "BANNER_URL" => $filteredArray->first()->BANNER_URL,
                        "ORGANS" => array_values($organDetails),
                    ];
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
                        "DASH_ID" => $item->DASH_ID,
                        "DIS_ID" => $item->DIS_ID,
                        "SYM_ID" => $item->SYM_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO1_URL,
                        "PHOTO1_URL" => $item->PHOTO_URL,
                        "QA1" => $item->QA1,
                        "QA2" => $item->QA2,
                        "QA3" => $item->QA3,
                        "QA4" => $item->QA4,
                        "QA5" => $item->QA5,
                        "QA6" => $item->QA6,
                        "QA7" => $item->QA7,
                        "QA8" => $item->QA8,
                        "QA9" => $item->QA9,

                    ];
                })->values()->all();
                $groupedData = [];
                foreach ($I_DTL as $row2) {
                    if (!isset($groupedData[$row2['DASH_SECTION_ID']])) {
                        $groupedData[$row2['DASH_SECTION_ID']] = [
                            "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
                            "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
                            "PHOTO_URL" => $row2['PHOTO_URL'],
                            "DETAILS" => []
                        ];
                    }
                    $groupedData[$row2['DASH_SECTION_ID']]['DETAILS'][] = [
                        "DASH_ID" => $row2['DASH_ID'],
                        "DIS_ID" => $row2['DIS_ID'],
                        "SYM_ID" => $row2['SYM_ID'],
                        "DASH_NAME" => $row2['DASH_NAME'],
                        "DASH_DESCRIPTION" => $row2['DASH_DESCRIPTION'],
                        "PHOTO_URL" => $row2['PHOTO1_URL'],
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
                        "DASH_ID" => $item->DASH_ID,
                        "DIS_ID" => $item->DIS_ID,
                        "SYM_ID" => $item->SYM_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO1_URL,
                        "PHOTO1_URL" => $item->PHOTO_URL,
                        "QA1" => $item->QA1,
                        "QA2" => $item->QA2,
                        "QA3" => $item->QA3,
                        "QA4" => $item->QA4,
                        "QA5" => $item->QA5,
                        "QA6" => $item->QA6,
                        "QA7" => $item->QA7,
                        "QA8" => $item->QA8,
                        "QA9" => $item->QA9,
                    ];
                })->values()->all();
                $groupedData = [];
                foreach ($J_DTL as $row2) {
                    if (!isset($groupedData[$row2['DASH_SECTION_ID']])) {
                        $groupedData[$row2['DASH_SECTION_ID']] = [
                            "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
                            "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
                            "PHOTO_URL" => $row2['PHOTO_URL'],
                            "DETAILS" => []
                        ];
                    }
                    $groupedData[$row2['DASH_SECTION_ID']]['DETAILS'][] = [
                        "DASH_ID" => $row2['DASH_ID'],
                        "DIS_ID" => $row2['DIS_ID'],
                        "SYM_ID" => $row2['SYM_ID'],
                        "DASH_NAME" => $row2['DASH_NAME'],
                        "DASH_DESCRIPTION" => $row2['DASH_DESCRIPTION'],
                        "PHOTO_URL" => $row2['PHOTO1_URL'],
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
                        "DASH_ID" => $item->DASH_ID,
                        "DIS_ID" => $item->DIS_ID,
                        "SYM_ID" => $item->SYM_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO1_URL,
                        "PHOTO1_URL" => $item->PHOTO_URL,
                        "QA1" => $item->QA1,
                        "QA2" => $item->QA2,
                        "QA3" => $item->QA3,
                        "QA4" => $item->QA4,
                        "QA5" => $item->QA5,
                        "QA6" => $item->QA6,
                        "QA7" => $item->QA7,
                        "QA8" => $item->QA8,
                        "QA9" => $item->QA9,
                    ];
                })->values()->all();
                $groupedData = [];
                foreach ($K_DTL as $row2) {
                    if (!isset($groupedData[$row2['DASH_SECTION_ID']])) {
                        $groupedData[$row2['DASH_SECTION_ID']] = [
                            "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
                            "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
                            "PHOTO_URL" => $row2['PHOTO_URL'],
                            "DETAILS" => []
                        ];
                    }
                    $groupedData[$row2['DASH_SECTION_ID']]['DETAILS'][] = [
                        "DASH_ID" => $row2['DASH_ID'],
                        "DIS_ID" => $row2['DIS_ID'],
                        "SYM_ID" => $row2['SYM_ID'],
                        "DASH_NAME" => $row2['DASH_NAME'],
                        "DASH_DESCRIPTION" => $row2['DASH_DESCRIPTION'],
                        "PHOTO_URL" => $row2['PHOTO1_URL'],
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
                        "DASH_ID" => $item->DASH_ID,
                        "DIS_ID" => $item->DIS_ID,
                        "SYM_ID" => $item->SYM_ID,
                        "DASH_SECTION_ID" => $item->DASH_SECTION_ID,
                        "DASH_SECTION_NAME" => $item->DASH_SECTION_NAME,
                        "DASH_NAME" => $item->DASH_NAME,
                        "DASH_TYPE" => $item->DASH_TYPE,
                        "DASH_DESCRIPTION" => $item->DASH_DESCRIPTION,
                        "PHOTO_URL" => $item->PHOTO1_URL,
                        "PHOTO1_URL" => $item->PHOTO_URL,
                        "QA1" => $item->QA1,
                        "QA2" => $item->QA2,
                        "QA3" => $item->QA3,
                        "QA4" => $item->QA4,
                        "QA5" => $item->QA5,
                        "QA6" => $item->QA6,
                        "QA7" => $item->QA7,
                        "QA8" => $item->QA8,
                        "QA9" => $item->QA9,
                    ];
                })->values()->all();
                $groupedData = [];
                foreach ($L_DTL as $row2) {
                    if (!isset($groupedData[$row2['DASH_SECTION_ID']])) {
                        $groupedData[$row2['DASH_SECTION_ID']] = [
                            "DASH_SECTION_ID" => $row2['DASH_SECTION_ID'],
                            "DASH_SECTION_NAME" => $row2['DASH_SECTION_NAME'],
                            "PHOTO_URL" => $row2['PHOTO_URL'],
                            "DETAILS" => []
                        ];
                    }
                    $groupedData[$row2['DASH_SECTION_ID']]['DETAILS'][] = [
                        "DASH_ID" => $row2['DASH_ID'],
                        "DIS_ID" => $row2['DIS_ID'],
                        "SYM_ID" => $row2['SYM_ID'],
                        "DASH_NAME" => $row2['DASH_NAME'],
                        "DASH_DESCRIPTION" => $row2['DASH_DESCRIPTION'],
                        "PHOTO_URL" => $row2['PHOTO1_URL'],
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
                    ->orderBy('facility_type.DT_POSITION')
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

                // $AH["IPD_Facilities"] = array_values($groupedData);

                $AJ["Second_Opinion"] = array_values($groupedData);


                //SECTION-AQ #### IPD Section

                $I_DTL = DB::table('facility_section')
                    ->join('facility_type', 'facility_section.DASH_SECTION_ID', '=', 'facility_type.DASH_SECTION_ID')
                    ->join('facility', 'facility_type.DASH_TYPE_ID', '=', 'facility.DASH_TYPE_ID')
                    ->where(['facility_section.DS_STATUS' => 'Active', 'facility_type.DT_STATUS' => 'Active', 'facility.DN_STATUS' => 'Active'])
                    ->where('facility_section.DASH_SECTION_ID', 'AH')
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

                $AH["IPD_Facilities"] = array_values($groupedData);


                $data = $A + $DASH_A + $SPLST + $SYM + $CLINIC + $E + $D + $M + $N + $O + $P + $Q + $SURG + $R + $DIAG + $TST + $B + $C + $F + $DASH_BNR + $HOSP + $I + $J + $K + $L + $AJ + $AH;

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
}
