<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    
    public function submit(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:50',
                'service' => 'required|string|max:255',
                'date' => 'nullable|date',
                'message' => 'nullable|string|max:5000',
                'video' => 'nullable|file|mimes:mp4,mov,avi,wmv,flv,webm|max:51200', // 50MB max
            ]);

            $videoPath = null;
            $videoUrl = null;

           
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                if ($video->isValid()) {
                    $videoPath = $video->store('public/contact-videos');
                    $videoUrl = url('storage/' . str_replace('public/', '', $videoPath));
                }
            }

            
            $emailData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? 'Not provided',
                'service' => $validated['service'],
                'date' => $validated['date'] ?? 'Not specified',
                'message' => $validated['message'] ?? 'No message provided',
                'videoUrl' => $videoUrl,
                'submittedAt' => now()->format('F j, Y, g:i a'),
            ];

           
            Mail::send('emails.contact-inquiry', $emailData, function ($message) use ($emailData) {
                $message->to('jking3509@gmail.com')
                    ->subject('New Photography Inquiry - ' . $emailData['service'])
                    ->replyTo($emailData['email'], $emailData['name']);
            });

           
            Log::info('Contact form submitted', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'service' => $validated['service'],
                'has_video' => $videoPath !== null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your inquiry has been sent successfully! I will get back to you within 24 hours.',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Contact form submission failed: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send inquiry. Please try again or contact directly at jking3509@gmail.com',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
