<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Device;
use App\Models\LeasingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    protected function errorResponse($desc = '', $code = 404)
    {
        return response()->json([
            'title' => 'error',
            'description' => $desc
        ], $code);
    }

    public function get_info_device($deviceId)
    {
        $device = Device::where('uniq_id', $deviceId)->first();

        if($device->device_type == 'free'){
            return response()->json([
                'deviceId' => $device->uniq_id,
                'deviceType' => $device->device_type,
                'leasingPeriods' => [],
                'timestamp' => now(),
            ],200);
        }

        $leasing_period = $device->leasing_period()->where('is_active', true)->first();
        $leasing_plan = LeasingPlan::find($leasing_period->leasing_plan_id);

        return response()->json([
            'deviceId' => $device->uniq_id,
            'deviceType' => $device->device_type,
            'deviceOwner' => $device->user->name,
            'deviceOwnerDetails' => $device->device_owner_details,
            'dateofRegistration' => $leasing_period->created_at->format('Y-m-d'),
            'leasingPeriodsComputed' => [
                'leasingConstructionId' => $leasing_plan->id,
                'leasingConstructionMaximumTraining' => $leasing_plan->max_training_session,
                'leasingConstructionMaximumDate' => $leasing_plan->max_date,
                'leasingActualPeriodStartDate' => $leasing_period->created_at->format('Y-m-d'),
                'leasingNextCheck' => $leasing_period->leasing_next_check
            ],
            'leasingPeriods' => $device->leasing_period->transform(function($item){
                $leasing_plan = LeasingPlan::find($item->leasing_plan_id);
                return  [
                    'leasingConstructionId' => $leasing_plan->id,
                    'leasingConstructionMaximumTraining' => $leasing_plan->max_training_session,
                    'leasingConstructionMaximumDate' => $leasing_plan->max_date
                ];
            }),
            'timestamp' => now()
        ], 200);
    }

    public function add_leasing_plan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deviceId' => 'required|string|max:121',
            'leasing_plan_id' => 'required'
        ]);

        if($validator->fails()){
             return $this->errorResponse($validator->errors()->all(), 422);
        }

        $device = Device::where('uniq_id', $request->deviceId)->whereIn('device_type', ['leasing', 'restricted'])->first();

        if(!$device){
            return $this->errorResponse('Device not found', 404);
        }

        $leasing_period = $device->leasing_period()->where('is_active', true)->first();

        if(!$leasing_period){
            $leasing_plan = LeasingPlan::find($request->leasing_plan_id);
    
            if(!$leasing_plan){
                return $this->errorResponse('Leasing plan not found', 404);
            }

            $device->leasing_period()->create([
                'leasing_plan_id' => $leasing_plan->id,
                'is_active' => true,
                'leasing_next_check' => Carbon::now()->addDays(1)
            ]);

            if($device->device_type == 'restricted'){
                $device->update([
                    'device_type' => 'leasing'
                ]);
            }

            return response()->json([
                'title' => 'success',
                'description' => 'Add leasing plan successfully.'
            ], 200);
        }

        return $this->errorResponse('You already have an active leasing plan', 400);
    }

    public function update_leasing_period(Request $request, $leasingId)
    {
        $validator = Validator::make($request->all(), [
            'deviceId' => 'required|string|max:121',
            'deviceTrainings' => 'required|integer'
        ]);

        if($validator->fails()){
             return $this->errorResponse($validator->errors()->all(), 422);
        }

        try{
            DB::beginTransaction();

            $device = Device::where('uniq_id', $request->deviceId)->first();
            $leasingPeriod = $device->leasing_period()->where(['id' => $leasingId, 'is_active' => true])->first();
            
            if(!$leasingPeriod){
                return $this->errorResponse('No active leasing found', 400);
            }

            $leasingPlan = LeasingPlan::find($leasingPeriod->leasing_plan_id);
            $leasingPeriod->increment('completed_trainings', $request->deviceTrainings);
            $leasingPeriod->update([
                'leasing_next_check' => Carbon::now()->addDays(1)
            ]);

            if(
                (!is_null($leasingPlan?->max_training_session) && $leasingPeriod->completed_trainings >= $leasingPlan?->max_training_session) ||
                (!is_null($leasingPlan?->max_date) && Carbon::now()->greaterThanOrEqualTo($leasingPlan?->max_date))
            ){
                $leasingPeriod->update([
                    'is_active' => false
                ]);

                $device->update([
                    'device_type' => 'restricted'
                ]);

                DB::commit();

                return response()->json([
                    'title' => 'success',
                    'description' => 'Your leasing period has expired'
                ], 200);
            }

            DB::commit();

            return response()->json([
                'title' => 'success',
                'description' => 'Leasing periods update successfully'
            ], 200);

        }catch(\Exception $e){
            DB::rollBack();
            
            \Log::error('Something have wrong', [
                'error' =>$e->getMessage()
            ]);

            return $this->errorResponse('Something have wrong', 422);
        }

        
    }
}
