<?php

namespace App\Http\Controllers\Auth;

use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use View;
use Hash;
use Input;
use Cache;
use Session;
use App\User;
use Redirect;
use Response;
use Validator;
use App\Library\Sms;
use App\Http\Controllers\BaseController;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers;


    public function __construct()
    {
//        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }


    /**
     * 登入系统
     * @return mixed
     * @author AndyLee <root@lostman.org>
     */
    public function getLogin()
    {
        return View::make('login');
    }

    /**
     * 登入系统
     * @author AndyLee <root@lostman.org>
     */
    public function postLogin()
    {
        $input = Input::Only('mobile', 'password');
        $rules = [
           'mobile' => 'required|Regex:/^1[0-9]{10}$/',
            'password' => 'required|min:6|max:16'
        ];

        $v = Validator::make($input, $rules);
        if($v->fails()){
            Session::flash('error', $v->messages()->first());
            return redirect()->to('/auth/login');
        }

        if(Auth::attempt(['mobile' => $input['mobile'], 'password' => $input['password']])){

            Session::flash('success', '登录成功');
            return redirect()->intended('/');

        }else{
            Session::flash('error', '手机号或者密码不正确');
            return redirect()->to('/auth/login');

        }


    }

    /**
     * 注册页面
     * @return mixed
     * @author AndyLee <root@lostman.org>
     */
    public function getRegister()
    {
        return View::make('register');
    }


    /**
     * 登出
     * @author AndyLee <root@lostman.org>
     */
    public function getLogout()
    {
        if(Auth::check()){
            Auth::logout();
        }

        Session::flush();

        Session::flash('success', '登出成功');
        return Redirect::to('/auth/login');

    }


    /**
     * 获取验证码
     * @param Request $request
     * @return json
     * @author AndyLee <root@lostman.org>
     */
    public function getCaptcha(Request $request)
    {
        $input = $request->get('mobile');

        if(!preg_match("/^13[0-9]{1}[0-9]{8}$|15[056189732]{1}[0-9]{8}$|17[056189732]{1}[0-9]{8}$|18[012356789]{1}[0-9]{8}$/",$input)){
            return Response::json(['message' => '手机号码格式不正确', 'message_code' => -1]);
        }

        $ip = $request->ip();

        $time = new \DateTime();

        $count = Cache::get($ip.'-'.$time->format('Y-m-d'));

        if($count > 10){
            return Response::json(['message' => '发送验证码次数超出阀值', 'message_code'=> -2]);
        }


        Cache::increment($ip.'-'.$time->format('Y-m-d'));
        Cache::put($input, 'aa', 3);

        $user = User::where('mobile', $input)->first();
        if(!empty($user)){
            return Response::json(['message' => '手机号码已经注册', 'message_code' => -3]);
        }


        $captcha = mt_rand(100000, 999999);

        $sms = new Sms();
        $text="【白羽软件】您的验证码是".$captcha.' ，请于3分钟内填写。';
        $result = $sms->sendMessage($text, $input);


        if($result->code < 0){
            //@see http://www.yunpian.com/api/retcode.html
            return Response::json(['message'=> '验证码发送失败, 网关异常', 'message_code' => -4]);
        }


        Cache::increment($ip.'-'.$time->format('Y-m-d'));
        Cache::put($input, $captcha, 3);

        return Response::json(['message' => '验证码发送成功, 请检查您的手机', 'message_code' => 1]);

    }

    /**
     * 注册Action
     * @return mixed
     * @author AndyLee <root@lostman.org>
     */
    public function postRegister()
    {
        $input = Input::Only('phone', 'nickname', 'secret', 'captcha');

        $rules = [
            'phone'    => 'required|Regex:/^1[0-9]{10}$/|unique:user_operators,mobile',
            'nickname' => 'required|min:2|max:8',
            'secret'   => 'required|min:6|max:16',
            'captcha'  => 'required'
        ];

        $message = [
            'phone.required'    => '手机号码必填',
            'phone.Regex'       => '手机号码不规范',
            'phone.unique'      => '手机号码已存在',
            'nickname.required' => '昵称必填',
            'nickname.min'      => '昵称最少2位',
            'nickname.max'      => '昵称最长8位',
            'secret.required'   => '密码必填',
            'secret.min'        => '密码最少6位',
            'secret.max'        => '密码最长16位',
            'captcha'           => '短信验证码必填'
        ];

        $v = Validator::make($input, $rules, $message);
        if($v->fails()){
            Session::flash('error', $v->messages()->first());
            return Redirect::to('/auth/register');
        }

        $captcha = Cache::get($input['phone']);
        if($captcha != $input['captcha']){
            Session::flash('error', '短信验证码不正确');
            return Redirect::to('/auth/register');
        }

        /**
         * 验证完成
         */
        $operator = new User;
        $operator->mobile = $input['phone'];
        $operator->username = $input['nickname'];
        $operator->password = Hash::make($input['secret']);
        $operator->avatar = get_static('assets/avatar/'.mt_rand(1,43).'.png');
        $operator->save();

        /**
         * 注册完成，自动登录
         */

        Auth::attempt(['mobile' => $input['phone'], 'password' => $input['secret']]);

        Session::flash('success', '注册成功,系统已经自动为您登录');

        return redirect()->intended('/');

    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

}
