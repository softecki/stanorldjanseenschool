<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\Library\IssueBook\IssueBookStoreRequest;
use App\Http\Requests\Library\IssueBook\IssueBookUpdateRequest;
use App\Models\Library\Member;
use App\Repositories\Library\IssueBookRepository;
use AWS\CRT\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;

class IssueBookController extends Controller
{
    private $Repo;

    function __construct(IssueBookRepository $Repo)
    {
        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        } 
        $this->Repo                  = $Repo;
    }

    public function index()
    {
        $data['issue_book'] = $this->Repo->getAll();
        $data['title'] = ___('settings.issue_book');
        return view('backend.library.issue-book.index', compact('data'));
    }

    public function indexStudent(Request $request): JsonResponse|RedirectResponse
    {
        $data['issue_book'] = $this->Repo->issueBook();
        $data['title'] = ___('settings.issue_book');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('student-panel/issue-books'));
    }  

    public function indexParent(Request $request): JsonResponse|RedirectResponse
    {
        $data['issue_book'] = $this->Repo->issueBook();
        $data['title'] = ___('settings.issue_book');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('parent-panel/issue-books'));
    }  

    public function create()
    {
        $data['title']       = ___('website.Create issue book.');
        $data['members']       = Member::all();
        return view('backend.library.issue-book.create', compact('data'));
    }

    public function store(IssueBookStoreRequest $request)
    {
        $result = $this->Repo->store($request);
        if($result['status']){
            return redirect()->route('issue-book.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit($id)
    {
        $data['issue_book']  = $this->Repo->show($id);
        $data['title']       = ___('website.Edit issue book');
        $data['user']        = $this->Repo->getUser($data['issue_book']->user_id);
        $data['book']        = $this->Repo->getBook($data['issue_book']->book_id);
        return view('backend.library.issue-book.edit', compact('data'));
    }

    public function update(IssueBookUpdateRequest $request, $id)
    {
        $result = $this->Repo->update($request, $id);
        if($result['status']){
            return redirect()->route('issue-book.index')->with('success', $result['message']);
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
        \Illuminate\Support\Facades\Log::info($result);
        return response()->json($result);
    }
    public function getBook(Request $request)
    {
        $result = $this->Repo->getBooks($request);
        return response()->json($result);
    }

    public function return($id)
    {
        $result = $this->Repo->return($id);
        if($result['status']){
            return redirect()->route('issue-book.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }

    public function search(Request $request)
    {
        $data['title']      = ___('settings.issue_book');
        $data['issue_book'] = $this->Repo->searchResult($request);
        $data['request']    = $request;
        
        return view('backend.library.issue-book.index', compact('data'));
    }
}
