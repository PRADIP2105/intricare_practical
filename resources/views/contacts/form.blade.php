@extends('layouts.app')

@section('content')
@php
    $isEdit = isset($contact);
    $formAction = $isEdit ? route('contacts.update', $contact->id) : route('contacts.store');
    $formMethod = $isEdit ? 'PUT' : 'POST';
@endphp

<div class="max-w-4xl mx-auto py-12 sm:px-8 lg:px-12 bg-white shadow rounded-lg mt-6">
    <h1 class="text-2xl font-bold mb-6">{{ $isEdit ? 'Edit Contact' : 'Create Contact' }}</h1>

    <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" id="contactForm" class="space-y-6">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif
        <div id="formMessage" class="fixed bottom-5 right-5 max-w-xs p-4 rounded shadow-lg text-sm hidden"></div>

        

        <div class="grid grid-cols-2 gap-4 mt-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $isEdit ? $contact->name : '') }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm" />
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                <input type="email" name="email" id="email" value="{{ old('email', $isEdit ? $contact->email : '') }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm" />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-6">
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone *</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $isEdit ? $contact->phone : '') }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm" />
            </div>

            <fieldset>
                <legend class="block text-sm font-medium text-gray-700 mb-1">Gender</legend>
                <div class="flex space-x-4 text-sm">
                    @php
                        $gender = old('gender', $isEdit ? $contact->gender : '');
                        $gender = old('gender', $isEdit ? $contact->gender : 'male');
                    @endphp
                    <label class="inline-flex items-center">
                        <input type="radio" name="gender" value="male" {{ $gender == 'male' ? 'checked' : '' }}
                            class="form-radio text-blue-600" />
                        <span class="ml-2">Male</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="gender" value="female" {{ $gender == 'female' ? 'checked' : '' }}
                            class="form-radio text-pink-600" />
                        <span class="ml-2">Female</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="gender" value="other" {{ $gender == 'other' ? 'checked' : '' }}
                            class="form-radio text-gray-600" />
                        <span class="ml-2">Other</span>
                    </label>
                </div>
            </fieldset>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-6">
            <div>
                <label for="profile_image" class="block text-sm font-medium text-gray-700">Profile Image</label>
                @if($isEdit && $contact->profile_image)
                    <img src="{{ asset('storage/' . $contact->profile_image) }}" alt="Profile Image" class="mb-2 w-32 h-32 object-cover rounded-md" onerror="this.style.display='none'">
                @endif
                <input type="file" name="profile_image" id="profile_image"
                    class="mt-1 block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-xs file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100" />
            </div>

            <div>
                <label for="additional_file" class="block text-sm font-medium text-gray-700">Additional File</label>
                @if($isEdit && $contact->additional_file)
                    <a href="{{ url('storage/' . $contact->additional_file) }}" class="block mb-2 text-blue-600 hover:underline" target="_blank" download>Download File</a>
                @endif
                <input type="file" name="additional_file" id="additional_file"
                    class="mt-1 block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-xs file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100" />
            </div>
        </div>
        <div id="customFieldsContainer" class="grid grid-cols-2 gap-4">
            @if($isEdit && $contact->customFields && $contact->customFields->count() > 0)
                @foreach($contact->customFields->unique('field_name') as $index => $customField)
                    <div class="custom-field mb-4" data-field-name="{{ $customField->field_name }}">
                    <input type="text" name="custom_fields[{{ $index }}][field_name]" placeholder="Field Name" value="{{ old('custom_fields.' . $index . '.field_name', $customField->field_name) }}" class="mb-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" autocomplete="off">
                    <input type="text" name="custom_fields[{{ $index }}][field_value]" placeholder="Field Value" value="{{ old('custom_fields.' . $index . '.field_value', $customField->field_value) }}" class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2">
                    <button type="button" class="mt-1 inline-flex items-center px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 removeCustomField">Remove</button>
                </div>
            @endforeach
        @else
            <div class="custom-field mb-4">
                <input type="text" name="custom_fields[0][field_name]" placeholder="Field Name" value="{{ old('custom_fields.0.field_name') }}" class="mb-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" autocomplete="off">
                <input type="text" name="custom_fields[0][field_value]" placeholder="Field Value" value="{{ old('custom_fields.0.field_value') }}" class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2">
                <button type="button" class="mt-1 inline-flex items-center px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 removeCustomField">Remove</button>
            </div>
        @endif
        </div>
        <button type="button" id="addCustomFieldBtn" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 mb-6">Add Custom Field</button>

        <div>
            <button type="submit"
                class="inline-flex justify-center py-1 px-3 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                {{ $isEdit ? 'Update Contact' : 'Save Contact' }}
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let customFieldIndex = {{ $isEdit && isset($contact->customFields) && is_countable($contact->customFields) ? count($contact->customFields) : 1 }};

    document.getElementById('addCustomFieldBtn').addEventListener('click', function () {
        const container = document.getElementById('customFieldsContainer');
        const newField = document.createElement('div');
        newField.classList.add('custom-field', 'mb-4');
        newField.innerHTML = `
            <input type="text" name="custom_fields[\${customFieldIndex}][field_name]" placeholder="Field Name" class="mb-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" required>
            <input type="text" name="custom_fields[\${customFieldIndex}][field_value]" placeholder="Field Value" class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2">
            <button type="button" class="mt-1 inline-flex items-center px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 removeCustomField">Remove</button>
        `;
        container.appendChild(newField);
        customFieldIndex++;

        newField.querySelector('.removeCustomField').addEventListener('click', function () {
            newField.remove();
        });
    });

    document.querySelectorAll('.removeCustomField').forEach(button => {
        button.addEventListener('click', function () {
            this.parentElement.remove();
        });
    });

    // AJAX form submission
    const form = document.getElementById('contactForm');
    const formMessage = document.getElementById('formMessage');

    form.addEventListener('submit', function(e) {
        
        e.preventDefault();
        formMessage.textContent = '';
        formMessage.classList.add('hidden');
        const formData = new FormData(form);
        console.log("action link >> ",form.action);
        fetch(form.action, {
            method: '{{ $isEdit ? 'POST' : 'POST' }}',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            } else if (response.status === 422) {
                return response.json().then(errData => {
                    throw errData;
                });
            } else {
                throw new Error('Network response was not ok.');
            }
        })
        .then(data => {
            if(data.message) {
                formMessage.textContent = data.message;
                formMessage.className = 'fixed bottom-5 right-5 max-w-xs p-4 rounded shadow-lg text-sm bg-green-100 text-green-800';
                formMessage.classList.remove('hidden');
                if(!{{ $isEdit ? 'true' : 'false' }}) {
                    form.reset();
                    // Redirect to contacts list after successful creation
                    window.location.href = "{{ route('contacts.index') }}";
                }
                setTimeout(() => {
                    formMessage.classList.add('hidden');
                }, 10000);
            }
        })
        .catch(error => {
            if (error.errors) {
                if (error.errors.email && error.errors.email.length > 0) {
                    formMessage.textContent = error.errors.email[0];
                } else if (error.errors.phone && error.errors.phone.length > 0) {
                    formMessage.textContent = error.errors.phone[0];
                } else {
                    const firstErrorKey = Object.keys(error.errors)[0];
                    formMessage.textContent = error.errors[firstErrorKey][0];
                }
            } else {
                formMessage.textContent = 'An error occurred. Please try again.';
            }
            formMessage.className = 'fixed bottom-5 right-5 max-w-xs p-4 rounded shadow-lg text-sm bg-red-100 text-red-800';
            formMessage.classList.remove('hidden');
            setTimeout(() => {
                formMessage.classList.add('hidden');
            }, 10000);
        });
    });
});
</script>
@endsection
