<?php

namespace App\Repositories\NoticeBoard;

use App\Models\Session;
use App\Models\Homework;
use App\Models\Certificate;
use App\Models\NoticeBoard;
use App\Models\HomeworkStudent;
use App\Traits\CommonHelperTrait;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use App\Models\StudentInfo\SessionClassStudent;
use App\Repositories\NoticeBoard\NoticeBoardInterface;
use App\Models\NoticeBoardTranslate;

class NoticeBoardRepository implements NoticeBoardInterface
{
    use ReturnFormatTrait, CommonHelperTrait;

    private $model;
    private $notice_board_trans;

    public function __construct(NoticeBoard $model, NoticeBoardTranslate $notice_board_trans)
    {
        $this->model = $model;
        $this->notice_board_trans = $notice_board_trans;
    }

    public function all()
    {
        return $this->model->orderByDesc('id')->get();
    }

    public function getPaginateAll()
    {
        return $this->model::latest()->orderByDesc('id')->paginate(10);
    }

    public function search($request)
    {
        $rows = $this->model::query();
        if($request->class != "") {
            $rows = $rows->where('classes_id', $request->class);
        }
        if($request->section != "") {
            $rows = $rows->where('section_id', $request->section);
        }
        if($request->subject != "") {
            $rows = $rows->where('subject_id', $request->subject);
        }
        return $rows->paginate(10);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $row                    = new $this->model;
            $row->title             = $request->title;
            $row->publish_date      = date('Y-m-d H:i:s', strtotime($request->publish_date));
            $row->date              = date('Y-m-d', strtotime($request->date));

            $row->is_visible_web       = $request->is_visible_web;
            $row->status               = $request->status;
            $row->description          = $request->description;
            $row->visible_to           = $request->visible_to;
            $row->attachment           = $this->UploadImageCreate($request->attachment, 'backend/uploads/communication');
            $row->save();

            $en_row                   = new $this->notice_board_trans;
            $en_row->notice_board_id        = $row->id ;
            $en_row->locale           = request()->locale ?? config('app.locale') ;
            $en_row->title             = $request->title;
            $en_row->description      = $request->description;
            $en_row->save();

            DB::commit();
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function show($id)
    {
        return $this->model->find($id);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $row                    = $this->model->find($id);
            $row->title             = $request->title;
            $row->publish_date      = date('Y-m-d H:i:s', strtotime($request->publish_date));
            $row->date              = date('Y-m-d', strtotime($request->date));

            $row->is_visible_web       = $request->is_visible_web;
            $row->status               = $request->status;
            $row->description          = $request->description;
            $row->visible_to           = $request->visible_to;
            $row->attachment           = $this->UploadImageUpdate($request->attachment, 'backend/uploads/communication', $row->attachment);
            $row->save();
            
            DB::commit();
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $row = $this->model->find($id);
            $row->delete();

            DB::commit();
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function translates($notice_board_id)
    {
        return $this->notice_board_trans->where('notice_board_id',$notice_board_id)->get()->groupBy('locale');
    }

    public function translateUpdate($request, $id)
    {

        DB::beginTransaction();
        try {
            $delete_old = $this->notice_board_trans->where('notice_board_id',$id)->delete();
            $notice_board = $this->show($id);

            foreach($request->title as $key => $title){
                $row                   = new $this->notice_board_trans;
                $row->notice_board_id        = $id ;
                $row->locale           = $key ;
                $row->title             = $title;
                $row->description      = isset($request->description[$key]) ? $request->description[$key] : $notice_board->description;
                $row->save();
            }

            DB::commit();
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

}
