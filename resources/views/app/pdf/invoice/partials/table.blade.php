<table width="100%" class="table2" cellspacing="0" border="0">
    <tr class="main-table-header">
        <th class="ItemTableHeader" style="text-align: right; color: #55547A; padding-right: 20px">#</th>
        @if ($invoice->discount_per_item === 'NO')
            <th width="50%" class="ItemTableHeader" style="text-align: left; color: #55547A; padding-left: 0px">Items
            </th>
        @else
            <th width="30%" class="ItemTableHeader" style="text-align: left; color: #55547A; padding-left: 0px">Items
            </th>
        @endif
        <th width="25%" class="ItemTableHeader" style="text-align: right; color: #55547A; padding-right: 20px">Quantity
        </th>
        <th width="25%" class="ItemTableHeader" style="text-align: right; color: #55547A; padding-right: 20px">Price
        </th>
        @if ($invoice->discount_per_item === 'YES')
            <th width="10%" class="ItemTableHeader" style="text-align: right; color: #55547A; padding-left: 10px">
                Discount</th>
        @endif
        <th width="25%" class="ItemTableHeader" style="text-align: right; color: #55547A;">Amount</th>
    </tr>
    @php
        $index = 1;
    @endphp
    @foreach ($invoice->inventories as $item)
        <tr class="item-details">
            <td class="inv-item items"
                style="text-align: right; color: #040405; padding-right: 20px; vertical-align: top;">
                {{ $index }}
            </td>
            <td class="inv-item items" style="text-align: left; color: #040405;padding-left: 0px">
                <span>{{ $item->name }}</span><br>
                <span
                    style="text-align: left; color: #595959; font-size: 9px; font-weight:300; line-height: 12px;">{{ $item->description }}</span>
            </td>
            <td class="inv-item items" style="text-align: right; color: #040405; padding-right: 20px">
                {{ $item->quantity }}
            </td>
            <td class="inv-item items" style="text-align: right; color: #040405; padding-right: 20px">
                ₹ {!! $item->price / 100 !!}
            </td>
            @if ($invoice->discount_per_item === 'YES')
                <td class="inv-item items" style="text-align: right; color: #040405; padding-left: 10px">
                    @if ($item->discount_type === 'fixed')
                        ₹ {!! $item->discount_val !!}
                    @endif
                    @if ($item->discount_type === 'percentage')
                        {{ $item->discount }}%
                    @endif
                </td>
            @endif
            <td class="inv-item items" style="text-align: right; color: #040405;">
                ₹ {!! $item->total / 100 !!}
            </td>
        </tr>
        @php
            $index += 1;
        @endphp
    @endforeach
</table>

<table cellspacing="0px" style="width:770px; margin-left:550px; top: 30px" border="0" class="table3 @if (count($invoice->inventories) > 12) page-break @endif">
    <tr>
        <td style="padding:3px 0px"></td>
        <td style="padding:3px 0px"></td>
    </tr>
    <tr>
        <td class="no-border total-border-left"
            style="padding-left:10px; padding-bottom:10px; text-align:left; padding-top:20px; font-size:12px;  color: #55547A;">
            <label class="total-bottom"> Total </label>
        </td>
        <td class="no-border total-border-right items padd8"
            style="padding-right:10px; font-weight: 500; text-align: right; font-size:12px;  padding-top:20px; color: #5851DB">
            ₹ {!! $invoice->total / 100 !!}
        </td>
    </tr>
</table>
