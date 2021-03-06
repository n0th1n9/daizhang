<?php

namespace App\Http\Controllers;

use Ramsey\Uuid\Uuid;
use DatePeriod;
use Auth;
use DateInterval;
use DateTime;
use App\Tax;
use Illuminate\Http\Request;
use App\CustomerCompany as Company;
use App\Http\Requests;

class TaxController extends BaseController
{
    /**
     * 获取税务信息
     * @return mixed
     * @param Request $request
     * @author AndyLee <root@lostman.org>
     */
    public function getTaxList(Request $request)
    {
        $company = Company::find($request->session()->get('customer_id'));
        $start = new \DateTime($company->created_at);
        $end      = new DateTime();
        $interval = DateInterval::createFromDateString('1 month');
        $period   = new DatePeriod($start, $interval, $end);

        $card = [];

        foreach ($period as $dt) {

            $map = [
                'user_id' => $request->session()->get('company_id'),
                'company_id' => $request->session()->get('customer_id'),
                'flag' => $dt->format("Ym")
            ];

            $tax = Tax::where($map)->first();
            if(empty($tax)){
                $record = new Tax();
                $record->user_id = $request->session()->get('company_id');
                $record->company_id = $request->session()->get('customer_id');
                $record->operator_id = Auth::user()->id;
                $record->card_name = $dt->format("Ym").'税务申报单';
                $record->finish_time = strtotime($dt->format('Y-m').'-15');
                $record->uuid = Uuid::uuid1();
                $record->guoshui_status = 0;
                $record->dishui_status = 0;
                $record->flag = $dt->format("Ym");
                $record->save();
                $card[] = $record->toArray();
            }else{
                $card[] = $tax->toArray();
            }
        }

        $card = array_reverse($card);
        return view('customer.tax.index')->with('cards', $card);
    }

    /**
     * 完成税务申报
     * @param Request $request
     * @return mixed
     * @author AndyLee <root@lostman.org>
     */
    public function postFinishTaxApply(Request $request){
        $input = $request->only('type', 'flag');

        $map = [
            'user_id' => $request->session()->get('company_id'),
            'company_id' => $request->session()->get('customer_id')
        ];
        /*
         * 国税部分
         */
        if($input['type'] == 'guoshui'){
            $map['guoshui_status'] = 0;

            $tax = Tax::where($map)->first();
            if(!empty($tax)){
                $tax->guoshui_status = 1;
                $tax->save();

                return \Response::json(['message'=> '国税申报完成', 'state' => 'success']);
            }
        }

        if($input['type'] == 'dishui'){
            $map['dishui_status'] = 0;

            $tax = Tax::where($map)->first();
            if(!empty($tax)){
                $tax->dishui_status = 1;
                $tax->save();

                return \Response::json(['message'=> '地税申报完成', 'state' => 'success']);
            }
        }

    }
}
