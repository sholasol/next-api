<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class AuthController extends Controller
{
    public function index()
    {
    }
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email',
            'phone' => 'nullable|string',
            'street' => 'nullable|string',
            'zip' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'password' => 'required|confirmed',
        ]);
        
        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try{
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'street' => $request->street,
                'zip' => $request->zip,
                'city' => $request->city,
                'country' => $request->country,
                'password' => Hash::make($request->password)
            ]);
            
            $accessToken = $user->createToken('authToken')->plainTextToken; // removed parentheses
            
            return response()->json([
                'status' => true,
                'user' => $user,
                'access_token' => $accessToken,
                'message' => 'User Created Successfully'
            ], 200);
        } catch(\Exception $e){
            return response()->json([
                'message' => 'Oops! Failed to create User',
                'errors' => $e->getMessage()
            ], 422);
        }
    }
    
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);
        
        if($validator->fails())
        {
            return response()->json([
                'message' => 'Validation fails',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $credential = [
            "email" => $request->email,
            "password" => $request->password,
        ];
        
        try{
            if(!auth()->attempt($credential)){
                return response([
                    'message' => 'Invalid user credential!'
                ], 401);
            }
            
            $user = User::where('email', $request->email)->firstOrFail();
            
            // This check is redundant since auth()->attempt already verified these credentials
            // But if you want to keep it, remove the parentheses from plainTextToken below
            
            $accessToken = $user->createToken('authToken')->plainTextToken; // removed parentheses
            
            return response()->json([
                'status' => true,
                'user' => $user,
                'access_token' => $accessToken,
                'message' => 'User logged in successfully!'
            ], 200);
        } catch(\Exception $e)
        {
            return response()->json([
                'message' => 'Oops! Something went wrong',
                'errors' => $e->getMessage()
            ], 422);
        }
    }
    
    public function profile()
    {
        $user = Auth::user();
        if(!$user){
            return response([
                'message' => 'User not authenticated'
            ], 401); // Added status code
        }
        
        return response()->json([
            'user' => $user,
            'message' => 'User exists'
        ], 200);
    }
    
    public function logout()
    {
        if(!Auth::user()){
            return response([
                'message' => 'User is not authenticated'
            ], 401);
        }
        
        // For sanctum tokens, we need to revoke the token instead of just Auth::logout()
        Auth::user()->tokens()->delete();
        
        return response()->json([
            'message' => 'User logged out successfully'
        ], 200);
    }
}