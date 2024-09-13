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

        <v-google-meet-calendar-connector></v-google-meet-calendar-connector>
    </div>

    @pushOnce('scripts')
        <script 
            type="text/x-template"
            id="v-google-meet-calendar-connector"
        >
            <div class="box-shadow flex flex-col gap-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 max-xl:flex-wrap">
                <div class="flex gap-2 border-b border-gray-200 dark:border-gray-800">
                    <!-- Tabs -->
                    <template v-for="tab in tabs" :key="tab.id">
                        <div
                            :class="[
                                'inline-block px-3 py-2.5 border-b-2  text-sm font-medium cursor-pointer',
                                activeTab === tab.id
                                ? 'text-brandColor border-brandColor dark:brandColor dark:brandColor'
                                : 'text-gray-600 dark:text-gray-300  border-transparent hover:text-gray-800 hover:border-gray-400 dark:hover:border-gray-400  dark:hover:text-white'
                            ]"
                            @click="scrollToSection(tab.id)"
                        >
                            @{{ tab.label }}
                        </div>
                    </template>
                </div>

                <div class="animate-[on-fade_0.5s_ease-in-out] p-4">
                    <div class="flex flex-col gap-4">
                        
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-google-meet-calendar-connector', {
                template: '#v-google-meet-calendar-connector',

                data() {
                    return {
                        tabs: [
                            {
                                id: 'google-meet',
                                label: 'Google Meet'
                            },
                            {
                                id: 'calendar',
                                label: 'Calendar'
                            }
                        ],

                        activeTab: 'google-meet'
                    }
                },

                methods: {
                    scrollToSection(id) {
                        this.activeTab = id;
                    },
                },
            })
        </script>

    @endPushOnce
</x-admin::layouts>