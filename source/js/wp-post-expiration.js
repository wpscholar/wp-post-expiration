import flatpickr from 'flatpickr';
import ConfirmDatePlugin from 'flatpickr/dist/plugins/confirmDate/confirmDate';

Array
    .from(document.querySelectorAll('.wp-post-expiration'))
    .forEach(
        (field) => {
            flatpickr(
                field.querySelector('.js-flatpickr'),
                {
                    altInput: true,
                    altFormat: 'M j, Y h:i K', // https://chmln.github.io/flatpickr/formatting/
                    dateFormat: 'U',
                    enableTime: true,
                    onReady: (dateObj, dateStr, instance) => {
                        const clearBtn = field.querySelector('.js-flatpickr-reset');
                        clearBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            instance.clear();
                            instance.close();
                        });

                    },
                    plugins: [new ConfirmDatePlugin({})]
                }
            );
        }
    );