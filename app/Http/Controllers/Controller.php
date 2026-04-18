<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function showUserManual()
    {
        // Path to the user manual PDF in the public directory
        $filePath = public_path('manuals/user_manual.pdf');

        if (file_exists($filePath)) {
            return Response::file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="user_manual.pdf"',
            ]);
        } else {
            return response()->json([
                'error' => 'User manual not found.'
            ], 404);
        }
    }

     public function privacyPolicy()
    {
            return view('backend.privacy');
    }


   
}
