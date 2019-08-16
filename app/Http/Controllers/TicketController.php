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
            $userTickets = Ticket::select('id', 'title', 'description', 'status', 'created_at')
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

    public function allTickets()
    {
        try {
            $allTickets = Ticket::all();
            return new TicketResource($allTickets);
        } catch (\Exception $e) {
            return response()->json([
                'fail' => true,
                'message' => 'Error en el servidor: error SQL',
                'errors' => $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'title' => 'required|max:40|min:1',
                'description' => 'required|max:500|min:1',
            ]);
            if ($validator->passes()) {
                $ticket->fill($request->all());
                if ($ticket->save()) {
                    return new TicketResource($ticket);
                } else {
                    return response()->json([
                        'error' => true,
                        "message" => 'error en el servidor al modificar el ticket'
                    ], 400);
                }
            } else {
                return response()->json([
                    'error' => true,
                    "message" => $validator->errors()
                ], 400);
            }
        } catch (\Exception $err) {
            return response()->json([
                "fails" => true,
                "messages" => "ocurrio un error en el proceso de modificacion",
                "errors" => $err->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        $ticketToDelete = Ticket::findOrFail($id);
        if ($ticketToDelete) {
            if ($ticketToDelete->delete()) {
                return response()->json([
                    "success" => true,
                    "message" => "categoria eliminada satisfactoriamente"
                ]);
            } else {
                return response()->json([
                    "fails" => true,
                    "message" => "error al eliminar la categoria"
                ]);
            }
        } else {
            return response()->json([
                "fails" => true,
                "message" => "no se encontro la categoria a eliminar"
            ]);
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
