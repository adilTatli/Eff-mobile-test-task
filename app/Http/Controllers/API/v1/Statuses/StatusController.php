<?php

namespace App\Http\Controllers\API\v1\Statuses;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\Statuses\StoreStatusRequest;
use App\Http\Requests\API\v1\Statuses\UpdateStatusRequest;
use App\Http\Resources\API\v1\Statuses\StatusResource;
use App\Models\Status;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * @OA\Schema(
 *   schema="Status",
 *   type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="title", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class StatusController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        $this->authorizeResource(Status::class, 'status');
    }

    /**
     * @return StatusResource
     *
     * @OA\Get(
     *     path="/api/statuses",
     *     summary="Список статусов",
     *     tags={"Statuses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Status"))
     *     )
     * )
     */
    public function index() : AnonymousResourceCollection
    {
        $status = Status::query()->orderBy('id')->paginate(10);
        return StatusResource::collection($status);
    }

    /**
     * @param StoreStatusRequest $request
     * @return StatusResource
     *
     * @OA\Post(
     *     path="/api/statuses",
     *     summary="Создать статус",
     *     tags={"Statuses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="На паузе"),
     *             @OA\Property(property="description", type="string", example="Временная приостановка")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Успешно", @OA\JsonContent(ref="#/components/schemas/Status"))
     * )
     */
    public function store(StoreStatusRequest $request) : JsonResponse
    {
        $status = Status::create($request->validated());

        return (new StatusResource($status))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param Status $status
     * @return StatusResource
     *
     * @OA\Get(
     *     path="/api/statuses/{id}",
     *     summary="Получить один статус",
     *     tags={"Statuses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Status"))
     * )
     */
    public function show(Status $status) : StatusResource
    {
        return new StatusResource($status);
    }

    /**
     * @param UpdateStatusRequest $request
     * @param Status $status
     * @return StatusResource
     *
     * @OA\Patch(
     *     path="/api/statuses/{id}",
     *     summary="Обновить статус",
     *     tags={"Statuses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Готово"),
     *             @OA\Property(property="description", type="string", example="Задача выполнена")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Status"))
     * )
     */
    public function update(UpdateStatusRequest $request, Status $status) : StatusResource
    {
        $status->update($request->validated());
        return new StatusResource($status);
    }

    /**
     * @param Status $status
     * @return \Illuminate\Http\Response
     *
     * @OA\Delete(
     *     path="/api/statuses/{id}",
     *     summary="Удалить статус",
     *     tags={"Statuses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No Content")
     * )
     */
    public function destroy(Status $status) : Response|JsonResponse
    {
        if ($status->tasks()->exists()) {
            return response()->json([
                'message' => 'Не возможно удалить статус: к нему привязаны задачи.',
            ], 409);
        }

        $status->delete();
        return response()->noContent();
    }
}
