<?php
namespace Edwinrtoha\Laravelboilerplate\Http\Controllers;

use App\Http\Controllers\Controller;
use Edwinrtoha\Laravelboilerplate\Models\ModelStd;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiController extends Controller
{
    var $model = ModelStd::class;
    var $withs = [];

    public function __construct()
    {
        $this->model = new $this->model;
    }

    /**
     * Display a listing of the resource.
     */
    public function queryModifier() {
        return $this->model;
    }

    public function query()
    {
        $query = $this->queryModifier();
        return $query;
    }

    public function response($data, $status = Response::HTTP_OK) {
        $metadata = [
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'next_page_url' => $data->nextPageUrl(),
            'prev_page_url' => $data->previousPageUrl(),
        ];

        // Return response with metadata
        return response()->json([
            'meta' => $metadata,
            'data' => $data->items(),
        ], $status);
    }

    public function index()
    {
        // Fetch all results
        $results = $this->query()->with($this->withs)->paginate(10);

        return $this->response($results);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request
        $validatedData = $request->post();

        // Create a new result
        $result = $this->model::create($validatedData);

        // Return response
        return response()->json($result, Response::HTTP_CREATED);
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
            return response()->json(['message' => 'result not found'], Response::HTTP_NOT_FOUND);
        }

        // Return the result
        return response()->json($result, Response::HTTP_OK);
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
            return response()->json(['message' => 'result not found'], Response::HTTP_NOT_FOUND);
        }

        // Validate incoming request
        $validatedData = $request->post();

        // Update result
        $result->update($validatedData);

        // Return response
        return response()->json($result, Response::HTTP_OK);
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
            return response()->json(['message' => 'result not found'], Response::HTTP_NOT_FOUND);
        }

        // Delete result
        $result->delete();

        // Return response
        return response()->json(['message' => 'result deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
