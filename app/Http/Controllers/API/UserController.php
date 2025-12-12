<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\EnvatoApi;
use App\Models\User;
use File;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
//use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $users = User::join('branches', 'users.branch_id', '=', 'branches.id')
            ->join('roles', 'users.role', '=', 'roles.id')
            ->where('users.status', 'Active')
            ->where('users.name', 'LIKE', '%' . $request->keyword . '%')
            ->limit(20)
            ->offset($request->start)
            ->orderBy('users.id', 'DESC')
            ->get(['users.*', 'branches.name as branchName', 'branches.code as branchCode', 'roles.name as roleName']);

        $branches = Branch::where('status', 'Active')
            ->get();

        $userRoles = Role::all();

        $totalRecords = User::where('status', 'Active')
            ->count();

        return [
            'records' => $users,
            'userRoles' => $userRoles,
            'branches' => $branches,
            'totalRecords' => $totalRecords,
            'limit' => 20,
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'role' => ['required'],
            'branch_id' => ['required'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:6'],
            'status' => ['required'],
        ]);

        $user = new User([
            'branch_id' => $request->branch_id,
            'role' => $request->role,
            'address' => $request->address,
            'contact' => $request->contact,
            'image' => $request->image,
            'description' => $request->description,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
        ]);

        $user->save();
        $role = Role::where(['id' => $request->role])->first();
        $user->assignRole($role->name);

        return response()->json([
            'alert' => 'info',
            'msg' => 'User Created Successfully',
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'deviceName' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect',
            ]);
        }

        return [
            'token' => $user->createToken($request->deviceName)->plainTextToken,
            'permissionList' => json_encode($user->getPermissionsViaRoles()),
            'userName' => $user->name,
            'currency' => Config::get('constant.currency'),
        ];
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['msg' => 'Logout Successful']);
    }

    public function show($id)
    {
        $user = User::find($id);
        return response()->json([$user]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', "unique:users,email,$request->id"],
            'branch_id' => 'required',
            'role' => 'required',
            'id' => 'required',
        ]);

        $user = User::find($request->id);
        $user->update($request->all());

        $user->roles()->detach();

        $role = Role::where(['id' => $request->role])->first();

        $user->assignRole($role->name);

        return response()->json([
            'alert' => 'info',
            'msg' => 'User Updated Successfully',
        ]);
    }

    public function changePin(Request $request)
    {
        $request->validate([
            'password' => ['required', 'min:6'],
        ]);

        $password = Hash::make($request->password);

        User::where('id', $request->id)
            ->update(
                array('password' => $password)
            );

        return response()->json([
            'alert' => 'info',
            'msg' => 'User pin changed successfully',
        ]);
    }

    public function delete(Request $request)
    {
        $user = User::find($request->id);
        $user->update($request->all());

        return response()->json([
            'alert' => 'info',
            'msg' => 'User Deleted Successfully',
        ]);
    }

    public function imageUploadPost(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'id' => 'required',
        ]);

        if ($request->previousImage != 'default.jpg') {
            File::delete(resource_path('js/assets/users'), $request->previousImage);
        }

        $imageName = time() . '.' . $request->image->extension();

        $request->image->move(resource_path('js/assets/users'), $imageName);

        User::where('id', $request->id)
            ->update(
                array('image' => $imageName)
            );

        return response()->json([
            'alert' => 'info',
            'msg' => 'User Image Uploaded Successfully',
        ]);
    }

    public function searchUser(Request $request)
    {
        $keyword = $request['keyword'];

        $options = User::where('status', 'Active')
            ->where('contact', '=', $request->keyword)
            ->orWhere('name', 'LIKE', '%' . $request->keyword . '%')
            ->orWhere('email', 'LIKE', '%' . $request->keyword . '%')
            ->limit(20)
            ->offset($request->start)
            ->orderBy('id', 'DESC')
            ->get();

        return [
            'records' => $options,
        ];
    }

    public function activeBranchUser()
    {
        $b = Branch::where('id', Auth::user()->branch_id)
            ->first();

        return [
            'userName' => Auth::user()->name,
            'branchName' => $b->name,
            'copyright' => Config::get('constant.copyright'),
        ];
    }

    public function checkNewInstallation()
    {
        $res = true;

        if (!Schema::hasTable('branches')) {
            $res = false;
        }

        return response()->json($res);
    }

    public function installApp(Request $request)
    {

        $request->validate([
            'userEmail' => 'required|email',
            'password' => 'required',
            'storeName' => 'required',
            'storeCode' => 'required',
            'address' => 'required',
            'userName' => 'required',
            'userEmail' => 'required',
            'purchaseCode' => 'required',
        ]);

        $storeName = $request['storeName'];
        $storeCode = $request['storeCode'];
        $address = $request['address'];
        $userName = $request['userName'];
        $userEmail = $request['userEmail'];
        $password = $request['password'];
        $purchaseCode = $request['purchaseCode'];

        $res = '';

        try
        {
            $en = new EnvatoApi();

            $purchaseRes = $en->verifyPurchaseCode($purchaseCode);

            if ($purchaseRes == "success") {

                if (!Schema::hasTable('branches')) {
                    $res = Artisan::call('migrate', ["--force" => true]);
                    $res = Artisan::output();

                    Artisan::call('db:seed', ['class' => 'DatabaseSeeder']);
                    $res = Artisan::output();

                    $branch = new Branch([
                        'name' => $storeName,
                        'code' => $storeCode,
                        'address' => $address,
                        'status' => 'Active',
                        'show_1' => 'true',
                        'tax_name_1' => 'SGST',
                        'tax_value_1' => 3,
                        'required_optional_1' => 'Required',
                        'link1' => 10,
                        'show_2' => 'true',
                        'tax_name_2' => 'CGST',
                        'tax_value_2' => 3,
                        'required_optional_2' => 'Required',
                        'link2' => 9,
                        'show_3' => 'false',
                        'tax_name_3' => null,
                        'tax_value_3' => 0,
                        'required_optional_3' => 'Optional',
                        'link3' => 5,
                    ]);

                    $branch->save();

                    $role = Role::where(['name' => 'Admin'])->first();

                    $user = new User([
                        'branch_id' => $branch->id,
                        'role' => $role->id,
                        'address' => '',
                        'contact' => '',
                        'image' => 'default.jpg',
                        'description' => 'here goes description',
                        'name' => $userName,
                        'email' => $userEmail,
                        'password' => Hash::make($password),
                        'status' => 'Active',
                    ]);

                    $user->save();

                    Artisan::call('db:seed', ['class' => 'AssignRoleSeeder']);
                    $res = Artisan::output();

                    $res = 'success';
                } else {
                    $res = 'exists';
                }
            } else {
                $res = $purchaseRes;
            }

        } catch (\Exception$ex) {
            $res = $ex->getMessage();
        }

        return response()->json($res);
    }
}
