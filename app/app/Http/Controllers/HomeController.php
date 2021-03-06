<?php

namespace App\Http\Controllers;

use Auth;
use View;
use Input;
use App\Task;
use Validator;
use App\Company;
use App\UserNexus;
use App\Http\Requests;
use App\CustomerCompany;
use Illuminate\Http\Request;

/**
 * Class HomeController
 * @package App\Http\Controllers
 * @version 1.0
 * @author  AndyLee <root@lostman.org>
 */
class HomeController extends BaseController
{


    /**
     * Home
     * @return mixed
     * @param Request $request
     * @author AndyLee <root@lostman.org>
     */
    public function getHome(Request $request)
    {

        $nexus = new UserNexus();

        $record = $nexus->where('operator_id', Auth::user()->id)->first();


        if(empty($record)){
            return view('home')->with('record', '');
        }

        if($request->session()->has('company_id')){

            return redirect()->to('/dashboard');

        }else{
            $record = $nexus->where('operator_id', Auth::user()->id)->get();

            return view('home')->with('record', $record);
        }

    }

    /**
     * 创建或者选择代账公司
     * @return mixed
     * @author AndyLee <root@lostman.org>
     */
    public function getCreateCompany()
    {
        return view('create_user');
    }

    /**
     * 添加代帐公司
     * @return mixed
     * @param Request $request
     * @author AndyLee <root@lostman.org>
     */
    public function postChooseType(Request $request)
    {
        //TODO 是否限制用户创建的代帐公司个数

        $input = $request->only('type', 'company', 'mobile');
        $rules = [

            'type'    => 'required|min:0|max:1|integer',
            'company' => 'required',
            'mobile'  => 'required|Regex:/^1[0-9]{10}$/'

        ];

        $v = Validator::make($input, $rules);
        if($v->fails()){
            $request->session()->flash('error', $v->messages()->first());
            return redirect()->to('/');
        }

        $company = new Company;

        /**
         * 0为个人版本   1为收费
         */
        if($input['type'] == 0){
            $company->registion_type = 'personal';
        }else{
            $company->registion_type = 'company';
        }

        $company->package_id = $input['type'];
        $company->name = $input['company'];
        $company->status = 0 ; //0为正常运行状态
        $company->mobile = $input['mobile'];
        $company->main_user = Auth::user()->id;
        $company->save();

        /**
         * 绑定代帐公司与操作员
         */
        $nexus = new UserNexus;
        $nexus->operator_id = Auth::user()->id;
        $nexus->user_id = $company->id;
        $nexus->level = 1;
        $nexus->save();

        $request->session()->flash('success', '信息完善成功，欢迎使用代帐通,请选择登录的代帐账户');

        return redirect()->to('/');

    }

    /**
     * 设置代帐公司
     * @return mixed
     * @param Request $request
     * @param $company_id int 公司ID
     * @author AndyLee <root@lostman.org>
     */
    public function getSetCompany(Request $request, $company_id)
    {
        /**
         * 检测是否有权限操作
         */

        $map = array();
        $map['user_id'] = $company_id;
        $map['operator_id'] = auth()->user()->id;

        $nexus = new UserNexus();
        $record = $nexus->where($map)->first();
        if(empty($record)){
            $request->session()->flash('error', '您不属于此代账公司,没有权限进行操作');
            return redirect()->back();
        }

        $request->session()->put('company_id', $company_id);

        return redirect()->to('/dashboard');

    }

    /**
     * 仪表盘
     * @param Request $request
     * @return mixed
     * @author AndyLee <root@lostman.org>
     */
    public function getDashBoard(Request $request)
    {
        if(!$request->session()->has('company_id')){
            $request->session()->flash('error', '请先选择一个代帐公司');
            return redirect()->to('/');
        }

        $today_start = strtotime(date('Y-m-d', time()).' 00:00:00');
        $today_end = strtotime(date('Y-m-d', time()).' 23:59:59');


        /**
         * 获取公司
         */

        $customers = CustomerCompany::where('user_id', $request->session()->get('company_id'));

        $count = $customers->count();

        $new_customer = CustomerCompany::where('user_id', $request->session()->get('company_id'))
            ->whereBetween('create_time', array($today_start, $today_end))->count();


        $month_begin = mktime(0,0,0,date('m'),1,date('Y'));

        $month_end = mktime(23,59,59,date('m'),date('t'),date('Y'));

        /**
         * 获取todo
         */

        $count_task  =  Task::where('user_id', $request->session()->get('company_id'))->count();

        $finish = Task::whereRaw('user_id = ? and is_finish = ?', [$request->session()->get('company_id'), 1])
            ->whereBetween('finish_time', array(
                $month_begin,
                $month_end
            ))->count();

        $order = $customers->orderBy('last_active_time', 'desc')->take(10)->get();


        return view('dashboard')->with('count', $count)
            ->with('new', $new_customer)
            ->with('tasks', $count_task)
            ->with('order', $order)
            ->with('finish', $finish);
    }



}
