<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EmailInvitationController extends Controller
{
    private $success = false;
    private $message = '';

    /**
     * This function is used to return email verification view
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.email-invitation.index');
    }

    /**
     * This is used to send invitation link
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendInvitation(Request $request)
    {
        $email = $request->input('email');
        $this->message = 'Enter user email';
        $this->success = false;
        if (!empty($email)) {
            $obj = new Invitation();
            $obj->name = $request->input('name');
            $obj->email = $request->input('email');
            $obj->save();
            Mail::to($email)->send(new InvitationMail(Auth::user()));
            $this->message = 'Invitation sent successfully';
            $this->success = true;
        }

        return response()->json(['success' => $this->success, 'message' => $this->message]);
    }
}
