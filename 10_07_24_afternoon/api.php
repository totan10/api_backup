<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminEasyHealths;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\DrProfileController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\TestDummy;

// Dashboard API #####
Route::any('/dashboard', [DashboardController::class,'dashboard']);
Route::any('/dashboard1', [DashboardController::class,'dashboard1']);
Route::any('/labdashboard', [DashboardController::class,'labdashboard']);
Route::any('/labdashboard1', [DashboardController::class,'labdashboard1']);
Route::any('/hndashboard', [DashboardController::class,'hndashboard']);
Route::any('/hndashboard1', [DashboardController::class,'hndashboard1']);
Route::any('/admindashboard', [DashboardController::class,'admindashboard']);
Route::any('/diagdash', [DashboardController::class,'diagdash']);
Route::any('/diagdash1', [DashboardController::class,'diagdash1']);
Route::any('/userdashboard', [DashboardController::class,'userdashboard']);
Route::any('/cltestdash', [DashboardController::class,'cltestdash']);
Route::any('/drdashboard', [DashboardController::class,'drdashboard']);

// Lab Controller API #####
Route::any('/singaltest', [LabController::class,'singaltest']);
Route::any('/stestdtl', [LabController::class,'stestdtl']);
Route::any('/vustst', [LabController::class,'vustst']);
Route::any('/vuclinicstst', [LabController::class,'vuclinicstst']);
Route::any('/labpkgdtl', [LabController::class,'labpkgdtl']);
Route::any('/diagpkgdtl', [LabController::class,'diagpkgdtl']);
Route::any('/diagallpkg', [LabController::class,'diagallpkg']);
Route::any('/alstst', [LabController::class,'alstst']);
Route::any('/labsrch', [LabController::class,'labsrch']);
Route::any('/diagsrch', [LabController::class,'diagsrch']);
Route::any('/edittest', [LabController::class,'edittest']);
Route::any('/testsrch1', [LabController::class,'testsrch1']);
Route::any('/dcsplst', [LabController::class,'dcsplst']);
Route::any('/catgclinicdr', [LabController::class,'catgclinicdr']);
Route::any('/scanorgantest', [LabController::class,'scanorgantest']);
Route::any('/scanorgan', [LabController::class,'scanorgan']);
Route::any('/diagallscan', [LabController::class,'diagallscan']);
Route::any('/diagalltest', [LabController::class,'diagalltest']);
Route::any('/vutodaydr', [LabController::class,'vutodaydr']);
Route::any('/itemorgantest', [LabController::class,'itemorgantest']);
Route::any('/clinicitemorgantest', [LabController::class,'clinicitemorgantest']);
// Route::any('/catghospital', [LabController::class,'catghospital']);
// Route::any('/surghospital', [LabController::class,'surghospital']);
// Route::any('/tpahospital', [LabController::class,'tpahospital']);
// Route::any('/ipdhospital', [LabController::class,'ipdhospital']);
Route::any('/hosp_facility', [LabController::class,'hosp_facility']);
Route::any('/hosp_lst', [LabController::class,'hosp_lst']);
Route::any('/hosp_lst1', [LabController::class,'hosp_lst1']);
Route::any('/temp', [LabController::class,'temp']);
Route::any('/usertesthis', [LabController::class,'usertesthis']);
Route::any('/testinvoice', [LabController::class,'testinvoice']);
Route::any('/hnsec_opinion', [LabController::class,'hnsec_opinion']);
Route::any('/hnsec_opinion1', [LabController::class,'hnsec_opinion1']);

// USER API #####
Route::any('/usersignup', [UserController::class,'usersignup']);
Route::any('/login', [UserController::class,'login']);
Route::any('/srchdr', [UserController::class,'srchdr']);
Route::any('/srchdrname', [UserController::class,'srchdrname']);
Route::any('/srchcitydr', [UserController::class,'srchcitydr']);
Route::any('/nearbydr', [UserController::class,'nearbydr']);
Route::any('/nearlivedr', [UserController::class,'nearlivedr']);
Route::any('/nearbyclinic', [UserController::class,'nearbyclinic']);
Route::any('/srchlivedr', [UserController::class,'srchlivedr']);
Route::get('/allsrchdr', [UserController::class,'allsrchdr']);
Route::any('/srcclinic', [UserController::class,'srcclinic']);
Route::any('/srchcityclinic', [UserController::class,'srchcityclinic']);
Route::any('/availdr', [UserController::class,'availdr']);
Route::any('/viewfamily', [UserController::class,'viewfamily']);
Route::any('/addfamily', [UserController::class,'addfamily']);
Route::any('/editfamily', [UserController::class,'editfamily']);
Route::any('/delfamily', [UserController::class,'delfamily']);
Route::any('/updatefamily', [UserController::class,'updatefamily']);
Route::any('/viewappointment', [UserController::class,'viewappointment']);
Route::any('/booking', [UserController::class,'booking']);
Route::any('/bookhis', [UserController::class,'bookhis']);
Route::any('/bookinginvoice', [UserController::class,'bookinginvoice']);
Route::any('/bookcancel', [UserController::class,'bookcancel']);
Route::any('/logout', [UserController::class,'logout']);
Route::any('/getaddress', [UserController::class,'getaddress']);
Route::any('/symptoms', [UserController::class,'symptoms']);
Route::any('/catgdr', [UserController::class,'catgdr']);
Route::any('/catgdr1', [UserController::class,'catgdr1']);
Route::any('/srccldr', [UserController::class,'srccldr']);
Route::any('/saveinvoice', [UserController::class,'saveinvoice']);
Route::any('/cldrct', [UserController::class,'cldrct']);
Route::any('/cldr', [UserController::class,'cldr']);
Route::any('/cldrtdct', [UserController::class,'cldrtdct']);
Route::any('/cldrtd', [UserController::class,'cldrtd']);
Route::any('/vuallsplst', [UserController::class,'vuallsplst']);
Route::any('/vuallsurg', [UserController::class,'vuallsurg']);
Route::any('/vuallsymp', [UserController::class,'vuallsymp']);
Route::any('/allsrch', [UserController::class,'allsrch']);
Route::any('/allsrchcl', [UserController::class,'allsrchcl']);
Route::any('/allsrchtst', [UserController::class,'allsrchtst']);
Route::any('/alldiag', [UserController::class,'alldiag']);
Route::any('/allclinic', [UserController::class,'allclinic']);
Route::any('/phopd', [UserController::class,'phopd']);
Route::any('/vuclallsplst', [UserController::class,'vuclallsplst']);
Route::any('/upprescription', [UserController::class,'upprescription']);
Route::any('/patientreview', [UserController::class,'patientreview']);
Route::any('/vudrvisit', [UserController::class,'vudrvisit']);
Route::any('/vusamedr', [UserController::class,'vusamedr']);
Route::any('/vusamedr1', [UserController::class,'vusamedr1']);
Route::any('/vuallreview', [UserController::class,'vuallreview']);
Route::any('/vuslot', [UserController::class,'vuslot']);
Route::any('/patientarrive', [UserController::class,'patientarrive']);
Route::any('/vurptday', [UserController::class,'vurptday']);
Route::any('/del_user', [UserController::class,'del_user']);
Route::any('/dash_facility', [UserController::class,'dash_facility']);
Route::any('/availdrslot', [UserController::class,'availdrslot']);
Route::any('/dash_facility1', [UserController::class,'dash_facility1']);
Route::any('/book_facilities', [UserController::class,'book_facilities']);
Route::any('/vuhnallsurg', [UserController::class,'vuhnallsurg']);

// Route::any('/find13', [UserController::class,'find13']);
// Route::any('/availdr1', [UserController::class,'availdr1']);
// Route::any('/log1', [UserController::class,'log1']);
// Route::any('/vuapnt', [UserController::class,'vuapnt']);
// Route::any('/srcclinic1', [UserController::class,'srcclinic1']);
// Route::any('/test', [UserController::class,'test']);

// Doctor API #####
Route::any('/dcatg', [DrProfileController::class,'dcatg']);
Route::get('/adcatg', [DrProfileController::class,'adcatg']);
Route::any('/pincode', [DrProfileController::class,'pincode']);
Route::get('/state', [DrProfileController::class,'state']);
Route::get('/states', [DrProfileController::class,'states']);
Route::any('/district', [DrProfileController::class,'district']);
Route::any('/city', [DrProfileController::class,'city']);
Route::any('/drsignup', [DrProfileController::class,'drsignup']);
// Route::any('/adddr', [DrProfileController::class,'adddr']);


// Clinic API #####
Route::any('/clinicsignup', [ClinicController::class,'clinicsignup']);
Route::any('/cliniclogin', [ClinicController::class,'cliniclogin']);
Route::any('/admcliniclogin', [ClinicController::class,'admcliniclogin']);
Route::any('/serdr', [ClinicController::class,'serdr']);
Route::any('/sndreq', [ClinicController::class,'sndreq']);
Route::any('/attchdr', [ClinicController::class,'attchdr']);

// AdminDesktop API #####
Route::any('/admlogin', [AdminController::class,'admlogin']);
Route::any('/add_slider', [AdminController::class,'add_slider']);
Route::any('/updt_slider', [AdminController::class,'updt_slider']);
Route::any('/add_appdash', [AdminController::class,'add_appdash']);
Route::any('/updt_appdash', [AdminController::class,'updt_appdash']);
Route::any('/add_bestoffer', [AdminController::class,'add_bestoffer']);
Route::any('/updt_bestoffer', [AdminController::class,'updt_bestoffer']);
Route::any('/add_catg', [AdminController::class,'add_catg']);
Route::any('/updt_catg', [AdminController::class,'updt_catg']);
Route::any('/add_symp', [AdminController::class,'add_symp']);
Route::any('/updt_symp', [AdminController::class,'updt_symp']);
Route::any('/add_expertcare_little', [AdminController::class,'add_expertcare_little']);
Route::any('/updt_expertcare_little', [AdminController::class,'updt_expertcare_little']);
Route::any('/add_expertcare_woman', [AdminController::class,'add_expertcare_woman']);
Route::any('/updt_expertcare_woman', [AdminController::class,'updt_expertcare_woman']);
Route::any('/add_health_tool', [AdminController::class,'add_health_tool']);
Route::any('/updt_health_tool', [AdminController::class,'updt_health_tool']);
Route::any('/add_health_check', [AdminController::class,'add_health_check']);
Route::any('/updt_health_check', [AdminController::class,'updt_health_check']);
Route::any('/add_consult_home', [AdminController::class,'add_consult_home']);
Route::any('/updt_consult_home', [AdminController::class,'updt_consult_home']);
Route::any('/add_fittrack', [AdminController::class,'add_fittrack']);
Route::any('/updt_fittrack', [AdminController::class,'updt_fittrack']);
Route::any('/add_spotlight', [AdminController::class,'add_spotlight']);
Route::any('/updt_spotlight', [AdminController::class,'updt_spotlight']);
Route::any('/add_surgery', [AdminController::class,'add_surgery']);
Route::any('/updt_surgery', [AdminController::class,'updt_surgery']);
Route::any('/add_video_clip', [AdminController::class,'add_video_clip']);
Route::any('/updt_video_clip', [AdminController::class,'updt_video_clip']);
Route::any('/homescreen', [AdminController::class,'homescreen']);
Route::any('/updt_caption', [AdminController::class,'updt_caption']);
Route::any('/updt_promo', [AdminController::class,'updt_promo']);
Route::any('/add_header', [AdminController::class,'add_header']);
Route::any('/delssn', [AdminController::class,'delssn']);
Route::any('/booktest', [AdminController::class,'booktest']);
Route::any('/admtesthis', [AdminController::class,'admtesthis']);
Route::any('/createstaff', [AdminController::class,'createstaff']);

// Admin Mobile App #####
Route::any('/testsrch', [AdminController::class,'testsrch']);
Route::any('/pkgtestsrch', [AdminController::class,'pkgtestsrch']);
Route::any('/pkgproftestsrch', [AdminController::class,'pkgproftestsrch']);
Route::any('/viewitem', [AdminController::class,'viewitem']);
Route::any('/addtest', [AdminController::class,'addtest']);
Route::any('/edittest', [AdminController::class,'edittest']);
Route::any('/addedtest', [AdminController::class,'addedtest']);
Route::any('/add_app_pkg', [AdminController::class,'add_app_pkg']);
Route::any('/add_pkgtest', [AdminController::class,'add_pkgtest']);
Route::any('/edit_pkgtest', [AdminController::class,'edit_pkgtest']);
Route::any('/del_pkgtest', [AdminController::class,'del_pkgtest']);
Route::any('/vuclpkg', [AdminController::class,'vuclpkg']);
Route::any('/vuclpkgtest', [AdminController::class,'vuclpkgtest']);
Route::any('/vupkgtest', [AdminController::class,'vupkgtest']);
Route::any('/vuallpkgtest', [AdminController::class,'vuallpkgtest']);
Route::any('/pkg_dtls', [AdminController::class,'pkg_dtls']);
Route::any('/pkg_dtls1', [AdminController::class,'pkg_dtls1']);
Route::any('/preshis', [AdminController::class,'preshis']);
Route::any('/viewstaff', [AdminController::class,'viewstaff']);
Route::any('/viewdoctors', [AdminController::class,'viewdoctors']);
Route::any('/addsch', [AdminController::class,'addsch']);
Route::any('/vusch', [AdminController::class,'vusch']);
Route::any('/allot', [AdminController::class,'allot']);
Route::any('/chkallot', [AdminController::class,'chkallot']);
Route::any('/vutestreq', [AdminController::class,'vutestreq']);
Route::any('/admtodaydr', [AdminController::class,'admtodaydr']);
Route::any('/chating', [AdminController::class,'chating']);
Route::any('/vuchating', [AdminController::class,'vuchating']);
Route::any('/vucatg', [AdminController::class,'vucatg']);
Route::any('/vunmc', [AdminController::class,'vunmc']);
Route::any('/adddr', [AdminController::class,'adddr']);
Route::any('/adddrcsv', [AdminController::class,'adddrcsv']);
Route::any('/editdr', [AdminController::class,'editdr']);
Route::any('/vudrprofile', [AdminController::class,'vudrprofile']);
Route::any('/drsts', [AdminController::class,'drsts']);
Route::any('/getsl', [AdminController::class,'getsl']);
Route::any('/drvisit', [AdminController::class,'drvisit']);
Route::any('/patientin', [AdminController::class,'patientin']);
Route::any('/vunonsch', [AdminController::class,'vunonsch']);
Route::any('/hlthtrk', [AdminController::class,'hlthtrk']);
Route::any('/appntdr', [AdminController::class,'appntdr']);
Route::any('/tdpatient', [AdminController::class,'tdpatient']);
Route::any('/clalldr', [AdminController::class,'clalldr']);
Route::any('/drleft', [AdminController::class,'drleft']);
Route::any('/editstaff', [AdminController::class,'editstaff']);
Route::any('/staffprivilege', [AdminController::class,'staffprivilege']);
Route::any('/editclinicprofile', [AdminController::class,'editclinicprofile']);
Route::any('/editsection', [AdminController::class,'editsection']);
Route::any('/addabout', [AdminController::class,'addabout']);
Route::any('/srchdrtest', [AdminController::class,'srchdrtest']);
Route::any('/srchdrtest1', [AdminController::class,'srchdrtest1']);
Route::any('/searchDoctorTest', [AdminController::class,'searchDoctorTest']);
Route::any('/admsearchtest', [AdminController::class,'admsearchtest']);
Route::any('/searchDoctorTest1', [AdminController::class,'searchDoctorTest1']);
Route::any('/staffleave', [AdminController::class,'staffleave']);
Route::any('/staffcl', [AdminController::class,'staffcl']);
Route::any('/vustaffcl', [AdminController::class,'vustaffcl']);
Route::any('/srchmob', [AdminController::class,'srchmob']);
Route::any('/vucatgdrdt', [AdminController::class,'vucatgdrdt']);
Route::any('/admopd', [AdminController::class,'admopd']);
Route::any('/admcatgclinicdr', [AdminController::class,'admcatgclinicdr']);
Route::any('/admcatgclinicdr1', [AdminController::class,'admcatgclinicdr1']);
Route::any('/phopdweb', [AdminController::class,'phopdweb']);
Route::any('/admbooking', [AdminController::class,'admbooking']);
Route::any('/drdelay', [AdminController::class,'drdelay']);
Route::any('/drcancel', [AdminController::class,'drcancel']);
Route::any('/drleave', [AdminController::class,'drleave']);
Route::any('/editsch', [AdminController::class,'editsch']);
Route::any('/delsch', [AdminController::class,'delsch']);
Route::any('/vu_facility', [AdminController::class,'vu_facility']);
Route::any('/vu_pharma_facility', [AdminController::class,'vu_pharma_facility']);
Route::any('/add_facility', [AdminController::class,'add_facility']);
// Route::any('/edit_facilities', [AdminController::class,'edit_facilities']);
Route::any('/vuupdt_facility', [AdminController::class,'vuupdt_facility']);
Route::any('/vu_dept', [AdminController::class,'vu_dept']);
Route::any('/add_deptdr', [AdminController::class,'add_deptdr']);
Route::any('/avail_chember', [AdminController::class,'avail_chember']);




// AdminEasyHealths API #####
Route::any('/add_maindash', [AdminEasyHealths::class,'add_maindash']);
Route::any('/updt_maindash', [AdminEasyHealths::class,'updt_maindash']);
Route::any('/del_maindash', [AdminEasyHealths::class,'del_maindash']);
Route::any('/updt_header', [AdminEasyHealths::class,'updt_header']);
Route::any('/updt_splst', [AdminEasyHealths::class,'updt_splst']);
Route::any('/updt_splsthdr', [AdminEasyHealths::class,'updt_splsthdr']);

Route::any('/add_splst', [AdminEasyHealths::class,'add_splst']);
Route::any('/add_symp', [AdminEasyHealths::class,'add_symp']);
Route::any('/updt_symp', [AdminEasyHealths::class,'updt_symp']);
Route::any('/add_surg', [AdminEasyHealths::class,'add_surg']);
Route::any('/updt_surg', [AdminEasyHealths::class,'updt_surg']);
Route::any('/updt_surgbnr', [AdminEasyHealths::class,'updt_surgbnr']);
Route::any('/updt_slider', [AdminEasyHealths::class,'updt_slider']);
Route::any('/add_slider', [AdminEasyHealths::class,'add_slider']);
Route::any('/updt_test', [AdminEasyHealths::class,'updt_test']);
Route::any('/updt_grservice', [AdminEasyHealths::class,'updt_grservice']);
Route::any('/add_service', [AdminEasyHealths::class,'add_service']);
Route::any('/updt_service', [AdminEasyHealths::class,'updt_service']);
Route::any('/updt_emergency', [AdminEasyHealths::class,'updt_emergency']);
Route::any('/updt_organ', [AdminEasyHealths::class,'updt_organ']);
Route::any('/updt_drprofile', [AdminEasyHealths::class,'updt_drprofile']);
Route::any('/updt_facility_section', [AdminEasyHealths::class,'updt_facility_section']);
Route::any('/add_grservice', [AdminEasyHealths::class,'add_grservice']);
Route::any('/updt_dsfaq', [AdminEasyHealths::class,'updt_dsfaq']);
Route::any('/updt_dtfaq', [AdminEasyHealths::class,'updt_dtfaq']);
Route::any('/updt_dnfaq', [AdminEasyHealths::class,'updt_dnfaq']);






// Route::any('/add_l_dash_dtl', [AdminEasyHealths::class,'add_l_dash_dtl']);
// Route::any('/updt_l_dash_dtl', [AdminEasyHealths::class,'updt_l_dash_dtl']);
// Route::any('/add_l_header', [AdminEasyHealths::class,'add_l_header']);
// Route::any('/updt_l_caption', [AdminEasyHealths::class,'updt_l_caption']);

// Test API #####
Route::any('/dr_profile_excel', [FileController::class,'dr_profile_excel']);
Route::any('/calculateDates', [FileController::class,'calculateDates']);

// Route::group(['middleware'=>'auth:sanctum'],function(){
    
   Route::any('/test', [UserController::class,'test']);  
// });

Route::middleware('auth:sanctum')->group(function () {
    
});

Route::middleware('auth:api')->get('/user',function(Request $request){
    return $request->user();
});




// LabTest Dummy API
Route::any('/labdashboard1_dummy', [TestDummy::class,'labdashboard1_dummy']);
Route::any('/alstst_dummy', [TestDummy::class,'alstst_dummy']);
Route::any('/labsrch_dummy', [TestDummy::class,'labsrch_dummy']);
Route::any('/vuclinicstst_dummy', [TestDummy::class,'vuclinicstst_dummy']);
Route::any('/labpkgdtl_dummy', [TestDummy::class,'labpkgdtl_dummy']);
Route::any('/booktest_dummy', [TestDummy::class,'booktest_dummy']);
Route::any('/testinvoice_dummy', [TestDummy::class,'testinvoice_dummy']);
Route::any('/savetestinvoice_dummy', [TestDummy::class,'savetestinvoice_dummy']);

Route::any('/testsearch_dummy', [TestDummy::class,'testsearch_dummy']);
Route::any('/clinicitemorgantest_dummy', [TestDummy::class,'clinicitemorgantest_dummy']);
Route::any('/symptom_tests_dummy', [TestDummy::class,'symptom_tests_dummy']);
Route::any('/labprofilesdtl_dummy', [TestDummy::class,'labprofilesdtl_dummy']);
Route::any('/labpackagesdtl_dummy', [TestDummy::class,'labpackagesdtl_dummy']);
Route::any('/allsymptom_pathology_dummy', [TestDummy::class,'allsymptom_pathology_dummy']);
Route::any('/testbookinghistory_dummy', [TestDummy::class, 'testbookinghistory_dummy']);
Route::any('/allsymptom_pathology1_dummy', [TestDummy::class,'allsymptom_pathology1_dummy']);
Route::any('/scanorgantest_dummy', [TestDummy::class,'scanorgantest_dummy']);
Route::any('/vustst_dummy', [TestDummy::class,'vustst_dummy']);
Route::any('/diagpkgdtl_dummy', [TestDummy::class,'diagpkgdtl_dummy']);
Route::any('/clinic_pathology_dummy', [TestDummy::class,'clinic_pathology_dummy']);
Route::any('/pathologysearch_dummy', [TestDummy::class,'pathologysearch_dummy']);
Route::any('/sample_pathology_dummy', [TestDummy::class,'sample_pathology_dummy']);
Route::any('/radiologyscan_search_dummy', [TestDummy::class,'radiologyscan_search_dummy']);
Route::any('/pathology_samplesrch_dummy', [TestDummy::class,'pathology_samplesrch_dummy']);
Route::any('/allSpecialist_dummy', [TestDummy::class,'allSpecialist_dummy']);
Route::any('/specialist_pharma_dummy', [TestDummy::class,'specialist_pharma_dummy']);
Route::any('/admcatgclinicdr1_dummy', [TestDummy::class,'admcatgclinicdr1_dummy']);
Route::any('/clinic_catgdr_dummy', [TestDummy::class,'clinic_catgdr_dummy']);
Route::any('/opinion_depts_dummy', [TestDummy::class,'opinion_depts_dummy']);
Route::any('/globalscrh_dummy', [TestDummy::class,'globalscrh_dummy']);
Route::any('/servicescrh_dummy', [TestDummy::class,'servicescrh_dummy']);
Route::any('/doctrscrh_dummy', [TestDummy::class,'doctrscrh_dummy']);
Route::any('/testscrh_dummy', [TestDummy::class,'testscrh_dummy']);
Route::any('/pharmascrh_dummy', [TestDummy::class,'pharmascrh_dummy']);
Route::any('/drappointments_dummy', [TestDummy::class,'drappointments_dummy']);
Route::any('/drdashboard_dummy', [TestDummy::class,'drdashboard_dummy']);
Route::any('/drappnt_clinic_dummy', [TestDummy::class,'drappnt_clinic_dummy']);







