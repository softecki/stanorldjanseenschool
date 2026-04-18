<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\Library\Member\MemberStoreRequest;
use App\Http\Requests\Library\Member\MemberUpdateRequest;
use App\Repositories\Library\MemberCategoryRepository;
use App\Repositories\Library\MemberRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MemberController extends Controller
{
    private $Repo, $categoryRepo;

    function __construct(MemberRepository $Repo, MemberCategoryRepository $categoryRepo)
    {
        if (!Schema::hasTable('settings') && !Schema::hasTable('users')) {
            abort(400);
        } 
        $this->Repo = $Repo;
        $this->categoryRepo  = $categoryRepo;
    }

    public function index()
    {
        try {
            $data['member'] = DB::table('members')
                ->join('member_categories', 'member_categories.id', '=', 'members.category_id')
                ->leftJoin('students', function ($join) {
                    $join->on('students.id', '=', 'members.user_id')
                        ->where('members.category_id', '=', 1);
                })
                ->leftJoin('users', function ($join) {
                    $join->on('users.id', '=', 'members.user_id')
                        ->where('members.category_id', '=', 2);
                })
                ->select(
                    'members.*',
                    'member_categories.name as member_name',
                    DB::raw("COALESCE(CONCAT(students.first_name, ' ', students.last_name), users.name) AS name"),
                    'users.phone',
                    'users.email'
                )
                ->whereIn('members.category_id', [1, 2])
                ->paginate(10);
            $data['title'] = ___('settings.Member');
            return view('backend.library.member.index', compact('data'));
        }catch (\Exception $e) {
            dd($e);
        }


    }

    public function create()
    {
        $data['title']       = ___('website.Create member');
        $data['categories']  = $this->categoryRepo->all();
        return view('backend.library.member.create', compact('data'));
    }

    public function store(MemberStoreRequest $request)
    {
        $result = $this->Repo->store($request);
        if($result['status']){
            return redirect()->route('member.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit($id)
    {
        $data['member']      = $this->Repo->show($id);
        $data['user']        = $this->Repo->getUser($data['member']->user_id);
        $data['title']       = ___('website.Edit member');
        $data['categories']  = $this->categoryRepo->all();
        return view('backend.library.member.edit', compact('data'));
    }

    public function update(MemberUpdateRequest $request, $id)
    {
        $result = $this->Repo->update($request, $id);
        if($result['status']){
            return redirect()->route('member.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->Repo->destroy($id);
        if($result['status']):
            $success[0] = $result['message'];
            $success[1] = 'success';
            $success[2] = ___('alert.deleted');
            $success[3] = ___('alert.OK');
            return response()->json($success);
        else:
            $success[0] = $result['message'];
            $success[1] = 'error';
            $success[2] = ___('alert.oops');
            return response()->json($success);
        endif;     
    }

    public function getMember(Request $request)
    {
        $result = $this->Repo->getMember($request);
        return response()->json($result);
    }
}
