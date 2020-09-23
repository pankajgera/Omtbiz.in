<?php

namespace Crater\Http\Controllers;

use Illuminate\Http\Request;
use Crater\Note;
use Exception;
use Log;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 20;

        $notes = Note::applyFilters($request->only([
                'name',
                'design_no',
                'rate',
                'average',
                'orderByField',
                'orderBy',
            ]))
            ->latest()
            ->paginate($limit);

        return response()->json([
            'notes' => $notes,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $note = Note::find($id);

        return response()->json([
            'note' => $note,
        ]);
    }

    /**
     * Create Note.
     *     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $note = new Note();
            $note->name = $request->name;
            $note->design_no = $request->design_no;
            $note->rate = $request->rate;
            $note->average = $request->average;
            $note->per_price = $request->per_price;
            $note->note = $request->note;
            $note->save();

            $note = Note::find($note->id);

            return response()->json([
                'note' => $note,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving note', [$e->getMessage()]);
        }
    }

    /**
     * Update an existing Note.
     *
     * @param int                               $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $note = Note::find($id);
            $note->name = $request->name;
            $note->design_no = $request->design_no;
            $note->rate = $request->rate;
            $note->average = $request->average;
            $note->per_price = $request->per_price;
            $note->note = $request->note;
            $note->save();

            $note = Note::find($note->id);

            return response()->json([
                'note' => $note,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating note', [$e->getMessage()]);
        }
    }

    /**
     * Delete an existing Note.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $data = Note::deleteNote($id);

        if (!$data) {
            return response()->json([
                'error' => 'note_attached',
            ]);
        }

        return response()->json([
            'success' => $data,
        ]);
    }

    /**
     * Delete a list of existing Note.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $notes = [];
        foreach ($request->id as $id) {
            $note = Note::deleteNote($id);
            if (!$note) {
                array_push($notes, $id);
            }
        }

        if (empty($notes)) {
            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'notes' => $notes,
        ]);
    }
}
