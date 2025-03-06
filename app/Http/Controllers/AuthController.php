<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Device;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ActivationCode;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected function errorResponse($desc = '', $code = 404)
    {
        return response()->json([
            'title' => 'error',
            'description' => $desc
        ], $code);
    }

    protected function successResponse($device)
    {
        return response()->json([
            'deviceId' => $device->uniq_id,
            'deviceAPIKey' => $device->api_key,
            'deviceType' => $device->device_type,
            'timestamp' => Carbon::now()
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deviceId' => 'required|string|max:121',
            'activationCode' => 'nullable|string'
        ], [
            'deviceId.required' => 'Device Id is required.'
        ]);

        if($validator->fails()){
            return $this->errorResponse($validator->errors()->all(), 422);
        }

        try{

            $device = Device::where('uniq_id', $request->deviceId)->first();

            if(!$device){
                return $this->errorResponse('Device not found', 404);
            }

            if($device->activation_code && !$request->activationCode){
                if($device->device_type == 'free'){
                    return $this->successResponse($device);
                }

                return $this->errorResponse('Device already registered', 400);
            }

            if(!$device->activation_code && !$request->activationCode){
                if($device->device_type == 'unset'){
                    $device->update([
                        'device_type' => 'free',
                        'registration_date' => Carbon::now(),
                        'api_key' => Str::random(32)
                    ]);
    
                    return $this->successResponse($device);
                }
            }

            if(!$device->activation_code && $request->activationCode){
                $activation_code = ActivationCode::where([
                    'code' => $request->activationCode,
                    'is_used' => false
                ])->first();

                if(!$activation_code){
                    return $this->errorResponse('Invalid activation code', 400);
                }

                $device->update([
                    'activation_code' => $activation_code->code,
                    'device_type' => 'leasing',
                    'api_key' => Str::random(32),
                    'registration_date' => Carbon::now()
                ]);

                $activation_code->update([
                    'is_used' => true,
                    'device_id' => $device->id
                ]);

                return $this->successResponse($device);
            }

            return $this->errorResponse('Device already registered', 400);

        }catch(\Exception $e){

            \Log::error('Registration error', [
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Something have wrong', 422);   
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deviceId' => 'required|string|max:121'
        ], [
            'deviceId.required' => 'Device Id is required'
        ]);

        if($validator->fails()){
            return $this->errorResponse($validator->errors()->all(), 422);
        }

        $device = Device::where('uniq_id', $request->deviceId)->whereIn('device_type', ['free', 'leasing'])->first();

        if(!$device){
            return $this->errorResponse('Device not found', 404);
        }

        $device->update(['api_key' => Str::random(32)]);

        return $this->successResponse($device);
    }
}
