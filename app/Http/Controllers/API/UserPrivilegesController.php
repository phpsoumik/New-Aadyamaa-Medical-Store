<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;



class UserPrivilegesController extends Controller
{
	public function index(Request $request)
	{
        if($request->roleID != 0)
        {
            $role = Role::findOrFail($request->roleID);
            $assignedPermission = $role->getAllPermissions()->pluck('id');
        }
        else
        {
            $assignedPermission = [];
        }

		$roles = Role::all();
		$permission = Permission::all();
		
		return [
			'roles' => $roles,
			'permission' => $permission,
			'assignedPermission' => $assignedPermission,
		];
	}

    public function store(Request $request)
	{
		$request->validate([
			'roleID'            => ['required'],
			'permission_list'    => ['required'],
		]);

        $permissions = json_decode($request->permission_list);

        if($permissions != NULL)
        {
            $role = Role::findOrFail($request->roleID);
			$role->syncPermissions($permissions);
        }

		return response()->json([
			'alert' =>'info',
			'msg'   =>'Permissions saved Successfully'
		]);
	}
}
