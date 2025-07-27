# Bugfix: Participant Handling in SPPD Creation

## Problem Description
When creating an SPPD, users were experiencing a database error:
```
SQLSTATE[22P02]: Invalid text representation: 7 ERROR: invalid input syntax for type bigint: "15,7"
```

## Root Cause Analysis
The issue was in the participant handling logic in both `store()` and `update()` methods of `TravelRequestController`. The JavaScript was sending participant data in an unexpected format:

**Expected format:** `["15", "7"]` (array of individual IDs)
**Actual format:** `["15,7"]` (array containing a single comma-separated string)

## Solution Implemented

### 1. Enhanced Participant Parsing Logic
Updated both `store()` and `update()` methods in `app/Http/Controllers/TravelRequestController.php` to handle multiple input formats:

```php
if ($request->filled('participants')) {
    $participants = $request->participants;
    $participantIds = collect();

    if (is_string($participants)) {
        // Case 1: "15,7" (single string, comma-separated) - Old JS behavior
        $participantIds = collect(explode(',', $participants))
            ->map(function($id) { return trim($id); })
            ->filter(function($id) { return !empty($id) && is_numeric($id); })
            ->map(function($id) { return (int)$id; });
    } elseif (is_array($participants)) {
        // Case 2: ["15", "7"] (array of strings) OR ["15,7"] (array with single comma-separated string)
        // Check if the array contains a single string that is comma-separated
        if (count($participants) === 1 && is_string($participants[0]) && str_contains($participants[0], ',')) {
            $participantIds = collect(explode(',', $participants[0]))
                ->map(function($id) { return trim($id); })
                ->filter(function($id) { return !empty($id) && is_numeric($id); })
                ->map(function($id) { return (int)$id; });
        } else {
            // Case 3: [15, 7] (array of integers) or ["15", "7"] (array of strings, no commas)
            $participantIds = collect($participants)
                ->filter(function($id) { return !empty($id) && is_numeric($id); }) // Ensure numeric
                ->map(function($id) { return (int)$id; }); // Cast to int
        }
    }

    // Filter out admin and self, then sync
    $filteredParticipantIds = $participantIds
        ->filter(function ($id) {
            $user = \App\Models\User::find($id);
            return $user && $user->role !== 'admin' && $user->id !== Auth::id();
        })->values()->all();

    $travelRequest->participants()->sync($filteredParticipantIds);
}
```

### 2. JavaScript Frontend Fix
The JavaScript in `resources/js/forms/sppd-form.js` was already correctly creating multiple hidden input fields:

```javascript
// Clear existing hidden inputs
const existingInputs = document.querySelectorAll('input[name="participants[]"]');
existingInputs.forEach(input => input.remove());

// Create new hidden inputs for each participant
selectedPeserta.forEach(participantId => {
    const newInput = document.createElement('input');
    newInput.type = 'hidden';
    newInput.name = 'participants[]';
    newInput.value = participantId;
    pesertaHidden.parentNode.appendChild(newInput);
});
```

## Test Coverage
Added specific tests to verify the fix:

1. **`test_participants_handling_fix()`** - Tests the participant parsing logic with various input formats
2. **`test_form_submission_with_participants()`** - Tests complete form submission with participants

## Verification
- ✅ All participant handling tests pass
- ✅ Form submission with participants works correctly
- ✅ Database error `invalid input syntax for type bigint: "15,7"` is resolved
- ✅ Multiple participant selection works as expected

## Files Modified
1. `app/Http/Controllers/TravelRequestController.php` - Enhanced participant parsing logic
2. `tests/Feature/RealSystemWorkflowTest.php` - Added test coverage

## Impact
- **Before:** SPPD creation failed with database error when participants were selected
- **After:** SPPD creation works correctly with any number of participants
- **Compatibility:** Maintains backward compatibility with existing data formats

---
*Bugfix implemented on: July 27, 2025*
*Status: ✅ RESOLVED* 