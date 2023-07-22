<?php
namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    use ApiResponseTrait;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        try {
            $rules = [
                "email" => "required",
                "password" => "required"
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $credentials = $request->only(['email', 'password']);

            $token = Auth::guard('api')->attempt($credentials);


            if (!$token)
                return response()->json(['error' => 'Unauthorized'], 401);

            $user = Auth::guard('api')->user();


            return response()->json(['token' => $token, 'user' => $user]);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'in:super admin,admin',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function update(Request $request, $id){

        $validator = Validator::make($request->all(), [
            'name' => 'string|between:2,100',
            'email' => 'string|email|max:100',
            'password' => 'string|min:6',
            'role' => 'in:super admin,admin',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null, $validator->errors(), 400);
        }

        $user = User::find($id);

        if ($user) {

            $user->update(array_merge(
                $validator->validated(),
                ['password' => bcrypt($request->password)])
            );
            return $this->apiResponse(new UserResource($user), 'The user update', 201);
            
        }else{
            return $this->apiResponse(null, 'The user Not Found', 404);
        }
    }

    public function destroy($id)
    {

        $user = User::find($id);

        if ($user) {

            $user->delete($id);
            return $this->apiResponse(null, 'The user deleted', 200);

        }else{
            return $this->apiResponse(null, 'The user Not Found', 404);
        }
    }
 
}