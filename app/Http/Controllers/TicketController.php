<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\TicketResource;
use App\Ticket;
use Validator;

class TicketController extends Controller
{
    public function getUserTickets($id)
    {
        try {
            $userTickets = Ticket::select('id', 'title', 'description', 'status','created_at')
                ->where('user_id', $id)
                ->get();

            return new TicketResource($userTickets);
        } catch (\Exception $e) {
            return response()->json([
                'fail' => true,
                'message' => 'Error en el servidor: error SQL',
                'errors' => $e->getMessage()
            ], 400);
        }
    }

    public function newTicket(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|max:40|min:1',
                'description' => 'required|max:500|min:1',
            ]);
            if ($validator->passes()) {
                $ticket = new Ticket();
                $ticket->user_id = $request->user()->id;
                $ticket->fill($request->all());
                $ticket->save();
                if ($ticket->save()) {
                    return new TicketResource(Ticket::find($ticket->id));
                } else {
                    return response()->json([
                        "error" => "true",
                        "message" => "Error al guardar registro en la base de datos"
                    ]);
                }
            } else if ($validator->fails()) {
                return response()->json([
                    'fail' => true,
                    'errors' => $validator->errors()
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'fail' => true,
                'errors' => $validator->errors(),
                'messages' => $e->getMessage()
            ]);
        }
    }
}
