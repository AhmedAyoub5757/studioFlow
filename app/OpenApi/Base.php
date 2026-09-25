<?php 

namespace App\OpenApi;

/**
 * @OA\Info(
 *     title="StudioFlow API",
 *     version="1.0.0",
 *     description="Software Agency & Project Delivery Platform — API-first backend."
 * )
 * @OA\Server(url=L5_SWAGGER_CONST_HOST, description="Local dev server")
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Sanctum Token",
 *     description="Enter the token from /api/login, without the word 'Bearer'."
 * )
 *
 * @OA\Tag(name="Auth", description="Registration, login, logout")
 * @OA\Tag(name="Staff", description="Internal user creation by Agency Manager")
 * @OA\Tag(name="Projects", description="Project CRUD and staff assignment")
 * @OA\Tag(name="Milestones", description="Project milestones")
 * @OA\Tag(name="Tasks", description="Task CRUD and status transitions")
 * @OA\Tag(name="TimeLogs", description="Time tracking against tasks")
 * @OA\Tag(name="Comments", description="Polymorphic comments on tasks/milestones/bugs")
 * @OA\Tag(name="Approvals", description="Milestone client approvals")
 * @OA\Tag(name="Bugs", description="QA bug tracking and lifecycle")
 * @OA\Tag(name="Invoices", description="Invoicing and payments")
 * @OA\Tag(name="Subscriptions", description="Recurring maintenance billing")
 * @OA\Tag(name="Notifications", description="In-app notifications")
 * @OA\Tag(name="Dashboard", description="Role-specific aggregated dashboards")
 */
class Base {}