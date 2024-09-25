<x-admin::layouts>
    <!-- Title -->
    <x-slot:title>
        @lang('google::app.meet.index.title')
    </x-slot>

    <!-- Body -->
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <div class="flex cursor-pointer items-center">
                    <x-admin::breadcrumbs name="google.meet.create" />
                </div>

                <div class="text-xl font-bold dark:text-white">
                    @lang('google::app.meet.index.title')
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
                @if ($account && in_array('meet', $account->scopes ?? []))
                    <!-- Remove the account -->
                    <x-admin::form         
                        :action="route('admin.google.destroy', $account->id)"
                        method="DELETE"
                        class="p-4"
                    >
                        <input 
                            name="route"
                            type="hidden"
                            value="meet"
                        >

                        <div class="flex gap-2">
                            <img 
                                src="{{ vite()->asset('images/google-meet-icon.png', 'google') }}"
                                class="h-10 w-10"
                            >

                            <div class="flex flex-col gap-2">
                                <h1 class="text-1xl font-semibold leading-none dark:text-white">
                                    @lang('google::app.meet.index.title')
                                </h1>
                                
                                <p class="dark:text-white">@lang('google::app.meet.index.info')</p>

                                <div class="flex">
                                    <button
                                        type="submit"
                                        class="text-red-500 hover:underline"
                                    >
                                        @lang('google::app.meet.index.remove')
                                    </button>
                                </div>
                            </div>
                        </div>
                    </x-admin::form>
                @else
                    <!-- Connect to the Google Account -->
                    <div class="flex">
                        <a
                            href="{{ route('admin.google.store', ['route' => 'meet']) }}"
                            class="secondary-button"
                        >
                            <div class="flex items-center gap-2">
                                <img 
                                    src="{{ vite()->asset('images/google-meet-icon.png', 'google') }}"
                                    class="h-4 w-4"
                                >

                                <p>@lang('google::app.meet.index.connect')</p>
                            </div>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin::layouts>