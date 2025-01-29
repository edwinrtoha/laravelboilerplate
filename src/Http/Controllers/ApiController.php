<?php
namespace Edwinrtoha\Laravelboilerplate\Http\Controllers;

use App\Http\Controllers\Controller;
use Closure;
use Edwinrtoha\Laravelboilerplate\Models\EndpointHasPermission;
use Edwinrtoha\Laravelboilerplate\Models\ModelStd;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiController extends Controller
{
    var $model = ModelStd::class;
    var $instance;
    var $withs = [];
    var $validatedData = [];
    var $storeValidateRequest = [];
    var $updateValidateRequest = [];
    var $paginate = 10;

    public static function middleware()
    {
        return [
            function (Request $request, Closure $next) {
                try {
                    $need_permission = EndpointHasPermission::with(['permission'])->where('endpoint', $request->path())->where('method', $request->method())->get()->pluck(('permission.name'));
                    $user_permission = $request->user()->getAllPermissions()->pluck('name');
                    $diff = $need_permission->diff($user_permission);

                    if ($diff->count() > 0) {
                        throw new \Exception('User does not have the required permissions.');
                    }
                    return $next($request);
                } catch (\Exception $e) {
                    return ApiController::response([], 404, $e->getMessage());
                }
                return $next($request);
            },    
        ];
    }

    public function __construct()
    {
        $this->model = new $this->model;
        $this->instance = $this->model;

        if ($this->validatedData != []) {
            if ($this->storeValidateRequest == []) {
                $this->storeValidateRequest = $this->validatedData;
            }

            if ($this->updateValidateRequest == []) {
                $this->updateValidateRequest = $this->validatedData;
            }
        }
    }

    public function validateRequest(Request $request, $validatedData) {
        $validatedData = $request->validate($this->storeValidateRequest);
        return $validatedData;
    }

    /**
     * Display a listing of the resource.
     */
    public function queryModifier() {
        return $this->instance;
    }

    public function query()
    {
        $query = $this->instance;
        return $query;
    }

    public static function response($data = [], $status = Response::HTTP_OK, $errors = null, $message = null) {
        // check $data is paginate or not
        if ($data instanceof LengthAwarePaginator) {
            $metadata = [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'next_page_url' => $data->nextPageUrl(),
                'prev_page_url' => $data->previousPageUrl(),
            ];
            $items = $data->items();
        }
        else {
            $isArray = function () use ($data) {
                foreach ($data as $element) {
                    if (is_array($element)) {
                        return true;
                    }
                    else {
                        return false;
                    }
                }
            };

            if ($isArray()) {
                $metadata = [
                    'total' => sizeof($data),
                    'per_page' => sizeof($data),
                    'current_page' => 1,
                    'last_page' => 1,
                    'next_page_url' => null,
                    'prev_page_url' => null,
                ];
            }
            else {
                $metadata = [
                    'total' => 1,
                    'per_page' => 1,
                    'current_page' => 1,
                    'last_page' => 1,
                    'next_page_url' => null,
                    'prev_page_url' => null,
                ];
                $items = $data;
            }
            $items = $data;
        }

        // Return response with metadata
        return response()->json([
            'meta' => $metadata,
            'errors' => $errors,
            'message' => $message,
            'data' => $items,
        ], $status);
    }

    public function index(Request $request)
    {
        // Fetch all results
        if ($this->paginate == 0 || $this->paginate == null) {
            $results = $this->instance->with($this->withs)->get();
        }
        else {
            $results = $this->instance->with($this->withs)->paginate($this->paginate);
        }

        return $this->response($results);
    }

    public function trashed()
    {
        // if $this->model use SoftDeletes
        if (!in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->model))) {
            return $this->response([], Response::HTTP_NOT_IMPLEMENTED, 'Not Implemented');
        }
        $this->instance = $this->instance::onlyTrashed();
        return $this->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($this->storeValidateRequest != []) {
            try {
                $this->validateRequest($request, $this->storeValidateRequest);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return $this->response([], Response::HTTP_BAD_REQUEST, $e->errors());
            }
        }
        else {
            $validatedData = $request->post();
        }

        // Validate incoming request
        $validatedData = $request->post();

        // Create a new result
        $result = $this->model::create($validatedData);

        // Return response
        return $this->response($result, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Find result by ID
        $result = $this->model::with($this->withs)->find($id);

        // If result not found, return 404
        if (!$result) {
            return $this->response([], Response::HTTP_NOT_FOUND, ['message' => 'result not found']);
        }

        // Return the result
        return $this->response($result, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Find result by ID
        $result = $this->model::find($id);

        // If result not found, return 404
        if (!$result) {
            return $this->response([], Response::HTTP_NOT_FOUND, ['message' => 'result not found']);
        }

        // Validate incoming request
        if ($this->updateValidateRequest != []) {
            try {
                $validatedData = $this->validateRequest($request, $this->updateValidateRequest);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return $this->response([], Response::HTTP_BAD_REQUEST, $e->errors());
            }
        }
        else {
            $validatedData = $request->post();
        }

        // Update result
        $result->update($validatedData);

        // Return response
        return $this->response($result, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find result by ID
        $result = $this->model::find($id);

        // If result not found, return 404
        if (!$result) {
            return $this->response([], Response::HTTP_NOT_FOUND, ['message' => 'result not found']);
        }

        // Delete result
        $result->delete();

        // Return response
        return $this->response(['message' => 'data deleted sucessfully'], Response::HTTP_OK);
    }

    public function restore($id)
    {
        // if $this->model use SoftDeletes
        if (!in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->model))) {
            return $this->response([], Response::HTTP_NOT_IMPLEMENTED, 'Not Implemented');
        }

        $this->instance = $this->instance::withTrashed()->find($id);
        if ($this->instance) {
            $this->instance->restore();
            return $this->response($this->instance, Response::HTTP_OK);
        } else {
            return $this->response([
                'message' => 'Data not found in trash',
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function forceDelete($id)
    {
        // if $this->model use SoftDeletes
        if (!in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->model))) {
            return $this->response([], Response::HTTP_NOT_IMPLEMENTED, 'Not Implemented');
        }

        $this->instance = $this->instance::withTrashed()->find($id);
        if ($this->instance) {
            $this->instance->forceDelete();
            return $this->response([], Response::HTTP_NO_CONTENT);
        } else {
            return $this->response([
                'message' => 'Data not found in trash',
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
