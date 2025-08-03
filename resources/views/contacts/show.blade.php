<h1 class="text-3xl font-bold mb-6">Contact Details</h1>

@if(!$contact->is_active)
    <div class="mb-6 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
        This contact has been merged into another contact: {{ $contact->mergedInto ? $contact->mergedInto->name : 'N/A' }}.
    </div>
@endif

<div class="grid grid-cols-2 gap-4 mb-6">
    <div>
        <h2 class="text-sm font-semibold text-gray-500">Name</h2>
        <p class="mt-1 text-gray-900">{{ $contact->name }}</p>
    </div>
    <div>
        <h2 class="text-sm font-semibold text-gray-500">Email</h2>
        <p class="mt-1 text-gray-900">{{ $contact->email }}</p>
    </div>
    <div>
        <h2 class="text-sm font-semibold text-gray-500">Phone</h2>
        <p class="mt-1 text-gray-900">{{ $contact->phone }}</p>
    </div>
    <div>
        <h2 class="text-sm font-semibold text-gray-500">Gender</h2>
        <p class="mt-1 text-gray-900 capitalize">{{ $contact->gender }}</p>
    </div>
    <div>
        <h2 class="text-sm font-semibold text-gray-500">Profile Image</h2>
        @if($contact->profile_image)
            <img src="{{ asset('storage/' . $contact->profile_image) }}" alt="Profile Image" class="mt-1 w-32 h-32 object-cover rounded-md">
        @else
            <p class="mt-1 text-gray-500">No image uploaded</p>
        @endif
    </div>
    <div>
        <h2 class="text-sm font-semibold text-gray-500">Additional File</h2>
        @if($contact->additional_file)
            <a href="{{ asset('storage/' . $contact->additional_file) }}" class="text-blue-600 hover:underline" target="_blank">Download File</a>
        @else
            <p class="mt-1 text-gray-500">No file uploaded</p>
        @endif
    </div>
</div>

<h3 class="text-xl font-semibold mb-4">Custom Fields</h3>
@if($contact->customFields && $contact->customFields->count() > 0)
    <div class="grid grid-cols-2 gap-4">
        @foreach($contact->customFields as $customField)
            <div>
                <h4 class="text-sm font-medium text-gray-500">{{ $customField->field_name }}</h4>
                <p class="mt-1 text-gray-900">
                    {{ $customField->field_value }}
                    @if(strpos($customField->field_value, ',') !== false)
                        <span class="text-xs text-blue-600 ml-2">(Merged values)</span>
                    @endif
                </p>
            </div>
        @endforeach
    </div>
@else
    <p class="text-gray-500">No custom fields available.</p>
@endif
