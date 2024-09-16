<a
    href="{{ route('admin.google.index', ['route' => 'calendar']) }}"
    class="
        inline-block cursor-pointer border-b-2 px-3 py-2.5 text-sm font-medium 
        {{ request('route') === 'calendar'
        ? 'text-brandColor border-brandColor dark:brandColor dark:brandColor'
        : 'text-gray-600 dark:text-gray-300  border-transparent hover:text-gray-800 hover:border-gray-400 dark:hover:border-gray-400  dark:hover:text-white' }}
    " 
>
    @lang('google::app.tabs.calendar')
</a>

<a
    href="{{ route('admin.google.index', ['route' => 'meet']) }}"
    class="
        inline-block cursor-pointer border-b-2 px-3 py-2.5 text-sm font-medium 
        {{ request('route') === 'meet'
        ? 'text-brandColor border-brandColor dark:brandColor dark:brandColor'
        : 'text-gray-600 dark:text-gray-300  border-transparent hover:text-gray-800 hover:border-gray-400 dark:hover:border-gray-400  dark:hover:text-white' }}
    " 
>
    @lang('google::app.tabs.meet')
</a>