<?php
/**
 * 银行账户控制器
 * @package App\Http\Controllers
 * @version 1.0
 * @author  AndyLee <root@lostman.org>
 */
namespace App\Http\Controllers;

use Auth;
use App\BankAccount;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Session;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;

/**
 * Class BankAccountController
 * @package App\Http\Controllers
 * @version 1.0
 * @author  AndyLee <root@lostman.org>
 */
class BankAccountController extends BaseController
{
    /**
     * 列出银行账户
     * @return mixed
     * @author AndyLee <root@lostman.org>
     */
    public function getBankAccount()
    {
        $bank_list = BankAccount::where('company_id', Session::get('customer_id'))->get();
        return view('customer.bank.index')->with('lists', $bank_list);
    }

    /**
     * 创建银行账户
     * @return \Illuminate\View\View
     * @author AndyLee <root@lostman.org>
     */
    public function getCreateBankAccount()
    {
        return view('customer.bank.create');
    }

    /**
     * 修改银行账户
     * @return mixed
     * @param integer $id 银行账户
     * @author AndyLee <root@lostman.org>
     */
    public function getModifyBankAccount($id)
    {
        try{
            $account = BankAccount::findOrFail($id);
        }catch (ModelNotFoundException $e){
            Session::flash('error', '银行账户信息不存在');
            return redirect()->back();
        }

        if($account->company_id != Session::get('customer_id')){
            Session::flash('error', '您没有权限修改此账户');
            return redirect()->back();
        }

        return view('customer.bank.modify')->with('account', $account);
    }

    /**
     * 删除银行账户信息
     * @param Request $request
     * @return redirect
     * @param $id
     * @author AndyLee <root@lostman.org>
     */
    public function postModifyBankAccount(Request $request, $id){

        $input = $request->only('account', 'register_user_name', 'bank_branch', 'card_name',
            'phone', 'bank', 'address', 'province', 'city', 'town', 'bank_url', 'bank_address', 'remarks');

        $rules = [
            'account' => 'required',
//            'bank_branch' => 'required',
            'card_name' => 'required',
            'bank'      => 'required',
//            'province'  => 'required',
//            'city'      => 'required',
//            'town'      => 'required',
            'bank_url'  => 'url',
            'address'   => 'integer'
        ];

        $v = Validator::make($input, $rules);
        if($v->fails()){
            Session::flash('error', $v->messages()->first());
            return redirect()->back();
        }

        try{
            $account = BankAccount::findOrFail($id);
        }catch (ModelNotFoundException $e){
            Session::flash('error', '银行账户信息不存在');
            return redirect()->back();
        }

        if($account->company_id != Session::get('customer_id')){
            Session::flash('error', '您没有权限修改此账户');
            return redirect()->back();
        }

        $account->account = $input['account'];
        $account->account_name = $input['card_name'];
        $account->bank = $input['bank'];
        $account->bank_branch = $input['bank_branch'];
        $account->remarks = $input['remarks'];
        $account->province = $input['province'];
        $account->city = $input['city'];
        $account->town = $input['town'];
        $account->bank_url = $input['bank_url'];
        $account->bank_address = $input['bank_address'];
        $account->state_code = $input['address'];
        $account->save();

        Session::flash('success', '更新银行账户成功');

        return redirect()->to(action('BankAccountController@getBankAccount', Session::get('customer_id')));




    }

    /**
     * 删除银行账户
     * @param integer $id
     * @return redirect
     * @author AndyLee <root@lostman.org>
     */
    public function getDeleteBankAccount($id)
    {
        try{
            $account = BankAccount::findOrFail($id);
        }catch (ModelNotFoundException $e){
            Session::flash('error', '银行账户信息不存在');
            return redirect()->back();
        }

        if($account->company_id != Session::get('customer_id')){
            Session::flash('error', '您没有权限修改此账户');
            return redirect()->back();
        }
        $account->delete();
        Session::flash('success', '删除银行账户完成');
        return redirect()->to(action('BankAccountController@getBankAccount', Session::get('customer_id')));

    }

    /**
     * 创建银行账户 post
     * @param Request $request
     * @return mixed
     * @author AndyLee <root@lostman.org>
     */
    public function postCreateBankAccount(Request $request)
    {
        $input = $request->only('account', 'register_user_name', 'bank_branch', 'card_name',
            'phone', 'bank', 'address', 'province', 'city', 'town', 'bank_url', 'bank_address', 'remarks');

        $rules = [
            'account' => 'required',
//            'bank_branch' => 'required',
            'card_name' => 'required',
//            'phone'     => 'required',
            'bank'      => 'required',
//            'province'  => 'required',
//            'city'      => 'required',
//            'town'      => 'required',
            'bank_url'  => 'url',
            'address'   => 'integer'
        ];

        $v = Validator::make($input, $rules);
        if($v->fails()){
            Session::flash('error', $v->messages()->first());
            return redirect()->back();
        }

        $account = new BankAccount();
        $account->company_id = Session::get('customer_id');
        $account->operator_id = Auth::user()->id;
        $account->account_type = ''; //TODO账户类型
        $account->account = $input['account'];
        $account->account_name = $input['card_name'];
        $account->bank = $input['bank'];
        $account->bank_branch = $input['bank_branch'];
        $account->remarks = $input['remarks'];
        $account->province = $input['province'];
        $account->city = $input['city'];
        $account->town = $input['town'];
        $account->bank_url = $input['bank_url'];
        $account->bank_address = $input['bank_address'];
        $account->state_code = $input['address'];
        $account->save();

        Session::flash('success', '添加银行账户成功');

        return redirect()->to(action('BankAccountController@getBankAccount', Session::get('customer_id')));
    }
}
