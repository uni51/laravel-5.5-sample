<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class SignupController extends Controller
{
    /**
     * 検証済みデータ格納用セッションキー
     */
    protected $sessionKey = 'SignupData';

    /**
     * 登録画面
     * 
     * @params
     * User $user：新規のユーザーモデルを取得
     */
    public function index(User $user)
    {
        if ($data = \Session::get($this->sessionKey)) {
            $user->fill($data);
        }

        return view('signup.index')->with(compact('user'));
    }

    // public function index()
    // {
    //     $user = new User();
    //     if ($data = \Session::get($this->sessionKey)) {
    //         $user->fill($data);
    //     }
    //     return view('signup.index')->with(compact('user'));
    // }   

    /**
     * 検証
     */
    public function postIndex(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|max:255',
            'email'    => 'required|max:255|email|unique:users,email',
            'password' => 'required|confirmed|password_between:4,30|password_string',
        ]);

        // こちらは不要
        // $data = $request->only('name', 'email', 'password');

        // 検証済みデータをセッションに保存        
        \Session::put($this->sessionKey, $data);

        return redirect()->route('signup.confirm');
    }

    /**
     * 確認画面
     */
    public function confirm()
    {
        if (! $data = \Session::get($this->sessionKey)) {
            return redirect()->route('signup.index');
        }

        return view('signup.confirm')->with(compact('data'));
    }

    /**
     * 登録処理等
     */
    public function postConfirm(User $user)
    {
        if (! $data = \Session::get($this->sessionKey)) {
            return redirect()->route('signup.index');
        }

        $data['password'] = bcrypt($data['password']);

        $user->fill($data)->save();

        // 登録したユーザーを、ユーザー区分でログイン状態とする（config/auth.php参照）
        auth('user')->login($user);

        // 使用済みのセッションを削除
        \Session::forget($this->sessionKey);

        return redirect()->route('signup.thanks');
    }

    /**
     * 完了画面
     */
    public function thanks()
    {
        return view('signup.thanks');
    }
}
