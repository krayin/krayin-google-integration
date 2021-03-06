@extends('admin::layouts.master')

@section('page_title')
    {{ __('google::app.calendar') }}
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
                <h1>{{ __('google::app.calendar') }}</h1>
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
                            <li class="active">
                                <a>{{ __('google::app.calendar') }}</a>
                            </li>

                            <li>
                                <a href="{{ route('admin.google.index', ['route' => 'meet']) }}">{{ __('google::app.meet') }}</a>
                            </li>
                        </ul>
                    </div>

                    @if ($account && in_array('calendar', $account->scopes ?? []))
                        <div class="tabs-content configure-google">
                            <div class="header">
                                <form method="POST" action="{{ route('admin.google.destroy', $account->id) }}">
                                    @csrf()

                                    <input name="_method" type="hidden" value="DELETE">

                                    <input name="route" type="hidden" value="calendar">

                                    <div class="icon-container">
                                        <span class="google-calendar-icon"></span>
                                    </div>

                                    <div class="title">
                                        <span>{{ __('google::app.google-calendar') }}</span>

                                        <p>{{ __('google::app.google-calendar-info') }}</p>

                                        <button type="submit" onclick="return confirm('{{ __('google::app.confirm-remove') }}')">{{ __('google::app.remove') }}</button>
                                    </div>
                                </form>
                            </div>

                            <div class="content">
                                <form method="POST" action="{{ route('admin.google.calendar.sync', $account->id) }}" @submit.prevent="onSubmit">
                                    @csrf()

                                    <div class="form-group">
                                        <label>{{ __('google::app.synced-account') }}</label>

                                        <input class="control" value="{{ $account->name }}" disabled/>
                                    </div>


                                    <div class="form-group" :class="[errors.has('calendar_id') ? 'has-error' : '']">
                                        <label class="required">{{ __('google::app.select-calendar') }}</label>

                                        <select name="calendar_id" class="control" v-validate="'required'">
                                            <option value="">{{ __('google::app.select') }}</option>

                                            @foreach ($account->calendars as $calendar)
                                                <option value="{{ $calendar->id }}" @if ($calendar->is_primary) selected @endif>{{ $calendar->name }}</option>
                                            @endforeach
                                        </select>

                                        <span class="control-info">{{ __('google::app.select-calendar-info') }}</span>

                                        <span class="control-error" v-if="errors.has('calendar_id')">
                                            @{{ errors.first('calendar_id') }}
                                        </span>
                                    </div>

                                    <button type="submit" class="btn btn-sm btn-primary">
                                        {{ __('google::app.save-sync')}}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="tabs-content connect-google">
                            <a href="{{ route('admin.google.store', ['route' => 'calendar']) }}" class="connect-google-btn">
                                <div class="icon-container">
                                    <span class="google-calendar-icon"></span>
                                </div>

                                <div class="title">
                                    <span>{{ __('google::app.connect-google-calendar') }}</span>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop