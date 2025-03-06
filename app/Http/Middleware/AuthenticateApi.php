<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Device;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $api_key = $request->header('X-API-KEY') ?? '';
        $deviceId = $request->route('deviceId');
        
        $device = Device::where('api_key', $api_key)->first();
        
        if($device && $device->uniq_id == $deviceId){
            return $next($request);
        }

        return response()->json([
            'title' => 'error',
            'description' => 'Unauthorized'
        ], 401);
    }
}
