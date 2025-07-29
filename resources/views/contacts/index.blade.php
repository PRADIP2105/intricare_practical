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
                @foreach ($contacts as $contact)
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
                @endforeach
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
                    location.reload();
                });
            }
        });
    });
});
</script>
@endsection
