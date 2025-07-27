{{-- Select2 Accessibility Fixes --}}
<script>
$(document).ready(function() {
    // Fix Select2 accessibility issues
    $('.select2-container').attr('role', 'combobox');
    $('.select2-container').attr('aria-haspopup', 'true');
    $('.select2-container').attr('aria-expanded', 'false');
    
    // Fix autocapitalize issue for Safari
    $('.select2-search__field').each(function() {
        // Remove autocapitalize attribute (not supported by Safari)
        $(this).removeAttr('autocapitalize');
        // Use alternative approach
        $(this).css('text-transform', 'none');
    });
    
    // Add proper ARIA labels
    $('.select2-selection').each(function() {
        var label = $(this).closest('.form-group').find('.form-label').text().trim();
        if (label) {
            $(this).attr('aria-label', label);
        }
    });
    
    // Update ARIA expanded state on dropdown open/close
    $(document).on('select2:open', function(e) {
        $(e.target).next('.select2-container').attr('aria-expanded', 'true');
    });
    
    $(document).on('select2:close', function(e) {
        $(e.target).next('.select2-container').attr('aria-expanded', 'false');
    });
});
</script> 