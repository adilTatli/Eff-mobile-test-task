<?php

namespace App\Http\Controllers\API\v1\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\Tasks\StoreTaskRequest;
use App\Http\Requests\API\v1\Tasks\UpdateTaskRequest;
use App\Http\Resources\API\v1\Tasks\TaskResource;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * @OA\Schema(
 *   schema="Task",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=10),
 *   @OA\Property(property="title", type="string", example="Сделать отчёт"),
 *   @OA\Property(property="description", type="string", nullable=true, example="Нужно к понедельнику"),
 *   @OA\Property(property="status_id", type="integer", example=1, description="ID статуса"),
 *   @OA\Property(property="status", ref="#/components/schemas/Status"),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-01T20:00:00Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-01T20:10:00Z")
 * )
 */
class TaskController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        $this->authorizeResource(Task::class, 'task');
    }

    /**
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *   path="/api/tasks",
     *   summary="Список задач",
     *   tags={"Tasks"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="page", in="query", required=false,
     *     @OA\Schema(type="integer", minimum=1),
     *     description="Номер страницы пагинации"
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Task")),
     *       @OA\Property(property="links", type="object"),
     *       @OA\Property(property="meta", type="object")
     *     )
     *   ),
     *   @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        $tasks = Task::with('status')->orderBy('id')->paginate(15);
        return TaskResource::collection($tasks);
    }

    /**
     * @param StoreTaskRequest $request
     * @return TaskResource
     *
     * @OA\Post(
     *   path="/api/tasks",
     *   summary="Создать задачу",
     *   tags={"Tasks"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"title","status_id"},
     *       @OA\Property(property="title", type="string", example="Сделать отчёт"),
     *       @OA\Property(property="description", type="string", example="Нужно к понедельнику"),
     *       @OA\Property(property="status_id", type="integer", example=1, description="ID существующего статуса")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/Task")),
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create($request->validated());
        $task->load('status');

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param Task $task
     * @return TaskResource
     *
     * @OA\Get(
     *   path="/api/tasks/{id}",
     *   summary="Получить задачу",
     *   tags={"Tasks"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Task")),
     *   @OA\Response(response=404, description="Not Found"),
     *   @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(Task $task): TaskResource
    {
        $task->load('status');
        return new TaskResource($task);
    }

    /**
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return TaskResource
     *
     * @OA\Patch(
     *   path="/api/tasks/{id}",
     *   summary="Обновить задачу",
     *   tags={"Tasks"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       @OA\Property(property="title", type="string", example="Сделать отчёт (обновлено)"),
     *       @OA\Property(property="description", type="string", example="Дедлайн перенесён"),
     *       @OA\Property(property="status_id", type="integer", example=2, description="ID существующего статуса")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Task")),
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=404, description="Not Found"),
     *   @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $task->update($request->validated());
        $task->load('status');

        return new TaskResource($task);
    }

    /**
     * @param Task $task
     * @return Response
     *
     * @OA\Delete(
     *   path="/api/tasks/{id}",
     *   summary="Удалить задачу",
     *   tags={"Tasks"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="No Content"),
     *   @OA\Response(response=404, description="Not Found"),
     *   @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(Task $task): Response
    {
        $task->delete();
        return response()->noContent();
    }
}
