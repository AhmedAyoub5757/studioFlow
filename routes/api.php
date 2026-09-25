<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MilestoneController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TimeLogController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\BugController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\SubscriptionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/logout', [AuthController::class, 'logout']);
//     Route::get('/me', [AuthController::class, 'me']);
// });

// Route::middleware(['auth:sanctum', 'permission:users.manage'])
//     ->get('/test-permission', fn () => 'You have access');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects/{project}/assign', [ProjectController::class, 'assignStaff']);

    Route::post('/staff', [UserController::class, 'storeStaff']);

    // Comments — Task
    Route::get('/tasks/{task}/comments', [CommentController::class, 'indexForTask']);
    Route::post('/tasks/{task}/comments', [CommentController::class, 'storeForTask']);

    // Comments — Milestone
    Route::get('/milestones/{milestone}/comments', [CommentController::class, 'indexForMilestone']);
    Route::post('/milestones/{milestone}/comments', [CommentController::class, 'storeForMilestone']);

    // Comments — shared delete
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // Approvals (milestone-scoped)
    Route::get('/milestones/{milestone}/approvals', [ApprovalController::class, 'index']);
    Route::post('/milestones/{milestone}/approvals/request', [ApprovalController::class, 'request']);
    Route::post('/milestones/{milestone}/approvals/decide', [ApprovalController::class, 'decide']);

    Route::get('/projects/{project}/bugs', [BugController::class, 'index']);
    Route::post('/projects/{project}/bugs', [BugController::class, 'store']);
    Route::get('/bugs/{bug}', [BugController::class, 'show']);
    Route::put('/bugs/{bug}', [BugController::class, 'update']);
    Route::patch('/bugs/{bug}/status', [BugController::class, 'transition']);
    Route::delete('/bugs/{bug}', [BugController::class, 'destroy']);

    // Comments on bugs — reuses Sprint 3's CommentController, zero new controller code
    Route::get('/bugs/{bug}/comments', [CommentController::class, 'indexForBug']);
    Route::post('/bugs/{bug}/comments', [CommentController::class, 'storeForBug']);

    // Invoices
    Route::get('/projects/{project}/invoices', [InvoiceController::class, 'index']);
    Route::post('/projects/{project}/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send']);
    Route::post('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);
    Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay']);
    Route::post('/invoices/{invoice}/refund', [InvoiceController::class, 'refund']);

    // Subscriptions
    Route::get('/projects/{project}/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/projects/{project}/subscriptions', [SubscriptionController::class, 'store']);
    Route::put('/subscriptions/{subscription}', [SubscriptionController::class, 'update']);
    Route::post('/subscriptions/{subscription}/generate-invoice', [SubscriptionController::class, 'generateInvoice']);
});

// Milestones (nested under project for index/store, flat for show/update/delete)
Route::get('/projects/{project}/milestones', [MilestoneController::class, 'index']);
Route::post('/projects/{project}/milestones', [MilestoneController::class, 'store']);
Route::get('/milestones/{milestone}', [MilestoneController::class, 'show']);
Route::put('/milestones/{milestone}', [MilestoneController::class, 'update']);
Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy']);

// Tasks
Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);
Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
Route::get('/tasks/{task}', [TaskController::class, 'show']);
Route::put('/tasks/{task}', [TaskController::class, 'update']);
Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

// Time logs
Route::get('/tasks/{task}/time-logs', [TimeLogController::class, 'index']);
Route::post('/tasks/{task}/time-logs', [TimeLogController::class, 'store']);
