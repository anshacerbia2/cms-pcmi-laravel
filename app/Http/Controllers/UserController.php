<?php

namespace App\Http\Controllers;

use View;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Role;
use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Http\Services\UserService;

class UserController extends Controller
{
	protected $userService;

	public function __construct(UserService $userService)
	{
		$this->userService = $userService;
	}

	/**
	 * Retrieve paginated users with ordering, excluding the password field,
	 * and pass the data to the manage-users view.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request)
	{
		if($request->ajax())
		{
			$searchValue = $request->search;
			$search = strtolower(trim(is_array($searchValue) ? ($searchValue['value'] ?? '') : ($searchValue ?? '')));
			$users = User::with(['role']);

			$result = DataTables::eloquent($users)
			->filter(function ($query) use ($search) {
				if ($search !== '') {
					$query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
								->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
								->orWhereRaw('LOWER(phone) LIKE ?', ["%{$search}%"])
								->orWhereRaw('LOWER(status) LIKE ?', ["%{$search}%"])
								->orWhereRaw('LOWER(location) LIKE ?', ["%{$search}%"])
								->orWhereHas('role', fn($r) =>
										$r->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
								);
				}
			})
			->addColumn('role', fn($user) => $user->role?->name )
			->addColumn('actions', function($user) {
				return '
					<div class="dropdown table-action">
						<a href="#" class="action-icon" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="fa fa-ellipsis-v"></i>
						</a>
						<div class="dropdown-menu dropdown-menu-end">
							<a  
								class="dropdown-item" 
								href="'.route('users.read', ['user_id' => $user->id]).'">
								<i class="ti ti-eye text-info"></i> View
							</a>
							<a  
								class="dropdown-item c_user_change_password_btn" 
								href="javascript:void(0);" 
								data-url="'.route('users.changePassword', ['user_id' => $user->id]).'">
								<i class="ti ti-lock-cog text-blue"></i> Change Password
							</a>
							<a  
								class="dropdown-item c_user_edit_btn" 
								href="javascript:void(0);" 
								data-url="'.route('users.read', ['user_id' => $user->id]).'">
								<i class="ti ti-edit text-blue"></i> Edit
							</a>
							<a  
								class="dropdown-item c_user_delete_btn" 
								href="javascript:void(0);" 
								data-url="'.route('users.delete', ['user_id' => $user->id]).'">
								<i class="ti ti-trash text-danger"></i> Delete
							</a>
						</div>
					</div>
				';
				}
			)
			->rawColumns(['actions'])
			->make(true);
			return $result; 
		}

		$roles = Role::all();
		return view('users', compact('roles'));
	}

	public function create(UserRequest $request): JsonResponse
	{
		try {
			$user = $this->userService->createUser($request->validated());
			return response()->json([
				'success' => true,
				'message' => 'User created successfully',
				'data' => $user
			], 201);
		} catch (Exception $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage()?: 'Failed to create user'
			], 500);
		}
	}

	public function readAll(Request $request): JsonResponse
	{
		if ($request->wantsJson() || $request->ajax()) {
			try {
				$users = $this->userService->getAllUsers();
				return response()->json([
					'success' => true,
					'data' => $users
				], 200);
			} catch (\Exception $e) {
				return response()->json([
					'success' => false,
					'message' => $e->getMessage() ?: 'Failed to fetch users'
				], 500);
			}
		}
		abort(404);
	}

	public function read(Request $request, $user_id)
	{
		if($request->wantsJson() || $request->ajax()) {
			try {
				$user = $this->userService->getUserById($user_id);
				return response()->json([
					'success' => true,
					'data' => $user
				], 200);
			} catch (Exception $e) {
				return response()->json([
					'success' => false,
					'message' => $e->getMessage()?: 'Failed to load user data'
				], 500);
			}
		}
		
		$user = $this->userService->getUserById($user_id);
    return view('users.detail', compact('user'));
	}

	public function update(UserRequest $request, $user_id): JsonResponse
	{
		try {
			$validatedData = $request->validated();
			$user = $this->userService->updateUser($user_id, $validatedData);
			return response()->json([
				'success' => true,
				'message' => 'User updated successfully',
				'data' => $user
			], 200);
		} catch (Exception $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage()?: 'Failed to update user'
			], 500);
		}
	}

	
	public function changePassword(UserRequest $request, $user_id): JsonResponse
	{
		try {
			$validatedData = $request->validated();
			$user = $this->userService->changePasswordUser($validatedData);
			return response()->json([
				'success' => true,
				'message' => 'User password updated successfully',
				'data' => $user
			], 200);
		} catch (Exception $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage()?: 'Failed to change password'
			], 500);
		}
	}

	public function delete($user_id): JsonResponse
	{
		try {
			$this->userService->deleteUser($user_id);
			return response()->json([
				'success' => true,
				'message' => 'User deleted successfully'
			], 200);
		} catch (Exception $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage()?: 'Failed to delete user'
			], 500);
		}
	}
}