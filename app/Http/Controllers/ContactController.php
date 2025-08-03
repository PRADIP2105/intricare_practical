<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
   
    public function index(Request $request)
    {
        $request->validate([
            'gender' => 'nullable|in:male,female,other',
        ]);

        $user = $request->user();
        $query = Contact::where('user_id', $user->id)->whereNull('deleted_at');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhere('phone', 'like', '%' . $searchTerm . '%');
            });
        }
        
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('status')) {
            $status = strtolower($request->status);
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        if ($request->filled('custom_field')) {
            $customFieldValue = $request->custom_field;
            $query->whereHas('customFields', function ($q) use ($customFieldValue) {
                $q->where('field_value', 'like', '%' . $customFieldValue . '%');
            });
        }

        $contacts = $query->with('customFields')->orderByDesc('id')->paginate(10);
      
        if ($request->ajax()) {
            // Check if it's a request for just the table component
            if ($request->has('component') && $request->component === 'table') {
                // Get all request parameters except component and page
                $params = $request->except('component', 'page');
                
                // Append the current request parameters to pagination links
                foreach ($params as $key => $value) {
                    if ($value !== null && $value !== '') {
                        $contacts->appends($key, $value);
                    }
                }
                
                return view('contacts._table', compact('contacts'))->render();
            }
            return response()->json($contacts);
        }

        // For non-AJAX requests, also append parameters to pagination links
        $params = $request->except('page');
        foreach ($params as $key => $value) {
            if ($value !== null && $value !== '') {
                $contacts->appends($key, $value);
            }
        }
        
        return view('contacts.index', compact('contacts'));
    }

    
    public function create(Request $request)
    {
        if ($request->ajax()) {
            return view('contacts.form', ['contact' => null])->render();
        }
        return view('contacts.create');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:contacts,email',
                'phone' => ['required', 'regex:/^\d{10}$/', 'unique:contacts,phone'],
                'gender' => 'nullable|in:male,female,other',
                'profile_image' => 'nullable|image|max:2048',
                'additional_file' => 'nullable|file|max:5120',
                'custom_fields' => 'nullable|array',
                'custom_fields.*.field_name' => 'nullable|string|max:255',
                'custom_fields.*.field_value' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

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

   
    public function show(Request $request, string $id)
    {
        $user = auth()->user();
        $contact = Contact::with(['customFields', 'mergedInto'])->where('user_id', $user->id)->findOrFail($id);
        
        if ($request->ajax()) {
            return view('contacts.show', compact('contact'))->render();
        }
        return view('contacts.show', compact('contact'));
    }

    public function edit(Request $request, string $id)
    {
        $user = auth()->user();
        $contact = Contact::with('customFields')->where('user_id', $user->id)->findOrFail($id);
        
        if ($request->ajax()) {
            return view('contacts.form', ['contact' => $contact])->render();
        }
        return view('contacts.edit', compact('contact'));
    }

    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        $contact = Contact::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email,' . $contact->id,
            'phone' => ['required', 'regex:/^\d{10}$/', 'unique:contacts,phone,' . $contact->id],
            'gender' => 'required|in:male,female,other',
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

   
    public function showMerge(Contact $contact)
    {
        $user = auth()->user();
        // Get all contacts of the user except the one to merge
        $contacts = Contact::where('user_id', $user->id)
            ->where('id', '!=', $contact->id)
            ->get();

        return view('contacts.merge', compact('contact', 'contacts'));
    }

    
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

            // Check if it's an AJAX request
            if (request()->ajax()) {
                return response()->json(['message' => 'Contacts merged successfully.']);
            }

            return redirect()->route('contacts.index')->with('success', 'Contacts merged successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error merging contacts: ' . $e->getMessage());
            
            // Check if it's an AJAX request
            if (request()->ajax()) {
                return response()->json(['error' => 'An error occurred while merging contacts.'], 500);
            }
            
            return redirect()->back()->withErrors('An error occurred while merging contacts.');
        }
    }
}
