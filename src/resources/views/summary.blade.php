@extends('web::layouts.grids.12')

@section('title', trans('billing::billing.summary'))
@section('page_header', trans('billing::billing.summary-live'))

@section('full')
  <div class="box box-default box-solid">
    <div class="box-header with-border">
      <h3 class="box-title">Previous Bills</h3>
    </div>
    <div class="box-body">
      @foreach($dates->chunk(3) as $date)
        <div class="row ">
          @foreach ($date as $yearmonth)
            <div class="col-xs-4">
              <span class="text-bold">
                <a href="{{ route('billing.pastbilling', ['year' => $yearmonth['year'], 'month' => $yearmonth['month']]) }}">
                {{ date('Y-M', mktime(0,0,0, $yearmonth['month'], 1, $yearmonth['year'])) }}</a>
              </span>
            </div>
          @endforeach
        </div>
      @endforeach
    </div>
  </div>

  <div class="nav-tabs-custom">
    <ul class="nav nav-tabs pull-right bg-gray">
      <li><a href="#tab3" data-toggle="tab">{{ trans('billing::billing.summary-ind-mining') }}</a></li>
      <li><a href="#tab2" data-toggle="tab">{{ trans('billing::billing.summary-corp-pve') }}</a></li>
      <li class="active"><a href="#tab1" data-toggle="tab">{{ trans('billing::billing.summary-corp-mining') }}</a></li>
      <li class="pull-left header">
        <i class="fa fa-line-chart"></i> Current Live Numbers
      </li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane active" id="tab1">
        <div class="col-md-12">
          <select class="form-control" style="width: 25%" id="alliancespinner">
            <option selected disabled>Choose an Alliance</option>
            <option value="0">All Alliances</option>
            @foreach($alliances as $alliance)
              <option value="{{ $alliance->alliance_id }}">{{ $alliance->name }}</option>
            @endforeach
          </select>
        </div>
        <table class="table table-striped" id='livenumbers'>
          <thead>
          <tr>
            <th>Corporation</th>
            <th>Mined Amount</th>
            <th>Percentage of Market Value</th>
            <th>Adjusted Value</th>
            <th>Tax Rate</th>
            <th>Tax Owed</th>
            <th>Registered Users</th>
          </tr>
          </thead>
          <tbody>
          @foreach($stats as $row)
            <tr>
              <td>{{ $row->name }}</td>
              <td class="text-right" data-order="{{ $row->mining }}">{{ number_format($row->mining, 2) }} ISK</td>
              @if($row->actives / $row->members < (setting('irate', true) / 100))
              <td class="text-right" data-order="{{ setting('oremodifier', true) }}">{{ setting('oremodifier', true) }}%</td>
              @else
              <td class="text-right" data-order="{{ setting('ioremodifier', true) }}">{{ setting('ioremodifier', true) }}%</td>
              @endif
              @if($row->actives / $row->members < (setting('irate', true) / 100))
              <td class="text-right" data-order="{{ $row->mining * (setting('oremodifier', true) / 100) }}">{{ number_format(($row->mining * (setting('oremodifier', true) / 100)), 2) }} ISK</td>
              @else
              <td class="text-right" data-order="{{ $row->mining * (setting('ioremodifier', true) / 100) }}">{{ number_format(($row->mining * (setting('ioremodifier', true) / 100)), 2) }} ISK</td>
              @endif
              @if($row->actives / $row->members < (setting('irate', true) / 100))
              <td class="text-right" data-order="{{ setting('oretaxrate', true) }}">{{ setting('oretaxrate', true) }}%</td>
              @else
              <td class="text-right" data-order="{{ setting('ioretaxrate', true) }}">{{ setting('ioretaxrate', true) }}%</td>
              @endif
              @if($row->actives / $row->members < (setting('irate', true) / 100))
              <td class="text-right" data-order="{{ ($row->mining * (setting('oremodifier', true) / 100)) * (setting('oretaxrate', true) / 100) }}">{{ number_format(($row->mining * (setting('oremodifier', true) / 100)) * (setting('oretaxrate', true) / 100), 2) }} ISK</td>
              @else
              <td class="text-right" data-order="{{ ($row->mining * (setting('ioremodifier', true) / 100)) * (setting('ioretaxrate', true) / 100) }}">{{ number_format(($row->mining * (setting('ioremodifier', true) / 100)) * (setting('ioretaxrate', true) / 100), 2) }} ISK</td>
              @endif
              @if ($row->members > 0)
              <td class="text-right" data-order="{{ $row->actives / $row->members }}">
                {{ $row->actives }} / {{ $row->members }}
                ({{ round(($row->actives / $row->members) * 100) }}%)
              </td>
              @else
              <td class="text-right" data-order="0">0 / 0 (0%)</td>
              @endif
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      <div class="tab-pane" id="tab2">
        <table class="table table-striped" id="livepve">
          <thead>
          <tr>
            <th>Corporation</th>
            <th>Total Bounties</th>
            <th>Tax Rate</th>
            <th>Tax Owed</th>
            <th>Registered Users</th>
          </tr>
          </thead>
          <tbody>
          @foreach($stats as $row)
            <tr>
              <td>{{ $row->name }}</td>
              <td class="text-right" data-order="{{ $row->bounties }}">{{ number_format($row->bounties, 2) }} ISK</td>
              @if($row->actives / $row->members < (setting('irate', true) / 100))
              <td class="text-right" data-order="{{ setting('bountytaxrate', true) }}">{{ setting('bountytaxrate', true) }}%</td>
              @else
              <td class="text-right" data-order="{{ setting('ibountytaxrate', true) }}">{{ setting('ibountytaxrate', true) }}%</td>
              @endif
              @if($row->actives / $row->members < (setting('irate', true) / 100))
              <td class="text-right" data-order="{{ $row->bounties * (setting('bountytaxrate', true)) }}">{{ number_format(($row->bounties * (setting('bountytaxrate', true) / 100)),2) }} ISK</td>
              @else
              <td class="text-right" data-order="{{ $row->bounties * (setting('ibountytaxrate', true)) }}">{{ number_format(($row->bounties * (setting('ibountytaxrate', true) / 100)),2) }} ISK</td>
              @endif
              @if ($row->members > 0)
              <td class="text-right" data-order="{{ $row->actives / $row->members }}">
                {{ $row->actives }} / {{ $row->members }}
                ({{ round(($row->actives / $row->members) * 100)  }}%)
              </td>
              @else
              <td class="text-right" data-order="0">0 / 0 (0%)</td>
              @endif
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      <div class="tab-pane" id="tab3">
        <div class="col-md-6">
          <select class="form-control" style="width: 50%" id="corpspinner">
            <option disabled selected value="0">Please Choose a Corp</option>
            @foreach($stats as $row)
              <option value="{{ $row->corporation_id }}">{{ $row->name }}</option>
            @endforeach
          </select>
        </div>
        <table class="table compact table-condensed table-hover table-responsive table-striped" id='indivmining'>
          <thead>
          <tr>
            <th>Character Name</th>
            <th>Mining Amount</th>
            <th>Mining Tax Modifier</th>
            <th>Mining Tax</th>
            <th>Tax Due</th>
          </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

@endsection

@push('javascript')
  @include('web::includes.javascript.id-to-name')
  <script type="application/javascript">
      table = $('#indivmining').DataTable({
          paging: false,
      });

      $('#corpspinner').change(function () {

          $('#indivmining').find('tbody').empty();
          id = $('#corpspinner').find(":selected").val();
          if (id > 0) {
              $.ajax({
                  headers: function () {
                  },
                  url: "/billing/getindbilling/" + id,
                  type: "GET",
                  dataType: 'json',
                  timeout: 10000
              }).done(function (result) {
                  if (result) {
                      table.clear();
                      for (var chars in result) {
                          table.row.add(['<a href=""><span class="id-to-name" data-id="' + chars + '">{{ trans('web::seat.unknown') }}</span></a>',
                              (new Intl.NumberFormat('en-US').format(result[chars].amount)) + " ISK",
                              (result[chars].modifier * 100) + "%",
                              (result[chars].taxrate * 100) + "%", 
                              (new Intl.NumberFormat('en-US', {maximumFractionDigits: 2}).format(result[chars].amount * result[chars].taxrate * result[chars].modifier)) + " ISK"]);
                      }
                      table.draw();
                      ids_to_names();
                  }
              });
          }
      });

      $(document).ready(function () {
          $('#corpspinner').select2();
      });

      $('#alliancespinner').change(function () {
          id = $('#alliancespinner').find(":selected").val();
              window.location.href = '/billing/alliance/' + id;
      });

      $('#livenumbers').DataTable();
      $('#livepve').DataTable();
  </script>
@endpush
