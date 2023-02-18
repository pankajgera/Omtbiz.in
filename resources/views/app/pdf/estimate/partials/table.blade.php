<table width="100%" class="table2" cellspacing="0" border="0">
    <tr class="main-table-header">
        <th class="ItemTableHeader" style="text-align: right; color: #55547A; padding-right: 20px">#</th>
        <th width="40%" class="ItemTableHeader" style="text-align: left; color: #55547A; padding-left: 0px">Items</th>
        <th width="17%" class="ItemTableHeader" style="text-align: right; color: #55547A; padding-right: 20px">Quantity</th>
        <th width="18%" class="ItemTableHeader" style="text-align: right; color: #55547A; padding-right: 40px">Price</th>
        <th width="15%" class="ItemTableHeader" style="text-align: right; color: #55547A;">Amount</th>
    </tr>
    @php
        $index = 1
    @endphp
    @foreach ($estimate->items as $item)
        <tr class="item-details">
            <td
                class="inv-item items"
                style="text-align: right; color: #040405; padding-right: 20px; vertical-align: top;"
            >
                {{$index}}
            </td>
            <td
                class="inv-item items"
                style="text-align: left; color: #040405;padding-left: 0px"
            >
                <span>{{ $item->name }}</span><br>
                <span
                    style="text-align: left; color: #595959; font-size: 9px; font-weight:300; line-height: 12px;"
                >
                    {{ $item->description }}
                </span>
            </td>
            <td
                class="inv-item items"
                style="text-align: right; color: #040405; padding-right: 20px"
            >
                {{$item->quantity}}
            </td>
            <td
                class="inv-item items"
                style="text-align: right; color: #040405; padding-right: 40px"
            >
                {!! format_money_pdf($item->price, $estimate->user->currency) !!}
            </td>
            <td class="inv-item items" style="text-align: right; color: #040405;">
                {!! format_money_pdf($item->total, $estimate->user->currency) !!}
            </td>
        </tr>
        @php
            $index += 1
        @endphp
    @endforeach
</table>

<table width="100%" cellspacing="0px" style="margin-left:420px" border="0" class="table3 @if(count($estimate->items) > 12) page-break @endif">
    <tr>
        <td class="no-borde" style="color: #55547A; padding-left:10px;  font-size:12px;">Subtotal</td>
        <td class="no-border items"
            style="padding-right:10px; text-align: right;  font-size:12px; color: #040405; font-weight: 500;">{!! format_money_pdf($estimate->sub_total, $estimate->user->currency) !!}</td>
    </tr>
    <tr>
        <td style="padding:3px 0px"></td>
        <td style="padding:3px 0px"></td>
    </tr>
    <tr>
        <td class="no-border total-border-left"
            style="padding-left:10px; padding-bottom:10px; text-align:left; padding-top:20px; font-size:12px;  color: #55547A;"
        >
            <label class="total-bottom"> Total </label>
        </td>
        <td
            class="no-border total-border-right items padd8"
            style="padding-right:10px; font-weight: 500; text-align: right; font-size:12px;  padding-top:20px; color: #5851DB"
        >
            {!! format_money_pdf($estimate->total, $estimate->user->currency)!!}
        </td>
    </tr>
</table>
