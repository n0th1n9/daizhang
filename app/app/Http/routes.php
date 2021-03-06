<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


Route::group(['prefix' => 'wx', 'middleware' => ['weixin_auth']], function () {
    Route::get('/bind', 'Weixin\BindController@index');
//    Route::group(['middleware' => ['weixin_user_filter']], function () {
//        Route::get('/', function () {
//            return "Weixin Home";
//        });
//    });
});



Route::get('/', 'HomeController@getHome');
Route::get('/create', 'HomeController@getCreateCompany');

Route::post('/choose_type', 'HomeController@postChooseType');

Route::get('/set_company/{company_id}', 'HomeController@getSetCompany');

Route::get('/dashboard', 'HomeController@getDashBoard');

//TODO 需要filter保护
Route::get('/company/member', 'CompanyController@getMember');
Route::get('/company/invite', 'CompanyController@getInvite');


/**
 * 路由认证
 */
Route::group(['prefix' => 'auth'], function () {
    Route::get('/login', 'Auth\AuthController@getLogin');
    Route::post('/login', 'Auth\AuthController@postLogin');
    Route::get('/register', 'Auth\AuthController@getRegister');
    Route::post('/register', 'Auth\AuthController@postRegister');
    Route::get('/logout', 'Auth\AuthController@getLogOut');
    Route::get('/sms', 'Auth\AuthController@getCaptcha');

    /**
     * 选择代帐账户
     */
    Route::get('/choose_account', 'Auth\AuthController@getChooseType');
});

/**
 * 客户模块
 */
Route::group(['prefix' => 'customer', 'middleware' => ['session_filter']], function () {

    Route::get('/', 'CustomerController@getIndex');
    Route::get('/order/{order}', 'CustomerController@getOrderCustomer');
    Route::get('/captcha', 'CustomerController@getJsgsjImg');

    Route::get('/create', 'CustomerController@getCreate');
    Route::post('/create', 'CustomerController@postCreate');

    Route::get('/company', 'CompanyController@getCompanyInfo');


    /**
     * 从工商局获取类似公司信息
     */
    Route::post('/company_info', 'CustomerController@postMainProcess');

    /**
     * 从工商局获取指定公司信息
     */
    Route::get('/get_company', 'CustomerController@getCompanyDetail');
    Route::post('/get_company', 'CustomerController@postCompanyDetail');

    /**
     * 手动录入客户
     */
    Route::get('/write', 'CustomerController@getWrite');
    Route::post('/write', 'CustomerController@postWrite');


    Route::group(['middleware' => 'company_filter'], function () {


        /**
         * 设置默认客户
         */
        Route::get('/set_customer/{uuid}', 'CustomerController@getSetCustomerCompany');
        Route::get('/{id}', 'CustomerController@getCustomerCompanyInfo');
        Route::get('/{id}/modify', 'CustomerController@getModifyCompany');
        Route::post('/{id}/modify', 'CustomerController@postModifyCompany');

        /**
         * 股东信息
         *
         */
        Route::get('/{id}/partner', 'PartnerController@getPartner');
        Route::post('/{id}/partner', 'PartnerController@postPartner');
        Route::get('/{id}/partner/create', 'PartnerController@getCreatePartner');
        Route::post('/{id}/partner/create', 'PartnerController@postCreatePartner');
        Route::get('/{id}/partner/modify/{pid}', 'PartnerController@getModifyPartner');
        Route::post('/{id}/partner/modify/{pid}', 'PartnerController@postModifyPartner');
        Route::get('/{id}/partner/delete/{pid}', 'PartnerController@getDeletePartner');

        /**
         * 联系人信息
         */
        Route::get('/{id}/contact', 'ContactController@getContact');
        Route::get('/{id}/contact/create', 'ContactController@getCreateContact');
        Route::post('/{id}/contact/create', 'ContactController@postCreateContact');
        Route::get('/{id}/contact/default/{pid}', 'ContactController@getSetDefaultContact');
        Route::get('/{id}/contact/modify/{pid}', 'ContactController@getModifyContact');
        Route::post('/{id}/contact/modify/{pid}', 'ContactController@postModifyContact');
        Route::get('/{id}/contact/delete/{pid}', 'ContactController@getDeleteContact');

        /**
         * 代办事项LIST
         */
        Route::get('/{id}/task', 'TaskController@getTaskList');
        Route::get('/{id}/task/create', 'TaskController@getCreateTask');
        Route::post('/{id}/task/create', 'TaskController@postCreateTask');
        Route::post('/{id}/task/finish', 'TaskController@postFinishTask');
        Route::post('/{id}/task/delete', 'TaskController@postRemoveTask');


        /**
         * 公司银行账户
         */

        Route::get('/{id}/bank_account', 'BankAccountController@getBankAccount');
        Route::post('/{id}/bank_account', 'BankAccountController@postBankAccount');
        Route::get('/{id}/bank_account/create', 'BankAccountController@getCreateBankAccount');
        Route::post('/{id}/bank_account/create', 'BankAccountController@postCreateBankAccount');
        Route::get('/{id}/bank_account/modify/{pid}', 'BankAccountController@getModifyBankAccount');
        Route::post('/{id}/bank_account/modify/{pid}', 'BankAccountController@postModifyBankAccount');
        Route::get('/{id}/bank_account/delete/{pid}', 'BankAccountController@getDeleteBankAccount');


        /**
         * 证件信息
         */
        Route::get('/{id}/certificate', 'CertificateController@getCertificates');
        Route::get('/{id}/certificate/create', 'CertificateController@getCreateCertificate');
        Route::post('/{id}/certificate/create', 'CertificateController@postCreateCertificate');

        Route::post('/{id}/certificate/upload', 'CertificateController@postUploadCertificate');
        Route::get('/{id}/certificate/delete', 'CertificateController@getDeleteCertificate');
        Route::get('/{id}/certificate/{pid}/detail', 'CertificateController@getCertificateDetail');
        Route::get('/{id}/certificate/download', 'CertificateController@getDownloadCertificate');


        /**
         * 公司账户与密码管理
         */

        Route::get('/{id}/password', 'PasswordController@getPassword');
        Route::get('/{id}/password/create', 'PasswordController@getCreatePassword');
        Route::post('/{id}/password/create', 'PasswordController@postCreatePassword');
        Route::post('/{id}/password/decrypt', 'PasswordController@postDecryptHash');
        Route::get('/{id}/password/modify/{pid}', 'PasswordController@getModifyPassword');
        Route::post('/{id}/password/modify/{pid}', 'PasswordController@postModifyPassword');
        Route::get('/{id}/password/delete', 'PasswordController@getDeletePassword');

        Route::get('/{id}/tax', 'TaxController@getTaxList');
        Route::post('/{id}/apply', 'TaxController@postFinishTaxApply');


        Route::get('/{companyId}/tax', 'TaxController@getTaxList');
        Route::post('/{companyId}/apply', 'TaxController@postFinishTaxApply');

        Route::get('/{companyId}/bill', 'CompanyBillController@getAllBills');
        Route::get('/{companyId}/bill/add', 'CompanyBillController@toAddBill');
        Route::post('/{companyId}/bill/add', 'CompanyBillController@addBill');
        Route::get('/{companyId}/bill/{id}/update', 'CompanyBillController@toUpdateBill');
        Route::post('/{companyId}/bill/{id}/update', 'CompanyBillController@updateBill');
        Route::any('/{companyId}/bill/{id}/delete', 'CompanyBillController@deleteBill');
        Route::get('/{companyId}/bill/{id}/pay', 'CompanyBillController@toPayBill');
        Route::post('/{companyId}/bill/{id}/pay', 'CompanyBillController@payBill');
        Route::any('/{companyId}/bill/{id}/do-invoice', 'CompanyBillController@doInvoice');

        Route::get('/{companyId}/cycle-bill/add', 'CompanyBillController@toAddCycleBill');
        Route::post('/{companyId}/cycle-bill/add', 'CompanyBillController@addCycleBill');
        Route::any('/{companyId}/cycle-bill/{id}/delete', 'CompanyBillController@deleteCycleBill');
        Route::get('/{companyId}/cycle-bill/{id}/update', 'CompanyBillController@toUpdateCycleBill');
        Route::post('/{companyId}/cycle-bill/{id}/update', 'CompanyBillController@updateCycleBill');


        Route::post('/{companyId}/bill/notice', 'CompanyBillController@sendBillNotice');

    });

});

Route::get('/unpaid-bill', 'CompanyBillController@getSummaryUnpaidBills');


