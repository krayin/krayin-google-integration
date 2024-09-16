<x-admin::layouts>
    <!-- Title -->
    <x-slot:title>
        @lang('google::app.calendar.index.title')
    </x-slot>

    <!-- Body -->
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <div class="flex cursor-pointer items-center">
                    <x-admin::breadcrumbs name="google.calendar.create" />
                </div>

                <div class="text-xl font-bold dark:text-white">
                    @lang('google::app.calendar.index.title')
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="box-shadow flex flex-col gap-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 max-xl:flex-wrap">
            <div class="flex gap-2 border-b border-gray-200 dark:border-gray-800">
                <!-- Tabs -->
               <x-google::tabs />
            </div>

            <div class="flex flex-col gap-4 px-4 py-2">
                @if ($account && in_array('calendar', $account->scopes ?? []))
                    <!-- Remove the account -->
                    <x-admin::form         
                        :action="route('admin.google.destroy', $account->id)"
                        method="DELETE"
                        class="border-b border-gray-200 p-4 dark:border-gray-800"
                    >
                        <input 
                            name="route"
                            type="hidden"
                            value="calendar"
                        >

                        <div class="flex gap-2">
                            <img 
                                src="{{ vite()->asset('images/google-calendar-icon.png', 'google') }}"
                                class="h-10 w-10"
                            >

                            <div class="flex flex-col gap-2">
                                <h1 class="text-1xl font-semibold leading-none dark:text-white">
                                    @lang('google::app.calendar.index.title')
                                </h1>
                                
                                <p class="dark:text-white">@lang('google::app.calendar.index.info')</p>

                                <div class="flex">
                                    <button
                                        type="submit"
                                        class="text-red-500 hover:underline"
                                    >
                                        @lang('google::app.calendar.index.remove')
                                    </button>
                                </div>
                            </div>
                        </div>
                    </x-admin::form>

                    <!-- Sync the account -->
                    <x-admin::form         
                        :action="route('admin.google.calendar.sync', $account->id)"
                        method="POST"
                    >
                        <div class="w-1/2">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('google::app.calendar.index.synced-account')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="account_name"
                                    :value="$account->name"
                                    disabled
                                />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('google::app.calendar.index.select-calendar')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="calendar_id"
                                    name="calendar_id"
                                    :value="$account?->calendars->first(fn($calendar) => $calendar?->is_primary)?->id"
                                    :label="trans('google::app.calendar.index.title')"
                                    rules="required"
                                >
                                    @foreach ($account->calendars as $calendar)
                                        <option 
                                            value="{{ $calendar->id }}"
                                            @if ($calendar->is_primary) selected @endif
                                        >
                                            {{ $calendar->name }}
                                        </option>
                                    @endforeach
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="calendar_id" />
                            </x-admin::form.control-group>
                        </div>

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('google::app.calendar.index.save-and-sync')
                        </button>
                    </x-admin::form>
                @else
                    <!-- Connect to the Google Account -->
                    <div class="flex">
                        <a
                            href="{{ route('admin.google.store', ['route' => 'calendar']) }}"
                            class="secondary-button"
                        >
                            <div class="flex items-center gap-2">
                                <img 
                                    src="{{ vite()->asset('images/google-calendar-icon.png', 'google') }}"
                                    class="h-4 w-4"
                                >

                                <p>@lang('google::app.calendar.index.connect')</p>
                            </div>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin::layouts>