<?php

namespace App\Http\Controllers;

use App\Jobs\RecievedEmailsJob;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\Domain;
use App\Models\DomainSender;
use App\Models\EmailMeta;
use App\Models\Lead;
use App\Models\Message as ModelsMessage;
use App\Models\Sender;
use App\Models\EmailSuppression;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZBateson\MailMimeParser\Header\HeaderConsts;
use ZBateson\MailMimeParser\Message;
use ZBateson\MailMimeParser\Message\MimePart;

class SNSController extends Controller
{
    public function eventHook(Request $request)
    {
        $data = $request->json()->all();
        Log::channel('emails')->info(json_encode($data));

        if (isset($data['Type']) && $data['Type'] == 'SubscriptionConfirmation') {
            file_get_contents($data['SubscribeURL']);
        } else if (isset($data['eventType'])) {
            $messageIdHeader = array_filter($data['mail']['headers'], function ($header) {
                return $header['name'] === 'Message-ID';
            });

            if (!empty($messageIdHeader)) {
                $message_id = str_replace(
                    ['<', '>'],
                    '',
                    current($messageIdHeader)['value']
                );
                $emailMeta = EmailMeta::where('message_id', $message_id)->first();

                if ($emailMeta instanceof EmailMeta) {
                    switch ($data['eventType']) {
                        case 'Bounce':
                            if ($emailMeta->lead_id) {
                                $this->unsubscribeLead($emailMeta->lead_id);
                            }
                            $emailMeta->update(['is_bounced' => true]);
                            EmailSuppression::firstOrCreate(['email' => $data['mail']['destination'][0]]);
                            return true;

                        case 'Bounced':
                            if ($emailMeta->lead_id) {
                                $this->unsubscribeLead($emailMeta->lead_id);
                            }
                            $emailMeta->update(['is_bounced' => true]);
                            EmailSuppression::firstOrCreate(['email' => $data['mail']['destination'][0]]);
                            return true;

                        case 'Delivery':
                            Log::channel('delivered_emails')->info(
                                $data['mail']['destination'][0]
                            );
                            Log::channel('delivered_emails')->info(
                                json_encode($data)
                            );

                            ModelsMessage::query()->find($emailMeta->conversation_message_id)?->update([
                                'deliver_at' => now()
                            ]);
                            return true;

                        case 'Complaint':
                            if ($emailMeta->lead_id) {
                                $this->unsubscribeLead($emailMeta->lead_id);
                            }
                            $emailMeta->update(['is_complaint' => true]);
                            EmailSuppression::firstOrCreate(['email' => $data['mail']['destination'][0]]);
                            return true;

                        case 'Open':
                            $emailMeta->update(['is_open' => true]);

                            ModelsMessage::query()->find($emailMeta->conversation_message_id)?->update([
                                'read_at' => now()
                            ]);
                            return true;

                        case 'Click':
                            $emailMeta->update(['is_clicked' => true]);
                            return true;

                        default:
                            break;
                    }
                }
            }
        }

        return response($data, 200);
    }

    public function emailHook(Request $request)
    {
        $data = $request->json()->all();

        if (isset($data['Type']) && $data['Type'] == 'SubscriptionConfirmation') {
            file_get_contents($data['SubscribeURL']);
        }

        Log::channel('inbox')->info(json_encode($data));

        $email = Storage::disk('s3')->get($data['receipt']['action']['objectKey']);

        $message = Message::from($email, true);

        $toEmail = $this->getRecipientEmail($data);
        $fromEmail = $this->getToEmail($data); // $data['mail']['source'];

        $subject = null;
        $fromName = null;

        foreach ($data['mail']['headers'] as $key => $header) {
            if ($header['name'] == 'Subject')
                $subject = $header['value'];
            if ($header['name'] == 'From')
                $fromName = $this->getNameFromEmailHeader($header['value']);
        }

        $domainName = explode('@', $toEmail);

        $domain = Domain::where('domain', $domainName[1])->first();

        $domain?->update([
            'is_verified' => true
        ]);

        $sender = Sender::query()
            ->where('from_email', $domainName[0])
            // ->where('reply_to', $toEmail)
            ->where('model_type', Client::class)
            ->first();

        if (!$sender) {
            $sender = Sender::query()->firstOrCreate([
                'model_id' => config('app.default_sender_id'),
                'model_type' => User::class
            ], [
                'reply_to' => $toEmail,
                'from_email' => $domainName[0],
                'from_name' => $fromName ?? $domainName[0],
                // 'domain_id' => $domain?->id,
            ]);
        }

        $isTextContent = false;

        if (is_null($message->getHtmlContent())) {
            $isTextContent = true;
            $message = $this->processTextMessage($message);
        }

        $messageBody = $isTextContent ? $message->getContent() : $message->getHtmlContent();

        $messageBody = $this->addBlankTargetToHyperLikns($messageBody);

        if ($fromName == 'Amazon Web Services') {
            $matches = '';
            preg_match('/<a\s+.*?href="(.*?)".*?>/i', $messageBody, $matches);
            $decodedLink = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            file_get_contents($decodedLink);

            DomainSender::firstOrCreate([
                'sender_id' => $sender->id,
                'domain_id' => $domain->id,
            ]);

            Log::info($toEmail);
        }

        if ($sender) {

            $conversation = Conversation::withTrashed()
                ->where('domain_id', $domain?->id)
                ->where('external_email', $fromEmail)
                ->withTrashed()
                ->first();

            if (is_null($conversation)) {
                $conversation = Conversation::create([
                    'sender_id' => $sender->id,
                    'is_favorite' => false,
                    'is_archived' => false,
                    'external_email' => $fromEmail,
                    'external_name' => $fromName
                ]);
            }

            Log::channel('inbox')->info("Email now in conversation numner #{$conversation->id}");

            if ($conversation->trashed()) {
                $conversation->restore();
            }

            //restore if softDeleted
            $conversation->update([
                'is_read' => false,
                'is_archived' => false,
                'created_at' => now(),
                'domain_id' => $domain?->id
            ]);

            /**
             * @var ModelsMessage $messageModel
             */
            $messageModel = ModelsMessage::create([
                'conversation_id' => $conversation->id,
                'is_reply' => true,
                'email_subject' => $subject,
                'email_body' => $messageBody
            ]);

            $messageId = $data['mail']['messageId'];

            if (config('app.incoming_email_enabled') && $sender->id) {
                RecievedEmailsJob::dispatch($messageModel, $sender, $fromEmail, $toEmail, $messageId)->onQueue(get_queue_name('conversation_emails'));
            }

            $files = collect($message->getAllParts())->map(function (MimePart $part) use ($messageId) {
                $attachmentDisposition = $part->getHeader(HeaderConsts::CONTENT_DISPOSITION);
                $attachmentContentType = $part->getHeader(HeaderConsts::CONTENT_TYPE);

                if ($attachmentDisposition !== null && $attachmentContentType !== null) {
                    $filename = $part->getHeaderParameter(HeaderConsts::CONTENT_DISPOSITION, 'filename');
                    $mimeType = $attachmentContentType->getValue();
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $path = "email-attachments/{$messageId}/{$filename}";
                    $attachmentData = $part->getContent();
                    $fileSize = strlen($attachmentData);

                    if (!empty($filename)) {
                        $file = Upload::query()->create([
                            'name' => $filename,
                            'path' => $path,
                            'ext' => $extension,
                            'size' => $fileSize,
                            'mime_type' => $mimeType
                        ]);

                        Storage::put("public/$path", $attachmentData);

                        return $file->id;
                    }
                }

                return null;
            })->filter()->values();

            $messageModel->uploads()->createMany($files->map(fn ($file_id) => [
                'upload_id' => $file_id
            ]));

            // ConversationSummaryJob::dispatch($conversation)->onQueue('summary');

        }

        return response($data, 200);
    }
    
    protected function extractEmailFromHeaderValue($input)
    {
        $pattern = '/<([^>]+)>/';
        if (preg_match($pattern, $input, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }

    protected function getRecipientEmail($data)
    {
        $email = $data['mail']['destination'][0] ?? null;
        $emailHeadersKey = ['To'];

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

            foreach ($data['mail']['headers'] as $key => $header) {
                if (in_array($header['name'], $emailHeadersKey)) {
                    $email = $this->extractEmailFromHeaderValue($header['value']);

                    if (!empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        break;
                    }
                }
            }
        }

        if (empty($email)) {
            $email = $data['receipt']['recipients'][0];
        }

        return $email;
    }

    protected function addBlankTargetToHyperLikns(string $message)
    {
        $pattern = '/<a(.*?)href=["\'](https?:\/\/[^"\']+)["\'](.*?)>/i';
        $replacement = '<a$1href="$2"$3 target="_blank">';
        return preg_replace($pattern, $replacement, $message);
    }

    public function getToEmail($data)
    {
        function extractEmailFromHeaderValue($input)
        {
            $pattern = '/<([^>]+)>/';
            if (preg_match($pattern, $input, $matches)) {
                return $matches[1];
            } else {
                return null;
            }
        }

        $toEmail = $data['mail']['source'];

        $toEmailHeadersKey = ['Return-Path', 'From'];

        if (empty($toEmail)) {
            foreach ($data['mail']['headers'] as $key => $header) {
                if (in_array($header['name'], $toEmailHeadersKey)) {
                    $toEmail = extractEmailFromHeaderValue($header['value']);

                    if (!empty($toEmail)) {
                        break;
                    }
                }
            }
        }

        if (empty($toEmail)) {
            $toEmail = $data['mail']['commonHeaders']['returnPath'] ?? null;
        }

        if (empty($toEmail)) {
            $toEmail = $data['mail']['commonHeaders']['from'][0] ?? null;
        }

        return $toEmail;
    }

    protected function replaceLinkWithATag(string $text)
    {
        $pattern = '/(https?|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\S*)?/';
        return preg_replace($pattern, '<a href="$0">$0</a>', $text);
    }

    protected function processTextMessage(Message $message)
    {
        $body = nl2br(
            $this->replaceLinkWithATag(
                htmlspecialchars(
                    $message->getTextContent() ?? $message->getContent()
                )
            )
        );

        return $message->setContent("<html><body>$body</body></html>");
    }

    public function getNameFromEmailHeader(string $header_value)
    {
        if (preg_match('/([A-Za-z\s]+)\s*</', $header_value, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    public function unsubscribeLead(int $lead_id)
    {
        $lead = Lead::whereId($lead_id)->first();

        $lead?->subscriber->update([
            'is_subscribed' => false
        ]);
    }
}
