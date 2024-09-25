@php
    $account = app('\Webkul\Google\Repositories\AccountRepository')->findOneByField('user_id', auth()->user()->id)?->scopes;

    $canCreateMeet = $account && in_array('meet', $account);
@endphp

<!-- Google Meet Activity Vue Component -->
<v-google-meet-activity></v-google-meet-activity>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-google-meet-activity-template"
    >
        <x-admin::form.control-group>
            <div class="flex">
                @if ($canCreateMeet)
                    <template v-if="! meet.hasMeetLocation">
                        <!-- Create Google Meet -->
                        <button 
                            type="button"
                            class="secondary-button"
                            @click="create"
                        >
                            <!-- Spinner -->
                            <x-admin::spinner
                                class="h-4 w-4"
                                v-if="isLoading"
                            />

                            <div class="flex items-center gap-2">
                                <img 
                                    src="{{ vite()->asset('images/google-meet-icon.png', 'google') }}"
                                    class="h-5 w-5"
                                >
                
                                <p>@lang('google::app.activity.google-meet')</p>
                            </div>
                        </button>
                    </template>

                    <template v-else>
                        <!-- Join Google Meet -->
                        <div class="flex items-center gap-2">
                            <a 
                                :href="meet?.location"
                                target="_blank"
                                class="secondary-button"
                            >
                                <div class="flex items-center gap-2">
                                    <img 
                                        src="{{ vite()->asset('images/google-meet-icon.png', 'google') }}"
                                        class="h-5 w-5"
                                    >
                    
                                    <p>@lang('google::app.activity.join-google-meet')</p>
                                </div>
                            </a>

                            <!-- Remove -->
                            <span
                                @click="remove"
                                title="@lang('google::app.activity.remove-google-meet')"
                                class="icon-delete cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                            ></span>
                        </div>
                    </template>
                @else
                    <!-- Connect to Google Meet -->
                    <a 
                        href="{{ route('admin.google.index', ['route' => 'meet']) }}"
                        class="secondary-button"
                        @click="create"
                    >
                        <div class="flex items-center gap-2">
                            <img 
                                src="{{ vite()->asset('images/google-meet-icon.png', 'google') }}"
                                class="h-5 w-5"
                            >
            
                            <p>@lang('google::app.activity.connect-google-meet')</p>
                        </div>
                    </a>
                @endif
            </div>
        </x-admin::form.control-group>
    </script>

    <script type="module">
        app.component('v-google-meet-activity', {
            template: '#v-google-meet-activity-template',

            data() {
                return {
                    isLoading: false,

                    meet: {
                        hasMeetLocation: false,
                        location: '',
                        comment: '',
                    }
                };
            },

            methods: {
                remove() {
                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            this.setFormValues({
                                location: '',
                                comment: '',
                            });
                        },
                    });
                },

                setFormValues({ location, comment, hasMeetLocation = false }) {
                    this.$parent.$parent.$parent.$parent.$refs.modalForm.setValues({
                        location,
                        comment,
                    });

                    this.meet = {
                        hasMeetLocation,
                        location,
                        comment,
                    };
                },

                create(event) {                    
                    this.isLoading = true;
                    
                    let formData = new FormData(event.target.closest('form'));

                    let participants = {
                        users: [],
                        persons: [],
                    };

                    let index = 0;
                   
                    while (formData.has(`participants.users[${index}]`)) {
                        if (formData.getAll(`participants.users[${index}]`)[0]) {
                            participants.users.push(formData.getAll(`participants.users[${index}]`)[0]);
                        }

                        if (formData.getAll(`participants.persons[${index}]`)[0]) {
                            participants.persons.push(formData.getAll(`participants.persons[${index}]`)[0]);
                        }

                        index++;
                    }

                    this.$axios.post('{{ route('admin.google.meet.create_link') }}', {
                        'title': formData.get('title'),
                        'schedule_from': formData.get('schedule_from'),
                        'schedule_to': formData.get('schedule_to'),
                        participants,
                    })
                        .then(response => {
                            this.setFormValues({
                                location: response.data.link,
                                comment: response.data.comment,
                                hasMeetLocation: true,
                            });
                        })
                        .catch(error => {
                            this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                },
            },
        });
    </script>
@endPushOnce
