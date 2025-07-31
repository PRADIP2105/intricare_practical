@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Contacts</h1>
        <a href="{{ route('contacts.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Add Contact
        </a>
    </div>

    <form id="filterForm" class="mb-6 flex flex-wrap gap-4">
        <input type="text" name="name" placeholder="Name" value="{{ request('name') }}" class="rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 flex-grow min-w-[200px]" />
        <input type="text" name="email" placeholder="Email" value="{{ request('email') }}" class="rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 flex-grow min-w-[200px]" />
        <select name="gender" class="rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 w-40">
            <option value="">Select Gender</option>
            <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
            <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
            <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
        </select>
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
            Filter
        </button>
    </form>

    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200" id="contactsTable">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($contacts as $contact)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contacts->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $contact->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contact->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contact->phone }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($contact->gender) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($contact->is_active)
                            <span class="text-green-600 font-semibold">Active</span>
                        @else
                            <span class="text-red-600 font-semibold">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <a href="{{ route('contacts.show', $contact->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                        @if($contact->is_active)
                            <a href="{{ route('contacts.edit', $contact->id) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                            <a href="#" class="text-green-600 hover:text-green-900 merge-contact" data-contact-id="{{ $contact->id }}">Merge</a>
                        @else
                            <span class="text-gray-400 cursor-not-allowed" title="Merged contact cannot be edited">Edit</span>
                        @endif
                        <form action="{{ route('contacts.destroy', $contact->id) }}" method="POST" class="inline-block deleteForm">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                        No records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $contacts->links() }}
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        var mergeContactId = null;
        var contactsList = @json($contacts);

        $('.merge-contact').off('click').on('click', function(e) {
            e.preventDefault();
            mergeContactId = $(this).data('contact-id');

            // Populate master contact select excluding the contact to merge
            var options = '<option value="">Select master contact</option>';
            contactsList.data.forEach(function(contact) {
                if(contact.id !== mergeContactId && contact.is_active) {
                    options += '<option value="' + contact.id + '">' + contact.name + ' (' + contact.email + ')</option>';
                }
            });
            $('#masterContactSelect').html(options);

            $('#mergeModal').removeClass('hidden').addClass('flex');
        });

        $('#cancelMergeBtn').off('click').on('click', function() {
            $('#mergeModal').addClass('hidden').removeClass('flex');
            mergeContactId = null;
        });

        $('#confirmMergeBtn').off('click').on('click', function() {
            var masterContactId = $('#masterContactSelect').val();
            if(!masterContactId) {
                alert('Please select a master contact.');
                return;
            }

            // Confirm before merging
            // Remove browser alert and use custom modal instead
            $('#mergeModal').addClass('hidden').removeClass('flex');
            $('#mergeConfirmModal').removeClass('hidden').addClass('flex');
            return;
        });

        // Custom merge confirmation modal buttons
        $('#mergeConfirmCancelBtn').off('click').on('click', function() {
            $('#mergeConfirmModal').addClass('hidden').removeClass('flex');
            $('#mergeModal').removeClass('hidden').addClass('flex');
        });

        $('#mergeConfirmBtn').off('click').on('click', function() {
            var masterContactId = $('#masterContactSelect').val();
            if(!masterContactId) {
                alert('Please select a master contact.');
                return;
            }

            // Submit merge request via POST
            $.ajax({
                url: '/contacts/' + mergeContactId + '/merge',
                type: 'POST',
                data: {
                    master_contact_id: masterContactId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Remove alert and show success message at bottom right
                    var formMessage = $('#formMessage');
                    if(formMessage.length === 0) {
                        formMessage = $('<div id="formMessage" class="fixed bottom-5 right-5 max-w-xs p-4 rounded shadow-lg text-sm bg-green-100 text-green-800"></div>');
                        $('body').append(formMessage);
                    }
                    formMessage.text('Contacts merged successfully.');

                    // After showing message, redirect to contacts index page without reload
                    formMessage.show();
                    setTimeout(function() {
                        formMessage.hide();
                        window.location.href = '{{ route("contacts.index") }}';
                    }, 3000);
                },
                error: function(xhr) {
                    alert('Error merging contacts: ' + xhr.responseJSON.message || 'Unknown error');
                }
            });
            $('#mergeConfirmModal').addClass('hidden').removeClass('flex');
        });
    });
    </script>

    <!-- Merge Modal -->
    <div id="mergeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
        <div class="bg-white rounded-lg p-6 w-96">
            <h2 class="text-lg font-semibold mb-4">Select Master Contact</h2>
            <select id="masterContactSelect" class="w-full border border-gray-300 rounded-md p-2 mb-4">
                <!-- Options will be populated dynamically -->
            </select>
            <div class="flex justify-end space-x-4">
                <button id="cancelMergeBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button id="confirmMergeBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Merge</button>
            </div>
        </div>
    </div>

    <!-- Merge Confirmation Modal -->
    <div id="mergeConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
        <div class="bg-white rounded-lg p-6 w-96">
            <h2 class="text-lg font-semibold mb-4">Confirm Merge</h2>
            <p>Are you sure you want to merge the selected contacts?</p>
            <div class="flex justify-end space-x-4 mt-4">
                <button id="mergeConfirmCancelBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button id="mergeConfirmBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Confirm</button>
            </div>
        </div>
    </div>
@endsection
