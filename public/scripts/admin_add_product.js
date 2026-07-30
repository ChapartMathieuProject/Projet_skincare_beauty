document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.upload-zone input[type="file"]').forEach(input => {
        input.addEventListener('change', () => {
            const zone = input.closest('.upload-zone');
            const icon = zone.querySelector('i');
            const file = input.files[0];

            if (zone.dataset.previewUrl) {
                URL.revokeObjectURL(zone.dataset.previewUrl);
                delete zone.dataset.previewUrl;
            }

            if (!file) {
                zone.style.backgroundImage = '';
                zone.classList.remove('has-preview');
                if (icon) icon.style.display = '';
                return;
            }

            const url = URL.createObjectURL(file);
            zone.dataset.previewUrl = url;
            zone.style.backgroundImage = `url("${url}")`;
            zone.style.backgroundSize = 'cover';
            zone.style.backgroundPosition = 'center';
            zone.classList.add('has-preview');
            if (icon) icon.style.display = 'none'; 
        });
    });
});
