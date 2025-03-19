@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-center text-blue-700 mb-6">Pledge Agreement</h1>

    <form action="{{ route('employee.loan.store_pledge') }}" method="POST" id="pledgeForm">
        @csrf
        <input type="hidden" name="loan_id" value="{{ $loan->loan_id }}">

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <p class="mb-4">
                <label for="name" class="block mb-1">Name</label>
                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" id="name" name="name" value="{{ $loan->employee->full_name }}" required>
                @error('name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror

                <label for="nationalId" class="block mt-3 mb-1">The holder of National ID number</label>
                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" id="nationalId" name="national_id" value="{{ $loan->employee->national_id }}" required>
                @error('national_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror

                <label for="address" class="block mt-3 mb-1">Residing at</label>
                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" id="address" name="address" value="{{ $loan->employee->physical_address }}" required>
                @error('address')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror

                hereby pledge the following assets to secure the loan that is granted to me by the Lender, Zimbabwe Women's Microfinance Bank (ZWMB), in full accordance with the provisions in the loan agreement:
            </p>

            <div class="overflow-x-auto mt-6">
                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="border border-gray-300 bg-gray-100 px-4 py-2 text-left">Description of asset</th>
                            <th class="border border-gray-300 bg-gray-100 px-4 py-2 text-left">Estimated Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 1; $i <= 6; $i++)
                        <tr>
                            <td class="border border-gray-300 px-4 py-2">
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" id="asset{{ $i }}" name="assets[{{ $i }}][description]">
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" id="value{{ $i }}" name="assets[{{ $i }}][value]">
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <p class="mt-6">
                I declare that I am the legal owner of the aforementioned assets and that no other person has any interest in or right to them. I agree that at any time during the term of this loan until I have fully repaid the loan, the Lender has the right to take possession of the aforementioned assets and to hold them in a safe place without using them. I understand that if I default from the loan, the Lender has the right to sell, dispose of, and realise value from the aforementioned assets.
                
                @if ($loan->loan_type->requires_vehicle_pledge ?? false)
                <span class="block mt-3">
                    I confirm that I have handed over to the Lender the original motor vehicle registration book (registration number 
                    <input type="text" class="px-3 py-2 border border-gray-300 rounded-md" id="registrationNumber" name="registration_number">).
                    I understand that I will not have access to this document until after I have fully repaid my loan.
                </span>
                @endif
            </p>

            <div class="mt-10 text-center">
                <p>
                    <label for="location" class="block mb-1">Signed at</label>
                    <input type="text" class="w-1/2 px-3 py-2 border border-gray-300 rounded-md" id="location" name="location" required>
                    @error('location')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                    
                    <span class="block my-2">on this {{ now()->format('j') }} day of {{ now()->format('F') }} of {{ now()->format('Y') }}</span>
                </p>
                
                <div class="mt-6">
                    <label for="signature" class="block mb-1">Digital Signature (Type your full name to sign)</label>
                    <input type="text" class="w-1/2 px-3 py-2 border border-gray-300 rounded-md" id="signature" name="signature" required>
                    @error('signature')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="flex justify-center space-x-4 mt-6">
                    <a href="{{ route('employee.loan.policy', ['loan' => $loan->loan_id]) }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Back</a>
                    <button type="submit" class="px-4 py-2 bg-blue-700 text-white rounded-md hover:bg-blue-800">Submit Pledge</button>
                </div>
            </div>
        </div>
    </form>
</div>

@if(session('success'))
<div class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
    <span class="block sm:inline">{{ session('success') }}</span>
</div>
@endif

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pledgeForm = document.getElementById('pledgeForm');
        
        pledgeForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const nationalId = document.getElementById('nationalId').value;
            const address = document.getElementById('address').value;
            const location = document.getElementById('location').value;
            const signature = document.getElementById('signature').value;
            
            let isValid = true;
            let errorMessage = '';
            
            if (!name) {
                isValid = false;
                errorMessage += 'Please enter your name.\n';
                document.getElementById('name').classList.add('border-red-500');
            }
            
            if (!nationalId) {
                isValid = false;
                errorMessage += 'Please enter your National ID.\n';
                document.getElementById('nationalId').classList.add('border-red-500');
            }
            
            if (!address) {
                isValid = false;
                errorMessage += 'Please enter your address.\n';
                document.getElementById('address').classList.add('border-red-500');
            }
            
            if (!location) {
                isValid = false;
                errorMessage += 'Please enter the location.\n';
                document.getElementById('location').classList.add('border-red-500');
            }
            
            if (!signature) {
                isValid = false;
                errorMessage += 'Please sign the form by typing your full name.\n';
                document.getElementById('signature').classList.add('border-red-500');
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please correct the following errors:\n' + errorMessage);
            }
        });
    });
</script>
@endsection