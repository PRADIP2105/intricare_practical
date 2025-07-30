@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Contacts</h1>
        <a href="{{ route('contacts.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Add Contact
        </a>
    </div>

    <form id="filterForm" class="mb-6 flex space-x-4">
        <input type="text" name="name" placeholder="Name" value="{{ request('name') }}" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 w-1/3" />
        <input type="text" name="email" placeholder="Email" value="{{ request('email') }}" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 w-1/3" />
        <select name="gender" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 w-1/6">
            <option value="">Select Gender</option>
            <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
            <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
            <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
        </select>
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
            Filter
        </button>
    </form>

    <script>
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const params = new URLSearchParams();
        for (const pair of formData.entries()) {
            if(pair[1]) {
                params.append(pair[0], pair[1]);
            }
        }
        fetch('{{ route("contacts.index") }}?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#contactsTable tbody');
            tbody.innerHTML = '';
            data.data.forEach(contact => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${contact.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${contact.email}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${contact.phone}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${contact.gender.charAt(0).toUpperCase() + contact.gender.slice(1)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <a href="/contacts/${contact.id}" class="text-indigo-600 hover:text-indigo-900">View</a>
                        <a href="/contacts/${contact.id}/edit" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                        <form action="/contacts/${contact.id}" method="POST" class="inline-block deleteForm">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            attachDeleteHandlers();
        });
    });

    function attachDeleteHandlers() {
        const deleteForms = document.querySelectorAll('.deleteForm');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this contact?')) {
                    fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: 'DELETE'
                        })
                    }).then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        form.closest('tr').remove();
                    });
                }
            });
        });
    }

    attachDeleteHandlers();
    </script>

    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200" id="contactsTable">
            <thead class="bg-gray-50">
                <tr>
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
</div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteForms = document.querySelectorAll('.deleteForm');
        const formMessage = document.createElement('div');
        formMessage.id = 'formMessage';
        formMessage.className = 'fixed bottom-5 right-5 max-w-xs p-4 rounded shadow-lg text-sm bg-green-100 text-green-800 hidden';
        document.body.appendChild(formMessage);

        function showMessage(message, isError = false) {
            formMessage.textContent = message;
            formMessage.className = 'fixed bottom-5 right-5 max-w-xs p-4 rounded shadow-lg text-sm ' + (isError ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800');
            formMessage.classList.remove('hidden');
            setTimeout(() => {
                formMessage.classList.add('hidden');
            }, 5000);
        }

            deleteForms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const currentForm = this;
                    // Custom confirm box
                    // Remove window.confirm and use custom modal
                    if (!document.getElementById('customConfirmModal')) {
                        showCustomConfirm('Are you sure you want to delete this contact?', () => {
                            fetch(currentForm.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    _method: 'DELETE'
                                })
                            }).then(response => response.json())
                            .then(data => {
                                if(data.message) {
                                    showMessage(data.message);
                                    currentForm.closest('tr').remove();
                                }
                            })
                            .catch(() => {
                                showMessage('An error occurred. Please try again.', true);
                            });
                        });
                    }
                });

            // Custom confirm modal implementation
            function showCustomConfirm(message, onConfirm) {
                // Create modal elements
                const modalOverlay = document.createElement('div');
                modalOverlay.style.position = 'fixed';
                modalOverlay.style.top = '0';
                modalOverlay.style.left = '0';
                modalOverlay.style.width = '100%';
                modalOverlay.style.height = '100%';
                modalOverlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
                modalOverlay.style.display = 'flex';
                modalOverlay.style.justifyContent = 'center';
                modalOverlay.style.alignItems = 'center';
                modalOverlay.style.zIndex = '1000';

                const modalBox = document.createElement('div');
                modalBox.style.backgroundColor = '#fff';
                modalBox.style.padding = '20px';
                modalBox.style.borderRadius = '8px';
                modalBox.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
                modalBox.style.maxWidth = '400px';
                modalBox.style.width = '90%';
                modalBox.style.textAlign = 'center';

                const msg = document.createElement('p');
                msg.textContent = message;
                msg.style.marginBottom = '20px';

                const btnConfirm = document.createElement('button');
                btnConfirm.textContent = 'Confirm';
                btnConfirm.style.marginRight = '10px';
                btnConfirm.style.padding = '8px 16px';
                btnConfirm.style.backgroundColor = '#2563eb';
                btnConfirm.style.color = '#fff';
                btnConfirm.style.border = 'none';
                btnConfirm.style.borderRadius = '4px';
                btnConfirm.style.cursor = 'pointer';

                const btnCancel = document.createElement('button');
                btnCancel.textContent = 'Cancel';
                btnCancel.style.padding = '8px 16px';
                btnCancel.style.backgroundColor = '#e5e7eb';
                btnCancel.style.color = '#000';
                btnCancel.style.border = 'none';
                btnCancel.style.borderRadius = '4px';
                btnCancel.style.cursor = 'pointer';

                modalBox.appendChild(msg);
                modalBox.appendChild(btnConfirm);
                modalBox.appendChild(btnCancel);
                modalOverlay.appendChild(modalBox);
                document.body.appendChild(modalOverlay);

                btnConfirm.addEventListener('click', () => {
                    onConfirm();
                    document.body.removeChild(modalOverlay);
                });

                btnCancel.addEventListener('click', () => {
                    document.body.removeChild(modalOverlay);
                });
            }
            });
        });
    });
    </script>
@endsection
