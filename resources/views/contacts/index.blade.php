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
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <a href="{{ route('contacts.show', $contact->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                        <a href="{{ route('contacts.edit', $contact->id) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
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
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var params = form.serialize();
            $.ajax({
                url: '{{ route("contacts.index") }}',
                type: 'GET',
                data: params,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(data) {
                    var tbody = $('#contactsTable tbody');
                    tbody.empty();
                    if(data.data.length === 0) {
                        tbody.append('<tr><td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">No records found.</td></tr>');
                    } else {
                        $.each(data.data, function(index, contact) {
                            var tr = $('<tr></tr>');
                            var recordNumber = data.from + index;
                            tr.append('<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + recordNumber + '</td>');
                            tr.append('<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' + contact.name + '</td>');
                            tr.append('<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + contact.email + '</td>');
                            tr.append('<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + contact.phone + '</td>');
                            tr.append('<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + contact.gender.charAt(0).toUpperCase() + contact.gender.slice(1) + '</td>');
                            tr.append('<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">' +
                                '<a href="/contacts/' + contact.id + '" class="text-indigo-600 hover:text-indigo-900">View</a> ' +
                                '<a href="/contacts/' + contact.id + '/edit" class="text-yellow-600 hover:text-yellow-900">Edit</a> ' +
                                '<form action="/contacts/' + contact.id + '" method="POST" class="inline-block deleteForm">' +
                                '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                                '<input type="hidden" name="_method" value="DELETE">' +
                                '<button type="submit" class="text-red-600 hover:text-red-900">Delete</button>' +
                                '</form>' +
                                '</td>');
                            tbody.append(tr);
                        });
                    }
                    attachDeleteHandlers();
                }
            });
        });

        function attachDeleteHandlers() {
            $('.deleteForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                var currentForm = $(this);
                if ($('#customConfirmModal').length === 0) {
                    showCustomConfirm('Are you sure you want to delete this contact?', function() {
                        $.ajax({
                            url: currentForm.attr('action'),
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            contentType: 'application/json',
                            data: JSON.stringify({
                                _method: 'DELETE'
                            }),
                            success: function(data) {
                                if(data.message) {
                                    showMessage(data.message);
                                    currentForm.closest('tr').remove();
                                }
                            },
                            error: function() {
                                showMessage('An error occurred. Please try again.', true);
                            }
                        });
                    });
                }
            });
        }

        function showMessage(message, isError) {
            isError = isError || false;
            var formMessage = $('#formMessage');
            if(formMessage.length === 0) {
                formMessage = $('<div id="formMessage" class="fixed bottom-5 right-5 max-w-xs p-4 rounded shadow-lg text-sm"></div>');
                $('body').append(formMessage);
            }
            formMessage.text(message);
            formMessage.removeClass('bg-green-100 text-green-800 bg-red-100 text-red-800');
            if(isError) {
                formMessage.addClass('bg-red-100 text-red-800');
            } else {
                formMessage.addClass('bg-green-100 text-green-800');
            }
            formMessage.show();
            setTimeout(function() {
                formMessage.hide();
            }, 5000);
        }

        function showCustomConfirm(message, onConfirm) {
            var modalOverlay = $('<div id="customConfirmModal"></div>').css({
                position: 'fixed',
                top: 0,
                left: 0,
                width: '100%',
                height: '100%',
                backgroundColor: 'rgba(0,0,0,0.5)',
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                zIndex: 1000
            });

            var modalBox = $('<div></div>').css({
                backgroundColor: '#fff',
                padding: '20px',
                borderRadius: '8px',
                boxShadow: '0 2px 10px rgba(0,0,0,0.1)',
                maxWidth: '400px',
                width: '90%',
                textAlign: 'center'
            });

            var msg = $('<p></p>').text(message).css('marginBottom', '20px');

            var btnConfirm = $('<button>Confirm</button>').css({
                marginRight: '10px',
                padding: '8px 16px',
                backgroundColor: '#2563eb',
                color: '#fff',
                border: 'none',
                borderRadius: '4px',
                cursor: 'pointer'
            });

            var btnCancel = $('<button>Cancel</button>').css({
                padding: '8px 16px',
                backgroundColor: '#e5e7eb',
                color: '#000',
                border: 'none',
                borderRadius: '4px',
                cursor: 'pointer'
            });

            modalBox.append(msg, btnConfirm, btnCancel);
            modalOverlay.append(modalBox);
            $('body').append(modalOverlay);

            btnConfirm.on('click', function() {
                onConfirm();
                modalOverlay.remove();
            });

            btnCancel.on('click', function() {
                modalOverlay.remove();
            });
        }

        attachDeleteHandlers();
    });
    </script>
@endsection
