<?php

namespace App\Http\Controllers\Frontend;

use PDF;
use App\Support\PublicSiteMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\GenderRepository;
use Illuminate\Support\Facades\Schema;
use App\Repositories\ReligionRepository;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Report\MarksheetRepository;
use App\Repositories\Frontend\FrontendRepository;
use App\Repositories\WebsiteSetup\PageRepository;
use App\Http\Requests\Frontend\SearchResultRequest;
use App\Repositories\Academic\ShiftRepository;
use App\Repositories\StudentInfo\StudentRepository;
use App\Repositories\StudentInfo\OnlineAdmissionSettingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;

class FrontendController extends Controller
{
    private $repo;
    private $religionRepo;
    private $genderRepo;
    private $marksheetRepo;
    private $studentRepo;
    private $pageRepo;
    private $admission_setting_repo;
    private $shift_repo;

    function __construct(
        FrontendRepository $repo,
        ReligionRepository $religionRepo,
        GenderRepository   $genderRepo,
        MarksheetRepository    $marksheetRepo,
        StudentRepository      $studentRepo,
        PageRepository      $pageRepo,
        OnlineAdmissionSettingRepository      $admission_setting_repo,
        ShiftRepository      $shift_repo,
    )
    {
        if (!Schema::hasTable('settings') && !Schema::hasTable('users'))
            abort(400);
        $this->repo         = $repo;
        $this->religionRepo = $religionRepo;
        $this->genderRepo   = $genderRepo;
        $this->marksheetRepo      = $marksheetRepo;
        $this->studentRepo        = $studentRepo;
        $this->pageRepo        = $pageRepo;
        $this->admission_setting_repo        = $admission_setting_repo;
        $this->shift_repo        = $shift_repo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['sliders']          = $this->repo->sliders();
        $data['counters']         = $this->repo->counters();
        $data['galleryCategory']  = $this->repo->galleryCategory();
        $data['gallery']          = $this->repo->gallery();
        $data['latestNews']       = $this->repo->latestNews();
        $data['comingEvents']     = $this->repo->comingEvents();

        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => PublicSiteMeta::page('Home')]);
        }
        return view('spa.app');
    }

    // Result
    public function getClasses(Request $request){
        $data = $this->repo->getClasses($request); // session id
        return response()->json($data);
    }
    public function getSections(Request $request){
        $data = $this->repo->getSections($request); // class id
        return response()->json($data);
    }
    public function getExamType(Request $request)
    {
        $result = $this->repo->getExamType($request);
        return response()->json($result, 200);
    }
    public function result(Request $request): JsonResponse|View
    {
        $data = $this->repo->result();
        $data['result'] = null;
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => PublicSiteMeta::page('Result')]);
        }
        return view('spa.app');
    }

    public function searchResult(SearchResultRequest $request): JsonResponse|View
    {
        $data = $this->repo->searchResult($request);
        if(!$data)
        {
            $data = $this->repo->result();
            $data['result'] = "Result not found!";
            if ($request->expectsJson()) {
                return response()->json(['data' => $data, 'message' => 'Result not found'], 404);
            }
            return view('spa.app');
        }
        $data['request'] = $request;
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'message' => 'Result found']);
        }
        return view('spa.app');
    }

    public function downloadPDF($id, $type, $class, $section)
    {
        $request = new Request([
            'student'   => $id,
            'exam_type' => $type,
            'class'     => $class,
            'section'   => $section,
        ]);

        $data['student']      = $this->studentRepo->show($request->student);
        $data['resultData']   = $this->marksheetRepo->search($request);

        $pdf = PDF::loadView('backend.report.marksheetPDF', compact('data'));
        return $pdf->download('marksheet'.'_'.date('d_m_Y').'_'.@$data['student']->first_name .'_'. @$data['student']->last_name .'.pdf');
    }

    public function about(Request $request): JsonResponse|View
    {
        $data = $this->repo->abouts();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => PublicSiteMeta::page('About')]);
        }
        return view('spa.app');
    }

    // Blog
    public function news(Request $request): JsonResponse|View
    {
        $data['news'] = $this->repo->news();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => PublicSiteMeta::page('News')]);
        }
        return view('spa.app');
    }
    public function newsDetail(Request $request, $id): JsonResponse|View
    {
        $data['allNews'] = $this->repo->news();
        $data['news']    = $this->repo->newsDetail($id);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => PublicSiteMeta::page('News detail')]);
        }
        return view('spa.app');
    }

    // Event
    public function events(Request $request): JsonResponse|View
    {
        $events = $this->repo->events();
        if ($request->expectsJson()) {
            return response()->json(['data' => ['events' => $events], 'meta' => PublicSiteMeta::page('Events')]);
        }
        return view('spa.app');
    }
    public function eventDetail(Request $request, $id): JsonResponse|View
    {
        $data['allEvent'] = $this->repo->events();
        $data['event']    = $this->repo->eventDetail($id);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => PublicSiteMeta::page('Event detail')]);
        }
        return view('spa.app');
    }


    public function page(Request $request, $slug): JsonResponse|View
    {
        $data['page']    = $this->pageRepo->findBySlug($slug);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => PublicSiteMeta::page('Page')]);
        }
        return view('spa.app');
    }


    // Event
    public function notices(Request $request): JsonResponse|View
    {
        $data['notices'] = $this->repo->notices();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => PublicSiteMeta::page('Notices')]);
        }
        return view('spa.app');
    }
    public function noticeDetail(Request $request, $id): JsonResponse|View
    {
        $data['allNotice'] = $this->repo->notices();
        $data['notice-board']    = $this->repo->noticeDetail($id);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => PublicSiteMeta::page('Notice detail')]);
        }
        return view('spa.app');
    }

    // Contact
    public function contact(Request $request): JsonResponse|View
    {
        $data['contactInfo']    = $this->repo->contactInfo();
        $data['depContact']     = $this->repo->depContact();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => PublicSiteMeta::page('Contact')]);
        }
        return view('spa.app');
    }

    // onlineAdmission
    public function onlineAdmission(Request $request): JsonResponse|View
    {
        $data = $this->repo->result();
        $data['religions']= $this->religionRepo->all();
        $data['genders']  = $this->genderRepo->all();
        $data['shifts']  = $this->shift_repo->all();
        $data['setting']  = $this->admission_setting_repo->getIsShowByType('online_admission');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => PublicSiteMeta::page('Online Admission')]);
        }
        return view('spa.app');
    }


        // onlineAdmission
        public function onlineAdmissionFees(Request $request, $student_phone, $admission_id): JsonResponse|View
        {
            $data['admission'] = $this->repo->onlineAdmissionDetail($admission_id);
            $data['setting']  = $this->admission_setting_repo->getIsShowByType('online_admission');
            $data['fees'] = $this->repo->onlineAdmissionFees($data['admission']->session_id, $data['admission']->classes_id , $data['admission']->section_id);
            $data['payment_instruction'] = $this->admission_setting_repo->getOneByFied('admission_payment_info');
            if ($request->expectsJson()) {
                return response()->json(['data' => $data, 'meta' => PublicSiteMeta::page('Admission Fees')]);
            }
            return view('spa.app');
        }

    public function storeOnlineAdmission(Request $request) {

        $admission = $this->repo->onlineAdmission($request);
        $fees = $this->repo->onlineAdmissionFees($admission->session_id, $admission->classes_id , $admission->section_id);
        $payment_setting = $this->admission_setting_repo->getOneByFied('admission_payment');

        $successMsg = 'Admission Inform submitted successfully, Please wait for school approval';
        $successMsgFees = 'Admission Inform submitted successfully , Please wait for school approval';

        if ($request->expectsJson()) {
            if ($admission && $fees && $payment_setting->is_show == 1) {
                return response()->json([
                    'message' => $successMsgFees,
                    'redirect' => url('/online-admission-fees/'.$admission->reference_no.'/'.$admission->id),
                    'reference_no' => $admission->reference_no,
                    'admission_id' => $admission->id,
                ]);
            }
            return response()->json([
                'message' => $successMsg,
                'redirect' => url('/online-admission'),
                'reference_no' => $admission->reference_no ?? null,
                'admission_id' => $admission->id ?? null,
            ]);
        }

        if($admission && $fees && $payment_setting->is_show == 1){
            return redirect()->route('frontend.online-admission-fees',[$admission->reference_no , $admission->id])->with('message' , $successMsgFees);
        }
        return redirect()->back()->with('message' , $successMsg);

    }


    public function storeOnlineAdmissionFees(Request $request) {
        $validator = Validator::make($request->all(), [
            'payment_image' => 'required|mimes:jpeg,png,jpg,gif'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $admission = $this->repo->storeOnlineAdmissionFees($request);

        if($admission){
            $msg = 'Admission Inform submitted successfully , Please complete payment for successfully admission';
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $msg,
                    'redirect' => url('/online-admission'),
                ]);
            }
            return redirect()->route('frontend.online-admission')->with('message' , $msg);
        }
    }

    public function storeContact(Request $request)
    {
        return $this->repo->contact($request);
    }

    public function storeSubscribe(Request $request)
    {
        return $this->repo->subscribe($request);
    }
}
