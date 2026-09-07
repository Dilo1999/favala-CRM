@php($company = config('crm.company'))
@php($noun = $documentNoun ?? 'document')

<p class="text-center text-xs text-gray-400 italic mt-10 mb-6">Thank you for your business!</p>

<div class="text-xs text-gray-600 border-t border-gray-200 pt-4">
    <p class="font-bold text-gray-800 mb-1.5 tracking-wide">TERMS AND CONDITIONS</p>
    <ol class="list-decimal list-inside space-y-0.5">
        <li>All cheques should be made payable to '{{ $company['cheque_payable_to'] }}'</li>
        <li>For account transfers: {{ $company['bank_name'] }} MVR - {{ $company['bank_accounts']['MVR'] }} | USD - {{ $company['bank_accounts']['USD'] }}</li>
        <li>All queries should be notified with in 24 hours upon receipt of this {{ $noun }}</li>
        <li>Should there be any discrepancies in the {{ $noun }}, kindly inform us with 24 hours, otherwise it will be presumed correct</li>
    </ol>
</div>
