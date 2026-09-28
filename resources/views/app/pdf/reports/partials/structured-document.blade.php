<section class="report-document">
    <table class="document-heading">
        <tr>
            <td class="identity">
                <h1>{{ $documentTitle }}</h1>
                <p class="party-label">Party Name</p>
                <p class="party-name">{{ $document->master->name }}</p>
            </td>
            <td class="metadata">
                <table>
                    @foreach ($metadata as $label => $value)
                        <tr><th>{{ $label }}</th><td>{{ $value }}</td></tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>
    @if ($documentKind === 'receipt')
        <table class="line-items">
            <thead><tr><th>Number</th><th>Mode</th><th>Date</th><th>Party</th><th class="number">Amount</th></tr></thead>
            <tbody>
                <tr>
                    <td>{{ $document->receipt_number }}</td><td>{{ $document->receipt_mode }}</td>
                    <td>{{ $document->receipt_date }}</td><td>{{ $document->master->name }}</td>
                    <td class="number">₹ {{ $document->amount }}</td>
                </tr>
                <tr class="summary"><td colspan="4"></td><td class="number grand-total">{{ 'Rs ' . $total_amount }}</td></tr>
            </tbody>
        </table>
    @else
        <table class="line-items">
            <thead><tr>
                <th style="width: 8%">S.No.</th><th style="width: 38%">Description</th>
                <th style="width: 14%" class="quantity">Quantity</th><th style="width: 20%" class="number">Rate</th><th style="width: 20%" class="number">Amount</th>
            </tr></thead>
            <tbody>
                @foreach ($documentItems as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td><td>{{ $item->name }}</td>
                        <td class="quantity">{{ $item->quantity }} {{ $item->inventory ? $item->inventory->unit : '' }}</td>
                        <td class="number">₹ {{ $item->sale_price }}</td><td class="number">₹ {{ $item->total }}</td>
                    </tr>
                @endforeach
                @if ($documentKind === 'invoice')
                    <tr class="summary">
                        <td colspan="2"></td>
                        <td class="quantity">Total Quantity: {{ str_replace('.00', '', $total_quantity) }}</td>
                        <td colspan="2" style="padding: 0">
                            <table class="totals-breakdown">
                                <tr><td>Subtotal :</td><td class="number">₹ {{ $document->sub_total }}</td></tr>
                                @if ($document->indirect_income)
                                    <tr><td>{{ $document->indirect_income }} :</td><td class="number">₹ {{ $document->indirect_income_value }}</td></tr>
                                @endif
                                @if ($document->indirect_expense)
                                    <tr><td>{{ $document->indirect_expense }} :</td><td class="number">(-) ₹ {{ $document->indirect_expense_value }}</td></tr>
                                @endif
                                <tr><td class="grand-total">Total :</td><td class="number grand-total">₹ {{ $document->total }}</td></tr>
                            </table>
                        </td>
                    </tr>
                @else
                    <tr class="summary"><td colspan="2"></td><td class="quantity">Total: {{ $total_quantity }}</td><td></td><td class="number grand-total">{{ 'Rs ' . $total_amount }}</td></tr>
                @endif
            </tbody>
        </table>
    @endif
    <div class="document-footer" data-last-page-footer>
        <table class="document-notes"><tr>
            <td><p class="label">Amount Chargeable (in words)</p><p class="amount-words">{{ $numberTowords($total_amount) }}</p></td>
            <td><p class="label">Remark:</p><p>{{ $document->notes }}</p></td>
        </tr></table>
        <div class="declaration">
            <table><tr>
                <td><p class="label">Declaration:</p><p>We declare that this {{ $documentKind === 'receipt' ? 'receipt' : 'estimate' }} shows the actual price of the goods described and that all particulars are true and correct</p></td>
                <td class="signature">Authorised Signatory</td>
            </tr></table>
            <p class="document-footnote">This is a Computer Generated {{ $documentKind === 'receipt' ? 'Receipt' : 'Estimate' }}</p>
        </div>
    </div>
</section>
