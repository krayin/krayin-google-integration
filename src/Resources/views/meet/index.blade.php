@extends('admin::layouts.master')

@section('page_title')
    {{ __('google::app.meet') }}
@stop

@push('css')
    <style>
        #options > div {
            padding: 10px;
            box-shadow: rgb(0 0 0 / 24%) 0px 3px 8px;
        }
    </style>
@endpush

@section('content-wrapper')
    <div class="content full-page adjacent-center">
        {!! view_render_event('admin.google.header.before') !!}

        <div class="page-header">

            <div class="page-title">
                <h1>{{ __('google::app.meet') }}</h1>
            </div>
        </div>

        {!! view_render_event('admin.google.calendar.header.after') !!}

        <div class="page-content">
            <div class="form-container">

                <div class="panel">
                    <div class="panel-header">
                        {!! view_render_event('admin.google.calendar.form_buttons.before') !!}

                        <a href="{{ route('admin.settings.attributes.index') }}">{{ __('google::app.back') }}</a>

                        {!! view_render_event('admin.google.calendar.form_buttons.after') !!}
                    </div>

                    <div class="tabs">
                        <ul>
                            <li>
                                <a href="{{ route('admin.google.index', ['route' => 'calendar']) }}">{{ __('google::app.calendar') }}</a>
                            </li>

                            <li class="active">
                                <a>{{ __('google::app.meet') }}</a>
                            </li>
                        </ul>
                    </div>

                    @if ($account && in_array('meet', $account->scopes ?? []))
                        <div class="tabs-content configure-google">
                            <div class="header">
                                <form method="POST" action="{{ route('admin.google.destroy', $account->id) }}">
                                    @csrf()

                                    <input name="_method" type="hidden" value="DELETE">

                                    <input name="route" type="hidden" value="meet">

                                    <div class="icon-container">
                                        <span class="google-meet-icon"></span>
                                    </div>

                                    <div class="title">
                                        <span>{{ __('google::app.google-meet') }}</span>

                                        <p>{{ __('google::app.google-meet-info') }}</p>

                                        <button type="submit" onclick="return confirm('{{ __('google::app.confirm-remove') }}')">{{ __('google::app.remove') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="tabs-content connect-google">
                            <a href="{{ route('admin.google.store', ['route' => 'meet']) }}" class="connect-google-btn">
                                <div class="icon-container">
                                    <span class="google-meet-icon"></span>
                                </div>

                                <div class="title">
                                    <span>{{ __('google::app.connect-google-meet') }}</span>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop