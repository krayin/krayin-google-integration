@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/google/assets/css/admin.css') }}">
@endpush

@push('scripts')
    <script type="text/javascript">
        $(function() {
            var activity = @json($activity);

            if (activity.location.includes('meet.google.com')) {
                $('.video-conference').append('<button type="button" class="btn btn-sm btn-secondary-outline" id="create-google-meet-link" style="display: none"><i class="icon google-meet-icon"></i>Google Meet</button>');

                $('.video-conference').append('<div class="join-google-meet-link"><a href="' + activity.location + '" target="_blank" class="btn btn-sm btn-secondary-outline">Join Google Meet</a><i class="icon trash-icon" id="remove-google-meet-button"></i></div>');
            } else {
                $('.video-conference').append('<button type="button" class="btn btn-sm btn-secondary-outline" id="create-google-meet-link"><i class="icon google-meet-icon"></i>Google Meet</button>');
            }

            $('#create-google-meet-link').on('click', function(e) {
                window.app.pageLoaded = false;

                window.axios.post(`{{ route('admin.google.meet.create_link') }}`, {
                    }).then(response => {
                        window.app.pageLoaded = true;

                        $('input[name=location]').val(response.data.link);

                        $('#activity-comment').val(response.data.comment);

                        $('.video-conference').append('<div class="join-google-meet-link"><a href="' + response.data.link + '" target="_blank" class="btn btn-sm btn-secondary-outline">Join Google Meet</a><i class="icon trash-icon" id="remove-google-meet-button"></i></div>');

                        $('#create-google-meet-link').hide();
                    })
                    .catch(error => {});
            });

            $('.video-conference').delegate('#remove-google-meet-button', 'click', function(e) {
                $('.join-google-meet-link').remove();

                $('#create-google-meet-link').show();

                $('input[name=location]').val('');
            });
        });
    </script>
@endpush