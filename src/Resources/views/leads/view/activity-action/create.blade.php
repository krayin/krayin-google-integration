@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/google/assets/css/admin.css') }}">
@endpush

@push('scripts')
    <script type="text/javascript">
        $(function() {
            var account = @json(app('\Webkul\Google\Repositories\AccountRepository')->findOneByField('user_id', auth()->user()->id));

            if (account && account.scopes.includes('meet')) {
                $('.video-conference').append('<button type="button" class="btn btn-sm btn-secondary-outline create-link" id="create-google-meet-link"><i class="icon google-meet-icon"></i>Google Meet</button>');

                $('#create-google-meet-link').on('click', function(e) {
                    window.app.pageLoaded = false;

                    var formElement = $('.video-conference').parents('form');

                    window.axios.post(`{{ route('admin.google.meet.create_link') }}`, {
                        'title': formElement.find('input[name="title"]').val(),
                        'schedule_from': formElement.find('input[name="schedule_from"]').val(),
                        'schedule_to': formElement.find('input[name="schedule_to"]').val(),
                        'participants': {
                            'users': $("input[name='participants[users][]").map(function(){return $(this).val();}).get(),
                            'persons': $("input[name='participants[persons][]").map(function(){return $(this).val();}).get(),
                        }
                    }).then(response => {
                        window.app.pageLoaded = true;

                        $('input[name=location]').val(response.data.link);

                        $('#activity-comment').val(response.data.comment);

                        $('.video-conference').append('<span class="join-google-meet-link join-link"><a href="' + response.data.link + '" target="_blank" class="btn btn-sm btn-secondary-outline">Join Google Meet</a><i class="icon trash-icon" id="remove-google-meet-button"></i></span>');

                        $('.create-link').hide();

                        $('.connect-account').hide();
                    })
                    .catch(error => {});
                });

                $('.video-conference').delegate('#remove-google-meet-button', 'click', function(e) {
                    $('.join-link').remove();

                    $('.create-link').show();

                    $('.connect-account').show();

                    $('input[name=location]').val('');
                });
            } else {
                $('.video-conference').append('<a href="{{ route('admin.google.index', ['route' => 'meet']) }}" target="_blank" class="btn btn-sm btn-secondary-outline connect-account" id="connect-google-account"><i class="icon google-meet-icon"></i>Connect Google Account</a>');
            }
        });
    </script>
@endpush