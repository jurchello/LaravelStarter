@forelse ($tests as $test)
    <tr data-testid="ab-tests-table-row">
        <td>{{ $test->name }}</td>
        <td>{{ $test->slug }}</td>
        <td>
            <span class="admin-status-pill admin-status-pill--{{ $test->status }}">{{ $test->status }}</span>
        </td>
        <td>{{ $test->trafficPercent }}%</td>
        <td>{{ $test->variantsCount }}</td>
        <td>
            <x-admin.button href="{{ route('admin.ab-tests.show', $test->id) }}">Manage</x-admin.button>
        </td>
    </tr>
@empty
@endforelse
