@extends('layouts.admin')

@section('content')
    <x-admin-page-header eyebrow="Application" title="Settings" description="Control customer access requirements across WINNER." />
    <form class="admin-settings-form" method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PATCH')
        <fieldset>
            <legend>Customer email verification</legend>
            <p>When enabled, customers must verify their email before checkout and order history access.</p>
            <label class="admin-setting-option"><input type="radio" name="require_customer_email_verification" value="1" @checked($verificationRequired)><span><strong>Enabled</strong><small>Queue verification emails and require verified customer access.</small></span></label>
            <label class="admin-setting-option"><input type="radio" name="require_customer_email_verification" value="0" @checked(! $verificationRequired)><span><strong>Disabled</strong><small>Allow authenticated customers to checkout and view orders without verification.</small></span></label>
        </fieldset>
        <button class="btn btn-dark" type="submit">Save settings</button>
    </form>
@endsection
