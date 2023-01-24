<?php

namespace App\Http\Controllers;

use App\Models\Images;
use Illuminate\Http\Request;
use App\Models\Note;
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
            ->whereCompany($request->header('company'))
            ->latest()
            ->paginate($limit);

        return response()->json([
            'notes' => $notes,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $note = Note::find($id);
        $image_upload = Images::where('id', $note->images_id)->first();

        return response()->json([
            'note' => $note,
            'image' => $image_upload->image_path,
        ]);
    }

    /**
     * Create Note.
     *
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
            $note->company_id = $request->header('company');
            $note->save();

            $note = Note::find($note->id);

            $image = '';
            if ($request->image) {
                $image = $note->uploadImage($request->image);
            }

            return response()->json([
                'note' => $note,
                'image' => $image,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving note', [$e]);
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
            $note->company_id = $request->header('company');
            $note->save();

            $note = Note::find($note->id);
            $image = '';
            if ($request->image) {
                $image = $note->uploadImage($request->image);
            }

            return response()->json([
                'note' => $note,
                'image' => $image,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating note', [$e]);
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
