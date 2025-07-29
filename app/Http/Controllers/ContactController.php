<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Contact::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $contacts = $query->with('customFields')->paginate(10);

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email',
            'phone' => 'required|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'profile_image' => 'nullable|image|max:2048',
            'additional_file' => 'nullable|file|max:5120',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.field_name' => 'required|string|max:255',
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

        $contact = Contact::create($contactData);

        if (!empty($validated['custom_fields'])) {
            foreach ($validated['custom_fields'] as $field) {
                $contact->customFields()->create($field);
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
        $contact = Contact::with('customFields')->findOrFail($id);
        return view('contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $contact = Contact::with('customFields')->findOrFail($id);
        return view('contacts.edit', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $contact = Contact::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email,' . $contact->id,
            'phone' => 'required|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'profile_image' => 'nullable|image|max:2048',
            'additional_file' => 'nullable|file|max:5120',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.id' => 'nullable|exists:contact_custom_fields,id',
            'custom_fields.*.field_name' => 'required|string|max:255',
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
            foreach ($validated['custom_fields'] as $field) {
                if (isset($field['id'])) {
                    $customField = $contact->customFields()->find($field['id']);
                    if ($customField) {
                        $customField->update($field);
                    }
                } else {
                    $contact->customFields()->create($field);
                }
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
        $contact = Contact::findOrFail($id);
        $contact->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Contact deleted successfully']);
        }

        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully');
    }
}
