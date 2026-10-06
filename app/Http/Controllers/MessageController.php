<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\DeleteMessageRequest;
use App\Http\Requests\EditMessageRequest;
use App\Http\Requests\SendMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\FirebaseNotificationService;
use App\Traits\HttpResponses;
use Throwable;

class MessageController extends Controller
{
    use HttpResponses;

    public function send(
        SendMessageRequest $request,
        Conversation $conversation,
        FirebaseNotificationService $firebaseNotificationService
    ) {
        $user = $request->user();

        $isMember = $conversation->members()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            return $this->error(
                null,
                'You are not a member of this conversation.',
                403
            );
        }

        if ($request->reply_to_id) {
            $replyMessage = Message::find($request->reply_to_id);

            if (
                !$replyMessage ||
                $replyMessage->conversation_id !== $conversation->id
            ) {
                return $this->error(
                    null,
                    'The message you are trying to reply to does not belong to this conversation.',
                    400
                );
            }
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->message,
            'reply_to_id' => $request->reply_to_id,
        ]);

        $message->load([
            'sender',
            'replyTo.sender',
        ]);

        broadcast(new MessageSent($message));

        /*
         * Send push notification to the other members
         * of the conversation.
         */
        $recipients = $conversation->members()
            ->where('user_id', '!=', $user->id)
            ->with('user.deviceTokens')
            ->get();

        foreach ($recipients as $recipient) {
            foreach ($recipient->user->deviceTokens as $deviceToken) {
                try {
                    $firebaseNotificationService->sendToToken(
                        $deviceToken->token,
                        $user->name,
                        $message->message ?? 'Sent you a message.',
                        [
                            'type' => 'message',
                            'conversation_id' => (string) $conversation->id,
                            'message_id' => (string) $message->id,
                        ]
                    );
                } catch (Throwable $e) {
                    report($e);
                }
            }
        }

        return $this->success(
            $message,
            'Message sent successfully.',
            201
        );
    }

    public function index(Conversation $conversation)
    {
        $user = request()->user();

        $isMember = $conversation->members()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            return $this->error(
                null,
                'You are not a member of this conversation.',
                403
            );
        }

        $messages = $conversation->messages()
            ->with([
                'sender',
                'replyTo.sender',
            ])
            ->latest()
            ->paginate(30);

        $messages->setCollection(
            $messages->getCollection()
                ->reverse()
                ->values()
        );

        return $this->success(
            $messages,
            'Messages retrieved successfully.'
        );
    }

    public function update(
        EditMessageRequest $request,
        Message $message
    ) {
        $user = $request->user();

        if ($message->sender_id !== $user->id) {
            return $this->error(
                null,
                'You can only edit your own messages.',
                403
            );
        }

        if ($message->deleted_at !== null) {
            return $this->error(
                null,
                'Deleted messages cannot be edited.',
                400
            );
        }

        $message->update([
            'message' => $request->message,
            'edited_at' => now(),
        ]);

        return $this->success(
            $message->fresh()->load('sender'),
            'Message updated successfully.'
        );
    }

    public function destroy(
        DeleteMessageRequest $request,
        Message $message
    ) {
        $user = $request->user();

        if ($message->sender_id !== $user->id) {
            return $this->error(
                null,
                'You can only delete your own messages.',
                403
            );
        }

        if ($message->deleted_at !== null) {
            return $this->error(
                null,
                'Message has already been deleted.',
                400
            );
        }

        $message->update([
            'deleted_at' => now(),
            'message' => null,
        ]);

        return $this->success(
            $message->fresh(),
            'Message deleted successfully.'
        );
    }
}