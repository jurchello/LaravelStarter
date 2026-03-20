@extends('admin-panel.layouts.admin')

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="mail-previews"
        data-testid="admin-mail-previews-page"
    >
        <x-admin.page-header
            title="Mail Previews"
            subtitle="Local auth email previews for fast browser review."
        />

        <x-admin.surface padded>
            <x-admin.toolbar title="Templates" />

            <x-admin.table>
                <thead>
                    <tr>
                        <th>Template</th>
                        <th>Purpose</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Email Verification</td>
                        <td>Preview the default verification notification email.</td>
                        <td>
                            <a
                                class="admin-button admin-button--primary"
                                href="{{ route('admin.mail-previews.show', ['template' => 'verify-email']) }}"
                                target="_blank"
                                rel="noreferrer"
                                data-testid="admin-mail-preview-verify-email"
                            >
                                Open preview
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Password Reset</td>
                        <td>Preview the default password reset notification email.</td>
                        <td>
                            <a
                                class="admin-button admin-button--primary"
                                href="{{ route('admin.mail-previews.show', ['template' => 'password-reset']) }}"
                                target="_blank"
                                rel="noreferrer"
                                data-testid="admin-mail-preview-password-reset"
                            >
                                Open preview
                            </a>
                        </td>
                    </tr>
                </tbody>
            </x-admin.table>
        </x-admin.surface>
    </x-admin.page>
@endsection
