<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\RegisterRequestRequest;
use App\Models\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class RegisterRequestController extends Controller
{
    public function request(RegisterRequestRequest $request)
    {
        $data = $request->validated();
        RegisterRequest::create([
            'name' => $data['name'],
            'form_of_own' => $data['form_of_own'],
            'contact' => $data['contact'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'bank_info' => $data['bank_info'],
            'unp' => $data['unp'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'pending',
        ]);

        return Redirect::route('home')
            ->with('message', 'Заявка отправлена, мы свяжемся с вами в ближайшее время');
    }
}
