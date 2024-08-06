<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    public function all(Request $req)
    {
        try {
            $user_id = $req->attributes->get('user_id');
            $todos = Todo::where('user_id', $user_id)->orderBy('due_date', 'desc')->orderBy('created_at', 'desc')->get();

            return response()->json([
                'msg' => 'success',
                'data' => $todos,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 500);
        }
    }
    public function getById($id)
    {

        try {
            $todo = Todo::where('id', $id)->first();
            // dd($todo);
            if(!$todo){
                return response()->json([
                    'msg' => 'data tidak ada',
                    'data' => $todo,
                ], 400);
            }
            return response()->json([
                'msg' => 'success',
                'data' => $todo,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 500);
        }
    }
    public function create(Request $req)
    {
        try {
            $v = Validator::make($req->all(), [
                'task' => 'required'
            ]);

            if ($v->fails()) {
                return response()->json([
                    'errors' => $v->errors(),
                ], 422);
            }

            $user_id = $req->attributes->get('user_id');

            $todo = new Todo();
            $todo->user_id = $user_id;
            $todo->task = $req->task;
            $todo->due_date = $req->due_date ? $req->due_date : $todo->due_date;
            $todo->save();

            return response()->json([
                'msg' => 'success',
                'data' => $todo,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(Request $req, $id)
    {
        try {
            $v = Validator::make($req->all(), [
                'task' => 'required'
            ]);

            if ($v->fails()) {
                return response()->json([
                    'errors' => $v->errors(),
                ], 422);
            }

            $todo = Todo::where('id', $id)->first();

            $todo->task = $req->task;
            $todo->due_date = $req->due_date ? $req->due_date : $todo->due_date;
            $todo->save();

            return response()->json([
                'msg' => 'success',
                'data' => $todo,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $todo = Todo::where('id', $id)->first()->delete();

            return response()->json([
                'msg' => 'success',

            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 500);
        }
    }
    public function check($id)
    {
        try {
            $todo = Todo::where('id', $id)->first();
            $todo->isChecked = 1;
            $todo->save();

            return response()->json([
                'msg' => 'success',
                'data' => $todo,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 500);
        }
    }
    public function uncheck($id)
    {
        try {
            $todo = Todo::where('id', $id)->first();
            $todo->isChecked = 0;
            $todo->save();

            return response()->json([
                'msg' => 'success',
                'data' => $todo,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 500);
        }
    }
}
