<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class NotificationTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = NotificationTemplate::query();

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('trigger_event', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate($request->integer('per_page', 15)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255', 'unique:notification_templates,key'],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'channels' => ['nullable', 'array'],
            'channels.*' => ['string', Rule::in(['mail', 'database', 'sms'])],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['string'],
            'trigger_event' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $template = NotificationTemplate::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notification template created successfully.',
            'data' => $template,
        ], 201);
    }

    public function show(NotificationTemplate $notificationTemplate): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $notificationTemplate,
        ]);
    }

    public function update(Request $request, NotificationTemplate $notificationTemplate): JsonResponse
    {
        $validated = $request->validate([
            'key' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('notification_templates', 'key')->ignore($notificationTemplate->id),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['sometimes', 'required', 'string'],
            'channels' => ['nullable', 'array'],
            'channels.*' => ['string', Rule::in(['mail', 'database', 'sms'])],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['string'],
            'trigger_event' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $notificationTemplate->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notification template updated successfully.',
            'data' => $notificationTemplate->fresh(),
        ]);
    }

    public function destroy(NotificationTemplate $notificationTemplate): JsonResponse
    {
        $notificationTemplate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification template deleted successfully.',
        ]);
    }

    public function testEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => ['required', 'email'],
            'template_key' => ['nullable', 'string', 'exists:notification_templates,key'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'variables' => ['nullable', 'array'],
        ]);

        if (!empty($validated['template_key'])) {
            $template = NotificationTemplate::where('key', $validated['template_key'])
                ->where('is_active', true)
                ->firstOrFail();

            $subject = $this->renderTemplate(
                $template->subject ?? 'Smile Concept Notification',
                $validated['variables'] ?? []
            );

            $body = $this->renderTemplate(
                $template->body,
                $validated['variables'] ?? []
            );
        } else {
            $subject = $validated['subject'] ?? 'Smile Concept Mail Test';
            $body = $validated['body'] ?? 'Smile Concept mail service test.';
        }

        Mail::raw($body, function ($message) use ($validated, $subject) {
            $message->to($validated['to'])
                ->subject($subject);
        });

        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully.',
        ]);
    }

    private function renderTemplate(string $content, array $variables = []): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{ ' . $key . ' }}', (string) $value, $content);
            $content = str_replace('{{' . $key . '}}', (string) $value, $content);
        }

        return $content;
    }
}