<?php

namespace App\Http\Controllers\Tenant\TenantBaseControllers;

use App\Http\Controllers\Core\BaseController;
use App\Http\Filters\TicketFilter;
use App\Http\Requests\Tenant\TenantBaseRequests\TicketChatRequest;
use App\Http\Requests\Tenant\TenantBaseRequests\TicketRequest;
use App\Models\Tenant\TenantBaseModels\Ticket;
use App\Models\Tenant\TenantBaseModels\TicketChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketController extends BaseController
{
    protected string $model = Ticket::class;
    protected $filter = TicketFilter::class;
    protected string $request = TicketRequest::class;

    /**
    * Change the status of a ticket to 'closed'.
    *
    * @param \Illuminate\Http\Request $request The HTTP request instance.
    * @param int $id The identifier of the ticket to change its status.
    *
    * @return \Illuminate\Http\JsonResponse The JSON response indicating the success of the operation.
    */
    public function changeStatus(Request $request, $id): \Illuminate\Http\JsonResponse
    {

        $is_closed = Ticket::where('id', $id)->update([
            'status' => 'closed',
            'closed_by' => Auth::user()->id,
            'closing_date' => now()->format('Y-m-d')
        ]);
        if ($is_closed) {
            $response['message'] = trans('response.updated', ['object' => 'data']);
        }
        return response()->json($response);
    }

    /**
   * Process a chat message for a ticket.
   *
   * @param \App\Http\Requests\TicketChatRequest $request The validated request instance.
   *
   * @return \Illuminate\Http\JsonResponse The JSON response indicating the success of the operation.
   */
    public function ticketChat(TicketChatRequest $request): JsonResponse
    {

        $ticket = Ticket::where('id', data_get($request, 'ticket_id'))->first();
        $attachment = $request->get('attachment');
        if (!empty($ticket)) {
            // In case of ticket is null we open the ticket and save message
            if ($ticket->status == 'closed') {
                Ticket::where('id', data_get($request, 'ticket_id'))->update([
                    'status' => 'open',
                    'closed_by' => null,
                    'closing_date' => null
                ]);
            }
            if (!empty($attachment)) {
                if ($attachment->isValid()) {
                    $name = time() . '_reference_' . $ticket->reference;
                    $path = Storage::disk('public')->put($name, $attachment);
                    $request['attachment'] = $path;
                }
            }
            $ticket_chat = TicketChat::create($request->all());
            if ($ticket_chat) {
                $response['message'] = trans('response.created', ['object' => 'data']);
                $response['data'] = $ticket_chat;
            }
        }

        return response()->json($response);
    }

    /**
   * Retrieve chat messages for a specific ticket.
   *
   * @param \Illuminate\Http\Request $request The HTTP request instance.
   * @param int $id The ID of the ticket for which chat messages are retrieved.
   *
   * @return \Illuminate\Http\JsonResponse The JSON response containing the retrieved chat messages.
   */
    public function ticketChatRetrieve(Request $request, $id): JsonResponse
    {

        $ticket_chat = TicketChat::where('ticket_id', $id)->get();
        if ($ticket_chat) {
            $response['message'] = trans('response.created', ['object' => 'data']);
            $response['data'] = $ticket_chat;
        }

        return response()->json($response);
    }
}
