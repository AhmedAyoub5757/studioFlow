<?php

namespace App\OpenApi;

/**
 * @OA\Schema(
 *     schema="Project",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="E-commerce Revamp"),
 *     @OA\Property(property="slug", type="string", example="e-commerce-revamp-xk29a"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"pending","in_progress","on_hold","completed","cancelled"}),
 *     @OA\Property(property="budget", type="number", format="float", nullable=true, example=15000),
 *     @OA\Property(property="start_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="end_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="client", type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(property="created_by", type="string"),
 *     @OA\Property(property="staff", type="array", @OA\Items(
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="role_on_project", type="string", enum={"manager","developer","designer","qa"})
 *     ))
 * )
 *
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="project_id", type="integer"),
 *     @OA\Property(property="milestone_id", type="integer", nullable=true),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="type", type="string", enum={"development","design","qa","general"}),
 *     @OA\Property(property="status", type="string", enum={"todo","in_progress","in_review","done"}),
 *     @OA\Property(property="priority", type="string", enum={"low","medium","high","urgent"}),
 *     @OA\Property(property="due_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="assignee", type="object", nullable=true,
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Milestone",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="project_id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="amount", type="number", format="float", nullable=true),
 *     @OA\Property(property="due_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"pending","in_progress","completed","approved"}),
 *     @OA\Property(property="order", type="integer")
 * )
 *
 * @OA\Schema(
 *     schema="Bug",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="project_id", type="integer"),
 *     @OA\Property(property="task_id", type="integer", nullable=true),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="severity", type="string", enum={"low","medium","high","critical"}),
 *     @OA\Property(property="status", type="string", enum={"open","in_progress","fixed","closed","wont_fix"}),
 *     @OA\Property(property="reporter", type="string"),
 *     @OA\Property(property="assignee", type="object", nullable=true,
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Invoice",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="invoice_number", type="string", example="INV-20260925-AB12CD"),
 *     @OA\Property(property="type", type="string", enum={"deposit","milestone","final","subscription"}),
 *     @OA\Property(property="status", type="string", enum={"draft","sent","paid","overdue","cancelled","refunded"}),
 *     @OA\Property(property="subtotal", type="number", format="float"),
 *     @OA\Property(property="tax", type="number", format="float"),
 *     @OA\Property(property="total", type="number", format="float"),
 *     @OA\Property(property="due_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="items", type="array", @OA\Items(
 *         @OA\Property(property="description", type="string"),
 *         @OA\Property(property="quantity", type="integer"),
 *         @OA\Property(property="unit_price", type="number", format="float"),
 *         @OA\Property(property="line_total", type="number", format="float")
 *     ))
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(property="errors", type="object", additionalProperties=@OA\Schema(type="array", @OA\Items(type="string")))
 * )
 *
 * @OA\Schema(
 *     schema="ForbiddenError",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="This action is unauthorized.")
 * )
 */
class Schemas {}