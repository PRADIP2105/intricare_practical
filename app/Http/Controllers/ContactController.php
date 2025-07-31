<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Contact::where('user_id', $user->id);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $contacts = $query->with('customFields')->orderByDesc('id')->paginate(10);

        if ($request->ajax()) {
            return response()->json($contacts);
        }

        return view('contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('contacts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email',
            'phone' => 'required|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'profile_image' => 'nullable|image|max:2048',
            'additional_file' => 'nullable|file|max:5120',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.field_name' => 'nullable|string|max:255',
            'custom_fields.*.field_value' => 'nullable|string',
        ]);

        $contactData = $validated;
        unset($contactData['custom_fields']);

        if ($request->hasFile('profile_image')) {
            $contactData['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }
        if ($request->hasFile('additional_file')) {
            $contactData['additional_file'] = $request->file('additional_file')->store('additional_files', 'public');
        }

        $contactData['user_id'] = $user->id;

        $contact = Contact::create($contactData);

        if (!empty($validated['custom_fields'])) {
            foreach ($validated['custom_fields'] as $field) {
                if (!empty($field['field_name'])) {
                    $contact->customFields()->create($field);
                }
            }
        }

        if ($request->ajax()) {
            return response()->json(['message' => 'Contact created successfully', 'contact' => $contact]);
        }

        return redirect()->route('contacts.index')->with('success', 'Contact created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $contact = Contact::with(['customFields', 'mergedInto'])->where('user_id', $user->id)->findOrFail($id);
        return view('contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = auth()->user();
        $contact = Contact::with('customFields')->where('user_id', $user->id)->findOrFail($id);
        return view('contacts.edit', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        $contact = Contact::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email,' . $contact->id,
            'phone' => 'required|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'profile_image' => 'nullable|image|max:2048',
            'additional_file' => 'nullable|file|max:5120',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.id' => 'nullable|exists:contact_custom_fields,id',
            'custom_fields.*.field_name' => 'nullable|string|max:255',
            'custom_fields.*.field_value' => 'nullable|string',
        ]);

        $contactData = $validated;
        unset($contactData['custom_fields']);

        if ($request->hasFile('profile_image')) {
            $contactData['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }
        if ($request->hasFile('additional_file')) {
            $contactData['additional_file'] = $request->file('additional_file')->store('additional_files', 'public');
        }

        $contact->update($contactData);

        // Update or create custom fields
        if (!empty($validated['custom_fields'])) {
            $existingFieldIds = $contact->customFields()->pluck('id')->toArray();
            $receivedFieldIds = [];
            foreach ($validated['custom_fields'] as $field) {
                if (isset($field['id'])) {
                    $receivedFieldIds[] = $field['id'];
                    $customField = $contact->customFields()->find($field['id']);
                    if ($customField) {
                        $customField->update($field);
                    }
                } else {
                    if (!empty($field['field_name'])) {
                        $contact->customFields()->create($field);
                    }
                }
            }
            // Delete custom fields that were removed in the form
            $fieldsToDelete = array_diff($existingFieldIds, $receivedFieldIds);
            if (!empty($fieldsToDelete)) {
                $contact->customFields()->whereIn('id', $fieldsToDelete)->delete();
            }
        }

        if ($request->ajax()) {
            return response()->json(['message' => 'Contact updated successfully', 'contact' => $contact]);
        }

        return redirect()->route('contacts.index')->with('success', 'Contact updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        $contact = Contact::where('user_id', $user->id)->findOrFail($id);
        $contact->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Contact deleted successfully']);
        }

        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully');
    }

    /**
     * Show the merge modal to select master contact.
     */
    public function showMerge(Contact $contact)
    {
        $user = auth()->user();
        // Get all contacts of the user except the one to merge
        $contacts = Contact::where('user_id', $user->id)
            ->where('id', '!=', $contact->id)
            ->get();

        return view('contacts.merge', compact('contact', 'contacts'));
    }

    /**
     * Perform the merge of two contacts.
     */
    public function merge(Request $request, Contact $contact)
    {
        $user = auth()->user();

        Log::info('Merge started for contact ID: ' . $contact->id);

        $request->validate([
            'master_contact_id' => 'required|exists:contacts,id',
        ]);

        $masterContact = Contact::where('user_id', $user->id)->findOrFail($request->master_contact_id);
        $secondaryContact = $contact;

        if ($masterContact->id === $secondaryContact->id) {
            Log::warning('Master contact same as secondary contact: ' . $masterContact->id);
            return redirect()->back()->withErrors('Master contact cannot be the same as the secondary contact.');
        }

        DB::beginTransaction();

        try {
            Log::info('Merging emails and phones');

            if ($secondaryContact->email !== $masterContact->email) {
                $existingEmails = $masterContact->customFields()->where('field_name', 'Additional Email')->pluck('field_value')->toArray();
                if (!in_array($secondaryContact->email, $existingEmails)) {
                    $masterContact->customFields()->create([
                        'field_name' => 'Additional Email',
                        'field_value' => $secondaryContact->email,
                    ]);
                }
            }

            if ($secondaryContact->phone !== $masterContact->phone) {
                $existingPhones = $masterContact->customFields()->where('field_name', 'Additional Phone')->pluck('field_value')->toArray();
                if (!in_array($secondaryContact->phone, $existingPhones)) {
                    $masterContact->customFields()->create([
                        'field_name' => 'Additional Phone',
                        'field_value' => $secondaryContact->phone,
                    ]);
                }
            }

            // Merge profile image if master does not have one and secondary has
            if (empty($masterContact->profile_image) && !empty($secondaryContact->profile_image)) {
                $masterContact->profile_image = $secondaryContact->profile_image;
                $masterContact->save();
            }

            // Merge additional file if master does not have one and secondary has
            if (empty($masterContact->additional_file) && !empty($secondaryContact->additional_file)) {
                $masterContact->additional_file = $secondaryContact->additional_file;
                $masterContact->save();
            }

            Log::info('Merging custom fields');

            $masterCustomFields = $masterContact->customFields()->pluck('field_value', 'field_name')->toArray();
            $secondaryCustomFields = $secondaryContact->customFields()->pluck('field_value', 'field_name')->toArray();

            foreach ($secondaryCustomFields as $fieldName => $fieldValue) {
                if (!array_key_exists($fieldName, $masterCustomFields)) {
                    $masterContact->customFields()->create([
                        'field_name' => $fieldName,
                        'field_value' => $fieldValue,
                    ]);
                } elseif ($masterCustomFields[$fieldName] !== $fieldValue) {
                    $newValue = $masterCustomFields[$fieldName] . ', ' . $fieldValue;
                    $customField = $masterContact->customFields()->where('field_name', $fieldName)->first();
                    $customField->update(['field_value' => $newValue]);
                }
            }

            Log::info('Marking secondary contact as merged/inactive');

            $secondaryContact->update(['merged_into' => $masterContact->id, 'is_active' => false]);


            DB::commit();

            Log::info('Merge completed successfully');

            return redirect()->route('contacts.index')->with('success', 'Contacts merged successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error merging contacts: ' . $e->getMessage());
            return redirect()->back()->withErrors('An error occurred while merging contacts.');
        }
    }
}
