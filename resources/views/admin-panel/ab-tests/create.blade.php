@extends('admin-panel.layouts.admin')

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="ab-test-create"
        data-page-state="ready"
        aria-busy="false"
        data-ab-test-create-endpoint="{{ route('admin.api.ab-tests.store', absolute: false) }}"
        data-ab-test-audience-estimate-endpoint="{{ route('admin.api.ab-tests.audience-estimate', absolute: false) }}"
        data-ab-tests-base-route="{{ route('admin.ab-tests.index', absolute: false) }}"
        data-testid="admin-ab-test-create-page"
    >
        <x-admin.resource-header
            title="Create AB Test"
            subtitle="Define the experiment identity and default traffic allocation before adding variants."
        >
            <x-admin.button href="{{ route('admin.ab-tests.index') }}">Back to tests</x-admin.button>
        </x-admin.resource-header>

        <x-admin.form data-ab-test-create-form>
            <x-admin.form-section
                title="Experiment setup"
                description="The test starts in draft and can be activated after variants are configured."
            >
                <x-admin.form-grid class="admin-form-grid--2">
                    <x-admin.field for="ab-test-name" label="Name">
                        <x-admin.input
                            id="ab-test-name"
                            name="name"
                            autocomplete="off"
                            placeholder="Homepage Hero"
                            value="{{ old('name') }}"
                            data-ab-test-input="name"
                        />
                    </x-admin.field>

                    <x-admin.field for="ab-test-slug" label="Slug" hint="Auto-generated from the name. You can adjust it before creation only.">
                        <x-admin.input
                            id="ab-test-slug"
                            name="slug"
                            autocomplete="off"
                            placeholder="homepage-hero"
                            value="{{ old('slug') }}"
                            data-ab-test-input="slug"
                        />
                    </x-admin.field>
                </x-admin.form-grid>

                <x-admin.field for="ab-test-traffic" label="Traffic Percent" hint="Allowed range is 0 to 100 while drafting.">
                    <x-admin.input
                        id="ab-test-traffic"
                        name="trafficPercent"
                        type="number"
                        min="0"
                        max="100"
                        value="{{ old('trafficPercent', '100') }}"
                        data-ab-test-input="traffic"
                    />
                    <x-admin.form-help data-ab-test-traffic-estimate></x-admin.form-help>
                </x-admin.field>

                <x-admin.field label="Variant distribution" hint="Equal split picks a variant uniformly at random. Manual weights use the configured variant percentages.">
                    <x-admin.checkbox
                        id="ab-test-distribution-mode"
                        name="distributionMode"
                        label="Split evenly across all variants"
                        :checked="old('distributionMode', 'manual') === 'equal'"
                        data-ab-test-input="split-evenly"
                    />
                </x-admin.field>

                <x-admin.form-actions>
                    <x-admin.button type="submit" variant="primary">Create test</x-admin.button>
                </x-admin.form-actions>
            </x-admin.form-section>
        </x-admin.form>
    </x-admin.page>
@endsection
